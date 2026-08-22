<?php
/**
 * ویجت بخش تیره با جستجوی پیشرفته دوره (طبق بخش تیره طرح مرجع)
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Background;

/**
 * ویجت بخش تیره.
 */
class ZC_Widget_dark_search extends ZC_Widget_Base {

	/**
	 * نام.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_dark_search';
	}

	/**
	 * عنوان.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | بخش تیره + جستجوی دوره', 'zarincode' );
	}

	/**
	 * آیکن.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-search-bold';
	}

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			array( 'label' => __( 'محتوا', 'zarincode' ) )
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'عنوان', 'zarincode' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'default' => __( 'دوره مناسب <span>خودت</span> رو پیدا کن', 'zarincode' ),
			)
		);

		$this->add_control(
			'sub',
			array(
				'label'   => __( 'زیرعنوان', 'zarincode' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'default' => __( 'با فیلترهای هوشمند، سریع‌ترین مسیر یادگیری خود را انتخاب کنید', 'zarincode' ),
			)
		);

		$this->add_control(
			'show_finder',
			array(
				'label'   => __( 'نمایش فرم جستجو', 'zarincode' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'btn_text',
			array(
				'label'     => __( 'متن دکمه جستجو', 'zarincode' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'جستجوی دوره', 'zarincode' ),
				'condition' => array( 'show_finder' => 'yes' ),
			)
		);

		$this->end_controls_section();

		/* کارت‌های تکنولوژی */
		$this->start_controls_section(
			'cards_section',
			array( 'label' => __( 'کارت‌های تکنولوژی', 'zarincode' ) )
		);

		$rep = new Repeater();

		$rep->add_control(
			'icon',
			array(
				'label'   => __( 'آیکن', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'code',
				'options' => zc_get_icons_options(),
			)
		);

		$rep->add_control(
			'custom_image',
			array(
				'label'       => __( 'یا تصویر/لوگو دلخواه', 'zarincode' ),
				'type'        => Controls_Manager::MEDIA,
				'description' => __( 'در صورت انتخاب، جایگزین آیکن می‌شود.', 'zarincode' ),
			)
		);

		$rep->add_control(
			'title_en',
			array(
				'label'   => __( 'عنوان انگلیسی', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'PHP',
			)
		);

		$rep->add_control(
			'title_fa',
			array(
				'label'   => __( 'عنوان فارسی', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'پی اچ پی', 'zarincode' ),
			)
		);

		$rep->add_control(
			'link',
			array(
				'label'   => __( 'لینک', 'zarincode' ),
				'type'    => Controls_Manager::URL,
				'default' => array( 'url' => '#' ),
			)
		);

		$this->add_control(
			'cards',
			array(
				'label'       => __( 'کارت‌ها', 'zarincode' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ title_en }}}',
				'default'     => array(
					array( 'icon' => 'code', 'title_en' => 'PHP / Laravel', 'title_fa' => __( 'پی‌اچ‌پی و لاراول', 'zarincode' ) ),
					array( 'icon' => 'grid', 'title_en' => 'JavaScript', 'title_fa' => __( 'جاوااسکریپت', 'zarincode' ) ),
					array( 'icon' => 'plugin', 'title_en' => 'WordPress', 'title_fa' => __( 'وردپرس', 'zarincode' ) ),
					array( 'icon' => 'chart', 'title_en' => 'Python', 'title_fa' => __( 'پایتون', 'zarincode' ) ),
				),
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

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'sec_bg',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .zc-darksec',
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'رنگ عنوان', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-heading__title' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_control(
			'radius',
			array(
				'label'     => __( 'گردی گوشه بخش', 'zarincode' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors' => array( '{{WRAPPER}} .zc-darksec' => 'border-radius:{{SIZE}}{{UNIT}}' ),
			)
		);

		$this->add_responsive_control(
			'cards_columns',
			array(
				'label'          => __( 'ستون کارت‌ها', 'zarincode' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '4',
				'mobile_default' => '1',
				'options'        => array( '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶' ),
				'selectors'      => array( '{{WRAPPER}} .zc-flags' => 'grid-template-columns:repeat({{VALUE}},minmax(0,1fr))' ),
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
		$s       = $this->get_settings_for_display();
		$cats    = get_terms( array( 'taxonomy' => 'zc_course_cat', 'hide_empty' => false, 'number' => 40 ) );
		$archive = get_post_type_archive_link( 'zc_course' );
		?>
		<section class="zc-darksec">
			<div class="zc-container zc-darksec__in">

				<div class="zc-heading zc-heading--light" data-zc-anim="up">
					<div class="zc-heading__arrow"><?php zc_the_icon( 'chevron', 30 ); ?></div>
					<?php if ( ! empty( $s['title'] ) ) : ?>
						<h2 class="zc-heading__title" style="color:#fff"><?php echo wp_kses_post( $s['title'] ); ?></h2>
					<?php endif; ?>
					<?php if ( ! empty( $s['sub'] ) ) : ?>
						<p class="zc-heading__sub" style="color:rgba(255,255,255,.7)"><?php echo esc_html( $s['sub'] ); ?></p>
					<?php endif; ?>
				</div>

				<?php if ( 'yes' === $s['show_finder'] ) : ?>
				<form class="zc-finder" method="get" action="<?php echo esc_url( $archive ? $archive : home_url( '/' ) ); ?>" data-zc-anim="up" data-zc-delay="100">
					<div class="zc-finder__field">
						<input type="search" name="s" placeholder="<?php esc_attr_e( 'نام دوره یا تکنولوژی…', 'zarincode' ); ?>" aria-label="<?php esc_attr_e( 'جستجو', 'zarincode' ); ?>">
					</div>
					<div class="zc-finder__field">
						<select name="zc_course_cat" aria-label="<?php esc_attr_e( 'دسته‌بندی', 'zarincode' ); ?>">
							<option value=""><?php esc_html_e( 'همه دسته‌بندی‌ها', 'zarincode' ); ?></option>
							<?php
							if ( ! is_wp_error( $cats ) ) {
								foreach ( $cats as $cat ) {
									printf( '<option value="%s">%s</option>', esc_attr( $cat->slug ), esc_html( $cat->name ) );
								}
							}
							?>
						</select>
					</div>
					<div class="zc-finder__field">
						<select name="level" aria-label="<?php esc_attr_e( 'سطح', 'zarincode' ); ?>">
							<option value=""><?php esc_html_e( 'همه سطوح', 'zarincode' ); ?></option>
							<option value="beginner"><?php esc_html_e( 'مقدماتی', 'zarincode' ); ?></option>
							<option value="intermediate"><?php esc_html_e( 'متوسط', 'zarincode' ); ?></option>
							<option value="advanced"><?php esc_html_e( 'پیشرفته', 'zarincode' ); ?></option>
						</select>
					</div>
					<input type="hidden" name="post_type" value="zc_course">
					<button type="submit" class="zc-btn zc-btn--gold">
						<?php zc_the_icon( 'search', 18 ); ?>
						<?php echo esc_html( $s['btn_text'] ?: __( 'جستجو', 'zarincode' ) ); ?>
					</button>
				</form>
				<?php endif; ?>

				<?php if ( ! empty( $s['cards'] ) ) : ?>
				<div class="zc-flags">
					<?php
					$i = 0;
					foreach ( $s['cards'] as $card ) :
						$i++;
						?>
						<a href="<?php echo esc_url( $card['link']['url'] ?? '#' ); ?>" class="zc-flag" data-zc-anim="up" data-zc-delay="<?php echo (int) ( $i * 70 ); ?>">
							<span class="zc-flag__icon">
								<?php if ( ! empty( $card['custom_image']['url'] ) ) : ?>
									<img src="<?php echo esc_url( $card['custom_image']['url'] ); ?>" alt="<?php echo esc_attr( $card['title_en'] ); ?>" width="26" height="26" loading="lazy" style="border-radius:6px">
								<?php else : ?>
									<?php zc_the_icon( $card['icon'] ?: 'code', 22 ); ?>
								<?php endif; ?>
							</span>
							<span class="zc-flag__en"><?php echo esc_html( $card['title_en'] ); ?></span>
							<span class="zc-flag__fa"><?php echo esc_html( $card['title_fa'] ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>

			</div>
		</section>
		<?php
	}
}
