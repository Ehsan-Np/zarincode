<?php
/**
 * بارگذاری فایل‌های استایل و اسکریپت (بهینه‌شده برای سرعت)
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * ثبت و بارگذاری اسکریپت و استایل فرانت‌اند.
 *
 * @return void
 */
function zc_enqueue_assets() {
	$ver = ZC_VERSION;

	// فونت محلی (بدون درخواست خارجی = سرعت بالا).
	wp_enqueue_style( 'zc-fonts', ZC_ASSETS . 'css/fonts.css', array(), $ver );

	// استایل اصلی.
	wp_enqueue_style( 'zc-main', ZC_ASSETS . 'css/main.css', array( 'zc-fonts' ), $ver );

	// استایل ووکامرس فقط در صورت نیاز.
	if ( zc_is_woo() ) {
		wp_enqueue_style( 'zc-woo', ZC_ASSETS . 'css/woocommerce.css', array( 'zc-main' ), $ver );
	}

	// استایل پنل کاربری فقط در صفحات پنل.
	if ( is_page_template( 'templates/template-panel.php' ) || is_page_template( 'templates/template-login.php' ) ) {
		wp_enqueue_style( 'zc-panel', ZC_ASSETS . 'css/panel.css', array( 'zc-main' ), $ver );
	}

	/*
	 * شیوه‌نامه‌ی ویجت‌های المنتور (پیش‌تر درون خود ویجت‌ها چاپ می‌شد).
	 * فقط وقتی لازم است که المنتور فعال باشد.
	 */
	if ( did_action( 'elementor/loaded' ) ) {
		wp_enqueue_style( 'zc-widgets', ZC_ASSETS . 'css/widgets.css', array( 'zc-main' ), $ver );
	}

	/*
	 * لایه‌ی واکنش‌گرا آخرین شیوه‌نامه است تا بازچینش موبایل و تبلت
	 * بر همه‌ی قواعد پیشین (اصلی، ووکامرس و پنل) اولویت داشته باشد.
	 */
	wp_enqueue_style( 'zc-responsive', ZC_ASSETS . 'css/responsive.css', array( 'zc-main' ), $ver );

	/*
	 * لایه‌ی بازطراحی ظاهری (آخرین اولویت) تا رنگ‌بندی و کنتراستِ
	 * متن‌ها و استایل کلی قالب یک‌دست و مدرن باشد.
	 */
	wp_enqueue_style( 'zc-redesign', ZC_ASSETS . 'css/redesign.css', array( 'zc-responsive' ), $ver );

	/* استایل مدرن صفحات داخلی (تماس، درباره، قوانین و ...) */
	wp_enqueue_style( 'zc-pages', ZC_ASSETS . 'css/pages.css', array( 'zc-redesign' ), $ver );

	/* لایهٔ طراحی پریمیوم (آخرین اولویت — حس حرفه‌ای و خاص) */
	wp_enqueue_style( 'zc-premium', ZC_ASSETS . 'css/premium.css', array( 'zc-pages' ), $ver );

	// متغیرهای رنگ پویا از پنل تنظیمات.
	wp_add_inline_style( 'zc-main', zc_dynamic_css() );

	// اسکریپت اصلی (defer شده).
	wp_enqueue_script( 'zc-main', ZC_ASSETS . 'js/main.js', array(), $ver, true );

	$l10n = array(
		'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
		'nonce'     => zc_nonce(),
		'homeUrl'   => home_url( '/' ),
		'isRtl'     => is_rtl(),
		'panelUrl'  => zc_panel_url(),
		'loginUrl'  => zc_login_url(),
		'isLogged'  => is_user_logged_in(),
		'settings'  => array(
			'ajaxSearch' => (bool) zc_opt( 'zc_ajax_search', true ),
			'animations' => (bool) zc_opt( 'zc_animations', true ),
			'preloader'  => (bool) zc_opt( 'zc_preloader', true ),
			'backToTop'  => (bool) zc_opt( 'zc_back_to_top', true ),
			'chat'       => (bool) zc_opt( 'zc_chat_enable', true ),
			'searchDelay'=> 350,
		),
		'i18n'      => array(
			'loading'    => esc_html__( 'در حال بارگذاری…', 'zarincode' ),
			'noResults'  => esc_html__( 'نتیجه‌ای یافت نشد.', 'zarincode' ),
			'error'      => esc_html__( 'خطایی رخ داد. دوباره تلاش کنید.', 'zarincode' ),
			'added'      => esc_html__( 'با موفقیت افزوده شد.', 'zarincode' ),
			'copy'       => esc_html__( 'کپی شد!', 'zarincode' ),
			'confirm'    => esc_html__( 'آیا مطمئن هستید؟', 'zarincode' ),
			'seconds'    => esc_html__( 'ثانیه', 'zarincode' ),
			'sending'    => esc_html__( 'در حال ارسال…', 'zarincode' ),
		),
	);
	wp_localize_script( 'zc-main', 'ZC', $l10n );

	// اسکریپت پنل کاربری.
	if ( is_page_template( 'templates/template-panel.php' ) ) {
		wp_enqueue_script( 'zc-panel', ZC_ASSETS . 'js/panel.js', array( 'zc-main' ), $ver, true );
	}

	// پاسخ به دیدگاه.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'zc_enqueue_assets' );

/**
 * تولید CSS پویا بر اساس تنظیمات قالب.
 *
 * @return string
 */
function zc_dynamic_css() {
	$primary   = zc_opt( 'zc_color_primary', '#C9A227' );
	$primary2  = zc_opt( 'zc_color_primary_2', '#F5D061' );
	$dark      = zc_opt( 'zc_color_dark', '#141A31' );
	$dark2     = zc_opt( 'zc_color_dark_2', '#0B2187' );
	$body_bg   = zc_opt( 'zc_body_bg', '#FAFCFE' );
	$radius    = (int) zc_opt( 'zc_radius', 18 );
	$container = (int) zc_opt( 'zc_container', 1280 );
	$fsize     = (int) zc_opt( 'zc_font_size', 15 );
	$btn_bg    = zc_opt( 'zc_btn_bg', '#C9A227' );
	$btn_text  = zc_opt( 'zc_btn_text', '#241C05' );
	$topbar_bg = zc_opt( 'zc_topbar_bg', '#0B2187' );
	$footer_bg = zc_opt( 'zc_footer_bg', '#0E1226' );

	// پشتیبانی از فیلد رنگ Redux که ممکن است آرایه باشد.
	foreach ( array( 'primary', 'primary2', 'dark', 'dark2', 'body_bg', 'btn_bg', 'btn_text', 'topbar_bg', 'footer_bg' ) as $v ) {
		if ( is_array( $$v ) ) {
			$$v = isset( $$v['color'] ) ? $$v['color'] : ( isset( $$v['from'] ) ? $$v['from'] : '#C9A227' );
		}
	}

	$css = ':root{';
	$css .= '--zc-gold:' . esc_attr( $primary ) . ';';
	$css .= '--zc-gold-2:' . esc_attr( $primary2 ) . ';';
	$css .= '--zc-gold-soft:' . zc_hex_alpha( $primary, 0.12 ) . ';';
	$css .= '--zc-gold-line:' . zc_hex_alpha( $primary, 0.28 ) . ';';
	$css .= '--zc-dark:' . esc_attr( $dark ) . ';';
	$css .= '--zc-navy:' . esc_attr( $dark2 ) . ';';
	$css .= '--zc-body-bg:' . esc_attr( $body_bg ) . ';';
	$css .= '--zc-radius:' . $radius . 'px;';
	$css .= '--zc-radius-sm:' . max( 6, (int) ( $radius / 2 ) ) . 'px;';
	$css .= '--zc-radius-lg:' . ( $radius + 10 ) . 'px;';
	$css .= '--zc-container:' . $container . 'px;';
	$css .= '--zc-fs:' . $fsize . 'px;';
	$css .= '--zc-btn-bg:' . esc_attr( $btn_bg ) . ';';
	$css .= '--zc-btn-text:' . esc_attr( $btn_text ) . ';';
	$css .= '--zc-topbar-bg:' . esc_attr( $topbar_bg ) . ';';
	$css .= '--zc-footer-bg:' . esc_attr( $footer_bg ) . ';';

	// رنگ‌های لوگو (بخش اول طلایی / بخش دوم عادی).
	$css .= '--zc-logo-c1:' . esc_attr( zc_opt( 'zc_logo_color_1', '#C9A227' ) ) . ';';
	$css .= '--zc-logo-c2:' . esc_attr( zc_opt( 'zc_logo_color_2', '#141A31' ) ) . ';';
	$css .= '}';

	// رنگ دکمه اصلی و نوار بالا و فوتر.
	$css .= '.zc-btn--gold,.zc-btn[class*="btn--gold"],.zc-cta-bar,.zc-ctabar{background:' . esc_attr( $btn_bg ) . ';color:' . esc_attr( $btn_text ) . ';}';
	$css .= '.zc-topbar{background:' . esc_attr( $topbar_bg ) . ';}';
	$css .= '.zc-footer{background:' . esc_attr( $footer_bg ) . ';}';

	$custom = zc_opt( 'zc_custom_css', '' );
	if ( $custom ) {
		$css .= wp_strip_all_tags( $custom );
	}

	// اجازه به ماژول‌ها (مثل تایپوگرافی) برای افزودن CSS داینامیک.
	return apply_filters( 'zc_dynamic_css', $css );
}

/**
 * تبدیل HEX به RGBA.
 *
 * @param string $hex   رنگ.
 * @param float  $alpha شفافیت.
 * @return string
 */
function zc_hex_alpha( $hex, $alpha = 1 ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) ) {
		$hex = 'C9A227';
	}
	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );
	return "rgba({$r},{$g},{$b},{$alpha})";
}

/**
 * افزودن defer به اسکریپت‌های قالب برای بهبود سرعت.
 *
 * @param string $tag    تگ.
 * @param string $handle هندل.
 * @return string
 */
function zc_defer_scripts( $tag, $handle ) {
	$defer = array( 'zc-main', 'zc-panel', 'zc-bundle', 'zc-jalali' );
	if ( in_array( $handle, $defer, true ) && false === strpos( $tag, 'defer' ) ) {
		$tag = str_replace( ' src', ' defer src', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'zc_defer_scripts', 10, 2 );

/**
 * preload فونت اصلی برای جلوگیری از FOUT.
 *
 * @return void
 */
function zc_preload_assets() {
	// پیش‌بارگذاری فونت پیش‌فرض قالب (صمیم) با URL مطلق.
	$preload = array( 'Samim.woff2' );
	foreach ( $preload as $file ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( ZC_ASSETS . 'fonts/' . $file )
		);
	}
	echo '<meta name="theme-color" content="' . esc_attr( zc_opt( 'zc_color_dark', '#141A31' ) ) . '">' . "\n";
}
add_action( 'wp_head', 'zc_preload_assets', 1 );

/**
 * بارگذاری استایل ادمین.
 *
 * @param string $hook هوک.
 * @return void
 */
function zc_admin_assets( $hook ) {
	wp_enqueue_style( 'zc-admin', ZC_ASSETS . 'css/admin.css', array(), ZC_VERSION );
	wp_enqueue_script( 'zc-admin', ZC_ASSETS . 'js/admin.js', array( 'jquery' ), ZC_VERSION, true );
	wp_localize_script(
		'zc-admin',
		'ZCAdmin',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'zc_admin_nonce' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'zc_admin_assets' );

/**
 * استایل صفحه ورود وردپرس.
 *
 * @return void
 */
function zc_login_style() {
	wp_enqueue_style( 'zc-fonts', ZC_ASSETS . 'css/fonts.css', array(), ZC_VERSION );
	$logo = zc_opt( 'zc_logo', '' );
	$logo = is_array( $logo ) && isset( $logo['url'] ) ? $logo['url'] : $logo;
	?>
	<style>
		body.login{font-family:Vazirmatn,sans-serif;background:#141A31}
		.login h1 a{background-image:url('<?php echo esc_url( $logo ? $logo : ZC_ASSETS . 'img/logo.svg' ); ?>');background-size:contain;width:180px;height:60px}
		.login form{border-radius:18px;border:1px solid rgba(201,162,39,.25);box-shadow:0 20px 50px rgba(0,0,0,.25)}
		.wp-core-ui .button-primary{background:#C9A227;border-color:#C9A227;color:#141A31;font-weight:700;border-radius:10px}
		.login #backtoblog a,.login #nav a{color:#F5D061}
	</style>
	<?php
}
add_action( 'login_enqueue_scripts', 'zc_login_style' );

/**
 * پیش‌بارگذاری شیوه‌نامه‌ی اصلی برای بهبود LCP.
 *
 * @return void
 */
function zc_preload_main_css() {
	if ( is_admin() ) {
		return;
	}
	$handle = wp_style_is( 'zc-bundle', 'registered' ) ? 'zc-bundle' : 'zc-main';
	$reg    = wp_styles()->registered[ $handle ] ?? null;
	$src    = ( $reg && isset( $reg->src ) ) ? $reg->src : '';

	if ( $src ) {
		echo '<link rel="preload" as="style" href="' . esc_url( $src ) . '">';
	}
}
add_action( 'wp_head', 'zc_preload_main_css', 2 );
