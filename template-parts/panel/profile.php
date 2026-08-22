<?php
/**
 * تب ویرایش پروفایل
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_user = wp_get_current_user();
?>

<div class="zc-panel__box" data-zc-anim="up">
	<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'user', 19 ); ?><?php esc_html_e( 'اطلاعات شخصی', 'zarincode' ); ?></h3></div>
	<div class="zc-panel__box-body">
		<form data-zc-form="zc_update_profile" enctype="multipart/form-data" data-zc-reset="no">

			<div class="zc-avatar-upload">
				<div class="zc-avatar zc-avatar--xl"><?php echo get_avatar( $zc_user->ID, 110 ); ?></div>
				<div>
					<label class="zc-btn zc-btn--ghost zc-btn--sm" style="cursor:pointer">
						<?php zc_the_icon( 'edit', 16 ); ?>
						<span><?php esc_html_e( 'تغییر تصویر پروفایل', 'zarincode' ); ?></span>
						<input type="file" name="avatar" accept="image/*" style="display:none">
					</label>
					<p class="zc-help"><?php esc_html_e( 'فرمت JPG یا PNG، حداکثر ۲ مگابایت', 'zarincode' ); ?></p>
				</div>
			</div>

			<div class="zc-row">
				<div class="zc-field" style="flex:1;min-width:220px">
					<label class="zc-label"><?php esc_html_e( 'نام', 'zarincode' ); ?></label>
					<input type="text" name="first_name" value="<?php echo esc_attr( $zc_user->first_name ); ?>">
				</div>
				<div class="zc-field" style="flex:1;min-width:220px">
					<label class="zc-label"><?php esc_html_e( 'نام خانوادگی', 'zarincode' ); ?></label>
					<input type="text" name="last_name" value="<?php echo esc_attr( $zc_user->last_name ); ?>">
				</div>
			</div>

			<div class="zc-row">
				<div class="zc-field" style="flex:1;min-width:220px">
					<label class="zc-label"><?php esc_html_e( 'ایمیل', 'zarincode' ); ?></label>
					<input type="email" name="email" value="<?php echo esc_attr( $zc_user->user_email ); ?>">
				</div>
				<div class="zc-field" style="flex:1;min-width:220px">
					<label class="zc-label"><?php esc_html_e( 'شماره موبایل', 'zarincode' ); ?></label>
					<input type="tel" value="<?php echo esc_attr( zc_fa_num( get_user_meta( $zc_user->ID, 'zc_mobile', true ) ) ); ?>" disabled>
					<span class="zc-help"><?php esc_html_e( 'برای تغییر موبایل با پشتیبانی تماس بگیرید.', 'zarincode' ); ?></span>
				</div>
			</div>

			<div class="zc-row">
				<div class="zc-field" style="flex:1;min-width:220px">
					<label class="zc-label"><?php esc_html_e( 'شغل / تخصص', 'zarincode' ); ?></label>
					<input type="text" name="job" value="<?php echo esc_attr( get_user_meta( $zc_user->ID, 'zc_job', true ) ); ?>">
				</div>
				<div class="zc-field" style="flex:1;min-width:220px">
					<label class="zc-label"><?php esc_html_e( 'کد ملی', 'zarincode' ); ?></label>
					<input type="text" name="national_id" maxlength="10" inputmode="numeric" value="<?php echo esc_attr( get_user_meta( $zc_user->ID, 'zc_national_id', true ) ); ?>">
				</div>
			</div>

			<div class="zc-field">
				<label class="zc-label"><?php esc_html_e( 'درباره من', 'zarincode' ); ?></label>
				<textarea name="description" rows="4"><?php echo esc_textarea( $zc_user->description ); ?></textarea>
			</div>

			<div class="zc-field">
				<label class="zc-label"><?php esc_html_e( 'آدرس', 'zarincode' ); ?></label>
				<textarea name="address" rows="3"><?php echo esc_textarea( get_user_meta( $zc_user->ID, 'zc_address', true ) ); ?></textarea>
			</div>

			<div class="zc-form-msg"></div>

			<button type="submit" class="zc-btn zc-btn--gold"><?php zc_the_icon( 'check', 18 ); ?><?php esc_html_e( 'ذخیره تغییرات', 'zarincode' ); ?></button>
		</form>
	</div>
</div>
