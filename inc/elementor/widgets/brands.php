<?php
/**
 * ویجت لوگوی برندها / تکنولوژی‌ها
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * ویجت برندها.
 */
class ZC_Widget_brands extends ZC_Widget_Base {

	/**
	 * نام.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_brands';
	}

	/**
	 * عنوان.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | لوگوی برندها', 'zarincode' );
	}

	/**
	 * آیکن.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-logo';
	}

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->add_heading_controls( __( 'شرکت‌های <span>همکار</span>', 'zarincode' ), '' );

		$this->start_controls_section( 'items', array( 'label' => __( 'لوگوها', 'zarincode' ) ) );

		$rep = new Repeater();
		$rep->add_control( 'image', array( 'label' => __( 'لوگو', 'zarincode' ), 'type' => Controls_Manager::MEDIA ) );
		$rep->add_control( 'title', array( 'label' => __( 'نام', 'zarincode' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'link', array( 'label' => __( 'لینک', 'zarincode' ), 'type' => Controls_Manager::URL ) );

		$this->add_control(
			'items',
			array(
				'label'       => __( 'لیست', 'zarincode' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ title }}}',
			)
		);

		$this->add_control(
			'grayscale',
			array(
				'label'     => __( 'حالت سیاه‌وسفید', 'zarincode' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'selectors' => array( '{{WRAPPER}} .zc-brand img' => 'filter:grayscale(1);opacity:.65' ),
			)
		);

		$this->end_controls_section();

		$this->add_layout_controls( 6 );
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
			$tag  = ! empty( $item['link']['url'] ) ? 'a' : 'div';
			$href = ! empty( $item['link']['url'] ) ? ' href="' . esc_url( $item['link']['url'] ) . '" target="_blank" rel="noopener"' : '';
			printf(
				'<%1$s%2$s class="zc-brand %3$s"%4$s><img src="%5$s" alt="%6$s" loading="lazy" width="120" height="50" style="max-height:46px;width:auto;transition:all .3s"></%1$s>',
				esc_attr( $tag ),
				$href, // phpcs:ignore
				esc_attr( $this->item_class( $s ) ),
				$this->anim_attr( $s, $i ), // phpcs:ignore
				esc_url( $item['image']['url'] ?? ZC_ASSETS . 'img/placeholder.svg' ),
				esc_attr( $item['title'] ?? '' )
			);
			$i++;
		}

		$this->close_wrapper( $s );
	}
}
