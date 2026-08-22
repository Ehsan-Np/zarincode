<?php
/**
 * ویجت نمایش نوشته‌های بلاگ
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

/**
 * ویجت نوشته‌ها.
 */
class ZC_Widget_posts extends ZC_Widget_Base {

	/**
	 * نام.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_posts';
	}

	/**
	 * عنوان.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | مقالات بلاگ', 'zarincode' );
	}

	/**
	 * آیکن.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-posts-grid';
	}

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {

		$this->add_heading_controls(
			__( 'مجله <span>زرین کد</span>', 'zarincode' ),
			__( 'آخرین مقالات، اخبار تکنولوژی و نکات کاربردی دنیای برنامه‌نویسی', 'zarincode' )
		);

		$this->add_query_controls( 'post', 'category' );
		$this->add_layout_controls( 4 );

		$this->start_controls_section(
			'card_section',
			array( 'label' => __( 'اجزای کارت', 'zarincode' ) )
		);

		foreach ( array(
			'show_fab'     => array( __( 'دکمه فلش گوشه کارت', 'zarincode' ), 'yes' ),
			'show_cat'     => array( __( 'دسته‌بندی', 'zarincode' ), 'yes' ),
			'show_date'    => array( __( 'تاریخ', 'zarincode' ), 'yes' ),
			'show_author'  => array( __( 'نویسنده', 'zarincode' ), '' ),
			'show_views'   => array( __( 'بازدید', 'zarincode' ), 'yes' ),
			'show_excerpt' => array( __( 'خلاصه متن', 'zarincode' ), 'yes' ),
			'show_reading' => array( __( 'زمان مطالعه', 'zarincode' ), '' ),
		) as $key => $data ) {
			$this->add_control(
				$key,
				array(
					'label'   => $data[0],
					'type'    => Controls_Manager::SWITCHER,
					'default' => $data[1],
				)
			);
		}

		$this->add_control(
			'excerpt_length',
			array(
				'label'     => __( 'تعداد کلمات خلاصه', 'zarincode' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 16,
				'condition' => array( 'show_excerpt' => 'yes' ),
			)
		);

		$this->end_controls_section();

		$this->add_more_button_controls();

		$this->start_controls_section(
			'style_section',
			array(
				'label' => __( 'استایل', 'zarincode' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'رنگ عنوان', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-card__title a' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typo',
				'selector' => '{{WRAPPER}} .zc-card__title',
			)
		);

		$this->add_control(
			'excerpt_color',
			array(
				'label'     => __( 'رنگ خلاصه', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-card__excerpt' => 'color:{{VALUE}}' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * رندر.
	 *
	 * @return void
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$query = $this->build_query( $s, 'post', 'category' );

		$this->render_heading( $s );

		if ( ! $query->have_posts() ) {
			return;
		}

		$this->open_wrapper( $s );

		$i = 0;
		while ( $query->have_posts() ) {
			$query->the_post();
			$cats = get_the_category();
			?>
			<article class="zc-card <?php echo esc_attr( $this->item_class( $s ) ); ?>"<?php echo $this->anim_attr( $s, $i ); // phpcs:ignore ?>>
				<div class="zc-card__media<?php echo 'yes' === $s['show_fab'] ? ' zc-card__media--fab' : ''; ?>">
					<a href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
						<?php echo zc_thumbnail( get_the_ID(), 'zc-card' ); // phpcs:ignore ?>
					</a>
					<?php if ( 'yes' === $s['show_fab'] ) : ?>
						<a href="<?php the_permalink(); ?>" class="zc-card__fab" aria-hidden="true" tabindex="-1"><?php zc_the_icon( 'arrow-ul', 18 ); ?></a>
					<?php endif; ?>
					<?php if ( 'yes' === $s['show_cat'] && $cats ) : ?>
						<span class="zc-badge zc-badge--solid zc-badge--float"><?php echo esc_html( $cats[0]->name ); ?></span>
					<?php endif; ?>
				</div>

				<div class="zc-card__body">
					<h3 class="zc-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

					<?php if ( 'yes' === $s['show_excerpt'] ) : ?>
						<p class="zc-card__excerpt"><?php echo esc_html( zc_excerpt( get_the_excerpt(), (int) $s['excerpt_length'] ) ); ?></p>
					<?php endif; ?>

					<div class="zc-card__meta">
						<?php if ( 'yes' === $s['show_date'] ) : ?>
							<span><?php zc_the_icon( 'calendar', 15 ); ?><?php echo esc_html( zc_fa_num( get_the_date() ) ); ?></span>
						<?php endif; ?>
						<?php if ( 'yes' === $s['show_author'] ) : ?>
							<span><?php zc_the_icon( 'user', 15 ); ?><?php the_author(); ?></span>
						<?php endif; ?>
						<?php if ( 'yes' === $s['show_views'] ) : ?>
							<span><?php zc_the_icon( 'eye', 15 ); ?><?php echo esc_html( zc_fa_num( zc_get_views( get_the_ID() ) ) ); ?></span>
						<?php endif; ?>
						<?php if ( 'yes' === $s['show_reading'] ) : ?>
							<span><?php zc_the_icon( 'clock', 15 ); ?><?php echo esc_html( zc_fa_num( zc_reading_time() ) . ' ' . __( 'دقیقه', 'zarincode' ) ); ?></span>
						<?php endif; ?>
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
