<?php
/**
 * قالب نتایج جستجو
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

get_header();

zc_page_hero(
	sprintf(
		/* translators: %s: search query */
		esc_html__( 'نتایج جستجو برای: %s', 'zarincode' ),
		'<span style="color:var(--zc-gold-2)">' . esc_html( get_search_query() ) . '</span>'
	)
);
?>

<div class="zc-container">
	<div class="zc-content-sidebar">
		<div class="zc-content">
			<?php if ( have_posts() ) : ?>
				<div class="zc-grid zc-grid--3">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content/card', 'post' );
					endwhile;
					?>
				</div>
				<?php zc_pagination(); ?>
			<?php else : ?>
				<div class="zc-empty">
					<div class="zc-empty__icon"><?php zc_the_icon( 'search', 40 ); ?></div>
					<h3><?php esc_html_e( 'نتیجه‌ای یافت نشد', 'zarincode' ); ?></h3>
					<p><?php esc_html_e( 'عبارت دیگری را جستجو کنید یا از دسته‌بندی‌ها استفاده کنید.', 'zarincode' ); ?></p>
					<div style="max-width:420px;margin:0 auto">
						<?php get_search_form(); ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php get_sidebar(); ?>
	</div>
</div>

<?php
get_footer();
