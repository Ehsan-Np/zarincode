<?php
/**
 * REST API گسترده پلتفرم (دوره، پیشرفت، کیف پول، تیکت، وب‌هوک خروجی).
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * ثبت مسیرها.
 *
 * @return void
 */
function zc_platform_rest_routes() {
	$ns = 'zarincode/v1';

	register_rest_route(
		$ns,
		'/courses',
		array(
			'methods'             => 'GET',
			'callback'            => 'zc_rest_courses',
			'permission_callback' => '__return_true',
		)
	);
	register_rest_route(
		$ns,
		'/progress',
		array(
			array(
				'methods'             => 'GET',
				'callback'            => 'zc_rest_progress_get',
				'permission_callback' => static function () {
					return is_user_logged_in();
				},
			),
			array(
				'methods'             => 'POST',
				'callback'            => 'zc_rest_progress_post',
				'permission_callback' => static function () {
					return is_user_logged_in();
				},
			),
		)
	);
	register_rest_route(
		$ns,
		'/wallet',
		array(
			'methods'             => 'GET',
			'callback'            => 'zc_rest_wallet',
			'permission_callback' => static function () {
				return is_user_logged_in();
			},
		)
	);
	register_rest_route(
		$ns,
		'/tickets',
		array(
			'methods'             => 'GET',
			'callback'            => 'zc_rest_tickets',
			'permission_callback' => static function () {
				return is_user_logged_in();
			},
		)
	);
	register_rest_route(
		$ns,
		'/webhooks',
		array(
			'methods'             => 'POST',
			'callback'            => 'zc_rest_register_webhook',
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		)
	);
}
add_action( 'rest_api_init', 'zc_platform_rest_routes' );

/**
 * فهرست دوره‌های منتشرشده.
 *
 * @param WP_REST_Request $request درخواست.
 * @return WP_REST_Response
 */
function zc_rest_courses( $request ) {
	if ( ! zc_rest_allow( 'courses', 60 ) ) {
		return new WP_Error( 'rate_limit', __( 'تعداد درخواست بیش از حد مجاز است.', 'zarincode' ), array( 'status' => 429 ) );
	}
	$page = max( 1, (int) $request->get_param( 'page' ) );
	$q    = new WP_Query(
		array(
			'post_type'      => 'zc_course',
			'post_status'    => 'publish',
			'posts_per_page' => 12,
			'paged'          => $page,
		)
	);
	$items = array();
	foreach ( $q->posts as $post ) {
		$items[] = array(
			'id'       => $post->ID,
			'title'    => get_the_title( $post ),
			'excerpt'  => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'price'    => (float) get_post_meta( $post->ID, '_zc_price', true ),
			'lessons'  => zc_count_lessons( $post->ID ),
			'link'     => get_permalink( $post ),
			'image'    => get_the_post_thumbnail_url( $post, 'medium' ),
		);
	}
	return rest_ensure_response(
		array(
			'items' => $items,
			'total' => (int) $q->found_posts,
			'pages' => (int) $q->max_num_pages,
		)
	);
}

/**
 * پیشرفت کاربر.
 *
 * @return WP_REST_Response
 */
function zc_rest_progress_get() {
	$user = get_current_user_id();
	$out  = array();
	foreach ( zc_get_user_courses( $user ) as $row ) {
		$out[] = array(
			'course_id' => (int) $row->course_id,
			'title'     => get_the_title( $row->course_id ),
			'progress'  => zc_get_course_progress( $user, $row->course_id ),
			'classroom' => zc_classroom_url( $row->course_id ),
		);
	}
	return rest_ensure_response( $out );
}

/**
 * ثبت پیشرفت از کلاینت (با همان قواعد کلاس درس).
 *
 * @param WP_REST_Request $request درخواست.
 * @return WP_REST_Response|WP_Error
 */
function zc_rest_progress_post( $request ) {
	if ( ! zc_rest_allow( 'progress_post_' . get_current_user_id(), 40 ) ) {
		return new WP_Error( 'rate_limit', __( 'تعداد درخواست بیش از حد مجاز است.', 'zarincode' ), array( 'status' => 429 ) );
	}
	$course_id = absint( $request->get_param( 'course_id' ) );
	$lesson    = sanitize_text_field( (string) $request->get_param( 'lesson_key' ) );
	$seconds   = absint( $request->get_param( 'seconds' ) );
	$duration  = absint( $request->get_param( 'duration' ) );
	$complete  = (bool) $request->get_param( 'complete' );
	$user_id   = get_current_user_id();
	if ( ! $course_id || ! $lesson || ! zc_user_has_course( $user_id, $course_id ) ) {
		return new WP_Error( 'forbidden', __( 'دسترسی مجاز نیست.', 'zarincode' ), array( 'status' => 403 ) );
	}
	$threshold = max( 50, min( 100, (int) zc_opt( 'zc_lesson_complete_percent', 80 ) ) );
	$can_done  = false;
	if ( $complete && $duration > 0 ) {
		$can_done = ( ( $seconds / $duration ) * 100 ) >= $threshold;
	} elseif ( $complete && $seconds >= 90 ) {
		$can_done = true;
	}
	return rest_ensure_response( zc_save_lesson_progress( $user_id, $course_id, $lesson, $seconds, $can_done ) );
}

/**
 * موجودی و آخرین تراکنش‌ها.
 *
 * @return WP_REST_Response
 */
function zc_rest_wallet() {
	$user = get_current_user_id();
	$txs  = array();
	foreach ( zc_get_transactions( $user, array( 'limit' => 10 ) ) as $row ) {
		$txs[] = array(
			'id'     => (int) $row->id,
			'amount' => (float) $row->amount,
			'type'   => $row->type,
			'desc'   => $row->description,
			'date'   => $row->created_at,
		);
	}
	return rest_ensure_response(
		array(
			'balance'      => zc_wallet_balance( $user ),
			'transactions' => $txs,
		)
	);
}

/**
 * تیکت‌های کاربر.
 *
 * @return WP_REST_Response
 */
function zc_rest_tickets() {
	$q = zc_get_user_tickets( get_current_user_id(), array( 'posts_per_page' => 20 ) );
	$items = array();
	foreach ( $q->posts as $post ) {
		$items[] = array(
			'id'     => $post->ID,
			'title'  => get_the_title( $post ),
			'status' => get_post_meta( $post->ID, '_zc_status', true ),
			'date'   => $post->post_date,
		);
	}
	return rest_ensure_response( $items );
}

/**
 * ثبت وب‌هوک خروجی (فقط مدیر).
 *
 * @param WP_REST_Request $request درخواست.
 * @return WP_REST_Response|WP_Error
 */
function zc_rest_register_webhook( $request ) {
	$url    = esc_url_raw( (string) $request->get_param( 'url' ) );
	$events = array_map( 'sanitize_key', (array) $request->get_param( 'events' ) );
	$secret = sanitize_text_field( (string) $request->get_param( 'secret' ) );
	if ( ! $url || 'https' !== wp_parse_url( $url, PHP_URL_SCHEME ) ) {
		return new WP_Error( 'invalid_url', __( 'فقط آدرس HTTPS مجاز است.', 'zarincode' ), array( 'status' => 400 ) );
	}
	if ( function_exists( 'zc_url_is_public_https' ) && ! zc_url_is_public_https( $url ) ) {
		return new WP_Error( 'ssrf_blocked', __( 'آدرس وب‌هوک به شبکهٔ داخلی اشاره می‌کند.', 'zarincode' ), array( 'status' => 400 ) );
	}
	if ( ! $secret || strlen( $secret ) < 16 ) {
		return new WP_Error( 'weak_secret', __( 'کلید امضا باید حداقل ۱۶ نویسه باشد.', 'zarincode' ), array( 'status' => 400 ) );
	}
	$hooks   = get_option( 'zc_outgoing_webhooks', array() );
	$hooks[] = array(
		'id'     => wp_generate_password( 12, false, false ),
		'url'    => $url,
		'events' => $events ? $events : array( 'order.paid', 'course.completed' ),
		'secret' => $secret,
	);
	update_option( 'zc_outgoing_webhooks', $hooks, false );
	if ( function_exists( 'zc_audit' ) ) {
		zc_audit( 'webhook_register', 'webhook', 0, array( 'url' => $url ) );
	}
	return rest_ensure_response( array( 'ok' => true, 'count' => count( $hooks ) ) );
}

/**
 * ارسال وب‌هوک خروجی با HMAC.
 *
 * @param string $event رویداد.
 * @param array  $payload داده.
 * @return void
 */
function zc_dispatch_webhooks( $event, $payload ) {
	foreach ( (array) get_option( 'zc_outgoing_webhooks', array() ) as $hook ) {
		if ( empty( $hook['url'] ) || empty( $hook['secret'] ) ) {
			continue;
		}
		if ( ! empty( $hook['events'] ) && ! in_array( $event, (array) $hook['events'], true ) ) {
			continue;
		}
		$body = wp_json_encode(
			array(
				'event'     => $event,
				'payload'   => $payload,
				'timestamp' => time(),
			),
			JSON_UNESCAPED_UNICODE
		);
		$sig = hash_hmac( 'sha256', (string) $body, (string) $hook['secret'] );
		wp_remote_post(
			$hook['url'],
			array(
				'timeout'  => 8,
				'blocking' => false,
				'headers'  => array(
					'Content-Type'      => 'application/json',
					'X-Zarincode-Event' => $event,
					'X-Zarincode-Sign'  => $sig,
				),
				'body'     => $body,
			)
		);
	}
}
add_action(
	'zc_course_completed',
	static function ( $user_id, $course_id ) {
		zc_dispatch_webhooks( 'course.completed', array( 'user_id' => $user_id, 'course_id' => $course_id ) );
	},
	50,
	2
);
add_action(
	'woocommerce_payment_complete',
	static function ( $order_id ) {
		zc_dispatch_webhooks( 'order.paid', array( 'order_id' => $order_id ) );
	},
	50
);
