<?php
/**
 * ویجت باکس داستان/معرفی (کارت سرمه‌ای با دکمه‌های پایین) — طبق طرح مرجع
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Typography;

/**
 * ویجت داستان.
 */
class ZC_Widget_story_box extends ZC_Widget_Base {

	/**
	 * نام.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_story_box';
	}

	/**
	 * عنوان.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | باکس داستان برند', 'zarincode' );
	}

	/**
	 * آیکن.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-post-content';
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
			'icon',
			array(
				'label'   => __( 'آیکن بالای عنوان', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'sparkle',
				'options' => array_merge( array( '' => __( 'بدون آیکن', 'zarincode' ) ), zc_get_icons_options() ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'عنوان', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'داستان زرین کد از کجا شروع شد؟', 'zarincode' ),
			)
		);

		$this->add_control(
			'text',
			array(
				'label'   => __( 'متن', 'zarincode' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 6,
				'default' => __( 'زرین کد در سال ۱۳۹۸ با هدف ساده‌سازی مسیر یادگیری برنامه‌نویسی برای فارسی‌زبانان متولد شد. ما باور داریم آموزش باید پروژه‌محور، به‌روز و قابل استفاده در بازار کار واقعی باشد. امروز با بیش از ۲۰۰ دوره تخصصی، صدها محصول دیجیتال و جامعه‌ای بزرگ از توسعه‌دهندگان، در کنار شما هستیم تا از یک علاقه‌مند ساده به یک برنامه‌نویس حرفه‌ای تبدیل شوید.', 'zarincode' ),
			)
		);

		$this->add_control(
			'btn1_text',
			array(
				'label'   => __( 'متن دکمه اول', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'ادامه داستان', 'zarincode' ),
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
			'btn2_text',
			array(
				'label'   => __( 'متن دکمه دوم', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'تماس با ما', 'zarincode' ),
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
			'show_deco',
			array(
				'label'   => __( 'نمایش نقش تزئینی', 'zarincode' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
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

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'box_bg',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .zc-story',
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'رنگ عنوان', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-story__title' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typo',
				'selector' => '{{WRAPPER}} .zc-story__title',
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => __( 'رنگ متن', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-story__text' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_control(
			'actions_bg',
			array(
				'label'       => __( 'رنگ پس‌زمینه ناحیه دکمه‌ها', 'zarincode' ),
				'type'        => Controls_Manager::COLOR,
				'description' => __( 'باید با رنگ پس‌زمینه صفحه یکسان باشد.', 'zarincode' ),
				'selectors'   => array( '{{WRAPPER}} .zc-story__actions' => 'background:{{VALUE}}' ),
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
		<div class="zc-story" data-zc-anim="zoom">
			<?php if ( ! empty( $s['icon'] ) ) : ?>
				<div class="zc-story__icon"><?php zc_the_icon( $s['icon'], 38 ); ?></div>
			<?php endif; ?>

			<?php if ( ! empty( $s['title'] ) ) : ?>
				<h2 class="zc-story__title"><?php echo esc_html( $s['title'] ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $s['text'] ) ) : ?>
				<p class="zc-story__text"><?php echo nl2br( esc_html( $s['text'] ) ); ?></p>
			<?php endif; ?>

			<?php if ( 'yes' === $s['show_deco'] ) : ?>
				<span class="zc-story__deco"><?php zc_the_icon( 'code', 44 ); ?></span>
			<?php endif; ?>

			<?php if ( ! empty( $s['btn1_text'] ) || ! empty( $s['btn2_text'] ) ) : ?>
			<div class="zc-story__actions">
				<?php if ( ! empty( $s['btn1_text'] ) ) : ?>
					<a href="<?php echo esc_url( $s['btn1_link']['url'] ?? '#' ); ?>" class="zc-btn zc-btn--gold zc-btn--sm"><?php echo esc_html( $s['btn1_text'] ); ?><?php zc_the_icon( 'arrow-left', 15 ); ?></a>
				<?php endif; ?>
				<?php if ( ! empty( $s['btn2_text'] ) ) : ?>
					<a href="<?php echo esc_url( $s['btn2_link']['url'] ?? '#' ); ?>" class="zc-btn zc-btn--navy zc-btn--sm"><?php echo esc_html( $s['btn2_text'] ); ?><?php zc_the_icon( 'arrow-left', 15 ); ?></a>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
