<?php
/**
 * تب علاقه‌مندی‌ها
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_ids = zc_get_wishlist();
?>

<?php if ( $zc_ids ) : ?>
	<div class="zc-grid zc-grid--3">
		<?php
		$zc_q = new WP_Query(
			array(
				'post__in'       => $zc_ids,
				'post_type'      => array( 'product', 'zc_course', 'post', 'zc_tutorial' ),
				'posts_per_page' => 30,
				'orderby'        => 'post__in',
			)
		);
		$zc_i = 0;
		while ( $zc_q->have_posts() ) :
			$zc_q->the_post();
			?>
			<article class="zc-card" data-zc-anim="up" data-zc-delay="<?php echo (int) ( $zc_i * 60 ); ?>">
				<div class="zc-card__media">
					<a href="<?php the_permalink(); ?>"><?php echo zc_thumbnail( get_the_ID() ); // phpcs:ignore ?></a>
					<button class="zc-hicon zc-badge--float is-active" data-zc-wishlist="<?php the_ID(); ?>"
						style="background:#fff;color:var(--zc-danger);width:36px;height:36px;border-radius:50%"
						aria-label="<?php esc_attr_e( 'حذف از علاقه‌مندی‌ها', 'zarincode' ); ?>">
						<?php zc_the_icon( 'heart', 18 ); ?>
					</button>
				</div>
				<div class="zc-card__body">
					<h3 class="zc-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
					<div class="zc-card__footer">
						<?php
						if ( 'product' === get_post_type() && function_exists( 'wc_get_product' ) ) {
							$zc_p = wc_get_product( get_the_ID() );
							echo '<span class="zc-price__now">' . wp_kses_post( $zc_p->get_price_html() ) . '</span>';
							printf(
								'<button class="zc-btn zc-btn--gold zc-btn--sm" data-zc-addcart="%d">' . zc_icon( 'cart', 15 ) . '%s</button>',
								(int) get_the_ID(),
								esc_html__( 'افزودن به سبد', 'zarincode' )
							);
						} else {
							printf( '<a href="%s" class="zc-btn zc-btn--gold zc-btn--sm">%s%s</a>', esc_url( get_permalink() ), zc_icon( 'eye', 15 ), esc_html__( 'مشاهده', 'zarincode' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						?>
					</div>
				</div>
			</article>
			<?php
			$zc_i++;
		endwhile;
		wp_reset_postdata();
		?>
	</div>
<?php else : ?>
	<div class="zc-empty">
		<div class="zc-empty__icon"><?php zc_the_icon( 'heart', 40 ); ?></div>
		<h3><?php esc_html_e( 'لیست علاقه‌مندی‌های شما خالی است', 'zarincode' ); ?></h3>
		<p><?php esc_html_e( 'با کلیک روی آیکن قلب، دوره‌ها و محصولات مورد علاقه خود را ذخیره کنید.', 'zarincode' ); ?></p>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'zc_course' ) ); ?>" class="zc-btn zc-btn--gold"><?php zc_the_icon( 'video', 17 ); ?><?php esc_html_e( 'مشاهده دوره‌ها', 'zarincode' ); ?></a>
	</div>
<?php endif; ?>
