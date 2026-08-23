<?php
/**
 * مدیریت نسخه و migration دیتابیس مستقل از نسخهٔ ظاهری قالب.
 *
 * @package Zarincode
 */
defined( 'ABSPATH' ) || exit;

define( 'ZC_DB_VERSION', '3.36.2' );

/**
 * اجرای idempotent همهٔ سازنده‌های جدول.
 *
 * @return void
 */
function zc_run_database_migrations() {
	if ( get_option( 'zc_database_schema_version' ) === ZC_DB_VERSION ) {
		return;
	}

	$lock = get_transient( 'zc_database_migrating' );
	if ( $lock ) {
		return;
	}
	set_transient( 'zc_database_migrating', 1, 5 * MINUTE_IN_SECONDS );

	if ( function_exists( 'zc_create_tables' ) ) {
		zc_create_tables();
	}
	if ( function_exists( 'zc_ensure_attempts_table' ) ) {
		zc_ensure_attempts_table( true );
	}
	foreach ( array( 'zc_create_contract_chat_table', 'zc_create_service_coupon_table', 'zc_create_contacts_table', 'zc_create_growth_tables' ) as $callback ) {
		if ( function_exists( $callback ) ) {
			call_user_func( $callback );
		}
	}

	update_option( 'zc_database_schema_version', ZC_DB_VERSION, false );
	update_option( 'zc_db_version', ZC_DB_VERSION, false );
	delete_transient( 'zc_database_migrating' );
}
add_action( 'init', 'zc_run_database_migrations', 2 );
add_action( 'admin_init', 'zc_run_database_migrations', 1 );
add_action( 'after_switch_theme', 'zc_run_database_migrations', 99 );
