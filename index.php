<?php
/**
 * قالب اصلی بلاگ — طراحی مجله‌ای
 *
 * در صفحه‌ی نخست بلاگ، تازه‌ترین نوشته به صورت «مطلب ویژه» تمام‌عرض
 * و بقیه در شبکه‌ای واکنش‌گرا نمایش داده می‌شوند.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

get_header();

$zc_sidebar = zc_opt( 'zc_blog_sidebar', 'right' );
$zc_columns = (int) zc_opt( 'zc_blog_columns', 3 );
$zc_has_sb  = 'none' !== $zc_sidebar;

// مطلب ویژه فقط در صفحه‌ی اول و وقتی نوار کناری خاموش نیست.
$zc_feature = ( is_home() && ! is_paged() );
$zc_index   = 0;
?>

<div class="zc-blog-head">
	<div class="zc-container">
		<?php zc_breadcrumb( true ); ?>

		<h1 class="zc-blog-head__title">
			<?php
			if ( is_home() && ! is_front_page() ) {
				echo esc_html( get_the_title( (int) get_option( 'page_for_posts' ) ) );
			} else {
				esc_html_e( 'مجله زرین کد', 'zarincode' );
			}
			?>
		</h1>

		<p class="zc-blog-head__sub">
			<?php esc_html_e( 'آخرین مقالات، اخبار تکنولوژی و نکات کاربردی دنیای برنامه‌نویسی و کسب‌وکار دیجیتال', 'zarincode' ); ?>
		</p>

		<?php
		$zc_cats = get_categories(
			array(
				'hide_empty' => true,
				'number'     => 8,
				'orderby'    => 'count',
				'order'      => 'DESC',
			)
		);

		if ( $zc_cats ) :
			?>
			<nav class="zc-blog-filter" aria-label="<?php esc_attr_e( 'دسته‌بندی مطالب', 'zarincode' ); ?>">
				<a href="<?php echo esc_url( get_permalink( (int) get_option( 'page_for_posts' ) ) ? get_permalink( (int) get_option( 'page_for_posts' ) ) : home_url( '/' ) ); ?>"
					class="zc-blog-filter__item<?php echo is_category() ? '' : ' is-active'; ?>">
					<?php esc_html_e( 'همه مطالب', 'zarincode' ); ?>
				</a>

				<?php foreach ( $zc_cats as $zc_cat ) : ?>
					<a href="<?php echo esc_url( get_category_link( $zc_cat ) ); ?>"
						class="zc-blog-filter__item<?php echo is_category( $zc_cat->term_id ) ? ' is-active' : ''; ?>">
						<?php echo esc_html( $zc_cat->name ); ?>
						<span><?php echo esc_html( zc_fa_num( $zc_cat->count ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
	</div>
</div>

<div class="zc-container zc-section">
	<div class="zc-layout <?php echo $zc_has_sb ? 'zc-layout--sidebar' : 'zc-layout--full'; ?>">

		<main class="zc-layout__main">
			<?php if ( have_posts() ) : ?>

				<div class="zc-blog-grid zc-blog-grid--<?php echo esc_attr( $zc_columns ); ?>">
					<?php
					while ( have_posts() ) :
						the_post();

						// نوشته‌ی نخست به صورت ویژه و تمام‌عرض.
						$zc_style = ( $zc_feature && 0 === $zc_index ) ? 'featured' : 'grid';

						get_template_part(
							'template-parts/content/card',
							'post',
							array( 'style' => $zc_style )
						);

						$zc_index++;
					endwhile;
					?>
				</div>

				<?php zc_pagination(); ?>

			<?php else : ?>
				<div class="zc-empty">
					<div class="zc-empty__icon"><?php zc_the_icon( 'search', 40 ); ?></div>
					<h3><?php esc_html_e( 'هنوز مطلبی منتشر نشده است', 'zarincode' ); ?></h3>
					<p><?php esc_html_e( 'به زودی مقالات تازه در این بخش قرار می‌گیرد. می‌توانید از دوره‌های آموزشی دیدن کنید.', 'zarincode' ); ?></p>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'zc_course' ) ); ?>" class="zc-btn zc-btn--gold">
						<?php esc_html_e( 'مشاهده دوره‌ها', 'zarincode' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</main>

		<?php
		if ( $zc_has_sb ) {
			get_sidebar();
		}
		?>
	</div>
</div>

<?php
get_footer();
