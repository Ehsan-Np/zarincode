<?php
/**
 * بخش پرداخت مرحله‌ای در نمای قرارداد
 *
 * از متغیر $zc_c (داده‌ی قرارداد) استفاده می‌کند.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/*
 * پرداخت تنها پس از امضای قرارداد، اعلام مبلغ و تأیید کارفرما
 * نمایش داده می‌شود؛ پیش از آن مبلغی مورد توافق نیست.
 */
if ( ! zc_contract_amount_approved( $zc_c ) ) {
	require ZC_DIR . 'template-parts/panel/contract-quote.php';
	return;
}

$zc_plan  = zc_contract_payment_plan( $zc_c['id'] );
$zc_msg   = zc_get_pay_message();
$zc_files = zc_contract_deliverables( $zc_c['id'] );
$zc_bal   = zc_wallet_balance();

/*
 * قرارداد خاتمه‌یافته یا در حال بررسی فسخ، پرداخت‌پذیر نیست؛
 * جدول مراحل فقط به عنوان سابقه نمایش داده می‌شود.
 */
$zc_frozen = in_array( $zc_c['status'], array( 'terminated', 'canceled', 'terminating' ), true );

if ( ! $zc_plan['stages'] ) {
	return;
}
?>

<div class="zc-pay" data-zc-pay data-contract="<?php echo esc_attr( $zc_c['id'] ); ?>">

	<?php if ( $zc_msg ) : ?>
		<div class="zc-pay__flash is-<?php echo esc_attr( $zc_msg['type'] ); ?>">
			<?php zc_the_icon( 'success' === $zc_msg['type'] ? 'check' : 'alert', 17 ); ?>
			<span><?php echo esc_html( $zc_msg['text'] ); ?></span>
		</div>
	<?php endif; ?>

	<?php if ( $zc_frozen ) : ?>
		<div class="zc-pay__frozen">
			<?php zc_the_icon( 'alert', 17 ); ?>
			<span>
				<?php
				echo 'terminating' === $zc_c['status']
					? esc_html__( 'تا تعیین تکلیف درخواست فسخ، پرداخت جدید غیرفعال است.', 'zarincode' )
					: esc_html__( 'این قرارداد خاتمه یافته است؛ مراحل زیر تنها به عنوان سابقه نمایش داده می‌شوند.', 'zarincode' );
				?>
			</span>
		</div>
	<?php endif; ?>

	<div class="zc-pay__head">
		<h3 class="zc-pay__title">
			<?php zc_the_icon( 'wallet', 19 ); ?>
			<?php esc_html_e( 'پرداخت مرحله‌ای پروژه', 'zarincode' ); ?>
		</h3>

		<span class="zc-pay__count">
			<?php
			printf(
				/* translators: 1: تعداد مراحل */
				esc_html__( '%s مرحله', 'zarincode' ),
				esc_html( zc_fa_num( count( $zc_plan['stages'] ) ) )
			);
			?>
		</span>
	</div>

	<!-- خلاصه مالی -->
	<div class="zc-pay__summary">
		<div class="zc-pay__stat">
			<span><?php esc_html_e( 'مبلغ کل قرارداد', 'zarincode' ); ?></span>
			<strong><?php echo esc_html( zc_price_text( $zc_plan['total'] ) ); ?></strong>
		</div>

		<div class="zc-pay__stat zc-pay__stat--ok">
			<span><?php esc_html_e( 'پرداخت‌شده', 'zarincode' ); ?></span>
			<strong><?php echo esc_html( zc_price_text( $zc_plan['paid'] ) ); ?></strong>
		</div>

		<div class="zc-pay__stat zc-pay__stat--due">
			<span><?php esc_html_e( 'مانده', 'zarincode' ); ?></span>
			<strong><?php echo esc_html( zc_price_text( $zc_plan['remaining'] ) ); ?></strong>
		</div>
	</div>

	<!-- نوار پیشرفت پرداخت -->
	<div class="zc-pay__bar">
		<div class="zc-pay__bar-top">
			<span><?php esc_html_e( 'پیشرفت پرداخت', 'zarincode' ); ?></span>
			<strong><?php echo esc_html( zc_fa_num( $zc_plan['percent'] ) ); ?>٪</strong>
		</div>

		<div class="zc-pay__bar-track">
			<span style="width:<?php echo esc_attr( $zc_plan['percent'] ); ?>%"></span>
		</div>
	</div>

	<!-- مراحل -->
	<ol class="zc-stages">
		<?php foreach ( $zc_plan['stages'] as $zc_s ) : ?>
			<li class="zc-stage is-<?php echo esc_attr( $zc_s['status'] ); ?>">

				<span class="zc-stage__dot">
					<?php if ( 'paid' === $zc_s['status'] ) : ?>
						<?php zc_the_icon( 'check', 15 ); ?>
					<?php elseif ( 'locked' === $zc_s['status'] ) : ?>
						<?php zc_the_icon( 'lock', 14 ); ?>
					<?php else : ?>
						<?php echo esc_html( zc_fa_num( $zc_s['index'] + 1 ) ); ?>
					<?php endif; ?>
				</span>

				<div class="zc-stage__body">
					<div class="zc-stage__top">
						<strong class="zc-stage__name"><?php echo esc_html( $zc_s['title'] ); ?></strong>

						<span class="zc-stage__percent">
							<?php echo esc_html( zc_fa_num( $zc_s['percent'] ) ); ?>٪
						</span>
					</div>

					<?php if ( $zc_s['note'] ) : ?>
						<p class="zc-stage__note"><?php echo esc_html( $zc_s['note'] ); ?></p>
					<?php endif; ?>

					<?php if ( 'paid' === $zc_s['status'] ) : ?>
						<div class="zc-stage__meta">
							<span>
								<?php zc_the_icon( 'calendar', 13 ); ?>
								<?php echo esc_html( zc_fa_num( zc_jalali_date( 'j F Y — H:i', $zc_s['paid_at'] ) ) ); ?>
							</span>

							<?php if ( $zc_s['ref_id'] ) : ?>
								<span>
									<?php zc_the_icon( 'check', 13 ); ?>
									<?php esc_html_e( 'کد پیگیری:', 'zarincode' ); ?>
									<code dir="ltr"><?php echo esc_html( $zc_s['ref_id'] ); ?></code>
								</span>
							<?php endif; ?>
						</div>

					<?php elseif ( 'locked' === $zc_s['status'] ) : ?>
						<p class="zc-stage__lock">
							<?php zc_the_icon( 'lock', 13 ); ?>
							<?php
							if ( ! $zc_plan['signed'] ) {
								esc_html_e( 'پس از امضای قرارداد فعال می‌شود.', 'zarincode' );
							} else {
								printf(
									/* translators: 1: آستانه 2: پیشرفت فعلی */
									esc_html__( 'با رسیدن پیشرفت پروژه به %1$s٪ فعال می‌شود (پیشرفت فعلی: %2$s٪)', 'zarincode' ),
									esc_html( zc_fa_num( $zc_s['threshold'] ) ),
									esc_html( zc_fa_num( $zc_plan['progress'] ) )
								);
							}
							?>
						</p>
					<?php endif; ?>
				</div>

				<div class="zc-stage__side">
					<span class="zc-stage__amount"><?php echo esc_html( zc_price_text( $zc_s['amount'] ) ); ?></span>

					<?php if ( 'due' === $zc_s['status'] && ! $zc_frozen ) : ?>
						<div class="zc-stage__actions">
							<button type="button" class="zc-btn zc-btn--gold zc-btn--sm"
								data-zc-pay-gateway="<?php echo esc_attr( $zc_s['index'] ); ?>">
								<?php zc_the_icon( 'wallet', 15 ); ?>
								<?php esc_html_e( 'پرداخت آنلاین', 'zarincode' ); ?>
							</button>

							<?php if ( $zc_bal >= $zc_s['amount'] ) : ?>
								<button type="button" class="zc-btn zc-btn--ghost zc-btn--sm"
									data-zc-pay-wallet="<?php echo esc_attr( $zc_s['index'] ); ?>">
									<?php esc_html_e( 'از کیف پول', 'zarincode' ); ?>
								</button>
							<?php endif; ?>
						</div>
					<?php elseif ( 'paid' === $zc_s['status'] ) : ?>
						<span class="zc-stage__badge"><?php esc_html_e( 'پرداخت شد', 'zarincode' ); ?></span>
					<?php endif; ?>
				</div>
			</li>
		<?php endforeach; ?>
	</ol>

	<div class="zc-pay__msg"></div>

	<!-- تحویل پروژه -->
	<div class="zc-deliver <?php echo $zc_plan['settled'] ? 'is-open' : 'is-locked'; ?>">
		<div class="zc-deliver__head">
			<?php zc_the_icon( $zc_plan['settled'] ? 'download' : 'lock', 18 ); ?>
			<strong><?php esc_html_e( 'فایل‌های تحویلی پروژه', 'zarincode' ); ?></strong>
		</div>

		<?php if ( ! $zc_plan['settled'] ) : ?>
			<p class="zc-deliver__note">
				<?php esc_html_e( 'دسترسی به فایل‌های نهایی پروژه پس از تسویه کامل مبلغ قرارداد باز می‌شود.', 'zarincode' ); ?>
			</p>

		<?php elseif ( ! $zc_files ) : ?>
			<p class="zc-deliver__note">
				<?php esc_html_e( 'تسویه انجام شده است. فایل‌ها به‌زودی توسط مجری بارگذاری می‌شوند.', 'zarincode' ); ?>
			</p>

		<?php else : ?>
			<div class="zc-deliver__list">
				<?php foreach ( $zc_files as $zc_i => $zc_f ) : ?>
					<a class="zc-deliver__file" href="<?php echo esc_url( zc_contract_file_url( $zc_c['id'], $zc_i ) ); ?>">
						<?php zc_the_icon( 'file', 18 ); ?>
						<span><?php echo esc_html( $zc_f['name'] ?? '' ); ?></span>
						<?php zc_the_icon( 'download', 16 ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
