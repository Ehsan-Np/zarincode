<?php
/**
 * ویجت نمادهای اعتماد
 *
 * نمایش نماد اعتماد الکترونیکی (ای‌نماد)، ساماندهی، درگاه زرین‌پال
 * و نشان ملی ثبت رسانه‌های دیجیتال. هر نماد می‌تواند از سه راه
 * تعریف شود:
 *   ۱. کد اسکریپت/HTML رسمی که سازمان مربوطه می‌دهد
 *   ۲. تصویر دلخواه + لینک
 *   ۳. نماد آماده‌ی داخلی قالب (SVG)
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * ویجت نمادهای اعتماد.
 */
class ZC_Widget_trust_badges extends ZC_Widget_Base {

	/**
	 * نام ویجت.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_trust_badges';
	}

	/**
	 * عنوان ویجت.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | نمادهای اعتماد', 'zarincode' );
	}

	/**
	 * آیکن ویجت.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-lock-user';
	}

	/**
	 * کلیدواژه‌های جستجو در پنل المنتور.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'نماد', 'اعتماد', 'enamad', 'samandehi', 'zarinpal', 'زرین‌پال', 'ساماندهی', 'trust' );
	}

	/**
	 * ثبت کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {

		$this->add_heading_controls(
			__( 'نمادهای <span>اعتماد</span>', 'zarincode' ),
			__( 'خرید امن با مجوزهای رسمی', 'zarincode' )
		);

		/* ---------- نمادها ---------- */
		$this->start_controls_section(
			'badges_section',
			array( 'label' => __( 'نمادها', 'zarincode' ) )
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'badge_type',
			array(
				'label'   => __( 'نوع نماد', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'enamad',
				'options' => array(
					'enamad'    => __( 'نماد اعتماد الکترونیکی (ای‌نماد)', 'zarincode' ),
					'samandehi' => __( 'ساماندهی', 'zarincode' ),
					'zarinpal'  => __( 'درگاه پرداخت زرین‌پال', 'zarincode' ),
					'irandigi'  => __( 'نشان ملی ثبت رسانه‌های دیجیتال', 'zarincode' ),
					'custom'    => __( 'نماد دلخواه', 'zarincode' ),
				),
			)
		);

		$repeater->add_control(
			'source',
			array(
				'label'   => __( 'روش نمایش', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'code',
				'options' => array(
					'code'    => __( 'کد رسمی سازمان (اسکریپت/HTML)', 'zarincode' ),
					'image'   => __( 'تصویر و لینک', 'zarincode' ),
					'builtin' => __( 'نماد آماده قالب', 'zarincode' ),
				),
			)
		);

		$repeater->add_control(
			'code',
			array(
				'label'       => __( 'کد نماد', 'zarincode' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 6,
				'placeholder' => '<a referrerpolicy="origin" target="_blank" href="https://trustseal.enamad.ir/...">…</a>',
				'description' => __( 'کد کامل دریافتی از پنل ای‌نماد، ساماندهی یا زرین‌پال را اینجا بچسبانید.', 'zarincode' ),
				'condition'   => array( 'source' => 'code' ),
			)
		);

		$repeater->add_control(
			'image',
			array(
				'label'     => __( 'تصویر نماد', 'zarincode' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array( 'source' => 'image' ),
			)
		);

		$repeater->add_control(
			'link',
			array(
				'label'       => __( 'لینک نماد', 'zarincode' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://',
				'condition'   => array( 'source' => array( 'image', 'builtin' ) ),
			)
		);

		$repeater->add_control(
			'label',
			array(
				'label'       => __( 'برچسب زیر نماد', 'zarincode' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'مثلاً: درگاه امن', 'zarincode' ),
			)
		);

		$this->add_control(
			'badges',
			array(
				'label'       => __( 'فهرست نمادها', 'zarincode' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ badge_type }}}',
				'default'     => array(
					array( 'badge_type' => 'enamad', 'source' => 'builtin', 'label' => __( 'نماد اعتماد', 'zarincode' ) ),
					array( 'badge_type' => 'samandehi', 'source' => 'builtin', 'label' => __( 'ساماندهی', 'zarincode' ) ),
					array( 'badge_type' => 'zarinpal', 'source' => 'builtin', 'label' => __( 'درگاه امن', 'zarincode' ) ),
					array( 'badge_type' => 'irandigi', 'source' => 'builtin', 'label' => __( 'رسانه دیجیتال', 'zarincode' ) ),
				),
			)
		);

		$this->end_controls_section();

		/* ---------- ظاهر ---------- */
		$this->start_controls_section(
			'style_section',
			array( 'label' => __( 'ظاهر', 'zarincode' ) )
		);

		$this->add_control(
			'card_style',
			array(
				'label'   => __( 'سبک کارت', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'boxed',
				'options' => array(
					'boxed'  => __( 'کادردار سفید', 'zarincode' ),
					'glass'  => __( 'شیشه‌ای (مناسب پس‌زمینه تیره)', 'zarincode' ),
					'plain'  => __( 'ساده بدون کادر', 'zarincode' ),
				),
			)
		);

		$this->add_control(
			'align',
			array(
				'label'   => __( 'چیدمان', 'zarincode' ),
				'type'    => Controls_Manager::CHOOSE,
				'default' => 'center',
				'options' => array(
					'flex-start' => array( 'title' => __( 'راست', 'zarincode' ), 'icon' => 'eicon-text-align-right' ),
					'center'     => array( 'title' => __( 'وسط', 'zarincode' ), 'icon' => 'eicon-text-align-center' ),
					'flex-end'   => array( 'title' => __( 'چپ', 'zarincode' ), 'icon' => 'eicon-text-align-left' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .zc-trust' => 'justify-content:{{VALUE}}',
				),
			)
		);

		$this->add_control(
			'badge_size',
			array(
				'label'      => __( 'اندازه نماد (پیکسل)', 'zarincode' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array( 'px' => array( 'min' => 60, 'max' => 200 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 96 ),
				'selectors'  => array(
					'{{WRAPPER}} .zc-trust__item' => '--zc-trust-size:{{SIZE}}px',
				),
			)
		);

		$this->add_control(
			'grayscale',
			array(
				'label'        => __( 'سیاه‌وسفید تا زمان اشاره‌ی موس', 'zarincode' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_label',
			array(
				'label'        => __( 'نمایش برچسب زیر نماد', 'zarincode' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * نمادهای آماده‌ی داخلی به صورت SVG.
	 *
	 * تصاویر رسمی سازمان‌ها قابل توزیع در قالب نیستند؛ بنابراین
	 * نمادهای بازطراحی‌شده‌ی سبک‌وزن ارائه می‌شود تا پیش از دریافت
	 * کد رسمی، ظاهر بخش کامل باشد.
	 *
	 * @param string $type نوع نماد.
	 * @return string
	 */
	protected function builtin_badge( $type ) {
		$badges = array(
			'enamad'    => '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="enamad">
				<path d="M32 4 8 13v18c0 14 10.2 24.6 24 29 13.8-4.4 24-15 24-29V13L32 4z" fill="#1B4D8F"/>
				<path d="M32 9 13 16v15c0 11.5 8.2 20.3 19 24 10.8-3.7 19-12.5 19-24V16L32 9z" fill="#fff" opacity=".12"/>
				<path d="m22 32 7 7 14-14" stroke="#fff" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>',

			'samandehi' => '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="samandehi">
				<rect x="5" y="5" width="54" height="54" rx="12" fill="#0E7C5A"/>
				<path d="M20 40c0-8 5.4-12 12-12s12 4 12 12" stroke="#fff" stroke-width="4" stroke-linecap="round"/>
				<circle cx="32" cy="22" r="6" fill="#fff"/>
				<path d="M17 46h30" stroke="#8FE3C4" stroke-width="3.5" stroke-linecap="round"/>
			</svg>',

			'zarinpal'  => '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="zarinpal">
				<rect x="5" y="5" width="54" height="54" rx="14" fill="#FFCE00"/>
				<path d="M18 26h28M18 26l6-8h16l6 8" stroke="#1A1A1A" stroke-width="3.4" stroke-linejoin="round"/>
				<rect x="18" y="26" width="28" height="20" rx="4" stroke="#1A1A1A" stroke-width="3.4"/>
				<path d="M27 36h10" stroke="#1A1A1A" stroke-width="3.4" stroke-linecap="round"/>
			</svg>',

			'irandigi'  => '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="digital media">
				<rect x="5" y="5" width="54" height="54" rx="12" fill="#1D3E8C"/>
				<path d="M14 42V22l9 12 9-12v20" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
				<circle cx="45" cy="38" r="5" fill="#E63946"/>
				<path d="M40 24h11" stroke="#fff" stroke-width="3.4" stroke-linecap="round"/>
			</svg>',

			'custom'    => '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="badge">
				<rect x="5" y="5" width="54" height="54" rx="12" fill="#C9A227"/>
				<path d="m22 32 7 7 14-14" stroke="#fff" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>',
		);

		return $badges[ $type ] ?? $badges['custom'];
	}

	/**
	 * رندر خروجی.
	 *
	 * @return void
	 */
	protected function render() {
		$s      = $this->get_settings_for_display();
		$badges = $s['badges'] ?? array();

		if ( empty( $badges ) ) {
			return;
		}

		$classes = array( 'zc-trust', 'zc-trust--' . $s['card_style'] );

		if ( 'yes' === ( $s['grayscale'] ?? '' ) ) {
			$classes[] = 'zc-trust--gray';
		}
		?>
		<section class="zc-section zc-trust-sec">
			<div class="zc-container">
				<?php $this->render_heading( $s ); ?>

				<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
					<?php
					foreach ( $badges as $i => $badge ) :
						$type   = $badge['badge_type'] ?? 'custom';
						$source = $badge['source'] ?? 'builtin';
						$label  = $badge['label'] ?? '';
						?>
						<div class="zc-trust__item" data-zc-anim="zoom" data-zc-delay="<?php echo (int) ( $i * 70 ); ?>">
							<div class="zc-trust__box">
								<?php
								if ( 'code' === $source && ! empty( $badge['code'] ) ) {
									/*
									 * کد رسمی نمادها شامل <a> و <img> و گاهی <script> است.
									 * این کد را فقط مدیر سایت وارد می‌کند، بنابراین با
									 * مجموعه‌ی گسترده‌تری از تگ‌های مجاز پاک‌سازی می‌شود.
									 */
									echo zc_kses_badge( $badge['code'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

								} elseif ( 'image' === $source && ! empty( $badge['image']['url'] ) ) {
									$img = sprintf(
										'<img src="%s" alt="%s" loading="lazy" decoding="async" />',
										esc_url( $badge['image']['url'] ),
										esc_attr( $label ? $label : $type )
									);

									if ( ! empty( $badge['link']['url'] ) ) {
										printf(
											'<a href="%s" target="%s" rel="noopener nofollow">%s</a>',
											esc_url( $badge['link']['url'] ),
											! empty( $badge['link']['is_external'] ) ? '_blank' : '_self',
											$img // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										);
									} else {
										echo $img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									}
								} else {
									$svg = $this->builtin_badge( $type );

									if ( ! empty( $badge['link']['url'] ) ) {
										printf(
											'<a href="%s" target="%s" rel="noopener nofollow">%s</a>',
											esc_url( $badge['link']['url'] ),
											! empty( $badge['link']['is_external'] ) ? '_blank' : '_self',
											$svg // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										);
									} else {
										echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									}
								}
								?>
							</div>

							<?php if ( 'yes' === $s['show_label'] && $label ) : ?>
								<span class="zc-trust__label"><?php echo esc_html( $label ); ?></span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
