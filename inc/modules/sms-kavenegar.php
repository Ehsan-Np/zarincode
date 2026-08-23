<?php
/**
 * ماژول پنل پیامک کاوه‌نگار
 * پشتیبانی از ارسال OTP، الگو (Verify Lookup) و پیامک عادی
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * کلاس سرویس پیامک کاوه‌نگار.
 */
class ZC_Kavenegar {

	/**
	 * کلید API.
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * شماره فرستنده.
	 *
	 * @var string
	 */
	private $sender;

	/**
	 * سازنده.
	 */
	public function __construct() {
		$this->api_key = trim( (string) zc_opt( 'zc_kavenegar_api', '' ) );
		$this->sender  = trim( (string) zc_opt( 'zc_kavenegar_sender', '' ) );
	}

	/**
	 * آیا سرویس فعال است؟
	 *
	 * @return bool
	 */
	public function is_ready() {
		return ! empty( $this->api_key ) && zc_opt( 'zc_sms_enable', true );
	}

	/**
	 * ارسال درخواست به API کاوه‌نگار.
	 *
	 * @param string $method متد.
	 * @param array  $params پارامترها.
	 * @return array|WP_Error
	 */
	private function request( $method, $params ) {
		if ( ! $this->is_ready() ) {
			return new WP_Error( 'zc_sms_disabled', __( 'سرویس پیامک پیکربندی نشده است.', 'zarincode' ) );
		}

		$url = sprintf(
			'https://api.kavenegar.com/v1/%s/%s.json',
			rawurlencode( $this->api_key ),
			$method
		);

		$response = wp_remote_post(
			$url,
			array(
				'timeout'   => 20,
				'body'      => $params,
				'sslverify' => true,
			)
		);

		if ( is_wp_error( $response ) ) {
			zc_log( $response->get_error_message(), 'Kavenegar' );
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== (int) $code || ! isset( $body['return']['status'] ) || 200 !== (int) $body['return']['status'] ) {
			$msg = $body['return']['message'] ?? __( 'خطا در ارسال پیامک.', 'zarincode' );
			zc_log( $msg, 'Kavenegar' );
			return new WP_Error( 'zc_sms_failed', $msg );
		}

		return $body;
	}

	/**
	 * ارسال پیامک ساده.
	 *
	 * @param string $receptor گیرنده.
	 * @param string $message  متن.
	 * @return array|WP_Error
	 */
	public function send( $receptor, $message ) {
		return $this->request(
			'sms/send',
			array(
				'receptor' => $receptor,
				'sender'   => $this->sender,
				'message'  => $message,
			)
		);
	}

	/**
	 * ارسال با الگو (Verify Lookup) — برای کد یکبار مصرف.
	 *
	 * @param string $receptor گیرنده.
	 * @param string $token    توکن اول.
	 * @param string $template الگو.
	 * @param string $token2   توکن دوم.
	 * @param string $token3   توکن سوم.
	 * @return array|WP_Error
	 */
	public function verify_lookup( $receptor, $token, $template = '', $token2 = '', $token3 = '' ) {
		$template = $template ? $template : zc_opt( 'zc_kavenegar_template', 'verify' );

		$params = array(
			'receptor' => $receptor,
			'token'    => $token,
			'template' => $template,
		);

		if ( $token2 ) {
			$params['token2'] = $token2;
		}
		if ( $token3 ) {
			$params['token3'] = $token3;
		}

		return $this->request( 'verify/lookup', $params );
	}

	/**
	 * ارسال گروهی.
	 *
	 * @param array  $receptors گیرندگان.
	 * @param string $message   متن.
	 * @return array|WP_Error
	 */
	public function send_bulk( $receptors, $message ) {
		return $this->request(
			'sms/send',
			array(
				'receptor' => implode( ',', (array) $receptors ),
				'sender'   => $this->sender,
				'message'  => $message,
			)
		);
	}

	/**
	 * دریافت اطلاعات حساب (اعتبار) از کاوه‌نگار.
	 *
	 * @return array|WP_Error {
	 *     float $balance      موجودی (ریال).
	 *     float $min_credit   حداقل موجودی.
	 * }
	 */
	public function account_info() {
		$result = $this->request( 'account/info', array() );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$entries = $result['entries'][0] ?? array();

		return array(
			'balance'    => (float) ( $entries['balance'] ?? 0 ),
			'min_credit' => (float) ( $entries['min_credit'] ?? 0 ),
		);
	}

	/**
	 * برآورد هزینه‌ی ارسال پیامک بر اساس طول پیام و تعداد گیرنده.
	 * قیمت هر پیامک از تنظیم (zc_sms_cost_per_sms) گرفته می‌شود؛ پس از هر
	 * ارسال واقعی، از هزینه‌ی بازگشتیِ کاوه‌نگار برای برآوردهای بعدی به‌روز می‌شود.
	 *
	 * @param string $text  متن پیامک.
	 * @param int    $count تعداد گیرنده.
	 * @return int  هزینه‌ی برآوردی (ریال).
	 */
	public function estimate_cost( $text, $count = 1 ) {
		$text   = (string) $text;
		$count  = max( 1, (int) $count );

		// هر پیامک فارسی ~۷۰ کاراکتر؛ برای متن ASCII ۱۶۰ کاراکتر.
		$is_persian = preg_match( '/[\x{0600}-\x{06FF}]/u', $text );
		$per_part   = $is_persian ? 70 : 160;
		$parts      = max( 1, (int) ceil( mb_strlen( $text ) / $per_part ) );

		$cost_per_part = max( 0, (float) zc_opt( 'zc_sms_cost_per_sms', 300 ) );

		return (int) ceil( $cost_per_part * $parts * $count );
	}
}

/**
 * دسترسی سریع به سرویس پیامک.
 *
 * @return ZC_Kavenegar
 */
function zc_sms() {
	static $instance = null;
	if ( null === $instance ) {
		$instance = new ZC_Kavenegar();
	}
	return $instance;
}

/**
 * دسترسی به موجودی حساب کاوه‌نگار.
 *
 * @return array|WP_Error {
 *     float $balance      موجودی (ریال).
 *     float $min_credit   حداقل موجودی.
 *     bool  $sufficient   آیا برای هزینه‌ی برآوردی کافی است؟
 *     int   $estimate     هزینه‌ی برآوردی.
 * }
 */
function zc_kavenegar_balance( $text = '', $count = 1 ) {
	if ( ! zc_sms()->is_ready() ) {
		return new WP_Error( 'zc_sms_not_ready', __( 'سرویس پیامک پیکربندی نشده است.', 'zarincode' ) );
	}

	$info = zc_sms()->account_info();
	if ( is_wp_error( $info ) ) {
		return $info;
	}

	$estimate = $text ? zc_sms()->estimate_cost( $text, $count ) : 0;

	return array(
		'balance'    => $info['balance'],
		'min_credit' => $info['min_credit'],
		'estimate'   => $estimate,
		'sufficient' => $info['balance'] >= $estimate,
	);
}

/**
 * بررسی اینکه آیا موجودی برای ارسال پیامک کافی است.
 *
 * @param string $text  متن.
 * @param int    $count تعداد گیرنده.
 * @return array { bool $ok, string $message }
 */
function zc_sms_check_credit( $text, $count = 1 ) {
	$balance = zc_kavenegar_balance( $text, $count );

	if ( is_wp_error( $balance ) ) {
		return array(
			'ok'      => false,
			'message' => $balance->get_error_message(),
			'balance' => 0,
			'estimate'=> 0,
		);
	}

	if ( $balance['sufficient'] ) {
		return array(
			'ok'       => true,
			'message'  => sprintf(
				/* translators: 1: cost 2: balance */
				__( 'هزینه‌ی برآوردی %1$s ریال است و موجودی شما %2$s ریال می‌باشد.', 'zarincode' ),
				zc_fa_num( number_format( $balance['estimate'] ) ),
				zc_fa_num( number_format( $balance['balance'] ) )
			),
			'balance'  => $balance['balance'],
			'estimate' => $balance['estimate'],
		);
	}

	return array(
		'ok'       => false,
		'message'  => sprintf(
			/* translators: 1: cost 2: balance */
			__( 'اعتبار کافی نیست. هزینه‌ی برآوردی %1$s ریال و موجودی %2$s ریال است؛ لطفاً حساب کاوه‌نگار را شارژ کنید.', 'zarincode' ),
			zc_fa_num( number_format( $balance['estimate'] ) ),
			zc_fa_num( number_format( $balance['balance'] ) )
		),
		'balance'  => $balance['balance'],
		'estimate' => $balance['estimate'],
	);
}

/**
 * تولید و ارسال کد یکبار مصرف.
 *
 * @param string $mobile موبایل.
 * @return array|WP_Error
 */
function zc_send_otp( $mobile ) {
	$mobile = zc_sanitize_mobile( $mobile );

	if ( ! $mobile ) {
		return new WP_Error( 'zc_invalid_mobile', __( 'شماره موبایل معتبر نیست.', 'zarincode' ) );
	}
	if ( ! zc_rate_limit( 'otp_send', 20, HOUR_IN_SECONDS ) ) {
		return new WP_Error( 'zc_otp_ip_limit', __( 'تعداد درخواست کد از این اتصال بیش از حد مجاز است.', 'zarincode' ) );
	}

	// محدودیت ارسال (Rate Limit).
	$lock_key = 'zc_otp_lock_' . md5( $mobile );
	if ( get_transient( $lock_key ) ) {
		return new WP_Error( 'zc_otp_wait', __( 'لطفاً کمی صبر کنید و سپس دوباره تلاش کنید.', 'zarincode' ) );
	}

	// محدودیت تعداد در ساعت.
	$count_key = 'zc_otp_count_' . md5( $mobile );
	$count     = (int) get_transient( $count_key );
	if ( $count >= (int) zc_opt( 'zc_otp_hourly_limit', 5 ) ) {
		return new WP_Error( 'zc_otp_limit', __( 'تعداد درخواست‌های شما بیش از حد مجاز است. یک ساعت دیگر تلاش کنید.', 'zarincode' ) );
	}

	$code   = (string) wp_rand( 10000, 99999 );
	$expire = (int) zc_opt( 'zc_otp_expire', 120 );

	set_transient( 'zc_otp_' . md5( $mobile ), wp_hash_password( $code ), $expire );
	delete_transient( 'zc_otp_tries_' . md5( $mobile ) );
	set_transient( $lock_key, 1, (int) zc_opt( 'zc_otp_resend', 60 ) );
	set_transient( $count_key, $count + 1, HOUR_IN_SECONDS );

	// حالت تست: کد در لاگ ثبت می‌شود.
	if ( zc_opt( 'zc_sms_test_mode', false ) ) {
		zc_log( 'OTP for ' . $mobile . ' = ' . $code, 'ZC-OTP' );
		return array( 'test' => true, 'code' => $code );
	}

	$template = zc_opt( 'zc_kavenegar_template', '' );

	if ( $template ) {
		$result = zc_sms()->verify_lookup( $mobile, $code, $template );
	} else {
		$text = zc_sms_parse_vars(
			zc_sms_message( 'otp' ),
			array( 'code' => $code )
		);
		$result = zc_sms()->send( $mobile, $text );
	}

	if ( is_wp_error( $result ) ) {
		delete_transient( 'zc_otp_' . md5( $mobile ) );
		delete_transient( $lock_key );
		return $result;
	}

	return array( 'sent' => true, 'expire' => $expire );
}

/**
 * بررسی صحت کد یکبار مصرف.
 *
 * @param string $mobile موبایل.
 * @param string $code   کد.
 * @return bool
 */
function zc_verify_otp( $mobile, $code ) {
	$mobile = zc_sanitize_mobile( $mobile );
	if ( ! $mobile ) {
		return false;
	}

	$key   = md5( $mobile );
	$hash  = get_transient( 'zc_otp_' . $key );
	$tries = (int) get_transient( 'zc_otp_tries_' . $key );
	if ( ! $hash || $tries >= 5 ) {
		return false;
	}

	require_once ABSPATH . 'wp-includes/class-phpass.php';
	$hasher = new PasswordHash( 8, true );

	if ( $hasher->CheckPassword( zc_en_num( $code ), $hash ) ) {
		delete_transient( 'zc_otp_' . $key );
		delete_transient( 'zc_otp_tries_' . $key );
		return true;
	}

	$tries++;
	set_transient( 'zc_otp_tries_' . $key, $tries, 15 * MINUTE_IN_SECONDS );
	if ( $tries >= 5 ) { delete_transient( 'zc_otp_' . $key ); }
	return false;
}

/**
 * ارسال پیامک اطلاع‌رسانی به کاربر.
 *
 * @param int    $user_id کاربر.
 * @param string $message متن.
 * @return bool
 */
function zc_notify_user_sms( $user_id, $message ) {
	$mobile = get_user_meta( $user_id, 'zc_mobile', true );
	if ( ! $mobile || ! zc_sms()->is_ready() ) {
		return false;
	}
	$result = zc_sms()->send( $mobile, $message );
	return ! is_wp_error( $result );
}

/**
 * پیامک پس از ثبت سفارش موفق.
 *
 * @param int $order_id شناسه سفارش.
 * @return void
 */
function zc_sms_order_completed( $order_id ) {
	if ( ! zc_opt( 'zc_sms_order_notify', true ) || ! function_exists( 'wc_get_order' ) ) {
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$mobile = $order->get_billing_phone();
	$mobile = zc_sanitize_mobile( $mobile );
	if ( ! $mobile ) {
		return;
	}

	$name  = $order->get_billing_first_name() ? $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() : '';
	$ref   = $order->get_transaction_id() ? $order->get_transaction_id() : '';

	zc_sms_send_message(
		'order_paid',
		$mobile,
		array(
			'name'  => trim( $name ),
			'order' => $order->get_order_number(),
			'ref'   => $ref,
		)
	);
}
add_action( 'woocommerce_order_status_completed', 'zc_sms_order_completed' );

/**
 * پیامک پس از ثبت سفارش جدید (وضعیت pending / on-hold).
 *
 * @param int $order_id شناسه سفارش.
 * @return void
 */
function zc_sms_order_created( $order_id ) {
	if ( ! zc_opt( 'zc_sms_order_notify', true ) || ! function_exists( 'wc_get_order' ) ) {
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$mobile = zc_sanitize_mobile( $order->get_billing_phone() );
	if ( ! $mobile ) {
		return;
	}

	$name = $order->get_billing_first_name() ? $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() : '';

	zc_sms_send_message(
		'order_new',
		$mobile,
		array(
			'name'  => trim( $name ),
			'order' => $order->get_order_number(),
		)
	);
}
add_action( 'woocommerce_new_order', 'zc_sms_order_created', 20 );
add_action( 'woocommerce_checkout_order_processed', 'zc_sms_order_created', 30 );

/**
 * پیامک پس از لغو/ناموفق شدن سفارش.
 *
 * @param int $order_id شناسه سفارش.
 * @return void
 */
function zc_sms_order_failed( $order_id ) {
	if ( ! zc_opt( 'zc_sms_order_notify', true ) || ! function_exists( 'wc_get_order' ) ) {
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$mobile = zc_sanitize_mobile( $order->get_billing_phone() );
	if ( ! $mobile ) {
		return;
	}

	$name = $order->get_billing_first_name() ? $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() : '';

	zc_sms_send_message(
		'order_failed',
		$mobile,
		array(
			'name'  => trim( $name ),
			'order' => $order->get_order_number(),
		)
	);
}
add_action( 'woocommerce_order_status_failed', 'zc_sms_order_failed' );
add_action( 'woocommerce_order_status_cancelled', 'zc_sms_order_failed' );

/**
 * ای‌جکس ارسال کد.
 *
 * @return void
 */
function zc_ajax_send_otp() {
	zc_check_ajax();

	$mobile = isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : '';
	$result = zc_send_otp( $mobile );

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	}

	wp_send_json_success(
		array(
			'message' => __( 'کد تایید برای شما ارسال شد.', 'zarincode' ),
			'expire'  => zc_opt( 'zc_otp_resend', 60 ),
			'mobile'  => zc_sanitize_mobile( $mobile ),
		)
	);
}
add_action( 'wp_ajax_nopriv_zc_send_otp', 'zc_ajax_send_otp' );
add_action( 'wp_ajax_zc_send_otp', 'zc_ajax_send_otp' );

/**
 * بررسی خودکار موجودی کاوه‌نگار و هشدار شارژ (با کش برای کاهش درخواست API).
 *
 * @return array|WP_Error {
 *     float $balance
 *     float $threshold
 *     bool  $low
 * }
 */
function zc_kavenegar_balance_check() {
	$cached = get_transient( 'zc_kavenegar_balance' );
	if ( false !== $cached && is_array( $cached ) ) {
		return $cached;
	}

	$threshold = max( 0, (float) zc_opt( 'zc_sms_low_balance', 50000 ) );
	$info      = zc_kavenegar_balance();

	if ( is_wp_error( $info ) ) {
		return $info;
	}

	$result = array(
		'balance'   => $info['balance'],
		'threshold' => $threshold,
		'low'       => $info['balance'] < $threshold,
	);

	set_transient( 'zc_kavenegar_balance', $result, 6 * HOUR_IN_SECONDS );

	return $result;
}

/**
 * هشدار مدیریتی برای موجودی پایین کاوه‌نگار.
 *
 * @return void
 */
function zc_kavenegar_low_balance_notice() {
	if ( ! current_user_can( 'manage_options' ) || is_network_admin() ) {
		return;
	}
	if ( ! function_exists( 'zc_sms' ) || ! zc_sms()->is_ready() ) {
		return;
	}
	if ( ! zc_opt( 'zc_sms_balance_alert', true ) ) {
		return;
	}

	$check = zc_kavenegar_balance_check();
	if ( is_wp_error( $check ) || empty( $check['low'] ) ) {
		return;
	}

	echo '<div class="notice notice-error is-dismissible zc-sms-balance-notice">';
	echo '<p><strong>' . esc_html__( 'زرین کد — موجودی پیامک کاوه‌نگار:', 'zarincode' ) . '</strong> ';
	printf(
		esc_html__( 'موجودی فعلی %1$s ریال است و از آستانه‌ی %2$s ریال کمتر است. لطفاً برای ادامه‌ی ارسال پیامک، حساب خود را شارژ کنید.', 'zarincode' ),
		'<strong>' . esc_html( zc_fa_num( number_format( (float) $check['balance'] ) ) ) . '</strong>',
		esc_html( zc_fa_num( number_format( (float) $check['threshold'] ) ) )
	);
	echo '</p></div>';
}
add_action( 'admin_notices', 'zc_kavenegar_low_balance_notice' );

/**
 * هشدار فعال به مدیر (پیامک/ایمیل) هنگام پایین بودن موجودی.
 * با یک ترنزینت ۲۴ ساعته، فقط یک‌بار در روز ارسال می‌شود.
 *
 * @return void
 */
function zc_kavenegar_send_balance_alert() {
	if ( ! function_exists( 'zc_sms' ) || ! zc_sms()->is_ready() ) {
		return;
	}
	if ( ! zc_opt( 'zc_sms_balance_alert', true ) ) {
		return;
	}
	if ( get_transient( 'zc_kavenegar_alert_sent' ) ) {
		return;
	}

	$check = zc_kavenegar_balance_check();
	if ( is_wp_error( $check ) || empty( $check['low'] ) ) {
		return;
	}

	// محدود به یک بار در روز.
	set_transient( 'zc_kavenegar_alert_sent', 1, DAY_IN_SECONDS );

	$message = sprintf(
		__( '⚠️ هشدار زرین کد: موجودی پیامک کاوه‌نگار به %1$s ریال رسیده و کمتر از آستانه‌ی %2$s ریال است. لطفاً حساب را شارژ کنید.', 'zarincode' ),
		number_format( (float) $check['balance'] ),
		number_format( (float) $check['threshold'] )
	);

	// پیامک به مدیر.
	$admin_mobile = (string) zc_opt( 'zc_admin_alert_mobile', '' );
	if ( $admin_mobile && function_exists( 'zc_sms_dispatch' ) ) {
		zc_sms_dispatch( $admin_mobile, $message, 'alert' );
	}

	// ایمیل به مدیر.
	$admin_email = (string) zc_opt( 'zc_admin_alert_email', '' );
	if ( is_email( $admin_email ) ) {
		wp_mail( $admin_email, __( 'هشدار موجودی پیامک زرین کد', 'zarincode' ), $message );
	}
}
add_action( 'zc_sms_daily', 'zc_kavenegar_send_balance_alert' );

/**
 * ثبت تنظیمات اطلاع‌رسانی مدیر در ساختار پیش‌فرض.
 *
 * @param array $defaults مقادیر پیش‌فرض.
 * @return array
 */
function zc_kavenegar_alert_defaults( $defaults ) {
	$defaults['zc_admin_alert_mobile'] = '';
	$defaults['zc_admin_alert_email']  = '';
	return $defaults;
}
add_filter( 'zc_default_options', 'zc_kavenegar_alert_defaults' );
