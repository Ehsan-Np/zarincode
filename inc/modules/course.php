<?php
/**
 * سیستم آموزشی (دوره، جلسات، پیشرفت، ثبت‌نام)
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * شمارش جلسات یک دوره.
 *
 * @param int $course_id دوره.
 * @return int
 */
function zc_count_lessons( $course_id ) {
	$sections = get_post_meta( $course_id, '_zc_curriculum', true );
	$count    = 0;

	if ( is_array( $sections ) ) {
		foreach ( $sections as $section ) {
			if ( ! empty( $section['lessons'] ) && is_array( $section['lessons'] ) ) {
				$count += count( $section['lessons'] );
			}
		}
	}

	return $count;
}

/**
 * دریافت سرفصل‌های دوره.
 *
 * @param int $course_id دوره.
 * @return array
 */
function zc_get_curriculum( $course_id ) {
	$sections = get_post_meta( $course_id, '_zc_curriculum', true );
	return is_array( $sections ) ? $sections : array();
}

/**
 * آیا کاربر به دوره دسترسی دارد؟
 *
 * @param int $user_id   کاربر.
 * @param int $course_id دوره.
 * @return bool
 */
function zc_user_has_course( $user_id, $course_id ) {
	global $wpdb;

	if ( ! $user_id ) {
		return false;
	}

	// مدیر و نویسنده دوره همیشه دسترسی دارند.
	if ( user_can( $user_id, 'manage_options' ) ) {
		return true;
	}
	if ( (int) get_post_field( 'post_author', $course_id ) === (int) $user_id ) {
		return true;
	}

	// دوره رایگان.
	$price = (float) get_post_meta( $course_id, '_zc_price', true );
	if ( $price <= 0 ) {
		return true;
	}

	// اشتراک ویژه.
	if ( zc_user_has_subscription( $user_id ) ) {
		return true;
	}

	$table = $wpdb->prefix . 'zc_enrollments';
	$row   = $wpdb->get_var( // phpcs:ignore
		$wpdb->prepare(
			"SELECT id FROM {$table} WHERE user_id = %d AND course_id = %d AND status = 'active' AND (expire_at IS NULL OR expire_at > NOW()) LIMIT 1",
			$user_id,
			$course_id
		)
	);

	return (bool) $row;
}

/**
 * بررسی اشتراک فعال کاربر.
 *
 * @param int $user_id کاربر.
 * @return bool
 */
function zc_user_has_subscription( $user_id ) {
	$expire = get_user_meta( $user_id, 'zc_subscription_expire', true );
	if ( ! $expire ) {
		return false;
	}
	return strtotime( $expire ) > current_time( 'timestamp' );
}

/**
 * ثبت‌نام کاربر در دوره.
 *
 * @param int   $user_id   کاربر.
 * @param int   $course_id دوره.
 * @param int   $order_id  سفارش.
 * @param float $price     قیمت.
 * @return bool
 */
function zc_enroll_user( $user_id, $course_id, $order_id = 0, $price = 0 ) {
	global $wpdb;

	if ( ! $user_id || ! $course_id ) {
		return false;
	}

	if ( zc_user_has_course( $user_id, $course_id ) && $order_id === 0 ) {
		return true;
	}

	$table  = $wpdb->prefix . 'zc_enrollments';
	$access = (int) get_post_meta( $course_id, '_zc_access_days', true );
	$expire = $access > 0 ? gmdate( 'Y-m-d H:i:s', strtotime( "+{$access} days" ) ) : null;

	$wpdb->replace( // phpcs:ignore
		$table,
		array(
			'user_id'    => $user_id,
			'course_id'  => $course_id,
			'order_id'   => $order_id,
			'price'      => $price,
			'status'     => 'active',
			'expire_at'  => $expire,
			'created_at' => current_time( 'mysql' ),
		)
	);

	// افزایش شمارنده دانشجویان.
	$students = (int) get_post_meta( $course_id, '_zc_students', true );
	update_post_meta( $course_id, '_zc_students', $students + 1 );

	do_action( 'zc_user_enrolled', $user_id, $course_id, $order_id );

	// پیامک اطلاع‌رسانی.
	if ( zc_opt( 'zc_sms_enroll_notify', true ) ) {
		zc_notify_user_sms(
			$user_id,
			sprintf(
				/* translators: %s: course title */
				__( 'ثبت‌نام شما در دوره «%s» با موفقیت انجام شد. زرین کد', 'zarincode' ),
				mb_substr( get_the_title( $course_id ), 0, 40 )
			)
		);
	}

	return true;
}

/**
 * دریافت دوره‌های کاربر.
 *
 * @param int $user_id کاربر.
 * @return array
 */
function zc_get_user_courses( $user_id = 0 ) {
	global $wpdb;

	$user_id = $user_id ? $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return array();
	}

	$table = $wpdb->prefix . 'zc_enrollments';

	return $wpdb->get_results( // phpcs:ignore
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE user_id = %d AND status = 'active' ORDER BY created_at DESC",
			$user_id
		)
	);
}

/**
 * محاسبه درصد پیشرفت کاربر در دوره.
 *
 * @param int $user_id   کاربر.
 * @param int $course_id دوره.
 * @return int
 */
function zc_get_course_progress( $user_id, $course_id ) {
	global $wpdb;

	$total = zc_count_lessons( $course_id );
	if ( ! $total ) {
		return 0;
	}

	$table = $wpdb->prefix . 'zc_progress';
	$done  = (int) $wpdb->get_var( // phpcs:ignore
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND course_id = %d AND status = 'completed'",
			$user_id,
			$course_id
		)
	);

	return min( 100, (int) round( ( $done / $total ) * 100 ) );
}

/**
 * علامت‌گذاری جلسه به عنوان دیده‌شده.
 *
 * @return void
 */
function zc_ajax_complete_lesson() {
	zc_check_ajax();

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'ابتدا وارد شوید.', 'zarincode' ) ) );
	}

	global $wpdb;

	$course_id = isset( $_POST['course_id'] ) ? absint( $_POST['course_id'] ) : 0;
	$lesson    = isset( $_POST['lesson_key'] ) ? sanitize_text_field( wp_unslash( $_POST['lesson_key'] ) ) : '';
	$user_id   = get_current_user_id();

	if ( ! $course_id || ! $lesson ) {
		wp_send_json_error( array( 'message' => __( 'اطلاعات ناقص است.', 'zarincode' ) ) );
	}

	if ( ! zc_user_has_course( $user_id, $course_id ) ) {
		wp_send_json_error( array( 'message' => __( 'شما به این دوره دسترسی ندارید.', 'zarincode' ) ) );
	}

	$table = $wpdb->prefix . 'zc_progress';

	$wpdb->replace( // phpcs:ignore
		$table,
		array(
			'user_id'    => $user_id,
			'course_id'  => $course_id,
			'lesson_key' => $lesson,
			'status'     => 'completed',
			'updated_at' => current_time( 'mysql' ),
		)
	);

	$progress = zc_get_course_progress( $user_id, $course_id );

	// صدور گواهی در صورت تکمیل ۱۰۰٪.
	if ( 100 === $progress ) {
		do_action( 'zc_course_completed', $user_id, $course_id );
	}

	wp_send_json_success(
		array(
			'progress' => $progress,
			'message'  => __( 'پیشرفت شما ذخیره شد.', 'zarincode' ),
		)
	);
}
add_action( 'wp_ajax_zc_complete_lesson', 'zc_ajax_complete_lesson' );

/**
 * آیا جلسه توسط کاربر تکمیل شده؟
 *
 * @param int    $user_id   کاربر.
 * @param int    $course_id دوره.
 * @param string $lesson    کلید جلسه.
 * @return bool
 */
function zc_is_lesson_completed( $user_id, $course_id, $lesson ) {
	global $wpdb;

	if ( ! $user_id ) {
		return false;
	}

	$table = $wpdb->prefix . 'zc_progress';

	return (bool) $wpdb->get_var( // phpcs:ignore
		$wpdb->prepare(
			"SELECT id FROM {$table} WHERE user_id = %d AND course_id = %d AND lesson_key = %s AND status = 'completed'",
			$user_id,
			$course_id,
			$lesson
		)
	);
}

/**
 * مجموع مدت زمان دوره.
 *
 * @param int $course_id دوره.
 * @return string
 */
function zc_course_total_duration( $course_id ) {
	$sections = zc_get_curriculum( $course_id );
	$minutes  = 0;

	foreach ( $sections as $section ) {
		if ( empty( $section['lessons'] ) ) {
			continue;
		}
		foreach ( $section['lessons'] as $lesson ) {
			$dur = $lesson['duration'] ?? '';
			if ( preg_match( '/(\d+):(\d+)/', zc_en_num( $dur ), $m ) ) {
				$minutes += ( (int) $m[1] * 60 + (int) $m[2] ) / 60;
			} else {
				$minutes += (int) preg_replace( '/[^0-9]/', '', zc_en_num( $dur ) );
			}
		}
	}

	// جمع دقایق ممکن است اعشاری باشد؛ پیش از محاسبات به عدد صحیح تبدیل می‌شود
	// تا در PHP 8.1+ اخطار «تبدیل ضمنی float به int» رخ ندهد.
	$minutes = (int) round( $minutes );
	$hours   = intdiv( $minutes, 60 );
	$mins    = $minutes % 60;

	if ( $hours > 0 ) {
		return sprintf(
			/* translators: 1: hours 2: minutes */
			__( '%1$s ساعت و %2$s دقیقه', 'zarincode' ),
			zc_fa_num( $hours ),
			zc_fa_num( $mins )
		);
	}

	return sprintf( /* translators: %s: minutes */ __( '%s دقیقه', 'zarincode' ), zc_fa_num( $mins ) );
}

/**
 * آواتار مدرس بر اساس نام.
 *
 * @param string $name نام.
 * @return string
 */
function zc_teacher_avatar( $name ) {
	$teacher = zc_get_post_by_title( $name, 'zc_teacher' );

	if ( $teacher && has_post_thumbnail( $teacher->ID ) ) {
		return get_the_post_thumbnail_url( $teacher->ID, 'zc-avatar' );
	}

	return ZC_ASSETS . 'img/avatar.svg';
}

/**
 * خروجی تگ تصویر آواتار مدرس.
 *
 * به جای get_avatar() استفاده می‌شود تا وابستگی به گراواتار
 * (که در ایران در دسترس نیست و سرعت را کاهش می‌دهد) حذف شود.
 *
 * @param string $name نام مدرس.
 * @param int    $size اندازه بر حسب پیکسل.
 * @return string
 */
function zc_teacher_avatar_img( $name, $size = 26 ) {
	$size = (int) $size;

	return sprintf(
		'<img src="%1$s" alt="%2$s" width="%3$d" height="%3$d" loading="lazy" decoding="async" class="zc-avatar" />',
		esc_url( zc_teacher_avatar( $name ) ),
		esc_attr( $name ),
		$size
	);
}

/**
 * اتصال محصول ووکامرس به دوره پس از خرید.
 *
 * @param int $order_id سفارش.
 * @return void
 */
function zc_enroll_after_purchase( $order_id ) {
	if ( ! function_exists( 'wc_get_order' ) ) {
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$user_id = $order->get_user_id();
	if ( ! $user_id ) {
		return;
	}

	foreach ( $order->get_items() as $item ) {
		$product_id = $item->get_product_id();
		$course_id  = (int) get_post_meta( $product_id, '_zc_linked_course', true );

		if ( $course_id ) {
			zc_enroll_user( $user_id, $course_id, $order_id, (float) $item->get_total() );
		}

		// اشتراک ویژه.
		$sub_days = (int) get_post_meta( $product_id, '_zc_subscription_days', true );
		if ( $sub_days > 0 ) {
			$current = get_user_meta( $user_id, 'zc_subscription_expire', true );
			$base    = ( $current && strtotime( $current ) > time() ) ? strtotime( $current ) : time();
			update_user_meta( $user_id, 'zc_subscription_expire', gmdate( 'Y-m-d H:i:s', strtotime( "+{$sub_days} days", $base ) ) );
		}
	}
}
add_action( 'woocommerce_order_status_completed', 'zc_enroll_after_purchase' );
add_action( 'woocommerce_order_status_processing', 'zc_enroll_after_purchase' );

/**
 * ثبت‌نام رایگان در دوره (ای‌جکس).
 *
 * @return void
 */
function zc_ajax_free_enroll() {
	zc_check_ajax();

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'ابتدا وارد شوید.', 'zarincode' ), 'redirect' => zc_login_url() ) );
	}

	$course_id = isset( $_POST['course_id'] ) ? absint( $_POST['course_id'] ) : 0;
	$price     = (float) get_post_meta( $course_id, '_zc_price', true );

	if ( $price > 0 ) {
		wp_send_json_error( array( 'message' => __( 'این دوره رایگان نیست.', 'zarincode' ) ) );
	}

	zc_enroll_user( get_current_user_id(), $course_id );

	wp_send_json_success(
		array(
			'message' => __( 'ثبت‌نام شما انجام شد!', 'zarincode' ),
			'reload'  => true,
		)
	);
}
add_action( 'wp_ajax_zc_free_enroll', 'zc_ajax_free_enroll' );
