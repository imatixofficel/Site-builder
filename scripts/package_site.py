#!/usr/bin/env python3
"""Build host-ready packages from a finished design.

  python3 package_site.py --target php|wordpress|static --name "Brand" --html build/index.html
         [--schema build/schema.json] --roots <skill>/image <skill>/fonts [/mnt/user-data/uploads]
         [--out /mnt/user-data/outputs] [--lang fa]

Inputs
  --html    the page Claude wrote. For php/wordpress it is a TEMPLATE using the mini syntax in
            references/cms-schema.md ({{ key }}, {{#each list}}...{{/each}}, {{#if key}}...{{/if}});
            images/fonts are referenced by plain relative paths (image/x.jpg, fonts/<family>/<file>.woff2).
  --schema  schema.json (editable texts/colors/images + lists). Needed for php and wordpress.

Outputs (in --out)
  static     : <slug>-static-host.zip         + <slug>-static-GUIDE-FA.txt
  php        : <slug>-php-host.zip            + <slug>-php-GUIDE-FA.txt   (guide contains the one-time install key)
  wordpress  : <slug>-wp-theme.zip, <slug>-sb-security.zip, <slug>-wp-hardening.txt, <slug>-wp-GUIDE-FA.txt
The script validates the template/schema first and refuses to build if there are errors.
"""
import sys, os, re, json, shutil, zipfile, secrets, hashlib, argparse, tempfile

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, HERE)
from inline_assets import Assets  # noqa: E402

SKILL = os.path.dirname(HERE)
TPL = os.path.join(SKILL, 'templates')
BUILTINS = {'site_name', 'site_description', 'site_lang', 'site_dir', 'year', 'base', 'home', 'contact_url', 'flash', 'csrf', 'form_fields'}
TYPES = {'text', 'textarea', 'url', 'color', 'image'}
KEY_RE = re.compile(r'^[a-z][a-z0-9_]{0,16}$')


def slugify(name):
    s = re.sub(r'[^A-Za-z0-9]+', '-', name).strip('-').lower()
    return s[:30] or 'site'


# ----------------------------------------------------------------- validation
def validate(template, schema, target):
    errs, warns = [], []
    keys = set()
    for s in schema.get('settings', []):
        k = s.get('key', '')
        if not KEY_RE.match(k):
            errs.append(f'setting key "{k}" must be lowercase letters/digits/_ (max 17 chars, start with a letter)')
        if k in keys:
            errs.append(f'duplicate key "{k}"')
        keys.add(k)
        if s.get('type', 'text') not in TYPES:
            errs.append(f'setting "{k}": unknown type "{s.get("type")}"')
    colls = {}
    for c in schema.get('collections', []):
        k = c.get('key', '')
        if not KEY_RE.match(k):
            errs.append(f'collection key "{k}" must be lowercase letters/digits/_ (max 17 chars)')
        if k in keys:
            errs.append(f'collection key "{k}" clashes with another key')
        if not c.get('fields'):
            errs.append(f'collection "{k}" has no fields')
        fl = set()
        for f in c.get('fields', []):
            if not KEY_RE.match(f.get('key', '')):
                errs.append(f'collection "{k}": field key "{f.get("key")}" invalid')
            if f.get('type', 'text') not in TYPES:
                errs.append(f'collection "{k}": field "{f.get("key")}" unknown type')
            fl.add(f.get('key'))
        colls[k] = fl
        keys.add(k)
    if 'site_name' not in {s.get('key') for s in schema.get('settings', [])}:
        warns.append('no "site_name" setting declared: the site name will not be editable in the panel')
    # template tags
    tags = re.findall(r'\{\{([^{}]*)\}\}', template)
    stack = []
    for raw in tags:
        t = raw.strip()
        if t.startswith('#each '):
            name = t[6:].strip()
            if name not in colls:
                errs.append(f'{{{{#each {name}}}}}: no such collection in schema')
            stack.append(('each', name))
        elif t.startswith('#if '):
            stack.append(('if', t[4:].strip()))
            n = t[4:].strip()
            if n not in keys and n not in BUILTINS and not n.startswith('@') and not any(n in f for f in colls.values()):
                warns.append(f'{{{{#if {n}}}}}: unknown name')
        elif t == '/each':
            if not stack or stack[-1][0] != 'each':
                errs.append('{{/each}} without matching {{#each}}')
            else:
                stack.pop()
        elif t == '/if':
            if not stack or stack[-1][0] != 'if':
                errs.append('{{/if}} without matching {{#if}}')
            else:
                stack.pop()
        elif t == 'else':
            pass
        else:
            name = t.split('|')[0].strip()
            if name in BUILTINS or name in keys or name.startswith('@'):
                continue
            if any(name in f for f in colls.values()):
                continue
            errs.append(f'unknown variable {{{{ {name} }}}} (not a setting, builtin or list field)')
    if stack:
        errs.append(f'unclosed block: {stack[-1][0]} {stack[-1][1]}')
    # CSP / robustness rules for CMS targets
    if target in ('php', 'wordpress'):
        if re.search(r'\son[a-z]+\s*=\s*["\']', template, re.I):
            errs.append('inline event handlers (onclick=, onload=...) are blocked by the security policy: use addEventListener in a <script> block')
        if re.search(r'href\s*=\s*["\']\s*javascript:', template, re.I):
            errs.append('javascript: links are not allowed')
        for m in re.finditer(r'<script[^>]+src=["\'](https?:)?//([^"\']+)', template, re.I):
            errs.append(f'external script {m.group(2)[:60]} is blocked by the security policy: inline the code instead')
        if 'contact_form' in schema and schema['contact_form'] and '{{ form_fields }}' not in template.replace('{{form_fields}}', '{{ form_fields }}'):
            errs.append('schema has contact_form=true but the template has no {{ form_fields }} inside the <form>')
        if '</head>' not in template.lower() or '</body>' not in template.lower():
            errs.append('template must be a full HTML page with <head> and <body>')
        if re.search(r'\{\{[^}]*\}\}', re.sub(r'\{\{[^{}]*\}\}', '', template)):
            warns.append('stray "{{" or "}}" found (JS/CSS?). Avoid literal double braces in code')
    return errs, warns


def process_schema(schema, assets):
    """Copy/optimize assets that the schema references (image/..., fonts/...) and rewrite to assets/<name>."""
    def walk(v):
        if isinstance(v, dict):
            return {k: walk(x) for k, x in v.items()}
        if isinstance(v, list):
            return [walk(x) for x in v]
        if isinstance(v, str) and re.match(r'^(image|fonts)/', v):
            return assets.convert(v, 'assets/') or v
        return v
    return walk(schema)


def php_lint(root):
    bad = []
    for d, _, files in os.walk(root):
        for f in files:
            if not f.endswith('.php'):
                continue
            s = open(os.path.join(d, f), encoding='utf-8').read()
            t = re.sub(r"'(?:\\.|[^'\\])*'", "''", s)
            t = re.sub(r'"(?:\\.|[^"\\])*"', '""', t)
            t = re.sub(r'/\*.*?\*/', '', t, flags=re.S)
            t = re.sub(r'(?m)^\s*//.*$', '', t)
            for a, b in ('{}', '()', '[]'):
                if t.count(a) != t.count(b):
                    bad.append(f'{os.path.relpath(os.path.join(d, f), root)}: unbalanced {a}{b}')
    return bad


# ----------------------------------------------------------------- zip helpers
def zip_dir(src, dest, top=None):
    if os.path.exists(dest):
        os.remove(dest)
    with zipfile.ZipFile(dest, 'w', zipfile.ZIP_DEFLATED) as z:
        for d, dirs, files in os.walk(src):
            dirs.sort()
            rel_d = os.path.relpath(d, src)
            base = '' if rel_d == '.' else rel_d
            if not files and not dirs:
                z.write(d, os.path.join(top or '', base) + '/')
            for f in sorted(files):
                arc = os.path.join(top or '', base, f)
                z.write(os.path.join(d, f), arc)


def read(p):
    return open(p, encoding='utf-8').read()


def fill(text, **kw):
    for k, v in kw.items():
        text = text.replace('{{' + k + '}}', v)
    return text


# ----------------------------------------------------------------- targets
def build_static(a, html, work, out):
    root = os.path.join(work, 'public_html')
    os.makedirs(root)
    assets = Assets(a.roots, 'folder', root)
    open(os.path.join(root, 'index.html'), 'w', encoding='utf-8').write(assets.rewrite_html(html, 'assets/'))
    shutil.copy(os.path.join(TPL, 'static', '.htaccess'), os.path.join(root, '.htaccess'))
    z = os.path.join(out, f'{a.slug}-static-host.zip')
    zip_dir(root, z)
    open(os.path.join(out, f'{a.slug}-static-GUIDE-FA.txt'), 'w', encoding='utf-8').write(fill(read(os.path.join(TPL, 'guides', 'static-fa.txt')), NAME=a.name))
    return [z, os.path.join(out, f'{a.slug}-static-GUIDE-FA.txt')], assets


def build_php(a, html, schema, work, out):
    root = os.path.join(work, 'public_html')
    shutil.copytree(os.path.join(TPL, 'php-core'), root)
    assets = Assets(a.roots, 'folder', root)
    tpl = assets.rewrite_html(html, '{{ base }}/assets/')
    schema = process_schema(schema, assets)
    open(os.path.join(root, 'theme', 'index.tpl.html'), 'w', encoding='utf-8').write(tpl)
    json.dump(schema, open(os.path.join(root, 'theme', 'schema.json'), 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
    key = secrets.token_hex(10)
    open(os.path.join(root, 'data', 'install.key'), 'w').write(hashlib.sha256(key.encode()).hexdigest())
    bad = php_lint(root)
    if bad:
        sys.exit('PHP lint failed:\n  ' + '\n  '.join(bad))
    z = os.path.join(out, f'{a.slug}-php-host.zip')
    zip_dir(root, z)
    g = os.path.join(out, f'{a.slug}-php-GUIDE-FA.txt')
    open(g, 'w', encoding='utf-8').write(fill(read(os.path.join(TPL, 'guides', 'php-fa.txt')), NAME=a.name, KEY=key))
    return [z, g], assets


def build_wp(a, html, schema, work, out):
    theme = os.path.join(work, a.slug)
    shutil.copytree(os.path.join(TPL, 'wordpress', 'theme'), theme)
    os.makedirs(os.path.join(theme, 'inc'), exist_ok=True)
    shutil.copy(os.path.join(TPL, 'php-core', 'inc', 'engine.php'), os.path.join(theme, 'inc', 'engine.php'))
    assets = Assets(a.roots, 'folder', theme)
    tpl = assets.rewrite_html(html, '{{ base }}/assets/')
    schema = process_schema(schema, assets)
    open(os.path.join(theme, 'index.tpl.html'), 'w', encoding='utf-8').write(tpl)
    json.dump(schema, open(os.path.join(theme, 'schema.json'), 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
    st = os.path.join(theme, 'style.css')
    open(st, 'w', encoding='utf-8').write(fill(read(st), THEME_NAME=a.name))
    bad = php_lint(theme)
    if bad:
        sys.exit('PHP lint failed:\n  ' + '\n  '.join(bad))
    tz = os.path.join(out, f'{a.slug}-wp-theme.zip')
    zip_dir(theme, tz, top=a.slug)
    plug = os.path.join(work, 'sb-security')
    os.makedirs(plug)
    shutil.copy(os.path.join(TPL, 'wordpress', 'security-plugin', 'sb-security.php'), plug)
    pz = os.path.join(out, f'{a.slug}-sb-security.zip')
    zip_dir(plug, pz, top='sb-security')
    sec = os.path.join(TPL, 'wordpress', 'security-plugin')
    hard = os.path.join(out, f'{a.slug}-wp-hardening.txt')
    with open(hard, 'w', encoding='utf-8') as f:
        for i, n in enumerate(['htaccess-additions.txt', 'uploads-htaccess.txt', 'wp-config-additions.txt'], 1):
            f.write(f'########## بخش {i} ##########\n' + read(os.path.join(sec, n)) + '\n\n')
    g = os.path.join(out, f'{a.slug}-wp-GUIDE-FA.txt')
    open(g, 'w', encoding='utf-8').write(fill(read(os.path.join(TPL, 'guides', 'wordpress-fa.txt')), NAME=a.name,
                                               THEME_ZIP=os.path.basename(tz), PLUGIN_ZIP=os.path.basename(pz), HARDEN_FILE=os.path.basename(hard)))
    return [tz, pz, hard, g], assets


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--target', required=True, choices=['static', 'php', 'wordpress'])
    ap.add_argument('--name', required=True)
    ap.add_argument('--html', required=True)
    ap.add_argument('--schema')
    ap.add_argument('--roots', nargs='+', required=True)
    ap.add_argument('--out', default='/mnt/user-data/outputs')
    ap.add_argument('--slug')
    a = ap.parse_args()
    a.slug = a.slug or slugify(a.name)
    html = read(a.html)
    schema = {}
    if a.target != 'static':
        if not a.schema:
            sys.exit('--schema is required for php/wordpress')
        schema = json.load(open(a.schema, encoding='utf-8'))
        errs, warns = validate(html, schema, a.target)
        for w in warns:
            print('WARN ', w)
        if errs:
            sys.exit('VALIDATION FAILED:\n  ' + '\n  '.join(errs))
    os.makedirs(a.out, exist_ok=True)
    work = tempfile.mkdtemp(prefix='pkg_')
    try:
        if a.target == 'static':
            files, assets = build_static(a, html, work, a.out)
        elif a.target == 'php':
            files, assets = build_php(a, html, schema, work, a.out)
        else:
            files, assets = build_wp(a, html, schema, work, a.out)
    finally:
        shutil.rmtree(work, ignore_errors=True)
    print(assets.report())
    for f in files:
        print(f'OUTPUT {f}  ({os.path.getsize(f) / 1024:.0f} KB)')


if __name__ == '__main__':
    main()
