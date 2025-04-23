<?php
/**
 * Main plugin class file.
 *
 * @package Proxy & VPN Blocker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Proxy & VPN Blocker Main Class.
 */
class Proxy_VPN_Blocker {

	/**
	 * The single instance of Proxy_VPN_Blocker.
	 *
	 * @var     object
	 * @access  private
	 * @since   1.0
	 */
	private static $_instance = null; //phpcs:ignore

	/**
	 * Settings class object
	 *
	 * @var     object
	 * @access  public
	 * @since   1.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0
	 */
	public $_version; //phpcs:ignore

	/**
	 * The token.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0
	 */
	public $_token; //phpcs:ignore

	/**
	 * The main plugin file.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0
	 */
	public $script_suffix;

	/**
	 * The Plugin Admin.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0
	 */
	public $admin;

	/**
	 * Constructor function.
	 *
	 * @param string $file File constructor.
	 * @param string $version Plugin version.
	 *
	 * @access  public
	 * @since   1.0
	 * @return  void
	 */
	public function __construct( $file = '', $version = '3.2.4' ) {
		$this->_version = $version;
		$this->_token   = 'proxy_vpn_blocker';

		// Load plugin environment variables.
		$this->file       = $file;
		$this->dir        = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load admin JS & CSS.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'pvb_scripts_footer_function' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'pvb_scripts_header_function' ), 10, 1 );

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_pvb_block_editor' ), 10, 1 );

		// Load API for generic admin functions.
		if ( is_admin() ) {
			$this->admin = new proxy_vpn_blocker_Admin_API();
		}

		// Handle localisation.
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );

		// Handle updates.
		add_action( 'upgrader_process_complete', array( $this, 'install' ), 10, 2 );

		add_action( 'init', array( $this, 'pvb_register_post_meta' ), 10, 1 );
	} // End __construct ()

	/**
	 * Block Editor Integration.
	 */
	public function enqueue_pvb_block_editor() {
		if ( ! in_array( get_current_screen()->id, array( 'widgets' ) ) ) {
			wp_register_script( $this->_token . 'pvb-block-editor-script', esc_url( $this->assets_url ) . 'js/pvb-block-editor-script.js', array( 'wp-edit-post' ), $this->_version, true );
			wp_enqueue_script( $this->_token . 'pvb-block-editor-script' );
		}
	}

	/**
	 * Register meta items for posts and pages.
	 */
	public function pvb_register_post_meta() {
		register_post_meta(
			'post',
			'_pvb_checkbox_block_on_post',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'boolean',
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
		register_post_meta(
			'page',
			'_pvb_checkbox_block_on_post',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'boolean',
				'auth_callback' => function () {
					return current_user_can( 'edit_pages' );
				},
			)
		);
	}


	/**
	 * Admin enqueue style.
	 *
	 * @param string $hook Hook parameter.
	 *
	 * @return void
	 */
	public function admin_enqueue_styles( $hook = '' ) {
		$screen = get_current_screen();
		if ( stripos( $screen->base, 'proxy_vpn_blocker_' ) ) {
			wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.min.css', array(), $this->_version );
			wp_enqueue_style( $this->_token . '-admin' );
			wp_register_style( $this->_token . '-select2', esc_url( $this->assets_url ) . 'css/select2/select2pvb.min.css', array(), $this->_version );
			wp_enqueue_style( $this->_token . '-select2' );
		}
		wp_register_style( $this->_token . '-wpsettings-pvb-tooltips', esc_url( $this->assets_url ) . 'css/ui-extensions.min.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-wpsettings-pvb-tooltips' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin header Javascript.
	 *
	 * @access  public
	 *
	 * @param string $hook Hook parameter.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	public function pvb_scripts_header_function( $hook = '' ) {
		$screen = get_current_screen();
		if ( stripos( $screen->base, 'proxy_vpn_blocker_statistics' ) ) {
			wp_register_script( $this->_token . '-settings-pvb-am4core-js', esc_url( $this->assets_url ) . 'js/amcharts/core.js', array( 'jquery' ), $this->_version, false );
			wp_enqueue_script( $this->_token . '-settings-pvb-am4core-js' );
			wp_register_script( $this->_token . '-settings-pvb-am4charts-js', esc_url( $this->assets_url ) . 'js/amcharts/charts.js', array( 'jquery' ), $this->_version, false );
			wp_enqueue_script( $this->_token . '-settings-pvb-am4charts-js' );
			wp_register_script( $this->_token . '-settings-pvb-am4charts-animated-js', esc_url( $this->assets_url ) . 'js/amcharts/theme/animated.js', array( 'jquery' ), $this->_version, false );
			wp_enqueue_script( $this->_token . '-settings-pvb-am4charts-animated-js' );
			wp_register_script( $this->_token . '-settings-pvb-am4charts-dark-js', esc_url( $this->assets_url ) . 'js/amcharts/theme/dark.js', array( 'jquery' ), $this->_version, false );
			wp_enqueue_script( $this->_token . '-settings-pvb-am4charts-dark-js' );
		}
		wp_register_script( $this->_token . '-wpsettings-pvb-tooltips', esc_url( $this->assets_url ) . 'js/ui-extensions.min.js', array( 'jquery' ), $this->_version, false );
		wp_enqueue_script( $this->_token . '-wpsettings-pvb-tooltips' );
	}//end pvb_scripts_header_function()


	/**
	 * Load admin Footer Javascript.
	 *
	 * @access  public
	 *
	 * @param string $hook Hook parameter.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	public function pvb_scripts_footer_function( $hook = '' ) {
		$screen = get_current_screen();
		if ( stripos( $screen->base, 'proxy_vpn_blocker_' ) ) {
			wp_enqueue_script( 'jquery-ui-core' );// enqueue jQuery UI Core.
			wp_enqueue_script( 'jquery-ui-tabs' );// enqueue jQuery UI Tabs.
			wp_register_script( $this->_token . '-settings-pvb-js', esc_url( $this->assets_url ) . 'js/settings' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version, true );
			wp_enqueue_script( $this->_token . '-settings-pvb-js' );
			wp_register_script( $this->_token . '-settings-pvb-select2-js', esc_url( $this->assets_url ) . 'js/select2/select2pvb' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version, true );
			wp_enqueue_script( $this->_token . '-settings-pvb-select2-js' );
			wp_register_script( $this->_token . '-settings-pvb-cookie-js', esc_url( $this->assets_url ) . 'js/cookie' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version, true );
			wp_enqueue_script( $this->_token . '-settings-pvb-cookie-js' );
			wp_register_script( $this->_token . '-settings-pvb-action-logs', esc_url( $this->assets_url ) . 'js/log-ajax' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version, true );
			wp_enqueue_script( $this->_token . '-settings-pvb-action-logs' );
			wp_localize_script(
				$this->_token . '-settings-pvb-action-logs',
				'pvb_action_logs',
				array(
					'nonce'           => wp_create_nonce( 'pvb_action_logs_ajax_nonce' ),
					'ajax_url'        => admin_url( 'admin-ajax.php' ),
					'flags_url'       => esc_url( $this->assets_url ) . '/img/country_flags/',
					'whitelist_nonce' => wp_create_nonce( 'add-ip-whitelist' ),
				)
			);
		}
	}//end pvb_scripts_footer_function()
	/**
	 * Load plugin localisation
	 *
	 * @access  public
	 * @since   1.0
	 * @return  void
	 */
	public function load_localisation() {
		load_plugin_textdomain( 'proxy-vpn-blocker', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 *
	 * @access  public
	 * @since   1.0
	 * @return  void
	 */
	public function load_plugin_textdomain() {
		$domain = 'proxy-vpn-blocker';

		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} //End load_Plugin_Textdomain ()

	/**
	 * Main proxy_vpn_blocker Instance
	 *
	 * Ensures only one instance of proxy_vpn_blocker is loaded or can be loaded.
	 *
	 * @param string $file File instance.
	 * @param string $version Version parameter.
	 *
	 * @since 1.0
	 * @static
	 * @see proxy_vpn_blocker()
	 * @return Main proxy_vpn_blocker instance
	 */
	public static function instance( $file = '', $version = '3.2.4' ) {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

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

	/**
	 * Installation. Runs on activation.
	 *
	 * @access  public
	 * @since   1.0
	 * @return  void
	 */
	public function install() {
		$this->log_version_number();
	} //End install()

	/**
	 * Log the plugin version number.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function log_version_number() {
		update_option( $this->_token . '_version', $this->_version );
	} //End _log_version_number()
}
