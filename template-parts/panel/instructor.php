<?php
/**
 * تب پنل مدرس.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

if ( ! zc_user_is_instructor() ) {
	echo '<p>' . esc_html__( 'این بخش فقط برای مدرسان است.', 'zarincode' ) . '</p>';
	return;
}

$zc_courses = zc_instructor_courses();
?>
<div class="zc-admin-box" style="margin-bottom:18px">
	<p><?php esc_html_e( 'آمار ثبت‌نام، پیشرفت دانشجویان و درآمد دوره‌هایی که شما مدرس آن هستید.', 'zarincode' ); ?></p>
</div>
<?php if ( $zc_courses ) : ?>
	<div class="zc-grid zc-grid--2">
		<?php foreach ( $zc_courses as $zc_course ) : ?>
			<?php $st = zc_instructor_course_stats( $zc_course->ID ); ?>
			<article class="zc-card">
				<div class="zc-card__body">
					<h3 class="zc-card__title"><a href="<?php echo esc_url( get_permalink( $zc_course ) ); ?>"><?php echo esc_html( get_the_title( $zc_course ) ); ?></a></h3>
					<ul class="zc-check-list">
						<li><?php echo esc_html( sprintf( __( '%s دانشجو', 'zarincode' ), zc_fa_num( $st['students'] ) ) ); ?></li>
						<li><?php echo esc_html( sprintf( __( '%s نفر دوره را تمام کرده‌اند', 'zarincode' ), zc_fa_num( $st['completed'] ) ) ); ?></li>
						<li><?php echo esc_html( sprintf( __( 'میانگین پیشرفت: %s٪', 'zarincode' ), zc_fa_num( $st['avg'] ) ) ); ?></li>
						<li><?php echo esc_html( sprintf( __( 'درآمد ثبت‌شده: %s', 'zarincode' ), zc_price_text( $st['revenue'] ) ) ); ?></li>
					</ul>
					<div class="zc-card__footer">
						<a class="zc-btn zc-btn--gold zc-btn--sm" href="<?php echo esc_url( zc_classroom_url( $zc_course->ID ) ); ?>"><?php esc_html_e( 'کلاس درس', 'zarincode' ); ?></a>
						<?php if ( current_user_can( 'edit_post', $zc_course->ID ) ) : ?>
							<a class="zc-btn zc-btn--ghost zc-btn--sm" href="<?php echo esc_url( get_edit_post_link( $zc_course->ID ) ); ?>"><?php esc_html_e( 'ویرایش', 'zarincode' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
<?php else : ?>
	<div class="zc-empty">
		<h3><?php esc_html_e( 'هنوز دوره‌ای به نام شما ثبت نشده است.', 'zarincode' ); ?></h3>
	</div>
<?php endif; ?>
