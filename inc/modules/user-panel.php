<?php
/**
 * پنل کاربری اختصاصی
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * تعریف تب‌های پنل کاربری.
 *
 * @return array
 */
function zc_panel_tabs() {
	$tabs = array(
		'dashboard' => array(
			'label' => __( 'پیشخوان', 'zarincode' ),
			'icon'  => 'grid',
			'order' => 10,
		),
		'courses'   => array(
			'label' => __( 'دوره‌های من', 'zarincode' ),
			'icon'  => 'book',
			'order' => 20,
			'badge' => count( zc_get_user_courses() ),
		),
		'downloads' => array(
			'label' => __( 'دانلودهای من', 'zarincode' ),
			'icon'  => 'download',
			'order' => 30,
		),
		'orders'    => array(
			'label' => __( 'سفارش‌ها', 'zarincode' ),
			'icon'  => 'cart',
			'order' => 40,
		),
		'wallet'    => array(
			'label' => __( 'کیف پول', 'zarincode' ),
			'icon'  => 'wallet',
			'order' => 50,
		),
		'tickets'   => array(
			'label' => __( 'تیکت پشتیبانی', 'zarincode' ),
			'icon'  => 'ticket',
			'order' => 60,
		),
		'bookings'  => array(
			'label' => __( 'نوبت‌های من', 'zarincode' ),
			'icon'  => 'calendar',
			'order' => 70,
		),
		'wishlist'  => array(
			'label' => __( 'علاقه‌مندی‌ها', 'zarincode' ),
			'icon'  => 'heart',
			'order' => 80,
		),
		'certificates' => array(
			'label' => __( 'گواهینامه‌ها', 'zarincode' ),
			'icon'  => 'certificate',
			'order' => 90,
		),
		'requests'  => array(
			'label' => __( 'درخواست‌های پروژه', 'zarincode' ),
			'icon'  => 'edit',
			'order' => 95,
		),
		'contracts' => array(
			'label' => __( 'قراردادهای من', 'zarincode' ),
			'icon'  => 'file',
			'order' => 96,
			'badge' => count( zc_user_contracts() ),
		),
		'contract-chat' => array(
			'label' => __( 'گفتگوی قرارداد', 'zarincode' ),
			'icon'  => 'chat',
			'order' => 96.5,
			'badge' => zc_cchat_unread(),
		),
		'announcements' => array(
			'label' => __( 'اطلاعیه‌ها', 'zarincode' ),
			'icon'  => 'info',
			'order' => 97,
		),
		'notifications' => array(
			'label' => __( 'اعلان‌ها و ربات', 'zarincode' ),
			'icon'  => 'bell',
			'order' => 98,
		),
		'profile'   => array(
			'label' => __( 'ویرایش پروفایل', 'zarincode' ),
			'icon'  => 'user',
			'order' => 100,
		),
		'security'  => array(
			'label' => __( 'امنیت و رمز عبور', 'zarincode' ),
			'icon'  => 'lock',
			'order' => 110,
		),
	);

	// حذف تب‌های غیرفعال.
	if ( ! zc_opt( 'zc_wallet_enable', true ) ) {
		unset( $tabs['wallet'] );
	}
	if ( ! zc_opt( 'zc_ticket_enable', true ) ) {
		unset( $tabs['tickets'] );
	}
	if ( ! zc_opt( 'zc_booking_enable', true ) ) {
		unset( $tabs['bookings'] );
	}
	if ( ! zc_opt( 'zc_contract_enable', true ) ) {
		unset( $tabs['contracts'], $tabs['contract-chat'] );
	}
	if ( ! zc_opt( 'zc_certificate_enable', true ) ) {
		unset( $tabs['certificates'] );
	}
	if ( ! zc_is_woo() ) {
		unset( $tabs['orders'], $tabs['downloads'] );
	}

	$tabs = apply_filters( 'zc_panel_tabs', $tabs );

	uasort(
		$tabs,
		function ( $a, $b ) {
			return ( $a['order'] ?? 999 ) <=> ( $b['order'] ?? 999 );
		}
	);

	return $tabs;
}

/**
 * تب فعلی.
 *
 * @return string
 */
function zc_current_panel_tab() {
	$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'dashboard'; // phpcs:ignore
	$tabs = zc_panel_tabs();

	return isset( $tabs[ $tab ] ) ? $tab : 'dashboard';
}

/**
 * آمار کاربر برای پیشخوان.
 *
 * @param int $user_id کاربر.
 * @return array
 */
function zc_user_stats( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	$courses   = zc_get_user_courses( $user_id );
	$completed = 0;
	$hours     = 0;

	foreach ( $courses as $enrollment ) {
		$progress = zc_get_course_progress( $user_id, $enrollment->course_id );
		if ( 100 === $progress ) {
			$completed++;
		}
	}

	$orders_count = 0;
	$total_spent  = 0;

	if ( function_exists( 'wc_get_customer_order_count' ) ) {
		$orders_count = (int) wc_get_customer_order_count( $user_id );
		$total_spent  = (float) wc_get_customer_total_spent( $user_id );
	}

	return array(
		'courses'      => count( $courses ),
		'completed'    => $completed,
		'in_progress'  => count( $courses ) - $completed,
		'wallet'       => zc_wallet_balance( $user_id ),
		'orders'       => $orders_count,
		'spent'        => $total_spent,
		'tickets'      => zc_get_user_tickets( $user_id, array( 'posts_per_page' => -1 ) )->found_posts,
		'wishlist'     => count( zc_get_wishlist( $user_id ) ),
		'notifications'=> zc_unread_notifications_count( $user_id ),
	);
}

/**
 * صدور گواهینامه پس از تکمیل دوره.
 *
 * @param int $user_id   کاربر.
 * @param int $course_id دوره.
 * @return void
 */
function zc_issue_certificate( $user_id, $course_id ) {
	if ( ! zc_opt( 'zc_certificate_enable', true ) ) {
		return;
	}
	if ( function_exists( 'zc_quiz_blocks_certificate' ) && zc_quiz_blocks_certificate( $user_id, $course_id ) ) {
		return;
	}

	$certs = get_user_meta( $user_id, 'zc_certificates', true );
	if ( ! is_array( $certs ) ) {
		$certs = array();
	}

	foreach ( $certs as $cert ) {
		if ( (int) $cert['course_id'] === (int) $course_id ) {
			return;
		}
	}

	$certificate = array(
		'course_id' => $course_id,
		'code'      => strtoupper( 'ZC-' . wp_generate_password( 12, false, false ) ),
		'date'      => current_time( 'mysql' ),
	);
	$certs[] = $certificate;

	update_user_meta( $user_id, 'zc_certificates', $certs );
	if ( function_exists( 'zc_certificate_register' ) ) {
		zc_certificate_register( $user_id, $course_id, $certificate['code'] );
	}
	if ( function_exists( 'zc_track_event' ) ) {
		zc_track_event( 'certificate', $course_id, 0, array( 'user_id' => $user_id, 'code' => $certificate['code'] ) );
	}

	zc_add_notification(
		$user_id,
		__( 'گواهینامه شما صادر شد!', 'zarincode' ),
		sprintf( /* translators: %s: course */ __( 'تبریک! گواهی پایان دوره «%s» صادر شد.', 'zarincode' ), get_the_title( $course_id ) ),
		'success',
		zc_panel_url( 'certificates' )
	);
}
add_action( 'zc_course_completed', 'zc_issue_certificate', 10, 2 );

/**
 * دریافت گواهینامه‌های کاربر.
 *
 * @param int $user_id کاربر.
 * @return array
 */
function zc_get_certificates( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	return (array) get_user_meta( $user_id, 'zc_certificates', true );
}

/**
 * دریافت دانلودهای کاربر (محصولات دیجیتال).
 *
 * @param int $user_id کاربر.
 * @return array
 */
function zc_get_user_downloads( $user_id = 0 ) {
	if ( ! function_exists( 'wc_get_customer_available_downloads' ) ) {
		return array();
	}
	$user_id = $user_id ? $user_id : get_current_user_id();
	return wc_get_customer_available_downloads( $user_id );
}

/**
 * افزودن کلاس بدنه برای پنل.
 *
 * @param array $classes کلاس‌ها.
 * @return array
 */
function zc_panel_body_class( $classes ) {
	if ( is_page_template( 'templates/template-panel.php' ) ) {
		$classes[] = 'zc-panel-page';
		$classes[] = 'zc-panel-tab-' . zc_current_panel_tab();
	}
	return $classes;
}
add_filter( 'body_class', 'zc_panel_body_class' );

/**
 * محافظت از صفحه پنل.
 *
 * @return void
 */
function zc_protect_panel() {
	if ( ! is_page_template( 'templates/template-panel.php' ) ) {
		return;
	}
	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( add_query_arg( 'redirect_to', rawurlencode( get_permalink() ), zc_login_url() ) );
		exit;
	}
}
add_action( 'template_redirect', 'zc_protect_panel' );
