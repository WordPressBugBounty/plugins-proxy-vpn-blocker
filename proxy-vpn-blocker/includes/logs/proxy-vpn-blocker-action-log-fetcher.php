<?php
/**
 * Proxy & VPN Blocker Action Log Fetcher.
 *
 * @package Proxy & VPN Blocker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

 // Hook to register the AJAX handler.
add_action( 'wp_ajax_fetch_pvb_logs', 'fetch_pvb_logs' );

/**
 * Function for Ajax Handler to handle the fetching of Proxy & VPN Blocker Action Logs.
 */
function fetch_pvb_logs() {

	// Verify nonce.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'pvb_action_logs_ajax_nonce' ) ) {
		wp_send_json_error( 'Invalid nonce' );
		return;
	}

	global $wpdb;

	// Get pagination info from AJAX request.
	$page     = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
	$per_page = 12;
	$offset   = ( $page - 1 ) * $per_page;

	// Query to fetch logs with pagination.
	$logs = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}pvb_visitor_action_log ORDER BY blocked_at DESC LIMIT %d OFFSET %d",
			$per_page,
			$offset,
		)
	);

	// Get WordPress timezone.
	$wp_timezone = wp_timezone(); // Returns a DateTimeZone object

	// Convert each log's `blocked_at` to WordPress timezone.
	foreach ( $logs as $log ) {
		// Create a DateTime object from the UTC `blocked_at` timestamp.
		$blocked_at_utc = new DateTime( $log->blocked_at, new DateTimeZone( 'UTC' ) );

		// Convert the timestamp to the WordPress timezone.
		$blocked_at_utc->setTimezone( $wp_timezone );

		// Store the converted time in a new property or overwrite the original.
		$log->blocked_at_wp = $blocked_at_utc->format( 'Y-m-d H:i:s' );
	}

	// If logs are fetched successfully, return them.
	if ( ! empty( $logs ) ) {
		// Send the logs back as a JSON response.
		wp_send_json_success( $logs );
	} else {
		wp_send_json_error( 'No logs found' );
	}

	wp_die(); // Always call wp_die() at the end of your AJAX handler.
}

/**
 * Function to clear old Action Logs.
 */
function delete_old_pvb_logs() {
	global $wpdb;

	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}pvb_visitor_action_log WHERE blocked_at < %s",
			gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
		)
	);
}

// Schedule a daily event to clean logs.
if ( ! wp_next_scheduled( 'delete_old_pvb_action_logs' ) ) {
	wp_schedule_event( time(), 'daily', 'delete_old_pvb_action_logs' );
}

add_action( 'delete_old_pvb_action_logs', 'delete_old_pvb_logs' );
