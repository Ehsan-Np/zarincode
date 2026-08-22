<?php
/**
 * ویجت نوار آمار — کارت شناور زیر هیرو (طبق طرح مرجع)
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;

/**
 * ویجت آمار.
 */
class ZC_Widget_stats extends ZC_Widget_Base {

	/**
	 * نام.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_stats';
	}

	/**
	 * عنوان.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | نوار آمار', 'zarincode' );
	}

	/**
	 * آیکن.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-counter';
	}

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			array( 'label' => __( 'آیتم‌های آمار', 'zarincode' ) )
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'icon',
			array(
				'label'   => __( 'آیکن', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'users',
				'options' => zc_get_icons_options(),
			)
		);

		$repeater->add_control(
			'number',
			array(
				'label'   => __( 'عدد', 'zarincode' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 12000,
			)
		);

		$repeater->add_control(
			'prefix',
			array(
				'label'   => __( 'پیشوند', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '+',
			)
		);

		$repeater->add_control(
			'suffix',
			array(
				'label' => __( 'پسوند', 'zarincode' ),
				'type'  => Controls_Manager::TEXT,
			)
		);

		$repeater->add_control(
			'label',
			array(
				'label'   => __( 'برچسب', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'دانشجوی فعال', 'zarincode' ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'آمارها', 'zarincode' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ label }}}',
				'default'     => array(
					array( 'icon' => 'users', 'number' => 12000, 'prefix' => '+', 'label' => __( 'دانشجوی فعال', 'zarincode' ) ),
					array( 'icon' => 'video', 'number' => 210, 'prefix' => '+', 'label' => __( 'دوره تخصصی', 'zarincode' ) ),
					array( 'icon' => 'award', 'number' => 97, 'prefix' => '', 'suffix' => '%', 'label' => __( 'رضایت دانشجویان', 'zarincode' ) ),
					array( 'icon' => 'code', 'number' => 850, 'prefix' => '+', 'label' => __( 'محصول دیجیتال', 'zarincode' ) ),
				),
			)
		);

		$this->add_control(
			'float_mode',
			array(
				'label'       => __( 'حالت شناور (روی بنر بالایی)', 'zarincode' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => __( 'کارت آمار به سمت بالا کشیده می‌شود و روی بنر قرار می‌گیرد.', 'zarincode' ),
			)
		);

		$this->add_control(
			'animate_numbers',
			array(
				'label'   => __( 'انیمیشن شمارش اعداد', 'zarincode' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
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
			'box_bg',
			array(
				'label'     => __( 'رنگ پس‌زمینه کارت', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-stats' => 'background:{{VALUE}}' ),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'box_shadow',
				'selector' => '{{WRAPPER}} .zc-stats',
			)
		);

		$this->add_control(
			'num_color',
			array(
				'label'     => __( 'رنگ اعداد', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-stat__num' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'num_typo',
				'selector' => '{{WRAPPER}} .zc-stat__num',
			)
		);

		$this->add_control(
			'label_color',
			array(
				'label'     => __( 'رنگ برچسب', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-stat__label' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_control(
			'icon_bg',
			array(
				'label'     => __( 'پس‌زمینه آیکن', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-stat__icon' => 'background:{{VALUE}}' ),
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'رنگ آیکن', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-stat__icon' => 'color:{{VALUE}}' ),
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
		$items = $s['items'] ?? array();

		if ( empty( $items ) ) {
			return;
		}

		$count = (int) min( 4, count( $items ) );

		/*
		 * تعداد ستون‌ها به‌جای style درون‌خطی (که هیچ مدیا-کوئری‌ای
		 * نمی‌تواند آن را بازنویسی کند) با کلاس اعلام می‌شود تا لایه‌ی
		 * واکنش‌گرا بتواند در موبایل و تبلت بازچینش کند.
		 */
		$classes = 'zc-stats zc-stats--' . $count;

		if ( 'yes' !== $s['float_mode'] ) {
			$classes .= ' zc-stats--flat';
		}
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" data-zc-anim="up">
			<?php foreach ( $items as $item ) : ?>
				<div class="zc-stat">
					<span class="zc-stat__icon"><?php zc_the_icon( $item['icon'] ?: 'chart', 26 ); ?></span>
					<span>
						<span class="zc-stat__num">
							<?php echo esc_html( $item['prefix'] ?? '' ); ?><?php
							if ( 'yes' === $s['animate_numbers'] ) {
								echo '<span data-zc-count="' . esc_attr( (int) $item['number'] ) . '">۰</span>';
							} else {
								echo esc_html( zc_fa_num( number_format( (int) $item['number'] ) ) );
							}
							?><?php echo esc_html( $item['suffix'] ?? '' ); ?>
						</span>
						<span class="zc-stat__label"><?php echo esc_html( $item['label'] ); ?></span>
					</span>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
