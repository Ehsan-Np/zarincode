<?php
/**
 * قالب برگه
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	// اگر با المنتور ساخته شده، بدون هیرو و رپر نمایش داده شود.
	if ( zc_built_with_elementor() ) {
		the_content();
	} elseif ( function_exists( 'is_cart' ) && is_cart() && shortcode_exists( 'woocommerce_cart' ) ) {
		/*
		 * سبد خرید: به‌جای بلوک گوتنبرگِ ووکامرس (که به جاوااسکریپت
		 * wc-blocks نیاز دارد و در این قالب باگوتنبرگ غیرفعال رندر نمی‌شود)،
		 * از شورت‌کد کلاسیک ووکامرس استفاده می‌کنیم تا سبد همیشه نمایش داده
		 * شود و با قالب هماهنگ باشد.
		 */
		zc_page_hero( get_the_title() );
		echo '<div class="zc-container"><div class="zc-woo-flow">';
		echo do_shortcode( '[woocommerce_cart]' );
		echo '</div></div>';
	} elseif ( function_exists( 'is_checkout' ) && is_checkout() && shortcode_exists( 'woocommerce_checkout' ) ) {
		/*
		 * تسویه‌ی حساب: همان رویکرد — شورت‌کد کلاسیک به‌جای بلوک گوتنبرگ.
		 */
		zc_page_hero( get_the_title() );
		echo '<div class="zc-container"><div class="zc-woo-flow">';
		echo do_shortcode( '[woocommerce_checkout]' );
		echo '</div></div>';
	} else {
		zc_page_hero( get_the_title() );
		?>
		<div class="zc-container">
			<div class="zc-content" style="max-width:900px;margin:0 auto">
				<article <?php post_class( 'zc-entry' ); ?>>
					<div class="zc-entry__content"><?php the_content(); ?></div>
				</article>

				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</div>
		</div>
		<?php
	}
endwhile;

get_footer();
