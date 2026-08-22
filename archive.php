<?php
/**
 * قالب آرشیو
 *
 * برای نوع محتوای «خدمات» از دیزاین مدرن و برای بقیه از چیدمان استاندارد
 * استفاده می‌کند.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

get_header();

$zc_type = get_post_type();

// آرشیو خدمات — دیزاین مدرن.
if ( 'zc_service' === $zc_type ) :
	?>
	<div class="zc-container zc-modern-page">

		<header class="zc-ph">
			<nav class="zc-pbreadcrumb" aria-label="مسیر">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'zarincode' ); ?></a>
				<span class="sep" aria-hidden="true">‹</span>
				<span class="current"><?php esc_html_e( 'خدمات زرین کد', 'zarincode' ); ?></span>
			</nav>
			<span class="zc-ph__eyebrow"><?php esc_html_e( 'خدمات حرفه‌ای', 'zarincode' ); ?></span>
			<h1 class="zc-ph__title"><?php esc_html_e( 'خدمات ', 'zarincode' ); ?><span><?php esc_html_e( 'تخصصی', 'zarincode' ); ?></span> <?php esc_html_e( 'زرین کد', 'zarincode' ); ?></h1>
			<p class="zc-ph__lead"><?php esc_html_e( 'از طراحی سایت و اپلیکیشن تا سئو و مشاوره فنی؛ تیم زرین کد هر نیاز دیجیتال شما را با بالاترین کیفیت برطرف می‌کند.', 'zarincode' ); ?></p>
			<div class="zc-ph__meta">
				<span class="zc-ph__chip"><?php zc_the_icon( 'code', 17 ); ?><?php esc_html_e( 'برنامه‌نویسی', 'zarincode' ); ?></span>
				<span class="zc-ph__chip"><?php zc_the_icon( 'chart', 17 ); ?><?php esc_html_e( 'سئو و مارکتینگ', 'zarincode' ); ?></span>
				<span class="zc-ph__chip"><?php zc_the_icon( 'phone', 17 ); ?><?php esc_html_e( 'موبایل و وب', 'zarincode' ); ?></span>
			</div>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="zc-grid zc-grid--3" style="margin-top:34px">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content/card', 'service' );
				endwhile;
				?>
			</div>

			<?php zc_pagination(); ?>
		<?php else : ?>
			<div class="zc-empty">
				<div class="zc-empty__icon"><?php zc_the_icon( 'search', 40 ); ?></div>
				<h3><?php esc_html_e( 'موردی یافت نشد', 'zarincode' ); ?></h3>
			</div>
		<?php endif; ?>

		<section class="zc-cta">
			<h2 class="zc-cta__title"><?php esc_html_e( 'نیاز به خدمات تخصصی دارید؟', 'zarincode' ); ?></h2>
			<p class="zc-cta__sub"><?php esc_html_e( 'همین حالا درخواست خود را ثبت کنید یا مشاوره رایگان بگیرید.', 'zarincode' ); ?></p>
			<div class="zc-cta__actions">
				<a href="<?php echo esc_url( home_url( '/request/' ) ); ?>" class="zc-btn zc-btn--gold"><?php esc_html_e( 'درخواست پروژه', 'zarincode' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>" class="zc-btn zc-btn--navy"><?php esc_html_e( 'تماس با ما', 'zarincode' ); ?></a>
			</div>
		</section>

	</div>
	<?php
	get_footer();
	return;
endif;

/* ---------- آرشیو استاندارد بقیه انواع ---------- */

zc_page_hero();

$zc_columns = ( 'zc_course' === $zc_type ) ? 3 : (int) zc_opt( 'zc_blog_columns', 3 );
$zc_tpl     = 'post';

if ( 'zc_course' === $zc_type ) {
	$zc_tpl = 'course';
} elseif ( 'zc_tutorial' === $zc_type ) {
	$zc_tpl = 'tutorial';
} elseif ( 'zc_teacher' === $zc_type ) {
	$zc_tpl = 'teacher';
}
?>

<div class="zc-container">
	<div class="zc-content-sidebar">
		<div class="zc-content">
			<?php if ( have_posts() ) : ?>

				<div class="zc-archive-toolbar">
					<span class="zc-archive-count">
						<?php
						global $wp_query;
						printf(
							esc_html__( '%s مورد یافت شد', 'zarincode' ),
							'<strong>' . esc_html( zc_fa_num( $wp_query->found_posts ) ) . '</strong>'
						);
						?>
					</span>

					<form class="zc-archive-sort" method="get">
						<select name="orderby" onchange="this.form.submit()" aria-label="<?php esc_attr_e( 'مرتب‌سازی', 'zarincode' ); ?>">
							<?php
							$zc_orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'date'; // phpcs:ignore
							$zc_sorts   = array(
								'date'       => __( 'جدیدترین', 'zarincode' ),
								'popular'    => __( 'محبوب‌ترین', 'zarincode' ),
								'price_low'  => __( 'ارزان‌ترین', 'zarincode' ),
								'price_high' => __( 'گران‌ترین', 'zarincode' ),
							);
							foreach ( $zc_sorts as $zc_k => $zc_v ) {
								printf(
									'<option value="%s" %s>%s</option>',
									esc_attr( $zc_k ),
									selected( $zc_orderby, $zc_k, false ),
									esc_html( $zc_v )
								);
							}
							?>
						</select>
					</form>
				</div>

				<div class="zc-grid zc-grid--<?php echo esc_attr( $zc_columns ); ?>">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content/card', $zc_tpl );
					endwhile;
					?>
				</div>

				<?php zc_pagination(); ?>

			<?php else : ?>
				<div class="zc-empty">
					<div class="zc-empty__icon"><?php zc_the_icon( 'search', 40 ); ?></div>
					<h3><?php esc_html_e( 'موردی یافت نشد', 'zarincode' ); ?></h3>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="zc-btn zc-btn--gold"><?php zc_the_icon( 'arrow-left', 17 ); ?><?php esc_html_e( 'بازگشت به خانه', 'zarincode' ); ?></a>
				</div>
			<?php endif; ?>
		</div>

		<aside class="zc-sidebar">
			<?php
			if ( 'zc_course' === $zc_type && is_active_sidebar( 'sidebar-course' ) ) {
				dynamic_sidebar( 'sidebar-course' );
			} else {
				get_sidebar();
			}
			?>
		</aside>
	</div>
</div>

<?php
get_footer();
