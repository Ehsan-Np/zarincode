<?php
/**
 * تب «سوابق تلاش‌ها» پنل کاربری
 * ---------------------------------------------------------------------------
 * تلاش‌های خود کاربر را در آزمون‌ها، تمرین چالشی دوره‌ها و تمرین‌های پنل
 * از جدول گزارش نمایش می‌دهد.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

if ( ! zc_quiz_module_enabled() ) {
	return;
}

$zc_uid   = get_current_user_id();
$zc_user  = get_userdata( $zc_uid );
$zc_rows  = $zc_user ? zc_attempts_query( array( 'user' => $zc_user->user_login ) ) : array();
$zc_sum   = zc_attempts_summary( $zc_rows );
?>

<div class="zc-panel__welcome" data-zc-anim="up">
	<div>
		<h2><?php esc_html_e( 'سوابق تلاش‌های من 📊', 'zarincode' ); ?></h2>
		<p><?php esc_html_e( 'نمره‌ها و نتایج آزمون‌ها و تمرین‌های شما در اینجا ثبت شده است.', 'zarincode' ); ?></p>
	</div>
</div>

<div class="zc-panel__stats">
	<div class="zc-pstat zc-pstat--green">
		<span class="zc-pstat__info"><strong class="zc-pstat__value"><?php echo esc_html( zc_fa_num( $zc_sum['attempts'] ) ); ?></strong><span class="zc-pstat__label"><?php esc_html_e( 'تعداد تلاش', 'zarincode' ); ?></span></span>
	</div>
	<div class="zc-pstat zc-pstat--gold">
		<span class="zc-pstat__info"><strong class="zc-pstat__value"><?php echo esc_html( zc_fa_num( $zc_sum['passed'] ) ); ?></strong><span class="zc-pstat__label"><?php esc_html_e( 'تلاش قبول', 'zarincode' ); ?></span></span>
	</div>
	<div class="zc-pstat zc-pstat--blue">
		<span class="zc-pstat__info"><strong class="zc-pstat__value"><?php echo esc_html( zc_fa_num( $zc_sum['avg'] ) ); ?></strong><span class="zc-pstat__label"><?php esc_html_e( 'میانگین نمره', 'zarincode' ); ?></span></span>
	</div>
	<div class="zc-pstat zc-pstat--orange">
		<span class="zc-pstat__info"><strong class="zc-pstat__value"><?php echo esc_html( zc_fa_num( $zc_sum['rate'] ) ); ?>٪</strong><span class="zc-pstat__label"><?php esc_html_e( 'نرخ قبولی', 'zarincode' ); ?></span></span>
	</div>
</div>

<div class="zc-panel__box" data-zc-anim="up">
	<div class="zc-panel__box-head">
		<h3><?php zc_the_icon( 'chart', 19 ); ?><?php esc_html_e( 'جزئیات تلاش‌ها', 'zarincode' ); ?></h3>
	</div>
	<div class="zc-panel__box-body" style="overflow-x:auto">
		<table class="zc-table zc-table--attempts">
			<thead>
				<tr>
					<th><?php esc_html_e( 'نوع', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'دوره / تمرین', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'نمره', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'درست / کل', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'تاریخ', 'zarincode' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( ! $zc_rows ) : ?>
				<tr><td colspan="6"><?php esc_html_e( 'هنوز تلاشی ثبت نشده است.', 'zarincode' ); ?></td></tr>
			<?php else : foreach ( $zc_rows as $r ) : ?>
				<tr>
					<td><?php echo esc_html( zc_attempts_type_label( $r->type ) ); ?></td>
					<td><?php echo esc_html( zc_attempts_ref_title( $r->type, $r->ref_id ) ); ?></td>
					<td><strong><?php echo esc_html( zc_fa_num( $r->score ) ); ?>٪</strong></td>
					<td><?php echo esc_html( zc_fa_num( $r->correct ) ); ?> / <?php echo esc_html( zc_fa_num( $r->total ) ); ?></td>
					<td><?php echo $r->passed ? '<span class="zc-tag zc-tag--success">' . esc_html__( 'قبول', 'zarincode' ) . '</span>' : '<span class="zc-tag zc-tag--muted">' . esc_html__( 'رد', 'zarincode' ) . '</span>'; ?></td>
					<td><?php echo esc_html( $r->created_at ); ?></td>
				</tr>
			<?php endforeach; endif; ?>
			</tbody>
		</table>
	</div>
</div>
