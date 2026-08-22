<?php
/**
 * کلاس پایه ویجت‌های زرین کد
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;

/**
 * کلاس پایه.
 */
abstract class ZC_Widget_Base extends Widget_Base {

	/**
	 * دسته‌بندی ویجت.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'zarincode' );
	}

	/**
	 * کلمات کلیدی.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'zarincode', 'زرین کد', 'قالب' );
	}

	/**
	 * اسکریپت‌های موردنیاز.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return array( 'zc-main' );
	}

	/**
	 * استایل‌های موردنیاز.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return array( 'zc-main' );
	}

	/**
	 * افزودن کنترل‌های سربرگ بخش (عنوان + زیرعنوان + فلش).
	 *
	 * @param string $default_title عنوان پیش‌فرض.
	 * @param string $default_sub   زیرعنوان.
	 * @return void
	 */
	protected function add_heading_controls( $default_title = '', $default_sub = '' ) {
		$this->start_controls_section(
			'zc_heading_section',
			array( 'label' => __( 'سربرگ بخش', 'zarincode' ) )
		);

		$this->add_control(
			'show_heading',
			array(
				'label'        => __( 'نمایش سربرگ', 'zarincode' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'بله', 'zarincode' ),
				'label_off'    => __( 'خیر', 'zarincode' ),
			)
		);

		$this->add_control(
			'show_arrow',
			array(
				'label'     => __( 'نمایش فلش بالای عنوان', 'zarincode' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array( 'show_heading' => 'yes' ),
			)
		);

		$this->add_control(
			'heading_title',
			array(
				'label'       => __( 'عنوان', 'zarincode' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => $default_title,
				'placeholder' => __( 'برای رنگی شدن بخشی از متن، آن را داخل <span></span> بگذارید', 'zarincode' ),
				'condition'   => array( 'show_heading' => 'yes' ),
			)
		);

		$this->add_control(
			'heading_sub',
			array(
				'label'     => __( 'زیرعنوان', 'zarincode' ),
				'type'      => Controls_Manager::TEXTAREA,
				'rows'      => 2,
				'default'   => $default_sub,
				'condition' => array( 'show_heading' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'heading_align',
			array(
				'label'     => __( 'چیدمان', 'zarincode' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'start'  => array( 'title' => __( 'راست', 'zarincode' ), 'icon' => 'eicon-text-align-right' ),
					'center' => array( 'title' => __( 'وسط', 'zarincode' ), 'icon' => 'eicon-text-align-center' ),
					'end'    => array( 'title' => __( 'چپ', 'zarincode' ), 'icon' => 'eicon-text-align-left' ),
				),
				'default'   => 'center',
				'selectors' => array( '{{WRAPPER}} .zc-heading' => 'text-align:{{VALUE}}' ),
				'condition' => array( 'show_heading' => 'yes' ),
			)
		);

		$this->end_controls_section();

		// استایل سربرگ.
		$this->start_controls_section(
			'zc_heading_style',
			array(
				'label'     => __( 'استایل سربرگ', 'zarincode' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_heading' => 'yes' ),
			)
		);

		$this->add_control(
			'heading_color',
			array(
				'label'     => __( 'رنگ عنوان', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-heading__title' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_control(
			'heading_accent',
			array(
				'label'     => __( 'رنگ بخش تاکیدی (span)', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-heading__title span' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'heading_typo',
				'selector' => '{{WRAPPER}} .zc-heading__title',
			)
		);

		$this->add_control(
			'sub_color',
			array(
				'label'     => __( 'رنگ زیرعنوان', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-heading__sub' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_responsive_control(
			'heading_gap',
			array(
				'label'      => __( 'فاصله تا محتوا', 'zarincode' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 120 ) ),
				'selectors'  => array( '{{WRAPPER}} .zc-heading' => 'margin-bottom:{{SIZE}}{{UNIT}}' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * رندر سربرگ بخش.
	 *
	 * @param array $settings تنظیمات.
	 * @return void
	 */
	protected function render_heading( $settings ) {
		if ( 'yes' !== ( $settings['show_heading'] ?? 'yes' ) || empty( $settings['heading_title'] ) ) {
			return;
		}

		$align = $settings['heading_align'] ?? 'center';
		echo '<div class="zc-heading' . ( 'center' !== $align ? ' zc-heading--start' : '' ) . '" data-zc-anim="up">';

		if ( 'yes' === ( $settings['show_arrow'] ?? 'yes' ) ) {
			// کلاس مستقیماً روی خود آیکن می‌نشیند تا یک <div> اضافه حذف شود.
			echo zc_icon( 'chevron', 30, 'zc-heading__arrow' ); // phpcs:ignore
		}

		echo '<h2 class="zc-heading__title">' . wp_kses_post( $settings['heading_title'] ) . '</h2>';

		if ( ! empty( $settings['heading_sub'] ) ) {
			echo '<p class="zc-heading__sub">' . esc_html( $settings['heading_sub'] ) . '</p>';
		}

		echo '</div>';
	}

	/**
	 * کنترل‌های کوئری (برای ویجت‌های لیستی).
	 *
	 * @param string $post_type نوع پست.
	 * @param string $taxonomy  طبقه‌بندی.
	 * @return void
	 */
	protected function add_query_controls( $post_type = 'post', $taxonomy = 'category' ) {
		$this->start_controls_section(
			'zc_query_section',
			array( 'label' => __( 'تنظیمات نمایش محتوا', 'zarincode' ) )
		);

		$this->add_control(
			'posts_count',
			array(
				'label'   => __( 'تعداد نمایش', 'zarincode' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 8,
				'min'     => 1,
				'max'     => 30,
			)
		);

		$this->add_control(
			'query_cats',
			array(
				'label'       => __( 'فیلتر بر اساس دسته‌بندی', 'zarincode' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => zc_get_terms_options( $taxonomy ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'مرتب‌سازی بر اساس', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => array(
					'date'          => __( 'تاریخ انتشار', 'zarincode' ),
					'title'         => __( 'عنوان', 'zarincode' ),
					'rand'          => __( 'تصادفی', 'zarincode' ),
					'comment_count' => __( 'تعداد دیدگاه', 'zarincode' ),
					'menu_order'    => __( 'ترتیب دستی', 'zarincode' ),
					'zc_views'      => __( 'پربازدیدترین', 'zarincode' ),
					'zc_students'   => __( 'پرفروش‌ترین', 'zarincode' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => __( 'ترتیب', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => array(
					'DESC' => __( 'نزولی', 'zarincode' ),
					'ASC'  => __( 'صعودی', 'zarincode' ),
				),
			)
		);

		$this->add_control(
			'specific_posts',
			array(
				'label'       => __( 'یا انتخاب دستی موارد', 'zarincode' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => zc_get_posts_options( $post_type ),
				'label_block' => true,
				'description' => __( 'در صورت انتخاب، فیلترهای بالا نادیده گرفته می‌شوند.', 'zarincode' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * کنترل‌های چیدمان شبکه‌ای.
	 *
	 * @param int $default ستون پیش‌فرض.
	 * @return void
	 */
	protected function add_layout_controls( $default = 4 ) {
		$this->start_controls_section(
			'zc_layout_section',
			array( 'label' => __( 'چیدمان', 'zarincode' ) )
		);

		$this->add_control(
			'layout_mode',
			array(
				'label'   => __( 'حالت نمایش', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => array(
					'grid'   => __( 'شبکه‌ای', 'zarincode' ),
					'slider' => __( 'اسلایدر', 'zarincode' ),
				),
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'تعداد ستون', 'zarincode' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => (string) $default,
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array(
					'1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶',
				),
				'selectors'      => array(
					'{{WRAPPER}} .zc-grid' => 'grid-template-columns:repeat({{VALUE}},minmax(0,1fr))',
				),
			)
		);

		$this->add_responsive_control(
			'grid_gap',
			array(
				'label'     => __( 'فاصله بین آیتم‌ها', 'zarincode' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 70 ) ),
				'default'   => array( 'size' => 24 ),
				'selectors' => array(
					'{{WRAPPER}} .zc-grid'          => 'gap:{{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .zc-slider__track' => 'gap:{{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_control(
			'slides_per_view',
			array(
				'label'     => __( 'تعداد نمایش در اسلایدر', 'zarincode' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => $default,
				'min'       => 1,
				'max'       => 6,
				'condition' => array( 'layout_mode' => 'slider' ),
			)
		);

		$this->add_control(
			'slider_autoplay',
			array(
				'label'     => __( 'پخش خودکار', 'zarincode' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array( 'layout_mode' => 'slider' ),
			)
		);

		$this->add_control(
			'slider_arrows',
			array(
				'label'     => __( 'نمایش فلش‌ها', 'zarincode' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array( 'layout_mode' => 'slider' ),
			)
		);

		$this->add_control(
			'slider_dots',
			array(
				'label'     => __( 'نمایش نقطه‌ها', 'zarincode' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array( 'layout_mode' => 'slider' ),
			)
		);

		$this->add_control(
			'anim_enable',
			array(
				'label'     => __( 'انیمیشن ورود آیتم‌ها', 'zarincode' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'anim_style',
			array(
				'label'     => __( 'حالت انیمیشن', 'zarincode' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'rise',
				'options'   => array(
					'rise'    => __( 'برخاستن سه‌بعدی', 'zarincode' ),
					'up'      => __( 'حرکت از پایین', 'zarincode' ),
					'down'    => __( 'حرکت از بالا', 'zarincode' ),
					'start'   => __( 'حرکت از راست', 'zarincode' ),
					'end'     => __( 'حرکت از چپ', 'zarincode' ),
					'pop'     => __( 'بزرگ‌نمایی نرم', 'zarincode' ),
					'zoom'    => __( 'زوم', 'zarincode' ),
					'depth'   => __( 'ورود از عمق', 'zarincode' ),
					'flip'    => __( 'چرخش ورقه‌ای', 'zarincode' ),
					'blur'    => __( 'محو تدریجی', 'zarincode' ),
					'curtain' => __( 'پرده‌ی بالارونده', 'zarincode' ),
				),
				'condition' => array( 'anim_enable' => 'yes' ),
			)
		);

		$this->add_control(
			'anim_stagger',
			array(
				'label'       => __( 'فاصله‌ی پلکانی (میلی‌ثانیه)', 'zarincode' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 70,
				'min'         => 0,
				'max'         => 250,
				'step'        => 10,
				'description' => __( 'تأخیر ظاهر شدن هر آیتم نسبت به آیتم قبلی.', 'zarincode' ),
				'condition'   => array( 'anim_enable' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * کنترل‌های دکمه بیشتر.
	 *
	 * @return void
	 */
	protected function add_more_button_controls() {
		$this->start_controls_section(
			'zc_more_section',
			array( 'label' => __( 'دکمه پایانی', 'zarincode' ) )
		);

		$this->add_control(
			'show_more',
			array(
				'label'   => __( 'نمایش دکمه', 'zarincode' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'more_text',
			array(
				'label'     => __( 'متن دکمه', 'zarincode' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'مشاهده همه', 'zarincode' ),
				'condition' => array( 'show_more' => 'yes' ),
			)
		);

		$this->add_control(
			'more_link',
			array(
				'label'       => __( 'لینک دکمه', 'zarincode' ),
				'type'        => Controls_Manager::URL,
				'default'     => array( 'url' => '#' ),
				'condition'   => array( 'show_more' => 'yes' ),
			)
		);

		$this->add_control(
			'more_style',
			array(
				'label'     => __( 'استایل دکمه', 'zarincode' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'gold',
				'options'   => array(
					'gold'    => __( 'طلایی', 'zarincode' ),
					'dark'    => __( 'تیره', 'zarincode' ),
					'navy'    => __( 'سرمه‌ای', 'zarincode' ),
					'outline' => __( 'خطی', 'zarincode' ),
					'ghost'   => __( 'محو', 'zarincode' ),
				),
				'condition' => array( 'show_more' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * رندر دکمه بیشتر.
	 *
	 * @param array $settings تنظیمات.
	 * @return void
	 */
	protected function render_more_button( $settings ) {
		if ( 'yes' !== ( $settings['show_more'] ?? '' ) || empty( $settings['more_text'] ) ) {
			return;
		}

		$url    = $settings['more_link']['url'] ?? '#';
		$target = ! empty( $settings['more_link']['is_external'] ) ? ' target="_blank"' : '';
		$rel    = ! empty( $settings['more_link']['nofollow'] ) ? ' rel="nofollow"' : '';

		printf(
			'<div style="text-align:center;margin-top:36px" data-zc-anim="up"><a href="%1$s"%2$s%3$s class="zc-btn zc-btn--%4$s">%5$s %6$s</a></div>',
			esc_url( $url ),
			$target, // phpcs:ignore
			$rel, // phpcs:ignore
			esc_attr( $settings['more_style'] ?? 'gold' ),
			esc_html( $settings['more_text'] ),
			zc_icon( 'arrow-left', 18 ) // phpcs:ignore
		);
	}

	/**
	 * ساخت کوئری بر اساس تنظیمات.
	 *
	 * @param array  $settings  تنظیمات.
	 * @param string $post_type نوع پست.
	 * @param string $taxonomy  طبقه‌بندی.
	 * @return WP_Query
	 */
	protected function build_query( $settings, $post_type = 'post', $taxonomy = 'category' ) {
		$args = array(
			'post_type'           => $post_type,
			'posts_per_page'      => (int) ( $settings['posts_count'] ?? 8 ),
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		);

		if ( ! empty( $settings['specific_posts'] ) ) {
			$args['post__in'] = array_map( 'intval', (array) $settings['specific_posts'] );
			$args['orderby']  = 'post__in';
			return new WP_Query( $args );
		}

		if ( ! empty( $settings['query_cats'] ) ) {
			$args['tax_query'] = array( // phpcs:ignore
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => array_map( 'intval', (array) $settings['query_cats'] ),
				),
			);
		}

		$orderby = $settings['orderby'] ?? 'date';
		if ( in_array( $orderby, array( 'zc_views', 'zc_students' ), true ) ) {
			// کلید متای بازدید بدون پیشوند و کلید دانشجو با پیشوند ذخیره می‌شود.
			$args['meta_key'] = 'zc_views' === $orderby ? 'zc_views' : '_zc_students'; // phpcs:ignore
			$args['orderby']  = 'meta_value_num';
		} else {
			$args['orderby'] = $orderby;
		}
		$args['order'] = $settings['order'] ?? 'DESC';

		return new WP_Query( $args );
	}

	/**
	 * باز کردن رپر شبکه/اسلایدر.
	 *
	 * @param array $settings تنظیمات.
	 * @return void
	 */
	protected function open_wrapper( $settings ) {
		if ( 'slider' === ( $settings['layout_mode'] ?? 'grid' ) ) {
			$per = (int) ( $settings['slides_per_view'] ?? 4 );
			printf(
				'<div class="zc-slider" data-autoplay="%1$s" data-interval="5000"><div class="zc-slider__track" style="--zc-per:%2$d">',
				esc_attr( 'yes' === ( $settings['slider_autoplay'] ?? '' ) ? 'yes' : 'no' ),
				$per
			);
		} else {
			/*
			 * کارت‌ها خودشان data-zc-anim با تأخیر پلکانی دارند، پس
			 * data-zc-stagger روی شبکه اضافه نمی‌شود؛ در غیر این صورت
			 * هر کارت دو بار پنهان می‌شد و ظاهر شدنش به هم می‌ریخت.
			 */
			echo '<div class="zc-grid zc-grid--' . esc_attr( $settings['columns'] ?? 4 ) . '">';
		}
	}

	/**
	 * بستن رپر.
	 *
	 * @param array $settings تنظیمات.
	 * @return void
	 */
	protected function close_wrapper( $settings ) {
		if ( 'slider' === ( $settings['layout_mode'] ?? 'grid' ) ) {
			echo '</div>';
			echo '<div class="zc-slider__nav">';
			if ( 'yes' === ( $settings['slider_arrows'] ?? 'yes' ) ) {
				echo '<button class="zc-slider__arrow" data-zc-slide="prev" aria-label="' . esc_attr__( 'قبلی', 'zarincode' ) . '">' . zc_icon( 'arrow-left', 20 ) . '</button>'; // phpcs:ignore
			}
			if ( 'yes' === ( $settings['slider_dots'] ?? 'yes' ) ) {
				echo '<div class="zc-dots"></div>';
			}
			if ( 'yes' === ( $settings['slider_arrows'] ?? 'yes' ) ) {
				echo '<button class="zc-slider__arrow" data-zc-slide="next" aria-label="' . esc_attr__( 'بعدی', 'zarincode' ) . '" style="transform:rotate(180deg)">' . zc_icon( 'arrow-left', 20 ) . '</button>'; // phpcs:ignore
			}
			echo '</div></div>';
		} else {
			echo '</div>';
		}
	}

	/**
	 * کلاس آیتم (اسلاید یا شبکه).
	 *
	 * @param array $settings تنظیمات.
	 * @return string
	 */
	protected function item_class( $settings ) {
		if ( 'slider' === ( $settings['layout_mode'] ?? 'grid' ) ) {
			$per = (int) ( $settings['slides_per_view'] ?? 4 );
			return 'zc-slider__slide" style="width:calc((100% - ' . ( ( $per - 1 ) * 24 ) . 'px)/' . $per . ')';
		}
		return '';
	}

	/**
	 * ویژگی انیمیشن.
	 *
	 * @param array $settings تنظیمات.
	 * @param int   $index    ایندکس.
	 * @return string
	 */
	protected function anim_attr( $settings, $index = 0 ) {
		if ( 'yes' !== ( $settings['anim_enable'] ?? 'yes' ) ) {
			return '';
		}

		$mode = $settings['anim_style'] ?? 'rise';
		$step = (int) ( $settings['anim_stagger'] ?? 70 );

		/*
		 * سقف تأخیر ۴۲۰ میلی‌ثانیه است؛ در شبکه‌های بزرگ، کارت‌های
		 * انتهایی نباید آن‌قدر دیر ظاهر شوند که کاربر منتظر بماند.
		 */
		$delay = min( $index * $step, 420 );

		return sprintf(
			' data-zc-anim="%s" data-zc-delay="%d"',
			esc_attr( $mode ),
			$delay
		);
	}
}
