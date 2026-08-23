<?php
/**
 * درون‌ریزی شماره تلفن و ارسال پیامک انبوه
 *
 * فایل اکسل (CSV یا XLSX) را می‌خواند، شماره‌ها را استخراج و پاکسازی
 * می‌کند، و امکان ارسال گروهی با پشتیبانی کامل از پترن را می‌دهد.
 *
 * برای XLSX از خواندن مستقیم بسته‌ی ZIP و XML استفاده می‌شود تا به
 * کتابخانه‌ی سنگین PhpSpreadsheet نیازی نباشد.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * نام جدول مخاطبان.
 *
 * @return string
 */
function zc_contacts_table() {
	global $wpdb;

	return $wpdb->prefix . 'zc_contacts';
}

/**
 * ساخت جدول مخاطبان.
 *
 * @return void
 */
function zc_create_contacts_table() {
	global $wpdb;

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$charset = $wpdb->get_charset_collate();
	$table   = zc_contacts_table();

	$sql = "CREATE TABLE {$table} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		mobile VARCHAR(20) NOT NULL,
		name VARCHAR(120) NOT NULL DEFAULT '',
		field1 VARCHAR(190) NOT NULL DEFAULT '',
		field2 VARCHAR(190) NOT NULL DEFAULT '',
		list_name VARCHAR(120) NOT NULL DEFAULT '',
		status VARCHAR(20) NOT NULL DEFAULT 'active',
		last_sent DATETIME NULL DEFAULT NULL,
		created_at DATETIME NULL DEFAULT NULL,
		PRIMARY KEY (id),
		UNIQUE KEY mobile_list (mobile, list_name),
		KEY list_name (list_name),
		KEY status (status)
	) {$charset};";

	dbDelta( $sql );
}

/* ==========================================================================
   خواندن فایل
   ========================================================================== */

/**
 * استخراج سطرها از فایل CSV.
 *
 * @param string $path مسیر فایل.
 * @return array
 */
function zc_read_csv_rows( $path ) {
	$rows = array();

	$handle = fopen( $path, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions

	if ( ! $handle ) {
		return $rows;
	}

	/*
	 * اکسل فارسی معمولاً فایل را با BOM ذخیره می‌کند؛ اگر حذف نشود
	 * نخستین ستون با نویسه‌ی نامرئی شروع می‌شود و تشخیص ستون شماره
	 * شکست می‌خورد.
	 */
	$bom = fread( $handle, 3 ); // phpcs:ignore WordPress.WP.AlternativeFunctions

	if ( "\xEF\xBB\xBF" !== $bom ) {
		rewind( $handle );
	}

	while ( false !== ( $data = fgetcsv( $handle, 4096 ) ) ) {
		$rows[] = $data;

		if ( count( $rows ) > 20000 ) {
			break;
		}
	}

	fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions

	return $rows;
}

/**
 * استخراج سطرها از فایل XLSX.
 *
 * XLSX در واقع یک بسته‌ی ZIP حاوی XML است. رشته‌ها در
 * sharedStrings.xml و ساختار جدول در sheet1.xml قرار دارد.
 *
 * @param string $path مسیر فایل.
 * @return array
 */
function zc_read_xlsx_rows( $path ) {
	if ( ! class_exists( 'ZipArchive' ) ) {
		return array();
	}

	$zip = new ZipArchive();

	if ( true !== $zip->open( $path ) ) {
		return array();
	}

	// جدول رشته‌های مشترک.
	$shared = array();
	$xml    = $zip->getFromName( 'xl/sharedStrings.xml' );

	if ( $xml ) {
		$prev = libxml_use_internal_errors( true );
		$sx   = simplexml_load_string( $xml );
		libxml_use_internal_errors( $prev );

		if ( $sx ) {
			foreach ( $sx->si as $si ) {
				// متن ممکن است تکه‌تکه در چند <t> باشد.
				$text = '';

				if ( isset( $si->t ) ) {
					$text = (string) $si->t;
				} elseif ( isset( $si->r ) ) {
					foreach ( $si->r as $r ) {
						$text .= (string) $r->t;
					}
				}

				$shared[] = $text;
			}
		}
	}

	// نخستین کاربرگ.
	$sheet = $zip->getFromName( 'xl/worksheets/sheet1.xml' );

	$zip->close();

	if ( ! $sheet ) {
		return array();
	}

	$prev = libxml_use_internal_errors( true );
	$sx   = simplexml_load_string( $sheet );
	libxml_use_internal_errors( $prev );

	if ( ! $sx ) {
		return array();
	}

	$rows = array();

	foreach ( $sx->sheetData->row as $row ) {
		$cells = array();

		foreach ( $row->c as $c ) {
			$value = (string) $c->v;
			$type  = (string) $c['t'];

			// t="s" یعنی مقدار، اندیس جدول رشته‌های مشترک است.
			if ( 's' === $type && isset( $shared[ (int) $value ] ) ) {
				$value = $shared[ (int) $value ];
			} elseif ( 'inlineStr' === $type ) {
				$value = (string) $c->is->t;
			}

			$cells[] = $value;
		}

		$rows[] = $cells;

		if ( count( $rows ) > 20000 ) {
			break;
		}
	}

	return $rows;
}

/**
 * یافتن شماره‌ی موبایل در یک سطر.
 *
 * ستون شماره می‌تواند هر جایی باشد؛ به‌جای تکیه بر ترتیب ستون‌ها،
 * تمام سلول‌ها را می‌سنجیم و نخستین شماره‌ی معتبر را برمی‌داریم.
 *
 * @param array $row سطر.
 * @return string
 */
function zc_row_find_mobile( $row ) {
	foreach ( $row as $cell ) {
		$candidate = zc_sanitize_mobile( zc_en_num( (string) $cell ) );

		if ( preg_match( '/^09\d{9}$/', (string) $candidate ) ) {
			return $candidate;
		}
	}

	return '';
}

/**
 * درون‌ریزی سطرها در جدول مخاطبان.
 *
 * @param array  $rows سطرها.
 * @param string $list نام فهرست.
 * @return array آمار.
 */
function zc_import_contacts( $rows, $list = '' ) {
	global $wpdb;

	$table = zc_contacts_table();
	$list  = sanitize_text_field( $list );

	$added = 0;
	$dup   = 0;
	$bad   = 0;

	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$mobile = zc_row_find_mobile( $row );

		if ( ! $mobile ) {
			$bad++;
			continue;
		}

		/*
		 * نام و فیلدهای اضافی: نخستین سلول‌هایی که شماره نیستند و
		 * متن دارند، به‌ترتیب به name، field1 و field2 می‌روند تا در
		 * پترن پیامک قابل استفاده باشند.
		 */
		$texts = array();

		foreach ( $row as $cell ) {
			$cell = trim( (string) $cell );

			if ( '' === $cell ) {
				continue;
			}

			if ( zc_sanitize_mobile( zc_en_num( $cell ) ) === $mobile ) {
				continue;
			}

			$texts[] = $cell;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$ok = $wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"INSERT IGNORE INTO {$table} (mobile, name, field1, field2, list_name, status, created_at)
				 VALUES (%s, %s, %s, %s, %s, 'active', %s)",
				$mobile,
				$texts[0] ?? '',
				$texts[1] ?? '',
				$texts[2] ?? '',
				$list,
				current_time( 'mysql' )
			)
		);

		if ( $ok ) {
			$added++;
		} else {
			$dup++;
		}
	}

	return array(
		'added'     => $added,
		'duplicate' => $dup,
		'invalid'   => $bad,
	);
}

/**
 * دریافت مخاطبان یک فهرست.
 *
 * @param string $list   نام فهرست.
 * @param int    $limit  سقف.
 * @param int    $offset آفست.
 * @return array
 */
function zc_get_contacts( $list = '', $limit = 100, $offset = 0 ) {
	global $wpdb;

	$table = zc_contacts_table();

	if ( $list ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE list_name = %s AND status = 'active' ORDER BY id ASC LIMIT %d OFFSET %d",
				$list,
				$limit,
				$offset
			)
		);
		// phpcs:enable
	}

	// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE status = 'active' ORDER BY id ASC LIMIT %d OFFSET %d",
			$limit,
			$offset
		)
	);
	// phpcs:enable
}

/**
 * فهرست‌های موجود.
 *
 * @return array
 */
function zc_contact_lists() {
	global $wpdb;

	$table = zc_contacts_table();

	// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows = $wpdb->get_results(
		"SELECT list_name, COUNT(*) AS total FROM {$table} WHERE status = 'active' GROUP BY list_name ORDER BY total DESC"
	);
	// phpcs:enable

	return $rows ? $rows : array();
}

/* ==========================================================================
   آجاکس: بارگذاری فایل
   ========================================================================== */

/**
 * دریافت فایل اکسل و درون‌ریزی شماره‌ها.
 *
 * @return void
 */
function zc_ajax_import_contacts() {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'zc_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ) );
	}

	if ( empty( $_FILES['file'] ) ) {
		wp_send_json_error( array( 'message' => __( 'فایلی انتخاب نشده است.', 'zarincode' ) ) );
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$file = $_FILES['file'];
	$name = sanitize_file_name( $file['name'] ?? '' );
	$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );

	if ( ! in_array( $ext, array( 'csv', 'xlsx', 'txt' ), true ) ) {
		wp_send_json_error( array( 'message' => __( 'فقط فایل CSV یا XLSX پذیرفته می‌شود.', 'zarincode' ) ) );
	}

	$tmp = $file['tmp_name'] ?? '';

	if ( ! $tmp || ! is_uploaded_file( $tmp ) ) {
		wp_send_json_error( array( 'message' => __( 'بارگذاری فایل ناموفق بود.', 'zarincode' ) ) );
	}

	$rows = ( 'xlsx' === $ext ) ? zc_read_xlsx_rows( $tmp ) : zc_read_csv_rows( $tmp );

	if ( ! $rows ) {
		wp_send_json_error( array( 'message' => __( 'هیچ سطری در فایل خوانده نشد.', 'zarincode' ) ) );
	}

	$list = isset( $_POST['list'] ) ? sanitize_text_field( wp_unslash( $_POST['list'] ) ) : '';

	if ( ! $list ) {
		$list = pathinfo( $name, PATHINFO_FILENAME );
	}

	$stats = zc_import_contacts( $rows, $list );

	wp_send_json_success(
		array(
			'message' => sprintf(
				/* translators: 1: افزوده 2: تکراری 3: نامعتبر */
				__( '%1$s شماره افزوده شد، %2$s تکراری و %3$s سطر بدون شماره‌ی معتبر بود.', 'zarincode' ),
				zc_fa_num( $stats['added'] ),
				zc_fa_num( $stats['duplicate'] ),
				zc_fa_num( $stats['invalid'] )
			),
			'stats'   => $stats,
			'list'    => $list,
		)
	);
}
add_action( 'wp_ajax_zc_import_contacts', 'zc_ajax_import_contacts' );

/* ==========================================================================
   آجاکس: ارسال انبوه با پترن
   ========================================================================== */

/**
 * ارسال پیامک انبوه به مخاطبان درون‌ریزی‌شده.
 *
 * ارسال تکه‌تکه انجام می‌شود تا در سایت‌های با هزاران مخاطب، اجرا با
 * محدودیت زمانی PHP قطع نشود؛ مرورگر تا پایان کار درخواست‌ها را
 * پشت‌سرهم می‌فرستد.
 *
 * @return void
 */
function zc_ajax_send_contacts_sms() {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'zc_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ) );
	}

	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	$list    = isset( $_POST['list'] ) ? sanitize_text_field( wp_unslash( $_POST['list'] ) ) : '';
	$offset  = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;
	$chunk   = isset( $_POST['chunk'] ) ? max( 1, min( 100, absint( $_POST['chunk'] ) ) ) : 25;
	$percent = isset( $_POST['percent'] ) ? absint( $_POST['percent'] ) : 0;
	$days    = isset( $_POST['days'] ) ? absint( $_POST['days'] ) : 30;

	if ( '' === trim( $message ) ) {
		wp_send_json_error( array( 'message' => __( 'متن پیام را بنویسید.', 'zarincode' ) ) );
	}

	$contacts = zc_get_contacts( $list, $chunk, $offset );

	if ( ! $contacts ) {
		wp_send_json_success(
			array(
				'done'    => true,
				'offset'  => $offset,
				'message' => __( 'ارسال به پایان رسید.', 'zarincode' ),
			)
		);
	}

	global $wpdb;

	$sent = 0;

	foreach ( $contacts as $c ) {
		$code = '';

		/*
		 * اگر درصد تخفیف تعیین شده و مخاطب کاربر سایت است، کد
		 * اختصاصی ساخته می‌شود؛ برای شماره‌های بدون حساب، کد
		 * ووکامرسی معنا ندارد و شناسه خالی می‌ماند.
		 */
		if ( $percent > 0 ) {
			$user = get_users(
				array(
					'meta_key'   => 'zc_mobile', // phpcs:ignore WordPress.DB.SlowDBQuery
					'meta_value' => $c->mobile,  // phpcs:ignore WordPress.DB.SlowDBQuery
					'number'     => 1,
					'fields'     => 'ID',
				)
			);

			if ( $user ) {
				$code = zc_reward_create_coupon( (int) $user[0], $percent, $days, 'SMS' );
			}
		}

		$text = zc_sms_parse(
			$message,
			array(
				'{name}'    => $c->name,
				'{mobile}'  => $c->mobile,
				'{field1}'  => $c->field1,
				'{field2}'  => $c->field2,
				'{code}'    => $code,
				'{percent}' => zc_fa_num( $percent ),
				'{days}'    => zc_fa_num( $days ),
			)
		);

		if ( zc_sms_dispatch( $c->mobile, $text, 'bulk_import' ) ) {
			$sent++;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->update(
				zc_contacts_table(),
				array( 'last_sent' => current_time( 'mysql' ) ),
				array( 'id' => $c->id ),
				array( '%s' ),
				array( '%d' )
			);
		}
	}

	wp_send_json_success(
		array(
			'done'    => count( $contacts ) < $chunk,
			'sent'    => $sent,
			'offset'  => $offset + count( $contacts ),
			'message' => sprintf(
				/* translators: %s: تعداد */
				__( '%s پیامک در این بخش ارسال شد.', 'zarincode' ),
				zc_fa_num( $sent )
			),
		)
	);
}
add_action( 'wp_ajax_zc_send_contacts_sms', 'zc_ajax_send_contacts_sms' );

/**
 * حذف یک فهرست مخاطب.
 *
 * @return void
 */
function zc_ajax_delete_contact_list() {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'zc_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ) );
	}

	global $wpdb;

	$list = isset( $_POST['list'] ) ? sanitize_text_field( wp_unslash( $_POST['list'] ) ) : '';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$deleted = $wpdb->delete( zc_contacts_table(), array( 'list_name' => $list ), array( '%s' ) );

	wp_send_json_success(
		array(
			'message' => sprintf(
				/* translators: %s: تعداد */
				__( '%s مخاطب حذف شد.', 'zarincode' ),
				zc_fa_num( (int) $deleted )
			),
		)
	);
}
add_action( 'wp_ajax_zc_delete_contact_list', 'zc_ajax_delete_contact_list' );

/* ==========================================================================
   صفحه‌ی پیشخوان: مخاطبان و ارسال انبوه
   ========================================================================== */

/**
 * ثبت زیرمنوی مخاطبان.
 *
 * @return void
 */
function zc_register_contacts_page() {
	add_submenu_page(
		'zarincode',
		__( 'مخاطبان و پیامک انبوه', 'zarincode' ),
		__( 'مخاطبان و پیامک انبوه', 'zarincode' ),
		'manage_options',
		'zarincode-contacts',
		'zc_admin_contacts_page'
	);
}
add_action( 'admin_menu', 'zc_register_contacts_page', 22 );

/**
 * خروجی صفحه‌ی مخاطبان.
 *
 * @return void
 */
function zc_admin_contacts_page() {
	$lists = zc_contact_lists();
	$total = 0;

	foreach ( $lists as $l ) {
		$total += (int) $l->total;
	}
	?>
	<div class="wrap zc-admin-wrap zc-contacts">
		<?php zc_admin_notice_anchor(); ?>
		<h1><?php esc_html_e( 'مخاطبان و ارسال پیامک انبوه', 'zarincode' ); ?></h1>

		<p class="description" style="max-width:760px;line-height:2">
			<?php esc_html_e( 'فایل اکسل (XLSX) یا CSV خود را بارگذاری کنید. لازم نیست ستون شماره در جای خاصی باشد؛ سامانه هر سلولی را که شماره‌ی موبایل معتبر ایران باشد پیدا می‌کند و بقیه‌ی ستون‌ها را به‌ترتیب در شناسه‌های {name}، {field1} و {field2} می‌گذارد.', 'zarincode' ); ?>
		</p>

		<div class="zc-cn-grid">

			<!-- بارگذاری -->
			<div class="zc-cn-box">
				<h2><?php esc_html_e( '۱) درون‌ریزی شماره‌ها', 'zarincode' ); ?></h2>

				<table class="form-table">
					<tr>
						<th><label for="zc-cn-file"><?php esc_html_e( 'فایل اکسل یا CSV', 'zarincode' ); ?></label></th>
						<td>
							<input type="file" id="zc-cn-file" accept=".csv,.xlsx,.txt">
							<p class="description"><?php esc_html_e( 'حداکثر ۲۰٬۰۰۰ سطر در هر فایل.', 'zarincode' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="zc-cn-list"><?php esc_html_e( 'نام فهرست', 'zarincode' ); ?></label></th>
						<td>
							<input type="text" id="zc-cn-list" class="regular-text"
								placeholder="<?php esc_attr_e( 'مثلاً مشتریان مهر ۱۴۰۴', 'zarincode' ); ?>">
							<p class="description"><?php esc_html_e( 'خالی بگذارید تا نام فایل استفاده شود.', 'zarincode' ); ?></p>
						</td>
					</tr>
				</table>

				<button type="button" class="button button-primary" id="zc-cn-import">
					<?php esc_html_e( 'بارگذاری و درون‌ریزی', 'zarincode' ); ?>
				</button>

				<span class="zc-cn-msg" id="zc-cn-import-msg"></span>
			</div>

			<!-- فهرست‌ها -->
			<div class="zc-cn-box">
				<h2>
					<?php esc_html_e( 'فهرست‌های موجود', 'zarincode' ); ?>
					<span class="zc-cn-total"><?php echo esc_html( zc_fa_num( $total ) ); ?></span>
				</h2>

				<?php if ( ! $lists ) : ?>
					<p class="description"><?php esc_html_e( 'هنوز مخاطبی درون‌ریزی نشده است.', 'zarincode' ); ?></p>
				<?php else : ?>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'نام فهرست', 'zarincode' ); ?></th>
								<th style="width:110px"><?php esc_html_e( 'تعداد', 'zarincode' ); ?></th>
								<th style="width:90px"></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $lists as $l ) : ?>
								<tr>
									<td><?php echo esc_html( $l->list_name ? $l->list_name : __( '(بدون نام)', 'zarincode' ) ); ?></td>
									<td><?php echo esc_html( zc_fa_num( $l->total ) ); ?></td>
									<td>
										<button type="button" class="button-link delete zc-cn-del"
											data-list="<?php echo esc_attr( $l->list_name ); ?>">
											<?php esc_html_e( 'حذف', 'zarincode' ); ?>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</div>

		<!-- ارسال -->
		<div class="zc-cn-box" style="margin-top:18px">
			<h2><?php esc_html_e( '۲) ارسال پیامک انبوه', 'zarincode' ); ?></h2>

			<table class="form-table">
				<tr>
					<th><label for="zc-cn-send-list"><?php esc_html_e( 'ارسال به فهرست', 'zarincode' ); ?></label></th>
					<td>
						<select id="zc-cn-send-list">
							<option value=""><?php esc_html_e( 'همه‌ی مخاطبان', 'zarincode' ); ?></option>
							<?php foreach ( $lists as $l ) : ?>
								<option value="<?php echo esc_attr( $l->list_name ); ?>">
									<?php echo esc_html( $l->list_name . ' (' . zc_fa_num( $l->total ) . ')' ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="zc-cn-percent"><?php esc_html_e( 'درصد تخفیف', 'zarincode' ); ?></label></th>
					<td>
						<input type="number" id="zc-cn-percent" value="0" min="0" max="100" style="width:90px">
						<p class="description">
							<?php esc_html_e( 'صفر یعنی بدون کد. برای شماره‌هایی که حساب کاربری دارند، کد اختصاصی ساخته و در {code} گذاشته می‌شود.', 'zarincode' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th><label for="zc-cn-days"><?php esc_html_e( 'اعتبار کد (روز)', 'zarincode' ); ?></label></th>
					<td><input type="number" id="zc-cn-days" value="30" min="1" style="width:90px"></td>
				</tr>
				<tr>
					<th><label for="zc-cn-msg-text"><?php esc_html_e( 'متن پیام', 'zarincode' ); ?></label></th>
					<td>
						<textarea id="zc-cn-msg-text" rows="5" class="large-text"><?php
							echo esc_textarea( __( "{name} عزیز، پیشنهاد ویژه‌ی {site}\nکد تخفیف {percent}٪ شما: {code}\nاعتبار {days} روز", 'zarincode' ) );
						?></textarea>

						<p class="description">
							<strong><?php esc_html_e( 'شناسه‌های قابل استفاده:', 'zarincode' ); ?></strong>
							<code>{name}</code> <code>{mobile}</code> <code>{field1}</code>
							<code>{field2}</code> <code>{code}</code> <code>{percent}</code>
							<code>{days}</code> <code>{site}</code> <code>{url}</code>
						</p>
					</td>
				</tr>
			</table>

			<button type="button" class="button button-primary" id="zc-cn-send">
				<?php esc_html_e( 'شروع ارسال انبوه', 'zarincode' ); ?>
			</button>

			<div class="zc-cn-progress" id="zc-cn-progress" hidden>
				<div class="zc-cn-progress__bar"><span></span></div>
				<span class="zc-cn-progress__txt"></span>
			</div>

			<span class="zc-cn-msg" id="zc-cn-send-msg"></span>
		</div>
	</div>

	<style>
		.zc-cn-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-top:16px}
		.zc-cn-box{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:16px 20px}
		.zc-cn-box h2{margin-top:0;font-size:15px;display:flex;align-items:center;gap:9px}
		.zc-cn-total{background:#C9A227;color:#fff;border-radius:20px;padding:1px 11px;font-size:12px}
		.zc-cn-msg{display:inline-block;margin-inline-start:11px;font-size:13px}
		.zc-cn-msg.ok{color:#1B7A45}
		.zc-cn-msg.err{color:#B32D2E}
		.zc-cn-progress{margin-top:14px}
		.zc-cn-progress__bar{height:10px;background:#eef1f5;border-radius:10px;overflow:hidden}
		.zc-cn-progress__bar span{display:block;height:100%;width:0;background:linear-gradient(90deg,#C9A227,#F5D061);transition:width .3s}
		.zc-cn-progress__txt{font-size:12.5px;color:#555;display:inline-block;margin-top:6px}
		@media (max-width:1100px){.zc-cn-grid{grid-template-columns:1fr}}
	</style>

	<script>
	(function () {
		'use strict';

		var AJAX = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
		var NONCE = <?php echo wp_json_encode( wp_create_nonce( 'zc_admin_nonce' ) ); ?>;

		function post(data) {
			data.append('nonce', NONCE);
			return fetch(AJAX, { method: 'POST', body: data, credentials: 'same-origin' })
				.then(function (r) { return r.json(); });
		}

		function msg(el, text, ok) {
			el.textContent = text;
			el.className = 'zc-cn-msg ' + (ok ? 'ok' : 'err');
		}

		// درون‌ریزی
		var imp = document.getElementById('zc-cn-import');

		if (imp) {
			imp.addEventListener('click', function () {
				var file = document.getElementById('zc-cn-file').files[0];
				var out = document.getElementById('zc-cn-import-msg');

				if (!file) { msg(out, 'ابتدا فایل را انتخاب کنید.', false); return; }

				var fd = new FormData();
				fd.append('action', 'zc_import_contacts');
				fd.append('file', file);
				fd.append('list', document.getElementById('zc-cn-list').value);

				imp.disabled = true;
				msg(out, 'در حال خواندن فایل…', true);

				post(fd).then(function (res) {
					imp.disabled = false;
					msg(out, res.data.message, res.success);

					if (res.success) { setTimeout(function () { location.reload(); }, 1400); }
				}).catch(function () {
					imp.disabled = false;
					msg(out, 'خطا در ارتباط با سرور', false);
				});
			});
		}

		// حذف فهرست
		document.querySelectorAll('.zc-cn-del').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (!window.confirm('همه‌ی مخاطبان این فهرست حذف شوند؟')) { return; }

				var fd = new FormData();
				fd.append('action', 'zc_delete_contact_list');
				fd.append('list', btn.dataset.list);

				post(fd).then(function () { location.reload(); });
			});
		});

		// ارسال انبوه تکه‌تکه
		var send = document.getElementById('zc-cn-send');

		if (send) {
			send.addEventListener('click', function () {
				var out = document.getElementById('zc-cn-send-msg');
				var text = document.getElementById('zc-cn-msg-text').value;

				if (!text.trim()) { msg(out, 'متن پیام را بنویسید.', false); return; }

				if (!window.confirm('ارسال پیامک انبوه آغاز شود؟')) { return; }

				var box = document.getElementById('zc-cn-progress');
				var bar = box.querySelector('span');
				var txt = box.querySelector('.zc-cn-progress__txt');

				box.hidden = false;
				send.disabled = true;

				var offset = 0;
				var totalSent = 0;

				/*
				 * ارسال به‌صورت تکه‌ای انجام می‌شود تا اجرای PHP با
				 * محدودیت زمانی قطع نشود؛ هر پاسخ، آفست بعدی را می‌دهد.
				 */
				function step() {
					var fd = new FormData();
					fd.append('action', 'zc_send_contacts_sms');
					fd.append('message', text);
					fd.append('list', document.getElementById('zc-cn-send-list').value);
					fd.append('percent', document.getElementById('zc-cn-percent').value);
					fd.append('days', document.getElementById('zc-cn-days').value);
					fd.append('offset', offset);
					fd.append('chunk', 25);

					post(fd).then(function (res) {
						if (!res.success) {
							send.disabled = false;
							msg(out, res.data.message, false);
							return;
						}

						totalSent += (res.data.sent || 0);
						offset = res.data.offset;

						txt.textContent = 'ارسال‌شده: ' + totalSent;
						bar.style.width = Math.min(100, (offset % 500) / 5) + '%';

						if (res.data.done) {
							bar.style.width = '100%';
							send.disabled = false;
							msg(out, 'ارسال کامل شد. مجموع: ' + totalSent, true);
							return;
						}

						step();
					}).catch(function () {
						send.disabled = false;
						msg(out, 'خطا در ارتباط با سرور', false);
					});
				}

				step();
			});
		}
	}());
	</script>
	<?php
}
