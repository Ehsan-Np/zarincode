<?php
/**
 * ویجت نقشه راه یادگیری
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * ویجت نقشه راه.
 */
class ZC_Widget_roadmap extends ZC_Widget_Base {

	/** @return string */
	public function get_name() { return 'zc_roadmap'; }
	/** @return string */
	public function get_title() { return __( 'زرین کد | نقشه راه یادگیری', 'zarincode' ); }
	/** @return string */
	public function get_icon() { return 'eicon-flow'; }

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->add_heading_controls( __( 'نقشه راه <span>یادگیری</span>', 'zarincode' ), __( 'گام به گام تا تبدیل شدن به یک برنامه‌نویس حرفه‌ای', 'zarincode' ) );

		$this->start_controls_section( 'content', array( 'label' => __( 'مراحل', 'zarincode' ) ) );

		$rep = new Repeater();
		$rep->add_control( 'step', array( 'label' => __( 'شماره مرحله', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => '۱' ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'مبانی برنامه‌نویسی', 'zarincode' ) ) );
		$rep->add_control( 'text', array( 'label' => __( 'توضیحات', 'zarincode' ), 'type' => Controls_Manager::TEXTAREA ) );
		$rep->add_control( 'link', array( 'label' => __( 'لینک دوره', 'zarincode' ), 'type' => Controls_Manager::URL ) );

		$this->add_control(
			'items',
			array(
				'label'       => __( 'لیست مراحل', 'zarincode' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ step }}}. {{{ title }}}',
				'default'     => array(
					array( 'step' => '۱', 'title' => __( 'مبانی و الگوریتم', 'zarincode' ) ),
					array( 'step' => '۲', 'title' => __( 'HTML و CSS', 'zarincode' ) ),
					array( 'step' => '۳', 'title' => __( 'جاوااسکریپت', 'zarincode' ) ),
					array( 'step' => '۴', 'title' => __( 'فریم‌ورک و پروژه واقعی', 'zarincode' ) ),
				),
			)
		);

		$this->end_controls_section();
		$this->add_layout_controls( 4 );
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

		foreach ( $s['items'] as $i => $item ) {
			$url  = $item['link']['url'] ?? '';
			$tag  = $url ? 'a' : 'div';
			$href = $url ? ' href="' . esc_url( $url ) . '"' : '';
			printf(
				'<%1$s%2$s class="zc-step %3$s"%4$s>
					<span class="zc-step__num">%5$s</span>
					<h3 style="font-size:1rem;margin-bottom:7px">%6$s</h3>
					%7$s
				</%1$s>',
				esc_attr( $tag ),
				$href, // phpcs:ignore
				esc_attr( $this->item_class( $s ) ),
				$this->anim_attr( $s, $i ), // phpcs:ignore
				esc_html( $item['step'] ),
				esc_html( $item['title'] ),
				! empty( $item['text'] ) ? '<p style="font-size:.85rem;color:var(--zc-muted);margin:0;line-height:2">' . esc_html( $item['text'] ) . '</p>' : ''
			);
		}

		$this->close_wrapper( $s );
	}
}
