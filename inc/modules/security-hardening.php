<?php
/**
 * سخت‌سازی امنیتی سراسری زرین کد (۳.۳۸).
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * هدرهای امنیتی HTTP.
 *
 * @return void
 */
function zc_send_security_headers() {
	if ( headers_sent() || ! zc_opt( 'zc_security_headers', true ) ) {
		return;
	}

	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );
	header( 'X-XSS-Protection: 0' );

	if ( is_ssl() ) {
		header( 'Strict-Transport-Security: max-age=15552000; includeSubDomains' );
	}
}
add_action( 'send_headers', 'zc_send_security_headers', 1 );

/**
 * غیرفعال‌سازی XML-RPC در صورت تنظیم پنل.
 *
 * @return void
 */
function zc_maybe_disable_xmlrpc() {
	if ( zc_opt( 'zc_disable_xmlrpc', true ) ) {
		add_filter( 'xmlrpc_enabled', '__return_false' );
	}
}
add_action( 'init', 'zc_maybe_disable_xmlrpc', 1 );

/**
 * مخفی‌کردن نسخه وردپرس در هدر و فید.
 *
 * @return void
 */
function zc_hide_wp_version() {
	remove_action( 'wp_head', 'wp_generator' );
	add_filter( 'the_generator', '__return_empty_string' );
}
add_action( 'init', 'zc_hide_wp_version', 2 );

/**
 * محدودیت نرخ REST عمومی (گواهی، لایسنس، وب‌هوک).
 *
 * @param string $bucket کلید سطل.
 * @param int    $limit  سقف.
 * @param int    $window ثانیه.
 * @return bool
 */
function zc_rest_allow( $bucket, $limit = 20, $window = MINUTE_IN_SECONDS ) {
	return zc_rate_limit( 'rest_' . sanitize_key( $bucket ), $limit, $window );
}

/**
 * الگوی‌های خطرناک برای کد ارسالی به سندباکس.
 *
 * @param string $code کد.
 * @return bool true اگر مشکوک باشد.
 */
function zc_code_looks_dangerous( $code ) {
	$code = (string) $code;
	$needles = array(
		'system(',
		'exec(',
		'passthru(',
		'proc_open(',
		'popen(',
		'shell_exec(',
		'pcntl_',
		'`',
		'curl_exec',
		'file_get_contents("http',
		"file_get_contents('http",
		'fsockopen(',
		'socket_create(',
		'base64_decode(',
		'eval(',
		'assert(',
		'preg_replace("/e',
		'/etc/passwd',
		'php://input',
	);

	$lower = strtolower( $code );
	foreach ( $needles as $needle ) {
		if ( false !== strpos( $lower, strtolower( $needle ) ) ) {
			return true;
		}
	}

	return (bool) preg_match( '/\b(rm\s+-rf|wget\s+|curl\s+-|chmod\s+777)\b/i', $code );
}

/**
 * آیا میزبان سرویس اجرای کد مجاز است؟
 *
 * @param string $endpoint آدرس.
 * @return bool
 */
function zc_exec_endpoint_allowed( $endpoint ) {
	$host = wp_parse_url( $endpoint, PHP_URL_HOST );
	if ( ! $host || 'https' !== wp_parse_url( $endpoint, PHP_URL_SCHEME ) ) {
		return false;
	}

	$allow = apply_filters(
		'zc_exec_allowed_hosts',
		array( 'wandbox.org', 'api.wandbox.org' )
	);

	$custom = wp_parse_url( (string) zc_opt( 'zc_quiz_exec_api', '' ), PHP_URL_HOST );
	if ( $custom ) {
		$allow[] = $custom;
	}

	return in_array( strtolower( $host ), array_map( 'strtolower', $allow ), true );
}

/**
 * پاکسازی نماد اعتماد: حذف رویدادهای جاوااسکریپت و محدود کردن iframe.
 *
 * @param string $html HTML.
 * @return string
 */
function zc_sanitize_badge_html( $html ) {
	$html = (string) $html;
	$html = preg_replace( '/\son\w+\s*=\s*([\'"]).*?\1/i', '', $html );
	$html = preg_replace( '/\son\w+\s*=\s*[^\s>]+/i', '', $html );
	$html = preg_replace( '/javascript\s*:/i', '', $html );
	$html = preg_replace_callback(
		'/<iframe\b[^>]*>/i',
		static function ( $m ) {
			if ( ! preg_match( '/\ssrc\s*=\s*([\'"])(https?:\/\/[^\'"]+)\1/i', $m[0], $src ) ) {
				return '';
			}
			$host = wp_parse_url( $src[2], PHP_URL_HOST );
			$ok   = apply_filters(
				'zc_badge_iframe_hosts',
				array( 'trustseal.enamad.ir', 'logo.samandehi.ir', 'www.zarinpal.com', 'zarinpal.com' )
			);
			if ( ! $host || ! in_array( strtolower( $host ), array_map( 'strtolower', (array) $ok ), true ) ) {
				return '';
			}
			return $m[0];
		},
		$html
	);

	return $html;
}

/**
 * تولید/خواندن کلید رمزنگاری بکاپ (مستقل از AUTH_KEY قابل چرخش).
 *
 * @return string باینری ۳۲ بایتی.
 */
function zc_backup_crypto_key() {
	$stored = get_option( 'zc_backup_crypto_key', '' );
	if ( is_string( $stored ) && strlen( $stored ) >= 32 ) {
		return hash( 'sha256', $stored, true );
	}

	$fresh = wp_generate_password( 64, true, true );
	update_option( 'zc_backup_crypto_key', $fresh, false );
	return hash( 'sha256', $fresh, true );
}
