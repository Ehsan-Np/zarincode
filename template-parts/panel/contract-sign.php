<?php
/**
 * بخش تأیید پیامکی و امضای دیجیتال
 *
 * هم در گام سوم ساخت قرارداد و هم در نمای قرارداد امضانشده
 * فراخوانی می‌شود؛ شناسه‌ی قرارداد را از متغیر $zc_c می‌گیرد یا
 * جاوااسکریپت آن را هنگام ساخت پیش‌نویس تعیین می‌کند.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_sign_id = isset( $zc_c['id'] ) ? (int) $zc_c['id'] : 0;
?>
<div class="zc-ct-sign" data-zc-ct-sign data-id="<?php echo esc_attr( $zc_sign_id ); ?>">

	<h3 class="zc-ct-sign__h">
		<?php zc_the_icon( 'lock', 18 ); ?>
		<?php esc_html_e( 'تأیید و امضای قرارداد', 'zarincode' ); ?>
	</h3>

	<p class="zc-ct-sign__lead">
		<?php esc_html_e( 'برای امضای قرارداد، ابتدا کد تأیید را دریافت و وارد کنید، سپس امضای خود را در کادر زیر بکشید.', 'zarincode' ); ?>
	</p>

	<div class="zc-ct-sign__grid">

		<!-- کد یک‌بارمصرف -->
		<div class="zc-ct-sign__col">
			<label class="zc-label"><?php esc_html_e( 'کد تأیید پیامکی', 'zarincode' ); ?> <span class="req">*</span></label>

			<div class="zc-ct-otp">
				<input type="text" class="zc-ct-otp__in" data-zc-ct-code inputmode="numeric"
					maxlength="5" placeholder="- - - - -" autocomplete="one-time-code">

				<button type="button" class="zc-btn zc-btn--ghost zc-btn--sm" data-zc-ct-otp>
					<?php esc_html_e( 'ارسال کد', 'zarincode' ); ?>
				</button>
			</div>

			<span class="zc-help" data-zc-ct-timer>
				<?php esc_html_e( 'کد به شماره‌ی موبایل ثبت‌شده در قرارداد ارسال می‌شود.', 'zarincode' ); ?>
			</span>
		</div>

		<!-- امضا -->
		<div class="zc-ct-sign__col">
			<label class="zc-label"><?php esc_html_e( 'امضای دیجیتال', 'zarincode' ); ?> <span class="req">*</span></label>

			<div class="zc-sigpad" data-zc-sigpad>
				<canvas class="zc-sigpad__canvas" width="600" height="200"
					aria-label="<?php esc_attr_e( 'کادر امضا', 'zarincode' ); ?>"></canvas>

				<span class="zc-sigpad__hint"><?php esc_html_e( 'با ماوس یا انگشت امضا کنید', 'zarincode' ); ?></span>

				<button type="button" class="zc-sigpad__clear" data-zc-sigpad-clear>
					<?php esc_html_e( 'پاک کردن', 'zarincode' ); ?>
				</button>
			</div>
		</div>
	</div>

	<label class="zc-ct__agree">
		<input type="checkbox" data-zc-ct-final>
		<span>
			<?php esc_html_e( 'تأیید می‌کنم اطلاعات واردشده صحیح است و این امضا حکم امضای دست‌نویس من را دارد.', 'zarincode' ); ?>
		</span>
	</label>

	<div class="zc-form-msg"></div>

	<button type="button" class="zc-btn zc-btn--gold zc-btn--lg zc-btn--block" data-zc-ct-submit disabled>
		<?php zc_the_icon( 'check', 17 ); ?>
		<?php esc_html_e( 'امضا و ثبت نهایی قرارداد', 'zarincode' ); ?>
	</button>
</div>
