# CMS template + schema (used for the PHP+admin and WordPress targets)

You write the design ONCE as two files in `/home/claude/build/`:
- `index.tpl.html` - the full page (a normal HTML page with `{{ }}` tags where content should be editable)
- `schema.json` - the list of editable things (this automatically becomes the admin panel in the PHP package and the Customizer + menus in WordPress)

`scripts/package_site.py` validates both, copies/optimizes the images and fonts, and builds the host-ready zips. `scripts/render_preview.py` renders the same pair to a plain static HTML page using the defaults (useful for the static targets and for smoke tests).

## schema.json

```json
{
  "site": {"name": "Brand", "lang": "fa", "dir": "rtl", "description": "short description"},
  "settings": [
    {"key": "site_name",  "label": "نام سایت",   "type": "text",     "default": "Brand",   "group": "عمومی"},
    {"key": "hero_title", "label": "عنوان اصلی", "type": "text",     "default": "...",     "group": "صفحه اصلی"},
    {"key": "hero_text",  "label": "متن",        "type": "textarea", "default": "...",     "group": "صفحه اصلی"},
    {"key": "hero_image", "label": "تصویر اصلی", "type": "image",    "default": "image/hero.jpg", "group": "صفحه اصلی"},
    {"key": "primary",    "label": "رنگ اصلی",   "type": "color",    "default": "#5B8CFF", "group": "ظاهر"},
    {"key": "instagram",  "label": "اینستاگرام", "type": "url",      "default": "",        "group": "تماس"},
    {"key": "phone",      "label": "تلفن",       "type": "text",     "default": "",        "group": "تماس"}
  ],
  "collections": [
    {"key": "services", "label": "خدمات", "item_label": "خدمت",
     "fields": [
       {"key": "title", "label": "عنوان", "type": "text"},
       {"key": "text",  "label": "توضیح", "type": "textarea"},
       {"key": "image", "label": "تصویر", "type": "image"}
     ],
     "items": [ {"title": "...", "text": "...", "image": "image/service1.jpg"} ]}
  ],
  "contact_form": true
}
```

Rules
- Field types: `text`, `textarea`, `url`, `color` (hex), `image`.
- Keys: lowercase letters, digits, `_`; start with a letter; max 17 chars; unique across settings and collections.
- Always declare a `site_name` setting. Put every user-visible text/number/link that the owner may want to change into `settings` or `collections` (hero texts, about text, phone, address, hours, social links, prices, services, products, gallery, testimonials, FAQ items).
- The first field of a collection is its title in the admin list (use `title`). In WordPress the `title` field is the post title.
- Give `items` realistic default content (in the site's language) so the site looks complete right after install. Never invent real phone numbers or addresses: leave those `default` empty and wrap them in `{{#if phone}}...{{/if}}`.
- Asset paths in `default` and `items` use the same relative form as in the template (`image/x.jpg`); the packager copies/optimizes them.
- `group` (optional) groups settings under one heading in the panel/Customizer.
- `contact_form: true` enables message storage, the inbox in the panel and the anti-spam handling.

## index.tpl.html syntax

| Tag | Meaning |
|-----|---------|
| `{{ key }}` | value of a setting (escaped). Image-type values become full URLs, url-type values are scheme-checked, color values are validated. |
| `{{ key\|br }}` | same, with line breaks turned into `<br>` (for textarea values) |
| `{{#if key}}...{{else}}...{{/if}}` | show a block only when the setting is non-empty (or the collection has items). `{{else}}` is optional. |
| `{{#each list}}...{{/each}}` | repeat for each item of a collection. Inside: `{{ field }}`, `{{ @index }}` (1-based), `{{#if @first}}`, `{{#if @last}}`. Blocks can nest. |
| `{{ site_name }}` `{{ site_description }}` `{{ site_lang }}` `{{ site_dir }}` `{{ year }}` | built-ins |
| `{{ base }}` | base URL for files (the packager writes it in front of every asset path) |
| `{{ contact_url }}` `{{ form_fields }}` `{{ flash }}` | contact form plumbing (see below) |

Page skeleton: `<html lang="{{ site_lang }}" dir="{{ site_dir }}">`, `<title>{{ site_name }}</title>`, colors as CSS variables set from settings: `:root{--primary:{{ primary }};}`.

Contact form (required markup when `contact_form` is true)
```html
<section id="contact">
  {{#if flash}}<div class="form-flash">{{ flash }}</div>{{/if}}
  <form method="post" action="{{ contact_url }}">
    {{ form_fields }}
    <input name="name" required maxlength="100">
    <input name="phone" maxlength="40">
    <input name="email" type="email" maxlength="150">
    <textarea name="message" maxlength="3000"></textarea>
    <button type="submit">ارسال</button>
  </form>
</section>
```
Field names are fixed: `name`, `phone`, `email`, `message` (use any subset). `{{ form_fields }}` adds the CSRF/nonce token and the hidden anti-spam field: never remove it.

## Rules that keep the page working under the security policy (CSP)
1. No inline event handlers (`onclick="..."`). Use `addEventListener` inside a `<script>` block.
2. No external scripts, no CDN libraries. Write small vanilla JS inline. (Google Fonts CSS and fonts are allowed, but bundled fonts from `fonts/` are preferred.)
3. No `javascript:` links.
4. JS must be null-safe (`var el=document.querySelector(...); if(el){...}`): on WordPress, blog/inner pages reuse the header, footer and scripts of this page, so selectors may not exist there.
5. Do not write literal double braces in CSS/JS. If you must, split them with a space.
6. Use `{{ ... }}` only for the tags above; unknown names fail validation.
7. Reference images and fonts by plain relative paths: `image/<file>` and `fonts/<family folder>/<file>` (quote font URLs: `url('fonts/...')`). Never base64.
8. Images that the owner should be able to replace must come from an `image`-type setting or collection field (`<img src="{{ hero_image }}">`), not from a hard-coded path. Decorative images (patterns, icons) may be hard-coded paths.
9. Keep the header in a `<header>` element and the footer in a `<footer>` element: WordPress inner pages (posts, pages, 404, shop) reuse them.
10. On a static target (no PHP) a contact form cannot work: use phone / WhatsApp / Telegram / mailto buttons instead and tell the user.
