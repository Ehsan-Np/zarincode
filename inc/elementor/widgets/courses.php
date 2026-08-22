<?php
/**
 * ویجت نمایش دوره‌های آموزشی
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

/**
 * ویجت دوره‌ها.
 */
class ZC_Widget_courses extends ZC_Widget_Base {

	/**
	 * نام.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_courses';
	}

	/**
	 * عنوان.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | دوره‌های آموزشی', 'zarincode' );
	}

	/**
	 * آیکن.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-play-o';
	}

	/**
	 * دسته.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'zarincode-shop', 'zarincode' );
	}

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {

		$this->add_heading_controls(
			__( 'جدیدترین <span>دوره‌های آموزشی</span>', 'zarincode' ),
			__( 'با پروژه‌محورترین دوره‌های برنامه‌نویسی، مهارت واقعی بازار کار را بیاموزید', 'zarincode' )
		);

		$this->add_query_controls( 'zc_course', 'zc_course_cat' );
		$this->add_layout_controls( 4 );

		/* --- تنظیمات کارت --- */
		$this->start_controls_section(
			'card_section',
			array( 'label' => __( 'اجزای کارت دوره', 'zarincode' ) )
		);

		$fields = array(
			'show_cat'      => array( __( 'نمایش دسته‌بندی', 'zarincode' ), 'yes' ),
			'show_teacher'  => array( __( 'نمایش مدرس', 'zarincode' ), 'yes' ),
			'show_rating'   => array( __( 'نمایش امتیاز', 'zarincode' ), 'yes' ),
			'show_students' => array( __( 'نمایش تعداد دانشجو', 'zarincode' ), 'yes' ),
			'show_duration' => array( __( 'نمایش مدت زمان', 'zarincode' ), 'yes' ),
			'show_lessons'  => array( __( 'نمایش تعداد جلسات', 'zarincode' ), 'yes' ),
			'show_level'    => array( __( 'نمایش سطح دوره', 'zarincode' ), 'yes' ),
			'show_price'    => array( __( 'نمایش قیمت', 'zarincode' ), 'yes' ),
			'show_excerpt'  => array( __( 'نمایش خلاصه متن', 'zarincode' ), '' ),
			'show_progress' => array( __( 'نمایش درصد تکمیل (برای دانشجو)', 'zarincode' ), '' ),
		);

		foreach ( $fields as $key => $data ) {
			$this->add_control(
				$key,
				array(
					'label'   => $data[0],
					'type'    => Controls_Manager::SWITCHER,
					'default' => $data[1],
				)
			);
		}

		$this->add_control(
			'excerpt_length',
			array(
				'label'     => __( 'تعداد کلمات خلاصه', 'zarincode' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 14,
				'condition' => array( 'show_excerpt' => 'yes' ),
			)
		);

		$this->add_control(
			'btn_text',
			array(
				'label'   => __( 'متن دکمه کارت', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'مشاهده دوره', 'zarincode' ),
			)
		);

		$this->end_controls_section();

		$this->add_more_button_controls();

		/* --- استایل کارت --- */
		$this->start_controls_section(
			'card_style',
			array(
				'label' => __( 'استایل کارت', 'zarincode' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'card_bg',
			array(
				'label'     => __( 'پس‌زمینه کارت', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-card' => 'background:{{VALUE}}' ),
			)
		);

		$this->add_control(
			'card_radius',
			array(
				'label'     => __( 'گردی گوشه', 'zarincode' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'selectors' => array( '{{WRAPPER}} .zc-card' => 'border-radius:{{SIZE}}{{UNIT}}' ),
			)
		);

		$this->add_control(
			'card_title_color',
			array(
				'label'     => __( 'رنگ عنوان', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-card__title, {{WRAPPER}} .zc-card__title a' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'card_title_typo',
				'selector' => '{{WRAPPER}} .zc-card__title',
			)
		);

		$this->add_control(
			'price_color',
			array(
				'label'     => __( 'رنگ قیمت', 'zarincode' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .zc-price__now' => 'color:{{VALUE}}' ),
			)
		);

		$this->add_control(
			'media_ratio',
			array(
				'label'     => __( 'نسبت تصویر', 'zarincode' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '16/9',
				'options'   => array(
					'16/9' => '16:9',
					'4/3'  => '4:3',
					'3/2'  => '3:2',
					'1/1'  => '1:1',
				),
				'selectors' => array( '{{WRAPPER}} .zc-card__media' => 'aspect-ratio:{{VALUE}}' ),
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
		$s     = $this->get_settings_for_display();
		$query = $this->build_query( $s, 'zc_course', 'zc_course_cat' );

		$this->render_heading( $s );

		if ( ! $query->have_posts() ) {
			echo '<div class="zc-empty"><div class="zc-empty__icon">' . zc_icon( 'book', 40 ) . '</div><h3>' . esc_html__( 'هنوز دوره‌ای منتشر نشده است', 'zarincode' ) . '</h3></div>'; // phpcs:ignore
			return;
		}

		$this->open_wrapper( $s );

		$i = 0;
		while ( $query->have_posts() ) {
			$query->the_post();
			$this->render_card( $s, $i );
			$i++;
		}
		wp_reset_postdata();

		$this->close_wrapper( $s );
		$this->render_more_button( $s );
	}

	/**
	 * رندر یک کارت دوره.
	 *
	 * @param array $s تنظیمات.
	 * @param int   $i ایندکس.
	 * @return void
	 */
	private function render_card( $s, $i ) {
		$id       = get_the_ID();
		$price    = get_post_meta( $id, '_zc_price', true );
		$sale     = get_post_meta( $id, '_zc_sale_price', true );
		$students = (int) get_post_meta( $id, '_zc_students', true );
		$duration = get_post_meta( $id, '_zc_duration', true );
		$level    = get_post_meta( $id, '_zc_level', true );
		$rating   = (float) get_post_meta( $id, '_zc_rating', true );
		$teacher  = get_post_meta( $id, '_zc_teacher', true );
		$lessons  = zc_count_lessons( $id );
		$cats     = get_the_terms( $id, 'zc_course_cat' );

		$levels = array(
			'beginner'     => __( 'مقدماتی', 'zarincode' ),
			'intermediate' => __( 'متوسط', 'zarincode' ),
			'advanced'     => __( 'پیشرفته', 'zarincode' ),
		);
		?>
		<article class="zc-card zc-course-card <?php echo esc_attr( $this->item_class( $s ) ); ?>"<?php echo $this->anim_attr( $s, $i ); // phpcs:ignore ?>>

			<div class="zc-card__media">
				<a href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
					<?php echo zc_thumbnail( $id, 'zc-card' ); // phpcs:ignore ?>
				</a>

				<?php if ( 'yes' === $s['show_level'] && $level ) : ?>
					<span class="zc-badge zc-badge--solid zc-course-card__level">
						<?php echo esc_html( $levels[ $level ] ?? $level ); ?>
					</span>
				<?php endif; ?>

				<?php if ( $sale && $price ) : ?>
					<span class="zc-badge zc-badge--red zc-badge--float">
						<?php echo esc_html( zc_fa_num( round( ( ( $price - $sale ) / $price ) * 100 ) ) . '٪ تخفیف' ); ?>
					</span>
				<?php endif; ?>

				<div class="zc-play-overlay">
					<a href="<?php the_permalink(); ?>" class="zc-play-btn"><?php zc_the_icon( 'play', 22 ); ?></a>
				</div>
			</div>

			<div class="zc-card__body">
				<?php if ( 'yes' === $s['show_cat'] && $cats && ! is_wp_error( $cats ) ) : ?>
					<a href="<?php echo esc_url( get_term_link( $cats[0] ) ); ?>" class="zc-badge zc-badge--gold" style="align-self:flex-start">
						<?php echo esc_html( $cats[0]->name ); ?>
					</a>
				<?php endif; ?>

				<h3 class="zc-card__title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h3>

				<?php if ( 'yes' === $s['show_excerpt'] ) : ?>
					<p class="zc-card__excerpt"><?php echo esc_html( zc_excerpt( get_the_excerpt(), (int) $s['excerpt_length'] ) ); ?></p>
				<?php endif; ?>

				<?php if ( 'yes' === $s['show_teacher'] && $teacher ) : ?>
					<div class="zc-course-card__teacher">
						<?php echo zc_teacher_avatar_img( $teacher, 26 ); // phpcs:ignore ?>
						<span><?php echo esc_html( $teacher ); ?></span>
					</div>
				<?php endif; ?>

				<div class="zc-course-card__stats">
					<?php if ( 'yes' === $s['show_duration'] && $duration ) : ?>
						<span><?php zc_the_icon( 'clock', 14 ); ?><?php echo esc_html( zc_fa_num( $duration ) ); ?></span>
					<?php endif; ?>
					<?php if ( 'yes' === $s['show_lessons'] && $lessons ) : ?>
						<span><?php zc_the_icon( 'video', 14 ); ?><?php echo esc_html( zc_fa_num( $lessons ) . ' ' . __( 'جلسه', 'zarincode' ) ); ?></span>
					<?php endif; ?>
					<?php if ( 'yes' === $s['show_students'] && $students ) : ?>
						<span><?php zc_the_icon( 'users', 14 ); ?><?php echo esc_html( zc_fa_num( number_format( $students ) ) ); ?></span>
					<?php endif; ?>
				</div>

				<?php if ( 'yes' === $s['show_progress'] && is_user_logged_in() && zc_user_has_course( get_current_user_id(), $id ) ) : ?>
					<?php $prog = zc_get_course_progress( get_current_user_id(), $id ); ?>
					<div style="margin-top:4px">
						<div style="display:flex;justify-content:space-between;font-size:.76rem;color:var(--zc-muted);margin-bottom:5px">
							<span><?php esc_html_e( 'پیشرفت شما', 'zarincode' ); ?></span>
							<span><?php echo esc_html( zc_fa_num( $prog ) ); ?>٪</span>
						</div>
						<div class="zc-progress"><div class="zc-progress__bar" data-value="<?php echo esc_attr( $prog ); ?>"></div></div>
					</div>
				<?php endif; ?>

				<div class="zc-card__footer">
					<?php if ( 'yes' === $s['show_rating'] ) : ?>
						<?php echo zc_stars( $rating ?: 5 ); // phpcs:ignore ?>
					<?php endif; ?>

					<?php if ( 'yes' === $s['show_price'] ) : ?>
						<div class="zc-price">
							<?php if ( ! $price ) : ?>
								<span class="zc-price__free"><?php esc_html_e( 'رایگان', 'zarincode' ); ?></span>
							<?php elseif ( $sale ) : ?>
								<del class="zc-price__old"><?php echo esc_html( zc_fa_num( number_format( (float) $price ) ) ); ?></del>
								<span class="zc-price__now"><?php echo esc_html( zc_fa_num( number_format( (float) $sale ) ) . ' ' . zc_opt( 'zc_currency_symbol', 'تومان' ) ); ?></span>
							<?php else : ?>
								<span class="zc-price__now"><?php echo esc_html( zc_fa_num( number_format( (float) $price ) ) . ' ' . zc_opt( 'zc_currency_symbol', 'تومان' ) ); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</article>
		<?php
	}
}
