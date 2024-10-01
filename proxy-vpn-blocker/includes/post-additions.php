<?php
/**
 * Proxy & VPN Blocker Post Additions
 *
 * @package  Proxy & VPN Blocker Premium
 */

/**
 * CUSTOM COLUMNS
 */



/**
 * Add custom column to post and page lists.
 *
 * @param string $columns Add our column to WordPress Columns.
 */
function add_restricted_column( $columns ) {
	return array_merge( $columns, array( 'proxy_vpn_blocking' => __( 'Proxy/VPN Blocking', 'proxy-vpn-blocker' ) ) );
}
add_filter( 'manage_post_posts_columns', 'add_restricted_column' );
add_filter( 'manage_page_posts_columns', 'add_restricted_column' );

/**
 * Display content for custom column on posts list.
 *
 * @param string $column_key Check for our Column Key.
 * @param string $post_id check for our meta key using the post_id.
 */
function display_restricted_column_content( $column_key, $post_id ) {
	if ( 'proxy_vpn_blocking' === $column_key ) {
		$restricted = get_post_meta( $post_id, '_pvb_checkbox_block_on_post', true );
		if ( '' === get_option( 'pvb_proxycheckio_all_pages_activation' ) ) {
			if ( '1' === $restricted ) {
				echo '<img src="' . plugins_url( 'assets/img/green-dot.svg', __DIR__ ) . '" alt="Proxy & VPN Blocker (Not Blocking)" style="width: 10px; height: 10px; margin: 0 4px -1px 0;">';
				echo 'Proxies/VPNs Blocked';
			} else {
				echo '<img src="' . plugins_url( 'assets/img/red-dot.svg', __DIR__ ) . '" alt="Proxy & VPN Blocker (Not Blocking)" style="width: 10px; height: 10px; margin: 0 4px -1px 0;">';
				echo 'Proxies/VPNs Not Blocked';
			}
		} else {
			if ( '1' === $restricted ) {
				echo '<span class="dashicons dashicons-yes" style="color:lightgrey;" title="Yes"></span>';

			} else {
				echo '<span class="dashicons dashicons-no" style="color:lightgrey;" title="No"></span>';
			}
			echo '<p style="color:darkorange;">(Block on Entire Site Override)</p>';
		}
	}
}
add_action( 'manage_post_posts_custom_column', 'display_restricted_column_content', 10, 2 );

/**
 * Display content for custom column on pages list.
 *
 * @param string $column_key Check for our Column Key.
 * @param string $post_id check for our meta key using the post_id for the page.
 */
function display_restricted_column_content_pages( $column_key, $post_id ) {
	if ( 'proxy_vpn_blocking' === $column_key ) {
		$restricted = get_post_meta( $post_id, '_pvb_checkbox_block_on_post', true );
		if ( '' === get_option( 'pvb_proxycheckio_all_pages_activation' ) ) {
			if ( '1' === $restricted ) {
				echo '<img src="' . plugins_url( 'assets/img/green-dot.svg', __DIR__ ) . '" alt="Proxy & VPN Blocker (Not Blocking)" style="width: 10px; height: 10px; margin: 0 4px -1px 0;">';
				echo 'Proxies/VPNs Blocked';
			} else {
				echo '<img src="' . plugins_url( 'assets/img/red-dot.svg', __DIR__ ) . '" alt="Proxy & VPN Blocker (Not Blocking)" style="width: 10px; height: 10px; margin: 0 4px -1px 0;">';
				echo 'Proxies/VPNs Not Blocked';
			}
		} else {
			if ( '1' === $restricted ) {
				echo '<span class="dashicons dashicons-yes" style="color:lightgrey;" title="Yes"></span>';

			} else {
				echo '<span class="dashicons dashicons-no" style="color:lightgrey;" title="No"></span>';
			}
			echo '<p style="color:darkorange;">(Block on Entire Site Override)</p>';
		}
	}
}
add_action( 'manage_page_posts_custom_column', 'display_restricted_column_content_pages', 10, 2 );



/**
 * BULK ACTIONS
 */



/**
 * Handle the custom bulk action to set posts as blocked.
 *
 * @param string $bulk_actions The bulk actions list from WordPress.
 */
function pvb_set_posts_bulk_action( $bulk_actions ) {
	$bulk_actions['pvb_set_block_post'] = 'Block Proxies/VPNs';
	return $bulk_actions;
}
add_filter( 'bulk_actions-edit-post', 'pvb_set_posts_bulk_action' );

/**
 * Handle the custom bulk action to set pages as blocked.
 *
 * @param string $bulk_actions The bulk actions list from WordPress.
 */
function pvb_set_pages_bulk_action( $bulk_actions ) {
	$bulk_actions['pvb_set_block_page'] = 'Block Proxies/VPNs';
	return $bulk_actions;
}
add_filter( 'bulk_actions-edit-page', 'pvb_set_pages_bulk_action' );

/**
 * Handle the custom bulk action to set posts or pages as blocked.
 */
function handle_pvb_set_postspages_bulk_action() {
	// Check if the action is set.
	$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';

	// Check if the action is our custom action.
	if ( 'pvb_set_block_post' === $action || 'pvb_set_block_page' === $action ) {

		// You can access selected post IDs using $_REQUEST['post'] array.
		$post_ids = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : array();

		// Perform your custom action logic here.
		foreach ( $post_ids as $post_id ) {
			update_post_meta( $post_id, '_pvb_checkbox_block_on_post', '1' );
		}
		pvb_save_post_function();

		// Determine the post type to redirect to the correct list.
		$post_type = isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : 'post';
		$redirect_url = admin_url( 'edit.php?post_type=' . $post_type );

		// Add a query variable to the redirect URL to indicate the number of blocked posts/pages.
		$redirect_to = add_query_arg( 'bulk_blocked_postspages', count( $post_ids ), $redirect_url );
		wp_redirect( $redirect_to );
		exit;
	}
}
add_action( 'admin_action_pvb_set_block_post', 'handle_pvb_set_postspages_bulk_action' );
add_action( 'admin_action_pvb_set_block_page', 'handle_pvb_set_postspages_bulk_action' );

/**
 * Handle the custom bulk action to unset posts as blocked.
 *
 * @param string $bulk_actions The bulk actions list from WordPress.
 */
function pvb_unset_posts_bulk_action( $bulk_actions ) {
	$bulk_actions['pvb_unset_block_post'] = 'Unblock Proxies/VPNs';
	return $bulk_actions;
}
add_filter( 'bulk_actions-edit-post', 'pvb_unset_posts_bulk_action' );

/**
 * Handle the custom bulk action to unset pages as blocked.
 *
 * @param string $bulk_actions The bulk actions list from WordPress.
 */
function pvb_unset_pages_bulk_action( $bulk_actions ) {
	$bulk_actions['pvb_unset_block_page'] = 'Unblock Proxies/VPNs';
	return $bulk_actions;
}
add_filter( 'bulk_actions-edit-page', 'pvb_unset_pages_bulk_action' );

/**
 * Handle the custom bulk action to unset posts or pages as blocked.
 */
function handle_pvb_unset_postspages_bulk_action() {
	// Check if the action is set.
	$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';

	// Check if the action is our custom action.
	if ( 'pvb_unset_block_post' === $action || 'pvb_unset_block_page' === $action ) {

		// You can access selected post IDs using $_REQUEST['post'] array.
		$post_ids = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : array();

		// Perform your custom action logic here.
		foreach ( $post_ids as $post_id ) {
			update_post_meta( $post_id, '_pvb_checkbox_block_on_post', '' );
		}
		pvb_save_post_function();

		// Determine the post type to redirect to the correct list.
		$post_type = isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : 'post';
		$redirect_url = admin_url( 'edit.php?post_type=' . $post_type );

		// Add a query variable to the redirect URL to indicate the number of blocked posts/pages.
		$redirect_to = add_query_arg( 'bulk_unblocked_postspages', count( $post_ids ), $redirect_url );
		wp_redirect( $redirect_to );
		exit;
	}
}
add_action( 'admin_action_pvb_unset_block_post', 'handle_pvb_unset_postspages_bulk_action' );
add_action( 'admin_action_pvb_unset_block_page', 'handle_pvb_unset_postspages_bulk_action' );



/**
 * ADMIN NOTICES
 */



/**
 * Display an admin notice after performing pvb add post or page actions.
 */
function pvb_bulk_action_add_postpage_admin_notice() {
	if ( ! empty( $_REQUEST['bulk_blocked_postspages'] ) ) {
		$blocked_count = intval( $_REQUEST['bulk_blocked_postspages'] );
		printf(
			'<div id="message" class="updated notice is-dismissible"><p>' .
			_n( '%s post or page has been restricted for blocking in Proxy & VPN Blocker.', '%s posts or pages have been restricted for blocking in Proxy & VPN Blocker.', $blocked_count, 'proxy-vpn-blocker' ) .
			'</p></div>',
			$blocked_count
		);
	}
}
add_action( 'admin_notices', 'pvb_bulk_action_add_postpage_admin_notice' );

/**
 * Display an admin notice after performing the pvb remove post or page actions.
 */
function pvb_bulk_action_remove_postpage_admin_notice() {
	if ( ! empty( $_REQUEST['bulk_unblocked_postspages'] ) ) {
		$unblocked_count = intval( $_REQUEST['bulk_unblocked_postspages'] );
		printf(
			'<div id="message" class="updated notice is-dismissible"><p>' .
			_n( '%s post or page has been unrestricted for blocking in Proxy & VPN Blocker.', '%s posts or pages have been unrestricted for blocking in Proxy & VPN Blocker.', $unblocked_count, 'proxy-vpn-blocker' ) .
			'</p></div>',
			$unblocked_count
		);
	}
}
add_action( 'admin_notices', 'pvb_bulk_action_remove_postpage_admin_notice' );



/**
 * POST AND PAGE ID PROCESSOR FUNCTION
 */



/**
 * Trigger the update of our custom restricted pages/posts arrays.
 */
function pvb_save_post_function() {
	if ( defined( 'DOING_AUTOSAVE' ) ) {
		return;
	}

	$matching_ids = array();
	$batch_size   = 25; // Adjust the batch size as needed.
	$paged        = 1;

	// Meta key for the checkbox field.
	$meta_key = '_pvb_checkbox_block_on_post';
	// Meta value for the checkbox checked state.
	$meta_value = '1'; // Assuming the checkbox value for checked state is '1'.

	do {
		// Query arguments.
		$args = array(
			'post_type'      => array( 'post', 'page' ), // Adjust post types as needed.
			'posts_per_page' => $batch_size, // Retrieve posts in batches.
			'paged'          => $paged,
			'meta_query'     => array(
				array(
					'key'     => $meta_key,
					'value'   => $meta_value,
					'compare' => '=', // Match the exact meta value.
				),
			),
		);

		// Instantiate WP_Query.
		$query = new WP_Query( $args );

		// Check if there are any posts.
		if ( $query->have_posts() ) {
			// Loop through the posts.
			while ( $query->have_posts() ) {
				$query->the_post();
				$matching_ids[] = get_the_ID();
			}
			// Restore global post data.
			wp_reset_postdata();
		}

		++$paged;
	} while ( $query->have_posts() );

	update_option( 'pvb_blocked_pages_ids_array', $matching_ids );
}
add_action( 'save_post', 'pvb_save_post_function' );

/**
 * Update the post meta.
 *
 * @param name $meta_id The ID of the meta in question.
 * @param name $post_id The ID of the post in question.
 * @param name $meta_key The Meta Kay.
 * @param name $meta_value The Meta Value.
 */
function pvb_update_post_meta_function( $meta_id, $post_id, $meta_key, $meta_value ) {
	if ( '_pvb_checkbox_block_on_post' === $meta_key ) {
		pvb_save_post_function();
	}
}
add_action( 'update_post_meta', 'pvb_update_post_meta_function', 10, 4 );
