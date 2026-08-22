<?php
/**
 * ویجت فرم تماس با ما (ای‌جکس)
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * ویجت فرم تماس.
 */
class ZC_Widget_contact_form extends ZC_Widget_Base {

	/** @return string */
	public function get_name() { return 'zc_contact_form'; }
	/** @return string */
	public function get_title() { return __( 'زرین کد | فرم تماس با ما', 'zarincode' ); }
	/** @return string */
	public function get_icon() { return 'eicon-form-horizontal'; }

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'تنظیمات فرم', 'zarincode' ) ) );

		$this->add_control( 'form_title', array( 'label' => __( 'عنوان فرم', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'ارسال پیام', 'zarincode' ) ) );
		$this->add_control( 'show_subject', array( 'label' => __( 'فیلد موضوع', 'zarincode' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'show_phone', array( 'label' => __( 'فیلد تلفن', 'zarincode' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'show_dept', array( 'label' => __( 'فیلد دپارتمان', 'zarincode' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'btn_text', array( 'label' => __( 'متن دکمه', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'ارسال پیام', 'zarincode' ) ) );
		$this->add_control( 'receiver', array( 'label' => __( 'ایمیل دریافت‌کننده', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'placeholder' => get_option( 'admin_email' ) ) );

		$this->end_controls_section();

		$this->start_controls_section( 'style_sec', array( 'label' => __( 'استایل', 'zarincode' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'box_bg', array( 'label' => __( 'پس‌زمینه فرم', 'zarincode' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .zc-contact-form' => 'background:{{VALUE}}' ) ) );
		$this->add_control( 'label_color', array( 'label' => __( 'رنگ برچسب‌ها', 'zarincode' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .zc-label' => 'color:{{VALUE}}' ) ) );
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
		<div class="zc-contact-form" style="background:#fff;border-radius:var(--zc-radius);padding:28px;border:1px solid var(--zc-line-2);box-shadow:var(--zc-shadow-xs)" data-zc-anim="up">
			<?php if ( ! empty( $s['form_title'] ) ) : ?>
				<h3 style="display:flex;align-items:center;gap:9px;margin-bottom:20px"><?php zc_the_icon( 'send', 20 ); ?><?php echo esc_html( $s['form_title'] ); ?></h3>
			<?php endif; ?>

			<form data-zc-form="zc_contact_submit">
				<input type="hidden" name="receiver" value="<?php echo esc_attr( $s['receiver'] ?? '' ); ?>">
				<div class="zc-row">
					<div class="zc-field zc-field--half">
						<label class="zc-label"><?php esc_html_e( 'نام و نام خانوادگی', 'zarincode' ); ?> <span class="req">*</span></label>
						<input type="text" name="name" required>
					</div>
					<div class="zc-field zc-field--half">
						<label class="zc-label"><?php esc_html_e( 'ایمیل', 'zarincode' ); ?> <span class="req">*</span></label>
						<input type="email" name="email" required>
					</div>
				</div>

				<div class="zc-row">
					<?php if ( 'yes' === $s['show_phone'] ) : ?>
					<div class="zc-field zc-field--half">
						<label class="zc-label"><?php esc_html_e( 'شماره تماس', 'zarincode' ); ?></label>
						<input type="tel" name="phone" inputmode="numeric">
					</div>
					<?php endif; ?>
					<?php if ( 'yes' === $s['show_dept'] ) : ?>
					<div class="zc-field zc-field--half">
						<label class="zc-label"><?php esc_html_e( 'دپارتمان', 'zarincode' ); ?></label>
						<select name="department">
							<option><?php esc_html_e( 'پشتیبانی فنی', 'zarincode' ); ?></option>
							<option><?php esc_html_e( 'مالی و پرداخت', 'zarincode' ); ?></option>
							<option><?php esc_html_e( 'مشاوره دوره', 'zarincode' ); ?></option>
							<option><?php esc_html_e( 'همکاری و تبلیغات', 'zarincode' ); ?></option>
						</select>
					</div>
					<?php endif; ?>
				</div>

				<?php if ( 'yes' === $s['show_subject'] ) : ?>
				<div class="zc-field">
					<label class="zc-label"><?php esc_html_e( 'موضوع', 'zarincode' ); ?></label>
					<input type="text" name="subject">
				</div>
				<?php endif; ?>

				<div class="zc-field">
					<label class="zc-label"><?php esc_html_e( 'پیام شما', 'zarincode' ); ?> <span class="req">*</span></label>
					<textarea name="message" rows="5" required></textarea>
				</div>

				<div class="zc-form-msg"></div>

				<button type="submit" class="zc-btn zc-btn--gold zc-btn--block">
					<?php echo esc_html( $s['btn_text'] ); ?>
					<?php zc_the_icon( 'send', 18 ); ?>
				</button>
			</form>
		</div>
		<?php
	}
}
