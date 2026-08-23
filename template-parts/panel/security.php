<?php
/**
 * تب امنیت و رمز عبور
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_user = wp_get_current_user();
?>

<div class="zc-panel__grid-2">
	<div class="zc-panel__box" data-zc-anim="up">
		<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'lock', 19 ); ?><?php esc_html_e( 'تغییر رمز عبور', 'zarincode' ); ?></h3></div>
		<div class="zc-panel__box-body">
			<form data-zc-form="zc_change_password">
				<div class="zc-field">
					<label class="zc-label"><?php esc_html_e( 'رمز عبور فعلی', 'zarincode' ); ?> <span class="req">*</span></label>
					<input type="password" name="current_password" required autocomplete="current-password">
				</div>
				<div class="zc-field">
					<label class="zc-label"><?php esc_html_e( 'رمز عبور جدید', 'zarincode' ); ?> <span class="req">*</span></label>
					<input type="password" name="new_password" required minlength="8" autocomplete="new-password">
					<span class="zc-help"><?php esc_html_e( 'حداقل ۸ کاراکتر، ترکیبی از حروف و اعداد', 'zarincode' ); ?></span>
				</div>
				<div class="zc-field">
					<label class="zc-label"><?php esc_html_e( 'تکرار رمز عبور جدید', 'zarincode' ); ?> <span class="req">*</span></label>
					<input type="password" name="confirm_password" required minlength="8" autocomplete="new-password">
				</div>
				<div class="zc-form-msg"></div>
				<button type="submit" class="zc-btn zc-btn--gold zc-btn--block"><?php zc_the_icon( 'key', 17 ); ?><?php esc_html_e( 'تغییر رمز عبور', 'zarincode' ); ?></button>
			</form>
		</div>
	</div>

	<div class="zc-panel__box" data-zc-anim="up" data-zc-delay="90">
		<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'shield', 19 ); ?><?php esc_html_e( 'اطلاعات حساب', 'zarincode' ); ?></h3></div>
		<div class="zc-panel__box-body">
			<ul class="zc-info-list">
				<li>
					<span><?php zc_the_icon( 'user', 16 ); ?><?php esc_html_e( 'نام کاربری', 'zarincode' ); ?></span>
					<strong><?php echo esc_html( $zc_user->user_login ); ?></strong>
				</li>
				<li>
					<span><?php zc_the_icon( 'calendar', 16 ); ?><?php esc_html_e( 'تاریخ عضویت', 'zarincode' ); ?></span>
					<strong><?php echo esc_html( zc_fa_num( wp_date( 'Y/m/d', strtotime( $zc_user->user_registered ) ) ) ); ?></strong>
				</li>
				<li>
					<span><?php zc_the_icon( 'clock', 16 ); ?><?php esc_html_e( 'آخرین ورود', 'zarincode' ); ?></span>
					<strong>
						<?php
						$zc_last = get_user_meta( $zc_user->ID, 'zc_last_login', true );
						echo esc_html( $zc_last ? zc_fa_num( wp_date( 'Y/m/d H:i', strtotime( $zc_last ) ) ) : '—' );
						?>
					</strong>
				</li>
				<li>
					<span><?php zc_the_icon( 'phone', 16 ); ?><?php esc_html_e( 'وضعیت موبایل', 'zarincode' ); ?></span>
					<strong>
						<?php if ( get_user_meta( $zc_user->ID, 'zc_mobile_verified', true ) ) : ?>
							<span class="zc-badge zc-badge--green"><?php zc_the_icon( 'check', 13 ); ?><?php esc_html_e( 'تایید شده', 'zarincode' ); ?></span>
						<?php else : ?>
							<span class="zc-badge zc-badge--orange"><?php esc_html_e( 'تایید نشده', 'zarincode' ); ?></span>
						<?php endif; ?>
					</strong>
				</li>
			</ul>

			<div class="zc-alert zc-alert--info" style="margin-top:16px">
				<?php zc_the_icon( 'info', 18 ); ?>
				<span><?php esc_html_e( 'برای امنیت بیشتر، رمز عبور خود را هر چند ماه یکبار تغییر دهید و آن را در اختیار دیگران قرار ندهید.', 'zarincode' ); ?></span>
			</div>
		</div>
	</div>
</div>
