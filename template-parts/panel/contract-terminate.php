<?php
/**
 * درخواست فسخ قرارداد از پنل کاربری
 *
 * @package Zarincode
 * @var array $zc_c داده‌ی قرارداد.
 */

defined( 'ABSPATH' ) || exit;

$zc_req = get_post_meta( $zc_c['id'], '_zc_ct_term_requested', true );

// اگر درخواستی ثبت شده، وضعیت آن نمایش داده می‌شود.
if ( is_array( $zc_req ) ) :
	$zc_states = array(
		'pending'  => array( __( 'در حال بررسی', 'zarincode' ), '#D89B0D', 'clock' ),
		'approved' => array( __( 'پذیرفته شد', 'zarincode' ), '#1B9C58', 'check' ),
		'rejected' => array( __( 'رد شد', 'zarincode' ), '#B32D2E', 'close' ),
	);

	$zc_st = $zc_states[ $zc_req['status'] ] ?? $zc_states['pending'];
	?>
	<div class="zc-term zc-term--<?php echo esc_attr( $zc_req['status'] ); ?>">
		<div class="zc-term__head">
			<?php zc_the_icon( $zc_st[2], 18 ); ?>
			<strong><?php esc_html_e( 'درخواست فسخ قرارداد', 'zarincode' ); ?></strong>

			<span class="zc-term__badge" style="background:<?php echo esc_attr( $zc_st[1] ); ?>">
				<?php echo esc_html( $zc_st[0] ); ?>
			</span>
		</div>

		<div class="zc-term__rows">
			<div>
				<span><?php esc_html_e( 'دلیل', 'zarincode' ); ?></span>
				<strong><?php echo esc_html( $zc_req['reason'] ); ?></strong>
			</div>
			<div>
				<span><?php esc_html_e( 'تاریخ ثبت', 'zarincode' ); ?></span>
				<strong><?php echo esc_html( zc_fa_num( zc_jalali_date( 'j F Y', $zc_req['created_at'] ) ) ); ?></strong>
			</div>
			<?php if ( ! empty( $zc_req['refund'] ) ) : ?>
				<div>
					<span><?php esc_html_e( 'مبلغ مسترد شده', 'zarincode' ); ?></span>
					<strong><?php echo esc_html( zc_price_text( $zc_req['refund'] ) ); ?></strong>
				</div>
			<?php endif; ?>
		</div>

		<p class="zc-term__details"><?php echo esc_html( $zc_req['details'] ); ?></p>

		<?php if ( ! empty( $zc_req['admin_note'] ) ) : ?>
			<div class="zc-term__answer">
				<strong><?php esc_html_e( 'پاسخ مجری:', 'zarincode' ); ?></strong>
				<p><?php echo esc_html( $zc_req['admin_note'] ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( 'pending' === $zc_req['status'] ) : ?>
			<p class="zc-term__eta">
				<?php zc_the_icon( 'clock', 14 ); ?>
				<?php esc_html_e( 'نتیجه بررسی حداکثر ظرف ۷۲ ساعت کاری از طریق پیامک و گفتگوی قرارداد اعلام می‌شود.', 'zarincode' ); ?>
			</p>
		<?php endif; ?>
	</div>

	<?php
	return;
endif;

// در وضعیت‌های پایانی، فسخ معنا ندارد.
if ( ! zc_contract_can_terminate( $zc_c ) ) {
	return;
}

$zc_reasons = zc_termination_reasons();
$zc_terms   = get_page_by_path( 'terms' );
?>

<details class="zc-term-form" data-zc-terminate data-contract="<?php echo esc_attr( $zc_c['id'] ); ?>">
	<summary class="zc-term-form__toggle">
		<?php zc_the_icon( 'close', 15 ); ?>
		<?php esc_html_e( 'درخواست فسخ قرارداد', 'zarincode' ); ?>
	</summary>

	<div class="zc-term-form__body">

		<div class="zc-term-form__warn">
			<?php zc_the_icon( 'alert', 17 ); ?>
			<div>
				<strong><?php esc_html_e( 'پیش از ثبت درخواست بخوانید', 'zarincode' ); ?></strong>
				<p>
					<?php esc_html_e( 'فسخ قرارداد تابع بند «فسخ قرارداد» سند امضاشده است. در صورت انصراف بدون تقصیر مجری، پیش‌پرداخت مسترد نمی‌شود و کارکرد انجام‌شده تا تاریخ درخواست بر اساس ارزیابی کارشناسی محاسبه و از مبلغ پرداختی کسر می‌گردد.', 'zarincode' ); ?>
				</p>

				<?php if ( $zc_terms ) : ?>
					<a href="<?php echo esc_url( get_permalink( $zc_terms ) ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'مطالعه‌ی کامل شرایط فسخ', 'zarincode' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<div class="zc-field">
			<label class="zc-label" for="zc-term-reason-<?php echo esc_attr( $zc_c['id'] ); ?>">
				<?php esc_html_e( 'دلیل فسخ', 'zarincode' ); ?> <span class="req">*</span>
			</label>

			<select id="zc-term-reason-<?php echo esc_attr( $zc_c['id'] ); ?>" data-zc-term-reason>
				<?php foreach ( $zc_reasons as $zc_k => $zc_label ) : ?>
					<option value="<?php echo esc_attr( $zc_k ); ?>"><?php echo esc_html( $zc_label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="zc-field">
			<label class="zc-label" for="zc-term-det-<?php echo esc_attr( $zc_c['id'] ); ?>">
				<?php esc_html_e( 'توضیحات', 'zarincode' ); ?> <span class="req">*</span>
			</label>

			<textarea id="zc-term-det-<?php echo esc_attr( $zc_c['id'] ); ?>" rows="4" data-zc-term-details
				placeholder="<?php esc_attr_e( 'دلیل درخواست فسخ را با جزئیات شرح دهید (حداقل ۲۰ نویسه)…', 'zarincode' ); ?>"></textarea>
		</div>

		<label class="zc-ct__agree">
			<input type="checkbox" data-zc-term-confirm>
			<span><?php esc_html_e( 'شرایط فسخ مندرج در قرارداد را می‌پذیرم و می‌دانم مبلغ استرداد پس از کسر کارکرد انجام‌شده تعیین می‌شود.', 'zarincode' ); ?></span>
		</label>

		<div class="zc-term-form__msg"></div>

		<button type="button" class="zc-btn zc-btn--danger" data-zc-term-submit disabled>
			<?php esc_html_e( 'ثبت درخواست فسخ', 'zarincode' ); ?>
		</button>
	</div>
</details>
