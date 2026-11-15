#!/usr/bin/env python3
"""Compact overview of the fonts folder (the user may have hundreds of font files).

Usage: python3 list_fonts.py <fonts_dir> [--family <name-part>]   (--family prints every path of that family)
Groups files by family (top-level folder) and variant, and shows only the best format per weight
(woff2 > woff > ttf > otf). eot/svg are ignored (old browsers only).

Variants in Persian font packs (Vazir, Sahel, Samim, Shabnam, Tanha, Parastoo...):
  (plain)               Persian + Latin glyphs, Latin digits (0-9) everywhere
  Farsi-Digits (FD)     digits shown as Persian digits (۰-۹): best for Persian sites
  Without-Latin (WOL)   Persian glyphs only (lighter): pair with a Latin font for English text
  Farsi-Digits-Without-Latin  both of the above
"""
import sys, os, re, json
FMT = ['woff2', 'woff', 'ttf', 'otf']
WEIGHTS = ['Thin', 'ExtraLight', 'Light', 'Regular', 'Medium', 'SemiBold', 'Bold', 'ExtraBold', 'Black']


def weight_of(stem):
    for w in sorted(WEIGHTS, key=len, reverse=True):
        if w.lower() in stem.lower():
            return w
    return 'Regular'


def variant_of(rel):
    parts = [p.lower() for p in rel.split(os.sep)]
    fd = any('farsi-digits' in p for p in parts)
    wol = any('without-latin' in p for p in parts)
    return {(0, 0): 'plain', (1, 0): 'Farsi-Digits', (0, 1): 'Without-Latin', (1, 1): 'Farsi-Digits-Without-Latin'}[(int(fd), int(wol))]


def main(root, only=None):
    fam = {}
    for d, _, files in os.walk(root):
        for f in files:
            ext = f.rsplit('.', 1)[-1].lower()
            if ext not in FMT:
                continue
            rel = os.path.relpath(os.path.join(d, f), root)
            top = rel.split(os.sep)[0] if os.sep in rel else os.path.splitext(f)[0]
            stem = os.path.splitext(f)[0]
            key = (variant_of(os.path.dirname(rel)), weight_of(stem))
            fam.setdefault(top, {}).setdefault(key, {}).setdefault(stem, {})[ext] = rel
    for top in sorted(fam):
        if only and only.lower() not in top.lower():
            continue
        groups = fam[top]
        n = sum(len(v) for v in groups.values())
        if not only and n > 40:          # big decorative pack: names only
            names = sorted({st for v in groups.values() for st in v})
            print(f'FAMILY {top}: {n} display fonts (use --family "{top}" for paths). Names: ' + ', '.join(names[:12]) + ' ...')
            continue
        print(f'FAMILY {top}')
        byvar = {}
        for (variant, weight), stems in sorted(groups.items(), key=lambda kv: (kv[0][0], WEIGHTS.index(kv[0][1]))):
            for stem, fm in sorted(stems.items()):
                best = next(e for e in FMT if e in fm)
                byvar.setdefault(variant, []).append((weight, best, 'fonts/' + fm[best].replace(os.sep, '/')))
        for variant, items in byvar.items():
            fmts = sorted({b for _, b, _ in items})
            print(f'  [{variant}] weights: ' + ', '.join(w for w, _, _ in items) + f'  formats: {"/".join(fmts)}')
            if only:
                for w, b, path in items:
                    print(f'      {w:<10} {path}')
            else:
                print(f'      e.g. {items[0][2]}')


if __name__ == '__main__':
    if len(sys.argv) < 2:
        sys.exit(__doc__)
    fam = None
    if '--family' in sys.argv:
        fam = sys.argv[sys.argv.index('--family') + 1]
    main(sys.argv[1], fam)
