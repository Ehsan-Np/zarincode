<?php
/**
 * Progressive Web App: مانیفست و سرویس‌ورکر.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * فعال بودن PWA.
 *
 * @return bool
 */
function zc_pwa_enabled() {
	return (bool) zc_opt( 'zc_pwa_enable', true );
}

/**
 * خروجی مانیفست.
 *
 * @return void
 */
function zc_pwa_serve() {
	if ( ! zc_pwa_enabled() ) {
		return;
	}
	if ( isset( $_GET['zc_manifest'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		nocache_headers();
		header( 'Content-Type: application/manifest+json; charset=utf-8' );
		$icon = zc_opt( 'zc_logo', '' );
		$icon = is_array( $icon ) && isset( $icon['url'] ) ? $icon['url'] : $icon;
		if ( ! $icon ) {
			$icon = ZC_ASSETS . 'img/logo.svg';
		}
		echo wp_json_encode(
			array(
				'name'             => get_bloginfo( 'name' ),
				'short_name'       => zc_opt( 'zc_site_name_1', 'زرین' ) . zc_opt( 'zc_site_name_2', 'کد' ),
				'start_url'        => zc_panel_url(),
				'scope'            => home_url( '/' ),
				'display'          => 'standalone',
				'dir'              => 'rtl',
				'lang'             => 'fa-IR',
				'background_color' => zc_opt( 'zc_body_bg', '#FAFCFE' ),
				'theme_color'      => zc_opt( 'zc_color_dark', '#141A31' ),
				'icons'            => array(
					array(
						'src'   => $icon,
						'sizes' => '192x192',
						'type'  => 'image/png',
					),
				),
			),
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		);
		exit;
	}

	if ( isset( $_GET['zc_sw'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		nocache_headers();
		header( 'Content-Type: application/javascript; charset=utf-8' );
		header( 'Service-Worker-Allowed: /' );
		$ver = ZC_VERSION;
		echo "const ZC_CACHE='zarincode-{$ver}';\n";
		echo "self.addEventListener('install',e=>{self.skipWaiting();});\n";
		echo "self.addEventListener('activate',e=>{e.waitUntil(caches.keys().then(keys=>Promise.all(keys.filter(k=>k!==ZC_CACHE).map(k=>caches.delete(k))))); self.clients.claim();});\n";
		echo "self.addEventListener('fetch',e=>{if(e.request.method!=='GET')return; const u=new URL(e.request.url); if(u.origin!==location.origin)return; if(u.pathname.indexOf('/wp-admin')===0||u.pathname.indexOf('/wp-login')===0)return; if(u.search.indexOf('zc_learn')!==-1)return; e.respondWith(fetch(e.request).then(r=>{const c=r.clone(); caches.open(ZC_CACHE).then(cache=>cache.put(e.request,c)); return r;}).catch(()=>caches.match(e.request)));});\n";
		exit;
	}
}
add_action( 'template_redirect', 'zc_pwa_serve', 1 );

/**
 * لینک مانیفست و ثبت SW.
 *
 * @return void
 */
function zc_pwa_head() {
	if ( ! zc_pwa_enabled() || is_admin() ) {
		return;
	}
	printf( '<link rel="manifest" href="%s">' . "\n", esc_url( add_query_arg( 'zc_manifest', '1', home_url( '/' ) ) ) );
}
add_action( 'wp_head', 'zc_pwa_head', 3 );

/**
 * ثبت سرویس‌ورکر.
 *
 * @return void
 */
function zc_pwa_register_sw() {
	if ( ! zc_pwa_enabled() || is_admin() ) {
		return;
	}
	$sw = add_query_arg( 'zc_sw', '1', home_url( '/' ) );
	echo '<script>if("serviceWorker"in navigator){navigator.serviceWorker.register(' . wp_json_encode( $sw ) . ',{scope:"/"}).catch(function(){});}</script>';
}
add_action( 'wp_footer', 'zc_pwa_register_sw', 30 );
