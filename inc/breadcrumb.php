<?php
/**
 * مسیر راهنما (Breadcrumb)
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * نمایش بردکرامب.
 *
 * @param bool $light حالت روشن (روی پس‌زمینه تیره).
 * @return void
 */
function zc_breadcrumb( $light = false ) {
	if ( is_front_page() ) {
		return;
	}

	$sep   = '<span class="sep">/</span>';
	$items = array();

	$items[] = sprintf(
		'<a href="%s">%s %s</a>',
		esc_url( home_url( '/' ) ),
		zc_icon( 'grid', 14 ),
		esc_html__( 'خانه', 'zarincode' )
	);

	if ( is_singular( 'post' ) ) {
		$cats = get_the_category();
		if ( $cats ) {
			$items[] = sprintf( '<a href="%s">%s</a>', esc_url( get_category_link( $cats[0] ) ), esc_html( $cats[0]->name ) );
		}
		$items[] = '<span>' . esc_html( zc_excerpt( get_the_title(), 7 ) ) . '</span>';

	} elseif ( is_singular( 'zc_course' ) ) {
		$archive = get_post_type_archive_link( 'zc_course' );
		$items[] = sprintf( '<a href="%s">%s</a>', esc_url( $archive ), esc_html__( 'دوره‌های آموزشی', 'zarincode' ) );
		$terms   = get_the_terms( get_the_ID(), 'zc_course_cat' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$items[] = sprintf( '<a href="%s">%s</a>', esc_url( get_term_link( $terms[0] ) ), esc_html( $terms[0]->name ) );
		}
		$items[] = '<span>' . esc_html( zc_excerpt( get_the_title(), 7 ) ) . '</span>';

	} elseif ( is_singular( 'product' ) ) {
		if ( function_exists( 'wc_get_page_permalink' ) ) {
			$items[] = sprintf( '<a href="%s">%s</a>', esc_url( wc_get_page_permalink( 'shop' ) ), esc_html__( 'فروشگاه', 'zarincode' ) );
		}
		$terms = get_the_terms( get_the_ID(), 'product_cat' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$items[] = sprintf( '<a href="%s">%s</a>', esc_url( get_term_link( $terms[0] ) ), esc_html( $terms[0]->name ) );
		}
		$items[] = '<span>' . esc_html( zc_excerpt( get_the_title(), 7 ) ) . '</span>';

	} elseif ( is_page() ) {
		$parents = array_reverse( get_post_ancestors( get_the_ID() ) );
		foreach ( $parents as $parent ) {
			$items[] = sprintf( '<a href="%s">%s</a>', esc_url( get_permalink( $parent ) ), esc_html( get_the_title( $parent ) ) );
		}
		$items[] = '<span>' . esc_html( get_the_title() ) . '</span>';

	} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
		// عنوان پیش‌فرض ووکامرس انگلیسی است؛ عنوان فارسی جایگزین می‌شود.
		$items[] = '<span>' . esc_html__( 'فروشگاه', 'zarincode' ) . '</span>';

	} elseif ( function_exists( 'is_product_category' ) && ( is_product_category() || is_product_tag() ) ) {
		$items[] = sprintf( '<a href="%s">%s</a>', esc_url( wc_get_page_permalink( 'shop' ) ), esc_html__( 'فروشگاه', 'zarincode' ) );
		$items[] = '<span>' . esc_html( single_term_title( '', false ) ) . '</span>';

	} elseif ( is_category() || is_tag() || is_tax() ) {
		$items[] = '<span>' . esc_html( single_term_title( '', false ) ) . '</span>';

	} elseif ( is_post_type_archive() ) {
		$zc_pt    = get_post_type_object( get_query_var( 'post_type' ) );
		$zc_label = $zc_pt ? $zc_pt->labels->name : post_type_archive_title( '', false );
		$items[]  = '<span>' . esc_html( $zc_label ) . '</span>';

	} elseif ( is_search() ) {
		$items[] = '<span>' . esc_html__( 'نتایج جستجو', 'zarincode' ) . '</span>';

	} elseif ( is_404() ) {
		$items[] = '<span>' . esc_html__( 'صفحه یافت نشد', 'zarincode' ) . '</span>';

	} elseif ( is_author() ) {
		$items[] = '<span>' . esc_html( get_the_author() ) . '</span>';

	} elseif ( is_home() ) {
		$items[] = '<span>' . esc_html__( 'بلاگ', 'zarincode' ) . '</span>';
	}

	printf(
		'<nav class="zc-breadcrumb%s" aria-label="%s">%s</nav>',
		$light ? '' : ' zc-breadcrumb--light',
		esc_attr__( 'مسیر راهنما', 'zarincode' ),
		wp_kses_post( implode( ' ' . $sep . ' ', $items ) )
	);
}
