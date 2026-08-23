<?php
/**
 * پیکربندی پنل تنظیمات
 * از Redux Framework استفاده می‌کند و در صورت نبود آن، پنل داخلی فعال می‌شود.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * بررسی فعال بودن Redux.
 *
 * @return bool
 */
function zc_has_redux() {
	return class_exists( 'Redux' ) || class_exists( 'ReduxFramework' );
}

/**
 * فیلدهای شخصی‌سازی متن پیامک‌ها (ساخته‌شده از zc_sms_messages()).
 *
 * @return array
 */
function zc_sms_message_fields() {
	$fields = array(
		array(
			'id'    => 'zc_sms_msg_info',
			'type'  => 'info',
			'style' => 'info',
			'title' => __( 'شخصی‌سازی تک‌تک پیامک‌ها', 'zarincode' ),
			'desc'  => __( 'متن هر پیامک را در این بخش به دلخواه تغییر دهید. برای بازگشت به متن پیش‌فرض، فیلد را خالی کنید یا متن پیش‌فرض را برگردانید. متغیرهای قابل استفاده: {name} نام، {site} سایت، {url} نشانی، {code} کد، {order} سفارش، {amount} مبلغ، {date} تاریخ، {time} ساعت، {percent} درصد، {days} روز، {subject} موضوع، {course} دوره، {ref} کد پیگیری، {number} شماره قرارداد.', 'zarincode' ),
		),
	);

	if ( function_exists( 'zc_sms_messages' ) ) {
		foreach ( zc_sms_messages() as $key => $msg ) {
			$fields[] = array(
				'id'           => 'zc_sms_txt_' . $key,
				'type'         => 'rich',
				'rows'         => 5,
				'title'        => $msg['title'],
				'default'      => $msg['default'],
				'placeholders' => '{name} {site} {code} {order} {amount} {date} {time} {percent} {days} {subject} {course} {ref} {number} {mobile}',
			);
		}
	}

	return $fields;
}

/**
 * ساختار کامل تنظیمات قالب.
 * این آرایه هم برای Redux و هم برای پنل فالبک استفاده می‌شود.
 *
 * @return array
 */
function zc_settings_schema() {
	$schema = array(

		/* ============ عمومی ============ */
		'general' => array(
			'title'  => __( 'تنظیمات عمومی', 'zarincode' ),
			'icon'   => 'el el-home',
			'fields' => array(
				array( 'id' => 'zc_logo', 'type' => 'media', 'title' => __( 'لوگوی هدر', 'zarincode' ), 'desc' => __( 'اندازه پیشنهادی: ۱۸۰×۵۰ پیکسل (فرمت SVG یا PNG)', 'zarincode' ) ),
				array( 'id' => 'zc_logo_footer', 'type' => 'media', 'title' => __( 'لوگوی فوتر', 'zarincode' ) ),
				array( 'id' => 'zc_favicon', 'type' => 'media', 'title' => __( 'فاوآیکون', 'zarincode' ) ),
				array( 'id' => 'zc_site_name_1', 'type' => 'text', 'title' => __( 'نام سایت (بخش اول)', 'zarincode' ), 'default' => 'زرین' ),
				array( 'id' => 'zc_site_name_2', 'type' => 'text', 'title' => __( 'نام سایت (بخش دوم)', 'zarincode' ), 'default' => 'کد' ),
				array( 'id' => 'zc_logo_color_1', 'type' => 'color', 'title' => __( 'رنگ بخش اول لوگو (زرین)', 'zarincode' ), 'default' => '#C9A227', 'desc' => __( 'بخش «زرین» لوگو با این رنگ نمایش داده می‌شود.', 'zarincode' ) ),
				array( 'id' => 'zc_logo_color_2', 'type' => 'color', 'title' => __( 'رنگ بخش دوم لوگو (کد)', 'zarincode' ), 'default' => '#141A31', 'desc' => __( 'بخش «کد» لوگو با این رنگ نمایش داده می‌شود.', 'zarincode' ) ),
				array( 'id' => 'zc_site_tagline', 'type' => 'text', 'title' => __( 'شعار زیر لوگو', 'zarincode' ), 'default' => 'ZARINCODE' ),
				array( 'id' => 'zc_site_layout', 'type' => 'select', 'title' => __( 'چیدمان سایت', 'zarincode' ), 'default' => 'wide', 'options' => array( 'wide' => __( 'تمام عرض', 'zarincode' ), 'boxed' => __( 'باکس‌دار', 'zarincode' ) ) ),
				array( 'id' => 'zc_container', 'type' => 'slider', 'title' => __( 'عرض محتوا (پیکسل)', 'zarincode' ), 'default' => 1280, 'min' => 1000, 'max' => 1600, 'step' => 20 ),
				array( 'id' => 'zc_preloader', 'type' => 'switch', 'title' => __( 'نمایش پیش‌بارگذار', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_back_to_top', 'type' => 'switch', 'title' => __( 'دکمه بازگشت به بالا', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_animations', 'type' => 'switch', 'title' => __( 'انیمیشن‌های اسکرول', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_scroll_progress', 'type' => 'switch', 'title' => __( 'نوار پیشرفت اسکرول', 'zarincode' ), 'subtitle' => __( 'نوار طلایی باریک در بالای صفحه که میزان پیمایش را نشان می‌دهد.', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_currency_symbol', 'type' => 'text', 'title' => __( 'واحد پول', 'zarincode' ), 'default' => 'تومان' ),
				array( 'id' => 'zc_custom_css', 'type' => 'textarea', 'title' => __( 'CSS سفارشی', 'zarincode' ), 'rows' => 8 ),
			),
		),

		/* ============ رنگ و تایپوگرافی ============ */
		'styling' => array(
			'title'  => __( 'رنگ‌بندی و فونت', 'zarincode' ),
			'icon'   => 'el el-brush',
			'fields' => array(
				/* ---------- رنگ‌بندی ---------- */
				array( 'id' => 'zc_color_primary', 'type' => 'color', 'title' => __( 'رنگ اصلی (طلایی)', 'zarincode' ), 'default' => '#C9A227' ),
				array( 'id' => 'zc_color_primary_2', 'type' => 'color', 'title' => __( 'رنگ طلایی روشن', 'zarincode' ), 'default' => '#F5D061' ),
				array( 'id' => 'zc_color_dark', 'type' => 'color', 'title' => __( 'رنگ تیره', 'zarincode' ), 'default' => '#141A31' ),
				array( 'id' => 'zc_color_dark_2', 'type' => 'color', 'title' => __( 'رنگ سرمه‌ای', 'zarincode' ), 'default' => '#0B2187' ),
				array( 'id' => 'zc_body_bg', 'type' => 'color', 'title' => __( 'رنگ پس‌زمینه سایت', 'zarincode' ), 'default' => '#FAFCFE' ),
				array( 'id' => 'zc_text_color', 'type' => 'color', 'title' => __( 'رنگ متن اصلی', 'zarincode' ), 'default' => '#3c4652' ),
				array( 'id' => 'zc_heading_color', 'type' => 'color', 'title' => __( 'رنگ عنوان‌ها', 'zarincode' ), 'default' => '#141A31' ),
				array( 'id' => 'zc_link_color', 'type' => 'color', 'title' => __( 'رنگ لینک‌ها', 'zarincode' ), 'default' => '#8A6D12' ),
				array( 'id' => 'zc_btn_bg', 'type' => 'color', 'title' => __( 'رنگ پس‌زمینه دکمه اصلی', 'zarincode' ), 'default' => '#C9A227' ),
				array( 'id' => 'zc_btn_text', 'type' => 'color', 'title' => __( 'رنگ متن دکمه اصلی', 'zarincode' ), 'default' => '#241C05' ),
				array( 'id' => 'zc_topbar_bg', 'type' => 'color', 'title' => __( 'رنگ نوار بالای سایت', 'zarincode' ), 'default' => '#0B2187' ),
				array( 'id' => 'zc_footer_bg', 'type' => 'color', 'title' => __( 'رنگ پس‌زمینه فوتر', 'zarincode' ), 'default' => '#0E1226' ),
				array( 'id' => 'zc_radius', 'type' => 'slider', 'title' => __( 'گردی گوشه‌ها (پیکسل)', 'zarincode' ), 'default' => 18, 'min' => 0, 'max' => 40 ),
			),
		),

		/* ============ فونت و تایپوگرافی پیشرفته ============ */
		'typography' => array(
			'title'  => __( 'فونت و تایپوگرافی', 'zarincode' ),
			'icon'   => 'el el-font',
			'fields' => array(
				array( 'id' => 'zc_font_body', 'type' => 'select', 'title' => __( 'فونت متن (بدنه سایت)', 'zarincode' ), 'subtitle' => __( 'پیش‌فرض: صمیم', 'zarincode' ), 'default' => 'samim', 'options' => zc_font_options() ),
				array( 'id' => 'zc_font_heading', 'type' => 'select', 'title' => __( 'فونت عنوان‌ها (تیترها)', 'zarincode' ), 'subtitle' => __( 'فونت H1 تا H6 و تیتر بخش‌ها', 'zarincode' ), 'default' => 'samim', 'options' => zc_font_options() ),
				array( 'id' => 'zc_font_weight', 'type' => 'slider', 'title' => __( 'وزن فونت متن', 'zarincode' ), 'default' => 400, 'min' => 100, 'max' => 900, 'step' => 100, 'display_value' => 'text' ),
				array( 'id' => 'zc_heading_weight', 'type' => 'slider', 'title' => __( 'وزن فونت عنوان‌ها', 'zarincode' ), 'default' => 700, 'min' => 100, 'max' => 900, 'step' => 100, 'display_value' => 'text' ),
				array( 'id' => 'zc_font_size', 'type' => 'slider', 'title' => __( 'اندازه فونت پایه (پیکسل)', 'zarincode' ), 'default' => 15, 'min' => 12, 'max' => 20 ),
				array( 'id' => 'zc_heading_size', 'type' => 'slider', 'title' => __( 'اندازه فونت عنوان‌ها (٪)', 'zarincode' ), 'subtitle' => __( 'نسبت به اندازه پایه؛ ۰ یعنی خودکار', 'zarincode' ), 'default' => 0, 'min' => 0, 'max' => 60, 'step' => 5 ),
				array( 'id' => 'zc_font_line_height', 'type' => 'slider', 'title' => __( 'فاصله بین خطوط', 'zarincode' ), 'default' => 2, 'min' => 1.2, 'max' => 2.8, 'step' => 0.1, 'display_value' => 'text' ),
				array( 'id' => 'zc_font_letter_spacing', 'type' => 'slider', 'title' => __( 'فاصله بین حروف (پیکسل)', 'zarincode' ), 'default' => 0, 'min' => 0, 'max' => 5, 'step' => 0.5, 'display_value' => 'text' ),
			),
		),

		/* ============ نقشه ============ */
		'map' => array(
			'title'  => __( 'نقشه', 'zarincode' ),
			'icon'   => 'el el-map-marker',
			'fields' => array(
				array( 'id' => 'zc_neshan_api_key', 'type' => 'text', 'title' => __( 'کلید API نقشه نشان (Neshan)', 'zarincode' ), 'default' => '', 'desc' => __( 'از پنل توسعه‌دهندگان نشان (platform.neshan.org) دریافت کنید. برای نمایش نقشه‌ی نشان لازم است.', 'zarincode' ) ),
			),
		),

		/* ============ گزارش و نمودار ============ */
		'report' => array(
			'title'  => __( 'گزارش و نمودار', 'zarincode' ),
			'icon'   => 'el el-graph',
			'fields' => array(
				array( 'id' => 'zc_chart_primary', 'type' => 'color', 'title' => __( 'رنگ اصلی نمودارها', 'zarincode' ), 'default' => '#C9A227' ),
				array( 'id' => 'zc_chart_secondary', 'type' => 'color', 'title' => __( 'رنگ دوم نمودارها', 'zarincode' ), 'default' => '#2563EB' ),
				array( 'id' => 'zc_chart_tertiary', 'type' => 'color', 'title' => __( 'رنگ سوم نمودارها', 'zarincode' ), 'default' => '#16A34A' ),
				array( 'id' => 'zc_chart_quaternary', 'type' => 'color', 'title' => __( 'رنگ چهارم نمودارها', 'zarincode' ), 'default' => '#DC2626' ),
			),
		),

		/* ============ هدر ============ */
		'header' => array(
			'title'  => __( 'هدر و منو', 'zarincode' ),
			'icon'   => 'el el-website',
			'fields' => array(
				array( 'id' => 'zc_header_style', 'type' => 'select', 'title' => __( 'استایل هدر', 'zarincode' ), 'default' => 'default', 'options' => array( 'default' => __( 'پیش‌فرض', 'zarincode' ), 'centered' => __( 'وسط‌چین', 'zarincode' ), 'minimal' => __( 'مینیمال', 'zarincode' ) ) ),
				array( 'id' => 'zc_header_template', 'type' => 'select', 'title' => __( 'قالب سفارشی هدر (المنتور)', 'zarincode' ), 'options' => 'elementor_templates', 'default' => 0 ),
				array( 'id' => 'zc_sticky_header', 'type' => 'switch', 'title' => __( 'هدر چسبان هنگام اسکرول', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_topbar_enable', 'type' => 'switch', 'title' => __( 'نمایش نوار بالای سایت', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_header_cart', 'type' => 'switch', 'title' => __( 'نمایش آیکن سبد خرید', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_ajax_search', 'type' => 'switch', 'title' => __( 'جستجوی ای‌جکس', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_search_results_count', 'type' => 'slider', 'title' => __( 'تعداد نتایج جستجو', 'zarincode' ), 'default' => 8, 'min' => 3, 'max' => 20 ),
				array( 'id' => 'zc_popular_searches', 'type' => 'text', 'title' => __( 'جستجوهای پرطرفدار', 'zarincode' ), 'default' => 'php, لاراول, ری‌اکت, وردپرس, پایتون', 'desc' => __( 'با کاما جدا کنید', 'zarincode' ) ),
				array( 'id' => 'zc_header_cta_text', 'type' => 'text', 'title' => __( 'متن دکمه هدر', 'zarincode' ), 'default' => 'مشاوره رایگان' ),
				array( 'id' => 'zc_header_cta_link', 'type' => 'text', 'title' => __( 'لینک دکمه هدر', 'zarincode' ), 'default' => '#' ),
			),
		),

		/* ============ فوتر ============ */
		'footer' => array(
			'title'  => __( 'فوتر', 'zarincode' ),
			'icon'   => 'el el-th-large',
			'fields' => array(
				array( 'id' => 'zc_footer_template', 'type' => 'select', 'title' => __( 'قالب سفارشی فوتر (المنتور)', 'zarincode' ), 'options' => 'elementor_templates', 'default' => 0 ),
				array( 'id' => 'zc_footer_about', 'type' => 'textarea', 'title' => __( 'متن درباره ما در فوتر', 'zarincode' ), 'rows' => 5, 'default' => 'زرین کد، مرجع تخصصی آموزش برنامه‌نویسی و فروش محصولات دیجیتال.' ),
				array( 'id' => 'zc_footer_social_text', 'type' => 'text', 'title' => __( 'متن کنار شبکه‌های اجتماعی', 'zarincode' ), 'default' => 'در شبکه‌های اجتماعی همراه ما باشید!' ),
				array( 'id' => 'zc_footer_col1_title', 'type' => 'text', 'title' => __( 'عنوان ستون اول', 'zarincode' ), 'default' => 'دسترسی سریع' ),
				array( 'id' => 'zc_footer_col2_title', 'type' => 'text', 'title' => __( 'عنوان ستون دوم', 'zarincode' ), 'default' => 'لینک‌های مفید' ),
				array( 'id' => 'zc_copyright', 'type' => 'text', 'title' => __( 'متن کپی‌رایت', 'zarincode' ), 'default' => 'تمامی حقوق مادی و معنوی این وبسایت متعلق به زرین کد می‌باشد.' ),
				array( 'id' => 'zc_footer_badges', 'type' => 'slides', 'title' => __( 'نمادهای اعتماد', 'zarincode' ) ),
				array( 'id' => 'zc_footer_badges_html', 'type' => 'textarea', 'title' => __( 'کد HTML نمادهای اعتماد (اختیاری)', 'zarincode' ), 'rows' => 8, 'desc' => __( 'کد HTML ای‌نماد، ساماندهی و زرین‌پال را اینجا بچسبانید. رویدادهای جاوااسکریپت حذف می‌شوند و iframe فقط از میزبان‌های مجاز باقی می‌ماند.', 'zarincode' ) ),
				array( 'id' => 'zc_footer_badge_size', 'type' => 'number', 'title' => __( 'اندازه‌ی نمادهای HTML (پیکسل)', 'zarincode' ), 'default' => 84 ),
			),
		),

		/* ============ اطلاعات تماس ============ */
		'contact' => array(
			'title'  => __( 'اطلاعات تماس', 'zarincode' ),
			'icon'   => 'el el-phone',
			'fields' => array(
				array( 'id' => 'zc_site_url', 'type' => 'text', 'title' => __( 'آدرس وب‌سایت (دامنه)', 'zarincode' ), 'default' => 'https://zarincode.com', 'desc' => __( 'در قراردادها، ایمیل‌ها و سراسر سایت استفاده می‌شود.', 'zarincode' ) ),
				array( 'id' => 'zc_phone', 'type' => 'text', 'title' => __( 'شماره تماس', 'zarincode' ), 'default' => '071-42380267' ),
				array( 'id' => 'zc_mobile', 'type' => 'text', 'title' => __( 'موبایل / واتساپ', 'zarincode' ), 'default' => '09024561001' ),
				array( 'id' => 'zc_email', 'type' => 'text', 'title' => __( 'ایمیل', 'zarincode' ), 'default' => 'info@zarincode.com' ),
				array( 'id' => 'zc_contact_email', 'type' => 'text', 'title' => __( 'ایمیل دریافت فرم تماس', 'zarincode' ) ),
				array( 'id' => 'zc_address', 'type' => 'textarea', 'title' => __( 'آدرس', 'zarincode' ), 'rows' => 3, 'default' => 'استان فارس، شهرستان کازرون، دهستان انارستان، برج سوخته سفلی' ),
				array( 'id' => 'zc_address_region', 'type' => 'text', 'title' => __( 'استان', 'zarincode' ), 'default' => 'فارس' ),
				array( 'id' => 'zc_address_city', 'type' => 'text', 'title' => __( 'شهر', 'zarincode' ), 'default' => 'کازرون' ),
				array( 'id' => 'zc_working_hours', 'type' => 'text', 'title' => __( 'ساعات کاری', 'zarincode' ), 'default' => 'شنبه تا چهارشنبه ۹ تا ۱۸' ),
				array( 'id' => 'zc_map_lat', 'type' => 'text', 'title' => __( 'عرض جغرافیایی', 'zarincode' ), 'default' => '35.7575' ),
				array( 'id' => 'zc_map_lng', 'type' => 'text', 'title' => __( 'طول جغرافیایی', 'zarincode' ), 'default' => '51.4100' ),
			),
		),

		/* ============ شبکه‌های اجتماعی ============ */
		'social' => array(
			'title'  => __( 'شبکه‌های اجتماعی', 'zarincode' ),
			'icon'   => 'el el-share',
			'fields' => array(
				array( 'id' => 'zc_social_telegram', 'type' => 'text', 'title' => __( 'تلگرام', 'zarincode' ) ),
				array( 'id' => 'zc_social_instagram', 'type' => 'text', 'title' => __( 'اینستاگرام', 'zarincode' ) ),
				array( 'id' => 'zc_social_twitter', 'type' => 'text', 'title' => __( 'ایکس (توییتر)', 'zarincode' ) ),
				array( 'id' => 'zc_social_linkedin', 'type' => 'text', 'title' => __( 'لینکدین', 'zarincode' ) ),
				array( 'id' => 'zc_social_youtube', 'type' => 'text', 'title' => __( 'یوتیوب', 'zarincode' ) ),
				array( 'id' => 'zc_social_aparat', 'type' => 'text', 'title' => __( 'آپارات', 'zarincode' ) ),
				array( 'id' => 'zc_social_whatsapp', 'type' => 'text', 'title' => __( 'واتساپ', 'zarincode' ) ),
				array( 'id' => 'zc_social_bale', 'type' => 'text', 'title' => __( 'بله', 'zarincode' ) ),
				array( 'id' => 'zc_social_github', 'type' => 'text', 'title' => __( 'گیت‌هاب', 'zarincode' ) ),
			),
		),

		/* ============ اشتراک‌گذاری ============ */
		'share' => array(
			'title'  => __( 'دکمه‌های اشتراک‌گذاری', 'zarincode' ),
			'icon'   => 'el el-bullhorn',
			'fields' => array(
				array( 'id' => 'zc_share_enable', 'type' => 'switch', 'title' => __( 'فعال‌سازی اشتراک‌گذاری', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_share_auto', 'type' => 'switch', 'title' => __( 'نمایش خودکار در انتهای مطالب', 'zarincode' ), 'default' => false ),
				array( 'id' => 'zc_share_telegram', 'type' => 'switch', 'title' => __( 'تلگرام', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_share_whatsapp', 'type' => 'switch', 'title' => __( 'واتساپ', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_share_bale', 'type' => 'switch', 'title' => __( 'بله', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_share_twitter', 'type' => 'switch', 'title' => __( 'ایکس', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_share_linkedin', 'type' => 'switch', 'title' => __( 'لینکدین', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_share_facebook', 'type' => 'switch', 'title' => __( 'فیسبوک', 'zarincode' ), 'default' => false ),
				array( 'id' => 'zc_share_email', 'type' => 'switch', 'title' => __( 'ایمیل', 'zarincode' ), 'default' => true ),
			),
		),

		/* ============ پیامک کاوه‌نگار ============ */
		'sms' => array(
			'title'  => __( 'پنل پیامک کاوه‌نگار', 'zarincode' ),
			'icon'   => 'el el-comment',
			'fields' => array(
				array( 'id' => 'zc_sms_enable', 'type' => 'switch', 'title' => __( 'فعال‌سازی سرویس پیامک', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_kavenegar_api', 'type' => 'password', 'title' => __( 'کلید API کاوه‌نگار', 'zarincode' ), 'desc' => __( 'از پنل کاوه‌نگار » تنظیمات » کلید وب سرویس دریافت کنید.', 'zarincode' ) ),
				array( 'id' => 'zc_kavenegar_sender', 'type' => 'text', 'title' => __( 'شماره فرستنده', 'zarincode' ), 'desc' => __( 'مثال: 10004346', 'zarincode' ) ),
				array( 'id' => 'zc_kavenegar_template', 'type' => 'text', 'title' => __( 'نام الگوی کد تایید', 'zarincode' ), 'default' => 'verify', 'desc' => __( 'نام الگوی تایید شده در پنل کاوه‌نگار (Verify Lookup)', 'zarincode' ) ),
				array( 'id' => 'zc_sms_otp_text', 'type' => 'text', 'title' => __( 'متن پیامک کد (بدون الگو)', 'zarincode' ), 'default' => 'کد ورود شما به زرین کد: {code}' ),
				array( 'id' => 'zc_otp_expire', 'type' => 'slider', 'title' => __( 'مدت اعتبار کد (ثانیه)', 'zarincode' ), 'default' => 120, 'min' => 60, 'max' => 600, 'step' => 30 ),
				array( 'id' => 'zc_otp_resend', 'type' => 'slider', 'title' => __( 'فاصله ارسال مجدد (ثانیه)', 'zarincode' ), 'default' => 60, 'min' => 30, 'max' => 300, 'step' => 10 ),
				array( 'id' => 'zc_otp_hourly_limit', 'type' => 'slider', 'title' => __( 'حداکثر درخواست در ساعت', 'zarincode' ), 'default' => 5, 'min' => 1, 'max' => 20 ),
				array( 'id' => 'zc_sms_test_mode', 'type' => 'switch', 'title' => __( 'حالت تست (بدون ارسال واقعی)', 'zarincode' ), 'default' => false, 'desc' => __( 'کد در فایل debug.log ثبت می‌شود.', 'zarincode' ) ),
				array( 'id' => 'zc_sms_order_notify', 'type' => 'switch', 'title' => __( 'پیامک ثبت سفارش', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_sms_order_text', 'type' => 'text', 'title' => __( 'متن پیامک سفارش', 'zarincode' ), 'default' => 'سفارش شما با شماره {order} در {site} ثبت شد.' ),
				array( 'id' => 'zc_sms_ticket_notify', 'type' => 'switch', 'title' => __( 'پیامک پاسخ تیکت', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_sms_enroll_notify', 'type' => 'switch', 'title' => __( 'پیامک ثبت‌نام در دوره', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_sms_booking_notify', 'type' => 'switch', 'title' => __( 'پیامک رزرو نوبت', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_sms_gateway', 'type' => 'select', 'title' => __( 'سامانه پیامک', 'zarincode' ), 'options' => array( 'kavenegar' => __( 'کاوه‌نگار', 'zarincode' ) ), 'default' => 'kavenegar', 'desc' => __( 'سامانه‌های دیگر با فیلتر zc_sms_gateways قابل افزودن هستند.', 'zarincode' ) ),
				array( 'id' => 'zc_sms_balance_alert', 'type' => 'switch', 'title' => __( 'هشدار خودکار موجودی پایین', 'zarincode' ), 'default' => true, 'desc' => __( 'وقتی موجودی کاوه‌نگار کمتر از آستانه باشد، در پیشخوان هشدار نمایش داده می‌شود.', 'zarincode' ) ),
				array( 'id' => 'zc_sms_low_balance', 'type' => 'number', 'title' => __( 'آستانه‌ی موجودی (ریال)', 'zarincode' ), 'default' => 50000, 'desc' => __( 'اگر موجودی کمتر از این مقدار شود، هشدار شارژ نمایش داده می‌شود.', 'zarincode' ) ),
				array( 'id' => 'zc_admin_alert_mobile', 'type' => 'text', 'title' => __( 'موبایل مدیر برای هشدار شارژ', 'zarincode' ), 'desc' => __( 'در صورت پایین بودن موجودی، به این شماره پیامک هشدار ارسال می‌شود (یک‌بار در روز).', 'zarincode' ) ),
				array( 'id' => 'zc_admin_alert_email', 'type' => 'text', 'title' => __( 'ایمیل مدیر برای هشدار شارژ', 'zarincode' ), 'desc' => __( 'در صورت پایین بودن موجودی، به این ایمیل هشدار ارسال می‌شود (یک‌بار در روز).', 'zarincode' ) ),

				array( 'id' => 'zc_sms_camp_info', 'type' => 'info', 'title' => __( 'کارزارهای خودکار پیامکی', 'zarincode' ), 'desc' => __( 'متغیرهای قابل استفاده در متن‌ها: {name} نام کاربر، {code} کد تخفیف، {percent} درصد، {days} روز اعتبار، {site} نام سایت، {url} نشانی، {amount} مبلغ، {order} شماره سفارش.', 'zarincode' ) ),

				array( 'id' => 'zc_sms_welcome_enable', 'type' => 'switch', 'title' => __( '۱) پیامک خوش‌آمد به کاربر تازه', 'zarincode' ), 'subtitle' => __( 'بلافاصله پس از ثبت‌نام ارسال می‌شود.', 'zarincode' ), 'default' => false ),
				array( 'id' => 'zc_sms_welcome_percent', 'type' => 'text', 'title' => __( 'درصد تخفیف خوش‌آمد', 'zarincode' ), 'subtitle' => __( 'صفر یعنی بدون کد تخفیف.', 'zarincode' ), 'default' => '0', 'required' => array( 'zc_sms_welcome_enable', '=', true ) ),
				array( 'id' => 'zc_sms_welcome_days', 'type' => 'text', 'title' => __( 'اعتبار کد خوش‌آمد (روز)', 'zarincode' ), 'default' => '30', 'required' => array( 'zc_sms_welcome_enable', '=', true ) ),
				array( 'id' => 'zc_sms_welcome_text', 'type' => 'textarea', 'rows' => 4, 'title' => __( 'متن پیامک خوش‌آمد', 'zarincode' ), 'default' => "{name} عزیز، به {site} خوش آمدید!\nکد تخفیف {percent}٪ شما: {code}\nاعتبار تا {days} روز.", 'required' => array( 'zc_sms_welcome_enable', '=', true ) ),

				array( 'id' => 'zc_sms_abandoned_enable', 'type' => 'switch', 'title' => __( '۲) یادآوری پرداخت ناتمام', 'zarincode' ), 'subtitle' => __( 'برای کاربرانی که به درگاه رفته‌اند ولی پرداخت نکرده‌اند.', 'zarincode' ), 'default' => false ),
				array( 'id' => 'zc_sms_abandoned_hours', 'type' => 'text', 'title' => __( 'چند ساعت پس از ترک درگاه؟', 'zarincode' ), 'subtitle' => __( 'مثلاً ۲ یعنی دو ساعت بعد.', 'zarincode' ), 'default' => '2', 'required' => array( 'zc_sms_abandoned_enable', '=', true ) ),
				array( 'id' => 'zc_sms_abandoned_text', 'type' => 'textarea', 'rows' => 4, 'title' => __( 'متن یادآوری پرداخت', 'zarincode' ), 'default' => "{name} عزیز، سفارش شما در {site} تکمیل نشد.\nبرای ادامه پرداخت:\n{url}", 'required' => array( 'zc_sms_abandoned_enable', '=', true ) ),

				array( 'id' => 'zc_sms_winback_enable', 'type' => 'switch', 'title' => __( '۳) بازگرداندن مشتری غیرفعال', 'zarincode' ), 'subtitle' => __( 'به کاربرانی که مدتی خرید نکرده‌اند کد تخفیف می‌دهد.', 'zarincode' ), 'default' => false ),
				array( 'id' => 'zc_sms_winback_months', 'type' => 'text', 'title' => __( 'بدون خرید به مدت (ماه)', 'zarincode' ), 'subtitle' => __( 'مثلاً ۳ یعنی کسانی که سه ماه خرید نکرده‌اند.', 'zarincode' ), 'default' => '3', 'required' => array( 'zc_sms_winback_enable', '=', true ) ),
				array( 'id' => 'zc_sms_winback_percent', 'type' => 'text', 'title' => __( 'درصد تخفیف بازگشت', 'zarincode' ), 'default' => '30', 'required' => array( 'zc_sms_winback_enable', '=', true ) ),
				array( 'id' => 'zc_sms_winback_days', 'type' => 'text', 'title' => __( 'اعتبار کد بازگشت (روز)', 'zarincode' ), 'default' => '14', 'required' => array( 'zc_sms_winback_enable', '=', true ) ),
				array( 'id' => 'zc_sms_winback_cooldown', 'type' => 'text', 'title' => __( 'فاصله تا پیامک بعدی همان کاربر (روز)', 'zarincode' ), 'subtitle' => __( 'تا این مدت دوباره برای این فرد پیامک بازگشت ارسال نمی‌شود.', 'zarincode' ), 'default' => '90', 'required' => array( 'zc_sms_winback_enable', '=', true ) ),
				array( 'id' => 'zc_sms_winback_text', 'type' => 'textarea', 'rows' => 4, 'title' => __( 'متن پیامک بازگشت', 'zarincode' ), 'default' => "{name} عزیز، دلمان برایتان تنگ شده!\nکد تخفیف {percent}٪ ویژه شما: {code}\nاعتبار {days} روز — {site}", 'required' => array( 'zc_sms_winback_enable', '=', true ) ),
			),
		),

		/* ============ شخصی‌سازی متن پیامک‌ها ============ */
		'sms_messages' => array(
			'title'  => __( 'شخصی‌سازی متن پیامک‌ها', 'zarincode' ),
			'icon'   => 'el el-edit',
			'fields' => zc_sms_message_fields(),
		),

		/* ============ فروشگاه و تسویه حساب ============ */
		'checkout' => array(
			'title'  => __( 'فروشگاه و تسویه حساب', 'zarincode' ),
			'icon'   => 'el el-shopping-cart',
			'fields' => array(
				array( 'id' => 'zc_checkout_info', 'type' => 'info', 'title' => __( 'مدیریت فیلدهای تسویه', 'zarincode' ), 'desc' => __( 'برای شخصی‌سازی، فعال/غیرفعال‌سازی و ویرایش فیلدهای صفحه تسویه، از صفحه «مدیریت فیلدهای تسویه» در زیرمنوی زرین کد استفاده کنید.', 'zarincode' ) ),
				array( 'id' => 'zc_checkout_layout', 'type' => 'select', 'title' => __( 'چیدمان صفحه تسویه', 'zarincode' ), 'default' => '2col', 'options' => array( '2col' => __( 'دو ستونه (فرم + خلاصه سفارش)', 'zarincode' ), '1col' => __( 'تک ستونه (فرم و سپس پرداخت)', 'zarincode' ) ) ),
				array( 'id' => 'zc_checkout_auto_fill', 'type' => 'switch', 'title' => __( 'پر کردن خودکار فیلدها از پروفایل کاربر', 'zarincode' ), 'default' => true, 'desc' => __( 'نام، موبایل و ایمیل کاربرِ واردشده به‌صورت خودکار در فرم قرار می‌گیرد.', 'zarincode' ) ),
				array( 'id' => 'zc_checkout_guest', 'type' => 'switch', 'title' => __( 'اجازه خرید مهمان (بدون ثبت‌نام)', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_checkout_show_coupon', 'type' => 'switch', 'title' => __( 'نمایش صندوق کد تخفیف', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_checkout_show_order_review', 'type' => 'switch', 'title' => __( 'نمایش خلاصه سفارش', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_checkout_quick_pay', 'type' => 'switch', 'title' => __( 'حالت پرداخت سریع (حداقل فیلدها)', 'zarincode' ), 'default' => false, 'desc' => __( 'فقط فیلدهای ضروری (موبایل، نام و پرداخت) نمایش داده می‌شود تا کاربر سریع‌تر پرداخت کند.', 'zarincode' ) ),
				array( 'id' => 'zc_checkout_remove_order_notes', 'type' => 'switch', 'title' => __( 'حذف فیلد «یادداشت سفارش»', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_checkout_title', 'type' => 'text', 'title' => __( 'عنوان صفحه تسویه', 'zarincode' ), 'default' => 'تسویه حساب' ),
				array( 'id' => 'zc_checkout_subtitle', 'type' => 'text', 'title' => __( 'زیرعنوان صفحه تسویه', 'zarincode' ), 'default' => 'در کمتر از یک دقیقه سفارش خود را ثبت و پرداخت کنید' ),
			),
		),

		/* ============ درگاه‌های پرداخت ============ */
		'payment' => array(
			'title'  => __( 'درگاه‌های پرداخت', 'zarincode' ),
			'icon'   => 'el el-credit-card',
			'fields' => array(
				array( 'id' => 'zc_gateways_info', 'type' => 'info', 'title' => __( 'درگاه‌های پرداخت', 'zarincode' ), 'desc' => __( 'هر درگاه را می‌توانید از صفحه «ووکامرس ← تنظیمات ← پرداخت» فعال/غیرفعال و عنوان/توضیحات آن را ویرایش کنید. کلیدهای API را در این بخش وارد کنید.', 'zarincode' ) ),

				array( 'id' => 'zc_zarinpal_merchant', 'type' => 'text', 'title' => __( 'مرچنت کد زرین‌پال', 'zarincode' ), 'desc' => __( 'کد ۳۶ کاراکتری دریافتی از پنل زرین‌پال', 'zarincode' ) ),
				array( 'id' => 'zc_zarinpal_currency', 'type' => 'select', 'title' => __( 'واحد ارسالی زرین‌پال', 'zarincode' ), 'default' => 'IRT', 'options' => array( 'IRT' => __( 'تومان', 'zarincode' ), 'IRR' => __( 'ریال', 'zarincode' ) ) ),
				array( 'id' => 'zc_zarinpal_sandbox', 'type' => 'switch', 'title' => __( 'حالت تست زرین‌پال (Sandbox)', 'zarincode' ), 'default' => false ),

				/* ---------- ای‌دی‌پی ---------- */
				array( 'id' => 'zc_gateway_idpay_info', 'type' => 'info', 'title' => __( 'درگاه ای‌دی‌پی (IdPay)', 'zarincode' ), 'desc' => __( 'درگاه پرداخت ایدپی (idpay.ir).', 'zarincode' ) ),
				array( 'id' => 'zc_idpay_enable', 'type' => 'switch', 'title' => __( 'فعال‌سازی درگاه ای‌دی‌پی', 'zarincode' ), 'default' => false ),
				array( 'id' => 'zc_idpay_api', 'type' => 'password', 'title' => __( 'API Key ای‌دی‌پی', 'zarincode' ), 'required' => array( 'zc_idpay_enable', '=', true ) ),
				array( 'id' => 'zc_idpay_sandbox', 'type' => 'switch', 'title' => __( 'حالت تست ای‌دی‌پی', 'zarincode' ), 'default' => false, 'required' => array( 'zc_idpay_enable', '=', true ) ),

				/* ---------- پی‌آی‌آر ---------- */
				array( 'id' => 'zc_gateway_payir_info', 'type' => 'info', 'title' => __( 'درگاه پی‌آی‌آر (Pay.ir)', 'zarincode' ), 'desc' => __( 'درگاه پرداخت پی‌آی‌آر (pay.ir).', 'zarincode' ) ),
				array( 'id' => 'zc_payir_enable', 'type' => 'switch', 'title' => __( 'فعال‌سازی درگاه پی‌آی‌آر', 'zarincode' ), 'default' => false ),
				array( 'id' => 'zc_payir_api', 'type' => 'password', 'title' => __( 'API Key پی‌آی‌آر', 'zarincode' ), 'required' => array( 'zc_payir_enable', '=', true ) ),
				array( 'id' => 'zc_payir_sandbox', 'type' => 'switch', 'title' => __( 'حالت تست پی‌آی‌آر', 'zarincode' ), 'default' => false, 'required' => array( 'zc_payir_enable', '=', true ) ),

				/* ---------- کارت به کارت ---------- */
				array( 'id' => 'zc_gateway_cct_info', 'type' => 'info', 'title' => __( 'پرداخت کارت به کارت', 'zarincode' ), 'desc' => __( 'کاربر شماره کارت را می‌بیند، پرداخت می‌کند و تصویر رسید/کد پیگیری را ثبت می‌کند؛ پس از تأیید مدیر سفارش تکمیل می‌شود.', 'zarincode' ) ),
				array( 'id' => 'zc_cct_enable', 'type' => 'switch', 'title' => __( 'فعال‌سازی پرداخت کارت به کارت', 'zarincode' ), 'default' => false ),
				array( 'id' => 'zc_cct_card_number', 'type' => 'text', 'title' => __( 'شماره کارت', 'zarincode' ), 'required' => array( 'zc_cct_enable', '=', true ) ),
				array( 'id' => 'zc_cct_card_holder', 'type' => 'text', 'title' => __( 'به نام', 'zarincode' ), 'required' => array( 'zc_cct_enable', '=', true ) ),
				array( 'id' => 'zc_cct_card_bank', 'type' => 'text', 'title' => __( 'نام بانک', 'zarincode' ), 'required' => array( 'zc_cct_enable', '=', true ) ),
			),
		),

		/* ============ کیف پول و حسابداری ============ */
		'wallet' => array(
			'title'  => __( 'کیف پول و حسابداری', 'zarincode' ),
			'icon'   => 'el el-wallet',
			'fields' => array(
				array( 'id' => 'zc_wallet_enable', 'type' => 'switch', 'title' => __( 'فعال‌سازی کیف پول', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_wallet_min_charge', 'type' => 'text', 'title' => __( 'حداقل مبلغ شارژ', 'zarincode' ), 'default' => '10000' ),
				array( 'id' => 'zc_withdraw_min', 'type' => 'text', 'title' => __( 'حداقل مبلغ تسویه', 'zarincode' ), 'default' => '100000' ),
				array( 'id' => 'zc_welcome_gift', 'type' => 'text', 'title' => __( 'هدیه خوش‌آمدگویی به کیف پول', 'zarincode' ), 'default' => '0', 'desc' => __( 'مبلغی که هنگام ثبت‌نام به کیف پول کاربر واریز می‌شود. صفر = غیرفعال', 'zarincode' ) ),

				/* ---------- بازگشت اعتبار خرید (Cashback) ---------- */
				array( 'id' => 'zc_cashback_info', 'type' => 'info', 'title' => __( 'بازگشت اعتبار خرید (Cashback)', 'zarincode' ), 'desc' => __( 'با فعال‌سازی این قابلیت، پس از تکمیل هر سفارش، درصدی از مبلغ پرداخت‌شده (بعد از اعمال کد تخفیف) به کیف پول مشتری واریز می‌شود.', 'zarincode' ) ),
				array( 'id' => 'zc_cashback_enable', 'type' => 'switch', 'title' => __( 'فعال‌سازی بازگشت اعتبار خرید', 'zarincode' ), 'default' => false ),
				array( 'id' => 'zc_cashback_percent', 'type' => 'slider', 'title' => __( 'درصد بازگشت اعتبار', 'zarincode' ), 'default' => 5, 'min' => 1, 'max' => 50, 'step' => 1, 'desc' => __( 'درصدی از مبلغ نهایی (پس از کد تخفیف) که به کیف پول مشتری برمی‌گردد.', 'zarincode' ), 'required' => array( 'zc_cashback_enable', '=', true ) ),
				array( 'id' => 'zc_cashback_exclude_sale', 'type' => 'switch', 'title' => __( 'مستثنی‌کردن محصولات تخفیف‌خورده از cashback', 'zarincode' ), 'default' => false, 'desc' => __( 'اگر فعال شود، محصولاتی که قیمت فروش (sale) دارند از cashback مستثنی می‌شوند.', 'zarincode' ), 'required' => array( 'zc_cashback_enable', '=', true ) ),
			),
		),

		/* ============ دوره‌ها ============ */
		'courses' => array(
			'title'  => __( 'سیستم آموزشی', 'zarincode' ),
			'icon'   => 'el el-graduation-cap',
			'fields' => array(
				array( 'id' => 'zc_course_slug', 'type' => 'text', 'title' => __( 'نامک آدرس دوره‌ها', 'zarincode' ), 'default' => 'course' ),
				array( 'id' => 'zc_courses_per_page', 'type' => 'slider', 'title' => __( 'تعداد دوره در هر صفحه', 'zarincode' ), 'default' => 12, 'min' => 3, 'max' => 36 ),
				array( 'id' => 'zc_course_layout', 'type' => 'select', 'title' => __( 'چیدمان صفحه دوره', 'zarincode' ), 'default' => 'sidebar', 'options' => array( 'sidebar' => __( 'با سایدبار چسبان (مثل مکتب‌خونه)', 'zarincode' ), 'full' => __( 'تمام عرض', 'zarincode' ) ) ),
				array( 'id' => 'zc_show_curriculum', 'type' => 'switch', 'title' => __( 'نمایش سرفصل‌ها', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_course_comments', 'type' => 'switch', 'title' => __( 'فعال‌سازی نظرات دوره', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_certificate_enable', 'type' => 'switch', 'title' => __( 'صدور گواهی پایان دوره', 'zarincode' ), 'default' => true ),
			),
		),

		/* ============ آزمون، تمرین و کامپایلر ============ */
		'quiz_compiler' => array(
			'title'  => __( 'آزمون و کامپایلر', 'zarincode' ),
			'icon'   => 'el el-edit',
			'fields' => array(
				array( 'id' => 'zc_quiz_module_info', 'type' => 'info', 'title' => __( 'ماژول آزمون، تمرین و کامپایلر', 'zarincode' ), 'desc' => __( 'سیستم آزمون دوره‌ها، بخش تمرین کدنویسی پنل کاربری و سرویس اجرای کد را می‌توانید به‌صورت کامل یا جداگانه مدیریت کنید.', 'zarincode' ) ),
				array( 'id' => 'zc_quiz_module_enable', 'type' => 'switch', 'title' => __( 'کلید اصلی ماژول (همهٔ زیربخش‌ها)', 'zarincode' ), 'default' => true, 'desc' => __( 'با خاموش‌کردن این کلید، آزمون دوره، تمرین کدنویسی و اجرای کد همه‌جا غیرفعال می‌شوند.', 'zarincode' ) ),
				array( 'id' => 'zc_quiz_enable', 'type' => 'switch', 'title' => __( 'فعال‌سازی آزمون دوره‌ها', 'zarincode' ), 'default' => true, 'required' => array( 'zc_quiz_module_enable', '=', true ) ),
				array( 'id' => 'zc_practice_enable', 'type' => 'switch', 'title' => __( 'فعال‌سازی بخش «تمرین کدنویسی» در پنل کاربری', 'zarincode' ), 'default' => true, 'required' => array( 'zc_quiz_module_enable', '=', true ) ),

				array( 'id' => 'zc_quiz_threshold_info', 'type' => 'info', 'title' => __( 'حد نصاب قبولی و مدرک', 'zarincode' ), 'desc' => __( 'با رسیدن کاربر به «حد نصاب» (درصد قبولی) و تکمیل دوره، مدرک پایان دوره صادر می‌شود. حد نصاب هر دوره/تمرین را می‌توانید جداگانه در صفحهٔ همان دوره/تمرین تنظیم کنید.', 'zarincode' ) ),
				array( 'id' => 'zc_quiz_pass_percent', 'type' => 'slider', 'title' => __( 'حد نصاب پیش‌فرض آزمون دوره (٪)', 'zarincode' ), 'default' => 60, 'min' => 10, 'max' => 100, 'step' => 5, 'required' => array( 'zc_quiz_enable', '=', true ) ),
				array( 'id' => 'zc_practice_pass', 'type' => 'slider', 'title' => __( 'حد نصاب پیش‌فرض تمرین (٪)', 'zarincode' ), 'default' => 70, 'min' => 10, 'max' => 100, 'step' => 5, 'required' => array( 'zc_practice_enable', '=', true ) ),
				array( 'id' => 'zc_quiz_max_attempts', 'type' => 'slider', 'title' => __( 'حداکثر تعداد تلاش (پیش‌فرض)', 'zarincode' ), 'default' => 3, 'min' => 1, 'max' => 10, 'step' => 1, 'required' => array( 'zc_quiz_enable', '=', true ) ),
				array( 'id' => 'zc_quiz_require_for_cert', 'type' => 'switch', 'title' => __( 'صدور مدرک فقط پس از قبولی در آزمون', 'zarincode' ), 'default' => true, 'desc' => __( 'اگر فعال باشد، گواهی دوره فقط وقتی صادر می‌شود که کاربر به حد نصاب آزمون رسیده باشد.', 'zarincode' ), 'required' => array( 'zc_quiz_enable', '=', true ) ),
				array( 'id' => 'zc_quiz_shuffle', 'type' => 'switch', 'title' => __( 'به‌هم‌ریختن تصادفی ترتیب سوالات', 'zarincode' ), 'default' => false, 'required' => array( 'zc_quiz_enable', '=', true ) ),
				array( 'id' => 'zc_quiz_challenge', 'type' => 'switch', 'title' => __( 'حالت گام‌به‌گام (چالشی) به‌عنوان پیش‌فرض', 'zarincode' ), 'default' => true, 'desc' => __( 'سوال‌ها یکی‌یکی نمایش داده می‌شوند و هر پاسخِ درست، سوال بعدی را باز می‌کند (مثل w3schools). کاربر می‌تواند به «همهٔ سوالات» هم سوییچ کند.', 'zarincode' ), 'required' => array( 'zc_quiz_enable', '=', true ) ),

				array( 'id' => 'zc_quiz_exec_info', 'type' => 'info', 'title' => __( 'شخصی‌سازی محیط کدنویسی', 'zarincode' ), 'desc' => __( 'تنظیمات دقیق ویرایشگر کد و سرویس اجرا برای سوالات کدنویسی و تمرین.', 'zarincode' ) ),
				array( 'id' => 'zc_quiz_exec_enable', 'type' => 'switch', 'title' => __( 'فعال‌سازی اجرای کد', 'zarincode' ), 'default' => true, 'required' => array( 'zc_quiz_module_enable', '=', true ) ),
				array( 'id' => 'zc_quiz_exec_api', 'type' => 'text', 'title' => __( 'آدرس سرویس اجرای کد (API)', 'zarincode' ), 'default' => 'https://wandbox.org/api/compile.json', 'desc' => __( 'فقط اگر سرویس دلخواهی دارید تغییر دهید؛ فرمت خروجی باید با Wandbox سازگار باشد.', 'zarincode' ), 'required' => array( 'zc_quiz_exec_enable', '=', true ) ),
				array( 'id' => 'zc_quiz_exec_timeout', 'type' => 'slider', 'title' => __( 'مهلت اجرای هر کد (ثانیه)', 'zarincode' ), 'default' => 25, 'min' => 5, 'max' => 60, 'step' => 5, 'required' => array( 'zc_quiz_exec_enable', '=', true ) ),
				array( 'id' => 'zc_quiz_exec_ratelimit', 'type' => 'slider', 'title' => __( 'فاصلهٔ مجاز بین اجراها (ثانیه)', 'zarincode' ), 'default' => 2, 'min' => 0, 'max' => 10, 'step' => 1, 'desc' => __( 'برای جلوگیری از سواستفاده؛ ۰ = بدون محدودیت.', 'zarincode' ), 'required' => array( 'zc_quiz_exec_enable', '=', true ) ),
				array( 'id' => 'zc_quiz_exec_maxchars', 'type' => 'slider', 'title' => __( 'سقف حجم خروجی (نویسه)', 'zarincode' ), 'default' => 4000, 'min' => 500, 'max' => 10000, 'step' => 500, 'required' => array( 'zc_quiz_exec_enable', '=', true ) ),
				array( 'id' => 'zc_quiz_exec_theme', 'type' => 'select', 'title' => __( 'تم ویرایشگر کد', 'zarincode' ), 'default' => 'dark', 'options' => array( 'dark' => __( 'تیره', 'zarincode' ), 'light' => __( 'روشن', 'zarincode' ) ), 'required' => array( 'zc_quiz_exec_enable', '=', true ) ),
				array( 'id' => 'zc_quiz_exec_fontsize', 'type' => 'slider', 'title' => __( 'اندازهٔ فونت کد (پیکسل)', 'zarincode' ), 'default' => 14, 'min' => 11, 'max' => 20, 'step' => 1, 'required' => array( 'zc_quiz_exec_enable', '=', true ) ),
				array( 'id' => 'zc_quiz_exec_show_stdin', 'type' => 'switch', 'title' => __( 'نمایش فیلد ورودی استاندارد (stdin)', 'zarincode' ), 'default' => true, 'required' => array( 'zc_quiz_exec_enable', '=', true ) ),
				array( 'id' => 'zc_quiz_exec_autorun', 'type' => 'switch', 'title' => __( 'اجرای خودکار کد هنگام بازشدن سوال', 'zarincode' ), 'default' => false, 'required' => array( 'zc_quiz_exec_enable', '=', true ) ),

				array( 'id' => 'zc_quiz_lang_info', 'type' => 'info', 'title' => __( 'مدیریت زبان‌های برنامه‌نویسی', 'zarincode' ), 'desc' => __( 'فعال/غیرفعال‌کردن زبان‌ها، افزودن زبان سفارشی و تعیین زبان‌های مجاز هر دوره/تمرین در صفحهٔ «کامپایلر و زبان‌ها» و صفحهٔ هر دوره/تمرین انجام می‌شود.', 'zarincode' ) ),
				array(
					'id'      => 'zc_quiz_enabled_langs',
					'type'    => 'checkbox',
					'title'   => __( 'زبان‌های فعال (سراسری)', 'zarincode' ),
					'options' => array_map(
						function ( $l ) {
							return $l['label'];
						},
						zc_quiz_language_defs()
					),
					'default' => array_keys( zc_quiz_base_langs() ),
					'required' => array( 'zc_quiz_module_enable', '=', true ),
				),
			),
		),

		/* ============ تیکتینگ ============ */
		'ticket' => array(
			'title'  => __( 'تیکتینگ و پشتیبانی', 'zarincode' ),
			'icon'   => 'el el-tags',
			'fields' => array(
				array( 'id' => 'zc_ticket_enable', 'type' => 'switch', 'title' => __( 'فعال‌سازی تیکتینگ', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_ticket_departments', 'type' => 'textarea', 'title' => __( 'دپارتمان‌ها (هر خط یکی)', 'zarincode' ), 'rows' => 5, 'default' => "پشتیبانی فنی\nپشتیبانی دوره‌ها\nمالی و پرداخت\nفروش و مشاوره" ),
				array( 'id' => 'zc_ticket_notify_admin', 'type' => 'switch', 'title' => __( 'اطلاع تیکت تازه در تلگرام و بله', 'zarincode' ), 'subtitle' => __( 'با ثبت هر تیکت جدید به مدیران پیام داده می‌شود.', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_ticket_attach', 'type' => 'switch', 'title' => __( 'اجازه پیوست فایل', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_ticket_auto_close', 'type' => 'text', 'title' => __( 'بستن خودکار تیکت بی‌پاسخ (روز)', 'zarincode' ), 'subtitle' => __( 'صفر یعنی غیرفعال.', 'zarincode' ), 'default' => '7' ),
				array( 'id' => 'zc_ticket_rating', 'type' => 'switch', 'title' => __( 'رضایت‌سنجی پس از بستن تیکت', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_sla_urgent', 'type' => 'text', 'title' => __( 'زمان پاسخ تیکت بحرانی (ساعت)', 'zarincode' ), 'default' => '3' ),
				array( 'id' => 'zc_sla_high', 'type' => 'text', 'title' => __( 'زمان پاسخ اولویت زیاد (ساعت)', 'zarincode' ), 'default' => '8' ),
				array( 'id' => 'zc_sla_normal', 'type' => 'text', 'title' => __( 'زمان پاسخ اولویت متوسط (ساعت)', 'zarincode' ), 'default' => '24' ),
				array( 'id' => 'zc_sla_low', 'type' => 'text', 'title' => __( 'زمان پاسخ اولویت کم (ساعت)', 'zarincode' ), 'default' => '48' ),
				array( 'id' => 'zc_ticket_max_size', 'type' => 'slider', 'title' => __( 'حداکثر حجم پیوست (مگابایت)', 'zarincode' ), 'default' => 5, 'min' => 1, 'max' => 30 ),
				array( 'id' => 'zc_ticket_admin_email', 'type' => 'text', 'title' => __( 'ایمیل اطلاع‌رسانی تیکت', 'zarincode' ) ),
				array( 'id' => 'zc_chat_enable', 'type' => 'switch', 'title' => __( 'فعال‌سازی چت آنلاین', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_chat_default_reply', 'type' => 'textarea', 'title' => __( 'پاسخ پیش‌فرض چت', 'zarincode' ), 'rows' => 3 ),
				array( 'id' => 'zc_chat_rules', 'type' => 'textarea', 'title' => __( 'قوانین پاسخ خودکار', 'zarincode' ), 'rows' => 6, 'desc' => __( 'هر خط: کلیدواژه | پاسخ', 'zarincode' ) ),
				array( 'id' => 'zc_chat_telegram_notify', 'type' => 'switch', 'title' => __( 'اطلاع پیام چت در تلگرام', 'zarincode' ), 'default' => false ),
				array( 'id' => 'zc_chat_notify_admin', 'type' => 'switch', 'title' => __( 'اطلاع گفتگوی تازه در تلگرام و بله', 'zarincode' ), 'subtitle' => __( 'با شروع هر گفتگوی جدید، به همه‌ی مدیران تعریف‌شده پیام داده می‌شود.', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_chat_offline_msg', 'type' => 'textarea', 'title' => __( 'پیام خارج از ساعت کاری', 'zarincode' ), 'rows' => 3, 'default' => 'در حال حاضر خارج از ساعت کاری هستیم. پیام خود را بگذارید؛ در اولین فرصت پاسخ می‌دهیم.' ),
				array( 'id' => 'zc_chat_start_hour', 'type' => 'text', 'title' => __( 'ساعت شروع پشتیبانی', 'zarincode' ), 'default' => '9' ),
				array( 'id' => 'zc_chat_end_hour', 'type' => 'text', 'title' => __( 'ساعت پایان پشتیبانی', 'zarincode' ), 'default' => '18' ),
				array( 'id' => 'zc_chat_avatar', 'type' => 'media', 'title' => __( 'آواتار پشتیبان', 'zarincode' ) ),
				array( 'id' => 'zc_chat_auto_replies', 'type' => 'textarea', 'title' => __( 'پاسخ‌های خودکار', 'zarincode' ), 'rows' => 6, 'subtitle' => __( 'هر خط: کلیدواژه | پاسخ', 'zarincode' ), 'default' => "قیمت | برای مشاهده قیمت‌ها به صفحه فروشگاه مراجعه کنید.\nپشتیبانی | تیم پشتیبانی از شنبه تا چهارشنبه ۹ تا ۱۸ پاسخگوست." ),
			),
		),

		/* ============ رزرو نوبت ============ */
		'booking' => array(
			'title'  => __( 'رزرو نوبت', 'zarincode' ),
			'icon'   => 'el el-calendar',
			'fields' => array(
				array( 'id' => 'zc_booking_enable', 'type' => 'switch', 'title' => __( 'فعال‌سازی رزرو نوبت', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_booking_start_hour', 'type' => 'slider', 'title' => __( 'ساعت شروع', 'zarincode' ), 'default' => 9, 'min' => 0, 'max' => 23 ),
				array( 'id' => 'zc_booking_end_hour', 'type' => 'slider', 'title' => __( 'ساعت پایان', 'zarincode' ), 'default' => 18, 'min' => 1, 'max' => 24 ),
				array( 'id' => 'zc_booking_interval', 'type' => 'slider', 'title' => __( 'فاصله هر نوبت (دقیقه)', 'zarincode' ), 'default' => 30, 'min' => 10, 'max' => 120, 'step' => 5 ),
				array( 'id' => 'zc_booking_capacity', 'type' => 'slider', 'title' => __( 'ظرفیت هر بازه', 'zarincode' ), 'default' => 1, 'min' => 1, 'max' => 20 ),
			),
		),

		/* ============ پیام‌رسان‌ها ============ */
		'messengers' => array(
			'title'  => __( 'تلگرام و بله', 'zarincode' ),
			'icon'   => 'el el-paper-plane',
			'fields' => array(
				array( 'id' => 'zc_telegram_enable', 'type' => 'switch', 'title' => __( 'فعال‌سازی تلگرام', 'zarincode' ), 'default' => false ),
				array( 'id' => 'zc_telegram_token', 'type' => 'password', 'title' => __( 'توکن ربات تلگرام', 'zarincode' ), 'desc' => __( 'از @BotFather دریافت کنید.', 'zarincode' ) ),
				array( 'id' => 'zc_telegram_chat_id', 'type' => 'text', 'title' => __( 'آیدی کانال/گروه', 'zarincode' ), 'desc' => __( 'مثال: @zarincode یا -1001234567890', 'zarincode' ) ),
				array( 'id' => 'zc_bale_enable', 'type' => 'switch', 'title' => __( 'فعال‌سازی بله', 'zarincode' ), 'default' => false ),
				array( 'id' => 'zc_bale_token', 'type' => 'password', 'title' => __( 'توکن ربات بله', 'zarincode' ) ),
				array( 'id' => 'zc_bale_chat_id', 'type' => 'text', 'title' => __( 'آیدی کانال بله', 'zarincode' ) ),
				array( 'id' => 'zc_messenger_post_types', 'type' => 'checkbox', 'title' => __( 'ارسال خودکار برای', 'zarincode' ), 'options' => array( 'post' => __( 'نوشته‌ها', 'zarincode' ), 'zc_course' => __( 'دوره‌ها', 'zarincode' ), 'zc_tutorial' => __( 'آموزش‌ها', 'zarincode' ), 'product' => __( 'محصولات', 'zarincode' ) ), 'default' => array( 'post' => '1', 'zc_course' => '1', 'product' => '1' ) ),
				array( 'id' => 'zc_messenger_template', 'type' => 'textarea', 'title' => __( 'قالب پیام', 'zarincode' ), 'rows' => 6, 'default' => "{label}\n\n<b>{title}</b>\n\n{excerpt}{price}\n\n🌐 {site}\n{link}", 'desc' => __( 'شورت‌کدها: {label} {title} {excerpt} {price} {link} {site}', 'zarincode' ) ),
				array( 'id' => 'zc_messenger_btn_text', 'type' => 'text', 'title' => __( 'متن دکمه پیام', 'zarincode' ), 'default' => '🔗 مشاهده در سایت' ),

				array( 'id' => 'zc_bot_divider', 'type' => 'info', 'title' => __( 'ربات اعلان شخصی کاربران', 'zarincode' ), 'desc' => __( 'با تنظیم موارد زیر، کاربران می‌توانند حساب خود را به ربات متصل کنند و اعلان‌های شخصی (پاسخ تیکت، سفارش، دوره جدید و ...) دریافت کنند.', 'zarincode' ) ),
				array( 'id' => 'zc_telegram_bot_username', 'type' => 'text', 'title' => __( 'نام کاربری ربات تلگرام', 'zarincode' ), 'desc' => __( 'بدون @ — مثال: ZarincodeBot', 'zarincode' ) ),
				array( 'id' => 'zc_bale_bot_username', 'type' => 'text', 'title' => __( 'نام کاربری ربات بله', 'zarincode' ), 'desc' => __( 'بدون @', 'zarincode' ) ),
				array( 'id' => 'zc_bot_secret', 'type' => 'text', 'title' => __( 'کلید امنیتی وب‌هوک', 'zarincode' ), 'default' => '', 'desc' => __( 'یک رشته تصادفی؛ به انتهای آدرس وب‌هوک اضافه می‌شود.', 'zarincode' ) ),
				array( 'id' => 'zc_admin_chat_id', 'type' => 'textarea', 'rows' => 3, 'title' => __( 'آیدی چت مدیران', 'zarincode' ), 'desc' => __( 'برای دریافت اعلان گفتگوی آنلاین، تیکت و درخواست پروژه. می‌توانید چند آیدی را با ویرگول، فاصله یا خط تازه از هم جدا کنید؛ پیام برای همه‌ی آن‌ها در تلگرام و بله ارسال می‌شود. کاربران مدیر که ربات را در پنل خود متصل کرده و اعلان «هشدارهای مدیریتی» را روشن کرده باشند نیز خودکار اضافه می‌شوند.', 'zarincode' ) ),
				array( 'id' => 'zc_cron_key', 'type' => 'text', 'title' => __( 'کلید کران خارجی', 'zarincode' ), 'desc' => __( 'برای اجرای صف اعلان‌ها از طریق کران هاست.', 'zarincode' ) ),
			),
		),

		/* ============ حساب کاربری ============ */
		'account' => array(
			'title'  => __( 'حساب کاربری', 'zarincode' ),
			'icon'   => 'el el-user',
			'fields' => array(
				array( 'id' => 'zc_login_page', 'type' => 'select', 'title' => __( 'صفحه ورود و ثبت‌نام', 'zarincode' ), 'options' => 'pages' ),
				array( 'id' => 'zc_panel_page', 'type' => 'select', 'title' => __( 'صفحه پنل کاربری', 'zarincode' ), 'options' => 'pages' ),
				array( 'id' => 'zc_login_method', 'type' => 'select', 'title' => __( 'روش ورود', 'zarincode' ), 'default' => 'both', 'options' => array( 'both' => __( 'پیامک و رمز عبور', 'zarincode' ), 'otp' => __( 'فقط پیامک', 'zarincode' ), 'password' => __( 'فقط رمز عبور', 'zarincode' ) ) ),
				array( 'id' => 'zc_allow_registration', 'type' => 'switch', 'title' => __( 'اجازه ثبت‌نام کاربران', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_verify_mobile_on_register', 'type' => 'switch', 'title' => __( 'تایید موبایل هنگام ثبت‌نام', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_default_role', 'type' => 'select', 'title' => __( 'نقش پیش‌فرض کاربران', 'zarincode' ), 'default' => 'zc_student', 'options' => array( 'zc_student' => __( 'دانشجو', 'zarincode' ), 'customer' => __( 'مشتری', 'zarincode' ), 'subscriber' => __( 'مشترک', 'zarincode' ) ) ),
				array( 'id' => 'zc_block_dashboard', 'type' => 'switch', 'title' => __( 'مسدودسازی پیشخوان وردپرس', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_custom_login_redirect', 'type' => 'switch', 'title' => __( 'انتقال wp-login به صفحه سفارشی', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_login_title', 'type' => 'text', 'title' => __( 'عنوان صفحه ورود', 'zarincode' ), 'default' => 'به زرین کد خوش آمدید' ),
				array( 'id' => 'zc_login_desc', 'type' => 'textarea', 'title' => __( 'توضیحات صفحه ورود', 'zarincode' ), 'rows' => 4 ),
				array( 'id' => 'zc_terms_link', 'type' => 'text', 'title' => __( 'لینک قوانین و مقررات', 'zarincode' ) ),
				array( 'id' => 'zc_login_max_attempts', 'type' => 'slider', 'title' => __( 'حداکثر تلاش ناموفق ورود', 'zarincode' ), 'default' => 8, 'min' => 3, 'max' => 30 ),
			),
		),

		/* ============ بلاگ ============ */
		'blog' => array(
			'title'  => __( 'بلاگ و نوشته‌ها', 'zarincode' ),
			'icon'   => 'el el-pencil',
			'fields' => array(
				array( 'id' => 'zc_blog_layout', 'type' => 'select', 'title' => __( 'چیدمان بلاگ', 'zarincode' ), 'default' => 'grid', 'options' => array( 'grid' => __( 'شبکه‌ای', 'zarincode' ), 'list' => __( 'لیستی', 'zarincode' ), 'masonry' => __( 'آجری', 'zarincode' ) ) ),
				array( 'id' => 'zc_blog_columns', 'type' => 'select', 'title' => __( 'تعداد ستون', 'zarincode' ), 'default' => '3', 'options' => array( '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴' ) ),
				array( 'id' => 'zc_blog_sidebar', 'type' => 'select', 'title' => __( 'سایدبار بلاگ', 'zarincode' ), 'default' => 'right', 'options' => array( 'right' => __( 'راست', 'zarincode' ), 'left' => __( 'چپ', 'zarincode' ), 'none' => __( 'بدون سایدبار', 'zarincode' ) ) ),
				array( 'id' => 'zc_related_enable', 'type' => 'switch', 'title' => __( 'نمایش مطالب مرتبط', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_related_count', 'type' => 'slider', 'title' => __( 'تعداد مطالب مرتبط', 'zarincode' ), 'default' => 3, 'min' => 2, 'max' => 6 ),
				array( 'id' => 'zc_prevnext_enable', 'type' => 'switch', 'title' => __( 'نمایش نوشته قبلی/بعدی', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_author_box', 'type' => 'switch', 'title' => __( 'نمایش باکس نویسنده', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_reading_progress', 'type' => 'switch', 'title' => __( 'نوار پیشرفت مطالعه', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_toc_enable', 'type' => 'switch', 'title' => __( 'فهرست مطالب خودکار', 'zarincode' ), 'default' => true ),
			),
		),

		/* ============ سئو و سرعت ============ */
		'contracts' => array(
			'title'  => __( 'قراردادها و تقویم', 'zarincode' ),
			'icon'   => 'el el-file-edit',
			'fields' => array(
				array( 'id' => 'zc_jalali_enable', 'type' => 'switch', 'title' => __( 'تقویم شمسی', 'zarincode' ), 'subtitle' => __( 'همه‌ی تاریخ‌های سایت، پیشخوان و ووکامرس شمسی می‌شوند و فیلدهای تاریخ، تاریخ‌گزین شمسی می‌گیرند.', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_contract_enable', 'type' => 'switch', 'title' => __( 'سامانه‌ی قراردادها', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_contract_company', 'type' => 'text', 'title' => __( 'نام طرف اول (مجری)', 'zarincode' ), 'default' => 'زرین کد' ),
				array( 'id' => 'zc_contract_company_id', 'type' => 'text', 'title' => __( 'شناسه/شماره ثبت شرکت', 'zarincode' ), 'default' => '' ),
				array( 'id' => 'zc_contract_ceo', 'type' => 'text', 'title' => __( 'نام امضاکننده‌ی مجری', 'zarincode' ), 'default' => '' ),
				array( 'id' => 'zc_contract_prefix', 'type' => 'text', 'title' => __( 'پیشوند شماره قرارداد', 'zarincode' ), 'default' => 'ZC', 'desc' => __( 'نمونه‌ی خروجی: ZC-۱۴۰۴-۱۲', 'zarincode' ) ),
				array( 'id' => 'zc_payment_stages', 'type' => 'textarea', 'rows' => 5, 'title' => __( 'مراحل پیش‌فرض پرداخت', 'zarincode' ), 'subtitle' => __( 'هر خط: عنوان | درصد مبلغ | درصد پیشرفت آزادکننده | توضیح — مجموع درصدها باید ۱۰۰ باشد. این مقدار برای الگوهایی استفاده می‌شود که مراحل اختصاصی ندارند.', 'zarincode' ), 'default' => "پیش‌پرداخت | 40 | 0 | پس از امضای قرارداد و پیش از شروع عملیات اجرایی\nپرداخت میانی | 30 | 50 | با رسیدن پیشرفت پروژه به ۵۰ درصد\nتسویه نهایی | 30 | 100 | پس از تکمیل ۱۰۰ درصد پروژه و پیش از تحویل فایل‌ها" ),
				array( 'id' => 'zc_contract_otp_sms', 'type' => 'textarea', 'title' => __( 'متن پیامک کد امضا', 'zarincode' ), 'subtitle' => __( 'شناسه‌های مجاز: {code} {number} {site}', 'zarincode' ), 'default' => 'کد امضای قرارداد {number}: {code}', 'rows' => 3 ),
			),
		),

		'rewards' => array(
			'title'  => __( 'پاداش و کد تخفیف', 'zarincode' ),
			'icon'   => 'el el-gift',
			'fields' => array(
				array( 'id' => 'zc_reward_info', 'type' => 'info', 'title' => __( 'شناسه‌های متن پیامک', 'zarincode' ), 'desc' => __( '{name} نام کاربر — {code} کد تخفیف — {percent} درصد — {total} مجموع درصد — {days} روز اعتبار — {site} نام سایت — {url} نشانی — {telegram} لینک ربات تلگرام — {bale} لینک ربات بله — {messenger} نام پیام‌رسان', 'zarincode' ) ),

				array( 'id' => 'zc_reward_enable', 'type' => 'switch', 'title' => __( 'سامانه‌ی پاداش فعال‌سازی ربات', 'zarincode' ), 'subtitle' => __( 'به کاربر تازه پیامک معرفی ربات‌ها می‌فرستد و برای فعال‌سازی هر ربات کد تخفیف می‌دهد.', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_reward_bot_percent', 'type' => 'text', 'title' => __( 'درصد تخفیف هر ربات', 'zarincode' ), 'subtitle' => __( 'کاربر با فعال‌سازی هر دو ربات، دو برابر این مقدار تخفیف می‌گیرد.', 'zarincode' ), 'default' => '20', 'required' => array( 'zc_reward_enable', '=', true ) ),
				array( 'id' => 'zc_reward_bot_days', 'type' => 'text', 'title' => __( 'اعتبار کد ربات (روز)', 'zarincode' ), 'default' => '30', 'required' => array( 'zc_reward_enable', '=', true ) ),
				array( 'id' => 'zc_reward_intro_sms', 'type' => 'textarea', 'rows' => 5, 'title' => __( 'متن پیامک ثبت‌نام', 'zarincode' ), 'subtitle' => __( 'همراه لینک مستقیم دو ربات ارسال می‌شود و فقط یک‌بار برای هر کاربر می‌رود.', 'zarincode' ), 'default' => "{name} عزیز، به {site} خوش آمدید!\nبا فعال‌سازی اطلاع‌رسانی در هر ربات، {percent}٪ تخفیف بگیرید (مجموعاً {total}٪):\nتلگرام: {telegram}\nبله: {bale}", 'required' => array( 'zc_reward_enable', '=', true ) ),
				array( 'id' => 'zc_reward_bot_sms', 'type' => 'switch', 'title' => __( 'پیامک تأیید پس از فعال‌سازی ربات', 'zarincode' ), 'default' => true, 'required' => array( 'zc_reward_enable', '=', true ) ),
				array( 'id' => 'zc_reward_bot_sms_text', 'type' => 'textarea', 'rows' => 3, 'title' => __( 'متن پیامک پاداش ربات', 'zarincode' ), 'default' => "کد تخفیف {percent}٪ فعال‌سازی {messenger}:\n{code}\nاعتبار {days} روز — {site}", 'required' => array( 'zc_reward_bot_sms', '=', true ) ),
				array( 'id' => 'zc_reward_bot_message', 'type' => 'textarea', 'rows' => 5, 'title' => __( 'پیام تبریک داخل ربات', 'zarincode' ), 'default' => "🎁 تبریک! اطلاع‌رسانی {messenger} فعال شد.\n\nکد تخفیف اختصاصی {percent}٪ شما:\n<code>{code}</code>\n\nاعتبار: {days} روز\nاین کد با سایر کدهای شما قابل جمع شدن است.", 'required' => array( 'zc_reward_enable', '=', true ) ),

				array( 'id' => 'zc_reward_service_enable', 'type' => 'switch', 'title' => __( 'کد تخفیف خدمات پس از چند روز', 'zarincode' ), 'subtitle' => __( 'برای افزایش فروش خدمات برنامه‌نویسی؛ روزانه بررسی و ارسال می‌شود.', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_reward_service_after', 'type' => 'text', 'title' => __( 'چند روز پس از ثبت‌نام؟', 'zarincode' ), 'default' => '7', 'required' => array( 'zc_reward_service_enable', '=', true ) ),
				array( 'id' => 'zc_reward_service_percent', 'type' => 'text', 'title' => __( 'درصد تخفیف خدمات', 'zarincode' ), 'default' => '25', 'required' => array( 'zc_reward_service_enable', '=', true ) ),
				array( 'id' => 'zc_reward_service_days', 'type' => 'text', 'title' => __( 'اعتبار کد خدمات (روز)', 'zarincode' ), 'default' => '14', 'required' => array( 'zc_reward_service_enable', '=', true ) ),
				array( 'id' => 'zc_reward_service_sms', 'type' => 'textarea', 'rows' => 5, 'title' => __( 'متن پیامک خدمات', 'zarincode' ), 'default' => "{name} عزیز، {percent}٪ تخفیف ویژه‌ی خدمات برنامه‌نویسی {site}:\n{code}\nطراحی سایت، سئو و اجرای پروژه — اعتبار {days} روز\n{url}", 'required' => array( 'zc_reward_service_enable', '=', true ) ),

				array( 'id' => 'zc_coupon_stack_enable', 'type' => 'switch', 'title' => __( 'استفاده‌ی هم‌زمان از چند کد تخفیف', 'zarincode' ), 'subtitle' => __( 'قفل individual_use را از کدهای خودکار قالب برمی‌دارد تا روی هم جمع شوند.', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_coupon_max_count', 'type' => 'text', 'title' => __( 'حداکثر تعداد کد هم‌زمان', 'zarincode' ), 'subtitle' => __( 'صفر یعنی بدون محدودیت.', 'zarincode' ), 'default' => '3', 'required' => array( 'zc_coupon_stack_enable', '=', true ) ),
				array( 'id' => 'zc_coupon_max_percent', 'type' => 'text', 'title' => __( 'سقف مجموع درصد تخفیف', 'zarincode' ), 'subtitle' => __( 'مانع می‌شود مجموع کدها از این مقدار بیشتر شود.', 'zarincode' ), 'default' => '70', 'required' => array( 'zc_coupon_stack_enable', '=', true ) ),
				array( 'id' => 'zc_coupon_show_list', 'type' => 'switch', 'title' => __( 'نمایش کدهای کاربر در سبد خرید', 'zarincode' ), 'default' => true ),
			),
		),

		/* ============ افزونه‌های تکمیلی (فاکتور / KPI / معرفی / بکاپ) ============ */
		'extras' => array(
			'title'  => __( 'افزونه‌های تکمیلی', 'zarincode' ),
			'icon'   => 'el el-plus',
			'fields' => array(

				/* ---------- فاکتور PDF ---------- */
				array( 'id' => 'zc_invoice_info', 'type' => 'info', 'title' => __( 'فاکتور رسمی سفارش (PDF)', 'zarincode' ), 'desc' => __( 'به کاربران اجازه می‌دهد فاکتور هر سفارش را در پنل کاربری دانلود و چاپ کنند.', 'zarincode' ) ),
				array( 'id' => 'zc_invoice_enable', 'type' => 'switch', 'title' => __( 'فعال‌سازی دانلود فاکتور', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_invoice_prefix', 'type' => 'text', 'title' => __( 'پیشوند شماره فاکتور', 'zarincode' ), 'default' => 'INV', 'desc' => __( 'مثال: INV-۱۴۰۴-۱۲', 'zarincode' ), 'required' => array( 'zc_invoice_enable', '=', true ) ),
				array( 'id' => 'zc_invoice_show_email', 'type' => 'switch', 'title' => __( 'نمایش ایمیل روی فاکتور', 'zarincode' ), 'default' => true, 'required' => array( 'zc_invoice_enable', '=', true ) ),
				array( 'id' => 'zc_invoice_show_phone', 'type' => 'switch', 'title' => __( 'نمایش موبایل روی فاکتور', 'zarincode' ), 'default' => true, 'required' => array( 'zc_invoice_enable', '=', true ) ),
				array( 'id' => 'zc_invoice_show_tax', 'type' => 'switch', 'title' => __( 'نمایش مالیات', 'zarincode' ), 'default' => false, 'required' => array( 'zc_invoice_enable', '=', true ) ),
				array( 'id' => 'zc_invoice_show_discount', 'type' => 'switch', 'title' => __( 'نمایش تخفیف‌ها', 'zarincode' ), 'default' => true, 'required' => array( 'zc_invoice_enable', '=', true ) ),
				array( 'id' => 'zc_invoice_footer', 'type' => 'textarea', 'title' => __( 'متن پایین فاکتور', 'zarincode' ), 'rows' => 3, 'default' => 'سپاسگزاریم از خرید شما. برای دریافت پشتیبانی به پنل کاربری مراجعه کنید.', 'required' => array( 'zc_invoice_enable', '=', true ) ),

				/* ---------- داشبورد KPI ---------- */
				array( 'id' => 'zc_kpi_info', 'type' => 'info', 'title' => __( 'داشبورد اجرایی (KPI)', 'zarincode' ), 'desc' => __( 'نمایش یک‌جا آمار کلیدی فروش، درآمد، مشترک‌ها و عملکرد در یک صفحه.', 'zarincode' ) ),
				array( 'id' => 'zc_kpi_enable', 'type' => 'switch', 'title' => __( 'فعال‌سازی داشبورد KPI', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_kpi_days', 'type' => 'slider', 'title' => __( 'بازه‌ی پیش‌فرض نمودار (روز)', 'zarincode' ), 'default' => 30, 'min' => 7, 'max' => 365, 'step' => 1, 'required' => array( 'zc_kpi_enable', '=', true ) ),

				/* ---------- سیستم معرفی (Affiliate) ---------- */
				array( 'id' => 'zc_aff_info', 'type' => 'info', 'title' => __( 'سیستم معرفی و همکاری در فروش (Affiliate)', 'zarincode' ), 'desc' => __( 'هر کاربر لینک اختصاصی معرفی می‌گیرد و برای هر فروشِ ثبت‌شده از طریق آن، کمیسیون به کیف پولش واریز می‌شود.', 'zarincode' ) ),
				array( 'id' => 'zc_aff_enable', 'type' => 'switch', 'title' => __( 'فعال‌سازی سیستم معرفی', 'zarincode' ), 'default' => false ),
				array( 'id' => 'zc_aff_percent', 'type' => 'slider', 'title' => __( 'درصد کمیسیون', 'zarincode' ), 'default' => 10, 'min' => 1, 'max' => 90, 'step' => 1, 'desc' => __( 'درصدی از مبلغ نهاییِ پرداخت‌شده که به معرف‌کننده پرداخت می‌شود.', 'zarincode' ), 'required' => array( 'zc_aff_enable', '=', true ) ),
				array( 'id' => 'zc_aff_min_withdraw', 'type' => 'text', 'title' => __( 'حداقل برداشت کمیسیون (تومان)', 'zarincode' ), 'default' => '50000', 'desc' => __( 'کاربر برای درخواست تسویه باید به این مبلغ رسیده باشد.', 'zarincode' ), 'required' => array( 'zc_aff_enable', '=', true ) ),
				array( 'id' => 'zc_aff_days', 'type' => 'slider', 'title' => __( 'روز تأیید کمیسیون پس از خرید', 'zarincode' ), 'default' => 7, 'min' => 0, 'max' => 60, 'step' => 1, 'desc' => __( 'کمیسیون پس از این مدت (برای جلوگیری از بازگشت وجه) قطعی می‌شود. صفر = بلافاصله.', 'zarincode' ), 'required' => array( 'zc_aff_enable', '=', true ) ),

				/* ---------- بکاپ خودکار ---------- */
				array( 'id' => 'zc_backup_info', 'type' => 'info', 'title' => __( 'بکاپ خودکار دیتابیس', 'zarincode' ), 'desc' => __( 'پشتیبان‌گیری خودکار از دیتابیس و ارسال به تلگرام/بله یا ذخیره‌ی محلی.', 'zarincode' ) ),
				array( 'id' => 'zc_backup_enable', 'type' => 'switch', 'title' => __( 'فعال‌سازی بکاپ خودکار', 'zarincode' ), 'default' => false ),
				array( 'id' => 'zc_backup_freq', 'type' => 'select', 'title' => __( 'فرکانس بکاپ', 'zarincode' ), 'default' => 'daily', 'options' => array( 'daily' => __( 'روزانه', 'zarincode' ), 'weekly' => __( 'هفتگی', 'zarincode' ), 'monthly' => __( 'ماهانه', 'zarincode' ) ), 'required' => array( 'zc_backup_enable', '=', true ) ),
				array( 'id' => 'zc_backup_send_telegram', 'type' => 'switch', 'title' => __( 'ارسال به تلگرام/بله', 'zarincode' ), 'default' => true, 'desc' => __( 'بکاپ به کانال/گروه تنظیم‌شده در «تلگرام و بله» ارسال می‌شود.', 'zarincode' ), 'required' => array( 'zc_backup_enable', '=', true ) ),
				array( 'id' => 'zc_backup_keep_local', 'type' => 'switch', 'title' => __( 'ذخیره‌ی محلی همزمان', 'zarincode' ), 'default' => false, 'desc' => __( 'علاوه بر ارسال به پیام‌رسان، یک نسخه در پوشه‌ی محافظت‌شده نگهداری شود.', 'zarincode' ), 'required' => array( 'zc_backup_enable', '=', true ) ),
				array( 'id' => 'zc_backup_compress', 'type' => 'switch', 'title' => __( 'فشرده‌سازی Gzip', 'zarincode' ), 'default' => true, 'desc' => __( 'حجم فایل بکاپ و مصرف پهنای باند را کاهش می‌دهد.', 'zarincode' ), 'required' => array( 'zc_backup_enable', '=', true ) ),
				array( 'id' => 'zc_backup_max', 'type' => 'slider', 'title' => __( 'حداکثر تعداد بکاپ محلی', 'zarincode' ), 'default' => 5, 'min' => 1, 'max' => 30, 'step' => 1, 'required' => array( 'zc_backup_enable', '=', true ) ),
				array( 'id' => 'zc_backup_encrypt', 'type' => 'switch', 'title' => __( 'رمزنگاری فایل بکاپ', 'zarincode' ), 'default' => true, 'desc' => __( 'پس از Gzip، فایل با AES-256 رمز می‌شود و با پسوند .enc ارسال می‌گردد.', 'zarincode' ), 'required' => array( 'zc_backup_enable', '=', true ) ),
			),
		),

		/* ============ سئو و بهینه‌سازی ============ */
		'performance' => array(
			'title'  => __( 'سئو و بهینه‌سازی', 'zarincode' ),
			'icon'   => 'el el-dashboard',
			'fields' => array(
				array( 'id' => 'zc_seo_enable', 'type' => 'switch', 'title' => __( 'متاتگ‌های سئو داخلی', 'zarincode' ), 'default' => true, 'desc' => __( 'در صورت استفاده از یواست یا رنک‌مث، خودکار غیرفعال می‌شود.', 'zarincode' ) ),
				array( 'id' => 'zc_schema_enable', 'type' => 'switch', 'title' => __( 'داده‌های ساختاریافته (Schema)', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_lazyload', 'type' => 'switch', 'title' => __( 'بارگذاری تنبل تصاویر', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_disable_gutenberg', 'type' => 'switch', 'title' => __( 'غیرفعال‌سازی گوتنبرگ', 'zarincode' ), 'subtitle' => __( 'ویرایشگر کلاسیک و المنتور جایگزین می‌شوند و استایل‌های بلوکی حذف می‌شود.', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_remove_block_css', 'type' => 'switch', 'title' => __( 'حذف CSS بلوک‌های اضافی', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_remove_emoji', 'type' => 'switch', 'title' => __( 'حذف اسکریپت ایموجی', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_remove_jquery_migrate', 'type' => 'switch', 'title' => __( 'حذف jQuery Migrate', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_remove_dashicons', 'type' => 'switch', 'title' => __( 'حذف Dashicons برای مهمان', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_disable_heartbeat', 'type' => 'switch', 'title' => __( 'غیرفعال‌سازی Heartbeat', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_disable_xmlrpc', 'type' => 'switch', 'title' => __( 'غیرفعال‌سازی XML-RPC', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_security_headers', 'type' => 'switch', 'title' => __( 'هدرهای امنیتی', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_revisions_limit', 'type' => 'slider', 'title' => __( 'حداکثر نسخه‌های پیشین', 'zarincode' ), 'default' => 5, 'min' => 0, 'max' => 30 ),
				array( 'id' => 'zc_optimize_assets', 'type' => 'switch', 'title' => __( 'ادغام و فشرده‌سازی CSS و JS', 'zarincode' ), 'subtitle' => __( 'همه‌ی شیوه‌نامه‌ها و اسکریپت‌های قالب در یک فایل فشرده ادغام می‌شوند. در حالت WP_DEBUG خودکار غیرفعال است.', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_lazy_sections', 'type' => 'switch', 'title' => __( 'رندر تدریجی بخش‌های پایین صفحه', 'zarincode' ), 'subtitle' => __( 'بخش‌های خارج از دید با نزدیک‌شدن کاربر نمایش داده می‌شوند؛ تعداد عناصر DOM در بارگذاری اولیه کمتر می‌شود.', 'zarincode' ), 'default' => false ),
				array( 'id' => 'zc_analytics_enable', 'type' => 'switch', 'title' => __( 'تحلیل رویدادهای رشد', 'zarincode' ), 'desc' => __( 'بازدید یکتا، خرید، تکمیل دوره، لید و فعال‌سازی لایسنس را بدون ذخیره IP خام ثبت می‌کند.', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_disable_cache', 'type' => 'switch', 'title' => __( 'غیرفعال‌سازی کش داخلی (دیباگ)', 'zarincode' ), 'default' => false ),
			),
		),

		/* ============ بهینه‌سازی تصویر ============ */
		'image' => array(
			'title'  => __( 'بهینه‌سازی تصویر', 'zarincode' ),
			'icon'   => 'el el-picture',
			'fields' => array(
				array( 'id' => 'zc_image_opt_info', 'type' => 'info', 'title' => __( 'موتور تبدیل و فشرده‌سازی تصویر', 'zarincode' ), 'desc' => __( 'هر تصویری که آپلود می‌شود به‌صورت خودکار به WebP تبدیل شده، حجمش بدون افت محسوس کیفیت کاهش می‌یابد و فایل اصلی حذف می‌شود. نسخه‌های WebP همهٔ سایزها ساخته می‌شود.', 'zarincode' ) ),
				array( 'id' => 'zc_image_opt_enable', 'type' => 'switch', 'title' => __( 'فعال‌سازی موتور بهینه‌سازی', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_image_opt_webp', 'type' => 'switch', 'title' => __( 'تبدیل فرمت به WebP', 'zarincode' ), 'default' => true, 'required' => array( 'zc_image_opt_enable', '=', true ) ),
				array( 'id' => 'zc_image_opt_quality', 'type' => 'slider', 'title' => __( 'کیفیت WebP', 'zarincode' ), 'default' => 90, 'min' => 50, 'max' => 95, 'step' => 5, 'desc' => __( '۹۰ کیفیتِ «بدون افت محسوس» است؛ برای کاهش بیشتر حجم می‌توانید پایین بیاورید.', 'zarincode' ), 'required' => array( 'zc_image_opt_webp', '=', true ) ),
				array( 'id' => 'zc_image_opt_delete_original', 'type' => 'switch', 'title' => __( 'حذف فایل اصلی پس از تبدیل', 'zarincode' ), 'default' => false, 'desc' => __( 'پس از ساخت WebP، فایل JPG/PNG اصلی حذف می‌شود. پیش‌فرض خاموش است تا نسخهٔ اصلی از دست نرود.', 'zarincode' ), 'required' => array( 'zc_image_opt_webp', '=', true ) ),
				array( 'id' => 'zc_image_opt_sizes', 'type' => 'switch', 'title' => __( 'تولید نسخه‌های WebP همهٔ سایزها', 'zarincode' ), 'default' => true, 'desc' => __( 'برای پاسخ‌گویی به هر محل استفاده، برای هر سایز تصویر نسخهٔ WebP ساخته می‌شود.', 'zarincode' ), 'required' => array( 'zc_image_opt_enable', '=', true ) ),
			),
		),

		/* ============ پلتفرم ۳.۳۸ ============ */
		'platform' => array(
			'title'  => __( 'پلتفرم و امنیت', 'zarincode' ),
			'icon'   => 'el el-lock',
			'fields' => array(
				array( 'id' => 'zc_security_headers', 'type' => 'switch', 'title' => __( 'هدرهای امنیتی HTTP', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_disable_xmlrpc', 'type' => 'switch', 'title' => __( 'غیرفعال‌سازی XML-RPC', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_admin_login_secret', 'type' => 'password', 'title' => __( 'رمز ورود اضطراری مدیر', 'zarincode' ), 'desc' => __( 'اگر خالی باشد مسیر /wp-login.php?zc_admin=1 کار نمی‌کند. مقدار را محرمانه نگه دارید.', 'zarincode' ) ),
				array( 'id' => 'zc_lesson_complete_percent', 'type' => 'slider', 'title' => __( 'آستانه تکمیل جلسه (٪ تماشا)', 'zarincode' ), 'default' => 80, 'min' => 50, 'max' => 100, 'step' => 5 ),
				array( 'id' => 'zc_installments_enable', 'type' => 'switch', 'title' => __( 'خرید اقساطی دوره', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_installments_max', 'type' => 'slider', 'title' => __( 'حداکثر تعداد اقساط', 'zarincode' ), 'default' => 4, 'min' => 2, 'max' => 12, 'required' => array( 'zc_installments_enable', '=', true ) ),
				array( 'id' => 'zc_pwa_enable', 'type' => 'switch', 'title' => __( 'مانیفست PWA', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_dark_enable', 'type' => 'switch', 'title' => __( 'حالت تیره', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_whatsapp_enable', 'type' => 'switch', 'title' => __( 'دکمه واتساپ', 'zarincode' ), 'default' => true ),
				array( 'id' => 'zc_whatsapp_number', 'type' => 'text', 'title' => __( 'شماره واتساپ', 'zarincode' ), 'required' => array( 'zc_whatsapp_enable', '=', true ) ),
				array( 'id' => 'zc_whatsapp_prefill', 'type' => 'text', 'title' => __( 'متن پیش‌فرض واتساپ', 'zarincode' ), 'required' => array( 'zc_whatsapp_enable', '=', true ) ),
				array( 'id' => 'zc_update_endpoint', 'type' => 'text', 'title' => __( 'آدرس بررسی به‌روزرسانی قالب', 'zarincode' ), 'desc' => __( 'خالی = بدون بررسی از راه دور.', 'zarincode' ) ),
				array( 'id' => 'zc_update_license', 'type' => 'text', 'title' => __( 'کلید لایسنس به‌روزرسانی', 'zarincode' ) ),
			),
		),
	);

	return apply_filters( 'zc_settings_schema', $schema );
}
