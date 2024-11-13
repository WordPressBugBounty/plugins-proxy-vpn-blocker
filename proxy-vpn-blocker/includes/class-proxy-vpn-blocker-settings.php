<?php
/**
 * Proxy & VPN Blocker Plugin Settings
 *
 * @package  Proxy & VPN Blocker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Proxy & VPN Blocker Settings Class.
 */
class Proxy_VPN_Blocker_Settings {
	/**
	 * The single instance of Proxy_VPN_Blocker_Settings.
	 *
	 * @var     object
	 * @access  private
	 * @since   1.0
	 */
	private static $instance = null; //phpcs:ignorev

	/**
	 * The main plugin object.
	 *
	 * @var     object
	 * @access  public
	 * @since   1.0
	 */
	public $parent = null;

	/**
	 * Prefix for Proxy & VPN Blocker Settings.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 *
	 * @var     array
	 * @access  public
	 * @since   1.0
	 */
	public $settings = array();
	/**
	 * Plugin Constructor
	 *
	 * @param name $parent from The main plugin object.
	 */
	public function __construct( $parent ) {
		$this->parent = $parent;
		$this->base   = 'pvb_';
		// Initialise settings.
		add_action( 'init', array( $this, 'init_settings' ), 11 );
		// Register Proxy & VPN Blocker Settings.
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		// Add settings page to menu.
		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
		// Add settings link to plugins page.
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ), array( $this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 *
	 * @return void
	 */
	public function init_settings() {
		$this->settings = $this->settings_fields();
	}
	/**
	 * Add settings page to admin menu
	 *
	 * @return void
	 */
	public function add_menu_item() {
		$this->parent->assets_url = esc_url( trailingslashit( plugins_url( 'proxy-vpn-blocker/assets/' ) ) );
		add_menu_page( 'Proxy & VPN Blocker', 'PVB Settings', 'manage_options', $this->parent->_token . '_settings', array( $this, 'settings_page' ), esc_url( $this->parent->assets_url ) . 'img/pvb.svg' );
		add_submenu_page( $this->parent->_token . '_settings', 'Blacklist Editor', 'Blacklist Editor', 'manage_options', $this->parent->_token . '_blacklist', array( $this, 'ipblacklist_page' ) );
		add_submenu_page( $this->parent->_token . '_settings', 'Whitelist Editor', 'Whitelist Editor', 'manage_options', $this->parent->_token . '_whitelist', array( $this, 'ipwhitelist_page' ) );
		add_submenu_page( $this->parent->_token . '_settings', 'Statistics', 'API Key Statistics', 'manage_options', $this->parent->_token . '_statistics', array( $this, 'statistics_page' ) );
		add_submenu_page( $this->parent->_token . '_settings', 'Action Log', 'Action Log', 'manage_options', $this->parent->_token . '_action_log', array( $this, 'action_log_page' ) );
		if ( 'on' === get_option( 'pvb_enable_debugging' ) ) {
			add_submenu_page( $this->parent->_token . '_settings', 'PVB Debugging', 'PVB Debugging', 'manage_options', $this->parent->_token . '_debugging', array( $this, 'debugging_page' ) );
		}
	}

	/**
	 * Add settings link to plugin list table
	 *
	 * @param  array $links Existing links.
	 * @return array        Modified links
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="admin.php?page=proxy_vpn_blocker_settings">' . __( 'Settings', 'proxy-vpn-blocker' ) . '</a>';
		array_push( $links, $settings_link );
		return $links;
	}

	/**
	 * Build settings fields
	 *
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields() {
		// Allowing the use of custom entries in the Visitor IP header selection.
		$headers_array_default = array(
			'REMOTE_ADDR'           => 'Default Header: $_SERVER[\'REMOTE_ADDR\']',
			'HTTP_CF_CONNECTING_IP' => 'CloudFlare Header: $_SERVER[\'HTTP_CF_CONNECTING_IP\']',
			'HTTP_X_FORWARDED_FOR'  => 'Other Header: $_SERVER[\'HTTP_X_FORWARDED_FOR\'] (Not Recommended)',
		);
		$current_selection     = get_option( 'pvb_option_ip_header_type' );
		$default_values        = array_keys( $headers_array_default );
		if ( ! empty( $current_selection ) && ! in_array( $current_selection[0], $default_values, true ) ) {
			$new_arr       = array(
				$current_selection[0] => 'Custom Entry: ' . $current_selection[0],
			);
			$headers_array = array_merge( $headers_array_default, $new_arr );
		} else {
			$headers_array = $headers_array_default;
		}

		// Countries List for Block on Countries/Continents.
		$countries_list = array(
			'Africa'                                       => 'Africa',
			'Antarctica'                                   => 'Antarctica',
			'Asia'                                         => 'Asia',
			'Europe'                                       => 'Europe',
			'North America'                                => 'North America',
			'Oceania'                                      => 'Oceania',
			'South America'                                => 'South America',
			'Afghanistan'                                  => 'Afghanistan',
			'Aland Islands'                                => 'Aland Islands',
			'Albania'                                      => 'Albania',
			'Algeria'                                      => 'Algeria',
			'American Samoa'                               => 'American Samoa',
			'Andorra'                                      => 'Andorra',
			'Angola'                                       => 'Angola',
			'Anguilla'                                     => 'Anguilla',
			'Antigua and Barbuda'                          => 'Antigua and Barbuda',
			'Argentina'                                    => 'Argentina',
			'Armenia'                                      => 'Armenia',
			'Aruba'                                        => 'Aruba',
			'Australia'                                    => 'Australia',
			'Austria'                                      => 'Austria',
			'Azerbaijan'                                   => 'Azerbaijan',
			'Bahamas'                                      => 'Bahamas',
			'Bahrain'                                      => 'Bahrain',
			'Bangladesh'                                   => 'Bangladesh',
			'Barbados'                                     => 'Barbados',
			'Belarus'                                      => 'Belarus',
			'Belgium'                                      => 'Belgium',
			'Belize'                                       => 'Belize',
			'Benin'                                        => 'Benin',
			'Bermuda'                                      => 'Bermuda',
			'Bhutan'                                       => 'Bhutan',
			'Bolivia'                                      => 'Bolivia',
			'Bonaire, Saint Eustatius and Saba '           => 'Bonaire, Saint Eustatius and Saba ',
			'Bosnia and Herzegovina'                       => 'Bosnia and Herzegovina',
			'Botswana'                                     => 'Botswana',
			'Bouvet Island'                                => 'Bouvet Island',
			'Brazil'                                       => 'Brazil',
			'British Indian Ocean Territory'               => 'British Indian Ocean Territory',
			'British Virgin Islands'                       => 'British Virgin Islands',
			'Brunei'                                       => 'Brunei',
			'Bulgaria'                                     => 'Bulgaria',
			'Burkina Faso'                                 => 'Burkina Faso',
			'Burundi'                                      => 'Burundi',
			'Cabo Verde'                                   => 'Cabo Verde',
			'Cambodia'                                     => 'Cambodia',
			'Cameroon'                                     => 'Cameroon',
			'Canada'                                       => 'Canada',
			'Cayman Islands'                               => 'Cayman Islands',
			'Central African Republic'                     => 'Central African Republic',
			'Chad'                                         => 'Chad',
			'Chile'                                        => 'Chile',
			'China'                                        => 'China',
			'Christmas Island'                             => 'Christmas Island',
			'Cocos Islands'                                => 'Cocos Islands',
			'Colombia'                                     => 'Colombia',
			'Comoros'                                      => 'Comoros',
			'Cook Islands'                                 => 'Cook Islands',
			'Costa Rica'                                   => 'Costa Rica',
			'Croatia'                                      => 'Croatia',
			'Cuba'                                         => 'Cuba',
			'Curacao'                                      => 'Curacao',
			'Cyprus'                                       => 'Cyprus',
			'Czechia'                                      => 'Czechia',
			'Democratic Republic of the Congo'             => 'Democratic Republic of the Congo',
			'Denmark'                                      => 'Denmark',
			'Djibouti'                                     => 'Djibouti',
			'Dominica'                                     => 'Dominica',
			'Dominican Republic'                           => 'Dominican Republic',
			'Ecuador'                                      => 'Ecuador',
			'Egypt'                                        => 'Egypt',
			'El Salvador'                                  => 'El Salvador',
			'Equatorial Guinea'                            => 'Equatorial Guinea',
			'Eritrea'                                      => 'Eritrea',
			'Estonia'                                      => 'Estonia',
			'Eswatini'                                     => 'Eswatini',
			'Ethiopia'                                     => 'Ethiopia',
			'Falkland Islands'                             => 'Falkland Islands',
			'Faroe Islands'                                => 'Faroe Islands',
			'Fiji'                                         => 'Fiji',
			'Finland'                                      => 'Finland',
			'France'                                       => 'France',
			'French Guiana'                                => 'French Guiana',
			'French Polynesia'                             => 'French Polynesia',
			'French Southern Territories'                  => 'French Southern Territories',
			'Gabon'                                        => 'Gabon',
			'Gambia'                                       => 'Gambia',
			'Georgia'                                      => 'Georgia',
			'Germany'                                      => 'Germany',
			'Ghana'                                        => 'Ghana',
			'Gibraltar'                                    => 'Gibraltar',
			'Greece'                                       => 'Greece',
			'Greenland'                                    => 'Greenland',
			'Grenada'                                      => 'Grenada',
			'Guadeloupe'                                   => 'Guadeloupe',
			'Guam'                                         => 'Guam',
			'Guatemala'                                    => 'Guatemala',
			'Guernsey'                                     => 'Guernsey',
			'Guinea'                                       => 'Guinea',
			'Guinea-Bissau'                                => 'Guinea-Bissau',
			'Guyana'                                       => 'Guyana',
			'Haiti'                                        => 'Haiti',
			'Heard Island and McDonald Islands'            => 'Heard Island and McDonald Islands',
			'Honduras'                                     => 'Honduras',
			'Hong Kong'                                    => 'Hong Kong',
			'Hungary'                                      => 'Hungary',
			'Iceland'                                      => 'Iceland',
			'India'                                        => 'India',
			'Indonesia'                                    => 'Indonesia',
			'Iran'                                         => 'Iran',
			'Iraq'                                         => 'Iraq',
			'Ireland'                                      => 'Ireland',
			'Isle of Man'                                  => 'Isle of Man',
			'Israel'                                       => 'Israel',
			'Italy'                                        => 'Italy',
			'Ivory Coast'                                  => 'Ivory Coast',
			'Jamaica'                                      => 'Jamaica',
			'Japan'                                        => 'Japan',
			'Jersey'                                       => 'Jersey',
			'Jordan'                                       => 'Jordan',
			'Kazakhstan'                                   => 'Kazakhstan',
			'Kenya'                                        => 'Kenya',
			'Kiribati'                                     => 'Kiribati',
			'Kosovo'                                       => 'Kosovo',
			'Kuwait'                                       => 'Kuwait',
			'Kyrgyzstan'                                   => 'Kyrgyzstan',
			'Laos'                                         => 'Laos',
			'Latvia'                                       => 'Latvia',
			'Lebanon'                                      => 'Lebanon',
			'Lesotho'                                      => 'Lesotho',
			'Liberia'                                      => 'Liberia',
			'Libya'                                        => 'Libya',
			'Liechtenstein'                                => 'Liechtenstein',
			'Lithuania'                                    => 'Lithuania',
			'Luxembourg'                                   => 'Luxembourg',
			'Macao'                                        => 'Macao',
			'Madagascar'                                   => 'Madagascar',
			'Malawi'                                       => 'Malawi',
			'Malaysia'                                     => 'Malaysia',
			'Maldives'                                     => 'Maldives',
			'Mali'                                         => 'Mali',
			'Malta'                                        => 'Malta',
			'Marshall Islands'                             => 'Marshall Islands',
			'Martinique'                                   => 'Martinique',
			'Mauritania'                                   => 'Mauritania',
			'Mauritius'                                    => 'Mauritius',
			'Mayotte'                                      => 'Mayotte',
			'Mexico'                                       => 'Mexico',
			'Micronesia'                                   => 'Micronesia',
			'Moldova'                                      => 'Moldova',
			'Monaco'                                       => 'Monaco',
			'Mongolia'                                     => 'Mongolia',
			'Montenegro'                                   => 'Montenegro',
			'Montserrat'                                   => 'Montserrat',
			'Morocco'                                      => 'Morocco',
			'Mozambique'                                   => 'Mozambique',
			'Myanmar'                                      => 'Myanmar',
			'Namibia'                                      => 'Namibia',
			'Nauru'                                        => 'Nauru',
			'Nepal'                                        => 'Nepal',
			'Netherlands'                                  => 'Netherlands',
			'Netherlands Antilles'                         => 'Netherlands Antilles',
			'New Caledonia'                                => 'New Caledonia',
			'New Zealand'                                  => 'New Zealand',
			'Nicaragua'                                    => 'Nicaragua',
			'Niger'                                        => 'Niger',
			'Nigeria'                                      => 'Nigeria',
			'Niue'                                         => 'Niue',
			'Norfolk Island'                               => 'Norfolk Island',
			'North Korea'                                  => 'North Korea',
			'North Macedonia'                              => 'North Macedonia',
			'Northern Mariana Islands'                     => 'Northern Mariana Islands',
			'Norway'                                       => 'Norway',
			'Oman'                                         => 'Oman',
			'Pakistan'                                     => 'Pakistan',
			'Palau'                                        => 'Palau',
			'Palestinian Territory'                        => 'Palestinian Territory',
			'Panama'                                       => 'Panama',
			'Papua New Guinea'                             => 'Papua New Guinea',
			'Paraguay'                                     => 'Paraguay',
			'Peru'                                         => 'Peru',
			'Philippines'                                  => 'Philippines',
			'Pitcairn'                                     => 'Pitcairn',
			'Poland'                                       => 'Poland',
			'Portugal'                                     => 'Portugal',
			'Puerto Rico'                                  => 'Puerto Rico',
			'Qatar'                                        => 'Qatar',
			'Republic of the Congo'                        => 'Republic of the Congo',
			'Reunion'                                      => 'Reunion',
			'Romania'                                      => 'Romania',
			'Russia'                                       => 'Russia',
			'Rwanda'                                       => 'Rwanda',
			'Saint Barthelemy'                             => 'Saint Barthelemy',
			'Saint Helena'                                 => 'Saint Helena',
			'Saint Kitts and Nevis'                        => 'Saint Kitts and Nevis',
			'Saint Lucia'                                  => 'Saint Lucia',
			'Saint Martin'                                 => 'Saint Martin',
			'Saint Pierre and Miquelon'                    => 'Saint Pierre and Miquelon',
			'Saint Vincent and the Grenadines'             => 'Saint Vincent and the Grenadines',
			'Samoa'                                        => 'Samoa',
			'San Marino'                                   => 'San Marino',
			'Sao Tome and Principe'                        => 'Sao Tome and Principe',
			'Saudi Arabia'                                 => 'Saudi Arabia',
			'Senegal'                                      => 'Senegal',
			'Serbia'                                       => 'Serbia',
			'Serbia and Montenegro'                        => 'Serbia and Montenegro',
			'Seychelles'                                   => 'Seychelles',
			'Sierra Leone'                                 => 'Sierra Leone',
			'Singapore'                                    => 'Singapore',
			'Sint Maarten'                                 => 'Sint Maarten',
			'Slovakia'                                     => 'Slovakia',
			'Slovenia'                                     => 'Slovenia',
			'Solomon Islands'                              => 'Solomon Islands',
			'Somalia'                                      => 'Somalia',
			'South Africa'                                 => 'South Africa',
			'South Georgia and the South Sandwich Islands' => 'South Georgia and the South Sandwich Islands',
			'South Korea'                                  => 'South Korea',
			'South Sudan'                                  => 'South Sudan',
			'Spain'                                        => 'Spain',
			'Sri Lanka'                                    => 'Sri Lanka',
			'Sudan'                                        => 'Sudan',
			'Suriname'                                     => 'Suriname',
			'Svalbard and Jan Mayen'                       => 'Svalbard and Jan Mayen',
			'Sweden'                                       => 'Sweden',
			'Switzerland'                                  => 'Switzerland',
			'Syria'                                        => 'Syria',
			'Taiwan'                                       => 'Taiwan',
			'Tajikistan'                                   => 'Tajikistan',
			'Tanzania'                                     => 'Tanzania',
			'Thailand'                                     => 'Thailand',
			'Timor Leste'                                  => 'Timor Leste',
			'Togo'                                         => 'Togo',
			'Tokelau'                                      => 'Tokelau',
			'Tonga'                                        => 'Tonga',
			'Trinidad and Tobago'                          => 'Trinidad and Tobago',
			'Tunisia'                                      => 'Tunisia',
			'Turkey'                                       => 'Turkey',
			'Turkmenistan'                                 => 'Turkmenistan',
			'Turks and Caicos Islands'                     => 'Turks and Caicos Islands',
			'Tuvalu'                                       => 'Tuvalu',
			'U.S. Virgin Islands'                          => 'U.S. Virgin Islands',
			'Uganda'                                       => 'Uganda',
			'Ukraine'                                      => 'Ukraine',
			'United Arab Emirates'                         => 'United Arab Emirates',
			'United Kingdom'                               => 'United Kingdom',
			'United States'                                => 'United States',
			'United States Minor Outlying Islands'         => 'United States Minor Outlying Islands',
			'Uruguay'                                      => 'Uruguay',
			'Uzbekistan'                                   => 'Uzbekistan',
			'Vanuatu'                                      => 'Vanuatu',
			'Vatican'                                      => 'Vatican',
			'Venezuela'                                    => 'Venezuela',
			'Vietnam'                                      => 'Vietnam',
			'Wallis and Futuna'                            => 'Wallis and Futuna',
			'Western Sahara'                               => 'Western Sahara',
			'Yemen'                                        => 'Yemen',
			'Zambia'                                       => 'Zambia',
			'Zimbabwe'                                     => 'Zimbabwe',
		);

		$settings['Standard']                 = array(
			'title'       => __( 'General', 'proxy-vpn-blocker' ),
			'icon'        => __( 'fa-solid fa-gears', 'proxy-vpn-blocker' ),
			'description' => __( 'The most important settings for Proxy & VPN Blocker functionality, please configure these settings.', 'proxy-vpn-blocker' ),
			'fields'      => array(
				array(
					'id'          => 'proxycheckio_master_activation',
					'label'       => __( 'Enable Proxy & VPN Blocker', 'proxy-vpn-blocker' ),
					'description' => __( "Set this to 'on'  to enable Proxy & VPN Blocker. If set to 'off' this plugin will not be protecting this site.", 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => 'on',
				),
				array(
					'id'          => 'proxycheckio_API_Key_field',
					'label'       => __( 'proxycheck.io API key', 'proxy-vpn-blocker' ),
					'description' => __( 'Your free API Key with 1,000 daily queries can be obtained when signing up at <a href="https://proxycheck.io" target="_blank">proxycheck.io</a>. Paid proxycheck.io query plans for Proxy & VPN Blocker Plugin users are available for an exclusive discount from the <a href="https://proxyvpnblocker.com/discounted-plans/" target="_blank">Proxy & VPN Blocker Website</a>.', 'proxy-vpn-blocker' ),
					'field-note'  => __( 'Without an API Key you lose some Proxy & VPN Blocker and some proxycheck.io API features. You are also limited to 100 daily queries to the proxycheck.io API.', 'proxy-vpn-blocker' ),
					'type'        => 'apikey',
					'default'     => '',
					'placeholder' => __( 'Get your free API key at proxycheck.io', 'proxy-vpn-blocker' ),
				),
				array(
					'id'           => 'option_ip_header_type',
					'label'        => __( 'Remote Visitor IP Header', 'proxy-vpn-blocker' ),
					'description'  => __( 'Please select the correct Header for your Web Hosting Environment so that Proxy & VPN Blocker is able to get the visitors correct IP Address for processing. You are able to enter a custom value if you require something specific for your hosting environment.', 'proxy-vpn-blocker' ),
					'field-note'   => __( 'This is important if you are using a CDN (Content Delivery Network) Service for your website. If you are unsure, please leave this set to \'Default Header\'. If this is set incorrectly the IP address may instead be that of the CDN Server that served the request to the visitor.', 'proxy-vpn-blocker' ),
					'field-warn-h' => __( 'We think you\'re using CloudFlare, if that\'s the case, you should select CloudFlare Header in the below dropdown.', 'proxy-vpn-blocker' ),
					'type'         => 'select_ip_header_type',
					'default'      => '',
					'options'      => $headers_array,
					'placeholder'  => __( 'Select Header...', 'proxy-vpn-blocker' ),
				),
				array(
					'id'          => 'proxycheckio_VPN_select_box',
					'label'       => __( 'Detect VPNs?', 'proxy-vpn-blocker' ),
					'description' => __( 'Set this to \'on\' to enable detection of VPN Visitors in addition to Proxies.', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
				array(
					'id'          => 'log_user_ip_select_box',
					'label'       => __( 'Log User IP\'s Locally', 'proxy-vpn-blocker' ),
					'description' => __( 'When set to on, User\'s Registration and most recent Login IP Addresses will be logged locally and displayed (with link to proxycheck.io threats page for the IP) in WordPress Users list and on User profile for administrators.', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => 'on',
				),
			),
		);
		$settings['BlockedVisitorAction']     = array(
			'title'       => __( 'Block Action', 'proxy-vpn-blocker' ),
			'icon'        => __( 'fa-solid fa-arrow-down-up-lock', 'proxy-vpn-blocker' ),
			'description' => __( 'Configure the Proxy & VPN Blocker actions in this section. Choose what you want the Plugin to do when it detects the use of proxies or VPNs', 'proxy-vpn-blocker' ),
			'prem-upsell' => __( 'Further blocking options are available in Proxy & VPN Blocker Premium, including Customisable Block Page and Customisable Captcha Challenge Page. Learn more on the <a href="https://proxyvpnblocker.com/premium" target="_blank">Proxy & VPN Blocker Website.</a>', 'proxy-vpn-blocker' ),
			'fields'      => array(
				array(
					'id'          => 'proxycheckio_denied_access_field',
					'label'       => __( 'Access Denied Message', 'proxy-vpn-blocker' ),
					'description' => __( 'You can enter a custom Access Denied message here.', 'proxy-vpn-blocker' ),
					'type'        => 'text',
					'default'     => 'Proxy or VPN detected - Please disable to access this website!',
					'placeholder' => __( 'Custom Access Denied Message', 'proxy-vpn-blocker' ),
				),
				array(
					'id'          => 'proxycheckio_redirect_bad_visitor',
					'label'       => __( 'Redirect to URL', 'proxy-vpn-blocker' ),
					'description' => __( 'Enable redirection of detected bad visitors by setting this to \'on\'. Enter the URL you want to redirect them to in this box. If left unset, blocked visitors will be shown a generic block page with the message set above under "Access Denied Message".', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
				array(
					'id'          => 'proxycheckio_opt_redirect_url',
					'label'       => __( 'Redirection URL', 'proxy-vpn-blocker' ),
					'description' => __( 'Enter a custom redirect URL in this box. This can be either an external website address or a page from within this site.', 'proxy-vpn-blocker' ),
					'type'        => 'text',
					'default'     => 'https://wordpress.org',
					'placeholder' => __( 'https://wordpress.org', 'proxy-vpn-blocker' ),
				),
			),
		);
		$settings['RiskScore']                = array(
			'title'       => __( 'IP Risk Scores', 'proxy-vpn-blocker' ),
			'icon'        => __( 'fa-solid fa-chart-line', 'proxy-vpn-blocker' ),
			'description' => __( 'You can optionally opt to use IP Risk Score Checking.', 'proxy-vpn-blocker' ),
			'fields'      => array(
				array(
					'id'          => 'proxycheckio_risk_select_box',
					'label'       => __( 'Risk Score Checking', 'proxy-vpn-blocker' ),
					'description' => __( 'Set this to \'on\' to enable the proxycheck.io Risk Score feature.', 'proxy-vpn-blocker' ),
					'field-note'  => __( 'When using this feature your proxycheck.io positive detection log may not reflect what has actually been blocked by this plugin because they would still be positively detected, but the action will be taken by Proxy & VPN Blocker based on the IP Risk Score. IP\'s allowed through with the risk score feature are not cached as Known Good IP\'s.', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
				array(
					'id'          => 'proxycheckio_max_riskscore_proxy',
					'label'       => __( 'Risk Score - Proxies', 'proxy-vpn-blocker' ),
					'description' => __( 'If Risk Score checking is enabled, Any Proxies with a Risk Score equal to or higher than the value set here will be blocked and if the risk score is lower they will be allowed. - Default value is 33', 'proxy-vpn-blocker' ),
					'type'        => 'textslider-riskscore-proxy',
					'default'     => '33',
					'placeholder' => __( '33', 'proxy-vpn-blocker' ),
				),
				array(
					'id'          => 'proxycheckio_max_riskscore_vpn',
					'label'       => __( 'Risk Score - VPN\'s', 'proxy-vpn-blocker' ),
					'description' => __( 'If detecting VPN\'s and Risk Score checking is enabled, any VPN with a Risk Score equal to or higher than the value set here will be blocked and if the risk score is lower they will be allowed. - Default value is 66', 'proxy-vpn-blocker' ),
					'type'        => 'textslider-riskscore-vpn',
					'default'     => '66',
					'placeholder' => __( '66', 'proxy-vpn-blocker' ),
				),
			),
		);
		$settings['BlockCountriesContinents'] = array(
			'title'       => __( 'Locational Restrictions', 'proxy-vpn-blocker' ),
			'icon'        => __( 'fa-solid fa-map-location-dot', 'proxy-vpn-blocker' ),
			'description' => __( 'By Default this is Blacklist of Countries/Continents thet you do not want to access protected parts of this site, You can opt to make this a Country/Continent Whitelist if you only want to allow a select few countries. IP\'s detected as Proxies/VPN\'s from Whitelisted Countries will still be blocked.', 'proxy-vpn-blocker' ),
			'note-deprec' => __( 'This method of blacklisting (or whitelisting) Countries/Continents is superseded by the <a href="https://proxycheck.io/api/?cr=1" target="_blank">Custom Rules feature of the proxycheck.io API</a>. The proxycheck.io API helpfully provides a Custom Rules Library with various example configurations that can be altered for your needs. It is recommended that you use the proxycheck.io Custom Rules feature for blacklisting/whitelisting Countries/Continents instead of this tab.', 'proxy-vpn-blocker' ),
			'fields'      => array(
				array(
					'id'          => 'proxycheckio_blocked_countries_field',
					'label'       => __( 'Country/Continent', 'proxy-vpn-blocker' ),
					'description' => __( 'You can block specific Countries & Continents by adding them in this list. You can opt to make this a Whitelist below and then only the selected Countries/Continents will be allowed through.', 'proxy-vpn-blocker' ),
					'field-note'  => __( 'This is not affected by IP Risk Score Checking options. IP\'s that are not detected as bad by the proxycheck.io API but are blocked due to your settings here will not show up in your detections log. If you require this information then it is recommended that you use the Rules Feature of proxycheck.io instead of this.', 'proxy-vpn-blocker' ),
					'type'        => 'select_country_multi',
					'options'     => $countries_list,
					'placeholder' => __( 'Select or search...', 'proxy-vpn-blocker' ),
				),
				array(
					'id'            => 'proxycheckio_whitelist_countries_select_box',
					'label'         => __( 'Treat Country/Continent List as a Whitelist', 'proxy-vpn-blocker' ),
					'description'   => __( 'If this is turned \'on\' then the Countries/Continents selected above will be Whitelisted instead of Blacklisted, all other countries will be blocked.', 'proxy-vpn-blocker' ),
					'field-warning' => __( 'This Could Be Your Own Country/Continent! You would have to add your own Country or Continent or you WILL get blocked from logging in. Please see the FAQ for instructions on how to fix this if it happens!', 'proxy-vpn-blocker' ),
					'field-note'    => __( 'This will not turn on if your country list above is empty! Bad IP\'s from whitelisted Countries/Continents will still be blocked! ', 'proxy-vpn-blocker' ),
					'type'          => 'checkbox',
					'default'       => '',
				),
			),
		);
		$settings['RestrictPagePost']         = array(
			'title'       => __( 'Page Caching', 'proxy-vpn-blocker' ),
			'icon'        => __( 'fa-solid fa-scroll', 'proxy-vpn-blocker' ),
			'description' => __( 'Settings relating to Caching of WordPress Pages and Posts.', 'proxy-vpn-blocker' ),
			'fields'      => array(
				array(
					'id'          => 'cache_buster',
					'label'       => __( 'BETA: Add DONOTCACHEPAGE Headers', 'proxy-vpn-blocker' ),
					'description' => __( 'This will add no cache headers to your selected Pages and Posts and Login in order to prevent them from being cached by WordPress cache plugins in order to allow visitors to be checked and blocked as necessary, instead of cache serving them the page anyway.', 'proxy-vpn-blocker' ),
					'field-note'  => __( 'When using this option the pages selected for visitor IP checking and blocking will not be served by cache plugins, this has the potential to degrade performance on these pages but the impact should be minimal. Unfortunately there is no alternative if you want to block on pages/posts except in cases where Cache Plugins have the option of Deferred Execution or Late Init, <a href="https://proxyvpnblocker.com/2023/06/01/wordpress-caching-plugins-and-proxy-vpn-blocker-an-explainer/" target="_blank">Please see the Proxy & VPN Blocker Website for further information on this</a>', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
			),
		);
		$settings['Advanced']                 = array(
			'title'        => __( 'Advanced', 'proxy-vpn-blocker' ),
			'icon'         => __( 'fa-solid fa-screwdriver-wrench', 'proxy-vpn-blocker' ),
			'description'  => __( 'These are Advanced Settings that are not generally recommended to be altered from their defaults.', 'proxy-vpn-blocker' ),
			'warn-message' => __( 'Caution is advised if altering any of these settings!', 'proxy-vpn-blocker' ),
			'fields'       => array(
				array(
					'id'          => 'proxycheckio_Custom_TAG_field',
					'label'       => __( 'Custom Tag', 'proxy-vpn-blocker' ),
					'description' => __( 'By default the tag used is siteurl.com/path/to/page-accessed, however you can supply your own descriptive tag. return to default by leaving this empty.', 'proxy-vpn-blocker' ),
					'field-note'  => __( 'You can enter \'0\' in this box to disable tagging completely if you want queries to be private.', 'proxy-vpn-blocker' ),
					'type'        => 'text',
					'default'     => '',
					'placeholder' => __( 'Custom Tag', 'proxy-vpn-blocker' ),
				),
				array(
					'id'          => 'proxycheckio_good_ip_cache_time',
					'label'       => __( 'Known Good IP Cache', 'proxy-vpn-blocker' ),
					'description' => __( 'Known Good IP\'s are cached after the first time they are checked to save on queries to the proxycheck.io API, you can set this to between 10 and 240 mins (4hrs) - Default cache time is 30 minutes.', 'proxy-vpn-blocker' ),
					'type'        => 'textslider-good-ip-cache-time',
					'default'     => '30',
					'placeholder' => __( '30', 'proxy-vpn-blocker' ),
				),
				array(
					'id'          => 'proxycheckio_Days_Selector',
					'label'       => __( 'Last Detected Within', 'proxy-vpn-blocker' ),
					'description' => __( 'You can set this from 1 to 60 days depending on how strict you want the detection to be. 1 day would be very liberal, 60 days would be very strict.', 'proxy-vpn-blocker' ),
					'type'        => 'textslider',
					'default'     => '7',
					'placeholder' => __( '7', 'proxy-vpn-blocker' ),
				),
				array(
					'id'            => 'protect_login_authentication',
					'label'         => __( 'Protect WordPress Login/Auth', 'proxy-vpn-blocker' ),
					'description'   => __( 'This option blocks Proxy/VPN\'s on wp-login.php, Login Authentication.', 'proxy-vpn-blocker' ),
					'field-warning' => __( 'It is NOT EVER recommended to turn this off, but this option is provided for specific use cases.', 'proxy-vpn-blocker' ),
					'field-note'    => __( 'If this setting is turned off: Users logging in via \'wp-login.php\' will not be cached as good because checks are not run. Registration will not be protected if Registration is enabled for your site.', 'proxy-vpn-blocker' ),
					'type'          => 'checkbox',
					'default'       => 'on',
				),
				array(
					'id'          => 'proxycheckio_all_pages_activation',
					'label'       => __( 'Block on Entire Site', 'proxy-vpn-blocker' ),
					'description' => __( 'Set this to \'on\' to block Proxies/VPN\'s on every page of your website. This is at the expense of higher query usage and is NOT generally recommended.', 'proxy-vpn-blocker' ),
					'field-note'  => __( 'This will not work if you are using a caching plugin. Please see FAQ.', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
				array(
					'id'          => 'allow_staff_bypass',
					'label'       => __( 'Allow Staff Bypass', 'proxy-vpn-blocker' ),
					'description' => __( 'Set this to \'on\' to allow non Admin Staff Members (Editors & Authors) to Bypass the checks when \'Block on Entire Site\' is in use and \'Protect WordPress Login/Auth\' is turned off. This will allow Site Staff access to the WordPress Dashboard.', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
				array(
					'id'          => 'proxycheckio_Admin_Alert_Denied_Email',
					'label'       => 'proxycheck.io \'Denied\' Status Emails',
					'description' => __( 'If proxycheck.io returns a \'denied\' status message when a query is made, PVB will send you an email containing the details. To avoid too many emails being sent, this will only happen again if 3hrs have passed and there is still a \'denied\' status message.', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
				array(
					'id'          => 'proxycheckio_current_key',
					'label'       => 'Unique Settings Key',
					'description' => 'Each time the settings are saved, A unique ID is also saved, this ensures that previously cached "Known Good" IP\'s are re-checked under the new settings, instead of waiting until the cache for that IP expires.',
					'placeholder' => '',
					'type'        => 'hidden_key_field',
				),
				array(
					'id'          => 'option_help_mode',
					'label'       => 'Proxy & VPN Blocker Help Mode',
					'description' => __( 'Provides further information as an admin notice if there is a misconfiguration with certain settings.', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => 'on',
				),
				array(
					'id'          => 'enable_debugging',
					'label'       => 'Proxy & VPN Blocker Debug Page',
					'description' => __( 'Enables the Proxy & VPN Blocker Debugging Page, this option is for diagnostics information if you are having problems and require support. When this Option is turned on, you will see an extra menu option under "PVB Settings", in the WordPress Admin Sidebar.', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
				array(
					'id'          => 'cleanup_on_uninstall',
					'label'       => 'Cleanup on Uninstall',
					'description' => __( 'Cleans up all Proxy & VPN Blocker settings on plugin uninstall.', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
			),
		);
		//phpcs:ignore
		$settings = apply_filters( 'plugin_settings_fields', $settings );
		return $settings;
	}

	/**
	 * Register Proxy & VPN Blocker Settings.
	 *
	 * @return void
	 */
	public function register_settings() {
		if ( is_array( $this->settings ) ) {
			foreach ( $this->settings as $section => $data ) {
				// Add section to page.
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field.
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field.
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page.
					add_settings_field(
						$field['id'],
						$field['label'],
						array( $this->parent->admin, 'display_field' ),
						$this->parent->_token . '_settings',
						$section,
						array(
							'field'  => $field,
							'prefix' => $this->base,
						)
					);
				}
			}
		}
	}
	/**
	 * Settings Sections.
	 *
	 * @param  string $section Settings Section.
	 */
	public function settings_section( $section ) {
		//phpcs:ignore
		echo '<p class="description"> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		if ( isset( $this->settings[ $section['id'] ]['prem-upsell'] ) ) {
			//phpcs:ignore
			echo '<div class="pvb-prem-info"> ' . $this->settings[ $section['id'] ]['prem-upsell'] . '</div>' . "\n";
		}
		if ( isset( $this->settings[ $section['id'] ]['warn-message'] ) ) {
			//phpcs:ignore
			echo '<div class="pvb-warn-note"> ' . $this->settings[ $section['id'] ]['warn-message'] . '</div>' . "\n";
		}
		if ( isset( $this->settings[ $section['id'] ]['note-deprec'] ) ) {
			//phpcs:ignore
			echo '<div class="pvb-deprec"> ' . $this->settings[ $section['id'] ]['note-deprec'] . '</div>' . "\n";
		}
	}

	/**
	 * Custom function for settings page.
	 *
	 * @param  string $page Settings Page.
	 */
	public function pvb_do_settings_sections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;

		//phpcs:disable
		if ( isset( $_GET['settings-updated'] ) ) {
			echo '<div id="pvbshow" class="pvbsuccess">Settings Updated</div>' . "\n";
		}
		if ( ! isset( $wp_settings_sections ) || ! isset( $wp_settings_sections[ $page ] ) ) {
			return;
		}
		echo '	<div class="hide-no-script">' . "\n";
		echo '<div class="settings-grouping">' . "\n"; // settings grouping start.
		echo '	<div id="pvb-settings-tabs">' . "\n"; // settings tabs start.
		echo '		<ul class="nav-tab-wrapper">' . "\n";

		echo '	<div class="pvb-settings-tabs-logo">' . "\n"; // settings tabs logo start.
		echo '	</div>' . "\n"; // settings tabs logo end.

		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
			echo '		<li class="pvbsettingstabs" data-tab="tab-' . $section['id'] . '">' . "\n";
			echo '          <div class="">' . "\n";
			echo '				<i class="fa-fw  ' . $this->settings[ $section['id'] ]['icon'] . '"></i>' . "\n";
			echo '				<a class="tab-text">' . $section['title'] . '</a>' . "\n";
			echo '			</div>' . "\n";
			echo '		</li>' . "\n";
		}

		echo '	<div class="pvb-settings-tabs-after">' . "\n"; // settings tabs after start.
		echo '		<p>Proxy & VPN Blocker ' . get_option( 'proxy_vpn_blocker_version' ) . '</p>' . "\n";
		echo '	</div>' . "\n"; // settings tabs after end.

		echo '		</ul>' . "\n";
		echo '		<div class="tabs-content">' . "\n"; // tabs content start.
		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
			if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
				continue;
			}
			echo '		<div class="pvboptionswrap" id="tab-' . $section['id'] . '">' . "\n";
			echo '			<div class="pvoptionswrap-head">' . "\n";
			echo '		<h1>' . $section['title'] . '</h1>' . "\n";
			call_user_func( $section['callback'], $section );
			echo '			</div>' . "\n";
			echo '			<div class="settings-form-wrapper">' . "\n";
			$this->pvb_do_settings_fields( $page, $section['id'] );
			echo '			</div>' . "\n";
			echo '			<input name="Submit" type="submit" class="pvbdefault submit" value="' . esc_attr( __( 'Save Settings', 'proxy-vpn-blocker' ) ) . '" />' . "\n";
			echo '		</div>' . "\n";
		}
		echo '		</div>' . "\n"; // tabs content end.
		echo '	</div>' . "\n"; // settings tabs end.
		echo '<noscript>
				<style type="text/css">
					.hide-no-script {display:none;}
				</style>
				<div id="pvbshow" class="pvbfail">
				You don\'t have javascript enabled.  Javascript is required for the correct operation of the Proxy &amp; VPN Blocker Settings UI. Please enable javascript in your browser to continue.
				</div>
			</noscript>' . "\n";
		//phpcs:enable
		echo '</div>' . "\n"; // settings grouping end.
	}

	/**
	 * Custom Settings Fields.
	 *
	 * @param  string $page Settings Page.
	 * @param  string $section Settings Section.
	 */
	public function pvb_do_settings_fields( $page, $section ) {
		global $wp_settings_fields;

		if ( ! isset( $wp_settings_fields ) ||
			! isset( $wp_settings_fields[ $page ] ) ||
			! isset( $wp_settings_fields[ $page ][ $section ] ) ) {
			return;
		}
		//phpcs:disable
		foreach ( (array) $wp_settings_fields[ $page ][ $section ] as $field ) {
			echo '<div class="pvb_settingssection_container">' . "\n";
			if ( ! empty( $field['args']['label_for'] ) ) {
				echo '<div class="pvb_settingsform_row">' . "\n";
				echo '	<div class="pvb_settingsform_left box">' . "\n";
				echo '		<p><label for="' . $field['args']['label_for'] . '">' . $field['title'] . '</label><br />' . "\n";
				echo '	</div>' . "\n";
				echo '	<div class="pvb_settingsform_right">' . "\n";
				echo '	</div>' . "\n";
				echo '</div>' . "\n";
				//phpcs:ignore
				echo $html;
			} else {
				echo '<div class="pvb_settingsform_row">' . "\n";
				echo '	<div class="pvb_settingsform_left box">' . "\n";
				echo '		<h3>' . $field['title'] . '</h3>' . "\n";
				echo '	</div>' . "\n";
				echo '	<div class="pvb_settingsform_right">' . "\n";
				call_user_func( $field['callback'], $field['args'] ) . "\n";
				echo '	</div>' . "\n";
				echo'</div>' . "\n";
			}
			echo '</div>' . "\n";
		}
		//phpcs:enable
	}

	/**
	 * Load settings page content
	 *
	 * @return void
	 */
	public function settings_page() {
		/**
		 * Safety measure to prevent redirect loop if custom block page is defined in list of blocked pages
		 *
		 * @Since 1.4.0
		 */
		if ( ! empty( get_option( 'pvb_proxycheckio_custom_blocked_page' ) ) && ! empty( get_option( 'pvb_proxycheckio_blocked_select_pages_field' ) ) ) {
			$blocked_pages     = get_option( 'pvb_proxycheckio_blocked_select_pages_field' );
			$custom_block_page = get_option( 'pvb_proxycheckio_custom_blocked_page' );
			$key               = array_search( $custom_block_page[0], $blocked_pages );
			if ( ( $key ) !== false ) {
				unset( $blocked_pages[ $key ] );
				update_option( 'pvb_proxycheckio_blocked_select_pages_field', $blocked_pages );
			}
		}

		$get_api_key = get_option( 'pvb_proxycheckio_API_Key_field' );
		if ( ! get_option( 'pvb_proxycheck_apikey_details' ) && ! empty( $get_api_key ) ) {
			// Build page HTML.
			$request_args  = array(
				'timeout'     => '10',
				'blocking'    => true,
				'httpversion' => '1.1',
			);
			$request_usage = wp_remote_get( 'https://proxycheck.io/dashboard/export/usage/?key=' . $get_api_key, $request_args );
			$api_key_usage = json_decode( wp_remote_retrieve_body( $request_usage ) );

			if ( ! empty( $api_key_usage ) ) {
				$plan_tier = $api_key_usage->{'Plan Tier'};

				if ( 'Paid' === $plan_tier ) {
					$api_key_details = array(
						'tier'            => 'Paid',
						'activation_date' => gmdate( 'Y-m-d' ),
					);
				} elseif ( 'Free' === $plan_tier ) {
					$api_key_details = array(
						'tier'            => 'Free',
						'activation_date' => gmdate( 'Y-m-d' ),
					);
				}
			} else {
				$api_key_details = array(
					'tier'            => 'Unknown',
					'activation_date' => '',
				);
			}
			add_option( 'pvb_proxycheck_apikey_details', $api_key_details );
		}

		// Build page HTML.
		//phpcs:disable
		echo '<div class="wrap" id="' . $this->parent->_token . '_settings" dir="ltr">' . "\n";
		echo '<h2 class="pvb-wp-notice-fix"></h2>' . "\n";

		include_once 'review-mode.php';

		if ( empty( get_option( 'pvb_proxycheckio_API_Key_field' ) ) ) {
			echo '<div class="pvbinfowrap">' . "\n";
			echo '		<div class="pvbinfowraptext">' . "\n";
			echo '			<h1>' . __( 'Enhance Your Experience with a free proxycheck.io API Key', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
			echo '			<div class="pvbinfowrapblock">' . "\n";
			echo '          	<h2>' . __('Free API Key Benefits:', 'proxy-vpn-blocker' ) . '</h2>' . "\n";
			echo '         		 <ul>' . "\n";
			echo '          		<li>' . __( '✅ 1,000 queries per day (10x more).', 'proxy-vpn-blocker' ) . '</li>' . "\n";
			echo '          		<li>' . __( '✅ Full access to all API features.', 'proxy-vpn-blocker' ) . '</li>' . "\n";
			echo '          		<li>' . __( '✅ Complete statistics dashboard.', 'proxy-vpn-blocker' ) . '</li>' . "\n";
			echo '          		<li>' . __( '✅ Enhanced security monitoring.', 'proxy-vpn-blocker' ) . '</li>' . "\n";
			echo '          	</ul>' . "\n";
			echo '			</div>' . "\n";
			echo '			<div class="pvbinfowrapblock">' . "\n";
			echo '          	<h2>' . __( 'Higher Query Tiers Available' ) . '</h2>' . "\n";
			echo '				<p>' . __( 'Need more queries? Paid plans offer higher API query limits (10,000+ per day), ideal for larger websites and organizations. These are available from the <a href="https://proxyvpnblocker.com/discounted-plans/" target="_blank">Proxy & VPN Blocker Website</a> for a discounted rate.', 'proxy-vpn-blocker' ) . '</p>' . "\n";
			echo '			</div>' . "\n";
			echo '			<div class="pvbinfowrapblock">' . "\n";
			echo '         	 	<h2>' . __('Quick Setup Guide', 'proxy-vpn-blocker' ) . '</h2>' . "\n";
			echo '         		<ol>' . "\n";
			echo '          		<li>' . __( 'Visit <a href="https://proxycheck.io" target="_blank">proxycheck.io</a> to create an account.', 'proxy-vpn-blocker' ) . '</li>' . "\n";
			echo '          		<li>' . __( 'Get your Free API key from your proxycheck.io dashboard.', 'proxy-vpn-blocker' ) . '</li>' . "\n";
			echo '          		<li>' . __( 'Open the General tab below.', 'proxy-vpn-blocker' ) . '</li>' . "\n";
			echo '          		<li>' . __( 'Paste your API key in the designated field.', 'proxy-vpn-blocker' ) . '</li>' . "\n";
			echo '          		<li>' . __( 'Save changes to activate full functionality.', 'proxy-vpn-blocker' ) . '</li>' . "\n";
			echo '          	</ol>' . "\n";
			echo '				<p>' . __( 'Need help? Visit our <a href="https://proxyvpnblocker.com/installation-and-configuration-free/" target="_blank">Installation & Configuration Guide</a> or visit the <a href="https://wordpress.org/support/plugin/proxy-vpn-blocker/" target="_blank">WordPress Support Community</a>.', 'proxy-vpn-blocker' ) . '</p>' . "\n";
			echo '			</div>' . "\n";
			echo '		</div>' . "\n";
			echo '</div>' . "\n";
		}
		echo '<nav>' . "\n";
		echo '	<input type="checkbox" id="checkbox" />' . "\n";
		echo '	<label for="checkbox">' . "\n";
		echo '  	<ul class="menu first">' . "\n";
		echo '			<li><a href="https://proxyvpnblocker.com" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> PVB Website</a></li>' . "\n";
		echo '			<li><a href="https://wordpress.org/support/plugin/proxy-vpn-blocker/" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> Support & Issues</a></li>' . "\n";
		echo '			<li><a href="https://proxyvpnblocker.com/installation-and-configuration-free/" target="_blank"><i class="fa-solid fa-circle-question"></i> Configuration Guide</a></li>' . "\n";
		echo '			<li><a href="https://proxyvpnblocker.com/faq/" target="_blank"><i class="fa-solid fa-file-lines"></i> FAQ</a></li>' . "\n";
		echo '			<li id="premium"><a href="https://proxyvpnblocker.com/premium/" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> Go Premium!</a></li>' . "\n";
		echo ' 	 	</ul>' . "\n";
		echo '	  <span class="toggle">Menu</span>' . "\n";
		echo '	</label>' . "\n";
		echo '</nav>' . "\n";

		echo '<form method="post" id="pvb-options-form" class="pvb" action="options.php" enctype="multipart/form-data">' . "\n";
			// Get settings fields.
			ob_start();
			settings_fields( $this->parent->_token . '_settings' );
			$this->pvb_do_settings_sections( $this->parent->_token . '_settings' );
		echo ob_get_clean();
		echo '</form>' . "\n";
		echo '</div>' . "\n";
		//phpcs:enable
	}

	/**
	 * Load Information and Statistics page content.
	 *
	 * @return void
	 */
	public function statistics_page() {
		include_once 'proxycheckio-apikey-statistics.php';
	}

	/**
	 * Load IP Blacklist page.
	 *
	 * @return void
	 */
	public function ipblacklist_page() {
		include_once 'proxycheckio-blacklist.php';
	}

	/**
	 * Load IP Whitelist page.
	 *
	 * @return void
	 */
	public function ipwhitelist_page() {
		include_once 'proxycheckio-whitelist.php';
	}

	/**
	 * Load pvb action log page.
	 *
	 * @return void
	 */
	public function action_log_page() {
		include_once 'logs/proxy-vpn-blocker-logs.php';
	}


	/**
	 * Load debugging page.
	 *
	 * @return void
	 */
	public function debugging_page() {
		include_once 'dbg/debugging.php';
	}

	/**
	 * Main proxy_vpn_blocker_Settings Instance.
	 *
	 * Ensures only one instance of proxy_vpn_blocker_Settings is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @see proxy_vpn_blocker()
	 * @param object $parent Object instance.
	 * @return Main Proxy_VPN_Blocker_Settings instance
	 */
	public static function instance( $parent ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $parent );
		}
		return self::$instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cloning is forbidden.' ) ), esc_attr( $this->parent->_version ) );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Unserializing instances of this class is forbidden.' ) ), esc_attr( $this->parent->_version ) );
	} // End __wakeup ()
}
