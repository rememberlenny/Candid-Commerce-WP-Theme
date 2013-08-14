<?php
	/**
	 * Shipping zone class
	 */
	require_once( 'classes/class-wc-shipping-zone.php' );

	/**
	 * Admin Menu Items
	 */
	add_action( 'admin_menu', 'woocommerce_table_rate_menus', 25 );

	function woocommerce_table_rate_menus() {

		$shipping_zones_page = add_submenu_page( 'woocommerce', __('Shipping Zones', 'wc_shipping_zones'),  __('Shipping Zones', 'wc_shipping_zones') , 'manage_woocommerce', 'shipping_zones', 'woocommerce_shipping_zones_page');

		add_action( 'admin_print_styles-' . $shipping_zones_page, 'woocommerce_admin_css' );
		add_action( 'admin_print_styles-' . $shipping_zones_page, 'woocommerce_shipping_zones_css' );
	}

	/**
	 * The shipping zones admin page
	 */
	function woocommerce_shipping_zones_page() {
		require_once( 'classes/class-wc-admin-shipping-zones.php' );
		$WC_Admin_Shipping_zones = new WC_Admin_Shipping_zones();
		$WC_Admin_Shipping_zones->admin_page();
	}

	function woocommerce_shipping_zones_css() {
		wp_enqueue_script( 'woocommerce_admin' );
		wp_enqueue_script( 'chosen' );
    	wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_style( 'woocommerce_shipping_zone_admin_styles', plugins_url( '/shipping-zones/assets/css/shipping_zones.css', dirname( __FILE__ ) ) );

		do_action( 'woocommerce_shipping_zones_css' );
	}

	/**
	 * AJAX Handlers
	 */
	if ( defined( 'DOING_AJAX' ) ) {
		include_once( 'shipping-zones-ajax.php' );
	}

	/**
	 * woocommerce_get_shipping_zone function.
	 *
	 * @access public
	 * @param array $package
	 * @return WC_Shipping_Zone
	 */
	function woocommerce_get_shipping_zone( $package ) {
		global $woocommerce, $wpdb;

		$country 	= $package['destination']['country'];
		$state 		= $country . ':' . $package['destination']['state'];
		$postcode 	= $package['destination']['postcode'];

		$valid_postcodes 	= array( '*', $postcode );
		$valid_zone_ids		= array();

		// Work out possible valid wildcard postcodes
		$postcode_length	= strlen( $postcode );
		$wildcard_postcode	= $postcode;

		for ( $i = 0; $i < $postcode_length; $i ++ ) {

			$wildcard_postcode = substr( $wildcard_postcode, 0, -1 );

			$valid_postcodes[] = $wildcard_postcode . '*';

		}

		// Query range based postcodes to find matches
		if ( $postcode ) {
			$postcode_ranges = $wpdb->get_results( "
				SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zone_locations
				WHERE location_type = 'postcode' AND location_code LIKE '%-%'
			" );

			if ( $postcode_ranges ) {

				$encoded_postcode = woocommerce_make_numeric_postcode( $postcode );
				$encoded_postcode_len = strlen( $encoded_postcode );

				foreach ( $postcode_ranges as $postcode_range ) {

					$range = array_map( 'trim', explode( '-', $postcode_range->location_code ) );

					if ( sizeof( $range ) != 2 ) continue;

					if ( is_numeric( $range[0] ) && is_numeric( $range[1] ) ) {

						$encoded_postcode = $postcode;
						$min = $range[0];
						$max = $range[1];

					} else {

						$min = woocommerce_make_numeric_postcode( $range[0] );
						$max = woocommerce_make_numeric_postcode( $range[1] );

						$min = str_pad( $min, $encoded_postcode_len, '0' );
						$max = str_pad( $max, $encoded_postcode_len, '9' );

					}

					if ( $encoded_postcode >= $min && $encoded_postcode <= $max )
						$valid_zone_ids[] = $postcode_range->zone_id;

					//echo " Min: $min, Max: $max, PC: $encoded_postcode <br/><br/>";

				}
			}
		}

		// Get matching zones
		$matching_zone = $wpdb->get_var( $wpdb->prepare( "
			SELECT zones.zone_id FROM {$wpdb->prefix}woocommerce_shipping_zones as zones
			LEFT JOIN {$wpdb->prefix}woocommerce_shipping_zone_locations as locations ON zones.zone_id = locations.zone_id
			WHERE
			(
				(
					zone_type = 'countries'
					AND location_type = 'country'
					AND location_code = %s
				)
				OR
				(
					zone_type = 'states'
					AND
					(
						( location_type = 'state' AND location_code = %s )
						OR
						( location_type = 'country' AND location_code = %s )
					)
				)
				OR
				(
					zone_type = 'postcodes'
					AND
					(
						( location_type = 'state' AND location_code = %s )
						OR
						( location_type = 'country' AND location_code = %s )
					)
					AND
					(
						zones.zone_id IN (
							SELECT zone_id FROM {$wpdb->prefix}woocommerce_shipping_zone_locations
							WHERE location_type = 'postcode'
							AND location_code IN ('" . implode( "','", $valid_postcodes ) . "')
							)
						OR zones.zone_id IN ('" . implode( "','", $valid_zone_ids ) . "')
					)
				)
			)
			AND zone_enabled = 1
			ORDER BY zone_order ASC
			LIMIT 1
		", $country, $state, $country, $state, $country ) );

		if ( ! $matching_zone ) $matching_zone = 0; // Default

		return new WC_Shipping_Zone( $matching_zone );
	}

	/**
	 * make_numeric_postcode function.
	 *
	 * Converts letters to numbers so we can do a simple range check on postcodes.
	 *
	 * E.g. PE30 becomes 16050300 (P = 16, E = 05, 3 = 03, 0 = 00)
	 *
	 * @access public
	 * @param mixed $postcode
	 * @return void
	 */
	function woocommerce_make_numeric_postcode( $postcode ) {

		$postcode_length	= strlen( $postcode );

		$letters_to_numbers = array_merge( array( 0 ), range( 'A', 'Z' ) );
		$letters_to_numbers = array_flip( $letters_to_numbers );

		$numeric_postcode = '';

		for ( $i = 0; $i < $postcode_length; $i ++ ) {

			if ( is_numeric( $postcode[ $i ] ) )
				$numeric_postcode .= str_pad( $postcode[ $i ], 2, '0', STR_PAD_LEFT );
			elseif ( isset( $letters_to_numbers[ $postcode[ $i ] ] ) )
				$numeric_postcode .= str_pad( $letters_to_numbers[ $postcode[ $i ] ], 2, '0', STR_PAD_LEFT );
			else
				$numeric_postcode .= '00';
		}

		return $numeric_postcode;
	}
