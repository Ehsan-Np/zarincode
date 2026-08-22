<?php
/**
 * ویجت جدول قیمت‌گذاری / اشتراک
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * ویجت قیمت.
 */
class ZC_Widget_pricing extends ZC_Widget_Base {

	/** @return string */
	public function get_name() { return 'zc_pricing'; }
	/** @return string */
	public function get_title() { return __( 'زرین کد | جدول اشتراک و قیمت', 'zarincode' ); }
	/** @return string */
	public function get_icon() { return 'eicon-price-table'; }
	/** @return array */
	public function get_categories() { return array( 'zarincode-shop', 'zarincode' ); }

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->add_heading_controls( __( 'پلن‌های <span>اشتراک</span>', 'zarincode' ), __( 'با خرید اشتراک، به تمام دوره‌ها دسترسی نامحدود داشته باشید', 'zarincode' ) );

		$this->start_controls_section( 'items', array( 'label' => __( 'پلن‌ها', 'zarincode' ) ) );

		$rep = new Repeater();
		$rep->add_control( 'title', array( 'label' => __( 'عنوان پلن', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'اشتراک طلایی', 'zarincode' ) ) );
		$rep->add_control( 'sub', array( 'label' => __( 'زیرعنوان', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'مناسب یادگیری حرفه‌ای', 'zarincode' ) ) );
		$rep->add_control( 'price', array( 'label' => __( 'قیمت', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => '۹۹۰,۰۰۰' ) );
		$rep->add_control( 'old_price', array( 'label' => __( 'قیمت قبل', 'zarincode' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'period', array( 'label' => __( 'دوره', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'تومان / سالانه', 'zarincode' ) ) );
		$rep->add_control( 'features', array( 'label' => __( 'ویژگی‌ها (هر خط یک مورد)', 'zarincode' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 6, 'default' => "دسترسی به تمام دوره‌ها\nپشتیبانی اختصاصی مدرس\nگواهی پایان دوره\nبروزرسانی رایگان مادام‌العمر" ) );
		$rep->add_control( 'btn_text', array( 'label' => __( 'متن دکمه', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'خرید اشتراک', 'zarincode' ) ) );
		$rep->add_control( 'btn_link', array( 'label' => __( 'لینک', 'zarincode' ), 'type' => Controls_Manager::URL ) );
		$rep->add_control( 'featured', array( 'label' => __( 'پلن ویژه (برجسته)', 'zarincode' ), 'type' => Controls_Manager::SWITCHER ) );
		$rep->add_control( 'badge', array( 'label' => __( 'برچسب', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'پیشنهاد ویژه', 'zarincode' ) ) );

		$this->add_control(
			'items',
			array(
				'label'       => __( 'لیست پلن‌ها', 'zarincode' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array( 'title' => __( 'برنزی', 'zarincode' ), 'price' => '۲۹۰,۰۰۰', 'period' => __( 'تومان / ۳ ماهه', 'zarincode' ) ),
					array( 'title' => __( 'طلایی', 'zarincode' ), 'price' => '۹۹۰,۰۰۰', 'period' => __( 'تومان / سالانه', 'zarincode' ), 'featured' => 'yes' ),
					array( 'title' => __( 'الماس', 'zarincode' ), 'price' => '۱,۹۹۰,۰۰۰', 'period' => __( 'تومان / مادام‌العمر', 'zarincode' ) ),
				),
			)
		);

		$this->end_controls_section();
		$this->add_layout_controls( 3 );
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

		$this->open_wrapper( $s );

		$i = 0;
		foreach ( $s['items'] as $item ) {
			$feat  = 'yes' === ( $item['featured'] ?? '' );
			$lines = array_filter( array_map( 'trim', explode( "\n", $item['features'] ?? '' ) ) );
			?>
			<div class="zc-plan<?php echo $feat ? ' zc-plan--featured' : ''; ?> <?php echo esc_attr( $this->item_class( $s ) ); ?>"<?php echo $this->anim_attr( $s, $i ); // phpcs:ignore ?>>
				<?php if ( $feat && ! empty( $item['badge'] ) ) : ?>
					<span class="zc-plan__badge"><?php echo esc_html( $item['badge'] ); ?></span>
				<?php endif; ?>
				<h3><?php echo esc_html( $item['title'] ); ?></h3>
				<?php if ( ! empty( $item['sub'] ) ) : ?>
					<p style="font-size:.83rem;opacity:.7;margin-bottom:14px"><?php echo esc_html( $item['sub'] ); ?></p>
				<?php endif; ?>
				<div>
					<?php if ( ! empty( $item['old_price'] ) ) : ?>
						<del style="opacity:.55;font-size:.9rem"><?php echo esc_html( $item['old_price'] ); ?></del>
					<?php endif; ?>
					<span class="zc-plan__price"><?php echo esc_html( $item['price'] ); ?></span>
					<span class="zc-plan__period"><?php echo esc_html( $item['period'] ); ?></span>
				</div>
				<ul class="zc-plan__list">
					<?php foreach ( $lines as $line ) : ?>
						<li><?php zc_the_icon( 'check', 17 ); ?><span><?php echo esc_html( $line ); ?></span></li>
					<?php endforeach; ?>
				</ul>
				<a href="<?php echo esc_url( $item['btn_link']['url'] ?? '#' ); ?>" class="zc-btn zc-btn--<?php echo $feat ? 'gold' : 'outline'; ?> zc-btn--block">
					<?php echo esc_html( $item['btn_text'] ); ?>
				</a>
			</div>
			<?php
			$i++;
		}

		$this->close_wrapper( $s );
	}
}
