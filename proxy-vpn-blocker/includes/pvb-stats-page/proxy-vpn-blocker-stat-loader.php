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
 * Get stats from the proxycheck.io Dashboard API.
 */
function pvb_get_proxycheck_month_stats() {
	$get_api_key = get_option( 'pvb_proxycheckio_API_Key_field' );
	if ( ! empty( $get_api_key ) ) {
		// Build page HTML.
		$request_args    = array(
			'timeout'     => '10',
			'blocking'    => true,
			'httpversion' => '1.1',
		);
		$request_stats   = wp_remote_get( 'https://proxycheck.io/dashboard/export/queries/?json=1&key=' . $get_api_key, $request_args );
		$api_month_stats = json_decode( wp_remote_retrieve_body( $request_stats ) );

		return $api_month_stats;
	}
}

add_action( 'wp_ajax_pvb_fetch_apigraph', 'pvb_fetch_apigraph' );

/**
 * Function to process the proxycheck.io stats json response into a format that amcharts can use
 */
function pvb_fetch_apigraph() {
	if (
		! isset( $_POST['nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'pvb_apigraph_ajax_nonce' )
	) {
		wp_send_json_error( 'Invalid nonce' );
		wp_die();
	}

	$api_key_stats = pvb_get_proxycheck_month_stats();

	if ( isset( $api_key_stats->status ) && $api_key_stats->status === 'denied' ) {
		wp_send_json_error( 'Access denied by proxycheck.io' );
		wp_die();
	}

	$response_api_month = array();
	$date               = new DateTime( 'now', new DateTimeZone( 'America/Denver' ) );
	$datefix            = $date->add( new DateInterval( 'P1D' ) );

	foreach ( $api_key_stats as $key => $value ) {
		$proxies           = $value->proxies ?? 0;
		$vpns              = $value->vpns ?? 0;
		$undetected        = $value->undetected ?? 0;
		$disposable_emails = $value->{'disposable emails'} ?? 0;
		$reusable_emails   = $value->{'reusable emails'} ?? 0;
		$refused_queries   = $value->{'refused queries'} ?? 0;
		$custom_rules      = $value->{'custom rules'} ?? 0;
		$blacklisted       = $value->blacklisted ?? 0;

		// Calculate the total for this item.
		$row_total = $proxies + $vpns + $undetected + $disposable_emails + $reusable_emails + $refused_queries + $custom_rules + $blacklisted;

		$data = array(
			'days'              => $datefix->modify( '-1 day' )->format( 'M jS' ),
			'proxies'           => $proxies,
			'vpns'              => $vpns,
			'undetected'        => $undetected,
			'disposable emails' => $disposable_emails,
			'reusable emails'   => $reusable_emails,
			'refused queries'   => $refused_queries,
			'custom rules'      => $custom_rules,
			'blacklisted'       => $blacklisted,
			'total'             => $row_total,
		);

		$response_api_month[] = $data;
	}

	wp_send_json_success( array_reverse( $response_api_month ) );
	wp_die();
}
