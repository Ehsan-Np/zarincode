<?php
/**
 * ویجت جعبه تصویر
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * ویجت تصویر باکس.
 */
class ZC_Widget_image_box extends ZC_Widget_Base {

	/** @return string */
	public function get_name() { return 'zc_image_box'; }
	/** @return string */
	public function get_title() { return __( 'زرین کد | جعبه تصویر', 'zarincode' ); }
	/** @return string */
	public function get_icon() { return 'eicon-image-box'; }

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'zarincode' ) ) );
		$this->add_control( 'image', array( 'label' => __( 'تصویر', 'zarincode' ), 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => ZC_ASSETS . 'img/placeholder.svg' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'عنوان', 'zarincode' ) ) );
		$this->add_control( 'text', array( 'label' => __( 'متن', 'zarincode' ), 'type' => Controls_Manager::TEXTAREA ) );
		$this->add_control( 'link', array( 'label' => __( 'لینک', 'zarincode' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'badge', array( 'label' => __( 'برچسب روی تصویر', 'zarincode' ), 'type' => Controls_Manager::TEXT ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style_sec', array( 'label' => __( 'استایل', 'zarincode' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'ratio', array( 'label' => __( 'نسبت تصویر', 'zarincode' ), 'type' => Controls_Manager::SELECT, 'default' => '16/10', 'options' => array( '16/9' => '16:9', '16/10' => '16:10', '4/3' => '4:3', '1/1' => '1:1' ), 'selectors' => array( '{{WRAPPER}} .zc-card__media' => 'aspect-ratio:{{VALUE}}' ) ) );
		$this->add_control( 'radius', array( 'label' => __( 'گردی گوشه', 'zarincode' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 40 ) ), 'selectors' => array( '{{WRAPPER}} .zc-card' => 'border-radius:{{SIZE}}{{UNIT}}' ) ) );
		$this->end_controls_section();
	}

	/**
	 * رندر.
	 *
	 * @return void
	 */
	protected function render() {
		$s   = $this->get_settings_for_display();
		$url = $s['link']['url'] ?? '';
		?>
		<div class="zc-card" data-zc-anim="up">
			<div class="zc-card__media zc-card__media--fab">
				<?php if ( $url ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php endif; ?>
					<img src="<?php echo esc_url( $s['image']['url'] ); ?>" alt="<?php echo esc_attr( $s['title'] ); ?>" loading="lazy" width="600" height="380">
				<?php if ( $url ) : ?></a><?php endif; ?>
				<?php if ( ! empty( $s['badge'] ) ) : ?>
					<span class="zc-badge zc-badge--solid zc-badge--float"><?php echo esc_html( $s['badge'] ); ?></span>
				<?php endif; ?>
				<?php if ( $url ) : ?>
					<a href="<?php echo esc_url( $url ); ?>" class="zc-card__fab"><?php zc_the_icon( 'arrow-ul', 18 ); ?></a>
				<?php endif; ?>
			</div>
			<div class="zc-card__body">
				<h3 class="zc-card__title"><?php if ( $url ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php endif; ?><?php echo esc_html( $s['title'] ); ?><?php if ( $url ) : ?></a><?php endif; ?></h3>
				<?php if ( ! empty( $s['text'] ) ) : ?><p class="zc-card__excerpt"><?php echo esc_html( $s['text'] ); ?></p><?php endif; ?>
			</div>
		</div>
		<?php
	}
}
