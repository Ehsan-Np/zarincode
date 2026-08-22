<?php
/**
 * ویجت باکس ویدیو با لایت‌باکس
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * ویجت ویدیو.
 */
class ZC_Widget_video_box extends ZC_Widget_Base {

	/** @return string */
	public function get_name() { return 'zc_video_box'; }
	/** @return string */
	public function get_title() { return __( 'زرین کد | ویدیو', 'zarincode' ); }
	/** @return string */
	public function get_icon() { return 'eicon-play'; }

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'zarincode' ) ) );
		$this->add_control( 'poster', array( 'label' => __( 'تصویر کاور', 'zarincode' ), 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => ZC_ASSETS . 'img/placeholder.svg' ) ) );
		$this->add_control(
			'video_url',
			array(
				'label'       => __( 'لینک ویدیو', 'zarincode' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'آدرس آپارات، یوتیوب یا فایل mp4', 'zarincode' ),
				'label_block' => true,
			)
		);
		$this->add_control( 'title', array( 'label' => __( 'عنوان روی ویدیو', 'zarincode' ), 'type' => Controls_Manager::TEXT ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style_sec', array( 'label' => __( 'استایل', 'zarincode' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'radius', array( 'label' => __( 'گردی گوشه', 'zarincode' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 40 ) ), 'selectors' => array( '{{WRAPPER}} .zc-testi__video' => 'border-radius:{{SIZE}}{{UNIT}}' ) ) );
		$this->add_control( 'ratio', array( 'label' => __( 'نسبت', 'zarincode' ), 'type' => Controls_Manager::SELECT, 'default' => '16/9', 'options' => array( '16/9' => '16:9', '4/3' => '4:3', '21/9' => '21:9' ), 'selectors' => array( '{{WRAPPER}} .zc-testi__video' => 'aspect-ratio:{{VALUE}}' ) ) );
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
		<div class="zc-testi__video" data-zc-anim="zoom">
			<img src="<?php echo esc_url( $s['poster']['url'] ); ?>" alt="<?php echo esc_attr( $s['title'] ?? '' ); ?>" loading="lazy" width="900" height="500">
			<?php if ( ! empty( $s['video_url'] ) ) : ?>
				<div class="zc-testi__play">
					<button class="zc-play-btn zc-play-btn--pulse" data-zc-video="<?php echo esc_url( $s['video_url'] ); ?>" aria-label="<?php esc_attr_e( 'پخش ویدیو', 'zarincode' ); ?>">
						<?php zc_the_icon( 'play', 26 ); ?>
					</button>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $s['title'] ) ) : ?>
				<div style="position:absolute;bottom:18px;inset-inline-start:20px;z-index:3;color:#fff;font-weight:700;font-size:1.05rem;text-shadow:0 2px 10px rgba(0,0,0,.5)">
					<?php echo esc_html( $s['title'] ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
