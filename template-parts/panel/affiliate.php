<?php
/**
 * تب «همکاری در فروش» در پنل کاربری
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_user_id   = get_current_user_id();
$zc_link      = zc_affiliate_link( $zc_user_id );
$zc_percent   = zc_affiliate_percent();
$zc_min_with  = (float) zc_opt( 'zc_aff_min_withdraw', '50000' );
$zc_balance   = function_exists( 'zc_wallet_balance' ) ? zc_wallet_balance( $zc_user_id ) : 0;
$zc_aff_orders = get_posts(
	array(
		'post_type'      => 'shop_order',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
		'meta_key'       => '_zc_aff_ref', // phpcs:ignore
		'meta_value'     => $zc_user_id, // phpcs:ignore
	)
);
$zc_pending = 0;
$zc_paid    = 0;
foreach ( $zc_aff_orders as $oid ) {
	if ( get_post_meta( $oid, '_zc_aff_pending', true ) ) {
		$zc_pending += (float) get_post_meta( $oid, '_zc_aff_commission', true );
	} else {
		$zc_paid += (float) get_post_meta( $oid, '_zc_aff_commission', true );
	}
}
?>
<div class="zc-panel__box" data-zc-anim="up">
	<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'link', 19 ); ?><?php esc_html_e( 'لینک اختصاصی معرفی شما', 'zarincode' ); ?></h3></div>
	<div class="zc-panel__box-body">
		<p style="color:var(--zc-muted);font-size:.85rem;margin:0 0 12px">
			<?php printf( esc_html__( 'با هر لینکی که از طریق آن خرید ثبت شود، %s٪ کمیسیون به کیف پول شما واریز می‌شود. لینک را در شبکه‌های اجتماعی و پیام‌رسان‌ها به اشتراک بگذارید.', 'zarincode' ), esc_html( zc_fa_num( $zc_percent ) ) ); ?>
		</p>
		<div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
			<input type="text" id="zc-aff-link" value="<?php echo esc_url( $zc_link ); ?>" readonly dir="ltr"
				style="flex:1;min-width:240px;border:1px solid var(--zc-line);border-radius:10px;padding:11px 14px;font-size:.85rem">
			<button type="button" class="zc-btn zc-btn--gold zc-btn--sm" data-zc-copy="#zc-aff-link"><?php esc_html_e( 'کپی', 'zarincode' ); ?></button>
		</div>
	</div>
</div>

<div class="zc-panel__grid-2 zc-panel__grid-2--wide" style="margin-top:20px">
	<div class="zc-panel__box" data-zc-anim="up">
		<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'chart', 19 ); ?><?php esc_html_e( 'آمار کمیسیون شما', 'zarincode' ); ?></h3></div>
		<div class="zc-panel__box-body">
			<div class="zc-panel__stats" style="grid-template-columns:repeat(3,1fr)">
				<div class="zc-panel__stat"><strong><?php echo esc_html( zc_fa_num( number_format( $zc_paid ) ) ); ?></strong><span><?php esc_html_e( 'کمیسیون واریزشده', 'zarincode' ); ?></span></div>
				<div class="zc-panel__stat"><strong><?php echo esc_html( zc_fa_num( number_format( $zc_pending ) ) ); ?></strong><span><?php esc_html_e( 'در انتظار تأیید', 'zarincode' ); ?></span></div>
				<div class="zc-panel__stat"><strong><?php echo esc_html( zc_fa_num( number_format( $zc_balance ) ) ); ?></strong><span><?php esc_html_e( 'موجودی کیف پول', 'zarincode' ); ?></span></div>
			</div>
			<p style="font-size:.8rem;color:var(--zc-muted);margin-top:14px">
				<?php printf( esc_html__( 'حداقل موجودی برای درخواست تسویه: %s تومان.', 'zarincode' ), esc_html( zc_fa_num( number_format( $zc_min_with ) ) ) ); ?>
			</p>
		</div>
	</div>

	<div class="zc-panel__box" data-zc-anim="up">
		<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'cart', 19 ); ?><?php esc_html_e( 'سفارش‌های ثبت‌شده با لینک شما', 'zarincode' ); ?></h3></div>
		<div class="zc-panel__box-body">
			<?php if ( $zc_aff_orders ) : ?>
				<div class="zc-table-wrap">
					<table class="zc-table">
						<thead><tr><th><?php esc_html_e( 'سفارش', 'zarincode' ); ?></th><th><?php esc_html_e( 'کمیسیون', 'zarincode' ); ?></th><th><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></th></tr></thead>
						<tbody>
							<?php foreach ( $zc_aff_orders as $oid ) : ?>
								<?php $amount = (float) get_post_meta( $oid, '_zc_aff_commission', true ); $is_pending = get_post_meta( $oid, '_zc_aff_pending', true ); ?>
								<tr>
									<td><strong>#<?php echo esc_html( zc_fa_num( $oid ) ); ?></strong></td>
									<td><?php echo esc_html( zc_fa_num( number_format( $amount ) ) ); ?> <?php echo esc_html( zc_opt( 'zc_currency_symbol', 'تومان' ) ); ?></td>
									<td><?php echo $is_pending ? '<span class="zc-badge zc-badge--orange">' . esc_html__( 'در انتظار', 'zarincode' ) . '</span>' : '<span class="zc-badge zc-badge--green">' . esc_html__( 'واریزشده', 'zarincode' ) . '</span>'; ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<div class="zc-empty" style="padding:24px 12px"><p><?php esc_html_e( 'هنوز فروشی با لینک شما ثبت نشده است.', 'zarincode' ); ?></p></div>
			<?php endif; ?>
		</div>
	</div>
</div>
