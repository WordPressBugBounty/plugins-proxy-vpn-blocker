<?php
/**
 * This file runs if older than current DB Version is detected or on fresh installs.
 *
 * @package Proxy & VPN Blocker
 */

/**
 * Function to upgrade database.
 */
function upgrade_pvb_db() {
	global $wpdb;

	$database_version = get_option( 'pvb_db_version' );
	$current_version  = '5.2.3';

	// Handle both upgrade scenarios and fresh installations.
	if ( empty( $database_version ) ) {
		// Fresh installation - need to run all necessary setup.

		// Add default options that would have been added in earlier versions.
		if ( ! get_option( 'pvb_protect_default_login_page' ) ) {
			add_option( 'pvb_protect_default_login_page', 'on' );
		}
		if ( ! get_option( 'pvb_protect_comments' ) ) {
			add_option( 'pvb_protect_comments', 'on ' );
		}
		if ( ! get_option( 'pvb_log_user_ip_select_box' ) ) {
			add_option( 'pvb_log_user_ip_select_box', 'on' );
		}
		if ( ! get_option( 'pvb_option_ip_header_type' ) ) {
			add_option( 'pvb_option_ip_header_type', 'REMOTE_ADDR' );
		}

		// Create the database table (required for 5.1.1).
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}pvb_visitor_action_log (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			ip_address varchar(100) NOT NULL,
			detected_type varchar(100) NOT NULL,
			country varchar(100) NOT NULL,
			country_iso varchar(100) NOT NULL,
			risk_score varchar(100) NOT NULL,
			blocked_url varchar(255) NOT NULL,
			block_method varchar(100) NOT NULL,
			captcha_passed varchar(100) NOT NULL,
			blocked_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			api_type varchar(100) NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Include and run any necessary post setup functions.
		require_once 'includes/post-additions.php';
		pvb_save_post_function();

		// Set the version after everything is set up.
		update_option( 'pvb_db_version', $current_version );
	} elseif ( $current_version !== $database_version ) {
		// Upgrade DB to 3.0.0 if lower.
		if ( version_compare( $database_version, '3.0.0', '<' ) && version_compare( $database_version, '2.0.1', '>=' ) ) {
			add_option( 'pvb_protect_default_login_page', 'on' );
			add_option( 'pvb_protect_comments', 'on' );
			add_option( 'pvb_log_user_ip_select_box', 'on' );
			update_option( 'pvb_db_version', '3.0.0' );
		}

		// Upgrade DB to 3.2.0 if lower.
		if ( version_compare( $database_version, '3.2.0', '<' ) && version_compare( $database_version, '3.0.0', '>=' ) ) {
			if ( '' === get_option( 'pvb_proxycheckio_CLOUDFLARE_select_box' ) ) {
				add_option( 'pvb_option_ip_header_type', 'REMOTE_ADDR' );
			} elseif ( 'on' === get_option( 'pvb_proxycheckio_CLOUDFLARE_select_box' ) ) {
				add_option( 'pvb_option_ip_header_type', 'HTTP_CF_CONNECTING_IP' );
			}
			update_option( 'pvb_db_version', '3.2.0' );
		}

		// Upgrade DB to 3.3.1 if lower.
		if ( version_compare( $database_version, '3.3.1', '<' ) && version_compare( $database_version, '3.2.0', '>=' ) ) {
			if ( ! empty( get_option( 'pvb_proxycheckio_custom_blocked_page' ) ) ) {
				$custom_block_page = get_option( 'pvb_proxycheckio_custom_blocked_page' );

				if ( is_array( $custom_block_page ) ) {
					$url     = $custom_block_page[0];
					$page_id = url_to_postid( $url );

					if ( ! empty( $url ) ) {
						update_option( 'pvb_proxycheckio_custom_blocked_page', $page_id );
					}
				}
			}
			update_option( 'pvb_db_version', '3.3.1' );
		}

		// Upgrade DB to 4.0.2 if lower.
		if ( version_compare( $database_version, '4.0.2', '<' ) && version_compare( $database_version, '3.3.1', '>=' ) ) {
			if ( ! empty( get_option( 'pvb_proxycheckio_blocked_select_pages_field' ) ) ) {
				$select_pages = get_option( 'pvb_proxycheckio_blocked_select_pages_field' );

				if ( is_array( $select_pages ) ) {
					foreach ( $select_pages as $select_page ) {
						update_post_meta( $select_page, '_pvb_checkbox_block_on_post', '1' );
					}
				}
			}
			if ( ! empty( get_option( 'pvb_proxycheckio_blocked_select_posts_field' ) ) ) {
				$select_posts = get_option( 'pvb_proxycheckio_blocked_select_posts_field' );

				if ( is_array( $select_posts ) ) {
					foreach ( $select_posts as $select_post ) {
						update_post_meta( $select_post, '_pvb_checkbox_block_on_post', '1' );
					}
				}
			}

			pvb_save_post_function();

			delete_option( 'pvb_proxycheckio_blocked_select_pages_field' );
			delete_option( 'pvb_proxycheckio_blocked_select_posts_field' );

			$custom_blocked_page = get_option( 'pvb_proxycheckio_custom_blocked_page' );
			if ( ! empty( $custom_blocked_page[0] ) && '' === get_option( 'pvb_proxycheckio_redirect_bad_visitor' ) ) {
				update_option( 'pvb_proxycheckio_opt_redirect_url', get_permalink( $custom_blocked_page[0] ) );
				update_option( 'pvb_proxycheckio_redirect_bad_visitor', 'on' );
			}

			delete_option( 'pvb_proxycheckio_custom_blocked_page' );
			update_option( 'pvb_db_version', '4.0.2' );
		}

		// Upgrade DB to 5.0.0 if lower.
		if ( version_compare( $database_version, '5.0.0', '<' ) && version_compare( $database_version, '4.0.4', '>=' ) ) {
			require_once 'includes/post-additions.php';
			pvb_save_post_function();

			delete_option( 'pvb_blocked_posts_array' );
			delete_option( 'pvb_blocked_pages_array' );
			delete_option( 'pvb_blocked_permalinks_array' );

			update_option( 'pvb_db_version', '5.0.0' );
		}

		// Upgrade DB to 5.1.1 if lower.
		if ( version_compare( $database_version, '5.1.1', '<' ) && version_compare( $database_version, '5.0.0', '>=' ) ) {
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}pvb_visitor_action_log (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				ip_address varchar(100) NOT NULL,
				detected_type varchar(100) NOT NULL,
				country varchar(100) NOT NULL,
				country_iso varchar(100) NOT NULL,
				risk_score varchar(100) NOT NULL,
				blocked_url varchar(255) NOT NULL,
				block_method varchar(100) NOT NULL,
				captcha_passed varchar(100) NOT NULL,
				blocked_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			update_option( 'pvb_db_version', '5.1.1' );
		}

		// Upgrade DB to 5.1.2 if lower.
		if ( version_compare( $database_version, '5.1.2', '<' ) && version_compare( $database_version, '5.1.1', '>=' ) ) {
			update_option( 'pvb_cache_buster', 'on' );

			update_option( 'pvb_db_version', '5.1.2' );
		}

		// Upgrade DB to 5.2.0 if lower.
		if ( version_compare( $database_version, '5.2.3', '<' ) && version_compare( $database_version, '5.1.2', '>=' ) ) {
			$charset_collate = $wpdb->get_charset_collate();

			// Add the new api_type column to the existing table.
			$sql = "ALTER TABLE {$wpdb->prefix}pvb_visitor_action_log ADD COLUMN api_type varchar(100) NOT NULL;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$wpdb->query( $sql );

			update_option( 'pvb_db_version', '5.2.3' );
		}
	}
}
add_action( 'init', 'upgrade_pvb_db' );
