<?php
/**
 * Creates Endpoint for Proxy & VPN Blocker Stats in Settings UI.
 *
 * @package Proxy & VPN Blocker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates endpoint for month stats in admin.
 */
function endpoint_monthstat_init() {
	// route url: domain.com/wp-json/$namespace/$route.
	$namespace = 'proxy-vpn-blocker-stats/v1';
	$route     = 'month-stats';

	register_rest_route(
		$namespace,
		$route,
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'pvb_load_monthstat',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'endpoint_monthstat_init' );

/**
 * Function to process the proxycheck.io stats json response into a format that amcharts can use
 *
 * @param type $request request recieved by rest route.
 */
function pvb_load_monthstat( $request ) {
	$key = $request['key'];
	// Check if the API key is in the request and if it is the current API key, otherwise throw an error.
	if ( ! empty( $key ) && ( get_option( 'pvb_proxycheckio_API_Key_field' ) === $key ) ) {
		$request_args = array(
			'timeout'     => '10',
			'blocking'    => true,
			'httpversion' => '1.1',
		);
		// Get the months data.
		if ( 'on' === get_option( 'pvb_proxycheckio_dummy_data' ) ) {
			// Get the dummy data from the Proxy & VPN Blocker Dummy Pool.
			$request1      = file_get_contents( dirname( __DIR__ ) . '/includes/dbg/demo_data/proxycheck.monthstat.dummy.json' );
			$api_key_stats = json_decode( $request1 );
		} else {
			// Get the months data from the proxycheck dashboard API.
			$request1      = wp_remote_get( 'https://proxycheck.io/dashboard/export/queries/?json=1&key=' . $key, $request_args );
			$api_key_stats = json_decode( wp_remote_retrieve_body( $request1 ) );
		}
		if ( isset( $api_key_stats->status ) && 'denied' !== $api_key_stats->status ) {
			exit();
		} else {
			$response_api_month = array();
			$count_day          = 0;
			$date               = new DateTime( 'now', new DateTimeZone( 'America/Denver' ) );
			$datefix            = $date->add( new DateInterval( 'P1D' ) );
			foreach ( $api_key_stats as $key => $value ) {
					$data                      = array();
					$data['days']              = $datefix->modify( '-1 day' )->format( 'M jS' );
					$data['proxies']           = $value->proxies;
					$data['vpns']              = $value->vpns;
					$data['undetected']        = $value->undetected;
					$data['disposable emails'] = $value->{'disposable emails'};
					$data['reusable emails']   = $value->{'reusable emails'};
					$data['refused queries']   = $value->{'refused queries'};
					$data['custom rules']      = $value->{'custom rules'};
					$data['blacklisted']       = $value->blacklisted;
					array_push( $response_api_month, $data );
			}
			// Reverse the order of the array so that the current day is on the left.
			$reverse_order = array_reverse( $response_api_month );
			// Return the reversed array as REST response.
			return new WP_REST_Response( $reverse_order, 200 );
		}
	} else {
		$error = 'Incorrect or no API key provided.';
		// Return an error - Key not set or invalid.
		return new WP_REST_Response( array( 'error' => $error ), 400 );
	}
}
