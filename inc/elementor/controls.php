<?php
/**
 * توابع کمکی کنترل‌های المنتور
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * لیست ترم‌های یک طبقه‌بندی برای select2.
 *
 * @param string $taxonomy طبقه‌بندی.
 * @return array
 */
function zc_get_terms_options( $taxonomy ) {
	$options = array();

	if ( ! taxonomy_exists( $taxonomy ) ) {
		return $options;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'number'     => 200,
		)
	);

	if ( is_wp_error( $terms ) ) {
		return $options;
	}

	foreach ( $terms as $term ) {
		$options[ $term->term_id ] = $term->name;
	}

	return $options;
}

/**
 * لیست پست‌های یک نوع برای select2.
 *
 * @param string $post_type نوع پست.
 * @param int    $limit     تعداد.
 * @return array
 */
function zc_get_posts_options( $post_type = 'post', $limit = 150 ) {
	$options = array();

	$posts = get_posts(
		array(
			'post_type'      => $post_type,
			'posts_per_page' => $limit,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	foreach ( $posts as $post ) {
		$options[ $post->ID ] = $post->post_title;
	}

	return $options;
}


/**
 * لیست آیکن‌های قالب برای انتخاب در المنتور.
 *
 * @return array
 */
function zc_get_icons_options() {
	$icons  = array_keys( zc_icon_library() );
	$labels = array(
		'code'        => 'کد',
		'book'        => 'کتاب',
		'video'       => 'ویدیو',
		'cart'        => 'سبد خرید',
		'user'        => 'کاربر',
		'users'       => 'کاربران',
		'star'        => 'ستاره',
		'heart'       => 'قلب',
		'award'       => 'مدال',
		'certificate' => 'گواهینامه',
		'wallet'      => 'کیف پول',
		'ticket'      => 'تیکت',
		'chart'       => 'نمودار',
		'calendar'    => 'تقویم',
		'chat'        => 'گفتگو',
		'bell'        => 'زنگ',
		'download'    => 'دانلود',
		'shield'      => 'سپر',
		'plugin'      => 'افزونه',
		'font'        => 'فونت',
		'headphone'   => 'پشتیبانی',
		'sparkle'     => 'درخشش',
		'gift'        => 'هدیه',
		'lock'        => 'قفل',
		'clock'       => 'ساعت',
		'grid'        => 'شبکه',
		'phone'       => 'تلفن',
		'mail'        => 'ایمیل',
		'pin'         => 'موقعیت',
		'search'      => 'جستجو',
		'play'        => 'پخش',
		'check'       => 'تیک',
		'info'        => 'اطلاعات',
		'question'    => 'سوال',
		'refresh'     => 'بروزرسانی',
		'send'        => 'ارسال',
		'edit'        => 'ویرایش',
		'eye'         => 'مشاهده',
		'filter'      => 'فیلتر',
	);

	$options = array();
	foreach ( $icons as $icon ) {
		$options[ $icon ] = isset( $labels[ $icon ] ) ? $labels[ $icon ] : $icon;
	}

	return $options;
}
