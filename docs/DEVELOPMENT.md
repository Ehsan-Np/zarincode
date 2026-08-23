# راهنمای توسعه و انتشار زرین کد

## پیش‌نیاز

- PHP 7.4 تا 8.3
- Composer 2
- Node.js 20 یا 22
- Docker برای `wp-env`

## کنترل کیفیت

```bash
composer install
composer lint
composer test
composer phpcs
composer phpstan
npm test
```

## محیط یکپارچه محلی

```bash
npm install
npm run env:start
```

سپس قالب، WooCommerce، Elementor، Redux و افزونه همراه Zarincode Core در محیط WordPress 6.9 در دسترس‌اند.

```bash
npm run env:stop
npm run env:clean
```

## سناریوهای الزامی پیش از انتشار

1. فعال‌سازی قالب روی دیتابیس خالی و اجرای migration.
2. ارتقا از 3.35 به 3.36 و کنترل انتقال خبرنامه/مدارک.
3. خرید دوره در وضعیت processing و completed و کنترل عدم ثبت دوباره.
4. پرداخت ترکیبی کیف پول و درگاه: موفق، لغو، timeout، retry و refund.
5. خرید/تمدید/ارتقا/تنزل/هدیه اشتراک و refund کامل/جزئی.
6. آزمون challenge و all با تلاش برای تغییر payload و ترتیب سؤال‌ها.
7. ساخت، ارسال، نگهداری و restore آزمایشی بکاپ gzip.
8. صدور، فعال‌سازی، check، deactivate، suspend و revoke لایسنس.
9. صدور و ابطال گواهینامه و REST استعلام.
10. کمپین خبرنامه بیش از ۵۰۰۰ مخاطب و توقف/ادامه صف.
11. WooCommerce HPOS روشن و خاموش.
12. Redux نصب‌شده و پنل fallback بدون Redux.

## ساخت بسته انتشار

```bash
scripts/build-release.sh
```

خروجی‌ها در `.release/` ساخته می‌شوند:

- `zarincode-{version}.zip`
- `zarincode-core-{version}.zip`

بسته افزونه Core باید همراه قالب منتشر و به‌عنوان افزونه الزامی نصب شود.
