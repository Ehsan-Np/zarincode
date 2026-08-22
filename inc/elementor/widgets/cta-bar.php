<?php
/**
 * ویجت نوار فراخوان (CTA Bar) با حاشیه طلایی — طبق طرح مرجع
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

/**
 * ویجت نوار CTA.
 */
class ZC_Widget_cta_bar extends ZC_Widget_Base {

	/**
	 * نام.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_cta_bar';
	}

	/**
	 * عنوان.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | نوار فراخوان', 'zarincode' );
	}

	/**
	 * آیکن.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-call-to-action';
	}

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			array( 'label' => __( 'محتوا', 'zarincode' ) )
		);

		$this->add_control(
			'bold_text',
			array(
				'label'   => __( 'متن پررنگ', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'با بیش از ۱۲,۰۰۰ دانشجو همراه شوید', 'zarincode' ),
			)
		);

		$this->add_control(
			'light_text',
			array(
				'label'   => __( 'متن توضیحی', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'مشاوره رایگان انتخاب مسیر شغلی، همین حالا!', 'zarincode' ),
			)
		);

		$this->add_control(
			'btn1_text',
			array(
				'label'   => __( 'متن دکمه اول', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'مشاوره رایگان', 'zarincode' ),
			)
		);

		$this->add_control(
			'btn1_link',
			array(
				'label'   => __( 'لینک دکمه اول', 'zarincode' ),
				'type'    => Controls_Manager::URL,
				'default' => array( 'url' => '#' ),
			)
		);

		$this->add_control(
			'btn1_style',
			array(
				'label'   => __( 'استایل دکمه اول', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'gold',
				'options' => array(
					'gold'    => __( 'طلایی', 'zarincode' ),
					'dark'    => __( 'تیره', 'zarincode' ),
					'navy'    => __( 'سرمه‌ای', 'zarincode' ),
					'outline' => __( 'خطی', 'zarincode' ),
				),
			)
		);

		$this->add_control(
			'btn2_text',
			array(
				'label'   => __( 'متن دکمه دوم', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'دوره‌های رایگان', 'zarincode' ),
			)
		);

		$this->add_control(
			'btn2_link',
			array(
				'label'   => __( 'لینک دکمه دوم', 'zarincode' ),
				'type'    => Controls_Manager::URL,
				'default' => array( 'url' => '#' ),
			)
		);

		$this->add_control(
			'btn2_style',
			array(
				'label'   => __( 'استایل دکمه دوم', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'navy',
				'options' => array(
					'gold'    => __( 'طلایی', 'zarincode' ),
					'dark'    => __( 'تیره', 'zarincode' ),
					'navy'    => __( 'سرمه‌ای', 'zarincode' ),
					'outline' => __( 'خطی', 'zarincode' ),
				),
			)
		);

		$this->end_controls_section();

		/* استایل */
		$this->start_controls_section(
			'style_section',
			array(
				'label' => __( 'استایل', 'zarincode' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'border_color',
			array(
				'label'     => __( 'رنگ حاشیه', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-ctabar' => 'border-color:{{VALUE}}' ),
			)
		);

		$this->add_control(
			'bar_bg',
			array(
				'label'     => __( 'رنگ پس‌زمینه', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-ctabar' => 'background:{{VALUE}}' ),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => __( 'رنگ متن پررنگ', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-ctabar__text b' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'text_typo',
				'selector' => '{{WRAPPER}} .zc-ctabar__text b',
			)
		);

		$this->add_responsive_control(
			'bar_padding',
			array(
				'label'      => __( 'فاصله داخلی', 'zarincode' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .zc-ctabar' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}' ),
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
		?>
		<div class="zc-ctabar" data-zc-anim="up">
			<p class="zc-ctabar__text">
				<?php if ( ! empty( $s['bold_text'] ) ) : ?>
					<b><?php echo esc_html( $s['bold_text'] ); ?></b>
				<?php endif; ?>
				<?php if ( ! empty( $s['light_text'] ) ) : ?>
					<span><?php echo esc_html( $s['light_text'] ); ?></span>
				<?php endif; ?>
			</p>
			<div class="zc-ctabar__actions">
				<?php if ( ! empty( $s['btn1_text'] ) ) : ?>
					<a href="<?php echo esc_url( $s['btn1_link']['url'] ?? '#' ); ?>" class="zc-btn zc-btn--<?php echo esc_attr( $s['btn1_style'] ); ?>">
						<?php echo esc_html( $s['btn1_text'] ); ?>
						<?php zc_the_icon( 'arrow-left', 17 ); ?>
					</a>
				<?php endif; ?>
				<?php if ( ! empty( $s['btn2_text'] ) ) : ?>
					<a href="<?php echo esc_url( $s['btn2_link']['url'] ?? '#' ); ?>" class="zc-btn zc-btn--<?php echo esc_attr( $s['btn2_style'] ); ?>">
						<?php echo esc_html( $s['btn2_text'] ); ?>
						<?php zc_the_icon( 'arrow-left', 17 ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
