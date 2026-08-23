<?php
/** صفحه تکی مسیر یادگیری. @package Zarincode */
defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) : the_post();
	$zc_path_id = get_the_ID();
	$zc_courses = array_values( array_filter( array_map( 'absint', (array) get_post_meta( $zc_path_id, '_zc_path_courses', true ) ) ) );
	$zc_product_id = (int) get_post_meta( $zc_path_id, '_zc_path_product', true );
	$zc_product = $zc_product_id && function_exists( 'wc_get_product' ) ? wc_get_product( $zc_product_id ) : false;
	?>
	<section class="zc-page-hero"><div class="zc-container zc-page-hero__in"><?php zc_breadcrumb(); ?><span class="zc-page-hero__eyebrow"><?php esc_html_e( 'مسیر یادگیری', 'zarincode' ); ?></span><h1><?php the_title(); ?></h1><p><?php echo esc_html( get_the_excerpt() ); ?></p></div></section>
	<div class="zc-container" style="padding-block:40px 70px">
		<div class="zc-layout zc-layout--sidebar">
			<article class="zc-entry">
				<?php if ( has_post_thumbnail() ) : ?><div class="zc-entry__thumb"><?php echo zc_thumbnail( $zc_path_id, 'zc-wide' ); // phpcs:ignore ?></div><?php endif; ?>
				<div class="zc-entry__content"><?php the_content(); ?></div>
				<section style="margin-top:32px"><div class="zc-section-head"><h2><?php esc_html_e( 'دوره‌های این مسیر', 'zarincode' ); ?></h2><span><?php echo esc_html( zc_fa_num( count( $zc_courses ) ) ); ?> <?php esc_html_e( 'دوره', 'zarincode' ); ?></span></div>
					<div class="zc-grid zc-grid--2">
					<?php foreach ( $zc_courses as $index => $course_id ) : if ( 'publish' !== get_post_status( $course_id ) ) { continue; } ?>
						<article class="zc-card zc-course-card"><a href="<?php echo esc_url( get_permalink( $course_id ) ); ?>"><?php echo zc_thumbnail( $course_id, 'zc-card' ); // phpcs:ignore ?><div class="zc-card__body"><span class="zc-badge"><?php echo esc_html( zc_fa_num( $index + 1 ) ); ?></span><h3 class="zc-card__title"><?php echo esc_html( get_the_title( $course_id ) ); ?></h3><p><?php echo esc_html( get_the_excerpt( $course_id ) ); ?></p><?php if ( is_user_logged_in() && zc_user_has_course( get_current_user_id(), $course_id ) ) : ?><div class="zc-progress"><span><?php echo esc_html( zc_fa_num( zc_get_course_progress( get_current_user_id(), $course_id ) ) ); ?>٪</span></div><?php endif; ?></div></a></article>
					<?php endforeach; ?>
					</div>
				</section>
			</article>
			<aside class="zc-sidebar"><div class="zc-widget" style="position:sticky;top:110px"><h3 class="zc-widget__title"><span><?php esc_html_e( 'شروع این مسیر', 'zarincode' ); ?></span></h3><p><?php printf( esc_html__( 'با خرید این بسته به %s دوره دسترسی می‌گیرید.', 'zarincode' ), esc_html( zc_fa_num( count( $zc_courses ) ) ) ); ?></p>
			<?php if ( $zc_product ) : ?><div style="font-size:1.25rem;font-weight:800;margin:14px 0"><?php echo wp_kses_post( $zc_product->get_price_html() ); ?></div><a class="zc-btn zc-btn--gold zc-btn--block" href="<?php echo esc_url( add_query_arg( 'add-to-cart', $zc_product_id, function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : get_permalink( $zc_product_id ) ) ); ?>"><?php esc_html_e( 'ثبت‌نام در مسیر', 'zarincode' ); ?></a><?php else : ?><a class="zc-btn zc-btn--gold zc-btn--block" href="<?php echo esc_url( zc_panel_url( 'tickets' ) ); ?>"><?php esc_html_e( 'درخواست راهنمایی', 'zarincode' ); ?></a><?php endif; ?></div></aside>
		</div>
	</div>
	<?php
endwhile;
get_footer();
