# Suru Food — وب‌سایت سه‌زبانه و پنل مدیریت فارسی

Laravel 13 · PHP 8.3 · MySQL/MariaDB (utf8mb4) · Blade + ES modules · GSAP/ScrollTrigger + Lenis · Vite

سایت برند Suru Food با سه زبان **فارسی (RTL)**، **English (LTR)** و **العربية (RTL)**، کاتالوگ محصولات آماده فروش آنلاین، مجله، سیستم دستور غذایی، صفحه‌ساز کنترل‌شده، کتابخانه رسانه و پنل مدیریت کاملاً فارسی.

> **محتوای واقعی:** هیچ محصول، متن یا تصویر ساختگی در کد یا Seeder وجود ندارد. تمام محتوا با دستور `php artisan suru:import` از surufood.ir منتقل می‌شود (یا از پنل وارد می‌شود). تا وقتی داده واقعی نباشد، هر سکشن به‌جای Placeholder اصلاً نمایش داده نمی‌شود.

## نصب

```bash
composer install
npm install && npm run build
cp .env.example .env && php artisan key:generate
# DB_* را تنظیم کنید (utf8mb4)
php artisan migrate --seed          # فقط ساختار: نقش‌ها، منوها، چیدمان صفحه اصلی، دسته‌بندی «چی دوست داری؟»
php artisan storage:link
php artisan suru:admin you@example.com   # ساخت مدیر کل
php artisan suru:import             # انتقال محتوای واقعی Suru Food
php artisan queue:work              # ساخت نسخه‌های WebP/AVIF تصاویر و ارسال ایمیل‌ها
```

پنل مدیریت: `/admin` — سایت: `/fa`، `/en`، `/ar`

### Production
- `CACHE_STORE=redis`، `QUEUE_CONNECTION=redis`، `SESSION_SECURE_COOKIE=true`، `APP_DEBUG=false`
- یک Supervisor برای `php artisan queue:work --tries=3` لازم است (پردازش تصویر، ایمیل، درون‌ریزی از پنل).
- `php artisan config:cache route:cache view:cache` پس از هر استقرار.
- AVIF فقط وقتی ساخته می‌شود که GD با پشتیبانی AVIF کامپایل شده باشد؛ در غیر این صورت WebP + JPEG/PNG ساخته می‌شود.

## انتقال محتوا از surufood.ir

`php artisan suru:import [--source=https://surufood.ir] [--only=products --only=categories ...]`

۱. اگر سایت منبع وردپرس/ووکامرس باشد: محصولات، قیمت‌ها (تبدیل ریال به تومان)، موجودی، دسته‌ها، ویژگی‌ها، تصاویر، صفحات (درباره ما/تماس به صفحات سیستمی نگاشت می‌شوند) و مقالات از REST API خوانده می‌شوند.
۲. در غیر این صورت: sitemap خزیده و داده‌های JSON-LD / OpenGraph صفحات محصول استخراج می‌شود.
۳. لوگو، فاوآیکن، شماره‌ها، ایمیل، آدرس و شبکه‌های اجتماعی از صفحه اصلی خوانده می‌شوند.
۴. تصاویر دانلود و در کتابخانه رسانه ذخیره می‌شوند (Hotlink نمی‌شوند)؛ تصاویر داخل متن مقالات نیز محلی می‌شوند. عکس‌های PNG شفاف خودکار «عکس بسته» محصول و رنگ غالب آن «رنگ تم» محصول می‌شود.
۵. هر رکورد در جدول `content_import_logs` با `source_url`، مقصد، `content_hash` و وضعیت ثبت می‌شود؛ اجرای مجدد فقط موارد تغییرکرده را به‌روزرسانی می‌کند.
۶. برای همه آدرس‌های قدیمی، ریدایرکت ۳۰۱ به آدرس جدید ساخته می‌شود.

محتوا به زبان فارسی وارد می‌شود؛ ترجمه EN/AR در «مدیریت ترجمه» پنل انجام می‌شود (نقش «مترجم»).

> در محیط توسعه این پروژه، دسترسی شبکه به surufood.ir وجود نداشت؛ importer با پاسخ‌های شبیه‌سازی‌شده‌ی API در تست‌ها بررسی شده و باید روی سروری که به سایت دسترسی دارد اجرا شود.

## معماری

| لایه | محل |
|---|---|
| Controllerهای نازک (سایت / پنل) | `app/Http/Controllers/Site`, `app/Http/Controllers/Admin` |
| Validation | `app/Http/Requests` |
| منطق کسب‌وکار | `app/Services` (Catalog, Search, TasteFinder, Cart, Media, Seo, PageRenderer, ContentHealth, Import\*) |
| Actionها | `app/Actions` (PlaceOrder, Admin\SaveProduct) |
| Enum / DTO | `app/Enums`, `app/DTOs` |
| Event / Listener / Observer | `ContentChanged` → پاک‌سازی کش گروهی + ثبت فعالیت مدیر |
| Job | ساخت نسخه تصاویر، ایمیل پیام و سفارش، درون‌ریزی |

- **ترجمه‌ها:** جدول جدا برای هر موجودیت (`*_translations`) با `locale, title, slug, short_description, description, seo_title, seo_description` و `search_text` نرمال‌شده. Slug هر زبان مستقل است.
- **جستجو:** یکسان‌سازی ی/ي، ک/ك، ه/ة، اعراب، نیم‌فاصله و ارقام فارسی/عربی (`App\Support\TextNormalizer`).
- **کش:** گروه‌های نسخه‌دار (`App\Support\CacheGroups`) روی هر Store (Redis/DB)؛ با تغییر محتوا Event-driven باطل می‌شوند. کلاس‌های مجاز برای unserialize در `config/cache.php` صریحاً تعریف شده‌اند.
- **سئو:** title/description/canonical/OG/Twitter، `hreflang` فقط برای زبان‌های واقعاً ترجمه‌شده، JSON-LD (Organization, WebSite, Product, BreadcrumbList, Article, Recipe, FAQPage)، sitemap index + sitemap جدا برای هر زبان/نوع، robots.txt.
- **RTL/LTR:** همه کامپوننت‌ها با Logical CSS (`margin-inline`, `inset-inline`, …) نوشته شده‌اند؛ یک CSS برای هر سه زبان.
- **Motion:** `resources/js/site/motion` — فقط transform/opacity/clip-path، Lazy-load پس از idle، غیرفعال با `prefers-reduced-motion`. متن فارسی/عربی فقط در سطح کلمه/خط شکسته می‌شود تا اتصال حروف حفظ شود.
- **رنگ‌ها:** Design Tokenها در `resources/css/site/tokens.css`؛ پالت برند از «تنظیمات › رنگ‌ها › استخراج از لوگو» روی لوگوی واقعی ساخته می‌شود و رنگ هر محصول/دسته از عکس بسته‌بندی خودش استخراج می‌شود.

## امنیت
CSRF، escape خروجی Blade + پاک‌سازی HTML محتوا (symfony/html-sanitizer)، Eloquent/Query Builder، Rate limit برای ورود/فرم‌ها/جستجو، تشخیص MIME از بایت‌ها و رد SVG دارای اسکریپت، Policy مبتنی بر نقش/دسترسی، ورود دومرحله‌ای TOTP با کدهای بازیابی، Honeypot در فرم‌ها، هدرهای امنیتی، گزارش فعالیت مدیران.

نقش‌ها: مدیر کل، مدیر محتوا، مدیر محصولات، مترجم، مدیر سفارش‌ها، مدیر سئو.

## تست

```bash
php artisan test
```
تست‌ها روی دیتابیس MySQL `suru_test` اجرا می‌شوند (`phpunit.xml`) و سایت عمومی، همه صفحات پنل، دسترسی‌ها، 2FA، آپلود رسانه، صفحه‌ساز، سبد خرید و importer را پوشش می‌دهند.

## موارد باز
- درگاه پرداخت آنلاین (زرین‌پال و …) پیاده نشده؛ ساختار `payments` و روش‌های «پرداخت در محل» و «کارت‌به‌کارت» فعال‌اند و فروش آنلاین به‌صورت پیش‌فرض خاموش است.
- ترجمه انگلیسی/عربی محتوای واقعی باید توسط مترجم انجام شود.
