jQuery(document).ready(function() {
jQuery(function() {
jQuery('#select_all_rm').click(function() {
    var c = this.checked;
    jQuery('.rm').prop('checked',c);
});
});
});

jQuery(document).ready(function() {
jQuery(function() {
jQuery('#select_all_rm_s').click(function() {
    var c = this.checked;
    jQuery('.rm_s').prop('checked',c);
});
});
});

jQuery(document).ready(function() {
jQuery(function() {
jQuery('#select_all_rq').click(function() {
    var c = this.checked;
    jQuery('.rq').prop('checked',c);
});
});
});

jQuery(document).ready(function() {
jQuery(function() {
jQuery('#select_all_rq_s').click(function() {
    var c = this.checked;
    jQuery('.rq_s').prop('checked',c);
});
});
});

// Javascript for adding new field
jQuery(document).ready( function() {

	/**
	 * Credits to the Advanced Custom Fields plugin for this code
	 */

	// Update Order Numbers
	function update_order_numbers(div) {
		div.children('tbody').children('tr.wccs-row').each(function(i) {
			jQuery(this).children('td.wccs-order').html(i+1);
		});
	}
	
	// Make Sortable
	function make_sortable(div){
		var fixHelper = function(e, ui) {
			ui.children().each(function() {
				jQuery(this).width(jQuery(this).width());
			});
			return ui;
		};

		div.children('tbody').unbind('sortable').sortable({
			update: function(event, ui){
				update_order_numbers(div);
			},
			handle: 'td.wccs-order',
			helper: fixHelper
		});
	}

	var div = jQuery('.wccs-table'),
		row_count = div.children('tbody').children('tr.wccs-row').length;

	// Make the table sortable
	make_sortable(div);
	
	// Add button
	jQuery('#wccs-add-button').live('click', function(){

		var div = jQuery('.wccs-table'),			
			row_count = div.children('tbody').children('tr.wccs-row').length,
			new_field = div.children('tbody').children('tr.wccs-clone').clone(false); // Create and add the new field

		new_field.attr( 'class', 'wccs-row' );

		// Update names
		new_field.find('[name]').each(function(){
			var count = parseInt(row_count);
			var name = jQuery(this).attr('name').replace('[999]','[' + count + ']');
			jQuery(this).attr('name', name);
		});

		// Add row
		div.children('tbody').append(new_field); 
		update_order_numbers(div);

		// There is now 1 more row
		row_count ++;

		return false;	
	});

	// Remove button
	jQuery('.wccs-table .wccs-remove-button').live('click', function(){
		var div = jQuery('.wccs-table'),
			tr = jQuery(this).closest('tr');

		tr.animate({'left' : '50px', 'opacity' : 0}, 250, function(){
			tr.remove();
			update_order_numbers(div);
		});

		return false;
	});
});