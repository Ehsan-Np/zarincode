<?php
/**
 * گردش‌کار قرارداد: تأیید مبلغ و درخواست فسخ
 *
 * این ماژول سه بخش از چرخه‌ی عمر قرارداد را پوشش می‌دهد:
 *
 * ۱) تأیید مبلغ پیشنهادی توسط کارفرما — تا وقتی مبلغ تأیید نشده،
 *    جدول پرداخت نمایش داده نمی‌شود.
 * ۲) درخواست فسخ از سوی کارفرما و پاسخ مجری.
 * ۳) گذارهای مجاز وضعیت قرارداد.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   تأیید مبلغ
   ========================================================================== */

/**
 * آیا مبلغ قرارداد از سوی کارفرما تأیید شده است؟
 *
 * قرارداد تنها زمانی پرداخت‌پذیر می‌شود که هر سه شرط برقرار باشد:
 * امضا شده باشد، مبلغی برایش اعلام شده باشد، و کارفرما آن مبلغ را
 * صراحتاً تأیید کرده باشد.
 *
 * @param array|int $contract داده‌ی قرارداد یا شناسه‌ی آن.
 * @return bool
 */
function zc_contract_amount_approved( $contract ) {
	$id = is_array( $contract ) ? (int) $contract['id'] : (int) $contract;

	if ( ! $id ) {
		return false;
	}

	// وضعیت‌هایی که ذاتاً یعنی مبلغ پیش‌تر تأیید شده است.
	$status = is_array( $contract )
		? ( $contract['status'] ?? '' )
		: get_post_meta( $id, '_zc_ct_status', true );

	if ( in_array( $status, array( 'approved', 'active', 'done', 'terminating', 'terminated' ), true ) ) {
		return true;
	}

	$approved = get_post_meta( $id, '_zc_ct_amount_approved', true );

	return ! empty( $approved );
}

/**
 * ثبت تأیید مبلغ توسط کارفرما.
 *
 * @param int $contract_id شناسه‌ی قرارداد.
 * @return bool
 */
function zc_contract_approve_amount( $contract_id ) {
	$contract_id = (int) $contract_id;

	if ( ! $contract_id ) {
		return false;
	}

	$amount = (float) get_post_meta( $contract_id, '_zc_ct_amount', true );

	if ( $amount <= 0 ) {
		return false;
	}

	update_post_meta( $contract_id, '_zc_ct_amount_approved', $amount );
	update_post_meta( $contract_id, '_zc_ct_approved_at', current_time( 'timestamp' ) ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp

	zc_contract_set_status( $contract_id, 'approved' );

	do_action( 'zc_contract_amount_approved', $contract_id, $amount );

	return true;
}

/**
 * تغییر وضعیت قرارداد به همراه ثبت رویداد.
 *
 * @param int    $contract_id شناسه.
 * @param string $status      وضعیت جدید.
 * @return void
 */
function zc_contract_set_status( $contract_id, $status ) {
	$old = get_post_meta( $contract_id, '_zc_ct_status', true );

	if ( $old === $status ) {
		return;
	}

	update_post_meta( $contract_id, '_zc_ct_status', $status );

	do_action( 'zc_contract_status_changed', $contract_id, $status, $old );
}

/**
 * ای‌جکس: تأیید مبلغ از سوی کارفرما.
 *
 * @return void
 */
function zc_ajax_contract_approve() {
	zc_check_ajax();

	$id = isset( $_POST['contract'] ) ? absint( $_POST['contract'] ) : 0;

	if ( ! $id || ! zc_user_owns_contract( $id ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ) );
	}

	if ( ! zc_contract_approve_amount( $id ) ) {
		wp_send_json_error( array( 'message' => __( 'مبلغی برای این قرارداد ثبت نشده است.', 'zarincode' ) ) );
	}

	wp_send_json_success( array( 'message' => __( 'مبلغ تأیید شد. اکنون می‌توانید پیش‌پرداخت را انجام دهید.', 'zarincode' ) ) );
}
add_action( 'wp_ajax_zc_contract_approve', 'zc_ajax_contract_approve' );

/* ==========================================================================
   فسخ قرارداد
   ========================================================================== */

/**
 * دلایل از پیش تعریف‌شده‌ی درخواست فسخ.
 *
 * @return array
 */
function zc_termination_reasons() {
	return apply_filters(
		'zc_termination_reasons',
		array(
			'scope_change'  => __( 'تغییر نیازمندی‌ها یا دامنه‌ی پروژه', 'zarincode' ),
			'budget'        => __( 'محدودیت بودجه', 'zarincode' ),
			'delay'         => __( 'تأخیر در تحویل مراحل', 'zarincode' ),
			'quality'       => __( 'عدم رضایت از کیفیت خروجی', 'zarincode' ),
			'communication' => __( 'مشکل در ارتباط و پاسخ‌گویی', 'zarincode' ),
			'internal'      => __( 'تصمیم داخلی سازمان کارفرما', 'zarincode' ),
			'other'         => __( 'دلیل دیگر', 'zarincode' ),
		)
	);
}

/**
 * آیا این قرارداد قابل درخواست فسخ است؟
 *
 * در وضعیت‌های پایانی (خاتمه‌یافته، لغو یا فسخ‌شده) و نیز وقتی
 * درخواستی در حال بررسی است، فسخ معنا ندارد.
 *
 * @param array|int $contract داده‌ی قرارداد یا شناسه.
 * @return bool
 */
function zc_contract_can_terminate( $contract ) {
	$id = is_array( $contract ) ? (int) $contract['id'] : (int) $contract;

	if ( ! $id ) {
		return false;
	}

	$status = is_array( $contract )
		? ( $contract['status'] ?? '' )
		: get_post_meta( $id, '_zc_ct_status', true );

	// وضعیت‌های پایانی.
	if ( in_array( $status, array( 'done', 'canceled', 'terminated', 'terminating', 'draft' ), true ) ) {
		return false;
	}

	// قرارداد امضانشده اصلاً تعهدی ایجاد نکرده تا نیاز به فسخ باشد.
	if ( ! get_post_meta( $id, '_zc_ct_signed_at', true ) ) {
		return false;
	}

	// درخواست در حال بررسی.
	$req = get_post_meta( $id, '_zc_ct_term_requested', true );

	if ( is_array( $req ) && 'pending' === ( $req['status'] ?? '' ) ) {
		return false;
	}

	return (bool) apply_filters( 'zc_contract_can_terminate', true, $id, $status );
}

/**
 * ای‌جکس: ثبت درخواست فسخ از سوی کارفرما.
 *
 * @return void
 */
function zc_ajax_contract_terminate() {
	zc_check_ajax();

	$id = isset( $_POST['contract'] ) ? absint( $_POST['contract'] ) : 0;

	if ( ! $id || ! zc_user_owns_contract( $id ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ) );
	}

	if ( ! zc_contract_can_terminate( $id ) ) {
		wp_send_json_error( array( 'message' => __( 'این قرارداد در وضعیت قابل فسخ نیست.', 'zarincode' ) ) );
	}

	$reason_key = isset( $_POST['reason'] ) ? sanitize_key( wp_unslash( $_POST['reason'] ) ) : '';
	$details    = isset( $_POST['details'] ) ? sanitize_textarea_field( wp_unslash( $_POST['details'] ) ) : '';
	$reasons    = zc_termination_reasons();

	if ( ! isset( $reasons[ $reason_key ] ) ) {
		wp_send_json_error( array( 'message' => __( 'لطفاً دلیل فسخ را انتخاب کنید.', 'zarincode' ) ) );
	}

	if ( mb_strlen( $details ) < 20 ) {
		wp_send_json_error( array( 'message' => __( 'توضیحات باید دست‌کم ۲۰ نویسه باشد.', 'zarincode' ) ) );
	}

	$request = array(
		'status'     => 'pending',
		'reason_key' => $reason_key,
		'reason'     => $reasons[ $reason_key ],
		'details'    => $details,
		'created_at' => current_time( 'timestamp' ), // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp
		'user_id'    => get_current_user_id(),
	);

	update_post_meta( $id, '_zc_ct_term_requested', $request );

	// وضعیت پیشین نگه داشته می‌شود تا در صورت رد درخواست بازگردانده شود.
	$current = get_post_meta( $id, '_zc_ct_status', true );
	update_post_meta( $id, '_zc_ct_status_before_term', $current );

	zc_contract_set_status( $id, 'terminating' );

	do_action( 'zc_contract_termination_requested', $id, $request );

	wp_send_json_success(
		array(
			'message' => __( 'درخواست فسخ ثبت شد و حداکثر ظرف ۷۲ ساعت کاری بررسی می‌شود.', 'zarincode' ),
			'reload'  => true,
		)
	);
}
add_action( 'wp_ajax_zc_contract_terminate', 'zc_ajax_contract_terminate' );

/**
 * پاسخ مدیر به درخواست فسخ.
 *
 * @param int    $contract_id شناسه.
 * @param string $decision    approved یا rejected.
 * @param string $note        یادداشت مجری.
 * @param float  $refund      مبلغ استرداد.
 * @return bool
 */
function zc_contract_resolve_termination( $contract_id, $decision, $note = '', $refund = 0 ) {
	$req = get_post_meta( $contract_id, '_zc_ct_term_requested', true );

	if ( ! is_array( $req ) ) {
		return false;
	}

	$req['status']      = ( 'approved' === $decision ) ? 'approved' : 'rejected';
	$req['admin_note']  = sanitize_textarea_field( $note );
	$req['refund']      = (float) $refund;
	$req['resolved_at'] = current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp

	update_post_meta( $contract_id, '_zc_ct_term_requested', $req );

	if ( 'approved' === $decision ) {
		update_post_meta( $contract_id, '_zc_ct_terminated_at', $req['resolved_at'] );
		zc_contract_set_status( $contract_id, 'terminated' );

		if ( $refund > 0 && function_exists( 'zc_wallet_deposit' ) ) {
			$owner = (int) get_post_field( 'post_author', $contract_id );

			zc_wallet_deposit(
				$owner,
				(float) $refund,
				__( 'استرداد وجه پس از فسخ قرارداد', 'zarincode' ),
				'refund',
				array( 'contract_id' => $contract_id )
			);
		}
	} else {
		// بازگرداندن وضعیت پیشین.
		$before = get_post_meta( $contract_id, '_zc_ct_status_before_term', true );
		zc_contract_set_status( $contract_id, $before ? $before : 'active' );
	}

	do_action( 'zc_contract_termination_resolved', $contract_id, $decision, $req );

	return true;
}

/**
 * ای‌جکس مدیر: بررسی درخواست فسخ.
 *
 * @return void
 */
function zc_ajax_admin_resolve_termination() {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'zc_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ) );
	}

	$id       = isset( $_POST['contract'] ) ? absint( $_POST['contract'] ) : 0;
	$decision = isset( $_POST['decision'] ) ? sanitize_key( wp_unslash( $_POST['decision'] ) ) : '';
	$note     = isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '';
	$refund   = isset( $_POST['refund'] ) ? (float) wp_unslash( $_POST['refund'] ) : 0;

	if ( ! $id || ! in_array( $decision, array( 'approved', 'rejected' ), true ) ) {
		wp_send_json_error( array( 'message' => __( 'درخواست نامعتبر.', 'zarincode' ) ) );
	}

	if ( ! zc_contract_resolve_termination( $id, $decision, $note, $refund ) ) {
		wp_send_json_error( array( 'message' => __( 'درخواست فسخی برای این قرارداد ثبت نشده است.', 'zarincode' ) ) );
	}

	wp_send_json_success( array( 'message' => __( 'نتیجه ثبت شد.', 'zarincode' ) ) );
}
add_action( 'wp_ajax_zc_admin_resolve_termination', 'zc_ajax_admin_resolve_termination' );

/**
 * آیا کاربر جاری مالک این قرارداد است؟
 *
 * @param int $contract_id شناسه.
 * @return bool
 */
if ( ! function_exists( 'zc_user_owns_contract' ) ) :

	function zc_user_owns_contract( $contract_id ) {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		return (int) get_post_field( 'post_author', $contract_id ) === get_current_user_id();
	}

endif;
