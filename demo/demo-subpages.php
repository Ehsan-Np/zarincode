<?php
/**
 * صفحات اختصاصی جدید دمو
 *
 * این فایل دو صفحه‌ی جداگانه می‌سازد که فرم‌های حذف‌شده از صفحه اصلی
 * در آن‌ها قرار می‌گیرند:
 *
 *   ۱) صفحه‌ی «جستجوی محصولات»  → ویجت جستجو + گرید محصولات فروشگاه
 *   ۲) صفحه‌ی «درخواست پروژه و مشاوره» → فرم کامل درخواست پروژه/مشاوره
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/elementor-helpers.php';

/**
 * ذخیره داده المنتور روی یک صفحه.
 *
 * @param int    $page_id شناسه صفحه.
 * @param array  $data    داده المنتور.
 * @return void
 */
function zc_apply_elementor_data( $page_id, array $data ) {
	update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $page_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
	update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
	update_post_meta( $page_id, '_zc_demo', '1' );
	update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $data, JSON_UNESCAPED_UNICODE ) ) );
}

/**
 * ساخت یا یافتن یک صفحه.
 *
 * @param string $slug  اسلاگ.
 * @param string $title عنوان.
 * @return int|false
 */
function zc_get_or_create_page( $slug, $title ) {
	$existing = get_page_by_path( $slug );
	if ( $existing ) {
		return $existing->ID;
	}

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

	return ( $page_id && ! is_wp_error( $page_id ) ) ? $page_id : false;
}

/**
 * padding واکنش‌گرای سکشن.
 *
 * @param string $top    بالا.
 * @param string $bottom پایین.
 * @return array
 */
function zc_subpage_pad( $top = '20', $bottom = '20' ) {
	return array(
		'padding'        => array(
			'unit'     => 'px',
			'top'      => $top,
			'right'    => '0',
			'bottom'   => $bottom,
			'left'     => '0',
			'isLinked' => false,
		),
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
	);
}

/**
 * نصب صفحه‌ی «جستجوی محصولات».
 *
 * @return int|false
 */
function zc_install_demo_shop_page() {
	$page_id = zc_get_or_create_page( 'products', 'جستجوی محصولات' );
	if ( ! $page_id ) {
		return false;
	}

	$sections   = array();
	$sections[] = zc_el_section(
		'zc_dark_search',
		array(
			'title'       => 'دنبال قالب، افزونه یا سورس کد آماده <span>هستید؟</span>',
			'sub'         => 'بیش از ۱۸۰ محصول آماده با کد استاندارد، مستندات فارسی و بروزرسانی رایگان',
			'show_finder' => 'yes',
			'btn_text'    => 'جست‌وجو در فروشگاه',
		),
		zc_subpage_pad( '20', '0' )
	);

	$sections[] = zc_el_section(
		'zc_products',
		array(
			'show_heading'   => 'yes',
			'heading_title'  => 'همه‌ی <span>محصولات</span> فروشگاه',
			'heading_sub'    => 'قالب وردپرس، افزونه، سورس کد آماده و فونت فارسی با پشتیبانی کامل',
			'heading_align'  => 'center',
			'columns'        => '4',
			'posts_count'    => 12,
			'source'         => 'latest',
			'show_cat_tabs'  => 'yes',
		),
		zc_subpage_pad( '10', '20' )
	);

	zc_apply_elementor_data( $page_id, zc_el_merge_sections( $sections ) );
	return $page_id;
}

/**
 * نصب صفحه‌ی «درخواست پروژه و مشاوره».
 *
 * @return int|false
 */
function zc_install_demo_request_page() {
	$page_id = zc_get_or_create_page( 'request', 'درخواست پروژه و مشاوره' );
	if ( ! $page_id ) {
		return false;
	}

	$sections   = array();
	$sections[] = zc_el_section(
		'zc_request_form',
		array(
			'show_heading'  => 'yes',
			'heading_title' => 'درخواست <span>پروژه</span> یا مشاوره',
			'heading_sub'   => 'فرم زیر را پر کنید؛ کارشناسان ما حداکثر تا یک روز کاری با شما تماس می‌گیرند.',
			'heading_align' => 'center',
		),
		zc_subpage_pad( '20', '20' )
	);

	zc_apply_elementor_data( $page_id, zc_el_merge_sections( $sections ) );
	return $page_id;
}
