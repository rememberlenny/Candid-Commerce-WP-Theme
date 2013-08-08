<?php
/*
Plugin Name: WooCommerce PayPal Pro (Classic and PayFlow Editions) Gateway
Plugin URI: http://woothemes.com/woocommerce
Description: A payment gateway for PayPal Pro classic and PayFlow edition. A PayPal Pro merchant account, Curl support, and a server with SSL support and an SSL certificate is required (for security reasons) for this gateway to function.
Version: 3.0.3
Author: WooThemes
Author URI: http://woothemes.com/

	Copyright: © 2009-2011 WooThemes.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html

	PayPal Pro Docs: https://cms.paypal.com/cms_content/US/en_US/files/developer/PP_WPP_IntegrationGuide.pdf
*/

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) )
	require_once( 'woo-includes/woo-functions.php' );

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '6d23ba7f0e0198937c0029f9e865b40e', '18594' );

/**
 * Localisation
 **/
load_plugin_textdomain( 'wc_paypal_pro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

/**
 * wc_gateway_paypal_pro_ssl_check function.
 *
 * @access public
 * @return void
 */
function wc_gateway_paypal_pro_ssl_check() {

 	// Show message if enabled and FORCE SSL is disabled and WordpressHTTPS plugin is not detected
	if ( get_option('woocommerce_force_ssl_checkout')=='no' && ! class_exists( 'WordPressHTTPS' ) )
		echo '<div class="error"><p>' . sprintf( __('PayPal Pro requires that the <a href="%s">Force secure checkout</a> option is enabled; your checkout may not be secure! Please enable SSL and ensure your server has a valid SSL certificate - PayPal Pro will only work in test mode.', 'wc_paypal_pro'), admin_url('admin.php?page=woocommerce' ) ) . '</p></div>';
}

add_action( 'admin_notices', 'wc_gateway_paypal_pro_ssl_check' );

/**
 * woocommerce_gateway_paypal_pro_init function.
 *
 * @access public
 * @return void
 */
function woocommerce_gateway_paypal_pro_init() {

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;

	include( 'classes/class-wc-gateway-paypal-pro.php' );
	include( 'classes/class-wc-gateway-paypal-pro-payflow.php' );

	/**
 	* Add the Gateway to WooCommerce
 	**/
	function add_paypal_pro_gateway($methods) {
		$methods[] = 'WC_Gateway_PayPal_Pro';
		$methods[] = 'WC_Gateway_PayPal_Pro_Payflow';
		return $methods;
	}

	add_filter( 'woocommerce_payment_gateways', 'add_paypal_pro_gateway' );
}

add_action( 'plugins_loaded', 'woocommerce_gateway_paypal_pro_init', 0 );
