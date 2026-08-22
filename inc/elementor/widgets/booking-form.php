<?php
/**
 * ویجت فرم رزرو نوبت
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * ویجت رزرو.
 */
class ZC_Widget_booking_form extends ZC_Widget_Base {

	/** @return string */
	public function get_name() { return 'zc_booking_form'; }
	/** @return string */
	public function get_title() { return __( 'زرین کد | فرم رزرو نوبت', 'zarincode' ); }
	/** @return string */
	public function get_icon() { return 'eicon-calendar'; }

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->add_heading_controls( __( 'رزرو <span>مشاوره</span>', 'zarincode' ), __( 'زمان مناسب خود را برای جلسه مشاوره رایگان انتخاب کنید', 'zarincode' ) );

		$this->start_controls_section( 'content', array( 'label' => __( 'تنظیمات', 'zarincode' ) ) );
		$this->add_control( 'btn_text', array( 'label' => __( 'متن دکمه', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'ثبت درخواست رزرو', 'zarincode' ) ) );
		$this->add_control( 'show_service', array( 'label' => __( 'انتخاب خدمت', 'zarincode' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'note', array( 'label' => __( 'یادداشت زیر فرم', 'zarincode' ), 'type' => Controls_Manager::TEXTAREA, 'default' => __( 'پس از ثبت درخواست، همکاران ما جهت تایید نهایی زمان با شما تماس می‌گیرند.', 'zarincode' ) ) );
		$this->end_controls_section();
	}

	/**
	 * رندر.
	 *
	 * @return void
	 */
	protected function render() {
		$s        = $this->get_settings_for_display();
		$services = get_posts( array( 'post_type' => 'zc_service', 'posts_per_page' => 50 ) );

		$this->render_heading( $s );
		?>
		<div class="zc-formcard" data-zc-anim="up">
			<form data-zc-form="zc_booking_submit">
				<div class="zc-row">
					<div class="zc-field zc-field--half">
						<label class="zc-label"><?php esc_html_e( 'نام و نام خانوادگی', 'zarincode' ); ?> <span class="req">*</span></label>
						<input type="text" name="name" required value="<?php echo esc_attr( is_user_logged_in() ? wp_get_current_user()->display_name : '' ); ?>">
					</div>
					<div class="zc-field zc-field--half">
						<label class="zc-label"><?php esc_html_e( 'شماره موبایل', 'zarincode' ); ?> <span class="req">*</span></label>
						<input type="tel" name="mobile" required inputmode="numeric" placeholder="09xxxxxxxxx">
					</div>
				</div>

				<?php if ( 'yes' === $s['show_service'] && $services ) : ?>
				<div class="zc-field">
					<label class="zc-label"><?php esc_html_e( 'نوع خدمت', 'zarincode' ); ?></label>
					<select name="service">
						<?php foreach ( $services as $srv ) : ?>
							<option value="<?php echo esc_attr( $srv->ID ); ?>"><?php echo esc_html( $srv->post_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php endif; ?>

				<div class="zc-row">
					<div class="zc-field zc-field--half">
						<label class="zc-label"><?php esc_html_e( 'تاریخ', 'zarincode' ); ?> <span class="req">*</span></label>
						<input type="date" name="date" required min="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>">
					</div>
					<div class="zc-field zc-field--half">
						<label class="zc-label"><?php esc_html_e( 'ساعت', 'zarincode' ); ?> <span class="req">*</span></label>
						<select name="time" required>
							<?php
							$slots = zc_booking_time_slots();
							foreach ( $slots as $slot ) {
								printf( '<option value="%1$s">%2$s</option>', esc_attr( $slot ), esc_html( zc_fa_num( $slot ) ) );
							}
							?>
						</select>
					</div>
				</div>

				<div class="zc-field">
					<label class="zc-label"><?php esc_html_e( 'توضیحات', 'zarincode' ); ?></label>
					<textarea name="note" rows="3"></textarea>
				</div>

				<div class="zc-form-msg"></div>

				<button type="submit" class="zc-btn zc-btn--gold zc-btn--block">
					<?php zc_the_icon( 'calendar', 18 ); ?>
					<?php echo esc_html( $s['btn_text'] ); ?>
				</button>

				<?php if ( ! empty( $s['note'] ) ) : ?>
					<p class="zc-help" style="text-align:center;margin-top:12px"><?php echo esc_html( $s['note'] ); ?></p>
				<?php endif; ?>
			</form>
		</div>
		<?php
	}
}
