<?php
/**
 * سامانه‌ی پیامک‌های خودکار و کدهای تخفیف
 *
 * چهار سناریو:
 *  ۱. خوش‌آمدگویی به کاربر تازه (با کد تخفیف اختیاری)
 *  ۲. یادآوری پرداخت ناتمام (کاربر به درگاه رفته و برنگشته)
 *  ۳. بازگرداندن مشتری غیرفعال (بدون خرید در بازه‌ی مشخص، همراه کد تخفیف)
 *  ۴. پیامک آزاد به کاربران عضو (دستی، از پیشخوان)
 *
 * زمان‌بندی روی wp_cron انجام می‌شود و همان کران میزبان که در
 * docs/راهنمای-ربات-و-کران.md توضیح داده شده آن را پیش می‌برد.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   ۱. لایه‌ی چند-سامانه‌ای ارسال
   ========================================================================== */

/**
 * سامانه‌های پیامک پشتیبانی‌شده.
 *
 * افزودن سامانه‌ی تازه فقط با فیلتر `zc_sms_gateways` ممکن است؛ هر
 * سامانه باید یک callable در کلید `send` داشته باشد که
 * ( $mobile, $text ) بگیرد و true یا WP_Error برگرداند.
 *
 * @return array
 */
function zc_sms_gateways() {
	$gateways = array(
		'kavenegar' => array(
			'label' => __( 'کاوه‌نگار', 'zarincode' ),
			'send'  => 'zc_sms_send_kavenegar',
		),
	);

	/**
	 * فیلتر فهرست سامانه‌های پیامک.
	 *
	 * @param array $gateways سامانه‌ها.
	 */
	return apply_filters( 'zc_sms_gateways', $gateways );
}

/**
 * ارسال از طریق کاوه‌نگار (سامانه‌ی پیش‌فرض).
 *
 * @param string $mobile موبایل.
 * @param string $text   متن.
 * @return true|WP_Error
 */
function zc_sms_send_kavenegar( $mobile, $text ) {
	if ( ! function_exists( 'zc_sms' ) ) {
		return new WP_Error( 'zc_sms_missing', __( 'ماژول پیامک در دسترس نیست.', 'zarincode' ) );
	}

	$result = zc_sms()->send( $mobile, $text );

	return is_wp_error( $result ) ? $result : true;
}

/**
 * ارسال پیامک با سامانه‌ی فعال.
 *
 * تنها نقطه‌ی خروج پیامک در کل قالب؛ همه‌ی سناریوها از این عبور می‌کنند
 * تا گزارش‌گیری و محدودیت‌ها یکجا اعمال شود.
 *
 * @param string $mobile موبایل.
 * @param string $text   متن.
 * @param string $type   نوع پیام (برای گزارش).
 * @return bool
 */
function zc_sms_dispatch( $mobile, $text, $type = 'general' ) {
	$mobile = zc_sanitize_mobile( $mobile );

	// اگر متن از ویرایشگر HTML آمده باشد، به متن ساده برای پیامک تبدیل می‌شود.
	$text = zc_sms_plain_text( $text );

	if ( ! $mobile || '' === trim( (string) $text ) ) {
		return false;
	}

	$gateways = zc_sms_gateways();
	$active   = (string) zc_opt( 'zc_sms_gateway', 'kavenegar' );

	if ( ! isset( $gateways[ $active ] ) ) {
		$active = key( $gateways );
	}

	$handler = $gateways[ $active ]['send'] ?? '';

	if ( ! is_callable( $handler ) ) {
		return false;
	}

	$result = call_user_func( $handler, $mobile, $text );
	$ok     = ( true === $result );

	zc_sms_log( $mobile, $text, $type, $ok, is_wp_error( $result ) ? $result->get_error_message() : '' );

	/**
	 * پس از تلاش برای ارسال پیامک.
	 *
	 * @param string $mobile موبایل.
	 * @param string $text   متن.
	 * @param string $type   نوع.
	 * @param bool   $ok     موفق بود؟
	 */
	do_action( 'zc_sms_sent', $mobile, $text, $type, $ok );

	return $ok;
}

/**
 * تبدیل متن HTML (خروجی ویرایشگر) به متن ساده برای پیامک.
 *
 * @param string $html متن.
 * @return string
 */
function zc_sms_plain_text( $html ) {
	$html = (string) $html;

	// برچسب‌های خط جدید و پاراگراف.
	$html = preg_replace( '#<(br\s*/?|/p|/div|/li|/h[1-6])>#i', "\n", $html );
	$html = preg_replace( '#<li[^>]*>#i', '• ', $html );
	$html = wp_strip_all_tags( $html );
	$html = html_entity_decode( $html, ENT_QUOTES, 'UTF-8' );

	// حذف فاصله‌ها و خط‌های تکراری.
	$html = preg_replace( '/[ \t]+/', ' ', $html );
	$html = preg_replace( '/\n\s*\n+/', "\n", $html );

	return trim( $html );
}

/**
 * ثبت گزارش ارسال در جدول اختصاصی.
 *
 * @param string $mobile موبایل.
 * @param string $text   متن.
 * @param string $type   نوع.
 * @param bool   $ok     نتیجه.
 * @param string $error  خطا.
 * @return void
 */
function zc_sms_log( $mobile, $text, $type, $ok, $error = '' ) {
	global $wpdb;

	$table = $wpdb->prefix . 'zc_sms_log';

	if ( ! zc_table_exists( $table ) ) {
		return;
	}

	$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$table,
		array(
			'mobile'     => $mobile,
			'message'    => wp_trim_words( $text, 40, '…' ),
			'type'       => $type,
			'status'     => $ok ? 'sent' : 'failed',
			'error'      => $error,
			'created_at' => current_time( 'mysql' ),
		)
	);
}

/* ==========================================================================
   ۲. ساخت کد تخفیف اختصاصی
   ========================================================================== */

/**
 * ساخت یک کوپن ووکامرس مخصوص یک کاربر.
 *
 * کد یکتاست، فقط برای ایمیل همان کاربر معتبر است، یک‌بار مصرف است و
 * تاریخ انقضا دارد؛ پس ارسال پیامکی آن ریسک سوءاستفاده ندارد.
 *
 * @param int    $user_id شناسه کاربر.
 * @param int    $percent درصد تخفیف.
 * @param int    $days    اعتبار به روز.
 * @param string $prefix  پیشوند کد.
 * @return string کد ساخته‌شده یا رشته‌ی خالی.
 */
function zc_create_user_coupon( $user_id, $percent, $days = 30, $prefix = 'ZC' ) {
	if ( ! class_exists( 'WC_Coupon' ) ) {
		return '';
	}

	$percent = max( 1, min( 100, (int) $percent ) );
	$days    = max( 1, (int) $days );
	$user    = get_userdata( $user_id );

	if ( ! $user ) {
		return '';
	}

	// کد یکتا: پیشوند + چهار نویسه‌ی تصادفی خوانا.
	$alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
	$code     = '';

	do {
		$code = strtoupper( $prefix ) . '-';

		for ( $i = 0; $i < 5; $i++ ) {
			$code .= $alphabet[ wp_rand( 0, strlen( $alphabet ) - 1 ) ];
		}
	} while ( wc_get_coupon_id_by_code( $code ) );

	$coupon = new WC_Coupon();
	$coupon->set_code( $code );
	$coupon->set_discount_type( 'percent' );
	$coupon->set_amount( $percent );
	$coupon->set_individual_use( true );
	$coupon->set_usage_limit( 1 );
	$coupon->set_usage_limit_per_user( 1 );
	$coupon->set_date_expires( time() + ( $days * DAY_IN_SECONDS ) );
	$coupon->set_email_restrictions( array( $user->user_email ) );
	$coupon->set_description(
		sprintf(
			/* translators: %s: نام کاربر */
			__( 'کد تخفیف خودکار زرین کد برای %s', 'zarincode' ),
			$user->display_name
		)
	);

	$coupon->save();

	update_post_meta( $coupon->get_id(), '_zc_auto_coupon', 1 );
	update_post_meta( $coupon->get_id(), '_zc_coupon_user', (int) $user_id );

	return $code;
}

/* ==========================================================================
   ۳. جایگزینی متغیرها در متن پیام
   ========================================================================== */

/**
 * جایگزینی شورت‌کدهای متن پیامک.
 *
 * @param string $template قالب متن.
 * @param array  $vars     متغیرها.
 * @return string
 */
function zc_sms_parse( $template, $vars = array() ) {
	$defaults = array(
		'{site}'    => get_bloginfo( 'name' ),
		'{url}'     => home_url(),
		'{name}'    => '',
		'{code}'    => '',
		'{percent}' => '',
		'{days}'    => '',
		'{amount}'  => '',
		'{order}'   => '',
	);

	$vars = array_merge( $defaults, $vars );

	return trim( str_replace( array_keys( $vars ), array_values( $vars ), (string) $template ) );
}

/* ==========================================================================
   ۴. سناریوی یک: خوش‌آمدگویی به کاربر تازه
   ========================================================================== */

/**
 * ارسال پیامک خوش‌آمد پس از ثبت‌نام.
 *
 * @param int $user_id شناسه کاربر.
 * @return void
 */
function zc_sms_welcome_new_user( $user_id ) {
	if ( ! zc_opt( 'zc_sms_welcome_enable', false ) ) {
		return;
	}

	$mobile = zc_user_mobile( $user_id );

	if ( ! $mobile ) {
		return;
	}

	$user    = get_userdata( $user_id );
	$percent = (int) zc_opt( 'zc_sms_welcome_percent', 0 );
	$days    = (int) zc_opt( 'zc_sms_welcome_days', 30 );
	$code    = '';

	if ( $percent > 0 ) {
		$code = zc_create_user_coupon( $user_id, $percent, $days, 'WELCOME' );
	}

	$template = zc_sms_message( 'welcome' );

	$text = zc_sms_parse(
		$template,
		array(
			'{name}'    => $user ? $user->display_name : '',
			'{code}'    => $code,
			'{percent}' => zc_fa_num( $percent ),
			'{days}'    => zc_fa_num( $days ),
		)
	);

	// اگر کدی ساخته نشد، خطوط مربوط به کد از متن حذف می‌شوند.
	if ( ! $code ) {
		$lines = array_filter(
			explode( "\n", $text ),
			static function ( $line ) {
				return false === strpos( $line, 'کد تخفیف' ) && false === strpos( $line, 'اعتبار تا' );
			}
		);

		$text = trim( implode( "\n", $lines ) );
	}

	zc_sms_dispatch( $mobile, $text, 'welcome' );

	if ( $code ) {
		update_user_meta( $user_id, 'zc_welcome_coupon', $code );
	}

	update_user_meta( $user_id, 'zc_welcome_sms_at', current_time( 'mysql' ) );
}
add_action( 'user_register', 'zc_sms_welcome_new_user', 20 );

/**
 * موبایل کاربر از متای قالب یا صورتحساب ووکامرس.
 *
 * @param int $user_id شناسه.
 * @return string
 */
function zc_user_mobile( $user_id ) {
	$mobile = get_user_meta( $user_id, 'zc_mobile', true );

	if ( ! $mobile ) {
		$mobile = get_user_meta( $user_id, 'billing_phone', true );
	}

	return zc_sanitize_mobile( $mobile );
}

/* ==========================================================================
   ۵. سناریوی دو: یادآوری پرداخت ناتمام
   ========================================================================== */

/**
 * ثبت زمان رفتن کاربر به درگاه.
 *
 * سفارش در وضعیت «در انتظار پرداخت» می‌ماند؛ زمان را ذخیره می‌کنیم تا
 * کران بعداً بررسی کند که آیا پرداخت انجام شده یا نه.
 *
 * @param int $order_id شناسه سفارش.
 * @return void
 */
function zc_sms_mark_pending_payment( $order_id ) {
	if ( ! zc_opt( 'zc_sms_abandoned_enable', false ) || ! function_exists( 'wc_get_order' ) ) {
		return;
	}

	$order = wc_get_order( $order_id );

	if ( ! $order ) {
		return;
	}

	$order->update_meta_data( '_zc_gateway_at', time() );
	$order->save();
}
add_action( 'woocommerce_checkout_order_processed', 'zc_sms_mark_pending_payment', 20 );

/**
 * بررسی سفارش‌های پرداخت‌نشده و ارسال یادآوری.
 *
 * هر بار اجرای کران، سفارش‌هایی که از زمان رفتن به درگاه بیش از
 * فاصله‌ی تعیین‌شده گذشته و هنوز پرداخت نشده‌اند را پیدا و پیامک
 * می‌کند. هر سفارش تنها یک‌بار یادآوری می‌گیرد.
 *
 * @return int تعداد پیامک‌های ارسال‌شده.
 */
function zc_sms_run_abandoned_reminders() {
	if ( ! zc_opt( 'zc_sms_abandoned_enable', false ) || ! function_exists( 'wc_get_orders' ) ) {
		return 0;
	}

	$hours = max( 1, (int) zc_opt( 'zc_sms_abandoned_hours', 2 ) );
	$limit = time() - ( $hours * HOUR_IN_SECONDS );

	/*
	 * نکته‌ی مهم: wc_get_orders() شرط‌های meta_query از نوع
	 * EXISTS/NOT EXISTS را نادیده می‌گیرد (چه با HPOS چه بدون آن)، پس
	 * فیلترها را صریحاً در PHP اعمال می‌کنیم. تعداد بیشتری می‌گیریم و
	 * خودمان غربال می‌کنیم.
	 */
	$orders = wc_get_orders(
		array(
			'status'       => array( 'pending', 'failed' ),
			'limit'        => 120,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'date_created' => '>' . ( time() - ( 7 * DAY_IN_SECONDS ) ),
		)
	);

	$sent = 0;

	foreach ( $orders as $order ) {
		// هر سفارش تنها یک‌بار یادآوری می‌گیرد.
		if ( $order->get_meta( '_zc_abandoned_sms' ) ) {
			continue;
		}

		$at = (int) $order->get_meta( '_zc_gateway_at' );

		// هنوز به اندازه‌ی کافی نگذشته یا اصلاً به درگاه نرفته.
		if ( ! $at || $at > $limit ) {
			continue;
		}

		// سقف ۳۰ ارسال در هر اجرا تا صف پیامک اشباع نشود.
		if ( $sent >= 30 ) {
			break;
		}

		$mobile = zc_sanitize_mobile( $order->get_billing_phone() );

		if ( ! $mobile ) {
			$order->update_meta_data( '_zc_abandoned_sms', 'no-mobile' );
			$order->save();
			continue;
		}

		$template = zc_sms_message( 'abandoned' );

		$text = zc_sms_parse(
			$template,
			array(
				'{name}'   => $order->get_billing_first_name(),
				'{order}'  => $order->get_order_number(),
				'{amount}' => zc_fa_num( number_format( (float) $order->get_total() ) ),
				'{url}'    => $order->get_checkout_payment_url(),
			)
		);

		if ( zc_sms_dispatch( $mobile, $text, 'abandoned' ) ) {
			$sent++;
		}

		// چه موفق چه ناموفق، دوباره تلاش نمی‌کنیم تا اسپم نشود.
		$order->update_meta_data( '_zc_abandoned_sms', current_time( 'mysql' ) );
		$order->save();
	}

	return $sent;
}

/* ==========================================================================
   ۶. سناریوی سه: بازگرداندن مشتری غیرفعال
   ========================================================================== */

/**
 * یافتن کاربرانی که مدتی خرید نکرده‌اند و ارسال کد تخفیف.
 *
 * معیار «آخرین خرید» از سفارش‌های تکمیل‌شده‌ی ووکامرس گرفته می‌شود؛
 * کاربری که هرگز خرید نکرده هم پس از گذشت همان مدت از تاریخ عضویت،
 * مشمول می‌شود.
 *
 * @param int $batch حداکثر تعداد در هر اجرا.
 * @return int تعداد ارسال.
 */
function zc_sms_run_winback( $batch = 20 ) {
	if ( ! zc_opt( 'zc_sms_winback_enable', false ) ) {
		return 0;
	}

	$months  = max( 1, (int) zc_opt( 'zc_sms_winback_months', 3 ) );
	$percent = max( 1, (int) zc_opt( 'zc_sms_winback_percent', 30 ) );
	$days    = max( 1, (int) zc_opt( 'zc_sms_winback_days', 14 ) );
	$cutoff  = time() - ( $months * 30 * DAY_IN_SECONDS );

	/*
	 * فاصله‌ی حداقلی میان دو پیامک بازگشت به یک کاربر؛ بدون این شرط،
	 * هر اجرای کران دوباره برای همان فرد پیامک می‌فرستاد.
	 */
	$cooldown = time() - ( max( 1, (int) zc_opt( 'zc_sms_winback_cooldown', 90 ) ) * DAY_IN_SECONDS );

	$users = get_users(
		array(
			'number'  => $batch * 5,
			'orderby' => 'registered',
			'order'   => 'ASC',
		)
	);

	$sent = 0;

	foreach ( $users as $user ) {
		if ( $sent >= $batch ) {
			break;
		}

		// مدیران را هدف نمی‌گیریم.
		if ( user_can( $user->ID, 'manage_options' ) ) {
			continue;
		}

		/*
		 * meta_query در get_users برای DATETIME/NOT EXISTS قابل اتکا
		 * نیست، پس شرط را مستقیم بررسی می‌کنیم.
		 */
		$last_sms = get_user_meta( $user->ID, 'zc_winback_at', true );

		if ( $last_sms && strtotime( $last_sms ) > $cooldown ) {
			continue;
		}

		$mobile = zc_user_mobile( $user->ID );

		if ( ! $mobile ) {
			continue;
		}

		$last = zc_user_last_order_time( $user->ID );

		// اگر خریدی نداشته، مبنا تاریخ عضویت است.
		if ( ! $last ) {
			$last = strtotime( $user->user_registered );
		}

		if ( $last > $cutoff ) {
			continue;
		}

		$code = zc_create_user_coupon( $user->ID, $percent, $days, 'BACK' );

		if ( ! $code ) {
			continue;
		}

		$template = zc_sms_message( 'winback' );

		$text = zc_sms_parse(
			$template,
			array(
				'{name}'    => $user->display_name,
				'{code}'    => $code,
				'{percent}' => zc_fa_num( $percent ),
				'{days}'    => zc_fa_num( $days ),
			)
		);

		if ( zc_sms_dispatch( $mobile, $text, 'winback' ) ) {
			$sent++;
		}

		update_user_meta( $user->ID, 'zc_winback_at', current_time( 'mysql' ) );
		update_user_meta( $user->ID, 'zc_winback_coupon', $code );
	}

	return $sent;
}

/**
 * زمان آخرین سفارش تکمیل‌شده‌ی کاربر.
 *
 * @param int $user_id شناسه.
 * @return int زمان یونیکس یا صفر.
 */
function zc_user_last_order_time( $user_id ) {
	if ( ! function_exists( 'wc_get_orders' ) ) {
		return 0;
	}

	$orders = wc_get_orders(
		array(
			'customer_id' => $user_id,
			'status'      => array( 'completed', 'processing' ),
			'limit'       => 1,
			'orderby'     => 'date',
			'order'       => 'DESC',
		)
	);

	if ( ! $orders ) {
		return 0;
	}

	$date = $orders[0]->get_date_created();

	return $date ? $date->getTimestamp() : 0;
}

/* ==========================================================================
   ۷. زمان‌بندی
   ========================================================================== */

/**
 * ثبت رویدادهای کران.
 *
 * @return void
 */
function zc_sms_schedule_events() {
	if ( ! wp_next_scheduled( 'zc_sms_hourly' ) ) {
		wp_schedule_event( time() + 300, 'hourly', 'zc_sms_hourly' );
	}

	if ( ! wp_next_scheduled( 'zc_sms_daily' ) ) {
		wp_schedule_event( time() + 600, 'daily', 'zc_sms_daily' );
	}
}
add_action( 'init', 'zc_sms_schedule_events' );

/**
 * اجرای ساعتی: یادآوری پرداخت ناتمام.
 *
 * @return void
 */
function zc_sms_cron_hourly() {
	zc_sms_run_abandoned_reminders();
}
add_action( 'zc_sms_hourly', 'zc_sms_cron_hourly' );

/**
 * اجرای روزانه: بازگرداندن مشتری غیرفعال.
 *
 * @return void
 */
function zc_sms_cron_daily() {
	zc_sms_run_winback();
}
add_action( 'zc_sms_daily', 'zc_sms_cron_daily' );

/**
 * پاکسازی زمان‌بندی هنگام غیرفعال شدن قالب.
 *
 * @return void
 */
function zc_sms_clear_schedule() {
	wp_clear_scheduled_hook( 'zc_sms_hourly' );
	wp_clear_scheduled_hook( 'zc_sms_daily' );
}
add_action( 'switch_theme', 'zc_sms_clear_schedule' );

/* ==========================================================================
   ۸. ارسال دستی گروهی
   ========================================================================== */

/**
 * ارسال پیامک به گروهی از کاربران (AJAX پیشخوان).
 *
 * @return void
 */
function zc_ajax_sms_bulk() {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'zc_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ) );
	}

	$audience = isset( $_POST['audience'] ) ? sanitize_key( wp_unslash( $_POST['audience'] ) ) : 'all';
	$message  = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	$percent  = isset( $_POST['percent'] ) ? absint( $_POST['percent'] ) : 0;
	$days     = isset( $_POST['days'] ) ? absint( $_POST['days'] ) : 30;

	if ( '' === trim( $message ) ) {
		wp_send_json_error( array( 'message' => __( 'متن پیام را بنویسید.', 'zarincode' ) ) );
	}

	$args = array( 'number' => 200 );

	if ( 'customers' === $audience ) {
		$args['role__in'] = array( 'customer' );
	} elseif ( 'no_purchase' === $audience ) {
		$args['role__in'] = array( 'customer', 'subscriber' );
	}

	$users = get_users( $args );
	$sent  = 0;
	$skip  = 0;

	foreach ( $users as $user ) {
		$mobile = zc_user_mobile( $user->ID );

		if ( ! $mobile ) {
			$skip++;
			continue;
		}

		if ( 'no_purchase' === $audience && zc_user_last_order_time( $user->ID ) ) {
			$skip++;
			continue;
		}

		$code = $percent > 0 ? zc_create_user_coupon( $user->ID, $percent, $days, 'GIFT' ) : '';

		$text = zc_sms_parse(
			$message,
			array(
				'{name}'    => $user->display_name,
				'{code}'    => $code,
				'{percent}' => zc_fa_num( $percent ),
				'{days}'    => zc_fa_num( $days ),
			)
		);

		if ( zc_sms_dispatch( $mobile, $text, 'bulk' ) ) {
			$sent++;
		}
	}

	wp_send_json_success(
		array(
			'message' => sprintf(
				/* translators: 1: ارسال‌شده 2: رد‌شده */
				__( '%1$s پیامک ارسال شد، %2$s نفر رد شدند (بدون موبایل یا خارج از شرط).', 'zarincode' ),
				zc_fa_num( $sent ),
				zc_fa_num( $skip )
			),
			'sent'    => $sent,
		)
	);
}
add_action( 'wp_ajax_zc_sms_bulk', 'zc_ajax_sms_bulk' );

/**
 * اجرای دستی یک کارزار از پیشخوان.
 *
 * @return void
 */
function zc_ajax_sms_run_campaign() {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'zc_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ) );
	}

	$which = isset( $_POST['campaign'] ) ? sanitize_key( wp_unslash( $_POST['campaign'] ) ) : '';

	if ( 'abandoned' === $which ) {
		$n = zc_sms_run_abandoned_reminders();
	} elseif ( 'winback' === $which ) {
		$n = zc_sms_run_winback();
	} else {
		wp_send_json_error( array( 'message' => __( 'کارزار نامعتبر.', 'zarincode' ) ) );
	}

	wp_send_json_success(
		array(
			'message' => sprintf(
				/* translators: %s: تعداد */
				__( '%s پیامک ارسال شد.', 'zarincode' ),
				zc_fa_num( $n )
			),
		)
	);
}
add_action( 'wp_ajax_zc_sms_run_campaign', 'zc_ajax_sms_run_campaign' );

/**
 * ارسال پیامک آزمایشی.
 *
 * @return void
 */
function zc_ajax_sms_test() {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'zc_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ) );
	}

	$mobile = isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : '';
	$text   = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	if ( ! zc_sanitize_mobile( $mobile ) ) {
		wp_send_json_error( array( 'message' => __( 'شماره موبایل معتبر نیست.', 'zarincode' ) ) );
	}

	$ok = zc_sms_dispatch( $mobile, $text ? $text : __( 'پیام آزمایشی زرین کد.', 'zarincode' ), 'test' );

	if ( $ok ) {
		wp_send_json_success( array( 'message' => __( 'پیامک آزمایشی ارسال شد.', 'zarincode' ) ) );
	}

	wp_send_json_error( array( 'message' => __( 'ارسال ناموفق بود؛ تنظیمات سامانه پیامک را بررسی کنید.', 'zarincode' ) ) );
}
add_action( 'wp_ajax_zc_sms_test', 'zc_ajax_sms_test' );

/* ==========================================================================
   ۹. گزارش
   ========================================================================== */

/**
 * آمار پیامک‌های ارسال‌شده.
 *
 * @param int $days بازه به روز.
 * @return array
 */
function zc_sms_stats( $days = 30 ) {
	global $wpdb;

	$table = $wpdb->prefix . 'zc_sms_log';
	$empty = array(
		'total'  => 0,
		'sent'   => 0,
		'failed' => 0,
		'types'  => array(),
	);

	if ( ! zc_table_exists( $table ) ) {
		return $empty;
	}

	$since = gmdate( 'Y-m-d H:i:s', time() - ( max( 1, (int) $days ) * DAY_IN_SECONDS ) );

	$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
		$wpdb->prepare(
			"SELECT type, status, COUNT(*) AS n FROM {$wpdb->prefix}zc_sms_log WHERE created_at >= %s GROUP BY type, status", // phpcs:ignore
			$since
		)
	);

	if ( ! $rows ) {
		return $empty;
	}

	$out = $empty;

	foreach ( $rows as $row ) {
		$n              = (int) $row->n;
		$out['total']  += $n;
		$out[ 'sent' === $row->status ? 'sent' : 'failed' ] += $n;

		if ( ! isset( $out['types'][ $row->type ] ) ) {
			$out['types'][ $row->type ] = 0;
		}

		$out['types'][ $row->type ] += $n;
	}

	return $out;
}

/**
 * آخرین ردیف‌های گزارش پیامک.
 *
 * @param int $limit تعداد.
 * @return array
 */
function zc_sms_recent( $limit = 30 ) {
	global $wpdb;

	$table = $wpdb->prefix . 'zc_sms_log';

	if ( ! zc_table_exists( $table ) ) {
		return array();
	}

	return (array) $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
		$wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}zc_sms_log ORDER BY id DESC LIMIT %d", // phpcs:ignore
			max( 1, (int) $limit )
		)
	);
}

/**
 * برچسب فارسی نوع پیام.
 *
 * @param string $type نوع.
 * @return string
 */
function zc_sms_type_label( $type ) {
	$labels = array(
		'welcome'   => __( 'خوش‌آمدگویی', 'zarincode' ),
		'abandoned' => __( 'یادآوری پرداخت', 'zarincode' ),
		'winback'   => __( 'بازگشت مشتری', 'zarincode' ),
		'bulk'      => __( 'ارسال گروهی', 'zarincode' ),
		'test'      => __( 'آزمایشی', 'zarincode' ),
		'otp'       => __( 'کد ورود', 'zarincode' ),
		'order'     => __( 'سفارش', 'zarincode' ),
		'general'   => __( 'عمومی', 'zarincode' ),
	);

	return $labels[ $type ] ?? $type;
}

/**
 * گزارش دقیق هزینه‌ی پیامک‌ها.
 * هزینه بر اساس طول هر پیام و قیمت هر بخش (zc_sms_cost_per_sms) برآورد می‌شود.
 *
 * @param int $days تعداد روز.
 * @return array {
 *     int   $total_cost  هزینه‌ی کل (ریال).
 *     int   $total_sent  تعداد ارسال‌شده.
 *     array $by_type     هزینه به تفکیک نوع.
 *     array $count_type  تعداد به تفکیک نوع.
 * }
 */
function zc_sms_cost_report( $days = 30 ) {
	global $wpdb;

	$empty = array(
		'total_cost' => 0,
		'total_sent' => 0,
		'by_type'    => array(),
		'count_type' => array(),
	);

	$table = $wpdb->prefix . 'zc_sms_log';

	if ( ! function_exists( 'zc_table_exists' ) || ! zc_table_exists( $table ) ) {
		return $empty;
	}

	$since = gmdate( 'Y-m-d H:i:s', time() - ( max( 1, (int) $days ) * DAY_IN_SECONDS ) );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT type, message, COUNT(*) AS n FROM {$wpdb->prefix}zc_sms_log WHERE status='sent' AND created_at >= %s GROUP BY type, message",
			$since
		)
	);

	$out = $empty;

	foreach ( $rows as $row ) {
		$cost = 0;
		if ( function_exists( 'zc_sms' ) ) {
			$cost = zc_sms()->estimate_cost( (string) $row->message, (int) $row->n );
		}

		$n = (int) $row->n;
		$out['total_cost']  += $cost;
		$out['total_sent']  += $n;
		$out['by_type'][ $row->type ]    = (float) ( $out['by_type'][ $row->type ] ?? 0 ) + $cost;
		$out['count_type'][ $row->type ] = (int) ( $out['count_type'][ $row->type ] ?? 0 ) + $n;
	}

	return $out;
}
