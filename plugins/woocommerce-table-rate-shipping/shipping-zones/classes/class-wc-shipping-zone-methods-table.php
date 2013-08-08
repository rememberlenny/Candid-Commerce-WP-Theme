<?php
/**
 * WC_Shipping_Zone_Methods_Table class.
 *
 * @extends WP_List_Table
 */
class WC_Shipping_Zone_Methods_Table extends WP_List_Table {

    var $index;
    var $zone_id;

    /**
     * __construct function.
     *
     * @access public
     */
    function __construct(){
        global $status, $page, $woocommerce;

        $this->zone_id = (int) $_GET['zone'];
        $this->index = 0;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'Shipping Method',     //singular name of the listed records
            'plural'    => 'Shipping Methods',    //plural name of the listed records
            'ajax'      => false        		//does this table support ajax?
        ) );

        $woocommerce->add_inline_js("

			jQuery('table.shippingmethods tbody th, table.shippingmethods tbody td').css('cursor','move');

			jQuery('table.shippingmethods tbody').sortable({
				items: 'tr:not(.inline-edit-row)',
				cursor: 'move',
				axis: 'y',
				containment: 'table.shippingmethods',
				scrollSensitivity: 40,
				helper: function(e, ui) {
					ui.children().each(function() { jQuery(this).width(jQuery(this).width()); });
					return ui;
				},
				start: function(event, ui) {
					if ( ! ui.item.hasClass('alternate') ) ui.item.css( 'background-color', '#ffffff' );
					ui.item.children('td,th').css('border-bottom-width','0');
					ui.item.css( 'outline', '1px solid #dfdfdf' );
				},
				stop: function(event, ui) {
					ui.item.removeAttr('style');
					ui.item.children('td,th').css('border-bottom-width','1px');
				},
				update: function(event, ui) {
					jQuery('table.shippingmethods tbody th, table.shippingmethods tbody td').css('cursor','default');
					jQuery('table.shippingmethods tbody').sortable('disable');

					var shipping_method_id = ui.item.find('.check-column input').val();
					var prev_shipping_method_id = ui.item.prev().find('.check-column input').val();
					var next_shipping_method_id = ui.item.next().find('.check-column input').val();

					// show spinner
					ui.item.find('.check-column input').hide().after('<img alt=\"processing\" src=\"images/wpspin_light.gif\" class=\"waiting\" style=\"margin-left: 6px;\" />');

					// go do the sorting stuff via ajax
					jQuery.post( ajaxurl, { action: 'woocommerce_shipping_method_ordering', shipping_method_id: shipping_method_id, prev_shipping_method_id: prev_shipping_method_id, next_shipping_method_id: next_shipping_method_id }, function(response) {
						ui.item.find('.check-column input').show().siblings('img').remove();
						jQuery('table.shippingmethods tbody th, table.shippingmethods tbody td').css('cursor','move');
						jQuery('table.shippingmethods tbody').sortable('enable');
					});

					// fix cell colors
					jQuery( 'table.shippingmethods tbody tr' ).each(function(){
						var i = jQuery('table.shippingmethods tbody tr').index(this);
						if ( i%2 == 0 ) jQuery(this).addClass('alternate');
						else jQuery(this).removeClass('alternate');
					});
				}
			});

        ");
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
        	case 'title' :

        		$title = $item->title;

        		if ( ! $title )
        			$title = ucwords( $item->method_title );

        		return '
        			<strong><a href="' . add_query_arg( 'method', $item->number, add_query_arg( 'zone',  $this->zone_id, admin_url( 'admin.php?page=shipping_zones' ) ) ) . '">' . $title . '</a></strong>
        			<div class="row-actions">
        				<span class="id">ID: ' . $item->instance_id . ' | </span><span><a href="' . add_query_arg( 'method', $item->number, add_query_arg( 'zone',  $this->zone_id, admin_url( 'admin.php?page=shipping_zones' ) ) ) . '">' . __( 'Edit' , 'wc_shipping_zones' ) . '</a> | </span><span class="trash"><a class="submitdelete" href="' . wp_nonce_url( add_query_arg( 'delete_method', $item->number ), 'woocommerce_delete_method' ) . '">' . __( 'Delete', 'wc_shipping_zones' ) . '</a></span>
        			</div>';

        	case 'type' :
        		return $item->method_title;
        	case 'enabled' :
        		return ( $item->enabled == 'yes' ) ? '<img src="' . $woocommerce->plugin_url() . '/assets/images/success.png" alt="yes" width="16px" />' : '&ndash;';
        }
	}

    /**
     * column_cb function.
     *
     * @access public
     * @param mixed $item
     */
    function column_cb( $item ){
    	if ( ! $item->number ) return;
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ 'shipping_method_id',
            /*$2%s*/ $item->number
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
            'title'  	    => __('Method Title', 'wc_shipping_zones'),
            'type'     		=> __('Method Type', 'wc_shipping_zones'),
            'enabled'  		=> __('Enabled', 'wc_shipping_zones'),
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
            'delete'    => __('Delete', 'wc_shipping_zones')
        );
        return $actions;
    }

    /**
     * Process bulk actions
     */
    function process_bulk_action() {
        global $wpdb;

        if ( ! isset( $_POST['shipping_method_id'] ) ) return;

        $items = array_map( 'intval', $_POST['shipping_method_id'] );

        //Detect when a bulk action is being triggeredÉ
        if ( 'delete' === $this->current_action() ) {

        	if ( $items ) foreach ( $items as $id ) {

        		$id = (int) $id;
        		if ( ! $id ) continue;

        		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_shipping_zone_shipping_methods WHERE shipping_method_id = %d", $id ) );
        		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_shipping_table_rates WHERE shipping_method_id = %d", $id ) );
        		delete_option( 'woocommerce_table_rate_priorities_' . $id );
        		delete_option( 'woocommerce_table_rate_default_priority_' . $id );
        	}

            echo '<div class="updated success"><p>' . __( 'Shipping methods deleted', 'wc_shipping_zones' ) . '</p></div>';

        }

    }

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 3.1.0
	 * @access public
	 */
	function no_items() {
		echo '<p>' . __( 'No shipping methods found.', 'wc_shipping_zones' ) . '</p>';
	}

    /**
     * prepare_items function.
     *
     * @access public
     */
    function prepare_items() {
        global $wpdb;

        /**
         * Init column headers
         */
        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

        /**
         * Process bulk actions
         */
        $this->process_bulk_action();

		$shipping_methods = $wpdb->get_results( $wpdb->prepare( "
			SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zone_shipping_methods
			WHERE zone_id = %s
			ORDER BY `shipping_method_order` ASC
		", $this->zone_id ) );

		foreach ( $shipping_methods as $method ) {

			$class_callback = 'woocommerce_get_shipping_method_' . $method->shipping_method_type;

			if ( function_exists( $class_callback ) ) {
				$this->items[] = call_user_func( $class_callback, $method->shipping_method_id );
			}
		}

    }

}