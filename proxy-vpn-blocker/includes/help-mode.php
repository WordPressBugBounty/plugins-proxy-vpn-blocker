<?php
/**
 * Warnings and Notifications Handler for misconfigured Proxy & VPN Blocker Settings.
 *
 * @package Proxy & VPN Blocker
 */

/**
 * Function for the Proxy & VPN Blocker Help Mode.
 */
function pvb_helper_admin_notice__warnings() {
	// Check if an API Key is entered.
	$messages = array();

	if ( is_file( ABSPATH . 'disablepvb.txt' ) ) {
		$messages[] = __( 'Proxy & VPN Blocker is currently not protecting your site, disablepvb.txt exists in your WordPress root directory, please delete it!', 'proxy-vpn-blocker' );
	}

	// Check if proxycheck.io API Key is missing.
	if ( empty( get_option( 'pvb_proxycheckio_API_Key_field' ) ) ) {
		$messages[] = __( 'Your proxycheck.io API Key is Missing. Without an API key you are limited to 100 daily queries and some features will not function. ', 'proxy-vpn-blocker' );
	}

	// Display Admin Notice Message(s).
	if ( ! empty( $messages ) ) {
		echo '<div class="notice notice-warning">';
		echo '<p>Proxy & VPN Blocker Helper has detected that:</p>';
		echo '<ul>';
		foreach ( $messages as $message ) {
			echo '<li>' . esc_html( $message ) . '</li>';
		}
		echo '</ul>';
		echo '<p>Please check your Settings. You can turn off these messages under "Proxy & VPN Blocker Help Mode" under the "PVB Settings > Advanced" Settings Tab.</p>';
		echo '</div>';
	}
}
add_action( 'admin_notices', 'pvb_helper_admin_notice__warnings' );
