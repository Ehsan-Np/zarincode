<?php
/**
 * ویجت خدمات — نمایش خدمات آژانس (طراحی، سئو، پروژه و ...)
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * ویجت خدمات.
 */
class ZC_Widget_services extends ZC_Widget_Base {

	/**
	 * نام ویجت.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_services';
	}

	/**
	 * عنوان ویجت.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | خدمات ما', 'zarincode' );
	}

	/**
	 * آیکن ویجت.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-price-table';
	}

	/**
	 * ثبت کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {

		$this->add_heading_controls(
			__( 'خدمات <span>تخصصی</span> ما', 'zarincode' ),
			__( 'از ایده تا اجرا؛ تیم زرین کد در کنار شماست', 'zarincode' )
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
				'max'     => 24,
			)
		);

		$this->add_control(
			'style',
			array(
				'label'   => __( 'سبک نمایش', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'card',
				'options' => array(
					'card'  => __( 'کارت آیکن‌دار', 'zarincode' ),
					'image' => __( 'کارت تصویری', 'zarincode' ),
				),
			)
		);

		$this->add_control(
			'show_price',
			array(
				'label'        => __( 'نمایش شروع قیمت', 'zarincode' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_features',
			array(
				'label'        => __( 'نمایش موارد شامل خدمت', 'zarincode' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'btn_text',
			array(
				'label'   => __( 'متن دکمه', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'مشاهده و سفارش', 'zarincode' ),
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
				'post_type'      => 'zc_service',
				'posts_per_page' => (int) $s['count'],
				'orderby'        => 'menu_order title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);

		if ( ! $query->have_posts() ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="zc-empty"><p>' . esc_html__( 'هنوز خدمتی ثبت نشده است. از منوی «خدمات» موارد را اضافه کنید.', 'zarincode' ) . '</p></div>';
			}

			return;
		}

		$cols = (int) ( $s['columns'] ?? 3 );
		?>
		<section class="zc-section zc-services-sec">
			<div class="zc-container">
				<?php $this->render_heading( $s ); ?>

				<div class="zc-grid zc-grid--<?php echo esc_attr( $cols ); ?>">
					<?php
					$i = 0;

					while ( $query->have_posts() ) :
						$query->the_post();

						$id       = get_the_ID();
						$icon     = get_post_meta( $id, '_zc_service_icon', true );
						$icon     = $icon ? $icon : 'code';
						$from     = (float) get_post_meta( $id, '_zc_service_price_from', true );
						$duration = get_post_meta( $id, '_zc_service_duration', true );
						$color    = get_post_meta( $id, '_zc_service_color', true );
						$features = get_post_meta( $id, '_zc_features', true );
						$features = is_array( $features ) ? array_slice( $features, 0, 4 ) : array();
						?>
						<article class="zc-service-card zc-service-card--<?php echo esc_attr( $s['style'] ); ?>"
							data-zc-anim="up" data-zc-delay="<?php echo (int) ( $i * 70 ); ?>"
							<?php echo $color ? 'style="--zc-svc:' . esc_attr( $color ) . '"' : ''; ?>>

							<?php if ( 'image' === $s['style'] && has_post_thumbnail() ) : ?>
								<a class="zc-service-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
									<?php echo zc_thumbnail( $id, 'zc-card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</a>
							<?php endif; ?>

							<div class="zc-service-card__body">
								<span class="zc-service-card__icon"><?php zc_the_icon( $icon, 26 ); ?></span>

								<h3 class="zc-service-card__title">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h3>

								<p class="zc-service-card__desc"><?php echo esc_html( zc_excerpt( get_the_excerpt(), 18 ) ); ?></p>

								<?php if ( 'yes' === $s['show_features'] && $features ) : ?>
									<ul class="zc-service-card__list">
										<?php foreach ( $features as $feature ) : ?>
											<li><?php zc_the_icon( 'check', 15 ); ?><?php echo esc_html( $feature ); ?></li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>

								<div class="zc-service-card__foot">
									<?php if ( 'yes' === $s['show_price'] && $from ) : ?>
										<span class="zc-service-card__price">
											<small><?php esc_html_e( 'شروع از', 'zarincode' ); ?></small>
											<strong><?php echo esc_html( zc_price_text( $from ) ); ?></strong>
										</span>
									<?php elseif ( $duration ) : ?>
										<span class="zc-service-card__price">
											<small><?php esc_html_e( 'زمان تحویل', 'zarincode' ); ?></small>
											<strong><?php echo esc_html( zc_fa_num( $duration ) ); ?></strong>
										</span>
									<?php endif; ?>

									<a href="<?php the_permalink(); ?>" class="zc-btn zc-btn--gold zc-btn--sm">
										<?php echo esc_html( $s['btn_text'] ); ?>
										<?php zc_the_icon( 'arrow-left', 15 ); ?>
									</a>
								</div>
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
