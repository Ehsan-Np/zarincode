<?php
/**
 * تب سفارش‌ها
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

if ( ! zc_is_woo() ) {
	echo '<div class="zc-alert zc-alert--warning">' . esc_html__( 'فروشگاه فعال نیست.', 'zarincode' ) . '</div>';
	return;
}

$zc_orders = wc_get_orders(
	array(
		'customer_id' => get_current_user_id(),
		'limit'       => 30,
		'orderby'     => 'date',
		'order'       => 'DESC',
	)
);
?>

<div class="zc-panel__box" data-zc-anim="up">
	<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'cart', 19 ); ?><?php esc_html_e( 'تاریخچه سفارش‌ها', 'zarincode' ); ?></h3></div>
	<div class="zc-panel__box-body">
		<?php if ( $zc_orders ) : ?>
			<div class="zc-table-wrap">
				<table class="zc-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'شماره سفارش', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'تاریخ', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'محصولات', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'مبلغ', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'عملیات', 'zarincode' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $zc_orders as $zc_order ) : ?>
							<tr>
								<td><strong>#<?php echo esc_html( zc_fa_num( $zc_order->get_order_number() ) ); ?></strong></td>
								<td><?php echo esc_html( zc_fa_num( wp_date( 'Y/m/d', $zc_order->get_date_created()->getTimestamp() ) ) ); ?></td>
								<td>
									<?php
									$zc_names = array();
									foreach ( $zc_order->get_items() as $zc_item ) {
										$zc_names[] = $zc_item->get_name();
									}
									echo esc_html( zc_excerpt( implode( '، ', $zc_names ), 8 ) );
									?>
								</td>
								<td>
									<span class="zc-badge zc-badge--<?php echo $zc_order->is_paid() ? 'green' : ( 'cancelled' === $zc_order->get_status() ? 'red' : 'orange' ); ?>">
										<?php echo esc_html( wc_get_order_status_name( $zc_order->get_status() ) ); ?>
									</span>
								</td>
								<td><?php echo wp_kses_post( $zc_order->get_formatted_order_total() ); ?></td>
								<td style="display:flex;gap:6px">
									<a href="<?php echo esc_url( $zc_order->get_view_order_url() ); ?>" class="zc-btn zc-btn--ghost zc-btn--sm"><?php zc_the_icon( 'eye', 15 ); ?></a>
									<?php
									// دکمه‌ی دانلود فاکتور.
									if ( function_exists( 'zc_invoice_button' ) ) {
										zc_invoice_button( $zc_order->get_id() );
									}
									?>
									<?php if ( ! $zc_order->is_paid() && $zc_order->needs_payment() ) : ?>
										<a href="<?php echo esc_url( $zc_order->get_checkout_payment_url() ); ?>" class="zc-btn zc-btn--gold zc-btn--sm"><?php zc_the_icon( 'wallet', 15 ); ?><?php esc_html_e( 'پرداخت', 'zarincode' ); ?></a>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php else : ?>
			<div class="zc-empty">
				<div class="zc-empty__icon"><?php zc_the_icon( 'cart', 40 ); ?></div>
				<h3><?php esc_html_e( 'سفارشی ثبت نکرده‌اید', 'zarincode' ); ?></h3>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="zc-btn zc-btn--gold"><?php zc_the_icon( 'cart', 17 ); ?><?php esc_html_e( 'رفتن به فروشگاه', 'zarincode' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</div>
