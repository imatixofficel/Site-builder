---
name: site-builder
description: Builds complete, ready-to-open websites (landing pages, online shops, portfolios, company, restaurant, clinic, education, event and personal sites) by learning the visual style from the reference images in this skill's `image/` folder, reusing those images and the fonts from `fonts/` inside the site, and guiding the user through a short question flow (work style, colors, font, site name, logo, build, improvement questions). Use this skill whenever the user asks to build, design, redo, restyle or improve a website, landing page, web page, online store or portfolio, in any language, including Persian (ساخت سایت، وب‌سایت، صفحه فرود، فروشگاه آنلاین، پورتفولیو، طراحی سایت), even if they never mention images, fonts or this skill. Optimized for low credit use - one asset scan, one build call, tiny edits afterwards.
---

# Site Builder (سایت‌ساز)

Build an excellent, ready-to-open website that looks like the reference images in the skill folder, uses the images and fonts that come with the skill, and is guided by a short question flow. Spend as few credits as possible while keeping the result at professional quality.

## 0. Ground rules (read first, apply always)

1. **Language.** Reply in the user's language. If the user writes Persian, speak Persian, write the site copy in Persian, and build the site RTL (`<html lang="fa" dir="rtl">`). Mixed content (English brand names, numbers) stays LTR inside RTL text.
2. **Assume no coding knowledge.** Never ask technical questions, never paste code into the chat, never explain HTML. Deliver a file the user can double-click and open.
3. **One question at a time.** Short, friendly, concrete. When a tappable-options tool such as `ask_user_input_v0` is available, use it for multiple-choice questions (2-4 short options). Steps 4 and 5 may share one message because both are free-text/file answers.
4. **Never invent facts.** No fake phone numbers, addresses, prices, reviews, statistics or awards. Use clearly marked placeholders (for example `[شماره تماس]`) and ask for the real values in Step 6.
5. **Follow the folders, not generic taste.** The look of the site comes from the reference images in `image/`. Only fall back to your own taste where the references say nothing.
6. **Credit saving is a feature.** Follow section "Credit-saving rules" strictly.

## Folder layout of this skill

```
site-builder/
├── SKILL.md              <- this file
├── image/                <- style references AND photos to use in the site (any subfolders)
├── fonts/                <- font files (.woff2 / .woff / .ttf / .otf) to use in the site
├── references/
│   ├── questions.md      <- exact question wording + options for each step (Persian + English)
│   ├── color-palettes.md <- ready palettes by business field
│   ├── site-types.md     <- section blueprint per site type
│   └── quality-checklist.md
└── scripts/
    ├── scan_assets.py    <- scans image/ + fonts/ once, makes a contact sheet
    └── inline_assets.py  <- optimizes + embeds images/fonts into the finished HTML
```

Files the user uploads in the chat (`/mnt/user-data/uploads`) count as extra assets: same rules as `image/` and `fonts/`.

## The flow at a glance

| Step | What happens | Who acts |
|------|--------------|----------|
| prep | Silent one-time scan of `image/` + `fonts/` (this is the "check the folders" step; done first because it improves every later step) | Claude |
| 1 | Ask the customer's **work style** (field + look they want) | Ask user |
| 2 | **Pick the colors** from the field and the references and apply them | Claude (no question) |
| 3 | Learn fonts/styles from all images and folders; **ask which font** the user wants | Ask user |
| 4 | Ask the **site name** | Ask user |
| 5 | Ask for the **logo**; no logo means no logo | Ask user |
| 6 | **Build** the site, then ask improvement questions | Claude + user |

Do not skip or reorder the user-facing questions (1, 3, 4/5, 6). Never ask something the user already told you; extract answers from their first message and confirm only what is missing.

## Prep - scan the folders once (silent)

1. Locate this skill's folder (the folder that contains this `SKILL.md`). Run once:
   `python3 <skill>/scripts/scan_assets.py <skill>/image <skill>/fonts [/mnt/user-data/uploads]`
2. `view` the contact sheet(s) it prints (`/home/claude/contact_N.jpg`). **Do not open images one by one** unless a detail is unclear; the sheet plus the dominant-color list is enough and costs a fraction of the credits.
3. Sort every image into one of these roles (by looking, not by filename only):
   - **style reference**: screenshots or designs of UIs, websites, app screens, posters.
   - **site photo**: photographs, product shots, illustrations, textures, backgrounds, people, places.
   - **logo**: a mark/wordmark. Only treat it as the customer's logo if the user says so.
4. Write a compact **Design Brief** for yourself (a few lines, not shown to the user unless asked):
   - Style DNA from the references: layout pattern (hero type, grid, card style), corner radius (sharp/soft/pill), shadows or glass/blur, border use, spacing density (airy/compact), image treatment (full-bleed, masked, rounded, overlay), motion hints, overall mood.
   - Palette seen in the references (use the dominant colors from the scan).
   - Type feel seen in the references: serif/sans/display, weight, size contrast, letter-spacing, case.
   - Asset manifest: file → role → where it will be used.
5. If both folders are empty and the user uploaded nothing, say so in one sentence, then continue using the field-based palettes and `references/site-types.md`. If `frontend-design` is available, skim it only in this situation.

## Step 1 - Work style (ask)

Ask what the customer does and what look they want. Use the wording in `references/questions.md`.
- Field/work: shop, restaurant/cafe, clinic, portfolio, company, education, personal brand, blog/news, event, app landing, other.
- Look: offer 3 style options. **Option 1 must be the style learned from the reference images** (describe it in plain words, for example "نرم و شیشه‌ای مثل عکس‌های مرجع"). Add two contrasting alternatives that suit the field.
- If the first message already answers both, skip the question and state your understanding in one line.

## Step 2 - Colors (no question)

Pick the palette yourself:
1. Start from the field using `references/color-palettes.md`.
2. Blend with the reference colors from the Design Brief: the references win on mood, the field wins on meaning (trust, appetite, calm, luxury, energy).
3. Fix a final palette of: background, surface, text, muted text, primary, accent, plus a success/danger pair if the site needs them. All as CSS variables.
4. Check contrast: body text at least 4.5:1, large text at least 3:1.
5. Announce it in one short line with hex codes and the reason, for example: "رنگ‌ها: پس‌زمینه #0B1020، اصلی #5B8CFF، تأکید #FFC857 - مناسب حوزه آموزش و هماهنگ با عکس‌های مرجع." The user can change it later in Step 6.

## Step 3 - Fonts (learn, then ask)

1. **Fonts folder.** List what is in `fonts/`. Group files into families and weights (Regular/Medium/Bold). These are the first choice because they are bundled and work offline.
2. **Fonts seen in the images.** Look at the typography in the style references and guess the closest typeface. Be honest ("شبیه به ... است") and never claim certainty. Pick the closest match that is either in `fonts/` or free and safe (see below).
3. **Ask which font the user wants.** Offer 3-4 options, in this order:
   - the font matching the reference images,
   - the best family from `fonts/` (if different),
   - one more that fits the field,
   - "یک فونت دیگر / فونت خودم" (user supplies a font file or name).
4. Safe free families if nothing is bundled: Persian - Vazirmatn, Estedad, Shabnam, Sahel; Latin - Inter, DM Sans, Poppins, Montserrat, Space Grotesk, Playfair Display. If the user brings another font, remind them in one short sentence to check its license for commercial use.
5. **How fonts get into the site.**
   - Bundled files in `fonts/` are declared with `@font-face` using the relative path `fonts/<file>`; `inline_assets.py` embeds them so the site works offline.
   - If the chosen font is not bundled and there is no way to download it, use a Google Fonts `<link>` (works when the user is online) and always add a strong fallback stack (`'Vazirmatn', Tahoma, system-ui, sans-serif`). Tell the user once, simply: "برای نمایش دقیق فونت، اینترنت لازم است؛ اگر فایل فونت را بدهید داخل سایت قرار می‌دهم."
   - Use at most 2 families (one display/heading, one body). Persian sites: a Persian-capable family first, Latin fallback second.

## Step 4 - Site name (ask)

Ask for the site/brand name. Use it in `<title>`, header, footer, and meta description. Never translate or alter it.

## Step 5 - Logo (ask)

Ask for the logo in the same message as Step 4 (as two short lines), or right after. Rules:
- Logo provided: place it in the header and footer, create a favicon from it if it is square enough, and pull an accent color from it only if it improves harmony (do not repaint the logo).
- User says they have none (or ignores it): **build without a logo.** Do not invent or draw a logo. The header shows the site name as plain text in the heading font.

## Step 6 - Build, then improve

### 6a. Build in ONE pass
1. Choose sections from `references/site-types.md` for the field. Keep the page focused: usually 5-8 sections.
2. Write one `index.html` (CSS and JS inline, no frameworks, no build step) in `/home/claude/build/index.html`. Reference assets by **relative path** (`image/hero.jpg`, `fonts/Vazir-Bold.woff2`). Never paste base64 yourself.
3. Apply the Design Brief faithfully: same radius, shadow/glass, spacing, card patterns, image treatment, and mood as the references. Use CSS variables for the palette and type scale.
4. **Use the images from `image/`** for hero, cards, galleries, backgrounds and banners, matched to their meaning. Do not leave good photos unused; do not stretch or crop faces badly (`object-fit: cover`, sensible `object-position`). Add real `alt` text and `loading="lazy"` below the fold. Never use remote or stock images.
5. Write real, persuasive copy in the user's language that fits the field and the site name. No lorem ipsum. Unknown facts are marked placeholders.
6. Build quality in from the start (see `references/quality-checklist.md`): fully responsive (mobile first), RTL-correct for Persian, sticky or clear navigation, smooth anchor scrolling, visible focus states, hover/active states, subtle scroll-reveal animation that respects `prefers-reduced-motion`, one clear call-to-action per section, accessible contrast, semantic tags, SEO meta tags.
7. Make it come alive without bloat: a few well-chosen micro-interactions (menu toggle, tabs, FAQ accordion, gallery lightbox, counters) written in small vanilla JS, only if the field benefits.

### 6b. Package
1. Run: `python3 <skill>/scripts/inline_assets.py /home/claude/build/index.html /mnt/user-data/outputs/<site-name>.html --roots <skill>/image <skill>/fonts /mnt/user-data/uploads`
   This optimizes the images (resized, WebP) and embeds images and fonts, giving **one file that opens by double-click**.
2. Read the printed report. Fix every `MISSING` line (wrong path or file name) with a tiny edit and rerun. If the file is over about 8 MB, rerun with `--mode folder` to `/mnt/user-data/outputs/<site-name>/` and deliver it as a zip.
3. Deliver with the file-presenting tool (`present_files`). If a hosted preview tool is available and the user would benefit from a link, you may publish instead, but the downloadable file is the default deliverable.
4. In the chat, say in two or three lines: what was built, which style/palette/font were used, and how to open it ("فایل را دوبار کلیک کنید تا در مرورگر باز شود").

### 6c. Improvement questions (after the first version)
Ask up to 3 short questions in one go (tappable options when possible) that would most improve the result. Pick from:
- **Content:** real services/products list, prices (or "price on request"), about text, working hours, address, phone, WhatsApp/Telegram/Instagram links, map.
- **Sections:** anything to add, remove or reorder (testimonials, FAQ, gallery, pricing, blog, contact form).
- **Look:** lighter/darker, rounder/sharper, calmer/bolder, change the palette or font.
- **Goal:** the one action visitors should take (call, order, book, message).
- **Languages:** add English/Persian version, language switch.

Then iterate **one change at a time**: apply the single requested change, tell the user it is done, hand back only what changed, wait for the next change.

## Editing an existing site (follow-up turns)

- Edit the built `index.html` in place with small `str_replace` edits; never rewrite the whole file for a small change; never re-scan assets unless new files appeared.
- After a change, rerun `inline_assets.py` and deliver the updated file. In the message, name only the part that changed ("فقط بخش تماس عوض شد").
- If the user asks for a new section, add only that section's HTML/CSS and reuse the existing variables and patterns.

## Credit-saving rules (strict)

1. Scan assets **once**, with the script, and look at the contact sheet instead of every image.
2. Keep the Design Brief short and reuse it; do not re-analyze the images each turn.
3. Build the full page in **one** file-creation call. No draft-then-rewrite, no polish passes, no "let me double check by reading it back".
4. Reference assets by relative path and let `inline_assets.py` do the embedding and compression. Never output base64 or large data blobs.
5. Edits are tiny `str_replace` calls, not full rewrites.
6. Do not run screenshot/render loops unless the user asks or the report shows an error. The one automatic check is the `inline_assets.py` report.
7. Questions are batched sensibly and never repeated. Replies are short; no long explanations of what you did.
8. Do not install packages or browse the web unless a task truly needs it.

## What a great result looks like

- It is recognizably in the style of the reference images, not a generic template.
- The chosen font, palette and the user's photos are all visibly used.
- It opens by double-click, looks right on a phone and a laptop, and Persian text is correctly aligned and shaped.
- Every placeholder is clearly marked and listed back to the user in Step 6c.
- The user can say "change X" and receive only X.
