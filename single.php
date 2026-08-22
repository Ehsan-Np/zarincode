<?php
/**
 * قالب تک‌نوشته — طراحی مجله‌ای
 *
 * ساختار: هدر تمام‌عرض با تصویر شاخص و اطلاعات نوشته، ستون محتوا با
 * تایپوگرافی خوانا، فهرست مطالب خودکار، جعبه‌ی نویسنده، ناوبری و
 * نوشته‌های مرتبط.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( zc_opt( 'zc_reading_progress', true ) ) {
	zc_reading_progress_bar();
}

$zc_sidebar = 'none' !== zc_opt( 'zc_blog_sidebar', 'right' );

while ( have_posts() ) :
	the_post();

	$zc_id   = get_the_ID();
	$zc_cats = get_the_category();

	zc_set_views( $zc_id );
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'zc-single' ); ?>>

		<header class="zc-single__hero">
			<div class="zc-single__hero-bg" aria-hidden="true">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'zc-wide', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
				<?php endif; ?>
			</div>

			<div class="zc-container">
				<div class="zc-single__hero-in">
					<?php zc_breadcrumb( true ); ?>

					<?php if ( $zc_cats ) : ?>
						<div class="zc-single__cats">
							<?php foreach ( array_slice( $zc_cats, 0, 3 ) as $zc_cat ) : ?>
								<a href="<?php echo esc_url( get_category_link( $zc_cat ) ); ?>" class="zc-single__cat">
									<?php echo esc_html( $zc_cat->name ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<h1 class="zc-single__title"><?php the_title(); ?></h1>

					<?php if ( has_excerpt() ) : ?>
						<p class="zc-single__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>

					<div class="zc-single__byline">
						<div class="zc-single__author">
							<?php echo get_avatar( get_the_author_meta( 'ID' ), 46, '', get_the_author(), array( 'class' => 'zc-single__avatar' ) ); // phpcs:ignore ?>
							<span>
								<strong><?php the_author(); ?></strong>
								<small><?php echo esc_html( get_the_author_meta( 'description' ) ? zc_excerpt( get_the_author_meta( 'description' ), 6 ) : __( 'نویسنده زرین کد', 'zarincode' ) ); ?></small>
							</span>
						</div>

						<ul class="zc-single__facts">
							<li><?php zc_the_icon( 'calendar', 16 ); ?><?php echo esc_html( get_the_date() ); ?></li>
							<li><?php zc_the_icon( 'clock', 16 ); ?><?php
								/* translators: %s: تعداد دقیقه */
								printf( esc_html__( '%s دقیقه مطالعه', 'zarincode' ), esc_html( zc_fa_num( zc_reading_time( $zc_id ) ) ) );
								?></li>
							<li><?php zc_the_icon( 'eye', 16 ); ?><?php echo esc_html( zc_fa_num( number_format( zc_get_views( $zc_id ) ) ) ); ?></li>
							<li><?php zc_the_icon( 'chat', 16 ); ?><?php echo esc_html( zc_fa_num( get_comments_number() ) ); ?></li>
						</ul>
					</div>
				</div>
			</div>
		</header>

		<div class="zc-container zc-single__wrap">
			<div class="zc-layout <?php echo $zc_sidebar ? 'zc-layout--sidebar' : 'zc-layout--full'; ?>">

				<main class="zc-layout__main">

					<?php
					/*
					 * تصویر شاخص، متن مقاله، برچسب‌ها و دکمه‌های اشتراک‌گذاری
					 * همگی درون یک کارت سفید قرار می‌گیرند تا بدنه‌ی مطلب یک
					 * بلوک یکپارچه دیده شود. پیش از این هر بخش جدا روی پس‌زمینه
					 * می‌نشست و نوار اشتراک‌گذاری بیرون از کادر اصلی به نظر می‌رسید.
					 */
					?>
					<article class="zc-single__card">

					<?php if ( has_post_thumbnail() ) : ?>
				<figure class="zc-single__figure">
						<?php if ( function_exists( 'zc_image' ) && zc_image_opt_enabled() ) : ?>
							<?php echo zc_image( get_post_thumbnail_id(), 'zc-wide', array( 'fetchpriority' => 'high', 'class' => 'zc-img zc-img--fit' ) ); // phpcs:ignore ?>
						<?php else : ?>
							<?php the_post_thumbnail( 'zc-wide' ); ?>
						<?php endif; ?>
							<?php if ( wp_get_attachment_caption( get_post_thumbnail_id() ) ) : ?>
								<figcaption><?php echo esc_html( wp_get_attachment_caption( get_post_thumbnail_id() ) ); ?></figcaption>
							<?php endif; ?>
						</figure>
					<?php endif; ?>

					<div class="zc-single__body">
						<?php zc_the_content_guarded(); ?>
					</div>

					<?php
					$zc_tags = get_the_tags();

					if ( $zc_tags ) :
						?>
						<div class="zc-single__tags">
							<span class="zc-single__tags-label"><?php zc_the_icon( 'filter', 16 ); ?><?php esc_html_e( 'برچسب‌ها', 'zarincode' ); ?></span>
							<?php foreach ( $zc_tags as $zc_tag ) : ?>
								<a href="<?php echo esc_url( get_tag_link( $zc_tag ) ); ?>"><?php echo esc_html( $zc_tag->name ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<div class="zc-single__share">
						<span class="zc-single__share-label"><?php esc_html_e( 'این مطلب مفید بود؟ آن را با دوستانتان به اشتراک بگذارید', 'zarincode' ); ?></span>
						<?php zc_share_buttons(); ?>
					</div>

					</article><!-- .zc-single__card -->

					<?php
					/*
					 * بخش‌های فرعی (نویسنده، ناوبری، نوشته‌های مرتبط، نظرات و
					 * سایدبار) را جداگانه و محافظت‌شده رندر می‌کنیم تا اگر یکی
					 * از آن‌ها در هر محیطی (نسخه PHP، افزونه یا داده) خطا داد،
					 * بدنه‌ی اصلی مطلب که در بالا رندر شد، از بین نرود و صفحه
					 * خالی نشود. هر خطا صرفاً همان بخش را حذف می‌کند.
					 */
					foreach (
						array(
							'author'  => function () {
								if ( zc_opt( 'zc_author_box', true ) ) {
									get_template_part( 'template-parts/single/author-box' );
								}
							},
							'nav'     => 'zc_post_navigation',
							'related' => function () {
								zc_related_posts( (int) zc_opt( 'zc_related_count', 3 ) );
							},
							'comments' => function () {
								if ( comments_open() || get_comments_number() ) {
									comments_template();
								}
							},
						) as $zc_section_key => $zc_section_cb
					) :
						try {
							if ( is_callable( $zc_section_cb ) ) {
								$zc_section_cb();
							}
						} catch ( \Throwable $zc_e ) {
							// تنها همین بخش حذف می‌شود؛ ادامه‌ی صفحه سالم می‌ماند.
							do_action( 'zc_single_section_error', $zc_section_key, $zc_e );
						}
					endforeach;
					?>
				</main>

				<?php
				if ( $zc_sidebar ) {
					try {
						get_sidebar();
					} catch ( \Throwable $zc_e ) {
						// سایدبار خطا داد؛ محتوا بدون سایدبار سالم می‌ماند.
						do_action( 'zc_single_section_error', 'sidebar', $zc_e );
					}
				}
				?>
			</div>
		</div>
	</article>

	<?php
endwhile;

get_footer();
