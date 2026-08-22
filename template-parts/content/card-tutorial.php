<?php
/**
 * کارت آموزش
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_cats  = get_the_terms( get_the_ID(), 'zc_tutorial_cat' );
$zc_level = get_post_meta( get_the_ID(), '_zc_level', true );
$zc_levels = array(
	'beginner'     => array( __( 'مقدماتی', 'zarincode' ), 'green' ),
	'intermediate' => array( __( 'متوسط', 'zarincode' ), 'orange' ),
	'advanced'     => array( __( 'پیشرفته', 'zarincode' ), 'red' ),
);
?>
<article <?php post_class( 'zc-card' ); ?> data-zc-anim="up">
	<div class="zc-card__media zc-card__media--fab">
		<a href="<?php the_permalink(); ?>"><?php echo zc_thumbnail( get_the_ID() ); // phpcs:ignore ?></a>
		<a href="<?php the_permalink(); ?>" class="zc-card__fab" tabindex="-1"><?php zc_the_icon( 'arrow-ul', 18 ); ?></a>
		<?php if ( isset( $zc_levels[ $zc_level ] ) ) : ?>
			<span class="zc-badge zc-badge--<?php echo esc_attr( $zc_levels[ $zc_level ][1] ); ?> zc-badge--float">
				<?php echo esc_html( $zc_levels[ $zc_level ][0] ); ?>
			</span>
		<?php endif; ?>
	</div>
	<div class="zc-card__body">
		<?php if ( $zc_cats && ! is_wp_error( $zc_cats ) ) : ?>
			<span class="zc-badge zc-badge--gold" style="align-self:flex-start"><?php echo esc_html( $zc_cats[0]->name ); ?></span>
		<?php endif; ?>
		<h3 class="zc-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p class="zc-card__excerpt"><?php echo esc_html( zc_excerpt( get_the_excerpt(), 16 ) ); ?></p>
		<div class="zc-card__meta">
			<span><?php zc_the_icon( 'clock', 15 ); ?><?php echo esc_html( zc_fa_num( zc_reading_time() ) ); ?> <?php esc_html_e( 'دقیقه', 'zarincode' ); ?></span>
			<span><?php zc_the_icon( 'eye', 15 ); ?><?php echo esc_html( zc_fa_num( zc_get_views( get_the_ID() ) ) ); ?></span>
		</div>
	</div>
</article>
