<?php
/**
 * Proxy & VPN Blocker Logs Page.
 *
 * @package Proxy & VPN Blocker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$allowed_html = array(
	'div'    => array(
		'class' => array(),
		'id'    => array(),
		'style' => array(),
		'dir'   => array(),
	),
	'a'      => array(
		'href'  => array(),
		'title' => array(),
		'target' => array(),
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
	'section'  => array(),
	'header'     => array(
		'class' => array(),
	),
);

$html  = '<div class="wrap" id="' . $this->parent->_token . '_logs" dir="ltr">' . "\n";
$html .= '<h2 class="pvb-wp-notice-fix"></h2>' . "\n";
$html .= '<div class="pvbareawrap">' . "\n";
$html .= '<h1>Proxy &amp; VPN Blocker Action Log</h1>' . "\n";
$html .= '<p>This log contains up to 30 days of Actions taken against IP Addresses with positive Proxy & VPN detections by Proxy & VPN Blocker.</p>' . "\n";
$html .= '<p>Timestamps are localised to your WordPress Installation Time Zone.</p>' . "\n";
$html .= '<div class="pvb-prem-info">' . "\n";
$html .= '<a href="https://proxyvpnblocker.com/premium/" target="_blank">Proxy & VPN Blocker Premium</a> displays the action taken against the IP in this log (Captcha Challenge, Block Page, Redirect etc) with more features coming soon.' . "\n";
$html .= '</div>' . "\n";
if ( '' === get_option( 'pvb_log_user_ip_select_box' ) ) {
	$html .= '<div class="field-note">' . "\n";
	$html .= 'Notice: "Log User IP\'s Locally" is disabled in PVB Settings > General > "Log User IP\'s Locally", no additional entries will be added to this log.' . "\n";
	$html .= '</div>' . "\n";
}
$html .= '</div>' . "\n";
$html .= '<div id="log_outer">' . "\n";
$html .= '		<div class="angry-grid-head">' . "\n";
$html .= '			<div class="item-0">Time</div>' . "\n";
$html .= '			<div class="item-1">IP Address</div>' . "\n";
$html .= '			<div class="item-2">Country</div>' . "\n";
$html .= '			<div class="item-3">Risk Score</div>' . "\n";
$html .= '		</div>' . "\n";
$html .= '		<div class="log_content"></div>' . "\n";
$html .= '		<!-- Logs will be injected here by JavaScript -->' . "\n";
$html .= '		<div class="fancy-bottom">' . "\n";
$html .= '			<div class="pagination">' . "\n";
$html .= '				<button id="prev-page" class="pvbdefault submit" type="submit"><i class="fa-fw fa-solid fa-chevron-left"></i> Older Entries</button>' . "\n";
$html .= '				<button id="next-page" class="pvbdefault submit" style="float: right;" type="submit">Newer Entries <i class="fa-fw fa-solid fa-chevron-right"></i></button>' . "\n";
$html .= '			</div>' . "\n";
$html .= '		</div>' . "\n";
$html .= '</div>' . "\n";
echo wp_kses( $html, $allowed_html );
