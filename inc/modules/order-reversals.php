<?php
/**
 * برگشت یکپارچهٔ مزایا و اعتبارهای سفارش بازپرداخت‌شده.
 *
 * @package Zarincode
 */
defined( 'ABSPATH' ) || exit;

/**
 * برگشت دوره، اشتراک، لایسنس، cashback و affiliate پس از refund کامل.
 *
 * @param int $order_id سفارش.
 * @return void
 */
function zc_reverse_order_benefits( $order_id ) {
	if ( ! function_exists( 'wc_get_order' ) ) { return; }
	$order = wc_get_order( $order_id );
	if ( ! $order || $order->get_meta( '_zc_benefits_reversed', true ) ) { return; }

	global $wpdb;
	$user_id = (int) $order->get_user_id();

	// ابطال دسترسی دوره فقط وقتی enrollment هنوز به همین سفارش متصل است.
	if ( $user_id ) {
		$table      = $wpdb->prefix . 'zc_enrollments';
		$course_ids = $wpdb->get_col( $wpdb->prepare( "SELECT course_id FROM {$table} WHERE user_id=%d AND order_id=%d AND status='active'", $user_id, $order_id ) ); // phpcs:ignore
		$wpdb->update( $table, array( 'status' => 'refunded' ), array( 'user_id' => $user_id, 'order_id' => (int) $order_id ), array( '%s' ), array( '%d', '%d' ) ); // phpcs:ignore
		foreach ( $course_ids as $course_id ) {
			$current = (int) get_post_meta( $course_id, '_zc_students', true );
			update_post_meta( $course_id, '_zc_students', max( 0, $current - 1 ) );
		}
	}

	// اشتراک تنها در صورت تعلق رکورد جاری به سفارش برگشتی غیرفعال می‌شود.
	$subscription = $user_id ? zc_subscription_get_user( $user_id ) : array();
	if ( $subscription && (int) ( $subscription['order_id'] ?? 0 ) === (int) $order_id ) {
		$subscription['status']   = 'refunded';
		$subscription['expires']  = time();
		$subscription['ended_at'] = time();
		update_user_meta( $user_id, 'zc_subscription', $subscription );
	}

	// لایسنس‌های همان سفارش ابطال می‌شوند.
	$wpdb->update( $wpdb->prefix . 'zc_licenses', array( 'status' => 'revoked' ), array( 'order_id' => (int) $order_id ), array( '%s' ), array( '%d' ) ); // phpcs:ignore

	// اعتبار تشویقی صادرشده به بدهی/کسر کیف پول تبدیل می‌شود؛ حتی اگر خرج شده باشد.
	$cashback          = (float) $order->get_meta( '_zc_cashback_amount', true );
	$cashback_reversed = (float) $order->get_meta( '_zc_cashback_reversed_amount', true );
	$cashback_due      = max( 0, $cashback - $cashback_reversed );
	if ( $cashback_due > 0 && $user_id ) {
		$tx = zc_wallet_adjust( $user_id, -$cashback_due, sprintf( __( 'برگشت cashback سفارش #%s', 'zarincode' ), $order->get_order_number() ), 'cashback_reversal', 'cashback-reversal-full-' . $order_id );
		if ( $tx ) {
			$order->update_meta_data( '_zc_cashback_reversed', (int) $tx );
			$order->update_meta_data( '_zc_cashback_reversed_amount', $cashback );
		}
	}

	$referrer           = (int) $order->get_meta( '_zc_aff_ref', true );
	$commission         = (float) $order->get_meta( '_zc_aff_commission', true );
	$commission_reversed = (float) $order->get_meta( '_zc_aff_reversed_amount', true );
	$commission_due     = max( 0, $commission - $commission_reversed );
	if ( $referrer && $commission_due > 0 && $order->get_meta( '_zc_aff_paid', true ) ) {
		$tx = zc_wallet_adjust( $referrer, -$commission_due, sprintf( __( 'برگشت کمیسیون سفارش #%s', 'zarincode' ), $order->get_order_number() ), 'affiliate_reversal', 'affiliate-reversal-full-' . $order_id );
		if ( $tx ) {
			$order->update_meta_data( '_zc_aff_reversed', (int) $tx );
			$order->update_meta_data( '_zc_aff_reversed_amount', $commission );
		}
	}

	$order->update_meta_data( '_zc_benefits_reversed', current_time( 'mysql' ) );
	$order->add_order_note( __( 'مزایا، دسترسی‌ها، لایسنس و اعتبارهای وابسته به سفارش برگشت داده شدند.', 'zarincode' ) );
	$order->save();
}
add_action( 'woocommerce_order_fully_refunded', 'zc_reverse_order_benefits', 20 );

/**
 * برگشت تناسبی اعتبارها و دسترسی اقلام در refund جزئی.
 *
 * @param int $order_id  سفارش.
 * @param int $refund_id بازپرداخت.
 * @return void
 */
function zc_reverse_partial_order_benefits( $order_id, $refund_id ) {
	$order  = wc_get_order( $order_id );
	$refund = wc_get_order( $refund_id );
	if ( ! $order || ! $refund || $order->get_meta( '_zc_refund_processed_' . $refund_id, true ) ) { return; }

	global $wpdb;
	$user_id = (int) $order->get_user_id();
	$total   = max( 0.01, (float) $order->get_total() );
	$ratio   = min( 1, abs( (float) $refund->get_amount() ) / $total );

	$cashback_total   = (float) $order->get_meta( '_zc_cashback_amount', true );
	$cashback_already = (float) $order->get_meta( '_zc_cashback_reversed_amount', true );
	$cashback_target  = min( $cashback_total, round( $cashback_total * $ratio, 2 ) + $cashback_already );
	$cashback_delta   = max( 0, $cashback_target - $cashback_already );
	if ( $user_id && $cashback_delta > 0 ) {
		$tx = zc_wallet_adjust( $user_id, -$cashback_delta, sprintf( __( 'اصلاح cashback بازپرداخت #%s', 'zarincode' ), $refund_id ), 'cashback_reversal', 'cashback-reversal-refund-' . $refund_id );
		if ( $tx ) { $order->update_meta_data( '_zc_cashback_reversed_amount', $cashback_already + $cashback_delta ); }
	}

	$referrer          = (int) $order->get_meta( '_zc_aff_ref', true );
	$commission_total  = (float) $order->get_meta( '_zc_aff_commission', true );
	$commission_already = (float) $order->get_meta( '_zc_aff_reversed_amount', true );
	$commission_target = min( $commission_total, round( $commission_total * $ratio, 2 ) + $commission_already );
	$commission_delta  = max( 0, $commission_target - $commission_already );
	if ( $referrer && $commission_delta > 0 && $order->get_meta( '_zc_aff_paid', true ) ) {
		$tx = zc_wallet_adjust( $referrer, -$commission_delta, sprintf( __( 'اصلاح کمیسیون بازپرداخت #%s', 'zarincode' ), $refund_id ), 'affiliate_reversal', 'affiliate-reversal-refund-' . $refund_id );
		if ( $tx ) { $order->update_meta_data( '_zc_aff_reversed_amount', $commission_already + $commission_delta ); }
	}

	// ابطال دسترسی فقط برای محصولاتی که در refund line item حضور دارند.
	foreach ( $refund->get_items() as $item ) {
		$product_id = (int) $item->get_product_id();
		$course_id  = (int) get_post_meta( $product_id, '_zc_linked_course', true );
		if ( $course_id && $user_id ) {
			$changed = $wpdb->update( $wpdb->prefix . 'zc_enrollments', array( 'status' => 'refunded' ), array( 'user_id' => $user_id, 'course_id' => $course_id, 'order_id' => (int) $order_id, 'status' => 'active' ), array( '%s' ), array( '%d', '%d', '%d', '%s' ) ); // phpcs:ignore
			if ( $changed ) {
				update_post_meta( $course_id, '_zc_students', max( 0, (int) get_post_meta( $course_id, '_zc_students', true ) - 1 ) );
			}
		}
		$wpdb->update( $wpdb->prefix . 'zc_licenses', array( 'status' => 'revoked' ), array( 'order_id' => (int) $order_id, 'product_id' => $product_id ), array( '%s' ), array( '%d', '%d' ) ); // phpcs:ignore
		$plan_id = function_exists( 'zc_subscription_plan_by_product' ) ? zc_subscription_plan_by_product( $product_id ) : 0;
		$rec     = $user_id ? zc_subscription_get_user( $user_id ) : array();
		if ( $plan_id && (int) ( $rec['order_id'] ?? 0 ) === (int) $order_id && (int) ( $rec['plan_id'] ?? 0 ) === (int) $plan_id ) {
			$rec['status'] = 'refunded'; $rec['expires'] = time(); update_user_meta( $user_id, 'zc_subscription', $rec );
		}
	}

	$order->update_meta_data( '_zc_refund_processed_' . $refund_id, current_time( 'mysql' ) );
	$order->save();
}
add_action( 'woocommerce_order_refunded', 'zc_reverse_partial_order_benefits', 10, 2 );

/**
 * is_active باید وضعیت بازپرداخت/لغو را نیز رعایت کند.
 *
 * @param bool  $active  نتیجه.
 * @param int   $user_id کاربر.
 * @param array $record  رکورد.
 * @return bool
 */
function zc_subscription_status_guard( $active, $user_id, $record ) {
	return $active && in_array( (string) ( $record['status'] ?? 'active' ), array( 'active', 'renewed', 'upgraded' ), true );
}
add_filter( 'zc_subscription_is_active', 'zc_subscription_status_guard', 10, 3 );
