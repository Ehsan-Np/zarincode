<?php
/**
 * نسخه‌ی چاپی/دانلودی قرارداد
 *
 * صفحه‌ای مستقل و بدون هدر و فوتر سایت که با دکمه‌ی چاپ مرورگر به
 * PDF تبدیل می‌شود. سبک‌ها درون‌خطی‌اند تا فایل ذخیره‌شده هم درست
 * نمایش داده شود.
 *
 * @package Zarincode
 * @var array $contract داده‌های قرارداد.
 */

defined( 'ABSPATH' ) || exit;

$zc_logo = get_post_meta( $contract['tpl_id'], '_zc_ct_logo', true );

if ( ! $zc_logo ) {
	$zc_logo = zc_opt( 'zc_logo', '' );
}

$zc_company = zc_opt( 'zc_contract_company', zc_opt( 'zc_site_name', get_bloginfo( 'name' ) ) );
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( $contract['number'] . ' — ' . $contract['title'] ); ?></title>
	<link rel="stylesheet" href="<?php echo esc_url( ZC_URI . 'assets/css/fonts.css' ); ?>">

	<style>
		*{box-sizing:border-box}
		body{
			margin:0;padding:34px 20px;background:#EEF1F6;
			font-family:Vazirmatn,Tahoma,sans-serif;font-size:13.5px;line-height:2.05;color:#222
		}
		.sheet{
			max-width:820px;margin:0 auto;background:#fff;padding:44px 46px;
			box-shadow:0 6px 28px rgba(0,0,0,.09);border-radius:6px;position:relative
		}
		.sheet::before{
			content:'';position:absolute;inset-inline:0;top:0;height:5px;
			background:linear-gradient(90deg,#C9A227,#F5D061);border-radius:6px 6px 0 0
		}
		.hd{
			display:flex;justify-content:space-between;align-items:flex-start;gap:20px;
			padding-bottom:18px;border-bottom:2px solid #EDF1F7;margin-bottom:24px
		}
		.hd__logo img{max-height:56px;max-width:180px}
		.hd__logo strong{font-size:20px;color:#C9A227;display:block}
		.hd__co{font-size:11.5px;color:#666;margin-top:5px;line-height:1.9}
		.hd__meta{text-align:left;font-size:12px;color:#444;flex-shrink:0}
		.hd__meta code{
			display:inline-block;background:#FBF6E6;color:#8A6D0B;padding:3px 10px;
			border-radius:5px;font-size:12.5px;direction:ltr;font-weight:700
		}
		h1{font-size:19px;text-align:center;margin:0 0 26px;color:#141A31}
		h3,.zc-ct__h{font-size:15px;margin:22px 0 10px;color:#141A31;border-inline-start:3px solid #C9A227;padding-inline-start:9px}
		h4{font-size:13.5px;margin:16px 0 7px}
		p{margin:0 0 10px;text-align:justify}
		ol.zc-ct__clauses{padding-inline-start:19px;margin:0}
		ol.zc-ct__clauses li{margin-bottom:12px}
		ol.zc-ct__clauses strong{color:#141A31}
		ol.zc-ct__clauses p{margin:4px 0 0;color:#444}
		.zc-ct__notes{background:#FAFBFD;border:1px solid #EDF1F7;border-radius:8px;padding:14px 18px;margin-top:18px}
		.zc-ct__notes ul{margin:0;padding-inline-start:18px}
		.zc-ct__notes li{margin-bottom:6px;font-size:12.5px;color:#444}
		table.info{width:100%;border-collapse:collapse;margin:0 0 22px;font-size:12.5px}
		table.info th,table.info td{border:1px solid #E3E8F0;padding:8px 11px;text-align:right}
		table.info th{background:#FAFBFD;width:33%;font-weight:700;color:#333}
		.sign{
			display:flex;justify-content:space-between;gap:26px;margin-top:38px;
			padding-top:22px;border-top:2px dashed #DDE3EC
		}
		.sign__box{flex:1;text-align:center}
		.sign__cap{display:block;font-size:12px;color:#666;margin-bottom:9px;font-weight:700}
		.sign__img{height:82px;display:flex;align-items:center;justify-content:center}
		.sign__img img{max-height:78px;max-width:100%}
		.sign__line{border-top:1px solid #999;margin-top:7px;padding-top:6px;font-size:11px;color:#777}
		.stamp{
			display:inline-block;border:2.5px solid #1B9C58;color:#1B9C58;border-radius:8px;
			padding:7px 17px;font-size:13px;font-weight:700;transform:rotate(-7deg)
		}
		.foot{
			margin-top:26px;padding-top:14px;border-top:1px solid #EDF1F7;
			font-size:10.5px;color:#888;text-align:center;line-height:2
		}
		.foot code{direction:ltr;background:#F5F7FA;padding:1px 6px;border-radius:4px}
		.bar{max-width:820px;margin:0 auto 16px;display:flex;gap:9px;justify-content:flex-end}
		.bar button,.bar a{
			border:0;background:#C9A227;color:#fff;padding:9px 20px;border-radius:8px;
			cursor:pointer;font-family:inherit;font-size:13px;text-decoration:none
		}
		.bar a{background:#fff;color:#444;border:1px solid #DDE3EC}
		@media print{
			body{background:#fff;padding:0}
			.sheet{box-shadow:none;max-width:none;padding:22px;border-radius:0}
			.bar{display:none}
			@page{size:A4;margin:14mm}
		}
	</style>
</head>
<body>

	<div class="bar">
		<button type="button" onclick="window.print()"><?php esc_html_e( 'چاپ / ذخیره PDF', 'zarincode' ); ?></button>
		<a href="<?php echo esc_url( zc_panel_url() ); ?>"><?php esc_html_e( 'بازگشت به پنل', 'zarincode' ); ?></a>
	</div>

	<div class="sheet">

		<header class="hd">
			<div class="hd__logo">
				<?php if ( $zc_logo ) : ?>
					<img src="<?php echo esc_url( $zc_logo ); ?>" alt="<?php echo esc_attr( $zc_company ); ?>">
				<?php else : ?>
					<strong><?php echo esc_html( $zc_company ); ?></strong>
				<?php endif; ?>

				<div class="hd__co">
					<?php echo esc_html( zc_opt( 'zc_address', '' ) ); ?><br>
					<?php echo esc_html( zc_opt( 'zc_phone', '' ) ); ?>
					<?php if ( zc_opt( 'zc_email', '' ) ) : ?>
						— <?php echo esc_html( zc_opt( 'zc_email', '' ) ); ?>
					<?php endif; ?>
				</div>
			</div>

			<div class="hd__meta">
				<div><?php esc_html_e( 'شماره قرارداد', 'zarincode' ); ?></div>
				<code><?php echo esc_html( $contract['number'] ); ?></code>
				<div style="margin-top:7px">
					<?php esc_html_e( 'تاریخ:', 'zarincode' ); ?>
					<?php echo esc_html( zc_fa_num( zc_jalali_date( 'j F Y', $contract['created'] ) ) ); ?>
				</div>
			</div>
		</header>

		<h1><?php echo esc_html( $contract['title'] ); ?></h1>

		<table class="info">
			<tbody>
				<?php foreach ( zc_contract_fields( $contract['tpl_id'] ) as $zc_k => $zc_f ) : ?>
					<?php $zc_v = $contract['data'][ $zc_k ] ?? ''; ?>

					<?php if ( '' !== trim( (string) $zc_v ) ) : ?>
						<tr>
							<th><?php echo esc_html( $zc_f['label'] ); ?></th>
							<td><?php echo esc_html( $zc_v ); ?></td>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>

				<?php if ( $contract['amount'] > 0 ) : ?>
					<tr>
						<th><?php esc_html_e( 'مبلغ قرارداد', 'zarincode' ); ?></th>
						<td><strong><?php echo esc_html( zc_price_text( $contract['amount'] ) ); ?></strong></td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>

		<?php echo wp_kses_post( $contract['body'] ); ?>

		<div class="sign">
			<div class="sign__box">
				<span class="sign__cap"><?php esc_html_e( 'امضای مشتری', 'zarincode' ); ?></span>

				<div class="sign__img">
					<?php if ( $contract['signature'] ) : ?>
						<img src="<?php echo esc_attr( $contract['signature'] ); ?>" alt="">
					<?php endif; ?>
				</div>

				<div class="sign__line">
					<?php echo esc_html( $contract['data']['full_name'] ?? '' ); ?>
				</div>
			</div>

			<div class="sign__box">
				<span class="sign__cap"><?php esc_html_e( 'مهر و امضای مجری', 'zarincode' ); ?></span>

				<div class="sign__img">
					<?php if ( $contract['signed_at'] ) : ?>
						<span class="stamp"><?php esc_html_e( 'تأیید شد', 'zarincode' ); ?></span>
					<?php endif; ?>
				</div>

				<div class="sign__line">
					<?php echo esc_html( zc_opt( 'zc_contract_ceo', $zc_company ) ); ?>
				</div>
			</div>
		</div>

		<footer class="foot">
			<?php if ( $contract['signed_at'] ) : ?>
				<?php esc_html_e( 'این قرارداد به صورت الکترونیکی امضا شده است.', 'zarincode' ); ?><br>
				<?php esc_html_e( 'تاریخ امضا:', 'zarincode' ); ?>
				<?php echo esc_html( zc_fa_num( zc_jalali_date( 'j F Y — H:i', $contract['signed_at'] ) ) ); ?>
				&nbsp;|&nbsp;
				<?php esc_html_e( 'کد رهگیری:', 'zarincode' ); ?>
				<code><?php echo esc_html( $contract['hash'] ); ?></code>
				<?php if ( $contract['sign_ip'] ) : ?>
					&nbsp;|&nbsp; IP: <code><?php echo esc_html( $contract['sign_ip'] ); ?></code>
				<?php endif; ?>
			<?php else : ?>
				<?php esc_html_e( 'این نسخه پیش‌نویس است و هنوز امضا نشده است.', 'zarincode' ); ?>
			<?php endif; ?>
		</footer>
	</div>

</body>
</html>
