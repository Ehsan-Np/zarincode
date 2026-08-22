<?php
/**
 * چت آنلاین و پشتیبانی
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * دریافت شناسه نشست چت.
 *
 * @return string
 */
function zc_chat_session_id() {
	if ( is_user_logged_in() ) {
		return 'u' . get_current_user_id();
	}

	if ( isset( $_COOKIE['zc_chat_sid'] ) ) {
		return sanitize_text_field( wp_unslash( $_COOKIE['zc_chat_sid'] ) );
	}

	$sid = wp_generate_password( 20, false );
	setcookie( 'zc_chat_sid', $sid, time() + MONTH_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN );

	return $sid;
}

/**
 * ارسال پیام چت.
 *
 * @return void
 */
function zc_ajax_chat_send() {
	zc_check_ajax();

	if ( ! zc_opt( 'zc_chat_enable', true ) ) {
		wp_send_json_error( array( 'message' => __( 'چت آنلاین غیرفعال است.', 'zarincode' ) ) );
	}

	global $wpdb;

	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	if ( ! $message ) {
		wp_send_json_error( array( 'message' => __( 'پیام خالی است.', 'zarincode' ) ) );
	}

	// محدودیت اسپم.
	$lock = 'zc_chat_' . md5( zc_get_ip() );
	$c    = (int) get_transient( $lock );
	if ( $c > 20 ) {
		wp_send_json_error( array( 'message' => __( 'تعداد پیام‌های شما زیاد است.', 'zarincode' ) ) );
	}
	set_transient( $lock, $c + 1, MINUTE_IN_SECONDS );

	$session = zc_chat_session_id();
	$table   = $wpdb->prefix . 'zc_chats';

	$wpdb->insert( // phpcs:ignore
		$table,
		array(
			'user_id'    => get_current_user_id(),
			'session_id' => $session,
			'sender'     => 'user',
			'message'    => $message,
			'created_at' => current_time( 'mysql' ),
		)
	);

	// پاسخ خودکار.
	$reply = zc_chat_auto_reply( $message );

	$wpdb->insert( // phpcs:ignore
		$table,
		array(
			'user_id'    => get_current_user_id(),
			'session_id' => $session,
			'sender'     => 'bot',
			'message'    => $reply,
			'created_at' => current_time( 'mysql' ),
		)
	);

	/**
	 * پس از ثبت پیام کاربر در گفتگو.
	 *
	 * اطلاع‌رسانی به مدیران از طریق این قلاب انجام می‌شود.
	 *
	 * @param string $session شناسه جلسه.
	 * @param string $message متن پیام.
	 */
	do_action( 'zc_chat_message_sent', $session, $message );

	wp_send_json_success( array( 'reply' => $reply ) );
}
add_action( 'wp_ajax_zc_chat_send', 'zc_ajax_chat_send' );
add_action( 'wp_ajax_nopriv_zc_chat_send', 'zc_ajax_chat_send' );

/**
 * پاسخ خودکار هوشمند بر اساس کلیدواژه.
 *
 * @param string $message پیام.
 * @return string
 */
function zc_chat_auto_reply( $message ) {
	$rules = zc_opt( 'zc_chat_rules', '' );

	if ( $rules ) {
		$lines = array_filter( array_map( 'trim', explode( "\n", $rules ) ) );
		foreach ( $lines as $line ) {
			$parts = explode( '|', $line, 2 );
			if ( count( $parts ) === 2 && false !== mb_stripos( $message, trim( $parts[0] ) ) ) {
				return trim( $parts[1] );
			}
		}
	}

	// قوانین پیش‌فرض.
	$defaults = array(
		'قیمت'    => __( 'برای مشاهده قیمت دوره‌ها به صفحه دوره‌ها مراجعه کنید یا شماره تماس بگذارید تا همکاران ما تماس بگیرند.', 'zarincode' ),
		'تخفیف'   => __( 'کدهای تخفیف فعال در کانال تلگرام ما منتشر می‌شود. همچنین اولین خرید شما ۱۰٪ تخفیف دارد!', 'zarincode' ),
		'پشتیبان' => __( 'تیم پشتیبانی از ساعت ۹ تا ۱۸ پاسخگوی شماست. می‌توانید از بخش تیکت هم استفاده کنید.', 'zarincode' ),
		'گواهی'   => __( 'بله، پس از اتمام هر دوره گواهی پایان دوره با قابلیت استعلام صادر می‌شود.', 'zarincode' ),
		'سلام'    => __( 'سلام! به زرین کد خوش آمدید 👋 چطور می‌تونم کمکتون کنم؟', 'zarincode' ),
	);

	foreach ( $defaults as $keyword => $reply ) {
		if ( false !== mb_stripos( $message, $keyword ) ) {
			return $reply;
		}
	}

	return zc_opt(
		'zc_chat_default_reply',
		__( 'پیام شما دریافت شد. کارشناسان ما در اسرع وقت پاسخ می‌دهند. برای پیگیری سریع‌تر می‌توانید تیکت ثبت کنید.', 'zarincode' )
	);
}

/**
 * دریافت تاریخچه چت.
 *
 * @return void
 */
function zc_ajax_chat_history() {
	zc_check_ajax();

	global $wpdb;

	$session = zc_chat_session_id();
	$table   = $wpdb->prefix . 'zc_chats';

	$rows = $wpdb->get_results( // phpcs:ignore
		$wpdb->prepare( "SELECT sender, message, created_at FROM {$table} WHERE session_id = %s ORDER BY id ASC LIMIT 50", $session )
	);

	wp_send_json_success( array( 'messages' => $rows ) );
}
add_action( 'wp_ajax_zc_chat_history', 'zc_ajax_chat_history' );
add_action( 'wp_ajax_nopriv_zc_chat_history', 'zc_ajax_chat_history' );

/* ==========================================================================
   مدیریت کامل گفتگوی آنلاین
   ========================================================================== */

/**
 * نام جدول گفتگوها.
 *
 * @return string
 */
function zc_chat_table() {
	global $wpdb;

	return $wpdb->prefix . 'zc_chats';
}

/**
 * فهرست جلسه‌های گفتگو برای پیشخوان.
 *
 * هر جلسه شامل آخرین پیام، تعداد پیام‌ها، وضعیت خوانده‌شدن و
 * اطلاعات کاربر است.
 *
 * @param array $args آرگومان‌ها: status, search, limit, offset.
 * @return array
 */
function zc_get_chat_sessions( $args = array() ) {
	global $wpdb;

	$args  = wp_parse_args(
		$args,
		array(
			'status' => 'all',
			'search' => '',
			'limit'  => 30,
			'offset' => 0,
		)
	);
	$table = zc_chat_table();

	if ( ! zc_table_exists( $table ) ) {
		return array();
	}

	$where = '1=1';
	$params = array();

	if ( 'unread' === $args['status'] ) {
		$where .= ' AND is_read = 0 AND sender = %s';
		$params[] = 'user';
	} elseif ( 'closed' === $args['status'] ) {
		$where .= ' AND status = %s';
		$params[] = 'closed';
	} elseif ( 'open' === $args['status'] ) {
		$where .= " AND ( status IS NULL OR status = '' OR status = %s )";
		$params[] = 'open';
	}

	if ( $args['search'] ) {
		$where   .= ' AND message LIKE %s';
		$params[] = '%' . $wpdb->esc_like( $args['search'] ) . '%';
	}

	// شناسه‌ی جلسه‌ها به ترتیب آخرین فعالیت.
	$sql = "SELECT session_id,
					MAX(id) AS last_id,
					MAX(created_at) AS last_time,
					COUNT(*) AS msg_count,
					MAX(user_id) AS user_id,
					SUM( CASE WHEN is_read = 0 AND sender = 'user' THEN 1 ELSE 0 END ) AS unread
			FROM {$table}
			WHERE {$where}
			GROUP BY session_id
			ORDER BY last_time DESC
			LIMIT %d OFFSET %d";

	$params[] = (int) $args['limit'];
	$params[] = (int) $args['offset'];

	$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ) ); // phpcs:ignore

	if ( ! $rows ) {
		return array();
	}

	// افزودن آخرین پیام هر جلسه.
	foreach ( $rows as $row ) {
		$last = $wpdb->get_row( // phpcs:ignore
			$wpdb->prepare( "SELECT message, sender, status FROM {$table} WHERE id = %d", $row->last_id ) // phpcs:ignore
		);

		$row->last_message = $last->message ?? '';
		$row->last_sender  = $last->sender ?? '';
		$row->status       = $last->status ?? 'open';
	}

	return $rows;
}

/**
 * دریافت پیام‌های یک جلسه‌ی گفتگو.
 *
 * @param string $session_id شناسه جلسه.
 * @return array
 */
function zc_get_chat_messages( $session_id ) {
	global $wpdb;

	$table = zc_chat_table();

	if ( ! zc_table_exists( $table ) ) {
		return array();
	}

	return $wpdb->get_results( // phpcs:ignore
		$wpdb->prepare( "SELECT * FROM {$table} WHERE session_id = %s ORDER BY id ASC", $session_id ) // phpcs:ignore
	);
}

/**
 * بررسی وجود یک جدول در دیتابیس.
 *
 * @param string $table نام کامل جدول.
 * @return bool
 */
function zc_table_exists( $table ) {
	global $wpdb;

	static $cache = array();

	if ( isset( $cache[ $table ] ) ) {
		return $cache[ $table ];
	}

	$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ); // phpcs:ignore

	$cache[ $table ] = ( $found === $table );

	return $cache[ $table ];
}

/**
 * علامت‌گذاری پیام‌های یک جلسه به عنوان خوانده‌شده.
 *
 * @param string $session_id شناسه جلسه.
 * @return void
 */
function zc_mark_chat_read( $session_id ) {
	global $wpdb;

	if ( ! zc_table_exists( zc_chat_table() ) ) {
		return;
	}

	$wpdb->update( // phpcs:ignore
		zc_chat_table(),
		array( 'is_read' => 1 ),
		array( 'session_id' => $session_id, 'sender' => 'user' )
	);
}

/**
 * شمار گفتگوهای خوانده‌نشده (برای نشان روی منوی پیشخوان).
 *
 * @return int
 */
function zc_unread_chat_count() {
	global $wpdb;

	if ( ! zc_table_exists( zc_chat_table() ) ) {
		return 0;
	}

	$table = zc_chat_table();

	return (int) $wpdb->get_var( // phpcs:ignore
		"SELECT COUNT(DISTINCT session_id) FROM {$table} WHERE is_read = 0 AND sender = 'user'" // phpcs:ignore
	);
}

/**
 * پاسخ مدیر به یک گفتگو (آجاکس پیشخوان).
 *
 * @return void
 */
function zc_ajax_chat_admin_reply() {
	if ( ! check_ajax_referer( 'zc_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'درخواست نامعتبر است.', 'zarincode' ) ), 403 );
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ), 403 );
	}

	global $wpdb;

	$session = sanitize_text_field( wp_unslash( $_POST['session_id'] ?? '' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

	if ( ! $session || ! $message ) {
		wp_send_json_error( array( 'message' => __( 'پیام یا جلسه نامعتبر است.', 'zarincode' ) ) );
	}

	// شناسه‌ی کاربر صاحب جلسه (برای اعلان).
	$user_id = (int) $wpdb->get_var( // phpcs:ignore
		$wpdb->prepare( 'SELECT user_id FROM ' . zc_chat_table() . ' WHERE session_id = %s ORDER BY id DESC LIMIT 1', $session ) // phpcs:ignore
	);

	$wpdb->insert( // phpcs:ignore
		zc_chat_table(),
		array(
			'user_id'    => $user_id,
			'session_id' => $session,
			'sender'     => 'admin',
			'message'    => $message,
			'is_read'    => 1,
			'created_at' => current_time( 'mysql' ),
		)
	);

	zc_mark_chat_read( $session );

	// اعلان به کاربر در صورت اتصال به ربات.
	if ( $user_id && function_exists( 'zc_notify_user' ) ) {
		zc_notify_user(
			$user_id,
			'ticket_reply',
			sprintf(
				/* translators: %s: متن پاسخ */
				__( "💬 <b>پاسخ پشتیبانی</b>\n\n%s", 'zarincode' ),
				esc_html( wp_trim_words( $message, 40 ) )
			)
		);
	}

	do_action( 'zc_chat_admin_replied', $session, $message, $user_id );

	wp_send_json_success(
		array(
			'message' => __( 'پاسخ ارسال شد.', 'zarincode' ),
			'time'    => zc_fa_num( date_i18n( 'H:i' ) ),
		)
	);
}
add_action( 'wp_ajax_zc_chat_admin_reply', 'zc_ajax_chat_admin_reply' );

/**
 * بستن یا بازکردن یک گفتگو (آجاکس پیشخوان).
 *
 * @return void
 */
function zc_ajax_chat_set_status() {
	if ( ! check_ajax_referer( 'zc_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'درخواست نامعتبر است.', 'zarincode' ) ), 403 );
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ), 403 );
	}

	global $wpdb;

	$session = sanitize_text_field( wp_unslash( $_POST['session_id'] ?? '' ) );
	$status  = sanitize_text_field( wp_unslash( $_POST['status'] ?? 'closed' ) );
	$status  = in_array( $status, array( 'open', 'closed' ), true ) ? $status : 'closed';

	if ( ! $session ) {
		wp_send_json_error( array( 'message' => __( 'جلسه نامعتبر است.', 'zarincode' ) ) );
	}

	$wpdb->update( zc_chat_table(), array( 'status' => $status ), array( 'session_id' => $session ) ); // phpcs:ignore

	wp_send_json_success( array( 'message' => __( 'وضعیت گفتگو بروزرسانی شد.', 'zarincode' ) ) );
}
add_action( 'wp_ajax_zc_chat_set_status', 'zc_ajax_chat_set_status' );

/**
 * حذف کامل یک جلسه‌ی گفتگو (آجاکس پیشخوان).
 *
 * @return void
 */
function zc_ajax_chat_delete() {
	if ( ! check_ajax_referer( 'zc_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'درخواست نامعتبر است.', 'zarincode' ) ), 403 );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ), 403 );
	}

	global $wpdb;

	$session = sanitize_text_field( wp_unslash( $_POST['session_id'] ?? '' ) );

	if ( ! $session ) {
		wp_send_json_error( array( 'message' => __( 'جلسه نامعتبر است.', 'zarincode' ) ) );
	}

	$wpdb->delete( zc_chat_table(), array( 'session_id' => $session ) ); // phpcs:ignore

	wp_send_json_success( array( 'message' => __( 'گفتگو حذف شد.', 'zarincode' ), 'reload' => true ) );
}
add_action( 'wp_ajax_zc_chat_delete', 'zc_ajax_chat_delete' );

/**
 * دریافت پیام‌های تازه‌ی یک جلسه برای پیشخوان (نظرسنجی زنده).
 *
 * @return void
 */
function zc_ajax_chat_admin_fetch() {
	if ( ! check_ajax_referer( 'zc_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( array(), 403 );
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array(), 403 );
	}

	$session = sanitize_text_field( wp_unslash( $_POST['session_id'] ?? '' ) );

	if ( ! $session ) {
		wp_send_json_error( array() );
	}

	$messages = zc_get_chat_messages( $session );
	zc_mark_chat_read( $session );

	$out = array();

	foreach ( $messages as $m ) {
		$out[] = array(
			'sender'  => $m->sender,
			'message' => wp_kses_post( nl2br( $m->message ) ),
			'time'    => zc_fa_num( mysql2date( 'H:i', $m->created_at ) ),
			'date'    => zc_fa_num( mysql2date( 'j F', $m->created_at ) ),
		);
	}

	wp_send_json_success( array( 'messages' => $out, 'unread' => zc_unread_chat_count() ) );
}
add_action( 'wp_ajax_zc_chat_admin_fetch', 'zc_ajax_chat_admin_fetch' );

/**
 * اطلاع‌رسانی شروع گفتگوی تازه به مدیران.
 *
 * فقط برای اولین پیام هر جلسه ارسال می‌شود تا مدیران با هر پیام
 * بمباران نشوند.
 *
 * @param string $session شناسه جلسه.
 * @param string $message متن پیام.
 * @return void
 */
function zc_notify_admins_new_chat( $session, $message ) {
	if ( ! zc_opt( 'zc_chat_notify_admin', true ) ) {
		return;
	}

	if ( ! function_exists( 'zc_notify_admins' ) ) {
		return;
	}

	global $wpdb;

	// شمارش پیام‌های کاربر در این جلسه.
	$count = (int) $wpdb->get_var( // phpcs:ignore
		$wpdb->prepare(
			'SELECT COUNT(*) FROM ' . zc_chat_table() . ' WHERE session_id = %s AND sender = %s', // phpcs:ignore
			$session,
			'user'
		)
	);

	// فقط پیام نخست اعلان می‌فرستد.
	if ( $count > 1 ) {
		return;
	}

	$user = is_user_logged_in() ? wp_get_current_user()->display_name : __( 'کاربر مهمان', 'zarincode' );

	zc_notify_admins(
		sprintf(
			/* translators: 1: نام کاربر 2: متن پیام */
			__( "💬 <b>گفتگوی آنلاین تازه</b>\n\n👤 %1\$s\n\n«%2\$s»\n\nبرای پاسخ به پیشخوان مراجعه کنید.", 'zarincode' ),
			esc_html( $user ),
			esc_html( wp_trim_words( $message, 35 ) )
		),
		array(
			array(
				'text' => __( 'پاسخ در پیشخوان', 'zarincode' ),
				'url'  => admin_url( 'admin.php?page=zarincode-chats&session=' . rawurlencode( $session ) ),
			),
		)
	);
}
add_action( 'zc_chat_message_sent', 'zc_notify_admins_new_chat', 10, 2 );
