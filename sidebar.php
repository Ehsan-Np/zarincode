<?php
/**
 * سایدبار پیش‌فرض
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_sidebar_id = 'sidebar-main';

if ( is_singular( 'post' ) || is_home() || is_category() ) {
	$zc_sidebar_id = is_active_sidebar( 'sidebar-blog' ) ? 'sidebar-blog' : 'sidebar-main';
} elseif ( is_post_type_archive( 'zc_course' ) || is_singular( 'zc_course' ) ) {
	$zc_sidebar_id = is_active_sidebar( 'sidebar-course' ) ? 'sidebar-course' : 'sidebar-main';
}

/*
 * نوار کناری همیشه داخل <aside> قرار می‌گیرد تا در چیدمان شبکه‌ای
 * (.zc-layout--sidebar) به درستی در ستون خود بنشیند.
 */
echo '<aside class="zc-sidebar zc-layout__aside" role="complementary">';

if ( is_active_sidebar( $zc_sidebar_id ) ) {
	dynamic_sidebar( $zc_sidebar_id );
} else {
	// ویجت‌های پیش‌فرض زیبا در صورت خالی بودن سایدبار.
	?>
	<div class="zc-widget">
		<h3 class="zc-widget__title"><span><?php esc_html_e( 'جستجو', 'zarincode' ); ?></span></h3>
		<?php get_search_form(); ?>
	</div>

	<div class="zc-widget">
		<h3 class="zc-widget__title"><span><?php esc_html_e( 'دوره‌های پیشنهادی', 'zarincode' ); ?></span></h3>
		<?php
		$zc_courses = new WP_Query(
			array(
				'post_type'      => 'zc_course',
				'posts_per_page' => 4,
				'meta_key'       => '_zc_students', // phpcs:ignore
				'orderby'        => 'meta_value_num',
			)
		);
		if ( $zc_courses->have_posts() ) :
			while ( $zc_courses->have_posts() ) :
				$zc_courses->the_post();
				?>
				<a href="<?php the_permalink(); ?>" class="zc-mini-course" style="padding:9px 0">
					<span class="zc-mini-course__thumb"><?php echo zc_thumbnail( get_the_ID(), 'thumbnail' ); // phpcs:ignore ?></span>
					<span class="zc-mini-course__info">
						<strong style="font-size:.84rem"><?php echo esc_html( zc_excerpt( get_the_title(), 6 ) ); ?></strong>
						<small><?php echo esc_html( zc_fa_num( (int) get_post_meta( get_the_ID(), '_zc_students', true ) ) ); ?> <?php esc_html_e( 'دانشجو', 'zarincode' ); ?></small>
					</span>
				</a>
				<?php
			endwhile;
			wp_reset_postdata();
		endif;
		?>
	</div>

	<div class="zc-widget" style="background:var(--zc-grad-dark);color:#fff;text-align:center">
		<h3 class="zc-widget__title" style="color:#fff;border-color:rgba(255,255,255,.15)"><span><?php esc_html_e( 'مشاوره رایگان', 'zarincode' ); ?></span></h3>
		<p style="font-size:.86rem;color:rgba(255,255,255,.7);margin-bottom:14px"><?php esc_html_e( 'برای انتخاب مسیر یادگیری با کارشناسان ما صحبت کنید.', 'zarincode' ); ?></p>
		<a href="<?php echo esc_url( zc_opt( 'zc_header_cta_link', '#' ) ); ?>" class="zc-btn zc-btn--gold zc-btn--sm zc-btn--block"><?php zc_the_icon( 'headphone', 16 ); ?><?php esc_html_e( 'درخواست مشاوره', 'zarincode' ); ?></a>
	</div>
	<?php
}

echo '</aside>';
