<?php
/**
 * Handler for Proxy & VPN Blocker Review Messaging
 *
 * @package Proxy & VPN Blocker
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add Phase 1 Statistics Queries.
global $wpdb;

// Define the log table name.
$log_table = $wpdb->prefix . 'pvb_visitor_action_log';

// Get cached stats or calculate new ones (1 hour cache).
$stats = get_transient( 'pvb_review_stats' );

if ( ! $stats ) {
	// Calculate stats.
	$thirty_days_ago   = gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) );
	$fourteen_days_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-14 days' ) );

	$stats_1 = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT
				( SELECT COUNT(*) FROM $log_table WHERE blocked_at >= %s ) AS total_blocks,
				( SELECT COUNT(DISTINCT ip_address) FROM $log_table WHERE blocked_at >= %s ) AS unique_ips",
			$thirty_days_ago,
			$thirty_days_ago
		),
		ARRAY_A
	);

	$stats_2 = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT
				( SELECT COUNT(*) FROM $log_table WHERE blocked_at >= %s ) AS total_blocks,
				( SELECT COUNT(DISTINCT ip_address) FROM $log_table WHERE blocked_at >= %s ) AS unique_ips",
			$fourteen_days_ago,
			$fourteen_days_ago
		),
		ARRAY_A
	);

	// Build stats array.
	$stats_arr['total_blocks_30'] = $stats_1['total_blocks'] ?? 0;
	$stats_arr['unique_ips_30']   = $stats_1['unique_ips'] ?? 0;
	$stats_arr['total_blocks_14'] = $stats_2['total_blocks'] ?? 0;
	$stats_arr['unique_ips_14']   = $stats_2['unique_ips'] ?? 0;


	// Cache the result.
	set_transient( 'pvb_review_stats', $stats_arr, HOUR_IN_SECONDS );

	// Assign the calculated stats to $stats for immediate use.
	$stats = $stats_arr;
}

if ( ! empty( get_option( 'pvb_proxycheckio_API_Key_field' ) ) ) {
	$pvb_api_key_details = get_option( 'pvb_proxycheck_apikey_details' );
	if ( ! empty( $pvb_api_key_details ) && ! isset( $_COOKIE['pvb-hide-rvw-div'] ) ) {
		$current_date    = new DateTime();
		$activation_date = DateTime::createFromFormat( 'Y-m-d', $pvb_api_key_details['activation_date'] );

		if ( $current_date > $activation_date ) {
			$interval = $current_date->diff( $activation_date );
			if ( 'Free' === $pvb_api_key_details['tier'] && $interval->days >= 30 ) {
					echo '<div class=pvbrvwwrap">' . "\n";
					echo '	<div class="pvbrvwwrapwrapleft">' . "\n";
					echo '		<div class="pvbrvwwraplogoinside">' . "\n";
					echo '		</div>' . "\n";
					echo '	</div>' . "\n";
					echo '	<div class="pvbrvwwrapright">' . "\n";
					echo '		<button class="pvbdonatedismiss" id="pvbdonationclosebutton" title="close"><i class="fa-solid fa-circle-xmark"></i></button>' . "\n";
					echo '		<div class="pvbrvwraptext">' . "\n";
					echo '			<p>' . __( "Thank you for using Proxy & VPN Blocker on " . get_bloginfo( 'name' ) . "!", 'proxy-vpn-blocker' ) . '</p>' . "\n";
				if ( $stats['total_blocks_30'] > 0 ) {
					echo '			<p>' . __( 'Over the past 30 days, Proxy & VPN Blocker has successfully blocked' ) . number_format( $stats['total_blocks_30'] ) . ' ' . __( 'unwanted requests from', 'proxy-vpn-blocker' ) . ' ' . number_format( $stats['unique_ips_30'] ) . ' ' . __( 'unique IP Addresses, helping keep your site secure.', 'proxy-vpn-blocker' ) . '</p>' . "\n";
				}
					echo '			<p>' . __( 'If our plugin has made a difference to you, would you take a moment to share your experience? Your review helps us grow and lets other WordPress users discover a tool that works.', 'proxy-vpn-blocker' ) . '</p>' . "\n";
					echo '			<p>' . __( '<a href="https://wordpress.org/plugins/proxy-vpn-blocker/#reviews" target="_blank">👉 Leave a quick review - it only takes a minute!</a>', 'proxy-vpn-blocker' ) . '</p>' . "\n";
					echo '			<p>' . __( 'Thank you for your support!', 'proxy-vpn-blocker' ) . '</p>' . "\n";
					echo '		</div>' . "\n";
					echo '	</div>' . "\n";
					echo '</div>' . "\n";
			}
			if ( 'Paid' === $pvb_api_key_details['tier'] && $interval->days >= 14 ) {
					echo '<div class="pvbrvwwrap">' . "\n";
					echo '	<div class="pvbrvwwrapleft">' . "\n";
					echo '		<div class="pvbrvwwraplogoinside">' . "\n";
					echo '		</div>' . "\n";
					echo '	</div>' . "\n";
					echo '	<div class="pvbrvwwrapright">' . "\n";
					echo '		<button class="pvbdonatedismiss" id="pvbdonationclosebutton" title="close"><i class="fa-solid fa-circle-xmark"></i></button>' . "\n";
					echo '		<div class="pvbrvwwraptext">' . "\n";
					echo '			<p>' . __( 'Thank you for using Proxy & VPN Blocker on ' . get_bloginfo( 'name' ) . '!', 'proxy-vpn-blocker' ) . '</p>' . "\n";
				if ( $stats['total_blocks_14'] > 0 ) {
					echo '			<p>' . __( 'Over the past 14 days, Proxy & VPN Blocker has successfully blocked ' ) . number_format( $stats['total_blocks_14'] ) . ' ' . __( 'unwanted requests from', 'proxy-vpn-blocker' ) . ' ' . number_format( $stats['unique_ips_14'] ) . ' ' . __( 'unique IP Addresses, helping keep your site secure.', 'proxy-vpn-blocker' ) . '</p>' . "\n";
				}
					echo '			<p>' . __( 'If our plugin has made a difference to you, would you take a moment to share your experience? Your review helps us grow and lets other WordPress users discover a tool that works.', 'proxy-vpn-blocker' ) . '</p>' . "\n";
					echo '			<p>' . __( '<a href="https://wordpress.org/plugins/proxy-vpn-blocker/#reviews" target="_blank">👉 Leave a quick review - it only takes a minute!</a>', 'proxy-vpn-blocker' ) . '</p>' . "\n";
					echo '			<p>' . __( 'Thank you for your support!', 'proxy-vpn-blocker' ) . '</p>' . "\n";
					echo '		</div>' . "\n";
					echo '	</div>' . "\n";
					echo '</div>' . "\n";
			}
		}
	}
}

