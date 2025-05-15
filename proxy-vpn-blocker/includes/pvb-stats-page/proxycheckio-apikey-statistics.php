<?php
/**
 * Generate API Key Statistics Page.
 *
 * @package Proxy & VPN Blocker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$allowed_html = array(
	'div'    => array(
		'class'        => array(),
		'id'           => array(),
		'style'        => array(),
		'dir'          => array(),
		'data-tooltip' => array(),
		'title'        => array(),
	),
	'a'      => array(
		'href'  => array(),
		'title' => array(),
	),
	'i'      => array(
		'class' => array(),
	),
	'script' => array(
		'type' => array(),
	),
	'form'   => array(
		'class'  => array(),
		'id'     => array(),
		'action' => array(),
		'method' => array(),
		'target' => array(),
	),
	'input'  => array(
		'class' => array(),
		'id'    => array(),
		'name'  => array(),
		'type'  => array(),
		'title' => array(),
		'value' => array(),
	),
	'button' => array(
		'class'   => array(),
		'id'      => array(),
		'type'    => array(),
		'onclick' => array(),
		'name'    => array(),
		'style'   => array(),
	),
	'strong' => array(),
	'h1'     => array(),
	'h2'     => array(
		'class' => array(),
	),
	'h3'     => array(),
	'p'      => array(),
	'small'  => array(),
	'svg'    => array(
		'viewBox' => array(),
		'class'   => array(),
	),
	'path'   => array(
		'class' => array(),
		'd'     => array(),
	),
);

if ( ! empty( get_option( 'pvb_proxycheckio_API_Key_field' ) ) ) {
	$api_key_usage = pvb_get_proxycheck_api_key_stats();
	if ( isset( $api_key_usage->status ) && 'denied' === $api_key_usage->status ) {
		$html  = '<div class="wrap" id="' . $this->parent->_token . '_statistics" dir="ltr">' . "\n";
		$html .= '<h2></h2>';
		$html .= '<h1>' . __( 'Proxy &amp; VPN Blocker proxycheck.io Statistics', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
		$html .= '<div class="pvberror">' . "\n";
		$html .= '<div class="pvberrortitle">' . __( 'Oops!', 'proxy-vpn-blocker' ) . '</div>' . "\n";
		$html .= '<div class="pvberrorinside">' . "\n";
		$html .= '<h2>' . __( 'You must enable Dashboard API Access within your <a href="https://proxycheck.io" target="_blank">proxycheck.io</a> Dashboard to access this part of Proxy & VPN Blocker', 'proxy-vpn-blocker' ) . '</h2>' . "\n";
		$html .= '</div>' . "\n";
		$html .= '</div>' . "\n";
		$html .= '</div>';
		echo wp_kses( $html, $allowed_html );
	} elseif ( isset( $api_key_usage->error ) && 'No usage Found.' === $api_key_usage->error ) {
		$html  = '<div class="wrap" id="' . $this->parent->_token . '_statistics" dir="ltr">' . "\n";
		$html .= '<h2></h2>';
		$html .= '<h1>' . __( 'Proxy &amp; VPN Blocker proxycheck.io Statistics', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
		$html .= '<div class="pvberror">' . "\n";
		$html .= '<div class="pvberrortitle">' . __( 'Oops!', 'proxy-vpn-blocker' ) . '</div>' . "\n";
		$html .= '<div class="pvberrorinside">' . "\n";
		$html .= '<h2> An Error with the message "' . $api_key_usage->error . '" was returned by the proxycheck.io API, is your API Key correct?</h2>' . "\n";
		$html .= '</div>' . "\n";
		$html .= '</div>' . "\n";
		$html .= '</div>';
		echo wp_kses( $html, $allowed_html );
	} else {
		// Format and Display usage stats.
		$queries_today          = $api_key_usage->{'Queries Today'};
		$daily_limit            = $api_key_usage->{'Daily Limit'};
		$queries_total          = $api_key_usage->{'Queries Total'};
		$plan_tier              = $api_key_usage->{'Plan Tier'};
		$burst_tokens           = $api_key_usage->{'Burst Tokens Available'};
		$burst_tokens_allowance = $api_key_usage->{'Burst Token Allowance'};
		$burst_tokens_active    = $api_key_usage->{'Burst Token Active'};
		$queries_lifetime       = $api_key_usage->{'Queries Total'};
		$bursts_used            = $burst_tokens_allowance - $burst_tokens;
		$usage_percent          = ( $queries_today * 100 ) / $daily_limit;

		// Set CSS for the color of the day's query count.
		$query_color_id = 'query-normal';
		if ( $usage_percent >= 75 && $usage_percent < 90 ) {
			$query_color_id = 'query-warning';
		} elseif ( $usage_percent >= 90 ) {
			$query_color_id = 'query-critical';
		}

		$html  = '<div class="wrap" id="' . $this->parent->_token . '_statistics">' . "\n";
		$html .= '<h2 class="pvb-wp-notice-fix"></h2>' . "\n";
		$html .= '<div class="pvbareawrap">' . "\n";
		$html .= '	<div id="pvb-refresh-timer">' . "\n";
		$html .= '	<svg viewBox="0 0 36 36" class="circle">' . "\n";
		$html .= '	<path class="bg" d="M18 2.0845' . "\n";
		$html .= '		a 15.9155 15.9155 0 0 1 0 31.831' . "\n";
		$html .= '		a 15.9155 15.9155 0 0 1 0 -31.831" />' . "\n";
		$html .= '	<path class="progress" d="M18 2.0845' . "\n";
		$html .= '		a 15.9155 15.9155 0 0 1 0 31.831' . "\n";
		$html .= '		a 15.9155 15.9155 0 0 1 0 -31.831" />' . "\n";
		$html .= '	</svg>' . "\n";
		$html .= '	<div class="pvb-timer-count" title="Auto-refresh countdown"></div>' . "\n";
		$html .= '	</div>' . "\n";
		$html .= '	<h1>' . __( 'Your proxycheck.io API Key Statistics', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
		$html .= '	<div class="api-info-apikey">' . __( 'API Key: ', 'proxy-vpn-blocker' ) . get_option( 'pvb_proxycheckio_API_Key_field' ) . '</div>' . "\n";
		$html .= '<div class="api-info-tier">' . __( 'Plan: ', 'proxy-vpn-blocker' ) . $plan_tier . ' | ' . number_format_short( $daily_limit ) . ' Daily Queries</div>' . "\n";
		$html .= '</div>' . "\n";
		$html .= '<div class="api-info">' . "\n";
		// ─── COLUMN 1: QUERIES ───────────────────────────────────────────────────────
		$html .= '  <div class="api-info-col api-info-col1">' . "\n";
		$html .= '   	<div class="sub-card api-card">' . "\n";
		$html .= '      	<div class="tooltip-icon" data-tooltip="Total API queries used today, relative to your plan."><i class="fa-solid fa-circle-info"></i></div>' . "\n";
		$html .= '    		<h1>API Queries Today</h1>' . "\n";
		$html .= '    		<div class="api-info-queries-used" id="' . $query_color_id . '"><strong>' . number_format_short( $queries_today ) . '</strong></div>' . "\n";
		$html .= '   		<div class="api-info-title-small">That\'s ' . round( $usage_percent, 2 ) . '% of your plan\'s daily limit.</div>' . "\n";
		$html .= '     </div>' . "\n";
		$html .= '  </div>' . "\n";
		// ─── COLUMN 2: BURST TOKENS ─────────────────────────────────────────────────
		$html .= '  <div class="api-info-col api-info-col2">' . "\n";
		$html .= '     <div class="sub-card api-card">' . "\n";
		$html .= '      	<div class="tooltip-icon" data-tooltip="Burst tokens are relative to your proxycheck.io plan tier. For your ' . number_format_short( $daily_limit ) . ' Daily Queries ' . $plan_tier . ' plan, these Burst Tokens allow you to do 5x your daily query limit ' . $burst_tokens_allowance . ' times per month. This resets at the end of your billing period."><i class="fa-solid fa-circle-info"></i></div>' . "\n";
		$html .= '   	  <h1>Burst Tokens</h1>' . "\n";
		$html .= '    	  <div class="api-info-bursts"><strong>' . $bursts_used . '</strong> / ' . $burst_tokens_allowance . ' <small>Used</small></div>' . "\n";
		$html .= '      </div>' . "\n";
		$html .= '		<div class="sub-card api-card">' . "\n";
		$html .= '      	<div class="tooltip-icon" data-tooltip="The amount of queries you have made to proxycheck.io since signing up."><i class="fa-solid fa-circle-info"></i></div>' . "\n";
		$html .= '    	  <h1">Lifetime Queries</h1>' . "\n";
		$html .= '    	  <div class="api-info-bursts">' . number_format_short( $queries_lifetime ) . '</div>' . "\n";
		$html .= '   	 </div>' . "\n";
		$html .= '  </div>' . "\n";
		// ─── COLUMN 3: PROXY & VPN ANALYSIS ────────────────────────────────────────
		$html .= '  <div class="api-info-col api-info-col3">' . "\n";
		$html .= '    <div class="sub-card">' . "\n";
		$html .= '		<h1>Proxy & VPN Blocker Analysis</h1>' . "\n";

		if ( $usage_percent < 75 ) {
			$html .= '<p>Proxy & VPN Blocker has determined that based on your current Query and Burst Token usage, no actions are required.</p>' . "\n";
		} elseif ( $usage_percent >= 75 && $usage_percent < 90 ) {
			$html .= '<p>Over <strong>75%</strong> of your Queries have been used up, today.</p>' . "\n";
		} elseif ( $usage_percent >= 90 && $usage_percent < 100 ) {
			$html .= '<p>Over <strong>90%</strong> of your Queries have been used up, today. You have <strong>' . $burst_tokens . '</strong> Burst Token(s) Available.</p>' . "\n";
			if ( 0 === $burst_tokens ) {
				$html .= '<p>It is important that you keep an eye on your query usage. You have no Burst Tokens left this month!</p>' . "\n";
			} else {
				$html .= '<p>It is recommended that you keep an eye on your query usage. A Burst Token may be used soon.</p>' . "\n";
			}
			if ( 'Paid' === $plan_tier ) {
				$html .= '<p>If you are consistently nearing your daily limit based on the graph below, then you may need a higher tier plan</p>' . "\n";
			} else {
				$html .= '<p>If you are consistently nearing your daily limit based on the graph below, then you may need a paid plan.</p>' . "\n";
			}
			$html .= '<p>Discounted plans are available from the <a href="https://proxyvpnblocker.com/plan-donate/" target="_blank">Proxy & VPN Blocker Site</a>.</p>' . "\n";
		} elseif ( $usage_percent > 100 && ! empty( $burst_tokens ) ) {
			$html .= '<p>Over <strong>100%</strong> of your Queries have been used up, today. A Burst Token has been consumed, increasing your limit by 5x for today only!</p>' . "\n";
			if ( 'Paid' === $plan_tier ) {
				$html .= '<p>If you are consistently nearing, or hitting your daily limit based on the graph below, then you may need a higher tier plan</p>' . "\n";
			} else {
				$html .= '<p>If you are consistently nearing, or hitting your daily limit based on the graph below, then you may need a paid plan.</p>' . "\n";
			}
			$html .= '<p>Discounted plans are available from the <a href="https://proxyvpnblocker/plan-donate/" target="_blank">Proxy & VPN Blocker Site</a>.</p>' . "\n";
		} elseif ( 100 === $usage_percent && empty( $burst_tokens ) ) {
			$html .= '<p><strong>100%</strong> of your Queries have been used up, today. You have 0 Burst Tokens left this month and queries are no longer being answered until the daily reset.</p>' . "\n";
			if ( 'Paid' === $plan_tier ) {
				$html .= '<p>If you are consistently hitting your daily limit based on the graph below, or using Burst Tokens, then you may need a higher tier plan</p>' . "\n";
			} else {
				$html .= '<p>If you are consistently hitting your daily limit based on the graph below, or using Burst Tokens, then you may need a paid plan.</p>' . "\n";
			}
			$html .= '<p>Discounted Plans are available from the <a href="https://proxyvpnblocker.com/plan-donate/" target="_blank">Proxy & VPN Blocker Site</a>.</p>' . "\n";
		}

		$html .= '	 </div>' . "\n";
		$html .= '	</div>' . "\n";
		$html .= '</div>' . "\n";

		$html .= '<div class="amchartareawrap">' . "\n";
		$html .= '	<h1>' . __( 'API Queries: Past Month', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
		$html .= '	<div id="amchartAPImonth">' . "\n";
		$html .= '		<div id="amchart-loading">Loading chart...</div>' . "\n";
		$html .= '	</div>' . "\n";
		$html .= '</div>' . "\n";
		echo wp_kses( $html, $allowed_html );
	}
} else {
	$html  = '<div class="wrap" id="' . $this->parent->_token . '_statistics" dir="ltr">' . "\n";
	$html .= '<h1>' . __( 'Proxy &amp; VPN Blocker proxycheck.io Statistics', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
	$html .= '<div class="pvberror">' . "\n";
	$html .= '<div class="pvberrortitle">' . __( 'Oops - Your proxycheck.io API Key is missing!', 'proxy-vpn-blocker' ) . '</div>' . "\n";
	$html .= '<div class="pvberrorinside">' . "\n";
	$html .= '<h2>' . __( 'Please set your <a href="https://proxycheck.io" target="_blank">proxycheck.io</a> API Key in PVB Settings to be able to use this page!', 'proxy-vpn-blocker' ) . '</h2>' . "\n";
	$html .= '<h3>' . __( 'This page will display statistics about your API Key queries and recent detections.', 'proxy-vpn-blocker' ) . '</h3>' . "\n";
	$html .= '</div>' . "\n";
	$html .= '</div>';
	echo wp_kses( $html, $allowed_html );
}
