<?php
/**
 * سیستم رزرو نوبت
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * بازه‌های زمانی قابل رزرو.
 *
 * @return array
 */
function zc_booking_time_slots() {
	$start    = (int) zc_opt( 'zc_booking_start_hour', 9 );
	$end      = (int) zc_opt( 'zc_booking_end_hour', 18 );
	$interval = (int) zc_opt( 'zc_booking_interval', 30 );

	$slots   = array();
	$current = $start * 60;
	$finish  = $end * 60;

	while ( $current < $finish ) {
		$h       = floor( $current / 60 );
		$m       = $current % 60;
		$slots[] = sprintf( '%02d:%02d', $h, $m );
		$current += $interval;
	}

	return $slots;
}

/**
 * بررسی آزاد بودن یک بازه.
 *
 * @param string $date تاریخ.
 * @param string $time ساعت.
 * @return bool
 */
function zc_booking_is_available( $date, $time ) {
	global $wpdb;

	$table = $wpdb->prefix . 'zc_bookings';
	$max   = (int) zc_opt( 'zc_booking_capacity', 1 );

	$count = (int) $wpdb->get_var( // phpcs:ignore
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE date = %s AND time = %s AND status != 'canceled'",
			$date,
			$time
		)
	);

	return $count < $max;
}

/**
 * ثبت رزرو (ای‌جکس).
 *
 * @return void
 */
function zc_ajax_booking_submit() {
	zc_check_ajax();

	if ( ! zc_opt( 'zc_booking_enable', true ) ) {
		wp_send_json_error( array( 'message' => __( 'سیستم رزرو نوبت غیرفعال است.', 'zarincode' ) ) );
	}

	global $wpdb;

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$mobile  = isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : '';
	$date    = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
	$time    = isset( $_POST['time'] ) ? sanitize_text_field( wp_unslash( $_POST['time'] ) ) : '';
	$service = isset( $_POST['service'] ) ? absint( $_POST['service'] ) : 0;
	$note    = isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '';

	$mobile = zc_sanitize_mobile( $mobile );

	if ( ! $name || ! $mobile || ! $date || ! $time ) {
		wp_send_json_error( array( 'message' => __( 'لطفاً تمام فیلدهای ضروری را تکمیل کنید.', 'zarincode' ) ) );
	}

	if ( strtotime( $date ) < strtotime( gmdate( 'Y-m-d' ) ) ) {
		wp_send_json_error( array( 'message' => __( 'تاریخ انتخابی گذشته است.', 'zarincode' ) ) );
	}

	if ( ! zc_booking_is_available( $date, $time ) ) {
		wp_send_json_error( array( 'message' => __( 'این بازه زمانی رزرو شده است. لطفاً زمان دیگری انتخاب کنید.', 'zarincode' ) ) );
	}

	$table  = $wpdb->prefix . 'zc_bookings';
	$result = $wpdb->insert( // phpcs:ignore
		$table,
		array(
			'user_id'    => get_current_user_id(),
			'service_id' => $service,
			'name'       => $name,
			'mobile'     => $mobile,
			'date'       => $date,
			'time'       => $time,
			'note'       => $note,
			'status'     => 'pending',
			'created_at' => current_time( 'mysql' ),
		)
	);

	if ( ! $result ) {
		wp_send_json_error( array( 'message' => __( 'خطا در ثبت رزرو.', 'zarincode' ) ) );
	}

	$booking_id = (int) $wpdb->insert_id;

	// ثبت به عنوان پست برای مدیریت در پیشخوان.
	wp_insert_post(
		array(
			'post_type'    => 'zc_booking',
			'post_title'   => sprintf( '%s - %s %s', $name, $date, $time ),
			'post_content' => $note,
			'post_status'  => 'publish',
			'meta_input'   => array(
				'_zc_booking_id' => $booking_id,
				'_zc_mobile'     => $mobile,
				'_zc_date'       => $date,
				'_zc_time'       => $time,
				'_zc_service'    => $service,
				'_zc_status'     => 'pending',
			),
		)
	);

	// پیامک تایید (متن کاملاً از پنل شخصی‌سازی‌پذیر است).
	if ( zc_opt( 'zc_sms_booking_notify', true ) ) {
		$zc_user = $mobile ? get_user_by( 'login', $mobile ) : false;
		zc_sms_send_message(
			'booking',
			$mobile,
			array(
				'name' => $zc_user ? $zc_user->display_name : '',
				'date' => $date,
				'time' => $time,
			)
		);
	}

	do_action( 'zc_booking_created', $booking_id );

	wp_send_json_success(
		array(
			'message' => __( 'درخواست رزرو شما با موفقیت ثبت شد. به زودی با شما تماس می‌گیریم.', 'zarincode' ),
		)
	);
}
add_action( 'wp_ajax_zc_booking_submit', 'zc_ajax_booking_submit' );
add_action( 'wp_ajax_nopriv_zc_booking_submit', 'zc_ajax_booking_submit' );

/**
 * دریافت ساعت‌های آزاد یک روز (ای‌جکس).
 *
 * @return void
 */
function zc_ajax_booking_slots() {
	zc_check_ajax();

	$date  = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
	$slots = zc_booking_time_slots();
	$out   = array();

	foreach ( $slots as $slot ) {
		$out[] = array(
			'time'      => $slot,
			'label'     => zc_fa_num( $slot ),
			'available' => zc_booking_is_available( $date, $slot ),
		);
	}

	wp_send_json_success( array( 'slots' => $out ) );
}
add_action( 'wp_ajax_zc_booking_slots', 'zc_ajax_booking_slots' );
add_action( 'wp_ajax_nopriv_zc_booking_slots', 'zc_ajax_booking_slots' );

/**
 * دریافت رزروهای کاربر.
 *
 * @param int $user_id کاربر.
 * @return array
 */
function zc_get_user_bookings( $user_id = 0 ) {
	global $wpdb;

	$user_id = $user_id ? $user_id : get_current_user_id();
	$table   = $wpdb->prefix . 'zc_bookings';

	return $wpdb->get_results( // phpcs:ignore
		$wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d ORDER BY date DESC, time DESC LIMIT 50", $user_id )
	);
}

/**
 * لغو رزرو توسط کاربر.
 *
 * @return void
 */
function zc_ajax_cancel_booking() {
	zc_check_ajax();

	global $wpdb;

	$id      = isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0;
	$user_id = get_current_user_id();
	$table   = $wpdb->prefix . 'zc_bookings';

	$booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) ); // phpcs:ignore

	if ( ! $booking || (int) $booking->user_id !== $user_id ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ) );
	}

	$wpdb->update( $table, array( 'status' => 'canceled' ), array( 'id' => $id ) ); // phpcs:ignore

	wp_send_json_success( array( 'message' => __( 'رزرو لغو شد.', 'zarincode' ), 'reload' => true ) );
}
add_action( 'wp_ajax_zc_cancel_booking', 'zc_ajax_cancel_booking' );

/**
 * ارسال یادآوری نوبت‌های فردا به کاربران متصل به ربات.
 *
 * توسط کران (داخلی یا خارجی) فراخوانی می‌شود.
 *
 * @return int تعداد یادآوری‌های ارسال‌شده.
 */
function zc_send_booking_reminders() {
	if ( ! function_exists( 'zc_notify_user' ) ) {
		return 0;
	}

	global $wpdb;

	$table    = $wpdb->prefix . 'zc_bookings';
	$tomorrow = gmdate( 'Y-m-d', strtotime( '+1 day', current_time( 'timestamp' ) ) ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp

	// جدول ممکن است هنوز ساخته نشده باشد.
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) { // phpcs:ignore
		return 0;
	}

	$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE `date` = %s AND status IN ('confirmed','pending') AND reminded = 0 LIMIT 50", // phpcs:ignore
			$tomorrow
		)
	);

	if ( empty( $rows ) ) {
		return 0;
	}

	$sent = 0;

	foreach ( $rows as $row ) {
		if ( empty( $row->user_id ) ) {
			continue;
		}

		$count = zc_notify_user(
			(int) $row->user_id,
			'booking',
			sprintf(
				/* translators: 1: تاریخ 2: ساعت */
				__( "⏰ <b>یادآوری نوبت مشاوره</b>\n\nنوبت شما فردا %1\$s ساعت %2\$s رزرو شده است.\n\nلطفاً چند دقیقه زودتر آماده باشید.", 'zarincode' ),
				esc_html( zc_fa_num( $row->date ) ),
				esc_html( zc_fa_num( $row->time ) )
			)
		);

		// پیامک یادآوری نوبت (متن از پنل شخصی‌سازی‌پذیر).
		$zc_mobile = $row->user_id ? get_user_meta( (int) $row->user_id, 'zc_mobile', true ) : '';
		zc_sms_send_message(
			'booking_remind',
			$zc_mobile,
			array(
				'name' => $row->user_id ? get_userdata( (int) $row->user_id )->display_name : '',
				'date' => $row->date,
				'time' => $row->time,
			)
		);

		if ( $count ) {
			$sent++;
		}

		$wpdb->update( $table, array( 'reminded' => 1 ), array( 'id' => $row->id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}

	return $sent;
}
