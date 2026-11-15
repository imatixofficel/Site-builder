#!/usr/bin/env python3
"""Render a CMS template (index.tpl.html + schema.json) to plain HTML using the schema defaults.

Python twin of templates/php-core/inc/engine.php. Use it to
  * turn the same design into a plain static page (single HTML / static host zip) without re-writing it,
  * smoke-test a template (unknown names, unbalanced blocks) before packaging.

  python3 render_preview.py <index.tpl.html> <schema.json> <out.html> [--base .]
"""
import sys, re, json, html as H

TOKEN = re.compile(r'(\{\{[^{}]*\}\})')


def parse(parts, i):
    nodes = []
    last = 'eof'
    while i < len(parts):
        p = parts[i]
        i += 1
        if p == '':
            continue
        if not p.startswith('{{'):
            nodes.append(('text', p))
            continue
        tag = p[2:-2].strip()
        if tag.startswith('#each '):
            body, i, _ = parse(parts, i)
            nodes.append(('each', tag[6:].strip(), body))
        elif tag.startswith('#if '):
            then, i, last = parse(parts, i)
            els = []
            if last == 'else':
                els, i, last = parse(parts, i)
            nodes.append(('if', tag[4:].strip(), then, els))
        elif tag in ('/each', '/if'):
            return nodes, i, 'close'
        elif tag == 'else':
            return nodes, i, 'else'
        else:
            nodes.append(('var', tag))
    return nodes, i, 'eof'


class Engine:
    def __init__(self, schema, base='.'):
        self.schema = schema
        self.base = base.rstrip('/')
        self.types = {s['key']: s.get('type', 'text') for s in schema.get('settings', [])}
        self.vals = {s['key']: str(s.get('default', '')) for s in schema.get('settings', [])}
        self.colls = {c['key']: c for c in schema.get('collections', [])}
        site = schema.get('site', {})
        lang = site.get('lang', 'fa')
        self.builtin = {'site_name': site.get('name', ''), 'site_description': site.get('description', ''), 'site_lang': lang,
                        'site_dir': site.get('dir', 'rtl' if lang == 'fa' else 'ltr'), 'year': '2026', 'base': self.base, 'home': './',
                        'contact_url': '#contact', 'flash': '', 'csrf': ''}
        self.raw = {'form_fields': ''}

    def items(self, coll):
        c = self.colls.get(coll)
        return [dict(i) for i in c.get('items', [])] if c else []

    def lookup(self, key, ctx):
        if key in ctx:
            t = 'text'
            if '__coll' in ctx:
                for f in self.colls[ctx['__coll']]['fields']:
                    if f['key'] == key:
                        t = f.get('type', 'text')
            return str(ctx[key]), t
        if key in self.types:
            return self.vals[key], self.types[key]
        return str(self.builtin.get(key, '')), 'text'

    def truthy(self, key, ctx):
        if key not in ctx and key in self.colls:
            return len(self.items(key)) > 0
        v, _ = self.lookup(key, ctx)
        return v not in ('', '0')

    def render_nodes(self, nodes, ctx):
        out = []
        for n in nodes:
            if n[0] == 'text':
                out.append(n[1])
            elif n[0] == 'var':
                out.append(self.var(n[1], ctx))
            elif n[0] == 'if':
                out.append(self.render_nodes(n[2] if self.truthy(n[1], ctx) else n[3], ctx))
            elif n[0] == 'each':
                items = self.items(n[1])
                for k, it in enumerate(items, 1):
                    c = dict(it)
                    c.update({'@index': str(k), '@first': '1' if k == 1 else '', '@last': '1' if k == len(items) else '', '__coll': n[1]})
                    out.append(self.render_nodes(n[2], c))
        return ''.join(out)

    def var(self, tag, ctx):
        flt = ''
        if '|' in tag:
            tag, flt = [x.strip() for x in tag.split('|', 1)]
        key = tag.strip()
        if key in self.raw:
            return self.raw[key]
        val, t = self.lookup(key, ctx)
        if t == 'image':
            val = val if re.match(r'^https?://', val) or val.startswith('/') or not val else f'{self.base}/{val}'
        elif t == 'url':
            if re.match(r'^[a-z][a-z0-9+.-]*:', val, re.I) and not re.match(r'^(https?:|mailto:|tel:)', val, re.I):
                val = ''
        elif t == 'color':
            val = val if re.match(r'^#[0-9a-fA-F]{3,8}$', val) else ''
        esc = H.escape(val, quote=True)
        return esc.replace('\n', '<br>\n') if flt == 'br' else esc

    def render(self, tpl):
        nodes, _, _ = parse(TOKEN.split(tpl), 0)
        return self.render_nodes(nodes, {})


if __name__ == '__main__':
    if len(sys.argv) < 4:
        sys.exit(__doc__)
    base = sys.argv[sys.argv.index('--base') + 1] if '--base' in sys.argv else '.'
    tpl = open(sys.argv[1], encoding='utf-8').read()
    schema = json.load(open(sys.argv[2], encoding='utf-8'))
    open(sys.argv[3], 'w', encoding='utf-8').write(Engine(schema, base).render(tpl))
    print('rendered', sys.argv[3])
