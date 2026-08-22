<?php
/**
 * تب کیف پول
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_balance = zc_wallet_balance();
$zc_txs     = zc_get_transactions( 0, array( 'limit' => 25 ) );
$zc_symbol  = zc_opt( 'zc_currency_symbol', 'تومان' );
?>

<div class="zc-wallet-card" data-zc-anim="up">
	<div class="zc-wallet-card__info">
		<span class="zc-wallet-card__label"><?php zc_the_icon( 'wallet', 18 ); ?><?php esc_html_e( 'موجودی کیف پول شما', 'zarincode' ); ?></span>
		<strong class="zc-wallet-card__amount"><?php echo esc_html( zc_fa_num( number_format( $zc_balance ) ) ); ?> <small><?php echo esc_html( $zc_symbol ); ?></small></strong>
	</div>
	<div class="zc-wallet-card__deco"><?php zc_the_icon( 'wallet', 90 ); ?></div>
</div>

<div class="zc-panel__grid-2">
	<div class="zc-panel__box" data-zc-anim="up">
		<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'download', 19 ); ?><?php esc_html_e( 'افزایش موجودی', 'zarincode' ); ?></h3></div>
		<div class="zc-panel__box-body">
			<form data-zc-form="zc_wallet_charge">
				<div class="zc-field">
					<label class="zc-label"><?php esc_html_e( 'مبلغ مورد نظر', 'zarincode' ); ?> (<?php echo esc_html( $zc_symbol ); ?>)</label>
					<input type="text" name="amount" inputmode="numeric" required placeholder="<?php esc_attr_e( 'مثلاً ۱۰۰۰۰۰', 'zarincode' ); ?>" id="zc-charge-amount">
				</div>

				<div class="zc-quick-amounts">
					<?php foreach ( array( 50000, 100000, 200000, 500000 ) as $zc_amount ) : ?>
						<button type="button" class="zc-quick-amount" data-amount="<?php echo esc_attr( $zc_amount ); ?>">
							<?php echo esc_html( zc_fa_num( number_format( $zc_amount ) ) ); ?>
						</button>
					<?php endforeach; ?>
				</div>

				<div class="zc-form-msg"></div>

				<button type="submit" class="zc-btn zc-btn--gold zc-btn--block">
					<?php zc_the_icon( 'shield', 18 ); ?>
					<?php esc_html_e( 'پرداخت و شارژ کیف پول', 'zarincode' ); ?>
				</button>
				<p class="zc-help" style="text-align:center;margin-top:10px"><?php esc_html_e( 'پرداخت امن از طریق درگاه زرین‌پال', 'zarincode' ); ?></p>
			</form>
		</div>
	</div>

	<div class="zc-panel__box" data-zc-anim="up" data-zc-delay="90">
		<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'send', 19 ); ?><?php esc_html_e( 'درخواست تسویه', 'zarincode' ); ?></h3></div>
		<div class="zc-panel__box-body">
			<form data-zc-form="zc_withdraw_request">
				<div class="zc-field">
					<label class="zc-label"><?php esc_html_e( 'مبلغ برداشت', 'zarincode' ); ?></label>
					<input type="text" name="amount" inputmode="numeric" required>
				</div>
				<div class="zc-field">
					<label class="zc-label"><?php esc_html_e( 'شماره شبا (بدون IR)', 'zarincode' ); ?></label>
					<input type="text" name="sheba" value="<?php echo esc_attr( get_user_meta( get_current_user_id(), 'zc_sheba', true ) ); ?>" required maxlength="24">
				</div>
				<div class="zc-form-msg"></div>
				<button type="submit" class="zc-btn zc-btn--outline zc-btn--block"><?php zc_the_icon( 'send', 17 ); ?><?php esc_html_e( 'ثبت درخواست', 'zarincode' ); ?></button>
			</form>
		</div>
	</div>
</div>

<div class="zc-panel__box" data-zc-anim="up">
	<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'chart', 19 ); ?><?php esc_html_e( 'تاریخچه تراکنش‌ها', 'zarincode' ); ?></h3></div>
	<div class="zc-panel__box-body">
		<?php if ( $zc_txs ) : ?>
			<div class="zc-table-wrap">
				<table class="zc-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'شرح', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'مبلغ', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'موجودی', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></th>
							<th><?php esc_html_e( 'تاریخ', 'zarincode' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $zc_txs as $zc_tx ) : ?>
							<tr>
								<td>
									<?php echo esc_html( $zc_tx->description ); ?>
									<?php if ( $zc_tx->ref_id ) : ?>
										<br><small style="color:var(--zc-muted)"><?php esc_html_e( 'کد پیگیری:', 'zarincode' ); ?> <?php echo esc_html( zc_fa_num( $zc_tx->ref_id ) ); ?></small>
									<?php endif; ?>
								</td>
								<td>
									<strong style="color:<?php echo $zc_tx->amount >= 0 ? 'var(--zc-success)' : 'var(--zc-danger)'; ?>">
										<?php echo esc_html( ( $zc_tx->amount >= 0 ? '+' : '−' ) . zc_fa_num( number_format( abs( $zc_tx->amount ) ) ) ); ?>
									</strong>
								</td>
								<td><?php echo esc_html( zc_fa_num( number_format( $zc_tx->balance_after ) ) ); ?></td>
								<td>
									<span class="zc-badge zc-badge--<?php echo 'completed' === $zc_tx->status ? 'green' : 'orange'; ?>">
										<?php echo esc_html( 'completed' === $zc_tx->status ? __( 'موفق', 'zarincode' ) : __( 'در انتظار', 'zarincode' ) ); ?>
									</span>
								</td>
								<td><?php echo esc_html( zc_fa_num( wp_date( 'Y/m/d H:i', strtotime( $zc_tx->created_at ) ) ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php else : ?>
			<p style="text-align:center;color:var(--zc-muted);padding:30px 0"><?php esc_html_e( 'تراکنشی ثبت نشده است.', 'zarincode' ); ?></p>
		<?php endif; ?>
	</div>
</div>
