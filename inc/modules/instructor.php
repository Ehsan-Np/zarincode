<?php
/**
 * داشبورد مدرس در پنل کاربری.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * آیا کاربر مدرس است؟
 *
 * @param int $user_id کاربر.
 * @return bool
 */
function zc_user_is_instructor( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return false;
	}
	if ( user_can( $user_id, 'manage_options' ) || user_can( $user_id, 'zc_manage_own_courses' ) ) {
		return true;
	}
	$q = new WP_Query(
		array(
			'post_type'      => 'zc_course',
			'author'         => $user_id,
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'post_status'    => array( 'publish', 'draft', 'pending' ),
		)
	);
	return $q->have_posts();
}

/**
 * افزودن تب مدرس.
 *
 * @param array $tabs تب‌ها.
 * @return array
 */
function zc_instructor_panel_tab( $tabs ) {
	if ( zc_user_is_instructor() ) {
		$tabs['instructor'] = array(
			'label' => __( 'پنل مدرس', 'zarincode' ),
			'icon'  => 'award',
			'order' => 22,
		);
	}
	return $tabs;
}
add_filter( 'zc_panel_tabs', 'zc_instructor_panel_tab' );

/**
 * آمار یک دوره برای مدرس.
 *
 * @param int $course_id دوره.
 * @return array
 */
function zc_instructor_course_stats( $course_id ) {
	global $wpdb;
	$enroll = $wpdb->prefix . 'zc_enrollments';
	$students = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$enroll} WHERE course_id=%d AND status='active'", $course_id ) ); // phpcs:ignore
	$ids      = $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM {$enroll} WHERE course_id=%d AND status='active' LIMIT 500", $course_id ) ); // phpcs:ignore
	$sum      = 0;
	$done     = 0;
	foreach ( $ids as $uid ) {
		$p = zc_get_course_progress( (int) $uid, $course_id );
		$sum += $p;
		if ( 100 === $p ) {
			$done++;
		}
	}
	$avg = $students ? (int) round( $sum / max( 1, count( $ids ) ) ) : 0;
	return array(
		'students'  => $students,
		'completed' => $done,
		'avg'       => $avg,
		'revenue'   => (float) $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(SUM(price),0) FROM {$enroll} WHERE course_id=%d", $course_id ) ), // phpcs:ignore
	);
}

/**
 * دوره‌های مدرس.
 *
 * @param int $user_id کاربر.
 * @return WP_Post[]
 */
function zc_instructor_courses( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	$args    = array(
		'post_type'      => 'zc_course',
		'posts_per_page' => 50,
		'post_status'    => array( 'publish', 'draft', 'pending' ),
		'orderby'        => 'date',
		'order'          => 'DESC',
	);
	if ( ! user_can( $user_id, 'manage_options' ) ) {
		$args['author'] = $user_id;
	}
	return get_posts( $args );
}
