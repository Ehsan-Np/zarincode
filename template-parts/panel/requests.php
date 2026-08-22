<?php
/**
 * پنل کاربری — درخواست‌های پروژه‌ی کاربر
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_user_id  = get_current_user_id();
$zc_statuses = zc_request_statuses();
$zc_budgets  = zc_request_budgets();

$zc_requests = get_posts(
	array(
		'post_type'      => 'zc_request',
		'posts_per_page' => 20,
		'post_status'    => 'any',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'   => '_zc_req_user',
				'value' => $zc_user_id,
			),
		),
	)
);
?>

<div class="zc-panel__head">
	<h2 class="zc-panel__title">
		<?php zc_the_icon( 'edit', 22 ); ?>
		<?php esc_html_e( 'درخواست‌های پروژه', 'zarincode' ); ?>
	</h2>

	<a href="<?php echo esc_url( get_post_type_archive_link( 'zc_service' ) ); ?>" class="zc-btn zc-btn--gold zc-btn--sm">
		<?php zc_the_icon( 'sparkle', 15 ); ?>
		<?php esc_html_e( 'ثبت درخواست تازه', 'zarincode' ); ?>
	</a>
</div>

<?php if ( $zc_requests ) : ?>
	<div class="zc-req-list">
		<?php foreach ( $zc_requests as $zc_req ) : ?>
			<?php
			$zc_status = get_post_meta( $zc_req->ID, '_zc_req_status', true );
			$zc_status = $zc_status ? $zc_status : 'new';
			$zc_sid    = (int) get_post_meta( $zc_req->ID, '_zc_req_service', true );
			$zc_quote  = (float) get_post_meta( $zc_req->ID, '_zc_req_quote', true );
			$zc_budget = get_post_meta( $zc_req->ID, '_zc_req_budget', true );
			?>
			<article class="zc-req-item">
				<div class="zc-req-item__head">
					<h3 class="zc-req-item__title">
						<?php echo $zc_sid ? esc_html( get_the_title( $zc_sid ) ) : esc_html__( 'درخواست پروژه', 'zarincode' ); ?>
					</h3>

					<span class="zc-req-status zc-req-status--<?php echo esc_attr( $zc_status ); ?>">
						<?php echo esc_html( $zc_statuses[ $zc_status ] ?? $zc_status ); ?>
					</span>
				</div>

				<p class="zc-req-item__desc"><?php echo esc_html( zc_excerpt( $zc_req->post_content, 30 ) ); ?></p>

				<div class="zc-req-item__meta">
					<span>
						<?php zc_the_icon( 'calendar', 14 ); ?>
						<?php echo esc_html( get_the_date( '', $zc_req ) ); ?>
					</span>

					<?php if ( $zc_budget ) : ?>
						<span>
							<?php zc_the_icon( 'wallet', 14 ); ?>
							<?php echo esc_html( $zc_budgets[ $zc_budget ] ?? $zc_budget ); ?>
						</span>
					<?php endif; ?>

					<?php if ( $zc_quote ) : ?>
						<span class="zc-req-item__quote">
							<?php zc_the_icon( 'check', 14 ); ?>
							<?php esc_html_e( 'مبلغ پیشنهادی:', 'zarincode' ); ?>
							<strong><?php echo esc_html( zc_price_text( $zc_quote ) ); ?></strong>
						</span>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
<?php else : ?>
	<div class="zc-empty">
		<div class="zc-empty__icon"><?php zc_the_icon( 'edit', 38 ); ?></div>
		<h3><?php esc_html_e( 'هنوز درخواستی ثبت نکرده‌اید', 'zarincode' ); ?></h3>
		<p><?php esc_html_e( 'برای سفارش طراحی سایت، انجام پروژه یا خدمات سئو، از صفحه خدمات اقدام کنید.', 'zarincode' ); ?></p>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'zc_service' ) ); ?>" class="zc-btn zc-btn--gold">
			<?php esc_html_e( 'مشاهده خدمات', 'zarincode' ); ?>
		</a>
	</div>
<?php endif; ?>
