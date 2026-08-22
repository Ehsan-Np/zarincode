<?php
/**
 * تبدیل تاریخ میلادی به شمسی (هجری خورشیدی)
 *
 * پیاده‌سازی مستقل و بدون وابستگی به افزونه یا اکستنشن intl، تا روی
 * هر هاستی کار کند. الگوریتم بر پایه‌ی روز جولیَن است و برای بازه‌ی
 * سال‌های ۱۱۷۸ تا ۱۶۳۳ شمسی دقیق است.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * نام ماه‌های شمسی.
 *
 * @return array
 */
function zc_jalali_months() {
	return array(
		1  => 'فروردین',
		2  => 'اردیبهشت',
		3  => 'خرداد',
		4  => 'تیر',
		5  => 'مرداد',
		6  => 'شهریور',
		7  => 'مهر',
		8  => 'آبان',
		9  => 'آذر',
		10 => 'دی',
		11 => 'بهمن',
		12 => 'اسفند',
	);
}

/**
 * نام روزهای هفته به فارسی.
 *
 * @return array
 */
function zc_jalali_days() {
	return array(
		'Saturday'  => 'شنبه',
		'Sunday'    => 'یکشنبه',
		'Monday'    => 'دوشنبه',
		'Tuesday'   => 'سه‌شنبه',
		'Wednesday' => 'چهارشنبه',
		'Thursday'  => 'پنجشنبه',
		'Friday'    => 'جمعه',
	);
}

/**
 * تبدیل تاریخ میلادی به شمسی.
 *
 * @param int $gy سال میلادی.
 * @param int $gm ماه میلادی.
 * @param int $gd روز میلادی.
 * @return array آرایه‌ای شامل سال، ماه و روز شمسی.
 */
function zc_gregorian_to_jalali( $gy, $gm, $gd ) {
	$g_d_m = array( 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );

	$gy2 = ( $gm > 2 ) ? ( $gy + 1 ) : $gy;

	$days = 355666
		+ ( 365 * $gy )
		+ ( (int) ( ( $gy2 + 3 ) / 4 ) )
		- ( (int) ( ( $gy2 + 99 ) / 100 ) )
		+ ( (int) ( ( $gy2 + 399 ) / 400 ) )
		+ $gd
		+ $g_d_m[ $gm - 1 ];

	$jy   = -1595 + ( 33 * ( (int) ( $days / 12053 ) ) );
	$days %= 12053;

	$jy   += 4 * ( (int) ( $days / 1461 ) );
	$days %= 1461;

	if ( $days > 365 ) {
		$jy   += (int) ( ( $days - 1 ) / 365 );
		$days  = ( $days - 1 ) % 365;
	}

	if ( $days < 186 ) {
		$jm = 1 + (int) ( $days / 31 );
		$jd = 1 + ( $days % 31 );
	} else {
		$jm = 7 + (int) ( ( $days - 186 ) / 30 );
		$jd = 1 + ( ( $days - 186 ) % 30 );
	}

	return array( $jy, $jm, $jd );
}

/**
 * قالب‌بندی یک زمان‌مهر بر اساس تقویم شمسی.
 *
 * از نشانه‌های استاندارد PHP پشتیبانی می‌کند:
 * Y y n m j d F l G H i s a A
 *
 * @param string $format قالب خروجی.
 * @param int    $ts     زمان‌مهر یونیکس (به وقت محلی سایت).
 * @return string
 */
function zc_jalali_date( $format = 'j F Y', $ts = null ) {
	$ts = ( null === $ts ) ? current_time( 'timestamp' ) : (int) $ts; // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp

	$gy = (int) gmdate( 'Y', $ts );
	$gm = (int) gmdate( 'n', $ts );
	$gd = (int) gmdate( 'j', $ts );

	list( $jy, $jm, $jd ) = zc_gregorian_to_jalali( $gy, $gm, $gd );

	$months = zc_jalali_months();
	$days   = zc_jalali_days();
	$out    = '';
	$len    = strlen( $format );

	for ( $i = 0; $i < $len; $i++ ) {
		$c = $format[ $i ];

		switch ( $c ) {
			case 'Y':
				$out .= $jy;
				break;
			case 'y':
				$out .= substr( (string) $jy, -2 );
				break;
			case 'n':
				$out .= $jm;
				break;
			case 'm':
				$out .= str_pad( (string) $jm, 2, '0', STR_PAD_LEFT );
				break;
			case 'j':
				$out .= $jd;
				break;
			case 'd':
				$out .= str_pad( (string) $jd, 2, '0', STR_PAD_LEFT );
				break;
			case 'F':
				$out .= $months[ $jm ];
				break;
			case 'M':
				$out .= $months[ $jm ];
				break;
			case 'l':
				$out .= $days[ gmdate( 'l', $ts ) ] ?? '';
				break;
			case 'D':
				$out .= $days[ gmdate( 'l', $ts ) ] ?? '';
				break;
			case '\\':
				$i++;
				$out .= $format[ $i ] ?? '';
				break;
			default:
				// بقیه‌ی نشانه‌ها (ساعت، دقیقه و ...) به PHP سپرده می‌شود.
				$out .= gmdate( $c, $ts );
				break;
		}
	}

	return $out;
}

/**
 * جایگزینی تاریخ وردپرس با تاریخ شمسی.
 *
 * @param string $formatted تاریخ قالب‌بندی‌شده.
 * @param string $format    قالب درخواستی.
 * @param int    $ts        زمان‌مهر.
 * @return string
 */
function zc_filter_date( $formatted, $format, $ts ) {
	if ( ! zc_opt( 'zc_jalali_date', true ) ) {
		return $formatted;
	}

	// در پیشخوان دست نمی‌زنیم تا ابزارهای مدیریتی درست کار کنند.
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $formatted;
	}

	// قالب‌های ماشین‌خوان (ویژگی datetime، فید RSS، اسکیما و ...)
	// باید میلادی و استاندارد باقی بمانند.
	if ( zc_is_machine_date_format( $format ) ) {
		return $formatted;
	}

	return zc_fa_num( zc_jalali_date( $format, $ts ) );
}

/**
 * تشخیص قالب‌های تاریخ ماشین‌خوان که نباید شمسی شوند.
 *
 * مانند ISO-8601 (c)، RFC-2822 (r)، زمان‌مهر یونیکس (U) و
 * قالب استاندارد Y-m-d که در ویژگی datetime استفاده می‌شود.
 *
 * @param string $format قالب.
 * @return bool
 */
function zc_is_machine_date_format( $format ) {
	$machine = array( 'c', 'r', 'U', 'Y-m-d', 'Y-m-d H:i:s', 'Y-m-d\\TH:i:sP', DATE_W3C, DATE_RSS, DATE_ATOM );

	return in_array( (string) $format, $machine, true );
}

/**
 * فیلتر تاریخ نوشته.
 *
 * @param string $the_date تاریخ.
 * @param string $format   قالب.
 * @param int|WP_Post $post نوشته.
 * @return string
 */
function zc_filter_post_date( $the_date, $format, $post = null ) {
	if ( ! zc_opt( 'zc_jalali_date', true ) ) {
		return $the_date;
	}

	if ( is_admin() && ! wp_doing_ajax() ) {
		return $the_date;
	}

	$post = get_post( $post );

	if ( ! $post ) {
		return $the_date;
	}

	$format = $format ? $format : get_option( 'date_format' );

	if ( zc_is_machine_date_format( $format ) ) {
		return $the_date;
	}

	$ts = (int) get_post_time( 'U', false, $post );

	return zc_fa_num( zc_jalali_date( $format, $ts ) );
}

add_filter( 'date_i18n', 'zc_filter_date', 10, 3 );
add_filter( 'get_the_date', 'zc_filter_post_date', 10, 3 );
add_filter( 'get_the_modified_date', 'zc_filter_post_date', 10, 3 );

/**
 * تاریخ شمسی در ستون تاریخ فهرست نوشته‌های پیشخوان.
 *
 * @param string $status وضعیت.
 * @param object $post   نوشته.
 * @return string
 */
function zc_admin_jalali_column( $status, $post ) {
	if ( ! zc_opt( 'zc_jalali_date', true ) ) {
		return $status;
	}

	$ts = (int) get_post_time( 'U', false, $post );

	return zc_fa_num( zc_jalali_date( 'j F Y', $ts ) );
}
add_filter( 'post_date_column_time', 'zc_admin_jalali_column', 10, 2 );

/* ==========================================================================
   تبدیل شمسی به میلادی و ابزارهای تاریخ‌گزین
   ========================================================================== */

/**
 * تبدیل تاریخ شمسی به میلادی.
 *
 * وارونه‌ی zc_gregorian_to_jalali است و برای ذخیره‌ی ورودی کاربر
 * (که شمسی وارد می‌کند) در پایگاه داده‌ی وردپرس لازم است.
 *
 * @param int $jy سال شمسی.
 * @param int $jm ماه شمسی.
 * @param int $jd روز شمسی.
 * @return array آرایه‌ی [سال, ماه, روز] میلادی.
 */
function zc_jalali_to_gregorian( $jy, $jm, $jd ) {
	$jy = (int) $jy;
	$jm = (int) $jm;
	$jd = (int) $jd;

	$jy += 1595;
	$days = -355668 + ( 365 * $jy ) + ( ( (int) ( $jy / 33 ) ) * 8 )
		+ (int) ( ( ( $jy % 33 ) + 3 ) / 4 ) + $jd
		+ ( ( $jm < 7 ) ? ( $jm - 1 ) * 31 : ( ( $jm - 7 ) * 30 ) + 186 );

	$gy = 400 * (int) ( $days / 146097 );
	$days %= 146097;

	if ( $days > 36524 ) {
		$gy += 100 * (int) ( --$days / 36524 );
		$days %= 36524;

		if ( $days >= 365 ) {
			$days++;
		}
	}

	$gy += 4 * (int) ( $days / 1461 );
	$days %= 1461;

	if ( $days > 365 ) {
		$gy += (int) ( ( $days - 1 ) / 365 );
		$days = ( $days - 1 ) % 365;
	}

	$gd = $days + 1;

	$leap = ( ( $gy % 4 === 0 && $gy % 100 !== 0 ) || $gy % 400 === 0 ) ? 29 : 28;
	$sal_a = array( 0, 31, $leap, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );

	for ( $gm = 0; $gm < 13 && $gd > $sal_a[ $gm ]; $gm++ ) {
		$gd -= $sal_a[ $gm ];
	}

	return array( $gy, $gm, $gd );
}

/**
 * تبدیل رشته‌ی تاریخ شمسی به میلادی (Y-m-d).
 *
 * ورودی می‌تواند با «/» یا «-» و با ارقام فارسی باشد.
 *
 * @param string $jalali رشته‌ی شمسی مانند ۱۴۰۳/۰۵/۲۱.
 * @param string $time   بخش ساعت (اختیاری) مانند 14:30.
 * @return string تاریخ میلادی Y-m-d یا Y-m-d H:i:s؛ در صورت خطا رشته‌ی خالی.
 */
function zc_jalali_str_to_gregorian( $jalali, $time = '' ) {
	$jalali = zc_en_num( trim( (string) $jalali ) );
	$jalali = str_replace( array( '/', '.' ), '-', $jalali );

	if ( ! preg_match( '/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $jalali, $m ) ) {
		return '';
	}

	list( $gy, $gm, $gd ) = zc_jalali_to_gregorian( $m[1], $m[2], $m[3] );

	$out = sprintf( '%04d-%02d-%02d', $gy, $gm, $gd );

	if ( $time ) {
		$time = zc_en_num( trim( $time ) );

		if ( preg_match( '/^(\d{1,2}):(\d{1,2})/', $time, $t ) ) {
			$out .= sprintf( ' %02d:%02d:00', $t[1], $t[2] );
		}
	}

	return $out;
}

// تبدیل ارقام فارسی به لاتین در inc/helpers.php تعریف شده است (zc_en_num).

/**
 * آیا تقویم شمسی فعال است؟
 *
 * @return bool
 */
function zc_jalali_enabled() {
	return (bool) zc_opt( 'zc_jalali_enable', true );
}

/**
 * تبدیل زمان‌مهر به رشته‌ی شمسی قابل استفاده در تاریخ‌گزین.
 *
 * @param int $ts زمان‌مهر.
 * @return string
 */
function zc_jalali_input_value( $ts ) {
	return $ts ? zc_jalali_date( 'Y/m/d', (int) $ts ) : '';
}

/* ==========================================================================
   بارگذاری تاریخ‌گزین در سایت و پیشخوان
   ========================================================================== */

/**
 * بارگذاری اسکریپت تاریخ‌گزین شمسی در پیشخوان.
 *
 * فایل‌های ووکامرس، برگه‌ی نوبت‌دهی، قراردادها و هر فیلد تاریخی
 * دیگر با یک کامپوننت مشترک پوشش داده می‌شوند.
 *
 * @return void
 */
function zc_admin_jalali_assets() {
	if ( ! zc_jalali_enabled() ) {
		return;
	}

	wp_enqueue_style( 'zc-jalali', ZC_URI . 'assets/css/jalali.css', array(), ZC_VERSION );
	wp_enqueue_script( 'zc-jalali', ZC_URI . 'assets/js/jalali.js', array(), ZC_VERSION, true );

	wp_localize_script(
		'zc-jalali',
		'ZC_JALALI',
		array(
			'months'  => array_values( zc_jalali_months() ),
			'days'    => array( 'ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج' ),
			'today'   => zc_jalali_date( 'Y/m/d' ),
			'todayTxt' => __( 'امروز', 'zarincode' ),
			'clear'   => __( 'پاک کردن', 'zarincode' ),
			'close'   => __( 'بستن', 'zarincode' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'zc_admin_jalali_assets' );
add_action( 'wp_enqueue_scripts', 'zc_admin_jalali_assets' );
