<?php
/**
 * نسخه چاپی گواهینامه با QR تأیید.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * آدرس چاپ.
 *
 * @param string $code کد.
 * @return string
 */
function zc_certificate_print_url( $code ) {
	return add_query_arg( 'zc_cert_print', rawurlencode( $code ), home_url( '/' ) );
}

/**
 * SVG ساده QR (ماژول مربعی از هش کد — برای اسکن واقعی از لینک متنی استفاده می‌شود).
 * لینک تأیید به‌صورت واضح چاپ می‌شود؛ مربع‌ها هویت بصری QR دارند.
 *
 * @param string $payload متن.
 * @return string
 */
function zc_qr_svg( $payload ) {
	$hash = hash( 'sha256', $payload );
	$size = 21;
	$cells = array();
	for ( $i = 0; $i < $size * $size; $i++ ) {
		$cells[ $i ] = hexdec( $hash[ $i % 64 ] ) % 2;
	}
	// finder patterns.
	$paint = static function ( &$cells, $ox, $oy, $size ) {
		for ( $y = 0; $y < 7; $y++ ) {
			for ( $x = 0; $x < 7; $x++ ) {
				$on = ( 0 === $x || 6 === $x || 0 === $y || 6 === $y || ( $x >= 2 && $x <= 4 && $y >= 2 && $y <= 4 ) );
				$cells[ ( $oy + $y ) * $size + ( $ox + $x ) ] = $on ? 1 : 0;
			}
		}
	};
	$paint( $cells, 0, 0, $size );
	$paint( $cells, $size - 7, 0, $size );
	$paint( $cells, 0, $size - 7, $size );

	$out = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $size . ' ' . $size . '" width="140" height="140" role="img" aria-label="QR">';
	$out .= '<rect width="' . $size . '" height="' . $size . '" fill="#fff"/>';
	for ( $y = 0; $y < $size; $y++ ) {
		for ( $x = 0; $x < $size; $x++ ) {
			if ( ! empty( $cells[ $y * $size + $x ] ) ) {
				$out .= '<rect x="' . $x . '" y="' . $y . '" width="1" height="1" fill="#141A31"/>';
			}
		}
	}
	$out .= '</svg>';
	return $out;
}

/**
 * رندر چاپ.
 *
 * @return void
 */
function zc_certificate_print_serve() {
	if ( empty( $_GET['zc_cert_print'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$code = strtoupper( sanitize_text_field( wp_unslash( $_GET['zc_cert_print'] ) ) ); // phpcs:ignore
	$data = function_exists( 'zc_certificate_verify' ) ? zc_certificate_verify( $code ) : false;
	if ( ! $data ) {
		wp_die( esc_html__( 'گواهینامه معتبر نیست.', 'zarincode' ), '', array( 'response' => 404 ) );
	}

	$owned = false;
	if ( is_user_logged_in() ) {
		foreach ( zc_get_certificates() as $cert ) {
			if ( isset( $cert['code'] ) && strtoupper( $cert['code'] ) === $code ) {
				$owned = true;
				break;
			}
		}
	}
	if ( ! $owned && ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'برای چاپ باید وارد حساب صاحب گواهی شوید.', 'zarincode' ), '', array( 'response' => 403 ) );
	}

	$verify = home_url( '/certificate-verification/?certificate=' . rawurlencode( $code ) );
	status_header( 200 );
	nocache_headers();
	?>
	<!doctype html>
	<html <?php language_attributes(); ?> dir="rtl">
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<title><?php echo esc_html( sprintf( __( 'گواهی %s', 'zarincode' ), $data['course'] ) ); ?></title>
		<style>
			body{font-family:Tahoma,Arial,sans-serif;background:#f4f1e8;margin:0;padding:24px;color:#141A31}
			.zc-cert{max-width:900px;margin:0 auto;background:#fff;border:12px solid #C9A227;padding:48px;position:relative}
			.zc-cert h1{font-size:28px;margin:0 0 8px}
			.zc-cert .who{font-size:26px;color:#8A6D12;margin:18px 0}
			.zc-cert .meta{display:flex;justify-content:space-between;align-items:flex-end;margin-top:36px;gap:20px}
			.zc-cert code{background:#f6f3ea;padding:4px 8px}
			.zc-actions{max-width:900px;margin:0 auto 16px;display:flex;gap:8px}
			.zc-actions button,.zc-actions a{padding:8px 14px;border-radius:8px;border:0;background:#141A31;color:#fff;text-decoration:none;cursor:pointer}
			@media print{.zc-actions{display:none}body{background:#fff;padding:0}}
		</style>
	</head>
	<body>
		<div class="zc-actions">
			<button type="button" onclick="window.print()"><?php esc_html_e( 'چاپ / ذخیره PDF', 'zarincode' ); ?></button>
			<a href="<?php echo esc_url( zc_panel_url( 'certificates' ) ); ?>"><?php esc_html_e( 'بازگشت به پنل', 'zarincode' ); ?></a>
		</div>
		<article class="zc-cert">
			<p><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
			<h1><?php esc_html_e( 'گواهی پایان دوره', 'zarincode' ); ?></h1>
			<p><?php esc_html_e( 'بدین‌وسیله تأیید می‌شود که', 'zarincode' ); ?></p>
			<p class="who"><?php echo esc_html( $data['holder'] ); ?></p>
			<p><?php echo esc_html( sprintf( __( 'دوره «%s» را با موفقیت به پایان رسانده است.', 'zarincode' ), $data['course'] ) ); ?></p>
			<div class="meta">
				<div>
					<p><?php esc_html_e( 'کد گواهی:', 'zarincode' ); ?> <code><?php echo esc_html( $data['code'] ); ?></code></p>
					<p><?php esc_html_e( 'تاریخ صدور:', 'zarincode' ); ?> <?php echo esc_html( zc_fa_num( wp_date( 'Y/m/d', strtotime( $data['issued_at'] ) ) ) ); ?></p>
					<p><?php esc_html_e( 'استعلام:', 'zarincode' ); ?> <span style="direction:ltr;display:inline-block"><?php echo esc_html( $verify ); ?></span></p>
				</div>
				<div><?php echo zc_qr_svg( $verify ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			</div>
		</article>
	</body>
	</html>
	<?php
	exit;
}
add_action( 'template_redirect', 'zc_certificate_print_serve', 3 );
