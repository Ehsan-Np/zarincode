<?php
/**
 * قالب نمایش تک آموزش رایگان.
 *
 * ساختار مشابه نوشته‌های بلاگ اما با اطلاعات تکمیلی آموزش
 * (سطح، مدت زمان، ویدیو و دوره‌ی مرتبط) نمایش داده می‌شود.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$zc_id       = get_the_ID();
	$zc_level    = get_post_meta( $zc_id, '_zc_level', true );
	$zc_duration = get_post_meta( $zc_id, '_zc_duration', true );
	$zc_video    = get_post_meta( $zc_id, '_zc_video', true );
	$zc_teacher  = get_post_meta( $zc_id, '_zc_teacher', true );
	$zc_levels   = array(
		'beginner'     => __( 'مقدماتی', 'zarincode' ),
		'intermediate' => __( 'متوسط', 'zarincode' ),
		'advanced'     => __( 'پیشرفته', 'zarincode' ),
	);
	$zc_sidebar  = 'none' !== zc_opt( 'zc_blog_sidebar', 'right' );

	zc_set_views( $zc_id );
	?>

	<?php if ( zc_opt( 'zc_reading_progress', true ) ) : ?>
		<div class="zc-reading-progress" aria-hidden="true"><span></span></div>
	<?php endif; ?>

	<div class="zc-tutorial-hero">
		<div class="zc-container">
			<?php zc_breadcrumb( true ); ?>

			<div class="zc-tutorial-hero__in" data-zc-anim="up">
				<span class="zc-badge zc-badge--gold zc-badge--lg">
					<?php zc_the_icon( 'book', 15 ); ?>
					<?php esc_html_e( 'آموزش رایگان', 'zarincode' ); ?>
				</span>

				<h1 class="zc-tutorial-hero__title"><?php the_title(); ?></h1>

				<?php if ( has_excerpt() ) : ?>
					<p class="zc-tutorial-hero__sub"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>

				<ul class="zc-tutorial-hero__meta">
					<?php if ( $zc_teacher ) : ?>
						<li><?php zc_the_icon( 'user', 16 ); ?><?php echo esc_html( $zc_teacher ); ?></li>
					<?php endif; ?>

					<?php if ( $zc_level && isset( $zc_levels[ $zc_level ] ) ) : ?>
						<li><?php zc_the_icon( 'chart', 16 ); ?><?php echo esc_html( $zc_levels[ $zc_level ] ); ?></li>
					<?php endif; ?>

					<?php if ( $zc_duration ) : ?>
						<li><?php zc_the_icon( 'clock', 16 ); ?><?php echo esc_html( zc_fa_num( $zc_duration ) ); ?></li>
					<?php endif; ?>

					<li><?php zc_the_icon( 'eye', 16 ); ?><?php echo esc_html( zc_fa_num( number_format( zc_get_views( $zc_id ) ) ) ); ?></li>
					<li><?php zc_the_icon( 'calendar', 16 ); ?><?php echo esc_html( zc_human_time( get_the_time( 'U' ) ) ); ?></li>
				</ul>
			</div>
		</div>
	</div>

	<div class="zc-container zc-section">
		<div class="zc-layout <?php echo $zc_sidebar ? 'zc-layout--sidebar' : 'zc-layout--full'; ?>">

			<main class="zc-layout__main">
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'zc-entry zc-card zc-card--pad' ); ?>>

					<?php if ( $zc_video ) : ?>
						<div class="zc-entry__video zc-video-box" data-zc-anim="zoom">
							<?php echo wp_kses_post( wp_oembed_get( $zc_video ) ? wp_oembed_get( $zc_video ) : '<video controls preload="metadata" src="' . esc_url( $zc_video ) . '"></video>' ); ?>
						</div>
					<?php elseif ( has_post_thumbnail() ) : ?>
						<div class="zc-entry__thumb" data-zc-anim="zoom">
							<?php the_post_thumbnail( 'zc-wide', array( 'loading' => 'eager' ) ); ?>
						</div>
					<?php endif; ?>

					<div class="zc-entry__content">
						<?php
						the_content();

						wp_link_pages(
							array(
								'before' => '<div class="zc-page-links">' . esc_html__( 'صفحات:', 'zarincode' ),
								'after'  => '</div>',
							)
						);
						?>
					</div>

					<?php
					$zc_tags = get_the_term_list( $zc_id, 'zc_tutorial_cat', '', '' );

					if ( $zc_tags && ! is_wp_error( $zc_tags ) ) :
						?>
						<div class="zc-entry__tags">
							<?php zc_the_icon( 'filter', 15 ); ?>
							<?php echo wp_kses_post( $zc_tags ); ?>
						</div>
						<?php
					endif;
					?>

					<?php if ( zc_opt( 'zc_share_enable', true ) ) : ?>
						<div class="zc-entry__share">
							<span class="zc-entry__share-label"><?php esc_html_e( 'اشتراک‌گذاری این آموزش:', 'zarincode' ); ?></span>
							<?php zc_share_buttons(); ?>
						</div>
					<?php endif; ?>
				</article>

				<?php zc_post_navigation(); ?>

				<?php
				/* دوره‌های مرتبط: پیشنهاد دوره‌ی پولی هم‌موضوع. */
				$zc_terms = wp_get_post_terms( $zc_id, 'zc_tutorial_cat', array( 'fields' => 'names' ) );

				if ( ! is_wp_error( $zc_terms ) && $zc_terms ) {
					$zc_suggest = new WP_Query(
						array(
							'post_type'           => 'zc_course',
							'posts_per_page'      => 3,
							'ignore_sticky_posts' => true,
							'no_found_rows'       => true,
							's'                   => $zc_terms[0],
						)
					);

					if ( $zc_suggest->have_posts() ) :
						?>
						<section class="zc-related zc-related--courses">
							<h2 class="zc-related__title">
								<?php zc_the_icon( 'book', 20 ); ?>
								<?php esc_html_e( 'دوره‌های تخصصی مرتبط', 'zarincode' ); ?>
							</h2>

							<div class="zc-grid zc-grid--3">
								<?php
								while ( $zc_suggest->have_posts() ) :
									$zc_suggest->the_post();
									get_template_part( 'template-parts/content/card', 'course' );
								endwhile;
								?>
							</div>
						</section>
						<?php
					endif;

					wp_reset_postdata();
				}
				?>

				<?php
				try {
					zc_related_posts( $zc_id, 'zc_tutorial', 'zc_tutorial_cat' );
				} catch ( \Throwable $zc_e ) {
					do_action( 'zc_single_section_error', 'related', $zc_e );
				}

				if ( comments_open() || get_comments_number() ) {
					try {
						comments_template();
					} catch ( \Throwable $zc_e ) {
						do_action( 'zc_single_section_error', 'comments', $zc_e );
					}
				}
				?>
			</main>

			<?php
			if ( $zc_sidebar ) {
				try {
					get_sidebar();
				} catch ( \Throwable $zc_e ) {
					do_action( 'zc_single_section_error', 'sidebar', $zc_e );
				}
			}
			?>
		</div>
	</div>

	<?php
endwhile;

get_footer();
