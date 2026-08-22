<?php
/**
 * تب نوبت‌های من
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_bookings = zc_get_user_bookings();
$zc_statuses = array(
	'pending'   => array( __( 'در انتظار تایید', 'zarincode' ), 'orange' ),
	'confirmed' => array( __( 'تایید شده', 'zarincode' ), 'green' ),
	'done'      => array( __( 'انجام شده', 'zarincode' ), 'blue' ),
	'canceled'  => array( __( 'لغو شده', 'zarincode' ), 'red' ),
);
?>

<div class="zc-panel__grid-2 zc-panel__grid-2--wide">
	<div class="zc-panel__box" data-zc-anim="up">
		<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'calendar', 19 ); ?><?php esc_html_e( 'نوبت‌های رزرو شده', 'zarincode' ); ?></h3></div>
		<div class="zc-panel__box-body">
			<?php if ( $zc_bookings ) : ?>
				<div class="zc-table-wrap">
					<table class="zc-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'تاریخ', 'zarincode' ); ?></th>
								<th><?php esc_html_e( 'ساعت', 'zarincode' ); ?></th>
								<th><?php esc_html_e( 'خدمت', 'zarincode' ); ?></th>
								<th><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $zc_bookings as $zc_b ) : ?>
								<tr>
									<td><?php echo esc_html( zc_fa_num( $zc_b->date ) ); ?></td>
									<td><?php echo esc_html( zc_fa_num( $zc_b->time ) ); ?></td>
									<td><?php echo esc_html( $zc_b->service_id ? get_the_title( $zc_b->service_id ) : '—' ); ?></td>
									<td>
										<span class="zc-badge zc-badge--<?php echo esc_attr( $zc_statuses[ $zc_b->status ][1] ?? 'blue' ); ?>">
											<?php echo esc_html( $zc_statuses[ $zc_b->status ][0] ?? $zc_b->status ); ?>
										</span>
									</td>
									<td>
										<?php if ( in_array( $zc_b->status, array( 'pending', 'confirmed' ), true ) ) : ?>
											<button class="zc-btn zc-btn--ghost zc-btn--sm" data-zc-cancel-booking="<?php echo esc_attr( $zc_b->id ); ?>"><?php zc_the_icon( 'close', 15 ); ?><?php esc_html_e( 'لغو', 'zarincode' ); ?></button>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<div class="zc-empty" style="padding:34px 10px">
					<div class="zc-empty__icon" style="width:64px;height:64px"><?php zc_the_icon( 'calendar', 28 ); ?></div>
					<p><?php esc_html_e( 'نوبتی رزرو نکرده‌اید.', 'zarincode' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="zc-panel__box" data-zc-anim="up" data-zc-delay="90">
		<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'edit', 19 ); ?><?php esc_html_e( 'رزرو نوبت جدید', 'zarincode' ); ?></h3></div>
		<div class="zc-panel__box-body">
			<form data-zc-form="zc_booking_submit">
				<?php $zc_user = wp_get_current_user(); ?>
				<div class="zc-field">
					<label class="zc-label"><?php esc_html_e( 'نام', 'zarincode' ); ?></label>
					<input type="text" name="name" value="<?php echo esc_attr( $zc_user->display_name ); ?>" required>
				</div>
				<div class="zc-field">
					<label class="zc-label"><?php esc_html_e( 'موبایل', 'zarincode' ); ?></label>
					<input type="tel" name="mobile" value="<?php echo esc_attr( get_user_meta( $zc_user->ID, 'zc_mobile', true ) ); ?>" required>
				</div>
				<?php
				$zc_services = get_posts( array( 'post_type' => 'zc_service', 'posts_per_page' => 30 ) );
				if ( $zc_services ) :
					?>
					<div class="zc-field">
						<label class="zc-label"><?php esc_html_e( 'نوع خدمت', 'zarincode' ); ?></label>
						<select name="service">
							<?php foreach ( $zc_services as $zc_srv ) : ?>
								<option value="<?php echo esc_attr( $zc_srv->ID ); ?>"><?php echo esc_html( $zc_srv->post_title ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>
				<div class="zc-field">
					<label class="zc-label"><?php esc_html_e( 'تاریخ', 'zarincode' ); ?></label>
					<input type="date" name="date" required min="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>">
				</div>
				<div class="zc-field">
					<label class="zc-label"><?php esc_html_e( 'ساعت', 'zarincode' ); ?></label>
					<select name="time" required>
						<?php foreach ( zc_booking_time_slots() as $zc_slot ) : ?>
							<option value="<?php echo esc_attr( $zc_slot ); ?>"><?php echo esc_html( zc_fa_num( $zc_slot ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="zc-field">
					<label class="zc-label"><?php esc_html_e( 'توضیحات', 'zarincode' ); ?></label>
					<textarea name="note" rows="3"></textarea>
				</div>
				<div class="zc-form-msg"></div>
				<button type="submit" class="zc-btn zc-btn--gold zc-btn--block"><?php zc_the_icon( 'calendar', 17 ); ?><?php esc_html_e( 'ثبت رزرو', 'zarincode' ); ?></button>
			</form>
		</div>
	</div>
</div>
