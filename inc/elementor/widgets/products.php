<?php
/**
 * ویجت نمایش محصولات فروشگاه (ووکامرس)
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

/**
 * ویجت محصولات.
 */
class ZC_Widget_products extends ZC_Widget_Base {

	/**
	 * نام.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_products';
	}

	/**
	 * عنوان.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | محصولات فروشگاه', 'zarincode' );
	}

	/**
	 * آیکن.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-products';
	}

	/**
	 * دسته.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'zarincode-shop', 'zarincode' );
	}

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {

		$this->add_heading_controls(
			__( 'فروشگاه <span>محصولات دیجیتال</span>', 'zarincode' ),
			__( 'قالب وردپرس، افزونه، فونت و اسکریپت‌های آماده با پشتیبانی و بروزرسانی رایگان', 'zarincode' )
		);

		$this->start_controls_section(
			'query_section',
			array( 'label' => __( 'تنظیمات محصولات', 'zarincode' ) )
		);

		$this->add_control(
			'source',
			array(
				'label'   => __( 'منبع نمایش', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'recent',
				'options' => array(
					'recent'    => __( 'جدیدترین', 'zarincode' ),
					'featured'  => __( 'محصولات ویژه', 'zarincode' ),
					'sale'      => __( 'تخفیف‌دار', 'zarincode' ),
					'best'      => __( 'پرفروش‌ترین', 'zarincode' ),
					'top_rated' => __( 'محبوب‌ترین', 'zarincode' ),
					'manual'    => __( 'انتخاب دستی', 'zarincode' ),
				),
			)
		);

		$this->add_control(
			'posts_count',
			array(
				'label'   => __( 'تعداد نمایش', 'zarincode' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 8,
				'min'     => 1,
				'max'     => 30,
			)
		);

		$this->add_control(
			'query_cats',
			array(
				'label'       => __( 'دسته‌بندی محصول', 'zarincode' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => zc_get_terms_options( 'product_cat' ),
				'label_block' => true,
				'condition'   => array( 'source!' => 'manual' ),
			)
		);

		$this->add_control(
			'specific_posts',
			array(
				'label'       => __( 'محصولات', 'zarincode' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => zc_get_posts_options( 'product' ),
				'label_block' => true,
				'condition'   => array( 'source' => 'manual' ),
			)
		);

		$this->add_control(
			'show_cat_tabs',
			array(
				'label'       => __( 'نمایش تب‌های فیلتر دسته (ای‌جکس)', 'zarincode' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'دسته‌بندی‌ها به صورت تب بالای محصولات نمایش داده می‌شود.', 'zarincode' ),
			)
		);

		$this->end_controls_section();

		$this->add_layout_controls( 4 );

		$this->start_controls_section(
			'card_section',
			array( 'label' => __( 'اجزای کارت محصول', 'zarincode' ) )
		);

		foreach ( array(
			'show_cat'    => array( __( 'دسته‌بندی', 'zarincode' ), 'yes' ),
			'show_rating' => array( __( 'امتیاز', 'zarincode' ), 'yes' ),
			'show_price'  => array( __( 'قیمت', 'zarincode' ), 'yes' ),
			'show_cart'   => array( __( 'دکمه افزودن به سبد', 'zarincode' ), 'yes' ),
			'show_wish'   => array( __( 'دکمه علاقه‌مندی', 'zarincode' ), 'yes' ),
			'show_sales'  => array( __( 'تعداد فروش', 'zarincode' ), '' ),
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

		$this->end_controls_section();

		$this->add_more_button_controls();

		$this->start_controls_section(
			'style_section',
			array(
				'label' => __( 'استایل کارت', 'zarincode' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'card_bg',
			array(
				'label'     => __( 'پس‌زمینه', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-card' => 'background:{{VALUE}}' ),
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

		if ( ! zc_is_woo() ) {
			echo '<div class="zc-alert zc-alert--warning">' . esc_html__( 'برای نمایش محصولات، افزونه ووکامرس را نصب و فعال کنید.', 'zarincode' ) . '</div>';
			return;
		}

		$query = zc_wc_products_query( $s );

		if ( ! $query->have_posts() ) {
			echo '<div class="zc-empty"><div class="zc-empty__icon">' . zc_icon( 'cart', 40 ) . '</div><h3>' . esc_html__( 'محصولی یافت نشد', 'zarincode' ) . '</h3></div>'; // phpcs:ignore
			return;
		}

		if ( 'yes' === $s['show_cat_tabs'] ) {
			zc_render_product_cat_tabs( $s );
		}

		$this->open_wrapper( $s );

		$i = 0;
		while ( $query->have_posts() ) {
			$query->the_post();
			global $product;
			$product = wc_get_product( get_the_ID() );
			zc_product_card( $s, $this->item_class( $s ), $this->anim_attr( $s, $i ) );
			$i++;
		}
		wp_reset_postdata();

		$this->close_wrapper( $s );
		$this->render_more_button( $s );
	}
}
