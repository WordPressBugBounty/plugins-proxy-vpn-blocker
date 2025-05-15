<?php
/**
 * Ajax handler for fetching updated stats.
 *
 * @package Proxy & VPN Blocker
 */

/**
 * Generate Cleaner looking big numbers.
 *
 * @param type $n input number.
 */
function number_format_short( $n ) {
	// first strip any formatting.
	$n = ( 0 + str_replace( ',', '', $n ) );

	// is this a number?
	if ( ! is_numeric( $n ) ) {
		return false;
	}

	// now filter it.
	if ( $n > 1000000000000 ) {
		return round( ( $n / 1000000000000 ), 3 ) . ' Trillion';
	} elseif ( $n > 1000000000 ) {
		return round( ( $n / 1000000000 ), 3 ) . ' Billion';
	} elseif ( $n > 1000000 ) {
		return round( ( $n / 1000000 ), 3 ) . ' Million';
	}

	return number_format( $n );
}

/**
 * Get stats from the proxycheck.io Dashboard API.
 */
function pvb_get_proxycheck_api_key_stats() {
	$get_api_key = get_option( 'pvb_proxycheckio_API_Key_field' );
	if ( ! empty( $get_api_key ) ) {
		// Build page HTML.
		$request_args  = array(
			'timeout'     => '10',
			'blocking'    => true,
			'httpversion' => '1.1',
		);
		$request_usage = wp_remote_get( 'https://proxycheck.io/dashboard/export/usage/?key=' . $get_api_key, $request_args );
		$api_key_usage = json_decode( wp_remote_retrieve_body( $request_usage ) );

		return $api_key_usage;
	}
}

add_action( 'wp_ajax_pvb_refresh_stats', 'pvb_refresh_stats' );

/**
 * Ajax handler for fetching updated stats.
 *
 * @return void
 */
function pvb_refresh_stats() {
	// Verify nonce.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'pvb_stat_refresh_ajax_nonce' ) ) {
		wp_send_json_error( 'Invalid nonce' );
		return;
	}

	$api_key_usage = pvb_get_proxycheck_api_key_stats();

	if ( ! $api_key_usage ) {
		wp_send_json_error( 'No data' );
	}

	// Prepare just the data you want to return as JSON.
	$response = array(
		'queries_today'    => number_format_short( $api_key_usage->{'Queries Today'} ),
		'daily_limit'      => number_format_short( $api_key_usage->{'Daily Limit'} ),
		'queries_lifetime' => number_format_short( $api_key_usage->{'Queries Total'} ),
		'burst_used'       => $api_key_usage->{'Burst Token Allowance'} - $api_key_usage->{'Burst Tokens Available'},
		'burst_total'      => $api_key_usage->{'Burst Token Allowance'},
		// Add anything else you need here.
	);

	wp_send_json_success( $response );
	wp_die();
}
