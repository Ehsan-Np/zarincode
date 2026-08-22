<?php
/**
 * ویجت نمونه‌کارها — پروژه‌های انجام‌شده با فیلتر دسته‌بندی
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * ویجت نمونه‌کار.
 */
class ZC_Widget_portfolio extends ZC_Widget_Base {

	/**
	 * نام ویجت.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_portfolio';
	}

	/**
	 * عنوان ویجت.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | نمونه‌کارها', 'zarincode' );
	}

	/**
	 * آیکن ویجت.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	/**
	 * ثبت کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {

		$this->add_heading_controls(
			__( 'نمونه‌کارهای <span>ما</span>', 'zarincode' ),
			__( 'پروژه‌هایی که با افتخار تحویل داده‌ایم', 'zarincode' )
		);

		$this->start_controls_section(
			'query_section',
			array( 'label' => __( 'محتوا', 'zarincode' ) )
		);

		$this->add_control(
			'count',
			array(
				'label'   => __( 'تعداد نمایش', 'zarincode' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 6,
				'min'     => 1,
				'max'     => 30,
			)
		);

		$this->add_control(
			'show_filter',
			array(
				'label'        => __( 'نمایش فیلتر دسته‌بندی', 'zarincode' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_tech',
			array(
				'label'        => __( 'نمایش تکنولوژی‌ها', 'zarincode' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->add_layout_controls( 3 );
	}

	/**
	 * رندر خروجی.
	 *
	 * @return void
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		$query = new WP_Query(
			array(
				'post_type'      => 'zc_project',
				'posts_per_page' => (int) $s['count'],
				'no_found_rows'  => true,
			)
		);

		if ( ! $query->have_posts() ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="zc-empty"><p>' . esc_html__( 'هنوز نمونه‌کاری ثبت نشده است.', 'zarincode' ) . '</p></div>';
			}

			return;
		}

		$cols  = (int) ( $s['columns'] ?? 3 );
		$terms = get_terms(
			array(
				'taxonomy'   => 'zc_project_cat',
				'hide_empty' => true,
			)
		);
		$uid   = 'zc-pf-' . $this->get_id();
		?>
		<section class="zc-section zc-portfolio-sec">
			<div class="zc-container">
				<?php $this->render_heading( $s ); ?>

				<?php if ( 'yes' === $s['show_filter'] && ! is_wp_error( $terms ) && count( $terms ) > 1 ) : ?>
					<div class="zc-pf-filter" data-zc-filter="<?php echo esc_attr( $uid ); ?>">
						<button type="button" class="zc-pf-filter__btn is-active" data-filter="*">
							<?php esc_html_e( 'همه', 'zarincode' ); ?>
						</button>
						<?php foreach ( $terms as $term ) : ?>
							<button type="button" class="zc-pf-filter__btn" data-filter="<?php echo esc_attr( $term->slug ); ?>">
								<?php echo esc_html( $term->name ); ?>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="zc-grid zc-grid--<?php echo esc_attr( $cols ); ?>" id="<?php echo esc_attr( $uid ); ?>">
					<?php
					$i = 0;

					while ( $query->have_posts() ) :
						$query->the_post();

						$id     = get_the_ID();
						$client = get_post_meta( $id, '_zc_project_client', true );
						$url    = get_post_meta( $id, '_zc_project_url', true );
						$cats   = wp_get_post_terms( $id, 'zc_project_cat', array( 'fields' => 'all' ) );
						$techs  = wp_get_post_terms( $id, 'zc_project_tech', array( 'fields' => 'names' ) );
						$slugs  = ( ! is_wp_error( $cats ) && $cats ) ? wp_list_pluck( $cats, 'slug' ) : array();
						?>
						<article class="zc-pf-card" data-cats="<?php echo esc_attr( implode( ' ', $slugs ) ); ?>"
							data-zc-anim="up" data-zc-delay="<?php echo (int) ( $i * 70 ); ?>">

							<div class="zc-pf-card__media">
								<?php echo zc_thumbnail( $id, 'zc-card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

								<div class="zc-pf-card__overlay">
									<a href="<?php the_permalink(); ?>" class="zc-pf-card__btn" aria-label="<?php esc_attr_e( 'مشاهده جزئیات', 'zarincode' ); ?>">
										<?php zc_the_icon( 'search', 20 ); ?>
									</a>

									<?php if ( $url ) : ?>
										<a href="<?php echo esc_url( $url ); ?>" class="zc-pf-card__btn" target="_blank" rel="noopener nofollow" aria-label="<?php esc_attr_e( 'مشاهده سایت', 'zarincode' ); ?>">
											<?php zc_the_icon( 'arrow-ul', 20 ); ?>
										</a>
									<?php endif; ?>
								</div>

								<?php if ( ! is_wp_error( $cats ) && $cats ) : ?>
									<span class="zc-pf-card__cat"><?php echo esc_html( $cats[0]->name ); ?></span>
								<?php endif; ?>
							</div>

							<div class="zc-pf-card__body">
								<h3 class="zc-pf-card__title">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h3>

								<?php if ( $client ) : ?>
									<p class="zc-pf-card__client">
										<?php zc_the_icon( 'user', 14 ); ?>
										<?php echo esc_html( $client ); ?>
									</p>
								<?php endif; ?>

								<?php if ( 'yes' === $s['show_tech'] && ! is_wp_error( $techs ) && $techs ) : ?>
									<div class="zc-pf-card__tech">
										<?php foreach ( array_slice( $techs, 0, 4 ) as $tech ) : ?>
											<span><?php echo esc_html( $tech ); ?></span>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						</article>
						<?php
						$i++;
					endwhile;
					?>
				</div>
			</div>
		</section>
		<?php
		wp_reset_postdata();
	}
}
