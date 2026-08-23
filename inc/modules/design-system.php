<?php
/**
 * توکن‌های طراحی و حالت تیره.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * بارگذاری توکن و دارک مود.
 *
 * @return void
 */
function zc_design_enqueue() {
	wp_enqueue_style( 'zc-tokens', ZC_ASSETS . 'css/tokens.css', array(), ZC_VERSION );
	if ( zc_opt( 'zc_dark_enable', true ) ) {
		wp_enqueue_style( 'zc-dark', ZC_ASSETS . 'css/dark.css', array( 'zc-tokens', 'zc-main' ), ZC_VERSION );
		wp_enqueue_script( 'zc-dark', ZC_ASSETS . 'js/dark.js', array(), ZC_VERSION, true );
	}
}
add_action( 'wp_enqueue_scripts', 'zc_design_enqueue', 4 );

/**
 * کلاس اولیه html از کوکی (جلوگیری از چشمک).
 *
 * @return void
 */
function zc_dark_early_script() {
	if ( ! zc_opt( 'zc_dark_enable', true ) ) {
		return;
	}
	echo '<script>(function(){try{var t=localStorage.getItem("zc-theme");if(t==="dark"){document.documentElement.classList.add("zc-dark");}else if(t==="light"){document.documentElement.classList.remove("zc-dark");}else if(window.matchMedia&&window.matchMedia("(prefers-color-scheme: dark)").matches){document.documentElement.classList.add("zc-dark");}}catch(e){}})();</script>';
}
add_action( 'wp_head', 'zc_dark_early_script', 0 );

/**
 * دکمه سوییچ در تاپ‌بار پنل.
 *
 * @return void
 */
function zc_dark_toggle_markup() {
	if ( ! zc_opt( 'zc_dark_enable', true ) || ! is_page_template( 'templates/template-panel.php' ) ) {
		return;
	}
	echo '<button type="button" class="zc-hicon zc-theme-toggle" data-zc-theme aria-label="' . esc_attr__( 'تغییر حالت تیره', 'zarincode' ) . '">' . zc_icon( 'sparkle', 18 ) . '</button>'; // phpcs:ignore
}
add_action( 'wp_footer', 'zc_dark_toggle_markup', 5 );
