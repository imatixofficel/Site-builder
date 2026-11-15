#!/usr/bin/env python3
"""Scan image/ and fonts/ folders ONCE and build a contact sheet (saves credits).

Usage: python3 scan_assets.py <folder> [<folder> ...]
Prints a compact JSON report and writes contact sheets to /home/claude/contact_N.jpg
(max 16 thumbnails per sheet, each labelled with its index).
"""
import sys, os, json
from PIL import Image, ImageDraw

IMG = {'.png', '.jpg', '.jpeg', '.webp', '.gif', '.bmp'}
FONT = {'.ttf', '.otf', '.woff', '.woff2'}


def dominant(im, n=4):
    small = im.convert('RGB').resize((64, 64))
    q = small.quantize(colors=n, method=Image.Quantize.MEDIANCUT)
    pal = q.getpalette()[:n * 3]
    cols = sorted(q.getcolors(), reverse=True)
    return ['#%02x%02x%02x' % tuple(pal[i * 3:i * 3 + 3]) for _, i in cols]


def main(folders):
    images, fonts = [], []
    for root in folders:
        for d, _, files in os.walk(root):
            for f in sorted(files):
                p = os.path.join(d, f)
                ext = os.path.splitext(f)[1].lower()
                if ext in IMG:
                    try:
                        with Image.open(p) as im:
                            im.load()
                            images.append({
                                'i': len(images), 'path': p, 'w': im.width, 'h': im.height,
                                'kb': os.path.getsize(p) // 1024, 'colors': dominant(im)})
                    except Exception as e:
                        images.append({'i': len(images), 'path': p, 'error': str(e)})
                elif ext in FONT:
                    fonts.append({'path': p, 'name': os.path.splitext(f)[0],
                                  'kb': os.path.getsize(p) // 1024})
    sheets = []
    ok = [x for x in images if 'error' not in x]
    for s in range(0, len(ok), 16):
        chunk = ok[s:s + 16]
        cols = 4
        rows = (len(chunk) + cols - 1) // cols
        W, H = 320, 240
        sheet = Image.new('RGB', (cols * W, rows * H), (30, 30, 30))
        dr = ImageDraw.Draw(sheet)
        for k, it in enumerate(chunk):
            with Image.open(it['path']) as im:
                im = im.convert('RGB')
                im.thumbnail((W - 8, H - 8))
                x, y = (k % cols) * W, (k // cols) * H
                sheet.paste(im, (x + 4, y + 4))
                dr.rectangle([x + 4, y + 4, x + 40, y + 20], fill=(0, 0, 0))
                dr.text((x + 8, y + 6), str(it['i']), fill=(255, 255, 0))
        out = f'/home/claude/contact_{s // 16 + 1}.jpg'
        sheet.save(out, quality=80)
        sheets.append(out)
    print(json.dumps({'image_count': len(images), 'font_count': len(fonts),
                      'images': images, 'fonts': fonts, 'contact_sheets': sheets},
                     ensure_ascii=False, indent=1))


if __name__ == '__main__':
    if len(sys.argv) < 2:
        sys.exit(__doc__)
    main([f for f in sys.argv[1:] if os.path.isdir(f)])
