<?php
/**
 * Proxy & VPN Blocker
 *
 * @package           Proxy & VPN Blocker
 * @author            Proxy & VPN Blocker
 * @copyright         2017 - 2025 Proxy & VPN Blocker
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Proxy & VPN Blocker
 * Plugin URI: https://proxyvpnblocker.com
 * description: Proxy & VPN Blocker prevents Proxies, VPN's and other unwanted visitors from accessing pages, posts and more, using Proxycheck.io API data.
 * Version: 3.2.0
 * Author: Proxy & VPN Blocker
 * Author URI: https://profiles.wordpress.org/rickstermuk
 * License: GPLv2
 * Text Domain:       proxy-vpn-blocker
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

$version     = '3.2.0';
$update_date = 'January 24th 2025';

if ( version_compare( get_option( 'proxy_vpn_blocker_version' ), $version, '<' ) ) {
	update_option( 'proxy_vpn_blocker_version', $version );
	update_option( 'proxy_vpn_blocker_last_update', $update_date );
}

/**
 * Get Visitor IP Address
 */
function pvb_get_visitor_ip_address() {
	if ( ! empty( get_option( 'pvb_option_ip_header_type' ) ) ) {
		$header_type = get_option( 'pvb_option_ip_header_type' );
		if ( 'HTTP_CF_CONNECTING_IP' === $header_type[0] ) {
			if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
				$cf_ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
				// Fix for Cloudflare returning an array of IP's in rare occurances.
				if ( is_array( $cf_ip ) ) {
					$visitor_ip_address = $cf_ip[0];
				} else {
					$visitor_ip_address = $cf_ip;
				}
			} else {
				$visitor_ip_address = ! empty( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
			}
		} elseif ( 'HTTP_X_FORWARDED_FOR' === $header_type[0] ) {
			if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				$x_forwarded_for_ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
				// Checks if $x_forwarded_for_ip is an array of IP's.
				if ( is_array( $x_forwarded_for_ip ) ) {
					$visitor_ip_address = $x_forwarded_for_ip[0];
				} else {
					$visitor_ip_address = $x_forwarded_for_ip;
				}
			} else {
				$visitor_ip_address = ! empty( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
			}
		} else {
			$get_ip_var         = isset( $_SERVER[ $header_type[0] ] ) ? sanitize_text_field( wp_unslash( $_SERVER[ $header_type[0] ] ) ) : '';
			$visitor_ip_address = empty( $get_ip_var ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : $get_ip_var;
		}
	} else {
		$visitor_ip_address = ! empty( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	}
	return $visitor_ip_address;
}

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load plugin class and function files.
require_once 'includes/class-proxy-vpn-blocker.php';
require_once 'includes/class-proxy-vpn-blocker-settings.php';
require_once 'includes/custom-form-handlers.php';
require_once 'includes/proxy-vpn-blocker-stat-loader.php';
require_once 'includes/post-additions.php';
require_once 'includes/proxy-vpn-blocker-admin-bar.php';
require_once 'includes/logs/proxy-vpn-blocker-action-log-fetcher.php';


// Conditionally load User IP Logging.
if ( 'on' === get_option( 'pvb_log_user_ip_select_box' ) ) {
	require_once 'includes/logs/proxy-vpn-blocker-action-log-saver.php';
	require_once 'includes/user-ip.php';
}

// Condionally load proxycheck.io CORS code.
if ( 'on' === get_option( 'pvb_cors_integration' ) && 'on' === get_option( 'pvb_proxycheckio_master_activation' ) ) {
	require_once 'proxycheckio-cors.php';
}

// Conditionally load Help Mode.
if ( 'on' === get_option( 'pvb_option_help_mode' ) ) {
	require_once 'includes/help-mode.php';
}


// Load plugin libraries.
require_once 'includes/lib/class-proxy-vpn-blocker-admin-api.php';

// Load db updater.
require_once 'pvb-db-upgrade.php';

/**
 * Returns the main instance of Proxy_VPN_Blocker to prevent the need to use globals.
 *
 * @return object Proxy_VPN_Blocker
 */
function proxy_vpn_blocker() {
	global $version;
	$instance = Proxy_VPN_Blocker::instance( __FILE__, $version );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = Proxy_VPN_Blocker_Settings::instance( $instance );
	}

	return $instance;
}

proxy_vpn_blocker();

/**
 * Function to check rank of user to enable staff and administration bypass when Block on Entire Site is in effect.
 */
function pvb_check_rank() {
	if ( empty( get_option( 'pvb_allow_staff_bypass' ) ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		} else {
			return false;
		}
	} elseif ( 'on' === get_option( 'pvb_allow_staff_bypass' ) ) {
		if ( current_user_can( 'manage_options' ) || current_user_can( 'edit_posts' ) ) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}


/**
 * Proxy & VPN Blocker Block/Deny to ease repetitiveness.
 */
function pvb_block_deny() {
	$proxycheck_denied = get_option( 'pvb_proxycheckio_denied_access_field' );
	$request_uri       = ! empty( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	if ( 'on' === get_option( 'pvb_proxycheckio_redirect_bad_visitor' ) ) {
		if ( ! empty( get_option( 'pvb_proxycheckio_opt_redirect_url' ) ) ) {
			nocache_headers();
			//phpcs:disable
			wp_redirect( get_option( 'pvb_proxycheckio_opt_redirect_url' ), 302 );
			//phpcs:enable
			exit();
		} else {
			define( 'DONOTCACHEPAGE', true ); // Do not cache this page.
			//phpcs:disable
			wp_die( '<p>' . $proxycheck_denied . '</p>', $proxycheck_denied, array( 'back_link' => true ) );
			//phpcs:enable
		}
	} else {
		define( 'DONOTCACHEPAGE', true ); // Do not cache this page.
		//phpcs:disable
		wp_die( '<p>' . $proxycheck_denied . '</p>', $proxycheck_denied, array( 'back_link' => true ) );
		//phpcs:enable
	}
}

/**
 * Proxy & VPN Blocker General check for (pages, posts, login etc).
 */
function pvb_general_check() {
	$visitor_ip_address = pvb_get_visitor_ip_address();
	if ( ! empty( $visitor_ip_address ) ) {
		require_once 'proxycheckio-api-call.php';
		$countries = get_option( 'pvb_proxycheckio_blocked_countries_field' );
		if ( ! empty( $countries ) && is_array( $countries ) ) {
			$perform_country_check = 1;
		} else {
			$perform_country_check = 0;
		}
		$proxycheck_answer = proxycheck_function( $visitor_ip_address, 1, 0, 0 );
		if ( 'yes' === $proxycheck_answer[0] ) {
			// Check if Risk Score Checking is on.
			if ( 'on' === get_option( 'pvb_proxycheckio_risk_select_box' ) ) {
				// Check if proxycheck answer array key 4 is set and is NOT type VPN or RULE.
				if ( 'VPN' !== $proxycheck_answer[4] ) {
					// Check if proxycheck answer array key 4 for risk score and compare it to the set proxy risk score.
					if ( $proxycheck_answer[3] >= get_option( 'pvb_proxycheckio_max_riskscore_proxy' ) ) {
						if ( 'on' === get_option( 'pvb_log_user_ip_select_box' ) ) {
							pvb_log_action( $visitor_ip_address, $proxycheck_answer[4], $proxycheck_answer[1], $proxycheck_answer[5], $proxycheck_answer[3], $proxycheck_answer[7], 'PHP' );
						}
						pvb_block_deny();
					}
				} elseif ( 'VPN' === $proxycheck_answer[4] ) {
					// Check if proxycheck answer array key 4 for risk score and compare it to the set VPN risk score.
					if ( $proxycheck_answer[3] >= get_option( 'pvb_proxycheckio_max_riskscore_vpn' ) ) {
						if ( 'on' === get_option( 'pvb_log_user_ip_select_box' ) ) {
							pvb_log_action( $visitor_ip_address, $proxycheck_answer[4], $proxycheck_answer[1], $proxycheck_answer[5], $proxycheck_answer[3], $proxycheck_answer[7], 'PHP' );
						}
						pvb_block_deny();
					}
				}
			} else {
				// Do this if risk score checking is off.
				if ( 'on' === get_option( 'pvb_log_user_ip_select_box' ) ) {
					pvb_log_action( $visitor_ip_address, $proxycheck_answer[4], $proxycheck_answer[1], $proxycheck_answer[5], $proxycheck_answer[3], $proxycheck_answer[7], 'PHP' );
				}
				pvb_block_deny();
			}
		} elseif ( 1 === $perform_country_check ) {
			if ( empty( get_option( 'pvb_proxycheckio_whitelist_countries_select_box' ) ) ) {
				// Block Countries in Country Block List. Allow all others.
				if ( 'null' !== $proxycheck_answer[1] && 'null' !== $proxycheck_answer[2] ) {
					if ( in_array( $proxycheck_answer[1], $countries, true ) || in_array( $proxycheck_answer[2], $countries, true ) ) {
						if ( 'on' === get_option( 'pvb_log_user_ip_select_box' ) ) {
							pvb_log_action( $visitor_ip_address, $proxycheck_answer[4], $proxycheck_answer[1], $proxycheck_answer[5], $proxycheck_answer[3], $proxycheck_answer[7], 'PHP' );
						}
						pvb_block_deny();
					} else {
						set_transient( 'pvb_' . get_option( 'pvb_proxycheckio_current_key' ) . '_' . $visitor_ip_address, time() + 1800 . '-' . 0, 60 * get_option( 'pvb_proxycheckio_good_ip_cache_time' ) );
					}
				}
			}
			if ( 'on' === get_option( 'pvb_proxycheckio_whitelist_countries_select_box' ) ) {
				// Allow Countries through if listed if this is to be treated as a whitelist. Block all other countries.
				if ( 'null' !== $proxycheck_answer[1] && 'null' !== $proxycheck_answer[2] ) {
					if ( in_array( $proxycheck_answer[1], $countries, true ) || in_array( $proxycheck_answer[2], $countries, true ) ) {
						set_transient( 'pvb_' . get_option( 'pvb_proxycheckio_current_key' ) . '_' . $visitor_ip_address, time() + 1800 . '-' . 0, 60 * get_option( 'pvb_proxycheckio_good_ip_cache_time' ) );
					} else {
						if ( 'on' === get_option( 'pvb_log_user_ip_select_box' ) ) {
							pvb_log_action( $visitor_ip_address, $proxycheck_answer[4], $proxycheck_answer[1], $proxycheck_answer[5], $proxycheck_answer[3], $proxycheck_answer[7] );
						}
						pvb_block_deny();
					}
				}
			}
		} else {
			// No proxy has been detected so set a transient to cache this result as known good IP.
			set_transient( 'pvb_' . get_option( 'pvb_proxycheckio_current_key' ) . '_' . $visitor_ip_address, time() + 1800 . '-' . 0, 60 * get_option( 'pvb_proxycheckio_good_ip_cache_time' ) );
		}
	}
}

/**
 * Proxy & VPN Blocker Standard Script
 */
function pvb_standard_script() {
	if ( ! is_file( ABSPATH . 'disablepvb.txt' ) ) {
		$can_bypass = pvb_check_rank();

		$host        = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$full_url    = esc_url_raw( ( is_ssl() ? 'https://' : 'http://' ) . $host . $request_uri );

		// Array of URI's that we want to avoid code running on when Block on Entire Site is in use.
		$avoid_uris = array(
			esc_url_raw( ( is_ssl() ? 'https://' : 'http://' ) . $host . '/wp-content/plugins/matomo/app/matomo.php?' ),
		);

		// Check if the request is from WordPress Cron.
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			// This is a WordPress Cron request.
			return;
		}

		// Check if the request is from Admin AJAX.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			// This is an Admin AJAX request.
			return;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			// This is a REST API request.
			return;
		}

		if ( in_array( $full_url, $avoid_uris ) ) {
			// This request is for predefined scripts that we don't want to block on.
			return;
		}
		if ( false === $can_bypass ) {
			pvb_general_check();
		}
	}
}

/**
 * PVB on ALL pages integration.
 */
function pvb_all_pages_integration() {
	if ( ! is_file( ABSPATH . 'disablepvb.txt' ) ) {
		$can_bypass = pvb_check_rank();

		$host        = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$full_url    = esc_url_raw( ( is_ssl() ? 'https://' : 'http://' ) . $host . $request_uri );

		// Array of URI's that we want to avoid code running on when Block on Entire Site is in use.
		$avoid_uris = array(
			esc_url_raw( ( is_ssl() ? 'https://' : 'http://' ) . $host . '/wp-content/plugins/matomo/app/matomo.php?' ),
		);

		// Check if the request is from WordPress Cron.
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			// This is a WordPress Cron request.
			return;
		}

		// Check if the request is from Admin AJAX.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			// This is an Admin AJAX request.
			return;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			// This is a REST API request.
			return;
		}

		if ( in_array( $full_url, $avoid_uris ) ) {
			// This request is for predefined scripts that we don't want to block on.
			return;
		}

		if ( false === $can_bypass ) {
			$pvb_block_page = get_option( 'pvb_proxycheckio_opt_redirect_url' );
			if ( ! empty( $pvb_block_page ) && 'on' === get_option( 'pvb_proxycheckio_redirect_bad_visitor' ) ) {
				if ( stripos( $full_url, $pvb_block_page ) === false ) {
					pvb_general_check();
				}
			} else {
				pvb_general_check();
			}
		}
	}
}

/**
 * PVB on select pages & posts integration
 */
function pvb_select_postspages_integrate() {
	if ( ! is_file( ABSPATH . 'disablepvb.txt' ) ) {
		global $pvb_current_id;

		$can_bypass = pvb_check_rank();

		$blocked_pages_posts = get_option( 'pvb_blocked_pages_ids_array' );

		$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';

		// Array of URI's that we want to avoid code running on when Block on Entire Site is in use.
		$avoid_uris = array(
			esc_url_raw( ( is_ssl() ? 'https://' : 'http://' ) . $host . '/wp-content/plugins/matomo/app/matomo.php?' ),
		);

		// Check if the request is from WordPress Cron.
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			// This is a WordPress Cron request.
			return;
		}

		// Check if the request is from Admin AJAX.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			// This is an Admin AJAX request.
			return;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			// This is a REST API request.
			return;
		}

		if ( false === $can_bypass ) {
			if ( in_array( $pvb_current_id, $blocked_pages_posts ) ) {
				pvb_general_check();
			}
		}
	}
}

/**
 * Reprocesses Selected Restricted Pages and Posts to permalinks for use later if the WordPress Permalinks structure is updated.
 * Cannot otherwise get permalinks from page early enough to use when we need it.
 *
 * @param type $old_value Old Permalink Format.
 * @param type $new_value New Permalink Format.
 */
function pvb_wp_permalink_structure_changed( $old_value, $new_value ) {
	if ( $old_value !== $new_value && get_option( 'permalink_structure' ) === $old_value ) {
		pvb_save_post_function();
	}
}
add_action( 'update_option_permalink_structure', 'pvb_wp_permalink_structure_changed', 10, 2 );

/**
 * Sets a No Cache header on pages that we want to block on, otherwise Cache will serve page before our code can run.
 */
function pvb_set_do_not_cache_header() {
	$page_ids        = get_option( 'pvb_blocked_pages_ids_array' );
	$current_page_id = get_queried_object_id();

	if ( 'on' === get_option( 'pvb_proxycheckio_all_pages_activation' ) ) {
		nocache_headers();
	}

	if ( in_array( $GLOBALS['pagenow'], array( 'wp-login.php' ) ) ) {
		nocache_headers();
	}

	if ( ! empty( $page_ids ) ) {
		if ( in_array( $current_page_id, $page_ids ) ) {
			nocache_headers();
		}
	}
}

/**
 * Get current queried object ID for use later.
 */
function pvb_page_id_processor() {
	global $pvb_current_id;
	$pvb_current_id = get_queried_object_id();

	if ( function_exists( 'is_shop' ) && is_shop() ) {
		$pvb_current_id = wc_get_page_id( 'shop' );
	}
}

/**
 * Activation switch to enable or disable querying.
 */
if ( 'on' === get_option( 'pvb_proxycheckio_master_activation' ) ) {
	/**
	 * WordPress Auth protection and comments protection.
	 */
	if ( 'on' === get_option( 'pvb_protect_login_authentication' ) ) {
		add_filter( 'authenticate', 'pvb_standard_script', 1 );
		add_action( 'login_init', 'pvb_standard_script', 1 );
	}
	add_action( 'pre_comment_on_post', 'pvb_standard_script', 1 );

	/**
	 * Enable block on specified PAGES and POSTS option
	 */
	if ( ! empty( get_option( 'pvb_blocked_pages_ids_array' ) ) ) {
		add_action( 'wp', 'pvb_page_id_processor', 1 );
		add_action( 'template_redirect', 'pvb_select_postspages_integrate', 1 );
	}

	if ( 'on' === get_option( 'pvb_cache_buster' ) ) {
		add_action( 'send_headers', 'pvb_set_do_not_cache_header' );
	}

	/**
	 * Enable for all pages option
	 */
	if ( 'on' === get_option( 'pvb_proxycheckio_all_pages_activation' ) ) {
		if ( empty( get_option( 'pvb_protect_login_authentication' ) ) && function_exists( 'login_header' ) ) {
			// Do Nothing.
			return;
		} else {
			add_action( 'plugins_loaded', 'pvb_all_pages_integration', 1 );
		}
	}

	/**
	 * Settings Conflict Protection.
	 */
	/**
	 * Disable the Whitelist option if whitelist is empty.
	 */
	if ( 'on' === get_option( 'pvb_proxycheckio_whitelist_countries_select_box' ) && empty( get_option( 'pvb_proxycheckio_blocked_countries_field' ) ) ) {
		update_option( 'pvb_proxycheckio_whitelist_countries_select_box', '' );
	}

	/**
	 * Disable the Custom Block Page option if Redirection of Blocked Visitors is enabled.
	 */
	if ( 'on' === get_option( 'pvb_proxycheckio_redirect_bad_visitor' ) ) {
		update_option( 'pvb_proxycheckio_custom_blocked_page', '' );
	}
}
