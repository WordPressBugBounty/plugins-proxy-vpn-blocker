<?php
/**
 * A PHP Function which checks if the IP Address specified is a Proxy Server utilising the API provided by https://proxycheck.io
 *
 * @package Proxy & VPN Blocker.
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

		// Performing the API query to proxycheck.io/v2/ using WordPress HTTP API.
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

		// Get checkbox value for VPN_Option.
		if ( 'on' === get_option( 'pvb_proxycheckio_VPN_select_box' ) ) {
			$vpn_option = 1;
		} else {
			$vpn_option = 0;
		}

		// Perform the query.
		$response = wp_remote_post( 'https://proxycheck.io/v2/' . $visitor_ip . '?key=' . get_option( 'pvb_proxycheckio_API_Key_field' ) . '&risk=1&vpn=' . $vpn_option . '&days=' . get_option( 'pvb_proxycheckio_Days_Selector' ) . '&asn=' . $asn_check, $args );

		// Decode the JSON from proxycheck.io API.
		$decoded_json = json_decode( wp_remote_retrieve_body( $response ) );

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
				);
				return $array;
			}
		}

		// 0 Check if the IP we're testing is a proxy server or not according to proxycheck.io.
		if ( 'yes' === $decoded_json->$visitor_ip->proxy ) {
			$array = array( 'yes' );
		} else {
			$array = array( 'no' );
		}

		// 1 Country.
		if ( isset( $decoded_json->$visitor_ip->country ) ) {
			$array[] = $decoded_json->$visitor_ip->country;
		} else {
			$array[] = 'null';
		}

		// 2 Continent.
		if ( isset( $decoded_json->$visitor_ip->continent ) ) {
			$array[] = $decoded_json->$visitor_ip->continent;
		} else {
			$array[] = 'null';
		}

		// 3 Risk Score.
		if ( isset( $decoded_json->$visitor_ip->risk ) ) {
			$array[] = $decoded_json->$visitor_ip->risk;
		} else {
			$array[] = 'null';
		}

		// 4 Proxy Type.
		if ( isset( $decoded_json->$visitor_ip->type ) ) {
			$array[] = $decoded_json->$visitor_ip->type;
		} else {
			$array[] = 'null';
		}

		// 5 Country isocode.
		if ( isset( $decoded_json->$visitor_ip->isocode ) ) {
			$array[] = $decoded_json->$visitor_ip->isocode;
		} else {
			$array[] = 'null';
		}

		// 6 City.
		if ( isset( $decoded_json->$visitor_ip->city ) ) {
			$array[] = $decoded_json->$visitor_ip->city;
		} else {
			$array[] = 'null';
		}

		// 7 Blocked url for local logging.
		$protocols   = array( 'https://', 'http://', 'www.' );
		$host        = ! empty( $_SERVER['HTTP_HOST'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$req_uri     = ! empty( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$blocked_url = str_replace( $protocols, '', $host ) . $req_uri;
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
