<?php
/**
 * صفحه‌ی پیشخوان «پیامک و کارزارها»
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * ثبت زیرمنو.
 *
 * @return void
 */
function zc_register_sms_page() {
	add_submenu_page(
		'zarincode',
		__( 'پیامک و کارزارها', 'zarincode' ),
		__( 'پیامک و کارزارها', 'zarincode' ),
		'manage_options',
		'zarincode-sms',
		'zc_admin_sms_page'
	);
}
add_action( 'admin_menu', 'zc_register_sms_page', 21 );

/**
 * خروجی صفحه.
 *
 * @return void
 */
function zc_admin_sms_page() {
	$days   = isset( $_GET['days'] ) ? max( 1, min( 365, (int) $_GET['days'] ) ) : 30; // phpcs:ignore
	$stats  = zc_sms_stats( 30 );
	$recent = zc_sms_recent( 25 );
	$cost   = function_exists( 'zc_sms_cost_report' ) ? zc_sms_cost_report( $days ) : array();
	$ready  = function_exists( 'zc_sms' ) && zc_sms()->is_ready();
	?>
	<div class="wrap zc-admin-wrap zc-sms">
		<?php zc_admin_notice_anchor(); ?>
		<h1><?php esc_html_e( 'پیامک و کارزارهای خودکار', 'zarincode' ); ?></h1>

		<?php if ( ! $ready ) : ?>
			<div class="notice notice-warning">
				<p>
					<?php esc_html_e( 'سامانه پیامک هنوز پیکربندی نشده است. کلید API و شماره فرستنده را در تنظیمات قالب ← پیامک وارد کنید.', 'zarincode' ); ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=zarincode-options' ) ); ?>">
						<?php esc_html_e( 'رفتن به تنظیمات', 'zarincode' ); ?>
					</a>
				</p>
			</div>
		<?php endif; ?>

		<!-- آمار -->
		<div class="zc-sms-cards">
			<div class="zc-sms-card">
				<span class="zc-sms-card__num"><?php echo esc_html( zc_fa_num( $stats['total'] ) ); ?></span>
				<span class="zc-sms-card__label"><?php esc_html_e( 'کل پیامک‌ها (۳۰ روز)', 'zarincode' ); ?></span>
			</div>

			<div class="zc-sms-card zc-sms-card--ok">
				<span class="zc-sms-card__num"><?php echo esc_html( zc_fa_num( $stats['sent'] ) ); ?></span>
				<span class="zc-sms-card__label"><?php esc_html_e( 'ارسال موفق', 'zarincode' ); ?></span>
			</div>

			<div class="zc-sms-card zc-sms-card--bad">
				<span class="zc-sms-card__num"><?php echo esc_html( zc_fa_num( $stats['failed'] ) ); ?></span>
				<span class="zc-sms-card__label"><?php esc_html_e( 'ناموفق', 'zarincode' ); ?></span>
			</div>

			<?php
			$next_h = wp_next_scheduled( 'zc_sms_hourly' );
			$next_d = wp_next_scheduled( 'zc_sms_daily' );
			?>
			<div class="zc-sms-card">
				<span class="zc-sms-card__num" style="font-size:15px">
					<?php echo $next_h ? esc_html( zc_fa_num( wp_date( 'H:i', $next_h ) ) ) : '—'; ?>
					/
					<?php echo $next_d ? esc_html( zc_fa_num( wp_date( 'H:i', $next_d ) ) ) : '—'; ?>
				</span>
				<span class="zc-sms-card__label"><?php esc_html_e( 'اجرای بعدی ساعتی / روزانه', 'zarincode' ); ?></span>
			</div>

			<div class="zc-sms-card zc-sms-card--cost">
				<span class="zc-sms-card__num"><?php echo esc_html( zc_fa_num( number_format( (int) ( $cost['total_cost'] ?? 0 ) ) ) ); ?></span>
				<span class="zc-sms-card__label"><?php echo esc_html( sprintf( __( 'هزینه‌ی برآوردی پیامک (ریال، %s روز)', 'zarincode' ), zc_fa_num( $days ) ) ); ?></span>
			</div>
		</div>

		<?php if ( ! empty( $cost['by_type'] ) ) : ?>
		<div class="zc-sms-box" style="margin-top:18px">
			<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
				<h2><?php esc_html_e( 'گزارش هزینه به تفکیک نوع پیام', 'zarincode' ); ?></h2>
				<div>
					<form method="get" style="display:inline-flex;gap:6px;align-items:center">
						<input type="hidden" name="page" value="zarincode-sms">
						<select name="days">
							<option value="7">7 <?php esc_html_e( 'روز', 'zarincode' ); ?></option>
							<option value="30" selected>30 <?php esc_html_e( 'روز', 'zarincode' ); ?></option>
							<option value="90">90 <?php esc_html_e( 'روز', 'zarincode' ); ?></option>
							<option value="365">365 <?php esc_html_e( 'روز', 'zarincode' ); ?></option>
						</select>
						<button class="button"><?php esc_html_e( 'اعمال', 'zarincode' ); ?></button>
					</form>
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=zarincode-sms&zc_sms_pdf=1&days=' . ( isset( $_GET['days'] ) ? (int) $_GET['days'] : 30 ) ) ); ?>" target="_blank"><?php esc_html_e( 'خروجی PDF', 'zarincode' ); ?></a>
				</div>
			</div>
			<table class="widefat striped zc-sms-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'نوع', 'zarincode' ); ?></th>
						<th><?php esc_html_e( 'تعداد', 'zarincode' ); ?></th>
						<th><?php esc_html_e( 'هزینه (ریال)', 'zarincode' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $cost['by_type'] as $type => $c ) : ?>
						<tr>
							<td><?php echo esc_html( zc_sms_type_label( $type ) ); ?></td>
							<td><?php echo esc_html( zc_fa_num( (int) ( $cost['count_type'][ $type ] ?? 0 ) ) ); ?></td>
							<td><strong><?php echo esc_html( zc_fa_num( number_format( (int) $c ) ) ); ?></strong></td>
						</tr>
					<?php endforeach; ?>
					<tr>
						<td><strong><?php esc_html_e( 'جمع', 'zarincode' ); ?></strong></td>
						<td><strong><?php echo esc_html( zc_fa_num( (int) ( $cost['total_sent'] ?? 0 ) ) ); ?></strong></td>
						<td><strong><?php echo esc_html( zc_fa_num( number_format( (int) ( $cost['total_cost'] ?? 0 ) ) ) ); ?></strong></td>
					</tr>
				</tbody>
			</table>
			<p class="description"><?php esc_html_e( 'هزینه بر اساس طول هر پیام و قیمت هر بخش (تنظیمات ← پیامک) برآورد می‌شود.', 'zarincode' ); ?></p>
		</div>
		<?php endif; ?>

		<div class="zc-sms-grid">

			<!-- کارزارها -->
			<div class="zc-sms-box">
				<h2><?php esc_html_e( 'کارزارهای خودکار', 'zarincode' ); ?></h2>

				<table class="widefat striped zc-sms-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'کارزار', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'اجرای دستی', 'zarincode' ); ?></th>
						</tr>
					</thead>

					<tbody>
						<tr>
							<td>
								<strong><?php esc_html_e( 'خوش‌آمدگویی کاربر تازه', 'zarincode' ); ?></strong>
								<p class="description"><?php esc_html_e( 'بلافاصله پس از ثبت‌نام، همراه کد تخفیف اختصاصی.', 'zarincode' ); ?></p>
							</td>
							<td><?php zc_sms_status_pill( zc_opt( 'zc_sms_welcome_enable', false ) ); ?></td>
							<td><span class="description"><?php esc_html_e( 'خودکار', 'zarincode' ); ?></span></td>
						</tr>

						<tr>
							<td>
								<strong><?php esc_html_e( 'یادآوری پرداخت ناتمام', 'zarincode' ); ?></strong>
								<p class="description">
									<?php
									printf(
										/* translators: %s: ساعت */
										esc_html__( '%s ساعت پس از رفتن کاربر به درگاه، اگر پرداخت نشده باشد.', 'zarincode' ),
										esc_html( zc_fa_num( zc_opt( 'zc_sms_abandoned_hours', 2 ) ) )
									);
									?>
								</p>
							</td>
							<td><?php zc_sms_status_pill( zc_opt( 'zc_sms_abandoned_enable', false ) ); ?></td>
							<td>
								<button type="button" class="button zc-run-campaign" data-campaign="abandoned">
									<?php esc_html_e( 'اجرا کن', 'zarincode' ); ?>
								</button>
							</td>
						</tr>

						<tr>
							<td>
								<strong><?php esc_html_e( 'بازگرداندن مشتری غیرفعال', 'zarincode' ); ?></strong>
								<p class="description">
									<?php
									printf(
										/* translators: 1: ماه 2: درصد */
										esc_html__( 'کاربرانی که %1$s ماه خرید نکرده‌اند، کد تخفیف %2$s٪ می‌گیرند.', 'zarincode' ),
										esc_html( zc_fa_num( zc_opt( 'zc_sms_winback_months', 3 ) ) ),
										esc_html( zc_fa_num( zc_opt( 'zc_sms_winback_percent', 30 ) ) )
									);
									?>
								</p>
							</td>
							<td><?php zc_sms_status_pill( zc_opt( 'zc_sms_winback_enable', false ) ); ?></td>
							<td>
								<button type="button" class="button zc-run-campaign" data-campaign="winback">
									<?php esc_html_e( 'اجرا کن', 'zarincode' ); ?>
								</button>
							</td>
						</tr>
					</tbody>
				</table>

				<p class="description" style="margin-top:12px">
					<?php esc_html_e( 'روشن/خاموش کردن و تنظیم متن هر کارزار در: تنظیمات قالب ← پیامک و کارزارها', 'zarincode' ); ?>
				</p>
			</div>

			<!-- ارسال آزمایشی -->
			<div class="zc-sms-box">
				<h2><?php esc_html_e( 'ارسال آزمایشی', 'zarincode' ); ?></h2>

				<p>
					<label for="zc-test-mobile"><?php esc_html_e( 'شماره موبایل', 'zarincode' ); ?></label>
					<input type="text" id="zc-test-mobile" dir="ltr" placeholder="09xxxxxxxxx" class="regular-text" />
				</p>

				<p>
					<label for="zc-test-msg"><?php esc_html_e( 'متن', 'zarincode' ); ?></label>
					<textarea id="zc-test-msg" rows="3" class="large-text"><?php esc_html_e( 'پیام آزمایشی زرین کد.', 'zarincode' ); ?></textarea>
				</p>

				<button type="button" class="button button-primary" id="zc-send-test">
					<?php esc_html_e( 'ارسال آزمایشی', 'zarincode' ); ?>
				</button>
			</div>
		</div>

		<!-- ارسال گروهی -->
		<div class="zc-sms-box">
			<h2><?php esc_html_e( 'ارسال گروهی به کاربران', 'zarincode' ); ?></h2>

			<table class="form-table">
				<tr>
					<th><label for="zc-bulk-aud"><?php esc_html_e( 'گیرندگان', 'zarincode' ); ?></label></th>
					<td>
						<select id="zc-bulk-aud">
							<option value="all"><?php esc_html_e( 'همه‌ی کاربران', 'zarincode' ); ?></option>
							<option value="customers"><?php esc_html_e( 'فقط مشتریان', 'zarincode' ); ?></option>
							<option value="no_purchase"><?php esc_html_e( 'کاربرانی که هرگز خرید نکرده‌اند', 'zarincode' ); ?></option>
						</select>
					</td>
				</tr>

				<tr>
					<th><label for="zc-bulk-percent"><?php esc_html_e( 'درصد تخفیف', 'zarincode' ); ?></label></th>
					<td>
						<input type="number" id="zc-bulk-percent" value="0" min="0" max="100" style="width:90px" />
						<span class="description"><?php esc_html_e( 'صفر یعنی بدون کد تخفیف. اگر عددی بگذارید، برای هر کاربر یک کد یکتا ساخته می‌شود.', 'zarincode' ); ?></span>
					</td>
				</tr>

				<tr>
					<th><label for="zc-bulk-days"><?php esc_html_e( 'اعتبار کد (روز)', 'zarincode' ); ?></label></th>
					<td><input type="number" id="zc-bulk-days" value="30" min="1" style="width:90px" /></td>
				</tr>

				<tr>
					<th><label for="zc-bulk-msg"><?php esc_html_e( 'متن پیام', 'zarincode' ); ?></label></th>
					<td>
						<textarea id="zc-bulk-msg" rows="4" class="large-text"><?php echo esc_textarea( __( "{name} عزیز، کد تخفیف {percent}٪ ویژه شما: {code}\nاعتبار {days} روز — {site}", 'zarincode' ) ); ?></textarea>
						<p class="description">
							<?php esc_html_e( 'متغیرها: {name} {code} {percent} {days} {site} {url}', 'zarincode' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<button type="button" class="button button-primary button-large" id="zc-send-bulk">
				<?php esc_html_e( 'ارسال گروهی', 'zarincode' ); ?>
			</button>

			<span class="description" style="margin-inline-start:10px">
				<?php esc_html_e( 'حداکثر ۲۰۰ کاربر در هر اجرا.', 'zarincode' ); ?>
			</span>
		</div>

		<!-- گزارش -->
		<div class="zc-sms-box">
			<h2><?php esc_html_e( 'آخرین پیامک‌ها', 'zarincode' ); ?></h2>

			<?php if ( ! $recent ) : ?>
				<p class="description"><?php esc_html_e( 'هنوز پیامکی ارسال نشده است.', 'zarincode' ); ?></p>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'موبایل', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'نوع', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'متن', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'زمان', 'zarincode' ); ?></th>
						</tr>
					</thead>

					<tbody>
						<?php foreach ( $recent as $row ) : ?>
							<tr>
								<td dir="ltr"><?php echo esc_html( $row->mobile ); ?></td>
								<td><?php echo esc_html( zc_sms_type_label( $row->type ) ); ?></td>
								<td><?php echo esc_html( wp_trim_words( $row->message, 12 ) ); ?></td>
								<td>
									<?php if ( 'sent' === $row->status ) : ?>
										<span class="zc-pill zc-pill--ok"><?php esc_html_e( 'ارسال شد', 'zarincode' ); ?></span>
									<?php else : ?>
										<span class="zc-pill zc-pill--bad" title="<?php echo esc_attr( $row->error ); ?>">
											<?php esc_html_e( 'ناموفق', 'zarincode' ); ?>
										</span>
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( zc_fa_num( mysql2date( 'j F H:i', $row->created_at ) ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>

	<script>
		jQuery( function ( $ ) {
			var nonce = '<?php echo esc_js( wp_create_nonce( 'zc_admin_nonce' ) ); ?>';

			function busy( btn, on ) {
				btn.prop( 'disabled', on );
			}

			$( '#zc-send-test' ).on( 'click', function () {
				var b = $( this );
				busy( b, true );

				$.post( ajaxurl, {
					action: 'zc_sms_test', nonce: nonce,
					mobile: $( '#zc-test-mobile' ).val(),
					message: $( '#zc-test-msg' ).val()
				} ).done( function ( r ) {
					alert( r.data.message );
				} ).always( function () { busy( b, false ); } );
			} );

			$( '.zc-run-campaign' ).on( 'click', function () {
				var b = $( this );
				busy( b, true );

				$.post( ajaxurl, {
					action: 'zc_sms_run_campaign', nonce: nonce,
					campaign: b.data( 'campaign' )
				} ).done( function ( r ) {
					alert( r.data.message );
					if ( r.success ) { location.reload(); }
				} ).always( function () { busy( b, false ); } );
			} );

			$( '#zc-send-bulk' ).on( 'click', function () {
				if ( ! confirm( '<?php echo esc_js( __( 'پیامک برای همه‌ی کاربران انتخاب‌شده ارسال شود؟', 'zarincode' ) ); ?>' ) ) {
					return;
				}

				var b = $( this );
				busy( b, true );
				b.text( '<?php echo esc_js( __( 'در حال ارسال…', 'zarincode' ) ); ?>' );

				$.post( ajaxurl, {
					action: 'zc_sms_bulk', nonce: nonce,
					audience: $( '#zc-bulk-aud' ).val(),
					message: $( '#zc-bulk-msg' ).val(),
					percent: $( '#zc-bulk-percent' ).val(),
					days: $( '#zc-bulk-days' ).val()
				} ).done( function ( r ) {
					alert( r.data.message );
					if ( r.success ) { location.reload(); }
				} ).always( function () {
					busy( b, false );
					b.text( '<?php echo esc_js( __( 'ارسال گروهی', 'zarincode' ) ); ?>' );
				} );
			} );
		} );
	</script>
	<?php
}

/**
 * نشان وضعیت روشن/خاموش.
 *
 * @param bool $on وضعیت.
 * @return void
 */
function zc_sms_status_pill( $on ) {
	if ( $on ) {
		echo '<span class="zc-pill zc-pill--ok">' . esc_html__( 'فعال', 'zarincode' ) . '</span>';
	} else {
		echo '<span class="zc-pill zc-pill--off">' . esc_html__( 'غیرفعال', 'zarincode' ) . '</span>';
	}
}

/**
 * اندپوینت چاپ/PDF گزارش هزینه‌ی پیامک (نمای تمیز + window.print).
 *
 * @return void
 */
function zc_sms_cost_pdf_view() {
	if ( empty( $_GET['zc_sms_pdf'] ) || ! current_user_can( 'manage_options' ) ) { // phpcs:ignore
		return;
	}

	$cost = function_exists( 'zc_sms_cost_report' ) ? zc_sms_cost_report( 30 ) : array();
	$days = isset( $_GET['days'] ) ? max( 1, min( 365, (int) $_GET['days'] ) ) : 30; // phpcs:ignore
	if ( 30 !== $days && function_exists( 'zc_sms_cost_report' ) ) {
		$cost = zc_sms_cost_report( $days );
	}
	?>
	<!DOCTYPE html>
	<html lang="fa" dir="rtl">
	<head>
		<meta charset="UTF-8">
		<title><?php esc_html_e( 'گزارش هزینه پیامک', 'zarincode' ); ?></title>
		<style>
			body{font-family:Tahoma,Arial,sans-serif;color:#1f2437;padding:30px;max-width:800px;margin:auto}
			h1{font-size:20px;border-bottom:2px solid #C9A227;padding-bottom:10px}
			table{width:100%;border-collapse:collapse;margin-top:18px}
			th,td{border:1px solid #dcdcde;padding:9px 12px;text-align:right;font-size:13px}
			th{background:#FBF3DD}
			.summary{display:flex;gap:20px;margin-top:14px;flex-wrap:wrap}
			.summary div{background:#f7f9fc;border:1px solid #dcdcde;border-radius:8px;padding:12px 18px}
			.summary b{display:block;font-size:18px}
			@media print{body{padding:0}.noprint{display:none}}
		</style>
	</head>
	<body>
		<div class="noprint" style="margin-bottom:16px">
			<button onclick="window.print()" style="padding:10px 20px;background:#C9A227;color:#fff;border:0;border-radius:8px;cursor:pointer"><?php esc_html_e( 'ذخیره به‌عنوان PDF', 'zarincode' ); ?></button>
		</div>
		<h1><?php esc_html_e( 'گزارش هزینه‌ی پیامک زرین کد', 'zarincode' ); ?></h1>
		<p style="color:#6b7280"><?php echo esc_html( get_bloginfo( 'name' ) ); ?> — <?php echo esc_html( date_i18n( 'Y/m/d H:i' ) ); ?> — <?php echo esc_html( zc_fa_num( $days ) ); ?> <?php esc_html_e( 'روز اخیر', 'zarincode' ); ?></p>

		<div class="summary">
			<div><b><?php echo esc_html( zc_fa_num( number_format( (int) ( $cost['total_cost'] ?? 0 ) ) ) ); ?></b><?php esc_html_e( 'هزینه کل (ریال)', 'zarincode' ); ?></div>
			<div><b><?php echo esc_html( zc_fa_num( (int) ( $cost['total_sent'] ?? 0 ) ) ); ?></b><?php esc_html_e( 'تعداد ارسال', 'zarincode' ); ?></div>
		</div>

		<table>
			<thead><tr><th><?php esc_html_e( 'نوع', 'zarincode' ); ?></th><th><?php esc_html_e( 'تعداد', 'zarincode' ); ?></th><th><?php esc_html_e( 'هزینه (ریال)', 'zarincode' ); ?></th></tr></thead>
			<tbody>
				<?php if ( ! empty( $cost['by_type'] ) ) : foreach ( $cost['by_type'] as $type => $c ) : ?>
					<tr>
						<td><?php echo esc_html( zc_sms_type_label( $type ) ); ?></td>
						<td><?php echo esc_html( zc_fa_num( (int) ( $cost['count_type'][ $type ] ?? 0 ) ) ); ?></td>
						<td><?php echo esc_html( zc_fa_num( number_format( (int) $c ) ) ); ?></td>
					</tr>
				<?php endforeach; else : ?>
					<tr><td colspan="3"><?php esc_html_e( 'داده‌ای نیست.', 'zarincode' ); ?></td></tr>
				<?php endif; ?>
			</tbody>
		</table>
		<p style="margin-top:16px;font-size:11px;color:#9ca3af"><?php esc_html_e( 'هزینه بر اساس طول هر پیام و قیمت هر بخش (تنظیمات قالب ← پیامک) برآورد می‌شود.', 'zarincode' ); ?></p>
		<script>setTimeout(function(){window.print();}, 400);</script>
	</body>
	</html>
	<?php
	exit;
}
add_action( 'admin_init', 'zc_sms_cost_pdf_view' );
