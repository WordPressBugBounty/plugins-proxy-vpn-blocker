<?php
/**
 * Proxy & VPN Blocker - Setup Wizard Form Handler
 *
 * @package  Proxy & VPN Blocker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the setup wizard form submission.
 *
 * @since 3.4.0
 */

/**
 * Handles the AJAX request to complete the setup wizard.
 */
add_action(
	'wp_ajax_pvb_complete_setup',
	function () {
		// Verify user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
			return;
		}

		// Verify nonce for security.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'pvb_setup_wizard_ajax_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed.' ) );
			return;
		}

		// Handle proxycheck.io API key (optional field).
		if ( isset( $_POST['pvb_proxycheckio_API_Key_field'] ) ) {
			$api_key = sanitize_text_field( wp_unslash( $_POST['pvb_proxycheckio_API_Key_field'] ) );
			update_option( 'pvb_proxycheckio_API_Key_field', $api_key );
		}

		// Handle VPN detection toggle.
		if ( isset( $_POST['pvb_proxycheckio_VPN_select_box'] ) && 'on' === $_POST['pvb_proxycheckio_VPN_select_box'] ) {
			update_option( 'pvb_proxycheckio_VPN_select_box', 'on' );
		} else {
			update_option( 'pvb_proxycheckio_VPN_select_box', '' );
		}

		// Handle IP Risk Scores toggle.
		if ( isset( $_POST['pvb_proxycheckio_risk_select_box'] ) && 'on' === $_POST['pvb_proxycheckio_risk_select_box'] ) {
			update_option( 'pvb_proxycheckio_risk_select_box', 'on' );

			// Set default risk score thresholds if enabling risk scores.
			if ( ! get_option( 'proxycheckio_max_riskscore_vpn' ) || ! get_option( 'proxycheckio_max_riskscore_proxy' ) ) {
				// Set default VPN risk threshold to 66 if not already set.
				if ( ! get_option( 'proxycheckio_max_riskscore_vpn' ) ) {
					update_option( 'proxycheckio_max_riskscore_vpn', '66' );
				}
				// Set default Proxy risk threshold to 33 if not already set.
				if ( ! get_option( 'proxycheckio_max_riskscore_proxy' ) ) {
					update_option( 'proxycheckio_max_riskscore_proxy', '33' );
				}
			}
		} else {
			update_option( 'pvb_proxycheckio_risk_select_box', '' );
		}

		// Handle User IP Logging toggle.
		if ( isset( $_POST['pvb_log_user_ip_select_box'] ) && 'on' === $_POST['pvb_log_user_ip_select_box'] ) {
			update_option( 'pvb_log_user_ip_select_box', 'on' );
		} else {
			update_option( 'pvb_log_user_ip_select_box', '' );
		}

		// Handle IP Header Type selection.
		if ( isset( $_POST['pvb_option_ip_header_type'] ) ) {
			$ip_header_type = sanitize_text_field( wp_unslash( $_POST['pvb_option_ip_header_type'] ) );

			// Validate against allowed headers.
			$allowed_headers = array(
				'REMOTE_ADDR',
				'HTTP_CF_CONNECTING_IP',
				'HTTP_X_FORWARDED_FOR',
				'HTTP_X_REAL_IP',
			);

			if ( in_array( $ip_header_type, $allowed_headers ) ) {
				// Store as array to match your existing format.
				update_option( 'pvb_option_ip_header_type', array( $ip_header_type ) );
			}
		}

		// Handle the DONOTCACHEPAGE setting.
		if ( isset( $_POST['pvb_cache_buster'] ) && 'on' === $_POST['pvb_cache_buster'] ) {
			update_option( 'pvb_cache_buster', 'on' );
		} else {
			update_option( 'pvb_cache_buster', '' );
		}

		// Mark setup as complete.
		update_option( 'pvb_setup_complete', 'on' );

		// Send success response.
		wp_send_json_success(
			array(
				'message'      => 'Setup completed successfully!',
				'redirect_url' => admin_url( 'admin.php?page=proxy_vpn_blocker_settings' ),
			)
		);
	}
);

/**
 * Handles the AJAX request when user skips the setup wizard.
 */
add_action(
	'wp_ajax_pvb_skip_setup',
	function () {
		// Verify user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
			return;
		}

		// Verify nonce for security.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'pvb_setup_wizard_ajax_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed.' ) );
			return;
		}

		// Mark setup as complete (skipped).
		update_option( 'pvb_setup_complete', 'on' );

		// Send success response.
		wp_send_json_success(
			array(
				'message'      => 'Setup skipped successfully!',
				'redirect_url' => admin_url( 'admin.php?page=proxy_vpn_blocker_settings' ),
			)
		);
	}
);
