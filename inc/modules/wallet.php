<?php
/**
 * ماژول کیف پول و حسابداری پایه
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * ایجاد جداول دیتابیس قالب.
 *
 * @return void
 */
function zc_create_tables() {
	global $wpdb;

	$charset = $wpdb->get_charset_collate();
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	// جدول تراکنش‌های کیف پول و حسابداری.
	$table = $wpdb->prefix . 'zc_transactions';
	$sql   = "CREATE TABLE {$table} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		amount DECIMAL(18,2) NOT NULL DEFAULT 0,
		type VARCHAR(20) NOT NULL DEFAULT 'deposit',
		category VARCHAR(40) NOT NULL DEFAULT 'general',
		status VARCHAR(20) NOT NULL DEFAULT 'completed',
		description TEXT NULL,
		ref_id VARCHAR(100) NULL,
		authority VARCHAR(100) NULL,
		gateway VARCHAR(40) NULL,
		balance_after DECIMAL(18,2) NOT NULL DEFAULT 0,
		meta LONGTEXT NULL,
		created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY (id),
		KEY user_id (user_id),
		KEY type (type),
		KEY status (status),
		KEY created_at (created_at)
	) {$charset};";
	dbDelta( $sql );

	// جدول پیشرفت دوره‌ها.
	$table = $wpdb->prefix . 'zc_progress';
	$sql   = "CREATE TABLE {$table} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id BIGINT(20) UNSIGNED NOT NULL,
		course_id BIGINT(20) UNSIGNED NOT NULL,
		lesson_key VARCHAR(120) NOT NULL,
		status VARCHAR(20) NOT NULL DEFAULT 'completed',
		seconds INT UNSIGNED NOT NULL DEFAULT 0,
		updated_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY (id),
		UNIQUE KEY user_lesson (user_id, course_id, lesson_key),
		KEY course_id (course_id)
	) {$charset};";
	dbDelta( $sql );

	// جدول ثبت‌نام در دوره‌ها.
	$table = $wpdb->prefix . 'zc_enrollments';
	$sql   = "CREATE TABLE {$table} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id BIGINT(20) UNSIGNED NOT NULL,
		course_id BIGINT(20) UNSIGNED NOT NULL,
		order_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		price DECIMAL(18,2) NOT NULL DEFAULT 0,
		status VARCHAR(20) NOT NULL DEFAULT 'active',
		expire_at DATETIME NULL,
		created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY (id),
		UNIQUE KEY user_course (user_id, course_id),
		KEY course_id (course_id)
	) {$charset};";
	dbDelta( $sql );

	// جدول پیام‌های چت.
	$table = $wpdb->prefix . 'zc_chats';
	$sql   = "CREATE TABLE {$table} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		session_id VARCHAR(64) NOT NULL,
		sender VARCHAR(20) NOT NULL DEFAULT 'user',
		message TEXT NOT NULL,
		is_read TINYINT(1) NOT NULL DEFAULT 0,
		status VARCHAR(20) NOT NULL DEFAULT 'open',
		created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY (id),
		KEY session_id (session_id),
		KEY user_id (user_id),
		KEY is_read (is_read)
	) {$charset};";
	dbDelta( $sql );

	// جدول رزرو نوبت.
	$table = $wpdb->prefix . 'zc_bookings';
	$sql   = "CREATE TABLE {$table} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		service_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		name VARCHAR(190) NOT NULL,
		mobile VARCHAR(20) NOT NULL,
		date DATE NOT NULL,
		time VARCHAR(10) NOT NULL,
		note TEXT NULL,
		status VARCHAR(20) NOT NULL DEFAULT 'pending',
		reminded TINYINT(1) NOT NULL DEFAULT 0,
		created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY (id),
		KEY date_time (date, time),
		KEY user_id (user_id),
		KEY reminded (reminded)
	) {$charset};";
	dbDelta( $sql );

	// جدول گزارش پیامک.
	$table = $wpdb->prefix . 'zc_sms_log';
	$sql   = "CREATE TABLE {$table} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		mobile VARCHAR(20) NOT NULL,
		message TEXT NULL,
		type VARCHAR(30) NOT NULL DEFAULT 'general',
		status VARCHAR(20) NOT NULL DEFAULT 'sent',
		error VARCHAR(255) NULL,
		created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY (id),
		KEY type (type),
		KEY status (status),
		KEY created_at (created_at)
	) {$charset};";
	dbDelta( $sql );

	// جدول گفتگوی قرارداد در ماژول خودش تعریف شده است.
	if ( function_exists( 'zc_create_contract_chat_table' ) ) {
		zc_create_contract_chat_table();
	}

	// جدول‌هایی که در ماژول‌های خودشان تعریف شده‌اند.
	if ( function_exists( 'zc_create_contract_chat_table' ) ) {
		zc_create_contract_chat_table();
	}

	if ( function_exists( 'zc_create_service_coupon_table' ) ) {
		zc_create_service_coupon_table();
	}

	if ( function_exists( 'zc_create_contacts_table' ) ) {
		zc_create_contacts_table();
	}

	update_option( 'zc_db_version', ZC_VERSION );
}
add_action( 'after_switch_theme', 'zc_create_tables' );

/**
 * بررسی و بروزرسانی دیتابیس.
 *
 * @return void
 */
function zc_check_db() {
	if ( get_option( 'zc_db_version' ) !== ZC_VERSION ) {
		zc_create_tables();
	}
}
add_action( 'admin_init', 'zc_check_db' );

/* ==================== کیف پول ==================== */

/**
 * دریافت موجودی کیف پول.
 *
 * @param int $user_id کاربر.
 * @return float
 */
function zc_wallet_balance( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return 0;
	}
	return (float) get_user_meta( $user_id, 'zc_wallet_balance', true );
}

/**
 * افزایش موجودی کیف پول.
 *
 * @param int    $user_id     کاربر.
 * @param float  $amount      مبلغ.
 * @param string $description توضیح.
 * @param string $category    دسته.
 * @param array  $extra       اطلاعات اضافه.
 * @return int|false شناسه تراکنش.
 */
function zc_wallet_deposit( $user_id, $amount, $description = '', $category = 'deposit', $extra = array() ) {
	$amount = abs( (float) $amount );
	if ( ! $user_id || $amount <= 0 ) {
		return false;
	}

	$balance = zc_wallet_balance( $user_id ) + $amount;
	update_user_meta( $user_id, 'zc_wallet_balance', $balance );

	$tx = zc_add_transaction(
		array(
			'user_id'       => $user_id,
			'amount'        => $amount,
			'type'          => 'deposit',
			'category'      => $category,
			'description'   => $description,
			'balance_after' => $balance,
			'status'        => 'completed',
		) + $extra
	);

	do_action( 'zc_wallet_deposited', $user_id, $amount, $balance, $tx );

	return $tx;
}

/**
 * کاهش موجودی کیف پول.
 *
 * @param int    $user_id     کاربر.
 * @param float  $amount      مبلغ.
 * @param string $description توضیح.
 * @param string $category    دسته.
 * @param array  $extra       اضافه.
 * @return int|WP_Error
 */
function zc_wallet_withdraw( $user_id, $amount, $description = '', $category = 'purchase', $extra = array() ) {
	$amount  = abs( (float) $amount );
	$balance = zc_wallet_balance( $user_id );

	if ( $amount > $balance ) {
		return new WP_Error( 'zc_insufficient', __( 'موجودی کیف پول کافی نیست.', 'zarincode' ) );
	}

	$balance -= $amount;
	update_user_meta( $user_id, 'zc_wallet_balance', $balance );

	$tx = zc_add_transaction(
		array(
			'user_id'       => $user_id,
			'amount'        => -$amount,
			'type'          => 'withdraw',
			'category'      => $category,
			'description'   => $description,
			'balance_after' => $balance,
			'status'        => 'completed',
		) + $extra
	);

	do_action( 'zc_wallet_withdrawn', $user_id, $amount, $balance, $tx );

	return $tx;
}

/**
 * ثبت تراکنش در دیتابیس.
 *
 * @param array $args آرگومان‌ها.
 * @return int|false
 */
function zc_add_transaction( $args ) {
	global $wpdb;

	$defaults = array(
		'user_id'       => get_current_user_id(),
		'amount'        => 0,
		'type'          => 'deposit',
		'category'      => 'general',
		'status'        => 'completed',
		'description'   => '',
		'ref_id'        => '',
		'authority'     => '',
		'gateway'       => '',
		'balance_after' => 0,
		'meta'          => '',
		'created_at'    => current_time( 'mysql' ),
	);

	$data = wp_parse_args( $args, $defaults );

	if ( is_array( $data['meta'] ) ) {
		$data['meta'] = wp_json_encode( $data['meta'], JSON_UNESCAPED_UNICODE );
	}

	$result = $wpdb->insert( $wpdb->prefix . 'zc_transactions', $data ); // phpcs:ignore

	return $result ? (int) $wpdb->insert_id : false;
}

/**
 * دریافت تراکنش‌های کاربر.
 *
 * @param int   $user_id کاربر.
 * @param array $args    آرگومان.
 * @return array
 */
function zc_get_transactions( $user_id = 0, $args = array() ) {
	global $wpdb;

	$user_id = $user_id ? $user_id : get_current_user_id();
	$args    = wp_parse_args(
		$args,
		array(
			'limit'  => 20,
			'offset' => 0,
			'type'   => '',
			'status' => '',
		)
	);

	$table = $wpdb->prefix . 'zc_transactions';
	$where = $wpdb->prepare( 'WHERE user_id = %d', $user_id );

	if ( $args['type'] ) {
		$where .= $wpdb->prepare( ' AND type = %s', $args['type'] );
	}
	if ( $args['status'] ) {
		$where .= $wpdb->prepare( ' AND status = %s', $args['status'] );
	}

	// phpcs:disable
	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$table} {$where} ORDER BY id DESC LIMIT %d OFFSET %d",
			(int) $args['limit'],
			(int) $args['offset']
		)
	);
	// phpcs:enable
}

/**
 * شمارش تراکنش‌ها.
 *
 * @param int $user_id کاربر.
 * @return int
 */
function zc_count_transactions( $user_id = 0 ) {
	global $wpdb;
	$user_id = $user_id ? $user_id : get_current_user_id();
	$table   = $wpdb->prefix . 'zc_transactions';
	return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE user_id = %d", $user_id ) ); // phpcs:ignore
}

/**
 * درخواست شارژ کیف پول (ای‌جکس).
 *
 * @return void
 */
function zc_ajax_wallet_charge() {
	zc_check_ajax();

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'ابتدا وارد شوید.', 'zarincode' ) ) );
	}

	$amount = isset( $_POST['amount'] ) ? (float) zc_en_num( sanitize_text_field( wp_unslash( $_POST['amount'] ) ) ) : 0;
	$min    = (float) zc_opt( 'zc_wallet_min_charge', 10000 );

	if ( $amount < $min ) {
		wp_send_json_error(
			array(
				'message' => sprintf(
					/* translators: %s: minimum amount */
					__( 'حداقل مبلغ شارژ %s است.', 'zarincode' ),
					zc_fa_num( number_format( $min ) ) . ' ' . zc_opt( 'zc_currency_symbol', 'تومان' )
				),
			)
		);
	}

	$result = zc_zarinpal_request(
		$amount,
		__( 'شارژ کیف پول', 'zarincode' ),
		add_query_arg( 'zc_wallet_callback', '1', zc_panel_url( 'wallet' ) )
	);

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	}

	wp_send_json_success( array( 'redirect' => $result['url'] ) );
}
add_action( 'wp_ajax_zc_wallet_charge', 'zc_ajax_wallet_charge' );

/**
 * پردازش بازگشت از درگاه شارژ کیف پول.
 *
 * @return void
 */
function zc_handle_wallet_callback() {
	if ( ! isset( $_GET['zc_wallet_callback'] ) || ! is_user_logged_in() ) { // phpcs:ignore
		return;
	}

	$authority = isset( $_GET['Authority'] ) ? sanitize_text_field( wp_unslash( $_GET['Authority'] ) ) : ''; // phpcs:ignore
	$status    = isset( $_GET['Status'] ) ? sanitize_text_field( wp_unslash( $_GET['Status'] ) ) : ''; // phpcs:ignore

	if ( 'OK' !== $status || ! $authority ) {
		set_transient( 'zc_wallet_msg_' . get_current_user_id(), array( 'type' => 'error', 'text' => __( 'پرداخت لغو شد.', 'zarincode' ) ), 60 );
		return;
	}

	$pending = get_transient( 'zc_zp_' . $authority );
	if ( ! $pending ) {
		return;
	}

	$verify = zc_zarinpal_verify( $authority, $pending['amount'] );

	if ( is_wp_error( $verify ) ) {
		set_transient( 'zc_wallet_msg_' . get_current_user_id(), array( 'type' => 'error', 'text' => $verify->get_error_message() ), 60 );
		return;
	}

	zc_wallet_deposit(
		get_current_user_id(),
		$pending['amount'],
		__( 'شارژ کیف پول از درگاه زرین‌پال', 'zarincode' ),
		'charge',
		array(
			'ref_id'    => $verify['ref_id'],
			'authority' => $authority,
			'gateway'   => 'zarinpal',
		)
	);

	delete_transient( 'zc_zp_' . $authority );

	set_transient(
		'zc_wallet_msg_' . get_current_user_id(),
		array(
			'type' => 'success',
			'text' => sprintf(
				/* translators: %s: reference id */
				__( 'کیف پول شما با موفقیت شارژ شد. کد پیگیری: %s', 'zarincode' ),
				zc_fa_num( $verify['ref_id'] )
			),
		),
		60
	);
}
add_action( 'template_redirect', 'zc_handle_wallet_callback' );

/**
 * افزودن کیف پول به عنوان روش پرداخت ووکامرس.
 *
 * @return void
 */
function zc_wallet_checkout_field() {
	if ( ! zc_opt( 'zc_wallet_enable', true ) || ! is_user_logged_in() || ! function_exists( 'WC' ) ) {
		return;
	}

	$balance = zc_wallet_balance();
	if ( $balance <= 0 ) {
		return;
	}

	$total = (float) WC()->cart->get_total( 'edit' );
	$usable = min( $balance, $total );
	?>
	<div class="zc-wallet-pay" style="background:var(--zc-gold-soft);border:1px dashed var(--zc-gold);border-radius:var(--zc-radius-sm);padding:14px 16px;margin:16px 0">
		<label class="zc-check">
			<input type="checkbox" name="zc_use_wallet" value="1" <?php checked( WC()->session->get( 'zc_use_wallet' ) ); ?>>
			<span>
				<?php
				printf(
					/* translators: 1: balance 2: usable */
					esc_html__( 'استفاده از موجودی کیف پول (موجودی: %1$s | قابل استفاده: %2$s)', 'zarincode' ),
					esc_html( zc_fa_num( number_format( $balance ) ) . ' ' . zc_opt( 'zc_currency_symbol', 'تومان' ) ),
					esc_html( zc_fa_num( number_format( $usable ) ) )
				);
				?>
			</span>
		</label>
	</div>
	<?php
}
add_action( 'woocommerce_review_order_before_payment', 'zc_wallet_checkout_field' );
