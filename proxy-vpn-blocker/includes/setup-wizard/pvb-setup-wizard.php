<?php
/**
 * Proxy & VPN Blocker- Setup Wizard
 *
 * @package  Proxy & VPN Blocker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Detects available headers for IP detection.
 *
 * @return array List of detected headers with their availability and priority.
 */
function detect_available_headers() {
	$headers = array();

	// Always available.
	$headers[] = array(
		'key'       => 'REMOTE_ADDR',
		'label'     => __( 'Standard IP Detection', 'proxy-vpn-blocker' ),
		'available' => true,
		'priority'  => 1,
	);

	// Check for CloudFlare.
	if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
		$headers[] = array(
			'key'       => 'HTTP_CF_CONNECTING_IP',
			'label'     => __( 'CloudFlare IP Detection', 'proxy-vpn-blocker' ),
			'available' => true,
			'priority'  => 10,
		);
	}

	// Check for other common headers.
	if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$headers[] = array(
			'key'       => 'HTTP_X_FORWARDED_FOR',
			'label'     => __( 'Proxy/Load Balancer IP Detection', 'proxy-vpn-blocker' ),
			'available' => true,
			'priority'  => 5,
		);
	}

	if ( isset( $_SERVER['HTTP_X_REAL_IP'] ) ) {
		$headers[] = array(
			'key'       => 'HTTP_X_REAL_IP',
			'label'     => __( 'Real IP Header Detection', 'proxy-vpn-blocker' ),
			'available' => true,
			'priority'  => 6,
		);
	}

	return $headers;
}

/**
 * Gets the recommended header based on priority.
 *
 * @param array $headers List of detected headers.
 * @return array Recommended header with additional reasoning.
 */
function get_recommended_header( $headers ) {
	// Sort by priority (highest first).
	usort(
		$headers,
		function ( $a, $b ) {
			return $b['priority'] - $a['priority'];
		}
	);

	$recommended = $headers[0];

	// Add reasoning.
	switch ( $recommended['key'] ) {
		case 'HTTP_CF_CONNECTING_IP':
			$recommended['reason'] = __( 'CloudFlare detected - this will get the real visitor IP address.', 'proxy-vpn-blocker' );
			break;
		case 'HTTP_X_FORWARDED_FOR':
			$recommended['reason'] = __( 'Proxy/CDN detected - this should get the real visitor IP address.', 'proxy-vpn-blocker' );
			break;
		case 'HTTP_X_REAL_IP':
			$recommended['reason'] = __( 'Real IP header detected - this should get the actual visitor IP address.', 'proxy-vpn-blocker' );
			break;
		default:
			$recommended['reason'] = __( 'Standard IP detection - works for most hosting environments.', 'proxy-vpn-blocker' );
	}

	return $recommended;
}


?>
<div class="wrap" id="<?php $this->parent->_token; ?>_pvb-setup-wizard">
	<div class="pvb-setup-container">
		<h1>Proxy & VPN Blocker Setup Wizard</h1>
		<div class="pvb-progress-bar-wrapper">
			<div class="pvb-progress-bar">
				<div class="pvb-progress-bar-fill"></div>
			</div>
			<div class="pvb-progress-percent">0% Complete</div>
		</div>
		<div class="pvb-setup-container-inner">
			<form id="pvb-setup-form">
				<div class="pvb-steps-wrapper">
					<!-- Step 1: Welcome -->
					<div class="pvb-step active" data-step="1">
						<div class="step-number"><?php esc_html_e( 'Step 1', 'proxy-vpn-blocker' ); ?></div>
						<div class="pvb-settings-tabs-logo"><img src="<?php echo plugins_url( '../../assets/img/pvb-logo-large.png ', __FILE__ ); ?>" alt="Proxy & VPN Blocker Free Logo" /></div>
						<h1><?php esc_html_e( 'Thank you for installing Proxy & VPN Blocker!', 'proxy-vpn-blocker' ); ?></h1>
						<h2><?php esc_html_e( 'This wizard will help you quickly configure the most important settings and features - it\'ll only take a couple of minutes.', 'proxy-vpn-blocker' ); ?></h2>
						<p><?php esc_html_e( 'If you wish, you may skip this step and go directly to the regular settings page.', 'proxy-vpn-blocker' ); ?></p>
					</div>

					<!-- Step 2: Proxycheck.io API Key -->
					<div class="pvb-step hidden" data-step="2">
						<div class="step-number"><?php esc_html_e( 'Step 2', 'proxy-vpn-blocker' ); ?></div>
						<h2><?php esc_html_e( 'Your proxycheck.io API Key', 'proxy-vpn-blocker' ); ?></h2>
						<p><?php esc_html_e( 'Please enter your proxycheck.io API Key', 'proxy-vpn-blocker' ); ?></p>
						<div class="main-option">
							<div class="option-header">
								<div>
									<h3 class="option-title"><?php esc_html_e( 'proxycheck.io API Key', 'proxy-vpn-blocker' ); ?></h3>
									<span class="optional-badge"><?php esc_html_e( 'Optional', 'proxy-vpn-blocker' ); ?></span>
									<input type="text" autocomplete="off" placeholder="XXXXXX-XXXXXX-XXXXXX-XXXXXX" spellcheck="false" id="pvb_proxycheckio_API_Key_field" name="pvb_proxycheckio_API_Key_field" value="<?php echo esc_attr( get_option( 'pvb_proxycheckio_API_Key_field' ) ); ?>" class="regular-text">
									<p class="option-description">
										<?php esc_html_e( 'Enter your proxycheck.io API key to enable advanced proxy and VPN detection features. If you don\'t have an API key, you can still use the service with limited queries.', 'proxy-vpn-blocker' ); ?>
									</p>
									<button class="details-toggle" type="button">
										<span><?php esc_html_e( 'View details', 'proxy-vpn-blocker' ); ?></span>
										<svg class="chevron" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
											<path d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06z"/>
										</svg>
									</button>
									<div class="details-content">
										<p><?php esc_html_e( 'The proxycheck.io service offers different query limits based on your API key:', 'proxy-vpn-blocker' ); ?></p>
										<ul>
											<li><?php esc_html_e( 'No API Key - 100 daily queries', 'proxy-vpn-blocker' ); ?></li>
											<li><?php esc_html_e( 'Free API Key - 1,000 daily queries', 'proxy-vpn-blocker' ); ?></li>
											<li><?php esc_html_e( 'Paid API Key - 10,000 to 10.24M daily queries', 'proxy-vpn-blocker' ); ?></li>
										</ul>
										<p><strong><?php esc_html_e( 'Getting Started:', 'proxy-vpn-blocker' ); ?></strong> <?php esc_html_e( 'You can use the service immediately without an API key, but you\'ll be limited to 100 queries per day. For higher limits, get your free API key from <a href="https://proxycheck.io">proxycheck.io</a>, which provides 1,000 daily queries.', 'proxy-vpn-blocker' ); ?></p>
										<p><strong><?php esc_html_e( 'Need More Queries?', 'proxy-vpn-blocker' ); ?></strong> <?php esc_html_e( 'Upgrade to a paid plan with exclusive discounts available from the <a href="https://proxyvpnblocker.com/discounted-plans/" target="_blank">Proxy & VPN Blocker Website</a>.', 'proxy-vpn-blocker' ); ?></p>
									</div>
								</div>
							</div>
						</div>
						<p><?php esc_html_e( 'Next we will configure some basic options regarding IP Detection and Blocking.', 'proxy-vpn-blocker' ); ?></p>
					</div>

					<!-- Step 3: IP Detection and Blocking Options -->
					<div class="pvb-step hidden" data-step="3">
						<div class="step-number"><?php esc_html_e( 'Step 3', 'proxy-vpn-blocker' ); ?></div>
						<h2><?php esc_html_e( 'IP Detection and Blocking Options', 'proxy-vpn-blocker' ); ?></h2>
						<p><?php esc_html_e( 'Proxy & VPN Blocker offers a range of blocking options to protect your site from unwanted visitors. Here are some key features:', 'proxy-vpn-blocker' ); ?></p>
						<div class="main-option">
							<div class="option-header">
								<div>
									<h3 class="option-title"><?php esc_html_e( 'Detect VPNs', 'proxy-vpn-blocker' ); ?></h3>
									<span class="optional-badge"><?php esc_html_e( 'Optional', 'proxy-vpn-blocker' ); ?></span>
									<?php pvb_render_toggle_switch( 'pvb_proxycheckio_VPN_select_box', 'pvb_proxycheckio_VPN_select_box' ); ?>
									<p class="option-description">
										<?php esc_html_e( 'Enables the detection of users of Virtual Private Networks (VPNs).', 'proxy-vpn-blocker' ); ?>
									</p>
									<button class="details-toggle" type="button">
										<span><?php esc_html_e( 'View details', 'proxy-vpn-blocker' ); ?>/span>
										<svg class="chevron" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
											<path d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06z"/>
										</svg>
									</button>
									<div class="details-content">
										<div class="detail-section">
											<div class="detail-title"><?php esc_html_e( 'What Detect VPNs does', 'proxy-vpn-blocker' ); ?></div>
											<div class="detail-text"><?php esc_html_e( 'This setting enables VPN detection in addition to the regular detection of Proxies, Tor & Mysterium Network Nodes and other compromised IP Address sources.', 'proxy-vpn-blocker' ); ?></div>
										</div>
										<div class="detail-section">
											<div class="detail-title"><?php esc_html_e( 'VPN Detection', 'proxy-vpn-blocker' ); ?></div>
											<div class="detail-text"><?php esc_html_e( 'When enabled, Proxy & VPN Blocker will use proxycheck.io to detect VPN users. If a user is detected as using a VPN, they will be blocked from accessing chosen pages/posts and other selected resources on your site.', 'proxy-vpn-blocker' ); ?></div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="main-option">
							<div class="option-header">
								<div>
									<h3 class="option-title"><?php esc_html_e( 'Use IP Risk Scores', 'proxy-vpn-blocker' ); ?></h3>
									<span class="optional-badge"><?php esc_html_e( 'Optional', 'proxy-vpn-blocker' ); ?></span>
									<?php pvb_render_toggle_switch( 'pvb_proxycheckio_risk_select_box', 'pvb_proxycheckio_risk_select_box' ); ?>
									<p class="option-description">
										<?php esc_html_e( 'Enables the use of proxycheck.io IP Risk Score data to determine the likelihood of an IP being a threat. This helps in making informed decisions about blocking or allowing access. During setup this will use the default of 66 for VPN and 33 for Proxy, you may set the risk score threshold for Proxy or VPN in Proxy & VPN Blocker settings later.', 'proxy-vpn-blocker' ); ?>
									</p>
									<button class="details-toggle" type="button">
										<span><?php esc_html_e( 'View details', 'proxy-vpn-blocker' ); ?></span>
										<svg class="chevron" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
											<path d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06z"/>
										</svg>
									</button>
									<div class="details-content">
										<div class="detail-section">
											<div class="detail-title"><?php esc_html_e( 'Risk Score', 'proxy-vpn-blocker' ); ?></div>
											<div class="detail-text"><?php esc_html_e( 'Risk scores are a Risk rating between 0 and 100, for blocking purposes when using Risk Scores the default threshold is 66 for VPN and 33 for proxy, this means that a VPN with a risk score of 66 or above would be blocked and a risk score below 66 would be allowed.', 'proxy-vpn-blocker' ); ?></div>
										</div>
										<div class="detail-section">
											<div class="detail-title"><?php esc_html_e( 'Logging Caveat', 'proxy-vpn-blocker' ); ?></div>
											<div class="detail-text"><?php esc_html_e( 'When using this feature your proxycheck.io positive detection log may not reflect what has actually been blocked by this plugin because they would still be positively detected, but the action will be taken by Proxy & VPN Blocker based on the IP Risk Score. IP\'s allowed through with the risk score feature are not cached as Known Good IP\'s.', 'proxy-vpn-blocker' ); ?></div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<p><?php esc_html_e( 'Next we will configure some  options regarding User IP logging.', 'proxy-vpn-blocker' ); ?></p>
					</div>

					<!-- Step 4: User IP Logging -->
					<div class="pvb-step hidden" data-step="4">
						<div class="step-number"><?php esc_html_e( 'Step 4', 'proxy-vpn-blocker' ); ?></div>
						<h2><?php esc_html_e( 'User IP Logging', 'proxy-vpn-blocker' ); ?></h2>
						<p><?php esc_html_e( 'Choose whether to log user security information for better site protection', 'proxy-vpn-blocker' ); ?></p>
						<div class="main-option">
							<div class="option-header">
								<div>
									<h3 class="option-title"><?php esc_html_e( 'Enable User IP Logging', 'proxy-vpn-blocker' ); ?></h3>
									<span class="recommended-badge"><?php esc_html_e( 'Recommended', 'proxy-vpn-blocker' ); ?></span>
									<?php pvb_render_toggle_switch( 'pvb_log_user_ip_select_box', 'pvb_log_user_ip_select_box', true ); ?>
									<p class="option-description">
										<?php esc_html_e( 'Logs basic security information when users interact with your site. This helps maintain better security and makes whitelist/blacklist management easier.', 'proxy-vpn-blocker' ); ?>
									</p>
									<button class="details-toggle" type="button">
										<span><?php esc_html_e( 'View details', 'proxy-vpn-blocker' ); ?></span>
										<svg class="chevron" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
											<path d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06z"/>
										</svg>
									</button>
									<div class="details-content">
										<div class="detail-section">
											<div class="detail-title"><?php esc_html_e( 'What gets logged:', 'proxy-vpn-blocker' ); ?></div>
											<div class="detail-text"><?php esc_html_e( 'IP addresses, countries, risk scores (for blocked users), and timestamps. Visible to administrators in the Action Log, User Profiles, and Users List.', 'proxy-vpn-blocker' ); ?></div>
										</div>

										<div class="detail-section">
											<div class="detail-title"><?php esc_html_e( 'Management benefits:', 'proxy-vpn-blocker' ); ?></div>
											<div class="detail-text"><?php esc_html_e( 'The Action Log includes a quick \'Add to Whitelist\' option for blocked IPs, making it easy to approve IP Addresses when needed.', 'proxy-vpn-blocker' ); ?></div>
										</div>

										<div class="privacy-notice">
											<div style="display: flex; align-items: flex-start;">
												<span class="privacy-icon">⚠️</span>
												<div class="privacy-text">
													<strong><?php esc_html_e( 'Privacy Consideration:', 'proxy-vpn-blocker' ); ?></strong> <?php esc_html_e( 'You are responsible for ensuring compliance with your local privacy laws before enabling IP logging.', 'proxy-vpn-blocker' ); ?>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<p><?php esc_html_e( 'Next we will determine how we detect the IP Address of visitors.', 'proxy-vpn-blocker' ); ?></p>
					</div>

					<!-- Step 5: IP Address Detection Setup -->
					<div class="pvb-step hidden" data-step="5">
						<div class="step-number"><?php esc_html_e( 'Step 5', 'proxy-vpn-blocker' ); ?></div>
						<h2><?php esc_html_e( 'IP Address Detection Setup', 'proxy-vpn-blocker' ); ?></h2>
						<p><?php esc_html_e( 'We need to detect visitor IP addresses correctly for security checks. We\'ve automatically detected your server configuration and recommend the best setting for your environment.', 'proxy-vpn-blocker' ); ?></p>
						
						<?php
						// Get detected headers and recommendation.
						$detected_headers   = detect_available_headers();
						$recommended_header = get_recommended_header( $detected_headers );
						?>
						
						<div class="main-option">
							<div class="option-header">
								<div>
									<h3 class="option-title"><?php esc_html_e( 'IP Header Configuration', 'proxy-vpn-blocker' ); ?></h3>
									<span class="recommended-badge"><?php esc_html_e( 'Recommended', 'proxy-vpn-blocker' ); ?></span>
									
									<?php if ( $recommended_header ): ?>
										<div class="recommended-setting">
											<label class="header-option recommended">
												<input type="radio" name="pvb_option_ip_header_type" value="<?php echo esc_attr( $recommended_header['key'] ); ?>" checked>
												<div class="option-content">
													<strong><?php echo esc_html( $recommended_header['label'] ); ?></strong>
													<p class="option-description"><?php echo esc_html( $recommended_header['reason'] ); ?></p>
												</div>
											</label>
										</div>
									<?php endif; ?>
									
									<button class="details-toggle" type="button">
										<span><?php esc_html_e( 'Show advanced options', 'proxy-vpn-blocker' ); ?></span>
										<svg class="chevron" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
											<path d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06z"/>
										</svg>
									</button>
									
									<div class="details-content">
										<div class="detail-section">
											<div class="detail-title"><?php esc_html_e( 'Other Available Options:', 'proxy-vpn-blocker' ); ?></div>
											<div class="header-options">
												<?php foreach ( $detected_headers as $header ): ?>
													<?php if ( $header['key'] !== $recommended_header['key'] ): ?>
														<label class="header-option">
															<input type="radio" name="pvb_option_ip_header_type" value="<?php echo esc_attr( $header['key'] ); ?>">
															<div class="option-content">
																<strong><?php echo esc_html( $header['label'] ); ?></strong>
																<p class="option-description">
																	<?php
																	switch ( $header['key'] ) {
																		case 'REMOTE_ADDR':
																			_e( 'Standard server IP detection - works for most basic hosting.', 'proxy-vpn-blocker' );
																			break;
																		case 'HTTP_X_FORWARDED_FOR':
																			_e( 'For load balancers and some CDNs (may contain multiple IPs).', 'proxy-vpn-blocker' );
																			break;
																		case 'HTTP_X_REAL_IP':
																			_e( 'Alternative real IP header used by some reverse proxies.', 'proxy-vpn-blocker' );
																			break;
																		default:
																			_e( 'Custom header detected on your server.', 'proxy-vpn-blocker' );
																	}
																	?>
																</p>
															</div>
														</label>
													<?php endif; ?>
												<?php endforeach; ?>
											</div>
										</div>
										
										<div class="detail-section">
											<div class="detail-title"><?php esc_html_e( 'Why this matters:', 'proxy-vpn-blocker' ); ?></div>
											<div class="detail-text">
												<?php esc_html_e( 'If you\'re using a CDN (like CloudFlare) or load balancer, the wrong setting will show the CDN server\'s IP instead of your visitor\'s real IP address, which would prevent proper security checking.', 'proxy-vpn-blocker' ); ?>
											</div>
										</div>
										
										<div class="privacy-notice">
											<div style="display: flex; align-items: flex-start;">
												<span class="privacy-icon">⚠️</span>
												<div class="privacy-text">
													<strong><?php esc_html_e( 'Note:', 'proxy-vpn-blocker' ); ?></strong> <?php esc_html_e( 'You can change this setting later in the plugin\'s main settings if needed.', 'proxy-vpn-blocker' ); ?>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<p><?php esc_html_e( 'Next we\'ll proceed to cache detection and page caching options.', 'proxy-vpn-blocker' ); ?></p>
					</div>

					<!-- Step 6: Cache Detection & Page Caching Protection -->
					<div class="pvb-step hidden" data-step="6">
						<div class="step-number"><?php esc_html_e( 'Step 6', 'proxy-vpn-blocker' ); ?></div>
						<h2><?php esc_html_e( 'Cache Detection & Page Caching Protection', 'proxy-vpn-blocker' ); ?></h2>
						<p><?php esc_html_e( 'To ensure visitor blocking works correctly, we need to detect if your site uses page caching. If caching is detected, we recommend enabling the Cache Buster option.', 'proxy-vpn-blocker' ); ?></p>
						<?php
						$caching_plugins = pvb_detect_caching_plugins();
						$server_cache    = pvb_detect_server_cache();
						$cache_detected  = ! empty( $caching_plugins ) || $server_cache;

						// Convert boolean to the expected string format for the form handler.
						$cache_buster_value = $cache_detected ? 'on' : '';

						// Also check if there's already a saved value in the database.
						$saved_value = get_option( 'pvb_cache_buster', '' );
						if ( '' !== $saved_value ) {
							$cache_buster_value = $saved_value;
						}
						?>
						<div class="main-option">
							<div class="option-header">
								<div>
									<h3 class="option-title"><?php esc_html_e( 'Enable Cache Buster (DONOTCACHEPAGE Header)', 'proxy-vpn-blocker' ); ?></h3>
									<?php if ( $cache_detected ): ?>
										<span class="recommended-badge"><?php esc_html_e( 'Recommended', 'proxy-vpn-blocker' ); ?></span>
									<?php else: ?>
										<span class="optional-badge"><?php esc_html_e( 'Optional', 'proxy-vpn-blocker' ); ?></span>
									<?php endif; ?>
									<?php pvb_render_toggle_switch( 'pvb_cache_buster', 'pvb_cache_buster', $cache_buster_value ); ?>
									<?php if ( $cache_detected ): ?>
										<p class="option-description">
											<?php esc_html_e( 'We detected potential caching on your site. Enabling this option helps prevent cached pages from interfering with visitor blocking by setting DONOTCACHEPAGE headers on pages you have selected for blocking with Proxy & VPN Blocker.', 'proxy-vpn-blocker' ); ?>
										</p>
									<?php else: ?>
										<p class="option-description">
											<?php esc_html_e( 'No caching detected, but you may enable this option if you use caching plugins or server-side caching. This will set DONOTCACHEPAGE headers on the pages you have selected for blocking with Proxy & VPN Blocker.', 'proxy-vpn-blocker' ); ?>
										</p>
									<?php endif; ?>
									<button class="details-toggle" type="button">
										<span><?php esc_html_e( 'View details', 'proxy-vpn-blocker' ); ?></span>
										<svg class="chevron" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
											<path d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06z"/>
										</svg>
									</button>
									<div class="details-content">
										<div class="detail-section">
											<div class="detail-title"><?php esc_html_e( 'What does this do?', 'proxy-vpn-blocker' ); ?></div>
											<div class="detail-text"><?php esc_html_e( 'Setting the DONOTCACHEPAGE header tells caching plugins and servers not to cache the page, ensuring visitor blocking works reliably.', 'proxy-vpn-blocker' ); ?></div>
										</div>
										<?php if ( !empty($caching_plugins) ): ?>
											<div class="detail-section">
												<div class="detail-title"><?php esc_html_e( 'Detected Caching Plugins:', 'proxy-vpn-blocker' ); ?></div>
												<div class="detail-text"><?php echo esc_html( implode(', ', $caching_plugins) ); ?></div>
											</div>
										<?php endif; ?>
										<?php if ( $server_cache ): ?>
											<div class="detail-section">
												<div class="detail-title"><?php esc_html_e( 'Server-side Caching Detected', 'proxy-vpn-blocker' ); ?></div>
												<div class="detail-text"><?php esc_html_e( 'We detected server-side caching headers. This may affect visitor blocking.', 'proxy-vpn-blocker' ); ?></div>
											</div>
										<?php endif; ?>
										<div class="privacy-notice">
											<div style="display: flex; align-items: flex-start;">
												<span class="privacy-icon">⚠️</span>
												<div class="privacy-text">
													<strong><?php esc_html_e( 'Note:', 'proxy-vpn-blocker' ); ?></strong> <?php esc_html_e( 'You can change this setting later in the plugin\'s main settings if needed.', 'proxy-vpn-blocker' ); ?>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<p><?php esc_html_e( 'Next, we\'ll finish the setup!', 'proxy-vpn-blocker' ); ?></p>
					</div>

					<div class="pvb-step hidden" data-step="7">
						<div class="step-number"><?php esc_html_e( 'Step 7', 'proxy-vpn-blocker' ); ?></div>
						<div id="pvb-setup-complete"><i class="fa-fw fa-solid fa-check"></i></div>
						<h1><?php esc_html_e( 'Almost There!', 'proxy-vpn-blocker' ); ?></h1>
						<p><?php esc_html_e( 'The most important settings are configured and Proxy & VPN Blocker is now ready to start protecting your site! Click below to save the settings and then you will be directed to the full Proxy & VPN Blocker Settings page.', 'proxy-vpn-blocker' ); ?></p>
					</div>
				</div>

				<div class="pvb-navigation">
					<div class="pvb-left-buttons">
						<button type="button" data-nav="prev" class="pvbsecondary with-left-icon" data-prev style="display:none;"><i class="fa-fw fa-solid fa-angle-left"></i>  <?php esc_html_e( 'Back', 'proxy-vpn-blocker' ); ?></button>
					</div>
					<div class="pvb-right-buttons">
						<button type="button" class="pvbsecondary" data-skip><?php esc_html_e( 'Skip Setup', 'proxy-vpn-blocker' ); ?> </button>
						<button type="button" data-nav="next" class="pvbdefault with-right-icon" data-next> <?php esc_html_e( 'Continue', 'proxy-vpn-blocker' ); ?>  <i class="fa-fw fa-solid fa-angle-right"></i></button>
						<button type="submit" class="pvbdefault" style="display:none;"><?php esc_html_e( 'Finish Setup', 'proxy-vpn-blocker' ); ?></button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<?php
/**
 * Render a toggle switch for options.
 *
 * @param string $option_name The name of the option to toggle.
 * @param string $field_id The ID for the input field.
 * @param bool   $default_on Whether the switch should be on by default.
 */
function pvb_render_toggle_switch( $option_name, $field_id, $default_on = false ) {
	$data = get_option( $option_name );

	// If option doesn't exist yet, fall back to default.
	if ( false === $data ) {
		$data = $default_on ? 'on' : '';
	}

	$checked = ( $data === 'on' ) ? 'checked' : '';

	echo '<div class="onoffswitch-container">';
	echo '<div class="onoffswitch">';
	// Hidden fallback field.
	echo '<input type="hidden" name="' . esc_attr( $option_name ) . '" value="">';
	// Actual checkbox.
	echo '<input autocomplete="off" tabindex="0" class="onoffswitch-checkbox" type="checkbox" name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field_id ) . '" ' . $checked . '>';
	echo '<label class="onoffswitch-label" for="' . esc_attr( $field_id ) . '">';
	echo '<span class="onoffswitch-inner"></span>';
	echo '<span class="onoffswitch-switch"></span>';
	echo '</label>';
	echo '</div>';
	echo '</div>';
}

/**
 * Detects server-level caching.
 *
 * @return bool True if server caching is detected, false otherwise.
 */
function pvb_detect_server_cache() {
	$headers       = array_change_key_case( headers_list(), CASE_LOWER );
	$cache_headers = array( 'x-cache', 'x-litespeed-cache', 'x-wp-cf-super-cache', 'x-proxy-cache', 'x-nginx-cache' );
	foreach ( $cache_headers as $header ) {
		foreach ( $headers as $h ) {
			if ( strpos( strtolower( $h ), $header ) !== false ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Detects active caching plugins.
 *
 * @return array List of detected caching plugins.
 */
function pvb_detect_caching_plugins() {
	$plugins         = get_option( 'active_plugins', array() );
	$caching_plugins = array(
		'wp-super-cache/wp-cache.php'         => 'WP Super Cache',
		'w3-total-cache/w3-total-cache.php'   => 'W3 Total Cache',
		'wp-rocket/wp-rocket.php'             => 'WP Rocket',
		'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache',
		'cache-enabler/cache-enabler.php'     => 'Cache Enabler',
		'sg-cachepress/sg-cachepress.php'     => 'SiteGround Optimizer',
		'autoptimize/autoptimize.php'         => 'Autoptimize',
	);

	$detected = array();
	foreach ( $caching_plugins as $file => $name ) {
		if ( in_array( $file, $plugins ) ) {
			$detected[] = $name;
		}
	}
	return $detected;
}
