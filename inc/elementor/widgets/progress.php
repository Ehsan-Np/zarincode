<?php
/**
 * ویجت نوار مهارت / پیشرفت
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * ویجت پیشرفت.
 */
class ZC_Widget_progress extends ZC_Widget_Base {

	/** @return string */
	public function get_name() { return 'zc_progress'; }
	/** @return string */
	public function get_title() { return __( 'زرین کد | نوار مهارت', 'zarincode' ); }
	/** @return string */
	public function get_icon() { return 'eicon-skill-bar'; }

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'مهارت‌ها', 'zarincode' ) ) );

		$rep = new Repeater();
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => 'PHP' ) );
		$rep->add_control( 'value', array( 'label' => __( 'درصد', 'zarincode' ), 'type' => Controls_Manager::SLIDER, 'default' => array( 'size' => 85 ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ) ) );

		$this->add_control(
			'items',
			array(
				'label'       => __( 'لیست', 'zarincode' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array( 'title' => 'PHP / Laravel', 'value' => array( 'size' => 92 ) ),
					array( 'title' => 'JavaScript / React', 'value' => array( 'size' => 85 ) ),
					array( 'title' => 'WordPress', 'value' => array( 'size' => 96 ) ),
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'style_sec', array( 'label' => __( 'استایل', 'zarincode' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'bar_color', array( 'label' => __( 'رنگ نوار', 'zarincode' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .zc-progress__bar' => 'background:{{VALUE}}' ) ) );
		$this->add_control( 'bar_height', array( 'label' => __( 'ارتفاع نوار', 'zarincode' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 4, 'max' => 30 ) ), 'selectors' => array( '{{WRAPPER}} .zc-progress' => 'height:{{SIZE}}px' ) ) );
		$this->end_controls_section();
	}

	/**
	 * رندر.
	 *
	 * @return void
	 */
	protected function render() {
		$s = $this->get_settings_for_display();
		if ( empty( $s['items'] ) ) {
			return;
		}
		foreach ( $s['items'] as $i => $item ) {
			$val = $item['value']['size'] ?? 0;
			printf(
				'<div style="margin-bottom:18px" data-zc-anim="up" data-zc-delay="%1$d">
					<div style="display:flex;justify-content:space-between;margin-bottom:7px;font-size:.88rem;font-weight:600">
						<span>%2$s</span><span style="color:var(--zc-gold-3)">%3$s٪</span>
					</div>
					<div class="zc-progress"><div class="zc-progress__bar" data-value="%4$s"></div></div>
				</div>',
				(int) ( $i * 90 ),
				esc_html( $item['title'] ),
				esc_html( zc_fa_num( $val ) ),
				esc_attr( $val )
			);
		}
	}
}
