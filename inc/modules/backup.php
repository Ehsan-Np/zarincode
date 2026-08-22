<?php
/**
 * بکاپ خودکار دیتابیس زرین کد
 * ---------------------------------------------------------------------------
 * پشتیبان‌گیری خودکار از دیتابیس و ارسال به تلگرام/بله یا ذخیره‌ی محلی.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * فعال بودن بکاپ؟
 *
 * @return bool
 */
function zc_backup_enabled() {
	return (bool) zc_opt( 'zc_backup_enable', false );
}

/**
 * ساخت فایل بکاپ SQL از دیتابیس (بدون وابستگی خارجی).
 *
 * @return string|false مسیر فایل.
 */
function zc_backup_create() {
	global $wpdb;

	$dir = wp_upload_dir();
	$backup_dir = trailingslashit( $dir['basedir'] ) . 'zarincode-backup';
	if ( ! is_dir( $backup_dir ) ) {
		wp_mkdir_p( $backup_dir );
	}

	$filename = 'zarincode-backup-' . gmdate( 'Ymd-His' ) . '.sql';
	$filepath = trailingslashit( $backup_dir ) . $filename;

	$out = "-- Zarincode Database Backup\n-- Date: " . current_time( 'mysql' ) . "\n-- URL: " . home_url() . "\n\n";

	$tables = $wpdb->get_col( 'SHOW TABLES' ); // phpcs:ignore
	foreach ( $tables as $table ) {
		// فقط جداول وردپرس (پیشوند).
		if ( 0 !== strpos( $table, $wpdb->prefix ) ) {
			continue;
		}

		$out .= "DROP TABLE IF EXISTS `{$table}`;\n";
		$create = $wpdb->get_row( "SHOW CREATE TABLE `{$table}`", ARRAY_N ); // phpcs:ignore
		if ( $create && isset( $create[1] ) ) {
			$out .= $create[1] . ";\n\n";
		}

		$rows = $wpdb->get_results( "SELECT * FROM `{$table}`", ARRAY_A ); // phpcs:ignore
		foreach ( $rows as $row ) {
			$vals = array();
			foreach ( $row as $v ) {
				$vals[] = null === $v ? 'NULL' : "'" . esc_sql( $v ) . "'";
			}
			$out .= "INSERT INTO `{$table}` VALUES (" . implode( ',', $vals ) . ");\n";
		}
		$out .= "\n";
	}

	$written = file_put_contents( $filepath, $out ); // phpcs:ignore
	if ( false === $written ) {
		return false;
	}

	return $filepath;
}

/**
 * ارسال فایل بکاپ به تلگرام/بله.
 *
 * @param string $filepath مسیر فایل.
 * @return bool
 */
function zc_backup_send_messenger( $filepath ) {
	if ( ! zc_opt( 'zc_backup_send_telegram', true ) ) {
		return false;
	}

	$sent = false;

	// تلگرام.
	if ( function_exists( 'zc_telegram_send_document' ) ) {
		$sent = zc_telegram_send_document( $filepath ) || $sent;
	} elseif ( function_exists( 'zc_telegram_send' ) ) {
		// اگر متد سند نبود، پیام ساده.
		$sent = zc_telegram_send( "📦 بکاپ دیتابیس " . home_url() . " — " . basename( $filepath ) ) || $sent;
	}

	// بله.
	if ( function_exists( 'zc_bale_send' ) ) {
		$sent = zc_bale_send( "📦 بکاپ دیتابیس " . home_url() . " — " . basename( $filepath ) ) || $sent;
	}

	return $sent;
}

/**
 * پاک‌سازی بکاپ‌های قدیمی محلی.
 *
 * @return void
 */
function zc_backup_cleanup() {
	$dir = wp_upload_dir();
	$backup_dir = trailingslashit( $dir['basedir'] ) . 'zarincode-backup';
	$max = (int) zc_opt( 'zc_backup_max', 5 );

	$files = glob( trailingslashit( $backup_dir ) . '*.sql' );
	if ( ! $files ) {
		return;
	}

	// مرتب بر اساس تاریخ (قدیمی‌ترین اول).
	usort( $files, function ( $a, $b ) { return filemtime( $a ) <=> filemtime( $b ); } );

	$excess = count( $files ) - $max;
	for ( $i = 0; $i < $excess; $i++ ) {
		@unlink( $files[ $i ] ); // phpcs:ignore
	}
}

/**
 * اجرای بکاپ.
 *
 * @return void
 */
function zc_backup_run() {
	if ( ! zc_backup_enabled() ) {
		return;
	}

	$filepath = zc_backup_create();
	if ( ! $filepath ) {
		zc_log( 'Backup creation failed.', 'Backup' );
		return;
	}

	zc_backup_send_messenger( $filepath );

	// اگر نگهداری محلی فعال نباشد، فایل حذف می‌شود (فقط برای پیام‌رسان).
	if ( ! zc_opt( 'zc_backup_keep_local', false ) ) {
		@unlink( $filepath ); // phpcs:ignore
	} else {
		zc_backup_cleanup();
	}
}

/**
 * زمان‌بندی کرون بر اساس فرکانس.
 *
 * @return void
 */
function zc_backup_schedule() {
	if ( ! zc_backup_enabled() ) {
		wp_clear_scheduled_hook( 'zc_backup_event' );
		return;
	}

	$freq = (string) zc_opt( 'zc_backup_freq', 'daily' );
	$hook = 'zc_backup_event';
	$map  = array(
		'daily'   => 'daily',
		'weekly'  => 'weekly',
		'monthly' => 'monthly',
	);

	// وردپرس فقط daily دارد؛ برای weekly/monthly از تکرار دستی استفاده می‌کنیم.
	$recur = $map[ $freq ] ?? 'daily';

	if ( ! wp_next_scheduled( $hook ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, $recur, $hook );
	}
}
add_action( 'init', 'zc_backup_schedule' );

add_action( 'zc_backup_event', 'zc_backup_run' );

/**
 * دکمه‌ی بکاپ دستی در پیشخوان.
 *
 * @return void
 */
function zc_backup_manual() {
	if ( ! current_user_can( 'manage_options' ) || empty( $_GET['zc_backup_now'] ) ) { // phpcs:ignore
		return;
	}
	zc_backup_run();
	wp_safe_redirect( remove_query_arg( 'zc_backup_now' ) );
	exit;
}
add_action( 'admin_init', 'zc_backup_manual' );

/**
 * دکمه‌ی بکاپ دستی در داشبورد زرین کد.
 *
 * @return void
 */
function zc_backup_dashboard_button() {
	?>
	<div class="zc-admin-box" style="margin-top:16px">
		<h2><?php esc_html_e( 'بکاپ دیتابیس', 'zarincode' ); ?></h2>
		<p class="description" style="margin:0 0 12px"><?php esc_html_e( 'یک نسخه‌ی فوری از دیتابیس بسازید و به پیام‌رسان/محلی ارسال کنید.', 'zarincode' ); ?></p>
		<a class="button button-primary" href="<?php echo esc_url( add_query_arg( 'zc_backup_now', '1', admin_url( 'admin.php?page=zarincode' ) ) ); ?>">
			<?php esc_html_e( 'بکاپ فوری', 'zarincode' ); ?>
		</a>
	</div>
	<?php
}
add_action( 'zc_admin_dashboard_after_stats', 'zc_backup_dashboard_button' );
