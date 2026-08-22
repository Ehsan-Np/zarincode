<?php
/**
 * تب گواهینامه‌ها
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_certs = zc_get_certificates();
$zc_user  = wp_get_current_user();
?>

<?php if ( $zc_certs ) : ?>
	<div class="zc-grid zc-grid--2">
		<?php foreach ( $zc_certs as $zc_i => $zc_cert ) : ?>
			<div class="zc-certificate" data-zc-anim="up" data-zc-delay="<?php echo (int) ( $zc_i * 70 ); ?>">
				<div class="zc-certificate__head">
					<span class="zc-certificate__seal"><?php zc_the_icon( 'award', 30 ); ?></span>
					<div>
						<small><?php esc_html_e( 'گواهی پایان دوره', 'zarincode' ); ?></small>
						<h3><?php echo esc_html( get_the_title( $zc_cert['course_id'] ) ); ?></h3>
					</div>
				</div>
				<div class="zc-certificate__body">
					<p><?php printf( esc_html__( 'اعطا شده به: %s', 'zarincode' ), '<strong>' . esc_html( $zc_user->display_name ) . '</strong>' ); ?></p>
					<div class="zc-certificate__meta">
						<span><?php esc_html_e( 'کد گواهی:', 'zarincode' ); ?> <code><?php echo esc_html( $zc_cert['code'] ); ?></code></span>
						<span><?php echo esc_html( zc_fa_num( wp_date( 'Y/m/d', strtotime( $zc_cert['date'] ) ) ) ); ?></span>
					</div>
				</div>
				<div class="zc-certificate__footer">
					<button class="zc-btn zc-btn--gold zc-btn--sm" onclick="window.print()"><?php zc_the_icon( 'download', 15 ); ?><?php esc_html_e( 'دریافت گواهی', 'zarincode' ); ?></button>
					<a href="<?php echo esc_url( get_permalink( $zc_cert['course_id'] ) ); ?>" class="zc-btn zc-btn--ghost zc-btn--sm"><?php zc_the_icon( 'video', 15 ); ?><?php esc_html_e( 'مشاهده دوره', 'zarincode' ); ?></a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php else : ?>
	<div class="zc-empty">
		<div class="zc-empty__icon"><?php zc_the_icon( 'certificate', 40 ); ?></div>
		<h3><?php esc_html_e( 'هنوز گواهینامه‌ای دریافت نکرده‌اید', 'zarincode' ); ?></h3>
		<p><?php esc_html_e( 'با تکمیل ۱۰۰٪ جلسات هر دوره، گواهی پایان دوره برای شما صادر می‌شود.', 'zarincode' ); ?></p>
		<a href="<?php echo esc_url( zc_panel_url( 'courses' ) ); ?>" class="zc-btn zc-btn--gold"><?php zc_the_icon( 'play', 17 ); ?><?php esc_html_e( 'ادامه دوره‌ها', 'zarincode' ); ?></a>
	</div>
<?php endif; ?>
