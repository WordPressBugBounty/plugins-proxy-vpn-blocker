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
 * 
 * @param string|null $ip IP address of the visitor.
 * @param string|null $type Type of detection (e.g., "proxy", "vpn", etc.).
 * @param string|null $country Detected country of the visitor.
 * @param string|null $country_iso ISO code of the detected country.
 * @param int|null    $risk Risk score associated with the visitor.
 * @param string|null $url URL that was accessed when the block occurred.
 * @param string|null $apitype The API type used for detection.
 */
function pvb_log_action( $ip = null, $type = null, $country = null, $country_iso = null, $risk = null, $url = null, $apitype = null ) {
	global $wpdb;

	/*
	 * Sanitise every caller-supplied value before storage.
	 *
	 * sanitize_text_field() strips tags, extra whitespace and invalid UTF-8,
	 * which removes the raw HTML/attribute-injection payloads that could later
	 * be reflected into the admin Action Log page.
	 *
	 * IP address: validate against IPv4/IPv6 format; reject anything else.
	 * Risk score: cast to int so a payload string cannot be stored at all.
	 * URL:        esc_url_raw() normalises the value and strips javascript: etc.
	 */

	// Validate IP – must be a valid IPv4 or IPv6 address.
	$ip_address = filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';

	// Free-text fields: strip tags and normalise whitespace.
	$detected_type        = sanitize_text_field( (string) $type );
	$detected_country     = sanitize_text_field( (string) $country );
	$detected_country_iso = sanitize_text_field( (string) $country_iso );
	$api_type             = sanitize_text_field( (string) $apitype );

	// Numeric field: cast to int so a string payload becomes 0.
	$risk_score = intval( $risk );

	// URL field: esc_url_raw strips dangerous schemes (javascript:, data: etc).
	$blocked_url = esc_url_raw( (string) $url );

	$time = current_datetime()->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:sP' );

	$block_method   = null;
	$captcha_passed = null;

	// Safe insertion using prepared statements.
	$wpdb->query(
		$wpdb->prepare(
			"INSERT INTO {$wpdb->prefix}pvb_visitor_action_log (ip_address, detected_type, country, country_iso, risk_score, blocked_at, blocked_url, block_method, captcha_passed, api_type) VALUES (%s, %s, %s, %s, %d, %s, %s, %s, %s, %s)",
			$ip_address,
			$detected_type,
			$detected_country,
			$detected_country_iso,
			$risk_score,
			$time,
			$blocked_url,
			$block_method,
			$captcha_passed,
			$api_type
		)
	);
}
