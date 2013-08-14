<?php

class WC_Authorize_Net_DPM extends AuthorizeNetDPM {

    /**
     * Generate a sample form for use in a demo Direct Post implementation.
     *
     * @param string $amount                   Amount of the transaction.
     * @param string $fp_sequence              Sequential number(ie. Invoice #)
     * @param string $relay_response_url       The Relay Response URL
     * @param string $api_login_id             Your API Login ID
     * @param string $transaction_key          Your API Tran Key.
     * @param bool   $test_mode                Use the sandbox?
     * @param bool   $prefill                  Prefill sample values(for test purposes).
     *
     * @return string
     */
    public static function getCreditCardForm($amount, $fp_sequence, $relay_response_url, $api_login_id, $transaction_key, $test_mode = true, $prefill = true, $order = '')
    {
        $time = time();
        $fp = self::getFingerprint( $api_login_id, $transaction_key, $amount, $fp_sequence, $time );
        $sim = new AuthorizeNetSIM_Form(
            array(
            'x_amount'        	=> $amount,
            'x_fp_sequence'   	=> $fp_sequence,
            'x_fp_hash'       	=> $fp,
            'x_fp_timestamp'  	=> $time,
            'x_relay_response'	=> "TRUE",
            'x_relay_url'     	=> $relay_response_url,
            'x_login'         	=> $api_login_id,
            'x_test_request'	=> $test_mode ? 'TRUE' : 'FALSE',

            'x_first_name'		=> $order->billing_first_name,
            'x_last_name'		=> $order->billing_last_name,
            'x_company'			=> $order->billing_company,
            'x_address'			=> $order->billing_address_1 . ' ' . $order->billing_address_2,
            'x_city'			=> $order->billing_city,
            'x_state'			=> $order->billing_state,
            'x_zip'				=> $order->billing_postcode,
            'x_country'			=> $order->billing_country,
            'x_phone'			=> $order->billing_phone,
            'x_email'			=> $order->billing_email,

            'x_ship_to_first_name'	=> $order->shipping_first_name,
            'x_ship_to_last_name'	=> $order->shipping_last_name,
            'x_ship_to_company'		=> $order->shipping_company,
            'x_ship_to_address'		=> $order->shipping_address_1 . ' ' . $order->shipping_address_2,
            'x_ship_to_city'		=> $order->shipping_city,
            'x_ship_to_state'		=> $order->shipping_state,
            'x_ship_to_zip'			=> $order->shipping_postcode,
            'x_ship_to_country'		=> $order->shipping_country,

            'x_cust_id'			=> $order->user_id,
            'x_invoice_num'		=> $order->get_order_number(),
            'x_order_id'		=> $order->id,
            'x_order_key'		=> $order->order_key,
            'x_description'		=> sprintf( __('Order %s', 'wc-authorize-dpm'), $order->get_order_number() )
            )
        );

        $hidden_fields = $sim->getHiddenFieldString();
        $post_url = ( $test_mode ? self::SANDBOX_URL : self::LIVE_URL );

        ob_start();

        woocommerce_get_template( 'cc-form.php', array(
			'prefill' => $prefill
		), 'authorize-net-dpm/', plugin_dir_path( __FILE__ ) . 'templates/' );

		$form = '<form method="post" action="' . $post_url . '"> ' . $hidden_fields;
        $form .= ob_get_clean();
        $form .= '</form>';
        return $form;
    }
}