<?php
/**
 * ماژول مدیریت و شخصی‌سازی پیامک‌ها
 * ---------------------------------------------------------------------------
 * این ماژول یک سیستم متمرکز برای مدیریت «متن همه‌ی پیامک‌ها» فراهم می‌کند.
 * هر پیامک یک کلید (key) و یک متن پیش‌فرض دارد. کاربر می‌تواند از پنل
 * تنظیمات، متن هر پیامک را جداگانه شخصی‌سازی کند.
 *
 * متغیرهای قابل استفاده در متن پیامک‌ها:
 *   {name}    نام کاربر
 *   {site}    نام سایت
 *   {url}     نشانی سایت
 *   {code}    کد تایید / تخفیف
 *   {order}   شماره سفارش
 *   {amount}  مبلغ
 *   {date}    تاریخ
 *   {time}    ساعت
 *   {percent} درصد تخفیف
 *   {days}    روز اعتبار
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * فهرست کامل پیامک‌های قالب با متن‌های پیش‌فرض.
 *
 * هر ردیف: 'key' => array( 'title' => عنوان، 'default' => متن پیش‌فرض ).
 *
 * @return array
 */
function zc_sms_messages() {
	return array(
		'otp'           => array( 'title' => __( 'کد تایید ورود (بدون الگو)', 'zarincode' ), 'default' => 'کد ورود شما به {site}: {code}' ),
		'order_new'     => array( 'title' => __( 'ثبت سفارش جدید', 'zarincode' ), 'default' => '{name} عزیز، سفارش شما با شماره {order} در {site} ثبت شد.' ),
		'order_paid'    => array( 'title' => __( 'پرداخت موفق سفارش', 'zarincode' ), 'default' => '{name} عزیز، پرداخت سفارش {order} با موفقیت انجام شد. کد پیگیری: {ref}' ),
		'order_failed'  => array( 'title' => __( 'پرداخت ناموفق سفارش', 'zarincode' ), 'default' => '{name} عزیز، پرداخت سفارش {order} ناموفق بود. در صورت کسر وجه، طی ۷۲ ساعت بازگردانده می‌شود.' ),
		'ticket_reply'  => array( 'title' => __( 'پاسخ به تیکت پشتیبانی', 'zarincode' ), 'default' => '{name} عزیز، به تیکت شما با موضوع «{subject}» پاسخ داده شد. {site}' ),
		'enroll'        => array( 'title' => __( 'ثبت‌نام در دوره', 'zarincode' ), 'default' => '{name} عزیز، ثبت‌نام شما در دوره «{course}» انجام شد. از {site} لذت ببرید!' ),
		'booking'       => array( 'title' => __( 'رزرو نوبت', 'zarincode' ), 'default' => '{name} عزیز، درخواست رزرو شما برای تاریخ {date} ساعت {time} ثبت شد. {site}' ),
		'booking_remind'=> array( 'title' => __( 'یادآوری نوبت', 'zarincode' ), 'default' => '{name} عزیز، نوبت مشاوره شما فردا ساعت {time} است. لطفاً چند دقیقه زودتر آماده باشید. {site}' ),
		'wallet_deposit'=> array( 'title' => __( 'افزایش موجودی کیف پول', 'zarincode' ), 'default' => '{name} عزیز، مبلغ {amount} به کیف پول شما اضافه شد. {site}' ),
		'wallet_withdraw'=> array( 'title' => __( 'کسر از کیف پول', 'zarincode' ), 'default' => '{name} عزیز، مبلغ {amount} از کیف پول شما کسر شد. {site}' ),
		'contract_otp'  => array( 'title' => __( 'کد امضای قرارداد', 'zarincode' ), 'default' => 'کد امضای قرارداد {number}: {code}' ),
		'welcome'       => array( 'title' => __( 'خوش‌آمد به کاربر تازه', 'zarincode' ), 'default' => "{name} عزیز، به {site} خوش آمدید!\nکد تخفیف {percent}٪ شما: {code}\nاعتبار تا {days} روز." ),
		'abandoned'     => array( 'title' => __( 'یادآوری پرداخت ناتمام', 'zarincode' ), 'default' => "{name} عزیز، سفارش شما در {site} تکمیل نشد.\nبرای ادامه پرداخت:\n{url}" ),
		'winback'       => array( 'title' => __( 'بازگرداندن مشتری غیرفعال', 'zarincode' ), 'default' => "{name} عزیز، دلمان برایتان تنگ شده!\nکد تخفیف {percent}٪ ویژه شما: {code}\nاعتبار {days} روز — {site}" ),
		'bulk'          => array( 'title' => __( 'پیامک گروهی', 'zarincode' ), 'default' => 'پیام گروهی از {site}' ),
	);
}

/**
 * خواندن متن پیامک بر اساس کلید (با پشتیبانی از شخصی‌سازی پنل).
 *
 * اگر کاربر در پنل متنی تنظیم کرده باشد همان برگردانده می‌شود، وگرنه
 * متن پیش‌فرض. نام گزینه از zc_sms_txt_ + key ساخته می‌شود.
 *
 * @param string $key کلید پیامک.
 * @return string
 */
function zc_sms_message( $key ) {
	$msgs = zc_sms_messages();

	if ( ! isset( $msgs[ $key ] ) ) {
		return '';
	}

	// سازگاری با گزینه‌های قدیمی‌تر (پیش از سیستم متمرکز).
	$legacy = array(
		'otp'        => 'zc_sms_otp_text',
		'welcome'    => 'zc_sms_welcome_text',
		'abandoned'  => 'zc_sms_abandoned_text',
		'winback'    => 'zc_sms_winback_text',
		'contract_otp' => 'zc_contract_otp_sms',
	);

	$new_value = zc_opt( 'zc_sms_txt_' . $key, '' );
	if ( '' !== trim( (string) $new_value ) ) {
		return (string) $new_value;
	}

	if ( isset( $legacy[ $key ] ) ) {
		$old = zc_opt( $legacy[ $key ], '' );
		if ( '' !== trim( (string) $old ) ) {
			return (string) $old;
		}
	}

	return (string) $msgs[ $key ]['default'];
}

/**
 * جایگزینی متغیرها در متن پیامک.
 *
 * @param string $text متن پیامک.
 * @param array  $vars متغیرها به شکل کلید => مقدار.
 * @return string
 */
function zc_sms_parse_vars( $text, $vars = array() ) {
	$defaults = array(
		'name'    => '',
		'site'    => get_bloginfo( 'name' ),
		'url'     => home_url( '/' ),
		'code'    => '',
		'order'   => '',
		'ref'     => '',
		'amount'  => '',
		'date'    => '',
		'time'    => '',
		'percent' => '',
		'days'    => '',
		'subject' => '',
		'course'  => '',
		'number'  => '',
	);
	$vars = wp_parse_args( (array) $vars, $defaults );

	foreach ( $vars as $k => $v ) {
		$text = str_replace( '{' . $k . '}', (string) $v, $text );
	}

	return $text;
}

/**
 * ارسال پیامک شخصی‌سازی‌شده بر اساس کلید.
 *
 * @param string $key  کلید پیامک.
 * @param string $mobile شماره موبایل.
 * @param array  $vars متغیرها.
 * @return bool|WP_Error
 */
function zc_sms_send_message( $key, $mobile, $vars = array() ) {
	if ( ! zc_opt( 'zc_sms_enable', true ) || ! function_exists( 'zc_sms' ) || ! zc_sms()->is_ready() ) {
		return false;
	}

	$mobile = zc_sanitize_mobile( $mobile );
	if ( ! $mobile ) {
		return false;
	}

	$text = zc_sms_parse_vars( zc_sms_message( $key ), $vars );
	if ( ! trim( $text ) ) {
		return false;
	}

	return zc_sms_dispatch( $mobile, $text, $key );
}
