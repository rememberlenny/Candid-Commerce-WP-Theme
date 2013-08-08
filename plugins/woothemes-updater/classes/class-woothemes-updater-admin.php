<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WooThemes Updater Admin Class
 *
 * Admin class for the WooThemes Updater.
 *
 * @package WordPress
 * @subpackage WooThemes Updater
 * @category Core
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * private $token
 * private $api
 * private $name
 * private $menu_label
 * private $page_slug
 * private $plugin_path
 * private $screens_path
 * private $classes_path
 *
 * private $installed_products
 * private $pending_products
 *
 * - __construct()
 * - register_settings_screen()
 * - settings_screen()
 * - get_activated_products()
 * - get_product_reference_list()
 * - get_detected_products()
 * - get_pending_products()
 * - activate_products()
 * - deactivate_product()
 * - load_updater_instances()
 */
class WooThemes_Updater_Admin {
	private $token;
	private $api;
	private $name;
	private $menu_label;
	private $page_slug;
	private $plugin_path;
	private $screens_path;
	private $classes_path;

	private $installed_products;
	private $pending_products;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @since    1.0.0
	 * @return    void
	 */
	public function __construct ( $file ) {
		$this->token = 'woothemes-updater';

		// Load the API.
		require_once( 'class-woothemes-updater-api.php' );
		$this->api = new WooThemes_Updater_API();

		$this->name = __( 'WooThemes Licenses', 'woothemes-updater' );
		$this->menu_label = __( 'WooThemes Licenses', 'woothemes-updater' );
		$this->page_slug = 'woothemes-licenses';
		$this->plugin_path = trailingslashit( plugin_dir_path( $file ) );
		$this->screens_path = trailingslashit( $this->plugin_path . 'screens' );
		$this->classes_path = trailingslashit( $this->plugin_path . 'classes' );

		$this->installed_products = array();
		$this->pending_products = array();

		// Load the updaters.
		add_action( 'admin_init', array( $this, 'load_updater_instances') ); //$this->load_updater_instances();

		$menu_hook = is_multisite() ? 'network_admin_menu' : 'admin_menu';
		add_action( $menu_hook, array( $this, 'register_settings_screen' ) );
	} // End __construct()

	/**
	 * Register the admin screen.
	 *
	 * @access public
	 * @since   1.0.0
	 * @return   void
	 */
	public function register_settings_screen () {
		$hook = add_dashboard_page( $this->name, $this->menu_label, 'manage_options', $this->page_slug, array( $this, 'settings_screen' ) );

		add_action( 'load-' . $hook, array( $this, 'process_request' ) );
	} // End register_settings_screen()

	/**
	 * Load the main management screen.
	 *
	 * @access public
	 * @since   1.0.0
	 * @return   void
	 */
	public function settings_screen () {
		$this->installed_products = $this->get_detected_products();
		$this->pending_products = $this->get_pending_products();

		require_once( $this->screens_path . 'screen-manage.php' );
	} // End settings_screen()

	/**
	 * Returns the action value to use.
	 * @access private
	 * @since 1.0.0
	 * @return string|bool Contains the string given in $_POST['action'] or $_GET['action'], or false if none provided
	 */
	private function get_post_or_get_action( $supported_actions ) {
		if ( isset( $_POST['action'] ) && in_array( $_POST['action'], $supported_actions ) )
			return $_POST['action'];

		if ( isset( $_GET['action'] ) && in_array( $_GET['action'], $supported_actions ) )
			return $_GET['action'];

		return false;
	}

	/**
	 * Process the action for the admin screen.
	 * @since  1.0.0
	 * @return  void
	 */
	public function process_request () {
		$notices_hook = is_multisite() ? 'network_admin_notices' : 'admin_notices';
		add_action( $notices_hook, array( $this, 'admin_notices' ) );

		$supported_actions = array( 'activate-products', 'deactivate-product' );

		$action = $this->get_post_or_get_action( $supported_actions );

		if ( $action && in_array( $action, $supported_actions ) && check_admin_referer( 'bulk-' . 'licenses' ) ) {
			$response = false;
			$status = 'false';
			$type = $action;

			switch ( $type ) {
				case 'activate-products':
					$license_keys = array();
					if ( isset( $_POST['license_keys'] ) && 0 < count( $_POST['license_keys'] ) ) {
						foreach ( $_POST['license_keys'] as $k => $v ) {
							if ( '' != $v ) {
								$license_keys[$k] = $v;
							}
						}
					}

					if ( 0 < count( $license_keys ) ) {
						$response = $this->activate_products( $license_keys );
					} else {
						$response = false;
						$type = 'no-license-keys';
					}
				break;

				case 'deactivate-product':
					if ( isset( $_GET['filepath'] ) && ( '' != $_GET['filepath'] ) ) {
						$response = $this->deactivate_product( $_GET['filepath'] );
					}
				break;

				default:
				break;
			}

			if ( $response == true ) {
				$status = 'true';
			}

			wp_safe_redirect( add_query_arg( 'type', urlencode( $type ), add_query_arg( 'status', urlencode( $status ), add_query_arg( 'page', urlencode( $this->page_slug ),  network_admin_url( 'index.php' ) ) ) ) );
			exit;
		}
	} // End process_request()

	/**
	 * Display admin notices.
	 * @since  1.0.0
	 * @return  void
	 */
	public function admin_notices () {
		$message = '';
		$response = '';

		if ( isset( $_GET['status'] ) && in_array( $_GET['status'], array( 'true', 'false' ) ) && isset( $_GET['type'] ) ) {
			$classes = array( 'true' => 'updated', 'false' => 'error' );

			$request_errors = $this->api->get_error_log();

			switch ( $_GET['type'] ) {
				case 'no-license-keys':
					$message = __( 'No license keys were specified for activation.', 'woothemes-updater' );
				break;

				case 'deactivate-product':
					if ( 'true' == $_GET['status'] && ( 0 >= count( $request_errors ) ) ) {
						$message = __( 'Product deactivated successfully.', 'woothemes-updater' );
					} else {
						$message = __( 'There was an error while deactivating the product.', 'woothemes-updater' );
					}
				break;

				default:

					if ( 'true' == $_GET['status'] && ( 0 >= count( $request_errors ) ) ) {
						$message = __( 'Products activated successfully.', 'woothemes-updater' );
					} else {
						$message = __( 'There was an error and not all products were activated.', 'woothemes-updater' );
					}
				break;
			}

			$response = '<div class="' . esc_attr( $classes[$_GET['status']] ) . ' fade">' . "\n";
			$response .= wpautop( $message );
			$response .= '</div>' . "\n";

			// Cater for API request error logs.
			if ( is_array( $request_errors ) && ( 0 < count( $request_errors ) ) ) {
				$message = '';

				foreach ( $request_errors as $k => $v ) {
					$message .= wpautop( $v );
				}

				$response .= '<div class="error fade">' . "\n";
				$response .= $message;
				$response .= '</div>' . "\n";

				// Clear the error log.
				$this->api->clear_error_log();
			}

			if ( '' != $response ) {
				echo $response;
			}
		}
	} // End admin_notices()

	/**
	 * Detect which products have been activated.
	 *
	 * @access public
	 * @since   1.0.0
	 * @return   void
	 */
	protected function get_activated_products () {
		$response = array();

		$response = get_option( $this->token . '-activated', array() );

		if ( ! is_array( $response ) ) $response = array();

		return $response;
	} // End get_activated_products()

	/**
	 * Get a list of products from WooThemes.
	 *
	 * @access public
	 * @since   1.0.0
	 * @return   void
	 */
	protected function get_product_reference_list () {
		global $woothemes_updater;
		$response = array();
		$response = $woothemes_updater->get_products();
		return $response;
	} // End get_product_reference_list()

	/**
	 * Get a list of WooThemes products found on this installation.
	 *
	 * @access public
	 * @since   1.0.0
	 * @return   void
	 */
	protected function get_detected_products () {
		$response = array();
		$products = get_plugins();

		if ( is_array( $products ) && ( 0 < count( $products ) ) ) {
			$reference_list = $this->get_product_reference_list();
			$activated_products = $this->get_activated_products();
			if ( is_array( $reference_list ) && ( 0 < count( $reference_list ) ) ) {
				foreach ( $products as $k => $v ) {
					if ( in_array( $k, array_keys( $reference_list ) ) ) {
						$status = 'inactive';
						if ( in_array( $k, array_keys( $activated_products ) ) ) { $status = 'active'; }
						$response[$k] = array( 'product_name' => $v['Name'], 'product_version' => $v['Version'], 'file_id' => $reference_list[$k]['file_id'], 'product_id' => $reference_list[$k]['product_id'], 'product_status' => $status, 'product_file_path' => $k );
					}
				}
			}
		}

		return $response;
	} // End get_detected_products()

	/**
	 * Get an array of products that haven't yet been activated.
	 *
	 * @access public
	 * @since   1.0.0
	 * @return  array Products awaiting activation.
	 */
	protected function get_pending_products () {
		$response = array();

		$products = $this->installed_products;

		if ( is_array( $products ) && ( 0 < count( $products ) ) ) {
			$activated_products = $this->get_activated_products();

			if ( is_array( $activated_products ) && ( 0 <= count( $activated_products ) ) ) {
				foreach ( $products as $k => $v ) {
					if ( isset( $v['product_key']) && ! in_array( $v['product_key'], $activated_products ) ) {
						$response[$k] = array( 'product_name' => $v['product_name'] );
					}
				}
			}
		}

		return $response;
	} // End get_pending_products()

	/**
	 * Activate a given array of products.
	 *
	 * @since    1.0.0
	 * @param    array   $products  Array of products ( filepath => key )
	 * @return boolean
	 */
	protected function activate_products ( $products ) {
		$response = false;
		if ( ! is_array( $products ) || ( 0 >= count( $products ) ) ) { return false; } // Get out if we have incorrect data.

		$key = $this->token . '-activated';
		$has_update = false;
		$already_active = $this->get_activated_products();
		$product_keys = $this->get_product_reference_list();

		foreach ( $products as $k => $v ) {
			if ( ! in_array( $v, $product_keys ) ) {
				// Perform API "activation" request.
				$activate = $this->api->activate( $products[$k], $product_keys[$k]['product_id'], $k );

				if ( true == $activate ) {
					// key: base file, 0: product id, 1: file_id, 2: hashed license.
					$already_active[$k] = array( $product_keys[$k]['product_id'], $product_keys[$k]['file_id'], md5( $products[$k] ) );
					$has_update = true;
				}
			}
		}

		// Store the error log.
		$this->api->store_error_log();

		if ( $has_update ) {
			$response = update_option( $key, $already_active );
		} else {
			$response = true; // We got through successfully, and the supplied keys are already active.
		}

		return $response;
	} // End activate_products()

	/**
	 * Deactivate a given product key.
	 * @since    1.0.0
	 * @param   string $filename File name of the to deactivate plugin licence
	 * @return boolean      Whether or not the deactivation was successful.
	 */
	protected function deactivate_product ( $filename ) {
		$response = false;
		$already_active = $this->get_activated_products();

		if ( 0 < count( $already_active ) ) {
			$deactivated = true;

			if ( isset( $already_active[ $filename ][0] ) ) {
				$key = $already_active[ $filename ][2];

				$deactivated = $this->api->deactivate( $key );
			}

			if ( $deactivated ) {
				unset( $already_active[ $filename ] );
				$response = update_option( $this->token . '-activated', $already_active );
			} else {
				$this->api->store_error_log();
			}
		}

		return $response;
	} // End deactivate_product()

	/**
	 * Load an instance of the updater class for each activated WooThemes Product.
	 * @since  1.0.0
	 * @return void
	 */
	public function load_updater_instances () {
		$products = $this->get_detected_products();
		$activated_products = $this->get_activated_products();
		if ( 0 < count( $products ) ) {
			require_once( 'class-woothemes-updater-update-checker.php' );
			foreach ( $products as $k => $v ) {
				if ( isset( $v['product_id'] ) && isset( $v['file_id'] ) ) {

					$license_hash = isset( $activated_products[ $k ][2] ) ? $activated_products[ $k ][2] : '';
					new WooThemes_Updater_Update_Checker( $k, $v['product_id'], $v['file_id'], $license_hash );

				}
			}
		}
	} // End load_updater_instances()
} // End Class
?>