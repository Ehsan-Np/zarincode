<?php
/**
 * فاکتور رسمی سفارش (PDF) زرین کد
 * ---------------------------------------------------------------------------
 * به کاربران اجازه می‌دهد فاکتور هر سفارش را از پنل کاربری دانلود/چاپ کنند.
 * از روش چاپ مرورگر (مثل قرارداد) استفاده می‌شود که با فارسی و راست‌چین
 * کامل سازگار است و نیازی به کتابخانه‌ی سنگین ندارد.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

if ( ! zc_is_woo() ) {
	return;
}

/**
 * فعال بودن فاکتور؟
 *
 * @return bool
 */
function zc_invoice_enabled() {
	return (bool) zc_opt( 'zc_invoice_enable', true );
}

/**
 * شماره فاکتور برای یک سفارش.
 *
 * @param \WC_Order $order سفارش.
 * @return string
 */
function zc_invoice_number( $order ) {
	$prefix = (string) zc_opt( 'zc_invoice_prefix', 'INV' );
	$prefix = trim( $prefix ) ? $prefix : 'INV';
	$year   = function_exists( 'zc_jalali_date' ) ? zc_jalali_date( 'Y' ) : gmdate( 'Y' );
	return sprintf( '%s-%s-%d', $prefix, zc_en_num( $year ), $order->get_id() );
}

/**
 * آیا کاربر مجاز به دیدن فاکتور این سفارش است؟
 *
 * @param \WC_Order $order سفارش.
 * @return bool
 */
function zc_can_view_invoice( $order ) {
	if ( ! $order ) {
		return false;
	}

	// مدیر همیشه.
	if ( current_user_can( 'manage_options' ) ) {
		return true;
	}

	$user_id = (int) $order->get_user_id();
	return $user_id && $user_id === get_current_user_id();
}

/**
 * اندپوینت دانلود فاکتور.
 *
 * @return void
 */
function zc_invoice_download() {
	if ( empty( $_GET['zc_invoice'] ) ) { // phpcs:ignore
		return;
	}

	$order_id = absint( $_GET['zc_invoice'] ); // phpcs:ignore
	$order    = wc_get_order( $order_id );

	if ( ! $order || ! zc_invoice_enabled() || ! zc_can_view_invoice( $order ) ) {
		wp_die( esc_html__( 'دسترسی مجاز نیست.', 'zarincode' ) );
	}

	// نمایش قالب چاپی فاکتور.
	include ZC_DIR . 'template-parts/invoice-print.php';
	exit;
}
add_action( 'template_redirect', 'zc_invoice_download' );

/**
 * لینک دانلود فاکتور (با دسترسی امن).
 *
 * @param int $order_id سفارش.
 * @return string
 */
function zc_invoice_url( $order_id ) {
	return add_query_arg( 'zc_invoice', (int) $order_id, home_url( '/' ) );
}

/**
 * افزودن دکمه‌ی «دانلود فاکتور» در تب سفارش‌های پنل.
 * در فایل قالب orders.php به صورت هوک فراخوانی می‌شود.
 *
 * @param int $order_id سفارش.
 * @return void
 */
function zc_invoice_button( $order_id ) {
	if ( ! zc_invoice_enabled() ) {
		return;
	}
	$order = wc_get_order( $order_id );
	if ( ! $order || ! zc_can_view_invoice( $order ) ) {
		return;
	}
	echo '<a class="zc-btn zc-btn--ghost zc-btn--sm" href="' . esc_url( zc_invoice_url( $order_id ) ) . '" target="_blank">'
		. zc_icon( 'file', 15 ) // phpcs:ignore
		. '<span>' . esc_html__( 'دانلود فاکتور', 'zarincode' ) . '</span></a>';
}
