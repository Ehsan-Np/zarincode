<?php
/**
 * مدیریت لایسنس‌ها و گواهینامه‌های قابل استعلام.
 *
 * @package Zarincode
 */
defined( 'ABSPATH' ) || exit;

/** @return void */
function zc_credentials_admin_menu() {
	add_submenu_page(
		'zarincode',
		__( 'لایسنس‌ها و مدارک', 'zarincode' ),
		__( 'لایسنس و مدرک', 'zarincode' ),
		'manage_options',
		'zarincode-credentials',
		'zc_credentials_admin_page'
	);
}
add_action( 'admin_menu', 'zc_credentials_admin_menu', 26 );

/** @return void */
function zc_credentials_admin_actions() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) || empty( $_POST['zc_credential_action'] ) ) { // phpcs:ignore
		return;
	}
	check_admin_referer( 'zc_credentials_admin' );
	global $wpdb;
	$action = sanitize_key( wp_unslash( $_POST['zc_credential_action'] ) ); // phpcs:ignore
	$id     = absint( $_POST['credential_id'] ?? 0 ); // phpcs:ignore

	if ( 'license_status' === $action ) {
		$status = sanitize_key( wp_unslash( $_POST['status'] ?? '' ) ); // phpcs:ignore
		if ( in_array( $status, array( 'active', 'suspended', 'revoked' ), true ) ) {
			$wpdb->update( $wpdb->prefix . 'zc_licenses', array( 'status' => $status ), array( 'id' => $id ), array( '%s' ), array( '%d' ) ); // phpcs:ignore
		}
	} elseif ( 'license_reset' === $action ) {
		$wpdb->update( $wpdb->prefix . 'zc_licenses', array( 'activations' => '[]' ), array( 'id' => $id ), array( '%s' ), array( '%d' ) ); // phpcs:ignore
	} elseif ( 'certificate_toggle' === $action ) {
		$revoked = absint( $_POST['revoked'] ?? 0 ) ? 1 : 0; // phpcs:ignore
		$wpdb->update( $wpdb->prefix . 'zc_certificates', array( 'revoked' => $revoked ), array( 'id' => $id ), array( '%d' ), array( '%d' ) ); // phpcs:ignore
	}

	wp_safe_redirect( add_query_arg( array( 'page' => 'zarincode-credentials', 'tab' => sanitize_key( wp_unslash( $_POST['tab'] ?? 'licenses' ) ), 'updated' => 1 ), admin_url( 'admin.php' ) ) ); // phpcs:ignore
	exit;
}
add_action( 'admin_init', 'zc_credentials_admin_actions' );

/** @return void */
function zc_credentials_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	global $wpdb;
	$tab    = isset( $_GET['tab'] ) && 'certificates' === sanitize_key( wp_unslash( $_GET['tab'] ) ) ? 'certificates' : 'licenses'; // phpcs:ignore
	$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore
	$like   = '%' . $wpdb->esc_like( $search ) . '%';
	?>
	<div class="wrap zc-admin-wrap">
		<h1><?php esc_html_e( 'مدیریت لایسنس‌ها و گواهینامه‌ها', 'zarincode' ); ?></h1>
		<nav class="nav-tab-wrapper">
			<a class="nav-tab <?php echo 'licenses' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=zarincode-credentials&tab=licenses' ) ); ?>"><?php esc_html_e( 'لایسنس‌ها', 'zarincode' ); ?></a>
			<a class="nav-tab <?php echo 'certificates' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=zarincode-credentials&tab=certificates' ) ); ?>"><?php esc_html_e( 'گواهینامه‌ها', 'zarincode' ); ?></a>
		</nav>
		<form method="get" style="margin:16px 0"><input type="hidden" name="page" value="zarincode-credentials"><input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>"><input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'جستجوی کد یا کاربر', 'zarincode' ); ?>"><button class="button"><?php esc_html_e( 'جستجو', 'zarincode' ); ?></button></form>
		<?php if ( 'licenses' === $tab ) :
			$sql  = "SELECT * FROM {$wpdb->prefix}zc_licenses";
			$args = array();
			if ( $search ) { $sql .= ' WHERE license_key LIKE %s OR user_id LIKE %s OR product_id LIKE %s'; $args = array( $like, $like, $like ); }
			$sql .= ' ORDER BY id DESC LIMIT 200';
			$rows = $args ? $wpdb->get_results( $wpdb->prepare( $sql, $args ) ) : $wpdb->get_results( $sql ); // phpcs:ignore
			?>
			<table class="widefat striped"><thead><tr><th>#</th><th><?php esc_html_e( 'کلید', 'zarincode' ); ?></th><th><?php esc_html_e( 'کاربر / محصول', 'zarincode' ); ?></th><th><?php esc_html_e( 'فعال‌سازی‌ها', 'zarincode' ); ?></th><th><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></th><th><?php esc_html_e( 'عملیات', 'zarincode' ); ?></th></tr></thead><tbody>
			<?php foreach ( $rows as $row ) : $acts = json_decode( $row->activations, true ) ?: array(); $license_user = get_userdata( $row->user_id ); ?>
			<tr><td><?php echo (int) $row->id; ?></td><td><code dir="ltr"><?php echo esc_html( $row->license_key ); ?></code></td><td><?php echo esc_html( $license_user ? $license_user->display_name : '#' . $row->user_id ); ?><br><?php echo esc_html( get_the_title( $row->product_id ) ); ?></td><td><?php echo esc_html( count( $acts ) . '/' . (int) $row->activation_limit ); ?><br><small dir="ltr"><?php echo esc_html( implode( ', ', $acts ) ); ?></small></td><td><?php echo esc_html( $row->status ); ?></td><td>
				<form method="post" style="display:inline-flex;gap:4px;align-items:center"><?php wp_nonce_field( 'zc_credentials_admin' ); ?><input type="hidden" name="zc_credential_action" value="license_status"><input type="hidden" name="credential_id" value="<?php echo (int) $row->id; ?>"><input type="hidden" name="tab" value="licenses"><select name="status"><option value="active" <?php selected( $row->status, 'active' ); ?>>active</option><option value="suspended" <?php selected( $row->status, 'suspended' ); ?>>suspended</option><option value="revoked" <?php selected( $row->status, 'revoked' ); ?>>revoked</option></select><button class="button"><?php esc_html_e( 'ذخیره', 'zarincode' ); ?></button></form>
				<form method="post" style="display:inline-block"><?php wp_nonce_field( 'zc_credentials_admin' ); ?><input type="hidden" name="zc_credential_action" value="license_reset"><input type="hidden" name="credential_id" value="<?php echo (int) $row->id; ?>"><input type="hidden" name="tab" value="licenses"><button class="button" onclick="return confirm('<?php echo esc_js( __( 'فعال‌سازی‌ها پاک شوند؟', 'zarincode' ) ); ?>')"><?php esc_html_e( 'ریست دامنه‌ها', 'zarincode' ); ?></button></form>
			</td></tr><?php endforeach; ?>
			</tbody></table>
		<?php else :
			$sql  = "SELECT * FROM {$wpdb->prefix}zc_certificates";
			$args = array();
			if ( $search ) { $sql .= ' WHERE code LIKE %s OR user_id LIKE %s OR course_id LIKE %s'; $args = array( $like, $like, $like ); }
			$sql .= ' ORDER BY id DESC LIMIT 200';
			$rows = $args ? $wpdb->get_results( $wpdb->prepare( $sql, $args ) ) : $wpdb->get_results( $sql ); // phpcs:ignore
			?>
			<table class="widefat striped"><thead><tr><th>#</th><th><?php esc_html_e( 'کد', 'zarincode' ); ?></th><th><?php esc_html_e( 'دارنده', 'zarincode' ); ?></th><th><?php esc_html_e( 'دوره', 'zarincode' ); ?></th><th><?php esc_html_e( 'تاریخ', 'zarincode' ); ?></th><th><?php esc_html_e( 'وضعیت', 'zarincode' ); ?></th></tr></thead><tbody>
			<?php foreach ( $rows as $row ) : $user = get_userdata( $row->user_id ); ?>
			<tr><td><?php echo (int) $row->id; ?></td><td><code dir="ltr"><?php echo esc_html( $row->code ); ?></code></td><td><?php echo esc_html( $user ? $user->display_name : '#' . $row->user_id ); ?></td><td><?php echo esc_html( get_the_title( $row->course_id ) ); ?></td><td><?php echo esc_html( $row->issued_at ); ?></td><td><form method="post"><?php wp_nonce_field( 'zc_credentials_admin' ); ?><input type="hidden" name="zc_credential_action" value="certificate_toggle"><input type="hidden" name="credential_id" value="<?php echo (int) $row->id; ?>"><input type="hidden" name="tab" value="certificates"><input type="hidden" name="revoked" value="<?php echo $row->revoked ? 0 : 1; ?>"><button class="button <?php echo $row->revoked ? 'button-primary' : ''; ?>"><?php echo $row->revoked ? esc_html__( 'فعال‌سازی', 'zarincode' ) : esc_html__( 'ابطال', 'zarincode' ); ?></button></form></td></tr><?php endforeach; ?>
			</tbody></table>
		<?php endif; ?>
	</div>
	<?php
}
