<?php
/**
 * ویجت داشبورد وردپرس
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * ثبت ویجت داشبورد.
 *
 * @return void
 */
function zc_add_dashboard_widget() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	wp_add_dashboard_widget(
		'zc_dashboard_widget',
		'⭐ ' . __( 'خلاصه وضعیت زرین کد', 'zarincode' ),
		'zc_dashboard_widget_content'
	);
}
add_action( 'wp_dashboard_setup', 'zc_add_dashboard_widget' );

/**
 * محتوای ویجت.
 *
 * @return void
 */
function zc_dashboard_widget_content() {
	$stats   = zc_site_stats();
	$summary = zc_accounting_summary();
	?>
	<div class="zc-dash-widget">
		<div class="zc-dash-widget__grid">
			<div><strong><?php echo esc_html( zc_fa_num( $stats['courses'] ) ); ?></strong><span><?php esc_html_e( 'دوره', 'zarincode' ); ?></span></div>
			<div><strong><?php echo esc_html( zc_fa_num( $stats['products'] ) ); ?></strong><span><?php esc_html_e( 'محصول', 'zarincode' ); ?></span></div>
			<div><strong><?php echo esc_html( zc_fa_num( $stats['students'] ) ); ?></strong><span><?php esc_html_e( 'کاربر', 'zarincode' ); ?></span></div>
			<div><strong><?php echo esc_html( zc_fa_num( $stats['tickets'] ) ); ?></strong><span><?php esc_html_e( 'تیکت', 'zarincode' ); ?></span></div>
		</div>

		<p style="margin:14px 0 8px;padding-top:12px;border-top:1px solid #eee">
			<strong><?php esc_html_e( 'درآمد این ماه:', 'zarincode' ); ?></strong>
			<span style="color:#16A34A;font-weight:700"><?php echo esc_html( zc_fa_num( number_format( $summary['income'] ) ) ); ?> <?php echo esc_html( zc_opt( 'zc_currency_symbol', 'تومان' ) ); ?></span>
		</p>

		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=zarincode' ) ); ?>" class="button button-primary button-small"><?php esc_html_e( 'داشبورد کامل', 'zarincode' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=zarincode-options' ) ); ?>" class="button button-small"><?php esc_html_e( 'تنظیمات', 'zarincode' ); ?></a>
		</p>
	</div>

	<style>
	.zc-dash-widget__grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;text-align:center}
	.zc-dash-widget__grid div{background:#f6f7f7;border-radius:8px;padding:10px 6px}
	.zc-dash-widget__grid strong{display:block;font-size:1.3rem;color:#C9A227}
	.zc-dash-widget__grid span{font-size:.76rem;color:#666}
	</style>
	<?php
}
