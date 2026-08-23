<?php
/**
 * دفتر حسابرسی اقدامات حساس.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * ایجاد جدول لاگ.
 *
 * @return void
 */
function zc_create_audit_table() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset = $wpdb->get_charset_collate();
	$table   = $wpdb->prefix . 'zc_audit_log';
	$sql     = "CREATE TABLE {$table} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		action VARCHAR(80) NOT NULL,
		object_type VARCHAR(40) NOT NULL DEFAULT '',
		object_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		ip VARCHAR(45) NULL,
		meta LONGTEXT NULL,
		created_at DATETIME NULL DEFAULT NULL,
		PRIMARY KEY (id),
		KEY action (action),
		KEY user_id (user_id),
		KEY created_at (created_at)
	) {$charset};";
	dbDelta( $sql );
}

/**
 * ثبت رویداد حسابرسی.
 *
 * @param string $action      نام اقدام.
 * @param string $object_type نوع شیء.
 * @param int    $object_id   شناسه.
 * @param array  $meta        متا.
 * @return int|false
 */
function zc_audit( $action, $object_type = '', $object_id = 0, $meta = array() ) {
	global $wpdb;
	$table = $wpdb->prefix . 'zc_audit_log';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) { // phpcs:ignore
		zc_create_audit_table();
	}

	$ok = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$table,
		array(
			'user_id'     => get_current_user_id(),
			'action'      => sanitize_key( $action ),
			'object_type' => sanitize_key( $object_type ),
			'object_id'   => (int) $object_id,
			'ip'          => function_exists( 'zc_user_ip' ) ? zc_user_ip() : '',
			'meta'        => wp_json_encode( $meta, JSON_UNESCAPED_UNICODE ),
			'created_at'  => current_time( 'mysql' ),
		),
		array( '%d', '%s', '%s', '%d', '%s', '%s', '%s' )
	);

	return $ok ? (int) $wpdb->insert_id : false;
}

/**
 * هوک‌های پیش‌فرض حسابرسی.
 *
 * @return void
 */
function zc_audit_bind_hooks() {
	add_action(
		'zc_wallet_withdrawn',
		static function ( $user_id, $amount, $balance, $tx ) {
			zc_audit( 'wallet_withdraw', 'transaction', (int) $tx, array( 'user_id' => $user_id, 'amount' => $amount, 'balance' => $balance ) );
		},
		20,
		4
	);
	add_action(
		'zc_wallet_deposited',
		static function ( $user_id, $amount, $balance, $tx ) {
			if ( $amount >= 1000000 ) {
				zc_audit( 'wallet_large_deposit', 'transaction', (int) $tx, array( 'user_id' => $user_id, 'amount' => $amount ) );
			}
		},
		20,
		4
	);
	add_action(
		'set_user_role',
		static function ( $user_id, $role ) {
			zc_audit( 'role_change', 'user', (int) $user_id, array( 'role' => $role ) );
		},
		20,
		2
	);
	add_action(
		'zc_course_completed',
		static function ( $user_id, $course_id ) {
			zc_audit( 'course_completed', 'course', (int) $course_id, array( 'user_id' => $user_id ) );
		},
		40,
		2
	);
}
add_action( 'init', 'zc_audit_bind_hooks', 20 );

/**
 * منوی پیشخوان.
 *
 * @return void
 */
function zc_audit_admin_menu() {
	add_submenu_page( 'zarincode', __( 'حسابرسی امنیتی', 'zarincode' ), __( 'حسابرسی', 'zarincode' ), 'manage_options', 'zarincode-audit', 'zc_audit_admin_page' );
}
add_action( 'admin_menu', 'zc_audit_admin_menu', 40 );

/**
 * صفحه لاگ.
 *
 * @return void
 */
function zc_audit_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	global $wpdb;
	$table = $wpdb->prefix . 'zc_audit_log';
	$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC LIMIT 200" ); // phpcs:ignore
	?>
	<div class="wrap zc-admin-wrap">
		<?php if ( function_exists( 'zc_admin_notice_anchor' ) ) { zc_admin_notice_anchor(); } ?>
		<h1><?php esc_html_e( 'دفتر حسابرسی امنیتی', 'zarincode' ); ?></h1>
		<table class="widefat striped">
			<thead>
				<tr>
					<th>#</th>
					<th><?php esc_html_e( 'اقدام', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'کاربر', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'شیء', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'IP', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'جزئیات', 'zarincode' ); ?></th>
					<th><?php esc_html_e( 'زمان', 'zarincode' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( $rows ) : ?>
				<?php foreach ( $rows as $row ) : ?>
					<?php $user = get_user_by( 'id', $row->user_id ); ?>
					<tr>
						<td><?php echo (int) $row->id; ?></td>
						<td><code><?php echo esc_html( $row->action ); ?></code></td>
						<td><?php echo esc_html( $user ? $user->display_name : '—' ); ?></td>
						<td><?php echo esc_html( $row->object_type . '#' . $row->object_id ); ?></td>
						<td><?php echo esc_html( $row->ip ); ?></td>
						<td><small><?php echo esc_html( wp_trim_words( (string) $row->meta, 18 ) ); ?></small></td>
						<td><?php echo esc_html( $row->created_at ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr><td colspan="7"><?php esc_html_e( 'هنوز رویدادی ثبت نشده است.', 'zarincode' ); ?></td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}
