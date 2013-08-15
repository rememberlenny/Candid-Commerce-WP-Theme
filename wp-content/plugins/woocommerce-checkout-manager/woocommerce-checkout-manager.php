<?php
/*

Plugin Name: WooCommerce Checkout Manager
Plugin URI: http://www.trottyzone.com/product/woocommerce-checkout-manager-pro
Description: Manages WooCommerce Checkout fields
Version: 3.5
Author: Ephrain Marchan
Author URI: http://www.trottyzone.com
License: GPLv2 or later
*/

/*

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/


if ( ! defined( 'ABSPATH' ) ) die();

register_activation_hook( __FILE__, 'wccs_install' );
register_uninstall_hook( __FILE__, 'wccs_uninstall' );
function wccs_uninstall() {
	delete_option( 'wccs_settings' );
}

load_plugin_textdomain('woocommerce-checkout-manager', false, dirname(plugin_basename(__FILE__)) . '/languages/');

function wccs_install() {
	$defaults = array( 'buttons' => array( array(
			'label'  => __( 'Example Label', 'woocommerce-checkout-manager' ),
			'placeholder' => __( 'Example placeholder', 'woocommerce-checkout-manager' )
                        	
		)
	),
                           'checkness' => array( 
                           'checkbox1' => true,
                           'checkbox12' => true
                             )
          );

	add_option( 'wccs_settings', apply_filters( 'wccs_defaults', $defaults ) );
}

// Hook for adding admin menus
if ( is_admin() ){ // admin actions

  // Hook for adding admin menu
  add_action( 'admin_menu', 'wccs_admin_menu' );


// Display the 'Settings' link in the plugin row on the installed plugins list page
	add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'wccs_admin_plugin_actions', -10);

add_action( 'admin_init', 'wccs_register_setting' );


} else { // non-admin enqueues, actions, and filters
// hook to get option values and dynamically render css to support the tab classes

wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

}


// action function for above hook
function wccs_admin_menu() {

    // Add a new submenu under Settings:
    add_options_page( __('WooCommerce Checkout Manager', 'woocommerce-checkout-manager'), __('WooCommerce Checkout Manager', 'woocommerce-checkout-manager'), 'manage_options', __FILE__ , 'wccs__options_page');

}

function wccs_register_setting() {	
register_setting( 'wccs_options', 'wccs_settings', 'wccs_options_validate' );
}


// fc_settings_page() displays the page content 
function wccs__options_page() {

    //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }


// variables for the field and option names 
    $hidden_field_name = 'mccs_submit_hidden';
	

// read options values
$options = get_option( 'wccs_settings' );

    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {

        // Save the posted value in the database
update_option( 'wccs_settings', $options );

?>

<div class="updated"><p><strong><?php _e('settings saved.'); ?></strong></p></div>

<?php 

    }



    // Now display the settings editing screen

    echo '<div class="wrap">';
    
    // icon for settings
    echo '<div id="icon-themes" class="icon32"><br></div>';

    // header

    echo "<h2>" . __( 'WooCommerce Checkout Manager', 'woocommerce-checkout-manager' ) . "</h2>";

    // settings form 
    
    ?>

<div style="float:right;font-weight:700;color:red;">
Love this plugin? PLEASE <a href="http://www.trottyzone.com/donate/">DONATE </a><3 ... ALOT! of Time and Effort has been put into this
<form style="margin-bottom:-10px;" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="5TFAJB5686N8L">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
</div>


<form name="form" method="post" action="options.php" id="frm1">

<?php
        	settings_fields( 'wccs_options' );
			$options = get_option( 'wccs_settings' );
		?>



<table class="widefat" border="1" >

<thead>
	<tr>
		<th><?php _e('Field Name', 'woocommerce-checkout-manager');  ?></th>
		<th><?php _e('Remove Field Entirely', 'woocommerce-checkout-manager');  ?></th>		
		<th><?php _e('Remove Required Attribute', 'woocommerce-checkout-manager');  ?></th>
                <th class="wccs_replace"><?php _e('Replace Label Name', 'woocommerce-checkout-manager');  ?></th>
                <th class="wccs_replace"><?php _e('Replace Placeholder Name', 'woocommerce-checkout-manager');  ?></th>
	</tr>
</thead>


<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
		<td><strong><?php _e('Select All Check boxes in this Column', 'woocommerce-checkout-manager');  ?></strong></td>
		<td><input name="wccs_settings[checkness][select_all_rm]" type="checkbox" id="select_all_rm" value="1" <?php if (  1 == ($options['checkness']['select_all_rm'])) echo "checked='checked'"; ?> /></td>
                <td><input name="wccs_settings[checkness][select_all_rq]" type="checkbox" id="select_all_rq" value="1" <?php if (  1 == ($options['checkness']['select_all_rq'])) echo "checked='checked'"; ?> /></td>
<td></td>
<td></td>
		</tr>
</tbody>

<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('First Name', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[checkness][wccs_opt_1]" type="checkbox" class="rm" value="1" 
     <?php if (  1 == ($options['checkness']['wccs_opt_1']) ) echo 'checked="checked"'; ?>   /></td>

     <td><input name="wccs_settings[checkness][wccs_rq_1]" type="checkbox" class="rq" value="1" <?php if (  1 == ($options['checkness']['wccs_rq_1'])) echo "checked='checked'"; ?> /></td>

<td><input type="text" name="wccs_settings[replace][label]"  
          value="<?php echo esc_attr( $options['replace']['label'] ); ?>" /></td>

<td><input type="text" name="wccs_settings[replace][placeholder]"  
          value="<?php echo esc_attr( $options['replace']['placeholder'] ); ?>" /></td>
   </tr>
</tbody>

<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('Last Name', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[checkness][wccs_opt_2]" type="checkbox" class="rm" value="1" <?php if (  1 == ($options['checkness']['wccs_opt_2'])) echo "checked='checked'"; ?> /></td>
     <td><input name="wccs_settings[checkness][wccs_rq_2]" type="checkbox" class="rq" value="1" <?php if (  1 == ($options['checkness']['wccs_rq_2'])) echo "checked='checked'"; ?> /></td>

<td><input type="text" name="wccs_settings[replace][label1]"  
          value="<?php echo esc_attr( $options['replace']['label1'] ); ?>" /></td>

<td><input type="text" name="wccs_settings[replace][placeholder1]"  
          value="<?php echo esc_attr( $options['replace']['placeholder1'] ); ?>" /></td>
   </tr>
</tbody>

<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('Country', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[checkness][wccs_opt_8]" type="checkbox" class="rm" value="1" <?php if (  1 == ($options['checkness']['wccs_opt_8'])) echo "checked='checked'"; ?> /></td>
     <td><input name="wccs_settings[checkness][wccs_rq_8]" type="checkbox" class="rq" value="1" <?php if (  1 == ($options['checkness']['wccs_rq_8'])) echo "checked='checked'"; ?> /></td>

<td><input type="text" name="wccs_settings[replace][label2]"  
          value="<?php echo esc_attr( $options['replace']['label2'] ); ?>" /></td>
<td></td>
   </tr>
</tbody>

<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('Phone', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[checkness][wccs_opt_10]" type="checkbox" class="rm" value="1" <?php if (  1 == ($options['checkness']['wccs_opt_10'])) echo "checked='checked'"; ?> /></td>
     <td><input name="wccs_settings[checkness][wccs_rq_10]" type="checkbox" class="rq" value="1" <?php if (  1 == ($options['checkness']['wccs_rq_10'])) echo "checked='checked'"; ?> /></td>

<td><input type="text" name="wccs_settings[replace][label3]"  
          value="<?php echo esc_attr( $options['replace']['label3'] ); ?>" /></td>

<td><input type="text" name="wccs_settings[replace][placeholder3]"  
          value="<?php echo esc_attr( $options['replace']['placeholder3'] ); ?>" /></td>
   </tr>
</tbody>

<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('Email', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[checkness][wccs_opt_11]" type="checkbox" class="rm" value="1" <?php if (get_option('wccs_opt_11')) echo "checked='checked'"; ?> /></td>
     <td><input name="wccs_settings[checkness][wccs_rq_11]" type="checkbox" class="rq" value="1" <?php if (  1 == ($options['checkness']['wccs_rq_11'])) echo "checked='checked'"; ?> /></td>

<td><input type="text" name="wccs_settings[replace][label4]"  
          value="<?php echo esc_attr( $options['replace']['label4'] ); ?>" /></td>

<td><input type="text" name="wccs_settings[replace][placeholder4]"  
          value="<?php echo esc_attr( $options['replace']['placeholder4'] ); ?>" /></td>
   </tr>
</tbody>


<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('Company', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[checkness][wccs_opt_3]" type="checkbox" class="rm" value="1" <?php if (  1 == ($options['checkness']['wccs_opt_3'])) echo "checked='checked'"; ?> /></td>
     <td></td>

<td><input type="text" name="wccs_settings[replace][label5]"  
          value="<?php echo esc_attr( $options['replace']['label5'] ); ?>" /></td>

<td><input type="text" name="wccs_settings[replace][placeholder5]"  
          value="<?php echo esc_attr( $options['replace']['placeholder5'] ); ?>" /></td>
   </tr>
</tbody>



<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('Order Comments', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[checkness][wccs_opt_12]" type="checkbox" class="rm" value="1" <?php if (  1 == ($options['checkness']['wccs_opt_12'])) echo "checked='checked'"; ?> /></td>
<td></td>

<td><input type="text" name="wccs_settings[replace][label11]"  
          value="<?php echo esc_attr( $options['replace']['label11'] ); ?>" /></td>

<td><input type="text" name="wccs_settings[replace][placeholder11]"  
          value="<?php echo esc_attr( $options['replace']['placeholder11'] ); ?>" /></td>
   </tr>
</tbody>


<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('Address 1', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[checkness][wccs_opt_4]" type="checkbox" class="rm" value="1" <?php if (  1 == ($options['checkness']['wccs_opt_4'])) echo "checked='checked'"; ?></td>
     <td></td>
<td></td>
<td></td>
   </tr>
</tbody>

<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('Address 2', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[checkness][wccs_opt_5]" type="checkbox" class="rm" value="1" <?php if (  1 == ($options['checkness']['wccs_opt_5'])) echo "checked='checked'"; ?> /></td>
     <td></td>
<td></td>
<td></td>
   </tr>
</tbody>

<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('City', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[checkness][wccs_opt_6]" type="checkbox" class="rm" value="1" <?php if (  1 == ($options['checkness']['wccs_opt_6'])) echo "checked='checked'"; ?> /></td>
     <td></td>
<td></td>
<td></td>
   </tr>
</tbody>

<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('Postal Code', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[checkness][wccs_opt_7]" type="checkbox" class="rm" value="1" <?php if (  1 == ($options['checkness']['wccs_opt_7'])) echo "checked='checked'"; ?> /></td>
     <td></td>
<td></td>
<td></td>
   </tr>
</tbody>



<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('State', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[checkness][wccs_opt_9]" type="checkbox" class="rm" value="1" <?php if (  1 == ($options['checkness']['wccs_opt_9'])) echo "checked='checked'"; ?> /></td>
    <td></td>
<td></td>
<td></td>
   </tr>
</tbody>


<?php submit_button( __( 'Save Changes', 'woocommerce-checkout-manager' ) ); ?>

<div style="margin:5px 0 15px 10px;color:green;font-size:18px;float:left;">:: <?php _e('Billing Section', 'woocommerce-checkout-manager');  ?> ::</div>
</table>



<table class="widefat" border="1" >

<thead>
	<tr>
		<th><?php _e('Field Name', 'woocommerce-checkout-manager');  ?></th>
		<th><?php _e('Remove Field Entirely', 'woocommerce-checkout-manager');  ?></th>		
		<th><?php _e('Remove Required Attribute', 'woocommerce-checkout-manager');  ?></th>
                <th class="wccs_replace"><?php _e('Replace Label Name', 'woocommerce-checkout-manager');  ?></th>
                <th class="wccs_replace"><?php _e('Replace Placeholder Name', 'woocommerce-checkout-manager');  ?></th>
	</tr>
</thead>


<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
		<td><strong><?php _e('Select All Check boxes in this Column', 'woocommerce-checkout-manager');  ?></strong></td>
		<td><input name="wccs_settings[check][select_all_rm_s]" type="checkbox" id="select_all_rm_s" value="1" <?php if (  1 == ($options['check']['select_all_rm_s'])) echo "checked='checked'"; ?> /></td>
                <td><input name="wccs_settings[check][select_all_rq_s]" type="checkbox" id="select_all_rq_s" value="1" <?php if (  1 == ($options['check']['select_all_rq_s'])) echo "checked='checked'"; ?> /></td>
<td></td>
<td></td>
		</tr>
</tbody>


<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('First Name', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[check][wccs_opt_1_s]" type="checkbox" class="rm_s" value="1" 
     <?php if (  1 == ($options['check']['wccs_opt_1_s']) ) echo 'checked="checked"'; ?>   /></td>

     <td><input name="wccs_settings[check][wccs_rq_1_s]" type="checkbox" class="rq_s" value="1" <?php if (  1 == ($options['check']['wccs_rq_1_s'])) echo "checked='checked'"; ?> /></td>

<td><input type="text" name="wccs_settings[replace][label_s]"  
          value="<?php echo esc_attr( $options['replace']['label_s'] ); ?>" /></td>

<td><input type="text" name="wccs_settings[replace][placeholder_s]"  
          value="<?php echo esc_attr( $options['replace']['placeholder_s'] ); ?>" /></td>
   </tr>
</tbody>

<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('Last Name', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[check][wccs_opt_2_s]" type="checkbox" class="rm_s" value="1" <?php if (  1 == ($options['check']['wccs_opt_2_s'])) echo "checked='checked'"; ?> /></td>
     <td><input name="wccs_settings[check][wccs_rq_2_s]" type="checkbox" class="rq_s" value="1" <?php if (  1 == ($options['check']['wccs_rq_2_s'])) echo "checked='checked'"; ?> /></td>

<td><input type="text" name="wccs_settings[replace][label_s1]"  
          value="<?php echo esc_attr( $options['replace']['label_s1'] ); ?>" /></td>

<td><input type="text" name="wccs_settings[replace][placeholder_s1]"  
          value="<?php echo esc_attr( $options['replace']['placeholder_s1'] ); ?>" /></td>
   </tr>
</tbody>

<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('Company', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[check][wccs_opt_3_s]" type="checkbox" class="rm_s" value="1" <?php if (  1 == ($options['check']['wccs_opt_3_s'])) echo "checked='checked'"; ?> /></td>
<td></td>

<td><input type="text" name="wccs_settings[replace][label_s2]"  
          value="<?php echo esc_attr( $options['replace']['label_s2'] ); ?>" /></td>

<td><input type="text" name="wccs_settings[replace][placeholder_s2]"  
          value="<?php echo esc_attr( $options['replace']['placeholder_s2'] ); ?>" /></td>
   </tr>
</tbody>


<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('Country', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[check][wccs_opt_8_s]" type="checkbox" class="rm_s" value="1" <?php if (  1 == ($options['check']['wccs_opt_8_s'])) echo "checked='checked'"; ?> /></td>
     <td></td>

<td><input type="text" name="wccs_settings[replace][label_s7]"  
          value="<?php echo esc_attr( $options['replace']['label_s7'] ); ?>" /></td>

<td></td>
   </tr>
</tbody>


<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('Address 1', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[check][wccs_opt_4_s]" type="checkbox" class="rm_s" value="1" <?php if (  1 == ($options['check']['wccs_opt_4_s'])) echo "checked='checked'"; ?></td>
     <td></td>

<td></td>
<td></td>
   </tr>
</tbody>

<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('Address 2', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[check][wccs_opt_5_s]" type="checkbox" class="rm_s" value="1" <?php if (  1 == ($options['check']['wccs_opt_5_s'])) echo "checked='checked'"; ?> /></td>
     <td></td>

<td></td>
<td></td>
   </tr>
</tbody>

<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('City', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[check][wccs_opt_6_s]" type="checkbox" class="rm_s" value="1" <?php if (  1 == ($options['check']['wccs_opt_6_s'])) echo "checked='checked'"; ?> /></td>
     <td></td>

<td></td>
<td></td>
   </tr>
</tbody>

<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('Postal Code', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[check][wccs_opt_7_s]" type="checkbox" class="rm_s" value="1" <?php if (  1 == ($options['check']['wccs_opt_7_s'])) echo "checked='checked'"; ?> /></td>
     <td></td>

<td></td>
<td></td>
   </tr>
</tbody>


<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('State', 'woocommerce-checkout-manager');  ?></td>
     <td><input name="wccs_settings[check][wccs_opt_9_s]" type="checkbox" class="rm_s" value="1" <?php if (  1 == ($options['check']['wccs_opt_9_s'])) echo "checked='checked'"; ?> /></td>
     <td></td>

<td></td>
<td></td>
   </tr>
</tbody>


<div style="margin:20px 0 15px 10px;color:green;font-size:18px;float:left;">:: <?php _e('Shipping Section', 'woocommerce-checkout-manager');  ?> ::</div>
</table>


<div style="margin:20px 0 15px 10px;color:green;font-size:18px;">:: <?php _e('Receipt Section', 'woocommerce-checkout-manager');  ?> ::</div>
<table class="widefat" border="1">

<thead>
	<tr>
		<th><?php _e('Field Name', 'woocommerce-checkout-manager');  ?></th>
		<th><?php _e('Replace default footer content of Order Receipt', 'woocommerce-checkout-manager');  ?></th>		
	</tr>
</thead>


<tbody>
   <tr>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
     <td><?php _e('Footer Content: ', 'woocommerce-checkout-manager');  ?></td>
     <td style="width: 80%;"><textarea name="wccs_settings[footer][mccs_receipt_footer]" style="width: 100%;height:200px;"><?php echo esc_attr($options['footer']['mccs_receipt_footer']); ?></textarea></td>
   </tr>
</tbody>
</table>


<div style="margin:20px 0 15px 10px;color:green;font-size:18px;float:left;">:: <?php _e('Add New Field Section', 'woocommerce-checkout-manager');  ?> ::</div>

<table class="widefat" border="1" >
<thead>
<th width="100%" style="text-align:center;">Disable Additional information title on</th>
</thead>
</table>
<table class="widefat" border="1" >
<thead>
	<tr>
		<th width="50%" style="text-align:center;"><?php _e('Checkout Page', 'atc-menu');  ?></th>
		<th width="50%" style="text-align:center;"><?php _e('Checkout Details and Email Receipt', 'atc-menu');  ?></th>	
         
	</tr>
</thead>


<tbody>
   <tr>

		<td style="text-align:center;"><input name="wccs_settings[checkness][checkbox12]" type="checkbox" value="true" <?php if (  true == ($options['checkness']['checkbox12'])) echo "checked='checked'"; ?> /></td>

		<td style="text-align:center;"><input name="wccs_settings[checkness][checkbox1]" type="checkbox" value="true" <?php if (  true == ($options['checkness']['checkbox1'])) echo "checked='checked'"; ?> /></td>
		
</tr>

</tbody>

</table>

<style type="text/css">
.wccs-clone {
display:none;
}
.widefat input {
width:100%;
}
.wccs_replace {
width: 20%;
}
.wccs-order {
cursor:move;
}
.wccs-table > tbody > tr > td {
	background: #fff;
    border-right: 1px solid #ededed;
    border-bottom: 1px solid #ededed;
    padding: 8px;
    position: relative;
}

.wccs-table > tbody > tr:last-child td { border-bottom: 0 none; }
.wccs-table > tbody > tr td:last-child { border-right: 0 none; }
.wccs-table > thead > tr > th { border-right: 1px solid #e1e1e1; }
.wccs-table > thead > tr > th:last-child { border-right: 0 none; }

.wccs-table tr td.wccs-order,
.wccs-table tr th.wccs-order {
	width: 16px;
	text-align: center;
	vertical-align: middle;
	color: #aaa;
	text-shadow: #fff 0 1px 0;
}

.wccs-table .wccs-remove {
	width: 16px;
	vertical-align: middle;
}
.wccs-table input[type="text"] {
width: 100%;
}

.wccs-table-footer {
	position: relative;
	overflow: hidden;
	margin-top: 10px;
	padding: 8px 0;
}
</style>

<table class="widefat wccs-table" border="1">

<thead>
	<tr>
<th style="width:5%;" class="wccs-order" title="<?php esc_attr_e( 'Change order' , 'woocommerce-checkout-manager' ); ?>"></th>
		<th><?php _e('Label' , 'woocommerce-checkout-manager' ); ?></th>
		<th><?php _e('Placeholder' , 'woocommerce-checkout-manager' ); ?></th>
                <th width="15%"><?php _e('Choose Type' , 'woocommerce-checkout-manager' ); ?></th>
                <th width="10%"><?php _e('Abbreviation' , 'woocommerce-checkout-manager' ); ?></th>
                <th width="12%"><?php _e('Required Attribute' , 'woocommerce-checkout-manager' ); ?></th>
                <th scope="col" title="<?php esc_attr_e( 'Remove button', 'woocommerce-checkout-manager' ); ?>"><!-- remove --></th>		
	</tr>
</thead>
<tbody>


<?php
                	if ( isset ( $options['buttons'] ) ) :

					// Loop through all the buttons
                	for ( $i = 0; $i < count( $options['buttons'] ); $i++ ) :

                		if ( ! isset( $options['buttons'][$i] ) )
                			break;


                ?>



   <tr valign="top" class="wccs-row">
<td class="wccs-order" title="<?php esc_attr_e( 'Change order', 'woocommerce-checkout-manager' ); ?>"><?php echo $i + 1; ?></td>

<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
 

          <td><input type="text" name="wccs_settings[buttons][<?php echo $i; ?>][label]"  
          value="<?php echo esc_attr( $options['buttons'][$i][label] ); ?>" /></td>

          <td><input type="text" name="wccs_settings[buttons][<?php echo $i; ?>][placeholder]"  
          value="<?php echo esc_attr( $options['buttons'][$i][placeholder] ); ?>" /></td>

<td>
<select name="wccs_settings[buttons][<?php echo $i; ?>][type]" >  <!--Call run() function-->
     <option value="text" <?php selected( $options['buttons'][$i][type], 'text' ); ?>>Text</option>
     <option value="date" <?php selected( $options['buttons'][$i][type], 'date' ); ?>>Date</option>
     <option value="checkbox" <?php selected( $options['buttons'][$i][type], 'checkbox' ); ?> disabled>Checkbox</option>    
</select>
</td>




<td><input type="text" maxlength="10" name="wccs_settings[buttons][<?php echo $i; ?>][cow]"  
          value="<?php echo esc_attr( $options['buttons'][$i][cow] ); ?>" /></td>


<td><input name="wccs_settings[buttons][<?php echo $i; ?>][checkbox]" type="checkbox" value="true" <?php if (  true == ($options['buttons'][$i][checkbox])) echo "checked='checked'"; ?> /></td>


<td class="wccs-remove"><a class="wccs-remove-button" href="javascript:;" title="<?php esc_attr_e( 'Remove Field' , 'woocommerce-checkout-manager' ); ?>">&times;</a></td>
   </tr>

<?php endfor; endif; ?>
<!-- Empty -->

                    <?php $i = 999; ?>

<tr valign="top" class="wccs-clone" >

<td class="wccs-order" title="<?php esc_attr_e( 'Change order' , 'woocommerce-checkout-manager' ); ?>"><?php echo $i; ?></td>
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

       <td><input type="text" name="wccs_settings[buttons][<?php echo $i; ?>][label]"  
       title="<?php esc_attr_e( 'Label of the New Field', 'woocommerce-checkout-manager' ); ?>" value="" /></td>

       <td><input type="text" name="wccs_settings[buttons][<?php echo $i; ?>][placeholder]"  
       title="<?php esc_attr_e( 'Placeholder - Preview of Data to Input', 'woocommerce-checkout-manager' ); ?>" value=" " /></td>

<td>
<select name="wccs_settings[buttons][<?php echo $i; ?>][type]" >  <!--Call run() function-->
     <option value="text" >Text</option>
     <option value="date" >Date</option>
     <option value="checkbox"  disabled>Checkbox</option>    
</select>
</td>


<td><input type="text" maxlength="5" name="wccs_settings[buttons][<?php echo $i; ?>][cow]"  
       title="<?php esc_attr_e( 'Abbreviation (No spaces)', 'woocommerce-checkout-manager' ); ?>" value="" /></td>

   <td><input name="wccs_settings[buttons][<?php echo $i; ?>][checkbox]" type="checkbox" 
    title="<?php esc_attr_e( 'Add/Remove Required Attribute', 'woocommerce-checkout-manager' ); ?>" value=" " /></td>


<td class="wccs-remove"><a class="wccs-remove-button" href="javascript:;" title="<?php esc_attr_e( 'Remove Field' , 'woocommerce-checkout-manager' ); ?>">&times;</a></td>

   </tr>
</tbody>

</table>

<div class="wccs-table-footer">
<a href="javascript:;" id="wccs-add-button" class="button-secondary"><?php _e( '+ Add New Field' , 'woocommerce-checkout-manager' ); ?></a>
</div>

</form>
<?php
}



// Build array of links for rendering in installed plugins list
function wccs_admin_plugin_actions($links) {

	$wccs_plugin_links = array(
          '<a href="options-general.php?page=woocommerce-checkout-manager/woocommerce-checkout-manager.php">'.__('Settings').'</a>',
           '<a href="http://www.trottyzone.com/forums/forum/website-support/">'.__('Support').'</a>', 
                             );

	return array_merge( $wccs_plugin_links, $links );

}


function wccs_scripts( $hook_suffix ) {
	if ( $hook_suffix == 'settings_page_woocommerce-checkout-manager/woocommerce-checkout-manager' ) {
		
		wp_enqueue_script( 'script_wccs', plugins_url( 'script_wccs.js', __FILE__ ), array( 'jquery' ), '1.2' );
		if(!wp_script_is('jquery-ui-sortable', 'queue')){
                wp_enqueue_script('jquery-ui-sortable');
                     }
	}
}

add_action( 'admin_enqueue_scripts', 'wccs_scripts' );



// ================== First Name ===================
function wccs_override_checkout_fields1($fields1) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['checkness']['wccs_opt_1'] ) ) 
unset($fields1['billing']['billing_first_name']);
return $fields1;
}
add_filter( 'woocommerce_checkout_fields' , 'wccs_override_checkout_fields1' );
 
function wccs_override_required_fields1( $address_fields1 ) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['checkness']['wccs_rq_1'] ) ) 
	$address_fields1['billing_first_name']['required'] = false;
	return $address_fields1;
}
add_filter( 'woocommerce_billing_fields', 'wccs_override_required_fields1', 10, 1 );

// replace label and placeholder
function custom_replace_checkout_fields( $fields ) {
$options = get_option( 'wccs_settings' );
if ( ! empty( $options['replace']['placeholder'] ) ) 
     $fields['billing']['billing_first_name']['placeholder'] = ''.$options['replace']['placeholder'].'';
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_replace_checkout_fields' );


function custom_replace_checkout_fieldsa( $fields ) {
$options = get_option( 'wccs_settings' );
if ( ! empty( $options['replace']['label'] ) ) 
     $fields['billing']['billing_first_name']['label'] = ''.$options['replace']['label'].'';
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_replace_checkout_fieldsa' );


// ========== Last Name ==============
function wccs_override_checkout_fields2($fields2) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['checkness']['wccs_opt_2'] ) ) 
unset($fields2['billing']['billing_last_name']);
return $fields2;
}
add_filter( 'woocommerce_checkout_fields' , 'wccs_override_checkout_fields2' );

function wccs_override_required_fields2( $address_fields2 ) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['checkness']['wccs_rq_2'] ) ) 
	$address_fields2['billing_last_name']['required'] = false;
	return $address_fields2;
}
add_filter( 'woocommerce_billing_fields', 'wccs_override_required_fields2', 10, 1 );

// replace label and placeholder
function custom_replace_checkout_fields1( $fields ) {
$options = get_option( 'wccs_settings' );
if ( ! empty( $options['replace']['placeholder1'] ) ) 
     $fields['billing']['billing_last_name']['placeholder'] = ''.$options['replace']['placeholder1'].'';
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_replace_checkout_fields1' );


function custom_replace_checkout_fieldsb( $fields ) {
$options = get_option( 'wccs_settings' );
if ( ! empty( $options['replace']['label1'] ) ) 
     $fields['billing']['billing_last_name']['label'] = ''.$options['replace']['label1'].'';
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_replace_checkout_fieldsb' );

// ============ Company ==============
function wccs_override_checkout_fields3($fields3) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['checkness']['wccs_opt_3'] ) ) 
unset($fields3['billing']['billing_company']);
return $fields3;
}
add_filter( 'woocommerce_checkout_fields' , 'wccs_override_checkout_fields3' );

// replace label and placeholder
function custom_replace_checkout_fields2( $fields ) {
$options = get_option( 'wccs_settings' );
if ( ! empty( $options['replace']['placeholder2'] ) ) 
     $fields['billing']['billing_company']['placeholder'] = ''.$options['replace']['placeholder2'].'';
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_replace_checkout_fields2' );


function custom_replace_checkout_fieldsc( $fields ) {
$options = get_option( 'wccs_settings' );
if ( ! empty( $options['replace']['label2'] ) ) 
     $fields['billing']['billing_company']['label'] = ''.$options['replace']['label2'].'';
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_replace_checkout_fieldsc' );

// ============ Address 1 ==============
function wccs_override_checkout_fields4($fields4) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['checkness']['wccs_opt_4'] ) ) 
unset($fields4['billing']['billing_address_1']);
return $fields4;
}
add_filter( 'woocommerce_checkout_fields' , 'wccs_override_checkout_fields4' );


// ============ Address 2 ===============
function wccs_override_checkout_fields5($fields5) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['checkness']['wccs_opt_5'] ) ) 
unset($fields5['billing']['billing_address_2']);
return $fields5;
}
add_filter( 'woocommerce_checkout_fields' , 'wccs_override_checkout_fields5' );


// ============= City ================
function wccs_override_checkout_fields6($fields6) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['checkness']['wccs_opt_6'] )  ) 
unset($fields6['billing']['billing_city']);
return $fields6;
}
add_filter( 'woocommerce_checkout_fields' , 'wccs_override_checkout_fields6' );


// ============= Postal Code ================
function wccs_override_checkout_fields7($fields7) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['checkness']['wccs_opt_7'] )  ) 
unset($fields7['billing']['billing_postcode']);
return $fields7;
}
add_filter( 'woocommerce_checkout_fields' , 'wccs_override_checkout_fields7' );


// =================== Country==================
function wccs_override_checkout_fields8($fields8) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['checkness']['wccs_opt_8'] )  ) 
unset($fields8['billing']['billing_country']);
return $fields8;
}
add_filter( 'woocommerce_checkout_fields' , 'wccs_override_checkout_fields8' );

function wccs_override_required_fields8( $address_fields8 ) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['checkness']['wccs_rq_8'] ) ) 
	$address_fields8['billing_country']['required'] = false;
	return $address_fields8;
}
add_filter( 'woocommerce_billing_fields', 'wccs_override_required_fields8', 10, 1 );

function custom_replace_checkout_fieldsh( $fields ) {
$options = get_option( 'wccs_settings' );
if ( ! empty( $options['replace']['label7'] ) ) 
     $fields['billing']['billing_country']['label'] = ''.$options['replace']['label7'].'';
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_replace_checkout_fieldsh' );


// ================= State ===================
function wccs_override_checkout_fields9($fields9) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['checkness']['wccs_opt_9'] ) ) 
unset($fields9['billing']['billing_state']);
return $fields9;
}
add_filter( 'woocommerce_checkout_fields' , 'wccs_override_checkout_fields9' );

// =============== Phone ======================
function wccs_override_checkout_fields10($fields10) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['checkness']['wccs_opt_10'] ) ) 
unset($fields10['billing']['billing_phone']);
return $fields10;
}
add_filter( 'woocommerce_checkout_fields' , 'wccs_override_checkout_fields10' );

function wccs_override_required_fields10( $address_fields10 ) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['checkness']['wccs_rq_10'] ) ) 
	$address_fields10['billing_phone']['required'] = false;
	return $address_fields10;
}
add_filter( 'woocommerce_billing_fields', 'wccs_override_required_fields10', 10, 1 );

// replace label and placeholder
function custom_replace_checkout_fields9( $fields ) {
$options = get_option( 'wccs_settings' );
if ( ! empty( $options['replace']['placeholder3'] ) ) 
     $fields['billing']['billing_phone']['placeholder'] = ''.$options['replace']['placeholder3'].'';
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_replace_checkout_fields9' );


function custom_replace_checkout_fieldsj( $fields ) {
$options = get_option( 'wccs_settings' );
if ( ! empty( $options['replace']['label3'] ) ) 
     $fields['billing']['billing_phone']['label'] = ''.$options['replace']['label3'].'';
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_replace_checkout_fieldsj' );


// ====================  Email ========================
function wccs_override_checkout_fields11($fields11) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['checkness']['wccs_opt_11'] ) ) 
unset($fields11['billing']['billing_email']);
return $fields11;
}
add_filter( 'woocommerce_checkout_fields' , 'wccs_override_checkout_fields11' );

function wccs_override_required_fields11( $address_fields11 ) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['checkness']['wccs_rq_11'] ) ) 
	$address_fields11['billing_email']['required'] = false;
	return $address_fields11;
}
add_filter( 'woocommerce_billing_fields', 'wccs_override_required_fields11', 10, 1 );

// replace label and placeholder
function custom_replace_checkout_fields10( $fields ) {
$options = get_option( 'wccs_settings' );
if ( ! empty( $options['replace']['placeholder4'] ) ) 
     $fields['billing']['billing_email']['placeholder'] = ''.$options['replace']['placeholder4'].'';
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_replace_checkout_fields10' );


function custom_replace_checkout_fieldsk( $fields ) {
$options = get_option( 'wccs_settings' );
if ( ! empty( $options['replace']['label4'] ) ) 
     $fields['billing']['billing_email']['label'] = ''.$options['replace']['label4'].'';
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_replace_checkout_fieldsk' );


// ===================== Order Comments ==========================
function wccs_override_checkout_fields12($fields12) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['checkness']['wccs_opt_12'] ) )
unset($fields12['order']['order_comments']);
return $fields12;
}
add_filter( 'woocommerce_checkout_fields' , 'wccs_override_checkout_fields12' );

// replace label and placeholder
function custom_replace_checkout_fields11( $fields ) {
$options = get_option( 'wccs_settings' );
if ( ! empty( $options['replace']['placeholder11'] ) ) 
     $fields['order']['order_comments']['placeholder'] = ''.$options['replace']['placeholder11'].'';
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_replace_checkout_fields11' );


function custom_replace_checkout_fieldsl( $fields ) {
$options = get_option( 'wccs_settings' );
if ( ! empty( $options['replace']['label11'] ) ) 
     $fields['order']['order_comments']['label'] = ''.$options['replace']['label11'].'';
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_replace_checkout_fieldsl' );

// ====================== reciept hook  ===================
function custom_email_setup_wc_footer() {
$options = get_option( 'wccs_settings' );
		echo ''.$options['footer']['mccs_receipt_footer'].'';
	}
add_action( 'woocommerce_email_footer_text', 'custom_email_setup_wc_footer' );
remove_filter( 'woocommerce_email_footer_text', 'strip_tags' );


function wccs_add_title() {
$options = get_option( 'wccs_settings' );
if (true == ($options['checkness']['checkbox12']) )  
echo '<div class="add_info_wccs"><br><h3>' . __( 'Additional information', 'woocommerce-checkout-manager' ) . '</h3></div>';
}
add_action('woocommerce_after_checkout_billing_form', 'wccs_add_title');

// =============== Add the field to the checkout =====================
function wccs_custom_checkout_field( $checkout ) {

$options = get_option( 'wccs_settings' );

if ( count( $options['buttons'] ) > 0 ) : ?>

<?php
					$i = 0;

					// Loop through each button
					foreach ( $options['buttons'] as $btn ) :

						$label = ( isset( $btn['label'] ) ) ? $btn['label'] : '';

?>				
<?php

if ( ! empty( $btn['label'] ) &&  ($btn['type'] == 'text') ) {

   woocommerce_form_field( ''.$btn['cow'].'' , array(
        'type'          => 'text',
        'class'         => array('wccs-field-class wccs-form-row-wide'), 
        'label'         => ''.$btn['label'].'',
        'required'  => $btn['checkbox'],
        'placeholder'       => ''.$btn['placeholder'].'',

        ), $checkout->get_value( ''.$btn['cow'].'' )); 

}

if ( ! empty( $btn['label'] ) &&  ($btn['type'] == 'date') ) {

echo '<script type="text/javascript">

jQuery(document).ready(function() {
    jQuery(".MyDate-'.$btn['cow'].' #'.$btn['cow'].'").datepicker({
        dateFormat : "dd-mm-yy"
    });
});

</script>';

woocommerce_form_field( ''.$btn['cow'].'' , array(
        'type'          => 'text',
        'class'         => array('wccs-field-class MyDate-'.$btn['cow'].' wccs-form-row-wide'), 
        'label'         => ''.$btn['label'].'',
        'required'  => $btn['checkbox'],
        'placeholder'       => ''.$btn['placeholder'].'',

        ), $checkout->get_value( ''.$btn['cow'].'' )); 
 
}

if ( ! empty( $btn['label'] ) &&  ($btn['type'] == 'checkbox') ) {
woocommerce_form_field( ''.$btn['cow'].'' , array(
        'type'          => 'checkbox',
        'class'         => array('wccs-field-class wccs-form-row-wide'), 
        'label'         => ''.$btn['label'].'',
        'required'  => $btn['checkbox'],
        'placeholder'       => ''.$btn['placeholder'].'',

        ), $checkout->get_value( ''.$btn['cow'].'' )); 
}

?>
<?php 
$i++;
					endforeach;
?>
<?php
endif;

}

add_action('woocommerce_after_checkout_billing_form', 'wccs_custom_checkout_field');


// ============================== Update the order meta with field value ==============================
function wccs_custom_checkout_field_update_order_meta( $order_id ) {

$options = get_option( 'wccs_settings' );
if ( count( $options['buttons'] ) > 0 ) : 
					$i = 0;

					// Loop through each button
					foreach ( $options['buttons'] as $btn ) :

						$label = ( isset( $btn['label'] ) ) ? $btn['label'] : '';
if ( ! empty( $btn['label'] ) )
    if ( $_POST[ ''.$btn['cow'].'' ])
 update_post_meta( $order_id, ''.$btn['label'].'' , esc_attr( $_POST[ ''.$btn['cow'].'' ] ));
$i++;
					endforeach;
endif;
}
add_action('woocommerce_checkout_update_order_meta', 'wccs_custom_checkout_field_update_order_meta');

// =============== Add to email (working) =====================
add_filter('woocommerce_email_order_meta_keys', 'wccs_custom_checkout_field_order_meta_keys');
 
function wccs_custom_checkout_field_order_meta_keys( $keys ) {
$options = get_option( 'wccs_settings' );
if ( count( $options['buttons'] ) > 0 ) : 
					$i = 0;
					// Loop through each button
					foreach ( $options['buttons'] as $btn ) :

						$label = ( isset( $btn['label'] ) ) ? $btn['label'] : '';
if ( ! empty( $btn['label'] ) )
	$keys[] = ''.$btn['label'].'';
$i++;
					endforeach;
return $keys;
endif;
}
// ================ Style the Email =======================
add_action('woocommerce_email_after_order_table', 'wccs_custom_style_checkout_email');
function wccs_custom_style_checkout_email() {
$options = get_option( 'wccs_settings' );
if (true == ($options['checkness']['checkbox1']) )  
echo '<h2>' . __( 'Additional information', 'woocommerce-checkout-manager' ) . '</h2>';
}

// ============== Process the checkout (if needed activate) ==================
function wccs_custom_checkout_field_process() {
$options = get_option( 'wccs_settings' );

if ( count( $options['buttons'] ) > 0 ) : 
					$i = 0;

					// Loop through each button
					foreach ( $options['buttons'] as $btn ) :

						$label = ( isset( $btn['label'] ) ) ? $btn['label'] : '';
    global $woocommerce;

if ( (!$_POST[ ''.$btn['cow'].'' ] )  && (true == ($btn['checkbox']) ) )


$woocommerce->add_error( '<strong>'.$btn['label'].'</strong> '. __('is a required field', 'woocommerce-checkout-manager' ) . ' ');



$i++;
					endforeach;


endif;
}
add_action('woocommerce_checkout_process', 'wccs_custom_checkout_field_process');

function wccs_options_validatew() {
$options = get_option( 'wccs_settings' );
if ( empty( $options['replace']['label'] ) )
			unset( $options['replace']['label'] );
}


function wccs_options_validate( $input ) {

$options = get_option( 'wccs_settings' );


	// Don't save empty inputs
	foreach( $input['buttons'] as $i => $btn ) :

		if ( empty( $btn['label'] ) )
			unset( $input['buttons'][$i], $btn );

	endforeach;

	$input['buttons'] = array_values( $input['buttons'] );

return $input;

}
function wccs_custom_checkout_details( $order_id ) {
$options = get_option( 'wccs_settings' );

if (true == ($options['checkness']['checkbox1']) )  {
echo '<h2>' . __( 'Additional information', 'woocommerce-checkout-manager' ) . '</h2>';
}

if ( count( $options['buttons'] ) > 0 ) : 
					$i = 0;
					// Loop through each button
					foreach ( $options['buttons'] as $btn ) :
						$label = ( isset( $btn['label'] ) ) ? $btn['label'] : '';
$order_object = new WC_Order( $order_id );
echo ''.$btn['label'].': '.get_post_meta( $order_id->id , ''.$btn['label'].'', true).'<br>';
$i++;
					endforeach;
endif;
}
add_action('woocommerce_order_details_after_order_table', 'wccs_custom_checkout_details');





// ================== First Name ===================
function wccs_shipping_checkout_fields1( $fields1 ) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['check']['wccs_opt_1_s'] ) )
unset($fields1['shipping']['shipping_first_name']);
return $fields1;
}
add_filter( 'woocommerce_checkout_fields' , 'wccs_shipping_checkout_fields1' );
 
function wccs_shipping_required_fields1( $address_fields1 ) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['check']['wccs_rq_1_s'] ) ) 
	$address_fields1['shipping_first_name']['required'] = false;
	return $address_fields1;
}
add_filter( 'woocommerce_shipping_fields', 'wccs_shipping_required_fields1', 10, 1 );

// replace label and placeholder
function custom_replace_checkout_fieldss( $fields ) {
$options = get_option( 'wccs_settings' );
if ( ! empty( $options['replace']['placeholder_s'] ) ) 
     $fields['shipping']['shipping_first_name']['placeholder'] = ''.$options['replace']['placeholder_s'].'';
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_replace_checkout_fieldss' );


function custom_replace_checkout_fieldssa( $fields ) {
$options = get_option( 'wccs_settings' );
if ( ! empty( $options['replace']['label_s'] ) ) 
     $fields['shipping']['shipping_first_name']['label'] = ''.$options['replace']['label_s'].'';
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_replace_checkout_fieldssa' );


// ========== Last Name ==============
function wccs_shipping_checkout_fields2( $fields2 ) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['check']['wccs_opt_2_s'] ) ) 
unset($fields2['shipping']['shipping_last_name']);
return $fields2;
}
add_filter( 'woocommerce_checkout_fields' , 'wccs_shipping_checkout_fields2' );

function wccs_shipping_required_fields2( $address_fields2 ) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['check']['wccs_rq_2_s'] ) ) 
	$address_fields2['shipping_last_name']['required'] = false;
	return $address_fields2;
}
add_filter( 'woocommerce_shipping_fields', 'wccs_shipping_required_fields2', 10, 1 );

// replace label and placeholder
function custom_replace_checkout_fieldss1( $fields ) {
$options = get_option( 'wccs_settings' );
if ( ! empty( $options['replace']['placeholder_s1'] ) ) 
     $fields['shipping']['shipping_last_name']['placeholder'] = ''.$options['replace']['placeholder_s1'].'';
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_replace_checkout_fieldss1' );


function custom_replace_checkout_fieldssb( $fields ) {
$options = get_option( 'wccs_settings' );
if ( ! empty( $options['replace']['label_s1'] ) ) 
     $fields['shipping']['shipping_last_name']['label'] = ''.$options['replace']['label_s1'].'';
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_replace_checkout_fieldssb' );


// ============ Company ==============
function wccs_shipping_checkout_fields3( $fields3 ) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['check']['wccs_opt_3_s'] ) ) 
unset($fields3['shipping']['shipping_company']);
return $fields3;
}
add_filter( 'woocommerce_checkout_fields' , 'wccs_shipping_checkout_fields3' );

// replace label and placeholder
function custom_replace_checkout_fieldss2( $fields ) {
$options = get_option( 'wccs_settings' );
if ( ! empty( $options['replace']['placeholder_s2'] ) ) 
     $fields['shipping']['shipping_company']['placeholder'] = ''.$options['replace']['placeholder_s2'].'';
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_replace_checkout_fieldss2' );


function custom_replace_checkout_fieldssc( $fields ) {
$options = get_option( 'wccs_settings' );
if ( ! empty( $options['replace']['label_s2'] ) ) 
     $fields['shipping']['shipping_company']['label'] = ''.$options['replace']['label_s2'].'';
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_replace_checkout_fieldssc' );

// Hook in
add_filter( 'woocommerce_checkout_fields' , 'b1_custom_override_checkout_fields' );

// Our hooked in function - $fields is passed via the filter!
function b1_custom_override_checkout_fields( $fields ) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['check']['wccs_opt_4_s'] ) ) 
     unset($fields['shipping']['shipping_address_1']);

     return $fields;
}
// Hook in
add_filter( 'woocommerce_checkout_fields' , 'b2_custom_override_checkout_fields' );

// Our hooked in function - $fields is passed via the filter!
function b2_custom_override_checkout_fields( $fields ) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['check']['wccs_opt_5_s'] ) ) 
     unset($fields['shipping']['shipping_address_2']);

     return $fields;
}

// Hook in
add_filter( 'woocommerce_checkout_fields' , 'c_custom_override_checkout_fields' );

// Our hooked in function - $fields is passed via the filter!
function c_custom_override_checkout_fields( $fields ) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['check']['wccs_opt_6_s'] ) ) 
     unset($fields['shipping']['shipping_city']);

     return $fields;
}

// Hook in
add_filter( 'woocommerce_checkout_fields' , 'sp_custom_override_checkout_fields' );

// Our hooked in function - $fields is passed via the filter!
function sp_custom_override_checkout_fields( $fields ) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['check']['wccs_opt_7_s'] ) ) 
     unset($fields['shipping']['shipping_postcode']);

     return $fields;
}


// =================== Shipping Country ======================
// Hook in
add_filter( 'woocommerce_checkout_fields' , 'sc_custom_override_checkout_fields' );

// Our hooked in function - $fields is passed via the filter!
function sc_custom_override_checkout_fields( $fields ) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['check']['wccs_opt_8_s'] ) ) 
     unset($fields['shipping']['shipping_country']);

     return $fields;
}

// replace label and placeholder
function custom_replace_checkout_fieldssd( $fields ) {
$options = get_option( 'wccs_settings' );
if ( ! empty( $options['replace']['label_s7'] ) ) 
     $fields['shipping']['shipping_country']['label'] = ''.$options['replace']['label_s7'].'';
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_replace_checkout_fieldssd' );


// Hook in
add_filter( 'woocommerce_checkout_fields' , 'ss_custom_override_checkout_fields' );

// Our hooked in function - $fields is passed via the filter!
function ss_custom_override_checkout_fields( $fields ) {
$options = get_option( 'wccs_settings' );
if (  1 == ($options['check']['wccs_opt_9_s'] ) ) 
     unset($fields['shipping']['shipping_state']);

     return $fields;
}

function display_front_wccs() {
echo '<style type="text/css">
.wccs-field-class {
float:left;
width: 47%;
}
.wccs-field-class:nth-child(2n+2) {
float: right;
}
.add_info_wccs {
clear: both;
}
</style>';
}
add_action('wp_head','display_front_wccs');