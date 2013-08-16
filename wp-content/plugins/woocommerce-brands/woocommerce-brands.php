<?php
/*
Plugin Name: WooCommerce Brands Addon
Plugin URI: http://woothemes.com/woocommerce/
Description: Adds a brand taxonomy and several widgets and shortcodes for display on the frontend.
Version: 1.1.4
Author: Mike Jolley
Author URI: http://mikejolley.com
Requires at least: 3.3
Tested up to: 3.4

	Copyright: © 2009-2011 WooThemes.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) )
	require_once( 'woo-includes/woo-functions.php' );

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '8a88c7cbd2f1e73636c331c7a86f818c', '18737' );

if ( is_woocommerce_active() ) {

	/**
	 * Localisation
	 **/
	load_plugin_textdomain( 'wc_brands', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	/**
	 * WC_Brands classes
	 **/
	require_once( 'classes/class-wc-brands.php' );

	if ( is_admin() )
		require_once( 'classes/class-wc-brands-admin.php' );

	/**
	 * woocommerce_brands_queue_install function.
	 *
	 * @access public
	 * @return void
	 */
	function woocommerce_brands_queue_install() {
		update_option( 'woocommerce_brands_installed', 0 );
	}

	register_activation_hook( __FILE__, 'woocommerce_brands_queue_install' );


	/**
	 * woocommerce_brands_install function.
	 *
	 * @access public
	 * @return void
	 */
	function woocommerce_brands_install() {
		if ( get_option( 'woocommerce_brands_installed' ) != 1 ) {
			update_option( 'woocommerce_brands_installed', 1 );
			flush_rewrite_rules( false );
		}
	}

	add_action( 'woocommerce_init', 'woocommerce_brands_install' );


	/**
	 * Helper function :: get_brand_thumbnail_url function.
	 *
	 * @access public
	 * @return string
	 */
	function get_brand_thumbnail_url( $brand_id, $size = 'full' ) {
		$thumbnail_id = get_woocommerce_term_meta( $brand_id, 'thumbnail_id', true );

		if ( $thumbnail_id )
			return current( wp_get_attachment_image_src( $thumbnail_id, $size ) );
	}

	/**
	 * get_brands function.
	 *
	 * @access public
	 * @param int $post_id (default: 0)
	 * @param string $sep (default: ')
	 * @param mixed '
	 * @param string $before (default: '')
	 * @param string $after (default: '')
	 * @return void
	 */
	function get_brands( $post_id = 0, $sep = ', ', $before = '', $after = '' ) {
		global $post;

		if ( $post_id )
			$post_id = $post->ID;

		return get_the_term_list( $post_id, 'product_brand', $before, $sep, $after );
	}
}