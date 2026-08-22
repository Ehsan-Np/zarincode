<?php
/**
 * ماژول پیام‌رسان بله
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * ارسال پیام به بله.
 *
 * @param string $message متن.
 * @param string $image   تصویر.
 * @param array  $buttons دکمه‌ها.
 * @return bool|WP_Error
 */
function zc_bale_send( $message, $image = '', $buttons = array() ) {
	$token = trim( (string) zc_opt( 'zc_bale_token', '' ) );
	$chat  = trim( (string) zc_opt( 'zc_bale_chat_id', '' ) );

	if ( ! $token || ! $chat || ! zc_opt( 'zc_bale_enable', false ) ) {
		return new WP_Error( 'zc_bale_config', __( 'تنظیمات بله کامل نیست.', 'zarincode' ) );
	}

	$api    = 'https://tapi.bale.ai/bot' . $token . '/';
	$method = $image ? 'sendPhoto' : 'sendMessage';

	$body = array( 'chat_id' => $chat );

	if ( $image ) {
		$body['photo']   = $image;
		$body['caption'] = mb_substr( wp_strip_all_tags( $message ), 0, 1000 );
	} else {
		$body['text'] = mb_substr( wp_strip_all_tags( $message ), 0, 4000 );
	}

	if ( ! empty( $buttons ) ) {
		$body['reply_markup'] = wp_json_encode( array( 'inline_keyboard' => array( $buttons ) ) );
	}

	$response = wp_remote_post(
		$api . $method,
		array(
			'timeout' => 25,
			'body'    => $body,
		)
	);

	if ( is_wp_error( $response ) ) {
		zc_log( $response->get_error_message(), 'Bale' );
		return $response;
	}

	$result = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( empty( $result['ok'] ) ) {
		zc_log( $result, 'Bale' );
		return new WP_Error( 'zc_bale_failed', $result['description'] ?? __( 'خطا در ارسال به بله.', 'zarincode' ) );
	}

	return true;
}

/**
 * تست اتصال پیام‌رسان‌ها (ای‌جکس ادمین).
 *
 * @return void
 */
function zc_ajax_test_messenger() {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'zc_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ) );
	}

	$channel = isset( $_POST['channel'] ) ? sanitize_key( wp_unslash( $_POST['channel'] ) ) : '';
	$text    = sprintf(
		/* translators: %s: site name */
		__( '✅ پیام تست از قالب زرین کد — %s', 'zarincode' ),
		get_bloginfo( 'name' )
	);

	switch ( $channel ) {
		case 'telegram':
			$result = zc_telegram_send( $text );
			break;
		case 'bale':
			$result = zc_bale_send( $text );
			break;
		case 'sms':
			$mobile = isset( $_POST['mobile'] ) ? zc_sanitize_mobile( sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) ) : '';
			$result = $mobile ? zc_sms()->send( $mobile, $text ) : new WP_Error( 'no_mobile', __( 'شماره موبایل را وارد کنید.', 'zarincode' ) );
			break;
		default:
			$result = new WP_Error( 'bad_channel', __( 'کانال نامعتبر.', 'zarincode' ) );
	}

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	}

	wp_send_json_success( array( 'message' => __( 'پیام تست با موفقیت ارسال شد.', 'zarincode' ) ) );
}
add_action( 'wp_ajax_zc_test_messenger', 'zc_ajax_test_messenger' );
