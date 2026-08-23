<?php
/**
 * Zarincode Theme Bootstrap
 *
 * @package Zarincode
 * @author  Zarincode
 * @version 3.36.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * ثابت‌های اصلی قالب
 */
define( 'ZC_VERSION', '3.36.0' );
define( 'ZC_DIR', trailingslashit( get_template_directory() ) );
define( 'ZC_URI', trailingslashit( get_template_directory_uri() ) );
define( 'ZC_INC', ZC_DIR . 'inc/' );
define( 'ZC_ASSETS', ZC_URI . 'assets/' );
define( 'ZC_PREFIX', 'zarincode_options' );

/**
 * لودر فایل‌های قالب
 *
 * @param array $files لیست فایل‌ها نسبت به پوشه inc.
 * @return void
 */
function zc_require( array $files ) {
	foreach ( $files as $file ) {
		$path = ZC_INC . $file . '.php';
		if ( file_exists( $path ) ) {
			require_once $path;
		} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Zarincode missing module: ' . $path ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
		}
	}
}

zc_require(
	array(
		// هسته.
		'helpers',
		'setup',
		'enqueue',
		'template-tags',
		'breadcrumb',
		'walker-menu',
		'walker-comment',

		// انواع محتوا.
		'cpt',
		'metabox',

		// پنل تنظیمات.
		'panel/config',
		'panel/redux-config',
		'panel/fallback-panel',

		// تعریف پیامک‌ها باید پیش از پنل باشد تا فیلدهای شخصی‌سازی در دسترس باشد.
		'modules/sms-messages',

		// ماژول‌ها.
		'modules/classic-editor',
		'modules/fonts',
		'modules/typography',
		'modules/jalali',
		'modules/services',
		'modules/details',
		'modules/sms-campaigns',
		'modules/messenger-bot',
		'modules/auth',
		'modules/sms-kavenegar',
		'modules/wallet',
		'modules/database',
		'modules/cashback',
		'modules/accounting',
		'modules/course',
		'modules/booking',
		'modules/ticket',
		'modules/ticket-lifecycle',
		'modules/chat',
		'modules/notification',
		'modules/announcements',
		'modules/editor',
		'modules/contracts',
		'modules/contract-chat',
		'modules/contract-payments',
		'modules/contract-workflow',
		'modules/rewards',
		'modules/coupons',
		'modules/ajax-search',
		'modules/ajax-actions',
		'modules/social-share',
		'modules/seo',
		'modules/performance',
		'modules/asset-optimizer',
		'modules/plugin-manager',
		'modules/subscriptions',
		'modules/newsletter',
		'modules/growth-platform',
		'modules/credential-manager',
		'modules/order-reversals',
		'modules/privacy',
		'modules/zarinpal',
		'modules/gateways',
		'modules/quiz',
		'modules/practice',
		'modules/attempts',
		'modules/image-optimizer',
		'modules/telegram',
		'modules/bale',
		'modules/user-panel',
		'modules/checkout-manager',
		'modules/invoice',
		'modules/affiliate',
		'modules/kpi-dashboard',
		'modules/backup',
		'modules/lifecycle',

		// ووکامرس.
		'woocommerce',

		// المنتور.
		'elementor/init',

		// مدیریت.
		'admin/admin',
		'admin/code-manager',
		'admin/demo-importer',
		'admin/dashboard-widget',
		'admin/chats-page',
		'admin/sms-page',
		'admin/sms-import',
	)
);

// صفحات مدرن داخلی (تماس، درباره، قوانین و ...).
zc_require( array( 'modules/modern-pages' ) );

// محتوای حقوقی باید همواره در دسترس باشد (نه فقط هنگام ایمپورت دمو).
if ( file_exists( ZC_DIR . 'demo/legal-content.php' ) ) {
	require_once ZC_DIR . 'demo/legal-content.php';
}

/**
 * خطاگیر فرانت‌اند — جلوگیری از «صفحه‌ی خالی» در صفحات داخلی
 * -------------------------------------------------------------------------
 * وقتی در رندر یک صفحه (مثلاً نوشته/پروژه/دوره) یک خطای fatal رخ می‌دهد،
 * PHP خروجی را متوقف می‌کند و نتیجه یک صفحه‌ی کاملاً خالی است. این بسیار
 * گمراه‌کننده است؛ کاربر نه متوجه خطا می‌شود و نه پیامی می‌بیند.
 *
 * این خطاگیر، در زمان بسته‌شدن اسکریپت (shutdown) بررسی می‌کند که آیا
 * خطای مرگبارِ تولیدِ صفحه رخ داده است یا نه. اگر خروجیِ تولیدشده پیش از
 * خطا تقریباً خالی باشد، یک پیام خوانا به‌جای صفحه‌ی خالی چاپ می‌کند تا
 * مدیر سایت بفهمد چه رخ داده و موضوع را رفع کند. این فقط برای بازدید
 * فرانت‌اند است و در پیشخوان کاری نمی‌کند.
 *
 * @return void
 */
function zc_frontend_error_guard() {
	// فقط در فرانت‌اند و فقط برای درخواست‌های HTML.
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || wp_doing_cron() || wp_doing_ajax() ) {
		return;
	}

	$err = error_get_last();

	// خطای بحرانی: مرگبار، ناتوان‌کننده یا سازگاری.
	if ( ! $err ) {
		return;
	}

	$fatal_types = array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR );
	if ( ! in_array( $err['type'], $fatal_types, true ) ) {
		return;
	}

	// اگر محتوا از قبل تولید شده، دیگر صفحه خالی نیست؛ فقط لاگ می‌کنیم.
	$len = 0;
	foreach ( ob_get_status( true ) as $buf ) {
		$len += isset( $buf['buffer'] ) ? strlen( (string) $buf['buffer'] ) : 0;
	}
	if ( $len > 256 ) {
		return;
	}

	if ( ! headers_sent() ) {
		header( 'HTTP/1.1 500 Internal Server Error' );
	}

	error_log( 'Zarincode frontend fatal: ' . $err['message'] . ' @ ' . $err['file'] . ':' . $err['line'] );

	echo '<div style="max-width:720px;margin:60px auto;padding:28px 30px;font-family:Tahoma,Arial,sans-serif;direction:rtl;text-align:right;background:#fff;border:1px solid #e2e4e7;border-inline-start:5px solid #d63638;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.06)">
		<h2 style="margin:0 0 10px;font-size:20px;color:#1d2327">خطا در نمایش صفحه</h2>
		<p style="margin:0;font-size:14px;line-height:2;color:#3c434a">مشکلی در تولید محتوای این صفحه رخ داد. این خطا ثبت شده و تیم پشتیبانی در حال بررسی آن است. لطفاً کمی بعد دوباره تلاش کنید.</p>
		<p style="margin:14px 0 0;font-size:12px;color:#646970;direction:ltr;text-align:left">' . esc_html( $err['file'] . ':' . $err['line'] ) . '</p>
	</div>';
}
add_action( 'shutdown', 'zc_frontend_error_guard', 1 );
