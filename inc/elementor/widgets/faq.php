<?php
/**
 * ویجت سوالات متداول (آکاردئون)
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * ویجت FAQ.
 */
class ZC_Widget_faq extends ZC_Widget_Base {

	/**
	 * نام.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_faq';
	}

	/**
	 * عنوان.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | سوالات متداول', 'zarincode' );
	}

	/**
	 * آیکن.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-help-o';
	}

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {

		$this->add_heading_controls(
			__( 'سوالات <span>متداول</span>', 'zarincode' ),
			__( 'پاسخ پرتکرارترین سوالات شما درباره دوره‌ها و محصولات', 'zarincode' )
		);

		$this->start_controls_section(
			'items_section',
			array( 'label' => __( 'سوالات', 'zarincode' ) )
		);

		$this->add_control(
			'source',
			array(
				'label'   => __( 'منبع', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'manual',
				'options' => array(
					'manual' => __( 'ورود دستی', 'zarincode' ),
					'cpt'    => __( 'از بخش سوالات متداول', 'zarincode' ),
				),
			)
		);

		$rep = new Repeater();

		$rep->add_control(
			'question',
			array(
				'label'   => __( 'سوال', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'آیا دوره‌ها دارای پشتیبانی هستند؟', 'zarincode' ),
			)
		);

		$rep->add_control(
			'answer',
			array(
				'label'   => __( 'پاسخ', 'zarincode' ),
				'type'    => Controls_Manager::WYSIWYG,
				'default' => __( 'بله، تمام دوره‌های زرین کد دارای پشتیبانی مستقیم مدرس از طریق سیستم تیکتینگ و گروه اختصاصی دانشجویان هستند.', 'zarincode' ),
			)
		);

		$rep->add_control(
			'is_open',
			array(
				'label' => __( 'باز به صورت پیش‌فرض', 'zarincode' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'لیست سوالات', 'zarincode' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ question }}}',
				'condition'   => array( 'source' => 'manual' ),
				'default'     => array(
					array(
						'question' => __( 'آیا دوره‌ها دارای پشتیبانی هستند؟', 'zarincode' ),
						'answer'   => __( 'بله، تمام دوره‌ها دارای پشتیبانی مستقیم مدرس از طریق سیستم تیکتینگ هستند.', 'zarincode' ),
						'is_open'  => 'yes',
					),
					array(
						'question' => __( 'پس از خرید، دسترسی به دوره چقدر است؟', 'zarincode' ),
						'answer'   => __( 'دسترسی شما به دوره‌های خریداری‌شده مادام‌العمر است و بروزرسانی‌ها نیز رایگان ارائه می‌شود.', 'zarincode' ),
					),
					array(
						'question' => __( 'آیا امکان بازگشت وجه وجود دارد؟', 'zarincode' ),
						'answer'   => __( 'بله، تا ۷ روز پس از خرید در صورت عدم رضایت، مبلغ به کیف پول شما بازگردانده می‌شود.', 'zarincode' ),
					),
				),
			)
		);

		$this->add_control(
			'cpt_count',
			array(
				'label'     => __( 'تعداد نمایش', 'zarincode' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 8,
				'condition' => array( 'source' => 'cpt' ),
			)
		);

		$this->add_control(
			'single_open',
			array(
				'label'   => __( 'فقط یک سوال باز بماند', 'zarincode' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'schema',
			array(
				'label'       => __( 'افزودن اسکیمای سئو (FAQPage)', 'zarincode' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => __( 'باعث نمایش سوالات در نتایج گوگل می‌شود.', 'zarincode' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_section',
			array(
				'label' => __( 'استایل', 'zarincode' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'q_color',
			array(
				'label'     => __( 'رنگ سوال', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-accordion__head' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_control(
			'a_color',
			array(
				'label'     => __( 'رنگ پاسخ', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-accordion__inner' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_control(
			'item_bg',
			array(
				'label'     => __( 'پس‌زمینه آیتم', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-accordion__item' => 'background:{{VALUE}}' ),
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
		$items = array();

		if ( 'cpt' === $s['source'] ) {
			$faqs = get_posts(
				array(
					'post_type'      => 'zc_faq',
					'posts_per_page' => (int) ( $s['cpt_count'] ?? 8 ),
				)
			);
			foreach ( $faqs as $faq ) {
				$items[] = array(
					'question' => $faq->post_title,
					'answer'   => apply_filters( 'the_content', $faq->post_content ),
					'is_open'  => '',
				);
			}
		} else {
			$items = $s['items'] ?? array();
		}

		$this->render_heading( $s );

		if ( empty( $items ) ) {
			return;
		}

		echo '<div class="zc-accordion" data-single="' . esc_attr( 'yes' === $s['single_open'] ? 'yes' : 'no' ) . '">';

		$i      = 0;
		$schema = array();

		foreach ( $items as $item ) {
			$open = 'yes' === ( $item['is_open'] ?? '' );
			printf(
				'<div class="zc-accordion__item%1$s" data-zc-anim="up" data-zc-delay="%2$d">
					<button class="zc-accordion__head" type="button" aria-expanded="%3$s">
						<span style="display:flex;align-items:center;gap:10px">%4$s<span>%5$s</span></span>
						<span class="zc-accordion__icon">%6$s</span>
					</button>
					<div class="zc-accordion__body"><div class="zc-accordion__inner">%7$s</div></div>
				</div>',
				$open ? ' is-open' : '',
				(int) ( $i * 60 ),
				$open ? 'true' : 'false',
				zc_icon( 'question', 18 ), // phpcs:ignore
				esc_html( $item['question'] ),
				zc_icon( 'chevron', 18 ), // phpcs:ignore
				wp_kses_post( $item['answer'] )
			);

			$schema[] = array(
				'@type'          => 'Question',
				'name'           => wp_strip_all_tags( $item['question'] ),
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => wp_strip_all_tags( $item['answer'] ),
				),
			);
			$i++;
		}

		echo '</div>';

		if ( 'yes' === $s['schema'] && ! empty( $schema ) ) {
			printf(
				'<script type="application/ld+json">%s</script>',
				wp_json_encode(
					array(
						'@context'   => 'https://schema.org',
						'@type'      => 'FAQPage',
						'mainEntity' => $schema,
					),
					JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
				)
			);
		}
	}
}
