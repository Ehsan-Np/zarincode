<?php
/**
 * قالب نمایش پروفایل مدرس.
 *
 * شامل معرفی، آمار تدریس، شبکه‌های اجتماعی و فهرست
 * دوره‌ها و آموزش‌های منتشرشده توسط مدرس است.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$zc_id       = get_the_ID();
	$zc_role     = get_post_meta( $zc_id, '_zc_teacher_role', true );
	$zc_courses  = (int) get_post_meta( $zc_id, '_zc_teacher_courses', true );
	$zc_students = (int) get_post_meta( $zc_id, '_zc_teacher_students', true );
	$zc_socials  = array(
		'telegram' => get_post_meta( $zc_id, '_zc_teacher_telegram', true ),
		'linkedin' => get_post_meta( $zc_id, '_zc_teacher_linkedin', true ),
		'github'   => get_post_meta( $zc_id, '_zc_teacher_github', true ),
	);
	$zc_skills   = get_the_term_list( $zc_id, 'zc_teacher_skill', '', '' );
	$zc_name     = get_the_title();
	?>

	<div class="zc-teacher-hero">
		<div class="zc-container">
			<?php zc_breadcrumb( true ); ?>

			<div class="zc-teacher-hero__in">

				<div class="zc-teacher-hero__avatar" data-zc-anim="zoom">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'zc-square', array( 'loading' => 'eager' ) ); ?>
					<?php else : ?>
						<img src="<?php echo esc_url( ZC_ASSETS . 'img/avatar.svg' ); ?>" alt="<?php echo esc_attr( $zc_name ); ?>" width="180" height="180" />
					<?php endif; ?>
				</div>

				<div class="zc-teacher-hero__body" data-zc-anim="up">
					<h1 class="zc-teacher-hero__name"><?php echo esc_html( $zc_name ); ?></h1>

					<?php if ( $zc_role ) : ?>
						<p class="zc-teacher-hero__role"><?php echo esc_html( $zc_role ); ?></p>
					<?php endif; ?>

					<?php if ( $zc_skills && ! is_wp_error( $zc_skills ) ) : ?>
						<div class="zc-teacher-hero__skills"><?php echo wp_kses_post( $zc_skills ); ?></div>
					<?php endif; ?>

					<ul class="zc-teacher-hero__stats">
						<li>
							<strong><?php echo esc_html( zc_fa_num( number_format( $zc_courses ) ) ); ?></strong>
							<span><?php esc_html_e( 'دوره آموزشی', 'zarincode' ); ?></span>
						</li>
						<li>
							<strong><?php echo esc_html( zc_fa_num( number_format( $zc_students ) ) ); ?></strong>
							<span><?php esc_html_e( 'دانشجو', 'zarincode' ); ?></span>
						</li>
					</ul>

					<?php if ( array_filter( $zc_socials ) ) : ?>
						<div class="zc-teacher-hero__socials">
							<?php foreach ( $zc_socials as $zc_net => $zc_url ) : ?>
								<?php if ( $zc_url ) : ?>
									<a href="<?php echo esc_url( $zc_url ); ?>" class="zc-social zc-social--<?php echo esc_attr( $zc_net ); ?>" target="_blank" rel="noopener nofollow" aria-label="<?php echo esc_attr( $zc_net ); ?>">
										<?php echo zc_social_icon( $zc_net ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</a>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<div class="zc-container zc-section">

		<?php if ( trim( get_the_content() ) ) : ?>
			<article class="zc-card zc-card--pad zc-entry zc-teacher-bio" data-zc-anim="up">
				<h2 class="zc-teacher-bio__title"><?php esc_html_e( 'درباره مدرس', 'zarincode' ); ?></h2>
				<div class="zc-entry__content"><?php the_content(); ?></div>
			</article>
		<?php endif; ?>

		<?php
		/* دوره‌های این مدرس بر اساس متای نام مدرس. */
		$zc_q = new WP_Query(
			array(
				'post_type'      => 'zc_course',
				'posts_per_page' => 8,
				'no_found_rows'  => true,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => '_zc_teacher',
						'value' => $zc_name,
					),
				),
			)
		);

		if ( $zc_q->have_posts() ) :
			?>
			<section class="zc-teacher-courses">
				<?php
				zc_section_heading(
					__( 'دوره‌های این مدرس', 'zarincode' ),
					__( 'با یادگیری از بهترین‌ها، مسیر حرفه‌ای شدن را کوتاه کنید', 'zarincode' )
				);
				?>

				<div class="zc-grid zc-grid--4">
					<?php
					while ( $zc_q->have_posts() ) :
						$zc_q->the_post();
						get_template_part( 'template-parts/content/card', 'course' );
					endwhile;
					?>
				</div>
			</section>
			<?php
		endif;

		wp_reset_postdata();

		/* آموزش‌های رایگان این مدرس. */
		$zc_t = new WP_Query(
			array(
				'post_type'      => 'zc_tutorial',
				'posts_per_page' => 4,
				'no_found_rows'  => true,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => '_zc_teacher',
						'value' => $zc_name,
					),
				),
			)
		);

		if ( $zc_t->have_posts() ) :
			?>
			<section class="zc-teacher-tutorials">
				<?php
				zc_section_heading(
					__( 'آموزش‌های رایگان', 'zarincode' ),
					__( 'محتوای رایگان منتشرشده توسط این مدرس', 'zarincode' )
				);
				?>

				<div class="zc-grid zc-grid--4">
					<?php
					while ( $zc_t->have_posts() ) :
						$zc_t->the_post();
						get_template_part( 'template-parts/content/card', 'tutorial' );
					endwhile;
					?>
				</div>
			</section>
			<?php
		endif;

		wp_reset_postdata();
		?>
	</div>

	<?php
endwhile;

get_footer();
