<?php
/**
 * خرید اقساطی دوره از کیف پول / سفارش.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * جدول اقساط.
 *
 * @return void
 */
function zc_create_installments_table() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset = $wpdb->get_charset_collate();
	$table   = $wpdb->prefix . 'zc_installments';
	$sql     = "CREATE TABLE {$table} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id BIGINT(20) UNSIGNED NOT NULL,
		course_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		order_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		total DECIMAL(18,2) NOT NULL DEFAULT 0,
		parts SMALLINT UNSIGNED NOT NULL DEFAULT 2,
		paid_parts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
		paid_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
		next_due DATE NULL,
		status VARCHAR(20) NOT NULL DEFAULT 'active',
		meta LONGTEXT NULL,
		created_at DATETIME NULL DEFAULT NULL,
		PRIMARY KEY (id),
		KEY user_id (user_id),
		KEY course_id (course_id),
		KEY status (status)
	) {$charset};";
	dbDelta( $sql );
}

/**
 * افزودن تب اقساط.
 *
 * @param array $tabs تب‌ها.
 * @return array
 */
function zc_installments_panel_tab( $tabs ) {
	if ( zc_opt( 'zc_installments_enable', true ) ) {
		$tabs['installments'] = array(
			'label' => __( 'اقساط', 'zarincode' ),
			'icon'  => 'calendar',
			'order' => 42,
		);
	}
	return $tabs;
}
add_filter( 'zc_panel_tabs', 'zc_installments_panel_tab' );

/**
 * اقساط کاربر.
 *
 * @param int $user_id کاربر.
 * @return array
 */
function zc_user_installments( $user_id = 0 ) {
	global $wpdb;
	$user_id = $user_id ? $user_id : get_current_user_id();
	$table   = $wpdb->prefix . 'zc_installments';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) { // phpcs:ignore
		return array();
	}
	return (array) $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE user_id=%d ORDER BY id DESC", $user_id ) ); // phpcs:ignore
}

/**
 * مبلغ هر قسط.
 *
 * @param object $plan طرح.
 * @return float
 */
function zc_installment_part_amount( $plan ) {
	$remaining_parts = max( 1, (int) $plan->parts - (int) $plan->paid_parts );
	$remaining_amt   = max( 0, (float) $plan->total - (float) $plan->paid_amount );
	return round( $remaining_amt / $remaining_parts, 0 );
}

/**
 * ایجاد طرح اقساط پس از سفارش.
 *
 * @param int $order_id سفارش.
 * @return void
 */
function zc_installments_on_order( $order_id ) {
	if ( ! zc_opt( 'zc_installments_enable', true ) || ! function_exists( 'wc_get_order' ) ) {
		return;
	}
	$order = wc_get_order( $order_id );
	if ( ! $order || ! $order->is_paid() || $order->get_meta( '_zc_installment_built' ) ) {
		return;
	}
	$parts = (int) $order->get_meta( '_zc_installment_parts' );
	if ( $parts < 2 ) {
		return;
	}
	$user_id = (int) $order->get_user_id();
	if ( ! $user_id ) {
		return;
	}

	global $wpdb;
	$table = $wpdb->prefix . 'zc_installments';
	foreach ( $order->get_items() as $item ) {
		$course_id = (int) get_post_meta( $item->get_product_id(), '_zc_linked_course', true );
		if ( ! $course_id ) {
			continue;
		}
		$full_meta = (float) $order->get_meta( '_zc_installment_full_total' );
		$line      = (float) $item->get_total();
		$items_sum = 0;
		foreach ( $order->get_items() as $it ) {
			$items_sum += (float) $it->get_total();
		}
		$total = ( $full_meta > 0 && $items_sum > 0 ) ? ( $full_meta * ( $line / $items_sum ) ) : $line;
		$first = ( $items_sum > 0 )
			? round( ( (float) $order->get_total() ) * ( $line / $items_sum ), 0 )
			: round( $total / $parts, 0 );
		$wpdb->insert( // phpcs:ignore
			$table,
			array(
				'user_id'     => $user_id,
				'course_id'   => $course_id,
				'order_id'    => $order_id,
				'total'       => $total,
				'parts'       => $parts,
				'paid_parts'  => 1,
				'paid_amount' => $first,
				'next_due'    => wp_date( 'Y-m-d', time() + 30 * DAY_IN_SECONDS ),
				'status'      => 'active',
				'created_at'  => current_time( 'mysql' ),
			)
		);
		zc_enroll_user( $user_id, $course_id, $order_id, $first );
		if ( function_exists( 'zc_audit' ) ) {
			zc_audit( 'installment_start', 'course', $course_id, array( 'parts' => $parts, 'order' => $order_id ) );
		}
	}
	$order->update_meta_data( '_zc_installment_built', '1' );
	$order->save();
}
add_action( 'woocommerce_order_status_processing', 'zc_installments_on_order', 45 );
add_action( 'woocommerce_order_status_completed', 'zc_installments_on_order', 45 );

/**
 * فیلد تعداد اقساط در تسویه.
 *
 * @return void
 */
function zc_installments_checkout_field() {
	if ( ! zc_opt( 'zc_installments_enable', true ) || ! is_user_logged_in() ) {
		return;
	}
	$max = max( 2, min( 12, (int) zc_opt( 'zc_installments_max', 4 ) ) );
	?>
	<div class="zc-wallet-pay" style="margin:12px 0">
		<label><?php esc_html_e( 'پرداخت اقساطی دوره (اختیاری)', 'zarincode' ); ?></label>
		<select name="zc_installment_parts" class="update_totals_on_change">
			<option value="0"><?php esc_html_e( 'پرداخت کامل', 'zarincode' ); ?></option>
			<?php for ( $i = 2; $i <= $max; $i++ ) : ?>
				<option value="<?php echo (int) $i; ?>"><?php echo esc_html( sprintf( __( '%s قسط ماهانه', 'zarincode' ), zc_fa_num( $i ) ) ); ?></option>
			<?php endfor; ?>
		</select>
	</div>
	<?php
}
add_action( 'woocommerce_review_order_before_payment', 'zc_installments_checkout_field', 20 );

/**
 * تعداد اقساط درخواستی از POST یا نشست تسویه.
 *
 * @return int
 */
function zc_installments_requested_parts() {
	$max   = max( 2, min( 12, (int) zc_opt( 'zc_installments_max', 4 ) ) );
	$parts = 0;
	if ( isset( $_POST['zc_installment_parts'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$parts = absint( wp_unslash( $_POST['zc_installment_parts'] ) ); // phpcs:ignore
	} elseif ( function_exists( 'WC' ) && WC()->session ) {
		$parts = (int) WC()->session->get( 'zc_installment_parts', 0 );
	}
	return ( $parts >= 2 && $parts <= $max ) ? $parts : 0;
}

/**
 * ذخیره انتخاب اقساط هنگام به‌روزرسانی آجاکس تسویه.
 *
 * @param string $posted دادهٔ فرم.
 * @return void
 */
function zc_installments_remember_parts( $posted ) {
	if ( ! function_exists( 'WC' ) || ! WC()->session ) {
		return;
	}
	parse_str( (string) $posted, $data );
	if ( isset( $data['zc_installment_parts'] ) ) {
		WC()->session->set( 'zc_installment_parts', absint( $data['zc_installment_parts'] ) );
	}
}
add_action( 'woocommerce_checkout_update_order_review', 'zc_installments_remember_parts' );

/**
 * کسر اقساط باقی‌مانده از جمع تسویه تا فقط قسط اول از درگاه گرفته شود.
 *
 * @param WC_Cart $cart سبد.
 * @return void
 */
function zc_installments_cart_fee( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}
	$parts = zc_installments_requested_parts();
	if ( $parts < 2 || ! $cart ) {
		return;
	}
	$base = (float) $cart->get_cart_contents_total() + (float) $cart->get_shipping_total();
	if ( $base <= 0 ) {
		return;
	}
	$defer = round( $base * ( $parts - 1 ) / $parts, wc_get_price_decimals() );
	if ( $defer > 0 ) {
		$cart->add_fee(
			sprintf( /* translators: %d remaining installments */ __( 'اقساط باقی‌مانده (%d قسط)', 'zarincode' ), $parts - 1 ),
			-1 * $defer,
			false
		);
	}
}
add_action( 'woocommerce_cart_calculate_fees', 'zc_installments_cart_fee', 20 );

/**
 * ذخیره انتخاب اقساط روی سفارش.
 *
 * @param int $order_id سفارش.
 * @return void
 */
function zc_installments_save_checkout( $order_id ) {
	if ( empty( $_POST['zc_installment_parts'] ) ) { // phpcs:ignore
		return;
	}
	$parts = absint( wp_unslash( $_POST['zc_installment_parts'] ) ); // phpcs:ignore
	$max   = max( 2, min( 12, (int) zc_opt( 'zc_installments_max', 4 ) ) );
	if ( $parts >= 2 && $parts <= $max && function_exists( 'wc_get_order' ) ) {
		$order = wc_get_order( $order_id );
		if ( $order ) {
			$order->update_meta_data( '_zc_installment_parts', $parts );
			$order->save();
		}
	}
}
add_action( 'woocommerce_checkout_update_order_meta', 'zc_installments_save_checkout' );

/**
 * پرداخت قسط بعدی از کیف پول.
 *
 * @return void
 */
function zc_ajax_pay_installment() {
	zc_check_ajax();
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'ابتدا وارد شوید.', 'zarincode' ) ) );
	}
	$id   = isset( $_POST['plan_id'] ) ? absint( $_POST['plan_id'] ) : 0;
	$user = get_current_user_id();
	global $wpdb;
	$table = $wpdb->prefix . 'zc_installments';
	$plan  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id=%d AND user_id=%d LIMIT 1", $id, $user ) ); // phpcs:ignore
	if ( ! $plan || 'active' !== $plan->status ) {
		wp_send_json_error( array( 'message' => __( 'طرح اقساط معتبر نیست.', 'zarincode' ) ) );
	}
	$amount = zc_installment_part_amount( $plan );
	if ( $amount <= 0 ) {
		wp_send_json_error( array( 'message' => __( 'قسطی باقی نمانده است.', 'zarincode' ) ) );
	}
	$tx = zc_wallet_withdraw( $user, $amount, sprintf( __( 'پرداخت قسط دوره «%s»', 'zarincode' ), get_the_title( $plan->course_id ) ), 'installment', array( 'ref_id' => 'inst-' . $plan->id . '-' . ( (int) $plan->paid_parts + 1 ), 'gateway' => 'wallet' ) );
	if ( is_wp_error( $tx ) ) {
		wp_send_json_error( array( 'message' => $tx->get_error_message() ) );
	}
	$paid_parts  = (int) $plan->paid_parts + 1;
	$paid_amount = (float) $plan->paid_amount + $amount;
	$status      = $paid_parts >= (int) $plan->parts ? 'completed' : 'active';
	$wpdb->update( // phpcs:ignore
		$table,
		array(
			'paid_parts'  => $paid_parts,
			'paid_amount' => $paid_amount,
			'next_due'    => 'completed' === $status ? null : wp_date( 'Y-m-d', time() + 30 * DAY_IN_SECONDS ),
			'status'      => $status,
		),
		array( 'id' => $plan->id )
	);
	if ( function_exists( 'zc_audit' ) ) {
		zc_audit( 'installment_pay', 'installment', (int) $plan->id, array( 'amount' => $amount, 'part' => $paid_parts ) );
	}
	wp_send_json_success( array( 'message' => __( 'قسط با موفقیت پرداخت شد.', 'zarincode' ), 'reload' => true ) );
}
add_action( 'wp_ajax_zc_pay_installment', 'zc_ajax_pay_installment' );
