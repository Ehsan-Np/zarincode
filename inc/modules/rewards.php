<?php
/**
 * سامانه‌ی پاداش و کد تخفیف اختصاصی
 *
 * سه سناریو:
 *  ۱. ثبت‌نام کاربر تازه → پیامک معرفی ربات‌ها با لینک مستقیم
 *  ۲. فعال‌سازی هر ربات (تلگرام / بله) → کد تخفیف جداگانه
 *  ۳. هفت روز پس از ثبت‌نام → کد تخفیف ویژه‌ی خدمات برنامه‌نویسی
 *
 * هر کد به کاربر و شماره‌ی موبایل او قفل می‌شود و فقط یک‌بار ساخته
 * و ارسال می‌گردد.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   ساخت کد اختصاصی قفل‌شده به کاربر و موبایل
   ========================================================================== */

/**
 * ساخت کد تخفیف اختصاصی با قفل موبایل.
 *
 * برخلاف zc_create_user_coupon که فقط به ایمیل قفل می‌کند، این تابع
 * شماره‌ی موبایل را هم در متای کوپن ذخیره می‌کند تا هنگام اعمال،
 * تطابق کاربر و شماره بررسی شود.
 *
 * @param int    $user_id شناسه کاربر.
 * @param int    $percent درصد تخفیف.
 * @param int    $days    اعتبار به روز.
 * @param string $prefix  پیشوند کد.
 * @param array  $args    تنظیمات اضافی (scope, stackable).
 * @return string کد ساخته‌شده یا رشته‌ی خالی.
 */
function zc_reward_create_coupon( $user_id, $percent, $days = 30, $prefix = 'ZC', $args = array() ) {
	if ( ! class_exists( 'WC_Coupon' ) ) {
		return '';
	}

	$args = wp_parse_args(
		$args,
		array(
			'scope'     => 'all',   // all | products | services
			'stackable' => true,    // آیا با کدهای دیگر جمع می‌شود؟
			'label'     => '',
		)
	);

	$user = get_userdata( $user_id );

	if ( ! $user ) {
		return '';
	}

	$percent = max( 1, min( 100, (int) $percent ) );
	$days    = max( 1, (int) $days );
	$mobile  = zc_user_mobile( $user_id );

	// کد یکتا و خوانا (بدون نویسه‌های اشتباه‌انگیز مانند O و 0).
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

	/*
	 * نکته‌ی کلیدی: individual_use را روشن نمی‌کنیم، وگرنه ووکامرس
	 * اجازه‌ی استفاده‌ی هم‌زمان از چند کد را نمی‌دهد و خواسته‌ی
	 * «۲۰٪ + ۲۰٪ = ۴۰٪» غیرممکن می‌شود.
	 */
	$coupon->set_individual_use( ! $args['stackable'] );

	$coupon->set_usage_limit( 1 );
	$coupon->set_usage_limit_per_user( 1 );
	$coupon->set_date_expires( time() + ( $days * DAY_IN_SECONDS ) );
	$coupon->set_email_restrictions( array( $user->user_email ) );

	$coupon->set_description(
		$args['label'] ? $args['label'] : sprintf(
			/* translators: %s: نام کاربر */
			__( 'کد اختصاصی زرین کد برای %s', 'zarincode' ),
			$user->display_name
		)
	);

	$coupon->save();

	$coupon_id = $coupon->get_id();

	update_post_meta( $coupon_id, '_zc_auto_coupon', 1 );
	update_post_meta( $coupon_id, '_zc_coupon_user', (int) $user_id );
	update_post_meta( $coupon_id, '_zc_coupon_mobile', $mobile );
	update_post_meta( $coupon_id, '_zc_coupon_scope', $args['scope'] );
	update_post_meta( $coupon_id, '_zc_coupon_stackable', $args['stackable'] ? 1 : 0 );

	return $code;
}

/**
 * بررسی مالکیت کد تخفیف.
 *
 * کوپن‌های اختصاصی فقط برای همان کاربر و همان شماره‌ی موبایل
 * معتبرند؛ این تابع در فیلتر اعتبارسنجی ووکامرس و در کوپن خدمات
 * استفاده می‌شود.
 *
 * @param int $coupon_id شناسه کوپن.
 * @param int $user_id   شناسه کاربر (۰ = کاربر جاری).
 * @return bool
 */
function zc_reward_owns_coupon( $coupon_id, $user_id = 0 ) {
	$owner = (int) get_post_meta( $coupon_id, '_zc_coupon_user', true );

	// کوپن عمومی است، محدودیتی ندارد.
	if ( ! $owner ) {
		return true;
	}

	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	if ( $owner !== $user_id ) {
		return false;
	}

	// قفل شماره‌ی موبایل.
	$locked = (string) get_post_meta( $coupon_id, '_zc_coupon_mobile', true );

	if ( $locked && zc_user_mobile( $user_id ) !== $locked ) {
		return false;
	}

	return true;
}

/* ==========================================================================
   پیامک ثبت‌نام: معرفی ربات‌ها
   ========================================================================== */

/**
 * لینک شروع ربات برای یک کاربر.
 *
 * کد اتصال کاربر را داخل لینک می‌گذارد تا با یک کلیک وصل شود.
 *
 * @param string $messenger telegram یا bale.
 * @param int    $user_id   شناسه کاربر.
 * @return string
 */
function zc_reward_bot_link( $messenger, $user_id ) {
	$messengers = zc_messengers();

	if ( empty( $messengers[ $messenger ]['bot'] ) ) {
		return '';
	}

	$bot  = $messengers[ $messenger ]['bot'];
	$code = zc_get_connect_code( $user_id );

	return sprintf(
		'telegram' === $messenger ? 'https://t.me/%1$s?start=%2$s' : 'https://ble.ir/%1$s?start=%2$s',
		rawurlencode( $bot ),
		rawurlencode( $code )
	);
}

/**
 * پیامک معرفی ربات‌ها پس از ثبت‌نام.
 *
 * فقط یک‌بار برای هر کاربر ارسال می‌شود.
 *
 * @param int $user_id شناسه کاربر.
 * @return bool
 */
function zc_reward_send_intro_sms( $user_id ) {
	if ( ! zc_opt( 'zc_reward_enable', true ) ) {
		return false;
	}

	// گارد یک‌بارمصرف.
	if ( get_user_meta( $user_id, '_zc_reward_intro_sent', true ) ) {
		return false;
	}

	$mobile = zc_user_mobile( $user_id );

	if ( ! $mobile ) {
		return false;
	}

	$user    = get_userdata( $user_id );
	$percent = (int) zc_opt( 'zc_reward_bot_percent', 20 );

	$tg_link   = zc_reward_bot_link( 'telegram', $user_id );
	$bale_link = zc_reward_bot_link( 'bale', $user_id );

	$template = (string) zc_opt(
		'zc_reward_intro_sms',
		__( "{name} عزیز، به {site} خوش آمدید!\nبا فعال‌سازی اطلاع‌رسانی در هر ربات، {percent}٪ تخفیف بگیرید (مجموعاً {total}٪):\nتلگرام: {telegram}\nبله: {bale}", 'zarincode' )
	);

	$text = zc_sms_parse(
		$template,
		array(
			'{name}'     => $user ? $user->display_name : '',
			'{percent}'  => zc_fa_num( $percent ),
			'{total}'    => zc_fa_num( $percent * 2 ),
			'{telegram}' => $tg_link,
			'{bale}'     => $bale_link,
		)
	);

	// اگر رباتی پیکربندی نشده، خط مربوط به آن حذف شود.
	if ( ! $tg_link || ! $bale_link ) {
		$lines = array_filter(
			explode( "\n", $text ),
			static function ( $line ) use ( $tg_link, $bale_link ) {
				if ( ! $tg_link && false !== mb_strpos( $line, 'تلگرام' ) ) {
					return false;
				}

				if ( ! $bale_link && false !== mb_strpos( $line, 'بله' ) ) {
					return false;
				}

				return true;
			}
		);

		$text = trim( implode( "\n", $lines ) );
	}

	$ok = zc_sms_dispatch( $mobile, $text, 'reward_intro' );

	update_user_meta( $user_id, '_zc_reward_intro_sent', current_time( 'mysql' ) );

	return (bool) $ok;
}

/**
 * زمان‌بندی پیامک معرفی پس از ثبت‌نام.
 *
 * @param int $user_id شناسه کاربر.
 * @return void
 */
function zc_reward_on_register( $user_id ) {
	/*
	 * در لحظه‌ی user_register ممکن است هنوز شماره‌ی موبایل ذخیره
	 * نشده باشد (بسته به مسیر ثبت‌نام)، پس اجرا را کمی عقب می‌اندازیم.
	 */
	if ( zc_user_mobile( $user_id ) ) {
		zc_reward_send_intro_sms( $user_id );
		return;
	}

	wp_schedule_single_event( time() + 60, 'zc_reward_intro_event', array( $user_id ) );
}
add_action( 'user_register', 'zc_reward_on_register', 30 );

/**
 * اجرای رویداد زمان‌بندی‌شده‌ی پیامک معرفی.
 *
 * @param int $user_id شناسه کاربر.
 * @return void
 */
function zc_reward_intro_event( $user_id ) {
	zc_reward_send_intro_sms( (int) $user_id );
}
add_action( 'zc_reward_intro_event', 'zc_reward_intro_event' );

/**
 * ارسال پیامک معرفی در نخستین ورود.
 *
 * برای کاربرانی که پیش از فعال‌سازی این ماژول ثبت‌نام کرده‌اند یا
 * هنگام ثبت‌نام شماره نداشته‌اند.
 *
 * @param string  $login نام کاربری.
 * @param WP_User $user  کاربر.
 * @return void
 */
function zc_reward_on_first_login( $login, $user = null ) {
	if ( ! $user instanceof WP_User ) {
		$user = get_user_by( 'login', $login );
	}

	if ( ! $user ) {
		return;
	}

	if ( get_user_meta( $user->ID, '_zc_reward_intro_sent', true ) ) {
		return;
	}

	zc_reward_send_intro_sms( $user->ID );
}
add_action( 'wp_login', 'zc_reward_on_first_login', 20, 2 );

/* ==========================================================================
   پاداش فعال‌سازی ربات
   ========================================================================== */

/**
 * اهدای کد تخفیف پس از اتصال هر ربات.
 *
 * برای هر پیام‌رسان یک کد جداگانه ساخته می‌شود؛ کاربر با فعال‌سازی
 * هر دو ربات، دو کد می‌گیرد که روی هم جمع می‌شوند.
 *
 * @param int    $user_id   شناسه کاربر.
 * @param string $messenger پیام‌رسان.
 * @param string $chat_id   شناسه گفتگو.
 * @return void
 */
function zc_reward_on_bot_connect( $user_id, $messenger, $chat_id ) {
	if ( ! zc_opt( 'zc_reward_enable', true ) ) {
		return;
	}

	$meta_key = '_zc_reward_bot_' . sanitize_key( $messenger );

	// هر ربات فقط یک‌بار پاداش می‌دهد.
	if ( get_user_meta( $user_id, $meta_key, true ) ) {
		return;
	}

	$percent = (int) zc_opt( 'zc_reward_bot_percent', 20 );
	$days    = (int) zc_opt( 'zc_reward_bot_days', 30 );

	if ( $percent < 1 ) {
		return;
	}

	$messengers = zc_messengers();
	$label      = $messengers[ $messenger ]['label'] ?? $messenger;

	$code = zc_reward_create_coupon(
		$user_id,
		$percent,
		$days,
		'BOT' . strtoupper( substr( $messenger, 0, 2 ) ),
		array(
			'scope'     => 'all',
			'stackable' => true,
			'label'     => sprintf(
				/* translators: %s: نام پیام‌رسان */
				__( 'پاداش فعال‌سازی اطلاع‌رسانی %s', 'zarincode' ),
				$label
			),
		)
	);

	if ( ! $code ) {
		return;
	}

	update_user_meta( $user_id, $meta_key, $code );

	// پیام تبریک داخل خود ربات.
	$bot_text = zc_sms_parse(
		(string) zc_opt(
			'zc_reward_bot_message',
			__( "🎁 تبریک! اطلاع‌رسانی {messenger} فعال شد.\n\nکد تخفیف اختصاصی {percent}٪ شما:\n<code>{code}</code>\n\nاعتبار: {days} روز\nاین کد با سایر کدهای شما قابل جمع شدن است.", 'zarincode' )
		),
		array(
			'{messenger}' => $label,
			'{code}'      => $code,
			'{percent}'   => zc_fa_num( $percent ),
			'{days}'      => zc_fa_num( $days ),
		)
	);

	zc_messenger_send_to(
		$messenger,
		$chat_id,
		$bot_text,
		array(
			array(
				'text' => __( 'مشاهده‌ی فروشگاه', 'zarincode' ),
				'url'  => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
			),
		)
	);

	// پیامک تأیید.
	$mobile = zc_user_mobile( $user_id );

	if ( $mobile && zc_opt( 'zc_reward_bot_sms', true ) ) {
		$sms = zc_sms_parse(
			(string) zc_opt(
				'zc_reward_bot_sms_text',
				__( "کد تخفیف {percent}٪ فعال‌سازی {messenger}:\n{code}\nاعتبار {days} روز — {site}", 'zarincode' )
			),
			array(
				'{messenger}' => $label,
				'{code}'      => $code,
				'{percent}'   => zc_fa_num( $percent ),
				'{days}'      => zc_fa_num( $days ),
			)
		);

		zc_sms_dispatch( $mobile, $sms, 'reward_bot' );
	}

	do_action( 'zc_reward_granted', $user_id, $code, 'bot_' . $messenger );
}
add_action( 'zc_bot_connected', 'zc_reward_on_bot_connect', 10, 3 );

/* ==========================================================================
   کد تخفیف خدمات پس از هفت روز
   ========================================================================== */

/**
 * ارسال کد تخفیف خدمات به کاربران واجد شرایط.
 *
 * روزانه اجرا می‌شود و کاربرانی را می‌یابد که از ثبت‌نامشان به اندازه‌ی
 * تنظیم‌شده گذشته و هنوز این کد را نگرفته‌اند.
 *
 * @param int $batch حداکثر تعداد در هر اجرا.
 * @return int تعداد ارسال‌شده.
 */
function zc_reward_run_service_offer( $batch = 30 ) {
	if ( ! zc_opt( 'zc_reward_service_enable', true ) ) {
		return 0;
	}

	$days    = max( 1, (int) zc_opt( 'zc_reward_service_after', 7 ) );
	$percent = max( 1, (int) zc_opt( 'zc_reward_service_percent', 25 ) );
	$valid   = max( 1, (int) zc_opt( 'zc_reward_service_days', 14 ) );

	$cutoff = time() - ( $days * DAY_IN_SECONDS );

	/*
	 * get_users با شرط NOT EXISTS روی متا و مقایسه‌ی تاریخ ثبت‌نام
	 * قابل اعتماد نیست؛ فهرست را می‌گیریم و در PHP فیلتر می‌کنیم.
	 */
	$users = get_users(
		array(
			'number'  => 400,
			'orderby' => 'registered',
			'order'   => 'ASC',
			'fields'  => array( 'ID', 'user_registered' ),
		)
	);

	$sent = 0;

	foreach ( $users as $user ) {
		if ( $sent >= $batch ) {
			break;
		}

		$user_id = (int) $user->ID;

		if ( get_user_meta( $user_id, '_zc_reward_service_sent', true ) ) {
			continue;
		}

		if ( strtotime( $user->user_registered ) > $cutoff ) {
			continue;
		}

		$mobile = zc_user_mobile( $user_id );

		if ( ! $mobile ) {
			continue;
		}

		$code = zc_reward_create_coupon(
			$user_id,
			$percent,
			$valid,
			'SRV',
			array(
				'scope'     => 'services',
				'stackable' => true,
				'label'     => __( 'کد تخفیف خدمات برنامه‌نویسی', 'zarincode' ),
			)
		);

		// اگر ووکامرس نبود، کد داخلی خدمات ساخته می‌شود.
		if ( ! $code ) {
			$code = zc_service_coupon_create( $user_id, $percent, $valid );
		}

		if ( ! $code ) {
			continue;
		}

		$text = zc_sms_parse(
			(string) zc_opt(
				'zc_reward_service_sms',
				__( "{name} عزیز، {percent}٪ تخفیف ویژه‌ی خدمات برنامه‌نویسی {site}:\n{code}\nطراحی سایت، سئو و اجرای پروژه — اعتبار {days} روز\n{url}", 'zarincode' )
			),
			array(
				'{name}'    => get_the_author_meta( 'display_name', $user_id ),
				'{code}'    => $code,
				'{percent}' => zc_fa_num( $percent ),
				'{days}'    => zc_fa_num( $valid ),
				'{url}'     => zc_reward_services_url(),
			)
		);

		if ( zc_sms_dispatch( $mobile, $text, 'reward_service' ) ) {
			$sent++;
		}

		update_user_meta( $user_id, '_zc_reward_service_sent', current_time( 'mysql' ) );
		update_user_meta( $user_id, '_zc_reward_service_code', $code );

		// اعلان در ربات‌ها.
		zc_notify_user(
			$user_id,
			'discount',
			zc_sms_parse(
				__( "🎁 <b>{percent}٪ تخفیف خدمات برنامه‌نویسی</b>\n\nکد اختصاصی شما: <code>{code}</code>\nاعتبار: {days} روز", 'zarincode' ),
				array(
					'{code}'    => $code,
					'{percent}' => zc_fa_num( $percent ),
					'{days}'    => zc_fa_num( $valid ),
				)
			),
			array(
				array(
					'text' => __( 'مشاهده‌ی خدمات', 'zarincode' ),
					'url'  => zc_reward_services_url(),
				),
			)
		);
	}

	return $sent;
}

/**
 * نشانی برگه‌ی خدمات.
 *
 * @return string
 */
function zc_reward_services_url() {
	$page = get_page_by_path( 'services' );

	if ( $page ) {
		return get_permalink( $page );
	}

	$archive = get_post_type_archive_link( 'zc_service' );

	return $archive ? $archive : home_url( '/' );
}

/**
 * افزودن اجرای روزانه به کران موجود.
 *
 * @return void
 */
function zc_reward_cron_daily() {
	zc_reward_run_service_offer();
}
add_action( 'zc_sms_daily', 'zc_reward_cron_daily' );
