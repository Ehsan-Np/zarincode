<?php
/**
 * ماژول تلگرام — ارسال خودکار نوشته‌ها، محصولات و دوره‌ها
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * ارسال پیام به تلگرام.
 *
 * @param string $message متن.
 * @param string $image   تصویر.
 * @param array  $buttons دکمه‌ها.
 * @return bool|WP_Error
 */
function zc_telegram_send( $message, $image = '', $buttons = array() ) {
	$token = trim( (string) zc_opt( 'zc_telegram_token', '' ) );
	$chat  = trim( (string) zc_opt( 'zc_telegram_chat_id', '' ) );

	if ( ! $token || ! $chat || ! zc_opt( 'zc_telegram_enable', false ) ) {
		return new WP_Error( 'zc_tg_config', __( 'تنظیمات تلگرام کامل نیست.', 'zarincode' ) );
	}

	$api    = 'https://api.telegram.org/bot' . $token . '/';
	$method = $image ? 'sendPhoto' : 'sendMessage';

	$body = array(
		'chat_id'    => $chat,
		'parse_mode' => 'HTML',
	);

	if ( $image ) {
		$body['photo']   = $image;
		$body['caption'] = mb_substr( $message, 0, 1020 );
	} else {
		$body['text']                     = mb_substr( $message, 0, 4090 );
		$body['disable_web_page_preview'] = false;
	}

	if ( ! empty( $buttons ) ) {
		$body['reply_markup'] = wp_json_encode( array( 'inline_keyboard' => array( $buttons ) ) );
	}

	$response = wp_remote_post(
		$api . $method,
		array(
			'timeout' => 20,
			'body'    => $body,
		)
	);

	if ( is_wp_error( $response ) ) {
		zc_log( $response->get_error_message(), 'Telegram' );
		return $response;
	}

	$result = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( empty( $result['ok'] ) ) {
		zc_log( $result, 'Telegram' );
		return new WP_Error( 'zc_tg_failed', $result['description'] ?? __( 'خطا در ارسال به تلگرام.', 'zarincode' ) );
	}

	return true;
}

/**
 * ارسال خودکار هنگام انتشار محتوا.
 *
 * @param string  $new_status وضعیت جدید.
 * @param string  $old_status وضعیت قبلی.
 * @param WP_Post $post       پست.
 * @return void
 */
function zc_auto_publish_messengers( $new_status, $old_status, $post ) {
	if ( 'publish' !== $new_status || 'publish' === $old_status ) {
		return;
	}

	$types = (array) zc_opt( 'zc_messenger_post_types', array( 'post', 'zc_course', 'product' ) );

	if ( ! in_array( $post->post_type, $types, true ) ) {
		return;
	}

	// جلوگیری از ارسال تکراری.
	if ( get_post_meta( $post->ID, '_zc_sent_messengers', true ) ) {
		return;
	}

	$message = zc_build_messenger_message( $post );
	$image   = get_the_post_thumbnail_url( $post->ID, 'zc-card-lg' );
	$buttons = array(
		array(
			'text' => zc_opt( 'zc_messenger_btn_text', '🔗 مشاهده در سایت' ),
			'url'  => get_permalink( $post->ID ),
		),
	);

	if ( zc_opt( 'zc_telegram_enable', false ) ) {
		zc_telegram_send( $message, $image, $buttons );
	}

	if ( zc_opt( 'zc_bale_enable', false ) ) {
		zc_bale_send( $message, $image, $buttons );
	}

	update_post_meta( $post->ID, '_zc_sent_messengers', current_time( 'mysql' ) );
}
add_action( 'transition_post_status', 'zc_auto_publish_messengers', 10, 3 );

/**
 * ساخت متن پیام برای پیام‌رسان‌ها.
 *
 * @param WP_Post $post پست.
 * @return string
 */
function zc_build_messenger_message( $post ) {
	$labels = array(
		'post'        => '📝 مقاله جدید',
		'zc_course'   => '🎓 دوره جدید',
		'zc_tutorial' => '📚 آموزش جدید',
		'product'     => '🛍 محصول جدید',
	);

	$label   = $labels[ $post->post_type ] ?? '🔔 مطلب جدید';
	$excerpt = wp_strip_all_tags( get_the_excerpt( $post ) );
	$excerpt = mb_substr( $excerpt, 0, 300 );

	$price = '';
	if ( 'product' === $post->post_type && function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product( $post->ID );
		if ( $product ) {
			$price = "\n💰 قیمت: " . wp_strip_all_tags( $product->get_price_html() );
		}
	} elseif ( 'zc_course' === $post->post_type ) {
		$p     = (float) get_post_meta( $post->ID, '_zc_price', true );
		$price = "\n💰 قیمت: " . ( $p > 0 ? zc_fa_num( number_format( $p ) ) . ' تومان' : 'رایگان' );
	}

	$template = zc_opt(
		'zc_messenger_template',
		"{label}\n\n<b>{title}</b>\n\n{excerpt}{price}\n\n🌐 {site}\n{link}"
	);

	return str_replace(
		array( '{label}', '{title}', '{excerpt}', '{price}', '{link}', '{site}' ),
		array( $label, esc_html( $post->post_title ), esc_html( $excerpt ), $price, get_permalink( $post->ID ), get_bloginfo( 'name' ) ),
		$template
	);
}

/**
 * ارسال دستی تبلیغات (ای‌جکس ادمین).
 *
 * @return void
 */
function zc_ajax_messenger_broadcast() {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'zc_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ) );
	}

	$message  = isset( $_POST['message'] ) ? wp_kses_post( wp_unslash( $_POST['message'] ) ) : '';
	$image    = isset( $_POST['image'] ) ? esc_url_raw( wp_unslash( $_POST['image'] ) ) : '';
	$channels = isset( $_POST['channels'] ) ? array_map( 'sanitize_key', (array) $_POST['channels'] ) : array();

	if ( ! $message ) {
		wp_send_json_error( array( 'message' => __( 'متن پیام را وارد کنید.', 'zarincode' ) ) );
	}

	$results = array();

	if ( in_array( 'telegram', $channels, true ) ) {
		$r                    = zc_telegram_send( $message, $image );
		$results['telegram'] = is_wp_error( $r ) ? $r->get_error_message() : __( 'ارسال شد', 'zarincode' );
	}

	if ( in_array( 'bale', $channels, true ) ) {
		$r                = zc_bale_send( $message, $image );
		$results['bale'] = is_wp_error( $r ) ? $r->get_error_message() : __( 'ارسال شد', 'zarincode' );
	}

	if ( in_array( 'sms', $channels, true ) ) {
		$list = get_option( 'zc_newsletter_list', array() );
		$nums = array_filter( $list, 'zc_sanitize_mobile' );
		if ( $nums ) {
			$r               = zc_sms()->send_bulk( array_slice( $nums, 0, 100 ), wp_strip_all_tags( $message ) );
			$results['sms'] = is_wp_error( $r ) ? $r->get_error_message() : __( 'ارسال شد', 'zarincode' );
		}
	}

	wp_send_json_success( array( 'message' => __( 'عملیات انجام شد.', 'zarincode' ), 'results' => $results ) );
}
add_action( 'wp_ajax_zc_messenger_broadcast', 'zc_ajax_messenger_broadcast' );
