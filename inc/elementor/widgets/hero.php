<?php
/**
 * ویجت هیرو (بنر اصلی) — بر اساس طرح مرجع
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;

/**
 * ویجت هیرو.
 */
class ZC_Widget_hero extends ZC_Widget_Base {

	/**
	 * نام ویجت.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_hero';
	}

	/**
	 * عنوان.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | بنر اصلی (هیرو)', 'zarincode' );
	}

	/**
	 * آیکن.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-banner';
	}

	/**
	 * ثبت کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {

		/* ---------- محتوا ---------- */
		$this->start_controls_section(
			'content_section',
			array( 'label' => __( 'محتوای بنر', 'zarincode' ) )
		);

		$this->add_control(
			'badge_text',
			array(
				'label'   => __( 'متن نشان بالای عنوان', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'مرجع تخصصی آموزش برنامه‌نویسی', 'zarincode' ),
			)
		);

		$this->add_control(
			'badge_icon',
			array(
				'label'     => __( 'آیکن نشان', 'zarincode' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'sparkle',
				'options'   => zc_get_icons_options(),
				'condition' => array( 'badge_text!' => '' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'عنوان اصلی', 'zarincode' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'default'     => __( 'با <span>زرین کد</span> برنامه‌نویس شو', 'zarincode' ),
				'description' => __( 'برای طلایی‌شدن بخشی از متن از تگ span استفاده کنید.', 'zarincode' ),
			)
		);

		$this->add_control(
			'desc',
			array(
				'label'   => __( 'توضیحات', 'zarincode' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 4,
				'default' => __( 'آموزش پروژه‌محور برنامه‌نویسی از صفر تا بازار کار؛ به همراه فروشگاه تخصصی قالب، افزونه و فونت. همین امروز مسیر یادگیری‌ات را شروع کن.', 'zarincode' ),
			)
		);

		$this->add_control(
			'btn1_text',
			array(
				'label'   => __( 'متن دکمه اول', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'شروع یادگیری رایگان', 'zarincode' ),
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
				'default' => __( 'مشاهده دوره‌ها', 'zarincode' ),
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
			'image',
			array(
				'label'   => __( 'تصویر بنر', 'zarincode' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array( 'url' => ZC_ASSETS . 'img/hero.svg' ),
			)
		);

		$this->end_controls_section();

		/* ---------- کارت‌های شناور ---------- */
		$this->start_controls_section(
			'floats_section',
			array( 'label' => __( 'کارت‌های شناور روی تصویر', 'zarincode' ) )
		);

		$this->add_control(
			'show_floats',
			array(
				'label'   => __( 'نمایش کارت‌های شناور', 'zarincode' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'float_icon',
			array(
				'label'   => __( 'آیکن', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'users',
				'options' => zc_get_icons_options(),
			)
		);

		$repeater->add_control(
			'float_text',
			array(
				'label'   => __( 'متن', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( '+۱۲۰۰۰ دانشجو', 'zarincode' ),
			)
		);

		$this->add_control(
			'floats',
			array(
				'label'       => __( 'کارت‌ها (حداکثر ۳)', 'zarincode' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ float_text }}}',
				'default'     => array(
					array( 'float_icon' => 'users', 'float_text' => __( '+۱۲,۰۰۰ دانشجو', 'zarincode' ) ),
					array( 'float_icon' => 'video', 'float_text' => __( '+۲۰۰ دوره تخصصی', 'zarincode' ) ),
					array( 'float_icon' => 'certificate', 'float_text' => __( 'گواهی معتبر پایان دوره', 'zarincode' ) ),
				),
				'condition'   => array( 'show_floats' => 'yes' ),
			)
		);

		$this->end_controls_section();

		/* ---------- استایل ---------- */
		$this->start_controls_section(
			'style_section',
			array(
				'label' => __( 'استایل بنر', 'zarincode' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'hero_bg',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .zc-hero',
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'رنگ عنوان', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-hero__title' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typo',
				'selector' => '{{WRAPPER}} .zc-hero__title',
			)
		);

		$this->add_control(
			'desc_color',
			array(
				'label'     => __( 'رنگ توضیحات', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-hero__desc' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_responsive_control(
			'hero_padding',
			array(
				'label'      => __( 'فاصله داخلی', 'zarincode' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}} .zc-hero__grid' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}' ),
			)
		);

		$this->add_control(
			'hero_radius',
			array(
				'label'      => __( 'گردی گوشه‌ها', 'zarincode' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array( '{{WRAPPER}} .zc-hero' => 'border-radius:{{SIZE}}{{UNIT}}' ),
			)
		);

		$this->add_control(
			'reverse',
			array(
				'label'     => __( 'جابجایی تصویر و متن', 'zarincode' ),
				'type'      => Controls_Manager::SWITCHER,
				'selectors' => array( '{{WRAPPER}} .zc-hero__media' => 'order:-1' ),
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
		$s = $this->get_settings_for_display();
		?>
		<div class="zc-hero">
			<span class="zc-hero__deco zc-hero__deco--1"></span>
			<span class="zc-hero__deco zc-hero__deco--2"></span>

			<div class="zc-hero__grid">
				<div class="zc-hero__content" data-zc-anim="start">

					<?php if ( ! empty( $s['badge_text'] ) ) : ?>
						<span class="zc-hero__badge">
							<?php zc_the_icon( $s['badge_icon'] ?: 'sparkle', 16 ); ?>
							<?php echo esc_html( $s['badge_text'] ); ?>
						</span>
					<?php endif; ?>

					<?php if ( ! empty( $s['title'] ) ) : ?>
						<h1 class="zc-hero__title"><?php echo wp_kses_post( $s['title'] ); ?></h1>
					<?php endif; ?>

					<?php if ( ! empty( $s['desc'] ) ) : ?>
						<p class="zc-hero__desc"><?php echo esc_html( $s['desc'] ); ?></p>
					<?php endif; ?>

					<div class="zc-hero__actions">
						<?php if ( ! empty( $s['btn1_text'] ) ) : ?>
							<a href="<?php echo esc_url( $s['btn1_link']['url'] ?? '#' ); ?>" class="zc-btn zc-btn--gold zc-btn--lg">
								<?php echo esc_html( $s['btn1_text'] ); ?>
								<?php zc_the_icon( 'arrow-left', 19 ); ?>
							</a>
						<?php endif; ?>
						<?php if ( ! empty( $s['btn2_text'] ) ) : ?>
							<a href="<?php echo esc_url( $s['btn2_link']['url'] ?? '#' ); ?>" class="zc-btn zc-btn--white zc-btn--lg">
								<?php zc_the_icon( 'play', 17 ); ?>
								<?php echo esc_html( $s['btn2_text'] ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>

				<div class="zc-hero__media" data-zc-anim="end" data-zc-delay="150">
					<?php if ( ! empty( $s['image']['url'] ) ) : ?>
						<img src="<?php echo esc_url( $s['image']['url'] ); ?>"
							alt="<?php echo esc_attr( wp_strip_all_tags( $s['title'] ?? '' ) ); ?>"
							width="620" height="470" fetchpriority="high" decoding="async">
					<?php endif; ?>

					<?php
					if ( 'yes' === $s['show_floats'] && ! empty( $s['floats'] ) ) :
						$i = 0;
						foreach ( $s['floats'] as $float ) :
							$i++;
							if ( $i > 3 ) {
								break;
							}
							?>
							<div class="zc-hero__float zc-hero__float--<?php echo (int) $i; ?>">
								<?php zc_the_icon( $float['float_icon'] ?: 'check', 18 ); ?>
								<span><?php echo esc_html( $float['float_text'] ); ?></span>
							</div>
							<?php
						endforeach;
					endif;
					?>
				</div>
			</div>
		</div>
		<?php
	}
}
