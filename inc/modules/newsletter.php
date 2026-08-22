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

/**
 * افزودن مخاطب با جلوگیری از تکرار.
 *
 * @param array $data داده‌ی مخاطب.
 * @return array  کل مخاطبین.
 */
function zc_newsletter_add( $data ) {
	$list   = zc_newsletter_subscribers();
	$data   = wp_parse_args(
		$data,
		array( 'email' => '', 'mobile' => '', 'bale_id' => '', 'telegram_id' => '', 'name' => '', 'date' => current_time( 'mysql' ) )
	);
	$data['email'] = sanitize_email( $data['email'] );
	$data['mobile'] = zc_sanitize_mobile( $data['mobile'] );
	$data['bale_id'] = ltrim( (string) $data['bale_id'], '@' );
	$data['telegram_id'] = ltrim( (string) $data['telegram_id'], '@' );

	if ( ! $data['email'] && ! $data['mobile'] ) {
		return $list;
	}

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
	$campaigns = get_option( 'zc_newsletter_campaigns', array() );

	$campaign = wp_parse_args(
		$data,
		array(
			'date'     => current_time( 'mysql' ),
			'channel'  => 'sms',
			'subject'  => '',
			'message'  => '',
			'total'    => 0,
			'sent'     => 0,
			'failed'   => 0,
			'opened'   => 0,
			'recipients' => array(),
		)
	);

	$campaigns[] = $campaign;
	update_option( 'zc_newsletter_campaigns', array_slice( $campaigns, -200 ) );

	return count( $campaigns ) - 1;
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
	$campaigns = get_option( 'zc_newsletter_campaigns', array() );
	if ( ! isset( $campaigns[ $campaign_id ] ) ) {
		return;
	}

	$campaigns[ $campaign_id ]['recipients'][ $email ] = array(
		'status' => $status,
		'opened' => 0,
		'at'     => current_time( 'mysql' ),
	);
	update_option( 'zc_newsletter_campaigns', $campaigns );
}

/**
 * ردیابی باز شدن ایمیل (پیکسل) — تابعی که به‌عنوان تصویر ۱×۱ صدا زده می‌شود.
 *
 * @param int    $campaign_id شناسه‌ی کمپین.
 * @param string $email       ایمیل.
 * @return void
 */
function zc_newsletter_mark_opened( $campaign_id, $email ) {
	$campaigns = get_option( 'zc_newsletter_campaigns', array() );
	$campaign_id = (int) $campaign_id;

	if ( ! isset( $campaigns[ $campaign_id ] ) ) {
		return;
	}

	if ( isset( $campaigns[ $campaign_id ]['recipients'][ $email ] ) && empty( $campaigns[ $campaign_id ]['recipients'][ $email ]['opened'] ) ) {
		$campaigns[ $campaign_id ]['recipients'][ $email ]['opened'] = 1;
		$campaigns[ $campaign_id ]['opened'] = (int) $campaigns[ $campaign_id ]['opened'] + 1;
		update_option( 'zc_newsletter_campaigns', $campaigns );
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

	$parts = explode( '-', sanitize_text_field( wp_unslash( $_GET['zc_nl_track'] ) ) ); // phpcs:ignore
	$id    = isset( $parts[0] ) ? (int) $parts[0] : 0;
	$email = isset( $parts[1] ) ? urldecode( $parts[1] ) : '';

	if ( $id && $email ) {
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
	add_menu_page(
		__( 'خبرنامه زرین کد', 'zarincode' ),
		__( 'خبرنامه', 'zarincode' ),
		'manage_options',
		'zc-newsletter',
		'zc_newsletter_page',
		'dashicons-email-alt',
		30
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

	// حذف مخاطب.
	if ( isset( $_POST['zc_newsletter_delete'] ) && wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'zc_newsletter_delete' ) ) { // phpcs:ignore
		$index = (int) $_POST['zc_newsletter_delete'];
		$list  = zc_newsletter_subscribers();
		if ( isset( $list[ $index ] ) ) {
			unset( $list[ $index ] );
			update_option( 'zc_newsletter_subscribers', array_values( $list ) );
		}
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'مخاطب حذف شد.', 'zarincode' ) . '</p></div>';
	}

	// خروجی CSV.
	if ( isset( $_GET['zc_export'] ) && '1' === $_GET['zc_export'] ) {
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
	$list  = zc_newsletter_subscribers();
	$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	$filtered = $list;
	if ( $search ) {
		$filtered = array_filter(
			$list,
			function ( $s ) use ( $search ) {
				return false !== stripos( (string) $s['email'], $search )
					|| false !== stripos( (string) $s['mobile'], $search )
					|| false !== stripos( (string) $s['name'], $search )
					|| false !== stripos( (string) $s['telegram_id'], $search )
					|| false !== stripos( (string) $s['bale_id'], $search );
			}
		);
	}
	?>
	<div style="display:flex;flex-wrap:wrap;gap:14px;align-items:center;margin:16px 0;justify-content:space-between">
		<form method="get" style="display:flex;gap:8px">
			<input type="hidden" name="page" value="zc-newsletter">
			<input type="text" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'جستجو…', 'zarincode' ); ?>">
			<button class="button"><?php esc_html_e( 'جستجو', 'zarincode' ); ?></button>
		</form>
		<div style="display:flex;gap:8px">
			<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=zc-newsletter&zc_export=1' ) ); ?>"><?php esc_html_e( 'خروجی اکسل (CSV)', 'zarincode' ); ?></a>
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
								<button type="submit" name="zc_newsletter_delete" value="<?php echo (int) $i; ?>" class="button-link-delete"><?php esc_html_e( 'حذف', 'zarincode' ); ?></button>
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

	foreach ( zc_newsletter_subscribers() as $s ) {
		fputcsv(
			$out,
			array(
				$s['name'] ?? '',
				$s['email'] ?? '',
				$s['mobile'] ?? '',
				$s['telegram_id'] ?? '',
				$s['bale_id'] ?? '',
				$s['date'] ?? '',
			)
		);
	}

	fclose( $out );
}

/**
 * ورودی CSV.
 *
 * @param string $path مسیر فایل موقت.
 * @return string پیام.
 */
function zc_newsletter_import_csv( $path ) {
	if ( ! file_exists( $path ) ) {
		return __( 'فایل خوانده نشد.', 'zarincode' );
	}

	$content = file_get_contents( $path ); // phpcs:ignore
	// حذف BOM.
	$content = preg_replace( '/^\xEF\xBB\xBF/', '', $content );

	$lines = array_filter( array_map( 'trim', explode( "\n", $content ) ) );
	$header = null;
	$added  = 0;

	foreach ( $lines as $line ) {
		$row = str_getcsv( $line );

		if ( null === $header ) {
			$header = array_map( 'strtolower', $row );
			continue;
		}

		$data = array();
		foreach ( $header as $ci => $col ) {
			$data[ $col ] = $row[ $ci ] ?? '';
		}

		$data['mobile'] = zc_sanitize_mobile( (string) ( $data['mobile'] ?? '' ) );
		if ( empty( $data['mobile'] ) && empty( $data['email'] ) ) {
			continue;
		}

		$before = count( zc_newsletter_subscribers() );
		zc_newsletter_add( $data );
		if ( count( zc_newsletter_subscribers() ) > $before ) {
			$added++;
		}
	}

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
	$count = count( zc_newsletter_subscribers() );

	// نمایش نتیجه‌ی ارسال.
	if ( isset( $_GET['zc_nl_result'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( wp_unslash( $_GET['zc_nl_result'] ) ) . '</p></div>'; // phpcs:ignore
	}
	$zc_err = isset( $_COOKIE['zc_nl_error'] ) ? wp_unslash( $_COOKIE['zc_nl_error'] ) : '';
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
	$campaigns = get_option( 'zc_newsletter_campaigns', array() );
	$campaigns = array_reverse( $campaigns );

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
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $campaigns as $i => $c ) : ?>
				<?php
				$c = wp_parse_args(
					$c,
					array( 'date' => '', 'channel' => '', 'subject' => '', 'total' => 0, 'sent' => 0, 'failed' => 0, 'opened' => 0 )
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

	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	$count   = count( zc_newsletter_subscribers() );
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
		wp_die( __( 'دسترسی غیرمجاز.', 'zarincode' ) );
	}

	$channel       = isset( $_POST['channel'] ) ? sanitize_key( wp_unslash( $_POST['channel'] ) ) : 'sms';
	$subject       = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
	$message       = isset( $_POST['message'] ) ? wp_kses_post( wp_unslash( $_POST['message'] ) ) : '';
	$coupon_percent = isset( $_POST['coupon_percent'] ) ? max( 0, min( 100, (int) $_POST['coupon_percent'] ) ) : (int) zc_opt( 'zc_newsletter_coupon_percent', 0 );

	$sms_sent  = 0;
	$mail_sent = 0;
	$bot_sent  = 0;
	$failed    = 0;
	$total     = count( zc_newsletter_subscribers() );

	$do_sms   = in_array( $channel, array( 'sms', 'both', 'all' ), true );
	$do_mail  = in_array( $channel, array( 'email', 'both', 'all' ), true );
	$do_bot   = in_array( $channel, array( 'bot', 'all' ), true );

	// بررسی موجودی برای پیامک.
	if ( $do_sms ) {
		$sms_text = zc_sms_plain_text( $message );
		$mobiles  = array_filter( array_column( zc_newsletter_subscribers(), 'mobile' ) );
		$check    = zc_sms_check_credit( $sms_text, count( $mobiles ) );

		if ( $mobiles && ! $check['ok'] ) {
			setcookie( 'zc_nl_error', $check['message'], 0, COOKIEPATH, COOKIE_DOMAIN );
			wp_safe_redirect( admin_url( 'admin.php?page=zc-newsletter&tab=send' ) );
			exit;
		}
	}

	// ثبت کمپین.
	$campaign_id = zc_newsletter_campaign_add(
		array(
			'channel' => $channel,
			'subject' => $subject,
			'message' => $message,
			'total'   => $total,
		)
	);

	foreach ( zc_newsletter_subscribers() as $sub ) {
		$email = (string) ( $sub['email'] ?? '' );
		$mobile = (string) ( $sub['mobile'] ?? '' );

		// کد تخفیف یکتا (در صورت فعال بودن).
		$coupon = '';
		if ( $coupon_percent > 0 && $do_sms && class_exists( 'WC_Coupon' ) ) {
			$coupon = zc_newsletter_coupon_code( $coupon_percent, 14, 'NL', $email );
		}

		$vars = array(
			'name'   => (string) ( $sub['name'] ?? '' ),
			'email'  => $email,
			'mobile' => $mobile,
			'coupon' => $coupon,
		);
		$text = zc_sms_parse_vars( $message, $vars );
		$txt  = zc_sms_plain_text( $text );

		// پیامک.
		if ( $do_sms && $mobile ) {
			if ( zc_sms_dispatch( $mobile, $txt, 'newsletter' ) ) {
				$sms_sent++;
			} else {
				$failed++;
			}
			zc_newsletter_campaign_recipient( $campaign_id, $email ? $email : $mobile, $sms_sent ? 'sent' : 'failed' );
		}

		// ایمیل + پیکسل ردیابی.
		if ( $do_mail && $email ) {
			$track_url = home_url( '/?zc_nl_track=' . $campaign_id . '-' . rawurlencode( $email ) );
			$html_body = $text . '<br><img src="' . esc_url( $track_url ) . '" width="1" height="1" alt="" style="display:none">';
			$headers   = array( 'Content-Type: text/html; charset=UTF-8' );

			if ( wp_mail( $email, $subject ? $subject : get_bloginfo( 'name' ), $html_body, $headers ) ) {
				$mail_sent++;
			} else {
				$failed++;
			}
			zc_newsletter_campaign_recipient( $campaign_id, $email, 'sent' );
		}

		// ربات (تلگرام / بله به آیدی شخصی).
		if ( $do_bot ) {
			foreach ( array( 'telegram', 'bale' ) as $messenger ) {
				$handle = trim( (string) ( $sub[ $messenger . '_id' ] ?? '' ) );
				if ( ! $handle ) {
					continue;
				}
				if ( function_exists( 'zc_messenger_send_to' ) ) {
					$ok = zc_messenger_send_to( $messenger, '@' . ltrim( $handle, '@' ), $txt );
					if ( $ok ) {
						$bot_sent++;
					} else {
						$failed++;
					}
				}
			}
		}
	}

	// به‌روزرسانی آمار کمپین.
	$campaigns = get_option( 'zc_newsletter_campaigns', array() );
	if ( isset( $campaigns[ $campaign_id ] ) ) {
		$campaigns[ $campaign_id ]['sent']   = $sms_sent + $mail_sent + $bot_sent;
		$campaigns[ $campaign_id ]['failed'] = $failed;
		update_option( 'zc_newsletter_campaigns', $campaigns );
	}

	setcookie( 'zc_nl_error', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );

	$result_msg = sprintf(
		__( 'ارسال انجام شد. پیامک: %1$d، ایمیل: %2$d، ربات: %3$d، خطا: %4$d', 'zarincode' ),
		$sms_sent,
		$mail_sent,
		$bot_sent,
		$failed
	);

	wp_safe_redirect(
		add_query_arg(
			'zc_nl_result',
			rawurlencode( $result_msg ),
			admin_url( 'admin.php?page=zc-newsletter&tab=report' )
		)
	);
	exit;
}
add_action( 'admin_post_zc_newsletter_send', 'zc_newsletter_handle_send' );
