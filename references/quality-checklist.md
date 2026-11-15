# Quality checklist (check mentally while writing; do not re-read the file afterwards)

## Layout and responsiveness
- Mobile first; looks right at 360, 768, 1280 and 1920 px.
- No horizontal scroll; images use `max-width:100%`; wide tables scroll inside their own container.
- Spacing scale consistent (4/8 based); section padding generous on desktop, tighter on mobile.
- Safe viewport meta: `width=device-width, initial-scale=1`.

## Persian / RTL
- `<html lang="fa" dir="rtl">` for Persian sites; text aligned right by default.
- Use logical CSS properties (`margin-inline-start`, `padding-inline`, `inset-inline-end`) so RTL works naturally.
- Mirror directional icons (arrows, chevrons); numbers can be Persian digits in the copy.
- Line-height 1.8 or more for Persian body text; no letter-spacing on Persian text.
- Keep Latin brand names inside `<span dir="ltr">` when needed.

## Visual fidelity to the references
- Radius, shadows/glass, borders, card structure, image masking and spacing match the Design Brief.
- Typography scale has strong contrast: large display heading, calm body size (16-18 px).
- Photos are the heroes: large, well-cropped, consistent treatment (same radius, same overlay).

## Interaction
- Hover, focus-visible and active states on every link and button.
- Smooth scroll for anchors; sticky header does not hide section titles (`scroll-margin-top`).
- Reveal-on-scroll animations are subtle, short, and disabled under `prefers-reduced-motion`.

## Accessibility and SEO
- Contrast: text 4.5:1, large text 3:1.
- Semantic tags (`header`, `nav`, `main`, `section`, `footer`), one `h1`, logical heading order.
- `alt` on every image (empty alt only for decoration), labels on form fields, buttons are `<button>`.
- Title (under 60 chars), meta description (under 160 chars), `theme-color`, Open Graph title/description.

## Performance
- Images referenced by relative path and optimized by `scripts/inline_assets.py`.
- `loading="lazy"` on below-the-fold images; explicit `width`/`height` or aspect-ratio to avoid layout shift.
- At most 2 font families; `font-display: swap`.
- Small vanilla JS only; no libraries unless the user needs a feature that truly requires one.

## Content honesty
- No invented phone numbers, addresses, prices, statistics, reviews, certificates or awards.
- Placeholders are in brackets, easy to spot, and listed back to the user in Step 6c.

## Security (all targets, details in security.md)
- CMS targets: no inline event handlers, no external scripts, no `javascript:` links, `{{ form_fields }}` inside every form.
- Never include secrets, API keys or personal data in the page or schema.
- Forms: only the four fixed field names (name, phone, email, message) with length limits; no file uploads from visitors.
- External links that open a new tab use `rel="noopener noreferrer"`.
- Deliver the three owner must-dos in the final message: SSL + HTTPS redirect, strong admin password + 2FA, backups.
