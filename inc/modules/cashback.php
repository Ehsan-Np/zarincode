<?php
/**
 * بازگشت اعتبار خرید (Cashback) به کیف پول زرین کد
 * ---------------------------------------------------------------------------
 * پس از تکمیل هر سفارش ووکامرس، درصدی از مبلغ نهایی پرداخت‌شده (بعد از
 * اعمال کد تخفیف) به کیف پول مشتری واریز می‌شود.
 *
 * ویژگی‌ها:
 *  - محاسبه بر اساس مبلغ نهاییِ پس از کد تخفیف.
 *  - قابل فعال/غیرفعال‌سازی از پنل تنظیمات.
 *  - درصد قابل تنظیم (۱ تا ۵۰).
 *  - امکان مستثنی‌کردن محصولات تخفیف‌خورده (sale).
 *  - جلوگیری از واریز مجدد برای سفارش تکراری.
 *  - ثبت تراکنش و اعلان.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * آیا قابلیت cashback فعال است؟
 *
 * @return bool
 */
function zc_cashback_enabled() {
	return (bool) zc_opt( 'zc_cashback_enable', false );
}

/**
 * درصد بازگشت اعتبار.
 *
 * @return float
 */
function zc_cashback_percent() {
	$p = (float) zc_opt( 'zc_cashback_percent', 5 );
	return max( 0, min( 100, $p ) );
}

/**
 * محاسبه مبلغ cashback برای یک سفارش.
 * بر اساس مبلغ نهاییِ پرداخت‌شده (get_total که شامل تخفیف و کد تخفیف است).
 *
 * @param \WC_Order $order سفارش.
 * @return float
 */
function zc_cashback_amount( $order ) {
	if ( ! $order || ! zc_cashback_enabled() ) {
		return 0;
	}

	$percent = zc_cashback_percent();
	if ( $percent <= 0 ) {
		return 0;
	}

	// مبلغ نهایی پرداخت‌شده (بعد از کد تخفیف و همه‌ی هزینه‌ها).
	$total = (float) $order->get_total();

	// اگر مبلغ با کیف پول پرداخت شده باشد، بازگشت اعتبار منطقی نیست
	// (چون خودِ کیف پول از قبل اعتبار بوده). در صورت فعال بودن فیلد پرداخت
	// کیف پول، می‌توانیم این بخش را کنترل کنیم.
	if ( function_exists( 'wc_cart_totals' ) ) {
		// noop
	}

	// مستثنی‌کردن محصولات تخفیف‌خورده: فقط مجموع اقلام غیرتخفیفی حساب شود.
	if ( zc_opt( 'zc_cashback_exclude_sale', false ) ) {
		$excluded = 0.0;
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			if ( $product && $product->is_on_sale() ) {
				// مجموع این اقلام از مبلغ نهایی کسر می‌شود.
				$excluded += (float) $item->get_total();
			}
		}
		$total = max( 0, $total - $excluded );
	}

	// واریز اعتبار.
	$cashback = round( $total * ( $percent / 100 ), 0 );

	return max( 0, $cashback );
}

/**
 * پردازش cashback پس از تکمیل سفارش.
 *
 * @param int $order_id شناسه سفارش.
 * @return void
 */
function zc_cashback_process( $order_id ) {
	if ( ! zc_cashback_enabled() ) {
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$user_id = (int) $order->get_user_id();
	if ( ! $user_id ) {
		return;
	}

	// جلوگیری از واریز مجدد برای همان سفارش (سازگار با HPOS).
	if ( $order->get_meta( '_zc_cashback_paid', true ) ) {
		return;
	}

	$cashback = zc_cashback_amount( $order );
	if ( $cashback <= 0 ) {
		return;
	}

	$order_total = (float) $order->get_total();

	// واریز idempotent به کیف پول.
	$tx = function_exists( 'zc_wallet_deposit' ) ? zc_wallet_deposit(
		$user_id,
		$cashback,
		sprintf(
			/* translators: 1: درصد 2: شماره سفارش */
			__( 'بازگشت اعتبار %1$s٪ از سفارش #%2$s', 'zarincode' ),
			zc_fa_num( zc_cashback_percent() ),
			$order->get_order_number()
		),
		'cashback',
		array( 'ref_id' => 'cashback-order-' . $order_id, 'gateway' => 'wallet', 'meta' => array( 'order_id' => $order_id ) )
	) : false;

	if ( ! $tx ) {
		$order->add_order_note( __( 'ثبت اعتبار بازگشتی ناموفق بود و برای تلاش مجدد علامت‌گذاری نشد.', 'zarincode' ) );
		$order->save();
		return;
	}

	// علامت‌گذاری برای جلوگیری از واریز مجدد؛ پیش از اعلان‌ها ذخیره می‌شود.
	$order->update_meta_data( '_zc_cashback_paid', '1' );
	$order->update_meta_data( '_zc_cashback_amount', $cashback );
	$order->update_meta_data( '_zc_cashback_transaction_id', (int) $tx );
	$order->save();

	// اعلان یک‌باره در پنل کاربری.
	update_user_meta( $user_id, '_zc_cashback_last_notice', array( 'amount' => $cashback, 'order' => $order_id ) );

	// اعلان (تلگرام/بله) و یادداشت سفارش.
	$order->add_order_note(
		sprintf(
			/* translators: 1: مبلغ 2: درصد */
			__( 'اعتبار %1$s به کیف پول کاربر واریز شد (بازگشت %2$s٪).', 'zarincode' ),
			number_format( $cashback ),
			zc_fa_num( zc_cashback_percent() )
		)
	);

	if ( function_exists( 'zc_subscription_notify' ) ) {
		zc_subscription_notify( 'granted', $user_id, 0, array( 'text' => sprintf( __( 'مبلغ %s ریال به کیف پول شما بازگشت.', 'zarincode' ), number_format( $cashback ) ) ) );
	}

	/**
	 * پس از واریز cashback.
	 *
	 * @param int   $user_id  کاربر.
	 * @param int   $order_id سفارش.
	 * @param float $cashback مبلغ.
	 */
	do_action( 'zc_cashback_granted', $user_id, $order_id, $cashback );
}
add_action( 'woocommerce_order_status_completed', 'zc_cashback_process', 20 );
add_action( 'woocommerce_order_status_processing', 'zc_cashback_process', 20 );

/**
 * نمایش پیام/اعلان cashback در پنل کاربری (پیشخوان).
 * اگر کاربر در پنل، پیشخوان را ببیند، آخرین cashback را نشان می‌دهیم.
 *
 * @return void
 */
function zc_cashback_notice_in_panel() {
	if ( ! is_user_logged_in() || ! zc_cashback_enabled() ) {
		return;
	}

	$user_id = get_current_user_id();
	$notice  = get_user_meta( $user_id, '_zc_cashback_last_notice', true );

	if ( ! $notice ) {
		return;
	}

	// فقط یک‌بار نمایش.
	delete_user_meta( $user_id, '_zc_cashback_last_notice' );

	$label = $notice['amount'] ? sprintf( __( 'اعتبار %s به کیف پول شما بازگشت.', 'zarincode' ), number_format( $notice['amount'] ) ) : '';
	if ( $label ) {
		echo '<div class="zc-alert zc-alert--success" style="margin-bottom:20px">' . esc_html( $label ) . '</div>';
	}
}
add_action( 'zc_panel_dashboard_top', 'zc_cashback_notice_in_panel' );
