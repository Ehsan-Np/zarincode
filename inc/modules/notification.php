<?php
/**
 * سیستم اعلان‌های کاربر
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * افزودن اعلان برای کاربر.
 *
 * @param int    $user_id کاربر.
 * @param string $title   عنوان.
 * @param string $message متن.
 * @param string $type    نوع.
 * @param string $link    لینک.
 * @return void
 */
function zc_add_notification( $user_id, $title, $message = '', $type = 'info', $link = '' ) {
	$list = (array) get_user_meta( $user_id, 'zc_notifications', true );

	array_unshift(
		$list,
		array(
			'id'      => uniqid( 'n' ),
			'title'   => $title,
			'message' => $message,
			'type'    => $type,
			'link'    => $link,
			'read'    => false,
			'date'    => current_time( 'mysql' ),
		)
	);

	// نگهداری حداکثر ۵۰ اعلان.
	update_user_meta( $user_id, 'zc_notifications', array_slice( $list, 0, 50 ) );
}

/**
 * دریافت اعلان‌ها.
 *
 * @param int  $user_id     کاربر.
 * @param bool $unread_only فقط خوانده‌نشده.
 * @return array
 */
function zc_get_notifications( $user_id = 0, $unread_only = false ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	$list    = (array) get_user_meta( $user_id, 'zc_notifications', true );

	if ( $unread_only ) {
		$list = array_filter(
			$list,
			function ( $n ) {
				return empty( $n['read'] );
			}
		);
	}

	return array_values( array_filter( $list ) );
}

/**
 * تعداد اعلان‌های خوانده نشده.
 *
 * @param int $user_id کاربر.
 * @return int
 */
function zc_unread_notifications_count( $user_id = 0 ) {
	return count( zc_get_notifications( $user_id, true ) );
}

/**
 * علامت‌گذاری به عنوان خوانده‌شده.
 *
 * @return void
 */
function zc_ajax_read_notifications() {
	zc_check_ajax();

	if ( ! is_user_logged_in() ) {
		wp_send_json_error();
	}

	$user_id = get_current_user_id();
	$list    = (array) get_user_meta( $user_id, 'zc_notifications', true );

	foreach ( $list as &$item ) {
		$item['read'] = true;
	}

	update_user_meta( $user_id, 'zc_notifications', $list );

	wp_send_json_success( array( 'count' => 0 ) );
}
add_action( 'wp_ajax_zc_read_notifications', 'zc_ajax_read_notifications' );

/**
 * اعلان خودکار پس از ثبت‌نام در دوره.
 *
 * @param int $user_id   کاربر.
 * @param int $course_id دوره.
 * @return void
 */
function zc_notify_enrollment( $user_id, $course_id ) {
	zc_add_notification(
		$user_id,
		__( 'ثبت‌نام موفق در دوره', 'zarincode' ),
		sprintf( /* translators: %s: course */ __( 'شما با موفقیت در دوره «%s» ثبت‌نام شدید. یادگیری را شروع کنید!', 'zarincode' ), get_the_title( $course_id ) ),
		'success',
		get_permalink( $course_id )
	);
}
add_action( 'zc_user_enrolled', 'zc_notify_enrollment', 10, 2 );

/**
 * اعلان پاسخ تیکت.
 *
 * @param int  $ticket_id تیکت.
 * @param int  $comment_id دیدگاه.
 * @param bool $is_staff  پشتیبان.
 * @return void
 */
function zc_notify_ticket_reply( $ticket_id, $comment_id, $is_staff ) {
	if ( ! $is_staff ) {
		return;
	}

	$ticket = get_post( $ticket_id );

	zc_add_notification(
		(int) $ticket->post_author,
		__( 'پاسخ تیکت پشتیبانی', 'zarincode' ),
		sprintf( /* translators: %s: subject */ __( 'تیکت «%s» پاسخ داده شد.', 'zarincode' ), $ticket->post_title ),
		'info',
		add_query_arg( array( 'tab' => 'tickets', 'ticket' => $ticket_id ), zc_panel_url() )
	);
}
add_action( 'zc_ticket_replied', 'zc_notify_ticket_reply', 10, 3 );

/**
 * اعلان شارژ کیف پول.
 *
 * @param int   $user_id کاربر.
 * @param float $amount  مبلغ.
 * @return void
 */
function zc_notify_wallet( $user_id, $amount ) {
	zc_add_notification(
		$user_id,
		__( 'شارژ کیف پول', 'zarincode' ),
		sprintf(
			/* translators: %s: amount */
			__( 'مبلغ %s به کیف پول شما اضافه شد.', 'zarincode' ),
			zc_fa_num( number_format( $amount ) ) . ' ' . zc_opt( 'zc_currency_symbol', 'تومان' )
		),
		'success',
		zc_panel_url( 'wallet' )
	);
}
add_action( 'zc_wallet_deposited', 'zc_notify_wallet', 10, 2 );
