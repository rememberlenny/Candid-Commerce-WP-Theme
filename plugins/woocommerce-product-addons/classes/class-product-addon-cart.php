<?php

/**
 * Product_Addon_cart class.
 */
class Product_Addon_Cart {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {

		// Add to cart
		add_filter( 'woocommerce_add_cart_item', array( $this, 'add_cart_item' ), 10, 1 );

		// Load cart data per page load
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 10, 2 );

		// Get item data to display
		add_filter( 'woocommerce_get_item_data', array( $this, 'get_item_data' ), 10, 2 );

		// Add item data to the cart
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 10, 2 );

		// Validate when adding to cart
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_add_cart_item' ), 10, 3 );

		// Add meta to order
		add_action( 'woocommerce_order_item_meta', array( $this, 'order_item_meta' ), 10, 2 );

		// Add meta to order 2.0
		add_action( 'woocommerce_add_order_item_meta', array( $this, 'order_item_meta_2' ), 10, 2 );
	}

	/**
	 * add_cart_item function.
	 *
	 * @access public
	 * @param mixed $cart_item
	 * @return void
	 */
	function add_cart_item( $cart_item ) {

		// Adjust price if addons are set
		if ( ! empty( $cart_item['addons'] ) ) {

			$extra_cost = 0;

			foreach ( $cart_item['addons'] as $addon )
				if ( $addon['price'] > 0 )
					$extra_cost += $addon['price'];

			$cart_item['data']->adjust_price( $extra_cost );
		}

		return $cart_item;
	}

	/**
	 * get_cart_item_from_session function.
	 *
	 * @access public
	 * @param mixed $cart_item
	 * @param mixed $values
	 * @return void
	 */
	function get_cart_item_from_session( $cart_item, $values ) {

		if ( ! empty( $values['addons'] ) ) {
			$cart_item['addons'] = $values['addons'];
			$cart_item = $this->add_cart_item( $cart_item );
		}

		return $cart_item;
	}

	/**
	 * get_item_data function.
	 *
	 * @access public
	 * @param mixed $other_data
	 * @param mixed $cart_item
	 * @return void
	 */
	function get_item_data( $other_data, $cart_item ) {

		if ( ! empty( $cart_item['addons'] ) ) {

			foreach ( $cart_item['addons'] as $addon ) {

				$name = $addon['name'];

				if ( $addon['price'] > 0 && apply_filters( 'woocommerce_addons_add_price_to_name', '__return_true' ) )
					$name .= ' (' . woocommerce_price( $addon['price'] ) . ')';

				$other_data[] = array(
					'name'    => $name,
					'value'   => $addon['value'],
					'display' => isset( $addon['display'] ) ? $addon['display'] : ''
				);
			}
		}

		return $other_data;
	}

	/**
	 * add_cart_item_data function.
	 *
	 * @access public
	 * @param mixed $cart_item_meta
	 * @param mixed $product_id
	 * @return void
	 */
	function add_cart_item_data( $cart_item_meta, $product_id ) {
		global $woocommerce;

		$product_addons = get_product_addons( $product_id );

		$cart_item_meta['addons'] = array();

		if ( ! empty( $product_addons ) && is_array( $product_addons ) && sizeof( $product_addons ) > 0 ) {
			foreach ( $product_addons as $addon ) {

				if ( empty( $addon['name'] ) )
					continue;

				switch ( $addon['type'] ) {
					case "checkbox" :
					case "radiobutton" :

						// Posted var = name, value = label
						$posted = isset( $_POST[ 'addon-' . sanitize_title( $addon['name'] ) ] ) ? $_POST[ 'addon-' . sanitize_title( $addon['name'] ) ] : '';

						if ( is_array( $posted ) )
							$posted = array_map( 'stripslashes', $posted );
						else
							$posted = stripslashes( $posted );

						if ( empty( $posted ) )
							continue;

						foreach ( $addon['options'] as $option ) {

							if ( array_search( sanitize_title( $option['label'] ), $posted ) !== FALSE ) {

								// Set
								$cart_item_meta['addons'][] = array(
									'name' 		=> esc_attr( $addon['name'] ),
									'value'		=> esc_attr( $option['label'] ),
									'price' 	=> esc_attr( $option['price'] )
								);
							}
						}

					break;
					case "select" :

						// Posted var = name, value = label
						$posted = isset( $_POST[ 'addon-' . sanitize_title( $addon['name'] ) ] ) ? $_POST[ 'addon-' . sanitize_title( $addon['name'] ) ] : '';

						if ( is_array( $posted ) )
							$posted = array_map( 'stripslashes', $posted );
						else
							$posted = stripslashes( $posted );

						if ( empty( $posted ) )
							continue;

						$chosen_option = '';

						$loop = 0;

						foreach ( $addon['options'] as $option ) {
							$loop++;
							if ( sanitize_title( $option['label'] . '-' . $loop ) == $posted ) {
								$chosen_option = $option;
								break;
							}
						}

						if ( ! $chosen_option )
							continue;

						$cart_item_meta['addons'][] = array(
							'name' 		=> esc_attr( $addon['name'] ),
							'value'		=> esc_attr( $chosen_option['label'] ),
							'price' 	=> esc_attr( $chosen_option['price'] )
						);

					break;
					case "custom" :
					case "custom_textarea" :
					case "custom_price" :

						// Posted var = label, value = custom
						foreach ( $addon['options'] as $option ) {

							$posted = isset( $_POST[ 'addon-' . sanitize_title( $addon['name'] ) . '-' . sanitize_title( $option['label'] ) ] ) ? $_POST[ 'addon-' . sanitize_title( $addon['name'] ) . '-' . sanitize_title( $option['label'] ) ] : '';

							if ( is_array( $posted ) )
								$posted = array_map( 'stripslashes', $posted );
							else
								$posted = stripslashes( $posted );

							if ( empty( $posted ) )
								continue;

							$label  = ! empty( $option['label'] ) ? trim( $option['label'] ) : trim( $addon['name'] );

							if ( $addon['type'] == "custom_price" ) {
								$price = floatval( woocommerce_clean( $posted ) );

								if ( $price >= 0 ) {
									$cart_item_meta['addons'][] = array(
										'name' 		=> esc_attr( $label ),
										'value'		=> esc_attr( strip_tags( $price ) ),
										'price' 	=> esc_attr( $price ),
										'display'	=> esc_attr( strip_tags( woocommerce_price( $price ) ) ),
									);
								}
							} else {
								$cart_item_meta['addons'][] = array(
									'name' 		=> esc_attr( $label ),
									'value'		=> esc_attr( woocommerce_clean( $posted ) ),
									'price' 	=> esc_attr( $option['price'] )
								);
							}
						}

					break;
					case "file_upload" :

						include_once( ABSPATH . 'wp-admin/includes/file.php' );
						include_once( ABSPATH . 'wp-admin/includes/media.php' );

						add_filter( 'upload_dir',  array( $this, 'upload_dir' ) );

						foreach ( $addon['options'] as $option ) {

							$field_name = 'addon-' . sanitize_title( $addon['name'] ) . '-' . sanitize_title( $option['label'] );

							if ( ! empty( $_FILES[ $field_name ] ) && ! empty( $_FILES[ $field_name ]['name'] ) ) {

								$file   = $_FILES[ $field_name ];
								$upload = wp_handle_upload( $file, array( 'test_form' => false ) );
								$label  = ! empty( $option['label'] ) ? trim( $option['label'] ) : trim( $addon['name'] );

								if ( empty( $upload['error'] ) && ! empty( $upload['file'] ) ) {

						            $file_path = $upload['url'];

						            $cart_item_meta['addons'][] = array(
										'name' 		=> esc_attr( $label ),
										'value'		=> esc_attr( woocommerce_clean( $file_path ) ),
										'display'	=> esc_attr( basename( woocommerce_clean( $file_path ) ) ),
										'price' 	=> esc_attr( $option['price'] )
									);

						    	} else {
						    		$woocommerce->add_error( $upload['error'] );
						    	}
							}
						}

						remove_filter( 'upload_dir',  array( $this, 'upload_dir' ) );
					break;
				}
			}
		}

		return $cart_item_meta;
	}

	/**
	 * upload_dir function.
	 *
	 * @access public
	 * @param mixed $pathdata
	 * @return void
	 */
	function upload_dir( $pathdata ) {
		global $woocommerce;
		$subdir             = '/product_addons_uploads/' . md5( $woocommerce->session->get_customer_id() );
	 	$pathdata['path']   = str_replace( $pathdata['subdir'], $subdir, $pathdata['path'] );
	 	$pathdata['url']    = str_replace( $pathdata['subdir'], $subdir, $pathdata['url'] );
		$pathdata['subdir'] = str_replace( $pathdata['subdir'], $subdir, $pathdata['subdir'] );
		return $pathdata;
	}

	/**
	 * validate_add_cart_item function.
	 *
	 * @access public
	 * @param mixed $passed
	 * @param mixed $product_id
	 * @param mixed $qty
	 * @return void
	 */
	function validate_add_cart_item( $passed, $product_id, $qty ) {
		global $woocommerce;

		$product_addons = get_product_addons( $product_id );

		if ( ! empty( $product_addons ) && is_array( $product_addons ) && sizeof( $product_addons ) > 0 ) {
			foreach ( $product_addons as $addon ) {
				if ( empty( $addon['name'] ) || empty( $addon['required'] ) )
					continue;

				if ( $addon['required'] ) {

					switch ( $addon['type'] ) {
						case "checkbox" :
						case "radiobutton" :
						case "select" :

							$posted = isset( $_POST[ 'addon-' . sanitize_title( $addon['name'] ) ] ) ? $_POST[ 'addon-' . sanitize_title( $addon['name'] ) ] : '';

							if ( ! $posted || sizeof( $posted ) == 0 )
								$passed = false;

						break;
						case "custom" :
						case "custom_price" :
						case "custom_textarea" :

							foreach ( $addon['options'] as $option ) {

								$posted = isset( $_POST[ 'addon-' . sanitize_title( $addon['name'] ) . '-' . sanitize_title( $option['label'] ) ] ) ? $_POST[ 'addon-' . sanitize_title( $addon['name'] ) . '-' . sanitize_title( $option['label'] ) ] : '';

								if ( ! $posted || sizeof( $posted ) == 0 ) {
									$passed = false;
									break;
								}

								if ( $addon['type'] == "custom_price" ) {
									$price = floatval( woocommerce_clean( $posted ) );

									if ( $price < 0 ) {
										$woocommerce->add_error( sprintf( __( 'Invalid price entered for "%s".', 'woocommerce' ), $addon['name'] ) );
										return false;
									}
								}
							}

						break;
						case "file_upload" :

							foreach ( $addon['options'] as $option ) {

								$field_name = 'addon-' . sanitize_title( $addon['name'] ) . '-' . sanitize_title( $option['label'] );

								if ( empty( $_FILES[ $field_name ] ) || empty( $_FILES[ $field_name ]['name'] ) ) {
									$passed = false;
									break;
								}

							}

						break;
					}

					if ( ! $passed ) {
						$woocommerce->add_error( sprintf( __( '"%s" is a required field.', 'woocommerce' ), $addon['name'] ) );
						break;
					}
				}

				// Min max
				if ( $addon['options'] ) {
					foreach ( $addon['options'] as $option ) {
						switch ( $addon['type'] ) {
							case "custom" :
							case "custom_textarea" :

								$posted = isset( $_POST[ 'addon-' . sanitize_title( $addon['name'] ) . '-' . sanitize_title( $option['label'] ) ] ) ? $_POST[ 'addon-' . sanitize_title( $addon['name'] ) . '-' . sanitize_title( $option['label'] ) ] : '';

								if ( ! empty( $option['min'] ) && ! empty( $posted ) && strlen( $posted ) < $option['min'] ) {
									$woocommerce->add_error( sprintf( __( 'The minimum allowed length for "%s - %s" is %s.', 'woocommerce' ), $addon['name'], $option['label'], $option['min'] ) );
									return false;
								}

								if ( ! empty( $option['max'] ) && ! empty( $posted ) && strlen( $posted ) > $option['max'] ) {
									$woocommerce->add_error( sprintf( __( 'The maximum allowed length for "%s - %s" is %s.', 'woocommerce' ), $addon['name'], $option['label'], $option['max'] ) );
									return false;
								}
							break;
							case "custom_price" :

								$posted = isset( $_POST[ 'addon-' . sanitize_title( $addon['name'] ) . '-' . sanitize_title( $option['label'] ) ] ) ? $_POST[ 'addon-' . sanitize_title( $addon['name'] ) . '-' . sanitize_title( $option['label'] ) ] : '';

								if ( ! empty( $option['min'] ) && ! empty( $posted ) && $posted < $option['min'] ) {
									$woocommerce->add_error( sprintf( __( 'The minimum allowed amount for "%s - %s" is %s.', 'woocommerce' ), $addon['name'], $option['label'], $option['min'] ) );
									return false;
								}

								if ( ! empty( $option['max'] ) && ! empty( $posted ) && $posted > $option['max'] ) {
									$woocommerce->add_error( sprintf( __( 'The maximum allowed amount for "%s - %s" is %s.', 'woocommerce' ), $addon['name'], $option['label'], $option['max'] ) );
									return false;
								}

							break;
						}
					}
				}

				do_action( 'woocommerce_validate_posted_addon_data', $addon );
			}
		}

		return $passed;
	}

	/**
	 * order_item_meta function.
	 *
	 * @access public
	 * @param mixed $item_meta
	 * @param mixed $cart_item
	 * @return void
	 */
	function order_item_meta( $item_meta, $cart_item ) {

		// Add the fields
		if ( ! empty( $cart_item['addons'] ) ) {
			foreach ( $cart_item['addons'] as $addon ) {

				$name = $addon['name'];

				if ( $addon['price'] > 0 && apply_filters( 'woocommerce_addons_add_price_to_name', true ) )
					$name .= ' (' . strip_tags( woocommerce_price( $addon['price'] ) ) . ')';

				$item_meta->add( $name, $addon['value'] );
			}
		}
	}

	/**
	 * order_item_meta_2 function.
	 *
	 * @access public
	 * @param mixed $item_id
	 * @param mixed $values
	 * @return void
	 */
	function order_item_meta_2( $item_id, $values ) {
		if ( function_exists('woocommerce_add_order_item_meta') ) {

			if ( ! empty( $values['addons'] ) ) {
				foreach ( $values['addons'] as $addon ) {

					$name = $addon['name'];

					if ( $addon['price'] > 0 && apply_filters( 'woocommerce_addons_add_price_to_name', true ) )
						$name .= ' (' . strip_tags(woocommerce_price($addon['price'])) . ')';

					woocommerce_add_order_item_meta( $item_id, $name, $addon['value'] );
				}
			}

		}
	}
}