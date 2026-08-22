<?php
/**
 * قالب هدر
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php if ( zc_opt( 'zc_scroll_progress', true ) ) : ?>
	<div class="zc-scrollbar" aria-hidden="true"><span data-zc-scrollbar></span></div>
<?php endif; ?>

<?php if ( zc_opt( 'zc_preloader', true ) ) : ?>
<div class="zc-preloader" role="status" aria-label="<?php esc_attr_e( 'در حال بارگذاری', 'zarincode' ); ?>">
	<div class="zc-preloader__logo">
		<div class="zc-preloader__ring"></div>
		<span class="zc-preloader__text"><?php echo esc_html( zc_opt( 'zc_site_name', 'زرین کد' ) ); ?></span>
	</div>
</div>
<?php endif; ?>

<a class="skip-link screen-reader-text" href="#zc-main"><?php esc_html_e( 'پرش به محتوای اصلی', 'zarincode' ); ?></a>

<div id="zc-page" class="zc-page">

	<?php
	/**
	 * نوار اطلاعیه پیش از هدر.
	 */
	do_action( 'zc_before_header' );

	/**
	 * هدر: در صورت وجود قالب سفارشی المنتور از آن استفاده می‌شود.
	 */
	if ( ! zc_render_elementor_location( 'header' ) ) {
		get_template_part( 'template-parts/header/header', zc_opt( 'zc_header_style', 'default' ) );
	}
	?>

	<main id="zc-main" class="zc-main" role="main">
