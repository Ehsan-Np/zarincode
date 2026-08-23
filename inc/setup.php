<?php
/**
 * راه‌اندازی اولیه قالب
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * پشتیبانی‌های قالب.
 *
 * @return void
 */
function zc_theme_setup() {
	load_theme_textdomain( 'zarincode', ZC_DIR . 'languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 60,
			'width'       => 200,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support( 'woocommerce', array( 'thumbnail_image_width' => 500 ) );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	// اندازه تصاویر بهینه.
	add_image_size( 'zc-card', 600, 400, true );
	add_image_size( 'zc-card-lg', 900, 560, true );
	add_image_size( 'zc-wide', 1400, 620, true );
	add_image_size( 'zc-square', 400, 400, true );
	add_image_size( 'zc-avatar', 120, 120, true );

	// منوها.
	register_nav_menus(
		array(
			'primary'   => esc_html__( 'منوی اصلی (هدر)', 'zarincode' ),
			'mega'      => esc_html__( 'مگا منو', 'zarincode' ),
			'mobile'    => esc_html__( 'منوی موبایل', 'zarincode' ),
			'footer_1'  => esc_html__( 'فوتر - ستون اول', 'zarincode' ),
			'footer_2'  => esc_html__( 'فوتر - ستون دوم', 'zarincode' ),
			'footer_3'  => esc_html__( 'فوتر - ستون سوم', 'zarincode' ),
			'panel'     => esc_html__( 'منوی پنل کاربری', 'zarincode' ),
			'topbar'    => esc_html__( 'نوار بالای سایت', 'zarincode' ),
		)
	);

	// اندازه محتوا.
	if ( ! isset( $GLOBALS['content_width'] ) ) {
		$GLOBALS['content_width'] = 1280;
	}
}
add_action( 'after_setup_theme', 'zc_theme_setup' );

/**
 * ثبت سایدبارها.
 *
 * @return void
 */
function zc_widgets_init() {
	$areas = array(
		'sidebar-main'    => esc_html__( 'سایدبار اصلی', 'zarincode' ),
		'sidebar-blog'    => esc_html__( 'سایدبار بلاگ', 'zarincode' ),
		'sidebar-shop'    => esc_html__( 'سایدبار فروشگاه', 'zarincode' ),
		'sidebar-course'  => esc_html__( 'سایدبار دوره‌ها', 'zarincode' ),
		'footer-1'        => esc_html__( 'فوتر ۱', 'zarincode' ),
		'footer-2'        => esc_html__( 'فوتر ۲', 'zarincode' ),
		'footer-3'        => esc_html__( 'فوتر ۳', 'zarincode' ),
		'footer-4'        => esc_html__( 'فوتر ۴', 'zarincode' ),
	);

	foreach ( $areas as $id => $name ) {
		register_sidebar(
			array(
				'name'          => $name,
				'id'            => $id,
				'before_widget' => '<section id="%1$s" class="zc-widget widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h3 class="zc-widget__title"><span>',
				'after_title'   => '</span></h3>',
			)
		);
	}
}
add_action( 'widgets_init', 'zc_widgets_init' );

/**
 * افزودن کلاس‌های بدنه.
 *
 * @param array $classes کلاس‌ها.
 * @return array
 */
function zc_body_class( $classes ) {
	$classes[] = 'zc-theme';
	$classes[] = 'zc-font-' . zc_opt( 'zc_font_body', 'samim' );

	/*
	 * کلاس zc-anim-on دیگر از سمت سرور اضافه نمی‌شود؛ بلکه فقط با
	 * جاوااسکریپت به body اضافه می‌گردد. دلیل: این کلاس باعث می‌شود همه‌ی
	 * عناصر data-zc-anim با opacity:0 شروع شوند. اگر JS به هر دلیلی اجرا
	 * نشود (خطا، تداخل یا محیط بدون JS)، محتوای صفحات داخلی برای همیشه
	 * مخفی می‌ماند. با افزودنِ سمت کلاینت، در نبود JS محتوا به‌طور
	 * پیش‌فرض نمایان است و تنها با JS انیمیشن فعال می‌شود.
	 */
	if ( zc_opt( 'zc_sticky_header', true ) ) {
		$classes[] = 'zc-sticky-header';
	}
	if ( is_rtl() ) {
		$classes[] = 'zc-rtl';
	}
	$layout = zc_opt( 'zc_site_layout', 'wide' );
	$classes[] = 'zc-layout-' . $layout;

	if ( zc_built_with_elementor() ) {
		$classes[] = 'zc-elementor-page';
	}

	return $classes;
}
add_filter( 'body_class', 'zc_body_class' );

/**
 * افزودن پشتیبانی از عرض کامل برای المنتور.
 *
 * @return void
 */
function zc_elementor_locations() {
	if ( ! zc_is_elementor() ) {
		return;
	}
	add_theme_support( 'elementor' );
}
add_action( 'after_setup_theme', 'zc_elementor_locations', 20 );

/**
 * ثبت الگوهای بلوک (اختیاری).
 *
 * @return void
 */
function zc_register_pattern_category() {
	if ( function_exists( 'register_block_pattern_category' ) ) {
		register_block_pattern_category( 'zarincode', array( 'label' => esc_html__( 'زرین کد', 'zarincode' ) ) );
	}
}
add_action( 'init', 'zc_register_pattern_category' );

/**
 * ایجاد صفحات مورد نیاز هنگام فعال‌سازی قالب.
 *
 * @return void
 */
function zc_after_switch_theme() {
	$pages = array(
		'zc_panel_page'  => array( 'title' => 'پنل کاربری', 'slug' => 'panel', 'tpl' => 'templates/template-panel.php' ),
		'zc_login_page'  => array( 'title' => 'ورود و ثبت‌نام', 'slug' => 'login', 'tpl' => 'templates/template-login.php' ),
		'zc_booking_page'=> array( 'title' => 'رزرو نوبت', 'slug' => 'booking', 'tpl' => 'templates/template-booking.php' ),
		'zc_certificate_verify_page' => array( 'title' => 'استعلام گواهینامه', 'slug' => 'certificate-verification', 'tpl' => '', 'content' => '[zc_certificate_verify]' ),
	);

	$options = get_option( ZC_PREFIX, array() );
	if ( ! is_array( $options ) ) {
		$options = array();
	}

	foreach ( $pages as $key => $data ) {
		$existing = get_page_by_path( $data['slug'] );
		if ( $existing ) {
			$options[ $key ] = $existing->ID;
			continue;
		}
		$page_id = wp_insert_post(
			array(
				'post_title'   => $data['title'],
				'post_name'    => $data['slug'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => $data['content'] ?? '',
			)
		);
		if ( $page_id && ! is_wp_error( $page_id ) ) {
			if ( ! empty( $data['tpl'] ) ) {
				update_post_meta( $page_id, '_wp_page_template', $data['tpl'] );
			}
			$options[ $key ] = $page_id;
		}
	}

	// کلیدهای امنیتی پیش از فعال‌شدن endpointهای webhook/cron ساخته شوند.
	if ( empty( $options['zc_bot_secret'] ) ) {
		$options['zc_bot_secret'] = wp_generate_password( 32, false, false );
	}
	if ( empty( $options['zc_cron_key'] ) ) {
		$options['zc_cron_key'] = wp_generate_password( 32, false, false );
	}

	update_option( ZC_PREFIX, $options );
	flush_rewrite_rules();

	set_transient( 'zc_activation_redirect', 1, 60 );
}
add_action( 'after_switch_theme', 'zc_after_switch_theme' );

/**
 * حذف نسخه وردپرس و تمیزکاری هدر برای امنیت و سرعت.
 *
 * @return void
 */
function zc_clean_head() {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
}
add_action( 'init', 'zc_clean_head' );
