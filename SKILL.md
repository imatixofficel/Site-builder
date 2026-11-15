---
name: site-builder
description: Builds complete, secure, ready-to-use websites (landing pages, shops, portfolios, company, restaurant, clinic, education, event, personal sites) in the style of the reference images in this skill's image/ folder, using its photos and fonts, via a short question flow (work style, colors, font, site name, logo, delivery type, build, improvements). Delivers a double-click HTML file, a host-ready zip, a PHP site with backend + admin panel + browser installer for cPanel/DirectAdmin hosts, or a WordPress theme + security plugin, all hardened (security headers, .htaccess, login limits, CSRF, safe uploads, 2FA) with a Persian install guide. Use whenever the user asks to build, design, redo, secure or host a website, landing page, online store, portfolio, admin panel or WordPress theme, in any language incl. Persian (ساخت سایت، وب‌سایت، پنل مدیریت، بک‌اند، وردپرس، آپلود روی هاست، امنیت سایت), even without mentioning images, fonts or this skill. Low credit use: scripts do scanning and packaging.
---

# Site Builder (سایت‌ساز)

Build an excellent, secure website that looks like the reference images in the skill folder, uses the images and fonts that ship with the skill, and is delivered in the form the user can actually put on a host. Spend as few credits as possible while keeping professional quality.

## 0. Ground rules (read first, apply always)

1. **Language.** Reply in the user's language. For Persian users: speak Persian, write the site copy in Persian, build RTL (`lang="fa" dir="rtl"`). Brand names/numbers stay LTR inside RTL text.
2. **Assume no coding knowledge.** Never ask technical questions, never paste code into the chat, never explain HTML/PHP. Deliver files the user can double-click or upload.
3. **One question at a time.** Short, friendly, concrete. When a tappable-options tool such as `ask_user_input_v0` is available, use it for multiple-choice questions (2-4 short options). Steps 4 and 5 may share one message.
4. **Never invent facts.** No fake phone numbers, addresses, prices, reviews, statistics, awards. Use empty defaults wrapped in `{{#if}}` (CMS) or clearly marked placeholders such as `[شماره تماس]` (plain HTML) and ask for real values in Step 8.
5. **Follow the folders, not generic taste.** The look comes from the reference images in `image/`.
6. **Security is a requirement, not a feature.** Every delivered site gets the protections listed in `references/security.md`. Never weaken them to make something easier (no inline handlers, no external scripts in CMS targets, no skipping the install key, no world-writable permissions advice). Never ask for hosting passwords/FTP.
7. **Be honest about limits.** Say plainly what a package does not cover (no DDoS firewall, no payment gateway in the PHP package, backups are the owner's job). The PHP/WordPress code could not be executed in the build sandbox: if the user reports an error, ask for the text of `data/error.log` or a screenshot and fix only the affected file.
8. **Credit saving is a feature.** Follow "Credit-saving rules" strictly.

## Folder layout of this skill

```
site-builder/
├── SKILL.md
├── README.md             <- Persian usage notes for the owner
├── image/                <- style references AND photos to use (any subfolders)
├── fonts/                <- font families (woff2/woff/ttf/otf; Persian packs with Farsi-Digits / Without-Latin variants)
├── references/
│   ├── questions.md      <- exact question wording + options per step (Persian + English)
│   ├── color-palettes.md <- palettes by business field
│   ├── site-types.md     <- section blueprint + admin lists per site type
│   ├── quality-checklist.md
│   ├── cms-schema.md     <- template + schema.json spec for PHP/WordPress targets (read before writing one)
│   └── security.md       <- what each target protects, owner checklist, honesty list, recovery recipes
├── templates/            <- ready-made, security-hardened engines (do not rewrite them)
│   ├── php-core/         <- PHP site: engine, installer, admin panel, login+2FA, uploads, contact inbox, .htaccess
│   ├── wordpress/        <- theme engine (Customizer + post types + contact form) + SB Security plugin + hardening snippets
│   ├── static/.htaccess  <- hardened rules for plain HTML hosting
│   └── guides/           <- Persian install guides (filled by the packager)
└── scripts/
    ├── scan_assets.py    <- scan image/ once, contact sheet + dominant colors (fonts only counted)
    ├── list_fonts.py     <- compact font family / weight / variant overview
    ├── inline_assets.py  <- optimize + embed/copy images and fonts (single HTML or folder)
    ├── render_preview.py <- render template+schema defaults to plain HTML (static targets, smoke test)
    └── package_site.py   <- validate + build the zips for static / php / wordpress
```

Files uploaded in chat (`/mnt/user-data/uploads`) count as extra assets, same rules as `image/` and `fonts/`.

## The flow at a glance

| Step | What happens | Who acts |
|------|--------------|----------|
| prep | Silent one-time scan of `image/` and `fonts/` | Claude |
| 1 | Ask the customer's **work style** (field + look) | Ask user |
| 2 | **Pick colors** from the field and the references | Claude (no question) |
| 3 | Learn fonts from folders/images; **ask which font** | Ask user |
| 4 | Ask the **site name** | Ask user |
| 5 | Ask for the **logo**; none means no logo | Ask user |
| 6 | Ask the **delivery type** (file / host zip / PHP + panel / WordPress) | Ask user |
| 7 | **Build** and package | Claude |
| 8 | Improvement questions, then one change at a time | Claude + user |

Never ask something the user already told you; extract answers from their first message and confirm only what is missing. Skip Step 6 when the user already said it (host, admin panel, backend, WordPress, "just a file").

## Prep - scan the folders once (silent)

1. Find this skill's folder (the one containing this `SKILL.md`) and run once:
   `python3 <skill>/scripts/scan_assets.py <skill>/image <skill>/fonts [/mnt/user-data/uploads]`
   `python3 <skill>/scripts/list_fonts.py <skill>/fonts`
2. `view` the contact sheet(s) printed by the scan (`/home/claude/contact_N.jpg`). **Do not open images one by one.**
3. Sort every image by role (by looking): **style reference** (UI/web/app/poster screenshots), **site photo** (photos, products, illustrations, backgrounds), **logo** (only the customer's logo if the user says so).
4. Write a short **Design Brief** for yourself: layout pattern (hero type, grid, cards), corner radius, shadow or glass/blur, borders, spacing density, image treatment, motion hints, mood; palette seen (dominant colors); type feel (serif/sans/display, weight, contrast); asset manifest (file -> role -> use).
5. If both folders are empty and nothing was uploaded, say so in one sentence and continue with the field-based palettes and `references/site-types.md`. Only then skim `frontend-design` if available.

## Step 1 - Work style (ask)

Ask what the customer does and what look they want (wording in `references/questions.md`). Field: shop, restaurant/cafe, clinic, portfolio, company, education, personal brand, blog/news, event, app landing, other. Look: 3 options, **option 1 = the style learned from the reference images** (plain words), plus two contrasting options suited to the field. If the first message already answers both, state your understanding in one line instead of asking.

## Step 2 - Colors (no question)

1. Start from the field in `references/color-palettes.md`.
2. Blend with the reference colors: references decide mood, the field decides meaning.
3. Fix background, surface, text, muted, primary, accent (+ success/danger if needed) as CSS variables. Text contrast >= 4.5:1 (large text 3:1).
4. Announce in one line with hex codes and the reason. The user can change it in Step 8.

## Step 3 - Fonts (learn, then ask)

1. Read the `list_fonts.py` overview. Persian packs come in variants: plain = Persian+Latin glyphs with Latin digits; `Farsi-Digits` (FD) = Persian digits everywhere (best default for Persian sites); `Without-Latin` (WOL) = Persian glyphs only (lighter, pair with a Latin font for English words). Prefer woff2, then woff, ttf. Ignore eot/svg. Large decorative packs (for example "other font") are for headlines only; show names only if the user asks.
2. Guess the typeface seen in the style references ("شبیه ... است", never certain) and map it to the closest available family.
3. **Ask which font the user wants**: 3-4 options: the one matching the references, the best family from `fonts/`, one more suited to the field, "فونت دیگر / فونت خودم".
4. Use at most 2 families (headline + body) and 2-3 weights (Regular/Medium/Bold). Declare with `@font-face` and the **relative path of the file** from `fonts/` (get exact paths with `list_fonts.py <skill>/fonts --family <name>`):
   `@font-face{font-family:'Vazir';src:url('fonts/vazir-font-v18.0.0/Farsi-Digits/Vazir-FD.woff2') format('woff2');font-weight:400;font-display:swap}`
   Always add a fallback stack (`'Vazir', Tahoma, system-ui, sans-serif`). The packager copies only the files actually referenced.
5. If the chosen font is not bundled, use a Google Fonts `<link>` (needs internet; tell the user) and the same fallback stack. Remind the user to check licenses for fonts they bring.

## Step 4 - Site name (ask) and Step 5 - Logo (ask)

Ask both in one message (see `questions.md`). Use the exact name everywhere (title, header, footer, meta). Logo given: header + footer + favicon if square-ish; do not repaint it. No logo: **build without one, never invent or draw a logo**; the header shows the site name as text.

## Step 6 - Delivery type (ask)

Offer these (tappable options when available), recommending by need:
1. **یک فایل HTML** - opens by double-click, nothing to upload.
2. **زیپ آماده آپلود روی هاست (HTML ساده)** - fast static site with hardened `.htaccess`; no panel, forms cannot work.
3. **سایت PHP با بک‌اند و پنل مدیریت** - runs on any normal cPanel/DirectAdmin host (PHP 7.4+), browser installer, admin panel for texts/colors/images/lists, contact inbox, login with limits and optional 2FA. Recommend when the owner wants to edit content themselves or needs a contact form.
4. **قالب وردپرس + افزونه امنیتی** - for a host that already runs (or will run) WordPress; editing through Customizer and the WordPress menu; best for blogs, big content sites, and shops with WooCommerce.
If the user is unsure: recommend option 3 for business sites with a contact form and option 4 for blogs/shops with online payment.

## Step 7 - Build and package

Always build in **one pass**, then package with the scripts. `<skill>` = this skill's folder. Work in `/home/claude/build/`.

### 7a. Single HTML file (target 1)
Write one `index.html` (CSS/JS inline, no frameworks). Assets by relative path (`image/x.jpg`, `fonts/<family>/<file>`). Then:
`python3 <skill>/scripts/inline_assets.py /home/claude/build/index.html /mnt/user-data/outputs/<site-name>.html --roots <skill>/image <skill>/fonts /mnt/user-data/uploads`
Read the report, fix every `MISSING`. Over ~8 MB: use `--mode folder`.

### 7b. Static host zip (target 2)
Write the plain `index.html` as above (contact: phone/WhatsApp/Telegram/mailto buttons, no `<form>`), then:
`python3 <skill>/scripts/package_site.py --target static --name "<Site Name>" --html /home/claude/build/index.html --roots <skill>/image <skill>/fonts /mnt/user-data/uploads --out /mnt/user-data/outputs`
Outputs: `<slug>-static-host.zip` + `<slug>-static-GUIDE-FA.txt`.

### 7c. PHP site with backend and admin panel (target 3) and 7d. WordPress (target 4)
1. **Read `references/cms-schema.md` once** (template syntax, schema, CSP-safe rules). Do not read the PHP templates; they are finished and tested by the build scripts.
2. Write `/home/claude/build/index.tpl.html` (the design, with `{{ }}` tags) and `/home/claude/build/schema.json` (editable settings + lists + default content + `contact_form`). Everything the owner may change must be editable; defaults make the site look complete immediately.
3. Run the packager (it validates first; fix reported errors with tiny `str_replace` edits and rerun):
   `python3 <skill>/scripts/package_site.py --target php --name "<Site Name>" --html /home/claude/build/index.tpl.html --schema /home/claude/build/schema.json --roots <skill>/image <skill>/fonts /mnt/user-data/uploads --out /mnt/user-data/outputs`
   (`--target wordpress` for WordPress.)
4. Outputs: PHP -> `<slug>-php-host.zip` + `<slug>-php-GUIDE-FA.txt` (contains the one-time install key; tell the user to keep it on their computer and never upload it). WordPress -> `<slug>-wp-theme.zip`, `<slug>-sb-security.zip`, `<slug>-wp-hardening.txt`, `<slug>-wp-GUIDE-FA.txt`.
5. A plain static version of the same design is free: `python3 <skill>/scripts/render_preview.py index.tpl.html schema.json /home/claude/build/index.html` then 7b (replace the form with contact buttons).
6. Shop sites: PHP package = catalog (products list + "order by phone/WhatsApp/Telegram/contact form"); say clearly it has no online payment. For payment use WordPress + WooCommerce (the theme is WooCommerce-ready) with a trusted gateway plugin.

### Design rules for every build
- Match the Design Brief (radius, glass/shadow, spacing, card patterns, image treatment, mood). Use CSS variables for palette and type scale.
- Use the photos from `image/` (hero, cards, galleries, banners) matched to their meaning; `object-fit: cover`, sensible `object-position`, real `alt`, `loading="lazy"` below the fold. Never remote/stock images.
- Real persuasive copy in the user's language; no lorem ipsum.
- Quality from the start (`references/quality-checklist.md`): mobile-first responsive, RTL-correct, clear navigation, focus/hover states, subtle reveal animations that respect `prefers-reduced-motion`, one call-to-action per section, semantic HTML, SEO meta tags, small vanilla JS only.

### Delivery message (3-5 lines, Persian for Persian users)
Use `present_files` for every output file. Say: what was built, the style/palette/font used, where to upload (public_html / WordPress themes), and that the step-by-step Persian guide is in the GUIDE file. For PHP: the install key is in the guide, open `/install.php`, then `/admin/`. Remind the three security must-dos: SSL + HTTPS redirect, strong admin password + 2FA, backups.

## Step 8 - Improvement questions, then one change at a time

Ask up to 3 short questions at once: real content (phone, address, hours, social links, services/prices), sections to add/remove, look changes, the single main goal (call, order, book, message), languages, and security extras (behind Cloudflare? SSL ready?). Then apply **one change at a time**: make the single change, say it is done, hand back only the files that changed, wait for the next request.

## Editing a site delivered earlier

- Same session: edit `/home/claude/build/index.tpl.html` / `schema.json` (or `index.html`) with small `str_replace` edits and rerun the packager; deliver only the files that changed (for the PHP package the changed zip is still the unit the user uploads; say which files inside changed).
- New session: ask the user to upload the previous zip, extract it, edit the changed files in place (`theme/index.tpl.html` and `theme/schema.json` for PHP; `index.tpl.html` and `schema.json` inside the theme folder for WordPress) with `str_replace`, and re-zip. Do not rerun the asset scan unless new files appeared.
- When the owner already has live data (PHP site installed), tell them: upload only the changed files; texts they edited in the panel are stored in the database and are not overwritten. Schema changes (new editable fields or lists) appear automatically; removed fields simply stop showing.

## Credit-saving rules (strict)

1. Scan assets **once** (scripts), look at the contact sheet only; never list hundreds of font files.
2. Short Design Brief, reused; no re-analysis each turn.
3. Build the whole page/template **once**; no draft-then-rewrite, no polish passes, no read-back.
4. Reference assets by relative path; scripts embed/copy/compress. Never output base64 or large data.
5. Never re-read or re-write the ready-made PHP/WordPress templates; the packager copies them.
6. Edits are tiny `str_replace` calls; rerun the packager.
7. No screenshot loops unless the user asks; the validator/packager reports are the check.
8. Batch questions sensibly, never repeat them, keep replies short.
9. No package installs or web browsing unless truly needed.

## What a great result looks like

- Recognizably in the style of the references, with the user's font, palette and photos visibly used.
- Opens by double-click or installs in minutes on a normal host; Persian text aligned and shaped correctly; phone and laptop both look right.
- The owner can change texts, colors, images and lists without code (PHP panel or WordPress).
- Security protections are active by default and the owner knows the three things they must still do.
- Placeholders are listed back to the user; every output file is presented; the user can say "change X" and receive only X.
