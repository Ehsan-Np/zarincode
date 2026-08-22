<?php
/**
 * ویجت خبرنامه
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;

/**
 * ویجت خبرنامه.
 */
class ZC_Widget_newsletter extends ZC_Widget_Base {

	/**
	 * نام.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_newsletter';
	}

	/**
	 * عنوان.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | خبرنامه', 'zarincode' );
	}

	/**
	 * آیکن.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-email-field';
	}

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'zarincode' ) ) );

		$this->add_control(
			'title',
			array(
				'label'   => __( 'عنوان', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'از جدیدترین دوره‌ها باخبر شو', 'zarincode' ),
			)
		);

		$this->add_control(
			'desc',
			array(
				'label'   => __( 'توضیحات', 'zarincode' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => __( 'ایمیل یا شماره موبایل خود را وارد کنید تا تخفیف‌ها و دوره‌های جدید را زودتر از همه دریافت کنید.', 'zarincode' ),
			)
		);

		$this->add_control(
			'field_type',
			array(
				'label'   => __( 'نوع فیلد', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'email',
				'options' => array(
					'email'  => __( 'ایمیل', 'zarincode' ),
					'mobile' => __( 'موبایل (پیامک)', 'zarincode' ),
				),
			)
		);

		$this->add_control(
			'placeholder',
			array(
				'label'   => __( 'متن راهنمای فیلد', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'ایمیل خود را وارد کنید', 'zarincode' ),
			)
		);

		$this->add_control(
			'btn_text',
			array(
				'label'   => __( 'متن دکمه', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'عضویت', 'zarincode' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'zarincode' ), 'tab' => Controls_Manager::TAB_STYLE ) );

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'box_bg',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .zc-newsletter',
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'رنگ عنوان', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-newsletter h3' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_control(
			'radius',
			array(
				'label'     => __( 'گردی گوشه', 'zarincode' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 50 ) ),
				'selectors' => array( '{{WRAPPER}} .zc-newsletter' => 'border-radius:{{SIZE}}{{UNIT}}' ),
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
		<div class="zc-newsletter" data-zc-anim="zoom">
			<div>
				<h3><?php echo esc_html( $s['title'] ); ?></h3>
				<p><?php echo esc_html( $s['desc'] ); ?></p>
			</div>
			<form class="zc-newsletter__form zc-newsletter__form--full" data-zc-form="zc_newsletter">
				<div class="zc-newsletter__grid">
					<input type="email" name="email" placeholder="<?php esc_attr_e( 'ایمیل *', 'zarincode' ); ?>" required aria-label="<?php esc_attr_e( 'ایمیل', 'zarincode' ); ?>">
					<input type="tel" name="mobile" dir="ltr" placeholder="<?php esc_attr_e( 'موبایل * 09…', 'zarincode' ); ?>" required aria-label="<?php esc_attr_e( 'موبایل', 'zarincode' ); ?>">
					<input type="text" name="telegram_id" dir="ltr" placeholder="<?php esc_attr_e( 'آیدی تلگرام (اختیاری)', 'zarincode' ); ?>" aria-label="<?php esc_attr_e( 'آیدی تلگرام', 'zarincode' ); ?>">
					<input type="text" name="bale_id" dir="ltr" placeholder="<?php esc_attr_e( 'آیدی بله (اختیاری)', 'zarincode' ); ?>" aria-label="<?php esc_attr_e( 'آیدی بله', 'zarincode' ); ?>">
				</div>
				<button type="submit" class="zc-btn zc-btn--gold">
					<?php echo esc_html( $s['btn_text'] ); ?>
					<?php zc_the_icon( 'send', 18 ); ?>
				</button>
				<div class="zc-form-msg" style="width:100%"></div>
			</form>
		</div>
		<?php
	}
}
