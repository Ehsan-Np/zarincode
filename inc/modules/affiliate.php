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
	if ( ! $ref_id || $ref_id === get_current_user_id() || ! get_user_by( 'id', $ref_id ) ) {
		return;
	}

	// کوکی امضای معرفی داده حساس نیست، اما در برابر سرقت JS محافظت می‌شود.
	setcookie(
		'zc_aff_ref',
		(string) $ref_id,
		array(
			'expires' => time() + 30 * DAY_IN_SECONDS,
			'path' => COOKIEPATH ?: '/', 'domain' => COOKIE_DOMAIN, 'secure' => is_ssl(),
			'httponly' => true, 'samesite' => 'Lax',
		)
	);
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

	// جلوگیری از تکرار (سازگار با HPOS).
	if ( $order->get_meta( '_zc_aff_ref', true ) ) {
		return;
	}

	$commission = zc_affiliate_commission_amount( $order );
	if ( $commission <= 0 ) {
		return;
	}

	$order->update_meta_data( '_zc_aff_ref', $ref_id );
	$order->update_meta_data( '_zc_aff_commission', $commission );
	$order->update_meta_data( '_zc_aff_pending', '1' );
	$order->save();
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
	if ( ! zc_affiliate_enabled() || ! function_exists( 'wc_get_order' ) ) {
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order || ! $order->is_paid() ) {
		return;
	}

	$ref_id     = (int) $order->get_meta( '_zc_aff_ref', true );
	$commission = (float) $order->get_meta( '_zc_aff_commission', true );

	if ( ! $ref_id || $commission <= 0 || $order->get_meta( '_zc_aff_paid', true ) ) {
		return;
	}

	// رعایت واقعی دورهٔ اطمینان برای بازگشت وجه.
	$days = max( 0, (int) zc_opt( 'zc_aff_days', 7 ) );
	$paid = $order->get_date_paid() ?: $order->get_date_created();
	if ( $days > 0 && $paid && ( time() - $paid->getTimestamp() ) < ( $days * DAY_IN_SECONDS ) ) {
		return;
	}

	if ( ! function_exists( 'zc_wallet_deposit' ) ) {
		return;
	}

	$tx = zc_wallet_deposit(
		$ref_id,
		$commission,
		sprintf( __( 'کمیسیون معرفی از سفارش #%s', 'zarincode' ), $order->get_order_number() ),
		'affiliate',
		array( 'ref_id' => 'affiliate-order-' . $order_id )
	);

	if ( ! $tx ) {
		return;
	}

	$order->update_meta_data( '_zc_aff_paid', '1' );
	$order->update_meta_data( '_zc_aff_pending', '' );
	$order->update_meta_data( '_zc_aff_transaction_id', (int) $tx );
	$order->add_order_note( sprintf( __( 'کمیسیون معرفی %s به کاربر #%d واریز شد.', 'zarincode' ), number_format( $commission ), $ref_id ) );
	$order->save();
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
