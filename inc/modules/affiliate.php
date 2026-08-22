<?php
/**
 * سیستم معرفی و همکاری در فروش (Affiliate) زرین کد
 * ---------------------------------------------------------------------------
 * هر کاربر لینک اختصاصی معرفی می‌گیرد (ref=user_id) و برای هر فروشِ
 * ثبت‌شده از طریق آن، درصدی کمیسیون به کیف پولش واریز می‌شود.
 *
 * ویژگی‌ها:
 *  - لینک اختصاصی با پارامتر ref.
 *  - ذخیره‌ی کوکی معرف به مدت قابل تنظیم.
 *  - پرداخت کمیسیون بر اساس مبلغ نهایی (بعد از تخفیف).
 *  - تأخیر در قطعی‌شدن کمیسیون (برای جلوگیری از بازگشت وجه).
 *  - قابل فعال/غیرفعال و تنظیم درصد/حداقل برداشت از پنل.
 *  - تب «همکاری در فروش» در پنل کاربری.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

if ( ! zc_is_woo() ) {
	return;
}

/**
 * فعال بودن سیستم معرفی؟
 *
 * @return bool
 */
function zc_affiliate_enabled() {
	return (bool) zc_opt( 'zc_aff_enable', false );
}

/**
 * درصد کمیسیون.
 *
 * @return float
 */
function zc_affiliate_percent() {
	$p = (float) zc_opt( 'zc_aff_percent', 10 );
	return max( 0, min( 90, $p ) );
}

/**
 * ثبت کوکی معرف هنگام ورود از لینک.
 *
 * @return void
 */
function zc_affiliate_capture_ref() {
	if ( empty( $_GET['ref'] ) ) { // phpcs:ignore
		return;
	}

	$ref_id = absint( $_GET['ref'] ); // phpcs:ignore
	if ( ! $ref_id || $ref_id === get_current_user_id() ) {
		return;
	}

	// یک کوکی ۳۰ روزه (یا طبق تنظیم) ذخیره می‌شود.
	setcookie( 'zc_aff_ref', $ref_id, time() + 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
}
add_action( 'init', 'zc_affiliate_capture_ref', 1 );

/**
 * شناسه‌ی معرفِ مرتبط با این کاربر/نشست.
 *
 * @return int
 */
function zc_affiliate_get_ref() {
	// از کوکی.
	if ( ! empty( $_COOKIE['zc_aff_ref'] ) ) {
		return absint( $_COOKIE['zc_aff_ref'] );
	}

	// از فیلد مخفی هنگام ثبت‌نام.
	if ( ! empty( $_POST['zc_ref'] ) ) { // phpcs:ignore
		return absint( $_POST['zc_ref'] );
	}

	return 0;
}

/**
 * لینک اختصاصی معرفی کاربر.
 *
 * @param int $user_id کاربر.
 * @return string
 */
function zc_affiliate_link( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	return add_query_arg( 'ref', (int) $user_id, home_url( '/' ) );
}

/**
 * ثبت کمیسیونِ در انتظار تأیید هنگام ثبت سفارش.
 *
 * @param int $order_id سفارش.
 * @return void
 */
function zc_affiliate_register_pending( $order_id ) {
	if ( ! zc_affiliate_enabled() ) {
		return;
	}

	$ref_id = zc_affiliate_get_ref();
	if ( ! $ref_id ) {
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	// اگر خریدار خودش معرفِ خودش نباشد.
	if ( $order->get_user_id() === $ref_id ) {
		return;
	}

	// جلوگیری از تکرار.
	if ( get_post_meta( $order_id, '_zc_aff_ref', true ) ) {
		return;
	}

	$commission = zc_affiliate_commission_amount( $order );
	if ( $commission <= 0 ) {
		return;
	}

	update_post_meta( $order_id, '_zc_aff_ref', $ref_id );
	update_post_meta( $order_id, '_zc_aff_commission', $commission );
	update_post_meta( $order_id, '_zc_aff_pending', '1' );
}
add_action( 'woocommerce_checkout_order_processed', 'zc_affiliate_register_pending', 20 );

/**
 * محاسبه‌ی مبلغ کمیسیون بر اساس مبلغ نهایی سفارش.
 *
 * @param \WC_Order $order سفارش.
 * @return float
 */
function zc_affiliate_commission_amount( $order ) {
	$percent = zc_affiliate_percent();
	if ( $percent <= 0 ) {
		return 0;
	}
	// مبلغ نهایی بعد از تخفیف و کد کوپن.
	return round( (float) $order->get_total() * ( $percent / 100 ), 0 );
}

/**
 * قطعی‌کردن کمیسیون پس از تکمیل سفارش و گذشت روزهای اطمینان.
 *
 * @param int $order_id سفارش.
 * @return void
 */
function zc_affiliate_settle( $order_id ) {
	if ( ! zc_affiliate_enabled() ) {
		return;
	}

	$ref_id = (int) get_post_meta( $order_id, '_zc_aff_ref', true );
	$commission = (float) get_post_meta( $order_id, '_zc_aff_commission', true );

	if ( ! $ref_id || $commission <= 0 ) {
		return;
	}
	// اگر قبلاً واریز شده، نادیده بگیر.
	if ( get_post_meta( $order_id, '_zc_aff_paid', true ) ) {
		return;
	}

	// پرداخت به معرف.
	if ( function_exists( 'zc_wallet_deposit' ) ) {
		zc_wallet_deposit(
			$ref_id,
			$commission,
			sprintf(
				/* translators: 1: مبلغ 2: سفارش */
				__( 'کمیسیون معرفی از سفارش #%2$s', 'zarincode' ),
				number_format( $commission ),
				$order_id
			),
			'affiliate',
			array( 'order_id' => $order_id )
		);
	}

	update_post_meta( $order_id, '_zc_aff_paid', '1' );
	update_post_meta( $order_id, '_zc_aff_pending', '' );

	// اعلان به معرف.
	$order = wc_get_order( $order_id );
	if ( $order ) {
		$order->add_order_note( sprintf( __( 'کمیسیون معرفی %s به کاربر #%d واریز شد.', 'zarincode' ), number_format( $commission ), $ref_id ) );
	}
}
add_action( 'woocommerce_order_status_completed', 'zc_affiliate_settle', 25 );
add_action( 'woocommerce_order_status_processing', 'zc_affiliate_settle', 25 );

/**
 * اگر روزهای اطمینان بیشتر از ۰ باشد، واریز را به کرون روزانه بسپاریم.
 * وقتی سفارش completed شد و مدت گذشت، واریز می‌شود.
 *
 * @return void
 */
function zc_affiliate_daily_settle() {
	if ( ! zc_affiliate_enabled() ) {
		return;
	}

	$days = (int) zc_opt( 'zc_aff_days', 7 );
	if ( $days <= 0 ) {
		return;
	}

	// سفارش‌های تکمیل‌شده که کمیسیون pending دارند.
	$orders = wc_get_orders(
		array(
			'limit'      => -1,
			'status'     => array( 'completed', 'processing' ),
			'meta_key'   => '_zc_aff_pending', // phpcs:ignore
			'meta_value' => '1',
		)
	);

	foreach ( $orders as $order ) {
		$created = $order->get_date_created();
		if ( ! $created ) {
			continue;
		}
		$elapsed = time() - $created->getTimestamp();
		if ( $elapsed >= $days * DAY_IN_SECONDS ) {
			zc_affiliate_settle( $order->get_id() );
		}
	}
}
add_action( 'zc_sms_daily', 'zc_affiliate_daily_settle' );

/**
 * افزودن تب «همکاری در فروش» به پنل.
 *
 * @param array $tabs تب‌ها.
 * @return array
 */
function zc_affiliate_panel_tab( $tabs ) {
	if ( ! zc_affiliate_enabled() ) {
		return $tabs;
	}
	$tabs['affiliate'] = array(
		'label' => __( 'همکاری در فروش', 'zarincode' ),
		'icon'  => 'link',
		'order' => 55,
	);
	return $tabs;
}
add_filter( 'zc_panel_tabs', 'zc_affiliate_panel_tab' );
