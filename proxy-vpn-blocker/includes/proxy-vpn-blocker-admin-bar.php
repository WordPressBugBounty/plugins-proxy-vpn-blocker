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

			$icon_svg = '
			<svg width="52" height="60" viewBox="0 0 52 60" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M28.1159 1.38621C25.6666 2.51806 22.0809 3.82799 19.1576 4.65896C15.8069 5.61141 11.2227 6.51859 7.89683 6.88743C6.53243 7.03873 6.2528 7.11604 6.09766 7.38472C5.96812 7.60904 5.96715 29.2312 6.09654 30.6735C6.34925 33.4896 7.00441 35.9029 8.14016 38.2013C10.8133 43.6107 16.1903 48.5551 25.2777 53.9602C26.7726 54.8494 28.7552 55.9458 28.962 55.9979C29.13 56.0401 30.2408 55.4513 32.4819 54.1319C43.2407 47.798 48.9044 42.1118 51.0383 35.502C51.3196 34.6306 51.5789 33.5225 51.8017 32.2392L51.9562 31.3492L51.9781 19.4388L52 7.52862L51.8491 7.3387C51.766 7.23419 51.6252 7.12137 51.5361 7.0878C51.4471 7.0543 50.6297 6.9311 49.7198 6.81406C42.354 5.86632 35.7513 4.03369 29.7996 1.28495C29.4592 1.12777 29.1287 0.999517 29.0652 1C29.0017 1.00056 28.5745 1.17435 28.1159 1.38621ZM29.6097 4.90744C30.9074 5.5541 33.5901 6.60317 35.5969 7.24865C38.9951 8.34167 43.1653 9.33446 46.7155 9.89558C47.6003 10.0355 48.3647 10.1616 48.4144 10.176C48.4877 10.1973 48.5001 12.1418 48.4804 20.5335C48.454 31.7523 48.478 31.0245 48.0722 32.8942C47.7017 34.6007 46.7232 36.9952 45.7929 38.4719C44.8547 39.9612 43.3794 41.732 41.9663 43.0652C39.2678 45.6112 35.1754 48.5027 30.3032 51.3059L29.0688 52.0162L27.8901 51.3454C20.7662 47.2908 15.6502 43.1763 12.9425 39.3237C11.1396 36.7587 10.0555 33.8904 9.66683 30.657C9.59541 30.0632 9.57527 27.7247 9.57527 20.0332V10.1708L9.99493 10.1157C10.9892 9.98507 13.527 9.5493 14.7806 9.29384C19.6832 8.29481 24.8569 6.64705 28.3202 4.98171C28.6664 4.81525 28.9899 4.67641 29.0389 4.67322C29.0879 4.67004 29.3448 4.77545 29.6097 4.90744ZM28.5269 8.24858C27.1119 10.0358 25.8154 12.2294 24.9776 14.2535C24.6469 15.0522 24.0676 16.782 24.1149 16.8288C24.1301 16.8438 24.5317 16.5489 25.0073 16.1734C25.954 15.426 26.9162 14.7033 26.9646 14.7033C26.9815 14.7033 27.0026 18.3863 27.0114 22.8878L27.0274 31.0723L28.0457 32.0655C28.6057 32.6118 29.0756 33.0479 29.0898 33.0345C29.1041 33.0212 29.5584 32.5766 30.0994 32.0467L31.083 31.0831V22.8932C31.083 18.3888 31.0997 14.7033 31.12 14.7033C31.1404 14.7033 31.8013 15.2002 32.5888 15.8073C33.3763 16.4145 34.0207 16.9002 34.0207 16.8867C34.0207 16.786 33.3283 14.7586 33.1332 14.2881C32.613 13.0335 31.7191 11.3773 30.8169 9.99683C30.244 9.12004 29.1405 7.64358 29.0581 7.64358C29.0294 7.64358 28.7903 7.9158 28.5269 8.24858ZM20.6614 16.0441C18.3732 18.0252 16.5264 20.582 15.5306 23.1474C15.253 23.8626 14.9817 24.7482 15.0266 24.7926C15.042 24.8079 15.3012 24.6656 15.6023 24.4764C16.4259 23.959 17.3114 23.4242 17.3446 23.4242C17.3608 23.4242 17.3742 25.7056 17.3744 28.4941L17.3748 33.564L18.7499 34.706C19.5061 35.3341 21.135 36.689 22.3695 37.7168C23.6041 38.7446 25.1569 40.0349 25.8202 40.5841L27.0263 41.5827L27.0273 44.2524L27.0284 46.9221L28.0561 47.8689L29.0838 48.8157L30.0776 47.8689L31.0715 46.9221L31.0772 44.2484L31.083 41.5745L31.3191 41.3963C31.5915 41.1908 35.773 37.7422 38.76 35.2597L40.8005 33.564L40.8028 28.4861L40.8052 23.4083L40.9976 23.5342C41.536 23.8867 43.0746 24.8007 43.0952 24.7803C43.1082 24.7674 42.9757 24.3246 42.8006 23.7964C42.2363 22.0936 41.0858 20.0524 39.7821 18.4409C39.2709 17.8089 37.7166 16.2411 37.1157 15.7512L36.7485 15.4518V23.548V31.6442L35.2272 32.9133C34.3905 33.6113 32.9662 34.8066 32.0622 35.5696C31.1582 36.3327 30.1165 37.2105 29.7474 37.5206L29.0763 38.0842L27.3344 36.6312C26.3763 35.8321 24.6404 34.3818 23.4769 33.4083L21.3614 31.6383L21.3436 23.5536L21.3258 15.4688L20.6614 16.0441Z" fill="white"/>
				<circle cx="12.5" cy="47.5" r="12.5" fill="#B5B5B5"/>
			</svg>
			';

			// Create menu and set default icon, AJAX alters this based on the meta value.
			$args = array(
				'id'    => 'proxy_vpn_blocker',
				'title' => '<img src="data:image/svg+xml;base64,' . base64_encode( $icon_svg ) . '" alt="Proxy & VPN Blocker (Loading...)" style="width: 16px; height: 16px; padding-top: 10px;">',
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
