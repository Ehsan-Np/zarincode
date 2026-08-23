<?php
/**
 * چرخهٔ عمر خودکار تیکت‌ها.
 *
 * @package Zarincode
 */
defined( 'ABSPATH' ) || exit;

/** @return void */
function zc_ticket_auto_close_run() {
	$days = max( 0, (int) zc_opt( 'zc_ticket_auto_close', 7 ) );
	if ( ! zc_opt( 'zc_ticket_enable', true ) || 0 === $days ) {
		return;
	}
	$cutoff  = time() - ( $days * DAY_IN_SECONDS );
	$tickets = get_posts(
		array(
			'post_type' => 'zc_ticket', 'post_status' => 'any', 'posts_per_page' => 250,
			'fields' => 'ids', 'orderby' => 'modified', 'order' => 'ASC',
			'meta_query' => array( array( 'key' => '_zc_status', 'value' => array( 'answered', 'pending' ), 'compare' => 'IN' ) ), // phpcs:ignore
		)
	);
	foreach ( $tickets as $ticket_id ) {
		$last = get_post_meta( $ticket_id, '_zc_last_reply', true );
		$time = $last ? strtotime( $last ) : (int) get_post_modified_time( 'U', true, $ticket_id );
		if ( $time && $time <= $cutoff ) {
			update_post_meta( $ticket_id, '_zc_status', 'closed' );
			update_post_meta( $ticket_id, '_zc_auto_closed_at', time() );
			zc_add_notification(
				(int) get_post_field( 'post_author', $ticket_id ),
				__( 'بسته‌شدن خودکار تیکت', 'zarincode' ),
				sprintf( __( 'تیکت «%s» به‌دلیل بی‌پاسخ‌ماندن بسته شد و هر زمان قابل بازگشایی است.', 'zarincode' ), get_the_title( $ticket_id ) ),
				'info',
				add_query_arg( array( 'tab' => 'tickets', 'ticket' => $ticket_id ), zc_panel_url() )
			);
		}
	}
}
add_action( 'zc_ticket_daily', 'zc_ticket_auto_close_run' );

/** @return void */
function zc_ticket_schedule_lifecycle() {
	if ( ! wp_next_scheduled( 'zc_ticket_daily' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'zc_ticket_daily' );
	}
}
add_action( 'init', 'zc_ticket_schedule_lifecycle' );
