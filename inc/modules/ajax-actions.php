<?php
/**
 * اکشن‌های عمومی ای‌جکس (سبد خرید، علاقه‌مندی، فرم‌ها)
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * افزودن به سبد خرید با ای‌جکس.
 *
 * @return void
 */
function zc_ajax_add_to_cart() {
	zc_check_ajax();

	if ( ! zc_is_woo() ) {
		wp_send_json_error( array( 'message' => __( 'فروشگاه فعال نیست.', 'zarincode' ) ) );
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$quantity   = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;

	$added = WC()->cart->add_to_cart( $product_id, max( 1, $quantity ) );

	if ( ! $added ) {
		wp_send_json_error( array( 'message' => __( 'خطا در افزودن محصول به سبد خرید.', 'zarincode' ) ) );
	}

	wp_send_json_success(
		array(
			'message'  => __( 'محصول به سبد خرید اضافه شد.', 'zarincode' ),
			'count'    => zc_fa_num( WC()->cart->get_cart_contents_count() ),
			'total'    => WC()->cart->get_cart_total(),
			'cart_url' => wc_get_cart_url(),
		)
	);
}
add_action( 'wp_ajax_zc_add_to_cart', 'zc_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_zc_add_to_cart', 'zc_ajax_add_to_cart' );

/**
 * افزودن/حذف از علاقه‌مندی‌ها.
 *
 * @return void
 */
function zc_ajax_toggle_wishlist() {
	zc_check_ajax();

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'برای افزودن به علاقه‌مندی‌ها وارد شوید.', 'zarincode' ) ) );
	}

	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$user_id = get_current_user_id();
	$list    = (array) get_user_meta( $user_id, 'zc_wishlist', true );
	$list    = array_filter( array_map( 'absint', $list ) );

	if ( in_array( $post_id, $list, true ) ) {
		$list  = array_diff( $list, array( $post_id ) );
		$added = false;
		$msg   = __( 'از علاقه‌مندی‌ها حذف شد.', 'zarincode' );
	} else {
		$list[] = $post_id;
		$added  = true;
		$msg    = __( 'به علاقه‌مندی‌ها اضافه شد.', 'zarincode' );
	}

	update_user_meta( $user_id, 'zc_wishlist', array_values( $list ) );

	wp_send_json_success(
		array(
			'added'   => $added,
			'count'   => zc_fa_num( count( $list ) ),
			'message' => $msg,
		)
	);
}
add_action( 'wp_ajax_zc_toggle_wishlist', 'zc_ajax_toggle_wishlist' );

/**
 * دریافت لیست علاقه‌مندی‌ها.
 *
 * @param int $user_id کاربر.
 * @return array
 */
function zc_get_wishlist( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	$list    = (array) get_user_meta( $user_id, 'zc_wishlist', true );
	return array_filter( array_map( 'absint', $list ) );
}

/**
 * فرم تماس با ما.
 *
 * @return void
 */
function zc_ajax_contact_submit() {
	zc_check_ajax();
	if ( ! zc_rate_limit( 'contact', 5, HOUR_IN_SECONDS ) ) {
		wp_send_json_error( array( 'message' => __( 'تعداد پیام‌های شما بیش از حد مجاز است؛ کمی بعد تلاش کنید.', 'zarincode' ) ), 429 );
	}

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$phone   = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
	$dept    = isset( $_POST['department'] ) ? sanitize_text_field( wp_unslash( $_POST['department'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	$to      = isset( $_POST['receiver'] ) ? sanitize_email( wp_unslash( $_POST['receiver'] ) ) : '';

	if ( ! $name || ! $email || ! $message ) {
		wp_send_json_error( array( 'message' => __( 'لطفاً فیلدهای ضروری را تکمیل کنید.', 'zarincode' ) ) );
	}

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'ایمیل معتبر نیست.', 'zarincode' ) ) );
	}

	// جلوگیری از اسپم.
	$lock = 'zc_contact_' . md5( zc_get_ip() );
	if ( get_transient( $lock ) ) {
		wp_send_json_error( array( 'message' => __( 'لطفاً کمی صبر کنید.', 'zarincode' ) ) );
	}
	set_transient( $lock, 1, 60 );

	$to = $to ? $to : zc_opt( 'zc_contact_email', get_option( 'admin_email' ) );

	$body = sprintf(
		"نام: %s\nایمیل: %s\nتلفن: %s\nدپارتمان: %s\nموضوع: %s\n\nپیام:\n%s\n\nIP: %s",
		$name,
		$email,
		$phone,
		$dept,
		$subject,
		$message,
		zc_get_ip()
	);

	$sent = wp_mail(
		$to,
		sprintf( /* translators: %s: subject */ __( 'پیام جدید از سایت: %s', 'zarincode' ), $subject ? $subject : $name ),
		$body,
		array( 'Reply-To: ' . $email )
	);

	// ذخیره در دیتابیس.
	wp_insert_post(
		array(
			'post_type'    => 'zc_ticket',
			'post_title'   => $subject ? $subject : sprintf( __( 'پیام از %s', 'zarincode' ), $name ),
			'post_content' => $message,
			'post_status'  => 'publish',
			'meta_input'   => array(
				'_zc_status'     => 'open',
				'_zc_department' => $dept,
				'_zc_priority'   => 'normal',
				'_zc_guest_name' => $name,
				'_zc_guest_email'=> $email,
				'_zc_guest_phone'=> $phone,
				'_zc_source'     => 'contact_form',
				'_zc_last_reply' => current_time( 'mysql' ),
			),
		)
	);

	do_action( 'zc_contact_submitted', $name, $email, $message );

	wp_send_json_success(
		array(
			'message' => __( 'پیام شما با موفقیت ارسال شد. به زودی پاسخ می‌دهیم.', 'zarincode' ),
		)
	);
}
add_action( 'wp_ajax_zc_contact_submit', 'zc_ajax_contact_submit' );
add_action( 'wp_ajax_nopriv_zc_contact_submit', 'zc_ajax_contact_submit' );

/**
 * عضویت در خبرنامه.
 * ایمیل و موبایل الزامی‌اند؛ آیدی بله و تلگرام اختیاری.
 *
 * @return void
 */
function zc_ajax_newsletter() {
	zc_check_ajax();
	if ( ! zc_rate_limit( 'newsletter', 5, HOUR_IN_SECONDS ) ) {
		wp_send_json_error( array( 'message' => __( 'تعداد درخواست بیش از حد مجاز است.', 'zarincode' ) ), 429 );
	}

	$email      = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$mobile     = isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : '';
	$bale_id    = isset( $_POST['bale_id'] ) ? sanitize_text_field( wp_unslash( $_POST['bale_id'] ) ) : '';
	$telegram   = isset( $_POST['telegram_id'] ) ? sanitize_text_field( wp_unslash( $_POST['telegram_id'] ) ) : '';
	$name       = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

	// ایمیل و موبایل هر دو الزامی.
	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'ایمیل معتبر نیست.', 'zarincode' ) ) );
	}

	$mobile = zc_sanitize_mobile( $mobile );
	if ( ! $mobile ) {
		wp_send_json_error( array( 'message' => __( 'شماره موبایل معتبر نیست.', 'zarincode' ) ) );
	}

	// جلوگیری از تکرار بر اساس ایمیل یا موبایل بدون بارگذاری کل فهرست.
	if ( function_exists( 'zc_newsletter_storage_exists' ) && zc_newsletter_storage_ready() ) {
		if ( zc_newsletter_storage_exists( $email, $mobile ) ) {
			wp_send_json_error( array( 'message' => __( 'شما قبلاً عضو شده‌اید.', 'zarincode' ) ) );
		}
	} else {
		foreach ( zc_newsletter_subscribers() as $sub ) {
			if ( strtolower( (string) $sub['email'] ) === strtolower( $email ) || (string) $sub['mobile'] === $mobile ) {
				wp_send_json_error( array( 'message' => __( 'شما قبلاً عضو شده‌اید.', 'zarincode' ) ) );
				}
			}
		}

		$subscriber = array(
		'email'       => $email,
		'mobile'      => $mobile,
		'bale_id'     => ltrim( $bale_id, '@' ),
		'telegram_id' => ltrim( $telegram, '@' ),
		'name'        => $name,
		'date'        => current_time( 'mysql' ),
	);

	zc_newsletter_add( $subscriber );

	/**
	 * پس از عضویت در خبرنامه.
	 *
	 * @param array $subscriber داده‌ی عضو.
	 */
	do_action( 'zc_newsletter_subscribed', $subscriber );

	wp_send_json_success( array( 'message' => __( 'عضویت شما با موفقیت ثبت شد!', 'zarincode' ) ) );
}
add_action( 'wp_ajax_zc_newsletter', 'zc_ajax_newsletter' );
add_action( 'wp_ajax_nopriv_zc_newsletter', 'zc_ajax_newsletter' );

/**
 * بروزرسانی پروفایل کاربر.
 *
 * @return void
 */
function zc_ajax_update_profile() {
	zc_check_ajax();

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'ابتدا وارد شوید.', 'zarincode' ) ) );
	}

	$user_id = get_current_user_id();

	$first  = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
	$last   = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
	$email  = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$bio    = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
	$job    = isset( $_POST['job'] ) ? sanitize_text_field( wp_unslash( $_POST['job'] ) ) : '';
	$nid    = isset( $_POST['national_id'] ) ? sanitize_text_field( wp_unslash( $_POST['national_id'] ) ) : '';
	$addr   = isset( $_POST['address'] ) ? sanitize_textarea_field( wp_unslash( $_POST['address'] ) ) : '';

	if ( $email && ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'ایمیل معتبر نیست.', 'zarincode' ) ) );
	}

	$exists = $email ? email_exists( $email ) : false;
	if ( $exists && (int) $exists !== $user_id ) {
		wp_send_json_error( array( 'message' => __( 'این ایمیل توسط کاربر دیگری استفاده شده است.', 'zarincode' ) ) );
	}

	$data = array(
		'ID'           => $user_id,
		'first_name'   => $first,
		'last_name'    => $last,
		'description'  => $bio,
		'display_name' => trim( $first . ' ' . $last ) ? trim( $first . ' ' . $last ) : wp_get_current_user()->user_login,
	);

	if ( $email ) {
		$data['user_email'] = $email;
	}

	wp_update_user( $data );

	update_user_meta( $user_id, 'zc_job', $job );
	update_user_meta( $user_id, 'zc_national_id', $nid );
	update_user_meta( $user_id, 'zc_address', $addr );

	// آپلود آواتار.
	if ( ! empty( $_FILES['avatar']['name'] ) ) { // phpcs:ignore
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$attach_id = media_handle_upload( 'avatar', 0 );
		if ( ! is_wp_error( $attach_id ) ) {
			update_user_meta( $user_id, 'zc_avatar', $attach_id );
		}
	}

	wp_send_json_success( array( 'message' => __( 'اطلاعات با موفقیت بروزرسانی شد.', 'zarincode' ) ) );
}
add_action( 'wp_ajax_zc_update_profile', 'zc_ajax_update_profile' );

/**
 * تغییر رمز عبور از پنل.
 *
 * @return void
 */
function zc_ajax_change_password() {
	zc_check_ajax();

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'ابتدا وارد شوید.', 'zarincode' ) ) );
	}

	$current = isset( $_POST['current_password'] ) ? (string) wp_unslash( $_POST['current_password'] ) : ''; // phpcs:ignore
	$new     = isset( $_POST['new_password'] ) ? (string) wp_unslash( $_POST['new_password'] ) : ''; // phpcs:ignore
	$confirm = isset( $_POST['confirm_password'] ) ? (string) wp_unslash( $_POST['confirm_password'] ) : ''; // phpcs:ignore

	$user = wp_get_current_user();

	if ( ! wp_check_password( $current, $user->user_pass, $user->ID ) ) {
		wp_send_json_error( array( 'message' => __( 'رمز عبور فعلی اشتباه است.', 'zarincode' ) ) );
	}
	if ( strlen( $new ) < 8 ) {
		wp_send_json_error( array( 'message' => __( 'رمز جدید باید حداقل ۸ کاراکتر باشد.', 'zarincode' ) ) );
	}
	if ( $new !== $confirm ) {
		wp_send_json_error( array( 'message' => __( 'تکرار رمز عبور مطابقت ندارد.', 'zarincode' ) ) );
	}

	wp_set_password( $new, $user->ID );
	zc_do_login( get_user_by( 'id', $user->ID ) );

	wp_send_json_success( array( 'message' => __( 'رمز عبور با موفقیت تغییر کرد.', 'zarincode' ) ) );
}
add_action( 'wp_ajax_zc_change_password', 'zc_ajax_change_password' );

/**
 * جایگزینی آواتار پیش‌فرض با آواتار آپلودی کاربر.
 *
 * @param string $url     آدرس.
 * @param mixed  $id_or_email شناسه.
 * @param array  $args    آرگومان.
 * @return string
 */
function zc_custom_avatar( $url, $id_or_email, $args ) {
	$user_id = 0;

	if ( is_numeric( $id_or_email ) ) {
		$user_id = (int) $id_or_email;
	} elseif ( $id_or_email instanceof WP_User ) {
		$user_id = $id_or_email->ID;
	} elseif ( $id_or_email instanceof WP_Comment && $id_or_email->user_id ) {
		$user_id = (int) $id_or_email->user_id;
	} elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
		$user = get_user_by( 'email', $id_or_email );
		$user_id = $user ? $user->ID : 0;
	}

	if ( $user_id ) {
		$attach_id = get_user_meta( $user_id, 'zc_avatar', true );
		if ( $attach_id ) {
			$src = wp_get_attachment_image_url( $attach_id, 'zc-avatar' );
			if ( $src ) {
				return $src;
			}
		}
	}

	return $url;
}
add_filter( 'get_avatar_url', 'zc_custom_avatar', 10, 3 );
