<?php
/**
 * بهینه‌سازی سرعت و کارایی
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * حذف اسکریپت‌ها و استایل‌های غیرضروری.
 *
 * @return void
 */
function zc_performance_cleanup() {
	if ( is_admin() ) {
		return;
	}

	// حذف استایل بلوک‌ها در صورت عدم استفاده.
	if ( zc_opt( 'zc_remove_block_css', true ) && ! zc_page_has_blocks() ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'wc-blocks-style' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}

	// حذف ایموجی.
	if ( zc_opt( 'zc_remove_emoji', true ) ) {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'tiny_mce_plugins', 'zc_disable_emoji_tinymce' );
	}

	// حذف jQuery Migrate.
	if ( zc_opt( 'zc_remove_jquery_migrate', true ) ) {
		add_action( 'wp_default_scripts', 'zc_remove_jquery_migrate_cb' );
	}

	// حذف دش‌آیکنز برای مهمان.
	if ( zc_opt( 'zc_remove_dashicons', true ) && ! is_user_logged_in() ) {
		wp_deregister_style( 'dashicons' );
	}
}
add_action( 'wp_enqueue_scripts', 'zc_performance_cleanup', 100 );

/**
 * بررسی وجود بلوک گوتنبرگ در صفحه.
 *
 * @return bool
 */
function zc_page_has_blocks() {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_post();
	return $post && function_exists( 'has_blocks' ) && has_blocks( $post );
}

/**
 * غیرفعال‌سازی ایموجی در ادیتور.
 *
 * @param array $plugins افزونه‌ها.
 * @return array
 */
function zc_disable_emoji_tinymce( $plugins ) {
	return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
}

/**
 * حذف jQuery Migrate.
 *
 * @param WP_Scripts $scripts اسکریپت‌ها.
 * @return void
 */
function zc_remove_jquery_migrate_cb( $scripts ) {
	if ( is_admin() || ! isset( $scripts->registered['jquery'] ) ) {
		return;
	}
	$script = $scripts->registered['jquery'];
	if ( $script->deps ) {
		$script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
	}
}

/**
 * افزودن preconnect و dns-prefetch.
 *
 * @param array  $urls     آدرس‌ها.
 * @param string $relation نوع.
 * @return array
 */
function zc_resource_hints( $urls, $relation ) {
	if ( 'dns-prefetch' === $relation ) {
		// حذف پیش‌فرض s.w.org.
		$urls = array_filter(
			$urls,
			function ( $url ) {
				return false === strpos( is_array( $url ) ? ( $url['href'] ?? '' ) : $url, 's.w.org' );
			}
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'zc_resource_hints', 10, 2 );

/**
 * افزودن lazy loading و ابعاد به تصاویر محتوا.
 *
 * @param string $content محتوا.
 * @return string
 */
function zc_optimize_content_images( $content ) {
	if ( is_admin() || is_feed() || ! zc_opt( 'zc_lazyload', true ) ) {
		return $content;
	}

	// افزودن decoding=async به تصاویر.
	$content = preg_replace_callback(
		'/<img([^>]+)>/i',
		function ( $matches ) {
			$img = $matches[0];
			if ( false === strpos( $img, 'decoding=' ) ) {
				$img = str_replace( '<img', '<img decoding="async"', $img );
			}
			if ( false === strpos( $img, 'loading=' ) ) {
				$img = str_replace( '<img', '<img loading="lazy"', $img );
			}
			return $img;
		},
		$content
	);

	// افزودن loading=lazy به آیفریم‌ها.
	$content = preg_replace( '/<iframe(?![^>]*loading=)/i', '<iframe loading="lazy"', $content );

	return $content;
}
add_filter( 'the_content', 'zc_optimize_content_images', 20 );

/**
 * محدودسازی ذخیره نسخه‌های پیشین.
 *
 * @param int $num تعداد.
 * @return int
 */
function zc_limit_revisions( $num ) {
	return (int) zc_opt( 'zc_revisions_limit', 5 );
}
add_filter( 'wp_revisions_to_keep', 'zc_limit_revisions' );

/**
 * غیرفعال‌سازی XML-RPC برای امنیت و کاهش بار.
 *
 * @return bool
 */
function zc_disable_xmlrpc() {
	return ! zc_opt( 'zc_disable_xmlrpc', true );
}
add_filter( 'xmlrpc_enabled', 'zc_disable_xmlrpc' );

/**
 * حذف Heartbeat در فرانت برای کاهش درخواست‌ها.
 *
 * @return void
 */
function zc_control_heartbeat() {
	if ( ! is_admin() && zc_opt( 'zc_disable_heartbeat', true ) ) {
		wp_deregister_script( 'heartbeat' );
	}
}
add_action( 'init', 'zc_control_heartbeat', 1 );

/**
 * پاکسازی کش داخلی قالب.
 *
 * @return void
 */
function zc_clear_cache() {
	global $wpdb;

	$wpdb->query( // phpcs:ignore
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_zc_%' OR option_name LIKE '_transient_timeout_zc_%'"
	);

	delete_transient( 'zc_site_stats' );

	do_action( 'zc_cache_cleared' );
}

/**
 * پاکسازی کش هنگام انتشار محتوا.
 *
 * @return void
 */
function zc_clear_cache_on_save() {
	delete_transient( 'zc_site_stats' );
	global $wpdb;
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_zc_search_%'" ); // phpcs:ignore
}
add_action( 'save_post', 'zc_clear_cache_on_save' );

/**
 * افزودن هدرهای امنیتی.
 *
 * @param array $headers هدرها.
 * @return array
 */
function zc_security_headers( $headers ) {
	if ( ! zc_opt( 'zc_security_headers', true ) ) {
		return $headers;
	}

	$headers['X-Content-Type-Options'] = 'nosniff';
	$headers['X-XSS-Protection']       = '1; mode=block';
	$headers['Referrer-Policy']        = 'strict-origin-when-cross-origin';

	return $headers;
}
add_filter( 'wp_headers', 'zc_security_headers' );

/**
 * بهینه‌سازی کوئری اصلی برای صفحات آرشیو.
 *
 * @param WP_Query $query کوئری.
 * @return void
 */
function zc_optimize_queries( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_archive() || $query->is_home() ) {
		$query->set( 'update_post_term_cache', true );
		$query->set( 'update_post_meta_cache', true );
	}

	// تعداد آیتم آرشیو دوره‌ها.
	if ( $query->is_post_type_archive( 'zc_course' ) || $query->is_tax( 'zc_course_cat' ) ) {
		$query->set( 'posts_per_page', (int) zc_opt( 'zc_courses_per_page', 12 ) );
	}
}
add_action( 'pre_get_posts', 'zc_optimize_queries' );

/* ==========================================================================
   حذف درخواست‌های خارجی و دارایی‌های بی‌مصرف
   ========================================================================== */

/**
 * غیرفعال‌کردن بارگذاری فونت‌های گوگل توسط المنتور.
 *
 * قالب فونت وزیرمتن را محلی سرو می‌کند؛ درخواست به fonts.googleapis.com
 * هم کند است، هم برای کاربران ایرانی اغلب ناموفق، و حدود ۱۲۰ کیلوبایت
 * دانلود بی‌فایده تحمیل می‌کند.
 *
 * @return void
 */
function zc_disable_elementor_google_fonts() {
	add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );
}
add_action( 'init', 'zc_disable_elementor_google_fonts' );

/**
 * آیا صفحه‌ی جاری به دارایی‌های ووکامرس نیاز دارد؟
 *
 * @return bool
 */
function zc_is_woo_page() {
	if ( ! function_exists( 'is_woocommerce' ) ) {
		return false;
	}

	if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
		return true;
	}

	if ( is_singular() ) {
		$el = get_post_meta( get_the_ID(), '_elementor_data', true );

		if ( $el && false !== strpos( $el, 'zc_products' ) ) {
			return true;
		}
	}

	if ( is_page_template( 'templates/template-panel.php' ) ) {
		return true;
	}

	return (bool) apply_filters( 'zc_is_woo_page', false );
}

/**
 * آیا صفحه به جی‌کوئری نیاز دارد؟
 *
 * @return bool
 */
function zc_needs_jquery() {
	if ( zc_is_woo_page() ) {
		return true;
	}

	// المنتور در حالت ویرایش و پیش‌نمایش به جی‌کوئری وابسته است.
	if ( isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return true;
	}

	if ( is_singular() && comments_open() ) {
		return true;
	}

	return (bool) apply_filters( 'zc_needs_jquery', false );
}

/**
 * حذف شیوه‌نامه‌ها و اسکریپت‌های بی‌استفاده.
 *
 * @return void
 */
function zc_dequeue_unused_assets() {
	// بلوک‌های گوتنبرگ غیرفعال است؛ شیوه‌نامه‌اش لازم نیست.
	if ( ! function_exists( 'zc_page_has_blocks' ) || ! zc_page_has_blocks() ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'wc-blocks-style' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}

	// دارایی‌های ووکامرس فقط در صفحات فروشگاهی.
	if ( function_exists( 'is_woocommerce' ) && ! zc_is_woo_page() ) {
		foreach ( array( 'woocommerce-general', 'woocommerce-layout', 'woocommerce-smallscreen', 'wc-block-style', 'zc-woo' ) as $handle ) {
			wp_dequeue_style( $handle );
		}

		foreach ( array( 'wc-cart-fragments', 'woocommerce', 'wc-add-to-cart', 'jquery-blockui', 'wc-order-attribution', 'sourcebuster-js' ) as $handle ) {
			wp_dequeue_script( $handle );
		}
	}

	/*
	 * جی‌کوئری تنها وابستگی افزونه‌هاست؛ کد خود قالب کاملاً بومی است.
	 * اگر هیچ اسکریپتی به آن نیاز نداشته باشد، ۸۵ کیلوبایت حذف می‌شود.
	 */
	if ( ! is_admin() && ! zc_needs_jquery() ) {
		wp_dequeue_script( 'jquery' );
		wp_dequeue_script( 'jquery-core' );
		wp_dequeue_script( 'jquery-migrate' );
	}
}
add_action( 'wp_enqueue_scripts', 'zc_dequeue_unused_assets', 99 );
