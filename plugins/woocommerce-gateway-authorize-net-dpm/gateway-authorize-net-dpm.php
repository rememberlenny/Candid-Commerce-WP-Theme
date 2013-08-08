<?php
/*
Plugin Name: WooCommerce Authorize.net DPM Gateway
Plugin URI: http://woothemes.com/woocommerce
Description: Extends WooCommerce with an <a href="https://www.authorize.net" target="_blank">Authorize.net</a> DPM (Direct Post Method) gateway. An Authorize.net merchant account is required. Compatible with WC 1.4.5 +
Version: 1.4.3
Author: WooThemes
Author URI: http://woothemes.com/

	Copyright: © 2009-2011 WooThemes.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html

	DPM Docs: http://developer.authorize.net/guides/DPM/
*/

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) )
	require_once( 'woo-includes/woo-functions.php' );

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '9a55f12d866225c031a800cb21b22c78', '18654' );

add_action('plugins_loaded', 'woocommerce_authorize_dpm_init', 0);

function woocommerce_authorize_dpm_init() {

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;

    /**
     * Localisation
     */
    load_plugin_textdomain('wc-authorize-dpm', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');

	/**
 	 * Gateway class
 	 */
	class WC_Gateway_Authorize_DPM extends WC_Payment_Gateway {

		function __construct() {
			global $woocommerce;

			$this->id					= 'authorize_net_dpm';
			$this->method_title 		= __('Authorize.net DPM', 'wc-authorize-dpm');
			$this->method_description 	= __('Authorize.net DPM (direct post method) handles all the steps in the secure transaction while remaining virtually transparent. Payment data is passed from the checkout to Authorize.net for processing thus removing the necessity of SSL and simplifying PCI compliance.', 'wc-authorize-dpm');
			$this->icon 				= WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)) . '/images/cards.png';

			// Load the form fields
			$this->init_form_fields();

			// Load the settings.
			$this->init_settings();

			// Get setting values
			$this->title 			= $this->settings['title'];
			$this->description 		= $this->settings['description'];
			$this->enabled 			= $this->settings['enabled'];
			$this->api_login 		= $this->settings['api_login'];
			$this->transaction_key 	= $this->settings['transaction_key'];
			$this->md5_setting		= ! empty( $this->settings['md5_setting'] ) ? $this->settings['md5_setting'] : '';
			$this->testmode 		= isset( $this->settings['testmode'] ) && $this->settings['testmode'] == 'no' ? false : true;

			// Payment form hook
			add_action( 'woocommerce_receipt_authorize_net_dpm', array( $this, 'receipt_page' ) );

			// Payment listener/API hook
			if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '<' ) ) {

				$this->relay_response_url = home_url( '/' );

				if ( ! empty( $_REQUEST['x_order_id'] ) ) {

					add_action( 'wp', array( $this, 'relay_response' ) );

					if ( did_action( 'wp' ) )
						$this->relay_response();
				}

			} else {

				$this->relay_response_url = $woocommerce->api_request_url( get_class() );

				add_action( 'woocommerce_api_wc_gateway_authorize_dpm', array( $this, 'relay_response' ) );

			}

			/* 1.6.6 */
			add_action( 'woocommerce_update_options_payment_gateways', array( $this, 'process_admin_options' ) );

			/* 2.0.0 */
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		/**
	     * Initialise Gateway Settings Form Fields
	     */
	    function init_form_fields() {

	    	$this->form_fields = array(
				'enabled' => array(
								'title' => __( 'Enable/Disable', 'wc-authorize-dpm' ),
								'label' => __( 'Enable Authorize.net DPM', 'wc-authorize-dpm' ),
								'type' => 'checkbox',
								'description' => '',
								'default' => 'no'
							),
				'title' => array(
								'title' => __( 'Title', 'wc-authorize-dpm' ),
								'type' => 'text',
								'description' => __( 'This controls the title which the user sees during checkout.', 'wc-authorize-dpm' ),
								'default' => __( 'Credit card', 'wc-authorize-dpm' )
							),
				'description' => array(
								'title' => __( 'Description', 'wc-authorize-dpm' ),
								'type' => 'textarea',
								'description' => __( 'This controls the description which the user sees during checkout.', 'wc-authorize-dpm' ),
								'default' => 'Pay securely using your credit card.'
							),
				'api_login' => array(
								'title' => __( 'API Login ID', 'wc-authorize-dpm' ),
								'type' => 'text',
								'description' => __( 'Look this up by logging into your Authorize.net account.', 'wc-authorize-dpm' ),
								'default' => ''
							),
				'transaction_key' => array(
								'title' => __( 'Transaction key', 'wc-authorize-dpm' ),
								'type' => 'password',
								'description' => __( 'Look this up by logging into your Authorize.net account.', 'wc-authorize-dpm' ),
								'default' => ''
							),
				'md5_setting' => array(
								'title' => __( 'MD5 Hash', 'wc-authorize-dpm' ),
								'type' => 'password',
								'description' => __( 'Unless you have explicitly set MD5-Hash in the merchant interface (using Account > Settings > Security Settings > MD5-Hash), leave this as an empty string.', 'wc-authorize-dpm' ),
								'default' => ''
							),
				'testmode' => array(
								'title' => __( 'Test Mode', 'wc-authorize-dpm' ),
								'label' => __( 'Enable Test Mode', 'wc-authorize-dpm' ),
								'type' => 'checkbox',
								'description' => __( 'Place the payment gateway in test mode to work with developer/sandbox accounts.', 'wc-authorize-dpm' ),
								'default' => 'no'
							),
				);
	    }

		/**
	     * Check if this gateway is enabled
	     */
		function is_available() {

			if (!$this->api_login || !$this->transaction_key) return false;

			return parent::is_available();
		}

		/**
		 * Process the payment and return the result - this will redirect the customer to the pay page
		 */
		function process_payment( $order_id ) {

			$order = new WC_Order( $order_id );

			return array(
				'result' 	=> 'success',
				'redirect'	=> add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay'))))
			);
		}

		/**
		 * Receipt_page for showing the payment form which sends data to authorize.net
		 */
		function receipt_page( $order_id ) {
			global $woocommerce;

			// Include the SDK
			if ( ! class_exists( 'AuthorizeNetSIM' ) )
				include_once( dirname(__FILE__) . '/anet_php_sdk/AuthorizeNet.php');

			// Include our class which overrides the form
			require_once 'class-wc-authorize-net-dpm.php';

			// Get the order
			$order = new WC_Order( $order_id );

			// Get the amount
			$amount = $order->get_order_total();

			echo wpautop(__('Enter your payment details below and click "Confirm and pay" to securely pay for your order.', 'wc-authorize-dpm'));

			// Show the payment form
			echo WC_Authorize_Net_DPM::getCreditCardForm( $amount, $order_id, $this->relay_response_url, $this->api_login, $this->transaction_key, $this->testmode, $this->testmode, $order );
		}

		/**
		 * Relay response - handles return data from Authorize.net and does redirects
		 */
		function relay_response() {
			global $woocommerce;

			// Clean
			@ob_clean();

			// Header
			header('HTTP/1.1 200 OK');

			// Include the SDK
			if ( ! class_exists( 'AuthorizeNetSIM' ) )
				include_once( dirname(__FILE__) . '/anet_php_sdk/AuthorizeNet.php');

			// Process response
			$response = new AuthorizeNetSIM( $this->api_login, $this->md5_setting );

			if ( $response->isAuthorizeNet() ) {

				// Get the order
				$order = new WC_Order( (int) $response->response['x_order_id'] );

				$redirect_url = $this->get_return_url( $order );

				if ( $response->approved ) {

					if ( $order->key_is_valid( $response->response['x_order_key'] )) {

						// Payment complete
						$order->payment_complete();

						// Redirect URL
						$redirect_url = add_query_arg( 'response_code', 1, $redirect_url );
						$redirect_url = add_query_arg( 'transaction_id', $response->transaction_id, $redirect_url );

						// Remove cart
						$woocommerce->cart->empty_cart();

					} else {

						// Key did not match order id
						$order->add_order_note( sprintf(__('Payment received, but order ID did not match key: code %s - %s.', 'wc-authorize-dpm'), $response->response_code, $response->response_reason_text ) );

						// Put on hold if pending
						if ($order->status == 'pending' || $order->status == 'failed') {
							$order->update_status( 'on-hold' );
						}

					}

				} else {

					if ( $response->response_code == 4 ) {

						// Mark as on-hold (we're awaiting the payment)
						$order->update_status('on-hold', __( 'Authorize.net payment on-hold.', 'woocommerce' ));

						// Reduce stock levels
						$order->reduce_order_stock();

						// Redirect URL
						$redirect_url = add_query_arg( 'response_code', 4, $redirect_url );
						$redirect_url = add_query_arg( 'transaction_id', $response->transaction_id, $redirect_url );

						// Remove cart
						$woocommerce->cart->empty_cart();

					} else {

						// Mark failed
						$order->update_status( 'failed', sprintf(__('Payment failure: code %s - %s (%s).', 'wc-authorize-dpm'), $response->response_code, $response->response_reason_text, $response->response_reason_code ) );

						$redirect_url = add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay'))));

						$redirect_url = add_query_arg( 'wc_error', __('Error', 'wc-authorize-dpm') . ' ' . $response->response_code . ': ' . $response->response_reason_text . '(' . $response->response_reason_code . ')', $redirect_url );

						if (is_ssl() || get_option('woocommerce_force_ssl_checkout')=='yes') $redirect_url = str_replace('http:', 'https:', $redirect_url);

					}

				}

				echo AuthorizeNetDPM::getRelayResponseSnippet($redirect_url);

				//wp_redirect( $redirect_url );
				//exit;

			} else {

				echo "Error -- not AuthorizeNet. Check your MD5 Setting.";

			}

			exit;
		}

	} // end WC_Gateway_Authorize_DPM

	/**
 	* Add the Gateway to WooCommerce
 	**/
	function woocommerce_add_authorize_dpm_gateway($methods) {
		$methods[] = 'WC_Gateway_Authorize_DPM';
		return $methods;
	}

	add_filter('woocommerce_payment_gateways', 'woocommerce_add_authorize_dpm_gateway' );
}