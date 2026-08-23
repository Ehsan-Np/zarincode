<?php
/**
 * سیستم حسابداری و گزارش‌گیری مالی
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * دریافت خلاصه مالی در بازه زمانی.
 *
 * @param string $from از تاریخ.
 * @param string $to   تا تاریخ.
 * @return array
 */
function zc_accounting_summary( $from = '', $to = '' ) {
	global $wpdb;

	$table = $wpdb->prefix . 'zc_transactions';
	$from  = $from ? $from : gmdate( 'Y-m-01 00:00:00' );
	$to    = $to ? $to : current_time( 'mysql' );

	// phpcs:disable
	$income = (float) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COALESCE(SUM(amount),0) FROM {$table} WHERE type = 'income' AND status='completed' AND created_at BETWEEN %s AND %s",
			$from, $to
		)
	);

	$expense = (float) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COALESCE(SUM(ABS(amount)),0) FROM {$table} WHERE type IN ('expense','refund') AND status='completed' AND created_at BETWEEN %s AND %s",
			$from, $to
		)
	);

	$count = (int) $wpdb->get_var(
		$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE created_at BETWEEN %s AND %s", $from, $to )
	);
	// phpcs:enable

	$orders_total = 0;
	$orders_count = 0;

	if ( function_exists( 'wc_get_orders' ) ) {
		$orders = wc_get_orders(
			array(
				'limit'        => -1,
				'status'       => array( 'completed', 'processing' ),
				'date_created' => strtotime( $from ) . '...' . strtotime( $to ),
				'return'       => 'objects',
			)
		);
		foreach ( $orders as $order ) {
			$orders_total += (float) $order->get_total();
			$orders_count++;
		}
	}

	return array(
		'income'       => $income,
		'expense'      => $expense,
		'profit'       => $income - $expense,
		'tx_count'     => $count,
		'orders_total' => $orders_total,
		'orders_count' => $orders_count,
		'from'         => $from,
		'to'           => $to,
	);
}

/**
 * درآمد به تفکیک روز (برای نمودار).
 *
 * @param int $days تعداد روز.
 * @return array
 */
function zc_accounting_daily( $days = 30 ) {
	global $wpdb;

	$table = $wpdb->prefix . 'zc_transactions';
	$from  = gmdate( 'Y-m-d 00:00:00', strtotime( "-{$days} days" ) );

	$rows = $wpdb->get_results( // phpcs:ignore
		$wpdb->prepare(
			"SELECT DATE(created_at) AS day, COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END),0) AS income
			FROM {$table} WHERE created_at >= %s GROUP BY DATE(created_at) ORDER BY day ASC",
			$from
		),
		ARRAY_A
	);

	$out = array();
	for ( $i = $days; $i >= 0; $i-- ) {
		$date         = gmdate( 'Y-m-d', strtotime( "-{$i} days" ) );
		$out[ $date ] = 0;
	}

	foreach ( $rows as $row ) {
		$out[ $row['day'] ] = (float) $row['income'];
	}

	return $out;
}

/**
 * ثبت هزینه دستی.
 *
 * @param float  $amount مبلغ.
 * @param string $desc   توضیح.
 * @param string $cat    دسته.
 * @return int|false
 */
function zc_add_expense( $amount, $desc, $cat = 'general' ) {
	return zc_add_transaction(
		array(
			'user_id'     => 0,
			'amount'      => -abs( (float) $amount ),
			'type'        => 'expense',
			'category'    => $cat,
			'description' => $desc,
			'status'      => 'completed',
		)
	);
}

/**
 * خروجی CSV گزارش مالی.
 *
 * @return void
 */
function zc_export_accounting_csv() {
	if ( ! isset( $_GET['zc_export'] ) || 'accounting' !== $_GET['zc_export'] ) { // phpcs:ignore
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'دسترسی غیرمجاز.', 'zarincode' ) );
	}
	check_admin_referer( 'zc_export_accounting' );

	global $wpdb;
	$table = $wpdb->prefix . 'zc_transactions';
	$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC LIMIT 10000", ARRAY_A ); // phpcs:ignore

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=zarincode-accounting-' . gmdate( 'Y-m-d' ) . '.csv' );

	$out = fopen( 'php://output', 'w' );
	fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ); // BOM برای اکسل فارسی.

	fputcsv( $out, array( 'شناسه', 'کاربر', 'مبلغ', 'نوع', 'دسته', 'وضعیت', 'توضیح', 'کد پیگیری', 'درگاه', 'تاریخ' ) );

	foreach ( $rows as $row ) {
		$user = get_user_by( 'id', $row['user_id'] );
		fputcsv(
			$out,
			array(
				$row['id'],
				$user ? $user->display_name : '-',
				$row['amount'],
				$row['type'],
				$row['category'],
				$row['status'],
				$row['description'],
				$row['ref_id'],
				$row['gateway'],
				$row['created_at'],
			)
		);
	}

	fclose( $out );
	exit;
}
add_action( 'admin_init', 'zc_export_accounting_csv' );

/**
 * آمار کلی سایت برای داشبورد.
 *
 * @return array
 */
function zc_site_stats() {
	$cache = get_transient( 'zc_site_stats' );
	if ( false !== $cache ) {
		return $cache;
	}

	$stats = array(
		'courses'   => (int) wp_count_posts( 'zc_course' )->publish,
		'tutorials' => (int) wp_count_posts( 'zc_tutorial' )->publish,
		'posts'     => (int) wp_count_posts( 'post' )->publish,
		'students'  => (int) count_users()['total_users'],
		'tickets'   => (int) wp_count_posts( 'zc_ticket' )->publish,
		'products'  => zc_is_woo() ? (int) wp_count_posts( 'product' )->publish : 0,
	);

	set_transient( 'zc_site_stats', $stats, 15 * MINUTE_IN_SECONDS );

	return $stats;
}

/**
 * درخواست تسویه حساب مدرس/همکار.
 *
 * @return void
 */
function zc_ajax_withdraw_request() {
	zc_check_ajax();

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'ابتدا وارد شوید.', 'zarincode' ) ) );
	}

	$amount = isset( $_POST['amount'] ) ? (float) zc_en_num( sanitize_text_field( wp_unslash( $_POST['amount'] ) ) ) : 0;
	$sheba  = isset( $_POST['sheba'] ) ? sanitize_text_field( wp_unslash( $_POST['sheba'] ) ) : '';
	$min    = (float) zc_opt( 'zc_withdraw_min', 100000 );

	if ( $amount < $min ) {
		wp_send_json_error(
			array(
				'message' => sprintf(
					/* translators: %s: min amount */
					__( 'حداقل مبلغ برداشت %s است.', 'zarincode' ),
					zc_fa_num( number_format( $min ) )
				),
			)
		);
	}

	if ( $amount > zc_wallet_balance() ) {
		wp_send_json_error( array( 'message' => __( 'موجودی کافی نیست.', 'zarincode' ) ) );
	}

	if ( ! $sheba ) {
		wp_send_json_error( array( 'message' => __( 'شماره شبا را وارد کنید.', 'zarincode' ) ) );
	}

	update_user_meta( get_current_user_id(), 'zc_sheba', $sheba );

	zc_add_transaction(
		array(
			'user_id'     => get_current_user_id(),
			'amount'      => -$amount,
			'type'        => 'withdraw_request',
			'category'    => 'payout',
			'status'      => 'pending',
			'description' => sprintf( /* translators: %s: sheba */ __( 'درخواست تسویه به شبا %s', 'zarincode' ), $sheba ),
		)
	);

	wp_send_json_success( array( 'message' => __( 'درخواست تسویه ثبت شد و پس از بررسی واریز می‌شود.', 'zarincode' ) ) );
}
add_action( 'wp_ajax_zc_withdraw_request', 'zc_ajax_withdraw_request' );
