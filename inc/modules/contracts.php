<?php
/**
 * سامانه‌ی قراردادها
 *
 * گردش کار: کاربر اطلاعات را وارد می‌کند → متن قرارداد بر پایه‌ی
 * الگوی تعریف‌شده در پیشخوان و با جای‌گذاری شناسه‌ها ساخته می‌شود →
 * کد یک‌بارمصرف پیامکی برای کاربر ارسال می‌شود → با تأیید کد و کشیدن
 * امضا، قرارداد مهر و امضا شده و نسخه‌ی قابل دانلود تولید می‌شود.
 *
 * دو نوع پست:
 *  - zc_contract_tpl : الگوی قرارداد (متن، بندها، فیلدهای سفارشی)
 *  - zc_contract     : قرارداد صادرشده برای یک کاربر
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   نوع‌های پست
   ========================================================================== */

/**
 * ثبت نوع پست الگوی قرارداد و قرارداد صادرشده.
 *
 * @return void
 */
function zc_register_contract_cpt() {
	register_post_type(
		'zc_contract_tpl',
		array(
			'labels'          => array(
				'name'          => __( 'الگوهای قرارداد', 'zarincode' ),
				'singular_name' => __( 'الگوی قرارداد', 'zarincode' ),
				'add_new'       => __( 'افزودن الگو', 'zarincode' ),
				'add_new_item'  => __( 'افزودن الگوی قرارداد', 'zarincode' ),
				'edit_item'     => __( 'ویرایش الگوی قرارداد', 'zarincode' ),
				'menu_name'     => __( 'قراردادها', 'zarincode' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'menu_icon'       => 'dashicons-media-text',
			'menu_position'   => 27,
			'supports'        => array( 'title', 'editor' ),
			'capability_type' => 'post',
		)
	);

	register_post_type(
		'zc_contract',
		array(
			'labels'          => array(
				'name'          => __( 'قراردادهای صادرشده', 'zarincode' ),
				'singular_name' => __( 'قرارداد', 'zarincode' ),
				'edit_item'     => __( 'مشاهده‌ی قرارداد', 'zarincode' ),
				'search_items'  => __( 'جستجوی قرارداد', 'zarincode' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'edit.php?post_type=zc_contract_tpl',
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'map_meta_cap'    => true,
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
		)
	);
}
add_action( 'init', 'zc_register_contract_cpt' );

/* ==========================================================================
   وضعیت‌ها و شناسه‌ها
   ========================================================================== */

/**
 * وضعیت‌های چرخه‌ی عمر قرارداد.
 *
 * @return array
 */
function zc_contract_statuses() {
	return apply_filters(
		'zc_contract_statuses',
		array(
			'draft'    => array(
				'label' => __( 'پیش‌نویس', 'zarincode' ),
				'color' => '#8A93A6',
			),
			'pending'  => array(
				'label' => __( 'در انتظار امضا', 'zarincode' ),
				'color' => '#D89B0D',
			),
			'signed'   => array(
				'label' => __( 'امضا شده', 'zarincode' ),
				'color' => '#1B9C58',
			),
			'active'   => array(
				'label' => __( 'در حال اجرا', 'zarincode' ),
				'color' => '#0B6BCB',
			),
			'done'     => array(
				'label' => __( 'خاتمه‌یافته', 'zarincode' ),
				'color' => '#5A6478',
			),
			'canceled' => array(
				'label' => __( 'لغو شده', 'zarincode' ),
				'color' => '#B32D2E',
			),
		)
	);
}

/**
 * فیلدهای پایه‌ی قرارداد که همیشه وجود دارند.
 *
 * فیلدهای سفارشی مدیر به این فهرست افزوده می‌شوند.
 *
 * @return array
 */
function zc_contract_base_fields() {
	return apply_filters(
		'zc_contract_base_fields',
		array(
			'full_name'  => array(
				'label'    => __( 'نام و نام خانوادگی', 'zarincode' ),
				'type'     => 'text',
				'required' => true,
				'token'    => 'نام_مشتری',
			),
			'national_id' => array(
				'label'    => __( 'کد ملی', 'zarincode' ),
				'type'     => 'text',
				'required' => true,
				'token'    => 'کد_ملی',
				'pattern'  => '[0-9۰-۹]{10}',
			),
			'mobile'     => array(
				'label'    => __( 'شماره موبایل', 'zarincode' ),
				'type'     => 'tel',
				'required' => true,
				'token'    => 'موبایل',
			),
			'email'      => array(
				'label'    => __( 'ایمیل', 'zarincode' ),
				'type'     => 'email',
				'required' => false,
				'token'    => 'ایمیل',
			),
			'address'    => array(
				'label'    => __( 'نشانی', 'zarincode' ),
				'type'     => 'textarea',
				'required' => false,
				'token'    => 'نشانی',
			),
		)
	);
}

/**
 * فیلدهای سفارشی تعریف‌شده برای یک الگو.
 *
 * هر خط در متاباکس یک فیلد است با ساختار:
 * شناسه | برچسب | نوع | الزامی
 *
 * @param int $tpl_id شناسه الگو.
 * @return array
 */
function zc_contract_custom_fields( $tpl_id ) {
	$raw = (string) get_post_meta( $tpl_id, '_zc_ct_fields', true );

	if ( '' === trim( $raw ) ) {
		return array();
	}

	$out = array();

	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( $line );

		if ( '' === $line ) {
			continue;
		}

		$parts = array_map( 'trim', explode( '|', $line ) );

		$key = sanitize_key( $parts[0] ?? '' );

		if ( ! $key ) {
			continue;
		}

		$out[ $key ] = array(
			'label'    => $parts[1] ?? $key,
			'type'     => in_array( $parts[2] ?? 'text', array( 'text', 'textarea', 'number', 'date', 'tel', 'email', 'select' ), true ) ? $parts[2] : 'text',
			'required' => ! empty( $parts[3] ) && in_array( $parts[3], array( '1', 'الزامی', 'yes' ), true ),
			'options'  => ! empty( $parts[4] ) ? array_map( 'trim', explode( '،', $parts[4] ) ) : array(),
			'token'    => $parts[1] ?? $key,
			'custom'   => true,
		);
	}

	return $out;
}

/**
 * همه‌ی فیلدهای یک الگو (پایه + سفارشی).
 *
 * @param int $tpl_id شناسه الگو.
 * @return array
 */
function zc_contract_fields( $tpl_id ) {
	return array_merge( zc_contract_base_fields(), zc_contract_custom_fields( $tpl_id ) );
}

/* ==========================================================================
   جای‌گذاری شناسه‌ها در متن
   ========================================================================== */

/**
 * شناسه‌های سراسری قابل استفاده در متن قرارداد.
 *
 * @param array $data داده‌های قرارداد.
 * @param int   $contract_id شناسه قرارداد.
 * @return array
 */
function zc_contract_tokens( $data = array(), $contract_id = 0 ) {
	$tokens = array(
		'نام_سایت'      => zc_opt( 'zc_site_name', get_bloginfo( 'name' ) ),
		'نام_شرکت'      => zc_opt( 'zc_contract_company', zc_opt( 'zc_site_name', get_bloginfo( 'name' ) ) ),
		'شناسه_ملی_شرکت' => zc_opt( 'zc_contract_company_id', '' ),
		'نشانی_شرکت'    => zc_opt( 'zc_address', '' ),
		'تلفن_شرکت'     => zc_opt( 'zc_phone', '' ),
		'ایمیل_شرکت'    => zc_opt( 'zc_email', '' ),
		'مدیرعامل'      => zc_opt( 'zc_contract_ceo', '' ),
		// ارقام فارسی، چون متن قرارداد رسمی و فارسی است.
		'تاریخ'         => zc_fa_num( zc_jalali_date( 'j F Y' ) ),
		'تاریخ_عددی'    => zc_fa_num( zc_jalali_date( 'Y/m/d' ) ),
		'شماره_قرارداد' => $contract_id ? zc_contract_number( $contract_id ) : '',
	);

	/*
	 * تعداد بندها به‌صورت پویا محاسبه می‌شود تا اگر مدیر بندی را
	 * اضافه یا حذف کرد، متن پایانی قرارداد همچنان درست بماند.
	 */
	if ( ! empty( $data['_tpl_id'] ) ) {
		$tokens['تعداد_ماده'] = zc_fa_num( count( zc_contract_clauses( (int) $data['_tpl_id'] ) ) + 1 );
	}

	foreach ( $data as $key => $value ) {
		$tokens[ $key ] = $value;
	}

	return apply_filters( 'zc_contract_tokens', $tokens, $data, $contract_id );
}

/**
 * جای‌گذاری شناسه‌ها در یک متن.
 *
 * شناسه‌ها با الگوی {نام} نوشته می‌شوند.
 *
 * @param string $text   متن الگو.
 * @param array  $tokens شناسه‌ها.
 * @return string
 */
function zc_contract_parse( $text, $tokens ) {
	$text = (string) $text;

	foreach ( $tokens as $key => $value ) {
		$text = str_replace( '{' . $key . '}', (string) $value, $text );
	}

	// شناسه‌های پرنشده حذف شوند تا در خروجی دیده نشوند.
	$text = preg_replace( '/\{[^}\s]{1,40}\}/u', '……', $text );

	return $text;
}

/**
 * شماره‌ی نمایشی قرارداد.
 *
 * @param int $contract_id شناسه.
 * @return string
 */
function zc_contract_number( $contract_id ) {
	$saved = get_post_meta( $contract_id, '_zc_ct_number', true );

	if ( $saved ) {
		return $saved;
	}

	$prefix = zc_opt( 'zc_contract_prefix', 'ZC' );

	// شماره با ارقام لاتین ذخیره می‌شود تا در جستجو و مرتب‌سازی درست کار کند.
	$number = sprintf( '%s-%s-%d', $prefix, zc_en_num( zc_jalali_date( 'Y' ) ), $contract_id );

	update_post_meta( $contract_id, '_zc_ct_number', $number );

	return $number;
}

/**
 * بندهای قرارداد یک الگو.
 *
 * هر بند در یک خط با ساختار «عنوان | متن» نوشته می‌شود.
 *
 * @param int $tpl_id شناسه الگو.
 * @return array
 */
function zc_contract_clauses( $tpl_id ) {
	$raw = (string) get_post_meta( $tpl_id, '_zc_ct_clauses', true );

	if ( '' === trim( $raw ) ) {
		return array();
	}

	$out = array();

	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( $line );

		if ( '' === $line ) {
			continue;
		}

		$parts = array_map( 'trim', explode( '|', $line, 2 ) );

		$out[] = array(
			'title' => $parts[0],
			'text'  => $parts[1] ?? '',
		);
	}

	return $out;
}

/**
 * تب‌های (تبصره‌های) یک الگو.
 *
 * @param int $tpl_id شناسه الگو.
 * @return array
 */
function zc_contract_notes( $tpl_id ) {
	$raw = (string) get_post_meta( $tpl_id, '_zc_ct_notes', true );

	if ( '' === trim( $raw ) ) {
		return array();
	}

	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ) ) );
}

/* ==========================================================================
   داده‌ی یک قرارداد
   ========================================================================== */

/**
 * خواندن داده‌های کامل یک قرارداد.
 *
 * @param int|WP_Post $contract قرارداد.
 * @return array|null
 */
function zc_contract_data( $contract ) {
	$post = get_post( $contract );

	if ( ! $post || 'zc_contract' !== $post->post_type ) {
		return null;
	}

	$tpl_id = (int) get_post_meta( $post->ID, '_zc_ct_tpl', true );
	$data   = get_post_meta( $post->ID, '_zc_ct_data', true );
	$data   = is_array( $data ) ? $data : array();

	$status = (string) get_post_meta( $post->ID, '_zc_ct_status', true );
	$status = $status ? $status : 'pending';

	$statuses = zc_contract_statuses();

	return array(
		'id'         => $post->ID,
		'title'      => $post->post_title,
		'number'     => zc_contract_number( $post->ID ),
		'user_id'    => (int) $post->post_author,
		'tpl_id'     => $tpl_id,
		'data'       => $data,
		'body'       => (string) get_post_meta( $post->ID, '_zc_ct_body', true ),
		'status'     => $status,
		'status_label' => $statuses[ $status ]['label'] ?? $status,
		'status_color' => $statuses[ $status ]['color'] ?? '#8A93A6',
		'signed_at'  => (int) get_post_meta( $post->ID, '_zc_ct_signed_at', true ),
		'signature'  => (string) get_post_meta( $post->ID, '_zc_ct_signature', true ),
		'sign_ip'    => (string) get_post_meta( $post->ID, '_zc_ct_sign_ip', true ),
		'hash'       => (string) get_post_meta( $post->ID, '_zc_ct_hash', true ),
		'amount'     => (float) get_post_meta( $post->ID, '_zc_ct_amount', true ),
		'progress'   => (int) get_post_meta( $post->ID, '_zc_ct_progress', true ),
		'created'    => get_post_time( 'U', false, $post ),
	);
}

/**
 * قراردادهای یک کاربر.
 *
 * @param int $user_id شناسه کاربر.
 * @return array
 */
function zc_user_contracts( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	if ( ! $user_id ) {
		return array();
	}

	$posts = get_posts(
		array(
			'post_type'      => 'zc_contract',
			'author'         => $user_id,
			'posts_per_page' => 100,
			'post_status'    => 'any',
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	return array_filter( array_map( 'zc_contract_data', $posts ) );
}

/**
 * آیا کاربر به این قرارداد دسترسی دارد؟
 *
 * @param int $contract_id شناسه قرارداد.
 * @param int $user_id     شناسه کاربر.
 * @return bool
 */
function zc_can_view_contract( $contract_id, $user_id = 0 ) {
	if ( ! zc_opt( 'zc_contract_enable', true ) && ! current_user_can( 'manage_options' ) ) {
		return false;
	}
	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	if ( ! $user_id ) {
		return false;
	}

	if ( user_can( $user_id, 'manage_options' ) ) {
		return true;
	}

	return (int) get_post_field( 'post_author', $contract_id ) === $user_id;
}

/* ==========================================================================
   ساخت قرارداد
   ========================================================================== */

/**
 * ساخت متن نهایی قرارداد از الگو.
 *
 * @param int   $tpl_id      شناسه الگو.
 * @param array $data        داده‌های کاربر.
 * @param int   $contract_id شناسه قرارداد (برای شماره).
 * @return string HTML
 */
function zc_contract_build_body( $tpl_id, $data, $contract_id = 0 ) {
	$tpl = get_post( $tpl_id );

	if ( ! $tpl ) {
		return '';
	}

	// شناسه الگو برای محاسبه‌ی شناسه‌های پویا لازم است.
	$data['_tpl_id'] = $tpl_id;

	$tokens = zc_contract_tokens( $data, $contract_id );

	$body = zc_contract_parse( $tpl->post_content, $tokens );
	$html = wpautop( wp_kses_post( $body ) );

	$clauses = zc_contract_clauses( $tpl_id );

	if ( $clauses ) {
		$html .= '<h3 class="zc-ct__h">' . esc_html__( 'مواد و بندهای قرارداد', 'zarincode' ) . '</h3><ol class="zc-ct__clauses">';

		foreach ( $clauses as $i => $c ) {
			$html .= '<li><strong>' . esc_html( zc_contract_parse( $c['title'], $tokens ) ) . '</strong>';

			if ( $c['text'] ) {
				$html .= '<p>' . esc_html( zc_contract_parse( $c['text'], $tokens ) ) . '</p>';
			}

			$html .= '</li>';
		}

		$html .= '</ol>';
	}

	$notes = zc_contract_notes( $tpl_id );

	if ( $notes ) {
		$html .= '<div class="zc-ct__notes"><h4>' . esc_html__( 'تبصره‌ها', 'zarincode' ) . '</h4><ul>';

		foreach ( $notes as $n ) {
			$html .= '<li>' . esc_html( zc_contract_parse( $n, $tokens ) ) . '</li>';
		}

		$html .= '</ul></div>';
	}

	return $html;
}

/**
 * تولید کد یک‌بارمصرف امضا و ارسال پیامک.
 *
 * @param int $contract_id شناسه قرارداد.
 * @return bool
 */
function zc_contract_send_otp( $contract_id ) {
	$data = zc_contract_data( $contract_id );

	if ( ! $data ) {
		return false;
	}

	$mobile = $data['data']['mobile'] ?? zc_user_mobile( $data['user_id'] );
	$mobile = zc_sanitize_mobile( $mobile );

	if ( ! $mobile ) {
		return false;
	}

	$code = (string) wp_rand( 10000, 99999 );

	set_transient( 'zc_ct_otp_' . $contract_id, wp_hash_password( $code ), 10 * MINUTE_IN_SECONDS );
	delete_transient( 'zc_ct_otp_tries_' . $contract_id );

	$text = zc_sms_parse_vars(
		zc_sms_message( 'contract_otp' ),
		array(
			'code'   => $code,
			'number' => $data['number'] ?? '',
		)
	);

	return (bool) zc_sms_dispatch( $mobile, $text, 'contract_otp' );
}

/**
 * بررسی کد یک‌بارمصرف.
 *
 * @param int    $contract_id شناسه.
 * @param string $code        کد واردشده.
 * @return bool
 */
function zc_contract_check_otp( $contract_id, $code ) {
	$saved = get_transient( 'zc_ct_otp_' . $contract_id );

	if ( ! $saved ) {
		return false;
	}

	$code  = zc_en_num( trim( (string) $code ) );
	$tries = (int) get_transient( 'zc_ct_otp_tries_' . $contract_id );
	if ( $tries >= 5 || ! wp_check_password( $code, $saved ) ) {
		$tries++;
		set_transient( 'zc_ct_otp_tries_' . $contract_id, $tries, 10 * MINUTE_IN_SECONDS );
		if ( $tries >= 5 ) {
			delete_transient( 'zc_ct_otp_' . $contract_id );
		}
		return false;
	}

	delete_transient( 'zc_ct_otp_' . $contract_id );
	delete_transient( 'zc_ct_otp_tries_' . $contract_id );
	return true;
}

/**
 * اثر انگشت دیجیتال قرارداد.
 *
 * برای اثبات دست‌نخوردگی متن پس از امضا استفاده می‌شود.
 *
 * @param int $contract_id شناسه.
 * @return string
 */
function zc_contract_hash( $contract_id ) {
	$data = zc_contract_data( $contract_id );

	if ( ! $data ) {
		return '';
	}

	return strtoupper(
		substr(
			hash(
				'sha256',
				$data['number'] . '|' . wp_strip_all_tags( $data['body'] ) . '|' . $data['user_id'] . '|' . $data['signed_at']
			),
			0,
			32
		)
	);
}

/** @param int $contract_id قرارداد. @return bool */
function zc_contract_hash_valid( $contract_id ) {
	$saved = (string) get_post_meta( $contract_id, '_zc_ct_hash', true );
	return $saved && hash_equals( $saved, zc_contract_hash( $contract_id ) );
}

/* ==========================================================================
   درخواست‌های آجاکس
   ========================================================================== */

/**
 * ثبت اطلاعات و ساخت پیش‌نویس قرارداد.
 *
 * @return void
 */
function zc_ajax_contract_create() {
	zc_check_ajax();

	if ( ! zc_opt( 'zc_contract_enable', true ) ) {
		wp_send_json_error( array( 'message' => __( 'سامانه قراردادها غیرفعال است.', 'zarincode' ) ) );
	}
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'ابتدا وارد حساب کاربری شوید.', 'zarincode' ) ) );
	}

	$tpl_id = isset( $_POST['tpl'] ) ? absint( $_POST['tpl'] ) : 0;
	$tpl    = get_post( $tpl_id );

	if ( ! $tpl || 'zc_contract_tpl' !== $tpl->post_type ) {
		wp_send_json_error( array( 'message' => __( 'الگوی قرارداد یافت نشد.', 'zarincode' ) ) );
	}

	$fields = zc_contract_fields( $tpl_id );
	$data   = array();

	foreach ( $fields as $key => $field ) {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$raw = isset( $_POST[ 'f_' . $key ] ) ? wp_unslash( $_POST[ 'f_' . $key ] ) : '';

		$value = ( 'textarea' === $field['type'] )
			? sanitize_textarea_field( $raw )
			: sanitize_text_field( $raw );

		if ( ! empty( $field['required'] ) && '' === trim( $value ) ) {
			wp_send_json_error(
				array(
					/* translators: %s: نام فیلد */
					'message' => sprintf( __( 'تکمیل فیلد «%s» الزامی است.', 'zarincode' ), $field['label'] ),
					'field'   => $key,
				)
			);
		}

		// تاریخ شمسی برای ذخیره به میلادی تبدیل می‌شود.
		if ( 'date' === $field['type'] && $value ) {
			$greg = zc_jalali_str_to_gregorian( $value );

			if ( $greg ) {
				$data[ $key . '_g' ] = $greg;
			}
		}

		$data[ $key ] = $value;

		// شناسه‌ی فارسی هم برای متن قرارداد در دسترس باشد.
		if ( ! empty( $field['token'] ) ) {
			$data[ $field['token'] ] = $value;
		}
	}

	if ( ! empty( $data['national_id'] ) && ! zc_valid_national_id( $data['national_id'] ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'کد ملی واردشده معتبر نیست.', 'zarincode' ),
				'field'   => 'national_id',
			)
		);
	}

	$user_id = get_current_user_id();

	$contract_id = wp_insert_post(
		array(
			'post_type'    => 'zc_contract',
			'post_status'  => 'publish',
			'post_author'  => $user_id,
			'post_title'   => $tpl->post_title . ' — ' . ( $data['full_name'] ?? '' ),
		),
		true
	);

	if ( is_wp_error( $contract_id ) ) {
		wp_send_json_error( array( 'message' => $contract_id->get_error_message() ) );
	}

	update_post_meta( $contract_id, '_zc_ct_tpl', $tpl_id );
	update_post_meta( $contract_id, '_zc_ct_data', $data );
	update_post_meta( $contract_id, '_zc_ct_status', 'pending' );
	update_post_meta( $contract_id, '_zc_ct_amount', (float) get_post_meta( $tpl_id, '_zc_ct_amount', true ) );

	zc_contract_number( $contract_id );

	$body = zc_contract_build_body( $tpl_id, $data, $contract_id );
	update_post_meta( $contract_id, '_zc_ct_body', $body );

	wp_send_json_success(
		array(
			'id'      => $contract_id,
			'number'  => zc_contract_number( $contract_id ),
			'body'    => $body,
			'message' => __( 'پیش‌نویس قرارداد آماده شد. لطفاً متن را مطالعه کنید.', 'zarincode' ),
		)
	);
}
add_action( 'wp_ajax_zc_contract_create', 'zc_ajax_contract_create' );

/**
 * ارسال کد تأیید امضا.
 *
 * @return void
 */
function zc_ajax_contract_otp() {
	zc_check_ajax();

	$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

	if ( ! zc_can_view_contract( $id ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی مجاز نیست.', 'zarincode' ) ) );
	}

	// جلوگیری از ارسال پیاپی.
	if ( get_transient( 'zc_ct_otp_wait_' . $id ) ) {
		wp_send_json_error( array( 'message' => __( 'برای ارسال دوباره کمی صبر کنید.', 'zarincode' ) ) );
	}

	if ( ! zc_contract_send_otp( $id ) ) {
		wp_send_json_error( array( 'message' => __( 'ارسال پیامک ناموفق بود. شماره موبایل را بررسی کنید.', 'zarincode' ) ) );
	}

	set_transient( 'zc_ct_otp_wait_' . $id, 1, 2 * MINUTE_IN_SECONDS );

	wp_send_json_success(
		array(
			'message' => __( 'کد تأیید به شماره‌ی شما پیامک شد.', 'zarincode' ),
			'wait'    => 120,
		)
	);
}
add_action( 'wp_ajax_zc_contract_otp', 'zc_ajax_contract_otp' );

/**
 * امضای نهایی قرارداد.
 *
 * @return void
 */
function zc_ajax_contract_sign() {
	zc_check_ajax();

	$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

	if ( ! zc_can_view_contract( $id ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی مجاز نیست.', 'zarincode' ) ) );
	}

	$data = zc_contract_data( $id );

	if ( ! $data ) {
		wp_send_json_error( array( 'message' => __( 'قرارداد یافت نشد.', 'zarincode' ) ) );
	}

	if ( 'pending' !== $data['status'] ) {
		wp_send_json_error( array( 'message' => __( 'این قرارداد پیش‌تر امضا شده است.', 'zarincode' ) ) );
	}

	$code = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';

	if ( ! zc_contract_check_otp( $id, $code ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'کد تأیید نادرست یا منقضی شده است.', 'zarincode' ),
				'field'   => 'code',
			)
		);
	}

	// امضای کشیده‌شده به صورت data-uri.
	$sig = isset( $_POST['signature'] ) ? wp_unslash( $_POST['signature'] ) : ''; // phpcs:ignore

	if ( ! preg_match( '#^data:image/png;base64,[A-Za-z0-9+/=]+$#', (string) $sig ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'لطفاً امضای خود را در کادر رسم کنید.', 'zarincode' ),
				'field'   => 'signature',
			)
		);
	}

	if ( strlen( $sig ) > 400000 ) {
		wp_send_json_error( array( 'message' => __( 'حجم تصویر امضا زیاد است.', 'zarincode' ) ) );
	}

	$now = current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp

	update_post_meta( $id, '_zc_ct_signature', $sig );
	update_post_meta( $id, '_zc_ct_signed_at', $now );
	update_post_meta( $id, '_zc_ct_sign_ip', zc_user_ip() );
	update_post_meta( $id, '_zc_ct_status', 'signed' );
	update_post_meta( $id, '_zc_ct_hash', zc_contract_hash( $id ) );

	// اطلاع‌رسانی به کاربر و مدیران.
	zc_notify_user(
		$data['user_id'],
		'contract',
		sprintf(
			/* translators: %s: شماره قرارداد */
			__( 'قرارداد %s با موفقیت امضا شد.', 'zarincode' ),
			$data['number']
		)
	);

	if ( function_exists( 'zc_notify_admins' ) ) {
		zc_notify_admins(
			sprintf(
				/* translators: 1: شماره قرارداد 2: نام کاربر */
				__( "قرارداد تازه امضا شد\nشماره: %1\$s\nمشتری: %2\$s", 'zarincode' ),
				$data['number'],
				$data['data']['full_name'] ?? ''
			)
		);
	}

	do_action( 'zc_contract_signed', $id, $data );

	wp_send_json_success(
		array(
			'message'  => __( 'قرارداد با موفقیت امضا و ثبت شد.', 'zarincode' ),
			'redirect' => add_query_arg(
				array(
					'tab'      => 'contracts',
					'contract' => $id,
				),
				zc_panel_url()
			),
		)
	);
}
add_action( 'wp_ajax_zc_contract_sign', 'zc_ajax_contract_sign' );

/* ==========================================================================
   نسخه‌ی قابل دانلود
   ========================================================================== */

/**
 * خروجی نسخه‌ی چاپی/دانلودی قرارداد.
 *
 * از قالب template-parts/contract-print.php استفاده می‌کند و با
 * دستور چاپ مرورگر به PDF تبدیل می‌شود؛ این روش نیازی به کتابخانه‌ی
 * سنگین PDF ندارد و از فارسی و راست‌چین کامل پشتیبانی می‌کند.
 *
 * @return void
 */
function zc_contract_download() {
	if ( ! isset( $_GET['zc_contract'] ) ) {
		return;
	}

	$id = absint( $_GET['zc_contract'] );

	/*
	 * این صفحه فقط‌خواندنی است و دسترسی با بررسی مالکیت کنترل می‌شود،
	 * نه با nonce؛ چون nonce به نشست گره خورده و پس از ۲۴ ساعت باطل
	 * می‌شود و لینک ذخیره‌شده‌ی کاربر از کار می‌افتد.
	 */
	if ( ! zc_can_view_contract( $id ) ) {
		wp_die( esc_html__( 'دسترسی مجاز نیست.', 'zarincode' ) );
	}

	$contract = zc_contract_data( $id );

	if ( ! $contract ) {
		wp_die( esc_html__( 'قرارداد یافت نشد.', 'zarincode' ) );
	}

	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow', true );
	header( 'Content-Security-Policy: frame-ancestors \'self\'' );
	// phpcs:ignore WordPress.PHP.DontExtract
	include ZC_DIR . 'template-parts/contract-print.php';
	exit;
}
add_action( 'template_redirect', 'zc_contract_download' );

/**
 * نشانی دانلود امن قرارداد.
 *
 * @param int $id شناسه.
 * @return string
 */
function zc_contract_download_url( $id ) {
	return add_query_arg( 'zc_contract', (int) $id, home_url( '/' ) );
}

/* ==========================================================================
   متاباکس‌های پیشخوان
   ========================================================================== */

/**
 * افزودن متاباکس‌های الگو و قرارداد.
 *
 * @return void
 */
function zc_contract_metaboxes() {
	add_meta_box(
		'zc-ct-tpl',
		__( 'تنظیمات الگوی قرارداد', 'zarincode' ),
		'zc_contract_tpl_box',
		'zc_contract_tpl',
		'normal',
		'high'
	);

	add_meta_box(
		'zc-ct-tokens',
		__( 'شناسه‌های قابل استفاده', 'zarincode' ),
		'zc_contract_tokens_box',
		'zc_contract_tpl',
		'side',
		'default'
	);

	add_meta_box(
		'zc-ct-view',
		__( 'جزئیات قرارداد', 'zarincode' ),
		'zc_contract_view_box',
		'zc_contract',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'zc_contract_metaboxes' );

/**
 * متاباکس تنظیمات الگو.
 *
 * @param WP_Post $post پست.
 * @return void
 */
function zc_contract_tpl_box( $post ) {
	wp_nonce_field( 'zc_ct_save', 'zc_ct_nonce' );

	$fields  = (string) get_post_meta( $post->ID, '_zc_ct_fields', true );
	$clauses = (string) get_post_meta( $post->ID, '_zc_ct_clauses', true );
	$notes   = (string) get_post_meta( $post->ID, '_zc_ct_notes', true );
	$amount  = (string) get_post_meta( $post->ID, '_zc_ct_amount', true );
	$logo    = (string) get_post_meta( $post->ID, '_zc_ct_logo', true );
	$active  = get_post_meta( $post->ID, '_zc_ct_public', true );
	$active  = ( '' === $active ) ? 1 : (int) $active;
	?>
	<style>
		.zc-ctbox p{margin:0 0 6px}
		.zc-ctbox textarea{width:100%;font-family:inherit;line-height:1.9;direction:rtl}
		.zc-ctbox .desc{color:#666;font-size:12px;margin:4px 0 16px;line-height:1.8}
		.zc-ctbox h4{margin:18px 0 6px;font-size:14px}
		.zc-ctbox code{background:#f2f4f7;padding:1px 5px;border-radius:4px;direction:ltr;display:inline-block}
	</style>

	<div class="zc-ctbox">
		<h4><?php esc_html_e( 'فیلدهای اطلاعاتی مشتری', 'zarincode' ); ?></h4>
		<textarea name="zc_ct_fields" rows="6" placeholder="project_name | نام پروژه | text | 1"><?php echo esc_textarea( $fields ); ?></textarea>
		<p class="desc">
			<?php esc_html_e( 'هر خط یک فیلد سفارشی است با ساختار:', 'zarincode' ); ?>
			<code>شناسه | برچسب | نوع | الزامی | گزینه‌ها</code><br>
			<?php esc_html_e( 'نوع‌های مجاز: text، textarea، number، date، tel، email، select — برای الزامی بودن عدد ۱ بگذارید. گزینه‌های select را با «،» جدا کنید.', 'zarincode' ); ?><br>
			<?php esc_html_e( 'فیلدهای نام، کد ملی، موبایل، ایمیل و نشانی به‌صورت پیش‌فرض وجود دارند.', 'zarincode' ); ?>
		</p>

		<h4><?php esc_html_e( 'بندهای قرارداد', 'zarincode' ); ?></h4>
		<textarea name="zc_ct_clauses" rows="7" placeholder="موضوع قرارداد | طراحی و پیاده‌سازی وب‌سایت {نام_پروژه}"><?php echo esc_textarea( $clauses ); ?></textarea>
		<p class="desc">
			<?php esc_html_e( 'هر خط یک بند با ساختار «عنوان | متن». شناسه‌ها داخل متن قابل استفاده‌اند.', 'zarincode' ); ?>
		</p>

		<h4><?php esc_html_e( 'تبصره‌ها', 'zarincode' ); ?></h4>
		<textarea name="zc_ct_notes" rows="4"><?php echo esc_textarea( $notes ); ?></textarea>
		<p class="desc"><?php esc_html_e( 'هر خط یک تبصره.', 'zarincode' ); ?></p>

		<h4><?php esc_html_e( 'مبلغ قرارداد (تومان)', 'zarincode' ); ?></h4>
		<p><input type="number" name="zc_ct_amount" value="<?php echo esc_attr( $amount ); ?>" class="regular-text" min="0" step="1000"></p>
		<p class="desc"><?php esc_html_e( 'صفر یعنی مبلغ در قرارداد ذکر نمی‌شود.', 'zarincode' ); ?></p>

		<h4><?php esc_html_e( 'لوگوی اختصاصی این قرارداد', 'zarincode' ); ?></h4>
		<p>
			<input type="url" name="zc_ct_logo" value="<?php echo esc_url( $logo ); ?>" class="large-text" dir="ltr"
				placeholder="<?php esc_attr_e( 'نشانی تصویر لوگو', 'zarincode' ); ?>">
		</p>
		<p class="desc"><?php esc_html_e( 'خالی بگذارید تا لوگوی اصلی سایت استفاده شود.', 'zarincode' ); ?></p>

		<h4><?php esc_html_e( 'نمایش در پنل کاربران', 'zarincode' ); ?></h4>
		<p>
			<label>
				<input type="checkbox" name="zc_ct_public" value="1" <?php checked( $active, 1 ); ?>>
				<?php esc_html_e( 'کاربران بتوانند از این الگو قرارداد جدید بسازند.', 'zarincode' ); ?>
			</label>
		</p>
	</div>
	<?php
}

/**
 * متاباکس راهنمای شناسه‌ها.
 *
 * @param WP_Post $post پست.
 * @return void
 */
function zc_contract_tokens_box( $post ) {
	$tokens = array_keys( zc_contract_tokens() );

	foreach ( zc_contract_base_fields() as $f ) {
		$tokens[] = $f['token'];
	}

	foreach ( zc_contract_custom_fields( $post->ID ) as $f ) {
		$tokens[] = $f['token'];
	}

	$tokens = array_values( array_unique( array_filter( $tokens ) ) );
	?>
	<p style="font-size:12px;color:#666;line-height:1.8">
		<?php esc_html_e( 'این شناسه‌ها را داخل متن اصلی، بندها و تبصره‌ها بنویسید تا هنگام ساخت قرارداد با مقدار واقعی جایگزین شوند.', 'zarincode' ); ?>
	</p>
	<div style="display:flex;flex-wrap:wrap;gap:5px">
		<?php foreach ( $tokens as $t ) : ?>
			<code style="background:#f2f4f7;padding:3px 7px;border-radius:5px;font-size:11.5px;cursor:copy"
				onclick="navigator.clipboard&&navigator.clipboard.writeText(this.textContent)">{<?php echo esc_html( $t ); ?>}</code>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * متاباکس مشاهده‌ی قرارداد صادرشده.
 *
 * @param WP_Post $post پست.
 * @return void
 */
function zc_contract_view_box( $post ) {
	$c = zc_contract_data( $post->ID );

	if ( ! $c ) {
		return;
	}

	wp_nonce_field( 'zc_ct_save', 'zc_ct_nonce' );

	$statuses = zc_contract_statuses();
	?>
	<table class="widefat striped" style="margin-bottom:14px">
		<tbody>
			<tr>
				<th style="width:170px"><?php esc_html_e( 'شماره قرارداد', 'zarincode' ); ?></th>
				<td><strong dir="ltr"><?php echo esc_html( $c['number'] ); ?></strong></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></th>
				<td>
					<select name="zc_ct_status">
						<?php foreach ( $statuses as $k => $v ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $c['status'], $k ); ?>>
								<?php echo esc_html( $v['label'] ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'درصد پیشرفت', 'zarincode' ); ?></th>
				<td>
					<input type="number" name="zc_ct_progress" min="0" max="100"
						value="<?php echo esc_attr( $c['progress'] ); ?>" style="width:90px"> ٪
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'تاریخ امضا', 'zarincode' ); ?></th>
				<td>
					<?php
					echo $c['signed_at']
						? esc_html( zc_jalali_date( 'j F Y — H:i', $c['signed_at'] ) )
						: '—';
					?>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'آی‌پی امضاکننده', 'zarincode' ); ?></th>
				<td dir="ltr"><?php echo esc_html( $c['sign_ip'] ? $c['sign_ip'] : '—' ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'اثر انگشت دیجیتال', 'zarincode' ); ?></th>
				<td><code dir="ltr" style="font-size:11px"><?php echo esc_html( $c['hash'] ? $c['hash'] : '—' ); ?></code></td>
			</tr>
			<?php foreach ( $c['data'] as $key => $value ) : ?>
				<?php if ( is_string( $value ) && '' !== $value && '_' !== $key[0] && ! preg_match( '/_g$/', $key ) && preg_match( '/^[a-z_]+$/', $key ) ) : ?>
					<tr>
						<th><?php echo esc_html( $key ); ?></th>
						<td><?php echo esc_html( $value ); ?></td>
					</tr>
				<?php endif; ?>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php if ( $c['signature'] ) : ?>
		<p><strong><?php esc_html_e( 'امضای دیجیتال:', 'zarincode' ); ?></strong></p>
		<img src="<?php echo esc_attr( $c['signature'] ); ?>" alt="" style="max-width:260px;border:1px solid #ddd;border-radius:8px;background:#fff">
	<?php endif; ?>

	<p style="margin-top:14px">
		<a href="<?php echo esc_url( zc_contract_download_url( $c['id'] ) ); ?>" class="button" target="_blank">
			<?php esc_html_e( 'مشاهده‌ی نسخه‌ی چاپی', 'zarincode' ); ?>
		</a>
	</p>

	<div style="border:1px solid #e2e4e7;border-radius:8px;padding:16px;margin-top:14px;background:#fff;max-height:400px;overflow:auto">
		<?php echo wp_kses_post( $c['body'] ); ?>
	</div>
	<?php
}

/**
 * ذخیره‌ی متاباکس‌ها.
 *
 * @param int $post_id شناسه پست.
 * @return void
 */
function zc_contract_save_meta( $post_id ) {
	if ( ! isset( $_POST['zc_ct_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['zc_ct_nonce'] ) ), 'zc_ct_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$textareas = array( 'fields', 'clauses', 'notes' );

	foreach ( $textareas as $key ) {
		if ( isset( $_POST[ 'zc_ct_' . $key ] ) ) {
			update_post_meta(
				$post_id,
				'_zc_ct_' . $key,
				sanitize_textarea_field( wp_unslash( $_POST[ 'zc_ct_' . $key ] ) )
			);
		}
	}

	if ( isset( $_POST['zc_ct_amount'] ) ) {
		update_post_meta( $post_id, '_zc_ct_amount', (float) $_POST['zc_ct_amount'] );
	}

	if ( isset( $_POST['zc_ct_logo'] ) ) {
		update_post_meta( $post_id, '_zc_ct_logo', esc_url_raw( wp_unslash( $_POST['zc_ct_logo'] ) ) );
	}

	if ( 'zc_contract_tpl' === get_post_type( $post_id ) ) {
		update_post_meta( $post_id, '_zc_ct_public', isset( $_POST['zc_ct_public'] ) ? 1 : 0 );
	}

	if ( isset( $_POST['zc_ct_status'] ) ) {
		$status   = sanitize_text_field( wp_unslash( $_POST['zc_ct_status'] ) );
		$statuses = zc_contract_statuses();

		if ( isset( $statuses[ $status ] ) ) {
			$old = (string) get_post_meta( $post_id, '_zc_ct_status', true );

			update_post_meta( $post_id, '_zc_ct_status', $status );

			// آگاه‌سازی کاربر در صورت تغییر وضعیت.
			if ( $old && $old !== $status ) {
				zc_notify_user(
					(int) get_post_field( 'post_author', $post_id ),
					'contract',
					sprintf(
						/* translators: 1: شماره قرارداد 2: وضعیت */
						__( 'وضعیت قرارداد %1$s به «%2$s» تغییر کرد.', 'zarincode' ),
						zc_contract_number( $post_id ),
						$statuses[ $status ]['label']
					)
				);
			}
		}
	}

	if ( isset( $_POST['zc_ct_progress'] ) ) {
		update_post_meta( $post_id, '_zc_ct_progress', max( 0, min( 100, (int) $_POST['zc_ct_progress'] ) ) );
	}
}
add_action( 'save_post', 'zc_contract_save_meta' );

/**
 * ستون‌های سفارشی فهرست قراردادها.
 *
 * @param array $cols ستون‌ها.
 * @return array
 */
function zc_contract_columns( $cols ) {
	return array(
		'cb'       => $cols['cb'] ?? '',
		'title'    => __( 'قرارداد', 'zarincode' ),
		'zc_num'   => __( 'شماره', 'zarincode' ),
		'zc_user'  => __( 'مشتری', 'zarincode' ),
		'zc_state' => __( 'وضعیت', 'zarincode' ),
		'zc_prog'  => __( 'پیشرفت', 'zarincode' ),
		'zc_date'  => __( 'تاریخ امضا', 'zarincode' ),
	);
}
add_filter( 'manage_zc_contract_posts_columns', 'zc_contract_columns' );

/**
 * محتوای ستون‌ها.
 *
 * @param string $col ستون.
 * @param int    $id  شناسه.
 * @return void
 */
function zc_contract_column_content( $col, $id ) {
	$c = zc_contract_data( $id );

	if ( ! $c ) {
		return;
	}

	switch ( $col ) {
		case 'zc_num':
			echo '<code dir="ltr">' . esc_html( $c['number'] ) . '</code>';
			break;

		case 'zc_user':
			$user = get_userdata( $c['user_id'] );
			echo esc_html( $c['data']['full_name'] ?? ( $user ? $user->display_name : '—' ) );
			break;

		case 'zc_state':
			printf(
				'<span style="display:inline-block;padding:2px 10px;border-radius:20px;color:#fff;font-size:11.5px;background:%s">%s</span>',
				esc_attr( $c['status_color'] ),
				esc_html( $c['status_label'] )
			);
			break;

		case 'zc_prog':
			echo esc_html( zc_fa_num( $c['progress'] ) . '٪' );
			break;

		case 'zc_date':
			echo $c['signed_at'] ? esc_html( zc_fa_num( zc_jalali_date( 'Y/m/d', $c['signed_at'] ) ) ) : '—';
			break;
	}
}
add_action( 'manage_zc_contract_posts_custom_column', 'zc_contract_column_content', 10, 2 );
