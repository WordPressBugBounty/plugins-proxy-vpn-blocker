<?php
/**
 * The Proxy & VPN Blocker Debugging Page
 *
 * @package Proxy & VPN Blocker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$allowed_html = array(
	'div'      => array(
		'class' => array(),
		'id'    => array(),
		'style' => array(),
		'dir'   => array(),
	),
	'a'        => array(
		'href'  => array(),
		'title' => array(),
	),
	'i'        => array(
		'class' => array(),
	),
	'script'   => array(
		'type' => array(),
	),
	'form'     => array(
		'class'  => array(),
		'id'     => array(),
		'action' => array(),
		'method' => array(),
		'target' => array(),
	),
	'input'    => array(
		'class'        => array(),
		'id'           => array(),
		'name'         => array(),
		'type'         => array(),
		'title'        => array(),
		'value'        => array(),
		'required'     => array(),
		'placeholder'  => array(),
		'autocomplete' => array(),
	),
	'button'   => array(
		'class'   => array(),
		'id'      => array(),
		'type'    => array(),
		'onclick' => array(),
		'name'    => array(),
		'style'   => array(),
		'value'   => array(),
	),
	'table'    => array(
		'class' => array(),
		'id'    => array(),
	),
	'section'  => array(),
	'header'   => array(),
	'strong'   => array(),
	'h1'       => array(),
	'h2'       => array(
		'class' => array(),
	),
	'h3'       => array(),
	'p'        => array(),
	'textarea' => array(),
	'pre'      => array(),
);


// Get all plugins.
$all_plugins = get_plugins();

// Get active plugins.
$active_plugins = get_option( 'active_plugins' );

// Assemble array of name, version, and whether plugin is active (boolean).
foreach ( $all_plugins as $key => $value ) {
	$is_active = ( in_array( $key, $active_plugins, true ) ) ? 'yes' : 'no';
	if ( 'Proxy & VPN Blocker' === $value['Name'] ) { // Skip as we already have this elsewhere.
		continue;
	}
	$pluginsl[ $key ] = array(
		'name'    => $value['Name'],
		'version' => $value['Version'],
		'active'  => $is_active,
	);
}

if ( ! empty( get_option( 'pvb_proxycheckio_API_Key_field' ) ) || 'on' === get_option( 'pvb_proxycheckio_dummy_data' ) ) {
	$request_args = array(
		'timeout'     => '10',
		'blocking'    => true,
		'httpversion' => '1.1',
	);

	if ( 'on' === get_option( 'pvb_proxycheckio_dummy_data' ) && empty( get_option( 'pvb_proxycheckio_API_Key_field' ) ) ) {
		// Get the dummy data from the Proxy & VPN Blocker Dummy Pool.
		$request_usage = wp_remote_get( dirname( __DIR__ ) . '/dbg/demo_data/proxycheck.daystat.dummy.json' );
		if ( is_wp_error( $request_usage ) ) {
			$api_key_usage = null;
		} else {
			$api_key_usage = json_decode( wp_remote_retrieve_body( $request_usage ) );
		}
	} else {
		// Get and Decrypt API Key.
		$encrypted_key = get_option( 'pvb_proxycheckio_API_Key_field' );
		$get_api_key   = PVB_API_Key_Encryption::decrypt( $encrypted_key );

		// Get the data from the proxycheck dashboard API.
		$request_usage = wp_remote_get( 'https://proxycheck.io/dashboard/export/usage/?key=' . $get_api_key, $request_args );
		$api_key_usage = json_decode( wp_remote_retrieve_body( $request_usage ) );
	}

	if ( isset( $api_key_usage->status ) && 'denied' === $api_key_usage->status ) {
		$proxycheck_api_access_status = 'proxycheck.io Dashboard API Access Disabled. Can\'t get this statistic.';
	} else {
		// Format and Display usage stats.
		$queries_today = $api_key_usage->{'Queries Today'};
		$daily_limit   = $api_key_usage->{'Daily Limit'};
		$queries_total = $api_key_usage->{'Queries Total'};
		$plan_tier     = $api_key_usage->{'Plan Tier'};
		$burst_tokens  = $api_key_usage->{'Burst Tokens Available'};
	}
}

// Check if queries are being answered as expected.
require_once dirname( __DIR__, 2 ) . '/proxycheckio-api-call.php';
if ( ! empty( get_option( 'pvb_option_ip_header_type' ) ) ) {
	$header_type = get_option( 'pvb_option_ip_header_type' );
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
} else {
	$visitor_ip_address = ! empty( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
}

// If dummy data is enabled, use the dummy IP address.
if ( 'on' === get_option( 'pvb_proxycheckio_dummy_data' ) && empty( get_option( 'pvb_proxycheckio_API_Key_field' ) ) ) {
	$visitor_ip_address = '8.8.8.8'; // Dummy IP address.
}

$proxycheck_answer = proxycheck_function( $visitor_ip_address, 1, 1, 0 );

// Is API Key Set?
if ( ! empty( get_option( 'pvb_proxycheckio_API_Key_field' ) ) ) {
	$array_apikey[] = '<div class="row">
					<div class="col left"><strong>API Key Defined:</strong></div>
					<div class="col right">yes</div>
				</div>';
} else {
	$array_apikey[] = '<div class="row">
					<div class="col left"><strong>API Key Defined:</strong></div>
					<div class="col right">no</div>
				</div>';
}
// Plan Tier.
if ( ! isset( $plan_tier ) ) {
	$array_apikey[] = '<div class="row">
					<div class="col left"><strong>Plan Tier:</strong></div>
					<div class="col right">n/a</div>
				</div>';
} else {
	$array_apikey[] = '<div class="row">
					<div class="col left"><strong>Plan Tier:</strong></div>
					<div class="col right">' . $plan_tier . '</div>
				</div>';
}
// Queries Today.
if ( ! isset( $queries_today ) ) {
	$array_apikey[] = '<div class="row">
					<div class="col left"><strong>Queries Today:</strong></div>
					<div class="col right">n/a</div>
				</div>';
} else {
	$array_apikey[] = '<div class="row">
					<div class="col left"><strong>Queries Today:</strong></div>
					<div class="col right">' . number_format( $queries_today ) . '</div>
				</div>';
}
// Daily Query Limit.
if ( ! isset( $daily_limit ) ) {
	$array_apikey[] = '<div class="row">
					<div class="col left"><strong>Daily Query Limit:</strong></div>
					<div class="col right">n/a</div>
				</div>';
} else {
	$array_apikey[] = '<div class="row">
					<div class="col left"><strong>Daily Query Limit:</strong></div>
					<div class="col right">' . number_format( $daily_limit ) . '</div>
				</div>';
}
// Burst Token Available?
if ( ! isset( $burst_tokens ) ) {
	$array_apikey[] = '<div class="row">
					<div class="col left"><strong>Burst Tokens Available:</strong></div>
					<div class="col right">n/a</div>
				</div>';
} else {
	$array_apikey[] = '<div class="row">
					<div class="col left"><strong>Burst Tokens Available:</strong></div>
					<div class="col right">' . $burst_tokens . '</div>
				</div>';
}

// Get versions.
global $wp_version;

$array_versions[] = '<div class="row">
				<div class="col left"><strong>WordPress Version:</strong></div>
				<div class="col right"> ' . $wp_version . '</div>
			</div>';

$array_versions[] = '<div class="row">
				<div class="col left"><strong>PHP version: </strong></div>
				<div class="col right"> ' . phpversion() . '</div>
			</div>';

if ( ! empty( get_option( 'proxy_vpn_blocker_version' ) ) ) {
	$array_versions[] = '<div class="row">
					<div class="col left"><strong>Proxy & VPN Blocker Version:</strong></div>
					<div class="col right">' . get_option( 'proxy_vpn_blocker_version' ) . '</div>
				</div>';
}
if ( ! empty( get_option( 'pvb_db_version' ) ) ) {
	$array_versions[] = '<div class="row">
					<div class="col left"><strong>Proxy & VPN Blocker Database Version:</strong></div>
					<div class="col right">' . get_option( 'pvb_db_version' ) . '</div>
				</div>';
}

// Generate page/post slugs.
if ( ! empty( get_option( 'pvb_proxycheckio_blocked_select_pages_field' ) ) ) {
	foreach ( get_option( 'pvb_proxycheckio_blocked_select_pages_field' ) as $blocked_page ) {
		$formatted_page_permalink = str_replace( get_site_url(), '', get_permalink( $blocked_page ) );
		$permalink_pages_array[]  = $formatted_page_permalink;
	}
} else {
	$permalink_pages_array[] = 'None set';
}
if ( ! empty( get_option( 'pvb_proxycheckio_blocked_select_posts_field' ) ) ) {
	foreach ( get_option( 'pvb_proxycheckio_blocked_select_posts_field' ) as $blocked_post ) {
		$formatted_post_permalink = str_replace( get_site_url(), '', get_permalink( $blocked_post ) );
		$permalink_posts_array[]  = $formatted_post_permalink;
	}
} else {
	$permalink_posts_array[] = 'None set';
}

// Build array output containing versions and settings.
if ( ! empty( get_option( 'pvb_proxycheckio_master_activation' ) ) ) {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Master Activation:</strong></div>
					<div class="col right">on</div>
				</div>';
} else {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Master Activation:</strong></div>
					<div class="col right">off</div>
				</div>';
}

if ( ! empty( get_option( 'pvb_option_ip_header_type' ) ) ) {
	$header_type     = get_option( 'pvb_option_ip_header_type' );
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Remote Visitor IP Header:</strong></div>
					<div class="col right">' . $header_type[0] . '</div>
				</div>';
}

if ( ! empty( get_option( 'pvb_proxycheckio_VPN_select_box' ) ) ) {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Also Detect VPN\'s:</strong></div>
					<div class="col right">on</div>
				</div>';
} else {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Also Detect VPN\'s:</strong></div>
					<div class="col right">off</div>
				</div>';
}

if ( ! empty( get_option( 'pvb_proxycheckio_TAG_select_box' ) ) ) {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Custom Tag:</strong></div>
					<div class="col right">on</div>
				</div>';
} else {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Custom Tag:</strong></div>
					<div class="col right">off</div>
				</div>';
}

if ( ! empty( get_option( 'pvb_proxycheckio_Custom_TAG_field' ) ) ) {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Custom Tag Text Field:</strong></div>
					<div class="col right">' . get_option( 'pvb_proxycheckio_Custom_TAG_field' ) . '</div>
				</div>';
} else {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Custom Tag Text Field:</strong></div>
					<div class="col right">Undefined</div>
				</div>';
}

if ( ! empty( get_option( 'pvb_proxycheckio_denied_access_field' ) ) ) {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Access Denied Message:</strong></div>
					<div class="col right"> ' . get_option( 'pvb_proxycheckio_denied_access_field' ) . '</div>
				</div>';
} else {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Access Denied Message:</strong></div>
					<div class="col right">Undefined</div>
				</div>';
}

if ( ! empty( get_option( 'pvb_proxycheckio_Days_Selector' ) ) ) {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Last Detected Within:</strong></div>
					<div class="col right"> ' . get_option( 'pvb_proxycheckio_Days_Selector' ) . ' days</div>
				</div>';
} else {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Last Detected Within:</strong></div>
					<div class="col right">Undefined</div>
				</div>';
}

if ( ! empty( get_option( 'pvb_proxycheckio_all_pages_activation' ) ) ) {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Block on All Pages:</strong></div>
					<div class="col right">on</div>
				</div>';
} else {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Block on All Pages:</strong></div>
					<div class="col right">off</div>
				</div>';
}

$custom_block_page_id = get_option( 'pvb_proxycheckio_custom_blocked_page' );
if ( ! empty( $custom_block_page_id[0] ) ) {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Custom Block Page ID:</strong></div>
					<div class="col right">' . $custom_block_page_id[0] . '</div>
				</div>';
} else {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Custom Block Page ID:</strong></div>
					<div class="col right">Not set</div>
				</div>';
}

if ( ! empty( get_option( 'pvb_proxycheckio_blocked_countries_field' ) ) ) {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Countries Field:</strong></div>
					<div class="col right">' . implode( ', ', get_option( 'pvb_proxycheckio_blocked_countries_field' ) ) . '</div>
				</div>';
} else {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Countries Field:</strong></div>
					<div class="col right">Undefined</div>
				</div>';
}

if ( ! empty( get_option( 'pvb_proxycheckio_whitelist_countries_select_box' ) ) ) {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Countries Field as Whitelist:</strong></div>
					<div class="col right">yes</div>
				</div>';
} else {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Countries Field as Whitelist:</strong></div>
					<div class="col right">no</div>
				</div>';
}

if ( ! empty( get_option( 'pvb_proxycheckio_good_ip_cache_time' ) ) ) {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Known Good IP\'s Cached For:</strong></div>
					<div class="col right">' . get_option( 'pvb_proxycheckio_good_ip_cache_time' ) . ' minutes</div>
				</div>';
} else {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Known Good IP\'s Cached For:</strong></div>
					<div class="col right">Undefined</div>
				</div>';
}

if ( ! empty( get_option( 'pvb_proxycheckio_opt_redirect_url' ) ) ) {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Redirect Bad Visitors URL:</strong></div>
					<div class="col right">' . get_option( 'pvb_proxycheckio_opt_redirect_url' ) . '</div>
				</div>';
} else {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Redirect Bad Visitors URL:</strong></div>
					<div class="col right">Undefined</div>
					</div>';
}

if ( ! empty( get_option( 'pvb_proxycheckio_redirect_bad_visitor' ) ) ) {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Redirect Bad Visitors to URL:</strong></div>
					<div class="col right">on</div>
				</div>';
} else {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Redirect Bad Visitors to URL:</strong></div>
					<div class="col right">off</div>
				</div>';
}

if ( ! empty( get_option( 'pvb_proxycheckio_Admin_Alert_Denied_Email' ) ) ) {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Admin Alert Emails:</strong></div>
					<div class="col right">on</div>
				</div>';
} else {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Admin Alert Emails:</strong></div>
					<div class="col right">off</div>
				</div>';
}

if ( ! empty( get_option( 'pvb_protect_login_authentication' ) ) ) {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Protect Login Authentication:</strong></div>
					<div class="col right">on</div>
				</div>';
} else {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Protect Login Authentication:</strong></div>
					<div class="col right">off</div>
				</div>';
}

if ( ! empty( get_option( 'pvb_allow_staff_bypass' ) ) ) {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Allow Staff Bypass:</strong></div>
					<div class="col right">on</div>
				</div>';
} else {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Allow Staff Bypass:</strong></div>
					<div class="col right">off</div>
				</div>';
}

if ( ! empty( get_option( 'pvb_cache_buster' ) ) ) {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Cache Busting:</strong></div>
					<div class="col right">on</div>
				</div>';
} else {
	$array_options[] = '<div class="row">
					<div class="col left"><strong>Cache Busting:</strong></div>
					<div class="col right">off</div>
				</div>';
}

// Generate Output.
if ( 'on' === get_option( 'pvb_enable_debugging' ) ) {
	// Build page HTML.
	$html  = '<div class="wrap" id="' . $this->parent->_token . '_debugging" dir="ltr">' . "\n";
	$html .= '	<h2 class="pvb-wp-notice-fix"></h2>' . "\n";
	$html .= '	<div class="pvbareawrap">' . "\n";
	$html .= '		<h1>' . __( 'Proxy & VPN Blocker Debugging Information', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
	$html .= '		<p>' . __( 'This section contains information that may be useful for the Proxy & VPN Blocker Developer to help diagnose problems that you may be experiencing with the Plugin.', 'proxy-vpn-blocker' ) . '</p>' . "\n";
	$html .= '	</div>' . "\n";
	$html .= '  <div class="pvbareawrap">' . "\n";
	$html .= '		<h2>Checking if we can reach the proxycheck.io API...</h2>' . "\n";
	$html .= '		<p>Getting response from proxycheck.io API using your IP Address: ' . $visitor_ip_address . ' from the ' . $header_type[0] . ' Header:</p>' . "\n";
	if ( 'on' === get_option( 'pvb_proxycheckio_dummy_data' ) && empty( get_option( 'pvb_proxycheckio_API_Key_field' ) ) ) {
		$html .= '		<p>Note: Using a placeholder IP Address. It is not possible to check your real IP Address within WordPress Live Demo Environment.</p>' . "\n";
	}
	$html .= '		<pre>' . wp_json_encode( $proxycheck_answer, JSON_PRETTY_PRINT ) . '</pre>' . "\n";
	if ( 'ok' === $proxycheck_answer->status ) {
		$html .= '		<p>It seems everything is working okay here!</p>' . "\n";
	} elseif ( 'warning' === $proxycheck_answer->status ) {
		$html .= '		<p>The query was answered by the API, however, please check the message in the output above for more information.</p>' . "\n";
	} elseif ( 'error' === $proxycheck_answer->status ) {
		$html .= '		<p>The query was not answered by the API, however, please check the message in the output above for more information.</p>' . "\n";
	}
	$html .= '  </div>' . "\n";
	$html .= '		<div id="log_outer">' . "\n";
	$html .= '		<div class="stats-fancy">' . "\n";
	$html .= '			<section>' . "\n";
	$html .= '				<header>' . "\n";
	$html .= '					<div class="col left">System Versions</div>' . "\n";
	$html .= '					<div class="col left"></div>' . "\n";
	$html .= '				</header>' . "\n";
	foreach ( $array_versions as $item ) {
		$html .= $item;
	}
	$html .= '			</section>' . "\n";
	$html .= '		</div>';
	$html .= '		<div class="fancy-bottom"></div>' . "\n";
	$html .= '	</div>';
	$html .= '		<div id="log_outer">' . "\n";
	$html .= '		<div class="stats-fancy">' . "\n";
	$html .= '			<section>' . "\n";
	$html .= '				<header>' . "\n";
	$html .= '					<div class="col left">API Key Info</div>' . "\n";
	$html .= '					<div class="col left"></div>' . "\n";
	$html .= '				</header>' . "\n";
	foreach ( $array_apikey as $item ) {
		$html .= $item;
	}
	$html .= '			</section>' . "\n";
	$html .= '		</div>';
	$html .= '		<div class="fancy-bottom"></div>' . "\n";
	$html .= '	</div>';
	$html .= '		<div id="log_outer">' . "\n";
	$html .= '		<div class="stats-fancy">' . "\n";
	$html .= '			<section>' . "\n";
	$html .= '				<header>' . "\n";
	$html .= '					<div class="col left">Option</div>' . "\n";
	$html .= '					<div class="col left">Current Setting</div>' . "\n";
	$html .= '				</header>' . "\n";
	foreach ( $array_options as $item ) {
		$html .= $item;
	}
	$html .= '			</section>' . "\n";
	$html .= '		</div>';
	$html .= '		<div class="fancy-bottom"></div>' . "\n";
	$html .= '	</div>';
	$html .= '		<div id="log_outer">' . "\n";
	$html .= '		<div class="stats-fancy">' . "\n";
	$html .= '			<section>' . "\n";
	$html .= '				<header>' . "\n";
	$html .= '					<div class="col left">Plugin</div>' . "\n";
	$html .= '					<div class="col left">Version</div>' . "\n";
	$html .= '					<div class="col left">Is Active?</div>' . "\n";
	$html .= '				</header>' . "\n";
	foreach ( $pluginsl as $item ) {
		$html .= '			<div class="row">';
		$html .= '				<div class="col left"><strong>';
		$html .= $item['name'];
		$html .= '				</strong></div>';
		$html .= '				<div class="col left">';
		$html .= $item['version'];
		$html .= '				</div>';
		$html .= '				<div class="col left">';
		$html .= $item['active'];
		$html .= '				</div>';
		$html .= '			</div>';
	}
	$html .= '			</section>' . "\n";
	$html .= '		</div>';
	$html .= '		<div class="fancy-bottom"></div>' . "\n";
	$html .= '	</div>';
	$html .= '</div>';
	echo wp_kses( $html, $allowed_html );
}
