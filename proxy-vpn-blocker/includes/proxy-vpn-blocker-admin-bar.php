<?php
/**
 * Adds Proxy & VPN Blocker to WordPress Admin Bar.
 *
 * @package Proxy & VPN Blocker.
 */

/**
 * AJAX handler for updating the toolbar state and post meta.
 */
function pvb_admin_toolbar_ajax_handler() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_pages' ) ) {
		return;
	}

	// Verify nonce.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'pvb_admin_toolbar_ajax_nonce' ) ) {
		wp_send_json_error( 'Invalid nonce' );
		return;
	}

	// Get the post ID.
	if ( ! isset( $_POST['post_id'] ) ) {
		return;
	}

	$post_id = intval( $_POST['post_id'] );

	// Toggle the checkbox meta value if request is to update.
	if ( isset( $_POST['toggle'] ) && filter_var( wp_unslash( $_POST['toggle'] ), FILTER_VALIDATE_BOOLEAN ) ) {
		$checkbox_value = get_post_meta( $post_id, '_pvb_checkbox_block_on_post', true );
		if ( '1' === $checkbox_value ) {
			$updated = update_post_meta( $post_id, '_pvb_checkbox_block_on_post', '' );
		} else {
			$updated = update_post_meta( $post_id, '_pvb_checkbox_block_on_post', '1' );
		}

		if ( ! $updated ) {
			wp_send_json_error( 'Failed to update post meta' );
			return;
		}

		pvb_save_post_function();
	}

	// Fetch updated state.
	$checkbox_value = get_post_meta( $post_id, '_pvb_checkbox_block_on_post', true );
	$post_edit_link = admin_url( 'post.php?post=' . $post_id . '&action=edit' );

	$cache_status = ( 'on' === get_option( 'pvb_cache_buster' ) ) ? 'DONOTCACHEPAGE Active' : '';

	$toolbar_state = array(
		'checkbox_value' => $checkbox_value,
		'post_edit_link' => $post_edit_link,
		'cache_status'   => $cache_status,
		'is_shop'        => function_exists( 'is_shop' ) && is_shop(),
	);

	wp_send_json_success( $toolbar_state );
}
add_action( 'wp_ajax_pvb_admin_toolbar', 'pvb_admin_toolbar_ajax_handler' );


/**
 * Preloader for Proxy & VPN Blocker Admin Toolbar Items Innitial State before AJAX takes over.
 *
 * @param name $wp_admin_bar the wp admin toolbar object.
 */
function pvb_element_admin( $wp_admin_bar ) {
	if ( ! is_admin() && is_user_logged_in() ) {
		if ( current_user_can( 'manage_options' ) || current_user_can( 'edit_pages' ) ) {
			// Get current Post or Page ID.
			$post = strval( get_the_ID() );

			// Fix for WooCommerce.
			if ( function_exists( 'is_shop' ) && is_shop() ) {
				$post = wc_get_page_id( 'shop' );
			}

			// Get current checkbox value for post meta.
			$checkbox_value = get_post_meta( $post, '_pvb_checkbox_block_on_post', true );

			// Create an edit link for post/page.
			$post_edit_link = admin_url( 'post.php?post=' . $post . '&action=edit' );

			// Create menu and set default icon, AJAX alters this based on the meta value.
			$args = array(
				'id'    => 'proxy_vpn_blocker',
				'title' => '<img src="' . plugins_url( 'assets/img/pvb-grey-dot.svg', __DIR__ ) . '" alt="Proxy & VPN Blocker (Loading...)" style="width: 16px; height: 16px; padding-top: 10px;">',
			);
			$wp_admin_bar->add_node( $args );

			$args = array(
				'id'     => 'block_method',
				'title'  => 'Loading...',
				'parent' => 'proxy_vpn_blocker',
				'meta'   => array( 'class' => 'pvb-admin-toolbar-text' ),
			);
			$wp_admin_bar->add_node( $args );

			$args = array(
				'id'     => 'cache_status',
				'title'  => 'Loading...',
				'parent' => 'proxy_vpn_blocker',
				'meta'   => array( 'class' => 'pvb-admin-toolbar-text' ),
			);
			$wp_admin_bar->add_node( $args );

			$args = array(
				'parent' => 'proxy_vpn_blocker',
				'id'     => 'pvb-toggle-block-post',
				'title'  => 'Loading...',
				'href'   => '#',
				'meta'   => array( 'class' => 'pvb_admin_toolbar_ajax' ),
			);
			$wp_admin_bar->add_node( $args );

			$args = array(
				'id'     => 'prompt',
				'title'  => 'Change blocking method',
				'href'   => $post_edit_link,
				'parent' => 'proxy_vpn_blocker',
			);
			$wp_admin_bar->add_node( $args );
		}
	}
}
add_action( 'admin_bar_menu', 'pvb_element_admin', 999 );

/**
 * Enqueue script for Proxy & VPN Blocker Admin Toolbar AJAX handling.
 */
function pvb_admin_toolbar_scripts() {
	if ( is_user_logged_in() ) {
		if ( current_user_can( 'manage_options' ) || current_user_can( 'edit_pages' ) ) {
			$post_id = get_the_ID();

			if ( function_exists( 'is_shop' ) && is_shop() ) {
				$post_id = wc_get_page_id( 'shop' );
			}

			if ( 'on' === get_option( 'pvb_cache_buster' ) ) {
				$cache_status = 'DONOTCACHEPAGE Active';
			} else {
				$cache_status = '';
			}

			wp_enqueue_script( 'pvb-admin-toolbar-script', plugin_dir_url( __DIR__ ) . 'assets/js/pvb-admin-toolbar-script.js', array( 'jquery' ), get_option( 'proxy_vpn_blocker_version' ), true );

			// Pass nonce and admin-ajax URL to JavaScript.
			wp_localize_script(
				'pvb-admin-toolbar-script',
				'pvb_admin_toolbar',
				array(
					'nonce'        => wp_create_nonce( 'pvb_admin_toolbar_ajax_nonce' ),
					'ajax_url'     => admin_url( 'admin-ajax.php' ),
					'post_id'      => $post_id,
					'cache_status' => $cache_status,
					'plugin_url'   => plugin_dir_url( __DIR__ ),
				)
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'pvb_admin_toolbar_scripts' );

/**
 * CSS to alter text colours of Proxy & VPN Blocker Admin Toolbar..
 */
function pvb_add_admin_toolbar_css() {
	?>
	<style>
		li.pvb-admin-toolbar-text {
			background: #293035 !important;
		}
		li.pvb-admin-toolbar-text > .ab-item {
			color: #ba57ec !important;
		}
	</style>
	<?php
}
add_action( 'admin_bar_menu', 'pvb_add_admin_toolbar_css' );
