<?php
/**
 * ربات تلگرام و بله — اطلاع‌رسانی شخصی به کاربران
 *
 * کاربر با اتصال حساب خود به ربات مجموعه، اعلان‌های سایت
 * (پاسخ تیکت، دوره جدید، سفارش، کیف پول و ...) را مستقیماً
 * در تلگرام یا بله دریافت می‌کند.
 *
 * جریان کار:
 *  ۱. کاربر در پنل روی «اتصال به ربات» می‌زند و یک کد یکتا می‌گیرد.
 *  ۲. کد را برای ربات ارسال می‌کند (/start CODE).
 *  ۳. وب‌هوک یا کران، کد را با کاربر تطبیق می‌دهد و chat_id را ذخیره می‌کند.
 *  ۴. از آن پس اعلان‌ها به آن chat_id ارسال می‌شود.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * پیام‌رسان‌های پشتیبانی‌شده.
 *
 * @return array
 */
function zc_messengers() {
	return array(
		'telegram' => array(
			'label' => __( 'تلگرام', 'zarincode' ),
			'api'   => 'https://api.telegram.org/bot%s/%s',
			'token' => zc_opt( 'zc_telegram_token', '' ),
			'bot'   => zc_opt( 'zc_telegram_bot_username', '' ),
		),
		'bale'     => array(
			'label' => __( 'بله', 'zarincode' ),
			'api'   => 'https://tapi.bale.ai/bot%s/%s',
			'token' => zc_opt( 'zc_bale_token', '' ),
			'bot'   => zc_opt( 'zc_bale_bot_username', '' ),
		),
	);
}

/**
 * انواع اعلان‌هایی که کاربر می‌تواند مشترک شود.
 *
 * @return array
 */
function zc_notification_types() {
	return apply_filters(
		'zc_notification_types',
		array(
			'ticket_reply' => __( 'پاسخ به تیکت پشتیبانی', 'zarincode' ),
			'new_course'   => __( 'انتشار دوره‌های جدید', 'zarincode' ),
			'new_product'  => __( 'محصولات تازه فروشگاه', 'zarincode' ),
			'new_post'     => __( 'مقالات و آموزش‌های جدید', 'zarincode' ),
			'order'        => __( 'وضعیت سفارش‌ها', 'zarincode' ),
			'wallet'       => __( 'تراکنش‌های کیف پول', 'zarincode' ),
			'booking'      => __( 'یادآوری نوبت مشاوره', 'zarincode' ),
			'discount'     => __( 'تخفیف‌ها و پیشنهادهای ویژه', 'zarincode' ),
			'subscription_expiring' => __( 'یادآوری تمدید اشتراک', 'zarincode' ),
			'contract'     => __( 'قراردادها و پرداخت‌ها', 'zarincode' ),
			'admin_alerts' => __( 'اعلان‌های مدیریتی (فقط مدیران)', 'zarincode' ),
		)
	);
}

/**
 * ارسال یک درخواست به API پیام‌رسان.
 *
 * @param string $messenger کلید پیام‌رسان (telegram|bale).
 * @param string $method    متد API مانند sendMessage.
 * @param array  $args      پارامترها.
 * @return array|WP_Error
 */
function zc_messenger_request( $messenger, $method, $args = array() ) {
	$all = zc_messengers();

	if ( empty( $all[ $messenger ] ) ) {
		return new WP_Error( 'zc_bad_messenger', __( 'پیام‌رسان نامعتبر است.', 'zarincode' ) );
	}

	$config = $all[ $messenger ];

	if ( empty( $config['token'] ) ) {
		return new WP_Error( 'zc_no_token', __( 'توکن ربات تنظیم نشده است.', 'zarincode' ) );
	}

	$url = sprintf( $config['api'], $config['token'], $method );

	$response = wp_remote_post(
		$url,
		array(
			'timeout' => 15,
			'body'    => $args,
		)
	);

	if ( is_wp_error( $response ) ) {
		zc_log( 'messenger error: ' . $response->get_error_message() );
		return $response;
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( empty( $body['ok'] ) ) {
		$desc = $body['description'] ?? __( 'خطای ناشناخته', 'zarincode' );
		zc_log( 'messenger API error: ' . $desc );

		return new WP_Error( 'zc_api_error', $desc );
	}

	return $body;
}

/**
 * ارسال پیام به یک چت مشخص.
 *
 * @param string $messenger پیام‌رسان.
 * @param string $chat_id   شناسه چت.
 * @param string $text      متن پیام (پشتیبانی از HTML ساده).
 * @param array  $buttons   دکمه‌های شیشه‌ای: [ [ 'text' => .., 'url' => .. ] ]
 * @return bool
 */
function zc_messenger_send_to( $messenger, $chat_id, $text, $buttons = array() ) {
	if ( ! $chat_id ) {
		return false;
	}

	$args = array(
		'chat_id'    => $chat_id,
		'text'       => $text,
		'parse_mode' => 'HTML',
	);

	if ( $buttons ) {
		$args['reply_markup'] = wp_json_encode(
			array(
				'inline_keyboard' => array_map(
					static function ( $b ) {
						return array( array( 'text' => $b['text'], 'url' => $b['url'] ) );
					},
					$buttons
				),
			)
		);
	}

	$result = zc_messenger_request( $messenger, 'sendMessage', $args );

	return ! is_wp_error( $result );
}

/**
 * تولید (یا بازیابی) کد اتصال یکتا برای کاربر.
 *
 * @param int $user_id شناسه کاربر.
 * @return string
 */
function zc_get_connect_code( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	if ( ! $user_id ) {
		return '';
	}

	$code = get_user_meta( $user_id, 'zc_bot_code', true );

	if ( ! $code ) {
		$code = strtoupper( wp_generate_password( 8, false, false ) );
		update_user_meta( $user_id, 'zc_bot_code', $code );
	}

	return $code;
}

/**
 * یافتن کاربر بر اساس کد اتصال.
 *
 * @param string $code کد.
 * @return int شناسه کاربر یا صفر.
 */
function zc_user_by_connect_code( $code ) {
	$code = strtoupper( sanitize_text_field( $code ) );

	if ( ! $code ) {
		return 0;
	}

	$uid = (int) get_transient( 'zc_bot_code_' . $code );
	if ( $uid ) {
		delete_transient( 'zc_bot_code_' . $code );
		delete_user_meta( $uid, 'zc_bot_code' );
		delete_user_meta( $uid, 'zc_bot_code_exp' );
		return $uid;
	}

	$users = get_users(
		array(
			'meta_key'   => 'zc_bot_code', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value' => $code,         // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'number'     => 1,
			'fields'     => 'ID',
		)
	);
	if ( ! $users ) {
		return 0;
	}
	$uid = (int) $users[0];
	$exp = (int) get_user_meta( $uid, 'zc_bot_code_exp', true );
	if ( $exp && $exp < time() ) {
		delete_user_meta( $uid, 'zc_bot_code' );
		delete_user_meta( $uid, 'zc_bot_code_exp' );
		return 0;
	}
	delete_user_meta( $uid, 'zc_bot_code' );
	delete_user_meta( $uid, 'zc_bot_code_exp' );
	return $uid;
}

/**
 * آیا کاربر به پیام‌رسان متصل است؟
 *
 * @param string $messenger پیام‌رسان.
 * @param int    $user_id   کاربر.
 * @return string chat_id یا رشته خالی.
 */
function zc_user_chat_id( $messenger, $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	return (string) get_user_meta( $user_id, 'zc_' . $messenger . '_chat_id', true );
}

/**
 * بررسی اینکه کاربر مشترک یک نوع اعلان هست یا نه.
 *
 * @param int    $user_id کاربر.
 * @param string $type    نوع اعلان.
 * @return bool
 */
function zc_user_wants_notification( $user_id, $type ) {
	$prefs = get_user_meta( $user_id, 'zc_notify_prefs', true );

	// پیش‌فرض: همه‌ی اعلان‌ها روشن است.
	if ( ! is_array( $prefs ) ) {
		return true;
	}

	return ! empty( $prefs[ $type ] );
}

/**
 * ارسال اعلان به یک کاربر در همه‌ی پیام‌رسان‌های متصل.
 *
 * @param int    $user_id شناسه کاربر.
 * @param string $type    نوع اعلان.
 * @param string $text    متن پیام.
 * @param array  $buttons دکمه‌ها.
 * @return int تعداد پیام‌های ارسال‌شده.
 */
function zc_notify_user( $user_id, $type, $text, $buttons = array() ) {
	$user_id = (int) $user_id;

	if ( ! $user_id || ! zc_user_wants_notification( $user_id, $type ) ) {
		return 0;
	}

	$sent = 0;

	foreach ( array_keys( zc_messengers() ) as $messenger ) {
		$chat_id = zc_user_chat_id( $messenger, $user_id );

		if ( ! $chat_id ) {
			continue;
		}

		if ( zc_messenger_send_to( $messenger, $chat_id, $text, $buttons ) ) {
			$sent++;
		}
	}

	return $sent;
}

/**
 * ارسال پیام به مدیران سایت (برای رویدادهای مهم).
 *
 * @param string $text متن.
 * @return void
 */
function zc_messenger_notify_admin( $text ) {
	// اکنون از سامانه‌ی چندگیرنده استفاده می‌شود.
	zc_notify_admins( $text );
}

/* ==========================================================================
   وب‌هوک: دریافت پیام‌های ربات
   ========================================================================== */

/**
 * ثبت مسیر REST برای وب‌هوک ربات‌ها.
 *
 * آدرس وب‌هوک: /wp-json/zarincode/v1/bot/{telegram|bale}?secret=XXX
 *
 * @return void
 */
function zc_register_bot_webhook() {
	register_rest_route(
		'zarincode/v1',
		'/bot/(?P<messenger>telegram|bale)',
		array(
			'methods'             => 'POST',
			'callback'            => 'zc_handle_bot_webhook',
			'permission_callback' => '__return_true',
			'args'                => array(
				'messenger' => array(
					'validate_callback' => static function ( $v ) {
						return in_array( $v, array( 'telegram', 'bale' ), true );
					},
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'zc_register_bot_webhook' );

/**
 * پردازش پیام دریافتی از ربات.
 *
 * @param WP_REST_Request $request درخواست.
 * @return WP_REST_Response
 */
function zc_handle_bot_webhook( $request ) {
	// بررسی کلید امنیتی تا فقط پیام‌رسان بتواند این مسیر را صدا بزند.
	$options  = get_option( ZC_PREFIX, array() );
	$secret   = (string) ( $options['zc_bot_secret'] ?? '' );
	$provided = (string) $request->get_header( 'x-telegram-bot-api-secret-token' );
	if ( ! $provided ) {
		$provided = (string) $request->get_header( 'x-zarincode-bot-secret' );
	}
	if ( ! $provided ) {
		$provided = (string) $request->get_param( 'secret' );
	}

	// مسیر بدون secret هرگز باز نمی‌ماند؛ حتی پیش از اولین بازدید مدیر.
	if ( ! $secret || ! $provided || ! hash_equals( $secret, $provided ) ) {
		return new WP_REST_Response( array( 'ok' => false ), 403 );
	}

	$messenger = $request->get_param( 'messenger' );
	$body      = $request->get_json_params();
	$message   = $body['message'] ?? ( $body['edited_message'] ?? array() );

	$chat_id = $message['chat']['id'] ?? '';
	$text    = trim( (string) ( $message['text'] ?? '' ) );
	$name    = $message['chat']['first_name'] ?? '';

	if ( ! $chat_id ) {
		return new WP_REST_Response( array( 'ok' => true ) );
	}

	// دستور /start CODE — اتصال حساب.
	if ( preg_match( '/^\/start\s+([A-Za-z0-9]{4,20})$/', $text, $m ) ) {
		$user_id = zc_user_by_connect_code( $m[1] );

		if ( $user_id ) {
			update_user_meta( $user_id, 'zc_' . $messenger . '_chat_id', $chat_id );
			update_user_meta( $user_id, 'zc_' . $messenger . '_connected_at', time() );

			$user = get_userdata( $user_id );

			zc_messenger_send_to(
				$messenger,
				$chat_id,
				sprintf(
					/* translators: %s: نام کاربر */
					__( "✅ <b>اتصال با موفقیت انجام شد!</b>\n\nسلام %s عزیز،\nاز این پس اعلان‌های زرین کد را همین‌جا دریافت می‌کنید.\n\nبرای مدیریت اعلان‌ها به پنل کاربری خود مراجعه کنید.", 'zarincode' ),
					esc_html( $user ? $user->display_name : $name )
				),
				array(
					array(
						'text' => __( 'ورود به پنل کاربری', 'zarincode' ),
						'url'  => zc_panel_url(),
					),
				)
			);

			do_action( 'zc_bot_connected', $user_id, $messenger, $chat_id );
		} else {
			zc_messenger_send_to(
				$messenger,
				$chat_id,
				__( "❌ کد وارد شده معتبر نیست.\n\nلطفاً از پنل کاربری سایت، کد اتصال تازه دریافت کنید.", 'zarincode' )
			);
		}

		return new WP_REST_Response( array( 'ok' => true ) );
	}

	// دستور /start بدون کد.
	if ( '/start' === $text ) {
		zc_messenger_send_to(
			$messenger,
			$chat_id,
			sprintf(
				/* translators: %s: نام سایت */
				__( "👋 به ربات <b>%s</b> خوش آمدید!\n\nبرای دریافت اعلان‌های شخصی، ابتدا وارد پنل کاربری سایت شوید و کد اتصال خود را دریافت کنید، سپس آن را به این صورت ارسال کنید:\n\n<code>/start کد‌شما</code>", 'zarincode' ),
				esc_html( get_bloginfo( 'name' ) )
			),
			array(
				array(
					'text' => __( 'دریافت کد اتصال', 'zarincode' ),
					'url'  => zc_panel_url( 'notifications' ),
				),
			)
		);

		return new WP_REST_Response( array( 'ok' => true ) );
	}

	// دستور /stop — قطع اتصال.
	if ( '/stop' === $text ) {
		$users = get_users(
			array(
				'meta_key'   => 'zc_' . $messenger . '_chat_id', // phpcs:ignore
				'meta_value' => $chat_id,                        // phpcs:ignore
				'number'     => 1,
				'fields'     => 'ID',
			)
		);

		if ( $users ) {
			delete_user_meta( (int) $users[0], 'zc_' . $messenger . '_chat_id' );
		}

		zc_messenger_send_to( $messenger, $chat_id, __( '🔕 اتصال شما قطع شد. دیگر اعلانی دریافت نخواهید کرد.', 'zarincode' ) );

		return new WP_REST_Response( array( 'ok' => true ) );
	}

	// دستور /help.
	if ( in_array( $text, array( '/help', 'راهنما' ), true ) ) {
		zc_messenger_send_to(
			$messenger,
			$chat_id,
			__( "📖 <b>راهنمای ربات</b>\n\n/start کد — اتصال حساب کاربری\n/stop — قطع اعلان‌ها\n/help — نمایش این راهنما\n\nبرای پشتیبانی، از سایت تیکت ثبت کنید.", 'zarincode' )
		);
	}

	return new WP_REST_Response( array( 'ok' => true ) );
}

/**
 * آدرس وب‌هوک ربات.
 *
 * @param string $messenger پیام‌رسان.
 * @return string
 */
function zc_bot_webhook_url( $messenger ) {
	return rest_url( 'zarincode/v1/bot/' . $messenger );
}

/**
 * ثبت خودکار وب‌هوک نزد پیام‌رسان (آجاکس پیشخوان).
 *
 * @return void
 */
function zc_ajax_set_webhook() {
	// این درخواست از پیشخوان می‌آید و با نانس مدیریتی اعتبارسنجی می‌شود.
	if ( ! check_ajax_referer( 'zc_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'درخواست نامعتبر است.', 'zarincode' ) ), 403 );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ) );
	}

	$messenger = sanitize_text_field( wp_unslash( $_POST['messenger'] ?? 'telegram' ) );
	$options   = get_option( ZC_PREFIX, array() );
	$secret    = (string) ( $options['zc_bot_secret'] ?? '' );
	$args      = array( 'url' => zc_bot_webhook_url( $messenger ) );
	if ( $secret && 'telegram' === $messenger ) {
		$args['secret_token'] = $secret;
	} elseif ( $secret ) {
		$args['url'] = add_query_arg( 'secret', $secret, $args['url'] );
	}
	$result = zc_messenger_request( $messenger, 'setWebhook', $args );

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	}

	wp_send_json_success(
		array(
			'message' => __( 'وب‌هوک با موفقیت ثبت شد.', 'zarincode' ),
			'url'     => zc_bot_webhook_url( $messenger ),
		)
	);
}
add_action( 'wp_ajax_zc_set_webhook', 'zc_ajax_set_webhook' );

/**
 * ذخیره‌ی تنظیمات اعلان کاربر (آجاکس).
 *
 * @return void
 */
function zc_ajax_save_notify_prefs() {
	zc_check_ajax();

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'ابتدا وارد حساب خود شوید.', 'zarincode' ) ) );
	}

	$user_id = get_current_user_id();
	$types   = zc_notification_types();
	$raw     = isset( $_POST['prefs'] ) ? (array) wp_unslash( $_POST['prefs'] ) : array(); // phpcs:ignore
	$prefs   = array();

	// کاربر عادی نباید بتواند اعلان مدیریتی را برای خود روشن کند.
	if ( ! current_user_can( 'manage_options' ) ) {
		unset( $types['admin_alerts'] );
	}

	foreach ( array_keys( $types ) as $type ) {
		$prefs[ $type ] = ! empty( $raw[ $type ] ) ? 1 : 0;
	}

	update_user_meta( $user_id, 'zc_notify_prefs', $prefs );

	wp_send_json_success( array( 'message' => __( 'تنظیمات اعلان‌ها ذخیره شد.', 'zarincode' ) ) );
}
add_action( 'wp_ajax_zc_save_notify_prefs', 'zc_ajax_save_notify_prefs' );

/**
 * قطع اتصال یک پیام‌رسان از پنل کاربری (آجاکس).
 *
 * @return void
 */
function zc_ajax_disconnect_bot() {
	zc_check_ajax();

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'ابتدا وارد حساب خود شوید.', 'zarincode' ) ) );
	}

	$messenger = sanitize_text_field( wp_unslash( $_POST['messenger'] ?? '' ) );

	if ( ! array_key_exists( $messenger, zc_messengers() ) ) {
		wp_send_json_error( array( 'message' => __( 'پیام‌رسان نامعتبر است.', 'zarincode' ) ) );
	}

	delete_user_meta( get_current_user_id(), 'zc_' . $messenger . '_chat_id' );

	wp_send_json_success(
		array(
			'message' => __( 'اتصال قطع شد.', 'zarincode' ),
			'reload'  => true,
		)
	);
}
add_action( 'wp_ajax_zc_disconnect_bot', 'zc_ajax_disconnect_bot' );

/**
 * تولید کد اتصال تازه (آجاکس).
 *
 * @return void
 */
function zc_ajax_refresh_bot_code() {
	zc_check_ajax();

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'ابتدا وارد حساب خود شوید.', 'zarincode' ) ) );
	}

	delete_user_meta( get_current_user_id(), 'zc_bot_code' );

	wp_send_json_success(
		array(
			'message' => __( 'کد تازه ساخته شد.', 'zarincode' ),
			'code'    => zc_get_connect_code(),
		)
	);
}
add_action( 'wp_ajax_zc_refresh_bot_code', 'zc_ajax_refresh_bot_code' );

/* ==========================================================================
   اتصال اعلان‌ها به رویدادهای سایت
   ========================================================================== */

/**
 * اعلان پاسخ تیکت به کاربر.
 *
 * @param int $ticket_id شناسه تیکت.
 * @param int $user_id   پاسخ‌دهنده.
 * @return void
 */
function zc_bot_notify_ticket_reply( $ticket_id, $user_id = 0 ) {
	$owner = (int) get_post_field( 'post_author', $ticket_id );

	// اگر خود کاربر پاسخ داده، اعلانی لازم نیست.
	if ( ! $owner || $owner === (int) $user_id ) {
		return;
	}

	zc_notify_user(
		$owner,
		'ticket_reply',
		sprintf(
			/* translators: %s: عنوان تیکت */
			__( "💬 <b>پاسخ تازه به تیکت شما</b>\n\n📌 %s\n\nبرای مشاهده‌ی پاسخ روی دکمه‌ی زیر بزنید.", 'zarincode' ),
			esc_html( get_the_title( $ticket_id ) )
		),
		array(
			array(
				'text' => __( 'مشاهده تیکت', 'zarincode' ),
				'url'  => add_query_arg( 'ticket', $ticket_id, zc_panel_url( 'tickets' ) ),
			),
		)
	);
}
add_action( 'zc_ticket_replied', 'zc_bot_notify_ticket_reply', 10, 2 );

/**
 * اعلان انتشار محتوای تازه به همه‌ی مشترکان.
 *
 * برای جلوگیری از کندی صفحه، ارسال واقعی توسط کران انجام می‌شود و
 * اینجا فقط در صف قرار می‌گیرد.
 *
 * @param string  $new_status وضعیت تازه.
 * @param string  $old_status وضعیت قبلی.
 * @param WP_Post $post       نوشته.
 * @return void
 */
function zc_queue_content_notification( $new_status, $old_status, $post ) {
	if ( 'publish' !== $new_status || 'publish' === $old_status ) {
		return;
	}

	$map = array(
		'zc_course'   => 'new_course',
		'product'     => 'new_product',
		'post'        => 'new_post',
		'zc_tutorial' => 'new_post',
	);

	if ( empty( $map[ $post->post_type ] ) ) {
		return;
	}

	$queue = get_option( 'zc_notify_queue', array() );

	$queue[] = array(
		'post_id' => $post->ID,
		'type'    => $map[ $post->post_type ],
		'time'    => time(),
	);

	// صف حداکثر ۵۰ مورد نگه می‌دارد.
	update_option( 'zc_notify_queue', array_slice( $queue, -50 ), false );

	// در حضور WooCommerce Action Scheduler صف بدون انتظار برای ترافیک پردازش می‌شود.
	if ( function_exists( 'as_enqueue_async_action' ) ) {
		as_enqueue_async_action( 'zc_notify_cron', array(), 'zarincode', true );
	}
}
add_action( 'transition_post_status', 'zc_queue_content_notification', 20, 3 );

/**
 * پردازش صف اعلان‌ها — توسط کران اجرا می‌شود.
 *
 * در هر اجرا حداکثر یک آیتم از صف و ۲۵ کاربر پردازش می‌شود تا
 * محدودیت نرخ ارسال پیام‌رسان‌ها رعایت شود.
 *
 * @return array گزارش اجرا.
 */
function zc_process_notification_queue() {
	global $wpdb;
	$lock_name = 'zc_notification_queue';
	if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 1)', $lock_name ) ) ) { return array( 'sent' => 0, 'remaining' => -1, 'locked' => true ); } // phpcs:ignore
	try {
		$queue = get_option( 'zc_notify_queue', array() );

	if ( empty( $queue ) ) {
		return array( 'sent' => 0, 'remaining' => 0 );
	}

	$item    = $queue[0];
	$post    = get_post( $item['post_id'] );
	$offset  = (int) get_option( 'zc_notify_offset', 0 );
	$batch   = (int) apply_filters( 'zc_notify_batch_size', 25 );

	if ( ! $post ) {
		array_shift( $queue );
		update_option( 'zc_notify_queue', $queue, false );
		update_option( 'zc_notify_offset', 0, false );

		return array( 'sent' => 0, 'remaining' => count( $queue ) );
	}

	// کاربرانی که حداقل به یک پیام‌رسان متصل‌اند.
	$users = get_users(
		array(
			'number'     => $batch,
			'offset'     => $offset,
			'fields'     => 'ID',
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'OR',
				array( 'key' => 'zc_telegram_chat_id', 'compare' => 'EXISTS' ),
				array( 'key' => 'zc_bale_chat_id', 'compare' => 'EXISTS' ),
			),
		)
	);

	$labels = array(
		'new_course'  => __( '🎓 دوره‌ی جدید منتشر شد', 'zarincode' ),
		'new_product' => __( '🛒 محصول تازه در فروشگاه', 'zarincode' ),
		'new_post'    => __( '📝 مطلب تازه در مجله', 'zarincode' ),
	);

	$text = sprintf(
		"<b>%s</b>\n\n📌 %s\n\n%s",
		$labels[ $item['type'] ] ?? __( 'محتوای تازه', 'zarincode' ),
		esc_html( $post->post_title ),
		esc_html( wp_trim_words( wp_strip_all_tags( $post->post_excerpt ? $post->post_excerpt : $post->post_content ), 28 ) )
	);

	$buttons = array(
		array(
			'text' => __( 'مشاهده', 'zarincode' ),
			'url'  => get_permalink( $post ),
		),
	);

	$sent = 0;

	foreach ( $users as $uid ) {
		$sent += zc_notify_user( $uid, $item['type'], $text, $buttons );
	}

	if ( count( $users ) < $batch ) {
		// این آیتم تمام شد؛ به سراغ بعدی می‌رویم.
		array_shift( $queue );
		update_option( 'zc_notify_queue', $queue, false );
		update_option( 'zc_notify_offset', 0, false );
	} else {
		update_option( 'zc_notify_offset', $offset + $batch, false );
	}

	update_option( 'zc_notify_last_run', time(), false );

		return array( 'sent' => $sent, 'remaining' => count( $queue ) );
	} finally {
		$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) ); // phpcs:ignore
	}
}

/**
 * زمان‌بندی کران داخلی وردپرس (پشتیبان کران واقعی هاست).
 *
 * @return void
 */
function zc_schedule_notify_cron() {
	if ( ! wp_next_scheduled( 'zc_notify_cron' ) ) {
		wp_schedule_event( time() + 300, 'zc_five_minutes', 'zc_notify_cron' );
	}
}
add_action( 'wp', 'zc_schedule_notify_cron' );
add_action( 'zc_notify_cron', 'zc_process_notification_queue' );

/**
 * افزودن بازه‌ی پنج دقیقه‌ای به زمان‌بندهای وردپرس.
 *
 * @param array $schedules بازه‌ها.
 * @return array
 */
function zc_cron_schedules( $schedules ) {
	$schedules['zc_five_minutes'] = array(
		'interval' => 300,
		'display'  => __( 'هر ۵ دقیقه (زرین کد)', 'zarincode' ),
	);

	return $schedules;
}
add_filter( 'cron_schedules', 'zc_cron_schedules' ); // phpcs:ignore WordPress.WP.CronInterval.ChangeDetected

/**
 * نقطه‌ی ورود کران خارجی هاست.
 *
 * فراخوانی: https://example.com/?zc_cron=KEY
 * این روش سریع‌تر و مطمئن‌تر از کران داخلی وردپرس است.
 *
 * @return void
 */
function zc_external_cron_endpoint() {
	if ( empty( $_GET['zc_cron'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$options = get_option( ZC_PREFIX, array() );
	$key     = $options['zc_cron_key'] ?? '';

	if ( ! $key || ! hash_equals( (string) $key, sanitize_text_field( wp_unslash( $_GET['zc_cron'] ) ) ) ) { // phpcs:ignore
		status_header( 403 );
		wp_die( 'Invalid cron key', '', array( 'response' => 403 ) );
	}

	$result = zc_process_notification_queue();

	// یادآوری نوبت‌های مشاوره نیز در همین اجرا بررسی می‌شود.
	if ( function_exists( 'zc_send_booking_reminders' ) ) {
		$result['reminders'] = zc_send_booking_reminders();
	}

	wp_send_json_success( $result );
}
add_action( 'init', 'zc_external_cron_endpoint', 1 );

/* ==========================================================================
   اطلاع‌رسانی به مدیران — پشتیبانی از چند گیرنده
   ========================================================================== */

/**
 * فهرست شناسه‌های چت مدیران برای اطلاع‌رسانی.
 *
 * در تنظیمات می‌توان چند شناسه را با کاما، فاصله یا خط جدید وارد کرد.
 * علاوه بر آن، مدیرانی که حساب خود را به ربات وصل کرده‌اند و اجازه‌ی
 * دریافت اعلان مدیریتی دارند نیز به فهرست اضافه می‌شوند.
 *
 * @param string $messenger پیام‌رسان (telegram|bale).
 * @return array فهرست chat_id ها.
 */
function zc_admin_chat_ids( $messenger = '' ) {
	$raw = (string) zc_opt( 'zc_admin_chat_id', '' );
	$ids = preg_split( '/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY );
	$ids = $ids ? array_map( 'trim', $ids ) : array();

	// مدیرانی که ربات را وصل کرده‌اند.
	if ( $messenger ) {
		$admins = get_users(
			array(
				'role__in'   => array( 'administrator', 'editor' ),
				'fields'     => 'ID',
				'number'     => 20,
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => 'zc_' . $messenger . '_chat_id',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		foreach ( $admins as $uid ) {
			// مدیر می‌تواند اعلان مدیریتی را در پنل خود خاموش کند.
			if ( ! zc_user_wants_notification( $uid, 'admin_alerts' ) ) {
				continue;
			}

			$chat = zc_user_chat_id( $messenger, $uid );

			if ( $chat ) {
				$ids[] = $chat;
			}
		}
	}

	/**
	 * فیلتر فهرست گیرندگان اعلان مدیریتی.
	 *
	 * @param array  $ids       شناسه‌ها.
	 * @param string $messenger پیام‌رسان.
	 */
	return array_values( array_unique( apply_filters( 'zc_admin_chat_ids', $ids, $messenger ) ) );
}

/**
 * ارسال اعلان مدیریتی به همه‌ی گیرندگان در تلگرام و بله.
 *
 * @param string $text    متن پیام (HTML ساده).
 * @param array  $buttons دکمه‌های شیشه‌ای.
 * @return int تعداد پیام‌های ارسال‌شده.
 */
function zc_notify_admins( $text, $buttons = array() ) {
	$sent = 0;

	foreach ( array_keys( zc_messengers() ) as $messenger ) {
		$config = zc_messengers()[ $messenger ];

		if ( empty( $config['token'] ) ) {
			continue;
		}

		foreach ( zc_admin_chat_ids( $messenger ) as $chat_id ) {
			if ( zc_messenger_send_to( $messenger, $chat_id, $text, $buttons ) ) {
				$sent++;
			}
		}
	}

	return $sent;
}
