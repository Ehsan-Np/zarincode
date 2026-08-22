<?php
/**
 * ویجت فرم درخواست پروژه
 *
 * مشتری خدمت موردنظر، بودجه، زمان تحویل و توضیحات پروژه را
 * ثبت می‌کند و درخواست به صورت آجاکسی در پیشخوان ذخیره می‌شود.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * ویجت فرم درخواست پروژه.
 */
class ZC_Widget_request_form extends ZC_Widget_Base {

	/**
	 * نام ویجت.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_request_form';
	}

	/**
	 * عنوان ویجت.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | فرم درخواست پروژه', 'zarincode' );
	}

	/**
	 * آیکن ویجت.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	/**
	 * ثبت کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {

		$this->add_heading_controls(
			__( 'پروژه‌ات را به <span>ما</span> بسپار', 'zarincode' ),
			__( 'فرم زیر را پر کنید تا در کمتر از ۲۴ ساعت با شما تماس بگیریم', 'zarincode' )
		);

		$this->start_controls_section(
			'form_section',
			array( 'label' => __( 'تنظیمات فرم', 'zarincode' ) )
		);

		$this->add_control(
			'btn_text',
			array(
				'label'   => __( 'متن دکمه ارسال', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'ارسال درخواست', 'zarincode' ),
			)
		);

		$this->add_control(
			'side_title',
			array(
				'label'   => __( 'عنوان ستون کناری', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'چرا زرین کد؟', 'zarincode' ),
			)
		);

		$this->add_control(
			'side_items',
			array(
				'label'       => __( 'مزیت‌ها (هر خط یکی)', 'zarincode' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 6,
				'default'     => __( "مشاوره رایگان پیش از شروع\nقرارداد رسمی و شفاف\nتحویل مرحله‌ای پروژه\nپشتیبانی پس از تحویل\nضمانت بازگشت وجه", 'zarincode' ),
			)
		);

		$this->add_control(
			'show_side_stats',
			array(
				'label'        => __( 'نمایش آمار در ستون کناری', 'zarincode' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$stats = new Repeater();

		$stats->add_control(
			'stat_num',
			array(
				'label'   => __( 'عدد', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '۳۵۰+',
			)
		);

		$stats->add_control(
			'stat_label',
			array(
				'label'   => __( 'برچسب', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'پروژه موفق', 'zarincode' ),
			)
		);

		$this->add_control(
			'side_stats',
			array(
				'label'       => __( 'آمار ستون کناری', 'zarincode' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $stats->get_controls(),
				'title_field' => '{{{ stat_num }}} {{{ stat_label }}}',
				'condition'   => array( 'show_side_stats' => 'yes' ),
				'default'     => array(
					array( 'stat_num' => '۳۵۰+', 'stat_label' => __( 'پروژه موفق', 'zarincode' ) ),
					array( 'stat_num' => '۲۴ ساعت', 'stat_label' => __( 'زمان پاسخ', 'zarincode' ) ),
					array( 'stat_num' => '۹۷٪', 'stat_label' => __( 'رضایت کارفرما', 'zarincode' ) ),
				),
			)
		);

		$this->add_control(
			'dark',
			array(
				'label'        => __( 'پس‌زمینه تیره', 'zarincode' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * رندر خروجی.
	 *
	 * @return void
	 */
	protected function render() {
		$s        = $this->get_settings_for_display();
		$services = zc_services_list();
		$budgets  = zc_request_budgets();
		$dead     = zc_request_deadlines();
		$items    = array_filter( array_map( 'trim', explode( "\n", (string) $s['side_items'] ) ) );
		$dark     = 'yes' === $s['dark'];
		$user     = wp_get_current_user();
		?>
		<section class="zc-section zc-reqform-sec zc-reqform-sec--full<?php echo $dark ? ' zc-reqform-sec--dark' : ''; ?>">

			<span class="zc-reqform__glow zc-reqform__glow--a" aria-hidden="true"></span>
			<span class="zc-reqform__glow zc-reqform__glow--b" aria-hidden="true"></span>
			<span class="zc-reqform__grid-bg" aria-hidden="true"></span>

			<div class="zc-container">
				<?php $this->render_heading( $s ); ?>

				<div class="zc-reqform">

					<form class="zc-reqform__form" data-zc-form="zc_submit_request" novalidate>
						<div class="zc-reqform__grid">
							<div class="zc-field">
								<label for="zc-req-name"><?php esc_html_e( 'نام و نام خانوادگی', 'zarincode' ); ?> <span>*</span></label>
								<input type="text" id="zc-req-name" name="name" required
									value="<?php echo esc_attr( $user->exists() ? $user->display_name : '' ); ?>"
									placeholder="<?php esc_attr_e( 'مثلاً علی محمدی', 'zarincode' ); ?>" />
							</div>

							<div class="zc-field">
								<label for="zc-req-mobile"><?php esc_html_e( 'شماره موبایل', 'zarincode' ); ?> <span>*</span></label>
								<input type="tel" id="zc-req-mobile" name="mobile" required dir="ltr"
									inputmode="numeric" pattern="09[0-9]{9}" maxlength="11"
									value="<?php echo esc_attr( $user->exists() ? get_user_meta( $user->ID, 'zc_mobile', true ) : '' ); ?>"
									placeholder="09xxxxxxxxx" />
							</div>

							<div class="zc-field">
								<label for="zc-req-email"><?php esc_html_e( 'ایمیل', 'zarincode' ); ?></label>
								<input type="email" id="zc-req-email" name="email" dir="ltr"
									value="<?php echo esc_attr( $user->exists() ? $user->user_email : '' ); ?>"
									placeholder="you@example.com" />
							</div>

							<div class="zc-field">
								<label for="zc-req-service"><?php esc_html_e( 'خدمت موردنظر', 'zarincode' ); ?></label>
								<select id="zc-req-service" name="service">
									<option value=""><?php esc_html_e( 'انتخاب کنید…', 'zarincode' ); ?></option>
									<?php foreach ( $services as $sid => $title ) : ?>
										<option value="<?php echo (int) $sid; ?>"><?php echo esc_html( $title ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="zc-field">
								<label for="zc-req-budget"><?php esc_html_e( 'بودجه تقریبی', 'zarincode' ); ?></label>
								<select id="zc-req-budget" name="budget">
									<?php foreach ( $budgets as $k => $label ) : ?>
										<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="zc-field">
								<label for="zc-req-deadline"><?php esc_html_e( 'زمان تحویل', 'zarincode' ); ?></label>
								<select id="zc-req-deadline" name="deadline">
									<?php foreach ( $dead as $k => $label ) : ?>
										<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="zc-field zc-field--full">
								<label for="zc-req-desc"><?php esc_html_e( 'توضیحات پروژه', 'zarincode' ); ?> <span>*</span></label>
								<textarea id="zc-req-desc" name="description" rows="5" required
									placeholder="<?php esc_attr_e( 'هدف پروژه، امکانات موردنیاز و هر نکته‌ای که به ما کمک می‌کند بهتر راهنمایی‌تان کنیم…', 'zarincode' ); ?>"></textarea>
							</div>
						</div>

						<div class="zc-reqform__actions">
							<button type="submit" class="zc-btn zc-btn--gold zc-btn--lg">
								<?php zc_the_icon( 'send', 18 ); ?>
								<span><?php echo esc_html( $s['btn_text'] ); ?></span>
							</button>

							<p class="zc-reqform__note">
								<?php zc_the_icon( 'shield', 15 ); ?>
								<?php esc_html_e( 'اطلاعات شما محرمانه است و در اختیار کسی قرار نمی‌گیرد.', 'zarincode' ); ?>
							</p>
						</div>

						<div class="zc-form-msg" role="status" aria-live="polite"></div>
					</form>

					<aside class="zc-reqform__side">
						<h3 class="zc-reqform__side-title"><?php echo esc_html( $s['side_title'] ); ?></h3>

						<ul class="zc-reqform__list">
							<?php foreach ( $items as $item ) : ?>
								<li><?php zc_the_icon( 'check', 17 ); ?><span><?php echo esc_html( $item ); ?></span></li>
							<?php endforeach; ?>
						</ul>

						<?php if ( 'yes' === $s['show_side_stats'] && ! empty( $s['side_stats'] ) ) : ?>
							<div class="zc-reqform__stats">
								<?php foreach ( $s['side_stats'] as $stat ) : ?>
									<div class="zc-reqform__stat">
										<strong><?php echo esc_html( $stat['stat_num'] ); ?></strong>
										<span><?php echo esc_html( $stat['stat_label'] ); ?></span>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<div class="zc-reqform__contact">
							<span><?php esc_html_e( 'ترجیح می‌دهید تماس بگیرید؟', 'zarincode' ); ?></span>
							<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', zc_opt( 'zc_phone', '071-42380267' ) ) ); ?>" dir="ltr">
								<?php zc_the_icon( 'phone', 17 ); ?>
								<?php echo esc_html( zc_fa_num( zc_opt( 'zc_phone', '071-42380267' ) ) ); ?>
							</a>
						</div>
					</aside>
				</div>
			</div>
		</section>
		<?php
	}
}
