<?php
/**
 * صندوق اطلاعیه‌های پنل کاربری
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_items = zc_get_announcements( 'panel', 20 );
?>

<div class="zc-panel__head">
	<h2 class="zc-panel__title">
		<?php zc_the_icon( 'bell', 22 ); ?>
		<?php esc_html_e( 'اطلاعیه‌های سایت', 'zarincode' ); ?>
	</h2>

	<p class="zc-panel__sub">
		<?php esc_html_e( 'تازه‌ترین خبرها و اطلاعیه‌های زرین کد را اینجا ببینید.', 'zarincode' ); ?>
	</p>
</div>

<?php if ( ! $zc_items ) : ?>
	<div class="zc-empty">
		<div class="zc-empty__icon"><?php zc_the_icon( 'bell', 40 ); ?></div>
		<h3><?php esc_html_e( 'اطلاعیه‌ای برای نمایش نیست', 'zarincode' ); ?></h3>
		<p><?php esc_html_e( 'هر وقت خبر تازه‌ای باشد، همین‌جا به شما نشان می‌دهیم.', 'zarincode' ); ?></p>
	</div>
<?php else : ?>
	<div class="zc-anlist">
		<?php
		foreach ( $zc_items as $zc_i => $zc_post ) :
			$zc_a = zc_announce_data( $zc_post );
			?>
			<article class="zc-ancard zc-ancard--<?php echo esc_attr( $zc_a['style'] ); ?>"
				data-zc-anim="up" data-zc-delay="<?php echo (int) min( $zc_i * 60, 300 ); ?>">

				<span class="zc-ancard__icon"><?php zc_the_icon( $zc_a['icon'], 20 ); ?></span>

				<div class="zc-ancard__body">
					<div class="zc-ancard__head">
						<h3 class="zc-ancard__title"><?php echo esc_html( $zc_a['title'] ); ?></h3>
						<time class="zc-ancard__date"><?php echo esc_html( zc_fa_num( $zc_a['date'] ) ); ?></time>
					</div>

					<div class="zc-ancard__text"><?php echo wp_kses_post( $zc_a['content'] ); ?></div>

					<div class="zc-ancard__foot">
						<?php if ( $zc_a['btn_text'] && $zc_a['btn_url'] ) : ?>
							<a class="zc-btn zc-btn--sm zc-btn--gold" href="<?php echo esc_url( $zc_a['btn_url'] ); ?>">
								<?php echo esc_html( $zc_a['btn_text'] ); ?>
							</a>
						<?php endif; ?>

						<?php if ( $zc_a['dismissible'] ) : ?>
							<button type="button" class="zc-btn zc-btn--sm zc-btn--ghost"
								data-zc-an-dismiss="<?php echo (int) $zc_a['id']; ?>">
								<?php zc_the_icon( 'check', 15 ); ?>
								<span><?php esc_html_e( 'خواندم', 'zarincode' ); ?></span>
							</button>
						<?php endif; ?>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
