<?php
/**
 * ویجت نمایش مدرسان
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * ویجت مدرسان.
 */
class ZC_Widget_teachers extends ZC_Widget_Base {

	/**
	 * نام.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'zc_teachers';
	}

	/**
	 * عنوان.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'زرین کد | مدرسان', 'zarincode' );
	}

	/**
	 * آیکن.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-person';
	}

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->add_heading_controls(
			__( 'اساتید <span>زرین کد</span>', 'zarincode' ),
			__( 'یادگیری از متخصصانی که سال‌ها در بازار کار واقعی فعالیت کرده‌اند', 'zarincode' )
		);

		$this->add_query_controls( 'zc_teacher', 'zc_teacher_skill' );
		$this->add_layout_controls( 4 );

		$this->start_controls_section(
			'opts',
			array( 'label' => __( 'گزینه‌ها', 'zarincode' ) )
		);

		$this->add_control(
			'show_role',
			array(
				'label'   => __( 'نمایش تخصص', 'zarincode' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_courses',
			array(
				'label'   => __( 'نمایش تعداد دوره', 'zarincode' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_socials',
			array(
				'label'   => __( 'نمایش شبکه‌های اجتماعی', 'zarincode' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
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
		$query = $this->build_query( $s, 'zc_teacher', 'zc_teacher_skill' );

		$this->render_heading( $s );

		if ( ! $query->have_posts() ) {
			return;
		}

		$this->open_wrapper( $s );

		$i = 0;
		while ( $query->have_posts() ) {
			$query->the_post();
			$id      = get_the_ID();
			$role    = get_post_meta( $id, '_zc_teacher_role', true );
			$courses = (int) get_post_meta( $id, '_zc_teacher_courses', true );
			$students= (int) get_post_meta( $id, '_zc_teacher_students', true );
			?>
			<div class="zc-card <?php echo esc_attr( $this->item_class( $s ) ); ?>" style="text-align:center;padding:24px 18px"<?php echo $this->anim_attr( $s, $i ); // phpcs:ignore ?>>
				<a href="<?php the_permalink(); ?>" class="zc-avatar zc-avatar--xl" style="margin:0 auto 14px;display:block">
					<?php echo zc_thumbnail( $id, 'zc-avatar' ); // phpcs:ignore ?>
				</a>
				<h3 class="zc-card__title" style="margin-bottom:4px"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				<?php if ( 'yes' === $s['show_role'] && $role ) : ?>
					<p style="font-size:.82rem;color:var(--zc-gold-3);margin:0 0 10px;font-weight:600"><?php echo esc_html( $role ); ?></p>
				<?php endif; ?>
				<?php if ( 'yes' === $s['show_courses'] ) : ?>
					<div class="zc-card__meta" style="justify-content:center">
						<span><?php zc_the_icon( 'video', 15 ); ?><?php echo esc_html( zc_fa_num( $courses ) . ' ' . __( 'دوره', 'zarincode' ) ); ?></span>
						<span><?php zc_the_icon( 'users', 15 ); ?><?php echo esc_html( zc_fa_num( number_format( $students ) ) ); ?></span>
					</div>
				<?php endif; ?>
				<?php if ( 'yes' === $s['show_socials'] ) : ?>
					<div class="zc-share__list" style="justify-content:center;margin-top:12px">
						<?php
						foreach ( array( 'telegram', 'linkedin', 'github' ) as $net ) {
							$url = get_post_meta( $id, '_zc_teacher_' . $net, true );
							if ( ! $url ) {
								continue;
							}
							printf(
								'<a href="%s" class="zc-share__btn" data-net="%s" target="_blank" rel="noopener nofollow">%s</a>',
								esc_url( $url ),
								esc_attr( $net ),
								zc_social_icon( $net, 16 ) // phpcs:ignore
							);
						}
						?>
					</div>
				<?php endif; ?>
			</div>
			<?php
			$i++;
		}
		wp_reset_postdata();

		$this->close_wrapper( $s );
	}
}
