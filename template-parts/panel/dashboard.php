<?php
/**
 * تب پیشخوان پنل کاربری
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_user  = wp_get_current_user();
$zc_stats = zc_user_stats();
$zc_cards = array(
	array( 'icon' => 'book', 'label' => __( 'دوره‌های من', 'zarincode' ), 'value' => zc_fa_num( $zc_stats['courses'] ), 'color' => 'gold', 'link' => zc_panel_url( 'courses' ) ),
	array( 'icon' => 'award', 'label' => __( 'دوره تکمیل‌شده', 'zarincode' ), 'value' => zc_fa_num( $zc_stats['completed'] ), 'color' => 'green', 'link' => zc_panel_url( 'certificates' ) ),
	array( 'icon' => 'wallet', 'label' => __( 'موجودی کیف پول', 'zarincode' ), 'value' => zc_fa_num( number_format( $zc_stats['wallet'] ) ), 'color' => 'blue', 'link' => zc_panel_url( 'wallet' ) ),
	array( 'icon' => 'ticket', 'label' => __( 'تیکت‌های من', 'zarincode' ), 'value' => zc_fa_num( $zc_stats['tickets'] ), 'color' => 'orange', 'link' => zc_panel_url( 'tickets' ) ),
);

/**
 * هوک بالای پیشخوان پنل — برای نمایش اعلان‌های یک‌باره (مثل cashback).
 */
do_action( 'zc_panel_dashboard_top' );
?>

<div class="zc-panel__welcome" data-zc-anim="up">
	<div>
		<h2><?php printf( esc_html__( 'سلام %s، خوش آمدید 👋', 'zarincode' ), esc_html( $zc_user->first_name ? $zc_user->first_name : $zc_user->display_name ) ); ?></h2>
		<p><?php esc_html_e( 'از اینجا می‌توانید دوره‌ها، سفارش‌ها، کیف پول و تیکت‌های خود را مدیریت کنید.', 'zarincode' ); ?></p>
	</div>
	<a href="<?php echo esc_url( get_post_type_archive_link( 'zc_course' ) ); ?>" class="zc-btn zc-btn--gold">
		<?php zc_the_icon( 'sparkle', 18 ); ?>
		<?php esc_html_e( 'مشاهده دوره‌های جدید', 'zarincode' ); ?>
	</a>
</div>

<div class="zc-panel__stats">
	<?php foreach ( $zc_cards as $i => $zc_card ) : ?>
		<a href="<?php echo esc_url( $zc_card['link'] ); ?>" class="zc-pstat zc-pstat--<?php echo esc_attr( $zc_card['color'] ); ?>" data-zc-anim="up" data-zc-delay="<?php echo (int) ( $i * 70 ); ?>">
			<span class="zc-pstat__icon"><?php zc_the_icon( $zc_card['icon'], 24 ); ?></span>
			<span class="zc-pstat__info">
				<strong class="zc-pstat__value"><?php echo esc_html( $zc_card['value'] ); ?></strong>
				<span class="zc-pstat__label"><?php echo esc_html( $zc_card['label'] ); ?></span>
			</span>
		</a>
	<?php endforeach; ?>
</div>

<div class="zc-panel__grid-2">

	<!-- ادامه یادگیری -->
	<div class="zc-panel__box" data-zc-anim="up">
		<div class="zc-panel__box-head">
			<h3><?php zc_the_icon( 'play', 19 ); ?><?php esc_html_e( 'ادامه یادگیری', 'zarincode' ); ?></h3>
			<a href="<?php echo esc_url( zc_panel_url( 'courses' ) ); ?>" class="zc-panel__box-link"><?php esc_html_e( 'همه دوره‌ها', 'zarincode' ); ?></a>
		</div>
		<div class="zc-panel__box-body">
			<?php
			$zc_courses = array_slice( zc_get_user_courses(), 0, 3 );
			if ( $zc_courses ) :
				foreach ( $zc_courses as $zc_enroll ) :
					$zc_cid  = (int) $zc_enroll->course_id;
					$zc_prog = zc_get_course_progress( get_current_user_id(), $zc_cid );
					?>
					<a href="<?php echo esc_url( get_permalink( $zc_cid ) ); ?>" class="zc-mini-course">
						<span class="zc-mini-course__thumb"><?php echo zc_thumbnail( $zc_cid, 'thumbnail' ); // phpcs:ignore ?></span>
						<span class="zc-mini-course__info">
							<strong><?php echo esc_html( get_the_title( $zc_cid ) ); ?></strong>
							<span class="zc-progress"><span class="zc-progress__bar" data-value="<?php echo esc_attr( $zc_prog ); ?>"></span></span>
							<small><?php echo esc_html( zc_fa_num( $zc_prog ) ); ?>٪ <?php esc_html_e( 'تکمیل شده', 'zarincode' ); ?></small>
						</span>
					</a>
					<?php
				endforeach;
			else :
				?>
				<div class="zc-empty" style="padding:30px 10px">
					<div class="zc-empty__icon" style="width:64px;height:64px"><?php zc_the_icon( 'book', 28 ); ?></div>
					<p><?php esc_html_e( 'هنوز در دوره‌ای ثبت‌نام نکرده‌اید.', 'zarincode' ); ?></p>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'zc_course' ) ); ?>" class="zc-btn zc-btn--gold zc-btn--sm"><?php zc_the_icon( 'video', 15 ); ?><?php esc_html_e( 'مشاهده دوره‌ها', 'zarincode' ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- اعلان‌ها -->
	<div class="zc-panel__box" data-zc-anim="up" data-zc-delay="100">
		<div class="zc-panel__box-head">
			<h3><?php zc_the_icon( 'bell', 19 ); ?><?php esc_html_e( 'آخرین اعلان‌ها', 'zarincode' ); ?></h3>
		</div>
		<div class="zc-panel__box-body">
			<?php
			$zc_notifs = array_slice( zc_get_notifications(), 0, 5 );
			if ( $zc_notifs ) :
				foreach ( $zc_notifs as $zc_n ) :
					?>
					<div class="zc-notif<?php echo empty( $zc_n['read'] ) ? ' is-unread' : ''; ?>">
						<span class="zc-notif__dot zc-notif__dot--<?php echo esc_attr( $zc_n['type'] ); ?>"></span>
						<div class="zc-notif__body">
							<strong><?php echo esc_html( $zc_n['title'] ); ?></strong>
							<?php if ( ! empty( $zc_n['message'] ) ) : ?>
								<p><?php echo esc_html( $zc_n['message'] ); ?></p>
							<?php endif; ?>
							<time><?php echo esc_html( zc_human_time( $zc_n['date'] ) ); ?></time>
						</div>
						<?php if ( ! empty( $zc_n['link'] ) ) : ?>
							<a href="<?php echo esc_url( $zc_n['link'] ); ?>" class="zc-notif__link"><?php zc_the_icon( 'arrow-left', 17 ); ?></a>
						<?php endif; ?>
					</div>
					<?php
				endforeach;
			else :
				?>
				<p style="text-align:center;color:var(--zc-muted);padding:24px 0"><?php esc_html_e( 'اعلان جدیدی ندارید.', 'zarincode' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- آخرین سفارش‌ها -->
<?php if ( zc_is_woo() ) : ?>
<div class="zc-panel__box" data-zc-anim="up">
	<div class="zc-panel__box-head">
		<h3><?php zc_the_icon( 'cart', 19 ); ?><?php esc_html_e( 'آخرین سفارش‌ها', 'zarincode' ); ?></h3>
		<a href="<?php echo esc_url( zc_panel_url( 'orders' ) ); ?>" class="zc-panel__box-link"><?php esc_html_e( 'همه سفارش‌ها', 'zarincode' ); ?></a>
	</div>
	<div class="zc-panel__box-body">
		<?php
		$zc_orders = wc_get_orders( array( 'customer_id' => get_current_user_id(), 'limit' => 5 ) );
		if ( $zc_orders ) :
			?>
			<div class="zc-table-wrap">
				<table class="zc-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'شماره', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'تاریخ', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'مبلغ', 'zarincode' ); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $zc_orders as $zc_order ) : ?>
							<tr>
								<td>#<?php echo esc_html( zc_fa_num( $zc_order->get_order_number() ) ); ?></td>
								<td><?php echo esc_html( zc_fa_num( wp_date( 'Y/m/d', $zc_order->get_date_created()->getTimestamp() ) ) ); ?></td>
								<td><span class="zc-badge zc-badge--<?php echo $zc_order->is_paid() ? 'green' : 'orange'; ?>"><?php echo esc_html( wc_get_order_status_name( $zc_order->get_status() ) ); ?></span></td>
								<td><?php echo wp_kses_post( $zc_order->get_formatted_order_total() ); ?></td>
								<td><a href="<?php echo esc_url( $zc_order->get_view_order_url() ); ?>" class="zc-btn zc-btn--ghost zc-btn--sm"><?php zc_the_icon( 'eye', 15 ); ?><?php esc_html_e( 'مشاهده', 'zarincode' ); ?></a></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php else : ?>
			<p style="text-align:center;color:var(--zc-muted);padding:24px 0"><?php esc_html_e( 'هنوز سفارشی ثبت نکرده‌اید.', 'zarincode' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>

<?php if ( function_exists( 'zc_subscription_is_active' ) ) : ?>
<?php
$zc_sub_active  = zc_subscription_is_active();
$zc_sub_rec     = zc_subscription_get_user();
$zc_sub_plan_id = isset( $zc_sub_rec['plan_id'] ) ? (int) $zc_sub_rec['plan_id'] : 0;
$zc_sub_name    = $zc_sub_plan_id && function_exists( 'zc_subscription_plan_data' ) ? ( zc_subscription_plan_data( $zc_sub_plan_id )['title'] ?? '' ) : '';
$zc_sub_exp     = ! empty( $zc_sub_rec['expires'] ) ? wp_date( 'Y/m/d', (int) $zc_sub_rec['expires'] ) : '';
?>
<div class="zc-panel__box" data-zc-anim="up">
	<div class="zc-panel__box-head">
		<h3><?php zc_the_icon( 'gift', 19 ); ?><?php esc_html_e( 'اشتراک فعال', 'zarincode' ); ?></h3>
		<a href="<?php echo esc_url( zc_panel_url( 'subscription' ) ); ?>" class="zc-panel__box-link"><?php esc_html_e( 'مدیریت اشتراک', 'zarincode' ); ?></a>
	</div>
	<div class="zc-panel__box-body">
		<?php if ( $zc_sub_active ) : ?>
			<div class="zc-pstat zc-pstat--gold" style="margin-bottom:6px">
				<span class="zc-pstat__icon"><?php zc_the_icon( 'award', 24 ); ?></span>
				<span class="zc-pstat__info">
					<strong class="zc-pstat__value"><?php echo esc_html( $zc_sub_name ? $zc_sub_name : __( 'اشتراک ویژه', 'zarincode' ) ); ?></strong>
					<span class="zc-pstat__label">
						<?php echo $zc_sub_exp ? esc_html( sprintf( __( 'اعتبار تا %s', 'zarincode' ), $zc_sub_exp ) ) : esc_html__( 'مادام‌العمر', 'zarincode' ); ?>
					</span>
				</span>
			</div>
		<?php else : ?>
			<div class="zc-empty" style="padding:14px 6px;text-align:center">
				<p style="margin:0 0 12px;color:var(--zc-muted)"><?php esc_html_e( 'با اشتراک ویژه به همهٔ دوره‌ها دسترسی نامحدود داشته باشید.', 'zarincode' ); ?></p>
				<a href="<?php echo esc_url( zc_panel_url( 'subscription' ) ); ?>" class="zc-btn zc-btn--gold zc-btn--sm"><?php zc_the_icon( 'gift', 15 ); ?><?php esc_html_e( 'مشاهده پلن‌ها', 'zarincode' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>

<?php if ( function_exists( 'zc_attempts_query' ) ) : ?>
<?php
$zc_uid2  = get_current_user_id();
$zc_user  = get_userdata( $zc_uid2 );
$zc_my_attempts = zc_attempts_query( array( 'user' => $zc_user->user_login ) );
$zc_my_attempts = array_slice( $zc_my_attempts, 0, 5 );
?>
<div class="zc-panel__box" data-zc-anim="up" data-zc-delay="100">
	<div class="zc-panel__box-head">
		<h3><?php zc_the_icon( 'chart', 19 ); ?><?php esc_html_e( 'آخرین تلاش‌های من', 'zarincode' ); ?></h3>
		<a href="<?php echo esc_url( zc_panel_url( 'attempts' ) ); ?>" class="zc-panel__box-link"><?php esc_html_e( 'همه سوابق', 'zarincode' ); ?></a>
	</div>
	<div class="zc-panel__box-body">
		<?php if ( $zc_my_attempts ) : ?>
			<div class="zc-table-wrap">
				<table class="zc-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'نوع', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'دوره / تمرین', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'نمره', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $zc_my_attempts as $zc_a ) : ?>
							<tr>
								<td><?php echo esc_html( zc_attempts_type_label( $zc_a->type ) ); ?></td>
								<td><?php echo esc_html( zc_attempts_ref_title( $zc_a->type, $zc_a->ref_id ) ); ?></td>
								<td><strong><?php echo esc_html( zc_fa_num( $zc_a->score ) ); ?>٪</strong></td>
								<td><?php echo $zc_a->passed ? '<span class="zc-tag zc-tag--success">' . esc_html__( 'قبول', 'zarincode' ) . '</span>' : '<span class="zc-tag zc-tag--muted">' . esc_html__( 'رد', 'zarincode' ) . '</span>'; ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php else : ?>
			<p style="text-align:center;color:var(--zc-muted);padding:18px 0"><?php esc_html_e( 'هنوز در آزمون یا تمرینی شرکت نکرده‌اید.', 'zarincode' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>
