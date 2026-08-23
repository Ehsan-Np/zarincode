<?php
/**
 * فاز رشد: مسیر یادگیری، استعلام مدرک، لایسنس، API و تحلیل رویداد.
 *
 * @package Zarincode
 */
defined( 'ABSPATH' ) || exit;

/** جداول مقیاس‌پذیر قابلیت‌های رشد. @return void */
function zc_create_growth_tables() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$wpdb->prefix}zc_newsletter_subscribers (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		email VARCHAR(190) NULL,
		mobile VARCHAR(20) NULL,
		bale_id VARCHAR(100) NULL,
		telegram_id VARCHAR(100) NULL,
		name VARCHAR(190) NULL,
		status VARCHAR(20) NOT NULL DEFAULT 'active',
		created_at DATETIME NULL DEFAULT NULL,
		PRIMARY KEY (id), UNIQUE KEY email (email), UNIQUE KEY mobile (mobile), KEY status (status)
	) {$charset};";
	dbDelta( $sql );

	$sql = "CREATE TABLE {$wpdb->prefix}zc_newsletter_campaigns (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		channel VARCHAR(20) NOT NULL DEFAULT 'sms',
		subject VARCHAR(250) NULL,
		message LONGTEXT NULL,
		total BIGINT UNSIGNED NOT NULL DEFAULT 0,
		sent BIGINT UNSIGNED NOT NULL DEFAULT 0,
		failed BIGINT UNSIGNED NOT NULL DEFAULT 0,
		opened BIGINT UNSIGNED NOT NULL DEFAULT 0,
		coupon_percent SMALLINT UNSIGNED NOT NULL DEFAULT 0,
		status VARCHAR(20) NOT NULL DEFAULT 'queued',
		created_at DATETIME NULL DEFAULT NULL,
		finished_at DATETIME NULL DEFAULT NULL,
		PRIMARY KEY (id), KEY status (status), KEY created_at (created_at)
	) {$charset};";
	dbDelta( $sql );

	$sql = "CREATE TABLE {$wpdb->prefix}zc_newsletter_recipients (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		campaign_id BIGINT UNSIGNED NOT NULL,
		recipient VARCHAR(190) NOT NULL,
		status VARCHAR(20) NOT NULL DEFAULT 'pending',
		opened TINYINT(1) NOT NULL DEFAULT 0,
		sent_at DATETIME NULL DEFAULT NULL,
		PRIMARY KEY (id), UNIQUE KEY campaign_recipient (campaign_id,recipient), KEY status (status), KEY campaign_id (campaign_id)
	) {$charset};";
	dbDelta( $sql );

	$sql = "CREATE TABLE {$wpdb->prefix}zc_certificates (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		code VARCHAR(64) NOT NULL,
		user_id BIGINT UNSIGNED NOT NULL,
		course_id BIGINT UNSIGNED NOT NULL,
		issued_at DATETIME NULL DEFAULT NULL,
		revoked TINYINT(1) NOT NULL DEFAULT 0,
		PRIMARY KEY (id), UNIQUE KEY code (code), KEY user_id (user_id), KEY course_id (course_id)
	) {$charset};";
	dbDelta( $sql );

	$sql = "CREATE TABLE {$wpdb->prefix}zc_licenses (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		license_key VARCHAR(80) NOT NULL,
		user_id BIGINT UNSIGNED NOT NULL,
		product_id BIGINT UNSIGNED NOT NULL,
		order_id BIGINT UNSIGNED NOT NULL,
		status VARCHAR(20) NOT NULL DEFAULT 'active',
		activation_limit SMALLINT UNSIGNED NOT NULL DEFAULT 1,
		activations LONGTEXT NULL,
		expires_at DATETIME NULL DEFAULT NULL,
		created_at DATETIME NULL DEFAULT NULL,
		PRIMARY KEY (id), UNIQUE KEY license_key (license_key), KEY user_id (user_id), KEY product_id (product_id), KEY order_id (order_id)
	) {$charset};";
	dbDelta( $sql );

	$sql = "CREATE TABLE {$wpdb->prefix}zc_events (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
		event VARCHAR(50) NOT NULL,
		object_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
		value DECIMAL(18,2) NOT NULL DEFAULT 0,
		visitor_hash CHAR(32) NULL,
		event_date DATE NULL,
		meta LONGTEXT NULL,
		created_at DATETIME NULL DEFAULT NULL,
		PRIMARY KEY (id), KEY event (event), KEY object_id (object_id), KEY user_id (user_id), KEY visitor_date (visitor_hash,event_date), KEY created_at (created_at)
	) {$charset};";
	dbDelta( $sql );
}

/* ---------------- خبرنامهٔ جدولی ---------------- */

/** @return bool */
function zc_newsletter_storage_ready() {
	global $wpdb;
	static $ready = null;
	if ( null !== $ready ) { return $ready; }
	$table = $wpdb->prefix . 'zc_newsletter_subscribers';
	$ready = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table; // phpcs:ignore
	return $ready;
}

/** @param array $row سطر. @return array */
function zc_newsletter_storage_format( $row ) {
	return array(
		'_id' => (int) $row['id'], 'email' => (string) $row['email'], 'mobile' => (string) $row['mobile'],
		'bale_id' => (string) $row['bale_id'], 'telegram_id' => (string) $row['telegram_id'],
		'name' => (string) $row['name'], 'date' => (string) $row['created_at'],
	);
}

/** @param int $limit تعداد. @param int $offset شروع. @param string $search جستجو. @return array */
function zc_newsletter_storage_page( $limit = 100, $offset = 0, $search = '' ) {
	global $wpdb;
	$table = $wpdb->prefix . 'zc_newsletter_subscribers';
	$limit = max( 1, min( 1000, (int) $limit ) );
	if ( $search ) {
		$like = '%' . $wpdb->esc_like( $search ) . '%';
		$rows = (array) $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE status='active' AND (email LIKE %s OR mobile LIKE %s OR name LIKE %s OR telegram_id LIKE %s OR bale_id LIKE %s) ORDER BY id DESC LIMIT %d OFFSET %d", $like, $like, $like, $like, $like, $limit, $offset ), ARRAY_A ); // phpcs:ignore
	} else {
		$rows = (array) $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE status='active' ORDER BY id DESC LIMIT %d OFFSET %d", $limit, $offset ), ARRAY_A ); // phpcs:ignore
	}
	return array_map( 'zc_newsletter_storage_format', $rows );
}

/** @param string $search جستجو. @return int */
function zc_newsletter_storage_count( $search = '' ) {
	global $wpdb;
	$table = $wpdb->prefix . 'zc_newsletter_subscribers';
	if ( $search ) {
		$like = '%' . $wpdb->esc_like( $search ) . '%';
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE status='active' AND (email LIKE %s OR mobile LIKE %s OR name LIKE %s OR telegram_id LIKE %s OR bale_id LIKE %s)", $like, $like, $like, $like, $like ) ); // phpcs:ignore
	}
	return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status='active'" ); // phpcs:ignore
}

/** @return int */
function zc_newsletter_storage_mobile_count() {
	global $wpdb;
	$table = $wpdb->prefix . 'zc_newsletter_subscribers';
	return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status='active' AND mobile IS NOT NULL AND mobile<>''" ); // phpcs:ignore
}

/** انتقال یک‌بارهٔ option قدیمی به جدول. @return void */
function zc_newsletter_storage_migrate() {
	if ( get_option( 'zc_newsletter_table_migrated' ) || ! zc_newsletter_storage_ready() ) {
		return;
	}
	$legacy = get_option( 'zc_newsletter_subscribers', array() );
	foreach ( (array) $legacy as $row ) {
		if ( is_array( $row ) ) {
			zc_newsletter_storage_add( $row );
		}
	}
	update_option( 'zc_newsletter_table_migrated', 1, false );
	// option حجیم دیگر autoload نمی‌شود و پس از اطمینان از انتقال حذف می‌شود.
	delete_option( 'zc_newsletter_subscribers' );
	delete_option( 'zc_newsletter_list' );
}
add_action( 'admin_init', 'zc_newsletter_storage_migrate', 5 );

/** انتقال کمپین‌های option قدیمی به جداول جدید. @return void */
function zc_newsletter_campaigns_migrate() {
	if ( get_option( 'zc_newsletter_campaigns_migrated' ) ) { return; }
	global $wpdb;
	$table = $wpdb->prefix . 'zc_newsletter_campaigns';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) { return; } // phpcs:ignore
	foreach ( (array) get_option( 'zc_newsletter_campaigns', array() ) as $legacy ) {
		$id = zc_newsletter_campaign_add( array(
			'channel' => $legacy['channel'] ?? 'sms', 'subject' => $legacy['subject'] ?? '', 'message' => $legacy['message'] ?? '',
			'total' => $legacy['total'] ?? 0, 'status' => 'completed',
		) );
		if ( $id ) {
			$wpdb->update( $table, array( 'sent' => (int) ( $legacy['sent'] ?? 0 ), 'failed' => (int) ( $legacy['failed'] ?? 0 ), 'created_at' => $legacy['date'] ?? current_time( 'mysql' ), 'finished_at' => $legacy['date'] ?? current_time( 'mysql' ) ), array( 'id' => $id ) ); // phpcs:ignore
			foreach ( (array) ( $legacy['recipients'] ?? array() ) as $recipient => $state ) {
				zc_newsletter_campaign_recipient( $id, $recipient, $state['status'] ?? 'sent' );
				if ( ! empty( $state['opened'] ) ) { zc_newsletter_mark_opened( $id, $recipient ); }
			}
		}
	}
	delete_option( 'zc_newsletter_campaigns' );
	update_option( 'zc_newsletter_campaigns_migrated', 1, false );
}
add_action( 'admin_init', 'zc_newsletter_campaigns_migrate', 6 );

/** @return array */
function zc_newsletter_storage_all() {
	zc_newsletter_storage_migrate();
	$out = array(); $offset = 0; $limit = 1000;
	do {
		$rows   = zc_newsletter_storage_page( $limit, $offset );
		$out    = array_merge( $out, $rows );
		$offset += count( $rows );
	} while ( count( $rows ) === $limit && $offset < 50000 );
	return $out;
}

/** @param string $email ایمیل. @param string $mobile موبایل. @return bool */
function zc_newsletter_storage_exists( $email, $mobile ) {
	global $wpdb;
	$table = $wpdb->prefix . 'zc_newsletter_subscribers';
	return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE (email IS NOT NULL AND email=%s) OR (mobile IS NOT NULL AND mobile=%s) LIMIT 1", sanitize_email( $email ), zc_sanitize_mobile( $mobile ) ) ); // phpcs:ignore
}

/** @param array $data مخاطب. @return bool */
function zc_newsletter_storage_add( $data ) {
	global $wpdb;
	if ( ! zc_newsletter_storage_ready() ) {
		return false;
	}
	$email  = sanitize_email( $data['email'] ?? '' );
	$mobile = zc_sanitize_mobile( $data['mobile'] ?? '' );
	if ( ! $email && ! $mobile ) {
		return false;
	}
	$table = $wpdb->prefix . 'zc_newsletter_subscribers';
	$wpdb->query( // phpcs:ignore
		$wpdb->prepare(
			"INSERT INTO {$table} (email,mobile,bale_id,telegram_id,name,status,created_at) VALUES (NULLIF(%s,''),NULLIF(%s,''),%s,%s,%s,'active',%s)
			 ON DUPLICATE KEY UPDATE name=VALUES(name),bale_id=VALUES(bale_id),telegram_id=VALUES(telegram_id),status='active'",
			$email, $mobile, sanitize_text_field( $data['bale_id'] ?? '' ), sanitize_text_field( $data['telegram_id'] ?? '' ),
			sanitize_text_field( $data['name'] ?? '' ), current_time( 'mysql' )
		)
	);
	return true;
}

/** @param int $id شناسه. @return bool */
function zc_newsletter_storage_delete( $id ) {
	global $wpdb;
	return (bool) $wpdb->delete( $wpdb->prefix . 'zc_newsletter_subscribers', array( 'id' => (int) $id ), array( '%d' ) ); // phpcs:ignore
}

/* ---------------- مسیرهای یادگیری ---------------- */

/** @return void */
function zc_register_learning_path_cpt() {
	register_post_type( 'zc_learning_path', array(
		'labels' => array( 'name' => __( 'مسیرهای یادگیری', 'zarincode' ), 'singular_name' => __( 'مسیر یادگیری', 'zarincode' ), 'add_new_item' => __( 'افزودن مسیر یادگیری', 'zarincode' ) ),
		'public' => true, 'has_archive' => 'learning-paths', 'rewrite' => array( 'slug' => 'learning-path' ),
		'menu_icon' => 'dashicons-randomize', 'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail' ), 'show_in_rest' => true,
	) );
}
add_action( 'init', 'zc_register_learning_path_cpt' );

/** @return void */
function zc_learning_path_metaboxes() {
	add_meta_box( 'zc-learning-path', __( 'دوره‌ها و محصول مسیر', 'zarincode' ), 'zc_learning_path_metabox', 'zc_learning_path', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'zc_learning_path_metaboxes' );

/** @param WP_Post $post پست. @return void */
function zc_learning_path_metabox( $post ) {
	wp_nonce_field( 'zc_learning_path_save', 'zc_learning_path_nonce' );
	$selected = array_map( 'intval', (array) get_post_meta( $post->ID, '_zc_path_courses', true ) );
	$product  = (int) get_post_meta( $post->ID, '_zc_path_product', true );
	$courses  = get_posts( array( 'post_type' => 'zc_course', 'post_status' => 'publish', 'posts_per_page' => 300, 'orderby' => 'title', 'order' => 'ASC' ) );
	?>
	<p><label><strong><?php esc_html_e( 'محصول ووکامرس مرتبط', 'zarincode' ); ?></strong></label><br>
	<input type="number" name="zc_path_product" value="<?php echo (int) $product; ?>" min="0" class="regular-text"></p>
	<div style="columns:2;max-height:360px;overflow:auto">
	<?php foreach ( $courses as $course ) : ?>
		<label style="display:block;margin:6px"><input type="checkbox" name="zc_path_courses[]" value="<?php echo (int) $course->ID; ?>" <?php checked( in_array( $course->ID, $selected, true ) ); ?>> <?php echo esc_html( $course->post_title ); ?></label>
	<?php endforeach; ?>
	</div>
	<?php
}

/** @param int $post_id شناسه. @return void */
function zc_learning_path_save( $post_id ) {
	if ( ! isset( $_POST['zc_learning_path_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['zc_learning_path_nonce'] ) ), 'zc_learning_path_save' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$courses = isset( $_POST['zc_path_courses'] ) ? array_values( array_unique( array_filter( array_map( 'absint', (array) $_POST['zc_path_courses'] ) ) ) ) : array(); // phpcs:ignore
	update_post_meta( $post_id, '_zc_path_courses', $courses );
	update_post_meta( $post_id, '_zc_path_product', absint( $_POST['zc_path_product'] ?? 0 ) ); // phpcs:ignore
}
add_action( 'save_post_zc_learning_path', 'zc_learning_path_save' );

/** ثبت همه دوره‌های مسیر بعد از خرید. @param int $order_id سفارش. @return void */
function zc_learning_path_on_order( $order_id ) {
	$order = function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : false;
	if ( ! $order || ! $order->is_paid() || $order->get_meta( '_zc_paths_processed', true ) ) {
		return;
	}
	$user_id = (int) $order->get_user_id();
	if ( ! $user_id ) {
		return;
	}
	$done = array();
	foreach ( $order->get_items() as $item ) {
		$paths = get_posts( array( 'post_type' => 'zc_learning_path', 'post_status' => 'publish', 'posts_per_page' => 20, 'fields' => 'ids', 'meta_key' => '_zc_path_product', 'meta_value' => $item->get_product_id() ) ); // phpcs:ignore
		foreach ( $paths as $path_id ) {
			foreach ( (array) get_post_meta( $path_id, '_zc_path_courses', true ) as $course_id ) {
				zc_enroll_user( $user_id, (int) $course_id, $order_id, 0 );
			}
			$done[] = (int) $path_id;
		}
	}
	if ( $done ) {
		$order->update_meta_data( '_zc_paths_processed', $done );
		$order->save();
	}
}
add_action( 'woocommerce_order_status_processing', 'zc_learning_path_on_order', 30 );
add_action( 'woocommerce_order_status_completed', 'zc_learning_path_on_order', 30 );

/** @return string */
function zc_learning_paths_shortcode() {
	$q = new WP_Query( array( 'post_type' => 'zc_learning_path', 'post_status' => 'publish', 'posts_per_page' => 12 ) );
	ob_start();
	echo '<div class="zc-grid zc-grid--3">';
	while ( $q->have_posts() ) {
		$q->the_post();
		$courses = (array) get_post_meta( get_the_ID(), '_zc_path_courses', true );
		echo '<article class="zc-card"><a href="' . esc_url( get_permalink() ) . '">' . zc_thumbnail( get_the_ID() ) . '<h3>' . esc_html( get_the_title() ) . '</h3><p>' . esc_html( sprintf( __( '%s دوره در این مسیر', 'zarincode' ), zc_fa_num( count( $courses ) ) ) ) . '</p></a></article>';
	}
	echo '</div>';
	wp_reset_postdata();
	return ob_get_clean();
}
add_shortcode( 'zc_learning_paths', 'zc_learning_paths_shortcode' );

/* ---------------- مدرک و استعلام ---------------- */

/** @param int $user_id کاربر. @param int $course_id دوره. @param string $code کد. @return void */
function zc_certificate_register( $user_id, $course_id, $code ) {
	global $wpdb;
	$table = $wpdb->prefix . 'zc_certificates';
	$wpdb->query( $wpdb->prepare( "INSERT INTO {$table} (code,user_id,course_id,issued_at,revoked) VALUES (%s,%d,%d,%s,0) ON DUPLICATE KEY UPDATE user_id=VALUES(user_id),course_id=VALUES(course_id)", strtoupper( $code ), $user_id, $course_id, current_time( 'mysql' ) ) ); // phpcs:ignore
}

/** انتقال تدریجی مدارک نسخه‌های قدیمی به رجیستری قابل استعلام. @return void */
function zc_certificate_migrate_legacy() {
	if ( get_option( 'zc_certificate_migration_done' ) ) { return; }
	$offset = (int) get_option( 'zc_certificate_migration_offset', 0 );
	$users  = get_users( array( 'fields' => 'ID', 'number' => 200, 'offset' => $offset, 'meta_key' => 'zc_certificates', 'meta_compare' => 'EXISTS' ) ); // phpcs:ignore
	foreach ( $users as $user_id ) {
		foreach ( (array) get_user_meta( $user_id, 'zc_certificates', true ) as $cert ) {
			if ( ! empty( $cert['code'] ) && ! empty( $cert['course_id'] ) ) {
				zc_certificate_register( $user_id, (int) $cert['course_id'], $cert['code'] );
			}
		}
	}
	if ( count( $users ) < 200 ) {
		update_option( 'zc_certificate_migration_done', 1, false ); delete_option( 'zc_certificate_migration_offset' );
	} else {
		update_option( 'zc_certificate_migration_offset', $offset + 200, false );
	}
}
add_action( 'admin_init', 'zc_certificate_migrate_legacy', 20 );

/** ایجاد صفحه استعلام برای نصب‌های در حال ارتقا. @return void */
function zc_growth_install_pages() {
	if ( get_option( 'zc_growth_pages_installed' ) ) { return; }
	if ( ! get_page_by_path( 'certificate-verification' ) ) {
		wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => __( 'استعلام گواهینامه', 'zarincode' ), 'post_name' => 'certificate-verification', 'post_content' => '[zc_certificate_verify]' ) );
	}
	update_option( 'zc_growth_pages_installed', 1, false );
}
add_action( 'admin_init', 'zc_growth_install_pages', 25 );

/** @param string $code کد. @return array|false */
function zc_certificate_verify( $code ) {
	global $wpdb;
	$code = strtoupper( sanitize_text_field( $code ) );
	if ( ! preg_match( '/^ZC-[A-Z0-9-]{6,40}$/', $code ) ) {
		return false;
	}
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}zc_certificates WHERE code=%s AND revoked=0 LIMIT 1", $code ), ARRAY_A ); // phpcs:ignore
	if ( ! $row ) {
		return false;
	}
	$user = get_userdata( (int) $row['user_id'] );
	return array( 'valid' => true, 'code' => $code, 'holder' => $user ? $user->display_name : '', 'course' => get_the_title( (int) $row['course_id'] ), 'course_id' => (int) $row['course_id'], 'issued_at' => $row['issued_at'] );
}

/** @return string */
function zc_certificate_verify_shortcode() {
	$code = isset( $_GET['certificate'] ) ? sanitize_text_field( wp_unslash( $_GET['certificate'] ) ) : ''; // phpcs:ignore
	$data = $code ? zc_certificate_verify( $code ) : false;
	ob_start(); ?>
	<form method="get" class="zc-reqform__form"><label><?php esc_html_e( 'کد گواهینامه', 'zarincode' ); ?></label><input name="certificate" value="<?php echo esc_attr( $code ); ?>" required><button class="zc-btn zc-btn--gold"><?php esc_html_e( 'استعلام', 'zarincode' ); ?></button></form>
	<?php if ( $code ) : ?><div class="zc-alert zc-alert--<?php echo $data ? 'success' : 'error'; ?>" style="margin-top:16px"><?php echo $data ? esc_html( sprintf( __( 'گواهینامه معتبر است — %1$s، دوره %2$s', 'zarincode' ), $data['holder'], $data['course'] ) ) : esc_html__( 'گواهینامه‌ای با این کد یافت نشد.', 'zarincode' ); ?></div><?php endif;
	return ob_get_clean();
}
add_shortcode( 'zc_certificate_verify', 'zc_certificate_verify_shortcode' );

/* ---------------- لایسنس محصول ---------------- */

/** @return string */
function zc_generate_license_key() {
	return 'ZC-' . strtoupper( implode( '-', str_split( wp_generate_password( 20, false, false ), 5 ) ) );
}

/** @param int $order_id سفارش. @return void */
function zc_license_on_order( $order_id ) {
	global $wpdb;
	$order = function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : false;
	if ( ! $order || ! $order->is_paid() ) {
		return;
	}
	foreach ( $order->get_items() as $item ) {
		$product_id = $item->get_product_id();
		if ( 'yes' !== get_post_meta( $product_id, '_zc_license_enabled', true ) ) {
			continue;
		}
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}zc_licenses WHERE order_id=%d AND product_id=%d", $order_id, $product_id ) ); // phpcs:ignore
		if ( $exists ) {
			continue;
		}
		$limit = max( 1, (int) get_post_meta( $product_id, '_zc_license_limit', true ) );
		$days  = max( 0, (int) get_post_meta( $product_id, '_zc_license_days', true ) );
		$key = zc_generate_license_key();
		$ok  = $wpdb->insert( $wpdb->prefix . 'zc_licenses', array( 'license_key' => $key, 'user_id' => $order->get_user_id(), 'product_id' => $product_id, 'order_id' => $order_id, 'status' => 'active', 'activation_limit' => $limit, 'activations' => '[]', 'expires_at' => $days ? wp_date( 'Y-m-d H:i:s', time() + $days * DAY_IN_SECONDS, wp_timezone() ) : null, 'created_at' => current_time( 'mysql' ) ) ); // phpcs:ignore
		if ( $ok ) {
			zc_add_notification( (int) $order->get_user_id(), __( 'لایسنس محصول صادر شد', 'zarincode' ), sprintf( __( 'لایسنس محصول «%s» در پنل شما آماده است.', 'zarincode' ), get_the_title( $product_id ) ), 'success', zc_panel_url( 'licenses' ) );
			$order->add_order_note( sprintf( __( 'لایسنس محصول %s صادر شد (کلید در پنل کاربر).', 'zarincode' ), get_the_title( $product_id ) ) );
		}
	}
	$order->save();
}
add_action( 'woocommerce_order_status_processing', 'zc_license_on_order', 35 );
add_action( 'woocommerce_order_status_completed', 'zc_license_on_order', 35 );

/** افزودن فیلدهای لایسنس به محصول. @return void */
function zc_license_product_fields() {
	add_meta_box( 'zc-license-product', __( 'لایسنس زرین کد', 'zarincode' ), function ( $post ) {
		wp_nonce_field( 'zc_license_product', 'zc_license_nonce' ); ?>
		<p><label><input type="checkbox" name="zc_license_enabled" value="yes" <?php checked( get_post_meta( $post->ID, '_zc_license_enabled', true ), 'yes' ); ?>> <?php esc_html_e( 'صدور کلید لایسنس پس از خرید', 'zarincode' ); ?></label></p>
		<p><label><?php esc_html_e( 'حد فعال‌سازی', 'zarincode' ); ?></label><input type="number" name="zc_license_limit" min="1" value="<?php echo esc_attr( get_post_meta( $post->ID, '_zc_license_limit', true ) ?: 1 ); ?>"></p>
		<p><label><?php esc_html_e( 'اعتبار (روز، صفر نامحدود)', 'zarincode' ); ?></label><input type="number" name="zc_license_days" min="0" value="<?php echo esc_attr( get_post_meta( $post->ID, '_zc_license_days', true ) ?: 0 ); ?>"></p><?php
	}, 'product', 'side' );
}
add_action( 'add_meta_boxes', 'zc_license_product_fields' );

/** @param int $post_id محصول. @return void */
function zc_license_product_save( $post_id ) {
	if ( ! isset( $_POST['zc_license_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['zc_license_nonce'] ) ), 'zc_license_product' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_zc_license_enabled', isset( $_POST['zc_license_enabled'] ) ? 'yes' : 'no' );
	update_post_meta( $post_id, '_zc_license_limit', max( 1, absint( $_POST['zc_license_limit'] ?? 1 ) ) );
	update_post_meta( $post_id, '_zc_license_days', absint( $_POST['zc_license_days'] ?? 0 ) );
}
add_action( 'save_post_product', 'zc_license_product_save' );

/** @param int $user_id کاربر. @return array */
function zc_user_licenses( $user_id = 0 ) {
	global $wpdb;
	$user_id = $user_id ?: get_current_user_id();
	return (array) $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}zc_licenses WHERE user_id=%d ORDER BY id DESC", $user_id ) ); // phpcs:ignore
}

/** @param array $tabs تب‌ها. @return array */
function zc_license_panel_tab( $tabs ) {
	$tabs['licenses'] = array( 'label' => __( 'لایسنس‌ها', 'zarincode' ), 'icon' => 'key', 'order' => 45 );
	return $tabs;
}
add_filter( 'zc_panel_tabs', 'zc_license_panel_tab' );

/* ---------------- رویداد و تحلیل ---------------- */

/** @param string $event رویداد. @param int $object_id شیء. @param float $value مقدار. @param array $meta متا. @return int|false */
function zc_track_event( $event, $object_id = 0, $value = 0, $meta = array() ) {
	global $wpdb;
	if ( ! zc_opt( 'zc_analytics_enable', true ) ) { return false; }
	$allowed = array( 'view', 'search', 'add_to_cart', 'purchase', 'course_complete', 'certificate', 'license_activate', 'lead' );
	if ( ! in_array( $event, $allowed, true ) ) {
		return false;
	}
	$visitor = md5( wp_salt( 'nonce' ) . '|' . zc_user_ip() . '|' . wp_date( 'Ymd' ) );
	$ok = $wpdb->insert( $wpdb->prefix . 'zc_events', array( 'user_id' => get_current_user_id(), 'event' => $event, 'object_id' => (int) $object_id, 'value' => (float) $value, 'visitor_hash' => $visitor, 'event_date' => wp_date( 'Y-m-d' ), 'meta' => wp_json_encode( $meta, JSON_UNESCAPED_UNICODE ), 'created_at' => current_time( 'mysql' ) ) ); // phpcs:ignore
	return $ok ? (int) $wpdb->insert_id : false;
}

/** @param string $event رویداد. @param int $object_id شناسه. @param array $meta متا. @return void */
function zc_track_unique_event( $event, $object_id, $meta = array() ) {
	if ( ! zc_opt( 'zc_analytics_enable', true ) ) { return; }
	global $wpdb;
	$visitor = md5( wp_salt( 'nonce' ) . '|' . zc_user_ip() . '|' . wp_date( 'Ymd' ) );
	$exists  = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}zc_events WHERE event=%s AND object_id=%d AND visitor_hash=%s AND event_date=%s LIMIT 1", $event, $object_id, $visitor, wp_date( 'Y-m-d' ) ) ); // phpcs:ignore
	if ( ! $exists ) { zc_track_event( $event, $object_id, 0, $meta ); }
}

/** ثبت یک view یکتا برای هر بازدیدکننده/محتوا در روز. @return void */
function zc_track_singular_view_event() {
	if ( ! is_singular() || is_preview() || is_admin() ) { return; }
	zc_track_unique_event( 'view', get_queried_object_id() );
}
add_action( 'wp', 'zc_track_singular_view_event', 30 );
add_action( 'woocommerce_add_to_cart', static function ( $cart_key, $product_id, $quantity ) { zc_track_event( 'add_to_cart', $product_id, $quantity ); }, 20, 3 );
add_action( 'zc_contact_submitted', static function () { zc_track_event( 'lead', 0, 0, array( 'source' => 'contact' ) ); } );
add_action( 'zc_request_submitted', static function ( $request_id ) { zc_track_event( 'lead', $request_id, 0, array( 'source' => 'project' ) ); } );
add_action( 'zc_course_completed', static function ( $user_id, $course_id ) { zc_track_event( 'course_complete', $course_id, 0, array( 'user_id' => $user_id ) ); }, 30, 2 );

/** @param int $order_id سفارش. @return void */
function zc_track_order_purchase_event( $order_id ) {
	$order = function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : false;
	if ( $order && $order->is_paid() && ! $order->get_meta( '_zc_purchase_event', true ) ) {
		zc_track_event( 'purchase', $order_id, (float) $order->get_total(), array( 'gateway' => $order->get_payment_method() ) );
		$order->update_meta_data( '_zc_purchase_event', '1' ); $order->save();
	}
}
add_action( 'woocommerce_payment_complete', 'zc_track_order_purchase_event', 40 );
add_action( 'woocommerce_order_status_processing', 'zc_track_order_purchase_event', 40 );
add_action( 'woocommerce_order_status_completed', 'zc_track_order_purchase_event', 40 );

/** پاک‌سازی داده خام قدیمی برای کنترل اندازه جدول و حریم خصوصی. @return void */
function zc_analytics_retention_cleanup() {
	global $wpdb;
	$view_cutoff = wp_date( 'Y-m-d H:i:s', time() - 400 * DAY_IN_SECONDS, wp_timezone() );
	$hard_cutoff = wp_date( 'Y-m-d H:i:s', time() - 3 * YEAR_IN_SECONDS, wp_timezone() );
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}zc_events WHERE event IN ('view','search') AND created_at<%s LIMIT 10000", $view_cutoff ) ); // phpcs:ignore
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}zc_events WHERE created_at<%s LIMIT 10000", $hard_cutoff ) ); // phpcs:ignore
}
add_action( 'zc_subscription_daily', 'zc_analytics_retention_cleanup', 50 );

/** @return void */
function zc_growth_analytics_menu() {
	add_submenu_page( 'zarincode', __( 'تحلیل رشد', 'zarincode' ), __( 'تحلیل رشد', 'zarincode' ), 'manage_options', 'zarincode-growth', 'zc_growth_analytics_page' );
}
add_action( 'admin_menu', 'zc_growth_analytics_menu', 25 );

/** @return void */
function zc_growth_analytics_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	global $wpdb;
	$days = isset( $_GET['days'] ) ? max( 7, min( 365, absint( $_GET['days'] ) ) ) : 30; // phpcs:ignore
	$from = wp_date( 'Y-m-d H:i:s', time() - $days * DAY_IN_SECONDS, wp_timezone() );
	$rows = $wpdb->get_results( $wpdb->prepare( "SELECT event,COUNT(*) count,COALESCE(SUM(value),0) value FROM {$wpdb->prefix}zc_events WHERE created_at >= %s GROUP BY event ORDER BY count DESC", $from ) ); // phpcs:ignore
	?>
	<div class="wrap zc-admin-wrap"><h1><?php esc_html_e( 'تحلیل رشد زرین کد', 'zarincode' ); ?></h1>
	<form method="get"><input type="hidden" name="page" value="zarincode-growth"><select name="days"><option value="7" <?php selected( $days, 7 ); ?>>7</option><option value="30" <?php selected( $days, 30 ); ?>>30</option><option value="90" <?php selected( $days, 90 ); ?>>90</option><option value="365" <?php selected( $days, 365 ); ?>>365</option></select><button class="button"><?php esc_html_e( 'اعمال', 'zarincode' ); ?></button></form>
	<table class="widefat striped" style="margin-top:16px"><thead><tr><th><?php esc_html_e( 'رویداد', 'zarincode' ); ?></th><th><?php esc_html_e( 'تعداد', 'zarincode' ); ?></th><th><?php esc_html_e( 'ارزش', 'zarincode' ); ?></th></tr></thead><tbody><?php foreach ( $rows as $row ) : ?><tr><td><?php echo esc_html( $row->event ); ?></td><td><?php echo esc_html( zc_fa_num( $row->count ) ); ?></td><td><?php echo esc_html( zc_price_text( $row->value ) ); ?></td></tr><?php endforeach; ?></tbody></table></div>
	<?php
}

/** ---------------- REST API ---------------- */
function zc_growth_rest_routes() {
	register_rest_route( 'zarincode/v1', '/me', array( 'methods' => 'GET', 'callback' => 'zc_rest_me', 'permission_callback' => static function () { return is_user_logged_in(); } ) );
	register_rest_route( 'zarincode/v1', '/certificate/(?P<code>[A-Za-z0-9-]+)', array( 'methods' => 'GET', 'callback' => static function ( $request ) { if ( function_exists( 'zc_rest_allow' ) && ! zc_rest_allow( 'certificate', 20 ) ) { return new WP_Error( 'rate_limit', __( 'تعداد درخواست بیش از حد مجاز است.', 'zarincode' ), array( 'status' => 429 ) ); } $data = zc_certificate_verify( $request['code'] ); return $data ? rest_ensure_response( $data ) : new WP_Error( 'not_found', __( 'گواهینامه یافت نشد.', 'zarincode' ), array( 'status' => 404 ) ); }, 'permission_callback' => '__return_true' ) );
	register_rest_route( 'zarincode/v1', '/license/verify', array( 'methods' => 'POST', 'callback' => 'zc_rest_license_verify', 'permission_callback' => '__return_true' ) );
	register_rest_route( 'zarincode/v1', '/notifications', array( 'methods' => 'GET', 'callback' => static function () { return rest_ensure_response( array( 'count' => zc_unread_notifications_count(), 'items' => array_slice( zc_get_notifications(), 0, 10 ) ) ); }, 'permission_callback' => static function () { return is_user_logged_in(); } ) );
}
add_action( 'rest_api_init', 'zc_growth_rest_routes' );

/** @return WP_REST_Response */
function zc_rest_me() {
	if ( function_exists( 'zc_rest_allow' ) && ! zc_rest_allow( 'me', 30 ) ) {
		return new WP_Error( 'rate_limit', __( 'تعداد درخواست بیش از حد مجاز است.', 'zarincode' ), array( 'status' => 429 ) );
	}
	$user = wp_get_current_user();
	$data = array(
		'id'            => $user->ID,
		'name'          => $user->display_name,
		'email'         => $user->user_email,
		'mobile'        => get_user_meta( $user->ID, 'zc_mobile', true ),
		'stats'         => zc_user_stats( $user->ID ),
		'certificates'  => zc_get_certificates( $user->ID ),
		'courses'       => array_map(
			static function ( $row ) {
				return array(
					'id'       => (int) $row->course_id,
					'title'    => get_the_title( $row->course_id ),
					'progress' => zc_get_course_progress( get_current_user_id(), $row->course_id ),
				);
			},
			zc_get_user_courses( $user->ID )
		),
	);
	return rest_ensure_response( $data );
}

/** @param WP_REST_Request $request درخواست. @return WP_REST_Response|WP_Error */
function zc_rest_license_verify( $request ) {
	global $wpdb;
	$rate_key = 'zc_license_rate_' . md5( zc_user_ip() );
	$rate     = (int) get_transient( $rate_key );
	if ( $rate >= 30 ) {
		return new WP_Error( 'rate_limit', __( 'تعداد درخواست بیش از حد مجاز است.', 'zarincode' ), array( 'status' => 429 ) );
	}
	set_transient( $rate_key, $rate + 1, MINUTE_IN_SECONDS );

	$key       = strtoupper( sanitize_text_field( $request->get_param( 'license_key' ) ) );
	$domain    = strtolower( preg_replace( '#^https?://#', '', sanitize_text_field( $request->get_param( 'domain' ) ) ) );
	$domain    = trim( explode( '/', $domain )[0] );
	$action    = sanitize_key( $request->get_param( 'license_action' ) ?: 'activate' );
	$timestamp = absint( $request->get_param( 'timestamp' ) );
	$signature = strtolower( sanitize_text_field( $request->get_param( 'signature' ) ) );

	if ( ! in_array( $action, array( 'activate', 'deactivate', 'check' ), true ) || ! $domain || ! filter_var( $domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME ) ) {
		return new WP_Error( 'invalid_request', __( 'دامنه یا عملیات لایسنس معتبر نیست.', 'zarincode' ), array( 'status' => 400 ) );
	}
	if ( ! $timestamp || abs( time() - $timestamp ) > 300 ) {
		return new WP_Error( 'expired_request', __( 'زمان درخواست لایسنس منقضی شده است.', 'zarincode' ), array( 'status' => 401 ) );
	}
	$expected_signature = hash_hmac( 'sha256', $action . '|' . $domain . '|' . $timestamp, $key );
	if ( ! $signature || ! hash_equals( $expected_signature, $signature ) ) {
		return new WP_Error( 'invalid_signature', __( 'امضای درخواست لایسنس معتبر نیست.', 'zarincode' ), array( 'status' => 401 ) );
	}

	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}zc_licenses WHERE license_key=%s LIMIT 1", $key ), ARRAY_A ); // phpcs:ignore
	if ( ! $row || 'active' !== $row['status'] || ( $row['expires_at'] && strtotime( $row['expires_at'] ) < time() ) ) {
		return new WP_Error( 'invalid_license', __( 'لایسنس نامعتبر یا منقضی است.', 'zarincode' ), array( 'status' => 403 ) );
	}

	$lock_name = 'zc_license_' . (int) $row['id'];
	if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 5)', $lock_name ) ) ) { // phpcs:ignore
		return new WP_Error( 'license_busy', __( 'لایسنس در حال پردازش است؛ دوباره تلاش کنید.', 'zarincode' ), array( 'status' => 409 ) );
	}

	try {
		// پس از گرفتن lock دوباره می‌خوانیم تا race بین فعال‌سازی‌ها حذف شود.
		$row         = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}zc_licenses WHERE id=%d LIMIT 1", $row['id'] ), ARRAY_A ); // phpcs:ignore
		$activations = json_decode( $row['activations'], true ) ?: array();

		if ( 'activate' === $action && ! in_array( $domain, $activations, true ) ) {
			if ( count( $activations ) >= (int) $row['activation_limit'] ) {
				return new WP_Error( 'activation_limit', __( 'حد فعال‌سازی لایسنس تکمیل شده است.', 'zarincode' ), array( 'status' => 403 ) );
			}
			$activations[] = $domain;
			zc_track_event( 'license_activate', $row['product_id'], 0, array( 'domain' => $domain ) );
		} elseif ( 'deactivate' === $action ) {
			$activations = array_values( array_diff( $activations, array( $domain ) ) );
		}

		if ( 'check' !== $action ) {
			$wpdb->update( $wpdb->prefix . 'zc_licenses', array( 'activations' => wp_json_encode( $activations ) ), array( 'id' => $row['id'] ) ); // phpcs:ignore
		}

		return rest_ensure_response( array( 'valid' => true, 'action' => $action, 'product_id' => (int) $row['product_id'], 'expires_at' => $row['expires_at'], 'activations' => count( $activations ), 'limit' => (int) $row['activation_limit'] ) );
	} finally {
		$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) ); // phpcs:ignore
	}
}

/* ---------------- زمان‌بندی سازگار با Action Scheduler ---------------- */
/** @param int $timestamp زمان. @param string $hook هوک. @param array $args آرگومان. @return mixed */
function zc_schedule_action( $timestamp, $hook, $args = array() ) {
	if ( function_exists( 'as_schedule_single_action' ) ) {
		return as_schedule_single_action( $timestamp, $hook, $args, 'zarincode', true );
	}
	if ( wp_next_scheduled( $hook, $args ) ) { return false; }
	return wp_schedule_single_event( $timestamp, $hook, $args );
}

/** یادآوری تمدید اشتراک و ساخت سفارش پرداخت؛ برداشت خودکار بدون توکن بانکی انجام نمی‌شود. @return void */
function zc_subscription_renewal_reminders( $offset = 0 ) {
	$batch_size = 500;
	$users = get_users( array( 'fields' => 'ID', 'number' => $batch_size, 'offset' => max( 0, (int) $offset ), 'orderby' => 'ID', 'order' => 'ASC', 'meta_key' => 'zc_subscription', 'meta_compare' => 'EXISTS' ) ); // phpcs:ignore
	foreach ( $users as $user_id ) {
		$rec = zc_subscription_get_user( $user_id );
		if ( empty( $rec['expires'] ) ) {
			continue;
		}
		$days = (int) ceil( ( (int) $rec['expires'] - time() ) / DAY_IN_SECONDS );
		if ( ! in_array( $days, array( 7, 3, 1 ), true ) || get_user_meta( $user_id, 'zc_sub_reminded_' . $days . '_' . wp_date( 'Ymd' ), true ) ) {
			continue;
		}
		zc_add_notification( $user_id, __( 'یادآوری تمدید اشتراک', 'zarincode' ), sprintf( __( 'اشتراک شما %s روز دیگر منقضی می‌شود.', 'zarincode' ), zc_fa_num( $days ) ), 'warning', zc_panel_url( 'subscription' ) );
		zc_notify_user( $user_id, 'subscription_expiring', sprintf( __( 'اشتراک شما %s روز دیگر منقضی می‌شود.', 'zarincode' ), $days ) );
		update_user_meta( $user_id, 'zc_sub_reminded_' . $days . '_' . wp_date( 'Ymd' ), 1 );
	}
	if ( count( $users ) === $batch_size ) {
		zc_schedule_action( time() + 20, 'zc_subscription_reminder_batch', array( (int) $offset + $batch_size ) );
	}
}
add_action( 'zc_subscription_daily', 'zc_subscription_renewal_reminders', 20 );
add_action( 'zc_subscription_reminder_batch', 'zc_subscription_renewal_reminders', 10, 1 );
