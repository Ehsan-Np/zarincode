<?php
/**
 * ویجت سربرگ بخش (عنوان استایل‌دار)
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * ویجت سربرگ.
 */
class ZC_Widget_heading extends ZC_Widget_Base {

	/**
	 * نام.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_heading';
	}

	/**
	 * عنوان.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | سربرگ بخش', 'zarincode' );
	}

	/**
	 * آیکن.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-heading';
	}

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->add_heading_controls( __( 'عنوان <span>بخش</span>', 'zarincode' ), __( 'زیرعنوان توضیحی این بخش', 'zarincode' ) );

		$this->start_controls_section( 'extra', array( 'label' => __( 'تنظیمات بیشتر', 'zarincode' ) ) );

		$this->add_control(
			'html_tag',
			array(
				'label'   => __( 'تگ HTML عنوان', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h2',
				'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'div' => 'div' ),
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
		$s   = $this->get_settings_for_display();
		$tag = $s['html_tag'] ?? 'h2';

		if ( empty( $s['heading_title'] ) ) {
			return;
		}

		$align = $s['heading_align'] ?? 'center';
		echo '<div class="zc-heading' . ( 'center' !== $align ? ' zc-heading--start' : '' ) . '" data-zc-anim="up">';

		if ( 'yes' === ( $s['show_arrow'] ?? 'yes' ) ) {
			echo '<div class="zc-heading__arrow">' . zc_icon( 'chevron', 30 ) . '</div>'; // phpcs:ignore
		}

		printf( '<%1$s class="zc-heading__title">%2$s</%1$s>', esc_attr( $tag ), wp_kses_post( $s['heading_title'] ) );

		if ( ! empty( $s['heading_sub'] ) ) {
			echo '<p class="zc-heading__sub">' . esc_html( $s['heading_sub'] ) . '</p>';
		}

		echo '</div>';
	}
}
