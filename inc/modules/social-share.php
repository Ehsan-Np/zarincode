<?php
/**
 * مدیریت دکمه‌های اشتراک‌گذاری
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;


/**
 * ثبت و بررسی اینکه دکمه‌های اشتراک‌گذاری در این درخواست چاپ شده‌اند یا نه.
 *
 * از چاپ دوباره‌ی دکمه‌ها جلوگیری می‌کند؛ چون هم فیلتر the_content و هم
 * قالب‌های تک‌نوشته ممکن است آن‌ها را فراخوانی کنند.
 *
 * @param bool $mark اگر true باشد وضعیت را «چاپ‌شده» علامت می‌زند.
 * @return bool وضعیت پیش از فراخوانی.
 */
function zc_share_rendered( $mark = false, $post_id = null ) {
	static $rendered = array();

	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	$was     = ! empty( $rendered[ $post_id ] );

	if ( $mark ) {
		$rendered[ $post_id ] = true;
	}

	return $was;
}

/**
 * افزودن خودکار دکمه‌های اشتراک به انتهای محتوا.
 *
 * @param string $content محتوا.
 * @return string
 */
function zc_auto_share_buttons( $content ) {
	if ( ! is_singular( array( 'post', 'zc_tutorial' ) ) || ! is_main_query() || ! in_the_loop() ) {
		return $content;
	}

	if ( ! zc_opt( 'zc_share_auto', false ) || ! zc_opt( 'zc_share_enable', true ) ) {
		return $content;
	}

	// اگر دکمه‌ها قبلاً چاپ شده باشند، دوباره چاپ نمی‌شوند.
	if ( zc_share_rendered() ) {
		return $content;
	}

	ob_start();
	zc_share_buttons();

	return $content . ob_get_clean();
}
add_filter( 'the_content', 'zc_auto_share_buttons', 25 );

/**
 * شورت‌کد دکمه‌های اشتراک.
 *
 * @param array $atts ویژگی‌ها.
 * @return string
 */
function zc_share_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'id' => 0 ), $atts, 'zc_share' );
	ob_start();
	zc_share_buttons( (int) $atts['id'] );
	return ob_get_clean();
}
add_shortcode( 'zc_share', 'zc_share_shortcode' );

/**
 * ثبت آمار اشتراک‌گذاری.
 *
 * @return void
 */
function zc_ajax_track_share() {
	zc_check_ajax();

	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$network = isset( $_POST['network'] ) ? sanitize_key( wp_unslash( $_POST['network'] ) ) : '';

	if ( $post_id && $network ) {
		$stats             = (array) get_post_meta( $post_id, '_zc_share_stats', true );
		$stats[ $network ] = ( $stats[ $network ] ?? 0 ) + 1;
		update_post_meta( $post_id, '_zc_share_stats', $stats );
	}

	wp_send_json_success();
}
add_action( 'wp_ajax_zc_track_share', 'zc_ajax_track_share' );
add_action( 'wp_ajax_nopriv_zc_track_share', 'zc_ajax_track_share' );
