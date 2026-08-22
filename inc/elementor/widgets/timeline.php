<?php
/**
 * ویجت تایم‌لاین (مسیر رشد / تاریخچه)
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * ویجت تایم‌لاین.
 */
class ZC_Widget_timeline extends ZC_Widget_Base {

	/** @return string */
	public function get_name() { return 'zc_timeline'; }
	/** @return string */
	public function get_title() { return __( 'زرین کد | تایم‌لاین', 'zarincode' ); }
	/** @return string */
	public function get_icon() { return 'eicon-time-line'; }

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->add_heading_controls( __( 'مسیر <span>زرین کد</span>', 'zarincode' ), '' );

		$this->start_controls_section( 'content', array( 'label' => __( 'رویدادها', 'zarincode' ) ) );

		$rep = new Repeater();
		$rep->add_control( 'year', array( 'label' => __( 'سال / برچسب', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => '۱۳۹۸' ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'شروع فعالیت', 'zarincode' ) ) );
		$rep->add_control( 'text', array( 'label' => __( 'توضیحات', 'zarincode' ), 'type' => Controls_Manager::TEXTAREA ) );
		$rep->add_control( 'icon', array( 'label' => __( 'آیکن', 'zarincode' ), 'type' => Controls_Manager::SELECT, 'default' => 'sparkle', 'options' => zc_get_icons_options() ) );

		$this->add_control(
			'items',
			array(
				'label'       => __( 'لیست', 'zarincode' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ year }}} - {{{ title }}}',
				'default'     => array(
					array( 'year' => '۱۳۹۸', 'title' => __( 'شروع فعالیت', 'zarincode' ), 'icon' => 'sparkle' ),
					array( 'year' => '۱۴۰۰', 'title' => __( 'راه‌اندازی فروشگاه', 'zarincode' ), 'icon' => 'cart' ),
					array( 'year' => '۱۴۰۳', 'title' => __( '۱۰۰۰۰ دانشجو', 'zarincode' ), 'icon' => 'users' ),
				),
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
		?>
		<div class="zc-tl">
			<?php foreach ( $s['items'] as $i => $item ) : ?>
				<div class="zc-tl__item" data-zc-anim="start" data-zc-delay="<?php echo (int) ( $i * 90 ); ?>">
					<span class="zc-tl__dot"><?php zc_the_icon( $item['icon'] ?: 'check', 19 ); ?></span>
					<span class="zc-tl__year"><?php echo esc_html( $item['year'] ); ?></span>
					<h3 style="font-size:1.05rem;margin:0 0 6px"><?php echo esc_html( $item['title'] ); ?></h3>
					<?php if ( ! empty( $item['text'] ) ) : ?>
						<p style="font-size:.87rem;color:var(--zc-muted);margin:0;line-height:2"><?php echo esc_html( $item['text'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
