<?php
/**
 * سازنده‌ی صفحات جذاب دمو — تماس با ما و درخواست پروژه
 *
 * این فایل صفحه‌ی «تماس با ما» و صفحه‌ی «درخواست پروژه و مشاوره» را با
 * داده‌ی المنتور می‌سازد تا ظاهری حرفه‌ای، مدرن و واکنش‌گرا داشته باشند.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/elementor-helpers.php';

/**
 * Padding واکنش‌گرای سکشن.
 *
 * @param string $top    بالا.
 * @param string $bottom پایین.
 * @return array
 */
function zc_demo_builder_pad( $top = '0', $bottom = '0' ) {
	return array(
		'padding'        => array(
			'unit'     => 'px', 'top' => $top, 'right' => '0', 'bottom' => $bottom, 'left' => '0', 'isLinked' => false,
		),
		'padding_tablet' => array(
			'unit'     => 'px', 'top' => $top, 'right' => '20', 'bottom' => $bottom, 'left' => '20', 'isLinked' => false,
		),
		'padding_mobile' => array(
			'unit'     => 'px', 'top' => $top, 'right' => '14', 'bottom' => $bottom, 'left' => '14', 'isLinked' => false,
		),
	);
}

/**
 * ساخت داده‌ی کامل صفحه‌ی «تماس با ما».
 *
 * @return array
 */
function zc_build_contact_page_data() {

	$sections = array();

	/* ---------- ۱. عنوان صفحه ---------- */
	$sections[] = zc_el_section(
		'zc_heading',
		array(
			'heading_title' => 'با <span>زرین کد</span> در تماس باشید',
			'heading_sub'   => 'سوال، پیشنهاد یا ایده‌ی پروژه‌ای دارید؟ تیم پشتیبانی و مشاوران ما آماده‌ی پاسخ‌گویی به شما هستند.',
			'heading_align' => 'center',
			'heading_tag'   => 'h1',
		),
		zc_demo_builder_pad( '20', '0' )
	);

	/* ---------- ۲. کارت‌های اطلاعات تماس ---------- */
	$sections[] = zc_el_section(
		'zc_features',
		array(
			'style_mode' => 'card',
			'columns'    => '4',
			'items'      => array(
				array( '_id' => zc_el_id(), 'icon' => 'phone', 'title' => 'تماس تلفنی', 'text' => 'شنبه تا چهارشنبه، ساعت ۹ تا ۱۸', 'link' => array( 'url' => 'tel:07142380267' ) ),
				array( '_id' => zc_el_id(), 'icon' => 'send', 'title' => 'واتساپ و پیامک', 'text' => 'پاسخ در کمتر از ۲۴ ساعت کاری', 'link' => array( 'url' => 'https://wa.me/989024561001' ) ),
				array( '_id' => zc_el_id(), 'icon' => 'mail', 'title' => 'ایمیل', 'text' => 'info@zarincode.com', 'link' => array( 'url' => 'mailto:info@zarincode.com' ) ),
				array( '_id' => zc_el_id(), 'icon' => 'pin', 'title' => 'آدرس', 'text' => 'فارس، کازرون، دهستان انارستان' ),
			),
		),
		zc_demo_builder_pad( '20', '0' )
	);

	/* ---------- ۳. فرم تماس + اطلاعات جانبی ---------- */
	$left = array(
		array(
			'zc_contact_form',
			array(
				'form_title'   => 'ارسال پیام به تیم زرین کد',
				'show_subject' => 'yes',
				'show_phone'   => 'yes',
				'show_dept'    => 'yes',
			),
		),
	);

	$right = array(
		array(
			'zc_heading',
			array(
				'heading_title' => 'اطلاعات <span>تماس</span>',
				'heading_sub'   => 'راه‌های ارتباطی متنوعی برای شما فراهم کرده‌ایم؛ هر کدام را که راحت‌ترید انتخاب کنید.',
				'heading_align' => 'start',
			),
		),
		array(
			'zc_features',
			array(
				'style_mode' => 'inline',
				'columns'    => '1',
				'items'      => array(
					array( '_id' => zc_el_id(), 'icon' => 'phone', 'title' => 'تلفن ثابت', 'text' => '071-42380267' ),
					array( '_id' => zc_el_id(), 'icon' => 'send', 'title' => 'موبایل', 'text' => '09024561001' ),
					array( '_id' => zc_el_id(), 'icon' => 'mail', 'title' => 'ایمیل', 'text' => 'info@zarincode.com' ),
					array( '_id' => zc_el_id(), 'icon' => 'clock', 'title' => 'ساعت کاری', 'text' => 'شنبه تا چهارشنبه ۹ تا ۱۸' ),
					array( '_id' => zc_el_id(), 'icon' => 'pin', 'title' => 'آدرس', 'text' => 'استان فارس، شهرستان کازرون' ),
				),
			),
		),
	);

	$sections[] = zc_el_two_cols( $left, $right, zc_demo_builder_pad( '20', '0' ), 55 );

	/* ---------- ۴. نقشه ---------- */
	$sections[] = zc_el_section(
		'zc_map',
		array(
			'lat'    => '35.7575',
			'lng'    => '51.4100',
			'zoom'   => array( 'size' => 14 ),
			'height' => array( 'size' => 380 ),
		),
		zc_demo_builder_pad( '10', '20' )
	);

	return zc_el_merge_sections( $sections );
}

/**
 * ساخت داده‌ی کامل صفحه‌ی «درخواست پروژه و مشاوره».
 *
 * @return array
 */
function zc_build_request_page_data() {

	$sections = array();

	/* ---------- ۱. عنوان صفحه ---------- */
	$sections[] = zc_el_section(
		'zc_heading',
		array(
			'heading_title' => 'درخواست <span>پروژه</span> یا مشاوره',
			'heading_sub'   => 'فرم زیر را پر کنید؛ کارشناسان ما حداکثر تا یک روز کاری با شما تماس می‌گیرند و برآورد دقیق زمان و هزینه ارائه می‌دهند.',
			'heading_align' => 'center',
			'heading_tag'   => 'h1',
		),
		zc_demo_builder_pad( '20', '0' )
	);

	/* ---------- ۲. فرم درخواست ---------- */
	$sections[] = zc_el_section(
		'zc_request_form',
		array(
			'show_heading'   => 'yes',
			'heading_title'  => 'فرم <span>درخواست</span>',
			'heading_sub'    => 'مشخصات پروژه یا نیاز خود را شرح دهید؛ ما در اولین فرصت با شما تماس می‌گیریم.',
			'heading_align'  => 'center',
			'btn_text'       => 'ارسال درخواست',
			'side_title'     => 'چرا زرین کد؟',
			'side_items'     => "مشاوره رایگان پیش از شروع\nقرارداد رسمی و شفاف\nتحویل مرحله‌ای پروژه\nپشتیبانی پس از تحویل\nضمانت بازگشت وجه",
			'show_side_stats'=> 'yes',
			'side_stats'     => array(
				array( '_id' => zc_el_id(), 'stat_num' => '۳۵۰+', 'stat_label' => 'پروژه موفق' ),
				array( '_id' => zc_el_id(), 'stat_num' => '۲۴ ساعت', 'stat_label' => 'زمان پاسخ' ),
				array( '_id' => zc_el_id(), 'stat_num' => '۹۷٪', 'stat_label' => 'رضایت کارفرما' ),
			),
			'dark'           => 'no',
		),
		zc_demo_builder_pad( '10', '10' )
	);

	/* ---------- ۳. CTA پایانی ---------- */
	$sections[] = zc_el_section(
		'zc_cta_bar',
		array(
			'bold_text'  => 'پروژه‌ای در ذهن دارید؟',
			'light_text' => 'همین حالا مشاوره رایگان بگیرید و برآورد دقیق زمان و هزینه دریافت کنید.',
			'btn1_text'  => 'درخواست مشاوره رایگان',
			'btn1_link'  => array( 'url' => '#', 'is_external' => '', 'nofollow' => '' ),
			'btn1_style' => 'gold',
			'btn2_text'  => 'مشاهده نمونه‌کارها',
			'btn2_link'  => array( 'url' => get_post_type_archive_link( 'zc_project' ), 'is_external' => '', 'nofollow' => '' ),
			'btn2_style' => 'navy',
		),
		zc_demo_builder_pad( '20', '20' )
	);

	return zc_el_merge_sections( $sections );
}

/**
 * ذخیره داده‌ی المنتور روی یک صفحه.
 *
 * @param int   $page_id شناسه.
 * @param array $data    داده.
 * @return void
 */
function zc_apply_page_builder_data( $page_id, array $data ) {
	update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $page_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
	update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
	update_post_meta( $page_id, '_zc_demo', '1' );
	update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $data, JSON_UNESCAPED_UNICODE ) ) );
}

/**
 * اعمال قالب مدرن روی صفحه‌های کلیدی و ساخت/به‌روزرسانی آن‌ها.
 *
 * @return void
 */
function zc_apply_modern_page_template( $slug, $title ) {
	$existing = get_page_by_path( $slug );
	$page_id  = 0;

	if ( $existing ) {
		$page_id = $existing->ID;
		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_title'   => $title,
				'post_content' => '',
				'post_status'  => 'publish',
			)
		);
	} else {
		$page_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_content' => '',
				'meta_input'   => array( '_zc_demo' => '1' ),
			)
		);
	}

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		// قالب مدرن (نه المنتور) — محتوای صفحه از ماژول modern-pages می‌آید.
		update_post_meta( $page_id, '_wp_page_template', 'templates/template-modern-page.php' );
		update_post_meta( $page_id, '_zc_modern_page', '1' );
		update_post_meta( $page_id, '_zc_demo', '1' );

		// حذف داده المنتور قدیمی تا تداخلی ایجاد نشود.
		delete_post_meta( $page_id, '_elementor_edit_mode' );
		delete_post_meta( $page_id, '_elementor_data' );
	}

	return $page_id;
}

/**
 * نصب/به‌روزرسانی صفحات مدرن قالب.
 *
 * @return void
 */
function zc_install_demo_pages_builder() {
	// صفحات کلیدی با قالب مدرن.
	$zc_pages = array(
		'contact-us'    => 'تماس با ما',
		'about-us'      => 'درباره ما',
		'request'       => 'درخواست پروژه و مشاوره',
		// «خدمات» از آرشیو zc_service (اسلاگ services) رندر می‌شود — صفحه نمی‌سازیم.
		'terms'         => 'شرایط و قوانین',
		'privacy-policy'=> 'حریم خصوصی',
		'warranty'      => 'گارانتی و تضمین کیفیت',
		'refund-policy' => 'شرایط بازگشت وجه',
	);

	foreach ( $zc_pages as $slug => $title ) {
		zc_apply_modern_page_template( $slug, $title );
	}

	if ( class_exists( '\\Elementor\\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
}
