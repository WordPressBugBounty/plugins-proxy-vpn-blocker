<?php
/**
 * A PHP Function which checks if the IP Address specified is a Proxy Server utilising the API provided by https://proxycheck.io
 *
 * @package Proxy & VPN Blocker
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A PHP Function which checks if the IP Address specified is a Proxy Server utilising the API provided by https://proxycheck.io
 * This function is covered under a MIT License.
 *
 * @param type $visitor_ip defined in proxy-vpn-blocker-function.php as $visitor_ip_address.
 * @param type $asn_check defined in proxy-vpn-blocker-function.php as $perform_country_check.
 * @param type $raw if set to 1 outputs a raw result for PVB Debugging.
 * @param type $skip_transient if set to 1 skips the transient check and runs the proxycheck API Call anyway.
 */
function proxycheck_function( $visitor_ip, $asn_check, $raw, $skip_transient ) {

	$pvb_transient_exploded = explode( '-', get_transient( 'pvb_' . get_option( 'pvb_proxycheckio_current_key' ) . '_' . $visitor_ip ) );
	if ( false === $pvb_transient_exploded[0] ) {
		$pvb_transient_exploded[0] = 0;
	}

	if ( time() >= $pvb_transient_exploded[0] || 1 === $raw || 1 === $skip_transient ) {
		// Current time has surpassed the time we set for expirary if it existed already.
		// That means we need to check this IP with the API.

		// Applying TAG options.
		if ( empty( get_option( 'pvb_proxycheckio_Custom_TAG_field' ) ) ) {
			$protocols  = array( 'https://', 'http://', 'www.' );
			$host       = ! empty( $_SERVER['HTTP_HOST'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
			$req_uri    = ! empty( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$post_field = str_replace( $protocols, '', $host ) . $req_uri;
		} elseif ( ! empty( get_option( 'pvb_proxycheckio_Custom_TAG_field' ) ) ) {
			$post_field = get_option( 'pvb_proxycheckio_Custom_TAG_field' );
		} else {
			$post_field = '';
		}

		// Performing the API query to proxycheck.io/v3/ using WordPress HTTP API.
		$body = array(
			'tag' => $post_field,
		);

		global $wp_version;
		$args = array(
			'body'        => $body,
			'timeout'     => '5',
			'httpversion' => '1.1',
			'blocking'    => true,
			'user-agent'  => 'PVB/' . get_option( 'proxy_vpn_blocker_version' ) . '; WordPress/' . $wp_version . '; ' . home_url(),
			'headers'     => array(),
			'cookies'     => array(),
		);

		// Get and Decrypt API Key.
		$encrypted_key = get_option( 'pvb_proxycheckio_API_Key_field' );
		$get_api_key   = PVB_API_Key_Encryption::decrypt( $encrypted_key );

		// Get the Proxycheck.io API version we are using.
		$proxycheck_api_version = get_option( 'proxy_vpn_blocker_proxycheck_api_version' );

		// Perform the query.
		$response = wp_remote_post( 'https://proxycheck.io/v3/' . $visitor_ip . '?key=' . $get_api_key . '&ver=' . $proxycheck_api_version . '&rand=' . uniqid(), $args );

		// Get the response body for HMAC verification and JSON decoding.
		$response_body = wp_remote_retrieve_body( $response );

		// Decode the JSON from proxycheck.io API.
		$decoded_json = json_decode( $response_body );

		if ( ! empty( get_option( 'pvb_proxycheckio_HMAC_verification_key' ) ) ) {
			$hmac_key  = get_option( 'pvb_proxycheckio_HMAC_verification_key' );
			$hmac_hash = hash_hmac( 'sha256', $response_body, $hmac_key );

			$hmac_signature_header = wp_remote_retrieve_header( $response, 'http_x_signature' );
			if ( ! hash_equals( $hmac_hash, $hmac_signature_header ) ) {
				// Signature verification failed, exit.
				exit;
			}
			// If HMAC verification passes, continue to process the response below.
		}

		// If raw output is requested, return the object as-is.
		if ( 1 === $raw ) {
			return $decoded_json;
		}

		// Check if the JSON response is valid.
		if ( ! isset( $decoded_json->$visitor_ip ) || isset( $decoded_json->status ) && 'denied' === $decoded_json->status || 'warning' === $decoded_json->status ) {
			if ( 'on' === get_option( 'pvb_proxycheckio_Admin_Alert_Denied_Email' ) && ! get_transient( 'pvb_admin_email_denied_timeout_' . $decoded_json->status ) ) {
				// Prepare an email to sent to admin.
				$to       = get_option( 'admin_email' );
				$subject  = 'Proxy & VPN Blocker: proxycheck.io API Status: ' . $decoded_json->status . ' on ' . home_url();
				$message  = 'This is a courtesy message to tell you that Proxy & VPN Blocker on "' . home_url() . '" received the following status message from proxycheck.io when attempting to make a query to the API: ' . "\n\n";
				$message .= 'Status: ' . $decoded_json->status . "\n";
				$message .= 'Message: ' . $decoded_json->message . "\n\n";
				$message .= 'As a result, Proxy & VPN Blocker is not currently protecting your website.' . "\n\n";
				$message .= 'You can disable these emails by turning off "proxycheck.io \'denied\' status emails" in your site\'s Proxy & VPN Blocker Settings.';
				wp_mail( $to, $subject, $message );

				// Set a transient so this doesn't happen too many times.
				set_transient( 'pvb_admin_email_denied_timeout_' . $decoded_json->status, 3 * HOUR_IN_SECONDS );
			}

			// If the request to proxycheck.io was denied or malformed allow the visitor.
			if ( 'denied' === $decoded_json->status || ! isset( $decoded_json->$visitor_ip ) ) {
				// Return.
				$array = array(
					'no', // Undetected.
					'null',
					'null',
					'null',
					'null',
					'null',
					'null',
					'null',
				);
				return $array;
			}
		}

		// Define detection flags.
		$detection_flags = array( 'proxy', 'vpn', 'compromised', 'scraper', 'tor', 'hosting' );

		// 0 Check if the IP we're testing is a proxy server or not according to proxycheck.io.
		$is_detected = false;

		if ( isset( $decoded_json->$visitor_ip->detections ) ) {
			$detections = $decoded_json->$visitor_ip->detections;

			foreach ( $detection_flags as $flag ) {
				if ( isset( $detections->$flag ) && true === $detections->$flag ) {
					$is_detected = true;
					break; // Found at least one detection.
				}
			}
		}

		// Set array based on detection result.
		if ( $is_detected ) {
			$array = array( 'yes' );
		} else {
			$array = array( 'no' );
		}

		// 1 Country.
		if ( isset( $decoded_json->$visitor_ip->location->country_name ) ) {
			$array[] = $decoded_json->$visitor_ip->location->country_name;
		} else {
			$array[] = 'null';
		}

		// 2 Continent.
		if ( isset( $decoded_json->$visitor_ip->location->continent_name ) ) {
			$array[] = $decoded_json->$visitor_ip->location->continent_name;
		} else {
			$array[] = 'null';
		}

		// 3 Risk Score.
		if ( isset( $decoded_json->$visitor_ip->detections->risk ) ) {
			$array[] = $decoded_json->$visitor_ip->detections->risk;
		} else {
			$array[] = 'null';
		}

		// 4 Proxy Type.
		if ( isset( $decoded_json->$visitor_ip->detections ) ) {
			$detection_types = array();
			$detections      = $decoded_json->$visitor_ip->detections;

			foreach ( $detection_flags as $key ) {
				if ( isset( $detections->$key ) && true === $detections->$key ) {
					$detection_types[] = $key;
				}
			}

			if ( ! empty( $detection_types ) ) {
				$decoded_json->$visitor_ip->types = $detection_types;
				$array[]                          = implode( ',', $detection_types );
			} else {
				$array[] = 'null';
			}
		} else {
			$array[] = 'null';
		}

		// 5 Country isocode.
		if ( isset( $decoded_json->$visitor_ip->location->country_code ) ) {
			$array[] = $decoded_json->$visitor_ip->location->country_code;
		} else {
			$array[] = 'null';
		}

		// 6 City.
		if ( isset( $decoded_json->$visitor_ip->location->city_name ) ) {
			$array[] = $decoded_json->$visitor_ip->location->city_name;
		} else {
			$array[] = 'null';
		}

		// 7 Blocked url for local logging.
		$req_uri     = ! empty( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$blocked_url = $req_uri;
		if ( ! empty( $blocked_url ) ) {
			$array[] = $blocked_url;
		} else {
			$array[] = 'null';
		}

		return $array;

	} else {
		$array = array(
			'no', // undetected.
			'null',
			'null',
			'null',
			'null',
			'null',
			'null',
			'null',
		);

		return $array;
	}
}
