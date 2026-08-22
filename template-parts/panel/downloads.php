<?php
/**
 * تب دانلودهای من
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_downloads = zc_get_user_downloads();
?>

<div class="zc-panel__box" data-zc-anim="up">
	<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'download', 19 ); ?><?php esc_html_e( 'فایل‌های قابل دانلود', 'zarincode' ); ?></h3></div>
	<div class="zc-panel__box-body">
		<?php if ( $zc_downloads ) : ?>
			<div class="zc-grid zc-grid--2">
				<?php foreach ( $zc_downloads as $zc_i => $zc_dl ) : ?>
					<div class="zc-download-item" data-zc-anim="up" data-zc-delay="<?php echo (int) ( $zc_i * 60 ); ?>">
						<span class="zc-download-item__icon"><?php zc_the_icon( 'download', 22 ); ?></span>
						<div class="zc-download-item__info">
							<strong><?php echo esc_html( $zc_dl['product_name'] ); ?></strong>
							<small><?php echo esc_html( $zc_dl['download_name'] ); ?></small>
							<?php if ( ! empty( $zc_dl['downloads_remaining'] ) ) : ?>
								<small style="color:var(--zc-warning)">
									<?php printf( esc_html__( '%s بار باقیمانده', 'zarincode' ), esc_html( zc_fa_num( $zc_dl['downloads_remaining'] ) ) ); ?>
								</small>
							<?php endif; ?>
						</div>
						<a href="<?php echo esc_url( $zc_dl['download_url'] ); ?>" class="zc-btn zc-btn--gold zc-btn--sm">
							<?php esc_html_e( 'دانلود', 'zarincode' ); ?>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="zc-empty">
				<div class="zc-empty__icon"><?php zc_the_icon( 'download', 40 ); ?></div>
				<h3><?php esc_html_e( 'فایل قابل دانلودی ندارید', 'zarincode' ); ?></h3>
				<p><?php esc_html_e( 'پس از خرید محصولات دیجیتال، لینک دانلود در این بخش نمایش داده می‌شود.', 'zarincode' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</div>
