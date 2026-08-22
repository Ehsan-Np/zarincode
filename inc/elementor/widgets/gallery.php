<?php
/**
 * ویجت گالری تصاویر
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * ویجت گالری.
 */
class ZC_Widget_gallery extends ZC_Widget_Base {

	/** @return string */
	public function get_name() { return 'zc_gallery'; }
	/** @return string */
	public function get_title() { return __( 'زرین کد | گالری تصاویر', 'zarincode' ); }
	/** @return string */
	public function get_icon() { return 'eicon-gallery-masonry'; }

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->add_heading_controls( '', '' );

		$this->start_controls_section( 'content', array( 'label' => __( 'تصاویر', 'zarincode' ) ) );
		$this->add_control( 'images', array( 'label' => __( 'گالری', 'zarincode' ), 'type' => Controls_Manager::GALLERY ) );
		$this->add_control( 'lightbox', array( 'label' => __( 'بازشدن در لایت‌باکس', 'zarincode' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->add_layout_controls( 3 );
	}

	/**
	 * رندر.
	 *
	 * @return void
	 */
	protected function render() {
		$s = $this->get_settings_for_display();
		$this->render_heading( $s );

		if ( empty( $s['images'] ) ) {
			return;
		}

		$this->open_wrapper( $s );

		foreach ( $s['images'] as $i => $img ) {
			printf(
				'<a href="%1$s" class="zc-card %2$s" style="aspect-ratio:4/3"%3$s%4$s>
					<img src="%1$s" alt="" loading="lazy" style="width:100%%;height:100%%;object-fit:cover">
				</a>',
				esc_url( $img['url'] ),
				esc_attr( $this->item_class( $s ) ),
				$this->anim_attr( $s, $i ), // phpcs:ignore
				'yes' === $s['lightbox'] ? ' data-elementor-open-lightbox="yes"' : ''
			);
		}

		$this->close_wrapper( $s );
	}
}
