<?php
/**
 * WC_Shipping_Zone class.
 *
 * Controls a single zone, loads shipping methods, and registers them for use.
 */
class WC_Shipping_Zone {

    var $zone_id;
    var $zone_name;
    var $zone_enabled;
    var $zone_type;
    var $zone_order;
    var $shipping_methods = array();
    var $exists = false;

    /**
     * __construct function.
     *
     * @access public
     */
    function __construct( $zone_id ) {

        // Define class variables
        $this->zone_id = $zone_id;
        $this->init();

        // Find shipping methods for this zone
        $this->find_shipping_methods();
    }

    /**
     * init function.
     *
     * @access public
     * @return void
     */
    function init() {
	    global $wpdb;

	    if ( $this->zone_id > 0 ) {

		    $zone = $wpdb->get_row( $wpdb->prepare( "
				SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zones
				WHERE zone_id = %d LIMIT 1
			", $this->zone_id ) );

			if ( $zone ) {
				$this->zone_name = $zone->zone_name;
				$this->zone_enabled = $zone->zone_enabled;
				$this->zone_type = $zone->zone_type;
				$this->zone_order = $zone->zone_order;
				$this->exists = true;
			}

		} else {

			$this->zone_name = __('Everywhere else', 'wc_shipping_zones');
			$this->zone_enabled = 1;
			$this->zone_type = '';
			$this->zone_order = '';
			$this->exists = true;

		}
    }

    /**
     * exists function.
     *
     * @access public
     * @return void
     */
    function exists() {
	    return $this->exists;
    }

	/**
	 * load_shipping_methods function.
	 *
	 * @access public
	 * @return void
	 */
	function find_shipping_methods() {
		global $wpdb;

		$zone_methods = $wpdb->get_results( $wpdb->prepare( "
			SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zone_shipping_methods
			WHERE zone_id = %s
			ORDER BY shipping_method_order ASC
		", $this->zone_id ) );

		foreach ( $zone_methods as $method ) {

			$class_callback = 'woocommerce_get_shipping_method_' . $method->shipping_method_type;

			// Add this zone method to the other shipping methods
			$this->shipping_methods[] = array(
				'number' 	=> $method->shipping_method_id,		// Instance number for the method
				'callback'	=> $class_callback					// Callback function to init the method class
			);

		}

	}

	/**
	 * register_shipping_methods function.
	 *
	 * @access public
	 * @return void
	 */
	function register_shipping_methods() {
		global $woocommerce;

		foreach ( $this->shipping_methods as $shipping_method ) {

			if ( is_callable( $shipping_method['callback'] ) ) {

				$method = call_user_func( $shipping_method['callback'], $shipping_method['number'] );

				if ( $method->enabled == 'yes' ) {
					woocommerce_register_shipping_method( $method );

					if ( defined('WP_DEBUG') && WP_DEBUG == true )
						$woocommerce->add_message( 'Registering shipping method instance <strong>' . $method->title . '</strong> (#' . $method->instance_id . ')' );
				} else {
					if ( defined('WP_DEBUG') && WP_DEBUG == true )
						$woocommerce->add_message( 'Shipping method instance <strong>' . $method->title . '</strong> (#' . $method->instance_id . ') is disabled' );
				}

			}

		}

	}

	/**
	 * add_shipping_method function.
	 *
	 * @access public
	 * @param mixed $type
	 * @return void
	 */
	function add_shipping_method( $type ) {
		global $wpdb;

		if ( ! $type ) return;

		$wpdb->insert(
			$wpdb->prefix . 'woocommerce_shipping_zone_shipping_methods',
			array(
				'shipping_method_type'	=> $type,
				'zone_id' 				=> $this->zone_id,
				'shipping_method_order'	=> 0
			),
			array(
				'%s',
				'%d',
				'%d'
			)
		);

		return $wpdb->insert_id;
	}


	/**
	 * delete_shipping_method function.
	 *
	 * @access public
	 * @param mixed $id
	 * @return void
	 */
	function delete_shipping_method( $id ) {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_shipping_zone_shipping_methods WHERE shipping_method_id = %d", $id ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_shipping_table_rates WHERE shipping_method_id = %d", $id ) );
		delete_option( 'woocommerce_table_rate_priorities_' . $id );
		delete_option( 'woocommerce_table_rate_default_priority_' . $id );

		return true;
	}

}