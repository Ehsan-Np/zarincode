<?php
/**
 * کلاس درس تمام‌صفحه، پیشرفت ویدیو و محتوای قطره‌ای.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * ثبت query var.
 *
 * @param array $vars متغیرها.
 * @return array
 */
function zc_classroom_query_vars( $vars ) {
	$vars[] = 'zc_learn';
	$vars[] = 'zc_lesson';
	return $vars;
}
add_filter( 'query_vars', 'zc_classroom_query_vars' );

/**
 * بازنویسی اختیاری /learn/{id}/{lesson}.
 *
 * @return void
 */
function zc_classroom_rewrites() {
	add_rewrite_rule( '^learn/([0-9]+)/([^/]+)/?$', 'index.php?zc_learn=$matches[1]&zc_lesson=$matches[2]', 'top' );
}
add_action( 'init', 'zc_classroom_rewrites', 8 );

/**
 * آدرس کلاس درس.
 *
 * @param int    $course_id دوره.
 * @param string $lesson    کلید جلسه.
 * @return string
 */
function zc_classroom_url( $course_id, $lesson = '' ) {
	$args = array( 'zc_learn' => (int) $course_id );
	if ( $lesson ) {
		$args['lesson'] = $lesson;
	}
	return add_query_arg( $args, home_url( '/' ) );
}

/**
 * تخت‌کردن سرفصل به فهرست جلسات.
 *
 * @param int $course_id دوره.
 * @return array
 */
function zc_flatten_lessons( $course_id ) {
	$out = array();
	foreach ( zc_get_curriculum( $course_id ) as $si => $section ) {
		foreach ( (array) ( $section['lessons'] ?? array() ) as $li => $lesson ) {
			$key         = $si . '-' . $li;
			$lesson      = is_array( $lesson ) ? $lesson : array();
			$lesson['key']     = $key;
			$lesson['section'] = $section['title'] ?? '';
			$out[]             = $lesson;
		}
	}
	return $out;
}

/**
 * یافتن جلسه با کلید.
 *
 * @param int    $course_id دوره.
 * @param string $key       کلید.
 * @return array|null
 */
function zc_find_lesson( $course_id, $key ) {
	foreach ( zc_flatten_lessons( $course_id ) as $lesson ) {
		if ( (string) $lesson['key'] === (string) $key ) {
			return $lesson;
		}
	}
	return null;
}

/**
 * تاریخ ثبت‌نام فعال.
 *
 * @param int $user_id   کاربر.
 * @param int $course_id دوره.
 * @return string
 */
function zc_enrollment_started_at( $user_id, $course_id ) {
	global $wpdb;
	$table = $wpdb->prefix . 'zc_enrollments';
	$date  = $wpdb->get_var( // phpcs:ignore
		$wpdb->prepare(
			"SELECT created_at FROM {$table} WHERE user_id=%d AND course_id=%d AND status='active' LIMIT 1",
			$user_id,
			$course_id
		)
	);
	return $date ? (string) $date : current_time( 'mysql' );
}

/**
 * آیا جلسه با توجه به drip باز است؟
 *
 * @param int    $user_id   کاربر.
 * @param int    $course_id دوره.
 * @param array  $lesson    جلسه.
 * @return bool
 */
function zc_lesson_is_unlocked( $user_id, $course_id, $lesson ) {
	if ( user_can( $user_id, 'manage_options' ) ) {
		return true;
	}
	$days = (int) ( $lesson['drip_days'] ?? $lesson['drip'] ?? 0 );
	if ( $days <= 0 ) {
		return true;
	}
	$start = strtotime( zc_enrollment_started_at( $user_id, $course_id ) );
	return time() >= ( $start + ( $days * DAY_IN_SECONDS ) );
}

/**
 * تبدیل نشانی ویدیو به داده پخش.
 *
 * @param string $url نشانی.
 * @return array{type:string,src:string}
 */
function zc_video_embed_data( $url ) {
	$url = esc_url_raw( $url );
	if ( ! $url || ! preg_match( '#^https://#i', $url ) ) {
		return array( 'type' => 'none', 'src' => '' );
	}
	if ( preg_match( '#(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{6,})#', $url, $m ) ) {
		return array( 'type' => 'iframe', 'src' => 'https://www.youtube.com/embed/' . $m[1] . '?rel=0&enablejsapi=1' );
	}
	if ( preg_match( '#aparat\.com/v/([A-Za-z0-9]+)#', $url, $m ) ) {
		return array( 'type' => 'iframe', 'src' => 'https://www.aparat.com/video/video/embed/videohash/' . $m[1] . '/vt/frame' );
	}
	if ( preg_match( '/\.(mp4|webm|ogg)(\?|$)/i', $url ) ) {
		return array( 'type' => 'file', 'src' => $url );
	}
	return array( 'type' => 'iframe', 'src' => $url );
}

/**
 * ذخیره پیشرفت جلسه (ثانیه و وضعیت).
 *
 * @param int    $user_id   کاربر.
 * @param int    $course_id دوره.
 * @param string $lesson    کلید.
 * @param int    $seconds   ثانیه دیده‌شده.
 * @param bool   $complete  تکمیل؟
 * @return array
 */
function zc_save_lesson_progress( $user_id, $course_id, $lesson, $seconds = 0, $complete = false ) {
	global $wpdb;
	$table = $wpdb->prefix . 'zc_progress';
	$row   = $wpdb->get_row( // phpcs:ignore
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE user_id=%d AND course_id=%d AND lesson_key=%s LIMIT 1",
			$user_id,
			$course_id,
			$lesson
		)
	);

	$seconds = max( 0, (int) $seconds );
	$status  = ( $row && 'completed' === $row->status ) ? 'completed' : 'in_progress';
	if ( $complete ) {
		$status = 'completed';
	}
	if ( $row ) {
		$seconds = max( $seconds, (int) ( $row->seconds ?? 0 ) );
	}

	$wpdb->replace( // phpcs:ignore
		$table,
		array(
			'user_id'    => $user_id,
			'course_id'  => $course_id,
			'lesson_key' => $lesson,
			'status'     => $status,
			'seconds'    => $seconds,
			'updated_at' => current_time( 'mysql' ),
		)
	);

	$progress = zc_get_course_progress( $user_id, $course_id );
	if ( 100 === $progress && 'completed' === $status ) {
		do_action( 'zc_course_completed', $user_id, $course_id );
	}

	return array(
		'progress' => $progress,
		'status'   => $status,
		'seconds'  => $seconds,
	);
}

/**
 * AJAX ذخیره پیشرفت ویدیو.
 *
 * @return void
 */
function zc_ajax_save_watch() {
	zc_check_ajax();
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'ابتدا وارد شوید.', 'zarincode' ) ), 401 );
	}

	$user_id   = get_current_user_id();
	$course_id = isset( $_POST['course_id'] ) ? absint( $_POST['course_id'] ) : 0;
	$lesson    = isset( $_POST['lesson_key'] ) ? sanitize_text_field( wp_unslash( $_POST['lesson_key'] ) ) : '';
	$seconds   = isset( $_POST['seconds'] ) ? absint( $_POST['seconds'] ) : 0;
	$duration  = isset( $_POST['duration'] ) ? absint( $_POST['duration'] ) : 0;
	$complete  = ! empty( $_POST['complete'] );

	if ( ! $course_id || ! $lesson || ! zc_user_has_course( $user_id, $course_id ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی مجاز نیست.', 'zarincode' ) ), 403 );
	}
	if ( ! zc_find_lesson( $course_id, $lesson ) ) {
		wp_send_json_error( array( 'message' => __( 'جلسه نامعتبر است.', 'zarincode' ) ), 400 );
	}

	$threshold = max( 50, min( 100, (int) zc_opt( 'zc_lesson_complete_percent', 80 ) ) );
	$can_done  = false;
	if ( $complete && $duration > 0 ) {
		$can_done = ( ( $seconds / $duration ) * 100 ) >= $threshold;
	} elseif ( $complete && 0 === $duration && $seconds >= 30 ) {
		// ویدیوهای iframe بدون duration واقعی؛ حداقل ۳۰ ثانیه تماشا لازم است.
		$can_done = true;
	}

	$result = zc_save_lesson_progress( $user_id, $course_id, $lesson, $seconds, $can_done );
	wp_send_json_success( $result );
}
add_action( 'wp_ajax_zc_save_watch', 'zc_ajax_save_watch' );

/**
 * رندر کلاس درس.
 *
 * @return void
 */
function zc_classroom_template_redirect() {
	$course_id = (int) get_query_var( 'zc_learn' );
	if ( ! $course_id && isset( $_GET['zc_learn'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$course_id = absint( wp_unslash( $_GET['zc_learn'] ) ); // phpcs:ignore
	}
	if ( ! $course_id ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( add_query_arg( 'redirect_to', rawurlencode( zc_classroom_url( $course_id ) ), zc_login_url() ) );
		exit;
	}

	if ( 'zc_course' !== get_post_type( $course_id ) || 'publish' !== get_post_status( $course_id ) ) {
		wp_die( esc_html__( 'دوره یافت نشد.', 'zarincode' ), '', array( 'response' => 404 ) );
	}

	$user_id = get_current_user_id();
	$lesson  = isset( $_GET['lesson'] ) ? sanitize_text_field( wp_unslash( $_GET['lesson'] ) ) : (string) get_query_var( 'zc_lesson' ); // phpcs:ignore
	$lessons = zc_flatten_lessons( $course_id );
	if ( ! $lesson && $lessons ) {
		$lesson = $lessons[0]['key'];
	}
	$current = $lesson ? zc_find_lesson( $course_id, $lesson ) : null;
	$free    = $current && ! empty( $current['free'] );
	if ( ! $free && ! zc_user_has_course( $user_id, $course_id ) ) {
		wp_safe_redirect( get_permalink( $course_id ) );
		exit;
	}
	if ( $current && ! zc_lesson_is_unlocked( $user_id, $course_id, $current ) ) {
		wp_die( esc_html__( 'این جلسه هنوز باز نشده است (محتوای زمان‌بندی‌شده).', 'zarincode' ), '', array( 'response' => 403 ) );
	}

	status_header( 200 );
	nocache_headers();
	include ZC_DIR . 'templates/template-classroom.php';
	exit;
}
add_action( 'template_redirect', 'zc_classroom_template_redirect', 4 );
