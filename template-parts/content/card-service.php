<?php
/**
 * کارت خدمت — دیزاین مدرن
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_id      = get_the_ID();
$zc_icon    = get_post_meta( $zc_id, '_zc_service_icon', true );
$zc_icon    = $zc_icon ? $zc_icon : 'code';
$zc_from    = (float) get_post_meta( $zc_id, '_zc_service_price_from', true );
$zc_duration = get_post_meta( $zc_id, '_zc_service_duration', true );
$zc_color   = get_post_meta( $zc_id, '_zc_service_color', true );
?>
<article class="zc-svc-card" data-zc-anim="up" <?php echo $zc_color ? 'style="--zc-svc:' . esc_attr( $zc_color ) . '"' : ''; ?>>

	<div class="zc-svc-card__icon">
		<?php zc_the_icon( $zc_icon, 30 ); ?>
	</div>

	<h3 class="zc-svc-card__title">
		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	</h3>

	<p class="zc-svc-card__excerpt"><?php echo esc_html( zc_excerpt( get_the_excerpt(), 18 ) ); ?></p>

	<div class="zc-svc-card__foot">
		<?php if ( $zc_from ) : ?>
			<span class="zc-svc-card__price">
				<small><?php esc_html_e( 'شروع از', 'zarincode' ); ?></small>
				<strong><?php echo esc_html( zc_price_text( $zc_from ) ); ?></strong>
			</span>
		<?php elseif ( $zc_duration ) : ?>
			<span class="zc-svc-card__duration"><?php zc_the_icon( 'clock', 15 ); ?><?php echo esc_html( $zc_duration ); ?></span>
		<?php endif; ?>

		<a href="<?php the_permalink(); ?>" class="zc-btn zc-btn--gold zc-btn--sm">
			<?php esc_html_e( 'جزئیات خدمت', 'zarincode' ); ?>
			<?php zc_the_icon( 'arrow-left', 15 ); ?>
		</a>
	</div>

</article>
