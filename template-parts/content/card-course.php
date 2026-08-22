<?php
/**
 * کارت دوره
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_id       = get_the_ID();
$zc_price    = (float) get_post_meta( $zc_id, '_zc_price', true );
$zc_sale     = (float) get_post_meta( $zc_id, '_zc_sale_price', true );
$zc_students = (int) get_post_meta( $zc_id, '_zc_students', true );
$zc_level    = get_post_meta( $zc_id, '_zc_level', true );
$zc_rating   = (float) get_post_meta( $zc_id, '_zc_rating', true );
$zc_teacher  = get_post_meta( $zc_id, '_zc_teacher', true );
$zc_lessons  = zc_count_lessons( $zc_id );
$zc_cats     = get_the_terms( $zc_id, 'zc_course_cat' );
$zc_levels   = array(
	'beginner'     => __( 'مقدماتی', 'zarincode' ),
	'intermediate' => __( 'متوسط', 'zarincode' ),
	'advanced'     => __( 'پیشرفته', 'zarincode' ),
);
?>
<article <?php post_class( 'zc-card zc-course-card' ); ?> data-zc-anim="up">
	<div class="zc-card__media">
		<a href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
			<?php echo zc_thumbnail( $zc_id ); // phpcs:ignore ?>
		</a>
		<?php if ( isset( $zc_levels[ $zc_level ] ) ) : ?>
			<span class="zc-badge zc-badge--solid zc-course-card__level"><?php echo esc_html( $zc_levels[ $zc_level ] ); ?></span>
		<?php endif; ?>
		<?php if ( $zc_sale && $zc_price ) : ?>
			<span class="zc-badge zc-badge--red zc-badge--float">
				<?php echo esc_html( zc_fa_num( round( ( ( $zc_price - $zc_sale ) / $zc_price ) * 100 ) ) . '٪' ); ?>
			</span>
		<?php endif; ?>
		<div class="zc-play-overlay">
			<a href="<?php the_permalink(); ?>" class="zc-play-btn"><?php zc_the_icon( 'play', 22 ); ?></a>
		</div>
	</div>

	<div class="zc-card__body">
		<?php if ( $zc_cats && ! is_wp_error( $zc_cats ) ) : ?>
			<a href="<?php echo esc_url( get_term_link( $zc_cats[0] ) ); ?>" class="zc-badge zc-badge--gold" style="align-self:flex-start"><?php echo esc_html( $zc_cats[0]->name ); ?></a>
		<?php endif; ?>

		<h3 class="zc-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

		<?php if ( $zc_teacher ) : ?>
			<div class="zc-course-card__teacher">
				<?php zc_the_icon( 'user', 15 ); ?><span><?php echo esc_html( $zc_teacher ); ?></span>
			</div>
		<?php endif; ?>

		<div class="zc-course-card__stats">
			<span><?php zc_the_icon( 'video', 14 ); ?><?php echo esc_html( zc_fa_num( $zc_lessons ) ); ?> <?php esc_html_e( 'جلسه', 'zarincode' ); ?></span>
			<span><?php zc_the_icon( 'users', 14 ); ?><?php echo esc_html( zc_fa_num( number_format( $zc_students ) ) ); ?></span>
		</div>

		<div class="zc-card__footer">
			<?php echo zc_stars( $zc_rating ?: 5 ); // phpcs:ignore ?>
			<div class="zc-price">
				<?php if ( ! $zc_price ) : ?>
					<span class="zc-price__free"><?php esc_html_e( 'رایگان', 'zarincode' ); ?></span>
				<?php elseif ( $zc_sale ) : ?>
					<del class="zc-price__old"><?php echo esc_html( zc_fa_num( number_format( $zc_price ) ) ); ?></del>
					<span class="zc-price__now"><?php echo esc_html( zc_fa_num( number_format( $zc_sale ) ) ); ?></span>
				<?php else : ?>
					<span class="zc-price__now"><?php echo esc_html( zc_fa_num( number_format( $zc_price ) ) ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
</article>
