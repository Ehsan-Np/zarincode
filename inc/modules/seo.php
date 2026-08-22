<?php
/**
 * بهینه‌سازی سئو (سازگار با یواست و رنک‌مث)
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * آیا افزونه سئو فعال است؟
 *
 * @return bool
 */
function zc_has_seo_plugin() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || class_exists( 'All_in_One_SEO_Pack' );
}

/**
 * افزودن متا تگ‌های سئو در صورت نبود افزونه.
 *
 * @return void
 */
function zc_seo_meta_tags() {
	if ( zc_has_seo_plugin() || ! zc_opt( 'zc_seo_enable', true ) ) {
		return;
	}

	$title = wp_get_document_title();
	$desc  = '';
	$image = '';
	$url   = home_url( add_query_arg( array() ) );

	if ( is_singular() ) {
		$post_id = get_the_ID();
		$desc    = get_post_meta( $post_id, '_zc_meta_desc', true );
		if ( ! $desc ) {
			$desc = zc_excerpt( get_the_excerpt(), 28 );
		}
		$image = get_the_post_thumbnail_url( $post_id, 'full' );
		$url   = get_permalink();
	} elseif ( is_home() || is_front_page() ) {
		$desc  = get_bloginfo( 'description' );
		$logo  = zc_opt( 'zc_logo', '' );
		$image = is_array( $logo ) ? ( $logo['url'] ?? '' ) : $logo;
	} elseif ( is_category() || is_tax() ) {
		$desc = wp_strip_all_tags( term_description() );
	}

	$desc = mb_substr( wp_strip_all_tags( $desc ), 0, 160 );

	echo "\n<!-- Zarincode SEO -->\n";
	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );

	// Open Graph.
	printf( '<meta property="og:locale" content="%s">' . "\n", esc_attr( get_locale() ) );
	printf( '<meta property="og:type" content="%s">' . "\n", is_singular() ? 'article' : 'website' );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );

	if ( $image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image ) );
	}

	printf( '<meta name="twitter:card" content="summary_large_image">' . "\n" );
	printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $desc ) );
	echo "<!-- /Zarincode SEO -->\n\n";
}
add_action( 'wp_head', 'zc_seo_meta_tags', 2 );

/**
 * افزودن اسکیمای ساختاریافته.
 *
 * @return void
 */
function zc_schema_markup() {
	if ( ! zc_opt( 'zc_schema_enable', true ) ) {
		return;
	}

	$schema = array();

	// سازمان.
	if ( is_front_page() ) {
		$logo   = zc_opt( 'zc_logo', '' );
		$schema = array(
			'@context'      => 'https://schema.org',
			'@type'         => 'EducationalOrganization',
			'name'          => get_bloginfo( 'name' ),
			'url'           => home_url( '/' ),
			'logo'          => is_array( $logo ) ? ( $logo['url'] ?? '' ) : $logo,
			'description'   => get_bloginfo( 'description' ),
			'sameAs'        => array_values( zc_social_links() ),
			'contactPoint'  => array_values(
				array_filter(
					array(
						zc_opt( 'zc_phone', '' ) ? array(
							'@type'             => 'ContactPoint',
							'telephone'         => zc_opt( 'zc_phone', '' ),
							'contactType'       => 'customer support',
							'areaServed'        => 'IR',
							'availableLanguage' => array( 'fa', 'Persian' ),
						) : null,
						zc_opt( 'zc_mobile', '' ) ? array(
							'@type'             => 'ContactPoint',
							'telephone'         => zc_opt( 'zc_mobile', '' ),
							'contactType'       => 'sales',
							'areaServed'        => 'IR',
							'availableLanguage' => array( 'fa', 'Persian' ),
						) : null,
					)
				)
			),
		);

		// نشانی پستی برای سئوی محلی.
		$zc_addr = zc_opt( 'zc_address', '' );

		if ( $zc_addr ) {
			$schema['address'] = array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => $zc_addr,
				'addressRegion'   => zc_opt( 'zc_address_region', __( 'فارس', 'zarincode' ) ),
				'addressLocality' => zc_opt( 'zc_address_city', __( 'کازرون', 'zarincode' ) ),
				'addressCountry'  => 'IR',
			);
		}
	} elseif ( is_singular( 'zc_course' ) ) {
		$id    = get_the_ID();
		$price = (float) get_post_meta( $id, '_zc_price', true );
		$sale  = (float) get_post_meta( $id, '_zc_sale_price', true );

		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Course',
			'name'        => get_the_title(),
			'description' => zc_excerpt( get_the_excerpt(), 40 ),
			'provider'    => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
				'url'   => home_url( '/' ),
			),
			'offers'      => array(
				'@type'         => 'Offer',
				'price'         => $sale ? $sale : $price,
				'priceCurrency' => 'IRR',
				'availability'  => 'https://schema.org/InStock',
				'url'           => get_permalink(),
			),
		);

		$rating = (float) get_post_meta( $id, '_zc_rating', true );
		$count  = (int) get_post_meta( $id, '_zc_rating_count', true );
		if ( $rating > 0 && $count > 0 ) {
			$schema['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => $rating,
				'ratingCount' => $count,
				'bestRating'  => 5,
			);
		}
	} elseif ( is_singular( array( 'post', 'zc_tutorial' ) ) ) {
		$schema = array(
			'@context'         => 'https://schema.org',
			'@type'            => 'Article',
			'headline'         => get_the_title(),
			'datePublished'    => get_the_date( 'c' ),
			'dateModified'     => get_the_modified_date( 'c' ),
			'author'           => array( '@type' => 'Person', 'name' => get_the_author() ),
			'publisher'        => array( '@type' => 'Organization', 'name' => get_bloginfo( 'name' ) ),
			'mainEntityOfPage' => get_permalink(),
			'image'            => get_the_post_thumbnail_url( get_the_ID(), 'full' ),
		);
	}

	if ( ! empty( $schema ) ) {
		printf(
			'<script type="application/ld+json">%s</script>' . "\n",
			wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
		);
	}
}
add_action( 'wp_head', 'zc_schema_markup', 3 );

/**
 * اسکیمای بردکرامب.
 *
 * @return void
 */
function zc_breadcrumb_schema() {
	if ( is_front_page() || ! zc_opt( 'zc_schema_enable', true ) ) {
		return;
	}

	$items = array(
		array(
			'@type'    => 'ListItem',
			'position' => 1,
			'name'     => __( 'خانه', 'zarincode' ),
			'item'     => home_url( '/' ),
		),
	);

	if ( is_singular() ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => 2,
			'name'     => get_the_title(),
			'item'     => get_permalink(),
		);
	}

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode(
			array(
				'@context'        => 'https://schema.org',
				'@type'           => 'BreadcrumbList',
				'itemListElement' => $items,
			),
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		)
	);
}
add_action( 'wp_head', 'zc_breadcrumb_schema', 4 );

/**
 * بهینه‌سازی عنوان آرشیوها.
 *
 * @param string $title عنوان.
 * @return string
 */
function zc_archive_title( $title ) {
	if ( is_category() || is_tag() || is_tax() ) {
		$title = single_term_title( '', false );
	} elseif ( is_post_type_archive() ) {
		$title = post_type_archive_title( '', false );
	} elseif ( is_author() ) {
		$title = get_the_author();
	}
	return $title;
}
add_filter( 'get_the_archive_title', 'zc_archive_title' );
