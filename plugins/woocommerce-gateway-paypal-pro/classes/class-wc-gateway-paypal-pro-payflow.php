<?php

/**
 * WC_Gateway_PayPal_Pro_PayFlow class.
 *
 * @extends WC_Payment_Gateway
 */
class WC_Gateway_PayPal_Pro_PayFlow extends WC_Payment_Gateway {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {

		$this->id					= 'paypal_pro_payflow';
		$this->method_title 		= __( 'PayPal Pro PayFlow', 'wc_paypal_pro' );
		$this->method_description 	= __( 'PayPal Pro PayFlow Edition works by adding credit card fields on the checkout and then sending the details to PayPal for verification.', 'wc_paypal_pro' );
		$this->icon 				= WP_PLUGIN_URL . "/" . plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/assets/images/cards.png';
		$this->has_fields 			= true;
		$this->liveurl				= 'https://payflowpro.paypal.com';
		$this->testurl				= 'https://pilot-payflowpro.paypal.com';
		$this->allowed_currencies   = apply_filters( 'woocommerce_paypal_pro_allowed_currencies', array( 'USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD' ) );

		// Load the form fields
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Get setting values
		$this->title           = $this->settings['title'];
		$this->description     = $this->settings['description'];
		$this->enabled         = $this->settings['enabled'];

		$this->paypal_vendor   = $this->settings['paypal_vendor'];
		$this->paypal_partner  = ! empty( $this->settings['paypal_partner'] ) ? $this->settings['paypal_partner'] : 'PayPal';
		$this->paypal_password = $this->settings['paypal_password'];
		$this->paypal_user     = ! empty( $this->settings['paypal_user'] ) ? $this->settings['paypal_user'] : $this->paypal_vendor;

		$this->testmode        = $this->settings['testmode'];

		add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

		/* 1.6.6 */
		add_action( 'woocommerce_update_options_payment_gateways', array( $this, 'process_admin_options' ) );

		/* 2.0.0 */
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * payment_scripts function.
	 *
	 * @access public
	 */
	function payment_scripts() {

		if ( ! is_checkout() )
			return;

		wp_enqueue_style( 'wc-paypal-pro', plugins_url( 'assets/css/checkout.css', dirname( __FILE__ ) ) );
		wp_enqueue_script( 'card-type-detection', plugins_url( 'assets/js/card-type-detection.min.js', dirname( __FILE__ ) ), 'jquery', '1.0.0', true );
	}

	/**
     * Initialise Gateway Settings Form Fields
     */
    function init_form_fields() {

    	$this->form_fields = array(
			'enabled'         => array(
							'title'       => __( 'Enable/Disable', 'wc_paypal_pro' ),
							'label'       => __( 'Enable PayPal Pro Payflow Edition', 'wc_paypal_pro' ),
							'type'        => 'checkbox',
							'description' => '',
							'default'     => 'no'
						),
			'title'           => array(
							'title'       => __( 'Title', 'wc_paypal_pro' ),
							'type'        => 'text',
							'description' => __( 'This controls the title which the user sees during checkout.', 'wc_paypal_pro' ),
							'default'     => __( 'Credit card (PayPal)', 'wc_paypal_pro' )
						),
			'description'     => array(
							'title'       => __( 'Description', 'wc_paypal_pro' ),
							'type'        => 'textarea',
							'description' => __( 'This controls the description which the user sees during checkout.', 'wc_paypal_pro' ),
							'default'     => __( 'Pay with your credit card.', 'wc_paypal_pro' )
						),
			'testmode'        => array(
							'title'       => __( 'Test Mode', 'wc_paypal_pro' ),
							'label'       => __( 'Enable PayPal Sandbox/Test Mode', 'wc_paypal_pro' ),
							'type'        => 'checkbox',
							'description' => __( 'Place the payment gateway in development mode.', 'wc_paypal_pro' ),
							'default'     => 'no'
						),
			'paypal_vendor'   => array(
							'title'       => __( 'PayPal Vendor', 'wc_paypal_pro' ),
							'type'        => 'text',
							'description' => __( 'Your merchant login ID that you created when you registered for the account.', 'wc_paypal_pro' ),
							'default'     => ''
						),
			'paypal_password' => array(
							'title'       => __( 'PayPal Password', 'wc_paypal_pro' ),
							'type'        => 'password',
							'description' => __( 'The password that you defined while registering for the account.', 'wc_paypal_pro' ),
							'default'     => ''
						),
			'paypal_user'     => array(
							'title'       => __( 'PayPal User', 'wc_paypal_pro' ),
							'type'        => 'text',
							'description' => __( 'If you set up one or more additional users on the account, this value is the ID
of the user authorized to process transactions. Otherwise, leave this field blank.', 'wc_paypal_pro' ),
							'default'     => ''
						),
			'paypal_partner'  => array(
							'title'       => __( 'PayPal Partner', 'wc_paypal_pro' ),
							'type'        => 'text',
							'description' => __( 'The ID provided to you by the authorized PayPal Reseller who registered you
for the Payflow SDK. If you purchased your account directly from PayPal, use PayPal or leave blank.', 'wc_paypal_pro' ),
							'default'     => 'PayPal'
						),
			);
    }

	/**
     * Check if this gateway is enabled and available in the user's country
     *
     * This method no is used anywhere??? put above but need a fix below
     */
	function is_available() {
		global $woocommerce;

		if ( $this->enabled == "yes" ) {

			if ( ! is_ssl() && $this->testmode == "no" )
				return false;

			// Currency check
			if ( ! in_array( get_option('woocommerce_currency'), $this->allowed_currencies ) )
				return false;

			// Required fields check
			if ( ! $this->paypal_vendor || ! $this->paypal_password )
				return false;

			return true;
		}

		return false;
	}

	/**
     * Process the payment
     */
	function process_payment( $order_id ) {
		global $woocommerce;

		if ( ! session_id() )
			session_start();

		$order = new WC_Order( $order_id );

		$card_number = ! empty( $_POST['paypal_pro_payflow_card_number']) ? str_replace( array( ' ', '-' ), '', woocommerce_clean( $_POST['paypal_pro_payflow_card_number'] ) ) : '';
		$card_csc    = ! empty( $_POST['paypal_pro_payflow_card_csc']) ? woocommerce_clean( $_POST['paypal_pro_payflow_card_csc'] ) : '';
		$card_exp    = ! empty( $_POST['paypal_pro_payflow_card_expiration']) ? woocommerce_clean( $_POST['paypal_pro_payflow_card_expiration'] ) : '';

		// Do payment with paypal
		return $this->do_payment( $order, $card_number, $card_exp, $card_csc );
	}

	/**
	 * do_payment function.
	 *
	 * @access public
	 * @param mixed $order
	 * @param mixed $card_number
	 * @param mixed $card_exp
	 * @param mixed $card_csc
	 * @param string $centinelPAResStatus (default: '')
	 * @param string $centinelEnrolled (default: '')
	 * @param string $centinelCavv (default: '')
	 * @param string $centinelEciFlag (default: '')
	 * @param string $centinelXid (default: '')
	 * @return void
	 */
	function do_payment( $order, $card_number, $card_exp, $card_csc, $centinelPAResStatus = '', $centinelEnrolled = '', $centinelCavv = '', $centinelEciFlag = '', $centinelXid = '' ) {

		global $woocommerce;

		// Send request to paypal
		try {
			$url = $this->testmode == 'yes' ? $this->testurl : $this->liveurl;

			$post_data = array();

			$post_data['USER']     = $this->paypal_user;
			$post_data['VENDOR']   = $this->paypal_vendor;
			$post_data['PARTNER']  = $this->paypal_partner;
			$post_data['PWD']      = trim( $this->paypal_password );
			$post_data['TENDER']   = 'C'; // Credit card
			$post_data['TRXTYPE']  = 'S'; // Sale
			$post_data['ACCT']     = $card_number; // Credit Card
			$post_data['EXPDATE']  = $card_exp; //MMYY
			$post_data['AMT']      = $order->get_total(); // Order total
			$post_data['CURRENCY'] = get_option('woocommerce_currency'); // Currency code
			$post_data['CUSTIP']   = $this->get_user_ip(); // User IP Address
			$post_data['CVV2']     = $card_csc; // CVV code
			$post_data['EMAIL']    = $order->billing_email;
			$post_data['INVNUM']   = $order->get_order_number();

			/* Send Item details */
			$item_loop = 0;

			if ( sizeof( $order->get_items() ) > 0 ) {

				$ITEMAMT = 0;

				foreach ( $order->get_items() as $item ) {
					$_product = $order->get_product_from_item( $item );
					if ( $item['qty'] ) {
						$post_data[ 'L_NAME' . $item_loop ] = $item['name'];
						$post_data[ 'L_COST' . $item_loop ] = $order->get_item_total( $item, true );
						$post_data[ 'L_QTY' . $item_loop ]  = $item['qty'];

						if ( $_product->get_sku() )
							$post_data[ 'L_SKU' . $item_loop ] = $_product->get_sku();

						$ITEMAMT += $order->get_item_total( $item, true ) * $item['qty'];

						$item_loop++;
					}
				}

				// Shipping
				if ( ( $order->get_shipping() + $order->get_shipping_tax() ) > 0 ) {
					$post_data[ 'L_NAME' . $item_loop ] = 'Shipping';
					$post_data[ 'L_DESC' . $item_loop ] = 'Shipping and shipping taxes';
					$post_data[ 'L_COST' . $item_loop ] = $order->get_shipping() + $order->get_shipping_tax();
					$post_data[ 'L_QTY' . $item_loop ]  = 1;

					$ITEMAMT += $order->get_shipping() + $order->get_shipping_tax();

					$item_loop++;
				}

				// Discount
				if ( $order->get_order_discount() > 0 ) {
					$post_data[ 'L_NAME' . $item_loop ] = 'Order Discount';
					$post_data[ 'L_DESC' . $item_loop ] = 'Discounts after tax';
					$post_data[ 'L_COST' . $item_loop ] = '-' . $order->get_order_discount();
					$post_data[ 'L_QTY' . $item_loop ]  = 1;

					$item_loop++;
				}

				// Fix rounding
				if ( $ITEMAMT < $order->get_total() ) {
					$post_data[ 'L_NAME' . $item_loop ] = 'Rounding amendment';
					$post_data[ 'L_DESC' . $item_loop ] = 'Correction if rounding is off (this can happen with tax inclusive prices)';
					$post_data[ 'L_COST' . $item_loop ] = $order->get_total() - $ITEMAMT;
					$post_data[ 'L_QTY' . $item_loop ]  = 1;
				}

				$post_data[ 'ITEMAMT' ] = $order->get_total();
			}

			$post_data['ORDERDESC']      = 'Order ' . $order->get_order_number() . ' on ' . get_bloginfo( 'name' );
			$post_data['FIRSTNAME']      = $order->billing_first_name;
			$post_data['LASTNAME']       = $order->billing_last_name;
			$post_data['STREET']         = $order->billing_address_1 . ' ' . $order->billing_address_2;
			$post_data['CITY']           = $order->billing_city;
			$post_data['STATE']          = $order->billing_state;
			$post_data['COUNTRY']        = $order->billing_country;
			$post_data['ZIP']            = $order->billing_postcode;

			if ( $order->shipping_address_1 ) {
				$post_data['SHIPTOSTREET']  = $order->shipping_address_1 . ' ' . $order->shipping_address_2;
				$post_data['SHIPTOCITY']    = $order->shipping_city;
				$post_data['SHIPTOSTATE']   = $order->shipping_state;
				$post_data['SHIPTOCOUNTRY'] = $order->shipping_country;
				$post_data['SHIPTOZIP']     = $order->shipping_postcode;
			}

			$response = wp_remote_post( $url, array(
   				'method'		=> 'POST',
    			'body' 			=> apply_filters( 'wc_paypal_pro_payflow_request', $post_data, $order ),
    			'timeout' 		=> 70,
    			'sslverify' 	=> false,
    			'user-agent' 	=> 'WooCommerce'
			));

			if ( is_wp_error( $response ) )
				throw new Exception( __( 'There was a problem connecting to the payment gateway.', 'wc_paypal_pro' ) );

			if ( empty( $response['body'] ) )
				throw new Exception( __( 'Empty Paypal response.', 'wc_paypal_pro' ) );

			parse_str( $response['body'], $parsed_response );

			if ( isset( $parsed_response['RESULT'] ) && $parsed_response['RESULT'] == 0 ) {

				// Add order note
				$order->add_order_note( sprintf( __( 'PayPal Pro payment completed (PNREF: %s)', 'wc_paypal_pro' ), $parsed_response['PNREF'] ) );

				// Payment complete
				$order->payment_complete();

				// Remove cart
				$woocommerce->cart->empty_cart();

				// Return thank you page redirect
				return array(
					'result' 	=> 'success',
					'redirect'	=> add_query_arg( 'key', $order->order_key, add_query_arg( 'order', $order->id, get_permalink( get_option( 'woocommerce_thanks_page_id' ) ) ) )
				);

			} else {

				// Payment failed :(
				$order->update_status( 'failed', __('PayPal Pro payment failed. Payment was rejected due to an error: ', 'wc_paypal_pro' ) . '(' . $parsed_response['RESULT'] . ') ' . '"' . $parsed_response['RESPMSG'] . '"' );

				$woocommerce->add_error( __( 'Payment error:', 'wc_paypal_pro' ) . ' ' . $parsed_response['RESPMSG'] );
				return;
			}

		} catch( Exception $e ) {
			$woocommerce->add_error( __('Connection error:', 'wc_paypal_pro' ) . ': "' . $e->getMessage() . '"' );
			return;
		}
	}

	/**
     * Payment form on checkout page
     */
	function payment_fields() {
		global $woocommerce;

		if ( $this->description ) {
			echo '<p>';
			if ( $this->testmode == 'yes' )
				echo __('TEST MODE/SANDBOX ENABLED', 'wc_paypal_pro') . ' ';
			echo $this->description;
			echo '</p>';
		}
		?>
		<fieldset class="paypal_pro_credit_card_form">
			<p class="form-row form-row-wide validate-required paypal_pro_payflow_card_number_wrap">
				<label for="paypal_pro_payflow_card_number"><?php _e( "Card number", "wc_paypal_pro" ) ?></label>
				<input type="text" class="input-text" name="paypal_pro_payflow_card_number" id="paypal_pro_payflow_card_number" pattern="[0-9]{12,19}" />
				<span id="paypal_pro_payflow_card_type_image"></span>
			</p>
			<p class="form-row form-row-first validate-required">
				<label for="paypal_pro_payflow_card_expiration"><?php _e( "Expiry date <small>(MMYY)</small>", "wc_paypal_pro" ) ?></label>
				<input type="text" class="input-text" placeholder="MMYY" name="paypal_pro_payflow_card_expiration" id="paypal_pro_payflow_card_expiration" size="4" maxlength="4" max="1299" min="0100" pattern="[0-9]+" />
			</p>
			<p class="form-row form-row-last validate-required">
				<label for="paypal_pro_payflow_card_csc"><?php _e( "Card security code", "wc_paypal_pro" ) ?></label>
				<input type="text" class="input-text" id="paypal_pro_payflow_card_csc" name="paypal_pro_payflow_card_csc" maxlength="4" size="4" pattern="[0-9]+" />
			</p>
			<div class="clear"></div>
			<?php /*<p class="form-row form-row-wide">
				<label for="paypal_pro_payflow_card_type"><?php _e( "Card type", 'wc_paypal_pro' ) ?></label>
				<select id="paypal_pro_payflow_card_type" name="paypal_pro_payflow_card_type" class="woocommerce-select">
					<?php foreach ( $available_cards as $card => $label ) : ?>
								<option value="<?php echo $card ?>"><?php echo $label; ?></options>
					<?php endforeach; ?>
					<option value="other"><?php _e( 'Other', 'woocommerce' ); ?></options>
				</select>
			</p>*/ ?>
		</fieldset>
		<?php

		$woocommerce->add_inline_js( "
			/*jQuery('body').bind('updated_checkout', function() {
				jQuery('#paypal_pro_payflow_card_type').parent().hide(); // Use JS detection if JS enabled
			});*/

			jQuery('form.checkout, #order_review').on( 'keyup change blur', '#paypal_pro_payflow_card_number', function() {
				var csc = jQuery('#paypal_pro_payflow_card_csc').parent();
				var card_number = jQuery('#paypal_pro_payflow_card_number').val();

				jQuery('#paypal_pro_payflow_card_type_image').attr('class', '');

				if ( is_valid_card( card_number ) ) {

					var card_type = get_card_type( card_number );

					if ( card_type ) {
						jQuery('#paypal_pro_payflow_card_type_image').addClass( card_type );

						if ( card_type == 'visa' || card_type == 'amex' || card_type == 'discover' || card_type == 'mastercard' ) {
							csc.show();
						} else {
							csc.hide();
						}

						//jQuery('#paypal_pro_payflow_card_type').val(card_type);
					} else {
						//jQuery('#paypal_pro_payflow_card_type').val('other');
					}

					jQuery('#paypal_pro_payflow_card_number').parent().addClass('woocommerce-validated').removeClass('woocommerce-invalid');
				} else {
					jQuery('#paypal_pro_payflow_card_number').parent().removeClass('woocommerce-validated').addClass('woocommerce-invalid');
				}
			}).change();
		" );
	}


	/**
     * Get user's IP address
     */
	function get_user_ip() {
		return ! empty( $_SERVER['HTTP_X_FORWARD_FOR'] ) ? $_SERVER['HTTP_X_FORWARD_FOR'] : $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * clear_centinel_session function.
	 *
	 * @access public
	 * @return void
	 */
	function clear_centinel_session() {
        unset( $_SESSION['Message'] );
        foreach ( $_SESSION as $key => $value ) {
            if ( preg_match( "/^Centinel_.*/", $key ) > 0 ) {
                unset( $_SESSION[ $key ] );
            }
        }
    }
}