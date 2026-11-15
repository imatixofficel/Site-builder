#!/usr/bin/env python3
"""Optimize and embed/copy the images and fonts that a built page references.

Library use (package_site.py imports this):  Assets(roots, mode, out_dir, ...).rewrite_html(html, prefix)
CLI use (single self-contained HTML file or a folder):
  python3 inline_assets.py <in.html> <out_path> --roots <dir> [<dir> ...]
         [--mode single|folder] [--maxw 1600] [--quality 82] [--prefix assets/]

single : writes ONE .html file with images + fonts embedded as data URIs (opens by double-click).
folder : writes <out_path>/index.html + <out_path>/assets/ (use for hosting, or if single would exceed ~8 MB).
Claude writes plain relative paths (image/x.jpg, fonts/<family>/<file>.woff2) and this script does the
compression/embedding, so no base64 ever passes through the chat (saves credits).
"""
import sys, os, re, io, base64, argparse
from PIL import Image

MIME = {'.woff2': 'font/woff2', '.woff': 'font/woff', '.ttf': 'font/ttf', '.otf': 'font/otf',
        '.svg': 'image/svg+xml', '.gif': 'image/gif', '.ico': 'image/x-icon'}
RASTER = {'.png', '.jpg', '.jpeg', '.webp', '.bmp'}
SKIP = re.compile(r'^(https?:|data:|#|mailto:|tel:|//|javascript:|\{\{)', re.I)


class Assets:
    def __init__(self, roots, mode='single', out_dir=None, maxw=1600, quality=82):
        self.roots = [r for r in roots if os.path.isdir(r)]
        self.mode = mode
        self.out_dir = out_dir
        self.maxw = maxw
        self.quality = quality
        self.cache = {}      # (ref, prefix) -> new url or None
        self.names = {}      # dest file name -> source path
        self.missing = []
        self.done = []       # (ref, KB)
        if mode == 'folder':
            os.makedirs(os.path.join(out_dir, 'assets'), exist_ok=True)

    def find(self, ref):
        ref = ref.split('?')[0].split('#')[0]
        for r in self.roots:
            for cand in (os.path.join(r, ref), os.path.join(os.path.dirname(r), ref)):
                if os.path.isfile(cand):
                    return cand
        base = os.path.basename(ref)
        for r in self.roots:
            for d, _, files in os.walk(r):
                if base in files:
                    return os.path.join(d, base)
        return None

    def _optimize(self, path):
        with Image.open(path) as im:
            im.load()
            if im.width > self.maxw:
                im = im.resize((self.maxw, round(im.height * self.maxw / im.width)), Image.LANCZOS)
            alpha = im.mode in ('RGBA', 'LA') or (im.mode == 'P' and 'transparency' in im.info)
            im = im.convert('RGBA' if alpha else 'RGB')
            buf = io.BytesIO()
            im.save(buf, 'WEBP', quality=self.quality, method=6)
            return buf.getvalue(), 'image/webp', '.webp'

    def _unique(self, stem, ext, src):
        name = re.sub(r'[^A-Za-z0-9._-]', '_', stem) + ext
        k = 1
        while name in self.names and self.names[name] != src:
            k += 1
            name = re.sub(r'[^A-Za-z0-9._-]', '_', stem) + f'-{k}' + ext
        self.names[name] = src
        return name

    def convert(self, ref, prefix='assets/'):
        if SKIP.match(ref) or not ref.strip():
            return None
        key = (ref, prefix)
        if key in self.cache:
            return self.cache[key]
        p = self.find(ref)
        if not p:
            self.missing.append(ref)
            self.cache[key] = None
            return None
        ext = os.path.splitext(p)[1].lower()
        try:
            if ext in RASTER:
                data, mime, newext = self._optimize(p)
            else:
                data, mime, newext = open(p, 'rb').read(), MIME.get(ext, 'application/octet-stream'), ext
        except Exception as e:
            self.missing.append(f'{ref} ({e})')
            self.cache[key] = None
            return None
        if self.mode == 'folder':
            name = self._unique(os.path.splitext(os.path.basename(p))[0], newext, p)
            with open(os.path.join(self.out_dir, 'assets', name), 'wb') as f:
                f.write(data)
            res = prefix + name
        else:
            res = f'data:{mime};base64,' + base64.b64encode(data).decode()
        self.cache[key] = res
        self.done.append((ref, len(data) // 1024))
        return res

    def rewrite_html(self, html, prefix='assets/'):
        def attr(m):
            new = self.convert(m.group(3), prefix)
            return f'{m.group(1)}={m.group(2)}{new}{m.group(2)}' if new else m.group(0)

        def url(m):
            new = self.convert(m.group(2), prefix)
            return f'url({m.group(1)}{new}{m.group(1)})' if new else m.group(0)

        html = re.sub(r'\b(src|href|poster|data-src)=(["\'])([^"\']+)\2', attr, html)
        html = re.sub(r'url\((["\']?)([^"\')]+)\1\)', url, html)
        return html

    def report(self):
        lines = [f'converted: {len(self.done)} files']
        lines += [f'  ok       {r} ({kb} KB)' for r, kb in self.done]
        lines += [f'  MISSING  {r}' for r in self.missing]
        return '\n'.join(lines)


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('html')
    ap.add_argument('out')
    ap.add_argument('--roots', nargs='+', required=True)
    ap.add_argument('--mode', default='single', choices=['single', 'folder'])
    ap.add_argument('--maxw', type=int, default=1600)
    ap.add_argument('--quality', type=int, default=82)
    ap.add_argument('--prefix', default='assets/')
    a = ap.parse_args()
    html = open(a.html, encoding='utf-8').read()
    out_dir = a.out if a.mode == 'folder' else None
    assets = Assets(a.roots, a.mode, out_dir, a.maxw, a.quality)
    html = assets.rewrite_html(html, a.prefix)
    if a.mode == 'single':
        os.makedirs(os.path.dirname(os.path.abspath(a.out)), exist_ok=True)
        open(a.out, 'w', encoding='utf-8').write(html)
        size = os.path.getsize(a.out)
    else:
        open(os.path.join(a.out, 'index.html'), 'w', encoding='utf-8').write(html)
        size = sum(os.path.getsize(os.path.join(d, f)) for d, _, fs in os.walk(a.out) for f in fs)
    print(assets.report())
    print(f'final size: {size / 1024 / 1024:.2f} MB')
    if a.mode == 'single' and size > 8 * 1024 * 1024:
        print('WARNING: file is large (>8 MB). Re-run with --mode folder or lower --maxw/--quality.')


if __name__ == '__main__':
    main()
