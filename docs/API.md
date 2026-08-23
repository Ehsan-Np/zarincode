# Zarincode REST API — v1

مبنای مسیرها: `/wp-json/zarincode/v1`

## حساب کاربری

### `GET /me`
نیازمند cookie ورود وردپرس و هدر `X-WP-Nonce` است. پروفایل، آمار، دوره‌ها، پیشرفت و مدارک کاربر جاری را برمی‌گرداند.

### `GET /notifications`
نیازمند ورود و REST nonce است. تعداد و ده اعلان آخر کاربر جاری را برمی‌گرداند.

## استعلام مدرک

### `GET /certificate/{code}`
عمومی است. فقط مدارک موجود و ابطال‌نشده را معتبر اعلام می‌کند.

## لایسنس محصول

### `POST /license/verify`
پارامترها:

- `license_key`: کلید صادرشده
- `domain`: hostname بدون scheme و path
- `license_action`: یکی از `activate`، `deactivate` یا `check`
- `timestamp`: Unix timestamp حداکثر با اختلاف ۵ دقیقه
- `signature`: امضای HMAC-SHA256

رشتهٔ امضا:

```text
{license_action}|{domain}|{timestamp}
```

نمونهٔ PHP سمت محصول:

```php
$action = 'activate';
$domain = 'example.com';
$timestamp = time();
$signature = hash_hmac(
    'sha256',
    $action . '|' . $domain . '|' . $timestamp,
    $license_key
);
```

کلید لایسنس نباید در لاگ، URL یا کد سمت مرورگر قرار گیرد. درخواست باید از سرور محصول و روی HTTPS ارسال شود.

## ربات

### `POST /bot/{telegram|bale}?secret=...`
webhook پیام‌رسان است. secret الزامی و با مقایسهٔ timing-safe کنترل می‌شود.
