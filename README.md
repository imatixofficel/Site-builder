
```markdown
<div align="center">

# 🏗️ Site Builder — سایت‌ساز هوشمند

**بدون کدنویسی، سایت خودت رو بساز — فقط بگو «برام یه سایت بساز»** 😊

[![Made with Claude](https://img.shields.io/badge/Made%20with-Claude%20Skill-8b5cf6?style=flat-square)](https://claude.ai)
[![Language](https://img.shields.io/badge/Language-فارسی-38bdf8?style=flat-square)](#)
[![License](https://img.shields.io/badge/License-MIT-22c55e?style=flat-square)](#-لایسنس)
[![Status](https://img.shields.io/badge/Status-Active-22c55e?style=flat-square)](#)

### 🌐 [🇬🇧 English Version](#-english-version)

</div>

---

## 📖 درباره پروژه

**Site Builder** یک مهارت (Skill) برای Claude است که به کاربران اجازه می‌دهد بدون هیچ دانش برنامه‌نویسی، سایت شخصی، فروشگاهی، شرکتی، بلاگ یا پرتفولیوی خود را بسازند. کافی است در چت بنویسید **«برام یه سایت بساز»** و بقیه کارها با Site Builder است.

> ✨ خروجی به صورت **۴ نوع مختلف** تحویل داده می‌شود: از یک فایل HTML ساده تا سایت PHP با پنل مدیریت و قالب وردپرس.

---

## ✨ ویژگی‌ها

| ویژگی | توضیح |
|---|---|
| 🎨 **۴ نوع تحویل** | HTML ساده، زیپ هاست، PHP با پنل، قالب وردپرس |
| 🔤 **فونت‌های فارسی** | وزیر، ساحل، صمیم، شبنم، تنها، پرستو، یکان و... |
| 🖼️ **عکس‌های آماده** | پوشه `image/` با تصاویر مرجع و قابل استفاده |
| 🔒 **امنیت بالا** | CSP، CSRF، هش رمز، قفل Brute Force، ورود دو مرحله‌ای |
| ⚙️ **پنل مدیریت** | نسخه PHP با نصب مرورگری و پنل `/admin/` |
| 📱 **واکنش‌گرا** | خروجی‌ها روی موبایل، تبلت و دسکتاپ درست نمایش داده می‌شوند |
| 📄 **راهنمای فارسی** | فایل `...-GUIDE-FA.txt` کنار هر زیپ |
| 🌐 **چند زبانه** | پشتیبانی از فارسی و انگلیسی در قالب‌ها |

---

## 🚀 نصب و راه‌اندازی

**پیش‌نیازها:** حساب Claude با دسترسی به Skills + مرورگر مدرن

| مرحله | کاری که باید بکنی |
|:---:|---|
| **۱** | پوشه `site-builder` را همراه با تمام محتویات (فونت‌ها، عکس‌ها و...) **زیپ** کن |
| **۲** | برو به: `Claude > Settings > Capabilities > Skills > Upload Skill` |
| **۳** | فایل زیپ را انتخاب و **آپلود** کن |
| **۴** | در چت بنویس: **«برام یه سایت بساز»** |

> 💡 **نکته:** هر بار که عکس یا فونت جدیدی اضافه کردی، دوباره زیپ کن و آپلود کن تا Site Builder آن‌ها را ببیند.

---

## 🗂️ ساختار پروژه

```
site-builder/
├── 📁 image/          # عکس‌های مرجع و آماده استفاده در سایت
├── 📁 fonts/          # فونت‌های فارسی (وزیر، ساحل، صمیم، شبنم، تنها، پرستو، یکان و...)
├── 📁 templates/      # موتورهای آماده و امن
│   ├── php-with-panel/    # PHP با پنل مدیریت
│   ├── wordpress-theme/   # قالب وردپرس
│   ├── security-plugin/   # افزونه امنیتی وردپرس
│   └── htaccess/          # محافظ .htaccess
├── 📁 scripts/        # ابزارهای سبک (اسکن عکس/فونت، ساخت زیپ آماده هاست)
└── 📁 references/     # راهنماهای داخلی (سوال‌ها، رنگ‌ها، امنیت، قالب)
```

---

## 🎯 مراحل ساخت سایت

1. 🎨 **سبک کار** — فروشگاهی، شخصی، شرکتی، بلاگ، پرتفولیو و...
2. 🌈 **رنگ (خودکار)** — بر اساس سبک، رنگ مناسب پیشنهاد می‌شود
3. 🔤 **فونت** — از بین فونت‌های فارسی موجود (پیش‌فرض: وزیر)
4. 📝 **اسم سایت** — اسم برند یا سایت شما
5. 🖼️ **لوگو** — آپلود یا ساخت لوگوی ساده
6. 📦 **نوع تحویل** — یکی از ۴ گزینه
7. 🏗️ **ساخت سایت** — سایت شما ساخته می‌شود!
8. ✨ **سوال‌های بهبود** — رنگ، متن، چیدمان و...

---

## 📦 انواع تحویل

| # | نوع تحویل | توضیح | مناسب برای |
|:---:|---|---|---|
| 1️⃣ | **یک فایل HTML** | دوبار کلیک کن، باز می‌شه | تست سریع، استفاده شخصی |
| 2️⃣ | **زیپ آماده هاست (HTML ساده)** | آپلود مستقیم روی هاست | سایت ساده و سبک |
| 3️⃣ | **سایت PHP با پنل مدیریت** | نصب با مرورگر، پنل در `/admin/` | سایت حرفه‌ای با مدیریت محتوا |
| 4️⃣ | **قالب وردپرس + افزونه امنیتی** | نصب روی وردپرس | اگر وردپرس دارید |

> 📄 برای هر نوع تحویل، یک **فایل راهنمای فارسی** (`...-GUIDE-FA.txt`) کنار زیپ‌ها تحویل داده می‌شود.

---

## 🔒 امنیت

**ویژگی‌هایی که Site Builder فراهم می‌کند:**

| ویژگی | توضیح | وضعیت |
|---|---|:---:|
| **هدرهای امنیتی و CSP** | جلوگیری از حملات XSS و تزریق کد | ✅ |
| **محافظ `.htaccess`** | قفل کردن فایل‌های حساس | ✅ |
| **رمز هش‌شده** | پسوردها رمزنگاری می‌شوند | ✅ |
| **قفل بعد از ۵ تلاش ناموفق** | جلوگیری از Brute Force | ✅ |
| **ورود دو مرحله‌ای** | لایه امنیتی اضافه برای پنل ادمین | ✅ |
| **توکن CSRF** | جلوگیری از جعل درخواست | ✅ |
| **آپلود امن تصویر** | فقط فایل‌های مجاز آپلود می‌شوند | ✅ |
| **فرم تماس ضد اسپم** | جلوگیری از ربات‌های اسپم | ✅ |
| **افزونه امنیتی وردپرس** | برای نسخه وردپرس | ✅ |
| **صفحه وضعیت امنیت** | مشاهده وضعیت کلی امنیت سایت | ✅ |

**⚠️ مسئولیت‌های صاحب سایت:**

- [ ] 🔐 **فعال‌سازی SSL** (قفل سبز HTTPS)
- [ ] 🔑 **انتخاب رمز قوی** برای پنل مدیریت
- [ ] 💾 **بکاپ منظم** از سایت و دیتابیس
- [ ] 🔄 **به‌روزرسانی منظم** PHP، وردپرس و افزونه‌ها

> 📌 حتی امن‌ترین سایت هم با رمز ضعیف یا بدون بکاپ آسیب‌پذیر است.

**🚨 هشدار مهم برای نسخه PHP:**

فایل راهنمای نسخه PHP یک **«کد نصب»** دارد:
- ✅ این کد را **فقط روی کامپیوتر خودتان** نگه دارید
- ❌ **هرگز روی هاست آپلود نکنید** — چون هر کسی به آن دسترسی پیدا کند، می‌تواند سایت را دوباره نصب کند!

---

## 📦 خروجی‌ها

| فایل خروجی | توضیح |
|---|---|
| `site.html` | نسخه تک‌فایل HTML |
| `site-host.zip` | زیپ آماده آپلود روی هاست (HTML) |
| `site-php.zip` | سایت PHP با پنل مدیریت + راهنما |
| `site-wordpress.zip` | قالب وردپرس + افزونه امنیتی |
| `...-GUIDE-FA.txt` | راهنمای فارسی هر نسخه |

---

## ❓ سوال‌های پرتکرار

<details>
<summary><b>🔹 باید کد بنویسم؟</b></summary>
نه! همه‌چیز ساخته می‌شود، فقط جواب سوال‌ها را بدهید.
</details>

<details>
<summary><b>🔹 می‌توانم بعداً تغییر بدهم؟</b></summary>
بله! بعد از ساخت، سوال‌های بهبود پرسیده می‌شود و می‌توانید تغییرات بخواهید.
</details>

<details>
<summary><b>🔹 کدام نوع تحویل را انتخاب کنم؟</b></summary>

- فقط تست می‌خواهید؟ → **یک فایل HTML**
- سایت ساده روی هاست؟ → **زیپ آماده هاست**
- پنل مدیریت و دیتابیس می‌خواهید؟ → **سایت PHP**
- وردپرس دارید؟ → **قالب وردپرس**

</details>

<details>
<summary><b>🔹 فونت‌ها را خودم اضافه کنم؟</b></summary>
بله، فونت‌های دلخواه را در پوشه <code>fonts/</code> بگذارید و دوباره زیپ و آپلود کنید.
</details>

<details>
<summary><b>🔹 عکس‌هایم را چطور اضافه کنم؟</b></summary>
عکس‌ها را در پوشه <code>image/</code> بریزید و دوباره زیپ کنید.
</details>

<details>
<summary><b>🔹 فایل راهنما کجاست؟</b></summary>
برای هر نوع تحویل، یک فایل <code>...-GUIDE-FA.txt</code> کنار زیپ‌ها تحویل داده می‌شود.
</details>

<details>
<summary><b>🔹 رمز پنل ادمین را چطور عوض کنم؟</b></summary>
در نسخه PHP از پنل <code>/admin/</code> بخش تنظیمات امنیتی. در نسخه وردپرس از تنظیمات افزونه امنیتی.
</details>

<details>
<summary><b>🔹 روی کدام هاست‌ها کار می‌کند؟</b></summary>
هر هاستی که PHP 7.4+ و MySQL داشته باشد. برای نسخه HTML نیازی به PHP نیست.
</details>

---

## 🔄 به‌روزرسانی

```bash
zip -r site-builder.zip site-builder/
# سپس در Claude آپلود کن:
# Settings > Capabilities > Skills > Upload Skill
```

---

## 🛠️ تکنولوژی‌ها

<div align="center">

![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![WordPress](https://img.shields.io/badge/WordPress-21759B?style=for-the-badge&logo=wordpress&logoColor=white)
![Claude](https://img.shields.io/badge/Claude-D97757?style=for-the-badge&logo=anthropic&logoColor=white)

</div>

---

## 🤝 مشارکت

1. پروژه را **Fork** کنید
2. یک **Branch** جدید بسازید (`git checkout -b feature/AmazingFeature`)
3. تغییرات را **Commit** کنید (`git commit -m 'Add some AmazingFeature'`)
4. **Push** کنید (`git push origin feature/AmazingFeature`)
5. یک **Pull Request** باز کنید

قبل از ارسال، لطفاً [قالب Issue](.github/ISSUE_TEMPLATE/) مناسب را انتخاب کنید.

---

## 📝 لایسنس

این پروژه تحت لایسنس **MIT** منتشر شده است. برای اطلاعات بیشتر فایل [LICENSE](LICENSE) را ببینید.

---

## 🙏 تشکر

- از تیم [Claude](https://claude.ai) برای پشتیبانی از Skills
- از تمام سازندگان فونت‌های فارسی (وزیر، ساحل، صمیم و...)
- از شما که از این پروژه استفاده می‌کنید ❤️

<div align="center">

**⭐ اگر این پروژه برایتان مفید بود، یک ستاره بدهید! ⭐**

ساخته‌شده با ❤️ برای کاربران فارسی‌زبان

### 🌐 [🇬🇧 English Version](#-english-version)

</div>

---

<div dir="ltr">

## 🇬🇧 English Version

### 📖 About

**Site Builder** is a Claude Skill that lets users build personal, e-commerce, corporate, blog, or portfolio websites **without any coding knowledge**. Just type **"Build me a website"** in chat and Site Builder handles the rest.

> ✨ Delivers **4 different output types**: from a single HTML file to a PHP site with admin panel and a WordPress theme.

### ✨ Features

| Feature | Description |
|---|---|
| 🎨 **4 delivery types** | Single HTML, host-ready ZIP, PHP with panel, WordPress theme |
| 🔤 **Persian fonts** | Vazir, Sahel, Samim, Shabnam, Tanha, Parastoo, Yekan, ... |
| 🖼️ **Ready images** | `image/` folder with reference and usable assets |
| 🔒 **High security** | CSP, CSRF, password hashing, Brute Force lock, 2FA |
| ⚙️ **Admin panel** | PHP version with browser install and `/admin/` panel |
| 📱 **Responsive** | Outputs render correctly on mobile, tablet, and desktop |
| 📄 **Persian guide** | `...-GUIDE-FA.txt` next to each ZIP |
| 🌐 **Multilingual** | Persian and English support in templates |

### 🚀 Installation

**Requirements:** Claude account with Skills access + modern browser

| Step | Action |
|:---:|---|
| **1** | **Zip** the `site-builder` folder with all contents (fonts, images, etc.) |
| **2** | Go to: `Claude > Settings > Capabilities > Skills > Upload Skill` |
| **3** | Select the ZIP and **upload** |
| **4** | In chat, type: **"Build me a website"** |

> 💡 **Tip:** Whenever you add new images or fonts, re-zip and re-upload so Site Builder can see them.

### 🗂️ Project structure

```
site-builder/
├── 📁 image/          # Reference and ready-to-use images
├── 📁 fonts/          # Persian fonts (Vazir, Sahel, Samim, Shabnam, Tanha, Parastoo, Yekan, ...)
├── 📁 templates/      # Ready, secure engines
│   ├── php-with-panel/    # PHP with admin panel
│   ├── wordpress-theme/   # WordPress theme
│   ├── security-plugin/   # WordPress security plugin
│   └── htaccess/          # .htaccess guard
├── 📁 scripts/        # Lightweight tools (image/font scan, host-ready ZIP builder)
└── 📁 references/     # Internal guides (questions, colors, security, templates)
```

### 🎯 Build steps

1. 🎨 **Style** — e-commerce, personal, corporate, blog, portfolio, ...
2. 🌈 **Color (auto)** — suggested based on the chosen style
3. 🔤 **Font** — pick from available Persian fonts (default: Vazir)
4. 📝 **Site name** — your brand or site name
5. 🖼️ **Logo** — upload or generate a simple one
6. 📦 **Delivery type** — one of 4 options
7. 🏗️ **Build** — your site is generated!
8. ✨ **Refinement questions** — colors, text, layout, ...

### 📦 Delivery types

| # | Type | Description | Best for |
|:---:|---|---|---|
| 1️⃣ | **Single HTML file** | Double-click to open | Quick testing, personal use |
| 2️⃣ | **Host-ready ZIP (simple HTML)** | Upload directly to host | Simple, lightweight sites |
| 3️⃣ | **PHP site with admin panel** | Browser install, panel at `/admin/` | Professional sites with CMS |
| 4️⃣ | **WordPress theme + security plugin** | Install on WordPress | If you use WordPress |

> 📄 Each delivery type ships with a **Persian guide** (`...-GUIDE-FA.txt`) next to the ZIPs.

### 🔒 Security

**Features Site Builder provides:**

| Feature | Description | Status |
|---|---|:---:|
| **Security headers & CSP** | Prevent XSS and code injection | ✅ |
| **`.htaccess` guard** | Lock sensitive files | ✅ |
| **Hashed passwords** | Passwords stored encrypted | ✅ |
| **Lock after 5 failed attempts** | Prevent Brute Force | ✅ |
| **Two-factor login** | Extra layer for admin panel | ✅ |
| **CSRF token** | Prevent request forgery | ✅ |
| **Secure image upload** | Only allowed files accepted | ✅ |
| **Anti-spam contact form** | Block spam bots | ✅ |
| **WordPress security plugin** | For the WordPress version | ✅ |
| **Security status page** | View overall site security | ✅ |

**⚠️ Site owner responsibilities:**

- [ ] 🔐 **Enable SSL** (green HTTPS lock)
- [ ] 🔑 **Choose a strong password** for the admin panel
- [ ] 💾 **Regular backups** of site and database
- [ ] 🔄 **Regular updates** of PHP, WordPress, and plugins

> 📌 Even the most secure site is vulnerable with a weak password or no backups.

**🚨 Important warning for the PHP version:**

The PHP guide contains an **"install code"**:
- ✅ Keep it **only on your own computer**
- ❌ **Never upload it to the host** — anyone with access could reinstall the site!

### 📦 Outputs

| Output file | Description |
|---|---|
| `site.html` | Single-file HTML version |
| `site-host.zip` | Host-ready ZIP (HTML) |
| `site-php.zip` | PHP site with admin panel + guide |
| `site-wordpress.zip` | WordPress theme + security plugin |
| `...-GUIDE-FA.txt` | Persian guide for each version |

### ❓ FAQ

<details>
<summary><b>🔹 Do I need to write code?</b></summary>
No! Everything is generated — just answer the questions.
</details>

<details>
<summary><b>🔹 Can I change things later?</b></summary>
Yes! After building, refinement questions are asked and you can request changes.
</details>

<details>
<summary><b>🔹 Which delivery type should I pick?</b></summary>

- Just testing? → **Single HTML**
- Simple host site? → **Host-ready ZIP**
- Need admin panel and database? → **PHP site**
- Using WordPress? → **WordPress theme**

</details>

<details>
<summary><b>🔹 Can I add my own fonts?</b></summary>
Yes — drop them in the <code>fonts/</code> folder, then re-zip and re-upload.
</details>

<details>
<summary><b>🔹 How do I add my images?</b></summary>
Drop them in the <code>image/</code> folder and re-zip.
</details>

<details>
<summary><b>🔹 Where is the guide file?</b></summary>
Each delivery type includes a <code>...-GUIDE-FA.txt</code> next to the ZIPs.
</details>

<details>
<summary><b>🔹 How do I change the admin password?</b></summary>
In the PHP version from the <code>/admin/</code> security settings. In WordPress from the security plugin settings.
</details>

<details>
<summary><b>🔹 Which hosts are supported?</b></summary>
Any host with PHP 7.4+ and MySQL. The HTML version does not require PHP.
</details>

### 🔄 Updating

```bash
zip -r site-builder.zip site-builder/
# Then upload in Claude:
# Settings > Capabilities > Skills > Upload Skill
```

### 🛠️ Tech stack

<div align="center">

![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![WordPress](https://img.shields.io/badge/WordPress-21759B?style=for-the-badge&logo=wordpress&logoColor=white)
![Claude](https://img.shields.io/badge/Claude-D97757?style=for-the-badge&logo=anthropic&logoColor=white)

</div>

### 🤝 Contributing

1. **Fork** the project
2. Create a **branch** (`git checkout -b feature/AmazingFeature`)
3. **Commit** your changes (`git commit -m 'Add some AmazingFeature'`)
4. **Push** (`git push origin feature/AmazingFeature`)
5. Open a **Pull Request**

Please use the appropriate [Issue template](.github/ISSUE_TEMPLATE/) before submitting.

### 📝 License

Released under the **MIT** license. See [LICENSE](LICENSE) for details.

### 🙏 Credits

- [Claude](https://claude.ai) team for Skills support
- All Persian font creators (Vazir, Sahel, Samim, ...)
- You, for using this project ❤️

<div align="center">

**⭐ If this project helped you, please give it a star! ⭐**

Made with ❤️ for Persian-speaking users

### 🌐 [🇮🇷 بازگشت به فارسی](#-سایتساز-هوشمند)

</div>

</div>
```
