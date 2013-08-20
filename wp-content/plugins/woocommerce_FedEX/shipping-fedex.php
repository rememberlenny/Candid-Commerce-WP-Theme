<?php
/*
  Plugin Name: WooCommerce FedEx
  Plugin URI: http://hypnoticzoo.com
  Description: Fedex Shipping for WooCommerce
  Version: 1.0.7
  Author: Andy Zhang
  Author URI: http://hypnoticzoo.com

  Copyright: © 2009-2011 WooThemes.
  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
add_action('plugins_loaded', 'woocommerce_fedex_init', 0);

function woocommerce_fedex_init() {

    if (!class_exists('woocommerce_shipping_method'))
        return;

    if (!class_exists('xmlparser'))
        include_once(plugin_dir_path(__FILE__) . 'xmlparser.php');

    /**
     * Plugin updates
     * */
    if (is_admin()) {
        if (!class_exists('WooThemes_Plugin_Updater'))
            require_once( 'woo-updater/plugin-updater.class.php' );

        $woo_plugin_updater_fedex = new WooThemes_Plugin_Updater(__FILE__);
        $woo_plugin_updater_fedex->api_key = 'f8562dd1791cec7a3899dc079d60e1c0';
        $woo_plugin_updater_fedex->init();
    }

    /**
     * Shipping method class
     * */
    class fedex extends woocommerce_shipping_method {

        var $url = "https://gatewaybeta.fedex.com/GatewayDC";

        function __construct() {
            global $woocommerce;

            $this->id = 'fedex';
            $this->method_title = __('FedEx', 'woothemes');

            // Load the form fields.
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();
            
            $this->enabled = $this->settings['enabled'];
            $this->title = $this->settings['title'];
            $this->availability = $this->settings['availability'];
            $this->origin_country = $woocommerce->countries->get_base_country();
            $this->origin_post = $this->settings['origin'];
            $this->countries = $this->settings['countries'];
            $this->tax_status = $this->settings['tax_status'];
            $this->fee = $this->settings['fee'];
            $this->selected_shipping_type = (is_array(get_option("woocommerce_fedex_shipping_type", array()))) ? get_option("woocommerce_fedex_shipping_type", array()) : array();
            $this->weight_unit = (get_option("woocommerce_weight_unit") == "kg") ? "KGS" : "LBS";
            $this->multiple_rates = true;
            $this->rates = array();
            // When a method has more than one cost/choice it will be in this array of titles/costs
            $this->shipping_type = array(
                'domestic' => array(
                    'PRIORITYOVERNIGHT' => new fedex_priority_overnight(),
                    'STANDARDOVERNIGHT' => new fedex_standard_overnight(),
                    'FIRSTOVERNIGHT' => new fedex_first_overnight(),
                    'FEDEX2DAY' => new fedex_twoday(),
                    'FEDEXEXPRESSSAVER' => new fedex_express_saver(),
                    'FEDEXGROUND' => new fedex_ground(),
                    'GROUNDHOMEDELIVERY' => new fedex_home_delivery()
                ),
                'international' => array(
                    'INTERNATIONALPRIORITY' => new fedex_intl_priority(),
                    'INTERNATIONALECONOMY' => new fedex_intl_economy(),
                    'INTERNATIONALFIRST' => new fedex_intl_first()
                )
            );

            add_action('admin_notices', array(&$this, 'currency_check'));
            add_action('woocommerce_update_options_shipping_methods', array(&$this, 'process_admin_options'));
        }

        /**
         * Initialise Gateway Settings Form Fields
         */
        function init_form_fields() {
            global $woocommerce;

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'woothemes'),
                    'type' => 'checkbox',
                    'label' => __('Enable FedEx', 'woothemes'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __('Method Title', 'woothemes'),
                    'type' => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'woothemes'),
                    'default' => __('FedEx', 'woothemes')
                ),
                'origin' => array(
                    'title' => __('Origin Zip', 'woothemes'),
                    'type' => 'text',
                    'description' => __('Enter your origin zip code.', 'woothemes'),
                    'default' => __('', 'woothemes')
                ),
                'tax_status' => array(
                    'title' => __('Tax Status', 'woothemes'),
                    'type' => 'select',
                    'description' => '',
                    'default' => 'taxable',
                    'options' => array(
                        'taxable' => __('Taxable', 'woothemes'),
                        'none' => __('None', 'woothemes')
                    )
                ),
                'fee' => array(
                    'title' => __('Handling Fee', 'woothemes'),
                    'type' => 'text',
                    'description' => __('Fee excluding tax. Enter an amount, e.g. 2.50, or a percentage, e.g. 5%.', 'woothemes'),
                    'default' => ''
                ),
                'availability' => array(
                    'title' => __('Method availability', 'woothemes'),
                    'type' => 'select',
                    'default' => 'all',
                    'class' => 'availability',
                    'options' => array(
                        'all' => __('All allowed countries', 'woothemes'),
                        'specific' => __('Specific Countries', 'woothemes')
                    )
                ),
                'countries' => array(
                    'title' => __('Specific Countries', 'woothemes'),
                    'type' => 'multiselect',
                    'class' => 'chosen_select',
                    'css' => 'width: 450px;',
                    'default' => '',
                    'options' => $woocommerce->countries->countries
                )
            );
        }

        function currency_check() {

            if (get_option('woocommerce_currency') != "USD" && $this->enabled == 'yes') :

                echo '<div class="error"><p>' . sprintf(__('FedEx is enabled, but the <a href="%s">currency</a> is not USD; FedEx currently support only USD.', 'woothemes'), admin_url('admin.php?page=woocommerce&tab=general')) . '</p></div>';

            endif;

            if ($this->origin_country != "US" && $this->enabled == 'yes') :

                echo '<div class="error"><p>' . sprintf(__('FedEx is enabled, but the <a href="%s">base country/region</a> is not US.', 'woothemes'), admin_url('admin.php?page=woocommerce&tab=general')) . '</p></div>';

            endif;

            if (!$this->origin_post && $this->enabled == 'yes') :

                echo '<div class="error"><p>' . sprintf(__('FedEx is enabled, but the <a href="%s">origin zip code</a> is not available.', 'woothemes'), admin_url('admin.php?page=woocommerce&tab=shipping_methods')) . '</p></div>';

            endif;
        }

        function calculate_shipping() {
            global $woocommerce;
            $customer = $woocommerce->customer;

            $_tax = &new woocommerce_tax();
            $this->shipping_total = 0;
            $this->shipping_tax = 0;
            $weight = 0;
            if (sizeof($woocommerce->cart->get_cart()) > 0 && ($customer->get_shipping_state()) || $customer->get_shipping_postcode()) {

                foreach ($woocommerce->cart->get_cart() as $item_id => $values) {

                    $_product = $values['data'];

                    if ($_product->exists() && $values['quantity'] > 0) {

                        if (!$_product->is_virtual()) {

                            $weight += $_product->get_weight() * $values['quantity'];
                        }
                    }
                }
                $data['weight'] = $weight;
                if ($weight) {
                    $this->get_shipping_response($data);
                }
            }
        }

        /**
         * Set shipping rates from cache or from FedEx API
         * @global type $woocommerce
         * @param type $data 
         */
        function get_shipping_response($data = false) {
            global $woocommerce;
            $customer = $woocommerce->customer;
            $xmlParser = new xmlparser();
            $update_rates = false;

            $cart_items = $woocommerce->cart->get_cart();
            foreach ($cart_items as $id => $cart_item) {
                $cart_temp[] = $id . $cart_item['quantity'];
            }
            $cart_hash = hash('MD5', serialize($cart_temp));

            $shipping_data = array(
                'Pickup_Postcode' => $this->origin_post,
                'Pickup_Country' => $this->origin_country,
                'Destination_Postcode' => $customer->get_shipping_postcode(),
                'State' => $customer->get_shipping_state(),
                'Country' => $customer->get_shipping_country(),
                'Weight' => $data['weight'],
            );

            $doi = ($this->origin_country == $customer->get_shipping_country()) ? 'domestic' : 'international';
            $cache_data = get_transient(get_class($this));

            if ($cache_data) {
                if ($cache_data['cart_hash'] == $cart_hash && $cache_data['shipping_data']['Destination_Postcode'] == $shipping_data['Destination_Postcode'] && $cache_data['shipping_data']['State'] == $shipping_data['State'] && $cache_data['shipping_data']['Country'] == $shipping_data['Country']) {
                    $this->rates = $cache_data['rates'];
                } else {
                    $update_rates = true;
                }
            } else {
                $update_rates = true;
            }


            if ($update_rates) {
                foreach ($this->shipping_type[$doi] as $typename => $shipping_type) {
                    if (in_array($typename, $this->selected_shipping_type)) {
                        $shipping_data['Shipping_Type'] = $typename;
                        $data = $this->fedex_encode($shipping_data);
                        if ($result = $this->fedex_shipping($data)) {
	                        $array = $xmlParser->GetXMLTree($result);
	                        //$xmlParser->printa($array);
	                        if (isset($array['FDXRATEREPLY'][0]['ERROR']) && count($array['FDXRATEREPLY'][0]['ERROR'])) { // If it is error
	                            $error = new fedexError();
	                            $error->number = $array['FDXRATEREPLY'][0]['ERROR'][0]['CODE'][0]['VALUE'];
	                            $error->description = $array['FDXRATEREPLY'][0]['ERROR'][0]['MESSAGE'][0]['VALUE'];
	                            $error->response = $array;
	                            $this->error = $error;
	                        } else if (isset($array['FDXRATEREPLY'][0]['ESTIMATEDCHARGES'][0]['DISCOUNTEDCHARGES'][0]['NETCHARGE']) && count($array['FDXRATEREPLY'][0]['ESTIMATEDCHARGES'][0]['DISCOUNTEDCHARGES'][0]['NETCHARGE'])) {
	                            $rate = $array['FDXRATEREPLY'][0]['ESTIMATEDCHARGES'][0]['DISCOUNTEDCHARGES'][0]['NETCHARGE'][0]['VALUE'];
	                            $this->shipping_type[$doi][$typename]->shipping_total = $rate + $this->get_fee($this->fee, $woocommerce->cart->cart_contents_total);
	                            array_push($this->rates, $this->shipping_type[$doi][$typename]);
	                        }
	                	}
                    }
                }
                $cache_data['shipping_data'] = $shipping_data;
                $cache_data['cart_hash'] = $cart_hash;
                $cache_data['rates'] = $this->rates;
            }
            set_transient(get_class($this), $cache_data);
        }

        function fedex_encode($data = false) {

            $str = '<?xml version="1.0" encoding="UTF-8" ?>';
            $str .= '    <FDXRateRequest xmlns:api="http://www.fedex.com/fsmapi" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="FDXRateRequest.xsd">';
            $str .= '        <RequestHeader>';
            $str .= '            <CustomerTransactionIdentifier>Express Rate</CustomerTransactionIdentifier>';
            $str .= '            <AccountNumber>510087020</AccountNumber>';
            $str .= '            <MeterNumber>118547677</MeterNumber>';
            $str .= '            <CarrierCode>FDXE</CarrierCode>';
            $str .= '        </RequestHeader>';
            $str .= '        <DropoffType>REGULARPICKUP</DropoffType>';
            $str .= '        <Service>' . $data['Shipping_Type'] . '</Service>';
            $str .= '        <Packaging>YOURPACKAGING</Packaging>';
            $str .= '        <WeightUnits>' . $this->weight_unit . '</WeightUnits>';
            $str .= '        <Weight>' . $data['Weight'] . '</Weight>';
            $str .= '        <OriginAddress>';
            $str .= '            <PostalCode>' . $data['Pickup_Postcode'] . '</PostalCode>';
            $str .= '            <CountryCode>' . $data['Pickup_Country'] . '</CountryCode>';
            $str .= '        </OriginAddress>';
            $str .= '        <DestinationAddress>';
            $str .= '            <StateOrProvinceCode>' . $data['State'] . '</StateOrProvinceCode>';
            $str .= '            <PostalCode>' . $data['Destination_Postcode'] . '</PostalCode>';
            $str .= '            <CountryCode>' . $data['Country'] . '</CountryCode>';
            $str .= '        </DestinationAddress>';
            $str .= '        <Payment>';
            $str .= '            <PayorType>SENDER</PayorType>';
            $str .= '        </Payment>';
            $str .= '    </FDXRateRequest>';

            return $str;
        }

        /**
         * Shipping result from the end point
         * @param type $data
         * @param type $cache
         * @return response 
         */
        function fedex_shipping($data=false, $cache=false) {

            $response = wp_remote_post($this->url, array(
                'method' 	=> 'POST',
                'body' 		=> $data,
                'timeout' 	=> 70,
                'sslverify' => 0
            ));
            if( !is_wp_error( $response ) ) {
				return $response['body'];
			}
            return false;
        }

        function is_available() {
            global $woocommerce;

            if ($this->enabled == "no")
                return false;

            if (isset($woocommerce->cart->cart_contents_total) && isset($this->min_amount) && $this->min_amount && $this->min_amount > $woocommerce->cart->cart_contents_total)
                return false;

            if (get_option('woocommerce_currency') != "USD"):
                return false;
            endif;

            if ($this->origin_country != "US"):
                return false;
            endif;

            if (!$this->origin_post):
                return false;
            endif;

            $ship_to_countries = '';

            if ($this->availability == 'specific') :
                $ship_to_countries = $this->countries;
            else :
                if (get_option('woocommerce_allowed_countries') == 'specific') :
                    $ship_to_countries = get_option('woocommerce_specific_allowed_countries');
                endif;
            endif;

            if (is_array($ship_to_countries)) :
                if (!in_array($woocommerce->customer->get_shipping_country(), $ship_to_countries))
                    return false;
            endif;

            return true;
        }

        /**
         * Admin Panel Options 
         * - Options for bits like 'title' and availability on a country-by-country basis
         *
         * @since 1.0.0
         */
        public function admin_options() {
            ?>
            <h3><?php _e('FedEx', 'woothemes'); ?></h3>
            <p><?php _e('FedEx calculates shipping price base on FedEx standard.', 'woothemes'); ?></p>
            <table class="form-table">
                <?php
                // Generate the HTML For the settings form.
                $this->generate_settings_html();
                ?>
                <tr valign="top">
                    <th scope="row" class="titledesc"><?php _e('Shipping methods', 'woothemes') ?></th>
                    <td class="forminp">
                        <?php
                        $shipping_types = array_merge($this->shipping_type['domestic'], $this->shipping_type['international']);
                        foreach ($shipping_types as $key => $shipping_type):
                            ?>
                            <input type="checkbox" name="woocommerce_fedex_shipping_type[]" value="<?php echo $key ?>" <?php
                if (in_array($key, $this->selected_shipping_type)) {
                    echo 'checked="checked"';
                }
                            ?> /><span><?php echo $shipping_type->title ?></span>
                            </br>
                        <?php endforeach; ?>
                    </td>
                </tr>

            </table><!--/.form-table-->
            <?php
        }
        
        function process_admin_options() {
            parent::process_admin_options();
            
            if (isset($_POST['woocommerce_fedex_shipping_type']))
                $selected_shipping_type = $_POST['woocommerce_fedex_shipping_type']; 
            else
                $selected_shipping_type = array();
            update_option('woocommerce_fedex_shipping_type', $selected_shipping_type);
        }

    }

    class fedexError {

        var $number;
        var $description;
        var $response;

    }

    class fedex_express_saver extends fedex {

        function __construct($shipping = false) {
            $this->id = 'fedex_express_saver';
            $this->title = __('FedEx Express Saver', 'woothemes');
            $this->shipping_total = $shipping;
        }

    }

    class fedex_priority_overnight extends fedex {

        function __construct($shipping = false) {
            $this->id = 'fedex_priority_overnight';
            $this->title = __('FedEx Priority Overnight', 'woothemes');
            $this->shipping_total = $shipping;
        }

    }

    class fedex_standard_overnight extends fedex {

        function __construct($shipping = false) {
            $this->id = 'fedex_standard_overnight';
            $this->title = __('FedEx Standard Overnight', 'woothemes');
            $this->shipping_total = $shipping;
        }

    }

    class fedex_first_overnight extends fedex {

        function __construct($shipping = false) {
            $this->id = 'fedex_first_overnight';
            $this->title = __('FedEx First Overnight', 'woothemes');
            $this->shipping_total = $shipping;
        }

    }

    class fedex_twoday extends fedex {

        function __construct($shipping = false) {
            $this->id = 'fedex_twoday';
            $this->title = __('FedEx 2 Day', 'woothemes');
            $this->shipping_total = $shipping;
        }

    }

    class fedex_intl_priority extends fedex {

        function __construct($shipping = false) {
            $this->id = 'fedex_intl_priority';
            $this->title = __('FedEx International Priority', 'woothemes');
            $this->shipping_total = $shipping;
        }

    }

    class fedex_intl_economy extends fedex {

        function __construct($shipping = false) {
            $this->id = 'fedex_intl_economy';
            $this->title = __('FedEx International Economy', 'woothemes');
            $this->shipping_total = $shipping;
        }

    }

    class fedex_intl_first extends fedex {

        function __construct($shipping = false) {
            $this->id = 'fedex_intl_first';
            $this->title = __('FedEx International First', 'woothemes');
            $this->shipping_total = $shipping;
        }

    }

    class fedex_oneday_fright extends fedex {

        function __construct($shipping = false) {
            $this->id = 'fedex_oneday_fright';
            $this->title = __('FedEx Overnight Freight', 'woothemes');
            $this->shipping_total = $shipping;
        }

    }

    class fedex_twoday_fright extends fedex {

        function __construct($shipping = false) {
            $this->id = 'fedex_twoday_fright';
            $this->title = __('FedEx 2 day Freight', 'woothemes');
            $this->shipping_total = $shipping;
        }

    }

    class fedex_threeday_fright extends fedex {

        function __construct($shipping = false) {
            $this->id = 'fedex_threeday_fright';
            $this->title = __('FedEx 3 day Freight', 'woothemes');
            $this->shipping_total = $shipping;
        }

    }

    class fedex_ground extends fedex {

        function __construct($shipping = false) {
            $this->id = 'fedex_ground';
            $this->title = __('FedEx Ground', 'woothemes');
            $this->shipping_total = $shipping;
        }

    }

    class fedex_home_delivery extends fedex {

        function __construct($shipping = false) {
            $this->id = 'fedex_home_delivery';
            $this->title = __('FedEx Home Delivery', 'woothemes');
            $this->shipping_total = $shipping;
        }

    }

    function add_fedex_method($methods) {
        $methods[] = 'fedex';
        return $methods;
    }

    add_filter('woocommerce_shipping_methods', 'add_fedex_method');
}