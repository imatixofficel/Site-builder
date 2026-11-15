<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Site Builder — سایت‌ساز هوشمند</title>
<link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
<style>
  :root{
    --bg:#0f172a;
    --card:#1e293b;
    --card2:#273449;
    --text:#e2e8f0;
    --muted:#94a3b8;
    --accent:#38bdf8;
    --accent2:#818cf8;
    --border:#334155;
    --radius:16px;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  body{
    font-family:'Vazirmatn',system-ui,sans-serif;
    background:linear-gradient(180deg,#0f172a,#111c33);
    color:var(--text);
    line-height:1.9;
    padding:24px 16px 80px;
  }
  .container{max-width:1000px;margin:0 auto}

  .bismillah{
    text-align:center;
    font-size:1.4rem;
    font-weight:800;
    background:linear-gradient(135deg,#1e293b,#312e81);
    border:1px solid var(--border);
    border-radius:var(--radius);
    padding:20px;
    margin-bottom:24px;
    color:var(--accent);
    letter-spacing:1px;
  }

  header.hero{
    background:linear-gradient(135deg,#1e293b,#312e81);
    border:1px solid var(--border);
    border-radius:var(--radius);
    padding:40px 28px;
    text-align:center;
    margin-bottom:32px;
    position:relative;
    overflow:hidden;
  }
  header.hero::before{
    content:"";
    position:absolute;inset:0;
    background:radial-gradient(circle at 20% 20%,rgba(56,189,248,.15),transparent 40%),
               radial-gradient(circle at 80% 80%,rgba(129,140,248,.15),transparent 40%);
    pointer-events:none;
  }
  header.hero h1{
    font-size:2rem;
    margin-bottom:8px;
    background:linear-gradient(90deg,var(--accent),var(--accent2));
    -webkit-background-clip:text;
    background-clip:text;
    color:transparent;
    position:relative;
  }
  header.hero p{color:var(--muted);position:relative}

  .badges{display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin-top:16px;position:relative}
  .badges img{height:24px}

  section{margin-bottom:32px}
  .section-title{
    display:flex;align-items:center;gap:10px;
    font-size:1.35rem;font-weight:700;
    margin-bottom:18px;
    padding-right:12px;
    border-right:4px solid var(--accent);
  }
  .section-title .num{
    background:var(--accent);
    color:#0f172a;
    width:32px;height:32px;
    border-radius:50%;
    display:inline-flex;align-items:center;justify-content:center;
    font-size:.95rem;font-weight:800;
  }

  .card{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--radius);
    padding:22px;
  }
  .grid{display:grid;gap:16px}
  .grid-2{grid-template-columns:repeat(auto-fit,minmax(280px,1fr))}

  .tile{
    background:var(--card2);
    border:1px solid var(--border);
    border-radius:14px;
    padding:18px;
    transition:.25s ease;
  }
  .tile:hover{
    transform:translateY(-3px);
    border-color:var(--accent);
    box-shadow:0 8px 24px rgba(56,189,248,.15);
  }
  .tile .icon{font-size:1.6rem;margin-bottom:8px;display:block}
  .tile h3{font-size:1.05rem;margin-bottom:6px;color:var(--accent)}
  .tile p{font-size:.92rem;color:var(--muted)}

  .steps{counter-reset:step;display:grid;gap:14px}
  .step{
    counter-increment:step;
    background:var(--card2);
    border:1px solid var(--border);
    border-radius:14px;
    padding:16px 18px 16px 60px;
    position:relative;
  }
  .step::before{
    content:counter(step);
    position:absolute;
    right:16px;top:50%;transform:translateY(-50%);
    width:32px;height:32px;
    background:linear-gradient(135deg,var(--accent),var(--accent2));
    color:#0f172a;font-weight:800;
    border-radius:50%;
    display:flex;align-items:center;justify-content:center;
  }
  .step strong{color:var(--accent)}

  .table-wrap{overflow-x:auto;border-radius:14px;border:1px solid var(--border)}
  table{width:100%;border-collapse:collapse;font-size:.92rem;min-width:480px}
  thead{background:linear-gradient(135deg,#312e81,#1e293b)}
  th,td{padding:12px 14px;text-align:right;border-bottom:1px solid var(--border)}
  th{font-weight:700;color:var(--accent)}
  tbody tr:hover{background:rgba(56,189,248,.05)}
  tbody tr:last-child td{border-bottom:none}

  .badge{
    display:inline-block;padding:3px 10px;border-radius:999px;
    font-size:.78rem;font-weight:700;
  }
  .badge-ok{background:rgba(34,197,94,.15);color:#22c55e;border:1px solid rgba(34,197,94,.3)}

  .alert{
    border-radius:14px;padding:16px 18px;margin-top:16px;
    border-right:4px solid;
    background:var(--card2);
    font-size:.93rem;
  }
  .alert-warn{border-color:#f59e0b}
  .alert-danger{border-color:#ef4444}
  .alert-info{border-color:var(--accent)}
  .alert strong{display:block;margin-bottom:4px}

  .flow{
    display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:center;
    background:var(--card2);border:1px dashed var(--border);
    border-radius:14px;padding:20px;font-weight:700;
  }
  .flow span{
    background:linear-gradient(135deg,var(--accent),var(--accent2));
    color:#0f172a;
    padding:8px 14px;border-radius:10px;
    font-size:.9rem;
  }
  .flow i{color:var(--muted);font-style:normal}

  .checklist{list-style:none;display:grid;gap:10px}
  .checklist li{
    background:var(--card2);border:1px solid var(--border);
    border-radius:12px;padding:12px 14px;
    display:flex;align-items:flex-start;gap:10px;font-size:.93rem;
  }
  .checklist li::before{content:"✅";flex-shrink:0}

  code{
    background:#0f172a;padding:2px 6px;border-radius:6px;
    font-family:monospace;font-size:.88em;color:var(--accent);
  }

  pre{
    background:#0f172a;border:1px solid var(--border);
    border-radius:12px;padding:16px;overflow-x:auto;
    direction:ltr;text-align:left;font-size:.85rem;
    color:var(--muted);
  }

  footer{
    text-align:center;color:var(--muted);font-size:.85rem;
    margin-top:40px;padding-top:20px;border-top:1px solid var(--border);
  }
  footer a{color:var(--accent);text-decoration:none}

  @media(max-width:600px){
    header.hero h1{font-size:1.5rem}
    body{padding:16px 12px 60px}
  }
</style>
</head>
<body>
<div class="container">

  <div class="bismillah">به نام پروردگار</div>

  <header class="hero">
    <h1>🏗️ Site Builder — سایت‌ساز هوشمند</h1>
    <p>بدون کدنویسی، سایت خودت رو بساز — فقط بگو «برام یه سایت بساز» 😊</p>
    <div class="badges">
      <img src="https://img.shields.io/badge/Made%20with-Claude%20Skill-8b5cf6?style=flat-square" alt="Claude">
      <img src="https://img.shields.io/badge/Language-فارسی-38bdf8?style=flat-square" alt="Persian">
      <img src="https://img.shields.io/badge/License-MIT-22c55e?style=flat-square" alt="MIT">
      <img src="https://img.shields.io/badge/Status-Active-22c55e?style=flat-square" alt="Active">
    </div>
  </header>

  <section>
    <h2 class="section-title"><span class="num">📖</span> درباره پروژه</h2>
    <div class="card">
      <p><strong style="color:var(--accent)">Site Builder</strong> یک مهارت (Skill) برای Claude است که به کاربران اجازه می‌دهد بدون هیچ دانش برنامه‌نویسی، سایت شخصی، فروشگاهی، شرکتی، بلاگ یا پرتفولیوی خود را بسازند. کافی است در چت بنویسید <strong style="color:var(--accent)">«برام یه سایت بساز»</strong> و بقیه کارها با Site Builder است.</p>
      <div class="alert alert-info" style="margin-top:16px">
        ✨ خروجی به صورت <strong>۴ نوع مختلف</strong> تحویل داده می‌شود: از یک فایل HTML ساده تا سایت PHP با پنل مدیریت و قالب وردپرس.
      </div>
    </div>
  </section>

  <section>
    <h2 class="section-title"><span class="num">✨</span> ویژگی‌ها</h2>
    <div class="grid grid-2">
      <div class="tile"><span class="icon">🎨</span><h3>۴ نوع تحویل</h3><p>HTML ساده، زیپ هاست، PHP با پنل، قالب وردپرس</p></div>
      <div class="tile"><span class="icon">🔤</span><h3>فونت‌های فارسی</h3><p>وزیر، ساحل، صمیم، شبنم، تنها، پرستو، یکان و...</p></div>
      <div class="tile"><span class="icon">🖼️</span><h3>عکس‌های آماده</h3><p>پوشه image/ با تصاویر مرجع و قابل استفاده</p></div>
      <div class="tile"><span class="icon">🔒</span><h3>امنیت بالا</h3><p>CSP، CSRF، هش رمز، قفل Brute Force، ورود دو مرحله‌ای</p></div>
      <div class="tile"><span class="icon">⚙️</span><h3>پنل مدیریت</h3><p>نسخه PHP با نصب مرورگری و پنل /admin/</p></div>
      <div class="tile"><span class="icon">📱</span><h3>واکنش‌گرا</h3><p>خروجی‌ها روی موبایل، تبلت و دسکتاپ درست نمایش داده می‌شوند</p></div>
      <div class="tile"><span class="icon">📄</span><h3>راهنمای فارسی</h3><p>فایل GUIDE-FA.txt کنار هر زیپ</p></div>
      <div class="tile"><span class="icon">🌐</span><h3>چند زبانه</h3><p>پشتیبانی از فارسی و انگلیسی در قالب‌ها</p></div>
    </div>
  </section>

  <section>
    <h2 class="section-title"><span class="num">🚀</span> نصب و راه‌اندازی</h2>
    <div class="alert alert-info" style="margin-bottom:16px">
      <strong>پیش‌نیازها:</strong> حساب Claude با دسترسی به Skills + مرورگر مدرن
    </div>
    <div class="steps">
      <div class="step"><strong>زیپ کن</strong> — پوشه site-builder را همراه با تمام محتویات (فونت‌ها، عکس‌ها و...) زیپ کن</div>
      <div class="step"><strong>برو به</strong> — Claude &gt; Settings &gt; Capabilities &gt; Skills &gt; Upload Skill</div>
      <div class="step"><strong>آپلود کن</strong> — فایل زیپ را انتخاب و آپلود کن</div>
      <div class="step"><strong>شروع کن</strong> — در چت بنویس: «برام یه سایت بساز»</div>
    </div>
    <div class="alert alert-warn">
      💡 <strong>نکته:</strong> هر بار که عکس یا فونت جدیدی اضافه کردی، دوباره زیپ کن و آپلود کن تا Site Builder آن‌ها را ببیند.
    </div>
  </section>

  <section>
    <h2 class="section-title"><span class="num">🗂️</span> ساختار پروژه</h2>
    <pre>site-builder/
├── 📁 image/          # عکس‌های مرجع و آماده استفاده در سایت
├── 📁 fonts/          # فونت‌های فارسی (وزیر، ساحل، صمیم، شبنم، تنها، پرستو، یکان و...)
├── 📁 templates/      # موتورهای آماده و امن
│   ├── php-with-panel/    # PHP با پنل مدیریت
│   ├── wordpress-theme/   # قالب وردپرس
│   ├── security-plugin/   # افزونه امنیتی وردپرس
│   └── htaccess/          # محافظ .htaccess
├── 📁 scripts/        # ابزارهای سبک (اسکن عکس/فونت، ساخت زیپ آماده هاست)
└── 📁 references/     # راهنماهای داخلی (سوال‌ها، رنگ‌ها، امنیت، قالب)</pre>
  </section>

  <section>
    <h2 class="section-title"><span class="num">🎯</span> مراحل ساخت سایت</h2>
    <div class="steps">
      <div class="step"><strong>سبک کار</strong> — فروشگاهی، شخصی، شرکتی، بلاگ، پرتفولیو و...</div>
      <div class="step"><strong>رنگ (خودکار)</strong> — بر اساس سبک، رنگ مناسب پیشنهاد می‌شود</div>
      <div class="step"><strong>فونت</strong> — از بین فونت‌های فارسی موجود (پیش‌فرض: وزیر)</div>
      <div class="step"><strong>اسم سایت</strong> — اسم برند یا سایت شما</div>
      <div class="step"><strong>لوگو</strong> — آپلود یا ساخت لوگوی ساده</div>
      <div class="step"><strong>نوع تحویل</strong> — یکی از ۴ گزینه (جدول پایین)</div>
      <div class="step"><strong>ساخت سایت</strong> — سایت شما ساخته می‌شود!</div>
      <div class="step"><strong>سوال‌های بهبود</strong> — رنگ، متن، چیدمان و...</div>
    </div>
  </section>

  <section>
    <h2 class="section-title"><span class="num">📦</span> انواع تحویل</h2>
    <div class="grid grid-2">
      <div class="tile"><span class="icon">📄</span><h3>۱. یک فایل HTML</h3><p>دوبار کلیک کن، باز می‌شه. مناسب تست سریع و استفاده شخصی.</p></div>
      <div class="tile"><span class="icon">📦</span><h3>۲. زیپ آماده هاست</h3><p>آپلود مستقیم روی هاست. سایت ساده و سبک.</p></div>
      <div class="tile"><span class="icon">⚙️</span><h3>۳. سایت PHP با پنل مدیریت</h3><p>نصب با مرورگر، پنل در /admin/. مناسب سایت حرفه‌ای.</p></div>
      <div class="tile"><span class="icon">🔌</span><h3>۴. قالب وردپرس + افزونه امنیتی</h3><p>نصب روی وردپرس. مناسب کسانی که وردپرس دارن.</p></div>
    </div>
    <div class="alert alert-warn">
      ⚠️ <strong>هشدار مهم برای نسخه PHP</strong>
      فایل راهنمای نسخه PHP یه «کد نصب» داره. این کد رو فقط روی کامپیوتر خودت نگه دار و هرگز روی هاست آپلود نکن — چون هر کسی بهش دسترسی پیدا کنه، می‌تونه سایتت رو دوباره نصب کنه!
    </div>
  </section>

  <section>
    <h2 class="section-title"><span class="num">🔒</span> امنیت — ویژگی‌های فراهم‌شده</h2>
    <div class="table-wrap">
      <table>
        <thead><tr><th>ویژگی امنیتی</th><th>توضیح</th><th>وضعیت</th></tr></thead>
        <tbody>
          <tr><td>هدرهای امنیتی و CSP</td><td>جلوگیری از XSS و تزریق کد</td><td><span class="badge badge-ok">فعال</span></td></tr>
          <tr><td>محافظ .htaccess</td><td>قفل فایل‌های حساس</td><td><span class="badge badge-ok">فعال</span></td></tr>
          <tr><td>رمز هش‌شده</td><td>پسوردها رمزنگاری می‌شن</td><td><span class="badge badge-ok">فعال</span></td></tr>
          <tr><td>قفل بعد از ۵ تلاش ناموفق</td><td>جلوگیری از Brute Force</td><td><span class="badge badge-ok">فعال</span></td></tr>
          <tr><td>ورود دو مرحله‌ای</td><td>لایه اضافه برای پنل ادمین</td><td><span class="badge badge-ok">فعال</span></td></tr>
          <tr><td>توکن CSRF</td><td>جلوگیری از جعل درخواست</td><td><span class="badge badge-ok">فعال</span></td></tr>
          <tr><td>آپلود امن تصویر</td><td>فقط فایل مجاز آپلود می‌شه</td><td><span class="badge badge-ok">فعال</span></td></tr>
          <tr><td>فرم تماس ضد اسپم</td><td>جلوگیری از ربات‌های اسپم</td><td><span class="badge badge-ok">فعال</span></td></tr>
          <tr><td>افزونه امنیتی وردپرس</td><td>برای نسخه وردپرس</td><td><span class="badge badge-ok">فعال</span></td></tr>
          <tr><td>صفحه وضعیت امنیت</td><td>مشاهده وضعیت کلی امنیت</td><td><span class="badge badge-ok">فعال</span></td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <section>
    <h2 class="section-title"><span class="num">⚠️</span> امنیت — مسئولیت صاحب سایت</h2>
    <ul class="checklist">
      <li>🔐 فعال‌سازی SSL (قفل سبز HTTPS)</li>
      <li>🔑 انتخاب رمز قوی برای پنل مدیریت</li>
      <li>💾 بکاپ منظم از سایت و دیتابیس</li>
      <li>🔄 به‌روزرسانی منظم PHP، وردپرس و افزونه‌ها</li>
    </ul>
    <div class="alert alert-danger" style="margin-top:16px">
      📌 این‌ها رو جدی بگیر! حتی امن‌ترین سایت هم با رمز ضعیف یا بدون بکاپ آسیب‌پذیره.
    </div>
  </section>

  <section>
    <h2 class="section-title"><span class="num">📦</span> خروجی‌ها</h2>
    <div class="table-wrap">
      <table>
        <thead><tr><th>فایل خروجی</th><th>توضیح</th></tr></thead>
        <tbody>
          <tr><td><code>site.html</code></td><td>نسخه تک‌فایل HTML</td></tr>
          <tr><td><code>site-host.zip</code></td><td>زیپ آماده آپلود روی هاست (HTML)</td></tr>
          <tr><td><code>site-php.zip</code></td><td>سایت PHP با پنل مدیریت + راهنما</td></tr>
          <tr><td><code>site-wordpress.zip</code></td><td>قالب وردپرس + افزونه امنیتی</td></tr>
          <tr><td><code>...-GUIDE-FA.txt</code></td><td>راهنمای فارسی هر نسخه</td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <section>
    <h2 class="section-title"><span class="num">❓</span> سوال‌های پرتکرار</h2>
    <div class="grid grid-2">
      <div class="tile"><h3>باید کد بنویسم؟</h3><p>نه! همه‌چیز ساخته می‌شه، فقط جواب سوال‌ها رو بده.</p></div>
      <div class="tile"><h3>می‌تونم بعداً تغییر بدم؟</h3><p>بله! بعد از ساخت، سوال‌های بهبود پرسیده می‌شه.</p></div>
      <div class="tile"><h3>کدوم نوع تحویل رو انتخاب کنم؟</h3><p>تست → HTML / هاست ساده → زیپ / پنل و دیتابیس → PHP / وردپرس → قالب.</p></div>
      <div class="tile"><h3>فونت‌ها رو خودم اضافه کنم؟</h3><p>بله، توی fonts/ بذار و دوباره زیپ و آپلود کن.</p></div>
      <div class="tile"><h3>عکس‌هام رو چطور اضافه کنم؟</h3><p>توی image/ بریز و دوباره زیپ کن.</p></div>
      <div class="tile"><h3>فایل راهنما کجاست؟</h3><p>برای هر نوع تحویل، یه فایل GUIDE-FA.txt کنار زیپ‌ها تحویل داده می‌شه.</p></div>
    </div>
  </section>

  <section>
    <h2 class="section-title"><span class="num">🔄</span> به‌روزرسانی</h2>
    <pre>zip -r site-builder.zip site-builder/

# سپس در Claude آپلود کن:
# Settings > Capabilities > Skills > Upload Skill</pre>
  </section>

  <section>
    <h2 class="section-title"><span class="num">🤝</span> مشارکت</h2>
    <div class="steps">
      <div class="step"><strong>Fork</strong> — پروژه را Fork کنید</div>
      <div class="step"><strong>Branch</strong> — یک Branch جدید بسازید (git checkout -b feature/AmazingFeature)</div>
      <div class="step"><strong>Commit</strong> — تغییرات را Commit کنید (git commit -m 'Add some AmazingFeature')</div>
      <div class="step"><strong>Push</strong> — Push کنید (git push origin feature/AmazingFeature)</div>
      <div class="step"><strong>Pull Request</strong> — یک Pull Request باز کنید</div>
    </div>
  </section>

  <section>
    <h2 class="section-title"><span class="num">⭐</span> خلاصه در یک نگاه</h2>
    <div class="flow">
      <span>📦 نصب</span><i>←</i>
      <span>🗣️ بگو «سایت بساز»</span><i>←</i>
      <span>🎨 جواب سوال‌ها</span><i>←</i>
      <span>🏗️ ساخت</span><i>←</i>
      <span>✨ بهبود</span>
    </div>
  </section>

  <footer>
    <p>📝 لایسنس: <strong style="color:var(--accent)">MIT</strong> — ساخته‌شده با ❤️ برای کاربران فارسی‌زبان</p>
    <p style="margin-top:8px">⭐ اگر این پروژه برایتان مفید بود، یک ستاره بدهید! ⭐</p>
  </footer>

</div>
</body>
</html>
