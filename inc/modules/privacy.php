<?php
/**
 * یکپارچگی ابزارهای حریم خصوصی وردپرس با داده‌های زرین کد.
 *
 * @package Zarincode
 */
defined( 'ABSPATH' ) || exit;

/** @param array $exporters صادرکننده‌ها. @return array */
function zc_privacy_exporters( $exporters ) {
	$exporters['zarincode'] = array( 'exporter_friendly_name' => __( 'داده‌های زرین کد', 'zarincode' ), 'callback' => 'zc_privacy_export' );
	return $exporters;
}
add_filter( 'wp_privacy_personal_data_exporters', 'zc_privacy_exporters' );

/** @param string $email ایمیل. @param int $page صفحه. @return array */
function zc_privacy_export( $email, $page = 1 ) {
	$user = get_user_by( 'email', $email );
	if ( ! $user ) { return array( 'data' => array(), 'done' => true ); }
	global $wpdb;
	$uid = (int) $user->ID; $data = array(); $page = max( 1, (int) $page ); $done = true;

	$profile = array(
		array( 'name' => __( 'موبایل', 'zarincode' ), 'value' => get_user_meta( $uid, 'zc_mobile', true ) ),
		array( 'name' => __( 'کد ملی', 'zarincode' ), 'value' => get_user_meta( $uid, 'zc_national_id', true ) ),
		array( 'name' => __( 'موجودی کیف پول', 'zarincode' ), 'value' => zc_wallet_balance( $uid ) ),
		array( 'name' => __( 'تاریخ ثبت‌نام', 'zarincode' ), 'value' => get_user_meta( $uid, 'zc_register_date', true ) ),
	);
	if ( 1 === $page ) { $data[] = array( 'group_id' => 'zarincode-profile', 'group_label' => __( 'پروفایل زرین کد', 'zarincode' ), 'item_id' => 'user-' . $uid, 'data' => $profile ); }

	$tables = array(
		'zc_transactions' => array( 'label' => __( 'تراکنش کیف پول', 'zarincode' ), 'columns' => array( 'amount', 'type', 'category', 'status', 'description', 'ref_id', 'created_at' ) ),
		'zc_enrollments' => array( 'label' => __( 'ثبت‌نام دوره', 'zarincode' ), 'columns' => array( 'course_id', 'order_id', 'price', 'status', 'expire_at', 'created_at' ) ),
		'zc_bookings' => array( 'label' => __( 'رزرو نوبت', 'zarincode' ), 'columns' => array( 'service_id', 'name', 'mobile', 'date', 'time', 'note', 'status' ) ),
		'zc_attempts' => array( 'label' => __( 'تلاش آزمون', 'zarincode' ), 'columns' => array( 'type', 'ref_id', 'score', 'correct', 'total', 'passed', 'created_at' ) ),
		'zc_licenses' => array( 'label' => __( 'لایسنس محصول', 'zarincode' ), 'columns' => array( 'license_key', 'product_id', 'order_id', 'status', 'activations', 'expires_at' ) ),
		'zc_certificates' => array( 'label' => __( 'گواهینامه', 'zarincode' ), 'columns' => array( 'code', 'course_id', 'issued_at', 'revoked' ) ),
	);
	foreach ( $tables as $suffix => $config ) {
		$table = $wpdb->prefix . $suffix;
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) { continue; } // phpcs:ignore
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE user_id=%d ORDER BY id DESC LIMIT 100 OFFSET %d", $uid, ( $page - 1 ) * 100 ), ARRAY_A ); // phpcs:ignore
		if ( count( $rows ) === 100 ) { $done = false; }
		foreach ( $rows as $row ) {
			$fields = array();
			foreach ( $config['columns'] as $column ) { if ( isset( $row[ $column ] ) ) { $fields[] = array( 'name' => $column, 'value' => is_scalar( $row[ $column ] ) ? (string) $row[ $column ] : wp_json_encode( $row[ $column ] ) ); } }
			$data[] = array( 'group_id' => 'zarincode-' . $suffix, 'group_label' => $config['label'], 'item_id' => $suffix . '-' . $row['id'], 'data' => $fields );
		}
	}

	$tickets = ( 1 === $page ) ? get_posts( array( 'post_type' => 'zc_ticket', 'author' => $uid, 'posts_per_page' => 50, 'post_status' => 'any', 'fields' => 'ids' ) ) : array();
	foreach ( $tickets as $tid ) {
		$data[] = array(
			'group_id'    => 'zarincode-tickets',
			'group_label' => __( 'تیکت پشتیبانی', 'zarincode' ),
			'item_id'     => 'ticket-' . $tid,
			'data'        => array(
				array( 'name' => __( 'عنوان', 'zarincode' ), 'value' => get_the_title( $tid ) ),
				array( 'name' => __( 'وضعیت', 'zarincode' ), 'value' => (string) get_post_meta( $tid, '_zc_status', true ) ),
			),
		);
	}

	$chat_table = $wpdb->prefix . 'zc_chats';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $chat_table ) ) === $chat_table ) { // phpcs:ignore
		$chats = $wpdb->get_results( $wpdb->prepare( "SELECT sender, message, created_at FROM {$chat_table} WHERE user_id=%d ORDER BY id DESC LIMIT 100", $uid ), ARRAY_A ); // phpcs:ignore
		foreach ( $chats as $chat ) {
			$data[] = array(
				'group_id'    => 'zarincode-chats',
				'group_label' => __( 'گفتگوی آنلاین', 'zarincode' ),
				'item_id'     => 'chat-' . md5( wp_json_encode( $chat ) ),
				'data'        => array(
					array( 'name' => 'sender', 'value' => (string) $chat['sender'] ),
					array( 'name' => 'message', 'value' => (string) $chat['message'] ),
					array( 'name' => 'date', 'value' => (string) $chat['created_at'] ),
				),
			);
		}
	}

	if ( 1 === $page ) {
		foreach ( zc_get_notifications( $uid ) as $index => $notification ) {
			$data[] = array( 'group_id' => 'zarincode-notifications', 'group_label' => __( 'اعلان‌های زرین کد', 'zarincode' ), 'item_id' => 'notification-' . $index, 'data' => array( array( 'name' => __( 'عنوان', 'zarincode' ), 'value' => $notification['title'] ?? '' ), array( 'name' => __( 'پیام', 'zarincode' ), 'value' => $notification['message'] ?? '' ), array( 'name' => __( 'تاریخ', 'zarincode' ), 'value' => $notification['date'] ?? '' ) ) );
		}
	}
	return array( 'data' => $data, 'done' => $done );
}

/** @param array $erasers پاک‌کننده‌ها. @return array */
function zc_privacy_erasers( $erasers ) {
	$erasers['zarincode'] = array( 'eraser_friendly_name' => __( 'داده‌های غیرالزامی زرین کد', 'zarincode' ), 'callback' => 'zc_privacy_erase' );
	return $erasers;
}
add_filter( 'wp_privacy_personal_data_erasers', 'zc_privacy_erasers' );

/** @param string $email ایمیل. @param int $page صفحه. @return array */
function zc_privacy_erase( $email, $page = 1 ) {
	$user = get_user_by( 'email', $email );
	if ( ! $user ) { return array( 'items_removed' => false, 'items_retained' => false, 'messages' => array(), 'done' => true ); }
	global $wpdb;
	$uid = (int) $user->ID; $removed = false; $retained = false; $messages = array();

	foreach ( array( 'zc_notifications', 'zc_wishlist', 'zc_bot_code', 'zc_bot_code_exp', 'zc_notify_prefs', 'zc_telegram_chat_id', 'zc_bale_chat_id', 'zc_custom_avatar' ) as $key ) {
		if ( metadata_exists( 'user', $uid, $key ) ) { delete_user_meta( $uid, $key ); $removed = true; }
	}

	// داده‌های تحلیلی ناشناس می‌شوند؛ رکورد خام برای آمار aggregate باقی می‌ماند.
	$wpdb->update( $wpdb->prefix . 'zc_events', array( 'user_id' => 0, 'visitor_hash' => null ), array( 'user_id' => $uid ), array( '%d', '%s' ), array( '%d' ) ); // phpcs:ignore

	// رزروها و چت‌ها پس از درخواست حذف، بدون ازبین‌بردن آمار عملیاتی ناشناس می‌شوند.
	$wpdb->update( $wpdb->prefix . 'zc_bookings', array( 'name' => __( 'کاربر حذف‌شده', 'zarincode' ), 'mobile' => '', 'note' => '' ), array( 'user_id' => $uid ), array( '%s', '%s', '%s' ), array( '%d' ) ); // phpcs:ignore
	$wpdb->update( $wpdb->prefix . 'zc_chats', array( 'message' => __( '[پیام حذف‌شده بنا به درخواست کاربر]', 'zarincode' ) ), array( 'user_id' => $uid, 'sender' => 'user' ), array( '%s' ), array( '%d', '%s' ) ); // phpcs:ignore

	$mobile = get_user_meta( $uid, 'zc_mobile', true );
	if ( function_exists( 'zc_newsletter_storage_ready' ) && zc_newsletter_storage_ready() ) {
		$table = $wpdb->prefix . 'zc_newsletter_subscribers';
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE email=%s OR mobile=%s", $email, $mobile ) ); // phpcs:ignore
	}
	delete_user_meta( $uid, 'zc_mobile' ); delete_user_meta( $uid, 'zc_national_id' ); delete_user_meta( $uid, 'zc_address' );
	$removed = true;

	// اسناد مالی، سفارش، قرارداد، لایسنس و مدرک به دلایل حقوقی/حسابداری نگهداری می‌شوند.
	if ( zc_count_transactions( $uid ) || zc_user_contracts( $uid ) ) {
		$retained = true;
		$messages[] = __( 'سوابق مالی و قراردادی طبق الزامات قانونی نگهداری شدند.', 'zarincode' );
	}
	return array( 'items_removed' => $removed, 'items_retained' => $retained, 'messages' => $messages, 'done' => true );
}

/** @return void */
function zc_privacy_policy_content() {
	if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) { return; }
	wp_add_privacy_policy_content( 'Zarincode', wp_kses_post( __( '<p>زرین کد برای ارائه دوره، سفارش، کیف پول، رزرو، پشتیبانی، لایسنس و گواهینامه داده‌های ضروری حساب را پردازش می‌کند. IP خام در تحلیل رشد ذخیره نمی‌شود و فقط یک هش روزانه برای جلوگیری از شمارش تکراری استفاده می‌شود. سوابق مالی و قراردادها ممکن است طبق الزامات قانونی پس از درخواست حذف نگهداری شوند.</p>', 'zarincode' ) ) );
}
add_action( 'admin_init', 'zc_privacy_policy_content' );
