<?php
/**
 * ماژول بارگذاری فونت — تزریق @font-face با URL مطلق قالب
 * ---------------------------------------------------------------------------
 * مشکل: فایل CSS با url('../fonts/...') از مسیر نسبی استفاده می‌کرد و در
 * نصب‌هایی که آدرس siteurl یا ساختار پوشه‌ها متفاوت است (مثلاً XAMPP روی
 * ویندوز)، مرورگر آدرس اشتباه مثل localhost/C:/xampp/... تولید می‌کرد.
 *
 * راه‌حل: همه‌ی @font-face ها با URL مطلقِ ساخته‌شده از
 * get_template_directory_uri() تزریق می‌شوند تا همیشه به مسیر درست
 * پوشه‌ی فونت قالب اشاره کنند.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * فهرست فونت‌ها و فایل‌های مرتبط (نام خانواده ← فایل‌ها).
 *
 * @return array
 */
function zc_font_faces() {
	return array(
		'Vazirmatn' => array(
			array(
				'src'    => array( 'Vazirmatn-Variable.woff2', 'Vazirmatn-Regular.woff2' ),
				'weight' => '100 900',
			),
			array( 'src' => array( 'Vazirmatn-Regular.woff2' ), 'weight' => '400' ),
			array( 'src' => array( 'Vazirmatn-Medium.woff2' ), 'weight' => '500' ),
			array( 'src' => array( 'Vazirmatn-Bold.woff2' ), 'weight' => '700' ),
			array( 'src' => array( 'Vazirmatn-ExtraBold.woff2' ), 'weight' => '800' ),
		),
		'Shabnam' => array(
			array( 'src' => array( 'Shabnam.woff2', 'Shabnam.woff' ), 'weight' => '400' ),
			array( 'src' => array( 'Shabnam-Thin.woff2', 'Shabnam-Thin.woff' ), 'weight' => '100' ),
			array( 'src' => array( 'Shabnam-Light.woff2', 'Shabnam-Light.woff' ), 'weight' => '300' ),
			array( 'src' => array( 'Shabnam-Medium.woff2', 'Shabnam-Medium.woff' ), 'weight' => '500' ),
			array( 'src' => array( 'Shabnam-Bold.woff2', 'Shabnam-Bold.woff' ), 'weight' => '700' ),
		),
		'Samim' => array(
			array( 'src' => array( 'Samim.woff2', 'Samim.woff' ), 'weight' => '400' ),
			array( 'src' => array( 'Samim-Medium.woff2', 'Samim-Medium.woff' ), 'weight' => '500' ),
			array( 'src' => array( 'Samim-Bold.woff2', 'Samim-Bold.woff' ), 'weight' => '700' ),
		),
		'Gandom' => array(
			array( 'src' => array( 'Gandom.woff2', 'Gandom.woff' ), 'weight' => '400' ),
		),
		'Tanha' => array(
			array( 'src' => array( 'Tanha.woff2', 'Tanha.woff' ), 'weight' => '400' ),
		),
		'Yekan' => array(
			array( 'src' => array( 'Yekan.woff2', 'Yekan.woff' ), 'weight' => '400' ),
		),
		'Arad' => array(
			array(
				'src'    => array( 'Arad-Variable.woff2', 'Arad-Regular.woff2' ),
				'weight' => '100 900',
			),
			array( 'src' => array( 'Arad-Regular.woff2' ), 'weight' => '400' ),
		),
		'Azad' => array(
			array( 'src' => array( 'Azad-Regular.woff2', 'Azad-Regular.woff' ), 'weight' => '400' ),
		),
		'Ario' => array(
			array( 'src' => array( 'Ario-Regular.woff2' ), 'weight' => '400' ),
		),
	);
}

/**
 * تولید CSS مربوط به @font-face با URL مطلق قالب.
 *
 * @return string
 */
function zc_build_font_faces_css() {
	$out    = '';
	$prefix = ZC_ASSETS . 'fonts/';

	foreach ( zc_font_faces() as $family => $faces ) {
		foreach ( $faces as $face ) {
			$srcs   = array();
			$formats = array( 'woff2', 'woff', 'ttf' );
			foreach ( $face['src'] as $file ) {
				$ext  = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
				$fmt  = in_array( $ext, $formats, true ) ? $ext : 'woff2';
				$srcs[] = "url('" . esc_url( $prefix . $file ) . "') format('" . $fmt . "')";
			}

			if ( empty( $srcs ) ) {
				continue;
			}

			$out .= '@font-face{';
			$out .= "font-family:'" . $family . "';";
			$out .= 'src:' . implode( ',', $srcs ) . ';';
			$out .= 'font-weight:' . $face['weight'] . ';font-style:normal;font-display:swap;';
			$out .= '}';
		}
	}

	return $out;
}

/**
 * تزریق @font-face به استایل قالب.
 *
 * @return void
 */
function zc_inject_font_faces() {
	if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		return;
	}
	$css = zc_build_font_faces_css();
	if ( $css ) {
		wp_add_inline_style( 'zc-fonts', $css );
	}
}
add_action( 'wp_enqueue_scripts', 'zc_inject_font_faces', 20 );

/**
 * تزریق @font-face در استایل صفحه ورود وردپرس.
 *
 * @return void
 */
function zc_inject_font_faces_login() {
	$css = zc_build_font_faces_css();
	if ( $css ) {
		wp_add_inline_style( 'zc-fonts', $css );
	}
}
add_action( 'login_enqueue_scripts', 'zc_inject_font_faces_login', 20 );
