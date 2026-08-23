<?php
/**
 * تب «تمرین کدنویسی» پنل کاربری
 * ---------------------------------------------------------------------------
 * فهرست تمرین‌ها را نشان می‌دهد و با انتخاب هر تمرین، آزمونِ گام‌به‌گام
 * (هر پاسخ درست → سوال بعدی) را به‌صورت درون‌صفحه‌ای اجرا می‌کند.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

if ( ! zc_practice_enabled() ) {
	return;
}

$zc_pid = isset( $_GET['practice'] ) ? absint( $_GET['practice'] ) : 0; // phpcs:ignore

if ( $zc_pid && 'publish' === get_post_status( $zc_pid ) && 'zc_practice' === get_post_type( $zc_pid ) ) {
	$zc_practice_questions = zc_practice_questions( $zc_pid );
	if ( ! empty( $zc_practice_questions ) ) :
		$zc_total     = count( $zc_practice_questions );
		$zc_best      = (float) get_user_meta( get_current_user_id(), 'zc_practice_' . $zc_pid, true );
			$zc_settings  = zc_practice_settings( $zc_pid );
			$zc_plangs    = zc_practice_languages( $zc_pid );
			$zc_attempt   = zc_quiz_attempt_create( 'practice', $zc_pid, $zc_practice_questions );
			?>
		<div class="zc-panel__box" style="margin-bottom:16px">
			<div class="zc-panel__box-head">
				<h3><?php zc_the_icon( 'code', 19 ); ?><?php echo esc_html( get_the_title( $zc_pid ) ); ?></h3>
				<a href="<?php echo esc_url( zc_panel_url( 'practice' ) ); ?>" class="zc-panel__box-link"><?php esc_html_e( 'فهرست تمرین‌ها', 'zarincode' ); ?></a>
			</div>
			<div class="zc-panel__box-body">
				<p style="color:var(--zc-muted);font-size:.85rem;margin:0">
					<?php echo esc_html( zc_fa_num( $zc_total ) ); ?> <?php esc_html_e( 'سوال', 'zarincode' ); ?> — <?php esc_html_e( 'حد نصاب:', 'zarincode' ); ?> <?php echo esc_html( zc_fa_num( $zc_settings['pass'] ) ); ?>٪
					<?php if ( $zc_best ) : ?> — <?php esc_html_e( 'بهترین نمره:', 'zarincode' ); ?> <?php echo esc_html( zc_fa_num( $zc_best ) ); ?>٪<?php endif; ?>
				</p>

				<div class="zc-quiz zc-quiz--ext zc-quiz--practice" data-quiz data-type="practice" data-id="<?php echo esc_attr( $zc_pid ); ?>" data-attempt="<?php echo esc_attr( $zc_attempt ); ?>" data-qcount="<?php echo (int) $zc_total; ?>" data-pass="<?php echo esc_attr( $zc_settings['pass'] ); ?>" data-challenge="1" data-autorun="<?php echo zc_opt( 'zc_quiz_exec_autorun', false ) ? '1' : '0'; ?>" style="<?php echo esc_attr( zc_compiler_style_attrs() ); ?>">
					<div class="zc-challenge">
						<div class="zc-challenge__progress">
							<span class="zc-challenge__pbar"><i data-width="0"></i></span>
							<span class="zc-challenge__ptext">0/<?php echo esc_html( zc_fa_num( $zc_total ) ); ?></span>
						</div>
						<div class="zc-challenge__stage">
							<?php echo zc_quiz_question_html( $zc_practice_questions[0], 0, 'challenge', $zc_plangs ); // phpcs:ignore ?>
						</div>
						<div class="zc-challenge__msg"></div>
					</div>
				</div>
			</div>
		</div>
		<?php
		return;
	endif;
}

/* ---------- فهرست تمرین‌ها ---------- */

$zc_query = new WP_Query(
	array(
		'post_type'      => 'zc_practice',
		'post_status'    => 'publish',
		'posts_per_page' => 24,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);
?>

<div class="zc-panel__welcome" data-zc-anim="up">
	<div>
		<h2><?php esc_html_e( 'تمرین کدنویسی 💻', 'zarincode' ); ?></h2>
		<p><?php esc_html_e( 'با حل تمرین‌ها، مهارت‌تان را گام‌به‌گام بسنجید. هر پاسخ درست، سوال بعدی را باز می‌کند.', 'zarincode' ); ?></p>
	</div>
</div>

<?php if ( ! $zc_query->have_posts() ) : ?>
	<div class="zc-panel__box">
		<div class="zc-empty" style="padding:40px 10px">
			<div class="zc-empty__icon" style="width:72px;height:72px"><?php zc_the_icon( 'code', 32 ); ?></div>
			<p><?php esc_html_e( 'هنوز تمرینی ثبت نشده است. به‌زودی تمرین‌های جدید اضافه می‌شود.', 'zarincode' ); ?></p>
		</div>
	</div>
<?php else : ?>
	<div class="zc-panel__grid-2">
		<?php
		while ( $zc_query->have_posts() ) :
			$zc_query->the_post();
			$zc_pid      = get_the_ID();
			$zc_qn       = zc_practice_question_count( $zc_pid );
			$zc_best     = (float) get_user_meta( get_current_user_id(), 'zc_practice_' . $zc_pid, true );
			$zc_done     = $zc_best >= 70;
			?>
			<div class="zc-panel__box zc-practice-card" data-zc-anim="up">
				<div class="zc-practice-card__head">
					<span class="zc-practice-card__icon"><?php zc_the_icon( 'code', 22 ); ?></span>
					<h3 style="margin:0 0 4px"><?php echo esc_html( get_the_title() ); ?></h3>
					<p style="margin:0;color:var(--zc-muted);font-size:.82rem"><?php echo esc_html( zc_fa_num( $zc_qn ) ); ?> <?php esc_html_e( 'سوال', 'zarincode' ); ?></p>
				</div>
				<div class="zc-practice-card__foot" style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:14px;flex-wrap:wrap">
					<span class="zc-practice-card__score">
						<?php if ( $zc_done ) : ?>
							<span class="zc-tag zc-tag--success"><?php esc_html_e( 'تکمیل شده', 'zarincode' ); ?></span>
						<?php elseif ( $zc_best ) : ?>
							<span class="zc-tag"><?php echo esc_html( zc_fa_num( $zc_best ) ); ?>٪</span>
						<?php else : ?>
							<span class="zc-tag zc-tag--muted"><?php esc_html_e( 'شروع نشده', 'zarincode' ); ?></span>
						<?php endif; ?>
					</span>
					<a href="<?php echo esc_url( add_query_arg( 'practice', $zc_pid, zc_panel_url( 'practice' ) ) ); ?>" class="zc-btn zc-btn--gold zc-btn--sm">
						<?php $zc_done ? esc_html_e( 'دوباره تمرین', 'zarincode' ) : esc_html_e( 'شروع تمرین', 'zarincode' ); ?>
					</a>
				</div>
			</div>
		<?php endwhile; ?>
	</div>
	<?php wp_reset_postdata(); ?>
<?php endif; ?>
