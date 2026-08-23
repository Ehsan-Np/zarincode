<?php
/**
 * بررسی به‌روزرسانی قالب از سرور مجاز (اختیاری).
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * فیلتر transient به‌روزرسانی پوسته‌ها.
 *
 * @param object $transient داده.
 * @return object
 */
function zc_check_theme_updates( $transient ) {
	if ( empty( $transient ) || ! is_object( $transient ) ) {
		return $transient;
	}
	$endpoint = esc_url_raw( (string) zc_opt( 'zc_update_endpoint', '' ) );
	$key      = sanitize_text_field( (string) zc_opt( 'zc_update_license', '' ) );
	if ( ! $endpoint || 'https' !== wp_parse_url( $endpoint, PHP_URL_SCHEME ) ) {
		return $transient;
	}

	$cache = get_transient( 'zc_update_payload' );
	if ( ! is_array( $cache ) ) {
		$response = wp_remote_get(
			add_query_arg(
				array(
					'theme'   => 'zarincode',
					'version' => ZC_VERSION,
					'domain'  => wp_parse_url( home_url(), PHP_URL_HOST ),
				),
				$endpoint
			),
			array(
				'timeout' => 8,
				'headers' => array(
					'Accept'               => 'application/json',
					'X-Zarincode-License'  => $key,
				),
			)
		);
		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			set_transient( 'zc_update_payload', array( 'none' => 1 ), 6 * HOUR_IN_SECONDS );
			return $transient;
		}
		$cache = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $cache ) ) {
			$cache = array( 'none' => 1 );
		}
		set_transient( 'zc_update_payload', $cache, 6 * HOUR_IN_SECONDS );
	}

	$new = isset( $cache['new_version'] ) ? (string) $cache['new_version'] : '';
	$pkg = isset( $cache['package'] ) ? esc_url_raw( $cache['package'] ) : '';
	if ( ! $new || ! $pkg || version_compare( $new, ZC_VERSION, '<=' ) ) {
		return $transient;
	}
	if ( 'https' !== wp_parse_url( $pkg, PHP_URL_SCHEME ) ) {
		return $transient;
	}
	$pkg_host = strtolower( (string) wp_parse_url( $pkg, PHP_URL_HOST ) );
	$end_host = strtolower( (string) wp_parse_url( $endpoint, PHP_URL_HOST ) );
	$ok_hosts = apply_filters( 'zc_update_package_hosts', array_filter( array( $end_host, 'zarincode.com', 'www.zarincode.com', 'cdn.zarincode.com' ) ) );
	if ( ! $pkg_host || ! in_array( $pkg_host, array_map( 'strtolower', $ok_hosts ), true ) ) {
		return $transient;
	}
	if ( function_exists( 'zc_url_is_public_https' ) && ! zc_url_is_public_https( $pkg ) ) {
		return $transient;
	}

	$slug = get_template();
	$transient->response[ $slug ] = array(
		'theme'       => $slug,
		'new_version' => $new,
		'url'         => isset( $cache['url'] ) ? esc_url_raw( $cache['url'] ) : 'https://zarincode.com',
		'package'     => $pkg,
	);
	return $transient;
}
add_filter( 'pre_set_site_transient_update_themes', 'zc_check_theme_updates' );
