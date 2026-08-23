<?php
/**
 * توابع کمکی عمومی قالب زرین کد
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * دریافت یک گزینه از پنل تنظیمات (Redux یا فالبک).
 *
 * @param string $key     کلید.
 * @param mixed  $default مقدار پیش‌فرض.
 * @return mixed
 */
function zc_opt( $key, $default = '' ) {
	static $options = null;

	if ( null === $options ) {
		global $zarincode_options;
		if ( is_array( $zarincode_options ) && ! empty( $zarincode_options ) ) {
			$options = $zarincode_options;
		} else {
			$options = get_option( ZC_PREFIX, array() );
			if ( ! is_array( $options ) ) {
				$options = array();
			}
		}
		$options = wp_parse_args( $options, zc_default_options() );
	}

	if ( ! isset( $options[ $key ] ) || '' === $options[ $key ] || ( is_array( $options[ $key ] ) && empty( $options[ $key ] ) ) ) {
		return $default;
	}

	return $options[ $key ];
}

/**
 * مقادیر پیش‌فرض تنظیمات قالب.
 *
 * @return array
 */
/**
 * لیست فونت‌های پشتیبانی‌شده قالب.
 * همه‌ی فونت‌ها به‌صورت آفلاین داخل قالب قرار دارند.
 *
 * @return array
 */
function zc_font_options() {
	return array(
		'samim'    => __( 'صمیم (Samim)', 'zarincode' ),
		'vazirmatn'=> __( 'وزیرمتن (Vazirmatn)', 'zarincode' ),
		'shabnam'  => __( 'شبنم (Shabnam)', 'zarincode' ),
		'gandom'   => __( 'گندم (Gandom)', 'zarincode' ),
		'tanha'    => __( 'تنها (Tanha)', 'zarincode' ),
		'yekan'    => __( 'یکان (Yekan)', 'zarincode' ),
		'arad'     => __( 'آراد (Arad)', 'zarincode' ),
		'azad'     => __( 'آزاد (Azad)', 'zarincode' ),
		'ario'     => __( 'آریو (Ario)', 'zarincode' ),
	);
}

/**
 * تبدیل نام فونت قالب به نام خانواده CSS.
 *
 * @param string $font نام فونت (کلید).
 * @return string
 */
function zc_font_css_family( $font ) {
	$map = array(
		'samim'     => 'Samim',
		'vazirmatn' => 'Vazirmatn',
		'shabnam'   => 'Shabnam',
		'gandom'    => 'Gandom',
		'tanha'     => 'Tanha',
		'yekan'     => 'Yekan',
		'arad'      => 'Arad',
		'azad'      => 'Azad',
		'ario'      => 'Ario',
	);

	return isset( $map[ $font ] ) ? $map[ $font ] : 'Samim';
}

/**
 * آدرس رسمی وب‌سایت زرین کد.
 *
 * از گزینه‌ی پنل zc_site_url خوانده می‌شود و در صورت خالی بودن به دامنه‌ی
 * رسمی Zarincode.com بازمی‌گردد. در قراردادها، ایمیل‌ها و کل سایت استفاده
 * می‌شود تا همواره یک آدرس یکپارچه داشته باشیم.
 *
 * @return string
 */
function zc_site_url() {
	return rtrim( (string) zc_opt( 'zc_site_url', 'https://zarincode.com' ), '/' );
}

/**
 * آدرس دامنه بدون پروتکل.
 *
 * @return string
 */
function zc_site_domain() {
	return str_replace( array( 'https://', 'http://', 'www.' ), '', zc_site_url() );
}

/**
 * نام رسمی سایت.
 *
 * @return string
 */
function zc_site_name() {
	return (string) zc_opt( 'zc_site_name', 'زرین کد' );
}

/**
 * ایمیل رسمی زرین کد.
 *
 * @return string
 */
function zc_site_email() {
	return (string) zc_opt( 'zc_email', 'info@' . zc_site_domain() );
}

/**
 * زبان‌های برنامه‌نویسی پایه (از پیش تعریف‌شده در قالب).
 * نام کامپایلر هر زبان با سرویس پیش‌فرض Wandbox سازگار است.
 *
 * @return array
 */
function zc_quiz_base_langs() {
	$langs = array(
		'python'     => array( 'label' => 'Python',            'compiler' => 'cpython-3.13.8',   'ext' => 'py',  'starter' => "print('سلام دنیا')" ),
		'javascript' => array( 'label' => 'JavaScript (Node)', 'compiler' => 'nodejs-20.17.0',   'ext' => 'js',  'starter' => "console.log('سلام دنیا');" ),
		'php'        => array( 'label' => 'PHP',               'compiler' => 'php-8.3.12',       'ext' => 'php', 'starter' => "<?php\necho 'سلام دنیا';\n" ),
		'bash'       => array( 'label' => 'Bash',              'compiler' => 'bash',             'ext' => 'sh',  'starter' => "echo 'سلام دنیا'" ),
		'c'          => array( 'label' => 'C',                 'compiler' => 'gcc-13.2.0-c',     'ext' => 'c',   'starter' => "#include <stdio.h>\nint main() { printf(\"سلام دنیا\\n\"); return 0; }" ),
		'cpp'        => array( 'label' => 'C++',               'compiler' => 'gcc-13.2.0',       'ext' => 'cpp', 'starter' => "#include <iostream>\nint main() { std::cout << \"سلام دنیا\" << std::endl; return 0; }" ),
		'java'       => array( 'label' => 'Java',              'compiler' => 'openjdk-jdk-21+35','ext' => 'java','starter' => 'class Main { public static void main(String[] args) { System.out.println("سلام دنیا"); } }' ),
		'csharp'     => array( 'label' => 'C#',                'compiler' => 'mono-6.12.0.199',  'ext' => 'cs',  'starter' => 'using System; class P { static void Main() { Console.WriteLine("سلام دنیا"); } }' ),
		'ruby'       => array( 'label' => 'Ruby',              'compiler' => 'ruby-3.3.11',      'ext' => 'rb',  'starter' => "puts 'سلام دنیا'" ),
		'go'         => array( 'label' => 'Go',                'compiler' => 'go-1.23.2',        'ext' => 'go',  'starter' => "package main\nimport \"fmt\"\nfunc main() { fmt.Println(\"سلام دنیا\") }" ),
		'rust'       => array( 'label' => 'Rust',              'compiler' => 'rust-1.82.0',      'ext' => 'rs',  'starter' => "fn main() { println!(\"سلام دنیا\"); }" ),
	);

	return $langs;
}

/**
 * زبان‌های سفارشی که مدیر از صفحهٔ «کامپایلر و زبان‌ها» اضافه کرده است.
 *
 * @return array
 */
function zc_quiz_custom_langs() {
	$custom = zc_opt( 'zc_quiz_custom_langs', array() );
	return is_array( $custom ) ? $custom : array();
}

/**
 * تعریف کامل همهٔ زبان‌ها (پایه + سفارشی).
 *
 * @return array
 */
function zc_quiz_language_defs() {
	$base   = zc_quiz_base_langs();
	$custom = array();
	foreach ( zc_quiz_custom_langs() as $c ) {
		if ( empty( $c['key'] ) || empty( $c['label'] ) || empty( $c['compiler'] ) ) {
			continue;
		}
		$custom[ $c['key'] ] = array(
			'label'    => sanitize_text_field( $c['label'] ),
			'compiler' => sanitize_text_field( $c['compiler'] ),
			'ext'      => sanitize_key( $c['ext'] ?? 'txt' ),
			'starter'  => (string) ( $c['starter'] ?? '' ),
		);
	}

	return apply_filters( 'zc_quiz_languages', array_merge( $base, $custom ) );
}

/**
 * کلید زبان‌های فعال (سراسری). اگر خالی باشد همهٔ زبان‌های پایه فعال‌اند.
 *
 * @return array
 */
function zc_quiz_enabled_langs() {
	$enabled = zc_opt( 'zc_quiz_enabled_langs', array() );
	$enabled = is_array( $enabled ) ? array_map( 'strval', $enabled ) : array();
	if ( empty( $enabled ) ) {
		return array_keys( zc_quiz_base_langs() );
	}
	return $enabled;
}

/**
 * آیا یک زبان در لیست فعال (سراسری) قرار دارد؟
 *
 * @param string $key کلید زبان.
 * @return bool
 */
function zc_quiz_lang_enabled( $key ) {
	return in_array( (string) $key, zc_quiz_enabled_langs(), true );
}

/**
 * فهرست زبان‌های قابل استفاده در یک بافتار.
 *
 * @param array|null $allowed اگر آرایهٔ کلید زبان داده شود فقط همان‌ها برمی‌گردد.
 * @return array
 */
function zc_quiz_languages( $allowed = null ) {
	$defs    = zc_quiz_language_defs();
	$enabled = zc_quiz_enabled_langs();
	$allow   = is_array( $allowed ) ? array_map( 'strval', $allowed ) : null;

	$out = array();
	foreach ( $defs as $k => $def ) {
		if ( ! in_array( (string) $k, $enabled, true ) ) {
			continue;
		}
		if ( null !== $allow && ! empty( $allow ) && ! in_array( (string) $k, $allow, true ) ) {
			continue;
		}
		$out[ $k ] = $def;
	}
	return $out;
}

function zc_default_options() {
	return array(
		'zc_color_primary'    => '#C9A227',
		'zc_color_primary_2'  => '#F5D061',
		'zc_color_dark'       => '#141A31',
		'zc_color_dark_2'     => '#0B2187',
		'zc_body_bg'          => '#FAFCFE',
		'zc_radius'           => 18,
		'zc_container'        => 1280,
		'zc_font_family'      => 'samim',
		'zc_font_body'        => 'samim',
		'zc_font_heading'     => 'samim',
		'zc_font_weight'      => 400,
		'zc_heading_weight'   => 700,
		'zc_font_size'        => 15,
		'zc_heading_size'     => 0,
		'zc_font_line_height' => 2,
		'zc_font_letter_spacing' => 0,
		'zc_text_color'       => '#3c4652',
		'zc_heading_color'    => '#141A31',
		'zc_link_color'       => '#8A6D12',
		'zc_btn_bg'           => '#C9A227',
		'zc_btn_text'         => '#241C05',
		'zc_topbar_bg'        => '#0B2187',
		'zc_footer_bg'        => '#0E1226',
		'zc_seo_enable'       => true,
		'zc_seo_home_desc'    => '',
		'zc_seo_default_image' => '',
		'zc_force_fa'         => true,
		'zc_optimize_assets'  => true,
		'zc_lazy_sections'    => false,
		'zc_analytics_enable' => true,
		'zc_preloader'        => true,
		'zc_back_to_top'      => true,
		'zc_sticky_header'    => true,
		'zc_animations'       => true,
		'zc_scroll_progress'  => true,
		'zc_sms_gateway'        => 'kavenegar',
		'zc_sms_welcome_enable' => false,
		'zc_sms_welcome_percent' => '0',
		'zc_sms_welcome_days'   => '30',
		'zc_sms_abandoned_enable' => false,
		'zc_sms_abandoned_hours' => '2',
		'zc_sms_winback_enable' => false,
		'zc_sms_winback_months' => '3',
		'zc_sms_winback_percent' => '30',
		'zc_sms_winback_days'   => '14',
		'zc_sms_winback_cooldown' => '90',
		'zc_lazyload'         => true,
		'zc_rtl'              => true,
		'zc_login_method'     => 'both',
		'zc_wallet_enable'    => true,
		'zc_ticket_enable'    => true,
		'zc_chat_notify_admin'  => true,
		'zc_ticket_notify_admin' => true,
		'zc_ticket_attach'      => true,
		'zc_ticket_rating'      => true,
		'zc_ticket_max_size'    => '5',

		// تقویم شمسی و قراردادها.
		'zc_jalali_enable'      => true,
		'zc_contract_enable'    => true,
		'zc_contract_company'   => 'زرین کد',
		'zc_contract_company_id' => '',
		'zc_contract_ceo'       => '',
		'zc_contract_prefix'    => 'ZC',
		'zc_contract_otp_sms'   => 'کد امضای قرارداد {number}: {code}',
		'zc_payment_stages'     => "پیش‌پرداخت | 40 | 0 | پس از امضای قرارداد و پیش از شروع عملیات اجرایی\nپرداخت میانی | 30 | 50 | با رسیدن پیشرفت پروژه به ۵۰ درصد\nتسویه نهایی | 30 | 100 | پس از تکمیل ۱۰۰ درصد پروژه و پیش از تحویل فایل‌ها",

		// پاداش ربات و کد تخفیف اختصاصی.
		'zc_reward_enable'        => true,
		'zc_reward_bot_percent'   => '20',
		'zc_reward_bot_days'      => '30',
		'zc_reward_bot_sms'       => true,
		'zc_reward_service_enable' => true,
		'zc_reward_service_after' => '7',
		'zc_reward_service_percent' => '25',
		'zc_reward_service_days'  => '14',
		'zc_reward_intro_sms'     => "{name} عزیز، به {site} خوش آمدید!\nبا فعال‌سازی اطلاع‌رسانی در هر ربات، {percent}٪ تخفیف بگیرید (مجموعاً {total}٪):\nتلگرام: {telegram}\nبله: {bale}",
		'zc_reward_bot_sms_text'  => "کد تخفیف {percent}٪ فعال‌سازی {messenger}:\n{code}\nاعتبار {days} روز — {site}",
		'zc_reward_bot_message'   => "🎁 تبریک! اطلاع‌رسانی {messenger} فعال شد.\n\nکد تخفیف اختصاصی {percent}٪ شما:\n<code>{code}</code>\n\nاعتبار: {days} روز\nاین کد با سایر کدهای شما قابل جمع شدن است.",
		'zc_reward_service_sms'   => "{name} عزیز، {percent}٪ تخفیف ویژه‌ی خدمات برنامه‌نویسی {site}:\n{code}\nطراحی سایت، سئو و اجرای پروژه — اعتبار {days} روز\n{url}",

		// چند کد تخفیف هم‌زمان.
		'zc_coupon_stack_enable'  => true,
		'zc_coupon_max_count'     => '3',
		'zc_coupon_max_percent'   => '70',
		'zc_coupon_show_list'     => true,
		'zc_ticket_auto_close'  => '7',
		'zc_chat_start_hour'    => '9',
		'zc_chat_end_hour'      => '18',
		'zc_booking_enable'   => true,
		'zc_chat_enable'      => true,
		'zc_share_enable'     => true,
		'zc_related_enable'   => true,
		'zc_prevnext_enable'  => true,
		'zc_ajax_search'      => true,
		'zc_currency_symbol'  => 'تومان',
		'zc_jalali_date'      => true,
		'zc_disable_gutenberg' => true,
		'zc_sla_urgent'       => 3,
		'zc_sla_high'         => 8,
		'zc_sla_normal'       => 24,
		'zc_sla_low'          => 48,
		'zc_phone'            => '071-42380267',
		'zc_mobile'           => '09024561001',
		'zc_site_url'         => 'https://zarincode.com',
		'zc_email'            => 'info@zarincode.com',
		'zc_address'          => 'استان فارس، شهرستان کازرون، دهستان انارستان، برج سوخته سفلی',
		'zc_address_region'   => 'فارس',
		'zc_address_city'     => 'کازرون',

		// فروشگاه و تسویه حساب.
		'zc_checkout_layout'            => '2col',
		'zc_checkout_auto_fill'         => true,
		'zc_checkout_guest'             => true,
		'zc_checkout_show_coupon'       => true,
		'zc_checkout_show_order_review' => true,
		'zc_checkout_quick_pay'         => false,
		'zc_checkout_remove_order_notes'=> true,
		'zc_checkout_remove_coupon'     => false,
		'zc_checkout_title'             => 'تسویه حساب',
		'zc_checkout_subtitle'          => 'در کمتر از یک دقیقه سفارش خود را ثبت و پرداخت کنید',
		'zc_checkout_fields'            => array(),

		// بازگشت اعتبار خرید (Cashback).
		'zc_cashback_enable'            => false,
		'zc_cashback_percent'           => 5,
		'zc_cashback_exclude_sale'      => false,

		// فاکتور PDF.
		'zc_invoice_enable'             => true,
		'zc_invoice_prefix'             => 'INV',
		'zc_invoice_show_email'         => true,
		'zc_invoice_show_phone'         => true,
		'zc_invoice_show_tax'           => false,
		'zc_invoice_show_discount'      => true,
		'zc_invoice_footer'             => 'سپاسگزاریم از خرید شما. برای دریافت پشتیبانی به پنل کاربری مراجعه کنید.',

		// داشبورد KPI.
		'zc_kpi_enable'                 => true,
		'zc_kpi_days'                   => 30,

		// سیستم معرفی (Affiliate).
		'zc_aff_enable'                 => false,
		'zc_aff_percent'                => 10,
		'zc_aff_min_withdraw'           => '50000',
		'zc_aff_days'                   => 7,

		// بکاپ خودکار.
		'zc_backup_enable'              => false,
		'zc_backup_freq'                => 'daily',
		'zc_backup_send_telegram'       => true,
		'zc_backup_keep_local'          => false,
		'zc_backup_compress'            => true,
		'zc_backup_max'                 => 5,

		// آزمون (Quiz) — ماژول آزمون، تمرین و کامپایلر.
		'zc_quiz_module_enable'         => true,
		'zc_quiz_enable'                => true,
		'zc_practice_enable'            => true,
		'zc_quiz_pass_percent'          => 60,
		'zc_quiz_max_attempts'          => 3,
		'zc_quiz_require_for_cert'      => true,
		'zc_quiz_shuffle'               => false,
		'zc_quiz_challenge'             => true,
		'zc_quiz_exec_enable'           => true,
		'zc_quiz_exec_api'              => 'https://wandbox.org/api/compile.json',
		'zc_quiz_exec_timeout'          => 25,
		'zc_quiz_exec_theme'            => 'dark',
		'zc_quiz_exec_fontsize'         => 14,
		'zc_quiz_exec_show_stdin'       => true,
		'zc_quiz_exec_autorun'          => false,
		'zc_quiz_exec_ratelimit'        => 2,
		'zc_quiz_exec_maxchars'         => 4000,
		'zc_practice_pass'              => 70,
		'zc_quiz_enabled_langs'         => array(),
		'zc_quiz_custom_langs'          => array(),

		// موتور بهینه‌سازی تصویر.
		'zc_image_opt_enable'         => true,
		'zc_image_opt_webp'           => true,
		'zc_image_opt_quality'        => 90,
		'zc_image_opt_delete_original'=> false,
		'zc_image_opt_sizes'          => true,

		// رنگ لوگو.
		'zc_logo_color_1'               => '#C9A227',
		'zc_logo_color_2'               => '#141A31',

		// درگاه‌های پرداخت بیشتر.
		'zc_idpay_enable'               => false,
		'zc_idpay_api'                  => '',
		'zc_idpay_sandbox'              => false,
		'zc_payir_enable'               => false,
		'zc_payir_api'                  => '',
		'zc_payir_sandbox'              => false,
		'zc_cct_enable'                 => false,
		'zc_cct_card_number'            => '',
		'zc_cct_card_holder'            => '',
		'zc_cct_card_bank'              => '',

		// پلتفرم ۳.۳۸.
		'zc_security_headers'           => true,
		'zc_disable_xmlrpc'             => true,
		'zc_admin_login_secret'         => '',
		'zc_lesson_complete_percent'    => 80,
		'zc_installments_enable'        => true,
		'zc_installments_max'           => 4,
		'zc_pwa_enable'                 => true,
		'zc_dark_enable'                => true,
		'zc_whatsapp_enable'            => true,
		'zc_whatsapp_number'            => '',
		'zc_whatsapp_prefill'           => '',
		'zc_whatsapp_token'             => '',
		'zc_whatsapp_phone_id'          => '',
		'zc_update_endpoint'            => '',
		'zc_update_license'             => '',
		'zc_backup_encrypt'             => true,
	);
}

/**
 * خروجی امن SVG آیکن‌های قالب.
 *
 * @param string $name  نام آیکن.
 * @param int    $size  اندازه.
 * @param string $class کلاس اضافی.
 * @return string
 */
function zc_icon( $name, $size = 24, $class = '' ) {
	$icons = zc_icon_library();

	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}

	/*
	 * چرا <use> به‌جای درج مستقیم مسیرها؟
	 *
	 * یک صفحه‌ی معمولی این قالب حدود ۳۴۰ آیکن دارد ولی تنها ۴۰ آیکن
	 * *متفاوت*؛ یعنی همان مسیرهای SVG ده‌ها بار در HTML تکرار می‌شد و
	 * به‌تنهایی حدود ۸۵۰ گره (۳۳٪ کل DOM) می‌ساخت. این یکی از دلایل
	 * اصلی هشدار «Reduce the number of DOM elements» در جی‌متریکس بود.
	 *
	 * حالا تعریف هر آیکن فقط یک‌بار در اسپرایت انتهای صفحه می‌آید و
	 * هر استفاده تنها دو گره (<svg> و <use>) هزینه دارد. هم DOM سبک‌تر
	 * می‌شود، هم حجم HTML به‌شکل محسوسی کاهش می‌یابد.
	 */
	zc_icon_mark_used( $name );

	return sprintf(
		'<svg class="zc-icon zc-icon--%1$s %2$s" width="%3$d" height="%3$d" aria-hidden="true" focusable="false"><use href="#zci-%1$s"></use></svg>',
		esc_attr( $name ),
		esc_attr( $class ),
		(int) $size
	);
}

/**
 * ثبت آیکن‌هایی که در این درخواست واقعاً استفاده شده‌اند.
 *
 * تنها همین‌ها در اسپرایت چاپ می‌شوند تا صفحه‌ای که ۱۲ آیکن دارد،
 * هزینه‌ی تعریف ۶۰ آیکن را نپردازد.
 *
 * @param string $name نام آیکن.
 * @return void
 */
function zc_icon_mark_used( $name ) {
	global $zc_used_icons;

	if ( ! is_array( $zc_used_icons ) ) {
		$zc_used_icons = array();
	}

	$zc_used_icons[ $name ] = true;
}

/**
 * چاپ اسپرایت آیکن‌های استفاده‌شده.
 *
 * در انتهای بدنه قرار می‌گیرد تا در زمان تجزیه‌ی HTML مانع رندر نشود؛
 * مرورگر ارجاع‌های <use> را پس از تعریف نماد هم به‌درستی رسم می‌کند.
 *
 * @return void
 */
function zc_print_icon_sprite() {
	global $zc_used_icons;

	if ( empty( $zc_used_icons ) || ! is_array( $zc_used_icons ) ) {
		return;
	}

	$icons = zc_icon_library();

	echo '<svg xmlns="http://www.w3.org/2000/svg" class="zc-icon-sprite" aria-hidden="true" focusable="false" style="position:absolute;width:0;height:0;overflow:hidden"><defs>';

	foreach ( array_keys( $zc_used_icons ) as $name ) {
		if ( ! isset( $icons[ $name ] ) ) {
			continue;
		}

		printf(
			'<symbol id="zci-%1$s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">%2$s</symbol>',
			esc_attr( $name ),
			$icons[ $name ] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	echo '</defs></svg>';
}
add_action( 'wp_footer', 'zc_print_icon_sprite', 99 );

/**
 * کتابخانه آیکن‌های SVG خطی (بدون وابستگی خارجی برای سرعت بالا).
 *
 * @return array
 */
function zc_icon_library() {
	return array(
		'search'     => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/>',
		'cart'       => '<path d="M2 3h2.2l2.1 12.2a2 2 0 0 0 2 1.6h8.6a2 2 0 0 0 2-1.55L21 8H6"/><circle cx="9.5" cy="20" r="1.4"/><circle cx="17.5" cy="20" r="1.4"/>',
		'user'       => '<circle cx="12" cy="8" r="4"/><path d="M4.5 20a7.5 7.5 0 0 1 15 0"/>',
		'heart'      => '<path d="M12 20s-7.5-4.7-7.5-10A4.5 4.5 0 0 1 12 7.4 4.5 4.5 0 0 1 19.5 10c0 5.3-7.5 10-7.5 10z"/>',
		'menu'       => '<path d="M4 6h16M4 12h16M4 18h16"/>',
		'close'      => '<path d="M6 6l12 12M18 6L6 18"/>',
		'chevron'    => '<path d="m6 9 6 6 6-6"/>',
		'arrow-left' => '<path d="M19 12H5m0 0 6-6m-6 6 6 6"/>',
		'arrow-right' => '<path d="M5 12h14m0 0-6-6m6 6-6 6"/>',
		'arrow-down' => '<path d="M12 5v14m0 0 6-6m-6 6-6-6"/>',
		'arrow-up'   => '<path d="M12 19V5m0 0-6 6m6-6 6 6"/>',
		'arrow-ul'   => '<path d="M17 17 7 7m0 0v8m0-8h8"/>',
		'play'       => '<path d="M7 4.5v15l13-7.5-13-7.5z" fill="currentColor" stroke="none"/>',
		'clock'      => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.2 2"/>',
		'book'       => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5z"/><path d="M20 18v3H6.5A2.5 2.5 0 0 1 4 18.5"/>',
		'video'      => '<rect x="2.5" y="6" width="13" height="12" rx="2.5"/><path d="m15.5 10.5 6-3v9l-6-3z"/>',
		'star'       => '<path d="m12 3.5 2.6 5.3 5.9.9-4.3 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8L3.5 9.7l5.9-.9z"/>',
		'wallet'     => '<path d="M3 7.5A2.5 2.5 0 0 1 5.5 5H18a2 2 0 0 1 2 2v1"/><rect x="3" y="7.5" width="18" height="12.5" rx="2.5"/><circle cx="16.5" cy="14" r="1.3" fill="currentColor" stroke="none"/>',
		'ticket'     => '<path d="M4 8.5A2.5 2.5 0 0 1 6.5 6h11A2.5 2.5 0 0 1 20 8.5v1.2a2.3 2.3 0 0 0 0 4.6v1.2a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 15.5v-1.2a2.3 2.3 0 0 0 0-4.6z"/>',
		'chart'      => '<path d="M4 20V10M10 20V4m6 16v-7m6 7V8"/>',
		'calendar'   => '<rect x="3" y="5" width="18" height="16" rx="2.5"/><path d="M8 3v4m8-4v4M3 10h18"/>',
		'chat'       => '<path d="M20 12.5a7.5 7.5 0 0 1-10.9 6.7L4 20.5l1.4-4.6A7.5 7.5 0 1 1 20 12.5z"/>',
		'bell'       => '<path d="M18 15V10a6 6 0 1 0-12 0v5l-1.5 2.5h15z"/><path d="M10 20a2 2 0 0 0 4 0"/>',
		'download'   => '<path d="M12 3v12m0 0 4.5-4.5M12 15l-4.5-4.5"/><path d="M4 20h16"/>',
		'check'      => '<path d="m5 12.5 4.5 4.5L19 7"/>',
		'shield'     => '<path d="M12 3 5 6v6c0 4.2 2.9 7.8 7 9 4.1-1.2 7-4.8 7-9V6z"/><path d="m9 12 2 2 4-4"/>',
		'code'       => '<path d="m8 8-4 4 4 4m8-8 4 4-4 4M14 5l-4 14"/>',
		'plugin'     => '<path d="M9 3v4H6.5A2.5 2.5 0 0 0 4 9.5V13h3a2 2 0 1 1 0 4H4v3.5A2.5 2.5 0 0 0 6.5 23"/><path d="M9 3h6v4h2.5A2.5 2.5 0 0 1 20 9.5V13h-3a2 2 0 1 0 0 4h3v2.5a2.5 2.5 0 0 1-2.5 2.5H9"/>',
		'font'       => '<path d="M5 20 11 4h2l6 16M8 14h8"/>',
		'phone'      => '<path d="M6 3h3l2 5-2.5 1.5a12 12 0 0 0 6 6L16 13l5 2v3a2 2 0 0 1-2.2 2A17 17 0 0 1 4 5.2 2 2 0 0 1 6 3z"/>',
		'mail'       => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m3.5 7 8.5 6 8.5-6"/>',
		'pin'        => '<path d="M12 21s7-6 7-11a7 7 0 1 0-14 0c0 5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
		'logout'     => '<path d="M15 5V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-1"/><path d="M10 12h11m0 0-3.5-3.5M21 12l-3.5 3.5"/>',
		'edit'       => '<path d="M4 20h4L20 8l-4-4L4 16z"/>',
		'trash'      => '<path d="M4 7h16M9 7V5h6v2m-8 0 1 13h8l1-13"/>',
		'eye'        => '<path d="M2 12s3.8-6.5 10-6.5S22 12 22 12s-3.8 6.5-10 6.5S2 12 2 12z"/><circle cx="12" cy="12" r="2.8"/>',
		'award'      => '<circle cx="12" cy="9" r="5.5"/><path d="m8.5 13.5-1.5 7 5-2.5 5 2.5-1.5-7"/>',
		'users'      => '<circle cx="9" cy="8" r="3.6"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 5a3.6 3.6 0 0 1 0 7m5.5 8a6 6 0 0 0-4-5.5"/>',
		'sparkle'    => '<path d="M12 3.5 13.8 9l5.7 1.9-5.7 1.9L12 18.3l-1.8-5.5L4.5 11 10.2 9z"/>',
		'send'       => '<path d="m21 3-9 18-2.5-7.5L2 11z"/>',
		'filter'     => '<path d="M3 5h18l-7 8v6l-4 2v-8z"/>',
		'grid'       => '<rect x="3.5" y="3.5" width="7" height="7" rx="2"/><rect x="13.5" y="3.5" width="7" height="7" rx="2"/><rect x="3.5" y="13.5" width="7" height="7" rx="2"/><rect x="13.5" y="13.5" width="7" height="7" rx="2"/>',
		'lock'       => '<rect x="4" y="10" width="16" height="11" rx="2.5"/><path d="M8 10V7a4 4 0 1 1 8 0v3"/>',
		'gift'       => '<rect x="3" y="8" width="18" height="4" rx="1.2"/><path d="M5 12v9h14v-9M12 8v13"/><path d="M12 8S10.5 3.5 8 4.5 9.5 8 12 8zm0 0s1.5-4.5 4-3.5S14.5 8 12 8z"/>',
		'refresh'    => '<path d="M20 11a8 8 0 1 0-.6 4"/><path d="M20 5v6h-6"/>',
		'info'       => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8.5v.5"/>',
		'question'   => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.4 2.3c-.6.3-.9.9-.9 1.5v.4m0 3v.3"/>',
		'certificate'=> '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M7 8h10M7 11h5"/><path d="M12 16v5l2.5-1.5L17 21v-5"/>',
		'image'      => '<rect x="3" y="3" width="18" height="18" rx="2.5"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/>',
		'file'       => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 13h6M9 17h6"/>',
		'settings'   => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9v0a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
		'package'    => '<path d="m7.5 4.27 9 5.15"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
		'alert'      => '<path d="M12 9v4"/><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 17h.01"/>',
		'globe'      => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a15 15 0 0 1 0 18 15 15 0 0 1 0-18z"/>',
		'key'        => '<circle cx="7.5" cy="15.5" r="3.5"/><path d="m10 13 8.5-8.5"/><path d="m16 7 2 2"/><path d="m19 4 2 2"/>',
		'tag'        => '<path d="M12.6 2.6A2 2 0 0 0 11.2 2H4a2 2 0 0 0-2 2v7.2a2 2 0 0 0 .6 1.4l8.2 8.2a2 2 0 0 0 2.8 0l7.2-7.2a2 2 0 0 0 0-2.8z"/><circle cx="6.5" cy="6.5" r="1.5"/>',
		'link'       => '<path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/>',
		'target'     => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5"/>',
		'bulb'       => '<path d="M9 18h6"/><path d="M10 22h4"/><path d="M12 2a6 6 0 0 0-3.7 10.7c.5.4.8.9.9 1.5l.1.8h5.4l.1-.8c.1-.6.4-1.1.9-1.5A6 6 0 0 0 12 2z"/>',
		'headphone'  => '<path d="M4 14v-2a8 8 0 1 1 16 0v2"/><rect x="2.5" y="13" width="4.5" height="7" rx="2"/><rect x="17" y="13" width="4.5" height="7" rx="2"/>',
	);
}

/**
 * چاپ آیکن.
 *
 * @param string $name  نام.
 * @param int    $size  اندازه.
 * @param string $class کلاس.
 * @return void
 */
function zc_the_icon( $name, $size = 24, $class = '' ) {
	echo zc_icon( $name, $size, $class ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * تبدیل اعداد انگلیسی به فارسی.
 *
 * @param string|int $num عدد.
 * @return string
 */
function zc_fa_num( $num ) {
	$en = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
	$fa = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );
	return str_replace( $en, $fa, (string) $num );
}

/**
 * فرمت قیمت با واحد پول.
 *
 * @param float $price قیمت.
 * @return string
 */
function zc_price( $price ) {
	if ( function_exists( 'wc_price' ) ) {
		return wc_price( $price );
	}

	return zc_fa_num( number_format( (float) $price ) ) . ' ' . zc_opt( 'zc_currency_symbol', 'تومان' );
}

/**
 * قیمت به صورت متن ساده (بدون تگ HTML).
 *
 * برای جاهایی که خروجی با esc_html() چاپ می‌شود یا داخل ویژگی‌های
 * HTML قرار می‌گیرد؛ چون zc_price() ممکن است مارک‌آپ ووکامرس برگرداند.
 *
 * @param float $price مبلغ.
 * @return string
 */
function zc_price_text( $price ) {
	/*
	 * ووکامرس نماد واحد پول را به صورت entity (مثل &#36;) برمی‌گرداند.
	 * wp_strip_all_tags فقط تگ‌ها را حذف می‌کند نه entity را؛ اگر
	 * رمزگشایی نشود، خروجی پس از esc_html به شکل «&#۳۶;» چاپ می‌شود.
	 */
	$text = wp_strip_all_tags( zc_price( $price ) );

	return trim( html_entity_decode( $text, ENT_QUOTES, 'UTF-8' ) );
}

/**
 * زمان خوانا (چند دقیقه پیش).
 *
 * @param string $date تاریخ.
 * @return string
 */
function zc_human_time( $date ) {
	$ts = is_numeric( $date ) ? $date : strtotime( $date );
	return sprintf(
		/* translators: %s: human readable time difference */
		esc_html__( '%s پیش', 'zarincode' ),
		human_time_diff( $ts, current_time( 'timestamp' ) )
	);
}

/**
 * تصویر شاخص با پشتیبانی lazyload و placeholder.
 *
 * @param int    $post_id شناسه.
 * @param string $size    اندازه.
 * @param array  $attr    ویژگی‌ها.
 * @return string
 */
function zc_thumbnail( $post_id = 0, $size = 'zc-card', $attr = array() ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$attr    = wp_parse_args(
		$attr,
		array(
			'loading' => zc_opt( 'zc_lazyload', true ) ? 'lazy' : 'eager',
			'decoding' => 'async',
			'class'   => 'zc-thumb__img',
		)
	);

	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail( $post_id, $size, $attr );
	}

	return sprintf(
		'<img src="%1$s" alt="%2$s" class="zc-thumb__img zc-thumb__img--placeholder" loading="lazy" decoding="async" width="600" height="400">',
		esc_url( ZC_ASSETS . 'img/placeholder.svg' ),
		esc_attr( get_the_title( $post_id ) )
	);
}

/**
 * محدود کردن متن.
 *
 * @param string $text  متن.
 * @param int    $limit تعداد کلمه.
 * @return string
 */
/**
 * رندر امن بدنه‌ی مطلب در صفحات داخلی — جلوگیری از «صفحه‌ی خالی».
 *
 * the_content() را اجرا و نتیجه را بافر می‌کند. اگر به هر دلیلی خالی
 * برگردد (تداخل افزونه، فیلتر یا داده‌ی خاص)، یک پیام جایگزین نشان
 * می‌دهد تا کاربر هرگز صفحه‌ی کاملاً خالی نبیند.
 *
 * @return void
 */
function zc_the_content_guarded() {
	ob_start();
	the_content();
	wp_link_pages(
		array(
			'before' => '<div class="zc-page-links">' . esc_html__( 'صفحات:', 'zarincode' ),
			'after'  => '</div>',
		)
	);
	$zc_content = ob_get_clean();

	if ( trim( wp_strip_all_tags( $zc_content ) ) !== '' ) {
		echo $zc_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		echo '<p class="zc-single__empty">' . esc_html__( 'محتوای این بخش در حال آماده‌سازی است.', 'zarincode' ) . '</p>';
	}
}

function zc_excerpt( $text, $limit = 20 ) {
	$text = wp_strip_all_tags( strip_shortcodes( $text ) );
	return wp_trim_words( $text, $limit, '…' );
}

/**
 * بررسی فعال بودن ووکامرس.
 *
 * @return bool
 */
function zc_is_woo() {
	return class_exists( 'WooCommerce' );
}

/**
 * بررسی فعال بودن المنتور.
 *
 * @return bool
 */
function zc_is_elementor() {
	return did_action( 'elementor/loaded' );
}

/**
 * آیا صفحه فعلی با المنتور ساخته شده؟
 *
 * @param int $post_id شناسه.
 * @return bool
 */
function zc_built_with_elementor( $post_id = 0 ) {
	if ( ! zc_is_elementor() ) {
		return false;
	}
	$post_id = $post_id ? $post_id : get_the_ID();
	return \Elementor\Plugin::$instance->documents->get( $post_id ) && \Elementor\Plugin::$instance->documents->get( $post_id )->is_built_with_elementor();
}

/**
 * دریافت آدرس صفحه پنل کاربری.
 *
 * @param string $endpoint اندپوینت.
 * @return string
 */
function zc_panel_url( $endpoint = '' ) {
	$page_id = (int) zc_opt( 'zc_panel_page', 0 );
	$url     = $page_id ? get_permalink( $page_id ) : home_url( '/panel/' );
	if ( $endpoint ) {
		$url = add_query_arg( 'tab', $endpoint, $url );
	}
	return $url;
}

/**
 * آدرس صفحه ورود/ثبت‌نام سفارشی.
 *
 * @return string
 */
function zc_login_url() {
	$page_id = (int) zc_opt( 'zc_login_page', 0 );
	return $page_id ? get_permalink( $page_id ) : wp_login_url();
}

/**
 * پاکسازی و اعتبارسنجی موبایل ایرانی.
 *
 * @param string $mobile موبایل.
 * @return string|false
 */
function zc_sanitize_mobile( $mobile ) {
	$mobile = zc_en_num( trim( $mobile ) );
	$mobile = preg_replace( '/[^0-9]/', '', $mobile );

	if ( 0 === strpos( $mobile, '98' ) ) {
		$mobile = '0' . substr( $mobile, 2 );
	}
	if ( 0 === strpos( $mobile, '9' ) && 10 === strlen( $mobile ) ) {
		$mobile = '0' . $mobile;
	}
	if ( ! preg_match( '/^09[0-9]{9}$/', $mobile ) ) {
		return false;
	}
	return $mobile;
}

/**
 * تبدیل اعداد فارسی/عربی به انگلیسی.
 *
 * @param string $str رشته.
 * @return string
 */
function zc_en_num( $str ) {
	$fa = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );
	$ar = array( '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩' );
	$en = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
	return str_replace( $ar, $en, str_replace( $fa, $en, (string) $str ) );
}

/**
 * لاگ داخلی قالب برای دیباگ.
 *
 * @param mixed  $data داده.
 * @param string $tag  برچسب.
 * @return void
 */
function zc_log( $data, $tag = 'ZC' ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( '[' . $tag . '] ' . ( is_scalar( $data ) ? $data : wp_json_encode( $data, JSON_UNESCAPED_UNICODE ) ) ); // phpcs:ignore
	}
}

/**
 * دریافت لیست صفحات برای فیلدهای select.
 *
 * @return array
 */
function zc_pages_list() {
	$pages  = get_pages( array( 'number' => 200 ) );
	$output = array( 0 => esc_html__( '— انتخاب کنید —', 'zarincode' ) );
	foreach ( $pages as $page ) {
		$output[ $page->ID ] = $page->post_title;
	}
	return $output;
}

/**
 * تولید nonce برای اسکریپت‌ها.
 *
 * @return string
 */
function zc_nonce() {
	return wp_create_nonce( 'zc_nonce' );
}

/**
 * بررسی nonce در ای‌جکس.
 *
 * @param string $field نام فیلد.
 * @return void
 */
function zc_check_ajax( $field = 'nonce' ) {
	if ( ! check_ajax_referer( 'zc_nonce', $field, false ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'درخواست نامعتبر است. صفحه را تازه‌سازی کنید.', 'zarincode' ) ), 403 );
	}
}

/**
 * محدودیت نرخ ساده و مشترک برای endpointهای عمومی.
 *
 * @param string $action نام عملیات.
 * @param int    $limit  سقف.
 * @param int    $window پنجره زمانی.
 * @return bool true اگر مجاز باشد.
 */
function zc_rate_limit( $action, $limit = 10, $window = MINUTE_IN_SECONDS ) {
	$ip  = function_exists( 'zc_user_ip' ) ? zc_user_ip() : sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$key = 'zc_rate_' . md5( sanitize_key( $action ) . '|' . $ip );
	$num = (int) get_transient( $key );
	if ( $num >= max( 1, (int) $limit ) ) {
		return false;
	}
	set_transient( $key, $num + 1, max( 1, (int) $window ) );
	return true;
}

/**
 * جایگزین سازگار برای get_page_by_title (منسوخ شده در وردپرس ۶.۲).
 *
 * @param string $title     عنوان.
 * @param string $post_type نوع پست.
 * @return WP_Post|null
 */
function zc_get_post_by_title( $title, $post_type = 'page' ) {
	$query = new WP_Query(
		array(
			'post_type'              => $post_type,
			'title'                  => $title,
			'post_status'            => 'all',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'orderby'                => 'post_date ID',
			'order'                  => 'ASC',
		)
	);

	return ! empty( $query->posts ) ? $query->posts[0] : null;
}

/**
 * فهرست تگ‌ها و ویژگی‌های مجاز برای خروجی حاوی آیکن SVG.
 *
 * توابعی مانند wp_kses_post() تگ <svg> را نمی‌شناسند و آن را به‌طور
 * کامل حذف می‌کنند؛ نتیجه‌اش دکمه‌هایی کاملاً خالی بود (مثلاً فلش‌های
 * صفحه‌بندی). این فهرست همان ویژگی‌هایی را مجاز می‌کند که zc_icon()
 * تولید می‌کند و چیزی بیشتر.
 *
 * @return array
 */
function zc_kses_svg_allowed() {
	$svg = array(
		'svg'      => array(
			'class'           => true,
			'width'           => true,
			'height'          => true,
			'viewbox'         => true,
			'fill'            => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
			'xmlns'           => true,
			'role'            => true,
			'aria-hidden'     => true,
			'aria-label'      => true,
			'focusable'       => true,
			'style'           => true,
		),
		'path'     => array( 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'opacity' => true, 'transform' => true ),
		'rect'     => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'transform' => true ),
		'circle'   => array( 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
		'ellipse'  => array( 'cx' => true, 'cy' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
		'line'     => array( 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true ),
		'polyline' => array( 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true ),
		'polygon'  => array( 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
		'g'        => array( 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'opacity' => true, 'transform' => true, 'class' => true ),
		'defs'     => array(),
		'title'    => array(),
		/*
		 * آیکن‌های قالب به‌صورت اسپرایت (sprite) هستند؛ یعنی تگ <svg> یک
		 * تگ <use> با href="#zci-..." دارد و تعریف واقعی مسیر انتهای صفحه
		 * می‌آید. اگر <use> یا صفت href مجاز نباشد، wp_kses() آن را حذف
		 * می‌کند و آیکن خالی رندر می‌شود (مثل دکمه‌های قبلی/بعدی صفحه‌بندی).
		 */
		'use'      => array(
			'href'        => true,
			'xlink:href'  => true,
			'class'       => true,
			'aria-hidden' => true,
			'fill'        => true,
		),
	);

	return apply_filters( 'zc_kses_svg_allowed', $svg );
}

/**
 * پاک‌سازی امن خروجی HTML که ممکن است آیکن SVG داشته باشد.
 *
 * @param string $html کد ورودی.
 * @return string
 */
function zc_kses_icon( $html ) {
	return wp_kses( $html, array_merge( wp_kses_allowed_html( 'post' ), zc_kses_svg_allowed() ) );
}

/**
 * پاک‌سازی امن کد نمادهای اعتماد.
 *
 * سازمان‌هایی مانند ای‌نماد و ساماندهی کدی می‌دهند که شامل <a> و
 * <img> با ویژگی‌های خاص (referrerpolicy، id پویا و ...) است.
 * wp_kses_post() این ویژگی‌ها را حذف می‌کند و نماد از کار می‌افتد.
 * این تابع فهرست مجاز را دقیقاً به همان چیزی که نمادها لازم دارند
 * گسترش می‌دهد — بدون اجازه دادن به اسکریپت دلخواه.
 *
 * @param string $html کد ورودی.
 * @return string
 */
function zc_kses_badge( $html ) {
	$allowed = array(
		'a'   => array(
			'href'           => true,
			'target'         => true,
			'rel'            => true,
			'referrerpolicy' => true,
			'id'             => true,
			'class'          => true,
			'style'          => true,
			'title'          => true,
		),
		'img' => array(
			'src'      => true,
			'alt'      => true,
			'id'       => true,
			'class'    => true,
			'style'    => true,
			'width'    => true,
			'height'   => true,
			'loading'  => true,
			'decoding' => true,
			'cursor'   => true,
		),
		'div' => array( 'id' => true, 'class' => true, 'style' => true ),
		'span' => array( 'id' => true, 'class' => true, 'style' => true ),
		'p'   => array( 'class' => true, 'style' => true ),
		'iframe' => array(
			'src' => true, 'width' => true, 'height' => true, 'frameborder' => true,
			'allowtransparency' => true, 'scrolling' => true, 'style' => true,
			'loading' => true, 'referrerpolicy' => true, 'allowfullscreen' => true,
			'allow' => true, 'title' => true,
		),
		'svg' => array(
			'viewbox' => true, 'width' => true, 'height' => true, 'fill' => true,
			'xmlns'   => true, 'class' => true, 'role' => true, 'aria-label' => true,
		),
		'path'   => array( 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'opacity' => true ),
		'rect'   => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
		'circle' => array( 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
	);

	$clean = wp_kses( $html, $allowed );
	return function_exists( 'zc_sanitize_badge_html' ) ? zc_sanitize_badge_html( $clean ) : $clean;
}

/**
 * نماد آماده‌ی SVG برای فوتر (ای‌نماد، ساماندهی، زرین‌پال، رسانه دیجیتال).
 *
 * @param string $type نوع نماد.
 * @param string $link لینک اختیاری.
 * @return string
 */
function zc_footer_badge_svg( $type, $link = '' ) {
	$badges = array(
		'enamad'    => '<svg viewBox="0 0 64 64" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="enamad"><path d="M32 4 8 13v18c0 14 10.2 24.6 24 29 13.8-4.4 24-15 24-29V13L32 4z" fill="#1B4D8F"/><path d="M32 9 13 16v15c0 11.5 8.2 20.3 19 24 10.8-3.7 19-12.5 19-24V16L32 9z" fill="#fff" opacity=".12"/><path d="m22 32 7 7 14-14" stroke="#fff" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'samandehi' => '<svg viewBox="0 0 64 64" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="samandehi"><rect x="5" y="5" width="54" height="54" rx="12" fill="#0E7C5A"/><path d="M20 40c0-8 5.4-12 12-12s12 4 12 12" stroke="#fff" stroke-width="4" stroke-linecap="round"/><circle cx="32" cy="22" r="6" fill="#fff"/><path d="M17 46h30" stroke="#8FE3C4" stroke-width="3.5" stroke-linecap="round"/></svg>',
		'zarinpal'  => '<svg viewBox="0 0 64 64" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="zarinpal"><rect x="5" y="5" width="54" height="54" rx="14" fill="#FFCE00"/><path d="M18 26h28M18 26l6-8h16l6 8" stroke="#1A1A1A" stroke-width="3.4" stroke-linejoin="round"/><rect x="18" y="26" width="28" height="20" rx="4" stroke="#1A1A1A" stroke-width="3.4"/><path d="M27 36h10" stroke="#1A1A1A" stroke-width="3.4" stroke-linecap="round"/></svg>',
		'irandigi'  => '<svg viewBox="0 0 64 64" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="digital media"><rect x="5" y="5" width="54" height="54" rx="12" fill="#1D3E8C"/><path d="M14 42V22l9 12 9-12v20" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="45" cy="38" r="5" fill="#E63946"/><path d="M40 24h11" stroke="#fff" stroke-width="3.4" stroke-linecap="round"/></svg>',
	);

	$svg = $badges[ $type ] ?? $badges['enamad'];

	if ( $link ) {
		return '<a href="' . esc_url( $link ) . '" target="_blank" rel="noopener nofollow" style="display:block;width:100%;height:100%">' . $svg . '</a>';
	}

	return $svg;
}

/**
 * اعتبارسنجی کد ملی ایران.
 *
 * الگوریتم رقم کنترل: مجموع وزنی ۹ رقم نخست بر ۱۱ تقسیم و با رقم
 * دهم مقایسه می‌شود.
 *
 * @param string $code کد ملی.
 * @return bool
 */
function zc_valid_national_id( $code ) {
	$code = zc_en_num( trim( (string) $code ) );

	if ( ! preg_match( '/^\d{10}$/', $code ) ) {
		return false;
	}

	// کدهای تکراری مانند ۱۱۱۱۱۱۱۱۱۱ نامعتبرند.
	if ( preg_match( '/^(\d)\1{9}$/', $code ) ) {
		return false;
	}

	$sum = 0;

	for ( $i = 0; $i < 9; $i++ ) {
		$sum += ( (int) $code[ $i ] ) * ( 10 - $i );
	}

	$rem   = $sum % 11;
	$check = (int) $code[9];

	return ( $rem < 2 ) ? ( $check === $rem ) : ( $check === 11 - $rem );
}

/**
 * نشانی آی‌پی بازدیدکننده.
 *
 * برای ثبت در سند امضای دیجیتال استفاده می‌شود.
 *
 * @return string
 */
function zc_user_ip() {
	$keys = ! empty( $_SERVER['HTTP_CF_RAY'] )
		? array( 'HTTP_CF_CONNECTING_IP', 'REMOTE_ADDR' )
		: array( 'REMOTE_ADDR' );

	foreach ( $keys as $key ) {
		if ( empty( $_SERVER[ $key ] ) ) {
			continue;
		}

		$value = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );

		// X-Forwarded-For می‌تواند چند آی‌پی داشته باشد.
		$ip = trim( explode( ',', $value )[0] );

		if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return $ip;
		}
	}

	return '';
}

/* ==========================================================================
   کمکی‌های المنتور (بدون وابستگی به فعال‌بودن افزونه)
   ========================================================================== */

/**
 * لیست قالب‌های ذخیره‌شده المنتور.
 *
 * @param string $type نوع.
 * @return array
 */
function zc_get_elementor_templates( $type = '' ) {
	$options = array( 0 => __( '— انتخاب کنید —', 'zarincode' ) );

	$args = array(
		'post_type'      => array( 'elementor_library', 'zc_template' ),
		'posts_per_page' => 100,
		'post_status'    => 'publish',
	);

	if ( $type ) {
		$args['tax_query'] = array( // phpcs:ignore
			array(
				'taxonomy' => 'elementor_library_type',
				'field'    => 'slug',
				'terms'    => $type,
			),
		);
	}

	$templates = get_posts( $args );
	foreach ( $templates as $tpl ) {
		$options[ $tpl->ID ] = $tpl->post_title . ' (#' . $tpl->ID . ')';
	}

	return $options;
}
