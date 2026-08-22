<?php
/**
 * ماژول احراز هویت (ورود/ثبت‌نام با موبایل و رمز عبور)
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * ورود با کد پیامکی.
 *
 * @return void
 */
function zc_ajax_login_otp() {
	zc_check_ajax();

	$mobile = isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : '';
	$code   = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
	$mobile = zc_sanitize_mobile( $mobile );

	if ( ! $mobile ) {
		wp_send_json_error( array( 'message' => __( 'شماره موبایل معتبر نیست.', 'zarincode' ) ) );
	}

	if ( ! zc_verify_otp( $mobile, $code ) ) {
		wp_send_json_error( array( 'message' => __( 'کد وارد شده صحیح نیست یا منقضی شده است.', 'zarincode' ) ) );
	}

	$user = zc_get_user_by_mobile( $mobile );

	// ثبت‌نام خودکار در صورت عدم وجود کاربر.
	if ( ! $user ) {
		if ( ! zc_opt( 'zc_allow_registration', true ) ) {
			wp_send_json_error( array( 'message' => __( 'ثبت‌نام در سایت غیرفعال است.', 'zarincode' ) ) );
		}
		$user_id = zc_create_user_by_mobile( $mobile );
		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
		}
		$user = get_user_by( 'id', $user_id );

		// هدیه خوش‌آمدگویی کیف پول.
		$gift = (float) zc_opt( 'zc_welcome_gift', 0 );
		if ( $gift > 0 ) {
			zc_wallet_deposit( $user_id, $gift, __( 'هدیه خوش‌آمدگویی', 'zarincode' ), 'gift' );
		}

		do_action( 'zc_user_registered', $user_id, 'mobile' );
	}

	zc_do_login( $user );

	wp_send_json_success(
		array(
			'message'  => __( 'خوش آمدید! در حال انتقال…', 'zarincode' ),
			'redirect' => zc_get_login_redirect(),
		)
	);
}
add_action( 'wp_ajax_nopriv_zc_login_otp', 'zc_ajax_login_otp' );
add_action( 'wp_ajax_zc_login_otp', 'zc_ajax_login_otp' );

/**
 * ورود با نام کاربری/ایمیل/موبایل و رمز عبور.
 *
 * @return void
 */
function zc_ajax_login_password() {
	zc_check_ajax();

	$login    = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
	$password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore
	$remember = ! empty( $_POST['remember'] );

	if ( ! $login || ! $password ) {
		wp_send_json_error( array( 'message' => __( 'نام کاربری و رمز عبور را وارد کنید.', 'zarincode' ) ) );
	}

	// محدودیت تلاش ناموفق.
	$ip_key    = 'zc_login_fail_' . md5( zc_get_ip() );
	$fail_count = (int) get_transient( $ip_key );
	if ( $fail_count >= (int) zc_opt( 'zc_login_max_attempts', 8 ) ) {
		wp_send_json_error( array( 'message' => __( 'تعداد تلاش‌های ناموفق زیاد است. ۱۵ دقیقه دیگر تلاش کنید.', 'zarincode' ) ) );
	}

	// پشتیبانی از ورود با موبایل.
	$mobile = zc_sanitize_mobile( $login );
	if ( $mobile ) {
		$user_obj = zc_get_user_by_mobile( $mobile );
		if ( $user_obj ) {
			$login = $user_obj->user_login;
		}
	}

	$user = wp_signon(
		array(
			'user_login'    => $login,
			'user_password' => $password,
			'remember'      => $remember,
		),
		is_ssl()
	);

	if ( is_wp_error( $user ) ) {
		set_transient( $ip_key, $fail_count + 1, 15 * MINUTE_IN_SECONDS );
		wp_send_json_error( array( 'message' => __( 'نام کاربری یا رمز عبور اشتباه است.', 'zarincode' ) ) );
	}

	delete_transient( $ip_key );
	wp_set_current_user( $user->ID );

	wp_send_json_success(
		array(
			'message'  => __( 'ورود موفق. در حال انتقال…', 'zarincode' ),
			'redirect' => zc_get_login_redirect(),
		)
	);
}
add_action( 'wp_ajax_nopriv_zc_login_password', 'zc_ajax_login_password' );

/**
 * ثبت‌نام با رمز عبور.
 *
 * @return void
 */
function zc_ajax_register() {
	zc_check_ajax();

	if ( ! zc_opt( 'zc_allow_registration', true ) ) {
		wp_send_json_error( array( 'message' => __( 'ثبت‌نام در سایت غیرفعال است.', 'zarincode' ) ) );
	}

	$name     = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$mobile   = isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : '';
	$password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore
	$code     = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';

	$mobile = zc_sanitize_mobile( $mobile );

	if ( ! $mobile ) {
		wp_send_json_error( array( 'message' => __( 'شماره موبایل معتبر نیست.', 'zarincode' ) ) );
	}
	if ( strlen( $password ) < 6 ) {
		wp_send_json_error( array( 'message' => __( 'رمز عبور باید حداقل ۶ کاراکتر باشد.', 'zarincode' ) ) );
	}
	if ( $email && ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'ایمیل معتبر نیست.', 'zarincode' ) ) );
	}
	if ( zc_get_user_by_mobile( $mobile ) ) {
		wp_send_json_error( array( 'message' => __( 'این شماره موبایل قبلاً ثبت شده است. وارد شوید.', 'zarincode' ) ) );
	}
	if ( $email && email_exists( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'این ایمیل قبلاً ثبت شده است.', 'zarincode' ) ) );
	}

	// تایید موبایل با کد در صورت فعال بودن.
	if ( zc_opt( 'zc_verify_mobile_on_register', true ) && ! zc_verify_otp( $mobile, $code ) ) {
		wp_send_json_error( array( 'message' => __( 'کد تایید موبایل صحیح نیست.', 'zarincode' ) ) );
	}

	$user_id = zc_create_user_by_mobile( $mobile, $password, $email, $name );

	if ( is_wp_error( $user_id ) ) {
		wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
	}

	$gift = (float) zc_opt( 'zc_welcome_gift', 0 );
	if ( $gift > 0 ) {
		zc_wallet_deposit( $user_id, $gift, __( 'هدیه خوش‌آمدگویی', 'zarincode' ), 'gift' );
	}

	do_action( 'zc_user_registered', $user_id, 'password' );

	zc_do_login( get_user_by( 'id', $user_id ) );

	wp_send_json_success(
		array(
			'message'  => __( 'ثبت‌نام با موفقیت انجام شد. خوش آمدید!', 'zarincode' ),
			'redirect' => zc_get_login_redirect(),
		)
	);
}
add_action( 'wp_ajax_nopriv_zc_register', 'zc_ajax_register' );

/**
 * بازیابی رمز عبور با پیامک.
 *
 * @return void
 */
function zc_ajax_reset_password() {
	zc_check_ajax();

	$mobile   = isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : '';
	$code     = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
	$password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore

	$mobile = zc_sanitize_mobile( $mobile );

	if ( ! $mobile || ! zc_verify_otp( $mobile, $code ) ) {
		wp_send_json_error( array( 'message' => __( 'کد تایید نامعتبر است.', 'zarincode' ) ) );
	}
	if ( strlen( $password ) < 6 ) {
		wp_send_json_error( array( 'message' => __( 'رمز عبور باید حداقل ۶ کاراکتر باشد.', 'zarincode' ) ) );
	}

	$user = zc_get_user_by_mobile( $mobile );
	if ( ! $user ) {
		wp_send_json_error( array( 'message' => __( 'کاربری با این شماره یافت نشد.', 'zarincode' ) ) );
	}

	wp_set_password( $password, $user->ID );
	zc_do_login( $user );

	wp_send_json_success(
		array(
			'message'  => __( 'رمز عبور با موفقیت تغییر کرد.', 'zarincode' ),
			'redirect' => zc_panel_url(),
		)
	);
}
add_action( 'wp_ajax_nopriv_zc_reset_password', 'zc_ajax_reset_password' );

/**
 * دریافت کاربر با شماره موبایل.
 *
 * @param string $mobile موبایل.
 * @return WP_User|false
 */
function zc_get_user_by_mobile( $mobile ) {
	$users = get_users(
		array(
			'meta_key'   => 'zc_mobile', // phpcs:ignore
			'meta_value' => $mobile, // phpcs:ignore
			'number'     => 1,
			'fields'     => 'all',
		)
	);

	if ( ! empty( $users ) ) {
		return $users[0];
	}

	// جستجو در نام کاربری (کاربران قدیمی).
	$user = get_user_by( 'login', $mobile );
	return $user ? $user : false;
}

/**
 * ساخت کاربر جدید با موبایل.
 *
 * @param string $mobile   موبایل.
 * @param string $password رمز.
 * @param string $email    ایمیل.
 * @param string $name     نام.
 * @return int|WP_Error
 */
function zc_create_user_by_mobile( $mobile, $password = '', $email = '', $name = '' ) {
	$username = $mobile;
	$i        = 1;
	while ( username_exists( $username ) ) {
		$username = $mobile . '_' . $i;
		$i++;
	}

	if ( ! $email ) {
		$email = $mobile . '@' . wp_parse_url( home_url(), PHP_URL_HOST );
	}
	if ( ! $password ) {
		$password = wp_generate_password( 12, true );
	}

	$user_id = wp_insert_user(
		array(
			'user_login'   => $username,
			'user_pass'    => $password,
			'user_email'   => $email,
			'display_name' => $name ? $name : $mobile,
			'first_name'   => $name,
			'role'         => zc_opt( 'zc_default_role', 'zc_student' ),
		)
	);

	if ( is_wp_error( $user_id ) ) {
		return $user_id;
	}

	update_user_meta( $user_id, 'zc_mobile', $mobile );
	update_user_meta( $user_id, 'zc_mobile_verified', 1 );
	update_user_meta( $user_id, 'zc_register_ip', zc_get_ip() );
	update_user_meta( $user_id, 'zc_register_date', current_time( 'mysql' ) );

	return $user_id;
}

/**
 * انجام ورود کاربر.
 *
 * @param WP_User $user کاربر.
 * @return void
 */
function zc_do_login( $user ) {
	if ( ! $user instanceof WP_User ) {
		return;
	}
	wp_clear_auth_cookie();
	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, true, is_ssl() );
	do_action( 'wp_login', $user->user_login, $user );
	update_user_meta( $user->ID, 'zc_last_login', current_time( 'mysql' ) );
}

/**
 * آدرس بازگشت پس از ورود.
 *
 * @return string
 */
function zc_get_login_redirect() {
	$redirect = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : ''; // phpcs:ignore
	if ( $redirect ) {
		return $redirect;
	}
	return zc_panel_url();
}

/**
 * دریافت IP کاربر.
 *
 * @return string
 */
function zc_get_ip() {
	$keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR' );
	foreach ( $keys as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
			$ip = trim( explode( ',', $ip )[0] );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}
	}
	return '0.0.0.0';
}

/**
 * ثبت نقش‌های سفارشی.
 *
 * @return void
 */
function zc_register_roles() {
	if ( get_role( 'zc_student' ) ) {
		return;
	}

	add_role(
		'zc_student',
		__( 'دانشجو', 'zarincode' ),
		array(
			'read'                   => true,
			'zc_view_courses'        => true,
			'zc_create_ticket'       => true,
		)
	);

	add_role(
		'zc_teacher',
		__( 'مدرس', 'zarincode' ),
		array(
			'read'                   => true,
			'upload_files'           => true,
			'edit_posts'             => true,
			'edit_published_posts'   => true,
			'publish_posts'          => false,
			'delete_posts'           => true,
			'zc_manage_own_courses'  => true,
			'zc_answer_ticket'       => true,
		)
	);
}
add_action( 'after_switch_theme', 'zc_register_roles' );
add_action( 'init', 'zc_register_roles', 5 );

/**
 * تغییر مسیر صفحه ورود پیش‌فرض وردپرس به صفحه سفارشی.
 *
 * @return void
 */
function zc_redirect_wp_login() {
	if ( ! zc_opt( 'zc_custom_login_redirect', true ) ) {
		return;
	}

	$page = (int) zc_opt( 'zc_login_page', 0 );
	if ( ! $page ) {
		return;
	}

	global $pagenow;

	if ( 'wp-login.php' !== $pagenow ) {
		return;
	}

	// درخواست‌های POST (ارسال فرم ورود) هرگز نباید تغییر مسیر داده شوند.
	if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		return;
	}

	$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	// اکشن‌های سیستمی وردپرس باید دست‌نخورده باقی بمانند.
	$skip = array( 'logout', 'lostpassword', 'rp', 'resetpass', 'postpass', 'register', 'confirmaction', 'entered_recovery_mode' );

	if ( in_array( $action, $skip, true ) ) {
		return;
	}

	// درِ پشتی مدیر: /wp-login.php?zc_admin=1
	// اگر صفحه‌ی ورود سفارشی دچار مشکل شود، مدیر همچنان راه ورود دارد.
	if ( isset( $_GET['zc_admin'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	// ورود میان‌مرحله‌ای (interim-login) داخل مودال وردپرس.
	if ( isset( $_GET['interim-login'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	// اجازه‌ی غیرفعال‌سازی برای افزونه‌ها.
	if ( ! apply_filters( 'zc_redirect_wp_login', true ) ) {
		return;
	}

	// مقصد پس از ورود حفظ می‌شود.
	$target = get_permalink( $page );

	if ( ! empty( $_GET['redirect_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$target = add_query_arg(
			'redirect_to',
			rawurlencode( esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$target
		);
	}

	wp_safe_redirect( $target );
	exit;
}
add_action( 'init', 'zc_redirect_wp_login' );

/**
 * مسدودسازی دسترسی به پیشخوان برای دانشجویان.
 *
 * @return void
 */
function zc_block_admin_access() {
	if ( ! is_admin() || wp_doing_ajax() || ! is_user_logged_in() ) {
		return;
	}
	if ( ! zc_opt( 'zc_block_dashboard', true ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'manage_options' ) ) {
		wp_safe_redirect( zc_panel_url() );
		exit;
	}
}
add_action( 'admin_init', 'zc_block_admin_access' );

/**
 * حذف نوار مدیریت برای کاربران عادی.
 *
 * @param bool $show نمایش.
 * @return bool
 */
function zc_hide_admin_bar( $show ) {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return false;
	}
	return $show;
}
add_filter( 'show_admin_bar', 'zc_hide_admin_bar' );

/**
 * تغییر مسیر پس از ورود.
 *
 * @param string           $redirect مقصد.
 * @param string           $request  درخواست.
 * @param WP_User|WP_Error $user     کاربر.
 * @return string
 */
function zc_login_redirect( $redirect, $request, $user ) {
	if ( $user instanceof WP_User && ! user_can( $user, 'edit_posts' ) ) {
		return zc_panel_url();
	}
	return $redirect;
}
add_filter( 'login_redirect', 'zc_login_redirect', 10, 3 );
