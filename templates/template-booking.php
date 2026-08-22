<?php
/**
 * Template Name: رزرو نوبت زرین کد
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

get_header();

zc_page_hero( get_the_title(), __( 'زمان مناسب خود را برای جلسه مشاوره انتخاب کنید', 'zarincode' ) );

$zc_services = get_posts( array( 'post_type' => 'zc_service', 'posts_per_page' => 50 ) );
?>

<div class="zc-container">
	<div class="zc-grid zc-grid--2" style="align-items:start;gap:30px">

		<div class="zc-panel__box" style="margin:0">
			<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'calendar', 19 ); ?><?php esc_html_e( 'فرم رزرو نوبت', 'zarincode' ); ?></h3></div>
			<div class="zc-panel__box-body">
				<form data-zc-form="zc_booking_submit">
					<div class="zc-row">
						<div class="zc-field" style="flex:1;min-width:200px">
							<label class="zc-label"><?php esc_html_e( 'نام و نام خانوادگی', 'zarincode' ); ?> <span class="req">*</span></label>
							<input type="text" name="name" required value="<?php echo esc_attr( is_user_logged_in() ? wp_get_current_user()->display_name : '' ); ?>">
						</div>
						<div class="zc-field" style="flex:1;min-width:200px">
							<label class="zc-label"><?php esc_html_e( 'شماره موبایل', 'zarincode' ); ?> <span class="req">*</span></label>
							<input type="tel" name="mobile" required placeholder="09xxxxxxxxx" dir="ltr" style="text-align:left"
								value="<?php echo esc_attr( is_user_logged_in() ? get_user_meta( get_current_user_id(), 'zc_mobile', true ) : '' ); ?>">
						</div>
					</div>

					<?php if ( $zc_services ) : ?>
					<div class="zc-field">
						<label class="zc-label"><?php esc_html_e( 'نوع خدمت', 'zarincode' ); ?></label>
						<select name="service">
							<?php foreach ( $zc_services as $zc_srv ) : ?>
								<option value="<?php echo esc_attr( $zc_srv->ID ); ?>"><?php echo esc_html( $zc_srv->post_title ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<?php endif; ?>

					<div class="zc-row">
						<div class="zc-field" style="flex:1;min-width:200px">
							<label class="zc-label"><?php esc_html_e( 'تاریخ', 'zarincode' ); ?> <span class="req">*</span></label>
							<input type="date" name="date" id="zc-booking-date" required min="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>">
						</div>
						<div class="zc-field" style="flex:1;min-width:200px">
							<label class="zc-label"><?php esc_html_e( 'ساعت', 'zarincode' ); ?> <span class="req">*</span></label>
							<select name="time" id="zc-booking-time" required>
								<?php foreach ( zc_booking_time_slots() as $zc_slot ) : ?>
									<option value="<?php echo esc_attr( $zc_slot ); ?>"><?php echo esc_html( zc_fa_num( $zc_slot ) ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="zc-field">
						<label class="zc-label"><?php esc_html_e( 'توضیحات', 'zarincode' ); ?></label>
						<textarea name="note" rows="4" placeholder="<?php esc_attr_e( 'در صورت نیاز توضیحات خود را بنویسید…', 'zarincode' ); ?>"></textarea>
					</div>

					<div class="zc-form-msg"></div>

					<button type="submit" class="zc-btn zc-btn--gold zc-btn--block zc-btn--lg">
						<?php zc_the_icon( 'check', 18 ); ?><?php esc_html_e( 'ثبت درخواست رزرو', 'zarincode' ); ?>
					</button>
				</form>
			</div>
		</div>

		<div>
			<div class="zc-panel__box">
				<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'info', 19 ); ?><?php esc_html_e( 'راهنمای رزرو', 'zarincode' ); ?></h3></div>
				<div class="zc-panel__box-body">
					<ul class="zc-check-list">
						<li><?php zc_the_icon( 'check', 17 ); ?><span><?php esc_html_e( 'پس از ثبت درخواست، همکاران ما جهت تایید نهایی تماس می‌گیرند.', 'zarincode' ); ?></span></li>
						<li><?php zc_the_icon( 'check', 17 ); ?><span><?php esc_html_e( 'جلسات مشاوره اولیه کاملاً رایگان است.', 'zarincode' ); ?></span></li>
						<li><?php zc_the_icon( 'check', 17 ); ?><span><?php esc_html_e( 'امکان لغو یا تغییر زمان تا ۲۴ ساعت قبل وجود دارد.', 'zarincode' ); ?></span></li>
						<li><?php zc_the_icon( 'check', 17 ); ?><span><?php echo esc_html( zc_opt( 'zc_working_hours', 'شنبه تا چهارشنبه ۹ تا ۱۸' ) ); ?></span></li>
					</ul>
				</div>
			</div>

			<div class="zc-panel__box">
				<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'phone', 19 ); ?><?php esc_html_e( 'تماس مستقیم', 'zarincode' ); ?></h3></div>
				<div class="zc-panel__box-body">
					<ul class="zc-info-list">
						<li><span><?php zc_the_icon( 'phone', 16 ); ?><?php esc_html_e( 'تلفن', 'zarincode' ); ?></span><strong><?php echo esc_html( zc_fa_num( zc_opt( 'zc_phone', '' ) ) ); ?></strong></li>
						<li><span><?php zc_the_icon( 'mail', 16 ); ?><?php esc_html_e( 'ایمیل', 'zarincode' ); ?></span><strong><?php echo esc_html( zc_opt( 'zc_email', '' ) ); ?></strong></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
get_footer();
