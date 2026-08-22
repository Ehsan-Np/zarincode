<?php
/**
 * ماژول گزارش تلاش‌های آزمون و تمرین زرین کد
 * ---------------------------------------------------------------------------
 * هر تلاش کاربر در آزمون‌ها، تمرین چالشی دوره‌ها و تمرین‌های پنل کاربری در
 * یک جدول مرکزی ثبت می‌شود تا مدیر بتواند گزارش‌های متنوع و جزئی بگیرد:
 *  - آمار کل (تعداد تلاش، کاربران، نرخ قبولی، میانگین نمره)
 *  - فیلتر بر اساس نوع (آزمون / تمرین چالشی دوره / تمرین پنل)، دوره/تمرین،
 *    بازهٔ زمانی، کاربر و وضعیت قبولی.
 *  - جزئیات هر تلاش + خلاصهٔ «بهترین نمرهٔ هر کاربر» برای هر دوره/تمرین.
 *  - خروجی CSV.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * نام جدول گزارش تلاش‌ها.
 *
 * @return string
 */
function zc_attempts_table() {
	global $wpdb;
	return $wpdb->prefix . 'zc_attempts';
}

/**
 * ایجاد جدول گزارش در صورت نبودش.
 *
 * @return void
 */
function zc_ensure_attempts_table() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	global $wpdb;
	$table = zc_attempts_table();
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ); // phpcs:ignore
	if ( $exists ) {
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$charset = $wpdb->get_charset_collate();
	$sql     = "CREATE TABLE {$table} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		type VARCHAR(20) NOT NULL DEFAULT 'quiz',
		ref_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		score FLOAT NOT NULL DEFAULT 0,
		correct INT UNSIGNED NOT NULL DEFAULT 0,
		total INT UNSIGNED NOT NULL DEFAULT 0,
		passed TINYINT(1) NOT NULL DEFAULT 0,
		mode VARCHAR(20) NOT NULL DEFAULT 'challenge',
		created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY (id),
		KEY user_id (user_id),
		KEY ref_id (ref_id),
		KEY type (type),
		KEY passed (passed),
		KEY created_at (created_at)
	) {$charset};";

	dbDelta( $sql );
}

/**
 * ثبت یک تلاش در جدول گزارش.
 *
 * @param int    $user_id کاربر.
 * @param string $type    نوع: course | course_practice | practice.
 * @param int    $ref_id  شناسهٔ دوره یا تمرین.
 * @param float  $score   نمره (۰-۱۰۰).
 * @param int    $correct تعداد پاسخ درست.
 * @param int    $total   تعداد کل.
 * @param bool   $passed  قبول/رد.
 * @param string $mode    حالت: challenge | all.
 * @return void
 */
function zc_log_attempt( $user_id, $type, $ref_id, $score, $correct, $total, $passed, $mode = 'challenge' ) {
	zc_ensure_attempts_table();

	global $wpdb;
	$wpdb->insert( // phpcs:ignore
		zc_attempts_table(),
		array(
			'user_id'    => (int) $user_id,
			'type'       => sanitize_key( $type ),
			'ref_id'     => (int) $ref_id,
			'score'      => (float) $score,
			'correct'    => (int) $correct,
			'total'      => (int) $total,
			'passed'     => $passed ? 1 : 0,
			'mode'       => sanitize_key( $mode ),
			'created_at' => current_time( 'mysql' ),
		),
		array( '%d', '%s', '%d', '%f', '%d', '%d', '%d', '%s', '%s' )
	);
}

/**
 * برچسب فارسی نوع.
 *
 * @param string $type نوع.
 * @return string
 */
function zc_attempts_type_label( $type ) {
	$map = array(
		'course'          => __( 'آزمون دوره', 'zarincode' ),
		'course_practice' => __( 'تمرین چالشی دوره', 'zarincode' ),
		'practice'        => __( 'تمرین پنل', 'zarincode' ),
	);
	return isset( $map[ $type ] ) ? $map[ $type ] : $type;
}

/**
 * عنوان دوره/تمرین مرتبط با یک تلاش.
 *
 * @param string $type   نوع.
 * @param int    $ref_id شناسه.
 * @return string
 */
function zc_attempts_ref_title( $type, $ref_id ) {
	if ( 'practice' === $type ) {
		return get_the_title( $ref_id );
	}
	return get_the_title( $ref_id );
}

/**
 * استخراج آرگومان‌های فیلتر از درخواست GET.
 *
 * @return array
 */
function zc_attempts_filters() {
	return array(
		'type'   => isset( $_GET['zatype'] ) ? sanitize_key( wp_unslash( $_GET['zatype'] ) ) : '', // phpcs:ignore
		'ref'    => isset( $_GET['zaref'] ) ? absint( $_GET['zaref'] ) : 0, // phpcs:ignore
		'from'   => isset( $_GET['zafrom'] ) ? sanitize_text_field( wp_unslash( $_GET['zafrom'] ) ) : '', // phpcs:ignore
		'to'     => isset( $_GET['zato'] ) ? sanitize_text_field( wp_unslash( $_GET['zato'] ) ) : '', // phpcs:ignore
		'user'   => isset( $_GET['zauser'] ) ? sanitize_text_field( wp_unslash( $_GET['zauser'] ) ) : '', // phpcs:ignore
		'status' => isset( $_GET['zastatus'] ) ? sanitize_key( wp_unslash( $_GET['zastatus'] ) ) : '', // phpcs:ignore
	);
}

/**
 * کوئری تلاش‌ها با فیلتر.
 *
 * @param array $filters فیلترها.
 * @return array
 */
function zc_attempts_query( $filters = array() ) {
	zc_ensure_attempts_table();
	global $wpdb;
	$table = zc_attempts_table();

	$where  = array( '1=1' );
	$params = array();

	if ( ! empty( $filters['type'] ) ) {
		$where[]  = 'type = %s';
		$params[] = $filters['type'];
	}
	if ( ! empty( $filters['ref'] ) ) {
		$where[]  = 'ref_id = %d';
		$params[] = (int) $filters['ref'];
	}
	if ( ! empty( $filters['status'] ) && in_array( $filters['status'], array( 'passed', 'failed' ), true ) ) {
		$where[]  = 'passed = %d';
		$params[] = 'passed' === $filters['status'] ? 1 : 0;
	}
	if ( ! empty( $filters['from'] ) ) {
		$where[]  = 'created_at >= %s';
		$params[] = $filters['from'] . ' 00:00:00';
	}
	if ( ! empty( $filters['to'] ) ) {
		$where[]  = 'created_at <= %s';
		$params[] = $filters['to'] . ' 23:59:59';
	}
	if ( ! empty( $filters['user'] ) ) {
		$ids   = zc_attempts_search_user_ids( $filters['user'] );
		$where[] = 'user_id IN (' . implode( ',', array_map( 'intval', $ids ) ) . ')';
	}

	$sql = "SELECT * FROM {$table} WHERE " . implode( ' AND ', $where ) . ' ORDER BY id DESC';

	if ( $params ) {
		$sql = $wpdb->prepare( $sql, $params ); // phpcs:ignore
	}

	return $wpdb->get_results( $sql ); // phpcs:ignore
}

/**
 * جستجوی شناسهٔ کاربر بر اساس نام کاربری/ایمیل/نام.
 *
 * @param string $q عبارت جستجو.
 * @return array
 */
function zc_attempts_search_user_ids( $q ) {
	$q    = trim( $q );
	$ids  = array( 0 );
	$args = array(
		'number'     => 200,
		'search'     => '*' . $q . '*',
		'search_columns' => array( 'user_login', 'user_email', 'display_name' ),
		'fields'     => 'ID',
	);
	$users = get_users( $args );
	foreach ( (array) $users as $u ) {
		$ids[] = (int) $u;
	}
	return array_unique( $ids );
}

/**
 * آمار خلاصه از لیست تلاش‌ها.
 *
 * @param array $rows ردیف‌ها.
 * @return array
 */
function zc_attempts_summary( $rows ) {
	$users = array();
	$sum   = 0;
	$passed = 0;
	foreach ( (array) $rows as $r ) {
		$users[ (int) $r->user_id ] = 1;
		$sum   += (float) $r->score;
		if ( (int) $r->passed ) {
			$passed++;
		}
	}
	$n = count( $rows );
	return array(
		'attempts' => $n,
		'users'    => count( $users ),
		'passed'   => $passed,
		'rate'     => $n ? round( ( $passed / $n ) * 100, 1 ) : 0,
		'avg'      => $n ? round( $sum / $n, 1 ) : 0,
	);
}

/**
 * ثبت صفحهٔ گزارش در زیرمنوی زرین کد.
 *
 * @return void
 */
function zc_attempts_menu() {
	add_submenu_page(
		'zarincode',
		__( 'گزارش آزمون و تمرین', 'zarincode' ),
		__( 'گزارش آزمون و تمرین', 'zarincode' ),
		'manage_options',
		'zarincode-quiz-report',
		'zc_attempts_report_page'
	);
}
add_action( 'admin_menu', 'zc_attempts_menu', 20 );

/**
 * افزودن تب «سوابق تلاش‌ها» به پنل کاربری.
 *
 * @param array $tabs تب‌ها.
 * @return array
 */
function zc_attempts_panel_tab( $tabs ) {
	if ( ! zc_quiz_module_enabled() ) {
		return $tabs;
	}
	$tabs['attempts'] = array(
		'label' => __( 'سوابق تلاش‌ها', 'zarincode' ),
		'icon'  => 'chart',
		'order' => 56,
	);
	return $tabs;
}
add_filter( 'zc_panel_tabs', 'zc_attempts_panel_tab' );

/**
 * خروجی CSV گزارش.
 *
 * @return void
 */
function zc_attempts_csv() {
	if ( empty( $_GET['zacsv'] ) || ! current_user_can( 'manage_options' ) ) { // phpcs:ignore
		return;
	}
	$filters = zc_attempts_filters();
	$rows    = zc_attempts_query( $filters );

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="zarincode-attempts-' . gmdate( 'Ymd-His' ) . '.csv"' );

	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" ); // BOM برای اکسل.
	fputcsv( $out, array( 'کاربر', 'نوع', 'دوره/تمرین', 'نمره', 'درست', 'کل', 'وضعیت', 'حالت', 'تاریخ' ) );
	foreach ( (array) $rows as $r ) {
		$u = get_userdata( $r->user_id );
		fputcsv(
			$out,
			array(
				$u ? $u->display_name . ' (' . $u->user_login . ')' : $r->user_id,
				zc_attempts_type_label( $r->type ),
				zc_attempts_ref_title( $r->type, $r->ref_id ),
				$r->score,
				$r->correct,
				$r->total,
				$r->passed ? 'قبول' : 'رد',
				$r->mode,
				$r->created_at,
			)
		);
	}
	fclose( $out );
	exit;
}
add_action( 'admin_init', 'zc_attempts_csv' );

/**
 * بهترین نمرهٔ هر کاربر برای یک دوره/تمرین (از جدول گزارش).
 *
 * @param string $type   نوع.
 * @param int    $ref_id شناسه.
 * @return array user_id => best score.
 */
function zc_attempts_best_per_user( $type, $ref_id ) {
	zc_ensure_attempts_table();
	global $wpdb;
	$table = zc_attempts_table();
	$rows  = $wpdb->get_results( // phpcs:ignore
		$wpdb->prepare( // phpcs:ignore
			"SELECT user_id, MAX(score) AS best, COUNT(*) AS attempts,
				MAX(CASE WHEN passed = 1 THEN 1 ELSE 0 END) AS ever_passed
			 FROM {$table} WHERE type = %s AND ref_id = %d GROUP BY user_id ORDER BY best DESC",
			$type,
			$ref_id
		)
	);
	return $rows;
}

/**
 * رندر صفحهٔ گزارش.
 *
 * @return void
 */
function zc_attempts_report_page() {
	$filters = zc_attempts_filters();
	$rows    = zc_attempts_query( $filters );
	$summary = zc_attempts_summary( $rows );

	// فهرست دوره‌ها و تمرین‌ها برای فیلتر.
	$courses = get_posts( array( 'post_type' => 'zc_course', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
	$practices = get_posts( array( 'post_type' => 'zc_practice', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );

	// آمار «بهترین نمرهٔ هر کاربر» اگر دوره یا تمرین مشخص انتخاب شده باشد.
	$best_rows = array();
	if ( $filters['ref'] && in_array( $filters['type'], array( 'course', 'course_practice', 'practice' ), true ) ) {
		$best_rows = zc_attempts_best_per_user( $filters['type'], $filters['ref'] );
	}

	$csv_url = add_query_arg( 'zacsv', '1', 'admin.php?page=zarincode-quiz-report' );
	foreach ( $filters as $k => $v ) {
		if ( '' !== $v ) {
			$csv_url = add_query_arg( 'za' . $k, $v, $csv_url );
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'گزارش آزمون و تمرین', 'zarincode' ); ?></h1>

		<form method="get" class="zc-admin-box" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;margin-bottom:18px">
			<input type="hidden" name="page" value="zarincode-quiz-report">
			<div>
				<label style="font-size:.75rem"><?php esc_html_e( 'نوع', 'zarincode' ); ?></label>
				<select name="zatype">
					<option value=""><?php esc_html_e( 'همه', 'zarincode' ); ?></option>
					<option value="course" <?php selected( $filters['type'], 'course' ); ?>><?php esc_html_e( 'آزمون دوره', 'zarincode' ); ?></option>
					<option value="course_practice" <?php selected( $filters['type'], 'course_practice' ); ?>><?php esc_html_e( 'تمرین چالشی دوره', 'zarincode' ); ?></option>
					<option value="practice" <?php selected( $filters['type'], 'practice' ); ?>><?php esc_html_e( 'تمرین پنل', 'zarincode' ); ?></option>
				</select>
			</div>
			<div>
				<label style="font-size:.75rem"><?php esc_html_e( 'دوره / تمرین', 'zarincode' ); ?></label>
				<select name="zaref">
					<option value=""><?php esc_html_e( 'همه', 'zarincode' ); ?></option>
					<optgroup label="<?php esc_attr_e( 'دوره‌ها', 'zarincode' ); ?>">
						<?php foreach ( $courses as $c ) : ?>
							<option value="<?php echo (int) $c->ID; ?>" <?php selected( $filters['ref'], $c->ID ); ?>><?php echo esc_html( $c->post_title ); ?></option>
						<?php endforeach; ?>
					</optgroup>
					<optgroup label="<?php esc_attr_e( 'تمرین‌های پنل', 'zarincode' ); ?>">
						<?php foreach ( $practices as $p ) : ?>
							<option value="<?php echo (int) $p->ID; ?>" <?php selected( $filters['ref'], $p->ID ); ?>><?php echo esc_html( $p->post_title ); ?></option>
						<?php endforeach; ?>
					</optgroup>
				</select>
			</div>
			<div>
				<label style="font-size:.75rem"><?php esc_html_e( 'از تاریخ', 'zarincode' ); ?></label>
				<input type="date" name="zafrom" value="<?php echo esc_attr( $filters['from'] ); ?>">
			</div>
			<div>
				<label style="font-size:.75rem"><?php esc_html_e( 'تا تاریخ', 'zarincode' ); ?></label>
				<input type="date" name="zato" value="<?php echo esc_attr( $filters['to'] ); ?>">
			</div>
			<div>
				<label style="font-size:.75rem"><?php esc_html_e( 'کاربر', 'zarincode' ); ?></label>
				<input type="text" name="zauser" value="<?php echo esc_attr( $filters['user'] ); ?>" placeholder="<?php esc_attr_e( 'نام کاربری / ایمیل', 'zarincode' ); ?>">
			</div>
			<div>
				<label style="font-size:.75rem"><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></label>
				<select name="zastatus">
					<option value=""><?php esc_html_e( 'همه', 'zarincode' ); ?></option>
					<option value="passed" <?php selected( $filters['status'], 'passed' ); ?>><?php esc_html_e( 'قبول', 'zarincode' ); ?></option>
					<option value="failed" <?php selected( $filters['status'], 'failed' ); ?>><?php esc_html_e( 'رد', 'zarincode' ); ?></option>
				</select>
			</div>
			<div>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'فیلتر', 'zarincode' ); ?></button>
				<a href="<?php echo esc_url( $csv_url ); ?>" class="button"><?php esc_html_e( 'خروجی CSV', 'zarincode' ); ?></a>
			</div>
		</form>

		<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:20px">
			<div class="zc-admin-stat"><div><strong><?php echo esc_html( zc_fa_num( number_format( $summary['attempts'] ) ) ); ?></strong><span><?php esc_html_e( 'تعداد تلاش', 'zarincode' ); ?></span></div></div>
			<div class="zc-admin-stat"><div><strong><?php echo esc_html( zc_fa_num( number_format( $summary['users'] ) ) ); ?></strong><span><?php esc_html_e( 'کاربران', 'zarincode' ); ?></span></div></div>
			<div class="zc-admin-stat"><div><strong><?php echo esc_html( zc_fa_num( number_format( $summary['passed'] ) ) ); ?></strong><span><?php esc_html_e( 'تلاش قبول', 'zarincode' ); ?></span></div></div>
			<div class="zc-admin-stat"><div><strong><?php echo esc_html( zc_fa_num( $summary['rate'] ) ); ?>٪</strong><span><?php esc_html_e( 'نرخ قبولی', 'zarincode' ); ?></span></div></div>
			<div class="zc-admin-stat"><div><strong><?php echo esc_html( zc_fa_num( $summary['avg'] ) ); ?></strong><span><?php esc_html_e( 'میانگین نمره', 'zarincode' ); ?></span></div></div>
		</div>

		<?php if ( $best_rows ) : ?>
			<h2><?php esc_html_e( 'بهترین نمرهٔ هر کاربر', 'zarincode' ); ?></h2>
			<table class="widefat striped" style="max-width:820px">
				<thead><tr><th><?php esc_html_e( 'کاربر', 'zarincode' ); ?></th><th><?php esc_html_e( 'بهترین نمره', 'zarincode' ); ?></th><th><?php esc_html_e( 'تعداد تلاش', 'zarincode' ); ?></th><th><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $best_rows as $b ) : $u = get_userdata( $b->user_id ); ?>
					<tr>
						<td><?php echo esc_html( $u ? $u->display_name . ' (' . $u->user_login . ')' : '#' . $b->user_id ); ?></td>
						<td><strong><?php echo esc_html( zc_fa_num( $b->best ) ); ?>٪</strong></td>
						<td><?php echo esc_html( zc_fa_num( $b->attempts ) ); ?></td>
						<td><?php echo $b->ever_passed ? '<span style="color:#16A34A;font-weight:700">✓ قبول</span>' : '<span style="color:#DC2626">✗ رد</span>'; ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<h2 style="margin-top:24px"><?php esc_html_e( 'جزئیات تلاش‌ها', 'zarincode' ); ?></h2>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'کاربر', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'نوع', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'دوره / تمرین', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'نمره', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'درست / کل', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'حالت', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'تاریخ', 'zarincode' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( ! $rows ) : ?>
				<tr><td colspan="8"><?php esc_html_e( 'تلاشی یافت نشد.', 'zarincode' ); ?></td></tr>
			<?php else : foreach ( $rows as $r ) : $u = get_userdata( $r->user_id ); ?>
				<tr>
					<td><?php echo esc_html( $u ? $u->display_name . ' (' . $u->user_login . ')' : '#' . $r->user_id ); ?></td>
					<td><?php echo esc_html( zc_attempts_type_label( $r->type ) ); ?></td>
					<td><?php echo esc_html( zc_attempts_ref_title( $r->type, $r->ref_id ) ); ?></td>
					<td><strong><?php echo esc_html( zc_fa_num( $r->score ) ); ?>٪</strong></td>
					<td><?php echo esc_html( zc_fa_num( $r->correct ) ); ?> / <?php echo esc_html( zc_fa_num( $r->total ) ); ?></td>
					<td><?php echo $r->passed ? '<span style="color:#16A34A;font-weight:700">✓ ' . esc_html__( 'قبول', 'zarincode' ) . '</span>' : '<span style="color:#DC2626">✗ ' . esc_html__( 'رد', 'zarincode' ) . '</span>'; ?></td>
					<td><?php echo 'all' === $r->mode ? esc_html__( 'همهٔ سوالات', 'zarincode' ) : esc_html__( 'گام‌به‌گام', 'zarincode' ); ?></td>
					<td><?php echo esc_html( $r->created_at ); ?></td>
				</tr>
			<?php endforeach; endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}
