<?php
/**
 * تب اقساط کاربر.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_plans = zc_user_installments();
?>
<?php if ( $zc_plans ) : ?>
	<div class="zc-admin-box">
		<table class="widefat striped" style="width:100%">
			<thead>
				<tr>
					<th><?php esc_html_e( 'دوره', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'پرداخت‌شده', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'سررسید بعدی', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $zc_plans as $plan ) : ?>
				<tr>
					<td><?php echo esc_html( get_the_title( $plan->course_id ) ); ?></td>
					<td><?php echo esc_html( zc_fa_num( $plan->paid_parts ) . ' / ' . $plan->parts ); ?> — <?php echo esc_html( zc_price_text( $plan->paid_amount ) ); ?></td>
					<td><?php echo $plan->next_due ? esc_html( zc_fa_num( wp_date( 'Y/m/d', strtotime( $plan->next_due ) ) ) ) : '—'; ?></td>
					<td><?php echo esc_html( 'completed' === $plan->status ? __( 'تسویه شده', 'zarincode' ) : __( 'فعال', 'zarincode' ) ); ?></td>
					<td>
						<?php if ( 'active' === $plan->status ) : ?>
							<button class="zc-btn zc-btn--gold zc-btn--sm" data-zc-pay-inst="<?php echo (int) $plan->id; ?>">
								<?php echo esc_html( sprintf( __( 'پرداخت قسط %s', 'zarincode' ), zc_price_text( zc_installment_part_amount( $plan ) ) ) ); ?>
							</button>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<script>
	document.querySelectorAll('[data-zc-pay-inst]').forEach(function(btn){
		btn.addEventListener('click', function(){
			if(!window.zcAjax){return;}
			btn.disabled=true;
			window.zcAjax('zc_pay_installment',{plan_id:btn.dataset.zcPayInst}).then(function(res){
				window.zcToast && window.zcToast(res.data.message, res.success?'success':'error');
				if(res.success){location.reload();}
				btn.disabled=false;
			});
		});
	});
	</script>
<?php else : ?>
	<div class="zc-empty">
		<h3><?php esc_html_e( 'قسط فعالی ندارید', 'zarincode' ); ?></h3>
		<p><?php esc_html_e( 'هنگام خرید دوره می‌توانید پرداخت را به چند قسط ماهانه تقسیم کنید.', 'zarincode' ); ?></p>
	</div>
<?php endif; ?>
