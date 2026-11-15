# Section blueprints by site type

Pick 5-8 sections. Order matters: hook, proof, offer, action. Every section has one clear purpose and at most one call to action. Use the user's own photos wherever they fit.

## Online shop
Header with search/cart icon -> hero with the main offer -> category tiles -> featured products grid (image, name, price placeholder, buy button) -> why us (shipping, return, support) -> reviews -> newsletter -> footer (contact, social, trust badges).

## Restaurant / cafe
Hero with signature dish photo + "reserve/order" -> about the place -> menu in tabs (categories) -> gallery -> opening hours + map placeholder -> reviews -> reservation/contact -> footer.

## Clinic / medical
Hero (reassuring headline + book button) -> services -> doctors/team -> how it works (3 steps) -> before-after or results (only if provided) -> FAQ -> appointment/contact -> footer. No medical claims that were not provided.

## Portfolio / creative
Hero (name + one-line role) -> selected work grid (large images, hover reveal) -> services -> process -> about -> testimonials -> contact. Showcase images first and large.

## Company / services
Hero (value proposition) -> services -> numbers/proof -> process -> clients/partners -> team or about -> FAQ -> contact form -> footer.

## Education / course
Hero (outcome promise + enroll) -> who it is for -> curriculum/modules -> instructor -> results/testimonials -> pricing -> FAQ -> enroll CTA.

## Personal brand
Hero (photo + statement) -> about -> expertise/services -> featured work or media -> testimonials -> social links + contact.

## Blog / news
Header with categories -> featured article -> latest grid -> popular -> newsletter -> footer. Readable type scale first.

## Event
Hero with date/place/countdown -> schedule -> speakers or lineup -> venue -> tickets/pricing -> sponsors -> FAQ -> register.

## App landing
Hero with device mockup (built from the user's images or CSS) -> key features -> how it works -> screenshots -> pricing or download -> testimonials -> FAQ -> download buttons.

## Always include
- Sticky or clear navigation with anchor links, mobile menu.
- A footer with the site name, contact placeholders and social links.
- Meta tags: title, description, theme-color, Open Graph basics.

## Editable lists per site type (schema collections for the PHP / WordPress targets)

| Site type | Collections (key: fields) |
|-----------|---------------------------|
| Online shop | products: title, text, price (text), image, link (url); categories: title, image |
| Restaurant / cafe | menu_items: title, text, price, image; gallery: title, image |
| Clinic | services: title, text, image; team: title, text, image; faq: title, text |
| Portfolio | projects: title, text, image, link; services: title, text |
| Company / services | services: title, text, image; clients: title, image; faq: title, text |
| Education | modules: title, text; reviews: title, text; faq: title, text |
| Event | schedule: title, text (time in title or text); speakers: title, text, image; sponsors: title, image |
| Blog / news | WordPress target: use WordPress posts (no collection); PHP target: articles: title, text, image |
| App landing | features: title, text, image; faq: title, text |
Settings always include: site_name, hero_title, hero_text, hero_image, primary color, phone, address, working_hours, instagram, telegram, whatsapp (leave unknown contact values empty and wrap them in `{{#if key}}`).
