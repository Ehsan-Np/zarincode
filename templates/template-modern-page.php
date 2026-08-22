<?php
/**
 * قالب مدرن صفحات داخلی زرین کد
 * ---------------------------------------------------------------------------
 * این قالب برای رندر صفحات کلیدی قالب (تماس با ما، درباره ما، خدمات،
 * قوانین، حریم خصوصی، گارانتی، بازگشت وجه و درخواست پروژه) به کار می‌رود.
 *
 * بر اساس «نامک» (slug) صفحه، تابع رندر مناسب از ماژول modern-pages صدا
 * زده می‌شود و خروجی HTML مدرن (با کلاس‌های pages.css) تولید می‌شود.
 *
 * Template Name: صفحه مدرن زرین کد
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

get_header();

$zc_page_id = get_the_ID();
$zc_slug    = get_post_field( 'post_name', $zc_page_id );

// نگاشت نامک به تابع رندر.
$zc_modern_map = array(
	'contact-us'   => 'zc_render_contact_page',
	'about-us'     => 'zc_render_about_page',
	'request'      => 'zc_render_request_page',
	'services'     => 'zc_render_services_page',
	'terms'        => 'zc_render_terms_page',
	'privacy-policy'=> 'zc_render_privacy_page',
	'warranty'     => 'zc_render_warranty_page',
	'refund-policy'=> 'zc_render_refund_page',
);

/**
 * اجازه به افزونه‌ها/فریم‌ورک برای افزودن یا جایگزینی نگاشت صفحات.
 */
$zc_modern_map = apply_filters( 'zc_modern_page_map', $zc_modern_map );

?>
<div class="zc-container zc-modern-page">

	<?php
	if ( isset( $zc_modern_map[ $zc_slug ] ) && function_exists( $zc_modern_map[ $zc_slug ] ) ) {
		call_user_func( $zc_modern_map[ $zc_slug ] );
	} else {
		// اگر صفحه‌ای به این سیستم نگاشت نشده بود، خروجی استاندارد وردپرس.
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
	}
	?>

</div>
<?php
get_footer();
