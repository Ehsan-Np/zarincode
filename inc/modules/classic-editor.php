<?php
/**
 * غیرفعال‌سازی کامل گوتنبرگ و بازگرداندن ویرایشگر کلاسیک
 *
 * ویرایشگرهای موردنظر این قالب «کلاسیک» و «المنتور» هستند. این ماژول
 * ویرایشگر بلوکی را در همه‌ی بخش‌های سایت خاموش می‌کند:
 *  - ویرایشگر نوشته‌ها، برگه‌ها و تمام نوع‌محتواهای سفارشی
 *  - ویرایشگر ابزارک‌های بلوکی (بازگشت به ابزارک‌های کلاسیک)
 *  - ویرایشگر قالب سایت (Site Editor) و منوی «نمایش » ویرایشگر»
 *  - استایل‌های بلوکی سمت کاربر که بی‌دلیل بارگذاری می‌شوند
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * آیا غیرفعال‌سازی گوتنبرگ فعال است؟
 *
 * @return bool
 */
function zc_gutenberg_disabled() {
	return (bool) zc_opt( 'zc_disable_gutenberg', true );
}

/* ==========================================================================
   ۱. ویرایشگر نوشته‌ها و برگه‌ها
   ========================================================================== */

/**
 * خاموش کردن ویرایشگر بلوکی برای همه‌ی نوع‌محتواها.
 *
 * @return bool
 */
function zc_disable_block_editor() {
	return ! zc_gutenberg_disabled();
}
add_filter( 'use_block_editor_for_post', 'zc_disable_block_editor', 100 );
add_filter( 'use_block_editor_for_post_type', 'zc_disable_block_editor', 100 );

// سازگاری با وردپرس ۴.۹ و افزونه‌ی Gutenberg.
add_filter( 'gutenberg_can_edit_post', 'zc_disable_block_editor', 100 );
add_filter( 'gutenberg_can_edit_post_type', 'zc_disable_block_editor', 100 );

/* ==========================================================================
   ۲. ابزارک‌ها — بازگشت به حالت کلاسیک
   ========================================================================== */

/**
 * غیرفعال‌سازی ویرایشگر بلوکی ابزارک‌ها.
 *
 * @return void
 */
function zc_disable_widget_block_editor() {
	if ( ! zc_gutenberg_disabled() ) {
		return;
	}

	remove_theme_support( 'widgets-block-editor' );
}
add_action( 'after_setup_theme', 'zc_disable_widget_block_editor', 100 );

add_filter( 'use_widgets_block_editor', 'zc_disable_block_editor', 100 );

/* ==========================================================================
   ۳. حذف استایل‌ها و اسکریپت‌های بلوکی از سمت کاربر
   ========================================================================== */

/**
 * حذف CSS بلوک‌ها که در این قالب استفاده نمی‌شود.
 *
 * این کار حجم صفحه را کم و سرعت بارگذاری را بیشتر می‌کند.
 *
 * @return void
 */
function zc_dequeue_block_assets() {
	if ( ! zc_gutenberg_disabled() ) {
		return;
	}

	/*
	 * صفحات سبد خرید و تسویه‌ی حساب ووکامرس با بلوک‌های گوتنبرگ
	 * (wp:woocommerce/cart و wp:woocommerce/checkout) ساخته می‌شوند و برای
	 * رندر محتوا به استایل/اسکریپتِ wc-blocks نیاز دارند. در این صفحات
	 * استایل‌های بلوک ووکامرس را حذف نمی‌کنیم تا سبد/تسویه خالی نماند.
	 */
	$zc_blocks_needed = false;
	if ( function_exists( 'is_cart' ) ) {
		$zc_blocks_needed = is_cart() || is_checkout() || ( function_exists( 'is_account_page' ) && is_account_page() );
	}

	/*
	 * استایل‌های هسته‌ی بلوک. نسخه‌ی راست‌چین با شناسه‌ی جداگانه
	 * («-rtl») ثبت می‌شود، پس هر دو حالت حذف می‌شوند.
	 */
	$handles = array(
		'wp-block-library',
		'wp-block-library-theme',
		'global-styles',
		'classic-theme-styles',
	);

	// استایل‌های بلوک ووکامرس فقط در صورت نیاز (نه در سبد/تسویه) حذف می‌شوند.
	if ( ! $zc_blocks_needed ) {
		$handles = array_merge(
			$handles,
			array( 'wc-block-style', 'wc-blocks-style', 'wc-blocks-packages-style' )
		);
	}

	foreach ( $handles as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
		wp_dequeue_style( $handle . '-rtl' );
	}

	// حذف هر استایل بلوکی ثبت‌شده توسط ووکامرس (نام‌های پویا) — به‌جز سبد/تسویه.
	$wp_styles = wp_styles();

	if ( $wp_styles instanceof WP_Styles && ! $zc_blocks_needed ) {
		foreach ( (array) $wp_styles->queue as $handle ) {
			if ( false !== strpos( $handle, 'wc-blocks' ) || false !== strpos( $handle, 'block-library' ) ) {
				wp_dequeue_style( $handle );
			}
		}
	}

	// استایل‌های بلوک المنتور نیز لازم نیست.
	wp_dequeue_style( 'elementor-gf-local-roboto' );
}
add_action( 'wp_enqueue_scripts', 'zc_dequeue_block_assets', 100 );

/**
 * حذف SVG های درون‌خطی داشبورد که فقط برای بلوک‌ها هستند.
 *
 * @return void
 */
function zc_remove_global_styles_svg() {
	if ( ! zc_gutenberg_disabled() ) {
		return;
	}

	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
}
add_action( 'init', 'zc_remove_global_styles_svg', 100 );

/* ==========================================================================
   ۴. ویرایشگر قالب سایت (FSE) و منوهای مرتبط
   ========================================================================== */

/**
 * حذف منوی «ویرایشگر» از پیشخوان.
 *
 * @return void
 */
function zc_remove_site_editor_menu() {
	if ( ! zc_gutenberg_disabled() ) {
		return;
	}

	remove_submenu_page( 'themes.php', 'site-editor.php' );
	remove_submenu_page( 'themes.php', 'gutenberg-edit-site' );
}
add_action( 'admin_menu', 'zc_remove_site_editor_menu', 999 );

/**
 * انتقال کاربر از ویرایشگر قالب سایت به پیشخوان.
 *
 * @return void
 */
function zc_block_site_editor_access() {
	if ( ! zc_gutenberg_disabled() ) {
		return;
	}

	global $pagenow;

	if ( 'site-editor.php' === $pagenow ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
}
add_action( 'admin_init', 'zc_block_site_editor_access' );

/* ==========================================================================
   ۵. پاک‌سازی رابط کاربری پیشخوان
   ========================================================================== */

/**
 * حذف پیوند «امتحان ویرایشگر بلوکی» و اعلان‌های مرتبط.
 *
 * @return void
 */
function zc_remove_gutenberg_notices() {
	if ( ! zc_gutenberg_disabled() ) {
		return;
	}

	remove_action( 'admin_notices', 'gutenberg_wordpress_version_notice' );
	remove_action( 'admin_notices', 'gutenberg_build_files_notice' );
}
add_action( 'admin_init', 'zc_remove_gutenberg_notices' );

/**
 * حذف تنظیمات بلوکی از سرصفحه‌ی سایت.
 *
 * @return void
 */
function zc_clean_block_head() {
	if ( ! zc_gutenberg_disabled() ) {
		return;
	}

	// حذف پیوند REST بلوک‌ها و اطلاعات اضافی.
	remove_action( 'wp_footer', 'the_block_template_skip_link' );
	remove_action( 'wp_enqueue_scripts', 'wp_common_block_scripts_and_styles' );
}
add_action( 'init', 'zc_clean_block_head', 100 );

/* ==========================================================================
   ۶. ووکامرس — غیرفعال‌سازی بلوک‌های فروشگاه
   ========================================================================== */

/**
 * جلوگیری از بارگذاری بلوک‌های ووکامرس.
 *
 * @param array $blocks بلوک‌ها.
 * @return array
 */
function zc_disable_woo_blocks( $blocks ) {
	// فقط در سمت کاربر؛ در پیشخوان اسکریپت‌های ووکامرس به این
	// وابستگی‌ها نیاز دارند و حذف آن‌ها خطای جاوااسکریپت می‌سازد.
	if ( is_admin() ) {
		return $blocks;
	}

	return zc_gutenberg_disabled() ? array() : $blocks;
}
add_filter( 'woocommerce_blocks_register_script_dependencies', 'zc_disable_woo_blocks', 100 );

/**
 * استفاده از قالب‌های کلاسیک ووکامرس به‌جای بلوکی.
 *
 * @return bool
 */
function zc_woo_use_classic_templates() {
	return false;
}
add_filter( 'woocommerce_has_block_template', 'zc_woo_use_classic_templates', 100 );

/* ==========================================================================
   ۷. ویرایشگر کلاسیک: بهبود تجربه
   ========================================================================== */

/**
 * فعال نگه داشتن ویرایشگر بصری و دکمه‌های کامل TinyMCE.
 *
 * @param array $buttons دکمه‌ها.
 * @return array
 */
function zc_classic_editor_buttons( $buttons ) {
	if ( ! zc_gutenberg_disabled() ) {
		return $buttons;
	}

	// افزودن دکمه‌های مفیدی که به‌صورت پیش‌فرض پنهان‌اند.
	$extra = array( 'superscript', 'subscript', 'hr', 'copy', 'paste', 'pastetext' );

	foreach ( $extra as $btn ) {
		if ( ! in_array( $btn, $buttons, true ) ) {
			$buttons[] = $btn;
		}
	}

	return $buttons;
}
add_filter( 'mce_buttons_2', 'zc_classic_editor_buttons' );

/**
 * راست‌چین کردن پیش‌فرض ویرایشگر کلاسیک.
 *
 * @param array $settings تنظیمات TinyMCE.
 * @return array
 */
function zc_classic_editor_rtl( $settings ) {
	if ( ! zc_gutenberg_disabled() || ! is_rtl() ) {
		return $settings;
	}

	$settings['directionality'] = 'rtl';

	return $settings;
}
add_filter( 'tiny_mce_before_init', 'zc_classic_editor_rtl' );

/**
 * حذف گزینه‌ی «ویرایش با گوتنبرگ» از فهرست نوشته‌ها.
 *
 * @param array $actions اقدام‌ها.
 * @return array
 */
function zc_remove_gutenberg_row_action( $actions ) {
	if ( ! zc_gutenberg_disabled() ) {
		return $actions;
	}

	unset( $actions['edit_gutenberg'] );

	return $actions;
}
add_filter( 'page_row_actions', 'zc_remove_gutenberg_row_action', 100 );
add_filter( 'post_row_actions', 'zc_remove_gutenberg_row_action', 100 );
