<?php
/**
 * دکمه شناور واتساپ و ارسال اختیاری از Cloud API.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * شماره واتساپ پاک‌شده.
 *
 * @return string
 */
function zc_whatsapp_number() {
	$raw = zc_opt( 'zc_whatsapp_number', zc_opt( 'zc_mobile', '' ) );
	$num = preg_replace( '/\D+/', '', zc_en_num( (string) $raw ) );
	if ( 0 === strpos( $num, '0' ) ) {
		$num = '98' . substr( $num, 1 );
	}
	return $num;
}

/**
 * لینک گفتگو.
 *
 * @param string $text متن پیش‌فرض.
 * @return string
 */
function zc_whatsapp_link( $text = '' ) {
	$num = zc_whatsapp_number();
	if ( ! $num ) {
		return '';
	}
	$text = $text ? $text : (string) zc_opt( 'zc_whatsapp_prefill', __( 'سلام، درباره دوره‌ها راهنمایی می‌خواهم.', 'zarincode' ) );
	return 'https://wa.me/' . $num . '?text=' . rawurlencode( $text );
}

/**
 * دکمه شناور.
 *
 * @return void
 */
function zc_whatsapp_fab() {
	if ( ! zc_opt( 'zc_whatsapp_enable', true ) || is_admin() ) {
		return;
	}
	if ( is_page_template( 'templates/template-classroom.php' ) ) {
		return;
	}
	$href = zc_whatsapp_link();
	if ( ! $href ) {
		return;
	}
	printf(
		'<a class="zc-wa-fab" href="%s" target="_blank" rel="noopener nofollow" aria-label="%s">%s<span>%s</span></a>',
		esc_url( $href ),
		esc_attr__( 'گفتگو در واتساپ', 'zarincode' ),
		zc_icon( 'chat', 22 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html__( 'واتساپ', 'zarincode' )
	);
	echo '<style>.zc-wa-fab{position:fixed;inset-inline-end:18px;bottom:18px;z-index:40;display:inline-flex;align-items:center;gap:8px;background:#25D366;color:#fff;padding:10px 14px;border-radius:999px;font-weight:700;box-shadow:0 10px 24px rgba(37,211,102,.35);text-decoration:none}.zc-wa-fab:hover{color:#fff;filter:brightness(1.05)}</style>';
}
add_action( 'wp_footer', 'zc_whatsapp_fab', 20 );

/**
 * ارسال پیام متنی از Cloud API (اگر توکن تنظیم شده باشد).
 *
 * @param string $to   شماره بین‌المللی.
 * @param string $body متن.
 * @return true|WP_Error
 */
function zc_whatsapp_send( $to, $body ) {
	$token   = (string) zc_opt( 'zc_whatsapp_token', '' );
	$phone_id = (string) zc_opt( 'zc_whatsapp_phone_id', '' );
	if ( ! $token || ! $phone_id ) {
		return new WP_Error( 'wa_disabled', __( 'واتساپ Cloud API پیکربندی نشده است.', 'zarincode' ) );
	}
	$to = preg_replace( '/\D+/', '', zc_en_num( $to ) );
	if ( ! $to ) {
		return new WP_Error( 'wa_number', __( 'شماره مقصد نامعتبر است.', 'zarincode' ) );
	}
	$resp = wp_remote_post(
		'https://graph.facebook.com/v20.0/' . rawurlencode( $phone_id ) . '/messages',
		array(
			'timeout' => 15,
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode(
				array(
					'messaging_product' => 'whatsapp',
					'to'                => $to,
					'type'              => 'text',
					'text'              => array( 'body' => wp_strip_all_tags( $body ) ),
				)
			),
		)
	);
	if ( is_wp_error( $resp ) ) {
		return $resp;
	}
	$code = (int) wp_remote_retrieve_response_code( $resp );
	if ( $code < 200 || $code >= 300 ) {
		return new WP_Error( 'wa_http', __( 'ارسال واتساپ ناموفق بود.', 'zarincode' ) );
	}
	return true;
}
