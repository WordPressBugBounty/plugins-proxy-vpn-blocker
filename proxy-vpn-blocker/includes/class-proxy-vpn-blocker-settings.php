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
	 * Get the instance of Proxy_VPN_Blocker_Premium_Settings.
	 */
	public function maybe_redirect_to_setup() {
		// Only redirect if the user is accessing the PVB Settings page.
		$is_main_settings_page = isset( $_GET['page'] ) && $_GET['page'] === $this->parent->_token . '_settings';

		if ( current_user_can( 'manage_options' ) && $is_main_settings_page && 'on' !== get_option( 'pvb_setup_complete' ) ) {
			wp_redirect( admin_url( 'admin.php?page=pvb_setup_wizard&step=1' ) );
			exit;
		}
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
		$icon_svg = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path fill-rule="evenodd" clip-rule="evenodd" d="M9.69249 0.140441C8.84054 0.552021 7.59335 1.02836 6.57655 1.33053C5.4111 1.67688 3.8166 2.00676 2.65977 2.14088C2.18519 2.1959 2.08793 2.22401 2.03397 2.32172C1.98891 2.40329 1.98857 10.2659 2.03358 10.7904C2.12148 11.8144 2.34936 12.692 2.7444 13.5277C3.67421 15.4948 5.54445 17.2928 8.70528 19.2582C9.22527 19.5816 9.91485 19.9803 9.98679 19.9992C10.0452 20.0146 10.4316 19.8005 11.2111 19.3207C14.9533 17.0174 16.9233 14.9498 17.6655 12.5462C17.7633 12.2293 17.8535 11.8264 17.931 11.3597L17.9848 11.0361L17.9924 6.70504L18 2.37404L17.9475 2.30498C17.9186 2.26698 17.8696 2.22595 17.8387 2.21375C17.8077 2.20156 17.5234 2.15676 17.2069 2.1142C14.6449 1.76957 12.3483 1.10316 10.2781 0.103619C10.1597 0.0464614 10.0448 -0.000175682 10.0227 4.97524e-07C10.0006 0.000201845 9.85202 0.0634 9.69249 0.140441ZM10.2121 1.42089C10.6634 1.65604 11.5966 2.03752 12.2946 2.27224C13.4765 2.6697 14.927 3.03071 16.1619 3.23476C16.4697 3.28562 16.7356 3.3315 16.7528 3.33674C16.7783 3.34449 16.7826 4.05155 16.7758 7.10308C16.7666 11.1827 16.775 10.918 16.6338 11.5979C16.505 12.2184 16.1646 13.0891 15.841 13.6261C15.5147 14.1677 15.0015 14.8116 14.51 15.2964C13.5714 16.2222 12.1479 17.2737 10.4533 18.2931L10.0239 18.5513L9.61396 18.3074C7.13608 16.833 5.35661 15.3368 4.41479 13.9359C3.78767 13.0031 3.41061 11.9601 3.27542 10.7844C3.25058 10.5684 3.24357 9.71806 3.24357 6.92116V3.33482L3.38954 3.31479C3.73537 3.2673 4.61807 3.10884 5.05411 3.01594C6.75938 2.65266 8.55892 2.05347 9.76356 1.44789C9.88398 1.38736 9.99648 1.33687 10.0135 1.33572C10.0306 1.33456 10.1199 1.37289 10.2121 1.42089ZM9.83545 2.63585C9.34329 3.28575 8.89231 4.08344 8.60091 4.81944C8.48588 5.10991 8.28437 5.73889 8.30084 5.75593C8.30612 5.76139 8.44581 5.65415 8.61125 5.51759C8.94053 5.24582 9.27519 4.98303 9.29203 4.98303C9.29791 4.98303 9.30524 6.3223 9.3083 7.95921L9.31387 10.9354L9.66807 11.2965C9.86287 11.4952 10.0263 11.6538 10.0312 11.6489C10.0362 11.6441 10.1942 11.4824 10.3824 11.2897L10.7245 10.9393V7.96117C10.7245 6.32321 10.7303 4.98303 10.7374 4.98303C10.7445 4.98303 10.9744 5.16369 11.2483 5.38447C11.5222 5.60527 11.7463 5.78191 11.7463 5.777C11.7463 5.74035 11.5055 5.00314 11.4376 4.83202C11.2567 4.37582 10.9458 3.77356 10.632 3.27158C10.4327 2.95274 10.0489 2.41585 10.0202 2.41585C10.0102 2.41585 9.92707 2.51484 9.83545 2.63585ZM7.09961 5.4706C6.30373 6.191 5.66137 7.12074 5.31498 8.05359C5.21842 8.31365 5.12405 8.63571 5.13967 8.65187C5.14505 8.65743 5.23518 8.60568 5.33994 8.53687C5.62638 8.34874 5.9344 8.15426 5.94594 8.15426C5.95158 8.15426 5.95625 8.98387 5.95632 9.99785L5.95644 11.8414L6.43474 12.2567C6.69778 12.4851 7.26433 12.9778 7.69375 13.3516C8.12315 13.7253 8.66326 14.1945 8.89399 14.3942L9.31348 14.7573L9.31385 15.7281L9.31421 16.699L9.67169 17.0432L10.0291 17.3875L10.3748 17.0432L10.7205 16.699L10.7225 15.7267L10.7245 14.7544L10.8067 14.6896C10.9014 14.6148 12.3558 13.3608 13.3948 12.4581L14.1045 11.8414L14.1053 9.99496L14.1062 8.14847L14.1731 8.19426C14.3603 8.32244 14.8955 8.65479 14.9027 8.64739C14.9072 8.64268 14.8611 8.48168 14.8002 8.28959C14.6039 7.6704 14.2037 6.92813 13.7503 6.34213C13.5725 6.11234 13.0319 5.54223 12.8228 5.36406L12.6951 5.25521V8.19926V11.1433L12.166 11.6048C11.875 11.8586 11.3796 12.2933 11.0651 12.5708C10.7507 12.8482 10.3884 13.1675 10.26 13.2802L10.0265 13.4852L9.42065 12.9568C9.0874 12.6662 8.48362 12.1388 8.07892 11.7848L7.34309 11.1412L7.33691 8.2013L7.33073 5.2614L7.09961 5.4706Z" fill="currentColor"/>
			</svg>';
		add_action( 'admin_init', array( $this, 'maybe_redirect_to_setup' ) );
		add_submenu_page(
			'Proxy & VPN Blocker',
			__( 'Proxy & VPN Blocker Setup Wizard', 'proxy-vpn-blocker' ),
			__( 'Proxy & VPN Blocker Setup Wizard', 'proxy-vpn-blocker' ),
			'manage_options',
			'pvb_setup_wizard',
			array( $this, 'render_setup_page' )
		);
		add_menu_page( 'Proxy & VPN Blocker', 'PVB Settings', 'manage_options', $this->parent->_token . '_settings', array( $this, 'settings_page' ), 'data:image/svg+xml;base64,' . base64_encode( $icon_svg ) );
		add_submenu_page( $this->parent->_token . '_settings', 'Blacklist Editor', 'Blacklist Editor', 'manage_options', $this->parent->_token . '_blacklist', array( $this, 'ipblacklist_page' ) );
		add_submenu_page( $this->parent->_token . '_settings', 'Whitelist Editor', 'Whitelist Editor', 'manage_options', $this->parent->_token . '_whitelist', array( $this, 'ipwhitelist_page' ) );
		add_submenu_page( $this->parent->_token . '_settings', 'Statistics', 'API Key Statistics', 'manage_options', $this->parent->_token . '_statistics', array( $this, 'statistics_page' ) );
		add_submenu_page( $this->parent->_token . '_settings', 'Action Log', 'Action Log', 'manage_options', $this->parent->_token . '_action_log', array( $this, 'action_log_page' ) );
		if ( 'on' === get_option( 'pvb_enable_debugging' ) ) {
			add_submenu_page( $this->parent->_token . '_settings', 'PVB Debugging', 'PVB Debugging', 'manage_options', $this->parent->_token . '_debugging', array( $this, 'debugging_page' ) );
		}
	}

	/**
	 * Render the settings wizard page.
	 *
	 * @return void
	 */
	public function render_setup_page() {
		include plugin_dir_path( __FILE__ ) . 'setup-wizard/pvb-setup-wizard.php';
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
					'description' => __( 'Master toggle: Enable visitor IP address monitoring. When disabled, all plugin functionality is turned off.', 'proxy-vpn-blocker' ),
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
					'label'       => __( 'Also Detect VPNs?', 'proxy-vpn-blocker' ),
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
		$settings['PageCaching']         = array(
			'title'       => __( 'Page Caching', 'proxy-vpn-blocker' ),
			'icon'        => __( 'fa-solid fa-scroll', 'proxy-vpn-blocker' ),
			'description' => __( 'Settings relating to Caching of WordPress Pages and Posts. Sometimes Proxy & VPN Blocker may not be able to function fully due to WordPress Page Caching being in effect, a page served by cache means an IP check will not happen as the cache serves a static version of pages to the visitor before Plugins like Proxy & VPN Blocker can run.', 'proxy-vpn-blocker' ),
			'fields'      => array(
				array(
					'id'          => 'cache_buster',
					'label'       => __( 'Add DONOTCACHEPAGE Headers', 'proxy-vpn-blocker' ),
					'description' => __( 'This will add no cache headers to your selected Pages and Posts and Login in order to prevent them from being cached by WordPress cache plugins in order to allow visitors to be checked and blocked as necessary, instead of cache serving them the page anyway.', 'proxy-vpn-blocker' ),
					'field-note'  => __( 'When using this option the pages and posts selected for visitor IP checking and blocking will not be served by cache plugins that respect the header, this has the potential to degrade performance on these pages but the impact should be minimal. Unfortunately there is no alternative if you want to block on pages/posts except in cases where Cache Plugins have the option of Deferred Execution or Late Init. If Block on Entire site is enabled along with this setting, then no cache headers will be defined sitewide. <a href="https://proxyvpnblocker.com/2023/06/01/wordpress-caching-plugins-and-proxy-vpn-blocker-an-explainer/" target="_blank">Please see the Proxy & VPN Blocker Website for further information on this</a>', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => 'on',
				),
			),
		);
		$settings['CORS']                     = array(
			'title'        => __( 'CORS API Settings', 'proxy-vpn-blocker' ),
			'icon'         => __( 'fa-solid fa-passport', 'proxy-vpn-blocker' ),
			'description'  => __( "These settings are for the proxycheck.io Cross-Origin Resource Sharing (CORS) API feature. This is intended as a fallback feature in cases where the PHP Version of the API Calls are not functioning, generally this is is the case if you are using a Caching Plugin or if the page is loaded from a web caching service. Also do note that this can work with specific pages/posts or block on all pages, but does not work with settings under 'Locational Restrictions' and 'Email Filtering' tabs. It is recommended that you use Custom Rules on proxycheck.io for specific tuning of how IP's are blocked here.", 'proxy-vpn-blocker' ),
			'warn-message' => __( 'Please note that this will display a generic "Please deactivate your proxy to access this page" message to a blocked visitor. In cases where the Anti Adblock CORS is enabled and an Adblocker is detected as preventing the contacting of the proxycheck.io CORS API, the message "Please deactivate your adblocker to access this page." will be displayed instead. CORS replaces regular blocking methods provided by Proxy & VPN Blocker and is intended to only be used if you have issues with those not working and troubleshooting steps haven\'t helped.', 'proxy-vpn-blocker' ),
			'fields'       => array(
				array(
					'id'          => 'cors_integration',
					'label'       => __( 'proxycheck.io CORS Support', 'proxy-vpn-blocker' ),
					'description' => __( 'Enable proxycheck.io Cross-Origin Resource Sharing (CORS)', 'proxy-vpn-blocker' ),
					'field-note'  => __( 'proxycheck.io Dashboard API Access is required, with the setting "Automatically Alter Origins" turned on, to be able to automatically add origins to your Origin Names list. When "Automatically Alter Origins" is turned on your sites URL will be automatically added to your Origin Names list when this setting is turned on, without this, you will have to manually add the sites URL to your Origin Names List on your proxycheck.io Dashboard..', 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
				array(
					'id'            => 'auto_alter_proxycheck_CORS_origins',
					'label'         => __( 'Automatically Alter Origins', 'proxy-vpn-blocker' ),
					'description'   => __( 'Automatically Add/Remove origins from CORS origins list on proxycheck.io', 'proxy-vpn-blocker' ),
					'field-note'    => __( 'We can Automatically add/remove CORS origins to your list on proxycheck.io if Dashboard API Access is enabled.', 'proxy-vpn-blocker' ),
					'field-warning' => __( 'If you have multiple WordPress sites sharing a single proxycheck API key it is recommended that you turn this setting off as there is the potential to cause a conflict where the origins will be added by one site and removed by another. Without this setting you will have to manually configure your allowed Origin Names on your proxycheck.io Dashboard.', 'proxy-vpn-blocker' ),
					'type'          => 'checkbox',
					'default'       => '',
				),
				array(
					'id'            => 'proxycheckio_CORS_public_key',
					'label'         => __( 'CORS Public key', 'proxy-vpn-blocker' ),
					'description'   => __( 'Your proxycheck.io Cross-Origin Resource Sharing (CORS) Public Key from your proxycheck.io dashboard (you will find this under the CORS tab).', 'proxy-vpn-blocker' ),
					'field-note'    => __( 'Without this, CORS will not function.', 'proxy-vpn-blocker' ),
					'field-warning' => __( "This key must start with 'public-'. if the key doesn't start with 'public-' it is not the correct key. This field will not accept a non 'public-' key.", 'proxy-vpn-blocker' ),
					'type'          => 'cors_public',
					'default'       => '',
					'placeholder'   => __( 'Get your CORS public key at https://proxycheck.io/dashboard', 'proxy-vpn-blocker' ),
				),
				array(
					'id'          => 'CORS_protect_on_webcache',
					'label'       => __( 'proxycheck.io CORS Protect on Web Cache Services', 'proxy-vpn-blocker' ),
					'description' => __( 'Use proxycheck.io Cross-Origin Resource Sharing (CORS) to protect the site from being viewed by Proxies & VPNs in Web Cache. (e.g. Google, Bing, Archive.org)', 'proxy-vpn-blocker' ),
					'field-note'  => __( "proxycheck.io Dashboard API Access is required to be able to automatically add origins to your Origin Names list. When this setting and 'Automatically Alter Origins' is turned on the Origins 'webcache.googleusercontent.com', 'cc.bingj.com' and 'web.archive.org' will be automatically added to your Origin Names list. Note that for Google/Bing Cache it may take some time for this to work because it will depend on when your site is next crawled. As for Archive.org, older versions of the site in the Archive will not be protected.", 'proxy-vpn-blocker' ),
					'type'        => 'checkbox',
					'default'     => '',
				),
				array(
					'id'          => 'CORS_antiadblock',
					'label'       => __( 'Use Anti Adblock CORS javascript', 'proxy-vpn-blocker' ),
					'description' => __( 'Use the Anti Adblock version of the CORS javascript - Some Ad block lists have added the proxycheck.io domain name, this means that CORS will not function. When this setting is on we can detect if this happens and throw an error when a connection to proxycheck.io is blocked.', 'proxy-vpn-blocker' ),
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
					'id'          => 'setup_complete',
					'label'       => 'Disable Setup Wizard',
					'description' => __( 'Disables the Proxy & VPN Blocker Setup Wizard. Turn off to go back to the Setup Wizard.', 'proxy-vpn-blocker' ),
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
		echo '		<p>Proxy & VPN Blocker Lite: ' . get_option( 'proxy_vpn_blocker_version' ) . '</p>' . "\n";
		echo '	</div>' . "\n"; // settings tabs after end.

		echo '		</ul>' . "\n";
		echo '		<div class="tabs-content">' . "\n"; // tabs content start.
		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
			if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
				continue;
			}
			echo '		<div class="pvboptionswrap" id="tab-' . $section['id'] . '">' . "\n";
			echo '			<div class="pvboptionswrap-head">' . "\n";
			echo '		<h1>' . $section['title'] . '</h1>' . "\n";
			call_user_func( $section['callback'], $section );
			echo '			</div>' . "\n";
			echo '			<div class="settings-form-wrapper">' . "\n";
			$this->pvb_do_settings_fields( $page, $section['id'] );
			echo '			</div>' . "\n";
			echo '			<div class="pvb-submit-footer">' . "\n";
			echo '				<input name="Submit" type="submit" class="pvbdefault submit" value="' . esc_attr( __( 'Save Settings', 'proxy-vpn-blocker' ) ) . '" />' . "\n";
			echo '          </div>' . "\n"; // submit footer end.
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
			echo '			<h1>' . __( 'Enhance Proxy & VPN Blocker functionality with a free proxycheck.io API Key', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
			echo '			<div class="pvbinfowrapblock">' . "\n";
			echo '          	<h2>' . __('Free API Key Benefits:', 'proxy-vpn-blocker' ) . '</h2>' . "\n";
			echo '         		 <ul>' . "\n";
			echo '          		<li>' . __( '✔️ 1,000 queries per day (10x more).', 'proxy-vpn-blocker' ) . '</li>' . "\n";
			echo '          		<li>' . __( '✔️ Full access to all API features.', 'proxy-vpn-blocker' ) . '</li>' . "\n";
			echo '          		<li>' . __( '✔️ Complete statistics dashboard.', 'proxy-vpn-blocker' ) . '</li>' . "\n";
			echo '          		<li>' . __( '✔️ Enhanced security monitoring.', 'proxy-vpn-blocker' ) . '</li>' . "\n";
			echo '          	</ul>' . "\n";
			echo '			</div>' . "\n";
			echo '			<div class="pvbinfowrapblock">' . "\n";
			echo '          	<h2>' . __( 'Higher Query Tiers Available' ) . '</h2>' . "\n";
			echo '				<p>' . __( 'Need more queries? Paid plans offer higher API query limits (10,000+ per day), ideal for larger websites and organizations. These are available from the <a href="https://proxyvpnblocker.com/discounted-plans/" target="_blank">Proxy & VPN Blocker Website</a> for am exclusive discounted price for Proxy & VPN Blocker Plugin users.', 'proxy-vpn-blocker' ) . '</p>' . "\n";
			echo '			</div>' . "\n";
			echo '			<div class="pvbinfowrapblock">' . "\n";
			echo '         	 	<h2>' . __('Quick Setup Guide', 'proxy-vpn-blocker' ) . '</h2>' . "\n";
			echo '         		<ol>' . "\n";
			echo '          		<li>' . __( 'Visit <a href="https://proxycheck.io" target="_blank">proxycheck.io</a> to create an account.', 'proxy-vpn-blocker' ) . '</li>' . "\n";
			echo '          		<li>' . __( 'Get your Free API key from your proxycheck.io dashboard.', 'proxy-vpn-blocker' ) . '</li>' . "\n";
			echo '          		<li>' . __( 'Open the General tab below.', 'proxy-vpn-blocker' ) . '</li>' . "\n";
			echo '          		<li>' . __( 'Paste your API key in the designated field.', 'proxy-vpn-blocker' ) . '</li>' . "\n";
			echo '          		<li>' . __( 'Save changes to activate full functionality.', 'proxy-vpn-blocker' ) . '</li>' . "\n";
			echo '          		<li>' . __( 'Configure other plugin settings as required.', 'proxy-vpn-blocker' ) . '</li>' . "\n";
			echo '          	</ol>' . "\n";
			echo '				<p>' . __( 'Need help? Visit our <a href="https://proxyvpnblocker.com/installation-and-configuration-free/" target="_blank">Installation & Configuration Guide</a> or visit the <a href="https://wordpress.org/support/plugin/proxy-vpn-blocker/" target="_blank">WordPress Support Community</a>.', 'proxy-vpn-blocker' ) . '</p>' . "\n";
			echo '				<p>' . __( 'Need more features and customization? Check out what is available in <a href="https://proxyvpnblocker.com/premium/" target="_blank">Proxy & VPN Blocker Premium</a>', 'proxy-vpn-blocker' ) . '</p>' . "\n";
			echo '			</div>' . "\n";
			echo '		</div>' . "\n";
			echo '</div>' . "\n";
		}
		echo '<nav>' . "\n";
		echo '	<input type="checkbox" id="checkbox" />' . "\n";
		echo '	<label for="checkbox">' . "\n";
		echo '  	<ul class="menu first">' . "\n";
		echo '			<li><a href="https://proxyvpnblocker.com" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> proxyvpnblocker.com</a></li>' . "\n";
		echo '			<li><a href="https://wordpress.org/support/plugin/proxy-vpn-blocker/" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> Support & Issues</a></li>' . "\n";
		echo '			<li><a href="https://proxyvpnblocker.com/installation-and-configuration-free/" target="_blank"><i class="fa-solid fa-circle-question"></i> Configuration Guide</a></li>' . "\n";
		echo '			<li><a href="https://proxyvpnblocker.com/faq/" target="_blank"><i class="fa-solid fa-file-lines"></i> FAQ</a></li>' . "\n";
		echo '			<li id="premium"><a href="https://proxyvpnblocker.com/premium/" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> Explore Premium</a></li>' . "\n";
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
		include_once 'pvb-stats-page/proxycheckio-apikey-statistics.php';
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
		include_once 'pvb-action-logs/proxy-vpn-blocker-logs.php';
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
