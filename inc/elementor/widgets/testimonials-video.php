<?php
/**
 * ویجت تجربه دانشجویان (ویدیو + لیست نظرات) — طبق طرح مرجع
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * ویجت نظرات ویدیویی.
 */
class ZC_Widget_testimonials_video extends ZC_Widget_Base {

	/**
	 * نام.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_testimonials_video';
	}

	/**
	 * عنوان.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | تجربه دانشجویان (ویدیویی)', 'zarincode' );
	}

	/**
	 * آیکن.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-testimonial';
	}

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {

		$this->add_heading_controls(
			__( 'تجربه <span>دانشجویان</span>', 'zarincode' ),
			__( 'به جمع بیش از ۱۲,۰۰۰ دانشجوی موفق زرین کد بپیوندید', 'zarincode' )
		);

		$this->start_controls_section(
			'items_section',
			array( 'label' => __( 'نظرات', 'zarincode' ) )
		);

		$rep = new Repeater();

		$rep->add_control(
			'name',
			array(
				'label'   => __( 'نام', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'محمد مرادی', 'zarincode' ),
			)
		);

		$rep->add_control(
			'role',
			array(
				'label'   => __( 'توضیح کوتاه / سمت', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'استخدام به عنوان توسعه‌دهنده لاراول', 'zarincode' ),
			)
		);

		$rep->add_control(
			'avatar',
			array(
				'label' => __( 'تصویر پروفایل', 'zarincode' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);

		$rep->add_control(
			'poster',
			array(
				'label'       => __( 'تصویر ویدیو (کاور)', 'zarincode' ),
				'type'        => Controls_Manager::MEDIA,
				'description' => __( 'با کلیک روی هر نظر، این تصویر در کادر سمت راست نمایش داده می‌شود.', 'zarincode' ),
			)
		);

		$rep->add_control(
			'video',
			array(
				'label'       => __( 'لینک ویدیو', 'zarincode' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'https://www.aparat.com/video/video/embed/videohash/xxx/vt/frame',
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'لیست نظرات', 'zarincode' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ name }}}',
				'default'     => array(
					array( 'name' => __( 'محمد مرادی', 'zarincode' ), 'role' => __( 'استخدام به عنوان توسعه‌دهنده لاراول', 'zarincode' ) ),
					array( 'name' => __( 'سارا رضایی', 'zarincode' ), 'role' => __( 'شروع فریلنسری با درآمد دلاری', 'zarincode' ) ),
					array( 'name' => __( 'نیما حسینی', 'zarincode' ), 'role' => __( 'راه‌اندازی فروشگاه اینترنتی شخصی', 'zarincode' ) ),
				),
			)
		);

		$this->add_control(
			'main_poster',
			array(
				'label'   => __( 'تصویر پیش‌فرض ویدیو', 'zarincode' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array( 'url' => ZC_ASSETS . 'img/placeholder.svg' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_section',
			array(
				'label' => __( 'استایل', 'zarincode' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'video_radius',
			array(
				'label'     => __( 'گردی گوشه ویدیو', 'zarincode' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'selectors' => array( '{{WRAPPER}} .zc-testi__video' => 'border-radius:{{SIZE}}{{UNIT}}' ),
			)
		);

		$this->add_control(
			'item_bg',
			array(
				'label'     => __( 'پس‌زمینه آیتم‌ها', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-testi__item' => 'background:{{VALUE}}' ),
			)
		);

		$this->add_control(
			'reverse',
			array(
				'label'     => __( 'جابجایی ویدیو و لیست', 'zarincode' ),
				'type'      => Controls_Manager::SWITCHER,
				'selectors' => array( '{{WRAPPER}} .zc-testi__video' => 'order:2' ),
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

		$first  = $s['items'][0];
		$poster = ! empty( $first['poster']['url'] ) ? $first['poster']['url'] : ( $s['main_poster']['url'] ?? ZC_ASSETS . 'img/placeholder.svg' );
		$video  = $first['video'] ?? '';
		?>
		<div class="zc-testi">
			<div class="zc-testi__video" data-zc-anim="start" <?php echo $video ? 'data-zc-video="' . esc_url( $video ) . '"' : ''; ?>>
				<img src="<?php echo esc_url( $poster ); ?>" alt="<?php esc_attr_e( 'ویدیو تجربه دانشجویان', 'zarincode' ); ?>" loading="lazy" width="700" height="440">
				<?php if ( $video ) : ?>
					<div class="zc-testi__play">
						<button class="zc-play-btn zc-play-btn--pulse" data-zc-video="<?php echo esc_url( $video ); ?>" aria-label="<?php esc_attr_e( 'پخش ویدیو', 'zarincode' ); ?>">
							<?php zc_the_icon( 'play', 24 ); ?>
						</button>
					</div>
				<?php endif; ?>
			</div>

			<div class="zc-testi__list" data-zc-anim="end" data-zc-delay="120">
				<?php
				$i = 0;
				foreach ( $s['items'] as $item ) :
					$avatar = $item['avatar']['url'] ?? '';
					?>
					<div class="zc-testi__item<?php echo 0 === $i ? ' is-active' : ''; ?>"
						data-image="<?php echo esc_url( $item['poster']['url'] ?? $poster ); ?>"
						data-video="<?php echo esc_url( $item['video'] ?? '' ); ?>"
						role="button" tabindex="0">
						<span class="zc-testi__btn"><?php zc_the_icon( 'play', 18 ); ?></span>
						<span class="zc-testi__info">
							<span class="zc-testi__name"><?php echo esc_html( $item['name'] ); ?></span>
							<span class="zc-testi__role"><?php echo esc_html( $item['role'] ); ?></span>
						</span>
						<?php if ( $avatar ) : ?>
							<span class="zc-avatar zc-avatar--md"><img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" loading="lazy" width="48" height="48"></span>
						<?php else : ?>
							<span class="zc-avatar zc-avatar--md" style="display:flex;align-items:center;justify-content:center;background:var(--zc-gold-soft);color:var(--zc-gold-3)"><?php zc_the_icon( 'user', 22 ); ?></span>
						<?php endif; ?>
					</div>
					<?php
					$i++;
				endforeach;
				?>
			</div>
		</div>
		<?php
	}
}
