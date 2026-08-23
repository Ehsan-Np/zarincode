<?php
/**
 * ماژول خبرنامه زرین کد
 * ---------------------------------------------------------------------------
 * عضویت، مدیریت مخاطبین، خروجی/ورودی اکسل (CSV سازگار با اکسل) و ارسال خبرنامه
 * از پیشخوان. ارسال پیامک هزینه را برآورد و موجودی کاوه‌نگار را بررسی می‌کند.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   ۱) ذخیره‌سازی مخاطبین
   ========================================================================== */

/**
 * دریافت فهرست مخاطبین خبرنامه.
 *
 * @return array
 */
function zc_newsletter_subscribers() {
	if ( function_exists( 'zc_newsletter_storage_ready' ) && zc_newsletter_storage_ready() ) {
		return zc_newsletter_storage_all();
	}

	$list = get_option( 'zc_newsletter_subscribers', array() );

	// سازگاری با ساختار قدیمی (فهرست ساده‌ی مقادیر).
	if ( ! is_array( $list ) || ( ! empty( $list ) && ! is_array( $list[0] ) ) ) {
		$old = get_option( 'zc_newsletter_list', array() );
		$list = array();
		foreach ( (array) $old as $value ) {
			$rec = array( 'email' => '', 'mobile' => '', 'bale_id' => '', 'telegram_id' => '', 'name' => '', 'date' => '' );
			if ( is_email( $value ) ) {
				$rec['email'] = $value;
			} elseif ( preg_match( '/^09\d{9}$/', $value ) ) {
				$rec['mobile'] = $value;
			} else {
				$rec['email'] = $value;
			}
			$list[] = $rec;
		}
		update_option( 'zc_newsletter_subscribers', $list );
	}

	return is_array( $list ) ? $list : array();
}

/** @return int */
function zc_newsletter_count() {
	if ( function_exists( 'zc_newsletter_storage_count' ) && zc_newsletter_storage_ready() ) {
		return zc_newsletter_storage_count();
	}
	return count( zc_newsletter_subscribers() );
}

/**
 * افزودن مخاطب با جلوگیری از تکرار.
 *
 * @param array $data داده‌ی مخاطب.
 * @return array  کل مخاطبین.
 */
function zc_newsletter_add( $data ) {
	$data = wp_parse_args(
		$data,
		array( 'email' => '', 'mobile' => '', 'bale_id' => '', 'telegram_id' => '', 'name' => '', 'date' => current_time( 'mysql' ) )
	);
	$data['email'] = sanitize_email( $data['email'] );
	$data['mobile'] = zc_sanitize_mobile( $data['mobile'] );
	$data['bale_id'] = ltrim( (string) $data['bale_id'], '@' );
	$data['telegram_id'] = ltrim( (string) $data['telegram_id'], '@' );

	if ( ! $data['email'] && ! $data['mobile'] ) {
		return array();
	}

	if ( function_exists( 'zc_newsletter_storage_ready' ) && zc_newsletter_storage_ready() ) {
		zc_newsletter_storage_add( $data );
		return array( $data );
	}

	$list = zc_newsletter_subscribers();
	foreach ( $list as $sub ) {
		if ( $data['email'] && strtolower( (string) $sub['email'] ) === strtolower( $data['email'] ) ) {
			return $list;
		}
		if ( $data['mobile'] && (string) $sub['mobile'] === $data['mobile'] ) {
			return $list;
		}
	}

	$list[] = $data;
	$list   = array_slice( $list, -50000 );
	update_option( 'zc_newsletter_subscribers', $list );

	return $list;
}

/* ==========================================================================
   ۱.۱) کمپین و کد تخفیف و ردیابی
   ========================================================================== */

/**
 * ساخت کد تخفیف (اختیاری محدود به ایمیل).
 *
 * @param int    $percent درصد تخفیف.
 * @param int    $days    اعتبار.
 * @param string $prefix  پیشوند.
 * @param string $email   ایمیل محدودشده (اختیاری).
 * @return string کد تخفیف.
 */
function zc_newsletter_coupon_code( $percent, $days = 14, $prefix = 'NL', $email = '' ) {
	if ( ! class_exists( 'WC_Coupon' ) ) {
		return '';
	}

	$percent = max( 1, min( 100, (int) $percent ) );
	$days    = max( 1, (int) $days );

	$alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
	$code     = '';

	do {
		$code = strtoupper( $prefix ) . '-' . wp_rand( 1000, 9999 );
		for ( $i = 0; $i < 4; $i++ ) {
			$code .= $alphabet[ wp_rand( 0, strlen( $alphabet ) - 1 ) ];
		}
	} while ( function_exists( 'wc_get_coupon_id_by_code' ) && wc_get_coupon_id_by_code( $code ) );

	$coupon = new WC_Coupon();
	$coupon->set_code( $code );
	$coupon->set_discount_type( 'percent' );
	$coupon->set_amount( $percent );
	$coupon->set_individual_use( true );
	$coupon->set_usage_limit( 1 );
	$coupon->set_usage_limit_per_user( 1 );
	$coupon->set_date_expires( time() + ( $days * DAY_IN_SECONDS ) );

	if ( $email ) {
		$coupon->set_email_restrictions( array( $email ) );
	}

	$coupon->set_description( __( 'تخفیف خبرنامه زرین کد', 'zarincode' ) );
	$coupon->save();

	return $code;
}

/**
 * ثبت کمپین خبرنامه.
 *
 * @param array $data داده‌ی کمپین.
 * @return string شناسه‌ی کمپین.
 */
function zc_newsletter_campaign_add( $data ) {
	global $wpdb;
	$campaign = wp_parse_args( $data, array( 'channel' => 'sms', 'subject' => '', 'message' => '', 'total' => 0, 'coupon_percent' => 0, 'status' => 'queued' ) );
	$ok = $wpdb->insert(
		$wpdb->prefix . 'zc_newsletter_campaigns',
		array(
			'channel' => sanitize_key( $campaign['channel'] ), 'subject' => sanitize_text_field( $campaign['subject'] ),
			'message' => wp_kses_post( $campaign['message'] ), 'total' => (int) $campaign['total'],
			'coupon_percent' => max( 0, min( 100, (int) $campaign['coupon_percent'] ) ),
			'status' => sanitize_key( $campaign['status'] ), 'created_at' => current_time( 'mysql' ),
		),
		array( '%s', '%s', '%s', '%d', '%d', '%s', '%s' )
	); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	return $ok ? (int) $wpdb->insert_id : 0;
}

/**
 * ثبت یک گیرنده در کمپین (وضعیت ارسال).
 *
 * @param string $campaign_id شناسه‌ی کمپین.
 * @param string $email       ایمیل گیرنده.
 * @param string $status      وضعیت: sent|failed.
 * @return void
 */
function zc_newsletter_campaign_recipient( $campaign_id, $email, $status ) {
	global $wpdb;
	$recipient = sanitize_text_field( $email );
	if ( ! $campaign_id || ! $recipient ) { return; }
	$table = $wpdb->prefix . 'zc_newsletter_recipients';
	$wpdb->query( $wpdb->prepare(
		"INSERT INTO {$table} (campaign_id,recipient,status,opened,sent_at) VALUES (%d,%s,%s,0,%s)
		 ON DUPLICATE KEY UPDATE status=VALUES(status),sent_at=VALUES(sent_at)",
		$campaign_id, $recipient, sanitize_key( $status ), current_time( 'mysql' )
	) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}

/**
 * ردیابی باز شدن ایمیل (پیکسل) — تابعی که به‌عنوان تصویر ۱×۱ صدا زده می‌شود.
 *
 * @param int    $campaign_id شناسه‌ی کمپین.
 * @param string $email       ایمیل.
 * @return void
 */
function zc_newsletter_mark_opened( $campaign_id, $email ) {
	global $wpdb;
	$campaign_id = (int) $campaign_id;
	$email       = sanitize_email( $email );
	if ( ! $campaign_id || ! $email ) { return; }
	$table   = $wpdb->prefix . 'zc_newsletter_recipients';
	$changed = $wpdb->query( $wpdb->prepare( "UPDATE {$table} SET opened=1 WHERE campaign_id=%d AND recipient=%s AND opened=0", $campaign_id, $email ) ); // phpcs:ignore
	if ( $changed ) {
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}zc_newsletter_campaigns SET opened=opened+1 WHERE id=%d", $campaign_id ) ); // phpcs:ignore
	}
}

/**
 * اندپوینت ردیابی باز شدن ایمیل (خروجی تصویر ۱×۱).
 *
 * @return void
 */
function zc_newsletter_tracking_endpoint() {
	if ( empty( $_GET['zc_nl_track'] ) ) { // phpcs:ignore
		return;
	}

	$id    = absint( $_GET['zc_nl_track'] ); // phpcs:ignore
	$email = isset( $_GET['recipient'] ) ? sanitize_email( urldecode( wp_unslash( $_GET['recipient'] ) ) ) : ''; // phpcs:ignore
	$sig   = isset( $_GET['sig'] ) ? sanitize_text_field( wp_unslash( $_GET['sig'] ) ) : ''; // phpcs:ignore
	$valid = $id && $email && $sig && hash_equals( hash_hmac( 'sha256', $id . '|' . $email, wp_salt( 'nonce' ) ), $sig );

	if ( $valid ) {
		zc_newsletter_mark_opened( $id, $email );
	}

	// خروجی تصویر ۱×۱ GIF.
	nocache_headers();
	header( 'Content-Type: image/gif' );
	header( 'Content-Length: 43' );
	echo base64_decode( 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' ); // phpcs:ignore
	exit;
}
add_action( 'init', 'zc_newsletter_tracking_endpoint', 5 );

/* ==========================================================================
   ۲) منوی پیشخوان
   ========================================================================== */

/**
 * ثبت منوی خبرنامه.
 *
 * @return void
 */
function zc_newsletter_menu() {
	add_submenu_page(
		'zarincode',
		__( 'خبرنامه زرین کد', 'zarincode' ),
		__( 'خبرنامه', 'zarincode' ),
		'manage_options',
		'zc-newsletter',
		'zc_newsletter_page'
	);
}
add_action( 'admin_menu', 'zc_newsletter_menu' );

/**
 * صفحه‌ی خبرنامه.
 *
 * @return void
 */
function zc_newsletter_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// پردازش اکشن‌ها.
	zc_newsletter_handle_actions();

	$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'subscribers';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'خبرنامه زرین کد', 'zarincode' ); ?></h1>

		<nav class="nav-tab-wrapper">
			<a class="nav-tab <?php echo 'subscribers' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=zc-newsletter&tab=subscribers' ) ); ?>"><?php esc_html_e( 'مخاطبین', 'zarincode' ); ?></a>
			<a class="nav-tab <?php echo 'send' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=zc-newsletter&tab=send' ) ); ?>"><?php esc_html_e( 'ارسال خبرنامه', 'zarincode' ); ?></a>
			<a class="nav-tab <?php echo 'report' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=zc-newsletter&tab=report' ) ); ?>"><?php esc_html_e( 'گزارش کمپین‌ها', 'zarincode' ); ?></a>
		</nav>

		<?php if ( 'send' === $tab ) : ?>
			<?php zc_newsletter_send_panel(); ?>
		<?php elseif ( 'report' === $tab ) : ?>
			<?php zc_newsletter_report_panel(); ?>
		<?php else : ?>
			<?php zc_newsletter_subscribers_panel(); ?>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * پردازش اکشن‌های فرم (حذف، خروجی، ورودی).
 *
 * @return void
 */
function zc_newsletter_handle_actions() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// توقف یا ازسرگیری کمپین صف‌شده.
	if ( isset( $_POST['zc_campaign_action'], $_POST['campaign_id'] ) && wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ?? '' ), 'zc_newsletter_campaign_action' ) ) { // phpcs:ignore
		global $wpdb;
		$campaign_id = absint( $_POST['campaign_id'] ); // phpcs:ignore
		$action      = sanitize_key( wp_unslash( $_POST['zc_campaign_action'] ) ); // phpcs:ignore
		if ( 'cancel' === $action ) {
			$wpdb->update( $wpdb->prefix . 'zc_newsletter_campaigns', array( 'status' => 'cancelled', 'finished_at' => current_time( 'mysql' ) ), array( 'id' => $campaign_id ), array( '%s', '%s' ), array( '%d' ) ); // phpcs:ignore
		} elseif ( 'resume' === $action ) {
			$processed = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}zc_newsletter_recipients WHERE campaign_id=%d AND status IN ('sent','failed','skipped')", $campaign_id ) ); // phpcs:ignore
			$wpdb->update( $wpdb->prefix . 'zc_newsletter_campaigns', array( 'status' => 'queued', 'finished_at' => null ), array( 'id' => $campaign_id ), array( '%s', '%s' ), array( '%d' ) ); // phpcs:ignore
			zc_schedule_action( time() + 2, 'zc_newsletter_process_batch', array( $campaign_id, $processed ) );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=zc-newsletter&tab=report' ) );
		exit;
	}

	// حذف مخاطب.
	if ( isset( $_POST['zc_newsletter_delete'] ) && wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'zc_newsletter_delete' ) ) { // phpcs:ignore
		$delete = sanitize_text_field( wp_unslash( $_POST['zc_newsletter_delete'] ) );
		if ( 0 === strpos( $delete, 'id:' ) && function_exists( 'zc_newsletter_storage_delete' ) ) {
			zc_newsletter_storage_delete( absint( substr( $delete, 3 ) ) );
		} else {
			$index = (int) $delete;
			$list  = zc_newsletter_subscribers();
			if ( isset( $list[ $index ] ) ) {
				unset( $list[ $index ] );
				update_option( 'zc_newsletter_subscribers', array_values( $list ), false );
			}
		}
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'مخاطب حذف شد.', 'zarincode' ) . '</p></div>';
	}

	// خروجی CSV.
	if ( isset( $_GET['zc_export'] ) && '1' === $_GET['zc_export'] ) {
		check_admin_referer( 'zc_newsletter_export' );
		zc_newsletter_export_csv();
		exit;
	}

	// ورودی CSV.
	if ( isset( $_POST['zc_newsletter_import'] ) && wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'zc_newsletter_import' ) ) { // phpcs:ignore
		if ( ! empty( $_FILES['zc_newsletter_file']['tmp_name'] ) ) {
			$added = zc_newsletter_import_csv( $_FILES['zc_newsletter_file']['tmp_name'] );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $added ) . '</p></div>';
		} else {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'فایلی انتخاب نشده است.', 'zarincode' ) . '</p></div>';
		}
	}
}

/**
 * پنل مخاطبین: لیست، خروجی و ورودی اکسل.
 *
 * @return void
 */
function zc_newsletter_subscribers_panel() {
	$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore
	$page     = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1; // phpcs:ignore
	$per_page = 100;
	if ( function_exists( 'zc_newsletter_storage_page' ) && zc_newsletter_storage_ready() ) {
		$filtered = zc_newsletter_storage_page( $per_page, ( $page - 1 ) * $per_page, $search );
		$total    = zc_newsletter_storage_count( $search );
	} else {
		$list     = zc_newsletter_subscribers();
		$filtered = $search ? array_filter( $list, static function ( $s ) use ( $search ) { return false !== stripos( implode( ' ', (array) $s ), $search ); } ) : $list;
		$total    = count( $filtered );
		$filtered = array_slice( $filtered, ( $page - 1 ) * $per_page, $per_page, true );
	}
	?>
	<div style="display:flex;flex-wrap:wrap;gap:14px;align-items:center;margin:16px 0;justify-content:space-between">
		<form method="get" style="display:flex;gap:8px">
			<input type="hidden" name="page" value="zc-newsletter">
			<input type="text" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'جستجو…', 'zarincode' ); ?>">
			<button class="button"><?php esc_html_e( 'جستجو', 'zarincode' ); ?></button>
		</form>
		<div style="display:flex;gap:8px">
			<a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=zc-newsletter&zc_export=1' ), 'zc_newsletter_export' ) ); ?>"><?php esc_html_e( 'خروجی اکسل (CSV)', 'zarincode' ); ?></a>
		</div>
	</div>

	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'نام', 'zarincode' ); ?></th>
				<th><?php esc_html_e( 'ایمیل', 'zarincode' ); ?></th>
				<th><?php esc_html_e( 'موبایل', 'zarincode' ); ?></th>
				<th><?php esc_html_e( 'تلگرام', 'zarincode' ); ?></th>
				<th><?php esc_html_e( 'بله', 'zarincode' ); ?></th>
				<th><?php esc_html_e( 'تاریخ عضویت', 'zarincode' ); ?></th>
				<th><?php esc_html_e( 'حذف', 'zarincode' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			if ( $filtered ) :
				foreach ( $filtered as $i => $s ) :
					?>
					<tr>
						<td><?php echo esc_html( $s['name'] ?? '' ); ?></td>
						<td><?php echo esc_html( $s['email'] ?? '' ); ?></td>
						<td dir="ltr"><?php echo esc_html( $s['mobile'] ?? '' ); ?></td>
						<td dir="ltr"><?php echo esc_html( $s['telegram_id'] ?? '' ); ?></td>
						<td dir="ltr"><?php echo esc_html( $s['bale_id'] ?? '' ); ?></td>
						<td><?php echo esc_html( $s['date'] ?? '' ); ?></td>
						<td>
							<form method="post" style="display:inline" onsubmit="return confirm('حذف شود؟')">
								<?php wp_nonce_field( 'zc_newsletter_delete' ); ?>
								<button type="submit" name="zc_newsletter_delete" value="<?php echo ! empty( $s['_id'] ) ? esc_attr( 'id:' . (int) $s['_id'] ) : (int) $i; ?>" class="button-link-delete"><?php esc_html_e( 'حذف', 'zarincode' ); ?></button>
							</form>
						</td>
					</tr>
					<?php
				endforeach;
			else :
				?>
				<tr><td colspan="7"><?php esc_html_e( 'مخاطبی یافت نشد.', 'zarincode' ); ?></td></tr>
			<?php endif; ?>
		</tbody>
	</table>
	<?php
	$pagination = paginate_links( array(
		'base' => add_query_arg( array( 'page' => 'zc-newsletter', 'tab' => 'subscribers', 's' => $search, 'paged' => '%#%' ), admin_url( 'admin.php' ) ),
		'format' => '', 'current' => $page, 'total' => max( 1, (int) ceil( $total / $per_page ) ), 'type' => 'list',
	) );
	if ( $pagination ) { echo wp_kses_post( $pagination ); }
	?>

	<div style="margin-top:20px;background:#fff;border:1px solid #e2e4e7;border-radius:8px;padding:18px;max-width:560px">
		<h3 style="margin-top:0"><?php esc_html_e( 'ورود مخاطبین از فایل اکسل', 'zarincode' ); ?></h3>
		<p class="description"><?php esc_html_e( 'فایل CSV با ستون‌های: name, email, mobile, telegram_id, bale_id. موبایل باید با 09 شروع شود.', 'zarincode' ); ?></p>
		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'zc_newsletter_import' ); ?>
			<input type="file" name="zc_newsletter_file" accept=".csv,.txt" required>
			<button type="submit" name="zc_newsletter_import" value="1" class="button button-secondary"><?php esc_html_e( 'بارگذاری', 'zarincode' ); ?></button>
		</form>
	</div>
	<?php
}

/**
 * خروجی CSV (سازگار با اکسل).
 *
 * @return void
 */
function zc_newsletter_export_csv() {
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="zarincode-newsletter-' . gmdate( 'Ymd-His' ) . '.csv"' );

	$out = fopen( 'php://output', 'w' );
	fprintf( $out, "\xEF\xBB\xBF" ); // BOM برای اکسل.
	fputcsv( $out, array( 'name', 'email', 'mobile', 'telegram_id', 'bale_id', 'date' ) );

	$offset = 0; $limit = 500;
	do {
		$rows = function_exists( 'zc_newsletter_storage_page' ) && zc_newsletter_storage_ready()
			? zc_newsletter_storage_page( $limit, $offset )
			: array_slice( zc_newsletter_subscribers(), $offset, $limit );
		foreach ( $rows as $s ) {
			fputcsv( $out, array( $s['name'] ?? '', $s['email'] ?? '', $s['mobile'] ?? '', $s['telegram_id'] ?? '', $s['bale_id'] ?? '', $s['date'] ?? '' ) );
		}
		$offset += count( $rows );
	} while ( count( $rows ) === $limit );

	fclose( $out );
}

/**
 * ورودی CSV.
 *
 * @param string $path مسیر فایل موقت.
 * @return string پیام.
 */
function zc_newsletter_import_csv( $path ) {
	if ( ! file_exists( $path ) ) { return __( 'فایل خوانده نشد.', 'zarincode' ); }
	$handle = fopen( $path, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! $handle ) { return __( 'فایل خوانده نشد.', 'zarincode' ); }

	$header = fgetcsv( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! is_array( $header ) ) { fclose( $handle ); return __( 'سربرگ CSV معتبر نیست.', 'zarincode' ); } // phpcs:ignore
	$header[0] = preg_replace( '/^\xEF\xBB\xBF/', '', (string) $header[0] );
	$header = array_map( static function ( $column ) { return sanitize_key( strtolower( trim( $column ) ) ); }, $header );
	$allowed = array( 'name', 'email', 'mobile', 'telegram_id', 'bale_id' );
	if ( ! array_intersect( $allowed, $header ) ) { fclose( $handle ); return __( 'ستون‌های CSV معتبر نیستند.', 'zarincode' ); } // phpcs:ignore

	$before = zc_newsletter_count(); $processed = 0;
	while ( ( $row = fgetcsv( $handle ) ) !== false ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ++$processed > 100000 ) { break; }
		$data = array();
		foreach ( $header as $index => $column ) {
			if ( in_array( $column, $allowed, true ) ) { $data[ $column ] = $row[ $index ] ?? ''; }
		}
		$data['email']  = sanitize_email( $data['email'] ?? '' );
		$data['mobile'] = zc_sanitize_mobile( $data['mobile'] ?? '' );
		if ( $data['email'] || $data['mobile'] ) { zc_newsletter_add( $data ); }
	}
	fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$added = max( 0, zc_newsletter_count() - $before );
	return sprintf( __( '%s مخاطب جدید اضافه شد.', 'zarincode' ), zc_fa_num( $added ) );
}

/* ==========================================================================
   ۳) ارسال خبرنامه
   ========================================================================== */

/**
 * پنل ارسال خبرنامه.
 *
 * @return void
 */
function zc_newsletter_send_panel() {
	$count = zc_newsletter_count();

	// نمایش نتیجه‌ی ارسال.
	if ( isset( $_GET['zc_nl_result'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( wp_unslash( $_GET['zc_nl_result'] ) ) . '</p></div>'; // phpcs:ignore
	}
	$zc_err = isset( $_GET['zc_nl_error'] ) ? sanitize_text_field( wp_unslash( $_GET['zc_nl_error'] ) ) : ( isset( $_COOKIE['zc_nl_error'] ) ? wp_unslash( $_COOKIE['zc_nl_error'] ) : '' ); // phpcs:ignore
	if ( $zc_err ) {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $zc_err ) . '</p></div>';
	}
	?>
	<div style="margin-top:16px">
		<p>
			<strong><?php esc_html_e( 'تعداد مخاطبین:', 'zarincode' ); ?></strong>
			<?php echo esc_html( zc_fa_num( $count ) ); ?>
		</p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="zc_newsletter_send">
			<?php wp_nonce_field( 'zc_newsletter_send' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="zc_nl_channel"><?php esc_html_e( 'کانال ارسال', 'zarincode' ); ?></label></th>
					<td>
						<select name="channel" id="zc_nl_channel">
							<option value="sms"><?php esc_html_e( 'پیامک (موبایل)', 'zarincode' ); ?></option>
							<option value="email"><?php esc_html_e( 'ایمیل', 'zarincode' ); ?></option>
							<option value="both"><?php esc_html_e( 'پیامک + ایمیل', 'zarincode' ); ?></option>
							<option value="bot"><?php esc_html_e( 'ربات (تلگرام / بله به آیدی شخصی)', 'zarincode' ); ?></option>
							<option value="all"><?php esc_html_e( 'همه‌ی کانال‌ها', 'zarincode' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'برای هر مخاطب به کانال(های) انتخابی که داده‌ی آن را دارد ارسال می‌شود. «ربات» به آیدی تلگرام/بله‌ی شخصی هر مشترک می‌فرستد.', 'zarincode' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="zc_nl_subject"><?php esc_html_e( 'موضوع (برای ایمیل)', 'zarincode' ); ?></label></th>
					<td><input type="text" name="subject" id="zc_nl_subject" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row"><label for="zc_nl_coupon"><?php esc_html_e( 'درصد تخفیف درون پیام (اختیاری)', 'zarincode' ); ?></label></th>
					<td>
						<input type="number" name="coupon_percent" id="zc_nl_coupon" min="0" max="100" value="<?php echo esc_attr( zc_opt( 'zc_newsletter_coupon_percent', 0 ) ); ?>" class="small-text">
						<span class="description"><?php esc_html_e( 'اگر بیشتر از ۰ باشد، برای هر گیرنده یک کد تخفیف یکتا ساخته و به‌صورت {coupon} در پیام جایگذاری می‌شود (مناسب پیامک).', 'zarincode' ); ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="zc_nl_message"><?php esc_html_e( 'متن پیام', 'zarincode' ); ?></label></th>
					<td>
						<?php
						wp_editor(
							'',
							'zc_nl_message',
							array(
								'textarea_name' => 'message',
								'textarea_rows' => 10,
								'media_buttons' => true,
							)
						);
						?>
						<p class="description">
							<?php esc_html_e( 'متغیرها: ', 'zarincode' ); ?>
							<code>{name}</code> <code>{email}</code> <code>{mobile}</code>
						</p>
					</td>
				</tr>
			</table>

			<div style="background:#fff;border:1px solid #e2e4e7;border-radius:8px;padding:14px 16px;margin-bottom:14px;max-width:560px">
				<button type="button" class="button" id="zc-nl-check-balance"><?php esc_html_e( 'بررسی موجودی و هزینه‌ی پیامک', 'zarincode' ); ?></button>
				<span id="zc-nl-balance-msg" style="margin-inline-start:10px;color:#2271b1"></span>
			</div>

			<button type="submit" class="button button-primary" id="zc-nl-send"><?php esc_html_e( 'ارسال خبرنامه', 'zarincode' ); ?></button>
		</form>
	</div>

	<script>
	(function ($) {
		$('#zc-nl-check-balance').on('click', function () {
			var $msg = $('#zc-nl-balance-msg');
			var text = (window.tinymce && tinymce.get('zc_nl_message')) ? tinymce.get('zc_nl_message').getContent() : $('#zc_nl_message').val();
			var channel = $('#zc_nl_channel').val();
			if (channel === 'email') { $msg.text('برای ایمیل نیازی به بررسی پیامک نیست.'); return; }
			$msg.text('در حال بررسی…');
			$.post(ajaxurl, { action: 'zc_newsletter_check_balance', message: text, nonce: '<?php echo esc_js( wp_create_nonce( 'zc_nonce' ) ); ?>' }, function (r) {
				$msg.html(r.success ? '<span style="color:#16a34a">' + r.data.message + '</span>' : '<span style="color:#dc2626">' + (r.data && r.data.message ? r.data.message : 'خطا') + '</span>');
			});
		});
	})(jQuery);
	</script>
	<?php
}

/**
 * پنل گزارش کمپین‌های خبرنامه (نرخ ارسال / باز شدن).
 *
 * @return void
 */
function zc_newsletter_report_panel() {
	global $wpdb;
	$campaigns = $wpdb->get_results( "SELECT id,created_at AS date,channel,subject,total,sent,failed,opened,status FROM {$wpdb->prefix}zc_newsletter_campaigns ORDER BY id DESC LIMIT 200", ARRAY_A ); // phpcs:ignore

	if ( ! $campaigns ) {
		echo '<p>' . esc_html__( 'هنوز کمپینی ارسال نشده است.', 'zarincode' ) . '</p>';
		return;
	}
	?>
	<table class="widefat striped" style="margin-top:16px">
		<thead>
			<tr>
				<th><?php esc_html_e( 'تاریخ', 'zarincode' ); ?></th>
				<th><?php esc_html_e( 'کانال', 'zarincode' ); ?></th>
				<th><?php esc_html_e( 'موضوع', 'zarincode' ); ?></th>
				<th><?php esc_html_e( 'کل', 'zarincode' ); ?></th>
				<th><?php esc_html_e( 'ارسال‌شده', 'zarincode' ); ?></th>
				<th><?php esc_html_e( 'خطا', 'zarincode' ); ?></th>
				<th><?php esc_html_e( 'بازشده', 'zarincode' ); ?></th>
				<th><?php esc_html_e( 'نرخ باز شدن', 'zarincode' ); ?></th>
				<th><?php esc_html_e( 'وضعیت / عملیات', 'zarincode' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $campaigns as $i => $c ) : ?>
				<?php
				$c = wp_parse_args(
					$c,
					array( 'id' => 0, 'date' => '', 'channel' => '', 'subject' => '', 'total' => 0, 'sent' => 0, 'failed' => 0, 'opened' => 0, 'status' => '' )
				);
				$rate = $c['sent'] > 0 ? round( ( $c['opened'] / $c['sent'] ) * 100, 1 ) : 0;
				$channels = array(
					'sms'  => __( 'پیامک', 'zarincode' ),
					'email'=> __( 'ایمیل', 'zarincode' ),
					'both' => __( 'پیامک + ایمیل', 'zarincode' ),
					'bot'  => __( 'ربات', 'zarincode' ),
					'all'  => __( 'همه', 'zarincode' ),
				);
				?>
				<tr>
					<td><?php echo esc_html( $c['date'] ); ?></td>
					<td><?php echo esc_html( $channels[ $c['channel'] ] ?? $c['channel'] ); ?></td>
					<td><?php echo esc_html( mb_substr( $c['subject'], 0, 40 ) ); ?></td>
					<td><?php echo esc_html( zc_fa_num( $c['total'] ) ); ?></td>
					<td style="color:#16a34a"><?php echo esc_html( zc_fa_num( $c['sent'] ) ); ?></td>
					<td style="color:#dc2626"><?php echo esc_html( zc_fa_num( $c['failed'] ) ); ?></td>
					<td><?php echo esc_html( zc_fa_num( $c['opened'] ) ); ?></td>
					<td><strong><?php echo esc_html( zc_fa_num( $rate ) ); ?>%</strong></td>
					<td><?php echo esc_html( $c['status'] ); ?>
						<?php if ( in_array( $c['status'], array( 'queued', 'processing' ), true ) ) : ?>
						<form method="post" style="display:inline"><?php wp_nonce_field( 'zc_newsletter_campaign_action' ); ?><input type="hidden" name="campaign_id" value="<?php echo (int) $c['id']; ?>"><button class="button-link-delete" name="zc_campaign_action" value="cancel"><?php esc_html_e( 'توقف', 'zarincode' ); ?></button></form>
						<?php elseif ( 'cancelled' === $c['status'] ) : ?>
						<form method="post" style="display:inline"><?php wp_nonce_field( 'zc_newsletter_campaign_action' ); ?><input type="hidden" name="campaign_id" value="<?php echo (int) $c['id']; ?>"><button class="button-link" name="zc_campaign_action" value="resume"><?php esc_html_e( 'ادامه', 'zarincode' ); ?></button></form>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="description" style="margin-top:10px"><?php esc_html_e( '«باز شدن» مخصوص ایمیل است (پیکسل ردیابی)؛ پیامک و ربات نرخ باز شدن ندارند.', 'zarincode' ); ?></p>
	<?php
}

/**
 * بررسی موجودی پیامک (AJAX).
 *
 * @return void
 */
function zc_ajax_newsletter_check_balance() {
	check_ajax_referer( 'zc_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ), 403 );
	}

	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	$count   = function_exists( 'zc_newsletter_storage_mobile_count' ) ? zc_newsletter_storage_mobile_count() : zc_newsletter_count();
	$text    = zc_sms_plain_text( $message );

	if ( ! $text ) {
		wp_send_json_error( array( 'message' => __( 'متن پیام خالی است.', 'zarincode' ) ) );
	}

	$check = zc_sms_check_credit( $text, $count );

	wp_send_json(
		array(
			'success' => $check['ok'],
			'data'    => $check,
		)
	);
}
add_action( 'wp_ajax_zc_newsletter_check_balance', 'zc_ajax_newsletter_check_balance' );

/**
 * پردازش ارسال خبرنامه.
 *
 * @return void
 */
function zc_newsletter_handle_send() {
	if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ?? '' ), 'zc_newsletter_send' ) ) { // phpcs:ignore
		wp_die( esc_html__( 'دسترسی غیرمجاز.', 'zarincode' ) );
	}

	$channel = sanitize_key( wp_unslash( $_POST['channel'] ?? 'sms' ) );
	$subject = sanitize_text_field( wp_unslash( $_POST['subject'] ?? '' ) );
	$message = wp_kses_post( wp_unslash( $_POST['message'] ?? '' ) );
	$percent = max( 0, min( 100, absint( $_POST['coupon_percent'] ?? 0 ) ) );
	$total   = zc_newsletter_count();

	if ( ! in_array( $channel, array( 'sms', 'email', 'both', 'bot', 'all' ), true ) || ! trim( wp_strip_all_tags( $message ) ) || ! $total ) {
		wp_die( esc_html__( 'کانال، متن یا فهرست مخاطبان معتبر نیست.', 'zarincode' ) );
	}

	if ( in_array( $channel, array( 'sms', 'both', 'all' ), true ) ) {
		$sms_count = function_exists( 'zc_newsletter_storage_mobile_count' ) ? zc_newsletter_storage_mobile_count() : $total;
		$check = zc_sms_check_credit( zc_sms_plain_text( $message ), $sms_count );
		if ( ! $check['ok'] ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'zc-newsletter', 'tab' => 'send', 'zc_nl_error' => $check['message'] ), admin_url( 'admin.php' ) ) );
			exit;
		}
	}

	$campaign_id = zc_newsletter_campaign_add( array(
		'channel' => $channel, 'subject' => $subject, 'message' => $message,
		'total' => $total, 'coupon_percent' => $percent, 'status' => 'queued',
	) );
	if ( ! $campaign_id ) {
		wp_die( esc_html__( 'ساخت کمپین ناموفق بود.', 'zarincode' ) );
	}

	zc_schedule_action( time() + 2, 'zc_newsletter_process_batch', array( $campaign_id, 0 ) );
	wp_safe_redirect( add_query_arg( array( 'page' => 'zc-newsletter', 'tab' => 'report', 'zc_nl_result' => __( 'کمپین در صف قرار گرفت و به‌صورت دسته‌ای ارسال می‌شود.', 'zarincode' ) ), admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_post_zc_newsletter_send', 'zc_newsletter_handle_send' );

/**
 * پردازش یک batch خبرنامه توسط Action Scheduler یا WP-Cron.
 *
 * @param int $campaign_id کمپین.
 * @param int $offset      شروع.
 * @return void
 */
function zc_newsletter_process_batch( $campaign_id, $offset = 0 ) {
	global $wpdb;
	$campaign_id = (int) $campaign_id;
	$offset      = max( 0, (int) $offset );
	$campaign    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}zc_newsletter_campaigns WHERE id=%d LIMIT 1", $campaign_id ), ARRAY_A ); // phpcs:ignore
	if ( ! $campaign || in_array( $campaign['status'], array( 'completed', 'cancelled' ), true ) ) { return; }

	$batch = function_exists( 'zc_newsletter_storage_page' ) && zc_newsletter_storage_ready()
		? zc_newsletter_storage_page( 50, $offset )
		: array_slice( zc_newsletter_subscribers(), $offset, 50 );
	if ( ! $batch ) {
		$wpdb->update( $wpdb->prefix . 'zc_newsletter_campaigns', array( 'status' => 'completed', 'finished_at' => current_time( 'mysql' ) ), array( 'id' => $campaign_id ), array( '%s', '%s' ), array( '%d' ) ); // phpcs:ignore
		return;
	}

	$wpdb->update( $wpdb->prefix . 'zc_newsletter_campaigns', array( 'status' => 'processing' ), array( 'id' => $campaign_id ), array( '%s' ), array( '%d' ) ); // phpcs:ignore
	$do_sms  = in_array( $campaign['channel'], array( 'sms', 'both', 'all' ), true );
	$do_mail = in_array( $campaign['channel'], array( 'email', 'both', 'all' ), true );
	$do_bot  = in_array( $campaign['channel'], array( 'bot', 'all' ), true );
	$sent = 0; $failed = 0;

	foreach ( $batch as $sub ) {
		$email     = sanitize_email( $sub['email'] ?? '' );
		$mobile    = zc_sanitize_mobile( $sub['mobile'] ?? '' );
		$recipient = $email ?: ( $mobile ?: (string) ( $sub['_id'] ?? '' ) );
		$prior     = $recipient ? $wpdb->get_var( $wpdb->prepare( "SELECT status FROM {$wpdb->prefix}zc_newsletter_recipients WHERE campaign_id=%d AND recipient=%s LIMIT 1", $campaign_id, $recipient ) ) : ''; // phpcs:ignore
		if ( in_array( $prior, array( 'sent', 'skipped' ), true ) ) { continue; }
		zc_newsletter_campaign_recipient( $campaign_id, $recipient, 'processing' );
		$coupon = '';
		if ( (int) $campaign['coupon_percent'] > 0 && class_exists( 'WC_Coupon' ) ) {
			$coupon = zc_newsletter_coupon_code( (int) $campaign['coupon_percent'], 14, 'NL', $email );
		}
		$text = zc_sms_parse_vars( $campaign['message'], array( 'name' => $sub['name'] ?? '', 'email' => $email, 'mobile' => $mobile, 'coupon' => $coupon ) );
		$plain = zc_sms_plain_text( $text );
		$recipient_sent = false; $recipient_failed = false;

		if ( $do_sms && $mobile ) {
			if ( zc_sms_dispatch( $mobile, $plain, 'newsletter' ) ) { $sent++; $recipient_sent = true; } else { $failed++; $recipient_failed = true; }
		}
		if ( $do_mail && $email ) {
			$sig = hash_hmac( 'sha256', $campaign_id . '|' . $email, wp_salt( 'nonce' ) );
			$track_url = add_query_arg( array( 'zc_nl_track' => $campaign_id, 'recipient' => $email, 'sig' => $sig ), home_url( '/' ) );
			$body = $text . '<br><img src="' . esc_url( $track_url ) . '" width="1" height="1" alt="" style="display:none">';
			if ( wp_mail( $email, $campaign['subject'] ?: get_bloginfo( 'name' ), $body, array( 'Content-Type: text/html; charset=UTF-8' ) ) ) { $sent++; $recipient_sent = true; } else { $failed++; $recipient_failed = true; }
		}
		if ( $do_bot ) {
			foreach ( array( 'telegram', 'bale' ) as $messenger ) {
				$handle = trim( (string) ( $sub[ $messenger . '_id' ] ?? '' ) );
				if ( ! $handle ) { continue; }
				if ( zc_messenger_send_to( $messenger, '@' . ltrim( $handle, '@' ), $plain ) ) { $sent++; $recipient_sent = true; } else { $failed++; $recipient_failed = true; }
			}
		}
		zc_newsletter_campaign_recipient( $campaign_id, $recipient, $recipient_sent ? 'sent' : ( $recipient_failed ? 'failed' : 'skipped' ) );
	}

	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}zc_newsletter_campaigns SET sent=sent+%d,failed=failed+%d WHERE id=%d", $sent, $failed, $campaign_id ) ); // phpcs:ignore
	$next = $offset + count( $batch );
	if ( count( $batch ) < 50 || $next >= (int) $campaign['total'] ) {
		$wpdb->update( $wpdb->prefix . 'zc_newsletter_campaigns', array( 'status' => 'completed', 'finished_at' => current_time( 'mysql' ) ), array( 'id' => $campaign_id ), array( '%s', '%s' ), array( '%d' ) ); // phpcs:ignore
	} else {
		zc_schedule_action( time() + 5, 'zc_newsletter_process_batch', array( $campaign_id, $next ) );
	}
}
add_action( 'zc_newsletter_process_batch', 'zc_newsletter_process_batch', 10, 2 );
