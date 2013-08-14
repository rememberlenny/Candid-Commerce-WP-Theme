<?php

/**
 * wc_table_rate_admin_shipping_rows function.
 *
 * @access public
 * @param mixed $table_rate_shipping
 */
function wc_table_rate_admin_shipping_rows( $table_rate_shipping ) {
	global $woocommerce;

	// Get shipping classes
	$shipping_classes = get_terms( 'product_shipping_class', 'hide_empty=0' );
	?>
	<table id="shipping_rates" class="shippingrows widefat" cellspacing="0" style="position:relative;">
		<thead>
			<tr>
				<th class="check-column"><input type="checkbox"></th>
				<th><?php _e('Shipping Class', 'wc_table_rate'); ?>&nbsp;<a class="tips" data-tip="<?php _e('Shipping class this rate applies to.', 'wc_table_rate'); ?>">[?]</a></th>
				<th><?php _e('Condition', 'wc_table_rate'); ?>&nbsp;<a class="tips" data-tip="<?php _e('Condition vs. destination', 'wc_table_rate'); ?>">[?]</a></th>
				<th><?php _e('Min', 'wc_table_rate'); ?>&nbsp;<a class="tips" data-tip="<?php _e('Bottom range for the selected condition. ', 'wc_table_rate'); ?>">[?]</a></th>
				<th><?php _e('Max', 'wc_table_rate'); ?>&nbsp;<a class="tips" data-tip="<?php _e('Top range for the selected condition. ', 'wc_table_rate'); ?>">[?]</a></th>
				<th><?php _e('Cost', 'wc_table_rate'); ?>&nbsp;<a class="tips" data-tip="<?php _e('Cost for shipping the order, excluding tax.', 'wc_table_rate'); ?>">[?]</a></th>
				<th class="cost_per_item"><?php _e('Item cost', 'wc_table_rate'); ?>&nbsp;<a class="tips" data-tip="<?php _e('Cost per item, excluding tax.', 'wc_table_rate'); ?>">[?]</a></th>
				<th class="cost_per_weight"><?php echo __('Cost per', 'wc_table_rate') . ' ' . get_option('woocommerce_weight_unit'); ?>&nbsp;<a class="tips" data-tip="<?php _e('Cost per item, excluding tax.', 'wc_table_rate'); ?>">[?]</a></th>
				<th class="cost_percent"><?php echo __('Cost %', 'wc_table_rate'); ?>&nbsp;<a class="tips" data-tip="<?php _e('Percentage of total to charge.', 'wc_table_rate'); ?>">[?]</a></th>
				<th class="shipping_label"><?php _e('Label', 'wc_table_rate'); ?>&nbsp;<a class="tips" data-tip="<?php _e('Label for the shipping method which the user will be presented. ', 'wc_table_rate'); ?>">[?]</a></th>
				<th width="1%"><?php _e('Break', 'wc_table_rate'); ?>&nbsp;<a class="tips" data-tip="<?php _e('Enable this option to offer this rate and no others when using per-order rates, or to stop any further rates being matched when using calculated rates. Priority is given to rates at the top of the list.', 'wc_table_rate'); ?>">[?]</a></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th colspan="2"><a href="#" class="add button"><?php _e('+ Add Shipping Rate', 'wc_table_rate'); ?></a></th>
				<th colspan="9"><span class="description"><?php _e('Define your table rates here. You can drag and drop rates to prioritise them.', 'wc_table_rate'); ?></span> <a href="#" class="dupe button"><?php _e('Duplicate selected rows', 'wc_table_rate'); ?></a> <a href="#" class="remove button"><?php _e('Delete selected rows', 'wc_table_rate'); ?></a></th>
			</tr>
		</tfoot>
		<tbody class="table_rates">
    	<?php
    	$i = -1; foreach( $table_rate_shipping->get_shipping_rates() as $rate ) : $i++;

    		echo '<tr class="table_rate">
    			<td class="check-column">
    				<input type="checkbox" name="select" />
    				<input type="hidden" class="rate_id" name="rate_id[' . $i . ']" value="' . $rate->rate_id . '" />
    			</td>
    			<td><select class="select" name="shipping_class[' . $i . ']" style="min-width:100px;">
    				<option value="" ' . selected( $rate->rate_class == "", true, false ) . '>' . __('Any shipping class', 'wc_table_rate') . '</option>
    				<option value="0" ' . selected( $rate->rate_class == '0', true, false ) . '>' . __('No shipping class', 'wc_table_rate') . '</option>';

    		foreach ( $shipping_classes as $class )
    			echo '<option value="' . $class->term_id . '" ' . selected( $rate->rate_class, $class->term_id, false ) . '>' . $class->name . '</option>';

    		echo '</select></td>
                <td><select class="select" name="shipping_condition[' . $i . ']" style="min-width:100px;">
                	<option value="">' . __('None', 'wc_table_rate') . '</option>
		            <option value="price" ' . selected( $rate->rate_condition, 'price', false ) . '>' . __('Price', 'wc_table_rate') . '</option>
		            <option value="weight" ' . selected( $rate->rate_condition, 'weight', false ) . '>' . __('Weight', 'wc_table_rate') . '</option>
		            <option value="items" ' . selected( $rate->rate_condition, 'items', false ) . '>' . __('Item count', 'wc_table_rate') . '</option>
		            <option value="items_in_class" ' . selected( $rate->rate_condition, 'items_in_class', false ) . '>' . __('Item count (same class)', 'wc_table_rate') . '</option>
		        </select></td>
                <td><input type="text" class="text" value="' . $rate->rate_min . '" name="shipping_min[' . $i . ']" placeholder="' . __('n/a', 'wc_table_rate') . '" size="4" /></td>
                <td><input type="text" class="text" value="' . $rate->rate_max . '" name="shipping_max[' . $i . ']" placeholder="' . __('n/a', 'wc_table_rate') . '" size="4" /></td>
                <td>
                	<input type="text" class="text" value="' . $rate->rate_cost . '" name="shipping_cost[' . $i . ']" placeholder="' . __('0.00', 'wc_table_rate') . '" size="4" />
                </td>
          		<td class="cost_per_item"><input type="text" class="text" value="' . $rate->rate_cost_per_item . '" name="shipping_per_item[' . $i . ']" placeholder="' . __('0.00', 'wc_table_rate') . '" size="4" /></td>
          		<td class="cost_per_weight"><input type="text" class="text" value="' . $rate->rate_cost_per_weight_unit . '" name="shipping_cost_per_weight[' . $i . ']" placeholder="' . __('0.00', 'wc_table_rate') . '" size="4" /></td>
          		<td class="cost_percent"><input type="text" class="text" value="' . $rate->rate_cost_percent . '" name="shipping_cost_percent[' . $i . ']" placeholder="' . __('0', 'wc_table_rate') . '" size="4" /></td>
                <td class="shipping_label"><input type="text" class="text" value="' . $rate->rate_label . '" name="shipping_label[' . $i . ']" size="8" /></td>
                <td width="1%"><input type="checkbox" class="checkbox" ' . checked( $rate->rate_priority, 1, false ) . ' name="shipping_priority[' . $i . ']" /></td>
            </tr>';
    	endforeach;
    	?>
    	</tbody>
    </table>

	<?php

	// Javascript for Table Rates admin
	ob_start();
	?>
	// Options which depend on calc type
	jQuery('#woocommerce_table_rate_calculation_type').change(function(){

		var selected = jQuery( this ).val();

		if ( selected == 'item' ) {

			jQuery( 'td.cost_per_item, th.cost_per_item' ).hide();

		} else {

			jQuery( 'td.cost_per_item, th.cost_per_item' ).show();

		}

		if ( selected ) {

			jQuery( '#shipping_class_priorities' ).hide();

		} else {

			jQuery( '#shipping_class_priorities' ).show();

		}

		if ( selected ) {

			jQuery( 'td.shipping_label, th.shipping_label' ).hide();

		} else {

			jQuery( 'td.shipping_label, th.shipping_label' ).show();

		}

		if ( ! selected ) {

			jQuery( '#shipping_class_priorities span.description.per_order' ).show();
			jQuery( '#shipping_class_priorities span.description.per_class' ).hide();

		}

	}).change();

	// shipping_condition select box
	jQuery('select[name^="shipping_condition"]').change(function(){

		var selected = jQuery( this ).val();
		var $row 	 = jQuery( this ).closest('tr');

		if ( selected == '' ) {

			$row.find('input[name^="shipping_min"], input[name^="shipping_max"]').val('').attr('disabled', 'disabled').addClass('disabled');

		} else {

			$row.find('input[name^="shipping_min"], input[name^="shipping_max"]').removeAttr('disabled').removeClass('disabled');

		}

	}).change();

	// Add rates
	jQuery('#shipping_rates a.add').live('click', function(){

		var size = jQuery('tbody.table_rates .table_rate').size();

		jQuery('<tr class="table_rate">\
    			<td class="check-column">\
    				<input type="checkbox" name="select" />\
    				<input type="hidden" class="rate_id" name="rate_id[' + size + ']" value="0" />\
    			</td>\
    			<td><select class="select" name="shipping_class[' + size + ']" style="min-width:100px;">\
    				<option value=""><?php _e('Any shipping class', 'wc_table_rate'); ?></option>\
    				<option value="0"><?php _e('No shipping class', 'wc_table_rate'); ?></option>\
    				<?php foreach ( $shipping_classes as $class )
	    				echo '<option value="' . $class->term_id . '">' . esc_js( $class->name ) . '</option>';
	    			?></select></td>\
                <td><select class="select" name="shipping_condition[' + size + ']" style="min-width:100px;">\
                	<option value=""><?php _e('None', 'wc_table_rate'); ?></option>\
		            <option value="price"><?php _e('Price', 'wc_table_rate'); ?></option>\
		            <option value="weight"><?php _e('Weight', 'wc_table_rate'); ?></option>\
		            <option value="items"><?php _e('Item count', 'wc_table_rate'); ?></option>\
		            <option value="items_in_class"><?php _e('Item count (same class)', 'wc_table_rate'); ?></option>\
		        </select></td>\
                <td><input type="text" class="text" name="shipping_min[' + size + ']" placeholder="<?php _e('n/a', 'wc_table_rate'); ?>" size="4" /></td>\
                <td><input type="text" class="text" name="shipping_max[' + size + ']" placeholder="<?php _e('n/a', 'wc_table_rate'); ?>" size="4" /></td>\
                <td>\
                	<input type="text" class="text" name="shipping_cost[' + size + ']" placeholder="<?php _e('0.00', 'wc_table_rate'); ?>" size="4" />\
                </td>\
          		<td class="cost_per_item"><input type="text" class="text" name="shipping_per_item[' + size + ']" placeholder="<?php _e('0.00', 'wc_table_rate'); ?>" size="4" /></td>\
          		<td class="cost_per_weight"><input type="text" class="text" name="shipping_cost_per_weight[' + size + ']" placeholder="<?php _e('0.00', 'wc_table_rate'); ?>" size="4" /></td>\
          		<td class="cost_percent"><input type="text" class="text" name="shipping_cost_percent[' + size + ']" placeholder="<?php _e('0', 'wc_table_rate'); ?>" size="4" /></td>\
                <td class="shipping_label"><input type="text" class="text" name="shipping_label[' + size + ']" size="8" /></td>\
                <td width="1%"><input type="checkbox" class="checkbox" name="shipping_priority[' + size + ']" /></td>\
            </tr>').appendTo('#shipping_rates tbody.table_rates');

		jQuery('#woocommerce_table_rate_calculation_type').change();

		return false;
	});

	// Remove rows
	jQuery('#shipping_rates a.remove').live('click', function(){
		var answer = confirm("<?php _e('Delete the selected rates?', 'wc_table_rate'); ?>")
		if (answer) {

			var rate_ids  = [];

			jQuery('#shipping_rates tbody.table_rates tr td.check-column input:checked').each(function(i, el){

				var rate_id = jQuery(el).closest('tr.table_rate').find('.rate_id').val();

				rate_ids.push( rate_id );

				jQuery(el).closest('tr.table_rate').addClass('deleting');

			});

			var data = {
				action: 'woocommerce_table_rate_delete',
				rate_id: rate_ids,
				security: '<?php echo wp_create_nonce("delete-rate"); ?>'
			};

			jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
				jQuery('tr.deleting').fadeOut('300', function(){
					jQuery(this).remove();
				});
			});
		}
		return false;
	});

	// Dupe row
	jQuery('#shipping_rates a.dupe').live('click', function(){
		var answer = confirm("<?php _e('Duplicate the selected rates?', 'wc_table_rate'); ?>")
		if (answer) {
			jQuery('#shipping_rates tbody.table_rates tr td.check-column input:checked').each(function(i, el){
				var dupe = jQuery(el).closest('tr').clone();

				dupe.find('.rate_id').val('0');

				// Append
				jQuery('#shipping_rates tbody.table_rates').append( dupe );
			});

			// Re-index keys
			var loop = 0;
			jQuery('tbody.table_rates .table_rate').each(function( index, row ){
				jQuery('input, select', row).each(function( i, el ){

					var t = jQuery(el);
					t.attr('name', t.attr('name').replace(/\[([^[]*)\]/, "[" + loop + "]"));

				});
				loop++;
			});
		}
		return false;
	});

	// Rate ordering
	jQuery('#shipping_rates tbody.table_rates').sortable({
		items:'tr',
		cursor:'move',
		axis:'y',
		handle: 'td',
		scrollSensitivity:40,
		helper:function(e,ui){
			ui.children().each(function(){
				jQuery(this).width(jQuery(this).width());
			});
			ui.css('left', '0');
			return ui;
		},
		start:function(event,ui){
			ui.item.css('background-color','#f6f6f6');
		},
		stop:function(event,ui){
			ui.item.removeAttr('style');
			shipping_rates_row_indexes();
		}
	});

	function shipping_rates_row_indexes() {
		// Re-index keys
		var loop = 0;
		jQuery('#shipping_rates tr.table_rate').each(function( index, row ){
			jQuery('input.text, input.checkbox, select.select', row).each(function( i, el ){

				var t = jQuery(el);
				t.attr('name', t.attr('name').replace(/\[([^[]*)\]/, "[" + loop + "]"));

			});
			loop++;
		});
	};
	<?php

	$js = ob_get_clean();

	$woocommerce->add_inline_js( $js );
}

/**
 * wc_table_rate_admin_shipping_class_priorities function.
 *
 * @access public
 * @return void
 */
function wc_table_rate_admin_shipping_class_priorities( $shipping_method_id ) {
	global $woocommerce;

	$classes = $woocommerce->shipping->get_shipping_classes();
	if (!$classes) :
		echo '<p class="description">' . __('No shipping classes exist - you can ignore this option :)', 'wc_table_rate') . '</p>';
	else :
		$priority = get_option( 'woocommerce_table_rate_default_priority_' . $shipping_method_id ) != '' ? get_option( 'woocommerce_table_rate_default_priority_' . $shipping_method_id ) : 10;
		?>
		<table class="widefat" style="position:relative;">
			<thead>
				<tr>
					<th><?php _e('Class', 'wc_table_rate'); ?></th>
					<th><?php _e('Priority', 'wc_table_rate'); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="2">
						<span class="description per_order"><?php _e('When calculating shipping, the cart contents will be <strong>searched for all shipping classes</strong>. If all product shipping classes are <strong>identical</strong>, the corresponding class will be used.<br/><strong>If there are a mix of classes</strong> then the class with the <strong>lowest number priority</strong> (defined above) will be used.', 'wc_table_rate'); ?></span>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<th><?php _e('Default', 'wc_table_rate'); ?></th>
					<td><input type="text" size="2" name="woocommerce_table_rate_default_priority" value="<?php echo $priority; ?>" /></td>
				</tr>
    			<?php
    			$woocommerce_table_rate_priorities = get_option( 'woocommerce_table_rate_priorities_' . $shipping_method_id );
        		foreach ($classes as $class) {
					$priority = (isset($woocommerce_table_rate_priorities[$class->slug])) ? $woocommerce_table_rate_priorities[$class->slug] : 10;

					echo '<tr><th>'.$class->name.'</th><td><input type="text" value="'.$priority.'" size="2" name="woocommerce_table_rate_priorities['.$class->slug.']" /></td></tr>';

				}
				?>
			</tbody>
		</table>
		<?php
	endif;
}

/**
 * wc_table_rate_admin_shipping_rows_process function.
 *
 * @access public
 * @return void
 */
function wc_table_rate_admin_shipping_rows_process( $shipping_method_id ) {
	global $woocommerce, $wpdb;

	// Clear cache
	$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_wc_ship_%')" );

	// Save class priorities
	if ( empty( $_POST['woocommerce_table_rate_calculation_type'] ) ) {

		if ( isset( $_POST['woocommerce_table_rate_priorities'] ) ) {
			$priorities = array_map('intval', (array) $_POST['woocommerce_table_rate_priorities']);
			update_option( 'woocommerce_table_rate_priorities_' . $shipping_method_id, $priorities );
		}

		if ( isset( $_POST['woocommerce_table_rate_default_priority'] ) ) {
			update_option('woocommerce_table_rate_default_priority_' . $shipping_method_id, (int) esc_attr( $_POST['woocommerce_table_rate_default_priority'] ) );
		}

	} else {
		delete_option( 'woocommerce_table_rate_priorities_' . $shipping_method_id );
		delete_option( 'woocommerce_table_rate_default_priority_' . $shipping_method_id );
	}

	// Save rates
	$rate_ids			 		= isset( $_POST['rate_id'] ) ? array_map( 'intval', $_POST['rate_id'] ) : array();
	$shipping_class 			= isset( $_POST['shipping_class'] ) ? array_map( 'woocommerce_clean', $_POST['shipping_class'] ) : array();
	$shipping_condition 		= isset( $_POST['shipping_condition'] ) ? array_map( 'woocommerce_clean', $_POST['shipping_condition'] ) : array();
	$shipping_min 				= isset( $_POST['shipping_min'] ) ? array_map( 'woocommerce_clean', $_POST['shipping_min'] ) : array();
	$shipping_max 				= isset( $_POST['shipping_max'] ) ? array_map( 'woocommerce_clean', $_POST['shipping_max'] ) : array();
	$shipping_cost 				= isset( $_POST['shipping_cost'] ) ? array_map( 'woocommerce_clean', $_POST['shipping_cost'] ) : array();
	$shipping_per_item 			= isset( $_POST['shipping_per_item'] ) ? array_map( 'woocommerce_clean', $_POST['shipping_per_item'] ) : array();
	$shipping_cost_per_weight	= isset( $_POST['shipping_cost_per_weight'] ) ? array_map( 'woocommerce_clean', $_POST['shipping_cost_per_weight'] ) : array();
	$cost_percent				= isset( $_POST['shipping_cost_percent'] ) ? array_map( 'woocommerce_clean', $_POST['shipping_cost_percent'] ) : array();
	$shipping_label 			= isset( $_POST['shipping_label'] ) ? array_map( 'woocommerce_clean', $_POST['shipping_label'] ) : array();
	$shipping_priority 			= ( isset( $_POST['shipping_priority'] ) ) ? array_map( 'woocommerce_clean', $_POST['shipping_priority'] ) : array();

	// Get max key
	$max_key = ( $rate_ids ) ? max( array_keys( $rate_ids ) ) : 0;

	for ( $i = 0; $i <= $max_key; $i++ ) {

		if ( ! isset( $rate_ids[ $i ] ) ) continue;

		$rate_id					= $rate_ids[ $i ];
		$rate_class 				= $shipping_class[ $i ];
		$rate_condition				= $shipping_condition[ $i ];
		$rate_min					= isset( $shipping_min[ $i ] ) ? $shipping_min[ $i ] : '';
		$rate_max					= isset( $shipping_max[ $i ] ) ? $shipping_max[ $i ] : '';
		$rate_cost					= rtrim( rtrim( number_format( (double) $shipping_cost[ $i ], 4, '.', '' ), '0' ), '.' );
		$rate_cost_per_item			= rtrim( rtrim( number_format( (double) $shipping_per_item[ $i ], 4, '.', '' ), '0' ), '.' );
		$rate_cost_per_weight_unit	= rtrim( rtrim( number_format( (double) $shipping_cost_per_weight[ $i ], 4, '.', '' ), '0' ), '.' );
		$rate_cost_percent			= rtrim( rtrim( number_format( (double) str_replace( '%', '', $cost_percent[ $i ] ), 2, '.', '' ), '0' ), '.' );
		$rate_label					= $shipping_label[ $i ];
		$rate_priority				= isset( $shipping_priority[ $i ] ) ? 1 : 0;

		// Format min and max
		switch ( $rate_condition ) {
			case 'weight' :
			case 'price' :
				if ( $rate_min ) $rate_min = number_format( $rate_min, 2, '.', '' );
				if ( $rate_max ) $rate_max = number_format( $rate_max, 2, '.', '' );
			break;
			case 'items' :
			case 'items_in_class' :
				if ( $rate_min ) $rate_min = round( $rate_min );
				if ( $rate_max ) $rate_max = round( $rate_max );
			break;
			default :
				$rate_min = $rate_max = '';
			break;
		}

		if ( $rate_id > 0 ) {

			// Update row
			$wpdb->update(
				$wpdb->prefix . 'woocommerce_shipping_table_rates',
				array(
					'rate_class'				=> $rate_class,
					'rate_condition' 			=> sanitize_title( $rate_condition ),
					'rate_min'					=> $rate_min,
					'rate_max'					=> $rate_max,
					'rate_cost'					=> $rate_cost,
					'rate_cost_per_item'		=> $rate_cost_per_item,
					'rate_cost_per_weight_unit'	=> $rate_cost_per_weight_unit,
					'rate_cost_percent'			=> $rate_cost_percent,
					'rate_label'				=> $rate_label,
					'rate_priority'				=> $rate_priority,
					'rate_order'				=> $i,
					'shipping_method_id'		=> $shipping_method_id
				),
				array(
					'rate_id' => $rate_id
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%d',
					'%d'
				),
				array(
					'%d'
				)
			);

		} else {

			// Insert row
			$result = $wpdb->insert(
				$wpdb->prefix . 'woocommerce_shipping_table_rates',
				array(
					'rate_class'				=> $rate_class,
					'rate_condition' 			=> sanitize_title( $rate_condition ),
					'rate_min'					=> $rate_min,
					'rate_max'					=> $rate_max,
					'rate_cost'					=> $rate_cost,
					'rate_cost_per_item'		=> $rate_cost_per_item,
					'rate_cost_per_weight_unit'	=> $rate_cost_per_weight_unit,
					'rate_cost_percent'			=> $rate_cost_percent,
					'rate_label'				=> $rate_label,
					'rate_priority'				=> $rate_priority,
					'rate_order'				=> $i,
					'shipping_method_id'		=> $shipping_method_id
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%d',
					'%d'
				)
			);

		}

	}
}