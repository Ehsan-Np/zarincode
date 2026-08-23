<?php
/**
 * گفتگوی اختصاصی قرارداد
 *
 * برگه‌ای جدا از تیکت پشتیبانی که برای هر قرارداد یک اتاق گفتگوی
 * مستقل می‌سازد. پیام‌ها متنی‌اند و می‌توانند تصویر یا فایل پیوست
 * داشته باشند. برخلاف تیکت، این گفتگو پیوسته است و برای هماهنگی
 * جزئیات پروژه به کار می‌رود.
 *
 * جدول اختصاصی {prefix}zc_contract_chat استفاده می‌شود تا با جدول
 * چت آنلاین سایت (zc_chats) قاطی نشود.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * ساخت جدول گفتگوی قرارداد.
 *
 * @return void
 */
function zc_create_contract_chat_table() {
	global $wpdb;

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$charset = $wpdb->get_charset_collate();
	$table   = $wpdb->prefix . 'zc_contract_chat';

	$sql = "CREATE TABLE {$table} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		contract_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		sender VARCHAR(20) NOT NULL DEFAULT 'user',
		message LONGTEXT NOT NULL,
		attachment BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		is_read TINYINT(1) NOT NULL DEFAULT 0,
		created_at DATETIME NULL DEFAULT NULL,
		PRIMARY KEY (id),
		KEY contract_id (contract_id),
		KEY user_id (user_id),
		KEY is_read (is_read)
	) {$charset};";

	dbDelta( $sql );
}

/**
 * نام جدول گفتگو.
 *
 * @return string
 */
function zc_cchat_table() {
	global $wpdb;

	return $wpdb->prefix . 'zc_contract_chat';
}

/**
 * دریافت پیام‌های یک قرارداد.
 *
 * @param int $contract_id شناسه قرارداد.
 * @param int $after       فقط پیام‌های بعد از این شناسه.
 * @return array
 */
function zc_cchat_messages( $contract_id, $after = 0 ) {
	global $wpdb;

	$table = zc_cchat_table();

	// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE contract_id = %d AND id > %d ORDER BY id ASC LIMIT 500",
			(int) $contract_id,
			(int) $after
		)
	);
	// phpcs:enable

	return $rows ? $rows : array();
}

/**
 * شمار پیام‌های خوانده‌نشده‌ی کاربر.
 *
 * @param int $user_id شناسه کاربر.
 * @return int
 */
function zc_cchat_unread( $user_id = 0 ) {
	global $wpdb;

	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	if ( ! $user_id ) {
		return 0;
	}

	$table = zc_cchat_table();

	// پیام‌های ادمین که کاربر هنوز ندیده است.
	// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$count = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND sender = 'admin' AND is_read = 0",
			$user_id
		)
	);
	// phpcs:enable

	return (int) $count;
}

/**
 * علامت‌گذاری پیام‌ها به عنوان خوانده‌شده.
 *
 * @param int    $contract_id شناسه قرارداد.
 * @param string $reader      خواننده (user یا admin).
 * @return void
 */
function zc_cchat_mark_read( $contract_id, $reader = 'user' ) {
	global $wpdb;

	// کاربر پیام‌های ادمین را می‌خواند و برعکس.
	$sender = ( 'user' === $reader ) ? 'admin' : 'user';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->update(
		zc_cchat_table(),
		array( 'is_read' => 1 ),
		array(
			'contract_id' => (int) $contract_id,
			'sender'      => $sender,
		),
		array( '%d' ),
		array( '%d', '%s' )
	);
}

/**
 * افزودن پیام تازه.
 *
 * @param int    $contract_id شناسه قرارداد.
 * @param string $message     متن.
 * @param string $sender      فرستنده.
 * @param int    $attachment  شناسه پیوست.
 * @return int|false
 */
function zc_cchat_add( $contract_id, $message, $sender = 'user', $attachment = 0 ) {
	global $wpdb;

	$contract_id = (int) $contract_id;
	$user_id     = (int) get_post_field( 'post_author', $contract_id );

	$ok = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		zc_cchat_table(),
		array(
			'contract_id' => $contract_id,
			'user_id'     => $user_id,
			'sender'      => ( 'admin' === $sender ) ? 'admin' : 'user',
			'message'     => $message,
			'attachment'  => (int) $attachment,
			'is_read'     => 0,
			'created_at'  => current_time( 'mysql' ),
		),
		array( '%d', '%d', '%s', '%s', '%d', '%d', '%s' )
	);

	return $ok ? (int) $wpdb->insert_id : false;
}

/**
 * آماده‌سازی یک پیام برای خروجی JSON.
 *
 * @param object $row سطر جدول.
 * @return array
 */
function zc_cchat_format( $row ) {
	$att = array();

	if ( ! empty( $row->attachment ) ) {
		$url = wp_get_attachment_url( $row->attachment );

		if ( $url ) {
			$att = array(
				'url'     => $url,
				'name'    => get_the_title( $row->attachment ),
				'isImage' => (bool) wp_attachment_is_image( $row->attachment ),
				'thumb'   => wp_get_attachment_image_url( $row->attachment, 'medium' ),
			);
		}
	}

	$ts = strtotime( $row->created_at );

	return array(
		'id'      => (int) $row->id,
		'sender'  => $row->sender,
		'message' => wpautop( wp_kses( $row->message, zc_editor_allowed_html() ) ),
		'raw'     => $row->message,
		'att'     => $att,
		'time'    => zc_fa_num( zc_jalali_date( 'H:i', $ts ) ),
		'date'    => zc_fa_num( zc_jalali_date( 'j F Y', $ts ) ),
		'ts'      => $ts,
	);
}

/* ==========================================================================
   آجاکس
   ========================================================================== */

/**
 * دریافت پیام‌های گفتگو.
 *
 * @return void
 */
function zc_ajax_cchat_fetch() {
	zc_check_ajax();

	$id    = isset( $_POST['contract'] ) ? absint( $_POST['contract'] ) : 0;
	$after = isset( $_POST['after'] ) ? absint( $_POST['after'] ) : 0;

	if ( ! zc_can_view_contract( $id ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی مجاز نیست.', 'zarincode' ) ) );
	}

	$rows = zc_cchat_messages( $id, $after );
	$out  = array_map( 'zc_cchat_format', $rows );

	zc_cchat_mark_read( $id, current_user_can( 'manage_options' ) ? 'admin' : 'user' );

	wp_send_json_success(
		array(
			'messages' => $out,
			'last'     => $out ? end( $out )['id'] : $after,
		)
	);
}
add_action( 'wp_ajax_zc_cchat_fetch', 'zc_ajax_cchat_fetch' );

/**
 * ارسال پیام تازه.
 *
 * @return void
 */
function zc_ajax_cchat_send() {
	zc_check_ajax();

	$id = isset( $_POST['contract'] ) ? absint( $_POST['contract'] ) : 0;

	if ( ! zc_can_view_contract( $id ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی مجاز نیست.', 'zarincode' ) ) );
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$message = isset( $_POST['message'] ) ? zc_kses_editor( wp_unslash( $_POST['message'] ) ) : '';

	$attachment = 0;

	if ( ! empty( $_FILES['file'] ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$size = (int) ( $_FILES['file']['size'] ?? 0 );

		if ( $size > zc_max_upload_size() ) {
			wp_send_json_error(
				array(
					/* translators: %s: حداکثر حجم */
					'message' => sprintf( __( 'حجم فایل بیش از %s مگابایت است.', 'zarincode' ), zc_max_upload_label() ),
				)
			);
		}

		$attachment = media_handle_upload( 'file', 0 );

		if ( is_wp_error( $attachment ) ) {
			wp_send_json_error( array( 'message' => $attachment->get_error_message() ) );
		}
	}

	if ( '' === trim( wp_strip_all_tags( $message ) ) && ! $attachment ) {
		wp_send_json_error( array( 'message' => __( 'پیام خالی است.', 'zarincode' ) ) );
	}

	$sender = current_user_can( 'manage_options' ) ? 'admin' : 'user';
	$row_id = zc_cchat_add( $id, $message, $sender, $attachment );

	if ( ! $row_id ) {
		wp_send_json_error( array( 'message' => __( 'ثبت پیام ناموفق بود.', 'zarincode' ) ) );
	}

	$contract = zc_contract_data( $id );

	// آگاه‌سازی طرف مقابل.
	if ( 'user' === $sender ) {
		if ( function_exists( 'zc_notify_admins' ) ) {
			zc_notify_admins(
				sprintf(
					/* translators: 1: شماره قرارداد 2: متن */
					__( "پیام تازه در گفتگوی قرارداد %1\$s\n%2\$s", 'zarincode' ),
					$contract['number'] ?? '',
					wp_trim_words( wp_strip_all_tags( $message ), 25 )
				)
			);
		}
	} else {
		zc_notify_user(
			(int) ( $contract['user_id'] ?? 0 ),
			'contract',
			sprintf(
				/* translators: 1: شماره قرارداد 2: متن */
				__( "پاسخ تازه در گفتگوی قرارداد %1\$s\n%2\$s", 'zarincode' ),
				$contract['number'] ?? '',
				wp_trim_words( wp_strip_all_tags( $message ), 25 )
			)
		);
	}

	global $wpdb;
	$table = zc_cchat_table();

	// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $row_id ) );
	// phpcs:enable

	wp_send_json_success(
		array(
			'message' => zc_cchat_format( $row ),
		)
	);
}
add_action( 'wp_ajax_zc_cchat_send', 'zc_ajax_cchat_send' );
