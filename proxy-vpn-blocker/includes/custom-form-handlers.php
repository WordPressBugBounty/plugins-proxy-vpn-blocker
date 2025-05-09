<?php
/**
 * Custom form handler for whitelist and blacklist.
 *
 * @package Proxy & VPN Blocker.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Using proxycheck.io dashboard API this adds IP's to whitelist.
 *
 * @since 1.4.0
 */
function whitelist_add() {
	if ( ! isset( $_POST['nonce_add_ip_whitelist'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce_add_ip_whitelist'] ) ), 'add-ip-whitelist' ) ) {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_send_json_error( 'Nonce failed' );
		}
		exit;
	}

	if ( isset( $_POST['add'] ) ) {
		$add          = sanitize_text_field( wp_unslash( $_POST['add'] ) );
		$identifier   = '#Added via ' . get_bloginfo( 'name' ) . ' at UTC ' . gmdate( 'Y/m/d H:i:s' );
		$args         = array(
			'method'      => 'POST',
			'timeout'     => '5',
			'httpversion' => '1.1',
			'blocking'    => true,
			'headers'     => array(),
			'body'        => array(
				'data' => $add . ' ' . $identifier,
			),
			'cookies'     => array(),
		);
		$response     = wp_remote_post( 'https://proxycheck.io/dashboard/whitelist/add/?key=' . get_option( 'pvb_proxycheckio_API_Key_field' ), $args );
		$decoded_json = json_decode( wp_remote_retrieve_body( $response ) );

		if ( 'ok' === $decoded_json->status ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				wp_send_json_success( 'IP added!' );
			} else {
				wp_safe_redirect( add_query_arg( 'add-pvb-whitelist', 'yes', admin_url( 'admin.php?page=proxy_vpn_blocker_whitelist' ) ) );
				exit;
			}
		} elseif ( 'denied' === $decoded_json->status ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				wp_send_json_error( 'proxycheck.io Dashboard Access was Denied!' );
			} else {
				wp_safe_redirect( add_query_arg( 'add-pvb-whitelist', 'no', admin_url( 'admin.php?page=proxy_vpn_blocker_whitelist' ) ) );
				exit;
			}
		} else {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				wp_send_json_error( 'Add failed.' );
			} else {
				wp_safe_redirect( add_query_arg( 'add-pvb-whitelist', 'no', admin_url( 'admin.php?page=proxy_vpn_blocker_whitelist' ) ) );
				exit;
			}
		}
	}
}
add_action( 'admin_post_whitelist_add', 'whitelist_add' );
add_action( 'wp_ajax_whitelist_add', 'whitelist_add' );

/**
 * Using proxycheck.io dashboard API this removes IP's from whitelist.
 *
 * @since 1.4.0
 */
function whitelist_remove() {
	if ( ! isset( $_POST['nonce_remove_ip_whitelist'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce_remove_ip_whitelist'] ) ), 'remove-ip-whitelist' ) ) {
		'Sorry, your nonce did not verify.';
		exit;
	} elseif ( isset( $_POST['remove'] ) ) {
		$remove       = sanitize_text_field( wp_unslash( $_POST['remove'] ) );
		$args         = array(
			'method'      => 'POST',
			'timeout'     => '5',
			'httpversion' => '1.1',
			'blocking'    => true,
			'headers'     => array(),
			'body'        => array(
				'data' => $remove,
			),
			'cookies'     => array(),
		);
		$response     = wp_remote_post( 'https://proxycheck.io/dashboard/whitelist/remove/?key=' . get_option( 'pvb_proxycheckio_API_Key_field' ), $args );
		$decoded_json = json_decode( wp_remote_retrieve_body( $response ) );
		if ( 'ok' === $decoded_json->status ) {
			wp_safe_redirect( add_query_arg( 'remove-pvb-whitelist', 'yes', admin_url( 'admin.php?page=proxy_vpn_blocker_whitelist' ) ) );
			exit();
		} else {
			wp_safe_redirect( add_query_arg( 'remove-pvb-whitelist', 'no', admin_url( 'admin.php?page=proxy_vpn_blocker_whitelist' ) ) );
			exit();
		}
	}
}
add_action( 'admin_post_whitelist_remove', 'whitelist_remove' );

/**
 * Using proxycheck.io dashboard API this adds IP's to whitelist.
 *
 * @since 1.4.0
 */
function blacklist_add() {
	if ( ! isset( $_POST['nonce_add_ip_blacklist'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce_add_ip_blacklist'] ) ), 'add-ip-blacklist' ) ) {
		'Sorry, your nonce did not verify.';
		exit;
	} elseif ( isset( $_POST['add'] ) ) {
		$add          = sanitize_text_field( wp_unslash( $_POST['add'] ) );
		$date         = gmdate( 'Y/m/d H:i:s' );
		$identifier   = '#Added via ' . get_bloginfo( 'name' ) . ' at UTC ' . gmdate( 'Y/m/d H:i:s' );
		$args         = array(
			'method'      => 'POST',
			'timeout'     => '5',
			'httpversion' => '1.1',
			'blocking'    => true,
			'headers'     => array(),
			'body'        => array(
				'data' => $add . ' ' . $identifier,
			),
			'cookies'     => array(),
		);
		$response     = wp_remote_post( 'https://proxycheck.io/dashboard/blacklist/add/?key=' . get_option( 'pvb_proxycheckio_API_Key_field' ), $args );
		$decoded_json = json_decode( wp_remote_retrieve_body( $response ) );
		if ( 'ok' === $decoded_json->status ) {
			wp_safe_redirect( add_query_arg( 'add-pvb-blacklist', 'yes', admin_url( 'admin.php?page=proxy_vpn_blocker_blacklist' ) ) );
			exit();
		} else {
			wp_safe_redirect( add_query_arg( 'add-pvb-blacklist', 'no', admin_url( 'admin.php?page=proxy_vpn_blocker_blacklist' ) ) );
			exit();
		}
	}
}
add_action( 'admin_post_blacklist_add', 'blacklist_add' );

/**
 * Using proxycheck.io dashboard API this removes IP's from blacklist.
 *
 * @since 1.4.0
 */
function blacklist_remove() {
	if ( ! isset( $_POST['nonce_remove_ip_blacklist'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce_remove_ip_blacklist'] ) ), 'remove-ip-blacklist' ) ) {
		'Sorry, your nonce did not verify.';
		exit;
	} elseif ( isset( $_POST['remove'] ) ) {
		$remove       = sanitize_text_field( wp_unslash( $_POST['remove'] ) );
		$args         = array(
			'method'      => 'POST',
			'timeout'     => '5',
			'httpversion' => '1.1',
			'blocking'    => true,
			'headers'     => array(),
			'body'        => array(
				'data' => $remove,
			),
			'cookies'     => array(),
		);
		$response     = wp_remote_post( 'https://proxycheck.io/dashboard/blacklist/remove/?key=' . get_option( 'pvb_proxycheckio_API_Key_field' ), $args );
		$decoded_json = json_decode( wp_remote_retrieve_body( $response ) );
		if ( 'ok' === $decoded_json->status ) {
			wp_safe_redirect( add_query_arg( 'remove-pvb-blacklist', 'yes', admin_url( 'admin.php?page=proxy_vpn_blocker_blacklist' ) ) );
			exit();
		} else {
			wp_safe_redirect( add_query_arg( 'remove-pvb-blacklist', 'no', admin_url( 'admin.php?page=proxy_vpn_blocker_blacklist' ) ) );
			exit();
		}
	}
}
add_action( 'admin_post_blacklist_remove', 'blacklist_remove' );
