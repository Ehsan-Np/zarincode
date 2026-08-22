<?php
/**
 * ویجت تب‌ها
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * ویجت تب.
 */
class ZC_Widget_tabs extends ZC_Widget_Base {

	/** @return string */
	public function get_name() { return 'zc_tabs'; }
	/** @return string */
	public function get_title() { return __( 'زرین کد | تب‌ها', 'zarincode' ); }
	/** @return string */
	public function get_icon() { return 'eicon-tabs'; }

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'تب‌ها', 'zarincode' ) ) );

		$rep = new Repeater();
		$rep->add_control( 'title', array( 'label' => __( 'عنوان تب', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'تب جدید', 'zarincode' ) ) );
		$rep->add_control( 'icon', array( 'label' => __( 'آیکن', 'zarincode' ), 'type' => Controls_Manager::SELECT, 'default' => '', 'options' => array_merge( array( '' => __( 'بدون آیکن', 'zarincode' ) ), zc_get_icons_options() ) ) );
		$rep->add_control( 'content', array( 'label' => __( 'محتوا', 'zarincode' ), 'type' => Controls_Manager::WYSIWYG, 'default' => __( 'محتوای این تب را وارد کنید.', 'zarincode' ) ) );

		$this->add_control(
			'items',
			array(
				'label'       => __( 'لیست تب‌ها', 'zarincode' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array( 'title' => __( 'معرفی', 'zarincode' ), 'icon' => 'info' ),
					array( 'title' => __( 'سرفصل‌ها', 'zarincode' ), 'icon' => 'book' ),
					array( 'title' => __( 'نظرات', 'zarincode' ), 'icon' => 'chat' ),
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'style_sec', array( 'label' => __( 'استایل', 'zarincode' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'active_color', array( 'label' => __( 'رنگ تب فعال', 'zarincode' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .zc-tabs__btn.is-active' => 'color:{{VALUE}};border-color:{{VALUE}}' ) ) );
		$this->add_control( 'tab_color', array( 'label' => __( 'رنگ تب‌ها', 'zarincode' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .zc-tabs__btn' => 'color:{{VALUE}}' ) ) );
		$this->end_controls_section();
	}

	/**
	 * رندر.
	 *
	 * @return void
	 */
	protected function render() {
		$s = $this->get_settings_for_display();
		if ( empty( $s['items'] ) ) {
			return;
		}
		$uid = 'zct' . $this->get_id();
		?>
		<div class="zc-tabs" data-zc-anim="up">
			<div class="zc-tabs__nav" role="tablist">
				<?php foreach ( $s['items'] as $i => $item ) : ?>
					<button type="button" class="zc-tabs__btn<?php echo 0 === $i ? ' is-active' : ''; ?>"
						data-tab="<?php echo esc_attr( $uid . $i ); ?>" role="tab"
						aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>">
						<?php if ( ! empty( $item['icon'] ) ) { zc_the_icon( $item['icon'], 17 ); } ?>
						<?php echo esc_html( $item['title'] ); ?>
					</button>
				<?php endforeach; ?>
			</div>
			<?php foreach ( $s['items'] as $i => $item ) : ?>
				<div class="zc-tabs__pane<?php echo 0 === $i ? ' is-active' : ''; ?>" data-pane="<?php echo esc_attr( $uid . $i ); ?>" role="tabpanel">
					<?php echo wp_kses_post( $item['content'] ); ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
