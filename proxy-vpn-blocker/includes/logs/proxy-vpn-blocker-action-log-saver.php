<?php
/**
 * Proxy & VPN Blocker Action Log Handler.
 *
 * @package Proxy & VPN Blocker
 */

 if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Function to log Proxy & VPN blocker Actions
 */
function pvb_log_action( $ip = null, $type = null, $country = null, $country_iso = null, $risk = null, $url = null ) {
	global $wpdb;

	$ip_address           = $ip;
	$detected_country     = $country;
	$detected_country_iso = $country_iso;
	$detected_type        = $type;
	$risk_score           = $risk;
	$time                 = current_datetime()->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:sP' );
	$blocked_url          = $url;

	// Safe insertion using prepared statements.
	$wpdb->query(
		$wpdb->prepare(
			"INSERT INTO {$wpdb->prefix}pvb_visitor_action_log (ip_address, detected_type, country, country_iso, risk_score, blocked_at, blocked_url) VALUES (%s, %s, %s, %s, %s, %s, %s)",
			$ip_address,
			$detected_type,
			$detected_country,
			$detected_country_iso,
			$risk_score,
			$time,
			$blocked_url
		)
	);
}
