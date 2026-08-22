<?php
/**
 * ویجت دکمه سفارشی
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

/**
 * ویجت دکمه.
 */
class ZC_Widget_button extends ZC_Widget_Base {

	/**
	 * نام.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_button';
	}

	/**
	 * عنوان.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | دکمه', 'zarincode' );
	}

	/**
	 * آیکن.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-button';
	}

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'zarincode' ) ) );

		$this->add_control( 'text', array( 'label' => __( 'متن', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'شروع کنید', 'zarincode' ) ) );
		$this->add_control( 'link', array( 'label' => __( 'لینک', 'zarincode' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );

		$this->add_control(
			'icon',
			array(
				'label'   => __( 'آیکن', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'arrow-left',
				'options' => array_merge( array( '' => __( 'بدون آیکن', 'zarincode' ) ), zc_get_icons_options() ),
			)
		);

		$this->add_control(
			'style',
			array(
				'label'   => __( 'استایل', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'gold',
				'options' => array(
					'gold'    => __( 'طلایی', 'zarincode' ),
					'dark'    => __( 'تیره', 'zarincode' ),
					'navy'    => __( 'سرمه‌ای', 'zarincode' ),
					'outline' => __( 'خطی', 'zarincode' ),
					'ghost'   => __( 'محو', 'zarincode' ),
					'white'   => __( 'سفید', 'zarincode' ),
				),
			)
		);

		$this->add_control(
			'size',
			array(
				'label'   => __( 'اندازه', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => array( 'zc-btn--sm' => __( 'کوچک', 'zarincode' ), '' => __( 'معمولی', 'zarincode' ), 'zc-btn--lg' => __( 'بزرگ', 'zarincode' ) ),
			)
		);

		$this->add_control( 'full_width', array( 'label' => __( 'تمام عرض', 'zarincode' ), 'type' => Controls_Manager::SWITCHER ) );

		$this->add_responsive_control(
			'align',
			array(
				'label'     => __( 'چیدمان', 'zarincode' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'right'  => array( 'title' => __( 'راست', 'zarincode' ), 'icon' => 'eicon-text-align-right' ),
					'center' => array( 'title' => __( 'وسط', 'zarincode' ), 'icon' => 'eicon-text-align-center' ),
					'left'   => array( 'title' => __( 'چپ', 'zarincode' ), 'icon' => 'eicon-text-align-left' ),
				),
				'default'   => 'right',
				'selectors' => array( '{{WRAPPER}} .zc-btn-wrap' => 'text-align:{{VALUE}}' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'style_sec', array( 'label' => __( 'استایل', 'zarincode' ), 'tab' => Controls_Manager::TAB_STYLE ) );

		$this->add_control( 'bg_color', array( 'label' => __( 'رنگ پس‌زمینه', 'zarincode' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .zc-btn' => 'background:{{VALUE}}' ) ) );
		$this->add_control( 'txt_color', array( 'label' => __( 'رنگ متن', 'zarincode' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .zc-btn' => 'color:{{VALUE}}' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'typo', 'selector' => '{{WRAPPER}} .zc-btn' ) );
		$this->add_control( 'radius', array( 'label' => __( 'گردی گوشه', 'zarincode' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 60 ) ), 'selectors' => array( '{{WRAPPER}} .zc-btn' => 'border-radius:{{SIZE}}{{UNIT}}' ) ) );

		$this->end_controls_section();
	}

	/**
	 * رندر.
	 *
	 * @return void
	 */
	protected function render() {
		$s      = $this->get_settings_for_display();
		$target = ! empty( $s['link']['is_external'] ) ? ' target="_blank"' : '';
		$rel    = ! empty( $s['link']['nofollow'] ) ? ' rel="nofollow"' : '';
		$block  = 'yes' === ( $s['full_width'] ?? '' ) ? ' zc-btn--block' : '';

		printf(
			'<div class="zc-btn-wrap" data-zc-anim="up"><a href="%1$s"%2$s%3$s class="zc-btn zc-btn--%4$s %5$s%6$s">%7$s%8$s</a></div>',
			esc_url( $s['link']['url'] ?? '#' ),
			$target, // phpcs:ignore
			$rel, // phpcs:ignore
			esc_attr( $s['style'] ),
			esc_attr( $s['size'] ),
			esc_attr( $block ),
			esc_html( $s['text'] ),
			! empty( $s['icon'] ) ? zc_icon( $s['icon'], 18 ) : '' // phpcs:ignore
		);
	}
}
