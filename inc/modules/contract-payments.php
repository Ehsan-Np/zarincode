<?php
/**
 * پرداخت چندمرحله‌ای قراردادها
 *
 * هر قرارداد به چند «مرحله پرداخت» تقسیم می‌شود. هر مرحله سه چیز
 * دارد: درصد مبلغ، درصد پیشرفتی که آن مرحله را باز می‌کند، و وضعیت.
 *
 * چرخه‌ی هر مرحله:
 *   locked → قفل است چون پیشرفت پروژه هنوز به آستانه نرسیده
 *   due    → باز شده و منتظر پرداخت کارفرماست
 *   paid   → پرداخت شده
 *
 * مبلغ مراحل همیشه از روی مبلغ قرارداد و درصدها محاسبه می‌شود، نه
 * ذخیره‌ی مستقیم؛ به این ترتیب اگر مدیر مبلغ قرارداد را اصلاح کند،
 * مراحل پرداخت‌نشده خودکار به‌روز می‌شوند و مراحل پرداخت‌شده مبلغ
 * واقعی پرداختی خود را حفظ می‌کنند.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   تعریف و ساختار مراحل
   ========================================================================== */

/**
 * مراحل پیش‌فرض پرداخت.
 *
 * مدیر می‌تواند این را در تنظیمات قالب یا روی هر الگو تغییر دهد.
 *
 * @return array
 */
function zc_default_payment_stages() {
	return apply_filters(
		'zc_default_payment_stages',
		array(
			array(
				'title'    => __( 'پیش‌پرداخت', 'zarincode' ),
				'percent'  => 40,
				'progress' => 0,
				'note'     => __( 'پس از امضای قرارداد و پیش از شروع عملیات اجرایی', 'zarincode' ),
			),
			array(
				'title'    => __( 'پرداخت میانی', 'zarincode' ),
				'percent'  => 30,
				'progress' => 50,
				'note'     => __( 'با رسیدن پیشرفت پروژه به ۵۰ درصد', 'zarincode' ),
			),
			array(
				'title'    => __( 'تسویه نهایی', 'zarincode' ),
				'percent'  => 30,
				'progress' => 100,
				'note'     => __( 'پس از تکمیل ۱۰۰ درصد پروژه و پیش از تحویل فایل‌ها', 'zarincode' ),
			),
		)
	);
}

/**
 * تجزیه‌ی متن تعریف مراحل.
 *
 * هر خط: عنوان | درصد مبلغ | درصد پیشرفت آزادکننده | توضیح
 *
 * @param string $raw متن خام.
 * @return array
 */
function zc_parse_payment_stages( $raw ) {
	$raw = (string) $raw;

	if ( '' === trim( $raw ) ) {
		return array();
	}

	$out = array();

	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( $line );

		if ( '' === $line ) {
			continue;
		}

		$parts = array_map( 'trim', explode( '|', $line ) );

		$title = $parts[0] ?? '';

		if ( '' === $title ) {
			continue;
		}

		$out[] = array(
			'title'    => $title,
			'percent'  => max( 0, min( 100, (float) zc_en_num( $parts[1] ?? 0 ) ) ),
			'progress' => max( 0, min( 100, (int) zc_en_num( $parts[2] ?? 0 ) ) ),
			'note'     => $parts[3] ?? '',
		);
	}

	return $out;
}

/**
 * تبدیل آرایه‌ی مراحل به متن قابل ویرایش.
 *
 * @param array $stages مراحل.
 * @return string
 */
function zc_payment_stages_to_text( $stages ) {
	$lines = array();

	foreach ( (array) $stages as $s ) {
		$lines[] = sprintf(
			'%s | %s | %s | %s',
			$s['title'] ?? '',
			$s['percent'] ?? 0,
			$s['progress'] ?? 0,
			$s['note'] ?? ''
		);
	}

	return implode( "\n", $lines );
}

/**
 * مراحل تعریف‌شده برای یک قرارداد.
 *
 * ترتیب اولویت: تنظیم اختصاصی همان قرارداد ← الگوی قرارداد ←
 * تنظیم سراسری قالب ← پیش‌فرض.
 *
 * @param int $contract_id شناسه قرارداد.
 * @param int $tpl_id      شناسه الگو (اختیاری).
 * @return array
 */
function zc_contract_stage_defs( $contract_id = 0, $tpl_id = 0 ) {
	if ( $contract_id ) {
		$own = zc_parse_payment_stages( get_post_meta( $contract_id, '_zc_ct_stages_def', true ) );

		if ( $own ) {
			return $own;
		}

		if ( ! $tpl_id ) {
			$tpl_id = (int) get_post_meta( $contract_id, '_zc_ct_tpl', true );
		}
	}

	if ( $tpl_id ) {
		$tpl = zc_parse_payment_stages( get_post_meta( $tpl_id, '_zc_ct_stages', true ) );

		if ( $tpl ) {
			return $tpl;
		}
	}

	$global = zc_parse_payment_stages( zc_opt( 'zc_payment_stages', '' ) );

	return $global ? $global : zc_default_payment_stages();
}

/* ==========================================================================
   وضعیت پرداخت‌های یک قرارداد
   ========================================================================== */

/**
 * پرداخت‌های ثبت‌شده‌ی یک قرارداد.
 *
 * @param int $contract_id شناسه.
 * @return array نگاشت index => داده‌ی پرداخت.
 */
function zc_contract_payments( $contract_id ) {
	$paid = get_post_meta( $contract_id, '_zc_ct_payments', true );

	return is_array( $paid ) ? $paid : array();
}

/**
 * محاسبه‌ی کامل وضعیت مراحل پرداخت یک قرارداد.
 *
 * خروجی این تابع تنها منبع حقیقت برای نمایش و اعتبارسنجی است.
 *
 * @param int $contract_id شناسه قرارداد.
 * @return array
 */
function zc_contract_payment_plan( $contract_id ) {
	$contract = zc_contract_data( $contract_id );

	if ( ! $contract ) {
		return array(
			'stages'    => array(),
			'total'     => 0,
			'paid'      => 0,
			'remaining' => 0,
			'percent'   => 0,
			'settled'   => false,
		);
	}

	$defs     = zc_contract_stage_defs( $contract_id, $contract['tpl_id'] );
	$payments = zc_contract_payments( $contract_id );
	$total    = (float) $contract['amount'];
	$progress = (int) $contract['progress'];
	$signed   = ! empty( $contract['signed_at'] );

	$stages   = array();
	$paid_sum = 0;

	/*
	 * جمع درصدها ممکن است به دلیل رُند شدن دقیقاً ۱۰۰ نشود؛ باقیمانده
	 * به آخرین مرحله اضافه می‌شود تا مجموع دقیقاً با مبلغ قرارداد
	 * برابر باشد و کارفرما هرگز چند تومان بدهکار یا بستانکار نماند.
	 */
	$last  = count( $defs ) - 1;
	$accum = 0;

	foreach ( $defs as $i => $def ) {
		$percent = (float) $def['percent'];

		if ( $i === $last ) {
			$amount = round( $total - $accum );
		} else {
			$amount = round( $total * $percent / 100 );
			$accum += $amount;
		}

		$record = $payments[ $i ] ?? null;
		$is_paid = ! empty( $record['paid_at'] );

		// مبلغ واقعی پرداخت‌شده حفظ می‌شود.
		if ( $is_paid ) {
			$amount    = (float) ( $record['amount'] ?? $amount );
			$paid_sum += $amount;
		}

		// شرط بازشدن: امضای قرارداد + رسیدن پیشرفت به آستانه.
		$threshold = (int) $def['progress'];
		$unlocked  = $signed && $progress >= $threshold;

		/*
		 * مرحله‌ها باید به ترتیب پرداخت شوند؛ اگر مرحله‌ی پیشین
		 * پرداخت نشده باشد، این مرحله هم قفل می‌ماند حتی اگر
		 * آستانه‌ی پیشرفتش رسیده باشد.
		 */
		if ( $unlocked && $i > 0 ) {
			for ( $j = 0; $j < $i; $j++ ) {
				if ( empty( $payments[ $j ]['paid_at'] ) ) {
					$unlocked = false;
					break;
				}
			}
		}

		if ( $is_paid ) {
			$status = 'paid';
		} elseif ( $unlocked ) {
			$status = 'due';
		} else {
			$status = 'locked';
		}

		$stages[] = array(
			'index'     => $i,
			'title'     => $def['title'],
			'note'      => $def['note'],
			'percent'   => $percent,
			'threshold' => $threshold,
			'amount'    => $amount,
			'status'    => $status,
			'paid_at'   => $record['paid_at'] ?? 0,
			'ref_id'    => $record['ref_id'] ?? '',
			'gateway'   => $record['gateway'] ?? '',
			'invoice'   => $record['invoice'] ?? '',
		);
	}

	$remaining = max( 0, $total - $paid_sum );

	return array(
		'stages'    => $stages,
		'total'     => $total,
		'paid'      => $paid_sum,
		'remaining' => $remaining,
		'percent'   => $total > 0 ? min( 100, round( $paid_sum / $total * 100 ) ) : 0,
		'settled'   => $total > 0 && $remaining < 1,
		'progress'  => $progress,
		'signed'    => $signed,
	);
}

/**
 * آیا قرارداد به‌طور کامل تسویه شده است؟
 *
 * تحویل فایل‌های نهایی به این شرط گره خورده است.
 *
 * @param int $contract_id شناسه.
 * @return bool
 */
function zc_contract_is_settled( $contract_id ) {
	$plan = zc_contract_payment_plan( $contract_id );

	return ! empty( $plan['settled'] );
}

/**
 * نخستین مرحله‌ی قابل پرداخت.
 *
 * @param int $contract_id شناسه.
 * @return array|null
 */
function zc_contract_due_stage( $contract_id ) {
	$plan = zc_contract_payment_plan( $contract_id );

	foreach ( $plan['stages'] as $stage ) {
		if ( 'due' === $stage['status'] ) {
			return $stage;
		}
	}

	return null;
}

/* ==========================================================================
   ثبت پرداخت
   ========================================================================== */

/**
 * ثبت پرداخت یک مرحله.
 *
 * @param int    $contract_id شناسه قرارداد.
 * @param int    $index       شماره مرحله.
 * @param float  $amount      مبلغ.
 * @param string $gateway     درگاه (wallet یا zarinpal).
 * @param string $ref_id      کد پیگیری.
 * @return bool
 */
function zc_contract_mark_paid( $contract_id, $index, $amount, $gateway, $ref_id = '' ) {
	global $wpdb;
	$lock_name = 'zc_contract_pay_' . (int) $contract_id . '_' . (int) $index;
	$locked    = '1' === (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 10)', $lock_name ) ); // phpcs:ignore
	if ( ! $locked ) {
		return false;
	}

	try {
		$payments = zc_contract_payments( $contract_id );

	// جلوگیری از ثبت دوباره‌ی یک مرحله.
	if ( ! empty( $payments[ $index ]['paid_at'] ) ) {
		return false;
	}

	$contract = zc_contract_data( $contract_id );
	$number   = $contract ? $contract['number'] : $contract_id;

	$payments[ $index ] = array(
		'amount'  => (float) $amount,
		'paid_at' => current_time( 'timestamp' ), // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp
		'gateway' => $gateway,
		'ref_id'  => $ref_id,
		'user_id' => get_current_user_id(),
		'invoice' => sprintf( 'INV-%s-%d', $number, $index + 1 ),
	);

	update_post_meta( $contract_id, '_zc_ct_payments', $payments );

	// ثبت در دفتر تراکنش‌ها برای گزارش مالی.
	zc_add_transaction(
		array(
			'user_id'     => $contract ? $contract['user_id'] : get_current_user_id(),
			'amount'      => $amount,
			'type'        => 'income',
			'category'    => 'contract',
			'status'      => 'completed',
			'description' => sprintf(
				/* translators: 1: عنوان مرحله 2: شماره قرارداد */
				__( 'پرداخت %1$s قرارداد %2$s', 'zarincode' ),
				zc_contract_stage_title( $contract_id, $index ),
				$number
			),
			'ref_id'      => $ref_id,
			'gateway'     => $gateway,
			'meta'        => array(
				'contract_id' => $contract_id,
				'stage'       => $index,
			),
		)
	);

	$plan = zc_contract_payment_plan( $contract_id );

	// با تسویه‌ی کامل، وضعیت قرارداد به «خاتمه‌یافته» می‌رود.
	if ( ! empty( $plan['settled'] ) ) {
		update_post_meta( $contract_id, '_zc_ct_status', 'done' );
	} elseif ( 'signed' === ( $contract['status'] ?? '' ) ) {
		// نخستین پرداخت یعنی پروژه عملاً آغاز شده است.
		update_post_meta( $contract_id, '_zc_ct_status', 'active' );
	}

	// اطلاع‌رسانی.
	zc_notify_user(
		$contract ? $contract['user_id'] : 0,
		'contract',
		sprintf(
			/* translators: 1: مبلغ 2: شماره قرارداد */
			__( "پرداخت شما ثبت شد ✅\nمبلغ: %1\$s\nقرارداد: %2\$s", 'zarincode' ),
			zc_price_text( $amount ),
			$number
		)
	);

	if ( function_exists( 'zc_notify_admins' ) ) {
		zc_notify_admins(
			sprintf(
				/* translators: 1: شماره قرارداد 2: مرحله 3: مبلغ */
				__( "پرداخت تازه\nقرارداد: %1\$s\nمرحله: %2\$s\nمبلغ: %3\$s", 'zarincode' ),
				$number,
				zc_contract_stage_title( $contract_id, $index ),
				zc_price_text( $amount )
			)
		);
	}

		do_action( 'zc_contract_stage_paid', $contract_id, $index, $amount, $gateway );

		return true;
	} finally {
		$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) ); // phpcs:ignore
	}
}

/**
 * عنوان یک مرحله.
 *
 * @param int $contract_id شناسه.
 * @param int $index       شماره مرحله.
 * @return string
 */
function zc_contract_stage_title( $contract_id, $index ) {
	$defs = zc_contract_stage_defs( $contract_id );

	return $defs[ $index ]['title'] ?? sprintf(
		/* translators: %s: شماره */
		__( 'مرحله %s', 'zarincode' ),
		zc_fa_num( $index + 1 )
	);
}

/* ==========================================================================
   آجاکس پرداخت
   ========================================================================== */

/**
 * اعتبارسنجی مشترک درخواست پرداخت.
 *
 * @param int $contract_id شناسه.
 * @param int $index       مرحله.
 * @return array|WP_Error
 */
function zc_validate_stage_payment( $contract_id, $index ) {
	if ( ! zc_can_view_contract( $contract_id ) ) {
		return new WP_Error( 'perm', __( 'دسترسی مجاز نیست.', 'zarincode' ) );
	}

	$plan = zc_contract_payment_plan( $contract_id );

	if ( ! isset( $plan['stages'][ $index ] ) ) {
		return new WP_Error( 'stage', __( 'مرحله پرداخت یافت نشد.', 'zarincode' ) );
	}

	$stage = $plan['stages'][ $index ];

	if ( 'paid' === $stage['status'] ) {
		return new WP_Error( 'done', __( 'این مرحله قبلاً پرداخت شده است.', 'zarincode' ) );
	}

	if ( 'locked' === $stage['status'] ) {
		if ( ! $plan['signed'] ) {
			return new WP_Error( 'unsigned', __( 'ابتدا باید قرارداد را امضا کنید.', 'zarincode' ) );
		}

		return new WP_Error(
			'locked',
			sprintf(
				/* translators: %s: درصد */
				__( 'این مرحله با رسیدن پیشرفت پروژه به %s درصد فعال می‌شود.', 'zarincode' ),
				zc_fa_num( $stage['threshold'] )
			)
		);
	}

	if ( $stage['amount'] < 1 ) {
		return new WP_Error( 'amount', __( 'مبلغ این مرحله معتبر نیست.', 'zarincode' ) );
	}

	return $stage;
}

/**
 * پرداخت مرحله از کیف پول.
 *
 * @return void
 */
function zc_ajax_pay_stage_wallet() {
	zc_check_ajax();

	$contract_id = isset( $_POST['contract'] ) ? absint( $_POST['contract'] ) : 0;
	$index       = isset( $_POST['stage'] ) ? absint( $_POST['stage'] ) : 0;

	$stage = zc_validate_stage_payment( $contract_id, $index );

	if ( is_wp_error( $stage ) ) {
		wp_send_json_error( array( 'message' => $stage->get_error_message() ) );
	}

	$user_id = get_current_user_id();
	$balance = zc_wallet_balance( $user_id );

	if ( $balance < $stage['amount'] ) {
		wp_send_json_error(
			array(
				'message' => sprintf(
					/* translators: 1: موجودی 2: مبلغ لازم */
					__( 'موجودی کیف پول کافی نیست. موجودی: %1$s — مبلغ لازم: %2$s', 'zarincode' ),
					zc_price_text( $balance ),
					zc_price_text( $stage['amount'] )
				),
				'shortage' => $stage['amount'] - $balance,
			)
		);
	}

	$ok = zc_wallet_withdraw(
		$user_id,
		$stage['amount'],
		sprintf(
			/* translators: 1: مرحله 2: قرارداد */
			__( 'پرداخت %1$s قرارداد %2$s', 'zarincode' ),
			$stage['title'],
			zc_contract_number( $contract_id )
			),
			'contract',
			array( 'ref_id' => 'contract-' . $contract_id . '-stage-' . $index, 'gateway' => 'wallet' )
		);

	if ( is_wp_error( $ok ) || ! $ok ) {
		wp_send_json_error( array( 'message' => is_wp_error( $ok ) ? $ok->get_error_message() : __( 'برداشت از کیف پول انجام نشد.', 'zarincode' ) ) );
	}

	zc_contract_mark_paid( $contract_id, $index, $stage['amount'], 'wallet', 'WALLET-' . $ok );

	wp_send_json_success(
		array(
			'message'  => __( 'پرداخت با موفقیت از کیف پول انجام شد.', 'zarincode' ),
			'reload'   => true,
		)
	);
}
add_action( 'wp_ajax_zc_pay_stage_wallet', 'zc_ajax_pay_stage_wallet' );

/**
 * پرداخت مرحله از درگاه بانکی.
 *
 * @return void
 */
function zc_ajax_pay_stage_gateway() {
	zc_check_ajax();

	$contract_id = isset( $_POST['contract'] ) ? absint( $_POST['contract'] ) : 0;
	$index       = isset( $_POST['stage'] ) ? absint( $_POST['stage'] ) : 0;

	$stage = zc_validate_stage_payment( $contract_id, $index );

	if ( is_wp_error( $stage ) ) {
		wp_send_json_error( array( 'message' => $stage->get_error_message() ) );
	}

	$callback = add_query_arg(
		array(
			'zc_ct_pay' => $contract_id,
			'zc_stage'  => $index,
		),
		home_url( '/' )
	);

	$result = zc_zarinpal_request(
		$stage['amount'],
		sprintf(
			/* translators: 1: مرحله 2: قرارداد */
			__( 'پرداخت %1$s قرارداد %2$s', 'zarincode' ),
			$stage['title'],
			zc_contract_number( $contract_id )
		),
		$callback
	);

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	}

	/*
	 * اطلاعات پرداخت تا بازگشت از درگاه نگه داشته می‌شود؛ مبلغ از
	 * همین‌جا خوانده می‌شود نه از پارامترهای بازگشتی، تا کاربر نتواند
	 * با دستکاری آدرس مبلغ کمتری تأیید کند.
	 */
	set_transient(
		'zc_ctpay_' . $result['authority'],
		array(
			'contract' => $contract_id,
			'stage'    => $index,
			'amount'   => $stage['amount'],
			'user_id'  => get_current_user_id(),
		),
		2 * HOUR_IN_SECONDS
	);

	wp_send_json_success(
		array(
			'redirect' => $result['url'],
			'message'  => __( 'در حال انتقال به درگاه پرداخت…', 'zarincode' ),
		)
	);
}
add_action( 'wp_ajax_zc_pay_stage_gateway', 'zc_ajax_pay_stage_gateway' );

/**
 * بازگشت از درگاه پرداخت مرحله.
 *
 * @return void
 */
function zc_handle_stage_callback() {
	if ( ! isset( $_GET['zc_ct_pay'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}

	$contract_id = absint( $_GET['zc_ct_pay'] ); // phpcs:ignore WordPress.Security.NonceVerification
	$authority   = isset( $_GET['Authority'] ) ? sanitize_text_field( wp_unslash( $_GET['Authority'] ) ) : ''; // phpcs:ignore
	$status      = isset( $_GET['Status'] ) ? sanitize_text_field( wp_unslash( $_GET['Status'] ) ) : ''; // phpcs:ignore

	$pending = $authority ? get_transient( 'zc_ctpay_' . $authority ) : false;
	$user_id = $pending ? (int) ( $pending['user_id'] ?? 0 ) : 0;

	if ( 'OK' !== $status || ! $pending || ! $user_id || (int) $pending['contract'] !== $contract_id ) {
		zc_set_pay_message( 'error', __( 'پرداخت لغو شد یا اطلاعات تراکنش معتبر نبود.', 'zarincode' ), $user_id );
		wp_safe_redirect( zc_panel_url( 'contracts' ) );
		exit;
	}

	// صحت تراکنش با authority و مالک ثبت‌شده کنترل می‌شود؛ callback به cookie ورود وابسته نیست.
	$contract_id = (int) $pending['contract'];
	if ( (int) get_post_field( 'post_author', $contract_id ) !== $user_id ) {
		zc_set_pay_message( 'error', __( 'مالک تراکنش با قرارداد مطابقت ندارد.', 'zarincode' ), $user_id );
		wp_safe_redirect( zc_panel_url( 'contracts' ) );
		exit;
	}

	$verify = zc_zarinpal_verify( $authority, $pending['amount'] );

	if ( is_wp_error( $verify ) ) {
		zc_set_pay_message( 'error', $verify->get_error_message(), $user_id );
		wp_safe_redirect( $back );
		exit;
	}

	$done = zc_contract_mark_paid(
		(int) $pending['contract'],
		(int) $pending['stage'],
		(float) $pending['amount'],
		'zarinpal',
		$verify['ref_id']
	);

	delete_transient( 'zc_ctpay_' . $authority );

	if ( $done ) {
		zc_set_pay_message(
			'success',
			sprintf(
				/* translators: %s: کد پیگیری */
				__( 'پرداخت با موفقیت انجام شد. کد پیگیری: %s', 'zarincode' ),
				zc_fa_num( $verify['ref_id'] )
			),
			$user_id
		);
	} else {
		zc_set_pay_message( 'error', __( 'این مرحله پیش‌تر پرداخت شده بود.', 'zarincode' ), $user_id );
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'tab'      => 'contracts',
				'contract' => $pending['contract'],
			),
			zc_panel_url()
		)
	);
	exit;
}
add_action( 'template_redirect', 'zc_handle_stage_callback' );

/**
 * ثبت پیام موقت برای نمایش پس از بازگشت از درگاه.
 *
 * @param string $type نوع.
 * @param string $text    متن.
 * @param int    $user_id کاربر مقصد.
 * @return void
 */
function zc_set_pay_message( $type, $text, $user_id = 0 ) {
	$user_id = $user_id ?: get_current_user_id();
	set_transient(
		'zc_ctpay_msg_' . (int) $user_id,
		array(
			'type' => $type,
			'text' => $text,
		),
		60
	);
}

/**
 * خواندن و پاک‌کردن پیام پرداخت.
 *
 * @return array|false
 */
function zc_get_pay_message() {
	$key = 'zc_ctpay_msg_' . get_current_user_id();
	$msg = get_transient( $key );

	if ( $msg ) {
		delete_transient( $key );
	}

	return $msg;
}

/* ==========================================================================
   تحویل فایل‌های پروژه پس از تسویه
   ========================================================================== */

/**
 * فایل‌های تحویلی یک قرارداد.
 *
 * @param int $contract_id شناسه.
 * @return array
 */
function zc_contract_deliverables( $contract_id ) {
	$files = get_post_meta( $contract_id, '_zc_ct_files', true );

	return is_array( $files ) ? $files : array();
}

/**
 * دانلود امن فایل تحویلی.
 *
 * دسترسی تنها پس از تسویه‌ی کامل و برای مالک قرارداد باز است.
 *
 * @return void
 */
function zc_contract_file_download() {
	if ( ! isset( $_GET['zc_ct_file'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}

	$contract_id = absint( $_GET['zc_ct_file'] ); // phpcs:ignore WordPress.Security.NonceVerification
	$index       = isset( $_GET['i'] ) ? absint( $_GET['i'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
	$nonce       = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : ''; // phpcs:ignore

	if ( ! wp_verify_nonce( $nonce, 'zc_ct_file_' . $contract_id . '_' . $index ) ) {
		wp_die( esc_html__( 'لینک دانلود نامعتبر یا منقضی شده است.', 'zarincode' ) );
	}

	if ( ! zc_can_view_contract( $contract_id ) ) {
		wp_die( esc_html__( 'دسترسی مجاز نیست.', 'zarincode' ) );
	}

	// مدیر همیشه دسترسی دارد؛ کارفرما فقط پس از تسویه.
	if ( ! current_user_can( 'manage_options' ) && ! zc_contract_is_settled( $contract_id ) ) {
		wp_die( esc_html__( 'دسترسی به فایل‌های پروژه پس از تسویه کامل امکان‌پذیر است.', 'zarincode' ) );
	}

	$files = zc_contract_deliverables( $contract_id );

	if ( empty( $files[ $index ]['id'] ) ) {
		wp_die( esc_html__( 'فایل یافت نشد.', 'zarincode' ) );
	}

	$path = get_attached_file( (int) $files[ $index ]['id'] );

	if ( ! $path || ! file_exists( $path ) ) {
		wp_die( esc_html__( 'فایل روی سرور موجود نیست.', 'zarincode' ) );
	}

	// ثبت شمار دانلود.
	$files[ $index ]['downloads'] = ( (int) ( $files[ $index ]['downloads'] ?? 0 ) ) + 1;
	update_post_meta( $contract_id, '_zc_ct_files', $files );

	nocache_headers();
	header( 'Content-Type: application/octet-stream' );
	header( 'Content-Disposition: attachment; filename="' . basename( $path ) . '"' );
	header( 'Content-Length: ' . filesize( $path ) );

	readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	exit;
}
add_action( 'template_redirect', 'zc_contract_file_download' );

/**
 * نشانی دانلود فایل تحویلی.
 *
 * @param int $contract_id شناسه.
 * @param int $index       شماره فایل.
 * @return string
 */
function zc_contract_file_url( $contract_id, $index ) {
	$url = add_query_arg(
		array(
			'zc_ct_file' => (int) $contract_id,
			'i'          => (int) $index,
		),
		home_url( '/' )
	);
	return wp_nonce_url( $url, 'zc_ct_file_' . (int) $contract_id . '_' . (int) $index );
}

/* ==========================================================================
   رابط پیشخوان
   ========================================================================== */

/**
 * افزودن متاباکس مراحل پرداخت.
 *
 * @return void
 */
function zc_payment_metaboxes() {
	add_meta_box(
		'zc-ct-stages',
		__( 'مراحل پرداخت', 'zarincode' ),
		'zc_payment_stages_box',
		'zc_contract_tpl',
		'normal',
		'high'
	);

	add_meta_box(
		'zc-ct-pay',
		__( 'وضعیت پرداخت و تحویل پروژه', 'zarincode' ),
		'zc_contract_pay_box',
		'zc_contract',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'zc_payment_metaboxes', 11 );

/**
 * متاباکس تعریف مراحل روی الگو.
 *
 * @param WP_Post $post پست.
 * @return void
 */
function zc_payment_stages_box( $post ) {
	wp_nonce_field( 'zc_pay_save', 'zc_pay_nonce' );

	$raw = (string) get_post_meta( $post->ID, '_zc_ct_stages', true );

	if ( '' === trim( $raw ) ) {
		$raw = zc_payment_stages_to_text( zc_default_payment_stages() );
	}

	$parsed = zc_parse_payment_stages( $raw );
	$sum    = 0;

	foreach ( $parsed as $s ) {
		$sum += $s['percent'];
	}

	$amount = (float) get_post_meta( $post->ID, '_zc_ct_amount', true );
	?>
	<style>
		.zc-stbox textarea{width:100%;font-family:Vazirmatn,monospace;line-height:2.1;direction:rtl}
		.zc-stbox .desc{color:#666;font-size:12px;margin:6px 0 14px;line-height:1.9}
		.zc-stbox code{background:#f2f4f7;padding:1px 6px;border-radius:4px}
		.zc-stpv{width:100%;border-collapse:collapse;margin-top:12px;font-size:13px}
		.zc-stpv th,.zc-stpv td{border:1px solid #e2e4e7;padding:8px 11px;text-align:right}
		.zc-stpv th{background:#f6f7f7;font-weight:600}
		.zc-sum-ok{color:#1B7A45;font-weight:700}
		.zc-sum-bad{color:#B32D2E;font-weight:700}
	</style>

	<div class="zc-stbox">
		<textarea name="zc_ct_stages" rows="6"><?php echo esc_textarea( $raw ); ?></textarea>

		<p class="desc">
			<?php esc_html_e( 'هر خط یک مرحله پرداخت است با ساختار:', 'zarincode' ); ?>
			<code>عنوان | درصد مبلغ | درصد پیشرفت آزادکننده | توضیح</code><br>
			<?php esc_html_e( 'ستون سوم تعیین می‌کند مرحله در چه درصدی از پیشرفت پروژه باز شود. مقدار صفر یعنی بلافاصله پس از امضای قرارداد.', 'zarincode' ); ?><br>
			<?php esc_html_e( 'مراحل باید به ترتیب پرداخت شوند و مجموع درصدها باید دقیقاً ۱۰۰ باشد.', 'zarincode' ); ?>
		</p>

		<p>
			<?php esc_html_e( 'مجموع درصدها:', 'zarincode' ); ?>
			<span class="<?php echo ( abs( $sum - 100 ) < 0.01 ) ? 'zc-sum-ok' : 'zc-sum-bad'; ?>">
				<?php echo esc_html( zc_fa_num( round( $sum, 2 ) ) ); ?>٪
				<?php if ( abs( $sum - 100 ) >= 0.01 ) : ?>
					— <?php esc_html_e( 'باید ۱۰۰ باشد!', 'zarincode' ); ?>
				<?php endif; ?>
			</span>
		</p>

		<?php if ( $parsed ) : ?>
			<table class="zc-stpv">
				<thead>
					<tr>
						<th style="width:40px">#</th>
						<th><?php esc_html_e( 'عنوان', 'zarincode' ); ?></th>
						<th style="width:90px"><?php esc_html_e( 'درصد', 'zarincode' ); ?></th>
						<th style="width:130px"><?php esc_html_e( 'مبلغ تقریبی', 'zarincode' ); ?></th>
						<th style="width:130px"><?php esc_html_e( 'آزاد در پیشرفت', 'zarincode' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $parsed as $i => $s ) : ?>
						<tr>
							<td><?php echo esc_html( zc_fa_num( $i + 1 ) ); ?></td>
							<td><strong><?php echo esc_html( $s['title'] ); ?></strong>
								<?php if ( $s['note'] ) : ?>
									<br><span style="color:#777;font-size:11.5px"><?php echo esc_html( $s['note'] ); ?></span>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( zc_fa_num( $s['percent'] ) ); ?>٪</td>
							<td>
								<?php
								echo $amount > 0
									? esc_html( zc_price_text( round( $amount * $s['percent'] / 100 ) ) )
									: '—';
								?>
							</td>
							<td><?php echo esc_html( zc_fa_num( $s['progress'] ) ); ?>٪</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * متاباکس وضعیت پرداخت روی قرارداد صادرشده.
 *
 * @param WP_Post $post پست.
 * @return void
 */
function zc_contract_pay_box( $post ) {
	wp_nonce_field( 'zc_pay_save', 'zc_pay_nonce' );

	$plan     = zc_contract_payment_plan( $post->ID );
	$override = (string) get_post_meta( $post->ID, '_zc_ct_stages_def', true );
	$files    = zc_contract_deliverables( $post->ID );
	?>
	<style>
		.zc-paybox table{width:100%;border-collapse:collapse;font-size:13px;margin-bottom:14px}
		.zc-paybox th,.zc-paybox td{border:1px solid #e2e4e7;padding:9px 11px;text-align:right}
		.zc-paybox th{background:#f6f7f7}
		.zc-pill{display:inline-block;padding:2px 11px;border-radius:20px;color:#fff;font-size:11.5px}
		.zc-paybox textarea{width:100%;font-family:Vazirmatn,monospace;line-height:2;direction:rtl}
		.zc-paybox .desc{color:#666;font-size:12px;margin:5px 0 14px;line-height:1.9}
	</style>

	<div class="zc-paybox">
		<table>
			<thead>
				<tr>
					<th style="width:40px">#</th>
					<th><?php esc_html_e( 'مرحله', 'zarincode' ); ?></th>
					<th style="width:130px"><?php esc_html_e( 'مبلغ', 'zarincode' ); ?></th>
					<th style="width:110px"><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></th>
					<th style="width:150px"><?php esc_html_e( 'تاریخ پرداخت', 'zarincode' ); ?></th>
					<th style="width:130px"><?php esc_html_e( 'کد پیگیری', 'zarincode' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $plan['stages'] as $s ) : ?>
					<?php
					$colors = array(
						'paid'   => '#1B9C58',
						'due'    => '#D89B0D',
						'locked' => '#8A93A6',
					);

					$labels = array(
						'paid'   => __( 'پرداخت شده', 'zarincode' ),
						'due'    => __( 'در انتظار پرداخت', 'zarincode' ),
						'locked' => __( 'قفل', 'zarincode' ),
					);
					?>
					<tr>
						<td><?php echo esc_html( zc_fa_num( $s['index'] + 1 ) ); ?></td>
						<td>
							<strong><?php echo esc_html( $s['title'] ); ?></strong>
							<br><span style="color:#777;font-size:11.5px">
								<?php
								printf(
									/* translators: %s: درصد */
									esc_html__( 'آزاد در پیشرفت %s٪', 'zarincode' ),
									esc_html( zc_fa_num( $s['threshold'] ) )
								);
								?>
							</span>
						</td>
						<td><?php echo esc_html( zc_price_text( $s['amount'] ) ); ?></td>
						<td>
							<span class="zc-pill" style="background:<?php echo esc_attr( $colors[ $s['status'] ] ); ?>">
								<?php echo esc_html( $labels[ $s['status'] ] ); ?>
							</span>
						</td>
						<td>
							<?php
							echo $s['paid_at']
								? esc_html( zc_fa_num( zc_jalali_date( 'Y/m/d H:i', $s['paid_at'] ) ) )
								: '—';
							?>
						</td>
						<td><code dir="ltr" style="font-size:11px"><?php echo esc_html( $s['ref_id'] ? $s['ref_id'] : '—' ); ?></code></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr style="background:#fafafa;font-weight:700">
					<td colspan="2"><?php esc_html_e( 'جمع', 'zarincode' ); ?></td>
					<td><?php echo esc_html( zc_price_text( $plan['total'] ) ); ?></td>
					<td colspan="3">
						<?php esc_html_e( 'پرداخت‌شده:', 'zarincode' ); ?>
						<?php echo esc_html( zc_price_text( $plan['paid'] ) ); ?>
						&nbsp;|&nbsp;
						<?php esc_html_e( 'مانده:', 'zarincode' ); ?>
						<?php echo esc_html( zc_price_text( $plan['remaining'] ) ); ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<h4><?php esc_html_e( 'بازنویسی مراحل فقط برای این قرارداد', 'zarincode' ); ?></h4>
		<textarea name="zc_ct_stages_def" rows="4"
			placeholder="<?php esc_attr_e( 'خالی بگذارید تا مراحل الگو استفاده شود', 'zarincode' ); ?>"><?php echo esc_textarea( $override ); ?></textarea>
		<p class="desc">
			<?php esc_html_e( 'ساختار: عنوان | درصد مبلغ | درصد پیشرفت آزادکننده | توضیح — تغییر مراحل، پرداخت‌های ثبت‌شده را حذف نمی‌کند.', 'zarincode' ); ?>
		</p>

		<h4><?php esc_html_e( 'فایل‌های تحویلی پروژه', 'zarincode' ); ?></h4>

		<?php if ( $files ) : ?>
			<table>
				<thead>
					<tr>
						<th><?php esc_html_e( 'فایل', 'zarincode' ); ?></th>
						<th style="width:110px"><?php esc_html_e( 'دانلود', 'zarincode' ); ?></th>
						<th style="width:80px"><?php esc_html_e( 'حذف', 'zarincode' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $files as $i => $f ) : ?>
						<tr>
							<td><?php echo esc_html( $f['name'] ?? get_the_title( $f['id'] ) ); ?></td>
							<td><?php echo esc_html( zc_fa_num( (int) ( $f['downloads'] ?? 0 ) ) ); ?></td>
							<td><label><input type="checkbox" name="zc_ct_file_del[]" value="<?php echo esc_attr( $i ); ?>"> <?php esc_html_e( 'حذف', 'zarincode' ); ?></label></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<p>
			<input type="file" name="zc_ct_new_file">
			<span class="desc"><?php esc_html_e( 'فایل تحویلی پروژه؛ کارفرما تنها پس از تسویه کامل می‌تواند دانلود کند.', 'zarincode' ); ?></span>
		</p>

		<?php if ( ! $plan['settled'] ) : ?>
			<p style="color:#B45309;background:#FEF6E7;padding:9px 13px;border-radius:6px;display:inline-block">
				<?php esc_html_e( 'قرارداد هنوز تسویه نشده — دانلود فایل‌ها برای کارفرما بسته است.', 'zarincode' ); ?>
			</p>
		<?php else : ?>
			<p style="color:#14713F;background:#F1FAF4;padding:9px 13px;border-radius:6px;display:inline-block">
				<?php esc_html_e( 'تسویه کامل انجام شده — کارفرما به فایل‌ها دسترسی دارد.', 'zarincode' ); ?>
			</p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * ذخیره‌ی تنظیمات پرداخت.
 *
 * @param int $post_id شناسه.
 * @return void
 */
function zc_payment_save_meta( $post_id ) {
	if ( ! isset( $_POST['zc_pay_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['zc_pay_nonce'] ) ), 'zc_pay_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( array( 'zc_ct_stages', 'zc_ct_stages_def' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, '_' . $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}

	$files = zc_contract_deliverables( $post_id );

	// حذف فایل‌های علامت‌خورده.
	if ( ! empty( $_POST['zc_ct_file_del'] ) && is_array( $_POST['zc_ct_file_del'] ) ) {
		foreach ( wp_unslash( $_POST['zc_ct_file_del'] ) as $i ) { // phpcs:ignore
			unset( $files[ (int) $i ] );
		}

		$files = array_values( $files );
	}

	// بارگذاری فایل تازه.
	if ( ! empty( $_FILES['zc_ct_new_file']['name'] ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$id = media_handle_upload( 'zc_ct_new_file', $post_id );

		if ( ! is_wp_error( $id ) ) {
			$files[] = array(
				'id'        => $id,
				'name'      => get_the_title( $id ),
				'downloads' => 0,
				'added'     => current_time( 'timestamp' ), // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp
			);
		}
	}

	update_post_meta( $post_id, '_zc_ct_files', $files );
}
add_action( 'save_post', 'zc_payment_save_meta', 11 );

/**
 * فرم ویرایش قرارداد باید چندبخشی باشد تا فایل بارگذاری شود.
 *
 * @return void
 */
function zc_contract_form_enctype() {
	$screen = get_current_screen();

	if ( $screen && 'zc_contract' === $screen->post_type ) {
		echo ' enctype="multipart/form-data"';
	}
}
add_action( 'post_edit_form_tag', 'zc_contract_form_enctype' );

/**
 * ستون پرداخت در فهرست قراردادها.
 *
 * @param array $cols ستون‌ها.
 * @return array
 */
function zc_contract_pay_column( $cols ) {
	$new = array();

	foreach ( $cols as $key => $label ) {
		$new[ $key ] = $label;

		if ( 'zc_prog' === $key ) {
			$new['zc_pay'] = __( 'پرداخت', 'zarincode' );
		}
	}

	return $new;
}
add_filter( 'manage_zc_contract_posts_columns', 'zc_contract_pay_column', 11 );

/**
 * محتوای ستون پرداخت.
 *
 * @param string $col ستون.
 * @param int    $id  شناسه.
 * @return void
 */
function zc_contract_pay_column_content( $col, $id ) {
	if ( 'zc_pay' !== $col ) {
		return;
	}

	$plan = zc_contract_payment_plan( $id );
	$paid = 0;

	foreach ( $plan['stages'] as $s ) {
		if ( 'paid' === $s['status'] ) {
			$paid++;
		}
	}

	printf(
		'<strong>%s</strong> <span style="color:#777">(%s از %s مرحله)</span>',
		esc_html( zc_fa_num( $plan['percent'] ) . '٪' ),
		esc_html( zc_fa_num( $paid ) ),
		esc_html( zc_fa_num( count( $plan['stages'] ) ) )
	);
}
add_action( 'manage_zc_contract_posts_custom_column', 'zc_contract_pay_column_content', 11, 2 );
