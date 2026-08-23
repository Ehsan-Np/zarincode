# حسابرسی زرین کد ۳.۳۸ — قالب و افزونه Core

تاریخ: ۲ شهریور ۱۴۰۵ · نسخه بررسی‌شده: `3.38.0` (`09b7255`)  
رفع‌ها در `3.38.1`: C1/C2، H1–H8 (کلید کاوه‌نگار طبق API رسمی در مسیر می‌ماند + فیلتر پروکسی)، M1/M2/M4/M5/M7–M12، قابلیت CPT، نقش مدرس/پشتیبان، schema در Core.  
دامنه: قالب (`inc/modules/*`، `inc/cpt.php`، `functions.php`) و `companion-plugin/zarincode-core/zarincode-core.php`

این سند یافته‌های دقیق است، نه چک‌لیست عمومی. هر مورد مسیر فایل دارد.

---

## ۱. جمع‌بندی اجرایی

قالب یک **مونولیت کامل LMS + فروشگاه + کیف پول + پشتیبانی + قرارداد + آزمون + ربات** است. افزونهٔ «Zarincode Core» برخلاف نام و توضیحاتش **لایهٔ داده/امنیت نیست**؛ فقط وقتی قالب غیرفعال باشد CPT/taxonomy را ثبت می‌کند (~۷۰ خط).

امنیت در ۳.۳۸ نسبت به نسخه‌های قبلی بهتر شده (نانس، rate-limit، HMAC لایسنس، قفل کیف پول، hardening هدرها، سندباکس کد با allowlist). با این حال چند حفرهٔ **قابل سوءاستفاده از اینترنت** باقی است و چند قابلیت محصول ناقص یا گمراه‌کننده است.

| شدت | تعداد تقریبی |
|---|---|
| Critical | ۲ |
| High | ۸ |
| Medium | ۱۲ |
| Low / محصول | ۱۵+ |

---

## ۲. معماری — مهم‌ترین بدهی محصول

### ۲.۱ Core لایهٔ پایدار نیست

`companion-plugin/zarincode-core/zarincode-core.php`

- فقط CPT/tax را وقتی قالب فعال نیست ثبت می‌کند.
- جداول (`zc_enrollments`, `zc_transactions`, `zc_licenses`, …)، REST، کیف پول، ثبت‌نام، webhook، OTP، هیچ‌کدام در افزونه نیستند.
- اگر قالب عوض شود: محتوا در دیتابیس می‌ماند ولی **ثبت‌نام، پخش درس، پرداخت، تیکت، کیف پول و پنل از کار می‌افتند**.
- توضیح افزونه («لایهٔ پایدار داده‌ها») با واقعیت کد در تضاد است و برای مشتری گمراه‌کننده است.

**اقدام لازم:** منطق کسب‌وکار (جداول، enrollment، wallet، licenses، REST، auth OTP) باید به Core منتقل شود؛ قالب فقط UI باشد.

### ۲.۲ CPT بدون قابلیت اختصاصی

`inc/cpt.php`

- `zc_course`, `zc_lesson`, `zc_tutorial` عمومی + `show_in_rest => true` با capability پیش‌فرض `post`.
- `zc_ticket` و `zc_request` هم `capability_type => post`.
- نقش Author/Editor با `edit_posts` به تیکت و درخواست مشتری دسترسی دارد (نه فقط مدیر).

`inc/modules/ticket.php` خطوط مالکیت:

```php
if ( (int) $ticket->post_author !== $user_id && ! current_user_can( 'edit_posts' ) )
```

هر کاربری با `edit_posts` (نویسنده، ویرایشگر، مدرس اگر این قابلیت را بگیرد) می‌تواند به **همهٔ تیکت‌ها** پاسخ دهد.

`inc/modules/instructor.php` نقش مدرس را با `zc_manage_own_courses` یا صرفاً «نویسنده بودن یک دوره» تشخیص می‌دهد؛ نقش و قابلیت جدا ثبت نشده.

**اقدام لازم:** `capability_type` اختصاصی (`zc_ticket`, `zc_course`)، `map_meta_cap`، نقش `zc_instructor` با قابلیت محدود به دورهٔ خودش.

### ۲.۳ درس به‌صورت CPT عمومی

`inc/cpt.php` — `zc_lesson` با `public => true` و REST. محتوای پولی اگر در پست درس ذخیره شود از URL مستقیم قابل خواندن است. کلاس درس فعلی از متای `_zc_curriculum` روی دوره استفاده می‌کند (`inc/modules/classroom.php`)؛ CPT درس عملاً موازی و خطر نشت است.

---

## ۳. امنیت

### Critical

#### C1. تزریق آرگومان `WP_Query` در «بارگذاری بیشتر»

`inc/modules/ajax-search.php` → `zc_handle_load_more`  
`assets/js/main.js` (~۱۳۲۶) `query: btn.dataset.query`

هر مهمان (`nopriv`) می‌تواند JSON دلخواه بفرستد:

- `post_type`: `zc_ticket`, `zc_contract`, `shop_order`, `attachment`
- `post_status`: `private` / `any` / `draft`
- `meta_query`, `author`, `p`, `posts_per_page`

نتیجه داخل `template-parts/content/card-*.php` رندر می‌شود. تیکت‌ها `post_status=publish` دارند؛ حتی بدون تغییر status هم قابل فهرست‌شدن‌اند.

**رفع:** فقط کلیدهای سفید (`post_type` از فهرست عمومی، `paged`, `posts_per_page` سقف‌دار). هرگز `query` خام کلاینت را به `WP_Query` ندهید. `post_status` را سخت `publish` کنید.

#### C2. فرم تماس = رلهٔ ایمیل باز

`inc/modules/ajax-actions.php` → `zc_ajax_contact_submit`

```php
$to = isset( $_POST['receiver'] ) ? sanitize_email( ... ) : '';
$to = $to ? $to : zc_opt( 'zc_contact_email', get_option( 'admin_email' ) );
```

مهمان می‌تواند `receiver` را هر ایمیلی بگذارد. سایت تبدیل به اسپم‌رله می‌شود. Rate-limit ۵/ساعت این را متوقف نمی‌کند.

**رفع:** گیرنده فقط از تنظیمات/allowlist؛ فیلد `receiver` را حذف یا با شناسهٔ دپارتمان داخلی نگاشت کنید.

### High

#### H1. نوع پست در جستجوی زنده بدون allowlist

`inc/modules/ajax-search.php` → `zc_handle_ajax_search`

اگر `type !== 'all'` همان `sanitize_key` مستقیم به `post_type` می‌رود. مهمان می‌تواند `zc_ticket` / `zc_contract` / `product` پیش‌نویس را جستجو کند (بسته به وضعیت انتشار).

**رفع:** تقاطع با همان آرایهٔ `$types` خط ۴۳.

#### H2. آپلود پیوست تیکت: ZIP/RAR در کتابخانهٔ عمومی

`inc/modules/ticket.php` → `zc_handle_ticket_upload`

پسوندهای مجاز: `jpg,jpeg,png,gif,pdf,zip,rar,txt,doc,docx`. فقط پسوند چک می‌شود، نه MIME/محتوا. `media_handle_upload` فایل را در `uploads` عمومی می‌گذارد. ZIP مخرب + URL قابل حدس = میزبانی بدافزار روی دامنهٔ شما.

**رفع:** ذخیره در پوشهٔ غیرقابل‌وب (`wp-content/uploads/zc-private`) با `index.php` + `.htaccess` Deny؛ سرو از طریق پروکسی با بررسی مالکیت. ZIP/RAR را پیش‌فرض خاموش کنید.

#### H3. آپلود ویرایشگر و آواتار بدون بررسی جادویی فایل

- `inc/modules/editor.php` → `zc_ajax_editor_upload`: `wp_check_filetype` فقط روی **نام فایل**. هر کاربر واردشده می‌تواند به Media Library بنویسد. بدون سقف نرخ.
- `inc/modules/ajax-actions.php` → `zc_ajax_update_profile`: آواتار بدون allowlist پسوند.

`p[style]` در `zc_editor_allowed_html` اجازهٔ `style` روی پاراگراف می‌دهد (CSS injection در پنل پشتیبانی).

**رفع:** `wp_check_filetype_and_ext` + `finfo`؛ نقش آپلود جدا؛ rate-limit؛ حذف `style` از kses.

#### H4. کلید API کاوه‌نگار در URL

`inc/modules/sms-kavenegar.php` — درخواست به `https://api.kavenegar.com/v1/{APIKEY}/...`

کلید در access log سرور، پروکسی، و Referer نشت می‌کند.

**رفع:** هدر Authorization یا بدنهٔ POST طبق API فعلی؛ هرگز کلید در path لاگ‌شده.

#### H5. حالت تست OTP کد را برمی‌گرداند

`inc/modules/sms-kavenegar.php` حدود خط ۳۳۲:

```php
return array( 'test' => true, 'code' => $code );
```

اگر لایهٔ AJAX این آرایه را در JSON بگذارد (یا بعداً کسی `wp_send_json_success($result)` کند)، OTP لو می‌رود. الان AJAX فقط پیام می‌فرستد؛ این یک **بمب ساعتی** است.

OTP پنج‌رقمی است → فضای جستجو ۱۰۰٬۰۰۰. Rate-limit IP+موبایل هست، ولی در برابر بات‌نت توزیع‌شده ضعیف است.

**رفع:** هرگز کد را در پاسخ نگذارید؛ ۶ رقم + backoff نمایی؛ حالت تست فقط در `WP_DEBUG` و فقط `error_log`.

#### H6. ویدیوی کلاس: iframe برای هر HTTPS

`inc/modules/classroom.php` → `zc_video_embed_data`  
اگر URL یوتیوب/آپارات/mp4 نباشد:

```php
return array( 'type' => 'iframe', 'src' => $url );
```

هر `https://` داخل iframe کلاس درس می‌رود (فیشینگ، crypto-miner). مدرس/ادمین اگر XSS یا حساب دزدیده‌شده داشته باشد، دانشجو صفحهٔ مهاجم را داخل دامنه می‌بیند.

**رفع:** allowlist میزبان (youtube, aparat, aparat.com, vimeo, دامنهٔ خودتان). فایل خام را از URL امضاشده سرو کنید نه لینک دائمی عمومی.

#### H7. به‌روزرسانی قالب از هر بستهٔ HTTPS

`inc/modules/updates.php`

لایسنس به‌صورت **query string GET** به endpoint سفارشی می‌رود (لاگ سرور مقابل). `package` هر URL با scheme https است؛ اگر endpoint یا DNS تصاحب شود، وردپرس ZIP مخرب را به‌عنوان آپدیت قالب نصب می‌کند.

**رفع:** allowlist میزبان بسته؛ لایسنس در هدر؛ امضای ed25519 روی ZIP؛ مقایسه checksum.

#### H8. نشست چت مهمان فقط کوکی

`inc/modules/chat.php` — کوکی `zc_chat_sid` (HttpOnly+Secure+Lax — خوب است) ولی به IP/UA گره نخورده. دزدیدن کوکی = تاریخچهٔ چت. `nopriv` برای send/history باز است (عمدی برای ویجت مهمان).

Staff chat با `edit_posts` کنترل می‌شود — همان مشکل قابلیت تیکت.

### Medium

| # | موضوع | مسیر |
|---|---|---|
| M1 | Fatal فرانت مسیر فایل را به بازدیدکننده نشان می‌دهد | `functions.php` `zc_frontend_error_guard` خط ۲۰۸ |
| M2 | موجودی کیف پول در usermeta است نه جمع ledger | `inc/modules/wallet.php` `zc_wallet_balance` |
| M3 | توکن ربات تلگرام/بله در URL API | `inc/modules/messenger-bot.php` `sprintf( $config['api'], $token, $method )` |
| M4 | Secret وب‌هوک ربات در query string (`?secret=`) | `zc_bot_webhook_url` — در لاگ وب‌سرور می‌ماند |
| M5 | کد اتصال ربات ۸ کاراکتر، بدون انقضا | `zc_get_connect_code` |
| M6 | REST `/me` ایمیل+موبایل+مدارک را برمی‌گرداند | `inc/modules/growth-platform.php` `zc_rest_me` |
| M7 | REST تیکت متای غلط می‌خواند (`_zc_ticket_status` به‌جای `_zc_status`) | `inc/modules/rest-platform.php` `zc_rest_tickets` |
| M8 | ثبت webhook خروجی فقط `manage_options` است ولی SSRF به هر HTTPS | `zc_rest_register_webhook` |
| M9 | اقساط: کارمزد منفی روی **کل سبد** نه فقط دوره | `inc/modules/installments.php` |
| M10 | لایسنس در یادداشت سفارش ووکامرس نوشته می‌شود | `zc_license_on_order` |
| M11 | CSP وجود ندارد؛ فقط چند هدر پایه | `inc/modules/security-hardening.php` |
| M12 | حریم خصوصی: چت مهمان، پیوست تیکت، رویدادهای جستجو در export نیستند | `inc/modules/privacy.php` |

### نکات امنیتی که درست پیاده شده‌اند (برای تعادل)

- لایسنس: HMAC + پنجرهٔ زمانی ۳۰۰ث + `GET_LOCK` + rate ۳۰/دقیقه (`growth-platform.php`).
- کیف پول: `GET_LOCK` هنگام تغییر موجودی.
- آزمون گام‌به‌گام: state سمت سرور + توکن یک‌بارمصرف (`quiz.php`).
- اجرای کد: allowlist میزبان Wandbox + فیلتر الگوهای خطرناک + سقف طول/نرخ (`security-hardening.php`, `quiz.php`).
- وب‌هوک ربات بدون secret → ۴۰۳.
- هدرهای nosniff / SAMEORIGIN / HSTS.
- بکاپ: gzip سپس encrypt؛ کلید جدا از `AUTH_KEY`.

---

## ۴. کامل‌بودن قابلیت‌ها — کارهایی که باید انجام شود

### ۴.۱ محصول آموزشی

| شکاف | توضیح | مسیر مرتبط |
|---|---|---|
| ویدیوی خصوصی نیست | لینک mp4 ثابت است؛ دانشجو می‌تواند URL را کپی و پخش کند | `classroom.php`, `zc_video_embed_data` |
| DRM / URL امضاشده نیست | برای دورهٔ پولی ضروری است (Cloudflare Stream, Bunny, S3+CloudFront) | — |
| پیشرفت قابل جعل است | کلاینت `seconds`/`complete` می‌فرستد؛ برای iframe فقط ۳۰ ثانیه کافی است | `zc_ajax_save_watch`, `zc_rest_progress_post` |
| مدرس نمی‌تواند دوره بسازد | تب مدرس فقط آمار است؛ CRUD فرانت نیست | `instructor.php` (۱۰۳ خط) |
| کمیسیون مدرس نیست | درآمد از `SUM(price)` ثبت‌نام است نه تسویه | همان |
| گواهی بدون آزمون اگر سوال خالی باشد | `zc_quiz_passed` بدون سوال `true` برمی‌گرداند | `quiz.php` |
| CPT درس و curriculum دو منبع حقیقت‌اند | سردرگمی محتوا و نشت احتمالی | `cpt.php` + `metabox.php` |

### ۴.۲ مالی

- کیف پول: موجودی usermeta می‌تواند با جدول تراکنش واگرا شود. منبع حقیقت باید `SUM(transactions WHERE status=done)` باشد با قفل.
- بازگشت وجه سفارش به کیف پول (`order-reversals.php`) باید با ledger یکسان باشد — الان دو مسیر موازی است.
- حسابداری (`accounting.php`) گزارش است نه دفترکل دوطرفه.
- درگاه‌ها غیر از زرین‌پال نازک‌اند؛ idempotency کال‌بک را در هر درگاه جدا بررسی کنید (`zarinpal.php`, `gateways.php`).
- اقساط روی کل سبد اعمال می‌شود → کاربر محصول فیزیکی + دوره را با کارمزد دوره ارزان می‌خرد.

### ۴.۳ پشتیبانی و ارتباط

- تیکت روی CPT وردپرس است نه جدول؛ مقیاس و SLA روی postmeta کند است (`zc_user_ticket_stats` همهٔ تیکت‌ها را با `posts_per_page=-1` می‌کشد).
- فرم تماس تیکت یتیم بدون نویسنده می‌سازد.
- چت زنده صف/وب‌سوکت ندارد؛ polling AJAX.
- صف اعلان ربات حداکثر ۵۰ آیتم در option (`zc_notify_queue`) — در سایت شلوغ گم می‌شود.

### ۴.۴ پلتفرم / API

- REST سطح اپ موبایل نیست: ساخت تیکت، پرداخت، ثبت‌نام دوره از REST نیست.
- Application Password / JWT تعریف نشده؛ کلاینت خارجی فقط cookie + nonce وردپرس.
- `zarincode/v1/courses` عمومی است (قیمت و تعداد درس) — قابل قبول؛ سرفصل و ویدیو نباید بعداً اضافه شود بدون auth.
- وب‌هوک خروجی فقط `order.paid` و `course.completed`؛ حذف/لیست از REST نیست.

### ۴.۵ عملیاتی

- تست‌ها دود/رگرسیون امنیتی‌اند (`tests/`)؛ PHP روی PATH این محیط نیست و phpunit اینجا اجرا نمی‌شود.
- تست واحد برای wallet lock، callback زرین‌پال، enrollment، OTP وجود ندارد.
- ایمپورتر دمو بزرگ است؛ روی سایت زنده خطر بازنویسی محتوا.
- PWA پایه است (`pwa.php`)؛ آفلاین کلاس درس و ویدیو ندارد.
- ویزارد نصب (`setup-wizard.php`) صفحات را می‌سازد ولی سلامت درگاه/اس‌ام‌اس را verify نمی‌کند.

---

## ۵. باگ‌های عملکردی مشخص

1. **REST تیکت وضعیت خالی برمی‌گرداند** — متا `_zc_ticket_status` وجود ندارد؛ واقعی `_zc_status` است.  
   `inc/modules/rest-platform.php` `zc_rest_tickets`
2. **URL کلاس درس بازنویسی با query جور نیست** — `zc_classroom_url` پارامتر `lesson` می‌گذارد؛ rewrite از `zc_lesson` می‌خواند. لینک‌های pretty ممکن است جلسهٔ اول را باز کنند.  
   `classroom.php` `zc_classroom_url` در برابر `zc_classroom_template_redirect`
3. **شرط مدرک آزمون شکننده است** — `remove_action( 'zc_course_completed', 'zc_issue_certificate', 10 )` اگر اولویت hook عوض شود کار نمی‌کند.
4. **جستجو نوع نامعتبر را به WP_Query می‌دهد** — ممکن است خطای PHP یا نتیجهٔ خالی silently.

---

## ۶. اولویت اقدام (پیشنهاد اسپرینت)

### فوری (قبل از هر سایت عمومی)

1. بستن `zc_handle_load_more` (C1)
2. حذف/قفل `receiver` فرم تماس (C2)
3. Allowlist `type` جستجو (H1)
4. حذف `file:line` از صفحهٔ خطای عمومی (M1)
5. حذف `code` از پاسخ تست OTP (H5)

### اسپرینت بعد

6. پیوست تیکت خصوصی + MIME واقعی (H2/H3)
7. قابلیت CPT و نقش مدرس/پشتیبان (۲.۲)
8. Allowlist iframe ویدیو (H6)
9. موجودی کیف پول از ledger (M2)
10. کلید API از URL خارج شود (H4, M3)

### بدهی معماری (نسخهٔ ۴.x)

11. انتقال جداول + enrollment + wallet + licenses به Zarincode Core
12. ویدیوی امضاشده / استریم خصوصی
13. REST اپ (ثبت‌نام، تیکت، پرداخت) با توکن
14. پنل مدرس واقعی (ایجاد دوره، دانشجو، تسویه)
15. تست واحد مالی و کال‌بک پرداخت

---

## ۷. آنچه این حسابرسی عمداً پوشش نداده

- کیفیت UI/UX و کپی فارسی
- عملکرد فرانت (حجم CSS/JS) جز اشاره به polling چت
- صحت حقوقی قراردادها (`contracts.php`)
- زیرساخت هاست (WAF، object storage)

بررسی دستی نفوذ روی سایت زنده انجام نشده؛ یافته‌ها از خواندن کد است.
