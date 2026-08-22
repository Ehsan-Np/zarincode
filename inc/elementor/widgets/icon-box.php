<?php
/**
 * ویجت جعبه آیکن تکی
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

/**
 * ویجت آیکن باکس.
 */
class ZC_Widget_icon_box extends ZC_Widget_Base {

	/** @return string */
	public function get_name() { return 'zc_icon_box'; }
	/** @return string */
	public function get_title() { return __( 'زرین کد | جعبه آیکن', 'zarincode' ); }
	/** @return string */
	public function get_icon() { return 'eicon-icon-box'; }

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'zarincode' ) ) );

		$this->add_control( 'icon', array( 'label' => __( 'آیکن', 'zarincode' ), 'type' => Controls_Manager::SELECT, 'default' => 'code', 'options' => zc_get_icons_options() ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'عنوان ویژگی', 'zarincode' ) ) );
		$this->add_control( 'text', array( 'label' => __( 'متن', 'zarincode' ), 'type' => Controls_Manager::TEXTAREA, 'default' => __( 'توضیحات کوتاه درباره این ویژگی.', 'zarincode' ) ) );
		$this->add_control( 'link', array( 'label' => __( 'لینک', 'zarincode' ), 'type' => Controls_Manager::URL ) );
		$this->add_control(
			'position',
			array(
				'label'   => __( 'موقعیت آیکن', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'top',
				'options' => array( 'top' => __( 'بالا', 'zarincode' ), 'side' => __( 'کنار', 'zarincode' ) ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'style_sec', array( 'label' => __( 'استایل', 'zarincode' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'icon_bg', array( 'label' => __( 'پس‌زمینه آیکن', 'zarincode' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .zc-feature__icon' => 'background:{{VALUE}}' ) ) );
		$this->add_control( 'icon_color', array( 'label' => __( 'رنگ آیکن', 'zarincode' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .zc-feature__icon' => 'color:{{VALUE}}' ) ) );
		$this->add_control( 'box_bg', array( 'label' => __( 'پس‌زمینه باکس', 'zarincode' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .zc-feature' => 'background:{{VALUE}}' ) ) );
		$this->add_control( 'title_color', array( 'label' => __( 'رنگ عنوان', 'zarincode' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .zc-feature__title' => 'color:{{VALUE}}' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'title_typo', 'selector' => '{{WRAPPER}} .zc-feature__title' ) );
		$this->add_control( 'text_color', array( 'label' => __( 'رنگ متن', 'zarincode' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .zc-feature__text' => 'color:{{VALUE}}' ) ) );
		$this->end_controls_section();
	}

	/**
	 * رندر.
	 *
	 * @return void
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$tag   = ! empty( $s['link']['url'] ) ? 'a' : 'div';
		$href  = ! empty( $s['link']['url'] ) ? ' href="' . esc_url( $s['link']['url'] ) . '"' : '';
		$style = 'side' === $s['position'] ? ' style="text-align:start;display:flex;gap:16px;align-items:flex-start"' : '';
		?>
		<<?php echo esc_attr( $tag ) . $href; // phpcs:ignore ?> class="zc-feature"<?php echo $style; // phpcs:ignore ?> data-zc-anim="up">
			<span class="zc-feature__icon"<?php echo 'side' === $s['position'] ? ' style="margin:0;flex-shrink:0"' : ''; ?>><?php zc_the_icon( $s['icon'], 28 ); ?></span>
			<span>
				<h3 class="zc-feature__title"><?php echo esc_html( $s['title'] ); ?></h3>
				<?php if ( ! empty( $s['text'] ) ) : ?><p class="zc-feature__text"><?php echo esc_html( $s['text'] ); ?></p><?php endif; ?>
			</span>
		</<?php echo esc_attr( $tag ); ?>>
		<?php
	}
}
