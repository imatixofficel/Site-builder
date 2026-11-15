#!/usr/bin/env python3
"""Turn a built HTML page (that points at image/... and fonts/... by relative path)
into a READY-TO-OPEN result. Optimizes images and embeds everything, so Claude never
has to paste huge base64 text into its own output (saves credits).

Usage:
  python3 inline_assets.py <in.html> <out_path> --roots <dir> [<dir> ...]
         [--mode single|folder] [--maxw 1600] [--quality 82]

--mode single (default): writes ONE self-contained .html file (<out_path>) with
                         images + fonts embedded.
--mode folder: writes <out_path>/index.html + <out_path>/assets/ (use when the single
               file would exceed ~8 MB).
Prints a short report: what was embedded, final size, and any missing files.
"""
import sys, os, re, io, base64, argparse
from PIL import Image

MIME = {'.woff2': 'font/woff2', '.woff': 'font/woff', '.ttf': 'font/ttf', '.otf': 'font/otf',
        '.svg': 'image/svg+xml', '.gif': 'image/gif', '.ico': 'image/x-icon'}
RASTER = {'.png', '.jpg', '.jpeg', '.webp', '.bmp'}


def find(ref, roots):
    ref = ref.split('?')[0].split('#')[0]
    for r in roots:
        for cand in (os.path.join(r, ref), os.path.join(os.path.dirname(r), ref)):
            if os.path.isfile(cand):
                return cand
    base = os.path.basename(ref)
    for r in roots:
        for d, _, files in os.walk(r):
            if base in files:
                return os.path.join(d, base)
    return None


def optimize(path, maxw, q):
    with Image.open(path) as im:
        im.load()
        if im.width > maxw:
            im = im.resize((maxw, round(im.height * maxw / im.width)), Image.LANCZOS)
        has_alpha = im.mode in ('RGBA', 'LA') or (im.mode == 'P' and 'transparency' in im.info)
        im = im.convert('RGBA' if has_alpha else 'RGB')
        buf = io.BytesIO()
        im.save(buf, 'WEBP', quality=q, method=6)
        return buf.getvalue(), 'image/webp', '.webp'


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('html')
    ap.add_argument('out')
    ap.add_argument('--roots', nargs='+', required=True)
    ap.add_argument('--mode', default='single', choices=['single', 'folder'])
    ap.add_argument('--maxw', type=int, default=1600)
    ap.add_argument('--quality', type=int, default=82)
    a = ap.parse_args()
    html = open(a.html, encoding='utf-8').read()
    cache, missing, embedded = {}, [], []
    assets_dir = os.path.join(a.out, 'assets') if a.mode == 'folder' else None
    if assets_dir:
        os.makedirs(assets_dir, exist_ok=True)

    def convert(ref):
        if re.match(r'^(https?:|data:|#|mailto:|tel:|//)', ref):
            return None
        if ref in cache:
            return cache[ref]
        p = find(ref, a.roots)
        if not p:
            missing.append(ref)
            cache[ref] = None
            return None
        ext = os.path.splitext(p)[1].lower()
        try:
            if ext in RASTER:
                data, mime, newext = optimize(p, a.maxw, a.quality)
            else:
                data = open(p, 'rb').read()
                mime, newext = MIME.get(ext, 'application/octet-stream'), ext
        except Exception as e:
            missing.append(f'{ref} ({e})')
            cache[ref] = None
            return None
        if assets_dir:
            name = re.sub(r'[^A-Za-z0-9._-]', '_', os.path.splitext(os.path.basename(p))[0]) + newext
            open(os.path.join(assets_dir, name), 'wb').write(data)
            res = 'assets/' + name
        else:
            res = f'data:{mime};base64,' + base64.b64encode(data).decode()
        cache[ref] = res
        embedded.append((ref, len(data) // 1024))
        return res

    def sub_attr(m):
        new = convert(m.group(3))
        return f'{m.group(1)}={m.group(2)}{new}{m.group(2)}' if new else m.group(0)

    def sub_url(m):
        new = convert(m.group(2))
        return f'url({m.group(1)}{new}{m.group(1)})' if new else m.group(0)

    html = re.sub(r'\b(src|href|poster|data-src)=(["\'])([^"\']+)\2', sub_attr, html)
    html = re.sub(r'url\((["\']?)([^"\')]+)\1\)', sub_url, html)
    if a.mode == 'single':
        os.makedirs(os.path.dirname(os.path.abspath(a.out)), exist_ok=True)
        open(a.out, 'w', encoding='utf-8').write(html)
        size = os.path.getsize(a.out)
    else:
        open(os.path.join(a.out, 'index.html'), 'w', encoding='utf-8').write(html)
        size = sum(os.path.getsize(os.path.join(d, f)) for d, _, fs in os.walk(a.out) for f in fs)
    print(f'embedded/converted: {len(embedded)} files; final size: {size / 1024 / 1024:.2f} MB')
    for r, kb in embedded:
        print(f'  ok  {r}  ({kb} KB)')
    for r in missing:
        print(f'  MISSING  {r}')
    if a.mode == 'single' and size > 8 * 1024 * 1024:
        print('WARNING: file is large (>8 MB). Re-run with --mode folder or lower --maxw/--quality.')


if __name__ == '__main__':
    main()
