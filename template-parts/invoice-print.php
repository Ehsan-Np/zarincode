<?php
/**
 * قالب چاپی/دانلودی فاکتور سفارش زرین کد
 * ---------------------------------------------------------------------------
 * صفحه‌ی مستقل و بدون هدر/فوتر که با دکمه‌ی چاپ مرورگر به PDF تبدیل می‌شود.
 *
 * @package Zarincode
 * @var \WC_Order $order سفارش.
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $order ) ) {
	return;
}

$zc_site_name = zc_site_name();
$zc_logo      = zc_opt( 'zc_logo', '' );
$zc_phone     = zc_opt( 'zc_phone', '' );
$zc_mobile    = zc_opt( 'zc_mobile', '' );
$zc_email     = zc_site_email();
$zc_address   = zc_opt( 'zc_address', '' );
$zc_number    = zc_invoice_number( $order );
$zc_date      = function_exists( 'zc_jalali_date' ) ? zc_jalali_date( 'j F Y' ) : $order->get_date_created()->date_i18n( 'Y/m/d' );
$zc_customer  = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( $zc_site_name ); ?> — <?php esc_html_e( 'فاکتور', 'zarincode' ); ?> <?php echo esc_html( $zc_number ); ?></title>
	<style>
		*{margin:0;padding:0;box-sizing:border-box}
		body{font-family:Tahoma,Arial,sans-serif;color:#1f2437;background:#f3f4f6;padding:24px}
		.invoice{max-width:820px;margin:0 auto;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)}
		.head{display:flex;justify-content:space-between;align-items:center;gap:20px;padding:26px 30px;background:linear-gradient(135deg,#141A31,#0B2187);color:#fff;flex-wrap:wrap}
		.head .logo img{height:48px;max-width:180px;object-fit:contain}
		.head h1{font-size:1.2rem;margin:0}
		.head .meta{text-align:left;font-size:.85rem;line-height:1.8;direction:ltr}
		.head .meta .num{font-weight:700;color:#F5D061}
		.body{padding:26px 30px}
		.parties{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px}
		.party{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px}
		.party h3{font-size:.9rem;color:#0B2187;margin:0 0 8px}
		.party p{font-size:.82rem;line-height:1.9;color:#475569;margin:0}
		.party .ltr{direction:ltr;text-align:left}
		table.items{width:100%;border-collapse:collapse;margin-bottom:8px}
		table.items th{background:#f1f5f9;font-size:.8rem;color:#0B2187;padding:10px 12px;text-align:right;border-bottom:2px solid #e2e8f0}
		table.items td{padding:10px 12px;font-size:.83rem;border-bottom:1px solid #f1f5f9;color:#334155}
		table.items tr:last-child td{border-bottom:0}
		.totals{display:flex;justify-content:flex-end}
		.totals table{width:320px}
		.totals td{padding:6px 10px;font-size:.84rem;color:#475569}
		.totals td:last-child{text-align:left;font-weight:600;color:#1f2437}
		.totals tr.grand td{font-size:1.05rem;color:#0B2187;font-weight:800;border-top:2px solid #0B2187;padding-top:10px}
		.footer{padding:20px 30px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:.8rem;color:#64748b;text-align:center;line-height:2}
		.noprint{margin:0 auto 14px;max-width:820px;display:flex;gap:10px}
		.noprint button{padding:11px 22px;background:#C9A227;color:#fff;border:0;border-radius:9px;cursor:pointer;font-weight:700;font-family:inherit}
		@media print{
			body{background:#fff;padding:0}
			.invoice{box-shadow:none;border-radius:0}
			.noprint{display:none}
		}
		@media (max-width:640px){.parties{grid-template-columns:1fr}.head{flex-direction:column}.head .meta{text-align:center}}
	</style>
</head>
<body>
	<div class="noprint">
		<button onclick="window.print()"><?php esc_html_e( 'دانلود / چاپ PDF', 'zarincode' ); ?></button>
		<button onclick="window.close()" style="background:#6b7280"><?php esc_html_e( 'بستن', 'zarincode' ); ?></button>
	</div>

	<div class="invoice">
		<div class="head">
			<div class="logo">
				<?php if ( $zc_logo ) : ?>
					<img src="<?php echo esc_url( $zc_logo ); ?>" alt="<?php echo esc_attr( $zc_site_name ); ?>">
				<?php else : ?>
					<h1><?php echo esc_html( $zc_site_name ); ?></h1>
				<?php endif; ?>
			</div>
			<div class="meta">
				<div class="num"><?php esc_html_e( 'فاکتور', 'zarincode' ); ?>: <?php echo esc_html( $zc_number ); ?></div>
				<div><?php esc_html_e( 'تاریخ', 'zarincode' ); ?>: <?php echo esc_html( $zc_date ); ?></div>
			</div>
		</div>

		<div class="body">
			<div class="parties">
				<div class="party">
					<h3><?php esc_html_e( 'فروشنده', 'zarincode' ); ?></h3>
					<p><?php echo esc_html( $zc_site_name ); ?></p>
					<?php if ( $zc_address ) : ?><p><?php echo esc_html( $zc_address ); ?></p><?php endif; ?>
					<?php if ( $zc_phone ) : ?><p class="ltr"><?php echo esc_html( $zc_phone ); ?></p><?php endif; ?>
					<?php if ( $zc_email ) : ?><p class="ltr"><?php echo esc_html( $zc_email ); ?></p><?php endif; ?>
				</div>
				<div class="party">
					<h3><?php esc_html_e( 'خریدار', 'zarincode' ); ?></h3>
					<p><?php echo esc_html( $zc_customer ); ?></p>
					<?php if ( zc_opt( 'zc_invoice_show_email', true ) && $order->get_billing_email() ) : ?><p class="ltr"><?php echo esc_html( $order->get_billing_email() ); ?></p><?php endif; ?>
					<?php if ( zc_opt( 'zc_invoice_show_phone', true ) && $order->get_billing_phone() ) : ?><p class="ltr"><?php echo esc_html( $order->get_billing_phone() ); ?></p><?php endif; ?>
				</div>
			</div>

			<table class="items">
				<thead>
					<tr>
						<th style="width:60%"><?php esc_html_e( 'شرح', 'zarincode' ); ?></th>
						<th><?php esc_html_e( 'تعداد', 'zarincode' ); ?></th>
						<th><?php esc_html_e( 'مبلغ واحد', 'zarincode' ); ?></th>
						<th><?php esc_html_e( 'جمع', 'zarincode' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $order->get_items() as $item ) : ?>
						<tr>
							<td><?php echo esc_html( $item->get_name() ); ?></td>
							<td><?php echo esc_html( zc_fa_num( $item->get_quantity() ) ); ?></td>
							<td><?php echo esc_html( number_format( $order->get_line_subtotal( $item ) ) ); ?></td>
							<td><?php echo esc_html( number_format( $item->get_total() ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<div class="totals">
				<table>
					<?php if ( zc_opt( 'zc_invoice_show_discount', true ) && $order->get_total_discount() > 0 ) : ?>
						<tr><td><?php esc_html_e( 'تخفیف', 'zarincode' ); ?></td><td>-<?php echo esc_html( number_format( $order->get_total_discount() ) ); ?></td></tr>
					<?php endif; ?>
					<?php if ( zc_opt( 'zc_invoice_show_tax', false ) && $order->get_total_tax() > 0 ) : ?>
						<tr><td><?php esc_html_e( 'مالیات', 'zarincode' ); ?></td><td><?php echo esc_html( number_format( $order->get_total_tax() ) ); ?></td></tr>
					<?php endif; ?>
					<tr class="grand"><td><?php esc_html_e( 'مبلغ قابل پرداخت', 'zarincode' ); ?></td><td><?php echo esc_html( number_format( $order->get_total() ) ); ?> <?php echo esc_html( zc_opt( 'zc_currency_symbol', 'تومان' ) ); ?></td></tr>
				</table>
			</div>
		</div>

		<div class="footer">
			<?php echo esc_html( zc_opt( 'zc_invoice_footer', 'سپاسگزاریم از خرید شما.' ) ); ?>
		</div>
	</div>
</body>
</html>
