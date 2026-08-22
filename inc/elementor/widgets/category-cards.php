<?php
/**
 * ویجت کارت‌های دسته‌بندی تصویری (طبق بخش «مهاجرت کاری» طرح مرجع)
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * ویجت کارت دسته‌بندی.
 */
class ZC_Widget_category_cards extends ZC_Widget_Base {

	/**
	 * نام.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_category_cards';
	}

	/**
	 * عنوان.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | کارت‌های دسته‌بندی تصویری', 'zarincode' );
	}

	/**
	 * آیکن.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {

		$this->add_heading_controls(
			__( 'مسیرهای <span>یادگیری</span>', 'zarincode' ),
			__( 'محبوب‌ترین شاخه‌های برنامه‌نویسی را انتخاب کنید و از صفر شروع کنید', 'zarincode' )
		);

		$this->start_controls_section(
			'items_section',
			array( 'label' => __( 'کارت‌ها', 'zarincode' ) )
		);

		$rep = new Repeater();

		$rep->add_control(
			'image',
			array(
				'label'   => __( 'تصویر', 'zarincode' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array( 'url' => ZC_ASSETS . 'img/placeholder.svg' ),
			)
		);

		$rep->add_control(
			'title_en',
			array(
				'label'   => __( 'عنوان انگلیسی', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'Web Development',
			)
		);

		$rep->add_control(
			'title_fa',
			array(
				'label'   => __( 'عنوان فارسی', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'برنامه‌نویسی وب', 'zarincode' ),
			)
		);

		$rep->add_control(
			'link',
			array(
				'label'   => __( 'لینک', 'zarincode' ),
				'type'    => Controls_Manager::URL,
				'default' => array( 'url' => '#' ),
			)
		);

		$rep->add_control(
			'fab_color',
			array(
				'label'   => __( 'رنگ دکمه فلش', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'gold',
				'options' => array(
					'gold' => __( 'طلایی', 'zarincode' ),
					'navy' => __( 'سرمه‌ای', 'zarincode' ),
					'dark' => __( 'تیره', 'zarincode' ),
				),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'لیست کارت‌ها', 'zarincode' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ title_fa }}}',
				'default'     => array(
					array( 'title_en' => 'Front-End', 'title_fa' => __( 'فرانت‌اند', 'zarincode' ) ),
					array( 'title_en' => 'Back-End', 'title_fa' => __( 'بک‌اند', 'zarincode' ) ),
					array( 'title_en' => 'Mobile App', 'title_fa' => __( 'اپلیکیشن موبایل', 'zarincode' ) ),
					array( 'title_en' => 'WordPress', 'title_fa' => __( 'وردپرس', 'zarincode' ), 'fab_color' => 'navy' ),
				),
			)
		);

		$this->end_controls_section();

		$this->add_layout_controls( 4 );

		/* استایل */
		$this->start_controls_section(
			'style_section',
			array(
				'label' => __( 'استایل', 'zarincode' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'card_ratio',
			array(
				'label'      => __( 'نسبت ابعاد کارت', 'zarincode' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array( 'px' => array( 'min' => 0.5, 'max' => 2, 'step' => 0.05 ) ),
				'default'    => array( 'size' => 0.88 ),
				'selectors'  => array( '{{WRAPPER}} .zc-catcard' => 'aspect-ratio:{{SIZE}}' ),
			)
		);

		$this->add_control(
			'card_radius',
			array(
				'label'     => __( 'گردی گوشه', 'zarincode' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'selectors' => array( '{{WRAPPER}} .zc-catcard' => 'border-radius:{{SIZE}}{{UNIT}}' ),
			)
		);

		$this->add_control(
			'overlay_color',
			array(
				'label'     => __( 'رنگ سایه روی تصویر', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-catcard::before' => 'background:linear-gradient(to top,{{VALUE}} 0%,transparent 70%)' ),
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

		$colors = array(
			'gold' => 'background:var(--zc-grad-gold);color:#241C05',
			'navy' => 'background:var(--zc-navy);color:#fff',
			'dark' => 'background:var(--zc-dark);color:var(--zc-gold-2)',
		);

		$i = 0;
		foreach ( $s['items'] as $item ) {
			$style = $colors[ $item['fab_color'] ?? 'gold' ] ?? $colors['gold'];
			printf(
				'<a href="%1$s" class="zc-catcard %2$s"%3$s>
					<img src="%4$s" alt="%5$s" loading="lazy" decoding="async" width="400" height="450">
					<span class="zc-catcard__inner">
						<span class="zc-catcard__fab" style="%6$s">%7$s</span>
						<span class="zc-catcard__txt">
							<span class="zc-catcard__en">%8$s</span>
							<span class="zc-catcard__fa">%5$s</span>
						</span>
					</span>
				</a>',
				esc_url( $item['link']['url'] ?? '#' ),
				esc_attr( $this->item_class( $s ) ),
				$this->anim_attr( $s, $i ), // phpcs:ignore
				esc_url( $item['image']['url'] ?? ZC_ASSETS . 'img/placeholder.svg' ),
				esc_attr( $item['title_fa'] ),
				esc_attr( $style ),
				zc_icon( 'arrow-ul', 18 ), // phpcs:ignore
				esc_html( $item['title_en'] )
			);
			$i++;
		}

		$this->close_wrapper( $s );
	}
}
