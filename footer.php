<?php
/**
 * قالب فوتر
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;
?>
	</main><!-- #zc-main -->

	<?php
	if ( ! zc_render_elementor_location( 'footer' ) ) {
		get_template_part( 'template-parts/footer/footer', zc_opt( 'zc_footer_style', 'default' ) );
	}
	?>

</div><!-- #zc-page -->

<?php
// جستجوی ای‌جکس.
if ( zc_opt( 'zc_ajax_search', true ) ) {
	get_template_part( 'template-parts/header/search-modal' );
}

// منوی موبایل.
get_template_part( 'template-parts/header/mobile-nav' );

// چت آنلاین.
if ( zc_opt( 'zc_chat_enable', true ) ) {
	get_template_part( 'template-parts/footer/chat' );
}

// بازگشت به بالا.
if ( zc_opt( 'zc_back_to_top', true ) ) :
	?>
	<button class="zc-to-top" aria-label="<?php esc_attr_e( 'بازگشت به بالا', 'zarincode' ); ?>">
		<?php zc_the_icon( 'arrow-up', 22 ); ?>
	</button>
	<?php
endif;

wp_footer();
?>
</body>
</html>
