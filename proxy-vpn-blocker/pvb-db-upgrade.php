<?php
/**
 * This file runs if older than current DB Version is detected.
 *
 * @package Proxy & VPN Blocker
 */

/**
 * Function to upgrade database.
 */
function upgrade_pvb_db() {
	$database_version = get_option( 'pvb_db_version' );
	$current_version  = '5.0.0';

	if ( $current_version !== $database_version ) {
		// Upgrade DB to 3.0.0 if lower.
		if ( $database_version >= '2.0.1' && $database_version < '3.0.0' ) {
			add_option( 'pvb_protect_default_login_page', 'on' );
			add_option( 'pvb_protect_comments', 'on' );
			add_option( 'pvb_log_user_ip_select_box', 'on' );
			update_option( 'pvb_db_version', '3.0.0' );
		}

		// Upgrade DB to 3.2.0 if lower.
		if ( $database_version >= '3.0.0' && $database_version < '3.2.0' ) {
			if ( '' === get_option( 'pvb_proxycheckio_CLOUDFLARE_select_box' ) ) {
				add_option( 'pvb_option_ip_header_type', 'REMOTE_ADDR' );
			} elseif ( 'on' === get_option( 'pvb_proxycheckio_CLOUDFLARE_select_box' ) ) {
				add_option( 'pvb_option_ip_header_type', 'HTTP_CF_CONNECTING_IP' );
			}
			update_option( 'pvb_db_version', '3.2.0' );
		}

		// Upgrade DB to 3.3.1 if lower.
		if ( $database_version >= '3.2.0' && $database_version < '3.3.1' ) {
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
		if ( $database_version >= '3.3.1' && $database_version < '4.0.2' ) {
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

		// Upgrade DB to 4.0.4 if lower.
		if ( $database_version >= '4.0.2' && $database_version < '4.0.4' ) {
			update_option( 'pvb_db_version', '4.0.4' );
		}

		// Upgrade DB to 5.0.0 if lower.
		if ( $database_version >= '4.0.4' && $database_version < '5.0.0' ) {
			require_once 'includes/post-additions.php';
			pvb_save_post_function();

			delete_option( 'pvb_blocked_posts_array' );
			delete_option( 'pvb_blocked_pages_array' );
			delete_option( 'pvb_blocked_permalinks_array' );

			update_option( 'pvb_db_version', '5.0.0' );
		}

		// Set latest DB version if doesn't exist.
		if ( empty( $database_version ) ) {
			require_once 'includes/post-additions.php';
			pvb_save_post_function();

			update_option( 'pvb_db_version', $current_version );
		}
	}
}
add_action( 'init', 'upgrade_pvb_db' );
