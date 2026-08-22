<?php
/**
 * ساختار صفحه اصلی دمو برای المنتور
 * این داده در متای _elementor_data صفحه اصلی ذخیره می‌شود.
 *
 * نسخه بازطراحی‌شده: چیدمان جدید، نمایش کامل خدمات، تکمیل زمینه‌های کاری،
 * حذف فرم جستجو و فرم درخواست از صفحه اصلی (هر کدام صفحه اختصاصی دارند).
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/elementor-helpers.php';

/**
 * ساخت داده کامل صفحه اصلی (نسخه بازطراحی‌شده).
 *
 * @return array
 */
function zc_build_homepage_data() {

	$sections = array();

	/*
	 * روایت بازطراحی‌شده صفحه اصلی:
	 *
	 *   ۱) معرفی و اعتماد   → بنر، آمار، برندها
	 *   ۲) خدمات و تخصص‌ها  → خدمات کامل، زمینه‌های کاری
	 *   ۳) اعتبار و فرایند   → نمونه‌کار، فرایند همکاری
	 *   ۴) آکادمی           → مسیرها، دوره‌ها، آموزش‌ها، مدرسان
	 *   ۵) فروشگاه          → محصولات، مزیت‌های خرید
	 *   ۶) اقناع نهایی       → نظرات، مجله، سوالات، خبرنامه
	 *
	 * فرم جستجوی محصولات و فرم درخواست پروژه از صفحه اصلی حذف شده‌اند و
	 * هر کدام در یک صفحه اختصاصی در دسترس هستند.
	 */

	$sec_pad = function ( $top = '0', $bottom = '0' ) {
		return array(
			'padding' => array(
				'unit'     => 'px',
				'top'      => $top,
				'right'    => '0',
				'bottom'   => $bottom,
				'left'     => '0',
				'isLinked' => false,
			),
		);
	};

	/*
	 * کمک‌تابع padding واکنش‌گرا برای سکشن‌ها.
	 * روی موبایل فاصله‌های عمودی کم‌تر می‌شود تا بخش‌ها به‌هم نچسبند
	 * و صفحه کوتاه‌تر و مرتب‌تر دیده شود.
	 */
	$sec_pad_resp = function ( $top = '0', $bottom = '0' ) use ( $sec_pad ) {
		return array_merge(
			$sec_pad( $top, $bottom ),
			array(
				'padding_tablet' => array(
					'unit'     => 'px',
					'top'      => max( 8, (int) $top * 0.8 ),
					'right'    => '0',
					'bottom'   => max( 8, (int) $bottom * 0.8 ),
					'left'     => '0',
					'isLinked' => false,
				),
				'padding_mobile' => array(
					'unit'     => 'px',
					'top'      => max( 6, (int) $top * 0.6 ),
					'right'    => '0',
					'bottom'   => max( 6, (int) $bottom * 0.6 ),
					'left'     => '0',
					'isLinked' => false,
				),
			)
		);
	};

	/* =====================================================================
	   ۱. بنر اصلی — معرفی کامل مجموعه
	   ===================================================================== */
	$sections[] = zc_el_section(
		'zc_hero',
		array(
			'badge_text'  => 'آژانس تخصصی توسعه نرم‌افزار + آکادمی برنامه‌نویسی',
			'badge_icon'  => 'sparkle',
			'title'       => 'سفارشی‌سازی و <span>توسعه نرم‌افزار</span> برای رشد کسب‌وکار شما',
			'desc'        => 'زرین کد یک آژانس تخصصی خدمات برنامه‌نویسی است: طراحی و توسعه‌ی سایت و اپلیکیشن، سامانه‌های تحت وب، فروشگاه اینترنتی، اتوماسیون و سئو. به‌همراه دوره‌های پروژه‌محور و محصولات آماده‌ی وردپرسی برای یادگیری و درآمدزایی.',
			'btn1_text'   => 'سفارش پروژه',
			'btn1_link'   => array( 'url' => '/request/', 'is_external' => '', 'nofollow' => '' ),
			'btn2_text'   => 'مشاهده خدمات',
			'btn2_link'   => array( 'url' => '#zc-services', 'is_external' => '', 'nofollow' => '' ),
			'image'       => array( 'url' => ZC_ASSETS . 'img/hero.svg' ),
			'show_floats' => 'yes',
			'floats'      => array(
				array( '_id' => zc_el_id(), 'float_icon' => 'users', 'float_text' => '+۱۲,۰۰۰ دانشجو' ),
				array( '_id' => zc_el_id(), 'float_icon' => 'award', 'float_text' => '+۳۵۰ پروژه موفق' ),
				array( '_id' => zc_el_id(), 'float_icon' => 'package', 'float_text' => '+۱۸۰ محصول آماده' ),
			),
		),
		$sec_pad_resp()
	);

	/* ---------- ۲. نوار آمار شناور ---------- */
	$sections[] = zc_el_section(
		'zc_stats',
		array(
			'float_mode'      => 'yes',
			'animate_numbers' => 'yes',
			'items'           => array(
				array( '_id' => zc_el_id(), 'icon' => 'users', 'number' => 12000, 'prefix' => '+', 'suffix' => '', 'label' => 'دانشجوی فعال' ),
				array( '_id' => zc_el_id(), 'icon' => 'award', 'number' => 350, 'prefix' => '+', 'suffix' => '', 'label' => 'پروژه تحویل‌شده' ),
				array( '_id' => zc_el_id(), 'icon' => 'package', 'number' => 180, 'prefix' => '+', 'suffix' => '', 'label' => 'محصول آماده' ),
				array( '_id' => zc_el_id(), 'icon' => 'star', 'number' => 97, 'prefix' => '', 'suffix' => '%', 'label' => 'رضایت مشتریان' ),
			),
		),
		$sec_pad_resp()
	);

	/* ---------- ۳. برندها / اعتماد ---------- */
	$sections[] = zc_el_section(
		'zc_brands',
		array(
			'show_heading'  => 'yes',
			'heading_title' => 'اعتماد <span>کسب‌وکارها</span>',
			'heading_sub'   => 'برندهایی که پروژه‌های خود را به زرین کد سپرده‌اند',
			'heading_align' => 'center',
			'columns'       => '6',
			'grayscale'     => 'yes',
			'items'         => array(
				array( '_id' => zc_el_id(), 'title' => 'زوم‌تک', 'image' => array( 'url' => ZC_ASSETS . 'img/zoomtech.svg' ) ),
				array( '_id' => zc_el_id(), 'title' => 'تک‌شاپ', 'image' => array( 'url' => ZC_ASSETS . 'img/techshop.svg' ) ),
				array( '_id' => zc_el_id(), 'title' => 'آریا صنعت', 'image' => array( 'url' => ZC_ASSETS . 'img/aria.svg' ) ),
				array( '_id' => zc_el_id(), 'title' => 'مهرگان', 'image' => array( 'url' => ZC_ASSETS . 'img/mehregan.svg' ) ),
				array( '_id' => zc_el_id(), 'title' => 'دیجی‌استایل', 'image' => array( 'url' => ZC_ASSETS . 'img/digistyle.svg' ) ),
				array( '_id' => zc_el_id(), 'title' => 'تسکو', 'image' => array( 'url' => ZC_ASSETS . 'img/tesco.svg' ) ),
			),
		),
		$sec_pad_resp()
	);

	/* =====================================================================
	   ۴. خدمات تخصصی — نمایش کامل همه‌ی خدمات
	   ===================================================================== */
	$sections[] = zc_el_section(
		'zc_services',
		array(
			'show_heading'  => 'yes',
			'show_arrow'    => 'yes',
			'heading_title' => 'خدمات <span>تخصصی</span> زرین کد',
			'heading_sub'   => 'از ایده تا اجرا و پشتیبانی؛ طراحی سایت، برنامه‌نویسی اختصاصی، گرافیک و خدمات وردپرس',
			'heading_align' => 'center',
			'count'         => 12,
			'columns'       => '3',
			'style'         => 'card',
			'show_price'    => 'yes',
			'show_features' => 'yes',
			'btn_text'      => 'مشاهده و سفارش',
		),
		$sec_pad_resp( '20', '0' )
	);

	/* ---------- ۵. زمینه‌های کاری (تکمیل‌شده) ---------- */
	$sections[] = zc_el_section(
		'zc_features',
		array(
			'show_heading'  => 'yes',
			'heading_title' => 'در چه زمینه‌هایی <span>کار می‌کنیم</span>',
			'heading_sub'   => 'هر پروژه را تیمی تخصصی و متناسب با همان حوزه اجرا می‌کند',
			'heading_align' => 'center',
			'style_mode'    => 'card',
			'columns'       => '4',
			'items'         => array(
				array( '_id' => zc_el_id(), 'icon' => 'code', 'title' => 'طراحی سایت وردپرسی', 'text' => 'سایت شرکتی، فروشگاهی و آموزشی با کدنویسی تمیز، سرعت بالا و پنل مدیریت فارسی.', 'link' => array( 'url' => '/services/' ) ),
				array( '_id' => zc_el_id(), 'icon' => 'plugin', 'title' => 'افزونه و قالب اختصاصی', 'text' => 'ساخت افزونه و قالب سفارشی مطابق استانداردهای رسمی وردپرس و سازگار با ووکامرس.', 'link' => array( 'url' => '/services/' ) ),
				array( '_id' => zc_el_id(), 'icon' => 'edit', 'title' => 'طراحی گرافیک و UI/UX', 'text' => 'طراحی رابط کاربری، هویت بصری، لوگو و محتوای گرافیکی شبکه‌های اجتماعی.', 'link' => array( 'url' => '/services/' ) ),
				array( '_id' => zc_el_id(), 'icon' => 'phone', 'title' => 'اپلیکیشن موبایل', 'text' => 'اپلیکیشن اندروید و iOS با فلاتر و ری‌اکت نیتیو، همراه با انتشار در بازارها.', 'link' => array( 'url' => '/services/' ) ),
				array( '_id' => zc_el_id(), 'icon' => 'cart', 'title' => 'فروشگاه اینترنتی', 'text' => 'راه‌اندازی و توسعه فروشگاه ووکامرس با درگاه پرداخت، سبد خرید و پنل مدیریت کامل.', 'link' => array( 'url' => '/services/' ) ),
				array( '_id' => zc_el_id(), 'icon' => 'chart', 'title' => 'سئو و دیجیتال مارکتینگ', 'text' => 'بهینه‌سازی فنی و محتوایی برای رسیدن به صفحه اول گوگل و رشد پایدار ترافیک.', 'link' => array( 'url' => '/services/' ) ),
				array( '_id' => zc_el_id(), 'icon' => 'settings', 'title' => 'API و سرویس سمت سرور', 'text' => 'طراحی سرویس‌های امن و مقیاس‌پذیر با لاراول و Node.js همراه با مستندسازی کامل.', 'link' => array( 'url' => '/services/' ) ),
				array( '_id' => zc_el_id(), 'icon' => 'sparkle', 'title' => 'هوش مصنوعی و اتوماسیون', 'text' => 'یکپارچه‌سازی مدل‌های زبانی، ربات‌ها و اتوماسیون فرایندها برای رشد کسب‌وکار.', 'link' => array( 'url' => '/services/' ) ),
				array( '_id' => zc_el_id(), 'icon' => 'grid', 'title' => 'سامانه و وب‌اپلیکیشن', 'text' => 'طراحی پنل مدیریت، سامانه‌ی داخلی و وب‌اپلیکیشن با معماری امن و مقیاس‌پذیر.', 'link' => array( 'url' => '/services/' ) ),
				array( '_id' => zc_el_id(), 'icon' => 'eye', 'title' => 'امنیت و بهینه‌سازی', 'text' => 'پاک‌سازی سایت، رفع آسیب‌پذیری، افزایش سرعت و سخت‌گیری‌های فنی استاندارد.', 'link' => array( 'url' => '/services/' ) ),
				array( '_id' => zc_el_id(), 'icon' => 'headphone', 'title' => 'پشتیبانی و نگهداری سایت', 'text' => 'پشتیبانی فنی دوره‌ای، بروزرسانی، بکاپ‌گیری و مانیتورینگ پیوسته سایت شما.', 'link' => array( 'url' => '/services/' ) ),
				array( '_id' => zc_el_id(), 'icon' => 'book', 'title' => 'آموزش و مشاوره‌ی تخصصی', 'text' => 'آموزش خصوصی و سازمانی، مشاوره‌ی فنی و انتخاب مسیر شغلی مناسب شما.', 'link' => array( 'url' => '/services/' ) ),
			),
		),
		$sec_pad_resp( '10', '0' )
	);

	/* ---------- ۵.۵. چرا زرین کد (مزایای همکاری) ---------- */
	$sections[] = zc_el_section(
		'zc_why',
		array(
			'show_heading'  => 'yes',
			'heading_title' => 'چرا کسب‌وکارها <span>زرین کد</span> را انتخاب می‌کنند؟',
			'heading_sub'   => 'تضمین کیفیت، شفافیت در قرارداد و پشتیبانی واقعی در تمام مسیر پروژه',
			'heading_align' => 'center',
			'style_mode'    => 'card',
			'columns'       => '3',
			'items'         => array(
				array( '_id' => zc_el_id(), 'icon' => 'shield', 'title' => 'قرارداد رسمی و شفاف', 'text' => 'پیش از شروع، برآورد دقیق مبلغ و زمان به‌صورت قرارداد آنلاین با تعهد حقوقی ثبت می‌شود.', 'link' => array( 'url' => '/contracts/' ) ),
				array( '_id' => zc_el_id(), 'icon' => 'code', 'title' => 'کدنویسی استاندارد و تمیز', 'text' => 'توسعه بر پایهٔ معماری امن، مستند و مقیاس‌پذیر با رعایت بهترین شیوه‌های دنیا.', 'link' => array( 'url' => '/services/' ) ),
				array( '_id' => zc_el_id(), 'icon' => 'clock', 'title' => 'تحویل مرحله‌ای و شفاف', 'text' => 'در هر مرحله نسخه‌ی قابل مشاهده و گزارش پیشرفت دریافت می‌کنید تا از مسیر مطمئن باشید.', 'link' => array( 'url' => '/services/' ) ),
				array( '_id' => zc_el_id(), 'icon' => 'gift', 'title' => 'گارانتی و پشتیبانی واقعی', 'text' => 'رفع باگ و به‌روزرسانی امنیتی در دوره‌ی گارانتی، به‌همراه تیم پشتیبانی پاسخگو.', 'link' => array( 'url' => '/warranty/' ) ),
				array( '_id' => zc_el_id(), 'icon' => 'target', 'title' => 'متمرکز بر نتیجه', 'text' => 'هدف ما رشد کسب‌وکار شماست؛ نه فقط تحویل پروژه. با تحلیل نیاز، راهکار را می‌سازیم.', 'link' => array( 'url' => '/about-us/' ) ),
				array( '_id' => zc_el_id(), 'icon' => 'users', 'title' => 'تیم متخصص و با تجربه', 'text' => 'هر پروژه توسط تیمی متخصص در حوزه‌ی خودش (وب، موبایل، سئو، هوش مصنوعی) اجرا می‌شود.', 'link' => array( 'url' => '/about-us/' ) ),
			),
		),
		$sec_pad_resp( '10', '0' )
	);

	/* ---------- ۶. نمونه‌کارها ---------- */
	$sections[] = zc_el_section(
		'zc_portfolio',
		array(
			'show_heading'  => 'yes',
			'show_arrow'    => 'yes',
			'heading_title' => 'نمونه‌کارهای <span>اخیر</span>',
			'heading_sub'   => 'پروژه‌هایی که برای کسب‌وکارهای واقعی طراحی و اجرا کرده‌ایم',
			'heading_align' => 'center',
			'count'         => 3,
			'columns'       => '3',
			'show_filter'   => 'yes',
			'show_tech'     => 'yes',
		),
		$sec_pad_resp( '10', '0' )
	);

	/* ---------- ۷. فرایند همکاری ---------- */
	$sections[] = zc_el_section(
		'zc_roadmap',
		array(
			'show_heading'  => 'yes',
			'heading_title' => 'مسیر <span>همکاری</span> با ما',
			'heading_sub'   => 'شفاف، مرحله‌به‌مرحله و بدون ابهام؛ از اولین تماس تا تحویل نهایی',
			'heading_align' => 'center',
			'columns'       => '4',
			'items'         => array(
				array( '_id' => zc_el_id(), 'step' => '۱', 'title' => 'مشاوره رایگان', 'text' => 'نیاز شما را می‌شنویم و راهکار فنی مناسب را پیشنهاد می‌دهیم.' ),
				array( '_id' => zc_el_id(), 'step' => '۲', 'title' => 'قرارداد و برآورد', 'text' => 'مبلغ و زمان دقیق اعلام و قرارداد رسمی آنلاین امضا می‌شود.' ),
				array( '_id' => zc_el_id(), 'step' => '۳', 'title' => 'اجرای مرحله‌ای', 'text' => 'در هر مرحله گزارش پیشرفت و نسخه قابل مشاهده تحویل می‌گیرید.' ),
				array( '_id' => zc_el_id(), 'step' => '۴', 'title' => 'تحویل و پشتیبانی', 'text' => 'آموزش کار با پروژه، تحویل کد منبع و پشتیبانی پس از تحویل.' ),
			),
		),
		$sec_pad_resp( '10', '0' )
	);

	/* =====================================================================
	   ۸. بخش آکادمی — مسیرهای یادگیری
	   ===================================================================== */
	$sections[] = zc_el_section(
		'zc_category_cards',
		array(
			'show_heading'  => 'yes',
			'show_arrow'    => 'yes',
			'heading_title' => 'مسیرهای <span>یادگیری</span>',
			'heading_sub'   => 'یک مسیر را انتخاب کنید و از صفر تا ورود به بازار کار پیش بروید',
			'heading_align' => 'center',
			'columns'       => '4',
			'items'         => array(
				array( '_id' => zc_el_id(), 'title_fa' => 'برنامه‌نویسی وب', 'title_en' => 'WEB', 'link' => array( 'url' => '/courses/' ), 'fab_color' => '#0B2187' ),
				array( '_id' => zc_el_id(), 'title_fa' => 'وردپرس حرفه‌ای', 'title_en' => 'WORDPRESS', 'link' => array( 'url' => '/courses/' ), 'fab_color' => '#C9A227' ),
				array( '_id' => zc_el_id(), 'title_fa' => 'اپلیکیشن موبایل', 'title_en' => 'MOBILE', 'link' => array( 'url' => '/courses/' ), 'fab_color' => '#059669' ),
				array( '_id' => zc_el_id(), 'title_fa' => 'طراحی و گرافیک', 'title_en' => 'DESIGN', 'link' => array( 'url' => '/courses/' ), 'fab_color' => '#DB2777' ),
			),
		),
		$sec_pad_resp( '10', '0' )
	);

	/* ---------- ۹. دوره‌های ویدیویی ---------- */
	$sections[] = zc_el_section(
		'zc_courses',
		array(
			'show_heading'   => 'yes',
			'show_arrow'     => 'yes',
			'heading_title'  => 'دوره‌های <span>ویدیویی</span> پرطرفدار',
			'heading_sub'    => 'آموزش پروژه‌محور با پشتیبانی مستقیم مدرس و دسترسی مادام‌العمر',
			'heading_align'  => 'center',
			'columns'        => '3',
			'posts_count'    => 3,
			'excerpt_length' => 14,
			'btn_text'       => 'مشاهده دوره',
		),
		array_merge( $sec_pad_resp( '10', '0' ), array( 'css_classes' => 'zc-anchor', '_element_id' => 'zc-courses' ) )
	);

	/* ---------- ۱۰. آموزش‌های متنی رایگان ---------- */
	$sections[] = zc_el_section(
		'zc_tutorials',
		array(
			'show_heading'    => 'yes',
			'show_arrow'      => 'yes',
			'heading_title'   => 'آموزش‌های <span>متنی</span> رایگان',
			'heading_sub'     => 'صدها مقاله‌ی آموزشی گام‌به‌گام، کاملاً رایگان و همیشه در دسترس',
			'heading_align'   => 'center',
			'columns'         => '3',
			'posts_count'     => 3,
			'show_excerpt'    => 'yes',
			'show_difficulty' => 'yes',
			'show_reading'    => 'yes',
		),
		$sec_pad_resp( '10', '0' )
	);

	/* ---------- ۱۱. مدرسان و تیم اجرایی ---------- */
	$sections[] = zc_el_section(
		'zc_teachers',
		array(
			'show_heading'  => 'yes',
			'show_arrow'    => 'yes',
			'heading_title' => 'مدرسان و <span>تیم اجرایی</span>',
			'heading_sub'   => 'متخصصانی که هم تدریس می‌کنند و هم پروژه‌های واقعی را اجرا می‌کنند',
			'heading_align' => 'center',
			'columns'       => '4',
			'posts_count'   => 4,
			'show_role'     => 'yes',
			'show_courses'  => 'yes',
			'show_socials'  => 'yes',
		),
		$sec_pad_resp( '10', '0' )
	);

	/* =====================================================================
	   ۱۲. فروشگاه — محصولات پرفروش
	   ===================================================================== */
	$sections[] = zc_el_section(
		'zc_products',
		array(
			'show_heading'   => 'yes',
			'show_arrow'     => 'yes',
			'heading_title'  => 'محصولات <span>پرفروش</span>',
			'heading_sub'    => 'قالب وردپرس، افزونه، سورس کد آماده و فونت فارسی با پشتیبانی کامل',
			'heading_align'  => 'center',
			'columns'        => '4',
			'posts_count'    => 4,
			'source'         => 'latest',
			'show_cat_tabs'  => 'yes',
		),
		$sec_pad_resp( '10', '0' )
	);

	/* ---------- ۱۳. مزیت‌های خرید ---------- */
	$sections[] = zc_el_section(
		'zc_features',
		array(
			'show_heading'  => 'yes',
			'heading_title' => 'چرا از <span>زرین کد</span> بخرید؟',
			'heading_sub'   => 'هر محصول با تعهد کامل پشتیبانی و بروزرسانی عرضه می‌شود',
			'heading_align' => 'center',
			'style_mode'    => 'inline',
			'columns'       => '4',
			'items'         => array(
				array( '_id' => zc_el_id(), 'icon' => 'refresh', 'title' => 'بروزرسانی مادام‌العمر', 'text' => 'یک‌بار خرید کنید و همیشه آخرین نسخه را رایگان دریافت کنید.' ),
				array( '_id' => zc_el_id(), 'icon' => 'headphone', 'title' => 'پشتیبانی تخصصی', 'text' => 'تیم فنی از طریق تیکت و چت آنلاین همراه شماست.' ),
				array( '_id' => zc_el_id(), 'icon' => 'shield', 'title' => 'ضمانت بازگشت وجه', 'text' => 'تا ۷ روز پس از خرید، بدون قید و شرط.' ),
				array( '_id' => zc_el_id(), 'icon' => 'file', 'title' => 'مستندات فارسی', 'text' => 'راهنمای نصب و استفاده‌ی کامل به زبان فارسی.' ),
			),
		),
		$sec_pad_resp( '10', '0' )
	);

	/* =====================================================================
	   ۱۴. اقناع نهایی — نظرات، مجله، سوالات
	   ===================================================================== */
	$sections[] = zc_el_section(
		'zc_testimonials_video',
		array(
			'show_heading'  => 'yes',
			'heading_title' => 'تجربه‌ی <span>مشتریان</span> و دانشجویان',
			'heading_sub'   => 'روایت کسانی که با زرین کد یاد گرفتند یا پروژه‌شان را به ما سپردند',
			'heading_align' => 'center',
			'columns'       => '3',
		),
		$sec_pad_resp( '10', '0' )
	);

	/* ---------- ۱۵. دعوت به اقدام (CTA) ---------- */
	$sections[] = zc_el_section(
		'zc_cta_bar',
		array(
			'text'       => 'آماده‌ی شروع پروژه‌ی بعدی‌تان هستید؟',
			'light_text' => 'همین حالا یک جلسه‌ی مشاوره‌ی رایگان رزرو کنید؛ تیم ما در سریع‌ترین زمان پاسخگوی شماست.',
			'btn1_text'  => 'درخواست مشاوره رایگان',
			'btn1_link'  => array( 'url' => '/request/' ),
			'btn1_style' => 'gold',
			'btn2_text'  => 'مشاهده نمونه‌کارها',
			'btn2_link'  => array( 'url' => '#zc-portfolio' ),
			'btn2_style' => 'outline',
		),
		$sec_pad_resp( '10', '0' )
	);

	/* ---------- ۱۶. مقالات مجله ---------- */
	$sections[] = zc_el_section(
		'zc_posts',
		array(
			'show_heading'   => 'yes',
			'show_arrow'     => 'yes',
			'heading_title'  => 'تازه‌های <span>مجله</span>',
			'heading_sub'    => 'تحلیل بازار کار، معرفی فناوری‌ها و تجربه‌های واقعی توسعه‌دهندگان',
			'heading_align'  => 'center',
			'columns'        => '3',
			'posts_count'    => 3,
			'excerpt_length' => 18,
		),
		$sec_pad_resp( '10', '0' )
	);

	/* ---------- ۱۶. سوالات متداول ---------- */
	$sections[] = zc_el_section(
		'zc_faq',
		array(
			'show_heading'  => 'yes',
			'heading_title' => 'سوالات <span>پرتکرار</span>',
			'heading_sub'   => 'پاسخ پرسش‌هایی که پیش از همکاری یا خرید بیشتر پرسیده می‌شود',
			'heading_align' => 'center',
			'single_open'   => 'yes',
			'schema'        => 'yes',
			'items'         => array(
				array( '_id' => zc_el_id(), 'question' => 'هزینه‌ی طراحی سایت یا اجرای پروژه چقدر است؟', 'answer' => 'هزینه به دامنه‌ی کار بستگی دارد. پس از جلسه‌ی مشاوره‌ی رایگان و بررسی نیازمندی‌ها، برآورد دقیق زمان و مبلغ به‌صورت کتبی اعلام می‌شود و تنها پس از تأیید شما قرارداد بسته می‌شود.', 'is_open' => 'yes' ),
				array( '_id' => zc_el_id(), 'question' => 'پرداخت پروژه چگونه انجام می‌شود؟', 'answer' => 'پرداخت‌ها مرحله‌ای است و به پیشرفت پروژه گره خورده؛ یعنی هر مرحله پس از تحویل و تأیید همان بخش پرداخت می‌شود. همه‌ی مراحل در پنل کاربری شما شفاف قابل پیگیری است.' ),
				array( '_id' => zc_el_id(), 'question' => 'آیا کد منبع پروژه تحویل داده می‌شود؟', 'answer' => 'بله. پس از تسویه‌ی نهایی، کد منبع کامل، مستندات فنی و دسترسی‌های مدیریتی به‌صورت رسمی تحویل می‌شود و هیچ‌گونه قفل یا مبهم‌سازی روی کد وجود ندارد.' ),
				array( '_id' => zc_el_id(), 'question' => 'دوره‌ها به چه صورت ارائه می‌شوند؟', 'answer' => 'دوره‌ها ویدیویی و پروژه‌محور هستند و بلافاصله پس از خرید در پنل کاربری فعال می‌شوند. دسترسی مادام‌العمر است و همه‌ی بروزرسانی‌های آینده رایگان در اختیار شما قرار می‌گیرد.' ),
				array( '_id' => zc_el_id(), 'question' => 'پس از خرید محصول، پشتیبانی دارم؟', 'answer' => 'بله. همه‌ی محصولات فروشگاه شامل پشتیبانی فنی از طریق سیستم تیکتینگ و بروزرسانی رایگان مادام‌العمر هستند. راهنمای نصب فارسی نیز همراه هر محصول ارائه می‌شود.' ),
			),
		),
		$sec_pad_resp( '10', '0' )
	);

	/* ---------- ۱۷. خبرنامه ---------- */
	$sections[] = zc_el_section(
		'zc_newsletter',
		array(
			'title' => 'از جدیدترین دوره‌ها و محصولات باخبر شوید',
			'desc'  => 'هر هفته یک ایمیل کوتاه شامل آموزش تازه، تخفیف‌ها و محصولات جدید — بدون هرزنامه.',
		),
		$sec_pad_resp( '10', '0' )
	);

	/*
	 * ادغام بخش‌های مجاور برای کاهش گره‌های ساختاری المنتور.
	 */
	return zc_el_merge_sections( $sections );
}

/**
 * اعمال داده المنتور روی صفحه اصلی.
 *
 * @return int|false شناسه صفحه.
 */
function zc_install_demo_homepage() {
	$page = get_page_by_path( 'home' );

	if ( ! $page ) {
		$page_id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_title'  => 'صفحه اصلی',
				'post_name'   => 'home',
				'post_status' => 'publish',
				'meta_input'  => array( '_zc_demo' => '1' ),
			)
		);
	} else {
		$page_id = $page->ID;
	}

	if ( ! $page_id || is_wp_error( $page_id ) ) {
		return false;
	}

	$data = zc_build_homepage_data();

	update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $page_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
	update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
	update_post_meta( $page_id, '_zc_demo', '1' );

	// المنتور داده را با slashes ذخیره می‌کند.
	update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $data, JSON_UNESCAPED_UNICODE ) ) );

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $page_id );

	// پاکسازی کش المنتور.
	if ( class_exists( '\\Elementor\\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	return $page_id;
}
