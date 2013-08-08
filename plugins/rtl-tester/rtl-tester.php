<?php
/**
 * RTL-Tester plugin.
 *
 * Adds a button to the admin bar that allow super admins to switch the text direction of the site.
 *
 * @package RTL_Tester
 * @author Automattic
 * @author Yoav Farhi
 * @version 1.0.3
 *
 * @wordpress
 * Plugin Name: RTL Tester
 * Plugin URI: http://wordpress.org/extend/plugins/rtl-tester/
 * Description: This plugin adds a button to the admin bar that allow super admins to switch the text direction of the site. It can be used to test WordPress themes and plugins with Right To Left (RTL) text direction.
 * Author: <a href="http://blog.yoavfarhi.com">Yoav Farhi</a>, <a href="http://automattic.com">Automattic</a>
 * Version: 1.0.3
 */

/**
 * Plugin class for RTL Tester plugin
 *
 * @package RTL_Tester
 */
class RTLTester {

	/**
	 * Loads plugin textdomain, and hooks in further actions.
	 */
	function __construct() {

		load_plugin_textdomain( 'rtl-tester', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		add_action( 'init', array( $this, 'set_direction' ) );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_rtl_switcher' ), 999 );
	}

	/**
	 * Adds button to admin bar.
	 *
	 * @global object $wp_admin_bar Most likely instance of WP_Admin_Bar but this is filterable.
	 * 
	 * @return null Retuns early if not site admin, or admin bar should not be showing. 
	 */
	function admin_bar_rtl_switcher() {
		global $wp_admin_bar;

		if ( ! is_super_admin() || ! is_admin_bar_showing() )
	      return;

		// Get opposite direction for button text
		$direction = is_rtl() ? 'ltr' : 'rtl';

		$wp_admin_bar->add_menu(
			array(
				'id'    => 'RTL',
		 		'title' => sprintf( __( 'Switch to %s', 'rtl-tester' ), strtoupper( $direction ) ),
		 		'href'  => add_query_arg( array( 'd' => $direction ) )
			)
		);
	}

	/**
	 * Save the currently chosen direction on a per-user basis.
	 *
	 * @global WP_Locale $wp_locale Locale object.
	 * @global WP_Styles $wp_styles Styles object.
	 */
	function set_direction() {
		global $wp_locale, $wp_styles;

		$_user_id = get_current_user_id();

		if ( isset( $_GET['d'] ) ) {
			$direction = $_GET['d'] == 'rtl' ? 'rtl' : 'ltr';
			update_user_meta( $_user_id, 'rtladminbar', $direction );
		} else {
			$direction = get_user_meta( $_user_id, 'rtladminbar', true );
			if ( false === $direction )
				$direction = isset( $wp_locale->text_direction ) ? $wp_locale->text_direction : 'ltr' ;
		}

		$wp_locale->text_direction = $direction;
		if ( ! is_a( $wp_styles, 'WP_Styles' ) ) {
			$wp_styles = new WP_Styles();
			$wp_styles->text_direction = $direction;
		}
	}

}

new RTLTester;