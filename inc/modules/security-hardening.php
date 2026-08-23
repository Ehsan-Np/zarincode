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

/**
 * آیا کاربر پشتیبان است (تیکت/چت)؟ edit_posts عمداً کافی نیست.
 *
 * @param int $user_id کاربر.
 * @return bool
 */
function zc_can_support( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return false;
	}
	return user_can( $user_id, 'manage_options' ) || user_can( $user_id, 'zc_answer_ticket' );
}

/**
 * انواع پست مجاز برای جستجو و بارگذاری بیشتر عمومی.
 *
 * @return array
 */
function zc_public_query_post_types() {
	$types = array( 'post', 'page', 'zc_course', 'zc_tutorial', 'zc_learning_path', 'zc_teacher', 'zc_service', 'zc_project', 'zc_faq' );
	if ( function_exists( 'zc_is_woo' ) && zc_is_woo() ) {
		$types[] = 'product';
	}
	return apply_filters( 'zc_public_query_post_types', $types );
}

/**
 * پاکسازی آرگومان WP_Query که از کلاینت می‌آید.
 *
 * @param mixed $raw دادهٔ خام.
 * @return array
 */
function zc_sanitize_public_query_args( $raw ) {
	$raw   = is_array( $raw ) ? $raw : array();
	$allow = zc_public_query_post_types();
	$type  = $raw['post_type'] ?? 'post';
	if ( is_array( $type ) ) {
		$type = array_values( array_intersect( array_map( 'sanitize_key', $type ), $allow ) );
		if ( ! $type ) {
			$type = array( 'post' );
		}
	} else {
		$type = sanitize_key( (string) $type );
		if ( ! in_array( $type, $allow, true ) ) {
			$type = 'post';
		}
	}

	$tax_query = array();
	if ( ! empty( $raw['cat'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'category',
			'field'    => 'term_id',
			'terms'    => array_map( 'absint', (array) $raw['cat'] ),
		);
	}
	if ( ! empty( $raw['zc_course_cat'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'zc_course_cat',
			'field'    => 'term_id',
			'terms'    => array_map( 'absint', (array) $raw['zc_course_cat'] ),
		);
	}

	$out = array(
		'post_type'           => $type,
		'post_status'         => 'publish',
		'posts_per_page'      => max( 1, min( 24, absint( $raw['posts_per_page'] ?? 9 ) ) ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => false,
	);
	if ( $tax_query ) {
		$out['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}
	return $out;
}

/**
 * آیا URL مقصد عمومی و HTTPS است (ضد SSRF به شبکهٔ داخلی)؟
 *
 * @param string $url نشانی.
 * @return bool
 */
function zc_url_is_public_https( $url ) {
	$url = esc_url_raw( (string) $url );
	if ( ! $url || 'https' !== wp_parse_url( $url, PHP_URL_SCHEME ) ) {
		return false;
	}
	$host = wp_parse_url( $url, PHP_URL_HOST );
	if ( ! $host || in_array( strtolower( $host ), array( 'localhost', 'metadata.google.internal' ), true ) ) {
		return false;
	}
	$ips = array();
	if ( filter_var( $host, FILTER_VALIDATE_IP ) ) {
		$ips[] = $host;
	} else {
		$resolved = gethostbynamel( $host );
		if ( is_array( $resolved ) ) {
			$ips = $resolved;
		}
	}
	if ( ! $ips ) {
		return false;
	}
	foreach ( $ips as $ip ) {
		if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
			return false;
		}
	}
	return true;
}

/**
 * میزبان‌های مجاز iframe ویدیوی کلاس درس.
 *
 * @return array
 */
function zc_video_iframe_hosts() {
	return apply_filters(
		'zc_video_iframe_hosts',
		array(
			'www.youtube.com',
			'youtube.com',
			'youtu.be',
			'www.youtube-nocookie.com',
			'www.aparat.com',
			'aparat.com',
			'player.vimeo.com',
			'vimeo.com',
		)
	);
}

/**
 * بررسی پسوند + بایت جادویی فایل آپلودی.
 *
 * @param array $file    عنصر $_FILES.
 * @param array $allowed پسوندهای مجاز.
 * @return true|WP_Error
 */
function zc_validate_upload_file( $file, $allowed ) {
	if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
		return new WP_Error( 'no_file', __( 'فایلی دریافت نشد.', 'zarincode' ) );
	}
	$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] ?? '' );
	$ext   = strtolower( (string) ( $check['ext'] ?? pathinfo( $file['name'] ?? '', PATHINFO_EXTENSION ) ) );
	if ( ! $ext || ! in_array( $ext, $allowed, true ) ) {
		return new WP_Error( 'bad_type', __( 'فرمت فایل مجاز نیست.', 'zarincode' ) );
	}
	$images = array( 'jpg', 'jpeg', 'png', 'gif', 'webp' );
	if ( in_array( $ext, $images, true ) ) {
		$info = @getimagesize( $file['tmp_name'] ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		if ( ! $info ) {
			return new WP_Error( 'bad_image', __( 'فایل تصویر معتبر نیست.', 'zarincode' ) );
		}
	}
	return true;
}

/**
 * پوشهٔ غیرقابل‌وب برای پیوست‌های خصوصی.
 *
 * @param array $dirs مسیرهای آپلود.
 * @return array
 */
function zc_private_upload_dir( $dirs ) {
	$subdir         = '/zc-private/' . gmdate( 'Y/m' );
	$dirs['subdir'] = $subdir;
	$dirs['path']   = $dirs['basedir'] . $subdir;
	$dirs['url']    = $dirs['baseurl'] . $subdir;
	return $dirs;
}

/**
 * ایجاد قفل وب روی پوشهٔ خصوصی.
 *
 * @return string
 */
function zc_ensure_private_upload_dir() {
	$base = trailingslashit( wp_upload_dir()['basedir'] ) . 'zc-private';
	wp_mkdir_p( $base );
	if ( ! file_exists( $base . '/index.php' ) ) {
		file_put_contents( $base . '/index.php', "<?php\n// Silence.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	}
	if ( ! file_exists( $base . '/.htaccess' ) ) {
		file_put_contents( $base . '/.htaccess', "Options -Indexes\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n" ); // phpcs:ignore
	}
	return $base;
}

/**
 * هدر CSP حداقلی که اسکریپت اینلاین قالب/المنتور را نمی‌شکند.
 *
 * @return void
 */
function zc_send_csp_header() {
	if ( headers_sent() || ! zc_opt( 'zc_security_headers', true ) ) {
		return;
	}
	header( "Content-Security-Policy: object-src 'none'; base-uri 'self'; frame-ancestors 'self'" );
}
add_action( 'send_headers', 'zc_send_csp_header', 2 );
