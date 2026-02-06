<?php
/**
 * Proxy & VPN Blocker
 *
 * @package           Proxy & VPN Blocker
 * @author            Proxy & VPN Blocker
 * @copyright         2017 - 2026 Proxy & VPN Blocker
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Proxy & VPN Blocker
 * Plugin URI: https://proxyvpnblocker.com
 * description: Proxy & VPN Blocker prevents Proxies, VPN's and other unwanted visitors from accessing pages, posts and more, using Proxycheck.io API data.
 * Version: 3.5.7
 * Author: Proxy & VPN Blocker
 * Author URI: https://profiles.wordpress.org/rickstermuk
 * License: GPLv2
 * Text Domain:       proxy-vpn-blocker
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


$version     = '3.5.7';
$update_date = 'January 23rd 2026';

// Set the proxycheck.io v3 API version we are using.
$proxycheck_api_version = '10-November-2025';

if ( version_compare( get_option( 'proxy_vpn_blocker_version' ), $version, '<' ) ) {
	update_option( 'proxy_vpn_blocker_version', $version );
	update_option( 'proxy_vpn_blocker_last_update', $update_date );
	update_option( 'proxy_vpn_blocker_proxycheck_api_version', $proxycheck_api_version );
}

/**
 * Get Visitor IP Address
 */
function pvb_get_visitor_ip_address() {
	if ( ! empty( get_option( 'pvb_option_ip_header_type' ) ) ) {
		$header_type = get_option( 'pvb_option_ip_header_type' );
		if ( 'HTTP_CF_CONNECTING_IP' === $header_type[0] ) {
			if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
				$cf_ip              = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
				$ip_array           = explode( ', ', $cf_ip );
				$visitor_ip_address = $ip_array[0];
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

// Load plugin class and function files.
require_once 'includes/class-proxy-vpn-blocker.php';
require_once 'includes/class-proxy-vpn-blocker-settings.php';
require_once 'includes/custom-form-handlers.php';
require_once 'includes/pvb-stats-page/proxy-vpn-blocker-stat-loader.php';
require_once 'includes/pvb-stats-page/proxy-vpn-blocker-usage-fetcher.php';
require_once 'includes/post-additions.php';
require_once 'includes/proxy-vpn-blocker-admin-bar.php';
require_once 'includes/pvb-action-logs/proxy-vpn-blocker-action-log-fetcher.php';
require_once 'includes/proxy-vpn-blocker-classic-editor-support.php';

// conditionally load the setup wizard form handler.
if ( 'on' !== get_option( 'pvb_setup_complete' ) ) {
	require_once 'includes/setup-wizard/setup-wizard-form-handler.php';
}

// Conditionally load User IP Logging.
if ( 'on' === get_option( 'pvb_log_user_ip_select_box' ) ) {
	require_once 'includes/pvb-action-logs/proxy-vpn-blocker-action-log-saver.php';
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
require_once 'includes/lib/class-pvb-api-key-encryption.php';

// Load db updater.
require_once 'pvb-db-upgrade.php';
// Run upgrade logic on plugin activation.
register_activation_hook( __FILE__, 'upgrade_pvb_db' );
// Run a version check to catch auto-updates or manual upgrades.
add_action( 'plugins_loaded', 'maybe_upgrade_pvb_db' );

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
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true ); // Do not cache this page.
			}
			//phpcs:disable
			wp_die( '<p>' . $proxycheck_denied . '</p>', $proxycheck_denied, array( 'back_link' => true ) );
			//phpcs:enable
		}
	} else {
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true ); // Do not cache this page.
		}
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

		$detected       = $proxycheck_answer[0];
		$country_info   = $proxycheck_answer[1];
		$continent_info = $proxycheck_answer[2];
		$risk_score     = $proxycheck_answer[3];
		$detected_type  = $proxycheck_answer[4];

		if ( 'yes' === $detected ) {
			// Define all possible detection types and their corresponding option names.
			$detection_types = array(
				'proxy'       => 'pvb_proxycheckio_detectiontype_proxy',
				'vpn'         => 'pvb_proxycheckio_detectiontype_vpn',
				'compromised' => 'pvb_proxycheckio_detectiontype_compromised',
				'scraper'     => 'pvb_proxycheckio_detectiontype_scraper',
				'tor'         => 'pvb_proxycheckio_detectiontype_tor',
				'hosting'     => 'pvb_proxycheckio_detectiontype_hosting',
			);

			// Handle multiple detected types (comma-separated or array).
			$detected_types = array();
			if ( is_array( $detected_type ) ) {
				$detected_types = $detected_type;
			} else {
				$detected_types = array_map( 'trim', explode( ',', strtolower( $detected_type ) ) );
			}

			// Check if any detected type should be blocked.
			$should_block  = false;
			$blocking_type = null;

			foreach ( $detected_types as $type ) {
				$type = strtolower( trim( $type ) );

				// Check if this detection type is enabled for blocking.
				if ( isset( $detection_types[ $type ] ) && 'on' === get_option( $detection_types[ $type ] ) ) {
					$should_block  = true;
					$blocking_type = $type;
					break; // We found a type that should be blocked.
				}
			}

			if ( $should_block ) {
				$proceed_with_blocking = true;

				// Check if Risk Score Checking is enabled.
				if ( 'on' === get_option( 'pvb_proxycheckio_risk_select_box' ) ) {
					$risk_threshold_met = false;

					// Determine which risk score threshold to use based on the blocking type.
					if ( 'vpn' === $blocking_type ) {
						$max_risk_score = get_option( 'pvb_proxycheckio_max_riskscore_vpn' );
						if ( $risk_score >= $max_risk_score ) {
							$risk_threshold_met = true;
						}
					} else {
						// For all other types, use the general proxy risk score.
						$max_risk_score = get_option( 'pvb_proxycheckio_max_riskscore_proxy' );
						if ( $risk_score >= $max_risk_score ) {
							$risk_threshold_met = true;
						}
					}

					// Only proceed with blocking if risk threshold is met.
					$proceed_with_blocking = $risk_threshold_met;
				}

				// Execute blocking if we should proceed.
				if ( $proceed_with_blocking ) {
					if ( 'on' === get_option( 'pvb_log_user_ip_select_box' ) ) {
						pvb_log_action( $visitor_ip_address, $detected_type, $country_info, $proxycheck_answer[5], $risk_score, $proxycheck_answer[7], 'PHP' );
					}
					pvb_block_deny();
				}
			}
		} elseif ( 1 === $perform_country_check ) {
			if ( empty( get_option( 'pvb_proxycheckio_whitelist_countries_select_box' ) ) ) {
				// Block Countries in Country Block List. Allow all others.
				if ( 'null' !== $country_info && 'null' !== $continent_info ) {
					if ( in_array( $country_info, $countries, true ) || in_array( $continent_info, $countries, true ) ) {
						if ( 'on' === get_option( 'pvb_log_user_ip_select_box' ) ) {
							pvb_log_action( $visitor_ip_address, $detected_type, $country_info, $proxycheck_answer[5], $risk_score, $proxycheck_answer[7], 'PHP' );
						}
						pvb_block_deny();
					} else {
						set_transient( 'pvb_' . get_option( 'pvb_proxycheckio_current_key' ) . '_' . $visitor_ip_address, time() + 1800 . '-' . 0, 60 * get_option( 'pvb_proxycheckio_good_ip_cache_time' ) );
					}
				}
			}
			if ( 'on' === get_option( 'pvb_proxycheckio_whitelist_countries_select_box' ) ) {
				// Allow Countries through if listed if this is to be treated as a whitelist. Block all other countries.
				if ( 'null' !== $country_info && 'null' !== $continent_info ) {
					if ( in_array( $country_info, $countries, true ) || in_array( $continent_info, $countries, true ) ) {
						set_transient( 'pvb_' . get_option( 'pvb_proxycheckio_current_key' ) . '_' . $visitor_ip_address, time() + 1800 . '-' . 0, 60 * get_option( 'pvb_proxycheckio_good_ip_cache_time' ) );
					} else {
						if ( 'on' === get_option( 'pvb_log_user_ip_select_box' ) ) {
							pvb_log_action( $visitor_ip_address, $detected_type, $country_info, $proxycheck_answer[5], $risk_score, $proxycheck_answer[7], 'PHP' );
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
 * PVB on select paths integration (for virtual/plugin paths like courses/, products/, etc.)
 */
function pvb_select_paths_integrate() {
	if ( ! is_file( ABSPATH . 'disablepvb.txt' ) ) {
		$can_bypass    = pvb_check_rank();
		$blocked_paths = get_option( 'pvb_defined_protected_paths', array() );
		$host          = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';

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

		// Skip if no paths are configured for blocking.
		if ( empty( $blocked_paths ) || ! is_array( $blocked_paths ) ) {
			return;
		}

		// Get the current request path.
		$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$request_path = wp_parse_url( $request_uri, PHP_URL_PATH );
		$request_path = trim( $request_path, '/' );

		// Skip empty paths (homepage).
		if ( empty( $request_path ) ) {
			return;
		}

		if ( false === $can_bypass ) {
			// Check if current path matches any protected path.
			$is_protected = false;

			// Handle both data formats:
			// 1. Premium format: array('path/' => array('method' => '...', 'redirect_url' => '...'))
			// 2. Simple format: array('path/', 'path2/')
			if ( ! empty( $blocked_paths ) && is_array( $blocked_paths ) ) {
				// Check if it's the premium format (associative array with path keys)
				$path_keys = array();
				foreach ( $blocked_paths as $key => $value ) {
					if ( is_array( $value ) && isset( $value['method'] ) ) {
						// Premium format: key is the path
						$path_keys[] = $key;
					} elseif ( is_string( $value ) ) {
						// Simple format: value is the path
						$path_keys[] = $value;
					}
				}

				// If no paths were extracted, try treating keys as paths (fallback)
				if ( empty( $path_keys ) ) {
					$path_keys = array_keys( $blocked_paths );
				}

				foreach ( $path_keys as $protected_path ) {
					$protected_path = trim( $protected_path, '/' );

					if ( empty( $protected_path ) ) {
						continue;
					}

					// Check for exact match.
					if ( $request_path === $protected_path ) {
						$is_protected = true;
						break;
					}

					// Check if request path starts with protected path (for sub-pages).
					// e.g., protecting "courses" also protects "courses/advanced-php".
					if ( strpos( $request_path . '/', $protected_path . '/' ) === 0 ) {
						$is_protected = true;
						break;
					}
				}
			}

			// If path is protected, run the general check.
			if ( $is_protected ) {
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
	 * Enable block on specified VIRTUAL PATHS option
	 */
	if ( ! empty( get_option( 'pvb_defined_protected_paths' ) ) && 'on' === get_option( 'pvb_protected_paths' ) ) {
		add_action( 'init', 'pvb_select_paths_integrate', 1 );
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
