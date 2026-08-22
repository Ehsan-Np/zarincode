<?php
/**
 * ویجت جعبه ویژگی‌ها / خدمات
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;

/**
 * ویجت ویژگی‌ها.
 */
class ZC_Widget_features extends ZC_Widget_Base {

	/**
	 * نام.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_features';
	}

	/**
	 * عنوان.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | ویژگی‌ها و خدمات', 'zarincode' );
	}

	/**
	 * آیکن.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-icon-box';
	}

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {

		$this->add_heading_controls(
			__( 'چرا <span>زرین کد</span>؟', 'zarincode' ),
			__( 'امکاناتی که یادگیری و خرید شما را متفاوت می‌کند', 'zarincode' )
		);

		$this->start_controls_section(
			'items_section',
			array( 'label' => __( 'ویژگی‌ها', 'zarincode' ) )
		);

		$rep = new Repeater();

		$rep->add_control(
			'icon',
			array(
				'label'   => __( 'آیکن', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'certificate',
				'options' => zc_get_icons_options(),
			)
		);

		$rep->add_control(
			'title',
			array(
				'label'   => __( 'عنوان', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'گواهینامه معتبر', 'zarincode' ),
			)
		);

		$rep->add_control(
			'text',
			array(
				'label'   => __( 'توضیحات', 'zarincode' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 3,
				'default' => __( 'پس از اتمام هر دوره، گواهی پایان دوره با قابلیت استعلام آنلاین دریافت می‌کنید.', 'zarincode' ),
			)
		);

		$rep->add_control(
			'link',
			array(
				'label' => __( 'لینک (اختیاری)', 'zarincode' ),
				'type'  => Controls_Manager::URL,
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'لیست ویژگی‌ها', 'zarincode' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array( 'icon' => 'certificate', 'title' => __( 'گواهینامه معتبر', 'zarincode' ), 'text' => __( 'گواهی پایان دوره با قابلیت استعلام آنلاین.', 'zarincode' ) ),
					array( 'icon' => 'headphone', 'title' => __( 'پشتیبانی ۲۴ ساعته', 'zarincode' ), 'text' => __( 'رفع اشکال توسط مدرس در تمام طول دوره.', 'zarincode' ) ),
					array( 'icon' => 'refresh', 'title' => __( 'بروزرسانی رایگان', 'zarincode' ), 'text' => __( 'محتوای دوره‌ها همیشه به‌روز و رایگان برای شما.', 'zarincode' ) ),
					array( 'icon' => 'shield', 'title' => __( 'ضمانت بازگشت وجه', 'zarincode' ), 'text' => __( 'تا ۷ روز پس از خرید، بدون قید و شرط.', 'zarincode' ) ),
				),
			)
		);

		$this->add_control(
			'style_mode',
			array(
				'label'   => __( 'حالت نمایش', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'card',
				'options' => array(
					'card'   => __( 'کارت وسط‌چین', 'zarincode' ),
					'inline' => __( 'آیکن کنار متن', 'zarincode' ),
					'plain'  => __( 'ساده بدون کادر', 'zarincode' ),
				),
			)
		);

		$this->end_controls_section();

		$this->add_layout_controls( 4 );

		$this->start_controls_section(
			'style_section',
			array(
				'label' => __( 'استایل', 'zarincode' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'icon_bg',
			array(
				'label'     => __( 'پس‌زمینه آیکن', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-feature__icon' => 'background:{{VALUE}}' ),
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'رنگ آیکن', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-feature__icon' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_control(
			'icon_size',
			array(
				'label'     => __( 'اندازه کادر آیکن', 'zarincode' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 40, 'max' => 120 ) ),
				'selectors' => array( '{{WRAPPER}} .zc-feature__icon' => 'width:{{SIZE}}px;height:{{SIZE}}px' ),
			)
		);

		$this->add_control(
			'ftitle_color',
			array(
				'label'     => __( 'رنگ عنوان', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-feature__title' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'ftitle_typo',
				'selector' => '{{WRAPPER}} .zc-feature__title',
			)
		);

		$this->add_control(
			'ftext_color',
			array(
				'label'     => __( 'رنگ توضیحات', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-feature__text' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_control(
			'card_bg',
			array(
				'label'     => __( 'پس‌زمینه کارت', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-feature' => 'background:{{VALUE}}' ),
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

		$this->render_heading( $s );

		if ( empty( $s['items'] ) ) {
			return;
		}

		$mode = $s['style_mode'] ?? 'card';
		$this->open_wrapper( $s );

		$i = 0;
		foreach ( $s['items'] as $item ) {
			$tag  = ! empty( $item['link']['url'] ) ? 'a' : 'div';
			$href = ! empty( $item['link']['url'] ) ? ' href="' . esc_url( $item['link']['url'] ) . '"' : '';
			$extra = 'plain' === $mode ? ' style="background:transparent;border:0;padding:14px 6px"' : '';
			$align = 'inline' === $mode ? ' style="text-align:start;display:flex;gap:16px;align-items:flex-start"' : $extra;
			?>
			<<?php echo esc_attr( $tag ) . $href; // phpcs:ignore ?> class="zc-feature <?php echo esc_attr( $this->item_class( $s ) ); ?>"<?php echo $align; // phpcs:ignore ?><?php echo $this->anim_attr( $s, $i ); // phpcs:ignore ?>>
				<span class="zc-feature__icon"<?php echo 'inline' === $mode ? ' style="margin:0;flex-shrink:0"' : ''; ?>>
					<?php zc_the_icon( $item['icon'] ?: 'check', 28 ); ?>
				</span>
				<span>
					<h3 class="zc-feature__title"><?php echo esc_html( $item['title'] ); ?></h3>
					<?php if ( ! empty( $item['text'] ) ) : ?>
						<p class="zc-feature__text"><?php echo esc_html( $item['text'] ); ?></p>
					<?php endif; ?>
				</span>
			</<?php echo esc_attr( $tag ); ?>>
			<?php
			$i++;
		}

		$this->close_wrapper( $s );
	}
}
