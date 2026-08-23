<?php
/** چرخه فعال/غیرفعال‌شدن قالب و پاک‌سازی jobها. @package Zarincode */
defined( 'ABSPATH' ) || exit;

/** @return void */
function zc_unschedule_theme_jobs() {
	foreach ( array( 'zc_subscription_daily', 'zc_subscription_daily_batch', 'zc_subscription_reminder_batch', 'zc_ticket_daily', 'zc_sms_hourly', 'zc_sms_daily', 'zc_notify_cron', 'zc_backup_event', 'zc_newsletter_process_batch' ) as $hook ) {
		wp_clear_scheduled_hook( $hook );
		if ( function_exists( 'as_unschedule_all_actions' ) ) { as_unschedule_all_actions( $hook, array(), 'zarincode' ); }
	}
}
add_action( 'switch_theme', 'zc_unschedule_theme_jobs', 100 );

/** وضعیت سلامت jobهای اصلی. @return array */
function zc_job_health() {
	return array(
		'notifications' => wp_next_scheduled( 'zc_notify_cron' ) ?: 0,
		'sms_hourly' => wp_next_scheduled( 'zc_sms_hourly' ) ?: 0,
		'sms_daily' => wp_next_scheduled( 'zc_sms_daily' ) ?: 0,
		'subscription_daily' => wp_next_scheduled( 'zc_subscription_daily' ) ?: 0,
		'ticket_daily' => wp_next_scheduled( 'zc_ticket_daily' ) ?: 0,
		'backup' => wp_next_scheduled( 'zc_backup_event' ) ?: 0,
	);
}
