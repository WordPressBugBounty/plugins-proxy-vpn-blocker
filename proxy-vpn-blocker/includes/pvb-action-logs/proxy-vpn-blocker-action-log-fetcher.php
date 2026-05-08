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
	$wp_timezone = wp_timezone(); // Returns a DateTimeZone object.

	// Escape all string fields before they leave PHP so the JavaScript layer
	// never receives raw attacker-controlled HTML/attribute content.
	//
	// esc_html() converts < > & " ' to their HTML entities, which prevents
	// a stored payload such as:  xx" onerror="alert(1)"
	// from being interpreted as HTML when the JS injects the value into the DOM.
	//
	// esc_url() is used for the URL column so javascript: and data: schemes are
	// stripped even if they somehow bypassed the write-time sanitisation.
	foreach ( $logs as $log ) {

		// Timezone conversion (unchanged logic).
		$blocked_at_utc = new DateTime( $log->blocked_at, new DateTimeZone( 'UTC' ) );
		$blocked_at_utc->setTimezone( $wp_timezone );
		$log->blocked_at_wp = $blocked_at_utc->format( 'Y-m-d H:i:s' );

		// Escape free-text / enum fields.
		$log->ip_address    = esc_html( $log->ip_address );
		$log->detected_type = esc_html( $log->detected_type );
		$log->country       = esc_html( $log->country );
		$log->country_iso   = esc_html( $log->country_iso );
		$log->api_type      = esc_html( $log->api_type );
		$log->block_method  = esc_html( $log->block_method );

		// Numeric field – cast to int so the JS always receives a number.
		$log->risk_score = intval( $log->risk_score );

		// URL field – strip dangerous schemes.
		$log->blocked_url = esc_url( $log->blocked_url );
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
