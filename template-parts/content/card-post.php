<?php
/**
 * کارت نوشته بلاگ — طراحی مجله‌ای
 *
 * حالت‌های نمایش با آرگومان $args قابل تنظیم است:
 *  - style : grid (پیش‌فرض) | list | featured
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_args  = isset( $args ) && is_array( $args ) ? $args : array();
$zc_style = $zc_args['style'] ?? 'grid';
$zc_id    = get_the_ID();
$zc_cats  = get_the_category();
$zc_mins  = zc_reading_time( $zc_id );
?>
<article <?php post_class( 'zc-bcard zc-bcard--' . esc_attr( $zc_style ) ); ?> data-zc-anim="up">

	<a class="zc-bcard__media" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>" tabindex="-1">
		<?php echo zc_thumbnail( $zc_id, 'zc-card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<?php if ( $zc_cats ) : ?>
			<span class="zc-bcard__cat"><?php echo esc_html( $zc_cats[0]->name ); ?></span>
		<?php endif; ?>

		<span class="zc-bcard__time">
			<?php zc_the_icon( 'clock', 13 ); ?>
			<?php
			/* translators: %s: تعداد دقیقه */
			printf( esc_html__( '%s دقیقه', 'zarincode' ), esc_html( zc_fa_num( $zc_mins ) ) );
			?>
		</span>
	</a>

	<div class="zc-bcard__body">

		<div class="zc-bcard__date">
			<?php zc_the_icon( 'calendar', 14 ); ?>
			<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( zc_fa_num( get_the_date() ) ); ?></time>
		</div>

		<h3 class="zc-bcard__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<p class="zc-bcard__excerpt">
			<?php echo esc_html( zc_excerpt( get_the_excerpt(), 'list' === $zc_style ? 26 : 18 ) ); ?>
		</p>

		<div class="zc-bcard__foot">
			<span class="zc-bcard__author">
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 30, '', get_the_author(), array( 'class' => 'zc-bcard__avatar' ) ); // phpcs:ignore ?>
				<?php the_author(); ?>
			</span>

			<a href="<?php the_permalink(); ?>" class="zc-bcard__more">
				<span><?php esc_html_e( 'ادامه مطلب', 'zarincode' ); ?></span>
				<?php zc_the_icon( 'arrow-left', 16 ); ?>
			</a>
		</div>
	</div>
</article>
