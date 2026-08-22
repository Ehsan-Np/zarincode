<?php
/**
 * ویجت نمایش آموزش‌ها
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * ویجت آموزش‌ها.
 */
class ZC_Widget_tutorials extends ZC_Widget_Base {

	/**
	 * نام.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_tutorials';
	}

	/**
	 * عنوان.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | آموزش‌های رایگان', 'zarincode' );
	}

	/**
	 * آیکن.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-document-file';
	}

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->add_heading_controls(
			__( 'آموزش‌های <span>رایگان</span>', 'zarincode' ),
			__( 'مقالات و آموزش‌های کاربردی برای شروع و ارتقای مهارت برنامه‌نویسی', 'zarincode' )
		);

		$this->add_query_controls( 'zc_tutorial', 'zc_tutorial_cat' );
		$this->add_layout_controls( 3 );

		$this->start_controls_section(
			'card_section',
			array( 'label' => __( 'اجزای کارت', 'zarincode' ) )
		);

		$this->add_control(
			'show_excerpt',
			array(
				'label'   => __( 'نمایش خلاصه', 'zarincode' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_difficulty',
			array(
				'label'   => __( 'نمایش سطح', 'zarincode' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_reading',
			array(
				'label'   => __( 'نمایش زمان مطالعه', 'zarincode' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->add_more_button_controls();
	}

	/**
	 * رندر.
	 *
	 * @return void
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$query = $this->build_query( $s, 'zc_tutorial', 'zc_tutorial_cat' );

		$this->render_heading( $s );

		if ( ! $query->have_posts() ) {
			return;
		}

		$this->open_wrapper( $s );

		$levels = array(
			'beginner'     => array( __( 'مقدماتی', 'zarincode' ), 'green' ),
			'intermediate' => array( __( 'متوسط', 'zarincode' ), 'orange' ),
			'advanced'     => array( __( 'پیشرفته', 'zarincode' ), 'red' ),
		);

		$i = 0;
		while ( $query->have_posts() ) {
			$query->the_post();
			$level = get_post_meta( get_the_ID(), '_zc_level', true );
			$cats  = get_the_terms( get_the_ID(), 'zc_tutorial_cat' );
			?>
			<article class="zc-card <?php echo esc_attr( $this->item_class( $s ) ); ?>"<?php echo $this->anim_attr( $s, $i ); // phpcs:ignore ?>>
				<div class="zc-card__media">
					<a href="<?php the_permalink(); ?>"><?php echo zc_thumbnail( get_the_ID() ); // phpcs:ignore ?></a>
					<?php if ( 'yes' === $s['show_difficulty'] && isset( $levels[ $level ] ) ) : ?>
						<span class="zc-badge zc-badge--<?php echo esc_attr( $levels[ $level ][1] ); ?> zc-badge--float">
							<?php echo esc_html( $levels[ $level ][0] ); ?>
						</span>
					<?php endif; ?>
				</div>
				<div class="zc-card__body">
					<?php if ( $cats && ! is_wp_error( $cats ) ) : ?>
						<span class="zc-badge zc-badge--gold" style="align-self:flex-start"><?php echo esc_html( $cats[0]->name ); ?></span>
					<?php endif; ?>
					<h3 class="zc-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
					<?php if ( 'yes' === $s['show_excerpt'] ) : ?>
						<p class="zc-card__excerpt"><?php echo esc_html( zc_excerpt( get_the_excerpt(), 16 ) ); ?></p>
					<?php endif; ?>
					<div class="zc-card__meta">
						<?php if ( 'yes' === $s['show_reading'] ) : ?>
							<span><?php zc_the_icon( 'clock', 15 ); ?><?php echo esc_html( zc_fa_num( zc_reading_time() ) . ' ' . __( 'دقیقه', 'zarincode' ) ); ?></span>
						<?php endif; ?>
						<span><?php zc_the_icon( 'eye', 15 ); ?><?php echo esc_html( zc_fa_num( zc_get_views( get_the_ID() ) ) ); ?></span>
					</div>
				</div>
			</article>
			<?php
			$i++;
		}
		wp_reset_postdata();

		$this->close_wrapper( $s );
		$this->render_more_button( $s );
	}
}
