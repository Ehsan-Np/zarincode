<?php
/** پنل لایسنس‌های محصولات. @package Zarincode */
defined( 'ABSPATH' ) || exit;
$zc_licenses = function_exists( 'zc_user_licenses' ) ? zc_user_licenses() : array();
?>
<div class="zc-panel__box">
	<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'key', 19 ); ?><?php esc_html_e( 'لایسنس‌های محصولات من', 'zarincode' ); ?></h3></div>
	<div class="zc-panel__box-body">
	<?php if ( ! $zc_licenses ) : ?>
		<div class="zc-empty"><p><?php esc_html_e( 'هنوز لایسنسی برای شما صادر نشده است.', 'zarincode' ); ?></p></div>
	<?php else : ?>
		<div class="zc-table-wrap"><table class="zc-table"><thead><tr><th><?php esc_html_e( 'محصول', 'zarincode' ); ?></th><th><?php esc_html_e( 'کلید', 'zarincode' ); ?></th><th><?php esc_html_e( 'فعال‌سازی', 'zarincode' ); ?></th><th><?php esc_html_e( 'اعتبار', 'zarincode' ); ?></th><th><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></th></tr></thead><tbody>
		<?php foreach ( $zc_licenses as $license ) : $acts = json_decode( $license->activations, true ) ?: array(); ?>
		<tr><td><?php echo esc_html( get_the_title( $license->product_id ) ); ?></td><td><code dir="ltr"><?php echo esc_html( $license->license_key ); ?></code> <button type="button" class="zc-btn zc-btn--ghost zc-btn--sm" data-zc-copy-text="<?php echo esc_attr( $license->license_key ); ?>"><?php esc_html_e( 'کپی', 'zarincode' ); ?></button></td><td><?php echo esc_html( count( $acts ) . ' / ' . (int) $license->activation_limit ); ?></td><td><?php echo $license->expires_at ? esc_html( zc_jalali_date( 'Y/m/d', strtotime( $license->expires_at ) ) ) : esc_html__( 'نامحدود', 'zarincode' ); ?></td><td><?php echo esc_html( $license->status ); ?></td></tr>
		<?php endforeach; ?>
		</tbody></table></div>
	<?php endif; ?>
	</div>
</div>
