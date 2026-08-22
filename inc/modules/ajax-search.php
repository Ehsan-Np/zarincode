<?php
/**
 * جستجوی زنده ای‌جکس
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * هندلر جستجوی ای‌جکس.
 *
 * @return void
 */
function zc_handle_ajax_search() {
	zc_check_ajax();

	$query = isset( $_POST['q'] ) ? sanitize_text_field( wp_unslash( $_POST['q'] ) ) : '';
	$type  = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : 'all';

	if ( mb_strlen( $query ) < 2 ) {
		wp_send_json_error( array( 'message' => __( 'حداقل ۲ حرف وارد کنید.', 'zarincode' ) ) );
	}

	// کش نتایج برای سرعت بالا.
	$cache_key = 'zc_search_' . md5( $query . $type );
	$cached    = get_transient( $cache_key );

	if ( false !== $cached && ! zc_opt( 'zc_disable_cache', false ) ) {
		wp_send_json_success( array( 'html' => $cached, 'cached' => true ) );
	}

	$types = array( 'post', 'page', 'zc_course', 'zc_tutorial' );
	if ( zc_is_woo() ) {
		$types[] = 'product';
	}

	if ( 'all' !== $type ) {
		$types = array( $type );
	}

	$results = new WP_Query(
		array(
			's'                   => $query,
			'post_type'           => $types,
			'posts_per_page'      => (int) zc_opt( 'zc_search_results_count', 8 ),
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	ob_start();

	if ( $results->have_posts() ) {
		$labels = array(
			'post'        => __( 'مقاله', 'zarincode' ),
			'page'        => __( 'صفحه', 'zarincode' ),
			'zc_course'   => __( 'دوره آموزشی', 'zarincode' ),
			'zc_tutorial' => __( 'آموزش', 'zarincode' ),
			'product'     => __( 'محصول', 'zarincode' ),
		);

		while ( $results->have_posts() ) {
			$results->the_post();
			$ptype = get_post_type();
			$price = '';

			if ( 'product' === $ptype && function_exists( 'wc_get_product' ) ) {
				$product = wc_get_product( get_the_ID() );
				$price   = $product ? $product->get_price_html() : '';
			} elseif ( 'zc_course' === $ptype ) {
				$p     = (float) get_post_meta( get_the_ID(), '_zc_price', true );
				$price = $p > 0 ? zc_fa_num( number_format( $p ) ) . ' ' . zc_opt( 'zc_currency_symbol', 'تومان' ) : __( 'رایگان', 'zarincode' );
			}
			?>
			<a href="<?php the_permalink(); ?>" class="zc-search__item">
				<span class="zc-search__thumb"><?php echo zc_thumbnail( get_the_ID(), 'thumbnail' ); // phpcs:ignore ?></span>
				<span class="zc-search__info">
					<span class="zc-search__title"><?php the_title(); ?></span>
					<span class="zc-search__type">
						<?php echo esc_html( $labels[ $ptype ] ?? $ptype ); ?>
						<?php if ( $price ) : ?>
							· <strong style="color:var(--zc-gold-3)"><?php echo wp_kses_post( $price ); ?></strong>
						<?php endif; ?>
					</span>
				</span>
				<span style="color:var(--zc-muted)"><?php zc_the_icon( 'arrow-left', 18 ); ?></span>
			</a>
			<?php
		}

		printf(
			'<div style="padding:12px;text-align:center"><a href="%s" class="zc-btn zc-btn--ghost zc-btn--sm">%s</a></div>',
			esc_url( home_url( '/?s=' . rawurlencode( $query ) ) ),
			esc_html__( 'مشاهده همه نتایج', 'zarincode' )
		);
	} else {
		printf(
			'<div class="zc-search__state">%s<p style="margin-top:10px">%s</p></div>',
			zc_icon( 'search', 40, 'zc-empty__icon' ), // phpcs:ignore
			esc_html__( 'نتیجه‌ای یافت نشد. عبارت دیگری را امتحان کنید.', 'zarincode' )
		);
	}

	wp_reset_postdata();
	$html = ob_get_clean();

	set_transient( $cache_key, $html, 10 * MINUTE_IN_SECONDS );

	wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_zc_ajax_search', 'zc_handle_ajax_search' );
add_action( 'wp_ajax_nopriv_zc_ajax_search', 'zc_handle_ajax_search' );

/**
 * بارگذاری بیشتر پست‌ها.
 *
 * @return void
 */
function zc_handle_load_more() {
	zc_check_ajax();

	$page  = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
	$query = isset( $_POST['query'] ) ? json_decode( sanitize_textarea_field( wp_unslash( $_POST['query'] ) ), true ) : array();
	$tpl   = isset( $_POST['tpl'] ) ? sanitize_key( wp_unslash( $_POST['tpl'] ) ) : 'post';

	$args = wp_parse_args(
		is_array( $query ) ? $query : array(),
		array(
			'post_type'      => 'post',
			'posts_per_page' => 9,
			'post_status'    => 'publish',
		)
	);

	$args['paged'] = $page;

	$loop = new WP_Query( $args );

	if ( ! $loop->have_posts() ) {
		wp_send_json_error( array( 'message' => __( 'موردی یافت نشد.', 'zarincode' ) ) );
	}

	ob_start();
	while ( $loop->have_posts() ) {
		$loop->the_post();
		get_template_part( 'template-parts/content/card', $tpl );
	}
	wp_reset_postdata();

	wp_send_json_success(
		array(
			'html'     => ob_get_clean(),
			'max_page' => $loop->max_num_pages,
		)
	);
}
add_action( 'wp_ajax_zc_load_more', 'zc_handle_load_more' );
add_action( 'wp_ajax_nopriv_zc_load_more', 'zc_handle_load_more' );
