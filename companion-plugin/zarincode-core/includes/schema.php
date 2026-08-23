<?php
/**
 * طرح جداول پایدار زرین کد — مستقل از فعال بودن قالب.
 *
 * @package ZarincodeCore
 */
defined( 'ABSPATH' ) || exit;

/**
 * نصب/به‌روزرسانی جداول اصلی.
 *
 * @return void
 */
function zarincode_core_install_schema() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset = $wpdb->get_charset_collate();

	dbDelta(
		"CREATE TABLE {$wpdb->prefix}zc_transactions (
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
			created_at DATETIME NULL DEFAULT NULL,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY type (type),
			KEY status (status),
			KEY created_at (created_at)
		) {$charset};"
	);

	dbDelta(
		"CREATE TABLE {$wpdb->prefix}zc_progress (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL,
			course_id BIGINT(20) UNSIGNED NOT NULL,
			lesson_key VARCHAR(120) NOT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'completed',
			seconds INT UNSIGNED NOT NULL DEFAULT 0,
			updated_at DATETIME NULL DEFAULT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY user_lesson (user_id, course_id, lesson_key),
			KEY course_id (course_id)
		) {$charset};"
	);

	dbDelta(
		"CREATE TABLE {$wpdb->prefix}zc_enrollments (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL,
			course_id BIGINT(20) UNSIGNED NOT NULL,
			order_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			price DECIMAL(18,2) NOT NULL DEFAULT 0,
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			expire_at DATETIME NULL,
			created_at DATETIME NULL DEFAULT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY user_course (user_id, course_id),
			KEY course_id (course_id)
		) {$charset};"
	);

	dbDelta(
		"CREATE TABLE {$wpdb->prefix}zc_chats (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			session_id VARCHAR(64) NOT NULL,
			sender VARCHAR(20) NOT NULL DEFAULT 'user',
			message TEXT NOT NULL,
			is_read TINYINT(1) NOT NULL DEFAULT 0,
			status VARCHAR(20) NOT NULL DEFAULT 'open',
			created_at DATETIME NULL DEFAULT NULL,
			PRIMARY KEY (id),
			KEY session_id (session_id),
			KEY user_id (user_id),
			KEY is_read (is_read)
		) {$charset};"
	);

	dbDelta(
		"CREATE TABLE {$wpdb->prefix}zc_licenses (
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
			PRIMARY KEY (id),
			UNIQUE KEY license_key (license_key),
			KEY user_id (user_id)
		) {$charset};"
	);

	dbDelta(
		"CREATE TABLE {$wpdb->prefix}zc_certificates (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			code VARCHAR(64) NOT NULL,
			user_id BIGINT UNSIGNED NOT NULL,
			course_id BIGINT UNSIGNED NOT NULL,
			issued_at DATETIME NULL DEFAULT NULL,
			revoked TINYINT(1) NOT NULL DEFAULT 0,
			PRIMARY KEY (id),
			UNIQUE KEY code (code),
			KEY user_id (user_id)
		) {$charset};"
	);
}
