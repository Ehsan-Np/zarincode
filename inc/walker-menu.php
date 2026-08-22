<?php
/**
 * واکر منوی سفارشی با پشتیبانی مگا منو
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * کلاس واکر منو.
 */
class ZC_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * شروع سطح (زیرمنو).
	 *
	 * @param string   $output خروجی.
	 * @param int      $depth  عمق.
	 * @param stdClass $args   آرگومان.
	 * @return void
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$indent  = str_repeat( "\t", $depth );
		$output .= "\n{$indent}<ul class=\"sub-menu\">\n";
	}

	/**
	 * شروع المان.
	 *
	 * @param string   $output خروجی.
	 * @param WP_Post  $item   آیتم.
	 * @param int      $depth  عمق.
	 * @param stdClass $args   آرگومان.
	 * @param int      $id     شناسه.
	 * @return void
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes   = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		$is_mega = 0 === $depth && in_array( 'zc-mega', $classes, true );
		if ( $is_mega ) {
			$classes[] = 'zc-has-mega';
		}

		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$output     .= '<li class="' . esc_attr( $class_names ) . '">';

		$atts          = array();
		$atts['title'] = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target']= ! empty( $item->target ) ? $item->target : '';
		$atts['rel']   = ! empty( $item->xfn ) ? $item->xfn : '';
		$atts['href']  = ! empty( $item->url ) ? $item->url : '';

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$attributes .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
			}
		}

		$title = apply_filters( 'the_title', $item->title, $item->ID );

		// آیکن سفارشی از فیلد attr_title (مثال: icon:code).
		$icon = '';
		if ( ! empty( $item->attr_title ) && 0 === strpos( $item->attr_title, 'icon:' ) ) {
			$icon_name = substr( $item->attr_title, 5 );
			$icon      = zc_icon( $icon_name, 17 );
		}

		$arrow = '';
		if ( in_array( 'menu-item-has-children', $classes, true ) || $is_mega ) {
			$arrow = zc_icon( 'chevron', 14, 'zc-nav__arrow' );
		}

		$output .= '<a' . $attributes . '>' . $icon . '<span>' . esc_html( $title ) . '</span>' . $arrow . '</a>';

		// رندر مگا منو.
		if ( $is_mega ) {
			$output .= $this->render_mega_menu( $item );
		}
	}

	/**
	 * رندر محتوای مگا منو.
	 *
	 * @param WP_Post $item آیتم.
	 * @return string
	 */
	private function render_mega_menu( $item ) {
		ob_start();
		?>
		<div class="zc-megamenu">
			<div class="zc-megamenu__grid">
				<?php
				// دسته‌بندی دوره‌ها.
				$cats = get_terms(
					array(
						'taxonomy'   => 'zc_course_cat',
						'hide_empty' => false,
						'number'     => 6,
						'parent'     => 0,
					)
				);

				if ( ! is_wp_error( $cats ) && $cats ) :
					?>
					<div>
						<h4 class="zc-megamenu__col-title"><?php zc_the_icon( 'book', 17 ); ?><?php esc_html_e( 'دسته‌بندی دوره‌ها', 'zarincode' ); ?></h4>
						<ul class="zc-megamenu__links">
							<?php foreach ( $cats as $cat ) : ?>
								<li><a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
					<?php
				endif;

				// دسته محصولات.
				if ( zc_is_woo() ) :
					$pcats = get_terms(
						array(
							'taxonomy'   => 'product_cat',
							'hide_empty' => false,
							'number'     => 6,
							'parent'     => 0,
						)
					);
					if ( ! is_wp_error( $pcats ) && $pcats ) :
						?>
						<div>
							<h4 class="zc-megamenu__col-title"><?php zc_the_icon( 'cart', 17 ); ?><?php esc_html_e( 'فروشگاه', 'zarincode' ); ?></h4>
							<ul class="zc-megamenu__links">
								<?php foreach ( $pcats as $pcat ) : ?>
									<li><a href="<?php echo esc_url( get_term_link( $pcat ) ); ?>"><?php echo esc_html( $pcat->name ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						</div>
						<?php
					endif;
				endif;
				?>

				<div>
					<h4 class="zc-megamenu__col-title"><?php zc_the_icon( 'sparkle', 17 ); ?><?php esc_html_e( 'محبوب‌ترین دوره‌ها', 'zarincode' ); ?></h4>
					<ul class="zc-megamenu__links">
						<?php
						$popular = new WP_Query(
							array(
								'post_type'      => 'zc_course',
								'posts_per_page' => 5,
								'meta_key'       => '_zc_students', // phpcs:ignore
								'orderby'        => 'meta_value_num',
							)
						);
						while ( $popular->have_posts() ) :
							$popular->the_post();
							?>
							<li><a href="<?php the_permalink(); ?>"><?php echo esc_html( zc_excerpt( get_the_title(), 5 ) ); ?></a></li>
							<?php
						endwhile;
						wp_reset_postdata();
						?>
					</ul>
				</div>

				<div class="zc-megamenu__promo">
					<h4><?php echo esc_html( zc_opt( 'zc_mega_promo_title', 'مشاوره رایگان انتخاب مسیر' ) ); ?></h4>
					<p><?php echo esc_html( zc_opt( 'zc_mega_promo_text', 'نمی‌دانید از کجا شروع کنید؟ کارشناسان ما راهنمای شما هستند.' ) ); ?></p>
					<a href="<?php echo esc_url( zc_opt( 'zc_header_cta_link', '#' ) ); ?>" class="zc-btn zc-btn--gold zc-btn--sm">
						<?php zc_the_icon( 'headphone', 16 ); ?>
						<?php esc_html_e( 'درخواست مشاوره', 'zarincode' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
