<?php
/**
 * Handler for Proxy & VPN Blocker Review Messaging
 *
 * @package Proxy & VPN Blocker
 */

$get_api_key = get_option( 'pvb_proxycheckio_API_Key_field' );
if ( ! empty( $get_api_key ) ) {
	$pvb_api_key_details = get_option( 'pvb_proxycheck_apikey_details' );
	if ( ! empty( $pvb_api_key_details ) && ! isset( $_COOKIE['pvb-hide-rvw-div'] ) ) {
		$current_date    = new DateTime();
		$activation_date = DateTime::createFromFormat( 'Y-m-d', $pvb_api_key_details['activation_date'] );

		if ( $current_date > $activation_date ) {
			$interval = $current_date->diff( $activation_date );
			if ( 'Free' === $pvb_api_key_details['tier'] && $interval->days >= 30 ) {
					echo '<div class=pvbrvwwrap">' . "\n";
					echo '	<div class="pvbrvwwrapwrapleft">' . "\n";
					echo '		<div class="pvbrvwwraplogoinside">' . "\n";
					echo '		</div>' . "\n";
					echo '	</div>' . "\n";
					echo '	<div class="pvbrvwwrapright">' . "\n";
					echo '		<button class="pvbdonatedismiss" id="pvbdonationclosebutton" title="close"><i class="fa-solid fa-circle-xmark"></i></button>' . "\n";
					echo '		<div class="pvbrvwraptext">' . "\n";
					echo '			<p>' . __( 'We are happy to see that you are making use of the Proxy & VPN Blocker plugin on ' . get_bloginfo( 'name' ) . '!', 'proxy-vpn-blocker' ) . '</p>' . "\n";
					echo '			<p>' . __( 'If our plugin has helped protect your site, would you mind sharing your experience with a quick review? It only takes a minute and helps other WordPress users discover the Plugin. <a href="https://wordpress.org/plugins/proxy-vpn-blocker/#reviews" target="_blank">leave a review</a>', 'proxy-vpn-blocker' ) . '</p>' . "\n";
					echo '			<p>' . __( 'Thank you!', 'proxy-vpn-blocker' ) . '</p>' . "\n";
					echo '		</div>' . "\n";
					echo '	</div>' . "\n";
					echo '</div>' . "\n";
			}
			if ( 'Paid' === $pvb_api_key_details['tier'] && $interval->days >= 14 ) {
					echo '<div class="pvbrvwwrap">' . "\n";
					echo '	<div class="pvbrvwwrapleft">' . "\n";
					echo '		<div class="pvbrvwwraplogoinside">' . "\n";
					echo '		</div>' . "\n";
					echo '	</div>' . "\n";
					echo '	<div class="pvbrvwwrapright">' . "\n";
					echo '		<button class="pvbdonatedismiss" id="pvbdonationclosebutton" title="close"><i class="fa-solid fa-circle-xmark"></i></button>' . "\n";
					echo '		<div class="pvbrvwwraptext">' . "\n";
					echo '			<p>' . __( 'We are happy to see that you are making use of the Proxy & VPN Blocker plugin on ' . get_bloginfo( 'name' ) . '!', 'proxy-vpn-blocker' ) . '</p>' . "\n";
					echo '			<p>' . __( 'If our plugin has helped protect your site, would you mind sharing your experience with a quick review? It only takes a minute and helps other WordPress users discover the Plugin. <a href="https://wordpress.org/plugins/proxy-vpn-blocker/#reviews" target="_blank">leave a review</a>', 'proxy-vpn-blocker' ) . '</p>' . "\n";
					echo '			<p>' . __( 'Thank you!', 'proxy-vpn-blocker' ) . '</p>' . "\n";
					echo '		</div>' . "\n";
					echo '	</div>' . "\n";
					echo '</div>' . "\n";
			}
		}
	}
}

