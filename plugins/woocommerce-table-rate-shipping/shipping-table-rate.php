<?php
/*
Plugin Name: WooCommerce Table Rate Shipping
Plugin URI: http://woothemes.com/woocommerce/
Description: Table rate shipping lets you define rates depending on location vs shipping class, price, weight, or item count. Requires WC 1.5.7+
Version: 2.6.5
Author: Mike Jolley
Author URI: http://mikejolley.com
Requires at least: 3.3
Tested up to: 3.4

	Copyright: 2009-2011 WooThemes.
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
woothemes_queue_update( plugin_basename( __FILE__ ), '3034ed8aff427b0f635fe4c86bbf008a', '18718' );

/**
 * Check if WooCommerce is active
 */
if ( is_woocommerce_active() ) {

	define( 'TABLE_RATE_SHIPPING_VERSION', '2.5.1' );

	/**
	 * Localisation
	 */
	load_plugin_textdomain( 'wc_table_rate', false, dirname( plugin_basename( __FILE__ ) ) . '/' );
	load_plugin_textdomain( 'wc_shipping_zones', false, dirname( plugin_basename( __FILE__ ) ) . '/' );

	/**
	 * Installation
	 */
	register_activation_hook( __FILE__, 'install_table_rate_shipping' );

	function install_table_rate_shipping() {

		include_once( 'admin/table-rate-install.php' );

		wc_table_rate_install();

		update_option( 'table_rate_shipping_version', TABLE_RATE_SHIPPING_VERSION );
	}

	/**
	 * AJAX Handlers
	 */
	if ( defined( 'DOING_AJAX' ) ) {
		include_once( 'admin/table-rate-ajax.php' );
	}

	/**
	 * Zones
	 */
	if ( ! class_exists( 'WC_Shipping_zone' ) ) {
		include_once( 'shipping-zones/shipping-zones-init.php' );
	}

	/**
	 * Install check (for updates)
	 */
	if ( get_option( 'table_rate_shipping_version' ) < TABLE_RATE_SHIPPING_VERSION )
		install_table_rate_shipping();

	/**
	 * Welcome notices
	 */
	if ( get_option( 'hide_table_rate_welcome_notice' ) == '' )
		add_action( 'admin_notices', 'woocommerce_table_rate_welcome_notice' );

	function woocommerce_table_rate_welcome_notice() {
		global $woocommerce;

		wp_enqueue_style( 'woocommerce-activation', $woocommerce->plugin_url() . '/assets/css/activation.css' );
		?>
		<div id="message" class="updated woocommerce-message wc-connect">
			<div class="squeezer">
				<h4><?php _e( '<strong>Table Rates is installed</strong> &#8211; Add some shipping zones to get started :)', 'wc_table_rate' ); ?></h4>
				<p class="submit"><a href="<?php echo admin_url('admin.php?page=shipping_zones'); ?>" class="button-primary"><?php _e( 'Setup Zones', 'wc_table_rate' ); ?></a> <a class="skip button-primary" href="http://wcdocs.woothemes.com/user-guide/table-rate-shipping-v2/"><?php _e('Documentation', 'wc_table_rate'); ?></a></p>
			</div>
		</div>
		<?php
		update_option( 'hide_table_rate_welcome_notice', 1 );
	}

	/**
	 * init_styles function.
	 *
	 * @access public
	 * @return void
	 */
	function woocommerce_shipping_table_rate_styles() {
	    wp_enqueue_style( 'woocommerce_shipping_table_rate_styles', plugins_url( '/assets/css/admin.css', __FILE__ ) );
	}

	add_action( 'woocommerce_shipping_zones_css', 'woocommerce_shipping_table_rate_styles' );

	/**
	 * woocommerce_init_shipping_table_rate function.
	 *
	 * @access public
	 * @return void
	 */
	add_action( 'woocommerce_shipping_init', 'woocommerce_init_shipping_table_rate' );

	function woocommerce_init_shipping_table_rate() {

		/**
	 	* Shipping method class
	 	*/
		class WC_Shipping_Table_Rate extends WC_Shipping_Method {

			var $available_rates;	// Available table rates titles and costs

			var $instance_id;		// ID for the instance/shipping method. id-number
			var $id;				// Method ID - should be unique to the shipping method
			var $number;			// Instance ID number

			function __construct( $instance = false ) {
				global $woocommerce, $wpdb;

				$this->id				= 'table_rate';
				$this->method_title 	= __( 'Table rates', 'wc_table_rate' );
				$this->title 			= $this->method_title;
				$this->has_settings		= false;
				$this->enabled			= 'yes';
				$this->supports			= array( 'zones' );
				$this->tax 				= new WC_Tax();

		        // Load the form fields.
				$this->init_form_fields();

				// Load any GLOBAL settings
				$this->init_settings();

				// If we have an instance, set the id
				if ( $instance !== FALSE ) {
					$this->_set( $instance );

					// Load INSTANCE settings
					$this->init_instance_settings();

					$this->title 			= $this->settings['title'] ? $this->settings['title'] : __( 'Table Rate', 'wc_table_rate' );
					$this->fee 				= $this->settings['handling_fee'];
					$this->tax_status		= $this->settings['tax_status'];
					$this->calculation_type	= $this->settings['calculation_type'];
					$this->min_cost			= isset( $this->settings['min_cost'] ) ? $this->settings['min_cost'] : '';
				}

				// Table rate specific variables
		        $this->rates_table 		= $wpdb->prefix . 'woocommerce_shipping_table_rates';
		        $this->available_rates	= array();
		    }

		    /**
		     * Instance related functions (not yet in core API's)
		     */
		    private function _set( $number ) {
			    $this->number = $number;
			    $this->instance_id = $this->id . '-' . $number;
			}

			/**
		     * Initialise Instance Settings
		     */
		    function init_instance_settings() {

		    	// Load instance settings (if applicable)
		    	if ( ! empty( $this->instance_fields ) && ! empty( $this->instance_id ) ) {

		    		$instance_settings = ( array ) get_option( $this->plugin_id . $this->instance_id . '_settings' );

			    	if ( ! $instance_settings ) {

			    		// If there are no settings defined, load defaults
			    		foreach ( $this->instance_fields as $k => $v )
			    			$instance_settings[ $k ] = isset( $v['default'] ) ? $v['default'] : '';

			    	} else {

				    	// Prevent "undefined index" errors.
				    	foreach ( $this->instance_fields as $k => $v )
		    				$instance_settings[ $k ] = isset( $instance_settings[ $k ] ) ? $instance_settings[ $k ] : $v['default'];

			    	}

			    	// Set and decode escaped values
			    	$this->settings = array_merge( (array) $this->settings, array_map( array( $this, 'format_settings' ), $instance_settings ) );
		    	}

		    	if ( isset( $this->settings['enabled'] ) )
		    		$this->enabled = $this->settings['enabled'];

		    } // End init_instance_settings()

			/**
		     * Initialise Gateway Settings Form Fields
		     */
		    function init_form_fields() {

		    	$this->form_fields = array(); // No global options for table rates

		    	$this->instance_fields = array(
					'title' => array(
									'title' 		=> __( 'Method Title', 'wc_table_rate' ),
									'type' 			=> 'text',
									'description' 	=> __( 'This controls the title which the user sees during checkout.', 'wc_table_rate' ),
									'default'		=> __( 'Table Rate', 'wc_table_rate' )
								),
					'enabled' => array(
									'title' 		=> __( 'Enable/Disable', 'wc_table_rate' ),
									'type' 			=> 'checkbox',
									'label' 		=> __( 'Enable this shipping method', 'wc_table_rate' ),
									'default' 		=> 'yes'
								),
					'tax_status' => array(
									'title' 		=> __( 'Tax Status', 'wc_table_rate' ),
									'type' 			=> 'select',
									'description' 	=> '',
									'default' 		=> 'taxable',
									'options'		=> array(
										'taxable' 	=> __('Taxable', 'wc_table_rate'),
										'none' 		=> __('None', 'wc_table_rate')
									)
								),
					'handling_fee' => array(
									'title' 		=> __( 'Handling Fee', 'wc_table_rate' ),
									'type' 			=> 'text',
									'description'	=> __('Fee excluding tax. Enter an amount, e.g. 2.50, or a percentage, e.g. 5%. Leave blank to disable.', 'wc_table_rate'),
									'default'		=> ''
								),
					'min_cost' => array(
									'title' 		=> __( 'Minimum Cost', 'wc_table_rate' ),
									'type' 			=> 'text',
									'description'	=> __('Minimum cost for this shipping method (optional). If the cost is lower, this minimum cost will be enforced.', 'wc_table_rate'),
									'default'		=> ''
								),
					'calculation_type' => array(
									'title' 		=> __( 'Calculation type', 'wc_table_rate' ),
									'type' 			=> 'select',
									'description' 	=> __('Per order rates will offer the customer all matching rates. Calculated rates will sum all matching rates and provide a single total.', 'wc_table_rate'),
									'default' 		=> '',
									'options'		=> array(
										'' 			=> __('Per order', 'wc_table_rate'),
										'item' 		=> __('Calculated rate (per item)', 'wc_table_rate'),
										'line' 		=> __('Calculated rate (per line)', 'wc_table_rate'),
										'class' 	=> __('Calculated rate (per shipping class)', 'wc_table_rate')
									)
								),
					);

		    } // End init_form_fields()

		    /**
		     * admin_options function.
		     *
		     * @access public
		     * @return void
		     */
		    public function instance_options() {
			    global $woocommerce;

			    include_once( 'admin/table-rate-rows.php' );

			    ?>
			    <table class="form-table">
				    <?php
				    // Generate the HTML For the settings form.
			    	$this->generate_settings_html( $this->instance_fields );
					?>
			        <tr>
						<th><?php _e('Rates', 'wc_table_rate'); ?></th>
						<td>
							<?php wc_table_rate_admin_shipping_rows( $this ); ?>
						</td>
					</tr>
					<tr valign="top" id="shipping_class_priorities">
			            <th scope="row" class="titledesc"><?php _e('Class Priorities', 'wc_table_rate'); ?></th>
			            <td class="forminp" id="shipping_rates">
			            	<?php wc_table_rate_admin_shipping_class_priorities( $this->number ); ?>
			            </td>
			        </tr>
			    </table>
			    <?php
		    }

			/**
			 * Admin Panel Options Processing
			 * - Saves the options to the DB
			 *
			 * @since 1.0.0
			 */
		    public function process_instance_options() {

		    	include_once( 'admin/table-rate-rows.php' );

		    	$this->validate_settings_fields( $this->instance_fields  );

		    	if ( count( $this->errors ) > 0 ) {

		    		$this->display_errors();

		    		return false;

		    	} else {

		    		wc_table_rate_admin_shipping_rows_process( $this->number  );

		    		update_option( $this->plugin_id . $this->instance_id . '_settings', $this->sanitized_fields );

		    		return true;
		    	}
		    }

		    /**
		     * is_available function.
		     *
		     * @access public
		     * @param mixed $package
		     * @return void
		     */
		    function is_available( $package ) {
		    	$available = true;

		    	if ( $this->enabled == "no" )
		    		$available = false;

		    	if ( ! $this->get_rates( $package ) ) {
			    	$available = false;
			    }

		    	return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $available, $package, $this );
		    }

			/**
			 * count_items_in_class function.
			 *
			 * @access public
			 * @return void
			 */
			function count_items_in_class( $package, $class_id ) {

				$count = 0;

				// Find shipping classes for products in the package
    			foreach ( $package['contents'] as $item_id => $values ) {
    				if ( $values['data']->needs_shipping() && $values['data']->get_shipping_class_id() == $class_id )
    					$count += $values['quantity'];
    			}

    			return $count;
			}

		    /**
		     * get_cart_shipping_class_id function.
		     *
		     * @access public
		     * @return void
		     */
		    function get_cart_shipping_class_id( $package ) {

				// Find shipping class for cart
				$found_shipping_classes = array();
				$shipping_class_id = 0;
				$shipping_class_slug = '';

	    		// Find shipping classes for products in the package
	    		if ( sizeof( $package['contents'] ) > 0 ) {
	    			foreach ( $package['contents'] as $item_id => $values ) {
	    				if ( $values['data']->needs_shipping() ) {
	    					$found_shipping_classes[ $values['data']->get_shipping_class_id() ] = $values['data']->get_shipping_class();
	    				}
	    			}
	    		}

	    		$found_shipping_classes = array_unique( $found_shipping_classes );

				if ( sizeof( $found_shipping_classes ) == 1 ) {
					$shipping_class_slug = current( $found_shipping_classes );
				} elseif ( $found_shipping_classes > 1 ) {

					// Get class with highest priority
					$priority 	= get_option('woocommerce_table_rate_default_priority_' . $this->number );
					$priorities = get_option( 'woocommerce_table_rate_priorities_' . $this->number );

					foreach ( $found_shipping_classes as $class ) {
						if ( isset( $priorities[ $class ] ) && $priorities[ $class ] < $priority ) {
							$priority = $priorities[ $class ];
							$shipping_class_slug = $class;
						}
					}
				}

				$found_shipping_classes = array_flip( $found_shipping_classes );

				if ( isset( $found_shipping_classes[ $shipping_class_slug ] ) )
					$shipping_class_id = $found_shipping_classes[ $shipping_class_slug ];

				return $shipping_class_id;
		    }

		    /**
		     * query_rates function.
		     *
		     * @access public
		     * @param mixed $args
		     * @return void
		     */
		    function query_rates( $args ) {
			    global $wpdb;

				$defaults = array(
					'price' 			=> '',
					'weight' 			=> '',
					'count' 			=> 1,
					'count_in_class' 	=> 1,
					'shipping_class_id' => ''
				);

				$args = wp_parse_args( $args, $defaults );

				extract( $args, EXTR_SKIP );

				if ( $shipping_class_id == "" ) {
					$shipping_class_id_in = " AND rate_class IN ( '', '0' )";
				} else {
					$shipping_class_id_in = " AND rate_class IN ( '', '" . absint( $shipping_class_id ) . "' )";
				}

			   	return $wpdb->get_results(
					$wpdb->prepare( "
						SELECT rate_id, rate_cost, rate_cost_per_item, rate_cost_per_weight_unit, rate_cost_percent, rate_label, rate_priority
						FROM {$this->rates_table}
						WHERE shipping_method_id IN ( %s )
						{$shipping_class_id_in}
						AND
						(
							rate_condition = ''
							OR
							(
								rate_condition = 'price'
								AND
								(
									( ( rate_min + 0 ) = '' AND ( rate_max + 0 ) = '' )
									OR
									( ( rate_min + 0 ) >= 0 AND ( rate_max + 0 ) >=0 AND '{$price}' >= ( rate_min + 0 ) AND '{$price}' <= ( rate_max + 0 ) )
									OR
									( ( rate_min + 0 ) >= 0 AND ( rate_max + 0 ) = '' AND '{$price}' >= ( rate_min + 0 ) )
									OR
									( ( rate_min + 0 ) = '' AND ( rate_max + 0 ) >= 0 AND '{$price}' <= ( rate_max + 0 ) )
								)
							)
							OR
							(
								rate_condition = 'weight'
								AND
								(
									( ( rate_min + 0 ) = '' AND ( rate_max + 0 ) = '' )
									OR
									( ( rate_min + 0 ) >= 0 AND ( rate_max + 0 ) >=0 AND '{$weight}' >= ( rate_min + 0 ) AND '{$weight}' <= ( rate_max + 0 ) )
									OR
									( ( rate_min + 0 ) >= 0 AND ( rate_max + 0 ) = '' AND '{$weight}' >= ( rate_min + 0 ) )
									OR
									( ( rate_min + 0 ) = '' AND ( rate_max + 0 ) >= 0 AND '{$weight}' <= ( rate_max + 0 ) )
								)
							)
							OR
							(
								rate_condition = 'items'
								AND
								(
									( ( rate_min + 0 ) = '' AND ( rate_max + 0 ) = '' )
									OR
									( ( rate_min + 0 ) >= 0 AND ( rate_max + 0 ) >=0 AND '{$count}' >= ( rate_min + 0 ) AND '{$count}' <= ( rate_max + 0 ) )
									OR
									( ( rate_min + 0 ) >= 0 AND ( rate_max + 0 ) = '' AND '{$count}' >= ( rate_min + 0 ) )
									OR
									( ( rate_min + 0 ) = '' AND ( rate_max + 0 ) >= 0 AND '{$count}' <= ( rate_max + 0 ) )
								)
							)
							OR
							(
								rate_condition = 'items_in_class'
								AND
								(
									( ( rate_min + 0 ) = '' AND ( rate_max + 0 ) = '' )
									OR
									( ( rate_min + 0 ) >= 0 AND ( rate_max + 0 ) >= 0 AND '{$count_in_class}' >= ( rate_min + 0 ) AND '{$count_in_class}' <= ( rate_max + 0 ) )
									OR
									( ( rate_min + 0 ) >= 0 AND ( rate_max + 0 ) = '' AND '{$count_in_class}' >= ( rate_min + 0 ) )
									OR
									( ( rate_min + 0 ) = '' AND ( rate_max + 0 ) >= 0 AND '{$count_in_class}' <= ( rate_max + 0 ) )
								)
							)
						)
						ORDER BY rate_order ASC
					", $this->number )
				);

		    }

		    /**
		     * get_rates function.
		     *
		     * @access public
		     * @return void
		     */
		    function get_rates( $package ) {
		    	global $woocommerce, $wpdb;

		    	if ( $this->enabled == "no" || ! $this->instance_id )
		    		return false;

		    	$rates = array();

				// Get rates, depending on type
				if ( $this->calculation_type == 'item' ) {

	    			// For each ITEM get matching rates
	    			$costs = array();

	    			$matched = false;

	    			foreach ( $package['contents'] as $item_id => $values ) {

	    				$_product = $values['data'];

						if ( $values['quantity'] > 0 && $_product->needs_shipping() ) {

							$matching_rates = $this->query_rates( array(
								'price' 			=> $this->get_product_price( $_product ),
								'weight' 			=> $_product->get_weight(),
								'count' 			=> 1,
								'count_in_class' 	=> $this->count_items_in_class( $package, $_product->get_shipping_class_id() ),
								'shipping_class_id' => $_product->get_shipping_class_id()
							) );

							$item_weight 		= round( $_product->get_weight(), 2 );
							$item_fee			= $this->get_fee( $this->fee, $this->get_product_price( $_product ) );
							$item_cost 			= 0;

							foreach ( $matching_rates as $rate ) {
								$item_cost += $rate->rate_cost;
								$item_cost += $rate->rate_cost_per_weight_unit * $item_weight;
								$item_cost += ( $rate->rate_cost_percent / 100 ) * $this->get_product_price( $_product );
								$matched = true;
								if ( $rate->rate_priority )
									break;
							}

							$cost = ( $item_cost + $item_fee ) * $values['quantity'];

							if ( $this->min_cost && $cost < $this->min_cost )
								$cost = $this->min_cost;

							$costs[ $item_id ] = $cost;

						}

					}

					if ( $matched )
			    		$rates[] = array(
							'id' 		=> $this->instance_id,
							'label' 	=> $this->title,
							'cost' 		=> $costs,
							'calc_tax' 	=> 'per_item'
						);

				} elseif ( $this->calculation_type == 'line' ) {

					// For each LINE get matching rates
	    			$costs = array();

	    			$matched = false;

	    			foreach ( $package['contents'] as $item_id => $values ) {

	    				$_product = $values['data'];

						if ( $values['quantity'] > 0 && $_product->needs_shipping() ) {

							$matching_rates = $this->query_rates( array(
								'price' 			=> $this->get_product_price( $_product, $values['quantity'] ),
								'weight' 			=> $_product->get_weight() * $values['quantity'],
								'count' 			=> $values['quantity'],
								'count_in_class' 	=> $this->count_items_in_class( $package, $_product->get_shipping_class_id() ),
								'shipping_class_id' => $_product->get_shipping_class_id()
							) );

							$item_weight 		= round( $_product->get_weight() * $values['quantity'], 2 );
							$item_fee			= $this->get_fee( $this->fee, $this->get_product_price( $_product, $values['quantity'] ) );
							$item_cost 			= 0;

							foreach ( $matching_rates as $rate ) {
								$item_cost += $rate->rate_cost;
								$item_cost += $rate->rate_cost_per_item * $values['quantity'];
								$item_cost += $rate->rate_cost_per_weight_unit * $item_weight;
								$item_cost += ( $rate->rate_cost_percent / 100 ) * ( $this->get_product_price( $_product, $values['quantity'] ) );
								$matched = true;
								if ( $rate->rate_priority )
									break;
							}

							$item_cost = $item_cost + $item_fee;

							if ( $this->min_cost && $item_cost < $this->min_cost )
								$item_cost = $this->min_cost;

							$costs[ $item_id ] = $item_cost;

						}

					}

					if ( $matched )
			    		$rates[] = array(
							'id' 		=> $this->instance_id,
							'label' 	=> $this->title,
							'cost' 		=> $costs,
							'calc_tax' 	=> 'per_item'
						);

				} elseif ( $this->calculation_type == 'class' ) {

					// For each CLASS get matching rates
	    			$total_cost	= 0;

	    			// First get all the rates in the table
	    			$all_rates = $this->get_shipping_rates();

	    			// Now go through cart items and group items by class
	    			$classes 	= array();

	  	    		foreach ( $package['contents'] as $item_id => $values ) {

	    				$_product = $values['data'];

	    				if ( $values['quantity'] > 0 && $_product->needs_shipping() ) {

		    				$shipping_class = $_product->get_shipping_class_id();

		    				if ( ! isset( $classes[ $shipping_class ] ) ) {
		    					$classes[ $shipping_class ] = new stdClass();
		    					$classes[ $shipping_class ]->price = 0;
		    					$classes[ $shipping_class ]->weight = 0;
		    					$classes[ $shipping_class ]->items = 0;
		    					$classes[ $shipping_class ]->items_in_class = 0;
		    				}

		    				$classes[ $shipping_class ]->price          += $this->get_product_price( $_product, $values['quantity'] );
		    				$classes[ $shipping_class ]->weight         += $_product->get_weight() * $values['quantity'];
		    				$classes[ $shipping_class ]->items          += $values['quantity'];
		    				$classes[ $shipping_class ]->items_in_class += $values['quantity'];
	    				}
	    			}

	    			$matched = false;
	    			$total_cost = 0;
	    			$stop = false;

	    			// Now we have groups, loop the rates and find matches in order
	    			foreach ( $all_rates as $rate ) {

		    			foreach ( $classes as $class_id => $class ) {

		    				if ( $class_id == "" ) {
								if ( $rate->rate_class !== 0 && $rate->rate_class !== '' )
		    						continue;
							} else {
								if ( $rate->rate_class != $class_id && $rate->rate_class !== '' )
		    						continue;
							}

			    			$rate_match = false;

			    			switch ( $rate->rate_condition ) {
				    			case '' :
				    				$rate_match = true;
				    			break;
				    			case 'price' :
				    			case 'weight' :
				    			case 'items_in_class' :
				    			case 'items' :

				    				$condition = $rate->rate_condition;
				    				$value = $class->$condition;

				    				if ( $rate->rate_min === '' && $rate->rate_max === '' )
				    					$rate_match = true;

				    				if ( $value >= $rate->rate_min && $value <= $rate->rate_max )
				    					$rate_match = true;

				    				if ( $value >= $rate->rate_min && ! $rate->rate_max )
				    					$rate_match = true;

				    				if ( $value <= $rate->rate_max && ! $rate->rate_min )
				    					$rate_match = true;

				    			break;
			    			}

			    			// Rate matched class
			    			if ( $rate_match ) {

				    			$total_cost += $rate->rate_cost;
								$total_cost += $rate->rate_cost_per_item * $class->items_in_class;
								$total_cost += $rate->rate_cost_per_weight_unit * $class->weight;
								$total_cost += ( $rate->rate_cost_percent / 100 ) * $class->price;

								if ( $rate->rate_priority )
									$stop = true;

								$matched = true;

								$class_fee	= $this->get_fee( $this->fee, $class->price );
								$total_cost += $class_fee;
			    			}
		    			}

		    			// Breakpoint
		    			if ( $stop )
		    				break;
		    		}

		    		if ( $this->min_cost && $total_cost < $this->min_cost )
						$total_cost = $this->min_cost;

		    		if ( $matched )
			    		$rates[] = array(
							'id' 		=> $this->instance_id,
							'label' 	=> $this->title,
							'cost' 		=> $total_cost
						);

				} else {

					// For the ORDER get matching rates
					$shipping_class 	= $this->get_cart_shipping_class_id( $package );
	    			$price = 0;
    				$weight = 0;
    				$count = 0;
    				$count_in_class = 0;

	    			foreach ( $package['contents'] as $item_id => $values ) {

	    				$_product = $values['data'];

	    				if ( $values['quantity'] > 0 && $_product->needs_shipping() ) {

		    				$price 			+= $this->get_product_price( $_product, $values['quantity'] );
		    				$weight			+= ( $_product->get_weight() * $values['quantity'] );
		    				$count			+= $values['quantity'];

		    				if ( $_product->get_shipping_class_id() == $shipping_class )
		    					$count_in_class += $values['quantity'];

	    				}
	    			}

	    			$matching_rates = $this->query_rates( array(
						'price' 			=> $price,
						'weight' 			=> $weight,
						'count' 			=> $count,
						'count_in_class' 	=> $count_in_class,
						'shipping_class_id' => $shipping_class
					) );

					foreach ( $matching_rates as $rate ) {
						$label = $rate->rate_label;
						if ( ! $label )
							$label = $this->title;

						if ( $rate->rate_priority )
							$rates = array();

						$cost = $rate->rate_cost;
						$cost += $rate->rate_cost_per_item * $count;
						$cost += $this->get_fee( $this->fee, $price );
						$cost += $rate->rate_cost_per_weight_unit * $weight;
						$cost += ( $rate->rate_cost_percent / 100 ) * $price;

						if ( $this->min_cost && $cost < $this->min_cost )
							$cost = $this->min_cost;

						$rates[] = array(
							'id' 		=> $this->instance_id . ' : ' . $rate->rate_id,
							'label' 	=> __( $label, 'wc_table_rate' ),
							'cost' 		=> $cost
						);

						if ( $rate->rate_priority )
							break;
					}

				}

				// None found?
				if ( sizeof( $rates ) == 0 )
					return false;

				// Set available
				$this->available_rates = $rates;

				return true;
		    }

		    /**
		     * calculate_shipping function.
		     *
		     * @access public
		     * @param mixed $package
		     * @return void
		     */
		    function calculate_shipping( $package ) {

		    	if ( $this->available_rates )
		    		foreach ( $this->available_rates as $rate )
		    			$this->add_rate( $rate );

		    }

		    /**
		     * get_shipping_rates function.
		     *
		     * @access public
		     * @param int $class (default: 0)
		     * @return void
		     */
		    function get_shipping_rates( ) {
		    	global $wpdb;

		    	return $wpdb->get_results( "
		    		SELECT * FROM {$this->rates_table}
		    		WHERE shipping_method_id = {$this->number}
		    		ORDER BY rate_order ASC;
		    	" );
			}


			/**
			 * get_product_price_with_tax function.
			 *
			 * @access public
			 * @param mixed $_product
			 * @return void
			 */
			function get_product_price( $_product, $qty = 1 ) {
				$row_base_price 		= $_product->get_price() * $qty;

				if ( ! $_product->is_taxable() )
					return $row_base_price;

				if ( get_option('woocommerce_prices_include_tax') == 'yes' ) {

					$base_tax_rates 		= $this->tax->get_shop_base_rate( $_product->tax_class );
					$tax_rates				= $this->tax->get_rates( $_product->get_tax_class() );

					if ( $tax_rates !== $base_tax_rates ) {
						$base_taxes			= $this->tax->calc_tax( $row_base_price, $base_tax_rates, true, true );
						$modded_taxes		= $this->tax->calc_tax( $row_base_price - array_sum( $base_taxes ), $tax_rates, false );
						$row_base_price 	= ( $row_base_price - array_sum( $base_taxes ) ) + array_sum( $modded_taxes );
					}

				} /*else {

					$tax_rates			 	= $this->tax->get_rates( $_product->get_tax_class() );
					$taxes 					= $this->tax->calc_tax( $row_base_price, $tax_rates, false );
					$tax_amount				= $this->tax->get_tax_total( $taxes );

					return $row_base_price + $tax_amount;

				}*/

				return $row_base_price;
			}

		}

	}

	/**
	 * woocommerce_register_table_rates function.
	 *
	 * @access public
	 * @param mixed $package
	 * @return void
	 */
	add_action( 'woocommerce_load_shipping_methods', 'woocommerce_register_table_rates' );

	function woocommerce_register_table_rates( $package ) {
		global $woocommerce;

		// Register the main class
		woocommerce_register_shipping_method( 'WC_Shipping_Table_Rate' );

		if ( ! $package ) return;

		// Get zone for package
		$zone = woocommerce_get_shipping_zone( $package );

		if ( defined('WP_DEBUG') && WP_DEBUG == true )
			$woocommerce->add_message( 'Customer matched shipping zone <strong>' . $zone->zone_name . '</strong> (#' . $zone->zone_id . ')' );

		if ( $zone->exists() ) {
			// Register zone methods
			$zone->register_shipping_methods();
		}
	}

	/**
	 * Callback function for loading an instance of this method
	 *
	 * @access public
	 * @param mixed $instance
	 * @param mixed $title
	 * @return WC_Shipping_Table_Rate
	 */
	function woocommerce_get_shipping_method_table_rate( $instance = false ) {
		return new WC_Shipping_Table_Rate( $instance );
	}

}