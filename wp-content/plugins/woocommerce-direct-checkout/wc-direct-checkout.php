<?php
/*
Plugin Name: WooCommerce Direct Checkout
Plugin URI: http://terrytsang.com/shop/shop/woocommerce-direct-checkout/
Description: Allow you to implement direct checkout (skip cart page) for WooCommerce
Version: 1.0.0
Author: Terry Tsang
Author URI: http://shop.terrytsang.com
*/

/*  Copyright 2012-2013 Terry Tsang (email: terrytsang811@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Define plugin name
define('wc_plugin_name_direct_checkout', 'WooCommerce Direct Checkout');

// Define plugin version
define('wc_version_direct_checkout', '1.0.0');


// Checks if the WooCommerce plugins is installed and active.
if(in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))){
	if(!class_exists('WooCommerce_Direct_Checkout')){
		class WooCommerce_Direct_Checkout{

			public static $plugin_prefix;
			public static $plugin_url;
			public static $plugin_path;
			public static $plugin_basefile;

			/**
			 * Gets things started by adding an action to initialize this plugin once
			 * WooCommerce is known to be active and initialized
			 */
			public function __construct(){
				load_plugin_textdomain('wc-direct-checkout', false, dirname(plugin_basename(__FILE__)) . '/languages/');
				
				WooCommerce_Direct_Checkout::$plugin_prefix = 'wc_direct_checkout_';
				WooCommerce_Direct_Checkout::$plugin_basefile = plugin_basename(__FILE__);
				WooCommerce_Direct_Checkout::$plugin_url = plugin_dir_url(WooCommerce_Direct_Checkout::$plugin_basefile);
				WooCommerce_Direct_Checkout::$plugin_path = trailingslashit(dirname(__FILE__));
				
				$this->textdomain = 'wc-direct-checkout';
				
				$this->options_direct_checkout = array(
					'direct_checkout_enabled' => '',
					'direct_checkout_cart_button_text' => ''
				);
	
				$this->saved_options_direct_checkout = array();
				
				add_action('woocommerce_init', array(&$this, 'init'));
			}

			/**
			 * Initialize extension when WooCommerce is active
			 */
			public function init(){
				
				//add menu link for the plugin (backend)
				add_action( 'admin_menu', array( &$this, 'add_menu_direct_checkout' ) );
				
				if(get_option('direct_checkout_enabled'))
				{
					//unset all related options to disabled / not active
					update_option('woocommerce_cart_redirect_after_add', 'no');
					update_option('woocommerce_enable_ajax_add_to_cart', 'no');
					
					add_filter('add_to_cart_redirect', array( &$this, 'custom_add_to_cart_redirect') );
					add_filter('single_add_to_cart_text', array( &$this, 'custom_cart_button_text') );
					add_filter('add_to_cart_text', array( &$this, 'custom_cart_button_text') );
				}
			}
			
			/**
			 * Set custom add to cart redirect
			 */
			function custom_add_to_cart_redirect() {
				
				return get_permalink(get_option('woocommerce_checkout_page_id')); // Replace with the url of your choosing
			}
			
			/**
			 * Set custom add to cart text
			 */
			function custom_cart_button_text() {
				$direct_checkout_cart_button_text = get_option( 'direct_checkout_cart_button_text' ) ? get_option( 'direct_checkout_cart_button_text' )  : "Add to cart";
				
				if($direct_checkout_cart_button_text && $direct_checkout_cart_button_text != "")
					return __($direct_checkout_cart_button_text, $this->textdomain);
			
			}
			
			/**
			 * Add a menu link to the woocommerce section menu
			 */
			function add_menu_direct_checkout() {
				$wc_page = 'woocommerce';
				$comparable_settings_page = add_submenu_page( $wc_page , __( 'Direct Checkout', $this->textdomain ), __( 'Direct Checkout', $this->textdomain ), 'manage_options', 'wc-direct-checkout', array(
						&$this,
						'settings_page_direct_checkout'
				));
			}
			
			/**
			 * Create the settings page content
			 */
			public function settings_page_direct_checkout() {
			
				// If form was submitted
				if ( isset( $_POST['submitted'] ) )
				{
					check_admin_referer( $this->textdomain );
	
					$this->saved_options_direct_checkout['direct_checkout_enabled'] = ! isset( $_POST['direct_checkout_enabled'] ) ? '1' : $_POST['direct_checkout_enabled'];
					$this->saved_options_direct_checkout['direct_checkout_cart_button_text'] = ! isset( $_POST['direct_checkout_cart_button_text'] ) ? 'Add to cart' : $_POST['direct_checkout_cart_button_text'];
						
					foreach($this->options_direct_checkout as $field => $value)
					{
						$option_direct_checkout = get_option( $field );
			
						if($option_direct_checkout != $this->saved_options_direct_checkout[$field])
							update_option( $field, $this->saved_options_direct_checkout[$field] );
					}
						
					// Show message
					echo '<div id="message" class="updated fade"><p>' . __( 'You have saved WooCommerce Direct Checkout options.', $this->textdomain ) . '</p></div>';
				}
			
				$direct_checkout_enabled			= get_option( 'direct_checkout_enabled' );
				$direct_checkout_cart_button_text	= get_option( 'direct_checkout_cart_button_text' );
				
				$checked = '';
			
				if($direct_checkout_enabled)
					$checked = 'checked="checked"';

			
				$actionurl = $_SERVER['REQUEST_URI'];
				$nonce = wp_create_nonce( $this->textdomain );
			
			
				// Configuration Page
			
				?>
				<div id="icon-options-general" class="icon32"></div>
				<h3><?php _e( 'Direct Checkout Options', $this->textdomain); ?></h3>
				
				
				<table width="90%" cellspacing="2">
				<tr>
					<td colspan="2">Checking out is the most important and key part of placing an order online, and many users end up abandoning their order at the end. This plugin will simplify the checkout process, leading to an immediate increase in sales.</td>
				</tr>
				<tr>
					<td width="70%" valign="top">
						<form action="<?php echo $actionurl; ?>" method="post">
						<table>
								<tbody>
									<tr>
										<td colspan="2">
											<table class="widefat fixed" cellspacing="2" cellpadding="5" border="0">
												<tr>
													<td width="25%"><?php _e( 'Enable', $this->textdomain ); ?></td>
													<td>
														<input class="checkbox" name="direct_checkout_enabled" id="direct_checkout_enabled" value="0" type="hidden">
														<input class="checkbox" name="direct_checkout_enabled" id="direct_checkout_enabled" value="1" type="checkbox" <?php echo $checked; ?> type="checkbox">
													</td>
												</tr>
												<tr>
													<td width="25%"><?php _e( 'Custom Add to Cart Text', $this->textdomain ); ?></td>
													<td>
														<input name="direct_checkout_cart_button_text" id="direct_checkout_cart_button_text" value="<?php echo $direct_checkout_cart_button_text; ?>" />
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td colspan=2">
											<input class="button-primary" type="submit" name="Save" value="<?php _e('Save Options', $this->textdomain); ?>" id="submitbutton" />
											<input type="hidden" name="submitted" value="1" /> 
											<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo $nonce; ?>" />
										</td>
									</tr>
								</tbody>
						</table>
						</form>
					
					</td>
					
					<td width="30%" style="background:#ececec;padding:10px 5px;" valign="top">
						<p><b>WooCommerce Direct Checkout</b> is a FREE woocommerce plugin developed by <a href="http://shop.terrytsang.com" target="_blank" title="Terry Tsang - a PHP Developer and Wordpress Consultant">Terry Tsang</a>. This plugin aims to add direct checkout for WooCommerce.</p>
						
						<h3>Get More Plugins</h3>
					
						<p><a href="http://shop.terrytsang.com" target="_blank" title="Premium &amp; Free Extensions/Plugins for E-Commerce by Terry Tsang">Go to My Site</a> to get more free and premium extensions/plugins for your ecommerce sites.</p>
					
						<h3>Spreading the Word</h3>

						<ul style="list-style:dash">If you find this plugin helpful, you can:	
							<li>- Write and review about it in your blog</li>
							<li>- Rate it on <a href="http://wordpress.org/extend/plugins/woocommerce-facebook-share-like-button/" target="_blank">wordpress plugin page</a></li>
							<li>- Share on your social media<br />
							<a href="http://www.facebook.com/sharer.php?u=http://terrytsang.com/shop/shop/woocommerce-direct-checkout/&amp;t=WooCommerce Direct Checkout" title="Share this WooCommerce Direct Checkout on Facebook" target="_blank"><img src="http://terrytsang.com/shop/images/social_facebook.png" alt="Share this WooCommerce Direct Checkout plugin on Facebook"></a> 
							<a href="https://twitter.com/intent/tweet?url=http%3A%2F%2Fterrytsang.com%2Fshop%2Fshop%2Fwoocommerce-facebook-share-like-button%2F&text=WooCommerce Direct Checkout - &via=terrytsang811" target="_blank"><img src="http://terrytsang.com/shop/images/social_twitter.png" alt="Tweet about WooCommerce Direct Checkout plugin"></a>
							<a href="http://linkedin.com/shareArticle?mini=true&amp;url=http://terrytsang.com/shop/shop/woocommerce-direct-checkout/&amp;title=WooCommerce Direct Checkout plugin" title="Share this WooCommerce Direct Checkout plugin on LinkedIn" target="_blank"><img src="http://terrytsang.com/shop/images/social_linkedin.png" alt="Share this WooCommerce Direct Checkout plugin on LinkedIn"></a>
							</li>
							<li>- Or make a donation</li>
						</ul>
	
						<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LJWSJDBBLNK7W" target="_blank"><img src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" alt="" /></a>

						<h3>Thank you for your support!</h3>
					</td>
					
				</tr>
				</table>
				
				
				<br />
				
			<?php
			}
			
			/**
			 * Get the setting options
			 */
			function get_options() {
				
				foreach($this->options_direct_checkout as $field => $value)
				{
					$array_options[$field] = get_option( $field );
				}
					
				return $array_options;
			}

			
		}//end class
			
	}//if class does not exist
	
	$woocommerce_direct_checkout = new WooCommerce_Direct_Checkout();
}
else{
	add_action('admin_notices', 'wc_direct_checkout_error_notice');
	function wc_direct_checkout_error_notice(){
		global $current_screen;
		if($current_screen->parent_base == 'plugins'){
			echo '<div class="error"><p>'.__(wc_plugin_name_direct_checkout.' requires <a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a> to be activated in order to work. Please install and activate <a href="'.admin_url('plugin-install.php?tab=search&type=term&s=WooCommerce').'" target="_blank">WooCommerce</a> first.').'</p></div>';
		}
	}
}

?>