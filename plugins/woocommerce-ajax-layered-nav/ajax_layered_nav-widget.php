<?php
/*
Plugin Name: WooCommerce Advanced Ajax Layered Navigation
Version: 1.2.5
Plugin URI: http://woothemes.com/woocommerce
Description: Ajaxifies the standard WooCommerce Layered Nav and adds additional output types like color swatches, sizes, checkboxes, etc
Author URI: http://sixtyonedesigns.com
Author: Sixty-One Designs, Inc.
Copyright: © 2009-2013 Sixty-One Designs, Inc.
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
woothemes_queue_update( plugin_basename( __FILE__ ), '8a0ed1b64e6a889a9f084db0ed5ece6c', '18675' );
if (is_woocommerce_active()) {
	include_once( 'widgets/class-sod-widget-ajax-layered-nav.php' );
	include_once( 'widgets/class-sod-widget-ajax-layered-nav-filters.php' );
	load_plugin_textdomain('sod_ajax_layered_nav', false, dirname(plugin_basename(__FILE__)) . '/lang/');
	/**
	 * Initialize WooCommerce loop if widget is active
	 **/
	if (is_active_widget( false, false, 'sod_ajax_layered_nav', 'true' ) && !is_admin()) :
		add_action('init', 'woocommerce_layered_nav_init', 1);
		add_filter('loop_shop_post_in', 'woocommerce_layered_nav_query');
	endif;

	/**
	 * Queue Scripts and Stylesheets
	 **/
	add_action('wp_enqueue_scripts', 'advanced_nav_scripts');
	add_action('admin_enqueue_scripts', 'advanced_nav_admin_scripts');

	function advanced_nav_scripts(){
		global $is_IE;

		wp_register_style( 'advanced_nav_css', plugins_url('assets/css/advanced_nav.css', __FILE__), false,'1.2.1');
		wp_enqueue_style( 'advanced_nav_css' );

		if ( $is_IE ) {
			wp_register_script( 'html5', plugins_url('assets/js/html5.js', __FILE__), '', '1.2.1', true);
			wp_enqueue_script('html5');
		}

		// No need for this on the Single Product page
		if ( ! is_product() )
			wp_enqueue_script( 'pageloader', plugins_url('assets/js/ajax_layered_nav.js', __FILE__), array( 'jquery' ),'1.2.1', true );

		$html_containers = array(
			'#products',
			'#pagination-wrapper',
			'.woocommerce-pagination',
			'.widget_layered_nav',
			'.widget_layered_nav_filters',
			'.woocommerce-ordering',
			'.woocommerce-result-count'
		);

		$clickables 	 = array(
			'.widget_layered_nav a',
			'.widget_layered_nav input[type="checkbox"]',
			'.widget_ajax_layered_nav_filters a'
		);

		$html_containers    = apply_filters( 'sod_ajax_layered_nav_containers', $html_containers );
		$clickables 	 	= apply_filters( 'sod_ajax_layered_nav_clickables', $clickables );
		$order_by_form 	 	= apply_filters( 'sod_ajax_layered_nav_orderby', '.woocommerce-ordering' );
		$products_container = apply_filters( 'sod_ajax_layered_nav_product_container', '#products' );

		$args = array(
			'loading_img'		=> plugins_url( 'assets/images/loading.gif', __FILE__ ),
			'loading_text'		=> __('Loading', 'sod_ajax_layered_nav'),
			'containers'		=> $html_containers,
			'triggers'			=> $clickables,
			'orderby'			=> $order_by_form,
			'product_container'	=> $products_container
		);

		wp_localize_script( 'pageloader', 'ajax_layered_nav', $args );
	}

	function advanced_nav_admin_scripts(){
		wp_register_script( 'advanced_nav_admin', plugins_url('assets/js/ajax_layered_nav_admin.js',__FILE__));
		wp_register_script( 'advanced_colorpicker', plugins_url('assets/js/colorpicker.js',__FILE__));
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'advanced_colorpicker' );
		wp_enqueue_script( 'advanced_nav_admin' );
		wp_register_style( 'colorpicker_css', plugins_url('assets/css/colorpicker.css',__FILE__), false,'1.0.0' );
		wp_register_style( 'advanced_nav_css', plugins_url('assets/css/advanced_nav.css',__FILE__), false,'1.0.0' );
		wp_enqueue_style( 'colorpicker_css' );
		wp_enqueue_style( 'advanced_nav_css' );
		$args = array(
			'siteurl'					=> get_site_url(),
			'sod_ajax_layered_nonce'	=> wp_create_nonce('sod_ajax_layered_nonce'),
		);
		wp_localize_script( 'advanced_nav_admin','site', $args );
	}

	/**
	 * Pagination Wrapper
	 *
	 * Makes for easy locating on pagination after ajax callback - makes sure paginations carrier through
	 **/
	add_action( 'woocommerce_pagination', 'ajax_layered_nav_pagination_before', 1 );
	add_action( 'woocommerce_pagination', 'ajax_layered_nav_pagination_after', 15 );

	function ajax_layered_nav_pagination_before() {
		echo '<nav id="pagination-wrapper">';
	}

	function ajax_layered_nav_pagination_after() {
		echo '</nav>';
	}

	/**
	 * Contents Wrapper
	 *
	 * Helps us know what elements to update with new content
	 **/
	add_action('woocommerce_before_shop_loop','add_before_products_div');
	add_action('woocommerce_after_shop_loop','add_after_products_div');

	function add_before_products_div() {
		echo '<section id="products">';
	}

	function add_after_products_div() {
		echo '</section>';
	}

	/**
	 * Ajax Handler function to set admin widget options
	 *
	 * Returns options table
	 **/
	add_action('wp_ajax_set_type', 'ajax_set_type');

	function ajax_set_type() {
	    Try{
	    	$nonce = $_POST['sod_ajax_layered_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'sod_ajax_layered_nonce' ) )
	    	die ( 'Busted!');
	    	$args=array(
	    	'hide_empty'=>'0'
			);
		    $attribute_values = get_terms('pa_'.$_POST['attr_name'],$args);
			$html=null;

			$number = explode('-',$_POST['id']);
			if(is_array($number)){
				$number = end($number);
			}
			$widget = explode('-',$_POST['id']);
			$end = array_pop($widget);
			$id = implode('-', $widget);
	    	switch ($_POST['type']) {
				case 'list':
				case 'checkbox':
				case 'slider':
					break;
				//Return new color picker table
				case 'colorpicker':
					$html .= '<table class="color">
								<thead>
									<tr>
										<td>'.__('Name', 'sod_ajax_layered_nav').'</td>
										<td>'.__('Color Code', 'sod_ajax_layered_nav').'</td>
									</tr>
								</thead>
								<tbody>';
					foreach($attribute_values as $attribute){
						$html.='<tr>
									<td class="labels"><label for="widget-'.$id.'['.$number.'][colors]['.$attribute->term_id.']">'.$attribute->name.'</label></td>
									<td class="inputs"><input class="color_input" type="input" name="widget-'.$id.'['.$number.'][colors]['.$attribute->term_id.']" id="widget-'.$id.'['.$number.'][colors]['.$attribute->term_id.']" size="3" maxlength="3"/>
									<div class="colorSelector"><div></div></div></td>
								</tr>';
					}
					$html .= '</tbody>
							</table>';
					break;
				//Return new color picker table of sizes
				case 'sizeselector':
					$html .= '<table class="sizes">
								<thead>
									<tr>
										<td>'.__('Name', 'sod_ajax_layered_nav').'</td>
										<td>'.__('Label', 'sod_ajax_layered_nav').'</td>
										<td></td>
									</tr>
								</thead>
								<tbody>';
					foreach($attribute_values as $attribute){
						$html.='<tr>
									<td class="labels"><label for="widget-'.$id.'['.$number.'][labels]['.$attribute->term_id.']">'.$attribute->name.'</label></td>
									<td class="inputs"><input type="input" name="widget-'.$id.'['.$number.'][labels]['.$attribute->term_id.'] id="widget-'.$id.'['.$number.'][labels]['.$attribute->term_id.']" size="3" maxlength="3"/></td>
									<td></td>
								</tr>';
					}
					$html .= '</tbody>
							</table>';
					break;
			}
			echo $html;
	    } catch (Exception $e){
	    	exit;
	 	}
		exit;
	}
	/**
	 * init widget
	 **/
	add_action( 'widgets_init', 'sod_layered_nav_init' );

	function sod_layered_nav_init() {
		register_widget( 'SOD_Widget_Ajax_Layered_Nav' );
		register_widget( 'SOD_Widget_Ajax_Layered_Nav_Filters' );
	}
}