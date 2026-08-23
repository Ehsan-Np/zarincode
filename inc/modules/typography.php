<?php
/**
 * اعمال تنظیمات تایپوگرافی پنل به CSS پویا.
 *
 * @package Zarincode
 */
defined( 'ABSPATH' ) || exit;

/**
 * متغیرها و قواعد تایپوگرافی.
 *
 * @param string $css CSS فعلی.
 * @return string
 */
function zc_typography_dynamic_css( $css ) {
	$body_font    = zc_font_css_family( zc_opt( 'zc_font_body', 'samim' ) );
	$heading_font = zc_font_css_family( zc_opt( 'zc_font_heading', 'samim' ) );
	$body_weight  = max( 100, min( 900, (int) zc_opt( 'zc_font_weight', 400 ) ) );
	$head_weight  = max( 100, min( 900, (int) zc_opt( 'zc_heading_weight', 700 ) ) );
	$line_height  = max( 1.2, min( 2.8, (float) zc_opt( 'zc_font_line_height', 2 ) ) );
	$letter       = max( 0, min( 5, (float) zc_opt( 'zc_font_letter_spacing', 0 ) ) );
	$heading_size = max( 0, min( 60, (int) zc_opt( 'zc_heading_size', 0 ) ) );
	$text_color   = sanitize_hex_color( zc_opt( 'zc_text_color', '#3c4652' ) ) ?: '#3c4652';
	$head_color   = sanitize_hex_color( zc_opt( 'zc_heading_color', '#141A31' ) ) ?: '#141A31';
	$link_color   = sanitize_hex_color( zc_opt( 'zc_link_color', '#8A6D12' ) ) ?: '#8A6D12';

	$css .= ':root{--zc-font-body:' . $body_font . ';--zc-font-heading:' . $heading_font . ';--zc-text:' . $text_color . ';--zc-heading:' . $head_color . ';--zc-link:' . $link_color . ';}';
	$css .= 'body,button,input,select,textarea{font-family:var(--zc-font-body);font-weight:' . $body_weight . ';line-height:' . $line_height . ';letter-spacing:' . $letter . 'px;color:var(--zc-text)}';
	$css .= 'h1,h2,h3,h4,h5,h6,.zc-section__title,.zc-heading__title{font-family:var(--zc-font-heading);font-weight:' . $head_weight . ';color:var(--zc-heading)}';
	$css .= 'a:not(.zc-btn):not(.button){--zc-link-current:var(--zc-link)}';
	if ( $heading_size > 0 ) {
		$scale = 1 + ( $heading_size / 100 );
		$css  .= 'h1{font-size:calc(clamp(1.7rem,3.4vw,2.6rem)*' . $scale . ')}h2{font-size:calc(clamp(1.4rem,2.6vw,2rem)*' . $scale . ')}h3{font-size:calc(clamp(1.15rem,2vw,1.45rem)*' . $scale . ')}';
	}
	return $css;
}
add_filter( 'zc_dynamic_css', 'zc_typography_dynamic_css', 20 );

/**
 * فونت انتخاب‌شده را preload می‌کند؛ Samim بی‌دلیل برای همه دانلود نمی‌شود.
 *
 * @return string
 */
function zc_typography_preload_file() {
	$map = array(
		'samim' => 'Samim.woff2', 'vazirmatn' => 'Vazirmatn-Variable.woff2',
		'shabnam' => 'Shabnam.woff2', 'gandom' => 'Gandom.woff2', 'tanha' => 'Tanha.woff2',
		'yekan' => 'Yekan.woff2', 'arad' => 'Arad-Variable.woff2', 'azad' => 'Azad-Regular.woff2', 'ario' => 'Ario-Regular.woff2',
	);
	$key = (string) zc_opt( 'zc_font_body', 'samim' );
	return $map[ $key ] ?? 'Samim.woff2';
}
