<?php
/**
 * ویجت شمارنده‌ها
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;

/**
 * ویجت شمارنده.
 */
class ZC_Widget_counters extends ZC_Widget_Base {

	/**
	 * نام.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_counters';
	}

	/**
	 * عنوان.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | شمارنده‌ها', 'zarincode' );
	}

	/**
	 * آیکن.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-number-field';
	}

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {

		$this->add_heading_controls( '', '' );

		$this->start_controls_section(
			'items_section',
			array( 'label' => __( 'شمارنده‌ها', 'zarincode' ) )
		);

		$rep = new Repeater();

		$rep->add_control(
			'icon',
			array(
				'label'   => __( 'آیکن', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'users',
				'options' => array_merge( array( '' => __( 'بدون آیکن', 'zarincode' ) ), zc_get_icons_options() ),
			)
		);

		$rep->add_control(
			'number',
			array(
				'label'   => __( 'عدد', 'zarincode' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 1200,
			)
		);

		$rep->add_control(
			'prefix',
			array(
				'label' => __( 'پیشوند', 'zarincode' ),
				'type'  => Controls_Manager::TEXT,
			)
		);

		$rep->add_control(
			'suffix',
			array(
				'label'   => __( 'پسوند', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '+',
			)
		);

		$rep->add_control(
			'label',
			array(
				'label'   => __( 'برچسب', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'دانشجو', 'zarincode' ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'لیست', 'zarincode' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ label }}}',
				'default'     => array(
					array( 'icon' => 'users', 'number' => 12000, 'suffix' => '+', 'label' => __( 'دانشجو', 'zarincode' ) ),
					array( 'icon' => 'video', 'number' => 210, 'suffix' => '+', 'label' => __( 'دوره', 'zarincode' ) ),
					array( 'icon' => 'code', 'number' => 850, 'suffix' => '+', 'label' => __( 'محصول', 'zarincode' ) ),
					array( 'icon' => 'award', 'number' => 97, 'suffix' => '%', 'label' => __( 'رضایت', 'zarincode' ) ),
				),
			)
		);

		$this->end_controls_section();

		$this->add_layout_controls( 4 );

		$this->start_controls_section(
			'style_section',
			array(
				'label' => __( 'استایل', 'zarincode' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'num_color',
			array(
				'label'     => __( 'رنگ عدد', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-counter__num' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'num_typo',
				'selector' => '{{WRAPPER}} .zc-counter__num',
			)
		);

		$this->add_control(
			'label_color',
			array(
				'label'     => __( 'رنگ برچسب', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-counter__label' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'رنگ آیکن', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-counter svg' => 'color:{{VALUE}}' ),
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
		$s = $this->get_settings_for_display();

		$this->render_heading( $s );

		if ( empty( $s['items'] ) ) {
			return;
		}

		$this->open_wrapper( $s );

		$i = 0;
		foreach ( $s['items'] as $item ) {
			?>
			<div class="zc-counter <?php echo esc_attr( $this->item_class( $s ) ); ?>"<?php echo $this->anim_attr( $s, $i ); // phpcs:ignore ?>>
				<?php if ( ! empty( $item['icon'] ) ) : ?>
					<div style="color:var(--zc-gold);margin-bottom:8px"><?php zc_the_icon( $item['icon'], 34 ); ?></div>
				<?php endif; ?>
				<span class="zc-counter__num">
					<?php echo esc_html( $item['prefix'] ?? '' ); ?><span data-zc-count="<?php echo esc_attr( (int) $item['number'] ); ?>">۰</span><?php echo esc_html( $item['suffix'] ?? '' ); ?>
				</span>
				<span class="zc-counter__label"><?php echo esc_html( $item['label'] ); ?></span>
			</div>
			<?php
			$i++;
		}

		$this->close_wrapper( $s );
	}
}
