<?php
/**
 * برگه‌های حقوقی دمو ساخته‌شده با المنتور
 *
 * سه برگه: شرایط و قوانین، گارانتی، و بازگشت وجه.
 * هر برگه با ویجت‌های بومی زرین کد ساخته می‌شود تا کاربر بتواند
 * همه چیز را با المنتور ویرایش کند.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/elementor-helpers.php';

// محتوای کامل حقوقی (شرایط، حریم خصوصی، گارانتی، بازگشت وجه و قراردادها).
require_once __DIR__ . '/legal-content.php';





/* ==========================================================================
   برگه ۱ — شرایط و قوانین
   ========================================================================== */

/**
 * ساخت داده‌ی المنتور برگه‌ی شرایط و قوانین.
 *
 * @return array
 */
function zc_build_terms_page() {
	$sections = array();

	// سربرگ
	$sections[] = zc_el_section(
		'zc_hero',
		array(
			'badge_text'  => 'سند حقوقی رسمی',
			'badge_icon'  => 'shield',
			'title'       => 'شرایط و <span>قوانین</span> استفاده',
			'desc'        => 'آخرین بروزرسانی: مرداد ۱۴۰۴ — لطفاً پیش از استفاده از خدمات زرین کد، این سند را با دقت مطالعه فرمایید.',
			'btn1_text'   => 'ثبت تیکت سوال',
			'btn1_link'   => array( 'url' => zc_panel_url() ),
			'btn2_text'   => '',
			'show_floats' => '',
			'anim_enable' => 'yes',
		)
	);

	// جعبه‌های کلیدی
	$sections[] = zc_el_section(
		'zc_features',
		array(
			'show_heading'  => 'yes',
			'heading_title' => 'خلاصه‌ی <span>حقوق شما</span>',
			'heading_sub'   => 'مهم‌ترین نکاتی که باید بدانید، پیش از خواندن متن کامل',
			'columns'       => '4',
			'layout_mode'   => 'grid',
			'style_mode'    => 'card',
			'anim_enable'   => 'yes',
			'anim_stagger'  => 'yes',
			'items'         => array(
				array(
					'_id'   => zc_el_id(),
					'icon'  => 'shield',
					'title' => 'خرید امن',
					'text'  => 'تمام پرداخت‌ها از درگاه بانکی دارای مجوز شاپرک انجام می‌شود و اطلاعات کارت شما نزد ما ذخیره نمی‌گردد.',
				),
				array(
					'_id'   => zc_el_id(),
					'icon'  => 'refresh',
					'title' => 'ضمانت بازگشت',
					'text'  => 'تا ۷ روز پس از خرید محصولات آموزشی، در صورت عدم رضایت مبلغ به شما بازگردانده می‌شود.',
				),
				array(
					'_id'   => zc_el_id(),
					'icon'  => 'lock',
					'title' => 'حریم خصوصی',
					'text'  => 'اطلاعات شخصی شما مطابق قانون تجارت الکترونیکی محرمانه است و در اختیار ثالث قرار نمی‌گیرد.',
				),
				array(
					'_id'   => zc_el_id(),
					'icon'  => 'headphone',
					'title' => 'پشتیبانی پاسخگو',
					'text'  => 'میانگین زمان پاسخ تیکت کمتر از ۶ ساعت کاری است و پشتیبانی محصولات تضمین‌شده است.',
				),
			),
		)
	);

	// متن کامل قوانین
	$sections[] = zc_el_stack(
		array(
			array(
				'zc_heading',
				array(
					'show_heading'  => 'yes',
					'heading_title' => 'متن <span>کامل</span> قوانین',
					'heading_sub'   => 'این سند بر اساس قوانین جمهوری اسلامی ایران تنظیم شده است',
					'heading_align' => 'center',
				),
			),
			zc_el_richtext( zc_legal_terms_html() ),
		)
	);

	// سوالات متداول حقوقی
	$sections[] = zc_el_section(
		'zc_faq',
		array(
			'show_heading'  => 'yes',
			'heading_title' => 'پرسش‌های <span>حقوقی</span> پرتکرار',
			'heading_sub'   => 'اگر پاسخ سوال خود را نیافتید، تیکت ثبت کنید',
			'source'        => 'manual',
			'single_open'   => 'yes',
			'schema'        => 'yes',
			'anim_enable'   => 'yes',
			'items'         => array(
				array(
					'_id'      => zc_el_id(),
					'question' => 'آیا خرید از زرین کد فاکتور رسمی دارد؟',
					'answer'   => '<p>بله. برای کلیه خریدها فاکتور الکترونیکی در پنل کاربری شما صادر و قابل دانلود است. در صورت نیاز به فاکتور رسمی دارای مهر و کد اقتصادی جهت امور مالیاتی، از طریق تیکت درخواست دهید تا حداکثر ظرف سه روز کاری صادر شود.</p>',
					'is_open'  => 'yes',
				),
				array(
					'_id'      => zc_el_id(),
					'question' => 'استفاده از محصولات برای چند دامنه مجاز است؟',
					'answer'   => '<p>پروانه استاندارد هر قالب یا افزونه، برای <strong>یک دامنه اصلی</strong> به همراه یک نسخه آزمایشی روی زیردامنه صادر می‌شود. برای استفاده روی چند دامنه باید پروانه توسعه‌دهنده تهیه کنید. بازفروش، اشتراک‌گذاری یا انتشار فایل‌ها در کانال‌ها، نقض قانون حمایت از حقوق پدیدآورندگان نرم‌افزارهای رایانه‌ای مصوب ۱۳۷۹ است.</p>',
				),
				array(
					'_id'      => zc_el_id(),
					'question' => 'مسئولیت محتوایی که کاربران منتشر می‌کنند با کیست؟',
					'answer'   => '<p>مسئولیت کامل دیدگاه‌ها، پرسش‌ها و فایل‌های ارسالی کاربران بر عهده‌ی خود آنهاست. زرین کد مطابق قانون جرائم رایانه‌ای مصوب ۱۳۸۸، محتوای مغایر با قوانین را بدون اطلاع قبلی حذف و در موارد مجرمانه، مراتب را به مراجع ذی‌صلاح گزارش می‌کند.</p>',
				),
				array(
					'_id'      => zc_el_id(),
					'question' => 'اگر قیمت محصولی اشتباه درج شده باشد چه می‌شود؟',
					'answer'   => '<p>در صورت بروز خطای فنی آشکار در قیمت‌گذاری، زرین کد حق ابطال سفارش و استرداد کامل وجه را برای خود محفوظ می‌دارد. این موضوع مطابق ماده ۱۹۹ قانون مدنی در خصوص اشتباه مؤثر در معامله است و وجه پرداختی حداکثر ظرف ۷۲ ساعت به‌طور کامل بازگردانده می‌شود.</p>',
				),
				array(
					'_id'      => zc_el_id(),
					'question' => 'قوانین چگونه تغییر می‌کنند و چطور مطلع می‌شوم؟',
					'answer'   => '<p>زرین کد می‌تواند این سند را بروزرسانی کند. تغییرات اساسی از طریق اطلاعیه‌ی سایت، پیامک و ربات‌های تلگرام و بله به کاربران اطلاع داده می‌شود. تاریخ آخرین بروزرسانی همواره در بالای این صفحه درج می‌گردد و ادامه‌ی استفاده از خدمات، به منزله‌ی پذیرش نسخه‌ی جدید است.</p>',
				),
			),
		)
	);

	// نوار پایانی
	$sections[] = zc_el_section(
		'zc_cta_bar',
		array(
			'bold_text'  => 'سوال حقوقی دارید؟',
			'light_text' => 'کارشناسان ما پاسخگوی شما هستند.',
			'btn1_text'  => 'ثبت تیکت پشتیبانی',
			'btn1_style' => 'gold',
			'btn1_link'  => array( 'url' => zc_panel_url() ),
			'btn2_text'  => 'تماس با ما',
			'btn2_style' => 'navy',
			'btn2_link'  => array( 'url' => home_url( '/contact-us/' ) ),
		)
	);

	return $sections;
}

/**
 * متن کامل شرایط و قوانین.
 *
 * @return string
 */
function zc_install_demo_legal_pages() {
	$pages = array(
		'terms' => array(
			'title'   => 'شرایط و قوانین',
			'slug'    => 'terms',
			'builder' => 'zc_build_terms_page',
		),
		'warranty' => array(
			'title'   => 'گارانتی و تضمین کیفیت',
			'slug'    => 'warranty',
			'builder' => 'zc_build_warranty_page',
		),
		'refund' => array(
			'title'   => 'شرایط بازگشت وجه',
			'slug'    => 'refund-policy',
			'builder' => 'zc_build_refund_page',
		),
		'privacy' => array(
			'title'   => 'حریم خصوصی',
			'slug'    => 'privacy-policy',
			'builder' => 'zc_build_privacy_page',
		),
		'faq' => array(
			'title'   => 'سوالات متداول',
			'slug'    => 'faq',
			'builder' => 'zc_build_faq_page',
		),
		'contracts' => array(
			'title'   => 'قراردادهای خدمات',
			'slug'    => 'contracts',
			'builder' => 'zc_build_contracts_page',
		),
	);

	$ids = array();

	foreach ( $pages as $key => $page ) {
		$existing = get_page_by_path( $page['slug'] );

		if ( $existing ) {
			$page_id = $existing->ID;

			// عنوان برگه‌ی قدیمی «قوانین و مقررات» به‌روز شود.
			wp_update_post(
				array(
					'ID'           => $page_id,
					'post_title'   => $page['title'],
					'post_content' => '',
				)
			);
		} else {
			$page_id = wp_insert_post(
				array(
					'post_type'   => 'page',
					'post_title'  => $page['title'],
					'post_name'   => $page['slug'],
					'post_status' => 'publish',
					'meta_input'  => array( '_zc_demo' => '1' ),
				)
			);
		}

		if ( ! $page_id || is_wp_error( $page_id ) ) {
			continue;
		}

		$data = call_user_func( $page['builder'] );

		update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
		update_post_meta( $page_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
		update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
		update_post_meta( $page_id, '_zc_demo', '1' );

		// المنتور داده را با slashes ذخیره می‌کند.
		update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $data, JSON_UNESCAPED_UNICODE ) ) );

		$ids[ $key ] = $page_id;
	}

	// پیوند قوانین در تنظیمات قالب.
	if ( ! empty( $ids['terms'] ) ) {
		$options                  = get_option( ZC_PREFIX, array() );
		$options['zc_terms_link'] = get_permalink( $ids['terms'] );
		update_option( ZC_PREFIX, $options );
	}

	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	return $ids;
}

function zc_build_warranty_page() {
	$sections = array();

	$sections[] = zc_el_section(
		'zc_hero',
		array(
			'badge_text'  => 'تعهد کتبی زرین کد',
			'badge_icon'  => 'shield',
			'title'       => 'گارانتی و <span>تضمین کیفیت</span>',
			'desc'        => 'پشت هر محصول و خدمتی که ارائه می‌دهیم، تعهد کتبی ما ایستاده است.',
			'btn1_text'   => 'ثبت درخواست گارانتی',
			'btn1_link'   => array( 'url' => zc_panel_url() ),
			'btn2_text'   => '',
			'show_floats' => '',
			'anim_enable' => 'yes',
		)
	);

	// مدت گارانتی هر سرویس
	$sections[] = zc_el_section(
		'zc_features',
		array(
			'show_heading'  => 'yes',
			'heading_title' => 'مدت <span>گارانتی</span> هر سرویس',
			'heading_sub'   => 'دوره‌ی تضمین از تاریخ تحویل نهایی محاسبه می‌شود',
			'columns'       => '3',
			'layout_mode'   => 'grid',
			'style_mode'    => 'card',
			'anim_enable'   => 'yes',
			'anim_stagger'  => 'yes',
			'items'         => array(
				array(
					'_id'   => zc_el_id(),
					'icon'  => 'globe',
					'title' => 'طراحی وب‌سایت — ۳ ماه',
					'text'  => 'رفع رایگان تمام باگ‌های فنی ناشی از کدنویسی ما، به همراه پشتیبانی راه‌اندازی و آموزش پنل مدیریت.',
				),
				array(
					'_id'   => zc_el_id(),
					'icon'  => 'phone',
					'title' => 'اپلیکیشن اندروید — ۶ ماه',
					'text'  => 'رفع باگ و سازگارسازی با یک نسخه بالاتر اندروید، بدون هیچ هزینه‌ی اضافی.',
				),
				array(
					'_id'   => zc_el_id(),
					'icon'  => 'code',
					'title' => 'نرم‌افزار سفارشی — ۱۲ ماه',
					'text'  => 'تضمین رفع کلیه نقایص مغایر با سند نیازمندی‌های امضاشده، به مدت یک سال کامل.',
				),
				array(
					'_id'   => zc_el_id(),
					'icon'  => 'package',
					'title' => 'قالب و افزونه — دائمی',
					'text'  => 'بروزرسانی رایگان مادام‌العمر و رفع ناسازگاری با نسخه‌های جدید وردپرس و ووکامرس.',
				),
				array(
					'_id'   => zc_el_id(),
					'icon'  => 'book',
					'title' => 'دوره آموزشی — مادام‌العمر',
					'text'  => 'دسترسی همیشگی به محتوا، بروزرسانی رایگان سرفصل‌ها و پشتیبانی رفع اشکال توسط مدرس.',
				),
				array(
					'_id'   => zc_el_id(),
					'icon'  => 'chart',
					'title' => 'خدمات سئو — دوره قرارداد',
					'text'  => 'گزارش ماهانه شفاف و تضمین اجرای کامل اقدامات فنی مندرج در قرارداد.',
				),
			),
		)
	);

	// شرایط و استثناها
	$sections[] = zc_el_stack(
		array(
			array(
				'zc_heading',
				array(
					'show_heading'  => 'yes',
					'heading_title' => 'شرایط <span>و استثناهای</span> گارانتی',
					'heading_sub'   => 'شفاف می‌گوییم چه چیزی پوشش دارد و چه چیزی ندارد',
					'heading_align' => 'center',
				),
			),
			zc_el_richtext( zc_legal_warranty_html() ),
		)
	);

	// مراحل استفاده از گارانتی
	$sections[] = zc_el_section(
		'zc_timeline',
		array(
			'show_heading'  => 'yes',
			'heading_title' => 'چطور از <span>گارانتی</span> استفاده کنم؟',
			'heading_sub'   => 'چهار گام ساده تا رفع مشکل',
			'anim_enable'   => 'yes',
			'items'         => array(
				array(
					'_id'   => zc_el_id(),
					'title' => 'گام ۱ — ثبت تیکت',
					'icon'  => 'ticket',
					'year'  => 'کمتر از ۵ دقیقه',
					'text'  => 'از پنل کاربری خود تیکت جدید با دپارتمان «پشتیبانی فنی» ثبت کنید و مشکل را همراه تصویر شرح دهید.',
				),
				array(
					'_id'   => zc_el_id(),
					'title' => 'گام ۲ — بررسی کارشناس',
					'icon'  => 'search',
					'year'  => 'حداکثر ۶ ساعت کاری',
					'text'  => 'کارشناس فنی تیکت شما را بررسی و در صورت نیاز، دسترسی موقت جهت عیب‌یابی درخواست می‌کند.',
				),
				array(
					'_id'   => zc_el_id(),
					'title' => 'گام ۳ — رفع مشکل',
					'icon'  => 'settings',
					'year'  => '۱ تا ۳ روز کاری',
					'text'  => 'در صورت تأیید شمول گارانتی، مشکل به‌صورت کاملاً رایگان برطرف و نتیجه به شما اعلام می‌شود.',
				),
				array(
					'_id'   => zc_el_id(),
					'title' => 'گام ۴ — تأیید نهایی',
					'icon'  => 'check',
					'year'  => 'پس از رفع',
					'text'  => 'پس از تأیید شما تیکت بسته می‌شود. اگر مشکل تکرار شد، همان تیکت قابل بازگشایی است.',
				),
			),
		)
	);

	$sections[] = zc_el_section(
		'zc_cta_bar',
		array(
			'bold_text'  => 'مشکلی در محصول خریداری‌شده دارید؟',
			'light_text' => 'گارانتی شما فعال است — همین حالا اقدام کنید.',
			'btn1_text'  => 'ثبت درخواست گارانتی',
			'btn1_style' => 'gold',
			'btn1_link'  => array( 'url' => zc_panel_url() ),
			'btn2_text'  => 'مطالعه شرایط بازگشت وجه',
			'btn2_style' => 'navy',
			'btn2_link'  => array( 'url' => home_url( '/refund-policy/' ) ),
		)
	);

	return $sections;
}

function zc_build_refund_page() {
	$sections = array();

	$sections[] = zc_el_section(
		'zc_hero',
		array(
			'badge_text'  => 'خرید بدون ریسک',
			'badge_icon'  => 'refresh',
			'title'       => 'شرایط <span>بازگشت وجه</span>',
			'desc'        => 'اگر از خرید خود راضی نبودید، تا ۷ روز مبلغ پرداختی به شما بازمی‌گردد.',
			'btn1_text'   => 'ثبت درخواست استرداد',
			'btn1_link'   => array( 'url' => zc_panel_url() ),
			'btn2_text'   => '',
			'show_floats' => '',
			'anim_enable' => 'yes',
		)
	);

	// وضعیت هر نوع محصول
	$sections[] = zc_el_section(
		'zc_features',
		array(
			'show_heading'  => 'yes',
			'heading_title' => 'مهلت <span>بازگشت وجه</span> بر اساس نوع خرید',
			'heading_sub'   => 'قوانین هر دسته متفاوت است — پیش از خرید مطالعه کنید',
			'columns'       => '3',
			'layout_mode'   => 'grid',
			'style_mode'    => 'card',
			'anim_enable'   => 'yes',
			'anim_stagger'  => 'yes',
			'items'         => array(
				array(
					'_id'   => zc_el_id(),
					'icon'  => 'book',
					'title' => 'دوره آموزشی — ۷ روز',
					'text'  => 'در صورت مشاهده کمتر از ۲۰٪ محتوا، تا ۷ روز پس از خرید کل مبلغ بازگردانده می‌شود.',
				),
				array(
					'_id'   => zc_el_id(),
					'icon'  => 'package',
					'title' => 'قالب و افزونه — ۷ روز',
					'text'  => 'در صورت وجود ایراد فنی غیرقابل رفع یا مغایرت با توضیحات صفحه محصول.',
				),
				array(
					'_id'   => zc_el_id(),
					'icon'  => 'font',
					'title' => 'فونت و فایل گرافیکی',
					'text'  => 'به دلیل ماهیت فایل و امکان کپی فوری، پس از دانلود قابل بازگشت نیست مگر خرابی فایل.',
				),
				array(
					'_id'   => zc_el_id(),
					'icon'  => 'globe',
					'title' => 'خدمات پروژه‌ای',
					'text'  => 'تابع بند فسخ قرارداد اختصاصی؛ کارکرد انجام‌شده کسر و مابقی مسترد می‌گردد.',
				),
				array(
					'_id'   => zc_el_id(),
					'icon'  => 'wallet',
					'title' => 'شارژ کیف پول',
					'text'  => 'موجودی استفاده‌نشده کیف پول در هر زمان قابل درخواست استرداد به حساب بانکی است.',
				),
				array(
					'_id'   => zc_el_id(),
					'icon'  => 'calendar',
					'title' => 'رزرو مشاوره',
					'text'  => 'لغو تا ۲۴ ساعت پیش از جلسه: بازگشت کامل. کمتر از ۲۴ ساعت: ۵۰٪ مبلغ.',
				),
			),
		)
	);

	// متن کامل
	$sections[] = zc_el_stack(
		array(
			array(
				'zc_heading',
				array(
					'show_heading'  => 'yes',
					'heading_title' => 'قوانین <span>کامل</span> استرداد',
					'heading_sub'   => 'بر پایه‌ی حق انصراف مندرج در قانون تجارت الکترونیکی',
					'heading_align' => 'center',
				),
			),
			zc_el_richtext( zc_legal_refund_html() ),
		)
	);

	// سوالات متداول
	$sections[] = zc_el_section(
		'zc_faq',
		array(
			'show_heading'  => 'yes',
			'heading_title' => 'سوالات رایج درباره <span>بازگشت وجه</span>',
			'heading_sub'   => '',
			'source'        => 'manual',
			'single_open'   => 'yes',
			'schema'        => 'yes',
			'anim_enable'   => 'yes',
			'items'         => array(
				array(
					'_id'      => zc_el_id(),
					'question' => 'پول چه زمانی به حسابم برمی‌گردد؟',
					'answer'   => '<p>پس از تأیید درخواست، مبلغ حداکثر ظرف <strong>۷۲ ساعت کاری</strong> به کیف پول شما واریز می‌شود و بلافاصله قابل استفاده است. در صورت درخواست واریز به حساب بانکی، با توجه به فرآیند تسویه بین‌بانکی، ۳ تا ۷ روز کاری زمان می‌برد.</p>',
					'is_open'  => 'yes',
				),
				array(
					'_id'      => zc_el_id(),
					'question' => 'اگر دوره را دیده باشم باز هم می‌توانم مرجوع کنم؟',
					'answer'   => '<p>اگر کمتر از <strong>۲۰ درصد</strong> محتوای دوره را مشاهده کرده باشید، بله. سیستم به‌صورت خودکار میزان پیشرفت شما را ثبت می‌کند. بیش از این مقدار، به منزله‌ی بهره‌مندی از محتوا تلقی شده و مشمول استرداد نمی‌شود.</p>',
				),
				array(
					'_id'      => zc_el_id(),
					'question' => 'آیا هزینه درگاه پرداخت کسر می‌شود؟',
					'answer'   => '<p>خیر. در استردادهای مشمول این سیاست، <strong>کل مبلغ پرداختی</strong> بدون کسر کارمزد بازگردانده می‌شود. تنها در مورد لغو دیرهنگام جلسات مشاوره، درصد مندرج در جدول کسر می‌گردد.</p>',
				),
				array(
					'_id'      => zc_el_id(),
					'question' => 'اگر با کد تخفیف خرید کرده باشم چه؟',
					'answer'   => '<p>مبلغی که واقعاً پرداخت کرده‌اید (پس از اعمال تخفیف) بازگردانده می‌شود. کد تخفیف مصرف‌شده در صورت انقضا نداشتن، مجدداً برای حساب شما فعال می‌گردد.</p>',
				),
				array(
					'_id'      => zc_el_id(),
					'question' => 'درخواست من رد شد، چه کنم؟',
					'answer'   => '<p>در صورت رد درخواست، دلیل آن کتباً در تیکت اعلام می‌شود. شما می‌توانید ظرف ۷ روز اعتراض خود را در همان تیکت ثبت کنید تا توسط مدیر بازبینی شود. در صورت عدم حصول توافق، مطابق ماده حل اختلاف سند قوانین اقدام خواهد شد.</p>',
				),
			),
		)
	);

	$sections[] = zc_el_section(
		'zc_cta_bar',
		array(
			'bold_text'  => 'می‌خواهید درخواست استرداد ثبت کنید؟',
			'light_text' => 'فرآیند کاملاً آنلاین و بدون نیاز به مراجعه حضوری است.',
			'btn1_text'  => 'ثبت درخواست استرداد',
			'btn1_style' => 'gold',
			'btn1_link'  => array( 'url' => zc_panel_url() ),
			'btn2_text'  => 'مشاهده شرایط گارانتی',
			'btn2_style' => 'navy',
			'btn2_link'  => array( 'url' => home_url( '/warranty/' ) ),
		)
	);

	return $sections;
}

/* ==========================================================================
   برگه‌های تکمیلی — حریم خصوصی، سوالات متداول و قراردادها
   ========================================================================== */

/**
 * ساخت صفحه‌ی «حریم خصوصی».
 *
 * @return array
 */
function zc_build_privacy_page() {
	$sections = array();

	$sections[] = zc_el_section(
		'zc_heading',
		array(
			'heading_title' => 'حریم <span>خصوصی</span>',
			'heading_sub'   => 'تعهد ما به حفاظت از اطلاعات شما مطابق قوانین جمهوری اسلامی ایران',
			'heading_align' => 'center',
			'heading_tag'   => 'h1',
		)
	);

	$sections[] = zc_el_stack(
		array(
			array(
				'zc_heading',
				array(
					'show_heading'  => 'yes',
					'heading_title' => 'متن <span>کامل</span> سیاست حریم خصوصی',
					'heading_align' => 'center',
				),
			),
			zc_el_richtext( zc_legal_privacy_html() ),
		)
	);

	return zc_el_merge_sections( $sections );
}

/**
 * ساخت صفحه‌ی «سوالات متداول».
 *
 * @return array
 */
function zc_build_faq_page() {
	$sections = array();

	$sections[] = zc_el_section(
		'zc_heading',
		array(
			'heading_title' => 'سوالات <span>متداول</span>',
			'heading_sub'   => 'پاسخ پرتکرارترین سوالات شما درباره دوره‌ها، خرید، پشتیبانی و امور حقوقی',
			'heading_align' => 'center',
			'heading_tag'   => 'h1',
		)
	);

	$sections[] = zc_el_stack(
		array(
			zc_el_richtext( zc_legal_faq_html() ),
		)
	);

	$sections[] = zc_el_section(
		'zc_faq',
		array(
			'show_heading'  => 'yes',
			'heading_title' => 'پرسش‌های <span>پرتکرار</span>',
			'heading_sub'   => 'نمونه‌ی آکاردئون پرسش و پاسخ',
			'heading_align' => 'center',
			'single_open'   => 'yes',
			'schema'        => 'yes',
		)
	);

	return zc_el_merge_sections( $sections );
}

/**
 * ساخت صفحه‌ی «قراردادهای خدمات».
 *
 * @return array
 */
function zc_build_contracts_page() {
	$sections = array();

	$sections[] = zc_el_section(
		'zc_heading',
		array(
			'heading_title' => 'قراردادهای <span>خدمات</span>',
			'heading_sub'   => 'متن دقیق قراردادهای تخصصی خدمات برنامه‌نویسی مطابق قوانین جمهوری اسلامی ایران',
			'heading_align' => 'center',
			'heading_tag'   => 'h1',
		)
	);

	$sections[] = zc_el_section(
		'zc_features',
		array(
			'show_heading'  => 'yes',
			'heading_title' => 'قراردادهای <span>آماده</span>',
			'heading_sub'   => 'برای هر خدمت، یک قرارداد استاندارد و قانونی تهیه شده است',
			'style_mode'    => 'card',
			'columns'       => '4',
			'items'         => array(
				array( '_id' => zc_el_id(), 'icon' => 'code', 'title' => 'طراحی سایت', 'text' => 'قرارداد طراحی و توسعه‌ی وب‌سایت' ),
				array( '_id' => zc_el_id(), 'icon' => 'settings', 'title' => 'نرم‌افزار ویندوز', 'text' => 'قرارداد توسعه‌ی نرم‌افزار دسکتاپ' ),
				array( '_id' => zc_el_id(), 'icon' => 'phone', 'title' => 'اپلیکیشن اندروید', 'text' => 'قرارداد توسعه‌ی اپلیکیشن موبایل' ),
				array( '_id' => zc_el_id(), 'icon' => 'edit', 'title' => 'تولید محتوا', 'text' => 'قرارداد تولید محتوای متنی و تصویری' ),
				array( '_id' => zc_el_id(), 'icon' => 'image', 'title' => 'طراحی گرافیک', 'text' => 'قرارداد طراحی گرافیک و هویت بصری' ),
				array( '_id' => zc_el_id(), 'icon' => 'chart', 'title' => 'سئو و بهینه‌سازی', 'text' => 'قرارداد خدمات سئو و بهینه‌سازی محتوا' ),
				array( '_id' => zc_el_id(), 'icon' => 'headphone', 'title' => 'مشاوره فنی', 'text' => 'قرارداد جلسات مشاوره‌ی تخصصی' ),
				array( '_id' => zc_el_id(), 'icon' => 'shield', 'title' => 'پشتیبانی سایت', 'text' => 'قرارداد پشتیبانی و نگهداری سایت' ),
			),
		)
	);

	$sections[] = zc_el_stack(
		array(
			array(
				'zc_heading',
				array(
					'show_heading'  => 'yes',
					'heading_title' => '۱. قرارداد <span>طراحی سایت</span>',
					'heading_align' => 'center',
				),
			),
			zc_el_richtext( zc_contract_website_html() ),
		)
	);

	$sections[] = zc_el_stack(
		array(
			array(
				'zc_heading',
				array(
					'show_heading'  => 'yes',
					'heading_title' => '۲. قرارداد <span>نرم‌افزار ویندوز</span>',
					'heading_align' => 'center',
				),
			),
			zc_el_richtext( zc_contract_windows_html() ),
		)
	);

	$sections[] = zc_el_stack(
		array(
			array(
				'zc_heading',
				array(
					'show_heading'  => 'yes',
					'heading_title' => '۳. قرارداد <span>اپلیکیشن اندروید</span>',
					'heading_align' => 'center',
				),
			),
			zc_el_richtext( zc_contract_android_html() ),
		)
	);

	$sections[] = zc_el_stack(
		array(
			array(
				'zc_heading',
				array(
					'show_heading'  => 'yes',
					'heading_title' => '۴. قرارداد <span>تولید محتوا</span>',
					'heading_align' => 'center',
				),
			),
			zc_el_richtext( zc_contract_content_html() ),
		)
	);

	$sections[] = zc_el_stack(
		array(
			array(
				'zc_heading',
				array(
					'show_heading'  => 'yes',
					'heading_title' => '۵. قرارداد <span>طراحی گرافیک</span>',
					'heading_align' => 'center',
				),
			),
			zc_el_richtext( zc_contract_graphic_html() ),
		)
	);

	$sections[] = zc_el_stack(
		array(
			array(
				'zc_heading',
				array(
					'show_heading'  => 'yes',
					'heading_title' => '۶. قرارداد <span>سئو و بهینه‌سازی</span>',
					'heading_align' => 'center',
				),
			),
			zc_el_richtext( zc_contract_seo_html() ),
		)
	);

	$sections[] = zc_el_stack(
		array(
			array(
				'zc_heading',
				array(
					'show_heading'  => 'yes',
					'heading_title' => '۷. قرارداد <span>مشاوره فنی</span>',
					'heading_align' => 'center',
				),
			),
			zc_el_richtext( zc_contract_consulting_html() ),
		)
	);

	$sections[] = zc_el_stack(
		array(
			array(
				'zc_heading',
				array(
					'show_heading'  => 'yes',
					'heading_title' => '۸. قرارداد <span>پشتیبانی سایت</span>',
					'heading_align' => 'center',
				),
			),
			zc_el_richtext( zc_contract_support_html() ),
		)
	);

	return zc_el_merge_sections( $sections );
}
