<?php
/**
 * WC_Shipping_Zones_Table class.
 *
 * @extends WP_List_Table
 */
class WC_Shipping_Zones_Table extends WP_List_Table {

    var $index;

    /**
     * __construct function.
     *
     * @access public
     */
    function __construct(){
        global $status, $page;

        $this->index = 0;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'Shipping Zone',     //singular name of the listed records
            'plural'    => 'Shipping Zones',    //plural name of the listed records
            'ajax'      => false        		//does this table support ajax?
        ) );
    }

    /**
     * column_default function.
     *
     * @access public
     * @param mixed $post
     * @param mixed $column_name
     */
    function column_default( $item, $column_name ) {
    	global $wpdb, $woocommerce;

        switch( $column_name ) {
        	case 'zone_name' :
        		$zone_name = '<strong><a href="' . add_query_arg( 'zone', $item->zone_id, admin_url('admin.php?page=shipping_zones') ) . '" class="configure_methods">' . $item->zone_name . '</a></strong> <input type="hidden" class="zone_id" name="zone_id[]" value="' . $item->zone_id . '" />';

        		if ( $item->zone_id > 0 ) {
	        		$zone_name .='
	        		<div class="row-actions">
						<a href="' . add_query_arg( 'edit_zone', $item->zone_id, admin_url( 'admin.php?page=shipping_zones' ) ) . '">' . __( 'Edit', 'wc_shipping_zones' ) . '</a> | <a href="' . add_query_arg( 'zone', $item->zone_id, admin_url('admin.php?page=shipping_zones') ) . '" class="configure_methods">' . __('Configure shipping methods', 'wc_shipping_zones') . '</a>
					</div>';
				} else {
					$zone_name .='
	        		<div class="row-actions">
						<a href="' . add_query_arg( 'zone', $item->zone_id, admin_url('admin.php?page=shipping_zones') ) . '" class="configure_methods">' . __('Configure shipping methods', 'wc_shipping_zones') . '</a>
					</div>';
				}

				return $zone_name;
        	case 'zone_type' :

        		if ( $item->zone_id == 0 )
        			return __( 'Everywhere', 'wc_shipping_zones' );

        		$locations = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zone_locations WHERE zone_id = %s;", $item->zone_id ) );

        		$count = (int) sizeof( $locations );

        		if ( $item->zone_type == 'postcodes' )
        			$count = $count - 1;

        		$locations_prepend = $locations_append = "";
        		$locations_list = array();

        		foreach ( $locations as $location ) {
        			if ( sizeof( $locations_list ) >= 8 ) {
	        			$locations_append = ' ' . sprintf( __( 'and %s others', 'wc_shipping_zones' ), ( $count - 8 ) );
	        			break;
        			}
	        		switch ( $location->location_type ) {
		        		case "country" :
		        		case "state" :

		        			if ( strstr( $location->location_code, ':' ) ) {
		        				$split_code = explode( ':', $location->location_code );
		        				if ( ! isset( $woocommerce->countries->states[ $split_code[0] ][ $split_code[1] ] ) )
		        					continue;
		        				$location_name = $woocommerce->countries->states[ $split_code[0] ][ $split_code[1] ];
		        			} else {
		        				if ( ! isset( $woocommerce->countries->countries[ $location->location_code ] ) )
		        					continue;
		        				$location_name = $woocommerce->countries->countries[ $location->location_code ];
		        			}

		        			if ( $item->zone_type == 'postcodes' ) {
			        			$locations_prepend = sprintf( __( 'Within %s:', 'wc_shipping_zones' ), $location_name ) . ' ';
		        			} else {
			        			$locations_list[] = $location_name;
		        			}
		        			break;
		        		case "postcode" :
		        			$locations_list[] = $location->location_code;
	        		}
        		}

        		switch ( $item->zone_type ) {
	        		case "countries" :
	        			return '<strong>' . __( 'Countries', 'wc_shipping_zones' ) . '</strong><br/>' . $locations_prepend . implode( ', ', $locations_list ) . $locations_append;
	        		case "states" :
	        			return '<strong>' . __( 'Countries and states', 'wc_shipping_zones' ) . '</strong><br/>' . $locations_prepend . implode( ', ', $locations_list ) . $locations_append;
	        		case "postcodes" :
	        			return '<strong>' . __( 'Postcodes', 'wc_shipping_zones' ) . '</strong><br/>' . $locations_prepend . implode( ', ', $locations_list ) . $locations_append;
        		}
        	case 'enabled' :
        		return ( $item->zone_enabled ) ? '<img src="' . $woocommerce->plugin_url() . '/assets/images/success.png" width="16px" alt="yes" />' : '&ndash;';
        	case 'methods' :

        		$output_methods = array();

        		$shipping_methods = $wpdb->get_results( $wpdb->prepare( "
					SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zone_shipping_methods
					WHERE zone_id = %s
					ORDER BY `shipping_method_order` ASC
				", $item->zone_id ) );

				if ( $shipping_methods ) {
					foreach ( $shipping_methods as $method ) {

						$class_callback = 'woocommerce_get_shipping_method_' . $method->shipping_method_type;

						if ( function_exists( $class_callback ) ) {
							$this_method = call_user_func( $class_callback, $method->shipping_method_id );

							$output_methods[] = '<a href="' . add_query_arg( 'method', $method->shipping_method_id, add_query_arg( 'zone', $item->zone_id, admin_url( 'admin.php?page=shipping_zones' ) ) ) . '">' . ( ( $this_method->title ) ? $this_method->title : $this_method->id ) . '</a>';
						}
					}

	        		return implode( ', ', $output_methods );
	        	} else {
		        	return __( 'None', 'wc_shipping_zones' );
	        	}
        }
	}

    /**
     * column_cb function.
     *
     * @access public
     * @param mixed $item
     */
    function column_cb( $item ){
    	if ( ! $item->zone_id ) return;
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ 'zone_id_cb',
            /*$2%s*/ $item->zone_id
        );
    }

    /**
     * get_columns function.
     *
     * @access public
     */
    function get_columns(){
        $columns = array(
            'cb'        	=> '<input type="checkbox" />',
            'zone_name'     => __('Zone name', 'wc_shipping_zones'),
            'zone_type'     => __('Zone type', 'wc_shipping_zones'),
            'enabled'  		=> __('Enabled', 'wc_shipping_zones'),
            'methods'  		=> __('Shipping Methods', 'wc_shipping_zones'),
        );
        return $columns;
    }

    /**
     * get_sortable_columns function.
     *
     * @access public
     */
    function get_sortable_columns() {
        return array();
    }

     /**
     * Get bulk actions
     */
    function get_bulk_actions() {
        $actions = array(
            'disable'   => __('Disable', 'wc_shipping_zones'),
            'enable'    => __('Enable', 'wc_shipping_zones'),
            ''   		=> '------',
            'delete'    => __('Delete', 'wc_shipping_zones')
        );
        return $actions;
    }

    /**
     * Process bulk actions
     */
    function process_bulk_action() {
        global $wpdb;

        if ( ! isset( $_POST['zone_id_cb'] ) ) return;

        $items = array_map( 'intval', $_POST['zone_id_cb'] );

        //Detect when a bulk action is being triggered�
        if ( 'delete' === $this->current_action() ) {

        	if ( $items ) foreach ( $items as $id ) {

        		$id = (int) $id;
        		if ( ! $id ) continue;

        		// Get methods
        		$methods = $wpdb->get_col( $wpdb->prepare( "SELECT shipping_method_id FROM {$wpdb->prefix}woocommerce_shipping_zone_shipping_methods WHERE zone_id = %d", $id ) );

        		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_shipping_zone_locations WHERE zone_id = %d", $id ) );
        		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_shipping_zones WHERE zone_id = %d", $id ) );
        		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_shipping_zone_shipping_methods WHERE zone_id = %d", $id ) );

        		foreach ( $methods as $method ) {

	        		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_shipping_table_rates WHERE shipping_method_id = %d", $method ) );
	        		delete_option( 'woocommerce_table_rate_priorities_' . $method );
	        		delete_option( 'woocommerce_table_rate_default_priority_' . $method );

        		}
        	}

            echo '<div class="updated success"><p>' . __('Shipping zones deleted', 'wc_shipping_zones') . '</p></div>';

        } elseif ( 'enable' === $this->current_action() ) {

        	if ( $items ) foreach ( $items as $id ) {

        		$id = (int) $id;
        		if ( ! $id ) continue;

        		// Update zone
				$wpdb->update(
					$wpdb->prefix . 'woocommerce_shipping_zones',
					array(
						'zone_enabled' => 1
					),
					array( 'zone_id' => $id ),
					array( '%d' ),
					array( '%d' )
				);

        	}

        	echo '<div class="updated success"><p>' . __('Shipping zones enabled', 'wc_shipping_zones') . '</p></div>';

        } elseif ( 'disable' === $this->current_action() ) {

        	if ( $items ) foreach ( $items as $id ) {

        		$id = (int) $id;
        		if ( ! $id ) continue;

        		// Update zone
				$wpdb->update(
					$wpdb->prefix . 'woocommerce_shipping_zones',
					array(
						'zone_enabled' => 0
					),
					array( 'zone_id' => $id ),
					array( '%d' ),
					array( '%d' )
				);

        	}

        	echo '<div class="updated success"><p>' . __('Shipping zones disabled', 'wc_shipping_zones') . '</p></div>';

        }

    }

    /**
     * prepare_items function.
     *
     * @access public
     */
    function prepare_items() {
        global $wpdb;

        //$current_page 		= $this->get_pagenum();
        //$per_page			= empty( $_REQUEST['products_per_page'] ) ? 50 : (int) $_REQUEST['products_per_page'];

        /**
         * Init column headers
         */
        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

        /**
         * Process bulk actions
         */
        $this->process_bulk_action();

        /**
         * Get items

        $max = $wpdb->get_var( "SELECT COUNT( zone_id ) FROM {$wpdb->prefix}woocommerce_shipping_zones;" );

		$this->items = $wpdb->get_results( $wpdb->prepare( "
			SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zones
			ORDER BY `zone_order` ASC LIMIT %d, %d
		", ( $current_page - 1 ) * $per_page, $per_page ) );*/

		$zone_order = 'zone_order';

		$this->items = $wpdb->get_results( "
			SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zones
			ORDER BY 'zone_order' ASC
		" );

		$default = new stdClass();

		$default->zone_id = 0;
		$default->zone_name = __('Default Zone (everywhere else)', 'wc_shipping_zones');
		$default->zone_type = __('All countries', 'wc_shipping_zones');
		$default->zone_enabled = 1;

		$this->items[] = $default;

        /**
         * Pagination

        $this->set_pagination_args( array(
            'total_items' => $max,
            'per_page'    => $per_page,
            'total_pages' => ceil( $max / $per_page )
        ) ); */
    }

}
