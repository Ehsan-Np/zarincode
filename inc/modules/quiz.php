<?php
/**
 * سیستم آزمون آنلاین دوره‌ها (Quiz) زرین کد — نسخهٔ پیشرفته
 * ---------------------------------------------------------------------------
 * برای هر دوره می‌توانید آزمون پایان دوره تعریف کنید. کاربرانِ ثبت‌نام‌شده
 * آزمون را در صفحه دوره حل می‌کنند و نمره‌شان ذخیره می‌شود.
 *
 * ویژگی‌ها:
 *  - سه نوع سوال: چندگزینه‌ای، جای خالی (پاسخ متنی) و کدنویسی.
 *  - حالت «گام‌به‌گام / چالشی» مثل w3schools: سوال یکی‌یکی می‌آید و اگر
 *    پاسخ درست باشد، سوال بعدی نمایش داده می‌شود.
 *  - حالت «همهٔ سوالات» برای ثبت نمره به‌صورت یک‌جا.
 *  - سوالات کدنویسی با اجرای واقعی کد در یک سرویس سندباکس (پیش‌فرض Wandbox)
 *    بررسی می‌شوند و خروجی با پاسخ مورد انتظار مقایسه می‌شود.
 *  - تنظیمات سراسری: فعال/غیرفعال، درصد قبولی، حداکثر تلاش، شرط گواهی، شافل.
 *  - تنظیمات اختصاصی هر دوره (درصد قبولی، حداکثر تلاش).
 *  - ذخیرهٔ تلاش‌ها و نمرات کاربران در user meta.
 *  - شرط قبولی آزمون برای صدور گواهی (اختیاری).
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * فعال بودن سیستم آزمون؟
 *
 * @return bool
 */
function zc_quiz_enabled() {
	return zc_quiz_module_enabled() && (bool) zc_opt( 'zc_quiz_enable', true );
}

/**
 * دریافت سوالات آزمون یک دوره.
 *
 * @param int $course_id دوره.
 * @return array
 */
function zc_quiz_questions( $course_id ) {
	$questions = get_post_meta( $course_id, '_zc_quiz', true );
	return is_array( $questions ) ? $questions : array();
}

/**
 * تنظیمات اختصاصی آزمون یک دوره.
 *
 * @param int $course_id دوره.
 * @return array
 */
function zc_quiz_course_settings( $course_id ) {
	$pass = get_post_meta( $course_id, '_zc_quiz_pass', true );
	$max  = get_post_meta( $course_id, '_zc_quiz_attempts', true );

	return array(
		'pass'     => $pass ? (float) $pass : (float) zc_opt( 'zc_quiz_pass_percent', 60 ),
		'attempts' => $max ? (int) $max : (int) zc_opt( 'zc_quiz_max_attempts', 3 ),
	);
}

/**
 * تلاش‌های کاربر در یک دوره.
 *
 * @param int $user_id   کاربر.
 * @param int $course_id دوره.
 * @return array
 */
function zc_quiz_user_attempts( $user_id, $course_id ) {
	$attempts = get_user_meta( $user_id, 'zc_quiz_' . $course_id, true );
	return is_array( $attempts ) ? $attempts : array();
}

/**
 * بهترین نمره کاربر در یک دوره.
 *
 * @param int $user_id   کاربر.
 * @param int $course_id دوره.
 * @return float
 */
function zc_quiz_best_score( $user_id, $course_id ) {
	$attempts = zc_quiz_user_attempts( $user_id, $course_id );
	$best = 0;
	foreach ( $attempts as $a ) {
		$best = max( $best, (float) $a['score'] );
	}
	return $best;
}

/**
 * آیا کاربر در آزمون قبول شده است؟
 *
 * @param int $user_id   کاربر.
 * @param int $course_id دوره.
 * @return bool
 */
function zc_quiz_passed( $user_id, $course_id ) {
	if ( ! zc_quiz_enabled() ) {
		return true;
	}
	$questions = zc_quiz_questions( $course_id );
	if ( empty( $questions ) ) {
		return true; // بدون آزمون، همیشه قبول.
	}
	$settings = zc_quiz_course_settings( $course_id );
	return zc_quiz_best_score( $user_id, $course_id ) >= $settings['pass'];
}

/**
 * تعداد تلاش‌های باقی‌مانده.
 *
 * @param int $user_id   کاربر.
 * @param int $course_id دوره.
 * @return int
 */
function zc_quiz_remaining_attempts( $user_id, $course_id ) {
	$settings = zc_quiz_course_settings( $course_id );
	$used     = count( zc_quiz_user_attempts( $user_id, $course_id ) );
	return max( 0, $settings['attempts'] - $used );
}

/* ==========================================================================
   موتور سوالات — سه نوع سوال + اجرای کد
   ========================================================================== */

/**
 * نوع سوال.
 *
 * @param array $q سوال.
 * @return string 'mc' | 'blank' | 'code'
 */
function zc_qtype( $q ) {
	$t = $q['type'] ?? 'mc';
	return in_array( $t, array( 'mc', 'blank', 'code' ), true ) ? $t : 'mc';
}

/**
 * نرمال‌سازی متن برای مقایسهٔ پاسخ‌ها (حذف فاصلهٔ اضافی، یکدست‌سازی).
 *
 * @param string $s متن.
 * @return string
 */
function zc_normalize_answer( $s ) {
	$s = trim( (string) $s );
	$s = preg_replace( '/\s+/u', ' ', $s );
	return trim( $s );
}

/**
 * پاسخ‌های پذیرفتنی سوال «جای خالی».
 *
 * @param array $q سوال.
 * @return array
 */
function zc_quiz_blank_answers( $q ) {
	$answers = (array) ( $q['answers'] ?? array() );
	$answers = array_filter( array_map( 'trim', $answers ) );
	return array_values( $answers );
}

/**
 * فهرست زبان‌های قابل اجرا برای سوالات کدنویسی.
 *
 * @return array
 */
/**
 * فعال بودن اجرای کد برای سوالات کدنویسی؟
 *
 * @return bool
 */
function zc_quiz_exec_enabled() {
	return (bool) zc_opt( 'zc_quiz_exec_enable', true );
}

/**
 * آیا ماژول کامل «آزمون، تمرین و کامپایلر» فعال است؟
 *
 * @return bool
 */
function zc_quiz_module_enabled() {
	return (bool) zc_opt( 'zc_quiz_module_enable', true );
}

/**
 * زبان‌های مجاز برای یک دورهٔ مشخص.
 * از متای `_zc_quiz_langs` خوانده می‌شود؛ اگر خالی باشد همهٔ زبان‌های فعال سراسری.
 *
 * @param int $course_id دوره.
 * @return array|null
 */
function zc_quiz_course_languages( $course_id ) {
	$langs = get_post_meta( $course_id, '_zc_quiz_langs', true );
	$langs = is_array( $langs ) ? array_map( 'strval', array_filter( $langs ) ) : array();
	return empty( $langs ) ? null : $langs;
}

/**
 * سوالات «تمرین چالشی» یک دوره (جدا از آزمون اصلی).
 *
 * @param int $course_id دوره.
 * @return array
 */
function zc_course_practice_questions( $course_id ) {
	$q = get_post_meta( $course_id, '_zc_course_practice', true );
	return is_array( $q ) ? $q : array();
}

/**
 * تنظیمات «تمرین چالشی» یک دوره (حد نصاب قبولی).
 *
 * @param int $course_id دوره.
 * @return array
 */
function zc_course_practice_settings( $course_id ) {
	$pass = get_post_meta( $course_id, '_zc_course_practice_pass', true );
	return array(
		'pass' => $pass ? (float) $pass : (float) zc_opt( 'zc_practice_pass', 70 ),
	);
}

/**
 * بهترین نمرهٔ کاربر در «تمرین چالشی» یک دوره.
 *
 * @param int $user_id   کاربر.
 * @param int $course_id دوره.
 * @return float
 */
function zc_course_practice_best( $user_id, $course_id ) {
	return (float) get_user_meta( $user_id, 'zc_course_practice_' . $course_id, true );
}

/**
 * اجرای کد در سرویس سندباکس (پیش‌فرض Wandbox، قابل تغییر در پنل).
 *
 * @param string $language کلید زبان.
 * @param string $code     کد.
 * @param string $stdin    ورودی استاندارد (اختیاری).
 * @return array{ok:bool,output:string,error:string,status:int,signal:string}
 */
function zc_execute_code( $language, $code, $stdin = '' ) {
	$empty = array( 'ok' => false, 'output' => '', 'error' => __( 'اجرای کد ممکن نیست.', 'zarincode' ), 'status' => 1, 'signal' => '' );

	if ( ! zc_quiz_exec_enabled() ) {
		$empty['error'] = __( 'اجرای کد در تنظیمات قالب غیرفعال است.', 'zarincode' );
		return $empty;
	}

	$langs = zc_quiz_language_defs();
	if ( ! isset( $langs[ $language ] ) ) {
		$empty['error'] = sprintf( __( 'زبان «%s» پشتیبانی نمی‌شود.', 'zarincode' ), $language );
		return $empty;
	}

	$endpoint = zc_opt( 'zc_quiz_exec_api', 'https://wandbox.org/api/compile.json' );
	$compiler = $langs[ $language ]['compiler'];

	$payload = array(
		'compiler' => $compiler,
		'code'     => (string) $code,
		'stdin'    => (string) $stdin,
	);

	$timeout = (int) zc_opt( 'zc_quiz_exec_timeout', 25 );
	$resp = wp_remote_post(
		$endpoint,
		array(
			'timeout'  => $timeout,
			'blocking' => true,
			'headers'  => array( 'Content-Type' => 'application/json', 'User-Agent' => 'Zarincode-Theme' ),
			'body'     => wp_json_encode( $payload ),
		)
	);

	if ( is_wp_error( $resp ) ) {
		$empty['error'] = $resp->get_error_message();
		return $empty;
	}

	$data = json_decode( wp_remote_retrieve_body( $resp ), true );
	if ( ! is_array( $data ) ) {
		$empty['error'] = __( 'پاسخ نامعتبر از سرویس اجرای کد دریافت شد.', 'zarincode' );
		return $empty;
	}

	$status  = isset( $data['status'] ) ? (int) $data['status'] : 1;
	$output  = (string) ( $data['program_output'] ?? '' );
	$error   = (string) ( $data['program_error'] ?? '' );
	$comp_err = (string) ( $data['compiler_error'] ?? '' );
	if ( '' !== $comp_err ) {
		$error = $comp_err . ( '' !== $error ? "\n" . $error : '' );
	}

	// محدود کردن حجم خروجی برای جلوگیری از پاسخ‌های عظیم.
	$maxchars = (int) zc_opt( 'zc_quiz_exec_maxchars', 4000 );
	if ( $maxchars > 0 ) {
		if ( mb_strlen( $output ) > $maxchars ) {
			$output = mb_substr( $output, 0, $maxchars ) . "\n… (خروجی محدود شد)";
		}
		if ( mb_strlen( $error ) > $maxchars ) {
			$error = mb_substr( $error, 0, $maxchars ) . "\n… (خروجی محدود شد)";
		}
	}

	return array(
		'ok'     => 0 === $status,
		'output' => trim( $output ),
		'error'  => trim( $error ),
		'status' => $status,
		'signal' => (string) ( $data['signal'] ?? '' ),
	);
}

/**
 * نمره‌دهی یک سوال بر اساس پاسخ کاربر.
 *
 * @param array  $q     سوال.
 * @param mixed  $value پاسخ (برای mc عدد، برای blank متن، برای code کد).
 * @return array{correct:bool,expected:string,output:string,error:string}
 */
function zc_grade_question( $q, $value ) {
	$type = zc_qtype( $q );
	$out  = array( 'correct' => false, 'expected' => '', 'output' => '', 'error' => '' );

	if ( 'mc' === $type ) {
		$answer       = (int) ( $q['answer'] ?? -1 );
		$out['correct'] = ( (int) $value === $answer );
		$opts = (array) ( $q['options'] ?? array() );
		$out['expected'] = isset( $opts[ $answer ] ) ? $opts[ $answer ] : '';
		return $out;
	}

	if ( 'blank' === $type ) {
		$answers = zc_quiz_blank_answers( $q );
		$norm    = zc_normalize_answer( $value );
		foreach ( $answers as $a ) {
			if ( zc_normalize_answer( $a ) === $norm ) {
				$out['correct'] = true;
				break;
			}
		}
		$out['expected'] = implode( ' | ', $answers );
		return $out;
	}

	// code
	$run = zc_execute_code( $q['language'] ?? 'python', (string) $value );
	$out['output']   = $run['output'];
	$out['error']    = $run['error'];
	$out['expected'] = (string) ( $q['expected'] ?? '' );

	if ( ! $run['ok'] ) {
		$out['correct'] = false;
		return $out;
	}

	$expected = zc_normalize_answer( $out['expected'] );
	$out['correct'] = ( '' !== $expected && zc_normalize_answer( $run['output'] ) === $expected );
	return $out;
}

/**
 * HTML یک سوال (برای حالت گام‌به‌گام یا همهٔ سوالات).
 *
 * @param array  $q       سوال.
 * @param int    $i       شمارهٔ سوال (صفرمبن).
 * @param string $context 'challenge' | 'all'.
 * @return string
 */
function zc_quiz_question_html( $q, $i, $context = 'challenge', $allowed_langs = null ) {
	$type = zc_qtype( $q );
	$num  = zc_fa_num( $i + 1 );

	// زبان‌های قابل انتخاب برای این بافتار (دوره/تمرین). اگر خالی باشد همهٔ فعال سراسری.
	$q_langs = zc_quiz_languages( $allowed_langs );
	if ( empty( $q_langs ) ) {
		$q_langs = zc_quiz_languages();
	}

	ob_start();
	?>
	<div class="zc-q zc-q--<?php echo esc_attr( $type ); ?>" data-qi="<?php echo (int) $i; ?>" data-qtype="<?php echo esc_attr( $type ); ?>">
		<p class="zc-q__title"><span class="zc-q__num"><?php echo esc_html( $num ); ?></span> <?php echo esc_html( $q['question'] ); ?></p>

		<?php if ( 'mc' === $type ) : ?>
			<div class="zc-q__opts">
				<?php foreach ( (array) ( $q['options'] ?? array() ) as $oi => $opt ) : ?>
					<label class="zc-q__opt">
						<input type="radio" name="zcq_<?php echo (int) $i; ?>" value="<?php echo (int) $oi; ?>">
						<span><?php echo esc_html( $opt ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>

		<?php elseif ( 'blank' === $type ) : ?>
			<input type="text" class="zc-q__input" name="zcq_<?php echo (int) $i; ?>" placeholder="<?php esc_attr_e( 'پاسخ را بنویسید…', 'zarincode' ); ?>" autocomplete="off">
			<?php if ( ! empty( $q['hint'] ) ) : ?>
				<p class="zc-q__hint"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> <?php echo esc_html( $q['hint'] ); ?></p>
			<?php endif; ?>

		<?php elseif ( 'code' === $type ) : ?>
			<div class="zc-q__code">
				<div class="zc-q__codehead">
					<span class="zc-q__codelabel"><?php esc_html_e( 'کد خود را بنویسید و سپس «اجرا» و «بررسی» را بزنید:', 'zarincode' ); ?></span>
					<select class="zc-q__langsel" data-qi="<?php echo (int) $i; ?>">
						<?php
						$cur = $q['language'] ?? '';
						if ( ! isset( $q_langs[ $cur ] ) ) {
							$keys = array_keys( $q_langs );
							$cur  = $keys ? $keys[0] : '';
						}
						foreach ( $q_langs as $k => $l ) {
							echo '<option value="' . esc_attr( $k ) . '"' . selected( $cur, $k, false ) . '>' . esc_html( $l['label'] ) . '</option>';
						}
						?>
					</select>
				</div>
				<textarea class="zc-q__textarea" name="zcq_<?php echo (int) $i; ?>" rows="8" spellcheck="false" data-lang="<?php echo esc_attr( $cur ); ?>"><?php echo esc_textarea( $q['starter'] ?? '' ); ?></textarea>
				<?php if ( zc_opt( 'zc_quiz_exec_show_stdin', true ) ) : ?>
					<input type="text" class="zc-q__stdin" placeholder="<?php esc_attr_e( 'ورودی استاندارد (stdin) — اختیاری', 'zarincode' ); ?>" autocomplete="off">
				<?php endif; ?>
				<div class="zc-q__codebar">
					<button type="button" class="zc-btn zc-btn--sm zc-btn--ghost zc-q__run"><?php esc_html_e( 'اجرا', 'zarincode' ); ?></button>
					<span class="zc-q__hint"><?php esc_html_e( 'خروجی باید با پاسخ مورد انتظار یکی باشد.', 'zarincode' ); ?></span>
				</div>
				<pre class="zc-q__output" hidden></pre>
				<?php if ( ! empty( $q['hint'] ) ) : ?>
					<p class="zc-q__hint"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> <?php echo esc_html( $q['hint'] ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( 'challenge' === $context ) : ?>
			<div class="zc-q__footer">
				<button type="button" class="zc-btn zc-btn--gold zc-q__submit"><?php esc_html_e( 'بررسی پاسخ', 'zarincode' ); ?></button>
			</div>
		<?php endif; ?>

		<div class="zc-q__feedback"></div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * افزودن شرط قبولی آزمون برای صدور گواهی.
 *
 * @param int $user_id   کاربر.
 * @param int $course_id دوره.
 * @return void
 */
function zc_quiz_maybe_issue_certificate( $user_id, $course_id ) {
	if ( ! zc_quiz_module_enabled() || ! zc_quiz_enabled() || ! zc_opt( 'zc_quiz_require_for_cert', true ) ) {
		return;
	}

	$questions = zc_quiz_questions( $course_id );
	if ( empty( $questions ) ) {
		return;
	}

	if ( ! zc_quiz_passed( $user_id, $course_id ) ) {
		remove_action( 'zc_course_completed', 'zc_issue_certificate', 10 );
	}
}
add_action( 'zc_course_completed', 'zc_quiz_maybe_issue_certificate', 9, 2 );

/**
 * پس از ثبت یک تلاش قبول‌شده، اگر دوره کامل شده باشد مدرک صادر می‌شود.
 * (برای حالتی که کاربر دوره را کامل کرده اما گواهی به خاطر نشدنِ حد نصاب
 * صادر نشده است؛ حالا که آزمون قبول شده، مدرک را صادر می‌کنیم.)
 *
 * @param int $user_id   کاربر.
 * @param int $course_id دوره.
 * @return void
 */
function zc_quiz_maybe_issue_cert_after_pass( $user_id, $course_id ) {
	if ( ! zc_quiz_module_enabled() || ! zc_quiz_enabled() || ! zc_opt( 'zc_quiz_require_for_cert', true ) ) {
		return;
	}
	if ( ! function_exists( 'zc_get_course_progress' ) || ! function_exists( 'zc_issue_certificate' ) ) {
		return;
	}
	// فقط وقتی دوره تکمیل شده باشد مدرک صادر می‌شود.
	if ( (int) zc_get_course_progress( $user_id, $course_id ) >= 100 ) {
		zc_issue_certificate( $user_id, $course_id );
	}
}

/* ==========================================================================
   AJAX
   ========================================================================== */

/**
 * خواندن سوالات از منبع (دوره یا تمرین) بر اساس نوع.
 *
 * @param string $type 'course' | 'practice'.
 * @param int    $id   شناسه.
 * @return array
 */
/**
 * زبان‌های مجاز برای یک بافتار (دوره یا تمرین).
 *
 * @param string $type 'course' | 'practice'.
 * @param int    $id   شناسه.
 * @return array|null
 */
function zc_quiz_context_languages( $type, $id ) {
	if ( 'practice' === $type ) {
		return function_exists( 'zc_practice_languages' ) ? zc_practice_languages( $id ) : null;
	}
	return zc_quiz_course_languages( $id );
}

/**
 * خواندن سوالات از منبع (دوره، تمرین چالشی دوره یا تمرین پنل).
 *
 * @param string $type 'course' | 'course_practice' | 'practice'.
 * @param int    $id   شناسه.
 * @return array
 */
function zc_quiz_source_questions( $type, $id ) {
	if ( 'course_practice' === $type ) {
		return zc_course_practice_questions( $id );
	}
	if ( 'practice' === $type ) {
		$questions = get_post_meta( $id, '_zc_practice', true );
		return is_array( $questions ) ? $questions : array();
	}
	return zc_quiz_questions( $id );
}

/**
 * AJAX: بررسی یک سوال در حالت گام‌به‌گام (بدون ثبت تلاش).
 * پارامترها: type(course|practice), id, qi, answer(JSON).
 *
 * @return void
 */
function zc_ajax_quiz_check() {
	zc_check_ajax();

	$user_id = get_current_user_id();
	$type    = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : 'course'; // phpcs:ignore
	$id      = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0; // phpcs:ignore
	$qi      = isset( $_POST['qi'] ) ? absint( $_POST['qi'] ) : 0; // phpcs:ignore
	$answer  = isset( $_POST['answer'] ) ? wp_unslash( $_POST['answer'] ) : ''; // phpcs:ignore

	if ( ! $user_id || ! $id ) {
		wp_send_json_error( array( 'message' => __( 'اطلاعات نامعتبر است.', 'zarincode' ) ) );
	}

	if ( 'course' === $type && ! zc_user_has_course( $user_id, $id ) ) {
		wp_send_json_error( array( 'message' => __( 'شما به این دوره دسترسی ندارید.', 'zarincode' ) ) );
	}

	$questions = zc_quiz_source_questions( $type, $id );
	if ( empty( $questions ) || ! isset( $questions[ $qi ] ) ) {
		wp_send_json_error( array( 'message' => __( 'سوالی یافت نشد.', 'zarincode' ) ) );
	}

	$q      = $questions[ $qi ];
	$result = zc_grade_question( $q, $answer );
	$total  = count( $questions );

	// بازخورد.
	$msg = '';
	if ( $result['correct'] ) {
		$msg = '<div class="zc-alert zc-alert--success">' . zc_icon( 'check', 18 ) . '<span>' . esc_html__( 'آفرین! پاسخ درست است.', 'zarincode' ) . '</span></div>';
	} else {
		$msg = '<div class="zc-alert zc-alert--error">' . zc_icon( 'close', 18 ) . '<span>' . esc_html__( 'پاسخ درست نیست، دوباره تلاش کنید.', 'zarincode' ) . '</span></div>';
		if ( '' !== $result['error'] ) {
			$msg .= '<pre class="zc-q__errout">' . esc_html( $result['error'] ) . '</pre>';
		}
	}

	$done      = false;
	$next_html = '';
	if ( $result['correct'] && $qi + 1 < $total ) {
		$next_html = zc_quiz_question_html( $questions[ $qi + 1 ], $qi + 1, 'challenge', zc_quiz_context_languages( $type, $id ) );
	} elseif ( $result['correct'] ) {
		$done = true;
	}

	wp_send_json_success(
		array(
			'correct'   => $result['correct'],
			'msg'       => $msg,
			'expected'  => $result['expected'],
			'output'    => $result['output'],
			'next_html' => $next_html,
			'done'      => $done,
			'qi'        => $qi,
			'total'     => $total,
		)
	);
}
add_action( 'wp_ajax_zc_quiz_check', 'zc_ajax_quiz_check' );

/**
 * AJAX: پایان حالت گام‌به‌گام و ثبت تلاش/نمره.
 * پارامترها: type, id, first_correct, total.
 *
 * @return void
 */
function zc_ajax_quiz_finish() {
	zc_check_ajax();

	$user_id  = get_current_user_id();
	$type     = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : 'course'; // phpcs:ignore
	$id       = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0; // phpcs:ignore
	$first    = isset( $_POST['first_correct'] ) ? absint( $_POST['first_correct'] ) : 0; // phpcs:ignore
	$total    = isset( $_POST['total'] ) ? max( 1, absint( $_POST['total'] ) ) : 1; // phpcs:ignore

	if ( ! $user_id || ! $id ) {
		wp_send_json_error( array( 'message' => __( 'اطلاعات نامعتبر است.', 'zarincode' ) ) );
	}

	$score  = round( ( $first / $total ) * 100, 1 );
	$passed = false;
	$message = '';

	if ( 'practice' === $type ) {
		// ذخیرهٔ بهترین نمرهٔ تمرین.
		$best = (float) get_user_meta( $user_id, 'zc_practice_' . $id, true );
		if ( $score > $best ) {
			update_user_meta( $user_id, 'zc_practice_' . $id, $score );
		}
		$practice_pass = function_exists( 'zc_practice_settings' ) ? (float) zc_practice_settings( $id )['pass'] : (float) zc_opt( 'zc_practice_pass', 70 );
		$passed        = $score >= $practice_pass;
		$message       = $passed ? __( 'تمرین را با موفقیت کامل کردید!', 'zarincode' ) : __( 'تمرین تمام شد.', 'zarincode' );
	} elseif ( 'course_practice' === $type ) {
		// تمرین چالشی دوره.
		if ( ! zc_user_has_course( $user_id, $id ) ) {
			wp_send_json_error( array( 'message' => __( 'شما به این دوره دسترسی ندارید.', 'zarincode' ) ) );
		}
		$cp_settings = zc_course_practice_settings( $id );
		$best        = zc_course_practice_best( $user_id, $id );
		if ( $score > $best ) {
			update_user_meta( $user_id, 'zc_course_practice_' . $id, $score );
		}
		$passed  = $score >= $cp_settings['pass'];
		$message = $passed ? __( 'تمرین چالشی را با موفقیت کامل کردید!', 'zarincode' ) : __( 'تمرین چالشی تمام شد.', 'zarincode' );
	} else {
		if ( ! zc_user_has_course( $user_id, $id ) ) {
			wp_send_json_error( array( 'message' => __( 'شما به این دوره دسترسی ندارید.', 'zarincode' ) ) );
		}
		$settings = zc_quiz_course_settings( $id );
		$attempts = zc_quiz_user_attempts( $user_id, $id );
		if ( count( $attempts ) >= $settings['attempts'] ) {
			wp_send_json_error( array( 'message' => __( 'به حداکثر تعداد تلاش رسیده‌اید.', 'zarincode' ) ) );
		}
		$passed = $score >= $settings['pass'];

		$attempts[] = array(
			'score'   => $score,
			'correct' => $first,
			'total'   => $total,
			'passed'  => $passed,
			'mode'    => 'challenge',
			'date'    => current_time( 'mysql' ),
		);
		update_user_meta( $user_id, 'zc_quiz_' . $id, $attempts );

		if ( $passed && zc_opt( 'zc_quiz_require_for_cert', true ) ) {
			update_user_meta( $user_id, 'zc_quiz_passed_' . $id, '1' );
			zc_quiz_maybe_issue_cert_after_pass( $user_id, $id );
		}

		$message = $passed ? __( 'تبریک! در آزمون قبول شدید.', 'zarincode' ) : __( 'این تلاش ثبت شد. برای قبولی باید درصد بالاتری بگیرید.', 'zarincode' );
	}

	// ثبت تلاش در جدول گزارش (برای گزارش‌گیری).
	if ( function_exists( 'zc_log_attempt' ) ) {
		zc_log_attempt( $user_id, $type, $id, $score, $first, $total, $passed, 'challenge' );
	}

	wp_send_json_success(
		array(
			'score'   => $score,
			'correct' => $first,
			'total'   => $total,
			'passed'  => $passed,
			'message' => $message,
		)
	);
}
add_action( 'wp_ajax_zc_quiz_finish', 'zc_ajax_quiz_finish' );

/**
 * AJAX: اجرای کد کاربر (برای دکمهٔ «اجرا»).
 * پارامترها: language, code, stdin.
 *
 * @return void
 */
function zc_ajax_quiz_run() {
	zc_check_ajax();

	$user_id  = get_current_user_id();
	$language = isset( $_POST['language'] ) ? sanitize_key( wp_unslash( $_POST['language'] ) ) : 'python'; // phpcs:ignore
	$code     = isset( $_POST['code'] ) ? wp_unslash( $_POST['code'] ) : ''; // phpcs:ignore
	$stdin    = isset( $_POST['stdin'] ) ? wp_unslash( $_POST['stdin'] ) : ''; // phpcs:ignore

	if ( ! $user_id ) {
		wp_send_json_error( array( 'message' => __( 'برای استفاده از این بخش وارد شوید.', 'zarincode' ) ) );
	}

	// محدودیت نرخ ساده برای هر کاربر (قابل تنظیم در پنل).
	$limit = max( 0, (int) zc_opt( 'zc_quiz_exec_ratelimit', 2 ) );
	if ( $limit > 0 ) {
		$last = (int) get_transient( 'zc_run_' . $user_id );
		if ( $last && ( time() - $last ) < $limit ) {
			wp_send_json_error( array( 'message' => __( 'لطفاً کمی صبر کنید.', 'zarincode' ) ) );
		}
	}
	set_transient( 'zc_run_' . $user_id, time(), max( 1, $limit + 5 ) );

	$run = zc_execute_code( $language, $code, $stdin );
	wp_send_json_success( $run );
}
add_action( 'wp_ajax_zc_quiz_run', 'zc_ajax_quiz_run' );

/**
 * AJAX: ثبت پاسخ‌های حالت «همهٔ سوالات» و محاسبه نمره.
 * پارامترها: course_id, answers(JSON آرایه‌ای شامل مقادیر هر سوال).
 *
 * @return void
 */
function zc_ajax_quiz_submit() {
	zc_check_ajax();

	$user_id   = get_current_user_id();
	$course_id = isset( $_POST['course_id'] ) ? absint( $_POST['course_id'] ) : 0; // phpcs:ignore
	$answers   = isset( $_POST['answers'] ) ? json_decode( wp_unslash( $_POST['answers'] ), true ) : array(); // phpcs:ignore

	if ( ! $user_id || ! $course_id ) {
		wp_send_json_error( array( 'message' => __( 'خطا در دریافت اطلاعات.', 'zarincode' ) ) );
	}

	if ( ! zc_user_has_course( $user_id, $course_id ) ) {
		wp_send_json_error( array( 'message' => __( 'شما به این دوره دسترسی ندارید.', 'zarincode' ) ) );
	}

	$questions = zc_quiz_questions( $course_id );
	if ( empty( $questions ) ) {
		wp_send_json_error( array( 'message' => __( 'آزمونی برای این دوره تعریف نشده است.', 'zarincode' ) ) );
	}

	$settings = zc_quiz_course_settings( $course_id );
	$attempts = zc_quiz_user_attempts( $user_id, $course_id );

	if ( count( $attempts ) >= $settings['attempts'] ) {
		wp_send_json_error( array( 'message' => __( 'به حداکثر تعداد تلاش رسیده‌اید.', 'zarincode' ) ) );
	}

	if ( zc_opt( 'zc_quiz_shuffle', false ) ) {
		shuffle( $questions );
	}

	$correct = 0;
	$total   = count( $questions );

	foreach ( $questions as $qi => $q ) {
		$value = isset( $answers[ $qi ] ) ? $answers[ $qi ] : '';
		$res   = zc_grade_question( $q, $value );
		if ( $res['correct'] ) {
			$correct++;
		}
	}

	$score  = $total ? round( ( $correct / $total ) * 100, 1 ) : 0;
	$passed = $score >= $settings['pass'];

	$attempts[] = array(
		'score'   => $score,
		'correct' => $correct,
		'total'   => $total,
		'passed'  => $passed,
		'mode'    => 'all',
		'date'    => current_time( 'mysql' ),
	);
	update_user_meta( $user_id, 'zc_quiz_' . $course_id, $attempts );

	if ( $passed && zc_opt( 'zc_quiz_require_for_cert', true ) ) {
		update_user_meta( $user_id, 'zc_quiz_passed_' . $course_id, '1' );
		zc_quiz_maybe_issue_cert_after_pass( $user_id, $course_id );
	}

	// ثبت تلاش در جدول گزارش.
	if ( function_exists( 'zc_log_attempt' ) ) {
		zc_log_attempt( $user_id, 'course', $course_id, $score, $correct, $total, $passed, 'all' );
	}

	wp_send_json_success(
		array(
			'score'   => $score,
			'correct' => $correct,
			'total'   => $total,
			'passed'  => $passed,
			'pass'    => $settings['pass'],
			'message' => $passed ? __( 'تبریک! در آزمون قبول شدید.', 'zarincode' ) : __( 'در این آزمون قبول نشدید. دوباره تلاش کنید.', 'zarincode' ),
		)
	);
}
add_action( 'wp_ajax_zc_quiz_submit', 'zc_ajax_quiz_submit' );

/* ==========================================================================
   متاباکس سوالات (مشترک بین آزمون دوره و تمرین پنل)
   ========================================================================== */

/**
 * رندر ویرایشگر سوالات برای یک متاکی.
 *
 * @param \WP_Post $post     پست.
 * @param string   $meta_key کلید متا.
 * @return void
 */
function zc_render_questions_editor( $post, $meta_key, $field = '' ) {
	$questions = get_post_meta( $post->ID, $meta_key, true );
	if ( ! is_array( $questions ) ) {
		$questions = array();
	}
	if ( '' === $field ) {
		$field = ( '_zc_practice' === $meta_key ) ? 'zcp_q' : 'zcq_q';
	}
	?>
	<style>
		.zc-quiz-q{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;margin-bottom:12px}
		.zc-quiz-q .zc-qrow{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:8px}
		.zc-quiz-q input[type=text],.zc-quiz-q textarea,.zc-quiz-q select{width:100%;margin-bottom:8px}
		.zc-quiz-q .zc-qmeta{display:flex;gap:8px;flex-wrap:wrap}
		.zc-quiz-q .zc-qmeta>*{flex:1;min-width:150px}
		.zc-quiz-q .zc-f{display:none;margin-top:6px}
		.zc-quiz-q[data-type=mc] .zc-f.mc,.zc-quiz-q[data-type=blank] .zc-f.blank,.zc-quiz-q[data-type=code] .zc-f.code{display:block}
		.zc-qtype-tag{font-size:.72rem;font-weight:700;padding:2px 8px;border-radius:20px}
	</style>
	<div id="zc-quiz-questions">
		<?php foreach ( $questions as $i => $q ) : ?>
			<?php $t = zc_qtype( $q ); ?>
			<div class="zc-quiz-q" data-type="<?php echo esc_attr( $t ); ?>" data-q="<?php echo (int) $i; ?>">
				<div class="zc-qrow">
					<select name="<?php echo esc_attr( $field ); ?>[<?php echo (int) $i; ?>][type]" class="zc-qtype" style="width:auto">
						<option value="mc" <?php selected( $t, 'mc' ); ?>><?php esc_html_e( 'چندگزینه‌ای', 'zarincode' ); ?></option>
						<option value="blank" <?php selected( $t, 'blank' ); ?>><?php esc_html_e( 'جای خالی (متن)', 'zarincode' ); ?></option>
						<option value="code" <?php selected( $t, 'code' ); ?>><?php esc_html_e( 'کدنویسی', 'zarincode' ); ?></option>
					</select>
					<button type="button" class="button zc-quiz-remove"><?php esc_html_e( 'حذف', 'zarincode' ); ?></button>
				</div>
				<input type="text" name="<?php echo esc_attr( $field ); ?>[<?php echo (int) $i; ?>][question]" value="<?php echo esc_attr( $q['question'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'متن سوال', 'zarincode' ); ?>">

				<div class="zc-f mc">
					<div style="display:flex;gap:6px;flex-wrap:wrap">
						<?php for ( $o = 0; $o < 4; $o++ ) : ?>
							<input type="text" name="<?php echo esc_attr( $field ); ?>[<?php echo (int) $i; ?>][options][<?php echo $o; ?>]" value="<?php echo esc_attr( $q['options'][ $o ] ?? '' ); ?>" placeholder="<?php echo esc_attr( 'گزینه ' . ( $o + 1 ) ); ?>" style="flex:1;min-width:120px">
						<?php endfor; ?>
					</div>
					<label style="font-size:.8rem"><?php esc_html_e( 'پاسخ صحیح:', 'zarincode' ); ?>
						<select name="<?php echo esc_attr( $field ); ?>[<?php echo (int) $i; ?>][answer]" style="width:auto">
							<?php for ( $o = 0; $o < 4; $o++ ) : ?>
								<option value="<?php echo $o; ?>" <?php selected( (int) ( $q['answer'] ?? 0 ), $o ); ?>><?php echo (int) ( $o + 1 ); ?></option>
							<?php endfor; ?>
						</select>
					</label>
				</div>

				<div class="zc-f blank">
					<label style="font-size:.8rem"><?php esc_html_e( 'پاسخ‌های پذیرفتنی (با , جدا کنید):', 'zarincode' ); ?></label>
					<input type="text" name="<?php echo esc_attr( $field ); ?>[<?php echo (int) $i; ?>][answers]" value="<?php echo esc_attr( implode( ', ', (array) ( $q['answers'] ?? array() ) ) ); ?>" placeholder="پاسخ۱, پاسخ۲">
					<input type="text" name="<?php echo esc_attr( $field ); ?>[<?php echo (int) $i; ?>][hint]" value="<?php echo esc_attr( $q['hint'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'راهنمایی (اختیاری)', 'zarincode' ); ?>">
				</div>

				<div class="zc-f code">
					<div class="zc-qmeta">
						<select name="<?php echo esc_attr( $field ); ?>[<?php echo (int) $i; ?>][language]">
							<?php foreach ( zc_quiz_languages() as $k => $l ) : ?>
								<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $q['language'] ?? 'python', $k ); ?>><?php echo esc_html( $l['label'] ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<label style="font-size:.8rem"><?php esc_html_e( 'خروجی مورد انتظار (هر چه برنامه چاپ کند):', 'zarincode' ); ?></label>
					<input type="text" name="<?php echo esc_attr( $field ); ?>[<?php echo (int) $i; ?>][expected]" value="<?php echo esc_attr( $q['expected'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'مثلاً: سلام دنیا', 'zarincode' ); ?>">
					<label style="font-size:.8rem"><?php esc_html_e( 'کد اولیهٔ ادیتور (اختیاری):', 'zarincode' ); ?></label>
					<textarea name="<?php echo esc_attr( $field ); ?>[<?php echo (int) $i; ?>][starter]" rows="3" placeholder="<?php esc_attr_e( 'کد پیش‌فرضی که در ادیتور نمایش داده می‌شود', 'zarincode' ); ?>"><?php echo esc_textarea( $q['starter'] ?? '' ); ?></textarea>
					<input type="text" name="<?php echo esc_attr( $field ); ?>[<?php echo (int) $i; ?>][hint]" value="<?php echo esc_attr( $q['hint'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'راهنمایی (اختیاری)', 'zarincode' ); ?>">
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<button type="button" class="button" id="zc-quiz-add"><?php esc_html_e( 'افزودن سوال', 'zarincode' ); ?></button>

	<script>
	(function () {
		var field = '<?php echo esc_js( $field ); ?>';
		var idx = <?php echo (int) count( $questions ); ?>;
		var langs = <?php
		$opts = array();
		foreach ( zc_quiz_languages() as $k => $l ) {
			$opts[ $k ] = $l['label'];
		}
		echo wp_json_encode( $opts );
		?>;
		var langSel = Object.keys(langs).map(function (k) { return '<option value="' + k + '">' + langs[k] + '</option>'; }).join('');
		var box = document.getElementById('zc-quiz-questions');

		function mcFields(i) {
			var opts = '';
			for (var o = 0; o < 4; o++) opts += '<input type="text" name="' + field + '[' + i + '][options][' + o + ']" placeholder="گزینه ' + (o+1) + '" style="flex:1;min-width:120px">';
			var sel = '<select name="' + field + '[' + i + '][answer]" style="width:auto">';
			for (var o = 0; o < 4; o++) sel += '<option value="' + o + '">' + (o+1) + '</option>';
			sel += '</select>';
			return '<div class="zc-f mc"><div style="display:flex;gap:6px;flex-wrap:wrap">' + opts + '</div><label style="font-size:.8rem">پاسخ صحیح: ' + sel + '</label></div>';
		}
		function blankFields(i) {
			return '<div class="zc-f blank">' +
				'<label style="font-size:.8rem">پاسخ‌های پذیرفتنی (با , جدا کنید):</label>' +
				'<input type="text" name="' + field + '[' + i + '][answers]" placeholder="پاسخ۱, پاسخ۲">' +
				'<input type="text" name="' + field + '[' + i + '][hint]" placeholder="راهنمایی (اختیاری)"></div>';
		}
		function codeFields(i) {
			return '<div class="zc-f code">' +
				'<div class="zc-qmeta"><select name="' + field + '[' + i + '][language]">' + langSel + '</select></div>' +
				'<label style="font-size:.8rem">خروجی مورد انتظار (هر چه برنامه چاپ کند):</label>' +
				'<input type="text" name="' + field + '[' + i + '][expected]" placeholder="مثلاً: سلام دنیا">' +
				'<label style="font-size:.8rem">کد اولیهٔ ادیتور (اختیاری):</label>' +
				'<textarea name="' + field + '[' + i + '][starter]" rows="3" placeholder="کد پیش‌فرض"></textarea>' +
				'<input type="text" name="' + field + '[' + i + '][hint]" placeholder="راهنمایی (اختیاری)"></div>';
		}
		function qHTML(i) {
			var types = '<select name="' + field + '[' + i + '][type]" class="zc-qtype" style="width:auto">' +
				'<option value="mc">چندگزینه‌ای</option><option value="blank">جای خالی (متن)</option><option value="code">کدنویسی</option></select>';
			return '<div class="zc-quiz-q" data-type="mc" data-q="' + i + '">' +
				'<div class="zc-qrow">' + types + '<button type="button" class="button zc-quiz-remove">حذف</button></div>' +
				'<input type="text" name="' + field + '[' + i + '][question]" placeholder="متن سوال">' +
				mcFields(i) + blankFields(i) + codeFields(i) +
				'</div>';
		}
		document.getElementById('zc-quiz-add').addEventListener('click', function () {
			box.insertAdjacentHTML('beforeend', qHTML(idx++));
		});
		box.addEventListener('click', function (e) {
			if (e.target.classList.contains('zc-quiz-remove')) e.target.closest('.zc-quiz-q').remove();
		});
		box.addEventListener('change', function (e) {
			if (e.target.classList.contains('zc-qtype')) {
				e.target.closest('.zc-quiz-q').dataset.type = e.target.value;
			}
		});
	})();
	</script>
	<?php
}

/**
 * ذخیرهٔ سوالات از یک فیلد فرم به متا.
 *
 * @param int    $post_id     پست.
 * @param string $field       نام فیلد فرم.
 * @param string $meta_key    کلید متا.
 * @param string $nonce_field فیلد نانس.
 * @param string $nonce_action اکشن نانس.
 * @return void
 */
function zc_save_questions_meta( $post_id, $field, $meta_key, $nonce_field, $nonce_action ) {
	if ( ! isset( $_POST[ $nonce_field ] ) || ! wp_verify_nonce( wp_unslash( $_POST[ $nonce_field ] ), $nonce_action ) ) { // phpcs:ignore
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$clean = array();
	if ( ! empty( $_POST[ $field ] ) && is_array( $_POST[ $field ] ) ) { // phpcs:ignore
		foreach ( wp_unslash( $_POST[ $field ] ) as $q ) { // phpcs:ignore
			$question = trim( (string) ( $q['question'] ?? '' ) );
			if ( '' === $question ) {
				continue;
			}
			$type = zc_qtype( $q );

			$item = array(
				'type'     => $type,
				'question' => sanitize_text_field( $question ),
				'hint'     => isset( $q['hint'] ) ? sanitize_text_field( $q['hint'] ) : '',
			);

			if ( 'mc' === $type ) {
				$options = array();
				foreach ( (array) ( $q['options'] ?? array() ) as $o ) {
					$o = sanitize_text_field( $o );
					if ( '' !== $o ) {
						$options[] = $o;
					}
				}
				$options = array_values( $options );
				if ( count( $options ) < 2 ) {
					continue;
				}
				$item['options'] = $options;
				$item['answer']  = min( (int) ( $q['answer'] ?? 0 ), count( $options ) - 1 );
			} elseif ( 'blank' === $type ) {
				$answers = array_map( 'sanitize_text_field', array_map( 'trim', explode( ',', (string) ( $q['answers'] ?? '' ) ) ) );
				$answers = array_values( array_filter( $answers ) );
				if ( empty( $answers ) ) {
					continue;
				}
				$item['answers'] = $answers;
			} elseif ( 'code' === $type ) {
				$langs = zc_quiz_languages();
				$lang  = sanitize_key( $q['language'] ?? 'python' );
				if ( ! isset( $langs[ $lang ] ) ) {
					$lang = 'python';
				}
				$expected = trim( (string) ( $q['expected'] ?? '' ) );
				if ( '' === $expected ) {
					continue;
				}
				$item['language'] = $lang;
				$item['expected'] = $expected;
				$item['starter']  = (string) ( $q['starter'] ?? '' );
			}

			$clean[] = $item;
		}
	}

	update_post_meta( $post_id, $meta_key, $clean );
}

/**
 * متاباکس آزمون دوره.
 *
 * @return void
 */
function zc_register_quiz_metabox() {
	add_meta_box(
		'zc_quiz_metabox',
		__( 'آزمون پایان دوره (Quiz)', 'zarincode' ),
		'zc_quiz_metabox_html',
		'zc_course',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'zc_register_quiz_metabox' );

/**
 * محتوای متاباکس آزمون.
 *
 * @param \WP_Post $post پست.
 * @return void
 */
function zc_quiz_metabox_html( $post ) {
	wp_nonce_field( 'zc_quiz_save', 'zc_quiz_nonce' );
	$settings = zc_quiz_course_settings( $post->ID );
	?>
	<style>
		.zc-quiz-opt{display:inline-flex;align-items:center;gap:6px;margin-inline-end:16px;margin-bottom:8px}
	</style>
	<div class="zc-quiz-opt">
		<label><?php esc_html_e( 'حد نصاب (درصد قبولی):', 'zarincode' ); ?></label>
		<input type="number" name="zc_quiz_pass" min="0" max="100" value="<?php echo esc_attr( $settings['pass'] ); ?>" style="width:70px">
		<span class="description"><?php esc_html_e( '(خالی = پیش‌فرض قالب)', 'zarincode' ); ?></span>
	</div>
	<div class="zc-quiz-opt">
		<label><?php esc_html_e( 'حداکثر تلاش:', 'zarincode' ); ?></label>
		<input type="number" name="zc_quiz_attempts" min="1" max="20" value="<?php echo esc_attr( $settings['attempts'] ); ?>" style="width:70px">
		<span class="description"><?php esc_html_e( '(خالی = پیش‌فرض)', 'zarincode' ); ?></span>
	</div>
	<p style="font-size:.8rem;color:#64748b">
		<?php esc_html_e( 'سه نوع سوال پشتیبانی می‌شود: چندگزینه‌ای، جای خالی (پاسخ متنی) و کدنویسی (با اجرای واقعی کد). هنگام رسیدن کاربر به «حد نصاب»، اگر دوره کامل شده باشد مدرک صادر می‌شود.', 'zarincode' ); ?>
	</p>

	<?php
	$zc_all_langs = zc_quiz_languages();
	if ( $zc_all_langs ) :
		$zc_course_langs = zc_quiz_course_languages( $post->ID );
		?>
		<div class="zc-quiz-opt" style="display:block">
			<label style="font-weight:600"><?php esc_html_e( 'زبان‌های مجاز این دوره (در سوالات کدنویسی):', 'zarincode' ); ?></label>
			<span class="description" style="margin-inline-start:6px"><?php esc_html_e( '(اگر هیچ‌کدام انتخاب نشود، همهٔ زبان‌های فعال سراسری نمایش داده می‌شوند)', 'zarincode' ); ?></span>
			<div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:6px">
				<?php foreach ( $zc_all_langs as $k => $l ) : ?>
					<label style="display:inline-flex;align-items:center;gap:5px;background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:3px 12px;cursor:pointer">
						<input type="checkbox" name="zc_quiz_langs[]" value="<?php echo esc_attr( $k ); ?>" <?php checked( is_array( $zc_course_langs ) && in_array( $k, $zc_course_langs, true ) ); ?>>
						<?php echo esc_html( $l['label'] ); ?>
					</label>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<h3 style="margin:14px 0 8px"><?php esc_html_e( 'سوالات آزمون', 'zarincode' ); ?></h3>
	<?php zc_render_questions_editor( $post, '_zc_quiz' ); ?>

	<hr style="margin:24px 0;border:0;border-top:1px solid #e2e8f0">

	<h3 style="margin:0 0 6px"><?php esc_html_e( 'تمرین چالشی این دوره', 'zarincode' ); ?></h3>
	<p style="font-size:.8rem;color:#64748b;margin:0 0 10px">
		<?php esc_html_e( 'تمرین چالشی جدا از آزمون است؛ کاربر در صفحهٔ دوره آن را گام‌به‌گام حل می‌کند و بهترین نمره‌اش ثبت می‌شود (برای صدور مدرک الزامی نیست).', 'zarincode' ); ?>
	</p>
	<?php
	$cp_settings = zc_course_practice_settings( $post->ID );
	?>
	<div class="zc-quiz-opt">
		<label><?php esc_html_e( 'حد نصاب قبولی تمرین چالشی:', 'zarincode' ); ?></label>
		<input type="number" name="zc_course_practice_pass" min="0" max="100" value="<?php echo esc_attr( $cp_settings['pass'] ); ?>" style="width:70px">
		<span class="description"><?php esc_html_e( '(خالی = پیش‌فرض قالب)', 'zarincode' ); ?></span>
	</div>
	<?php zc_render_questions_editor( $post, '_zc_course_practice', 'zcqp_q' ); ?>
	<?php
}

/**
 * ذخیره متاباکس آزمون دوره.
 *
 * @param int $post_id پست.
 * @return void
 */
function zc_quiz_save( $post_id ) {
	if ( ! isset( $_POST['zc_quiz_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['zc_quiz_nonce'] ), 'zc_quiz_save' ) ) { // phpcs:ignore
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$pass = isset( $_POST['zc_quiz_pass'] ) ? (float) wp_unslash( $_POST['zc_quiz_pass'] ) : 0; // phpcs:ignore
	$max  = isset( $_POST['zc_quiz_attempts'] ) ? (int) wp_unslash( $_POST['zc_quiz_attempts'] ) : 0; // phpcs:ignore
	update_post_meta( $post_id, '_zc_quiz_pass', $pass > 0 ? $pass : '' );
	update_post_meta( $post_id, '_zc_quiz_attempts', $max > 0 ? $max : '' );

	// زبان‌های مجاز این دوره.
	$langs = isset( $_POST['zc_quiz_langs'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['zc_quiz_langs'] ) ) : array(); // phpcs:ignore
	update_post_meta( $post_id, '_zc_quiz_langs', array_values( array_filter( $langs ) ) );

	// حد نصاب تمرین چالشی این دوره.
	$cp_pass = isset( $_POST['zc_course_practice_pass'] ) ? (float) wp_unslash( $_POST['zc_course_practice_pass'] ) : 0; // phpcs:ignore
	update_post_meta( $post_id, '_zc_course_practice_pass', $cp_pass > 0 ? $cp_pass : '' );

	zc_save_questions_meta( $post_id, 'zcq_q', '_zc_quiz', 'zc_quiz_nonce', 'zc_quiz_save' );
	zc_save_questions_meta( $post_id, 'zcqp_q', '_zc_course_practice', 'zc_quiz_nonce', 'zc_quiz_save' );
}
add_action( 'save_post_zc_course', 'zc_quiz_save' );

/* ==========================================================================
   رندر آزمون
   ========================================================================== */

/**
 * متغیرهای CSS برای شخصی‌سازی ویرایشگر کد (موضوع، اندازهٔ فونت و ...).
 * روی کانتینر آزمون/تمرین به‌صورت inline اعمال می‌شود و در main.css خوانده می‌شود.
 *
 * @return string
 */
function zc_compiler_style_attrs() {
	$theme    = zc_opt( 'zc_quiz_exec_theme', 'dark' );
	$fontsize = max( 11, (int) zc_opt( 'zc_quiz_exec_fontsize', 14 ) );

	if ( 'light' === $theme ) {
		$bg   = '#ffffff';
		$text = '#1f2937';
		$head = '#f1f5f9';
		$sub  = '#6b7280';
		$out  = '#0b7a3b';
	} else {
		$bg   = '#0f1428';
		$text = '#e6edf7';
		$head = '#141a31';
		$sub  = '#8b93a7';
		$out  = '#7ee787';
	}

	return sprintf(
		'--zc-ebg:%s;--zc-etxt:%s;--zc-ehead:%s;--zc-esub:%s;--zc-eout:%s;--zc-efs:%dpx;',
		esc_attr( $bg ),
		esc_attr( $text ),
		esc_attr( $head ),
		esc_attr( $sub ),
		esc_attr( $out ),
		(int) $fontsize
	);
}

/**
 * رندر آزمون در صفحه دوره.
 *
 * @param int $course_id دوره.
 * @return void
 */
function zc_quiz_render( $course_id ) {
	if ( ! zc_quiz_module_enabled() || ! zc_quiz_enabled() ) {
		return;
	}

	$questions = zc_quiz_questions( $course_id );
	if ( empty( $questions ) ) {
		return;
	}

	$user_id      = get_current_user_id();
	$has          = $user_id && zc_user_has_course( $user_id, $course_id );
	$passed       = $user_id && zc_quiz_passed( $user_id, $course_id );
	$best         = $user_id ? zc_quiz_best_score( $user_id, $course_id ) : 0;
	$settings     = zc_quiz_course_settings( $course_id );
	$attempts     = $user_id ? zc_quiz_user_attempts( $user_id, $course_id ) : array();
	$remaining    = $user_id ? zc_quiz_remaining_attempts( $user_id, $course_id ) : 0;
	$total        = count( $questions );
	$challenge    = zc_opt( 'zc_quiz_challenge', true );
	$course_langs = zc_quiz_course_languages( $course_id );
	?>
	<div class="zc-quiz zc-quiz--ext" data-quiz data-type="course" data-id="<?php echo esc_attr( $course_id ); ?>" data-qcount="<?php echo (int) $total; ?>" data-pass="<?php echo esc_attr( $settings['pass'] ); ?>" data-challenge="<?php echo $challenge ? '1' : '0'; ?>" data-autorun="<?php echo zc_opt( 'zc_quiz_exec_autorun', false ) ? '1' : '0'; ?>" style="<?php echo esc_attr( zc_compiler_style_attrs() ); ?>">
		<div class="zc-quiz__head">
			<h3 style="margin:0 0 6px"><?php esc_html_e( 'آزمون پایان دوره', 'zarincode' ); ?></h3>
			<p style="margin:0;color:var(--zc-muted);font-size:.85rem">
				<?php echo esc_html( zc_fa_num( $total ) ); ?> <?php esc_html_e( 'سوال', 'zarincode' ); ?> — <?php esc_html_e( 'نمره قبولی:', 'zarincode' ); ?> <?php echo esc_html( zc_fa_num( $settings['pass'] ) ); ?>٪
			</p>
		</div>

		<?php if ( $passed ) : ?>
			<div class="zc-alert zc-alert--success" style="margin:14px 0">
				<?php echo zc_icon( 'check', 18 ); // phpcs:ignore ?><span><?php esc_html_e( 'تبریک! شما در این آزمون قبول شده‌اید.', 'zarincode' ); ?> (<?php echo esc_html( zc_fa_num( $best ) ); ?>٪)</span>
			</div>
		<?php endif; ?>

		<?php if ( ! $user_id ) : ?>
			<p class="zc-quiz__notice"><?php esc_html_e( 'برای شرکت در آزمون وارد شوید.', 'zarincode' ); ?> <a href="<?php echo esc_url( zc_login_url() ); ?>"><?php esc_html_e( 'ورود / ثبت‌نام', 'zarincode' ); ?></a></p>
		<?php elseif ( ! $has ) : ?>
			<p class="zc-quiz__notice"><?php esc_html_e( 'برای شرکت در آزمون، ابتدا در دوره ثبت‌نام کنید.', 'zarincode' ); ?></p>
		<?php elseif ( $remaining <= 0 && ! $passed ) : ?>
			<p class="zc-quiz__notice" style="color:var(--zc-danger)"><?php esc_html_e( 'به حداکثر تعداد تلاش رسیده‌اید.', 'zarincode' ); ?></p>
		<?php else : ?>
			<div class="zc-quiz__info" style="margin:10px 0;font-size:.85rem;color:var(--zc-muted)">
				<?php echo esc_html( zc_fa_num( $remaining ) ); ?> <?php esc_html_e( 'تلاش باقی‌مانده', 'zarincode' ); ?>
				<?php if ( $attempts ) : ?>
					— <?php esc_html_e( 'آخرین نمره:', 'zarincode' ); ?> <?php echo esc_html( zc_fa_num( end( $attempts )['score'] ) ); ?>٪
				<?php endif; ?>
			</div>

			<?php if ( $total > 1 ) : ?>
				<div class="zc-quiz__modebar">
					<button type="button" class="zc-quiz__mode is-active" data-mode="challenge"><?php esc_html_e( 'گام‌به‌گام (چالشی)', 'zarincode' ); ?></button>
					<button type="button" class="zc-quiz__mode" data-mode="all"><?php esc_html_e( 'همهٔ سوالات', 'zarincode' ); ?></button>
				</div>
			<?php endif; ?>

			<!-- حالت گام‌به‌گام -->
			<div class="zc-challenge"<?php echo ( $challenge && $total > 1 ) ? '' : ' hidden'; ?>>
				<div class="zc-challenge__progress">
					<span class="zc-challenge__pbar"><i data-width="0"></i></span>
					<span class="zc-challenge__ptext">0/<?php echo esc_html( zc_fa_num( $total ) ); ?></span>
				</div>
				<div class="zc-challenge__stage">
					<?php echo zc_quiz_question_html( $questions[0], 0, 'challenge', $course_langs ); // phpcs:ignore ?>
				</div>
				<div class="zc-challenge__msg"></div>
			</div>

			<!-- حالت همهٔ سوالات -->
			<div class="zc-quiz__all"<?php echo ( $challenge && $total > 1 ) ? ' hidden' : ''; ?>>
				<?php foreach ( $questions as $qi => $q ) : ?>
					<?php echo zc_quiz_question_html( $q, $qi, 'all', $course_langs ); // phpcs:ignore ?>
				<?php endforeach; ?>
				<div class="zc-quiz__msg" style="margin-top:10px"></div>
				<button type="button" class="zc-btn zc-btn--gold zc-btn--block" data-zc-quiz-submit><?php zc_the_icon( 'check', 17 ); ?><?php esc_html_e( 'ثبت پاسخ‌ها', 'zarincode' ); ?></button>
			</div>

		<?php endif; ?>
	</div>
	<?php
}

/**
 * رندر «تمرین چالشی» یک دوره در صفحهٔ دوره (تب جداگانه از آزمون).
 *
 * @param int $course_id دوره.
 * @return void
 */
function zc_course_practice_render( $course_id ) {
	if ( ! zc_quiz_module_enabled() ) {
		return;
	}

	$questions = zc_course_practice_questions( $course_id );
	if ( empty( $questions ) ) {
		return;
	}

	$user_id  = get_current_user_id();
	$total    = count( $questions );
	$settings = zc_course_practice_settings( $course_id );
	$best     = $user_id ? zc_course_practice_best( $user_id, $course_id ) : 0;
	$langs    = zc_quiz_course_languages( $course_id );
	?>
	<div class="zc-quiz zc-quiz--ext zc-quiz--cp" data-quiz data-type="course_practice" data-id="<?php echo esc_attr( $course_id ); ?>" data-qcount="<?php echo (int) $total; ?>" data-pass="<?php echo esc_attr( $settings['pass'] ); ?>" data-challenge="1" data-autorun="<?php echo zc_opt( 'zc_quiz_exec_autorun', false ) ? '1' : '0'; ?>" style="<?php echo esc_attr( zc_compiler_style_attrs() ); ?>">
		<div class="zc-quiz__head">
			<h3 style="margin:0 0 6px"><?php esc_html_e( 'تمرین چالشی دوره', 'zarincode' ); ?></h3>
			<p style="margin:0;color:var(--zc-muted);font-size:.85rem">
				<?php echo esc_html( zc_fa_num( $total ) ); ?> <?php esc_html_e( 'سوال', 'zarincode' ); ?> — <?php esc_html_e( 'حد نصاب:', 'zarincode' ); ?> <?php echo esc_html( zc_fa_num( $settings['pass'] ) ); ?>٪
				<?php if ( $best ) : ?> — <?php esc_html_e( 'بهترین نمره:', 'zarincode' ); ?> <?php echo esc_html( zc_fa_num( $best ) ); ?>٪<?php endif; ?>
			</p>
		</div>

		<?php if ( ! $user_id ) : ?>
			<p class="zc-quiz__notice"><?php esc_html_e( 'برای شرکت در تمرین وارد شوید.', 'zarincode' ); ?> <a href="<?php echo esc_url( zc_login_url() ); ?>"><?php esc_html_e( 'ورود / ثبت‌نام', 'zarincode' ); ?></a></p>
		<?php elseif ( ! zc_user_has_course( $user_id, $course_id ) ) : ?>
			<p class="zc-quiz__notice"><?php esc_html_e( 'برای انجام تمرین، ابتدا در دوره ثبت‌نام کنید.', 'zarincode' ); ?></p>
		<?php else : ?>
			<div class="zc-challenge">
				<div class="zc-challenge__progress">
					<span class="zc-challenge__pbar"><i data-width="0"></i></span>
					<span class="zc-challenge__ptext">0/<?php echo esc_html( zc_fa_num( $total ) ); ?></span>
				</div>
				<div class="zc-challenge__stage">
					<?php echo zc_quiz_question_html( $questions[0], 0, 'challenge', $langs ); // phpcs:ignore ?>
				</div>
				<div class="zc-challenge__msg"></div>
			</div>
		<?php endif; ?>
	</div>
	<?php
}
