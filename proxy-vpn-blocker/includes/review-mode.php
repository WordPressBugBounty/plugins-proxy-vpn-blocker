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
			if ( 'Free' === $pvb_api_key_details['tier'] && $interval->days >= 120 ) {
					echo '<div class=pvbrvwwrap">' . "\n";
					echo '	<div class="pvbrvwwrapwrapleft">' . "\n";
					echo '		<div class="pvbrvwwraplogoinside">' . "\n";
					echo '		</div>' . "\n";
					echo '	</div>' . "\n";
					echo '	<div class="pvbrvwwrapright">' . "\n";
					echo '		<button class="pvbdonatedismiss" id="pvbdonationclosebutton" title="close"><i class="fa-solid fa-circle-xmark"></i></button>' . "\n";
					echo '		<div class="pvbrvwraptext">' . "\n";
					echo '			<p>' . __( 'We are very happy to see that you are making use of the Proxy & VPN Blocker plugin on ' . get_bloginfo( 'name' ) . '!', 'proxy-vpn-blocker' ) . '</p>' . "\n";
					echo '			<p>' . __( 'If at all possible, we would be very grateful if you would take a moment to <a href="https://wordpress.org/plugins/proxy-vpn-blocker/#reviews" target="_blank">leave us a review</a> as this helps to encourage more people.', 'proxy-vpn-blocker' ) . '</p>' . "\n";
					echo '			<p>' . __( 'Thank you!', 'proxy-vpn-blocker' ) . '</p>' . "\n";
					echo '		</div>' . "\n";
					echo '	</div>' . "\n";
					echo '</div>' . "\n";
			}
			if ( 'Paid' === $pvb_api_key_details['tier'] && $interval->days >= 60 ) {
					echo '<div class="pvbrvwwrap">' . "\n";
					echo '	<div class="pvbrvwwrapleft">' . "\n";
					echo '		<div class="pvbrvwwraplogoinside">' . "\n";
					echo '		</div>' . "\n";
					echo '	</div>' . "\n";
					echo '	<div class="pvbrvwwrapright">' . "\n";
					echo '		<button class="pvbdonatedismiss" id="pvbdonationclosebutton" title="close"><i class="fa-solid fa-circle-xmark"></i></button>' . "\n";
					echo '		<div class="pvbrvwwraptext">' . "\n";
					echo '			<p>' . __( 'We are very happy to see that you are making use of the Proxy & VPN Blocker plugin on ' . get_bloginfo( 'name' ) . '!', 'proxy-vpn-blocker' ) . '</p>' . "\n";
					echo '			<p>' . __( 'If at all possible, we would be very grateful if you would take a moment to <a href="https://wordpress.org/plugins/proxy-vpn-blocker/#reviews" target="_blank">leave us a review on WordPress.org</a> as this helps to increase visibility of Proxy & VPN Blocker.', 'proxy-vpn-blocker' ) . '</p>' . "\n";
					echo '			<p>' . __( 'Thank you!', 'proxy-vpn-blocker' ) . '</p>' . "\n";
					echo '		</div>' . "\n";
					echo '	</div>' . "\n";
					echo '</div>' . "\n";
			}
		}
	}
}

