<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WooThemes Updater Class
 *
 * Base class for the WooThemes Updater.
 *
 * @package WordPress
 * @subpackage WooThemes Updater
 * @category Core
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * public $updater
 * public $admin
 * private $token
 * public $plugin_url
 * public $plugin_path
 * public $version
 * private $file
 *
 * - __construct()
 * - load_localisation()
 * - load_plugin_textdomain()
 * - activation()
 * - register_plugin_version()
 * - add_product()
 * - remove_product()
 * - get_product()
 */
class WooThemes_Updater {
	public $updater;
	public $admin;
	private $token = 'woothemes-updater';
	private $plugin_url;
	private $plugin_path;
	public $version;
	private $file;
	private $products;

	/**
	 * Constructor.
	 * @param string $file The base file of the plugin.
	 * @since  1.0.0
	 * @return  void
	 */
	public function __construct ( $file ) {

		// If multisite, plugin must be network activated. First make sure the is_plugin_active_for_network function exists
		if( is_multisite() && ! is_network_admin() ) {
			remove_action( 'admin_notices', 'woothemes_updater_notice' ); // remove admin notices for plugins outside of network admin
			if ( !function_exists( 'is_plugin_active_for_network' ) )
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			if( !is_plugin_active_for_network( plugin_basename( $file ) ) )
				add_action( 'admin_notices', array( $this, 'admin_notice_require_network_activation' ) );
			return;
		}

		$this->file = $file;
		$this->plugin_url = trailingslashit( plugins_url( '', $plugin = $file ) );
		$this->plugin_path = trailingslashit( dirname( $file ) );

		$this->products = array();

		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );

		// Run this on activation.
		register_activation_hook( $this->file, array( $this, 'activation' ) );

		if ( is_admin() ) {
			// Load the self-updater.
			require_once( 'class-woothemes-updater-self-updater.php' );
			$this->updater = new WooThemes_Updater_Self_Updater( $file );

			// Load the admin.
			require_once( 'class-woothemes-updater-admin.php' );
			$this->admin = new WooThemes_Updater_Admin( $file );

			// Get queued plugin updates
			add_action( 'plugins_loaded', array( $this, 'load_queued_updates' ) );
		}

		$this->add_notice_unlicensed_product();

		add_filter( 'site_transient_' . 'update_plugins', array( $this, 'change_update_information' ) );

	} // End __construct()

	/**
	 * Load the plugin's localisation file.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'woothemes-updater', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation()

	/**
	 * Load the plugin textdomain from the main WordPress "languages" folder.
	 * @since  1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'woothemes-updater';
	    // The "plugin_locale" filter is also used in load_plugin_textdomain()
	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain()

	/**
	 * Run on activation.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function activation () {
		$this->register_plugin_version();
	} // End activation()

	/**
	 * Register the plugin's version.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	private function register_plugin_version () {
		if ( $this->version != '' ) {
			update_option( 'woothemes-updater' . '-version', $this->version );
		}
	} // End register_plugin_version()

	/**
	 * load_queued_updates function.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_queued_updates() {
		global $woothemes_queued_updates;

		if ( ! empty( $woothemes_queued_updates ) && is_array( $woothemes_queued_updates ) )
			foreach ( $woothemes_queued_updates as $plugin )
				if ( is_object( $plugin ) && ! empty( $plugin->file ) && ! empty( $plugin->file_id ) && ! empty( $plugin->product_id ) )
					$this->add_product( $plugin->file, $plugin->file_id, $plugin->product_id );

	} // END load_queued_updates()

	/**
	 * Add a product to await a license key for activation.
	 *
	 * Add a product into the array, to be processed with the other products.
	 *
	 * @since  1.0.0
	 * @param string $file The base file of the product to be activated.
	 * @param string $file_id The unique file ID of the product to be activated.
	 * @return  void
	 */
	public function add_product ( $file, $file_id, $product_id ) {
		if ( $file != '' && ! isset( $this->products[$file] ) ) { $this->products[$file] = array( 'file_id' => $file_id, 'product_id' => $product_id ); }
	} // End add_product()

	/**
	 * Remove a product from the available array of products.
	 *
	 * @since     1.0.0
	 * @param     string $key The key to be removed.
	 * @return    boolean
	 */
	public function remove_product ( $file ) {
		$response = false;
		if ( $file != '' && in_array( $file, array_keys( $this->products ) ) ) { unset( $this->products[$file] ); $response = true; }
		return $response;
	} // End remove_product()

	/**
	 * Return an array of the available product keys.
	 * @since  1.0.0
	 * @return array Product keys.
	 */
	public function get_products () {
		return (array) $this->products;
	} // End get_products()

	/**
	 * Display require network activation error.
	 * @since  1.0.0
	 * @return  void
	 */
	public function admin_notice_require_network_activation () {
		echo '<div class="error"><p>' . __( 'WooThemes Updater must be network activated when in multisite environment.', 'woothemes-updater' ) . '</p></div>';
	} // End admin_notice_require_network_activation()

	/**
	 * Add action for queued products to display message for unlicensed products
	 */
	public function add_notice_unlicensed_product () {
		global $woothemes_queued_updates;
		if( !is_array( $woothemes_queued_updates ) || count( $woothemes_queued_updates ) < 0 ) return;

		foreach ( $woothemes_queued_updates as $key => $update ) {
			add_action( 'in_plugin_update_message-' . $update->file, array( $this, 'need_license_message'), 10, 2 );
		}
	} // End add_notice_unlicensed_product()

	/**
	 * Message displayed if license not activated
	 * @param  array $plugin_data
	 * @param  object $r
	 * @return void
	 */
	public function need_license_message ( $plugin_data, $r ) {
		if( empty( $r->package ) ) {
			_e( ' To enable updates for this WooThemes product, please activate your license.', 'woothemes-updater' );
		}
	} // End need_license_message()

	/**
	 * Change the update information for unlicense WooThemes products
	 * @param  object $transient The update-plugins transient
	 * @return object
	 */
	public function change_update_information ( $transient ) {
		//If we are on the update core page, change the update message for unlicensed products
		global $pagenow;
		if ( ( 'update-core.php' == $pagenow ) && $transient && isset( $transient->response ) && ! isset( $_GET['action'] ) ) {

			global $woothemes_queued_updates;

			if( empty( $woothemes_queued_updates ) ) return $transient;

			foreach ( $woothemes_queued_updates as $key => $value ) {
				if( isset( $transient->response[ $value->file ] ) && isset( $transient->response[ $value->file ]->package ) && '' == $transient->response[ $value->file ]->package && ( FALSE === stristr($transient->response[ $value->file ]->upgrade_notice, 'To enable this update please activate your WooThemes license. ') ) ){
					$message = __( 'To enable this update please activate your WooThemes license. ' . $transient->response[ $value->file ]->upgrade_notice , 'woothemes-updater' );//@WARREN - Fix this message
					$transient->response[ $value->file ]->upgrade_notice = $message;
				}
			}

		}

		return $transient;
	} // End change_update_information()

} // End Class
?>