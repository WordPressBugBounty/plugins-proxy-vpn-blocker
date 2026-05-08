<?php
/**
 * Handler for Proxy & VPN Blocker Review Messaging
 *
 * @package Proxy & VPN Blocker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get review stats for the admin notice.
 *
 * @return array
 */
function pvb_get_review_stats() {
	global $wpdb;
	$log_table = $wpdb->prefix . 'pvb_visitor_action_log';
	$stats     = get_transient( 'pvb_review_stats' );
	if ( ! $stats ) {
		$thirty_days_ago   = gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) );
		$fourteen_days_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-14 days' ) );
		$stats_1           = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT ( SELECT COUNT(*) FROM {$log_table} WHERE blocked_at >= %s ) AS total_blocks, ( SELECT COUNT(DISTINCT ip_address) FROM {$log_table} WHERE blocked_at >= %s ) AS unique_ips",
				$thirty_days_ago,
				$thirty_days_ago
			),
			ARRAY_A
		);
		$stats_2           = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT ( SELECT COUNT(*) FROM {$log_table} WHERE blocked_at >= %s ) AS total_blocks, ( SELECT COUNT(DISTINCT ip_address) FROM {$log_table} WHERE blocked_at >= %s ) AS unique_ips",
				$fourteen_days_ago,
				$fourteen_days_ago
			),
			ARRAY_A
		);
		$stats_arr['total_blocks_30'] = $stats_1['total_blocks'] ?? 0;
		$stats_arr['unique_ips_30']   = $stats_1['unique_ips'] ?? 0;
		$stats_arr['total_blocks_14'] = $stats_2['total_blocks'] ?? 0;
		$stats_arr['unique_ips_14']   = $stats_2['unique_ips'] ?? 0;
		set_transient( 'pvb_review_stats', $stats_arr, HOUR_IN_SECONDS );
		$stats = $stats_arr;
	}
	return $stats;
}

/**
 * Display the review banner across all WP Admin pages.
 */
function pvb_review_admin_notice() {
	// Only show to administrators.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( get_option( 'pvb_review_banner_dismissed', false ) ) {
		return;
	}
	if ( empty( get_option( 'pvb_proxycheckio_API_Key_field' ) ) ) {
		return;
	}
	$pvb_api_key_details = get_option( 'pvb_proxycheck_apikey_details' );
	if ( empty( $pvb_api_key_details ) ) {
		return;
	}
	$current_date    = new DateTime();
	$activation_date = DateTime::createFromFormat( 'Y-m-d', $pvb_api_key_details['activation_date'] );
	if ( ! $activation_date || $current_date <= $activation_date ) {
		return;
	}
	$interval    = $current_date->diff( $activation_date );
	$stats       = pvb_get_review_stats();
	$show_banner = false;
	$tier        = $pvb_api_key_details['tier'] ?? 'Free';
	if ( 'Free' === $tier && $interval->days >= 30 ) {
		$show_banner = true;
		$days        = 30;
	} elseif ( 'Paid' === $tier && $interval->days >= 14 ) {
		$show_banner = true;
		$days        = 14;
	}
	if ( ! $show_banner ) {
		return;
	}

	// Only show if there are actual blocks to report (minimum threshold).
	$min_blocks_threshold = 5;
	if ( $stats[ 'total_blocks_' . $days ] < $min_blocks_threshold ) {
		return;
	}

	$site_name    = esc_html( get_bloginfo( 'name' ) );
	$period_text  = ( 30 === $days ) ? __( 'Over the past 30 days', 'proxy-vpn-blocker' ) : __( 'Over the past 14 days', 'proxy-vpn-blocker' );
	$total_blocks = number_format( $stats[ 'total_blocks_' . $days ] );
	$unique_ips   = number_format( $stats[ 'unique_ips_' . $days ] );
	$has_stats    = $stats[ 'total_blocks_' . $days ] > 0;
	$nonce        = wp_create_nonce( 'pvb_dismiss_review_nonce' );
	?>
	<style>
	.pvbrvwwrap {
		max-width: 1250px;
		box-sizing: border-box;
		margin: 10px auto;
		display: flex;
		flex-direction: row;
		border-radius: 7px;
		overflow: hidden;
		position: relative;
		box-shadow: 0 4px 8px 0 rgba(0,0,0,.1),0 6px 20px 0 rgba(0,0,0,.1)
	}

	.pvbrvwwrap::after {
		content: "";
		position: absolute;
		inset: 0;
		background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px);
		background-size: 22px 22px;
		z-index: 1;
		pointer-events: none;
		animation: pvb-bgdrift 18s linear infinite;
	}

	@keyframes pvb-bgdrift {
		0%   { background-position: 0 0; }
		100% { background-position: 44px 44px; }
	}

	.pvbrvwwrap .pvbrvwwrapright {
		flex: 1;
		position: relative;
		z-index: 2;
		display: flex;
		align-items: stretch;
		overflow: hidden;
	}

	.pvbrvwwrap .pvbrvwwraptext {
		padding: 18px 48px 18px 20px;
		flex: 1;
	}

	.pvbrvwwrap .pvbrvwwraptext p {
		margin: 0 0 8px 0;
		font-size: 15px;
		font-weight: 400;
		line-height: 1.55;
	}

	.pvbrvwwrap .pvbdonatedismiss {
		position: absolute;
		top: 12px;
		right: 12px;
		border-radius: 50%;
		width: 32px;
		height: 32px;
		cursor: pointer;
		display: flex;
		justify-content: center;
		font-size: 22px;
		font-weight: 900;
		padding: 0;
		line-height: 1;
		transition: background 0.2s, color 0.2s;
		border: none;
	}

	.pvbrvwwrap h2.pvb-headline {
		font-size: 18px;
		font-weight: 700;
		margin-top: -5px;
		margin-bottom: 18px;
	}

	.pvbrvwwrap .pvb-stats-badge {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		border-radius: 20px;
		padding: 4px 12px;
		margin-bottom: 10px;
		font-size: 16px;
		font-weight: 600;
		max-width: 1000px !important;
	}

	.pvbrvwwrap .pvb-stats-badge i {
		font-size: 16px;
	}

	.pvbrvwwrap .pvb-review-link {
		display: inline-flex;
		align-items: center;
		gap: 7px;
		font-weight: 700;
		font-size: 14px;
		padding: 7px 16px;
		border-radius: 20px;
		text-decoration: none;
		margin: 8px 0;
		transition: all 0.2s;
	}

	.pvbrvwwrap .pvb-review-link:hover {
		transform: translateY(-1px);
	}

	.pvbrvwwrap .pvb-upgrade-row {
		margin-top: 4px;
		padding-top: 10px;
		font-size: 11.5px;
	}

	.pvbrvwwrap .pvb-upgrade-row a {
		font-weight: 600;
		text-decoration: underline;
		text-underline-offset: 2px;
		transition: text-decoration-color 0.2s;
	}

	@media screen and (max-width: 782px) {
		.pvbrvwwrap .pvbrvwwraptext {
			padding: 15px 44px 15px 15px;
		}
	}

	/* ============================================================
	Proxy and VPN Blocker - Review Banner (theme / colours)
	============================================================ */

	.pvbrvwwrap {
		background: linear-gradient(120deg, #0c4a6e 0%, #0e7490 55%, #164e63 100%);
		box-shadow: 0 6px 24px rgba(12, 74, 110, 0.35), 0 2px 8px rgba(12, 74, 110, 0.2);
	}

	.pvbrvwwrap .pvbrvwwraptext p {
		color: rgba(255, 255, 255, 0.88);
	}

	.pvbrvwwrap .pvb-headline {
		color: #ffffff;
	}

	.pvbrvwwrap .pvbdonatedismiss {
		background: rgba(255, 255, 255, 0.12);
		border: 1px solid rgba(255, 255, 255, 0.22);
		color: rgba(255, 255, 255, 0.75);
	}

	.pvbrvwwrap .pvbdonatedismiss:hover {
		background: rgba(255, 255, 255, 0.24);
		color: #ffffff;
	}

	.pvbrvwwrap .pvb-stats-badge {
		background: rgba(56, 189, 248, 0.18);
		border: 1px solid rgba(56, 189, 248, 0.35);
		color: #bae6fd;
	}

	.pvbrvwwrap .pvb-stats-badge i {
		color: #38bdf8;
	}

	.pvbrvwwrap .pvb-review-link {
		background: #38bdf8;
		color: #0c2d48 !important;
		box-shadow: 0 2px 12px rgba(56, 189, 248, 0.45);
	}

	.pvbrvwwrap .pvb-review-link:hover {
		background: #7dd3fc;
		box-shadow: 0 4px 18px rgba(56, 189, 248, 0.6);
		color: #0c2d48 !important;
	}

	.pvbrvwwrap .pvb-upgrade-row {
		border-top: 1px solid rgba(255, 255, 255, 0.12);
		color: rgba(255, 255, 255, 0.65);
	}

	.pvbrvwwrap .pvb-upgrade-row a {
		color: #7dd3fc !important;
		text-decoration-color: rgba(125, 211, 252, 0.4);
	}

	.pvbrvwwrap .pvb-upgrade-row a:hover {
		text-decoration-color: #7dd3fc;
	}
	</style>
	<div class="pvbrvwwrap" id="pvb-review-banner">
		<div class="pvbrvwwrapright">
			<button class="pvbdonatedismiss" id="pvbdonationclosebutton" title="<?php esc_attr_e( 'Close', 'proxy-vpn-blocker' ); ?>">
				x
			</button>
			<div class="pvbrvwwraptext">
				<h2 class="pvb-headline">
					<?php
						printf(
							/* translators: %s is the site name. */
							esc_html__( 'Thank you for using Proxy & VPN Blocker on %s! 🎉', 'proxy-vpn-blocker' ),
							esc_html( $site_name )
						);
					?>
				</h2>
				<?php if ( $has_stats ) : ?>
				<p class="pvb-stats-badge">
					<i class="fa-solid fa-chart-simple"></i>
					<?php
						printf(
							/* translators: %1$s is the time period (e.g. "Over the past 30 days"), %2$s is the total number of blocked requests, %3$s is the number of unique IP addresses. */
							esc_html__( '%1$s, %2$s requests were blocked from %3$s unique IP Addresses, helping to keep your site secure.', 'proxy-vpn-blocker' ),
							esc_html( $period_text ),
							'<strong>' . esc_html( $total_blocks ) . '</strong>',
							'<strong>' . esc_html( $unique_ips ) . '</strong>'
						);
					?>
				</p>
				<?php endif; ?>
				<p><?php esc_html_e( 'If our plugin has made a difference to you, would you take a moment to share your experience? Your review helps us grow and lets other WordPress users discover a tool that works.', 'proxy-vpn-blocker' ); ?></p>
				<a class="pvb-review-link" href="https://wordpress.org/support/plugin/proxy-vpn-blocker/reviews/#new-post" target="_blank" rel="noopener noreferrer">
					<i class="fa-solid fa-star"></i>
					<?php esc_html_e( 'Leave a quick review, it only takes a minute!', 'proxy-vpn-blocker' ); ?>
				</a>
				<p class="pvb-upgrade-row">
					<?php esc_html_e( 'Want more protection?', 'proxy-vpn-blocker' ); ?>
					<br />
					<a href="https://proxyvpnblocker.com/premium-pricing-and-options/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Upgrade to Proxy & VPN Blocker Premium', 'proxy-vpn-blocker' ); ?></a><br />
				</p>
				<?php if ( 'Free' === $tier ) : ?>
				<p class="pvb-upgrade-row">
					<?php esc_html_e( 'You are currently on the proxycheck.io Free tier', 'proxy-vpn-blocker' ); ?><br />
					<?php esc_html_e( 'Check out the ', 'proxy-vpn-blocker' ); ?>
					<a href="https://proxyvpnblocker.com/discounted-plans/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Discounted proxycheck.io Query Plans', 'proxy-vpn-blocker' ); ?></a>
					<?php esc_html_e( ' or save even more with a ', 'proxy-vpn-blocker' ); ?>
					<a href="https://proxyvpnblocker.com/premium-pricing-and-options/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Proxy & VPN Blocker Premium + proxycheck.io Query Plan bundle', 'proxy-vpn-blocker' ); ?></a>
				</p>
				<?php endif; ?>
				<p style="margin-top:8px; font-size:14px; color:rgba(255,255,255,0.6);">
					<?php esc_html_e( 'Thank you for your support! 💙', 'proxy-vpn-blocker' ); ?>
				</p>
			</div>
		</div>
	</div>
	<script>
	(function() {
		var btn    = document.getElementById('pvbdonationclosebutton');
		var banner = document.getElementById('pvb-review-banner');
		if (!btn || !banner) return;
		btn.addEventListener('click', function() {
			banner.style.display = 'none';
			var xhr = new XMLHttpRequest();
			xhr.open('POST', <?php echo json_encode( admin_url( 'admin-ajax.php' ) ); ?>);
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			xhr.send('action=pvb_dismiss_review_banner&nonce=<?php echo esc_js( $nonce ); ?>');
		});
	})();
	</script>
	<?php
}
add_action( 'admin_notices', 'pvb_review_admin_notice' );

/**
 * AJAX handler to dismiss the review banner permanently.
 */
function pvb_dismiss_review_banner() {
	// Verify nonce with proper error handling.
	if ( ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ?? '' ), 'pvb_dismiss_review_nonce' ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Security check failed. Please try again.', 'proxy-vpn-blocker' ) ),
			403
		);
	}

	// Require administrator privileges.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Insufficient permissions.', 'proxy-vpn-blocker' ) ),
			403
		);
	}

	// Use update_option with autoload=false for better performance.
	$result = update_option( 'pvb_review_banner_dismissed', true, false );

	if ( $result ) {
		wp_send_json_success(
			array(
				'message' => __( 'Review banner dismissed successfully.', 'proxy-vpn-blocker' ),
				'dismissed' => true,
			)
		);
	} else {
		wp_send_json_error(
			array( 'message' => __( 'Failed to dismiss banner. Please try again.', 'proxy-vpn-blocker' ) )
		);
	}
}
add_action( 'wp_ajax_pvb_dismiss_review_banner', 'pvb_dismiss_review_banner' );
add_action( 'wp_ajax_nopriv_pvb_dismiss_review_banner', 'pvb_dismiss_review_banner' );
