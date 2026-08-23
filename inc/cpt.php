<?php
/**
 * انواع محتوای سفارشی و طبقه‌بندی‌ها
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * ثبت پست‌تایپ‌ها.
 *
 * @return void
 */
function zc_register_post_types() {

	/* ---------- دوره آموزشی ---------- */
	register_post_type(
		'zc_course',
		array(
			'labels'       => array(
				'name'               => __( 'دوره‌ها', 'zarincode' ),
				'singular_name'      => __( 'دوره', 'zarincode' ),
				'add_new'            => __( 'افزودن دوره', 'zarincode' ),
				'add_new_item'       => __( 'افزودن دوره جدید', 'zarincode' ),
				'edit_item'          => __( 'ویرایش دوره', 'zarincode' ),
				'all_items'          => __( 'همه دوره‌ها', 'zarincode' ),
				'search_items'       => __( 'جستجوی دوره', 'zarincode' ),
				'not_found'          => __( 'دوره‌ای یافت نشد', 'zarincode' ),
				'menu_name'          => __( 'دوره‌های آموزشی', 'zarincode' ),
			),
			'public'       => true,
			'has_archive'  => 'courses',
			'rewrite'      => array( 'slug' => zc_opt( 'zc_course_slug', 'course' ), 'with_front' => false ),
			'menu_icon'    => 'dashicons-welcome-learn-more',
			'menu_position'=> 26,
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'author', 'custom-fields', 'revisions' ),
			'show_in_rest' => true,
			'taxonomies'   => array(),
		)
	);

	/* ---------- درس / جلسه ---------- */
	register_post_type(
		'zc_lesson',
		array(
			'labels'       => array(
				'name'          => __( 'جلسات', 'zarincode' ),
				'singular_name' => __( 'جلسه', 'zarincode' ),
				'add_new_item'  => __( 'افزودن جلسه', 'zarincode' ),
				'menu_name'     => __( 'جلسات دوره', 'zarincode' ),
			),
			'public'       => true,
			'has_archive'  => false,
			'rewrite'      => array( 'slug' => 'lesson', 'with_front' => false ),
			'show_in_menu' => 'edit.php?post_type=zc_course',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
			'show_in_rest' => true,
		)
	);

	/* ---------- آموزش (مطلب آموزشی رایگان) ---------- */
	register_post_type(
		'zc_tutorial',
		array(
			'labels'       => array(
				'name'          => __( 'آموزش‌ها', 'zarincode' ),
				'singular_name' => __( 'آموزش', 'zarincode' ),
				'add_new_item'  => __( 'افزودن آموزش', 'zarincode' ),
				'menu_name'     => __( 'آموزش‌ها', 'zarincode' ),
			),
			'public'       => true,
			'has_archive'  => 'tutorials',
			'rewrite'      => array( 'slug' => 'tutorial', 'with_front' => false ),
			'menu_icon'    => 'dashicons-media-code',
			'menu_position'=> 27,
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'author', 'revisions' ),
			'show_in_rest' => true,
		)
	);

	/* ---------- مدرس ---------- */
	register_post_type(
		'zc_teacher',
		array(
			'labels'       => array(
				'name'          => __( 'مدرسان', 'zarincode' ),
				'singular_name' => __( 'مدرس', 'zarincode' ),
				'add_new_item'  => __( 'افزودن مدرس', 'zarincode' ),
				'menu_name'     => __( 'مدرسان', 'zarincode' ),
			),
			'public'       => true,
			'has_archive'  => 'teachers',
			'rewrite'      => array( 'slug' => 'teacher', 'with_front' => false ),
			'menu_icon'    => 'dashicons-businessperson',
			'menu_position'=> 28,
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'show_in_rest' => true,
		)
	);

	/* ---------- نظر مشتری (تستیمونیال) ---------- */
	register_post_type(
		'zc_testimonial',
		array(
			'labels'       => array(
				'name'          => __( 'نظرات مشتریان', 'zarincode' ),
				'singular_name' => __( 'نظر مشتری', 'zarincode' ),
				'menu_name'     => __( 'نظرات مشتریان', 'zarincode' ),
			),
			'public'       => true,
			'has_archive'  => false,
			'exclude_from_search' => true,
			'rewrite'      => array( 'slug' => 'testimonial' ),
			'menu_icon'    => 'dashicons-format-quote',
			'menu_position'=> 29,
			'supports'     => array( 'title', 'editor', 'thumbnail' ),
			'show_in_rest' => true,
		)
	);

	/* ---------- تیکت پشتیبانی ---------- */
	register_post_type(
		'zc_ticket',
		array(
			'labels'       => array(
				'name'          => __( 'تیکت‌ها', 'zarincode' ),
				'singular_name' => __( 'تیکت', 'zarincode' ),
				'menu_name'     => __( 'تیکت‌های پشتیبانی', 'zarincode' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'menu_icon'           => 'dashicons-tickets-alt',
			'menu_position'       => 30,
			'supports'            => array( 'title', 'editor', 'author', 'comments' ),
			'capability_type'     => 'post',
		)
	);

	/* ---------- رزرو نوبت ---------- */
	register_post_type(
		'zc_booking',
		array(
			'labels'       => array(
				'name'          => __( 'رزروها', 'zarincode' ),
				'singular_name' => __( 'رزرو', 'zarincode' ),
				'menu_name'     => __( 'رزرو نوبت', 'zarincode' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'menu_icon'           => 'dashicons-calendar-alt',
			'menu_position'       => 31,
			'supports'            => array( 'title', 'editor', 'author' ),
		)
	);

	/* ---------- خدمات (برای رزرو نوبت / صفحه خدمات) ---------- */
	register_post_type(
		'zc_service',
		array(
			'labels'        => array(
				'name'               => __( 'خدمات', 'zarincode' ),
				'singular_name'      => __( 'خدمت', 'zarincode' ),
				'menu_name'          => __( 'خدمات', 'zarincode' ),
				'add_new'            => __( 'افزودن خدمت', 'zarincode' ),
				'add_new_item'       => __( 'افزودن خدمت تازه', 'zarincode' ),
				'edit_item'          => __( 'ویرایش خدمت', 'zarincode' ),
				'all_items'          => __( 'همه خدمات', 'zarincode' ),
				'search_items'       => __( 'جستجوی خدمات', 'zarincode' ),
				'not_found'          => __( 'خدمتی یافت نشد.', 'zarincode' ),
			),
			'public'        => true,
			'has_archive'   => 'services',
			'rewrite'       => array( 'slug' => 'service', 'with_front' => false ),
			'menu_icon'     => 'dashicons-portfolio',
			'menu_position' => 26,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'comments' ),
			'show_in_rest'  => true,
		)
	);

	/* ---------- نمونه‌کار / پروژه‌های انجام‌شده ---------- */
	register_post_type(
		'zc_project',
		array(
			'labels'        => array(
				'name'          => __( 'نمونه‌کارها', 'zarincode' ),
				'singular_name' => __( 'نمونه‌کار', 'zarincode' ),
				'menu_name'     => __( 'نمونه‌کارها', 'zarincode' ),
				'add_new'       => __( 'افزودن نمونه‌کار', 'zarincode' ),
				'add_new_item'  => __( 'افزودن نمونه‌کار تازه', 'zarincode' ),
				'edit_item'     => __( 'ویرایش نمونه‌کار', 'zarincode' ),
				'all_items'     => __( 'همه نمونه‌کارها', 'zarincode' ),
			),
			'public'        => true,
			'has_archive'   => 'portfolio',
			'rewrite'       => array( 'slug' => 'project', 'with_front' => false ),
			'menu_icon'     => 'dashicons-images-alt2',
			'menu_position' => 27,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'show_in_rest'  => true,
		)
	);

	/* ---------- درخواست پروژه (سفارش مشتری) ---------- */
	register_post_type(
		'zc_request',
		array(
			'labels'        => array(
				'name'          => __( 'درخواست‌های پروژه', 'zarincode' ),
				'singular_name' => __( 'درخواست پروژه', 'zarincode' ),
				'menu_name'     => __( 'درخواست پروژه', 'zarincode' ),
				'all_items'     => __( 'همه درخواست‌ها', 'zarincode' ),
				'edit_item'     => __( 'بررسی درخواست', 'zarincode' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'menu_icon'           => 'dashicons-clipboard',
			'menu_position'       => 28,
			'supports'            => array( 'title', 'editor' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		)
	);

	/* ---------- سوالات متداول ---------- */
	register_post_type(
		'zc_faq',
		array(
			'labels'       => array(
				'name'          => __( 'سوالات متداول', 'zarincode' ),
				'singular_name' => __( 'سوال', 'zarincode' ),
				'menu_name'     => __( 'سوالات متداول', 'zarincode' ),
			),
			'public'       => true,
			'has_archive'  => false,
			'exclude_from_search' => true,
			'rewrite'      => array( 'slug' => 'faq' ),
			'menu_icon'    => 'dashicons-editor-help',
			'menu_position'=> 33,
			'supports'     => array( 'title', 'editor' ),
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'zc_register_post_types' );

/**
 * ثبت طبقه‌بندی‌ها.
 *
 * @return void
 */
function zc_register_taxonomies() {

	register_taxonomy(
		'zc_course_cat',
		array( 'zc_course' ),
		array(
			'labels'            => array(
				'name'          => __( 'دسته‌بندی دوره‌ها', 'zarincode' ),
				'singular_name' => __( 'دسته دوره', 'zarincode' ),
				'add_new_item'  => __( 'افزودن دسته جدید', 'zarincode' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'course-category' ),
		)
	);

	register_taxonomy(
		'zc_course_tag',
		array( 'zc_course', 'zc_tutorial' ),
		array(
			'labels'            => array(
				'name'          => __( 'برچسب‌های آموزشی', 'zarincode' ),
				'singular_name' => __( 'برچسب', 'zarincode' ),
			),
			'hierarchical'      => false,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'course-tag' ),
		)
	);

	register_taxonomy(
		'zc_tutorial_cat',
		array( 'zc_tutorial' ),
		array(
			'labels'            => array(
				'name'          => __( 'دسته‌بندی آموزش‌ها', 'zarincode' ),
				'singular_name' => __( 'دسته آموزش', 'zarincode' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'tutorial-category' ),
		)
	);

	register_taxonomy(
		'zc_teacher_skill',
		array( 'zc_teacher' ),
		array(
			'labels'            => array(
				'name'          => __( 'تخصص‌ها', 'zarincode' ),
				'singular_name' => __( 'تخصص', 'zarincode' ),
			),
			'hierarchical'      => false,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
		)
	);

	register_taxonomy(
		'zc_faq_cat',
		array( 'zc_faq' ),
		array(
			'labels'            => array( 'name' => __( 'دسته سوالات', 'zarincode' ) ),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
		)
	);

	/* ---------- دسته‌بندی خدمات ---------- */
	register_taxonomy(
		'zc_service_cat',
		array( 'zc_service' ),
		array(
			'labels'            => array(
				'name'          => __( 'دسته‌بندی خدمات', 'zarincode' ),
				'singular_name' => __( 'دسته خدمت', 'zarincode' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'service-category' ),
		)
	);

	/* ---------- دسته‌بندی نمونه‌کارها ---------- */
	register_taxonomy(
		'zc_project_cat',
		array( 'zc_project' ),
		array(
			'labels'            => array(
				'name'          => __( 'دسته‌بندی نمونه‌کار', 'zarincode' ),
				'singular_name' => __( 'دسته نمونه‌کار', 'zarincode' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'portfolio-category' ),
		)
	);

	/* ---------- تکنولوژی‌های به‌کاررفته در نمونه‌کار ---------- */
	register_taxonomy(
		'zc_project_tech',
		array( 'zc_project' ),
		array(
			'labels'            => array(
				'name'          => __( 'تکنولوژی‌ها', 'zarincode' ),
				'singular_name' => __( 'تکنولوژی', 'zarincode' ),
			),
			'hierarchical'      => false,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'tech' ),
		)
	);
}
add_action( 'init', 'zc_register_taxonomies' );

/**
 * ستون‌های سفارشی لیست دوره‌ها در پیشخوان.
 *
 * @param array $cols ستون‌ها.
 * @return array
 */
function zc_course_columns( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['zc_price']    = __( 'قیمت', 'zarincode' );
			$new['zc_students'] = __( 'دانشجو', 'zarincode' );
			$new['zc_lessons']  = __( 'جلسات', 'zarincode' );
			$new['zc_status']   = __( 'وضعیت دوره', 'zarincode' );
		}
	}
	return $new;
}
add_filter( 'manage_zc_course_posts_columns', 'zc_course_columns' );

/**
 * محتوای ستون‌های سفارشی.
 *
 * @param string $col ستون.
 * @param int    $id  شناسه.
 * @return void
 */
function zc_course_column_content( $col, $id ) {
	switch ( $col ) {
		case 'zc_price':
			$price = get_post_meta( $id, '_zc_price', true );
			echo $price ? esc_html( zc_fa_num( number_format( (float) $price ) ) . ' ' . zc_opt( 'zc_currency_symbol', 'تومان' ) ) : '<span style="color:#16A34A">' . esc_html__( 'رایگان', 'zarincode' ) . '</span>';
			break;
		case 'zc_students':
			echo esc_html( zc_fa_num( (int) get_post_meta( $id, '_zc_students', true ) ) );
			break;
		case 'zc_lessons':
			$sections = get_post_meta( $id, '_zc_curriculum', true );
			$count    = 0;
			if ( is_array( $sections ) ) {
				foreach ( $sections as $s ) {
					$count += isset( $s['lessons'] ) && is_array( $s['lessons'] ) ? count( $s['lessons'] ) : 0;
				}
			}
			echo esc_html( zc_fa_num( $count ) );
			break;
		case 'zc_status':
			$status = get_post_meta( $id, '_zc_course_status', true );
			$labels = array(
				'completed'   => array( 'تکمیل شده', '#16A34A' ),
				'in-progress' => array( 'در حال برگزاری', '#F59E0B' ),
				'upcoming'    => array( 'به زودی', '#2563EB' ),
			);
			$s = isset( $labels[ $status ] ) ? $labels[ $status ] : $labels['completed'];
			printf( '<span style="color:%s;font-weight:600">%s</span>', esc_attr( $s[1] ), esc_html( $s[0] ) );
			break;
	}
}
add_action( 'manage_zc_course_posts_custom_column', 'zc_course_column_content', 10, 2 );

/**
 * افزودن پست‌تایپ‌های قالب به نتایج جستجوی اصلی.
 *
 * @param WP_Query $query کوئری.
 * @return void
 */
function zc_search_include_cpt( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
		$types = array( 'post', 'page', 'zc_course', 'zc_tutorial', 'zc_learning_path' );
		if ( zc_is_woo() ) {
			$types[] = 'product';
		}
		$query->set( 'post_type', $types );
	}
}
add_action( 'pre_get_posts', 'zc_search_include_cpt' );
