<?php
/**
 * بکاپ امن و جریان‌محور دیتابیس زرین کد.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/** @return bool */
function zc_backup_enabled() {
	return (bool) zc_opt( 'zc_backup_enable', false );
}

/**
 * پوشهٔ محافظت‌شدهٔ بکاپ.
 *
 * @return string
 */
function zc_backup_directory() {
	$uploads = wp_upload_dir();
	$dir     = trailingslashit( $uploads['basedir'] ) . 'zarincode-private-backups/';

	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}

	// Apache/LiteSpeed، IIS و directory listing را پوشش می‌دهیم.
	if ( is_dir( $dir ) ) {
		if ( ! file_exists( $dir . '.htaccess' ) ) {
			file_put_contents( $dir . '.htaccess', "Require all denied\nDeny from all\n" ); // phpcs:ignore
		}
		if ( ! file_exists( $dir . 'web.config' ) ) {
			file_put_contents( $dir . 'web.config', '<?xml version="1.0"?><configuration><system.webServer><authorization><deny users="*" /></authorization></system.webServer></configuration>' ); // phpcs:ignore
		}
		if ( ! file_exists( $dir . 'index.php' ) ) {
			file_put_contents( $dir . 'index.php', "<?php\nhttp_response_code(404);\nexit;\n" ); // phpcs:ignore
		}
	}

	return $dir;
}

/**
 * نوشتن بکاپ به‌صورت chunk؛ هیچ جدول بزرگی یکجا وارد حافظه نمی‌شود.
 *
 * @return string|false
 */
function zc_backup_create() {
	global $wpdb;

	$dir = zc_backup_directory();
	if ( ! is_dir( $dir ) || ! is_writable( $dir ) ) {
		return false;
	}

	$filename = 'zarincode-' . gmdate( 'Ymd-His' ) . '-' . strtolower( wp_generate_password( 16, false, false ) ) . '.sql';
	$filepath = $dir . $filename;
	$handle   = fopen( $filepath, 'wb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions

	if ( ! $handle ) {
		return false;
	}

	$ok = true;
	$write = static function ( $text ) use ( $handle, &$ok ) {
		if ( false === fwrite( $handle, $text ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
			$ok = false;
		}
	};

	$write( "-- Zarincode Database Backup\n-- Date: " . current_time( 'mysql' ) . "\n-- URL: " . home_url() . "\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n" );

	$tables = (array) $wpdb->get_col( 'SHOW TABLES' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	foreach ( $tables as $table ) {
		if ( ! $ok || 0 !== strpos( $table, $wpdb->prefix ) || ! preg_match( '/^[A-Za-z0-9_]+$/', $table ) ) {
			continue;
		}

		$create = $wpdb->get_row( "SHOW CREATE TABLE `{$table}`", ARRAY_N ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$write( "DROP TABLE IF EXISTS `{$table}`;\n" );
		if ( $create && isset( $create[1] ) ) {
			$write( $create[1] . ";\n\n" );
		}

		$offset = 0;
		$chunk  = 500;
		do {
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$table}` LIMIT %d OFFSET %d", $chunk, $offset ), ARRAY_A ); // phpcs:ignore
			foreach ( (array) $rows as $row ) {
				$columns = array_map( static function ( $column ) { return '`' . str_replace( '`', '``', $column ) . '`'; }, array_keys( $row ) );
				$values  = array();
				foreach ( $row as $value ) {
					$values[] = null === $value ? 'NULL' : "'" . esc_sql( $value ) . "'";
				}
				$write( "INSERT INTO `{$table}` (" . implode( ',', $columns ) . ') VALUES (' . implode( ',', $values ) . ");\n" );
			}
			$offset += $chunk;
		} while ( $ok && count( (array) $rows ) === $chunk );

		$write( "\n" );
	}

	$write( "SET FOREIGN_KEY_CHECKS=1;\n" );
	fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions

	if ( ! $ok || ! file_exists( $filepath ) || 0 === filesize( $filepath ) ) {
		wp_delete_file( $filepath );
		return false;
	}

	// فشرده‌سازی stream برای کاهش جدی حجم انتقال و فضای دیسک.
	if ( function_exists( 'gzopen' ) && zc_opt( 'zc_backup_compress', true ) ) {
		$source = fopen( $filepath, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$gzpath = $filepath . '.gz';
		$target = gzopen( $gzpath, 'wb6' );
		if ( $source && $target ) {
			while ( ! feof( $source ) ) { gzwrite( $target, fread( $source, 1024 * 1024 ) ); } // phpcs:ignore WordPress.WP.AlternativeFunctions
			fclose( $source ); gzclose( $target ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( file_exists( $gzpath ) && filesize( $gzpath ) > 0 ) { wp_delete_file( $filepath ); return $gzpath; }
		} else {
			if ( $source ) { fclose( $source ); } // phpcs:ignore
			if ( $target ) { gzclose( $target ); }
		}
		wp_delete_file( $gzpath );
	}

	return $filepath;
}

/**
 * ارسال multipart فایل به Telegram/Bale بدون وابستگی به cURL.
 *
 * @param string $messenger پیام‌رسان.
 * @param string $chat_id   شناسه چت.
 * @param string $filepath  فایل.
 * @param string $caption   توضیح.
 * @return bool
 */
function zc_messenger_send_document( $messenger, $chat_id, $filepath, $caption = '' ) {
	if ( ! file_exists( $filepath ) || ! function_exists( 'zc_messengers' ) ) {
		return false;
	}
	$all = zc_messengers();
	if ( empty( $all[ $messenger ]['token'] ) || ! $chat_id ) {
		return false;
	}

	$url = sprintf( $all[ $messenger ]['api'], $all[ $messenger ]['token'], 'sendDocument' );

	// cURL فایل را stream می‌کند و برای بکاپ‌های بزرگ RAM مصرف نمی‌کند.
	if ( function_exists( 'curl_file_create' ) && function_exists( 'curl_init' ) ) {
		$curl = curl_init( $url ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		curl_setopt_array( $curl, array( // phpcs:ignore WordPress.WP.AlternativeFunctions
			CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 20, CURLOPT_TIMEOUT => 180,
			CURLOPT_POSTFIELDS => array( 'chat_id' => $chat_id, 'caption' => $caption, 'document' => curl_file_create( $filepath, '.gz' === substr( $filepath, -3 ) ? 'application/gzip' : 'application/sql', basename( $filepath ) ) ),
		) );
		$raw  = curl_exec( $curl ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$code = (int) curl_getinfo( $curl, CURLINFO_RESPONSE_CODE ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		curl_close( $curl ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$body = is_string( $raw ) ? json_decode( $raw, true ) : array();
		return $code >= 200 && $code < 300 && ! empty( $body['ok'] );
	}

	// fallback خام فقط برای فایل کوچک؛ در غیر این صورت فایل محلی حفظ می‌شود.
	if ( filesize( $filepath ) > 16 * MB_IN_BYTES ) { return false; }
	$boundary = '----ZC' . wp_generate_password( 24, false, false );
	$eol      = "\r\n";
	$parts    = '';
	$fields   = array( 'chat_id' => $chat_id, 'caption' => $caption );
	foreach ( $fields as $name => $value ) {
		$parts .= '--' . $boundary . $eol . 'Content-Disposition: form-data; name="' . $name . '"' . $eol . $eol . $value . $eol;
	}
	$parts .= '--' . $boundary . $eol;
	$parts .= 'Content-Disposition: form-data; name="document"; filename="' . sanitize_file_name( basename( $filepath ) ) . '"' . $eol;
	$parts .= 'Content-Type: ' . ( '.gz' === substr( $filepath, -3 ) ? 'application/gzip' : 'application/sql' ) . $eol . $eol;
	$parts .= file_get_contents( $filepath ) . $eol; // phpcs:ignore WordPress.WP.AlternativeFunctions
	$parts .= '--' . $boundary . '--' . $eol;

	$response = wp_remote_post(
		$url,
		array(
			'timeout' => 90,
			'headers' => array( 'Content-Type' => 'multipart/form-data; boundary=' . $boundary ),
			'body'    => $parts,
		)
	);
	if ( is_wp_error( $response ) ) {
		return false;
	}
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	return ! empty( $body['ok'] );
}

/** @return bool */
function zc_backup_send_messenger( $filepath ) {
	if ( ! zc_opt( 'zc_backup_send_telegram', true ) ) {
		return false;
	}

	$sent    = false;
	$caption = sprintf( 'Database backup — %s — %s', home_url(), current_time( 'mysql' ) );
	$targets = array(
		'telegram' => zc_opt( 'zc_telegram_chat_id', '' ),
		'bale'     => zc_opt( 'zc_bale_chat_id', '' ),
	);
	foreach ( $targets as $messenger => $chat_id ) {
		if ( $chat_id && zc_messenger_send_document( $messenger, $chat_id, $filepath, $caption ) ) {
			$sent = true;
		}
	}
	return $sent;
}

/** @return void */
function zc_backup_cleanup() {
	$dir   = zc_backup_directory();
	$max   = max( 1, (int) zc_opt( 'zc_backup_max', 5 ) );
	$files = glob( $dir . '*.sql*' );
	if ( ! $files ) {
		return;
	}
	usort( $files, static function ( $a, $b ) { return filemtime( $a ) <=> filemtime( $b ); } );
	foreach ( array_slice( $files, 0, max( 0, count( $files ) - $max ) ) as $file ) {
		wp_delete_file( $file );
	}
}

/** @return bool */
function zc_backup_run() {
	if ( ! zc_backup_enabled() ) { return false; }
	global $wpdb;
	$lock_name = 'zc_database_backup';
	if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 1)', $lock_name ) ) ) { return false; } // phpcs:ignore

	$filepath = false; $sent = false;
	try {
		$filepath = zc_backup_create();
		$sent     = $filepath ? zc_backup_send_messenger( $filepath ) : false;
		if ( $filepath ) {
			if ( $sent && ! zc_opt( 'zc_backup_keep_local', false ) ) { wp_delete_file( $filepath ); }
			else { zc_backup_cleanup(); }
		}
		update_option( 'zc_backup_last_result', array( 'time' => time(), 'file' => $filepath ? basename( $filepath ) : '', 'sent' => $sent ), false );
		return (bool) $filepath;
	} finally {
		$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) ); // phpcs:ignore
	}
}

/** @param array $schedules زمان‌بندی‌ها. @return array */
function zc_backup_cron_schedules( $schedules ) {
	$schedules['zc_monthly'] = array( 'interval' => 30 * DAY_IN_SECONDS, 'display' => __( 'ماهانه (زرین کد)', 'zarincode' ) );
	return $schedules;
}
add_filter( 'cron_schedules', 'zc_backup_cron_schedules' );

/** @return void */
function zc_backup_schedule() {
	$hook = 'zc_backup_event';
	if ( ! zc_backup_enabled() ) {
		wp_clear_scheduled_hook( $hook );
		return;
	}
	$map   = array( 'daily' => 'daily', 'weekly' => 'weekly', 'monthly' => 'zc_monthly' );
	$recur = $map[ (string) zc_opt( 'zc_backup_freq', 'daily' ) ] ?? 'daily';
	if ( wp_next_scheduled( $hook ) && wp_get_schedule( $hook ) !== $recur ) {
		wp_clear_scheduled_hook( $hook );
	}
	if ( ! wp_next_scheduled( $hook ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, $recur, $hook );
	}
}
add_action( 'init', 'zc_backup_schedule' );
add_action( 'zc_backup_event', 'zc_backup_run' );

/** @return void */
function zc_backup_manual() {
	if ( ! current_user_can( 'manage_options' ) || empty( $_GET['zc_backup_now'] ) ) { // phpcs:ignore
		return;
	}
	check_admin_referer( 'zc_backup_now' );
	zc_backup_run();
	wp_safe_redirect( remove_query_arg( array( 'zc_backup_now', '_wpnonce' ) ) );
	exit;
}
add_action( 'admin_init', 'zc_backup_manual' );

/** @return void */
function zc_backup_dashboard_button() {
	if ( ! zc_backup_enabled() ) {
		return;
	}
	$url = wp_nonce_url( add_query_arg( 'zc_backup_now', '1', admin_url( 'admin.php?page=zarincode' ) ), 'zc_backup_now' );
	?>
	<div class="zc-admin-box" style="margin-top:16px">
		<h2><?php esc_html_e( 'بکاپ دیتابیس', 'zarincode' ); ?></h2>
		<p class="description" style="margin:0 0 12px"><?php esc_html_e( 'بکاپ جریان‌محور، محافظت‌شده و قابل ارسال به پیام‌رسان.', 'zarincode' ); ?></p>
		<a class="button button-primary" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'بکاپ فوری', 'zarincode' ); ?></a>
	</div>
	<?php
}
add_action( 'zc_admin_dashboard_after_stats', 'zc_backup_dashboard_button' );
