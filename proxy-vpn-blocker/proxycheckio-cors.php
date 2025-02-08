<?php
/**
 * Extended proxycheck.io CORS functions with WordPress AJAX support
 *
 * @package Proxy & VPN Blocker
 */

/**
 * Adds proxycheck.io CORS to site header with extended functionality.
 */
function pvb_cors_javascript() {
	?>
	<script>
		(function() {
			// Configuration object to store settings
			const pvbConfig = {
				current_site: location.hostname,
				cors_blocked_sites: [
					<?php
					if ( 'on' === get_option( 'pvb_cors_integration' ) ) {
						$site_url = wp_parse_url( site_url() );
						print '"' . esc_html( $site_url['host'] ) . '", ';
					}
					if ( 'on' === get_option( 'pvb_CORS_protect_on_webcache' ) ) {
						print '"webcache.googleusercontent.com", "cc.bingj.com", "web.archive.org"';
					}
					?>
				],
				ajaxurl: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
				proxycheck_key: '<?php echo esc_html( get_option( 'pvb_proxycheckio_CORS_public_key' ) ); ?>',
				settings: Object.freeze({
					risk_enabled: <?php echo 'on' === get_option( 'pvb_proxycheckio_risk_select_box' ) ? 'true' : 'false'; ?>,
					max_risk_proxy: <?php echo (int) get_option( 'pvb_proxycheckio_max_riskscore_proxy', 66 ); ?>,
					max_risk_vpn: <?php echo (int) get_option( 'pvb_proxycheckio_max_riskscore_vpn', 33 ); ?>,
					vpn_detection: <?php echo 'on' === get_option( 'pvb_proxycheckio_VPN_select_box' ) ? 'true' : 'false'; ?>,
					redirect_enabled: <?php echo 'on' === get_option( 'pvb_proxycheckio_redirect_bad_visitor' ) ? 'true' : 'false'; ?>,
					redirect_url: '<?php echo esc_url( get_option( 'pvb_proxycheckio_opt_redirect_url' ) ); ?>',
					denied_message: '<?php echo esc_js( get_option( 'pvb_proxycheckio_denied_access_field', 'Access Denied' ) ); ?>',
				})
			};

			// Private functions inside closure
			function handleProxyResponse(data) {
				if (!data || typeof data !== 'object') return;

				if (data.status === "warning" || (data.status === "ok" && data[data.ip]?.proxy === "yes")) {
					const proxyData = data[data.ip];
					
					if (pvbConfig.settings.risk_enabled && proxyData?.risk) {
						const riskScore = parseInt(proxyData.risk);
						const isVPN = proxyData.type === 'VPN';
						const maxRisk = isVPN ? pvbConfig.settings.max_risk_vpn : pvbConfig.settings.max_risk_proxy;

						if (riskScore >= maxRisk) {
							blockAccess();
							return;
						}
					} else {
						blockAccess();
					}
				}

				if (data.status === "ok" && data[data.ip]?.proxy === "yes") {
					logDetectionToWordPress(data);
				}
			}

			// Function to block access
			function blockAccess() {
				if (pvbConfig.settings.redirect_enabled && pvbConfig.settings.redirect_url) {
					window.location.href = pvbConfig.settings.redirect_url;
				} else {
					document.body.innerHTML = `<div style="text-align: center;"><h1>${pvbConfig.settings.denied_message}</h1></div>`;
				}
			}

			// Function to log detection to WordPress
			function logDetectionToWordPress(data) {
				if (!data || !data.ip) return;

				// First fetch a fresh nonce
				fetch(pvbConfig.ajaxurl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: new URLSearchParams({
						action: 'pvb_get_fresh_nonce'
					})
				})
				.then(response => response.json())
				.then(nonceData => {
					if (!nonceData.success || !nonceData.data) {
						throw new Error('Failed to get nonce');
					}

					const proxyData = data[data.ip];
					const logData = {
						action: 'pvb_log_cors_detection',
						security: nonceData.data,  // Use fresh nonce
						ip: data.ip,
						type: proxyData?.type || 'unknown',
						country: proxyData?.country || 'unknown',
						country_iso: proxyData?.isocode || 'unknown',
						risk: proxyData?.risk || '0',
						page_url: window.location.href
					};

					return fetch(pvbConfig.ajaxurl, {
						method: 'POST',
						credentials: 'same-origin',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded',
						},
						body: new URLSearchParams(logData)
					});
				})
				.catch(error => console.error('PVB CORS Logging Error:', error));
			}

			// Run check immediately and add mutation observer to detect DOM changes
			function initCheck() {
				if (!pvbConfig.cors_blocked_sites.includes(pvbConfig.current_site)) return;

				const apiUrl = `https://proxycheck.io/v2/?key=${pvbConfig.proxycheck_key}&vpn=${pvbConfig.settings.vpn_detection ? 1 : 0}&asn=1&risk=1`;

				const xhr = new XMLHttpRequest();
				xhr.open('GET', apiUrl, true);
				xhr.onload = function() {
					if (xhr.status >= 200 && xhr.status < 400) {
						const data = JSON.parse(xhr.responseText);
						handleProxyResponse(data);
					}
				};
				<?php if ( 'on' === get_option( 'pvb_CORS_antiadblock' ) ) { ?>
					xhr.onerror = function(e) {
						document.body.innerHTML = 'Please deactivate your adblocker to access this page.';
						window.stop();
					};
				<?php } ?>
				xhr.send();
			}

			// Run immediately
			initCheck();

			// Watch for DOM changes that might indicate a page update without refresh
			const observer = new MutationObserver((mutations) => {
				for (const mutation of mutations) {
					if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
						initCheck();
						break;
					}
				}
			});

			observer.observe(document.body, { 
				childList: true,
				subtree: true 
			});
		})();
	</script>
	<noscript>
		<style>body { display: none; }</style>
		<h1>JavaScript is disabled</h1>
		<p>Please enable JavaScript to access this page.</p>
	</noscript>
	<?php
}


// Load CORS JavaScript across the entire site if the setting is enabled.
if ( 'on' === get_option( 'pvb_cors_integration' ) && ! empty( get_option( 'pvb_proxycheckio_CORS_public_key' ) ) && 'on' === get_option( 'pvb_proxycheckio_all_pages_activation' ) ) {
	add_action( 'wp_footer', 'pvb_cors_javascript' );
}

/**
 * Adds proxycheck.io CORS to site header on specified Pages & Posts.
 */
function add_pvb_cors() {
	$blocked_ids = get_option( 'pvb_blocked_pages_ids_array' );

	if ( ! empty( $blocked_ids ) ) {
		foreach ( $blocked_ids as $blocked_id ) {
			if ( is_page( $blocked_id ) || is_single( $blocked_id ) ) {
				add_action( 'wp_head', 'pvb_cors_javascript' );
				break; // No need to continue looping once the action is added.
			}
		}
	}
}

// Load CORS JavaScript on specified pages and posts if the setting is enabled.
if ( 'on' === get_option( 'pvb_cors_integration' ) && ! empty( get_option( 'pvb_proxycheckio_CORS_public_key' ) ) ) {
	add_action( 'get_header', 'add_pvb_cors', 1 );
}

/**
 * AJAX handler for logging CORS detections
 */
function pvb_handle_cors_detection() {
	check_ajax_referer( 'pvb_cors_nonce', 'security' );

	// Ensure the logging function exists.
	if ( ! function_exists( 'pvb_log_action' ) ) {
		wp_send_json_error( array( 'message' => 'Logging function not available' ) );
		return;
	}

	// Validate and sanitize input.
	$ip          = isset( $_POST['ip'] ) ? sanitize_text_field( wp_unslash( $_POST['ip'] ) ) : '';
	$type        = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
	$country     = isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '';
	$country_iso = isset( $_POST['country_iso'] ) ? sanitize_text_field( wp_unslash( $_POST['country_iso'] ) ) : '';
	$risk        = isset( $_POST['risk'] ) ? intval( $_POST['risk'] ) : 0;
	$page_url    = isset( $_POST['page_url'] ) ? esc_url_raw( wp_unslash( $_POST['page_url'] ) ) : '';

	// Validate IP address.
	if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
		wp_send_json_error( array( 'message' => 'Invalid IP address' ) );
		return;
	}

	// Validate risk score.
	if ( $risk < 0 || $risk > 100 ) {
		wp_send_json_error( array( 'message' => 'Invalid risk score' ) );
		return;
	}

	// Use existing logging function.
	pvb_log_action( $ip, $type, $country, $country_iso, $risk, $page_url, 'CORS' );

	wp_send_json_success();
}

if ( 'on' === get_option( 'pvb_log_user_ip_select_box' ) ) {
	add_action( 'wp_ajax_pvb_log_cors_detection', 'pvb_handle_cors_detection' );
	add_action( 'wp_ajax_nopriv_pvb_log_cors_detection', 'pvb_handle_cors_detection' );
}

/**
 * Generate a fresh nonce.
 */
function pvb_get_fresh_nonce() {
	wp_send_json_success( wp_create_nonce( 'pvb_cors_nonce' ) );
}
add_action( 'wp_ajax_pvb_get_fresh_nonce', 'pvb_get_fresh_nonce' );
add_action( 'wp_ajax_nopriv_pvb_get_fresh_nonce', 'pvb_get_fresh_nonce' );
