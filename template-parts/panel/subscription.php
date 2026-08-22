<?php
/**
 * تب «اشتراک من» در پنل کاربری
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_user   = wp_get_current_user();
$zc_active = zc_subscription_is_active( $zc_user->ID );
$zc_rec    = zc_subscription_get_user( $zc_user->ID );
$zc_plan   = $zc_active && ! empty( $zc_rec['plan_id'] ) ? get_post( (int) $zc_rec['plan_id'] ) : null;
$zc_usage  = zc_subscription_usage( $zc_user->ID );
$zc_notice = zc_subscription_take_notice( $zc_user->ID );

if ( $zc_notice ) :
	?>
	<div class="zc-alert zc-alert--warning" style="margin-bottom:20px">
		<?php zc_the_icon( 'alert', 20 ); ?><span><?php echo esc_html( $zc_notice ); ?></span>
	</div>
<?php endif; ?>

<?php if ( $zc_active && $zc_plan ) : ?>
	<?php $zc_pd = zc_subscription_plan_data( $zc_plan->ID ); ?>
	<div class="zc-panel__box" data-zc-anim="up">
		<div class="zc-panel__box-head">
			<h3><?php zc_the_icon( 'star', 19 ); ?><?php esc_html_e( 'اشتراک فعلی شما', 'zarincode' ); ?></h3>
		</div>
		<div class="zc-panel__box-body">
			<div class="zc-sub-current">
				<div style="display:flex;flex-wrap:wrap;gap:16px;justify-content:space-between;align-items:center">
					<div>
						<strong style="font-size:1.15rem"><?php echo esc_html( $zc_plan->post_title ); ?></strong>
						<div style="color:var(--zc-muted,#6B7280);font-size:.85rem;margin-top:4px">
							<?php zc_the_icon( 'calendar', 15 ); ?>
							<?php
							if ( ! empty( $zc_rec['expires'] ) ) :
								printf(
									esc_html__( 'اعتبار تا %s', 'zarincode' ),
									esc_html( zc_fa_num( date_i18n( 'Y/m/d', $zc_rec['expires'] ) ) )
								);
							else :
								esc_html_e( 'مادام‌العمر', 'zarincode' );
							endif;
							?>
						</div>
					</div>
					<span class="zc-badge zc-badge--green"><?php esc_html_e( 'فعال', 'zarincode' ); ?></span>
				</div>

				<?php if ( ! empty( $zc_rec['expires'] ) ) : ?>
					<div style="margin-top:14px;color:var(--zc-muted,#6B7280);font-size:.85rem">
						<?php
						$zc_days = (int) ceil( ( (int) $zc_rec['expires'] - time() ) / DAY_IN_SECONDS );
						printf(
							esc_html__( 'روز باقی‌مانده تا پایان اشتراک: %s', 'zarincode' ),
							esc_html( zc_fa_num( max( 0, $zc_days ) ) )
						);
						?>
					</div>
				<?php endif; ?>

				<?php
				$zc_pending_dn = zc_subscription_pending_plan( $zc_user->ID );
				if ( $zc_pending_dn && $zc_pending_dn !== (int) $zc_rec['plan_id'] ) :
					?>
					<div style="margin-top:14px;background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.3);color:#92400e;border-radius:12px;padding:12px 14px;font-size:.85rem;line-height:1.8">
						<?php
						printf(
							esc_html__( 'از پایان دوره‌ی فعلی، به پلن «%s» تنزل خواهید یافت.', 'zarincode' ),
							esc_html( get_the_title( $zc_pending_dn ) )
						);
						?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $zc_pd['limit_daily'] ) || ! empty( $zc_pd['limit_monthly'] ) || ! empty( $zc_pd['limit_total'] ) ) : ?>
					<div style="margin-top:20px;display:grid;gap:14px">
						<?php
						$zc_bars = array();
						if ( ! empty( $zc_pd['limit_daily'] ) ) {
							$zc_bars[] = array( 'label' => __( 'دانلود امروز', 'zarincode' ), 'used' => $zc_usage['daily'], 'limit' => (int) $zc_pd['limit_daily'] );
						}
						if ( ! empty( $zc_pd['limit_monthly'] ) ) {
							$zc_bars[] = array( 'label' => __( 'دانلود این ماه', 'zarincode' ), 'used' => $zc_usage['monthly'], 'limit' => (int) $zc_pd['limit_monthly'] );
						}
						if ( ! empty( $zc_pd['limit_total'] ) ) {
							$zc_bars[] = array( 'label' => __( 'دانلود کل', 'zarincode' ), 'used' => $zc_usage['total'], 'limit' => (int) $zc_pd['limit_total'] );
						}
						foreach ( $zc_bars as $zc_bar ) :
							$zc_pct = $zc_bar['limit'] > 0 ? min( 100, round( $zc_bar['used'] / $zc_bar['limit'] * 100 ) ) : 100;
							$zc_danger = $zc_pct >= 90;
							?>
							<div>
								<div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:6px">
									<span style="color:var(--zc-muted,#6B7280)"><?php echo esc_html( $zc_bar['label'] ); ?></span>
									<span>
										<strong><?php echo esc_html( zc_fa_num( $zc_bar['used'] ) ); ?></strong>
										<span style="color:var(--zc-muted)">/ <?php echo esc_html( zc_fa_num( $zc_bar['limit'] ) ); ?></span>
									</span>
								</div>
								<div style="height:8px;border-radius:999px;background:var(--zc-line-2,#F1F4F8);overflow:hidden">
									<div style="height:100%;width:<?php echo (int) $zc_pct; ?>%;border-radius:999px;background:<?php echo $zc_danger ? 'var(--zc-danger,#DC2626)' : 'var(--zc-grad-gold,#C9A227)'; ?>"></div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php else : ?>
	<div class="zc-panel__box" data-zc-anim="up">
		<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'star', 19 ); ?><?php esc_html_e( 'اشتراک شما', 'zarincode' ); ?></h3></div>
		<div class="zc-panel__box-body">
			<div class="zc-empty" style="padding:30px 12px">
				<div class="zc-empty__icon" style="width:64px;height:64px"><?php zc_the_icon( 'star', 28 ); ?></div>
				<h3><?php esc_html_e( 'هنوز اشتراک فعالی ندارید', 'zarincode' ); ?></h3>
				<p><?php esc_html_e( 'با خرید یکی از پلن‌های زیر به امکانات ویژه دسترسی پیدا کنید.', 'zarincode' ); ?></p>
			</div>
		</div>
	</div>
<?php endif; ?>

<div class="zc-panel__box" data-zc-anim="up" style="margin-top:22px">
	<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'tag', 19 ); ?><?php esc_html_e( 'پلن‌های اشتراک', 'zarincode' ); ?></h3></div>
	<div class="zc-panel__box-body">
		<?php zc_render_subscription_plans(); ?>
	</div>
</div>
