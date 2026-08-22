<?php
/**
 * یکپارچه‌سازی با ووکامرس
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

if ( ! zc_is_woo() ) {
	return;
}

/**
 * حذف رپرهای پیش‌فرض ووکامرس و افزودن رپر قالب.
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

/* ==========================================================================
   پنل کاربری زرین کد جایگزین «حساب کاربری» ووکامرس
   --------------------------------------------------------------------------
   ووکامرس ساخت صفحه‌ی «حساب کاربری» را الزامی می‌کند، اما ما نمی‌خواهیم
   کاربر به آن مراجعه کند؛ همه‌ی بخش‌ها در پنل اختصاصی زرین کد قرار دارند.
   اینجا صفحه‌ی my-account و تمام اندپوینت‌های آن به تب‌های معادل پنل
   هدایت می‌شوند تا هیچ تداخلی پیش نیاید و کاربر اصلاً به پنل ووکامرس
   نرسد. صفحه‌ی my-account همچنان (برای رضایت ووکامرس) وجود دارد اما
   هیچ بازدیدکننده‌ای به آن نمی‌رسد.
   ========================================================================== */

/**
 * نگاشت اندپوینت‌های حساب کاربری ووکامرس به تب‌های پنل زرین کد.
 *
 * @return string نام تب پنل.
 */
function zc_woo_endpoint_to_panel_tab() {
	if ( ! function_exists( 'WC' ) || ! WC()->query ) {
		return 'dashboard';
	}

	global $wp;
	$current = isset( $wp->query_vars['pagename'] ) ? $wp->query_vars['pagename'] : '';

	// ووکامرس اندپوینت‌ها را در query_vars نگه می‌دارد.
	$endpoints = array(
		'orders'          => 'orders',
		'downloads'       => 'downloads',
		'edit-account'    => 'profile',
		'edit-address'    => 'profile',
		'payment-methods' => 'wallet',
		'customer-logout' => '',
	);

	foreach ( $endpoints as $slug => $tab ) {
		if ( isset( $wp->query_vars[ $slug ] ) ) {
			return $tab;
		}
	}

	return 'dashboard';
}

/**
 * هدایت صفحه‌ی حساب کاربری ووکامرس به پنل زرین کد.
 *
 * @return void
 */
function zc_redirect_woo_account_to_panel() {
	if ( ! is_admin() && function_exists( 'is_account_page' ) && is_account_page() ) {

		// خروج از حساب: به لاگین برمی‌گردیم.
		if ( isset( $_GET['customer-logout'] ) ) { // phpcs:ignore
			wp_safe_redirect( zc_login_url() );
			exit;
		}

		// کاربر وارد نشده → صفحه‌ی ورود اختصاصی قالب.
		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( zc_login_url() );
			exit;
		}

		$tab = zc_woo_endpoint_to_panel_tab();
		wp_safe_redirect( $tab ? zc_panel_url( $tab ) : zc_panel_url() );
		exit;
	}
}
add_action( 'template_redirect', 'zc_redirect_woo_account_to_panel', 20 );

/**
 * حذف لینک‌های «حساب کاربری» ووکامرس از ایمیل‌ها و منوها و جایگزینی با پنل.
 *
 * @param string $url  آدرس.
 * @param object $user کاربر.
 * @return string
 */
function zc_replace_woo_account_urls( $url, $user = null ) {
	if ( is_admin() ) {
		return $url;
	}
	// در ایمیل‌ها ووکامرس برای «حساب من» از این فیلتر استفاده می‌کند.
	return zc_panel_url();
}
add_filter( 'woocommerce_get_endpoint_url', function ( $url, $endpoint, $value, $permalink ) {
	// نگاشت مستقیم اندپوینت به تب پنل.
	$map = array(
		'orders'          => 'orders',
		'downloads'       => 'downloads',
		'edit-account'    => 'profile',
		'edit-address'    => 'profile',
		'payment-methods' => 'wallet',
	);
	if ( isset( $map[ $endpoint ] ) ) {
		return zc_panel_url( $map[ $endpoint ] );
	}
	if ( 'customer-logout' === $endpoint ) {
		return wp_logout_url( home_url() );
	}
	return zc_panel_url();
}, 10, 4 );

/**
 * حذف تب‌های حساب کاربری از نوار ابزار ووکامرس.
 *
 * @return void
 */
function zc_remove_woo_myaccount_nav() {
	remove_action( 'woocommerce_account_navigation', 'woocommerce_account_navigation' );
	remove_action( 'woocommerce_account_content', 'woocommerce_account_content' );
}
add_action( 'init', 'zc_remove_woo_myaccount_nav', 5 );

/**
 * رپر ابتدای محتوا.
 *
 * @return void
 */
function zc_woo_wrapper_start() {
	// سربرگ اختصاصی فروشگاه با آمار و دسته‌بندی‌ها.
	$is_shop   = is_shop() || is_product_category() || is_product_tag();
	$is_flow   = is_cart() || is_checkout() || is_account_page() || is_order_received_page();

	if ( $is_shop ) {
		zc_shop_header();
	} elseif ( $is_flow ) {
		// سبد خرید / تسویه‌ی حساب: کانتینر تمیز و تمام‌عرض بدون سربرگ فروشگاه.
		echo '<div class="zc-container"><div class="zc-woo-flow">';
		return;
	} else {
		zc_page_hero();
	}

	echo '<div class="zc-container"><div class="zc-shop-layout">';

	if ( $is_shop && is_active_sidebar( 'sidebar-shop' ) ) {
		echo '<aside class="zc-sidebar zc-shop-sidebar">';
		dynamic_sidebar( 'sidebar-shop' );
		echo '</aside>';
	}

	echo '<div class="zc-shop-main">';
}

/**
 * سربرگ فروشگاه: عنوان فارسی، آمار و فیلتر دسته‌بندی.
 *
 * @return void
 */
function zc_shop_header() {
	$title = __( 'فروشگاه محصولات دیجیتال', 'zarincode' );
	$sub   = __( 'قالب وردپرس، افزونه، اسکریپت و فونت فارسی — با پشتیبانی و بروزرسانی رایگان', 'zarincode' );

	if ( is_product_category() || is_product_tag() ) {
		$term = get_queried_object();

		if ( $term && ! is_wp_error( $term ) ) {
			$title = $term->name;
			$sub   = $term->description ? $term->description : $sub;
		}
	}

	$total = (int) wp_count_posts( 'product' )->publish;
	$cats  = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'number'     => 8,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);
	?>
	<div class="zc-shop-head">
		<div class="zc-container">
			<?php zc_breadcrumb( true ); ?>

			<div class="zc-shop-head__in">
				<div>
					<h1 class="zc-shop-head__title"><?php echo esc_html( $title ); ?></h1>
					<p class="zc-shop-head__sub"><?php echo esc_html( $sub ); ?></p>
				</div>

				<ul class="zc-shop-head__stats">
					<li>
						<strong><?php echo esc_html( zc_fa_num( number_format( $total ) ) ); ?></strong>
						<span><?php esc_html_e( 'محصول', 'zarincode' ); ?></span>
					</li>
					<li>
						<strong><?php echo esc_html( zc_fa_num( '۱۰۰٪' ) ); ?></strong>
						<span><?php esc_html_e( 'اورجینال', 'zarincode' ); ?></span>
					</li>
					<li>
						<strong><?php esc_html_e( 'رایگان', 'zarincode' ); ?></strong>
						<span><?php esc_html_e( 'بروزرسانی', 'zarincode' ); ?></span>
					</li>
				</ul>
			</div>

			<?php if ( ! is_wp_error( $cats ) && $cats ) : ?>
				<nav class="zc-shop-cats" aria-label="<?php esc_attr_e( 'دسته‌بندی محصولات', 'zarincode' ); ?>">
					<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"
						class="zc-shop-cats__item<?php echo is_shop() ? ' is-active' : ''; ?>">
						<?php esc_html_e( 'همه محصولات', 'zarincode' ); ?>
					</a>

					<?php foreach ( $cats as $cat ) : ?>
						<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"
							class="zc-shop-cats__item<?php echo is_product_category( $cat->term_id ) ? ' is-active' : ''; ?>">
							<?php echo esc_html( $cat->name ); ?>
							<span><?php echo esc_html( zc_fa_num( $cat->count ) ); ?></span>
						</a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_before_main_content', 'zc_woo_wrapper_start', 10 );

/**
 * رپر انتهای محتوا.
 *
 * @return void
 */
function zc_woo_wrapper_end() {
	if ( is_cart() || is_checkout() || is_account_page() || is_order_received_page() ) {
		echo '</div>'; // zc-woo-flow
		echo '</div>'; // zc-container
	} else {
		echo '</div></div></div>'; // main / layout / container
	}
}
add_action( 'woocommerce_after_main_content', 'zc_woo_wrapper_end', 10 );

/**
 * تعداد محصولات در هر صفحه.
 *
 * @return int
 */
function zc_woo_products_per_page() {
	return (int) zc_opt( 'zc_shop_per_page', 12 );
}
add_filter( 'loop_shop_per_page', 'zc_woo_products_per_page', 20 );

/**
 * تعداد ستون‌های فروشگاه.
 *
 * @return int
 */
function zc_woo_loop_columns() {
	return (int) zc_opt( 'zc_shop_columns', 3 );
}
add_filter( 'loop_shop_columns', 'zc_woo_loop_columns', 20 );

/**
 * کارت محصول سفارشی زرین کد.
 *
 * @param array  $settings تنظیمات.
 * @param string $class    کلاس.
 * @param string $anim     ویژگی انیمیشن.
 * @return void
 */
/**
 * آیا محصولی در سبد خرید است؟
 *
 * @param int $product_id شناسه محصول.
 * @return bool
 */
function zc_cart_has_product( $product_id ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return false;
	}
	foreach ( WC()->cart->get_cart() as $item ) {
		if ( (int) $item['product_id'] === (int) $product_id ) {
			return true;
		}
	}
	return false;
}

function zc_product_card( $settings = array(), $class = '', $anim = '' ) {
	global $product;

	if ( ! $product ) {
		$product = wc_get_product( get_the_ID() );
	}
	if ( ! $product ) {
		return;
	}

	$defaults = array(
		'show_cat'    => 'yes',
		'show_rating' => 'yes',
		'show_price'  => 'yes',
		'show_cart'   => 'yes',
		'show_wish'   => 'yes',
		'show_sales'  => '',
	);
	$s   = wp_parse_args( $settings, $defaults );
	$id      = $product->get_id();
	$cats    = get_the_terms( $id, 'product_cat' );
	$wish    = in_array( $id, zc_get_wishlist(), true );
	$preview = get_post_meta( $id, '_zc_preview_url', true );
	$sales   = (int) get_post_meta( $id, 'total_sales', true );
	$rating  = (float) $product->get_average_rating();
	$reviews = (int) $product->get_review_count();
	$version = '';

	// شماره نسخه از مشخصات فنی محصول (اگر ثبت شده باشد).
	$specs = get_post_meta( $id, '_zc_specs', true );

	if ( is_array( $specs ) ) {
		foreach ( $specs as $spec ) {
			if ( isset( $spec['key'] ) && false !== mb_strpos( $spec['key'], 'نسخه' ) ) {
				$version = $spec['value'] ?? '';
				break;
			}
		}
	}
	?>
	<article class="zc-tf-card <?php echo esc_attr( $class ); ?>"<?php echo $anim; // phpcs:ignore ?>>

		<div class="zc-tf-card__media">
			<a class="zc-tf-card__thumb" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
				<?php echo zc_thumbnail( $id, 'zc-card' ); // phpcs:ignore ?>
			</a>

			<div class="zc-tf-card__actions">
				<?php if ( $preview ) : ?>
					<a href="<?php echo esc_url( $preview ); ?>" class="zc-tf-card__act zc-tf-card__act--preview"
						target="_blank" rel="noopener nofollow">
						<?php zc_the_icon( 'eye', 17 ); ?>
						<span><?php esc_html_e( 'پیش‌نمایش زنده', 'zarincode' ); ?></span>
					</a>
				<?php endif; ?>

				<button type="button" class="zc-tf-card__act zc-tf-card__act--quick"
					data-zc-quickview="<?php echo esc_attr( $id ); ?>">
					<?php zc_the_icon( 'search', 17 ); ?>
					<span><?php esc_html_e( 'مشاهده سریع', 'zarincode' ); ?></span>
				</button>
			</div>

			<div class="zc-tf-card__badges">
				<?php if ( $product->is_on_sale() ) : ?>
					<?php
					$regular = (float) $product->get_regular_price();
					$sale    = (float) $product->get_sale_price();
					$percent = $regular > 0 ? round( ( ( $regular - $sale ) / $regular ) * 100 ) : 0;
					?>
					<span class="zc-tf-badge zc-tf-badge--sale">
						<?php echo esc_html( zc_fa_num( $percent ) . '٪ ' . __( 'تخفیف', 'zarincode' ) ); ?>
					</span>
				<?php endif; ?>

				<?php if ( $product->is_featured() ) : ?>
					<span class="zc-tf-badge zc-tf-badge--hot"><?php esc_html_e( 'ویژه', 'zarincode' ); ?></span>
				<?php endif; ?>

				<?php if ( ! $product->is_in_stock() ) : ?>
					<span class="zc-tf-badge zc-tf-badge--out"><?php esc_html_e( 'ناموجود', 'zarincode' ); ?></span>
				<?php endif; ?>
			</div>

			<?php if ( 'yes' === $s['show_wish'] ) : ?>
				<button class="zc-tf-card__wish<?php echo $wish ? ' is-active' : ''; ?>"
					data-zc-wishlist="<?php echo esc_attr( $id ); ?>"
					aria-label="<?php esc_attr_e( 'افزودن به علاقه‌مندی‌ها', 'zarincode' ); ?>">
					<?php zc_the_icon( 'heart', 17 ); ?>
				</button>
			<?php endif; ?>
		</div>

		<div class="zc-tf-card__body">
			<div class="zc-tf-card__top">
				<?php if ( 'yes' === $s['show_cat'] && $cats && ! is_wp_error( $cats ) ) : ?>
					<a href="<?php echo esc_url( get_term_link( $cats[0] ) ); ?>" class="zc-tf-card__cat">
						<?php echo esc_html( $cats[0]->name ); ?>
					</a>
				<?php endif; ?>

				<?php if ( $version ) : ?>
					<span class="zc-tf-card__ver">v<?php echo esc_html( zc_en_num( $version ) ); ?></span>
				<?php endif; ?>
			</div>

			<h3 class="zc-tf-card__title">
				<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			</h3>

			<div class="zc-tf-card__stats">
				<?php if ( 'yes' === $s['show_rating'] ) : ?>
					<span class="zc-tf-card__rating">
						<?php echo zc_stars( $rating ? $rating : 5 ); // phpcs:ignore ?>
						<?php if ( $reviews ) : ?>
							<small>(<?php echo esc_html( zc_fa_num( $reviews ) ); ?>)</small>
						<?php endif; ?>
					</span>
				<?php endif; ?>

				<?php if ( 'yes' === $s['show_sales'] && $sales ) : ?>
					<span class="zc-tf-card__sales">
						<?php zc_the_icon( 'download', 14 ); ?>
						<?php echo esc_html( zc_fa_num( number_format( $sales ) ) ); ?>
					</span>
				<?php endif; ?>
			</div>

			<div class="zc-tf-card__foot">
				<?php if ( 'yes' === $s['show_price'] ) : ?>
					<div class="zc-tf-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
				<?php endif; ?>

				<?php if ( 'yes' === $s['show_cart'] && $product->is_in_stock() ) : ?>
					<?php if ( $product->is_type( 'simple' ) ) : ?>
						<?php if ( zc_cart_has_product( $id ) ) : ?>
							<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="zc-tf-card__cart zc-tf-card__cart--incart"
								data-zc-incart="<?php echo esc_attr( $id ); ?>"
								aria-label="<?php esc_attr_e( 'مشاهده سبد خرید', 'zarincode' ); ?>">
								<?php zc_the_icon( 'cart', 16 ); ?>
								<span><?php esc_html_e( 'مشاهده سبد', 'zarincode' ); ?></span>
							</a>
						<?php else : ?>
							<button class="zc-tf-card__cart" data-zc-addcart="<?php echo esc_attr( $id ); ?>"
								data-cart-url="<?php echo esc_url( wc_get_cart_url() ); ?>"
								aria-label="<?php esc_attr_e( 'افزودن به سبد خرید', 'zarincode' ); ?>">
								<?php zc_the_icon( 'cart', 16 ); ?>
								<span><?php esc_html_e( 'خرید', 'zarincode' ); ?></span>
							</button>
						<?php endif; ?>
					<?php else : ?>
						<a href="<?php the_permalink(); ?>" class="zc-tf-card__cart">
							<?php zc_the_icon( 'cart', 16 ); ?>
							<span><?php esc_html_e( 'مشاهده', 'zarincode' ); ?></span>
						</a>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	</article>
	<?php
}

/**
 * جایگزینی حلقه محصولات با کارت سفارشی.
 */
remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

/**
 * خروجی کارت در حلقه.
 *
 * @return void
 */
function zc_woo_loop_card() {
	zc_product_card( array(), '', ' data-zc-anim="up"' );
}
add_action( 'woocommerce_shop_loop_item_title', 'zc_woo_loop_card', 10 );

/**
 * ساخت کوئری محصولات بر اساس تنظیمات ویجت.
 *
 * @param array $s تنظیمات.
 * @return WP_Query
 */
function zc_wc_products_query( $s ) {
	$args = array(
		'post_type'           => 'product',
		'posts_per_page'      => (int) ( $s['posts_count'] ?? 8 ),
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'tax_query'           => array(), // phpcs:ignore
	);

	$source = $s['source'] ?? 'recent';

	switch ( $source ) {
		case 'featured':
			$args['tax_query'][] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'featured',
			);
			break;

		case 'sale':
			$args['post__in'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
			break;

		case 'best':
			$args['meta_key'] = 'total_sales'; // phpcs:ignore
			$args['orderby']  = 'meta_value_num';
			break;

		case 'top_rated':
			$args['meta_key'] = '_wc_average_rating'; // phpcs:ignore
			$args['orderby']  = 'meta_value_num';
			break;

		case 'manual':
			if ( ! empty( $s['specific_posts'] ) ) {
				$args['post__in'] = array_map( 'intval', (array) $s['specific_posts'] );
				$args['orderby']  = 'post__in';
			}
			break;
	}

	if ( ! empty( $s['query_cats'] ) && 'manual' !== $source ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => array_map( 'intval', (array) $s['query_cats'] ),
		);
	}

	return new WP_Query( $args );
}

/**
 * تب‌های فیلتر دسته محصول.
 *
 * @param array $s تنظیمات.
 * @return void
 */
function zc_render_product_cat_tabs( $s ) {
	$cats = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'number'     => 8,
			'parent'     => 0,
		)
	);

	if ( is_wp_error( $cats ) || empty( $cats ) ) {
		return;
	}

	/*
	 * پیش‌تر از کلاس‌های .zc-panel__filter استفاده می‌شد که تنها در
	 * شیوه‌نامه‌ی پنل کاربری تعریف شده‌اند؛ بنابراین این زبانه‌ها در
	 * صفحه‌ی اصلی و آرشیوها بدون استایل و به شکل متن ساده دیده می‌شدند.
	 */
	echo '<div class="zc-cattabs">';
	echo '<a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '" class="zc-cattabs__item is-active">' . esc_html__( 'همه', 'zarincode' ) . '</a>';

	foreach ( $cats as $cat ) {
		printf(
			'<a href="%s" class="zc-cattabs__item">%s</a>',
			esc_url( get_term_link( $cat ) ),
			esc_html( $cat->name )
		);
	}

	echo '</div>';
}

/**
 * حذف نوار اطلاع‌رسانی پیش‌فرض ووکامرس و استفاده از استایل قالب.
 */
add_filter( 'woocommerce_demo_store', '__return_false' );

/**
 * تغییر متن دکمه افزودن به سبد.
 *
 * @param string $text متن.
 * @return string
 */
function zc_woo_add_to_cart_text( $text ) {
	return __( 'افزودن به سبد خرید', 'zarincode' );
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'zc_woo_add_to_cart_text' );

/**
 * افزودن تب‌های سفارشی به صفحه محصول.
 *
 * @param array $tabs تب‌ها.
 * @return array
 */
function zc_woo_product_tabs( $tabs ) {
	$product_id = get_the_ID();

	// تب راهنمای نصب.
	$guide = get_post_meta( $product_id, '_zc_install_guide', true );
	if ( $guide ) {
		$tabs['zc_guide'] = array(
			'title'    => __( 'راهنمای نصب', 'zarincode' ),
			'priority' => 25,
			'callback' => function () use ( $guide ) {
				echo '<div class="zc-entry__content">' . wp_kses_post( wpautop( $guide ) ) . '</div>';
			},
		);
	}

	// تب مشخصات فنی.
	$specs = get_post_meta( $product_id, '_zc_specs', true );
	if ( is_array( $specs ) && $specs ) {
		$tabs['zc_specs'] = array(
			'title'    => __( 'مشخصات فنی', 'zarincode' ),
			'priority' => 15,
			'callback' => function () use ( $specs ) {
				echo '<table class="zc-table">';
				foreach ( $specs as $spec ) {
					printf(
						'<tr><th style="width:200px">%s</th><td>%s</td></tr>',
						esc_html( $spec['key'] ?? '' ),
						esc_html( $spec['value'] ?? '' )
					);
				}
				echo '</table>';
			},
		);
	}

	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'zc_woo_product_tabs' );

/**
 * ذخیره وضعیت کیف پول در نشست.
 *
 * @return void
 */
function zc_save_wallet_session() {
	if ( ! WC()->session ) {
		return;
	}
	WC()->session->set( 'zc_use_wallet', isset( $_POST['zc_use_wallet'] ) ); // phpcs:ignore
}
add_action( 'woocommerce_checkout_process', 'zc_save_wallet_session' );

/**
 * افزودن فیلد موبایل ایرانی به صورتحساب.
 *
 * @param array $fields فیلدها.
 * @return array
 */
function zc_woo_billing_fields( $fields ) {
	if ( isset( $fields['billing_phone'] ) ) {
		$fields['billing_phone']['placeholder'] = '09xxxxxxxxx';
		$fields['billing_phone']['label']       = __( 'شماره موبایل', 'zarincode' );
		$fields['billing_phone']['priority']    = 25;
	}

	// پر کردن خودکار از پروفایل.
	if ( is_user_logged_in() && empty( $fields['billing_phone']['default'] ) ) {
		$mobile = get_user_meta( get_current_user_id(), 'zc_mobile', true );
		if ( $mobile ) {
			$fields['billing_phone']['default'] = $mobile;
		}
	}

	return $fields;
}
add_filter( 'woocommerce_billing_fields', 'zc_woo_billing_fields' );

/**
 * نمایش تعداد سبد در هدر پس از ای‌جکس.
 *
 * @param array $fragments قطعات.
 * @return array
 */
function zc_woo_cart_fragment( $fragments ) {
	ob_start();
	?>
	<span class="zc-hicon__count" data-zc-cart-count>
		<?php echo esc_html( zc_fa_num( WC()->cart->get_cart_contents_count() ) ); ?>
	</span>
	<?php
	$fragments['[data-zc-cart-count]'] = ob_get_clean();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'zc_woo_cart_fragment' );

/**
 * افزودن متاباکس اتصال محصول به دوره.
 *
 * @return void
 */
function zc_woo_product_meta_box() {
	add_meta_box(
		'zc_product_course',
		__( 'تنظیمات زرین کد', 'zarincode' ),
		'zc_woo_product_meta_box_html',
		'product',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'zc_woo_product_meta_box' );

/**
 * محتوای متاباکس محصول.
 *
 * @param WP_Post $post پست.
 * @return void
 */
function zc_woo_product_meta_box_html( $post ) {
	wp_nonce_field( 'zc_product_meta', 'zc_product_nonce' );

	$course_id = get_post_meta( $post->ID, '_zc_linked_course', true );
	$sub_days  = get_post_meta( $post->ID, '_zc_subscription_days', true );
	$courses   = get_posts( array( 'post_type' => 'zc_course', 'posts_per_page' => 200 ) );
	?>
	<p>
		<label for="zc_linked_course"><strong><?php esc_html_e( 'اتصال به دوره آموزشی', 'zarincode' ); ?></strong></label>
		<select name="zc_linked_course" id="zc_linked_course" style="width:100%;margin-top:6px">
			<option value=""><?php esc_html_e( '— بدون اتصال —', 'zarincode' ); ?></option>
			<?php foreach ( $courses as $course ) : ?>
				<option value="<?php echo esc_attr( $course->ID ); ?>" <?php selected( $course_id, $course->ID ); ?>>
					<?php echo esc_html( $course->post_title ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<span class="description"><?php esc_html_e( 'پس از خرید این محصول، کاربر به دوره انتخابی دسترسی پیدا می‌کند.', 'zarincode' ); ?></span>
	</p>

	<p style="margin-top:14px">
		<label for="zc_subscription_days"><strong><?php esc_html_e( 'مدت اشتراک ویژه (روز)', 'zarincode' ); ?></strong></label>
		<input type="number" name="zc_subscription_days" id="zc_subscription_days" value="<?php echo esc_attr( $sub_days ); ?>" style="width:100%;margin-top:6px" min="0">
		<span class="description"><?php esc_html_e( 'اگر این محصول اشتراک است، تعداد روز دسترسی به همه دوره‌ها را وارد کنید.', 'zarincode' ); ?></span>
	</p>

	<p class="description" style="margin-top:14px;padding-top:12px;border-top:1px solid #e0e0e0">
		<?php esc_html_e( 'لینک پیش‌نمایش، نسخه، سازنده، ویژگی‌ها و تاریخچه تغییرات در باکس «جزئیات کامل محصول» پایین صفحه تنظیم می‌شوند.', 'zarincode' ); ?>
	</p>
	<?php
}

/**
 * ذخیره متاباکس محصول.
 *
 * @param int $post_id شناسه.
 * @return void
 */
function zc_woo_save_product_meta( $post_id ) {
	if ( ! isset( $_POST['zc_product_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['zc_product_nonce'] ) ), 'zc_product_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	update_post_meta( $post_id, '_zc_linked_course', isset( $_POST['zc_linked_course'] ) ? absint( $_POST['zc_linked_course'] ) : '' );
	update_post_meta( $post_id, '_zc_subscription_days', isset( $_POST['zc_subscription_days'] ) ? absint( $_POST['zc_subscription_days'] ) : 0 );
}
add_action( 'save_post_product', 'zc_woo_save_product_meta' );

/* ==========================================================================
   واحد پول ایران و نمایش فارسی قیمت‌ها
   ========================================================================== */

/**
 * افزودن تومان و ریال به فهرست واحدهای پول ووکامرس.
 *
 * @param array $currencies واحدهای پول.
 * @return array
 */
function zc_woo_add_currency( $currencies ) {
	$currencies['IRT'] = __( 'تومان ایران', 'zarincode' );
	$currencies['IRR'] = __( 'ریال ایران', 'zarincode' );

	return $currencies;
}
add_filter( 'woocommerce_currencies', 'zc_woo_add_currency' );

/**
 * نماد واحد پول.
 *
 * @param string $symbol   نماد فعلی.
 * @param string $currency کد واحد پول.
 * @return string
 */
function zc_woo_currency_symbol( $symbol, $currency ) {
	if ( 'IRT' === $currency ) {
		return zc_opt( 'zc_currency_symbol', __( 'تومان', 'zarincode' ) );
	}

	if ( 'IRR' === $currency ) {
		return __( 'ریال', 'zarincode' );
	}

	return $symbol;
}
add_filter( 'woocommerce_currency_symbol', 'zc_woo_currency_symbol', 10, 2 );

/**
 * تبدیل ارقام قیمت به فارسی.
 *
 * فقط زمانی اعمال می‌شود که زبان سایت فارسی باشد تا در سایت‌های
 * چندزبانه، خروجی لاتین دست‌نخورده بماند.
 *
 * @param string $formatted قیمت قالب‌بندی‌شده.
 * @return string
 */
function zc_woo_price_fa_digits( $formatted ) {
	if ( ! zc_opt( 'zc_persian_digits', true ) ) {
		return $formatted;
	}

	// اعداد داخل تگ‌های HTML (مانند مقدار ویژگی‌ها) نباید تغییر کنند.
	return preg_replace_callback(
		'/>([^<]+)</',
		static function ( $m ) {
			return '>' . zc_fa_num( $m[1] ) . '<';
		},
		$formatted
	);
}
add_filter( 'wc_price', 'zc_woo_price_fa_digits', 20 );

/**
 * حذف اعشار برای تومان (قیمت‌های ایرانی اعشار ندارند).
 *
 * @param int $decimals تعداد اعشار.
 * @return int
 */
function zc_woo_price_decimals( $decimals ) {
	return in_array( get_woocommerce_currency(), array( 'IRT', 'IRR' ), true ) ? 0 : $decimals;
}
add_filter( 'wc_get_price_decimals', 'zc_woo_price_decimals' );

/**
 * قرار دادن نماد واحد پول در سمت راست عدد.
 *
 * @param string $format   الگوی فعلی.
 * @param string $position موقعیت.
 * @return string
 */
function zc_woo_price_format( $format, $position ) {
	if ( in_array( get_woocommerce_currency(), array( 'IRT', 'IRR' ), true ) ) {
		return '%2$s&nbsp;%1$s';
	}

	return $format;
}
add_filter( 'woocommerce_price_format', 'zc_woo_price_format', 10, 2 );

/* ==========================================================================
   مشاهده سریع محصول (Quick View)
   ========================================================================== */

/**
 * خروجی آجاکسی جزئیات محصول برای پنجره‌ی مشاهده سریع.
 *
 * @return void
 */
function zc_ajax_quick_view() {
	zc_check_ajax();

	$id      = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
	$product = $id ? wc_get_product( $id ) : null;

	if ( ! $product ) {
		wp_send_json_error( array( 'message' => __( 'محصول یافت نشد.', 'zarincode' ) ) );
	}

	$preview = get_post_meta( $id, '_zc_preview_url', true );
	$author  = get_post_meta( $id, '_zc_product_author', true );
	$updated = get_post_meta( $id, '_zc_last_update', true );
	$specs   = get_post_meta( $id, '_zc_specs', true );
	$gallery = $product->get_gallery_image_ids();

	ob_start();
	?>
	<div class="zc-qv">
		<div class="zc-qv__media">
			<div class="zc-qv__main">
				<?php echo zc_thumbnail( $id, 'zc-card-lg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>

			<?php if ( $gallery ) : ?>
				<div class="zc-qv__thumbs">
					<?php
					$all = array_merge( array( get_post_thumbnail_id( $id ) ), $gallery );

					foreach ( array_slice( array_filter( $all ), 0, 5 ) as $gi => $img_id ) :
						?>
						<button type="button" class="zc-qv__thumb<?php echo 0 === $gi ? ' is-active' : ''; ?>"
							data-full="<?php echo esc_url( wp_get_attachment_image_url( $img_id, 'zc-card-lg' ) ); ?>">
							<?php echo wp_get_attachment_image( $img_id, 'thumbnail' ); ?>
						</button>
						<?php
					endforeach;
					?>
				</div>
			<?php endif; ?>
		</div>

		<div class="zc-qv__body">
			<h3 class="zc-qv__title"><?php echo esc_html( $product->get_name() ); ?></h3>

			<div class="zc-qv__rating">
				<?php echo zc_stars( (float) $product->get_average_rating() ?: 5 ); // phpcs:ignore ?>
				<span><?php echo esc_html( zc_fa_num( (int) $product->get_review_count() ) ); ?> <?php esc_html_e( 'دیدگاه', 'zarincode' ); ?></span>
			</div>

			<div class="zc-qv__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>

			<div class="zc-qv__excerpt"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>

			<ul class="zc-qv__meta">
				<?php if ( $author ) : ?>
					<li><span><?php esc_html_e( 'سازنده', 'zarincode' ); ?></span><strong><?php echo esc_html( $author ); ?></strong></li>
				<?php endif; ?>

				<?php if ( $updated ) : ?>
					<li><span><?php esc_html_e( 'آخرین بروزرسانی', 'zarincode' ); ?></span><strong><?php echo esc_html( zc_fa_num( $updated ) ); ?></strong></li>
				<?php endif; ?>

				<?php
				if ( is_array( $specs ) ) :
					foreach ( array_slice( $specs, 0, 3 ) as $spec ) :
						if ( empty( $spec['key'] ) ) {
							continue;
						}
						?>
						<li>
							<span><?php echo esc_html( $spec['key'] ); ?></span>
							<strong><?php echo esc_html( $spec['value'] ?? '' ); ?></strong>
						</li>
						<?php
					endforeach;
				endif;
				?>
			</ul>

			<div class="zc-qv__actions">
				<?php if ( $product->is_type( 'simple' ) && $product->is_in_stock() ) : ?>
					<button class="zc-btn zc-btn--gold zc-btn--lg" data-zc-addcart="<?php echo esc_attr( $id ); ?>">
						<?php zc_the_icon( 'cart', 18 ); ?>
						<span><?php esc_html_e( 'افزودن به سبد خرید', 'zarincode' ); ?></span>
					</button>
				<?php endif; ?>

				<?php if ( $preview ) : ?>
					<a href="<?php echo esc_url( $preview ); ?>" class="zc-btn zc-btn--outline zc-btn--lg" target="_blank" rel="noopener nofollow">
						<?php zc_the_icon( 'eye', 18 ); ?>
						<?php esc_html_e( 'پیش‌نمایش زنده', 'zarincode' ); ?>
					</a>
				<?php endif; ?>

				<a href="<?php echo esc_url( get_permalink( $id ) ); ?>" class="zc-qv__more">
					<?php esc_html_e( 'مشاهده جزئیات کامل', 'zarincode' ); ?>
					<?php zc_the_icon( 'arrow-left', 16 ); ?>
				</a>
			</div>
		</div>
	</div>
	<?php

	wp_send_json_success( array( 'html' => ob_get_clean() ) );
}
add_action( 'wp_ajax_zc_quick_view', 'zc_ajax_quick_view' );
add_action( 'wp_ajax_nopriv_zc_quick_view', 'zc_ajax_quick_view' );


/**
 * حذف عنوان پیش‌فرض فروشگاه (سربرگ اختصاصی جایگزین آن شده است).
 *
 * @return bool
 */
function zc_woo_hide_page_title() {
	return false;
}
add_filter( 'woocommerce_show_page_title', 'zc_woo_hide_page_title' );

/**
 * حذف عنوان آرشیو تکراری در دسته‌بندی محصولات.
 *
 * @return void
 */
function zc_woo_remove_archive_title() {
	remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
}
add_action( 'template_redirect', 'zc_woo_remove_archive_title' );

/* ==========================================================================
   صفحه‌بندی فروشگاه
   ========================================================================== */

/**
 * جایگزینی فلش‌های متنی صفحه‌بندی ووکامرس با آیکن‌های قالب.
 *
 * قالب پیش‌فرض ووکامرس از موجودیت‌های متنی «&larr;» و «&rarr;»
 * استفاده می‌کند که با بقیه‌ی دکمه‌های سایت هماهنگ نیست و در برخی
 * فونت‌ها بد نمایش داده می‌شود.
 *
 * @param array $args آرگومان‌های paginate_links.
 * @return array
 */
function zc_woo_pagination_args( $args ) {
	$args['prev_text'] = zc_icon( 'arrow-right', 18 )
		. '<span class="screen-reader-text">' . esc_html__( 'صفحه قبلی', 'zarincode' ) . '</span>';

	$args['next_text'] = zc_icon( 'arrow-left', 18 )
		. '<span class="screen-reader-text">' . esc_html__( 'صفحه بعدی', 'zarincode' ) . '</span>';

	$args['mid_size'] = 1;
	$args['end_size'] = 1;

	return $args;
}
add_filter( 'woocommerce_pagination_args', 'zc_woo_pagination_args' );

/* ==========================================================================
   فارسی‌سازی رشته‌های ووکامرس
   ========================================================================== */

/**
 * فارسی‌کردن شمارنده نتایج آرشیو فروشگاه.
 *
 * ووکامرس بدون نصب بسته زبان فارسی رشته‌ی انگلیسی
 * «Showing 1–12 of 50 results» را چاپ می‌کند.
 *
 * @return void
 */
function zc_woo_result_count() {
	global $wp_query;

	if ( ! wc_get_loop_prop( 'is_paginated' ) || ! woocommerce_products_will_display() ) {
		return;
	}

	$total   = (int) wc_get_loop_prop( 'total' );
	$per     = (int) wc_get_loop_prop( 'per_page' );
	$current = (int) wc_get_loop_prop( 'current_page' );

	if ( $total < 1 ) {
		return;
	}

	$first = ( $per * $current ) - $per + 1;
	$last  = min( $total, $per * $current );

	echo '<p class="woocommerce-result-count zc-result-count">';

	if ( 1 === $total ) {
		esc_html_e( 'نمایش تنها نتیجه', 'zarincode' );
	} elseif ( $total <= $per || -1 === $per ) {
		printf(
			/* translators: %s: تعداد کل */
			esc_html__( 'نمایش همه‌ی %s نتیجه', 'zarincode' ),
			esc_html( zc_fa_num( $total ) )
		);
	} else {
		printf(
			/* translators: 1: از 2: تا 3: کل */
			esc_html__( 'نمایش %1$s تا %2$s از %3$s محصول', 'zarincode' ),
			esc_html( zc_fa_num( $first ) ),
			esc_html( zc_fa_num( $last ) ),
			esc_html( zc_fa_num( $total ) )
		);
	}

	echo '</p>';
}

/**
 * جایگزینی شمارنده پیش‌فرض با نسخه فارسی.
 *
 * @return void
 */
function zc_woo_swap_result_count() {
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	add_action( 'woocommerce_before_shop_loop', 'zc_woo_result_count', 20 );
}
add_action( 'wp', 'zc_woo_swap_result_count' );

/**
 * فارسی‌کردن گزینه‌های مرتب‌سازی فروشگاه.
 *
 * @param array $options گزینه‌ها.
 * @return array
 */
function zc_woo_sorting_options( $options ) {
	$fa = array(
		'menu_order' => __( 'مرتب‌سازی پیش‌فرض', 'zarincode' ),
		'popularity' => __( 'پرفروش‌ترین', 'zarincode' ),
		'rating'     => __( 'بیشترین امتیاز', 'zarincode' ),
		'date'       => __( 'جدیدترین', 'zarincode' ),
		'price'      => __( 'ارزان‌ترین', 'zarincode' ),
		'price-desc' => __( 'گران‌ترین', 'zarincode' ),
	);

	foreach ( $fa as $key => $label ) {
		if ( isset( $options[ $key ] ) ) {
			$options[ $key ] = $label;
		}
	}

	return $options;
}
add_filter( 'woocommerce_catalog_orderby', 'zc_woo_sorting_options' );
add_filter( 'woocommerce_default_catalog_orderby_options', 'zc_woo_sorting_options' );
