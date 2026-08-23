<?php
/**
 * تب دوره‌های من
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_enrollments = zc_get_user_courses();
$zc_filter      = isset( $_GET['filter'] ) ? sanitize_key( wp_unslash( $_GET['filter'] ) ) : 'all'; // phpcs:ignore
?>

<div class="zc-panel__filters">
	<?php
	$zc_filters = array(
		'all'         => __( 'همه دوره‌ها', 'zarincode' ),
		'in-progress' => __( 'در حال یادگیری', 'zarincode' ),
		'completed'   => __( 'تکمیل شده', 'zarincode' ),
	);
	foreach ( $zc_filters as $zc_key => $zc_label ) :
		?>
		<a href="<?php echo esc_url( add_query_arg( 'filter', $zc_key, zc_panel_url( 'courses' ) ) ); ?>"
			class="zc-panel__filter<?php echo $zc_filter === $zc_key ? ' is-active' : ''; ?>">
			<?php echo esc_html( $zc_label ); ?>
		</a>
	<?php endforeach; ?>
</div>

<?php if ( $zc_enrollments ) : ?>
	<div class="zc-grid zc-grid--3">
		<?php
		foreach ( $zc_enrollments as $zc_i => $zc_enroll ) :
			$zc_cid  = (int) $zc_enroll->course_id;
			$zc_prog = zc_get_course_progress( get_current_user_id(), $zc_cid );

			if ( 'completed' === $zc_filter && 100 !== $zc_prog ) {
				continue;
			}
			if ( 'in-progress' === $zc_filter && 100 === $zc_prog ) {
				continue;
			}
			if ( 'publish' !== get_post_status( $zc_cid ) ) {
				continue;
			}
			?>
			<article class="zc-card" data-zc-anim="up" data-zc-delay="<?php echo (int) ( $zc_i * 60 ); ?>">
				<div class="zc-card__media">
					<a href="<?php echo esc_url( get_permalink( $zc_cid ) ); ?>"><?php echo zc_thumbnail( $zc_cid ); // phpcs:ignore ?></a>
					<?php if ( 100 === $zc_prog ) : ?>
						<span class="zc-badge zc-badge--green zc-badge--float"><?php zc_the_icon( 'check', 14 ); ?><?php esc_html_e( 'تکمیل شده', 'zarincode' ); ?></span>
					<?php endif; ?>
				</div>
				<div class="zc-card__body">
					<h3 class="zc-card__title"><a href="<?php echo esc_url( get_permalink( $zc_cid ) ); ?>"><?php echo esc_html( get_the_title( $zc_cid ) ); ?></a></h3>

					<div style="margin-top:auto">
						<div style="display:flex;justify-content:space-between;font-size:.78rem;color:var(--zc-muted);margin-bottom:6px">
							<span><?php esc_html_e( 'پیشرفت', 'zarincode' ); ?></span>
							<strong style="color:var(--zc-gold-3)"><?php echo esc_html( zc_fa_num( $zc_prog ) ); ?>٪</strong>
						</div>
						<div class="zc-progress"><div class="zc-progress__bar" data-value="<?php echo esc_attr( $zc_prog ); ?>"></div></div>
					</div>

					<div class="zc-card__footer">
						<small style="color:var(--zc-muted)">
							<?php zc_the_icon( 'calendar', 14 ); ?>
							<?php echo esc_html( zc_fa_num( wp_date( 'Y/m/d', strtotime( $zc_enroll->created_at ) ) ) ); ?>
						</small>
						<a href="<?php echo esc_url( function_exists( 'zc_classroom_url' ) ? zc_classroom_url( $zc_cid ) : get_permalink( $zc_cid ) ); ?>" class="zc-btn zc-btn--gold zc-btn--sm">
							<?php echo $zc_prog > 0 ? esc_html__( 'ادامه دوره', 'zarincode' ) : esc_html__( 'شروع دوره', 'zarincode' ); ?>
						</a>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
<?php else : ?>
	<div class="zc-empty">
		<div class="zc-empty__icon"><?php zc_the_icon( 'book', 40 ); ?></div>
		<h3><?php esc_html_e( 'هنوز در دوره‌ای ثبت‌نام نکرده‌اید', 'zarincode' ); ?></h3>
		<p><?php esc_html_e( 'با ثبت‌نام در دوره‌های زرین کد، مسیر حرفه‌ای شدن را شروع کنید.', 'zarincode' ); ?></p>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'zc_course' ) ); ?>" class="zc-btn zc-btn--gold"><?php zc_the_icon( 'video', 17 ); ?><?php esc_html_e( 'مشاهده دوره‌ها', 'zarincode' ); ?></a>
	</div>
<?php endif; ?>
