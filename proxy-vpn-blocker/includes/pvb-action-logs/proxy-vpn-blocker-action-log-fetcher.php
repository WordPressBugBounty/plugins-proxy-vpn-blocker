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

/**
 * Helper function to build a CSV line string (mimics fputcsv but returns a string).
 *
 * @param array  $fields    The array of fields to encode.
 * @param string $delimiter The field delimiter (default comma).
 * @param string $enclosure The field enclosure character (default double-quote).
 * @return string The encoded CSV line, including trailing newline.
 */
function pvb_fputcsv_line( $fields, $delimiter = ',', $enclosure = '"' ) {
	$output = array();
	foreach ( $fields as $value ) {
		// Enclose in quotes if the value contains delimiter, enclosure, or newline.
		if ( preg_match( '/[' . preg_quote( $delimiter . $enclosure, '/' ) . "\n\r]/", $value ) ) {
			$value = $enclosure . str_replace( $enclosure, $enclosure . $enclosure, $value ) . $enclosure;
		}
		$output[] = $value;
	}
	return implode( $delimiter, $output ) . "\n";
}

/**
 * Export action logs as CSV via AJAX.
 */
function export_pvb_logs_csv() {

	// Verify nonce.
	if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'pvb_export_csv_nonce' ) ) {
		wp_die( 'Invalid nonce' );
	}

	// Check user capabilities.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Insufficient permissions' );
	}

	global $wpdb;

	// Fetch all logs (up to 30 days, same as the cleanup cron).
	$logs = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}pvb_visitor_action_log WHERE blocked_at >= %s ORDER BY blocked_at DESC",
			gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
		)
	);

	// Set CSV headers.
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="pvb-action-log-' . gmdate( 'Y-m-d' ) . '.csv"' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );

	// Build the CSV content in memory.
	$csv_content = '';

	// UTF-8 BOM for Excel compatibility.
	$csv_content .= "\xEF\xBB\xBF";

	// CSV column headers.
	$csv_content .= pvb_fputcsv_line(
		array(
			'IP Address',
			'Detection Type',
			'Country',
			'Country ISO',
			'Risk Score',
			'Blocked URL',
			'Block Method',
			'API Type',
			'Blocked At (UTC)',
		)
	);

	// Write rows.
	if ( ! empty( $logs ) ) {
		foreach ( $logs as $log ) {
			$csv_content .= pvb_fputcsv_line(
				array(
					$log->ip_address,
					$log->detected_type,
					$log->country,
					$log->country_iso,
					$log->risk_score,
					$log->blocked_url,
					$log->block_method,
					$log->api_type,
					$log->blocked_at,
				)
			);
		}
	}

	// Output the complete CSV content.
	echo $csv_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}
add_action( 'wp_ajax_export_pvb_logs_csv', 'export_pvb_logs_csv' );
