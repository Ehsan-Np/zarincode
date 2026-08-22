<?php
/**
 * داشبورد اجرایی (KPI) زرین کد
 * ---------------------------------------------------------------------------
 * یک صفحه‌ی متمرکز با آمار کلیدی فروش، درآمد، مشترک‌ها، کوپن‌ها و عملکرد.
 * از داده‌های موجود (گزارش فروش و اشتراک) استفاده می‌کند و نمودار تعاملی
 * با Chart.js اضافه می‌کند.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * ثبت صفحه‌ی KPI در زیرمنوی زرین کد.
 *
 * @return void
 */
function zc_kpi_menu() {
	add_submenu_page(
		'zarincode',
		__( 'داشبورد اجرایی (KPI)', 'zarincode' ),
		__( 'داشبورد KPI', 'zarincode' ),
		'manage_options',
		'zarincode-kpi',
		'zc_kpi_page'
	);
}
add_action( 'admin_menu', 'zc_kpi_menu' );

/**
 * داده‌ی KPI.
 *
 * @return array
 */
function zc_kpi_data() {
	$days = (int) zc_opt( 'zc_kpi_days', 30 );
	$from = gmdate( 'Y-m-d', strtotime( "-{$days} days" ) );
	$to   = gmdate( 'Y-m-d' );

	$data = array(
		'revenue'       => 0,
		'orders'        => 0,
		'avg_order'     => 0,
		'customers'     => 0,
		'products_sold' => 0,
		'coupons_used'  => 0,
		'daily'         => array(),
		'top_products'  => array(),
		'new_customers' => 0,
		'mrr'           => 0,
		'subscribers'   => 0,
	);

	if ( function_exists( 'wc_get_orders' ) ) {
		$orders = wc_get_orders(
			array(
				'limit'        => -1,
				'status'       => array( 'completed', 'processing' ),
				'date_created' => strtotime( $from ) . '...' . ( strtotime( $to ) + DAY_IN_SECONDS ),
			)
		);

		$product_totals = array();
		$customer_ids   = array();

		foreach ( $orders as $order ) {
			$data['revenue'] += (float) $order->get_total();
			$data['orders']++;
			$data['coupons_used'] += count( $order->get_coupon_codes() );

			$created = $order->get_date_created()->date( 'Y-m-d' );
			$data['daily'][ $created ] = (float) ( $data['daily'][ $created ] ?? 0 ) + (float) $order->get_total();

			foreach ( $order->get_items() as $item ) {
				$data['products_sold'] += (int) $item->get_quantity();
				$product_totals[ $item->get_product_id() ] = (float) ( $product_totals[ $item->get_product_id() ] ?? 0 ) + (float) $item->get_total();
			}

			if ( $order->get_user_id() ) {
				$customer_ids[ $order->get_user_id() ] = true;
			}
		}

		$data['customers']    = count( $customer_ids );
		$data['avg_order']    = $data['orders'] ? round( $data['revenue'] / $data['orders'] ) : 0;
		arsort( $product_totals );
		$data['top_products'] = array_slice( $product_totals, 0, 6, true );
		krsort( $data['daily'] );
		$data['daily'] = array_slice( $data['daily'], 0, $days, true );
		$data['daily'] = array_reverse( $data['daily'] );
	}

	// مشترک‌های فعال و MRR.
	if ( function_exists( 'zc_subscription_report' ) ) {
		$sub            = zc_subscription_report();
		$data['subscribers'] = $sub['active_count'];
		$data['mrr']         = round( $sub['mrr'] );
	}

	// کاربران جدید در بازه.
	$data['new_customers'] = (int) get_users(
		array(
			'count_total' => true,
			'date_query'  => array(
				array( 'after' => $from, 'inclusive' => true ),
			),
			'fields'      => 'ID',
			'number'      => 1,
		)
	) ?: 0;

	return $data;
}

/**
 * صفحه‌ی KPI.
 *
 * @return void
 */
function zc_kpi_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$data = zc_kpi_data();
	$days = (int) zc_opt( 'zc_kpi_days', 30 );
	?>
	<div class="wrap zc-admin-wrap">
		<?php zc_admin_notice_anchor(); ?>
		<div class="zc-admin-header">
			<div><h1><?php esc_html_e( 'داشبورد اجرایی (KPI)', 'zarincode' ); ?></h1></div>
			<div class="zc-admin-header__actions">
				<form method="get" style="display:inline-flex;gap:8px;align-items:center">
					<input type="hidden" name="page" value="zarincode-kpi">
					<select name="zc_kpi_days">
						<option value="7" <?php selected( $days, 7 ); ?>>7 <?php esc_html_e( 'روز', 'zarincode' ); ?></option>
						<option value="30" <?php selected( $days, 30 ); ?>>30 <?php esc_html_e( 'روز', 'zarincode' ); ?></option>
						<option value="90" <?php selected( $days, 90 ); ?>>90 <?php esc_html_e( 'روز', 'zarincode' ); ?></option>
						<option value="365" <?php selected( $days, 365 ); ?>>365 <?php esc_html_e( 'روز', 'zarincode' ); ?></option>
					</select>
					<button class="button button-primary"><?php esc_html_e( 'اعمال', 'zarincode' ); ?></button>
				</form>
			</div>
		</div>

		<div class="zc-admin-stats" style="grid-template-columns:repeat(4,1fr)">
			<div class="zc-admin-stat"><span class="zc-admin-stat__icon" style="background:#C9A2271a;color:#C9A227"><span class="dashicons dashicons-chart-area"></span></span><div><strong><?php echo esc_html( zc_fa_num( number_format( $data['revenue'] ) ) ); ?></strong><span><?php esc_html_e( 'درآمد', 'zarincode' ); ?></span></div></div>
			<div class="zc-admin-stat"><span class="zc-admin-stat__icon" style="background:#2563EB1a;color:#2563EB"><span class="dashicons dashicons-cart"></span></span><div><strong><?php echo esc_html( zc_fa_num( $data['orders'] ) ); ?></strong><span><?php esc_html_e( 'سفارش', 'zarincode' ); ?></span></div></div>
			<div class="zc-admin-stat"><span class="zc-admin-stat__icon" style="background:#16A34A1a;color:#16A34A"><span class="dashicons dashicons-chart-line"></span></span><div><strong><?php echo esc_html( zc_fa_num( number_format( $data['avg_order'] ) ) ); ?></strong><span><?php esc_html_e( 'میانگین سفارش', 'zarincode' ); ?></span></div></div>
			<div class="zc-admin-stat"><span class="zc-admin-stat__icon" style="background:#7C3AED1a;color:#7C3AED"><span class="dashicons dashicons-users"></span></span><div><strong><?php echo esc_html( zc_fa_num( $data['customers'] ) ); ?></strong><span><?php esc_html_e( 'مشتری', 'zarincode' ); ?></span></div></div>
			<div class="zc-admin-stat"><span class="zc-admin-stat__icon" style="background:#0EA5E91a;color:#0EA5E9"><span class="dashicons dashicons-download"></span></span><div><strong><?php echo esc_html( zc_fa_num( $data['products_sold'] ) ); ?></strong><span><?php esc_html_e( 'اقلام فروخته‌شده', 'zarincode' ); ?></span></div></div>
			<div class="zc-admin-stat"><span class="zc-admin-stat__icon" style="background:#F59E0B1a;color:#F59E0B"><span class="dashicons dashicons-tickets-alt"></span></span><div><strong><?php echo esc_html( zc_fa_num( $data['coupons_used'] ) ); ?></strong><span><?php esc_html_e( 'کد تخفیف استفاده‌شده', 'zarincode' ); ?></span></div></div>
			<div class="zc-admin-stat"><span class="zc-admin-stat__icon" style="background:#DB27771a;color:#DB2777"><span class="dashicons dashicons-id-alt"></span></span><div><strong><?php echo esc_html( zc_fa_num( number_format( $data['mrr'] ) ) ); ?></strong><span><?php esc_html_e( 'درآمد ماهانه (MRR)', 'zarincode' ); ?></span></div></div>
			<div class="zc-admin-stat"><span class="zc-admin-stat__icon" style="background:#0891B21a;color:#0891B2"><span class="dashicons dashicons-star-filled"></span></span><div><strong><?php echo esc_html( zc_fa_num( $data['subscribers'] ) ); ?></strong><span><?php esc_html_e( 'مشترک فعال', 'zarincode' ); ?></span></div></div>
		</div>

		<div style="display:grid;grid-template-columns:1.6fr 1fr;gap:16px;margin-top:16px">
			<div class="zc-admin-box">
				<h2><?php esc_html_e( 'روند درآمد', 'zarincode' ); ?></h2>
				<canvas id="zc-kpi-chart" style="max-height:300px"></canvas>
			</div>
			<div class="zc-admin-box">
				<h2><?php esc_html_e( 'پرفروش‌ترین‌ها', 'zarincode' ); ?></h2>
				<table class="widefat striped">
					<tbody>
						<?php if ( $data['top_products'] ) : foreach ( $data['top_products'] as $pid => $amt ) : ?>
							<tr><td><?php echo esc_html( get_the_title( $pid ) ); ?></td><td><strong><?php echo esc_html( number_format( $amt ) ); ?></strong></td></tr>
						<?php endforeach; else : ?>
							<tr><td colspan="2"><?php esc_html_e( 'داده‌ای نیست.', 'zarincode' ); ?></td></tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

		<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
		<script>
		(function () {
			if (typeof window.Chart === 'undefined') return;
			var labels = <?php echo wp_json_encode( array_keys( $data['daily'] ) ); // phpcs:ignore ?>;
			var values = <?php echo wp_json_encode( array_values( $data['daily'] ) ); // phpcs:ignore ?>;
			var el = document.getElementById('zc-kpi-chart');
			if (el && values.length) {
				new Chart(el, { type: 'line', data: { labels: labels, datasets: [{ label: '<?php echo esc_js( __( 'درآمد', 'zarincode' ) ); ?>', data: values, borderColor: '#C9A227', backgroundColor: 'rgba(201,162,39,.15)', fill: true, tension: .35 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } } });
			}
		})();
		</script>
	</div>
	<?php
}
