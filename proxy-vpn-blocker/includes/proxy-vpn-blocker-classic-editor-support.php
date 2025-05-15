<?php
/**
 * Proxy & VPN Blocker Classic Editor Support
 *
 * @package  Proxy & VPN Blocker Premium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if the Gutenberg Block Editor is active.
 *
 * @param int|null $post_id Optional. Post ID to check.
 * @return bool True if the block editor is active, false if classic editor.
 */
function pvb_is_gutenberg_active( $post_id = null ) {
	// If the function doesn't exist, Gutenberg is not available.
	if ( ! function_exists( 'use_block_editor_for_post' ) ) {
		return false;
	}

	// Handle edit post screen.
	if ( $post_id ) {
		return use_block_editor_for_post( $post_id );
	}

	// Editing existing post.
	if ( isset( $_GET['post'] ) ) {
		$post_id = absint( $_GET['post'] );
		return use_block_editor_for_post( $post_id );
	}

	// Creating new post.
	if ( isset( $_GET['post_type'] ) ) {
		$post_type = sanitize_key( $_GET['post_type'] );
		return use_block_editor_for_post_type( $post_type );
	}

	// Fallback: if Classic Editor plugin is active and forcing classic editor.
	if ( class_exists( 'Classic_Editor' ) ) {
		$editor_option = get_option( 'classic-editor-replace' ); // 'block', 'classic', 'no-replace'

		if ( 'classic' === $editor_option || 'no-replace' === $editor_option ) {
			return false; // Classic editor is being enforced
		}
	}

	// Default to Gutenberg.
	return true;
}

add_action( 'add_meta_boxes', function() {
	if ( ! pvb_is_gutenberg_active() ) {
		// Add your meta box for Classic Editor
		$post_types = array( 'post', 'page' );
		foreach ( $post_types as $pt ) {
			add_meta_box(
				'pvb_classic_checkbox_meta_box',
				__( 'Proxy & VPN Blocker', 'proxy-vpn-blocker' ),
				'pvb_render_classic_checkbox_meta_box',
				$pt,
				'side'
			);
		}
	}
});

function pvb_render_classic_checkbox_meta_box( $post ) {
	$value = get_post_meta( $post->ID, '_pvb_checkbox_block_on_post', true );
	wp_nonce_field( 'pvb_classic_meta_box_nonce', 'pvb_classic_meta_box_nonce_field' );
	?>
	<p>
		<label>
			<input type="checkbox" name="_pvb_checkbox_block_on_post" value="1" <?php checked( $value, '1' ); ?> />
			<?php _e( 'Block on this Page/Post', 'pvb' ); ?>
		</label>
	</p>
	<?php
}

add_action( 'save_post', function( $post_id ) {
	if ( ! isset( $_POST['pvb_classic_meta_box_nonce_field'] ) || ! wp_verify_nonce( $_POST['pvb_classic_meta_box_nonce_field'], 'pvb_classic_meta_box_nonce' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_pvb_checkbox_block_on_post', isset( $_POST['_pvb_checkbox_block_on_post'] ) ? '1' : '' );
});
