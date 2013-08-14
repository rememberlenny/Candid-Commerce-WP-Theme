<?php

/**
 * WC_Brands_Admin class.
 */
class WC_Brands_Admin {

	var $settings_tabs;
	var $current_tab;
	var $fields = array();

	/**
	 * __construct function.
	 */
	function __construct() {

		$this->current_tab = ( isset($_GET['tab'] ) ) ? $_GET['tab'] : 'general';

		add_action( 'admin_enqueue_scripts', array( &$this, 'scripts' ) );
		add_action( 'product_brand_add_form_fields', array( &$this, 'add_thumbnail_field' ) );
		add_action( 'product_brand_edit_form_fields', array( &$this, 'edit_thumbnail_field' ), 10, 2 );
		add_action( 'created_term', array( &$this, 'thumbnail_field_save' ), 10, 3 );
		add_action( 'edit_term', array( &$this, 'thumbnail_field_save' ), 10, 3 );
		add_action( 'product_brand_pre_add_form', array( &$this, 'taxonomy_description' ) );
		add_filter( 'woocommerce_sortable_taxonomies', array( &$this, 'sort_brands' ) );
		add_filter( 'manage_edit-product_brand_columns', array( &$this, 'columns' ) );
		add_filter( 'manage_product_brand_custom_column', array( &$this, 'column' ), 10, 3);

		$this->settings_tabs = array(
			'brands' => __( 'Brands', 'wc_brands' )
		);

		// Load in the new settings tabs.
		/*add_action( 'woocommerce_settings_tabs', array( &$this, 'add_tab' ), 10 );

		// Run these actions when generating the settings tabs.
		foreach ( $this->settings_tabs as $name => $label ) {
			add_action( 'woocommerce_settings_tabs_' . $name, array( &$this, 'settings_tab_action' ), 10 );
			add_action( 'woocommerce_update_options_' . $name, array( &$this, 'save_settings' ), 10 );
		}*/

		// Add the settings fields to each tab.
		//add_action( 'woocommerce_brands_settings', array( &$this, 'add_settings_fields' ), 10 );
		$this->init_form_fields();
		add_action( 'woocommerce_settings_catalog_options_after', array( &$this, 'admin_settings' ) );
		add_action( 'woocommerce_update_options_catalog', array( &$this, 'save_admin_settings' ) );
    }

    /**
     * add_tab function.
     *
     * @access public
     * @return void

    function add_tab() {
		foreach ( $this->settings_tabs as $name => $label ) {
			$class = 'nav-tab';
			if( $this->current_tab == $name ) $class .= ' nav-tab-active';
			echo '<a href="' . admin_url( 'admin.php?page=woocommerce&tab=' . $name ) . '" class="' . $class . '">' . $label . '</a>';
		}
	}

	/**
	 * settings_tab_action()
	 *
	 * Do this when viewing our custom settings tab(s). One function for all tabs.

	function settings_tab_action() {
		global $woocommerce_settings;

		// Determine the current tab in effect.
		$current_tab = $this->get_tab_in_view( current_filter(), 'woocommerce_settings_tabs_' );

		// Hook onto this from another function to keep things clean.
		do_action( 'woocommerce_brands_settings' );

		// Display settings for this tab (make sure to add the settings to the tab).
		woocommerce_admin_fields( $woocommerce_settings[ $current_tab ] );
	}

	/**
	 * add_settings_fields()
	 *
	 * Add settings fields for each tab.

	function add_settings_fields() {
		global $woocommerce_settings;

		// Load the prepared form fields.
		$this->init_form_fields();

		if ( is_array( $this->fields ) ) :
			foreach ( $this->fields as $k => $v ) :
				$woocommerce_settings[$k] = $v;
			endforeach;
		endif;
	}

	/**
	 * get_tab_in_view()
	 *
	 * Get the tab current in view/processing.

	function get_tab_in_view ( $current_filter, $filter_base ) {
		return str_replace( $filter_base, '', $current_filter );
	}*/

	/**
	 * init_form_fields()
	 *
	 * Prepare form fields to be used in the various tabs.
	 */
	function init_form_fields() {

		// Define settings
		$this->settings = apply_filters( 'woocommerce_brands_settings_fields', array(

			array(	'name' => __( 'Brands Archives', 'wc_brands' ), 'type' => 'title','desc' => '', 'id' => 'brands_archives' ),

			array(
				'name' 		=> __( 'Show description', 'wc_brands' ),
				'desc' 		=> __( 'Choose to show the brand description on the archive page. Turn this off if you intend to use the description widget instead.', 'wc_brands' ),
				'tip' 		=> '',
				'id' 		=> 'wc_brands_show_description',
				'css' 		=> '',
				'std' 		=> 'yes',
				'type' 		=> 'checkbox',
			),

			array( 'type' => 'sectionend', 'id' => 'brands_archives' ),

		) ); // End brands settings
	}


    /**
     * scripts function.
     *
     * @access public
     * @return void
     */
    function scripts() {
    	$screen = get_current_screen();

	    if ( in_array( $screen->id, array( 'edit-product_brand' ) ) ) {
			wp_enqueue_media();
		}
    }

	/**
	 * admin_settings function.
	 *
	 * @access public
	 */
	function admin_settings() {
		woocommerce_admin_fields( $this->settings );
	}

	/**
	 * save_admin_settings function.
	 *
	 * @access public
	 */
	function save_admin_settings() {
		woocommerce_update_options( $this->settings );
	}

	/**
	 * Category thumbnails
	 */
	function add_thumbnail_field() {
		global $woocommerce;
		?>
		<div class="form-field">
			<label><?php _e( 'Thumbnail', 'wc_brands' ); ?></label>
			<div id="product_cat_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo woocommerce_placeholder_img_src(); ?>" width="60px" height="60px" /></div>
			<div style="line-height:60px;">
				<input type="hidden" id="product_cat_thumbnail_id" name="product_cat_thumbnail_id" />
				<button type="submit" class="upload_image_button button"><?php _e('Upload/Add image', 'wc_brands'); ?></button>
				<button type="submit" class="remove_image_button button"><?php _e('Remove image', 'wc_brands'); ?></button>
			</div>
			<script type="text/javascript">

				jQuery(function(){
					 // Only show the "remove image" button when needed
					 if ( ! jQuery('#product_cat_thumbnail_id').val() )
						 jQuery('.remove_image_button').hide();

					// Uploading files
					var file_frame;

					jQuery(document).on( 'click', '.upload_image_button', function( event ){

						event.preventDefault();

						// If the media frame already exists, reopen it.
						if ( file_frame ) {
							file_frame.open();
							return;
						}

						// Create the media frame.
						file_frame = wp.media.frames.downloadable_file = wp.media({
							title: '<?php _e( 'Choose an image', 'woocommerce' ); ?>',
							button: {
								text: '<?php _e( 'Use image', 'woocommerce' ); ?>',
							},
							multiple: false
						});

						// When an image is selected, run a callback.
						file_frame.on( 'select', function() {
							attachment = file_frame.state().get('selection').first().toJSON();

							jQuery('#product_cat_thumbnail_id').val( attachment.id );
							jQuery('#product_cat_thumbnail img').attr('src', attachment.url );
							jQuery('.remove_image_button').show();
						});

						// Finally, open the modal.
						file_frame.open();
					});

					jQuery(document).on( 'click', '.remove_image_button', function( event ){
						jQuery('#product_cat_thumbnail img').attr('src', '<?php echo woocommerce_placeholder_img_src(); ?>');
						jQuery('#product_cat_thumbnail_id').val('');
						jQuery('.remove_image_button').hide();
						return false;
					});
				});

			</script>
			<div class="clear"></div>
		</div>
		<?php
	}

	function edit_thumbnail_field( $term, $taxonomy ) {
		global $woocommerce;

		$image 			= '';
		$thumbnail_id 	= get_woocommerce_term_meta( $term->term_id, 'thumbnail_id', true );
		if ($thumbnail_id) :
			$image = wp_get_attachment_url( $thumbnail_id );
		else :
			$image = woocommerce_placeholder_img_src();
		endif;
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label><?php _e('Thumbnail', 'wc_brands'); ?></label></th>
			<td>
				<div id="product_cat_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo $image; ?>" width="60px" height="60px" /></div>
				<div style="line-height:60px;">
					<input type="hidden" id="product_cat_thumbnail_id" name="product_cat_thumbnail_id" value="<?php echo $thumbnail_id; ?>" />
					<button type="submit" class="upload_image_button button"><?php _e('Upload/Add image', 'wc_brands'); ?></button>
					<button type="submit" class="remove_image_button button"><?php _e('Remove image', 'wc_brands'); ?></button>
				</div>
				<script type="text/javascript">

					jQuery(function(){

						 // Only show the "remove image" button when needed
						 if ( ! jQuery('#product_cat_thumbnail_id').val() )
							 jQuery('.remove_image_button').hide();

						// Uploading files
						var file_frame;

						jQuery(document).on( 'click', '.upload_image_button', function( event ){

							event.preventDefault();

							// If the media frame already exists, reopen it.
							if ( file_frame ) {
								file_frame.open();
								return;
							}

							// Create the media frame.
							file_frame = wp.media.frames.downloadable_file = wp.media({
								title: '<?php _e( 'Choose an image', 'woocommerce' ); ?>',
								button: {
									text: '<?php _e( 'Use image', 'woocommerce' ); ?>',
								},
								multiple: false
							});

							// When an image is selected, run a callback.
							file_frame.on( 'select', function() {
								attachment = file_frame.state().get('selection').first().toJSON();

								jQuery('#product_cat_thumbnail_id').val( attachment.id );
								jQuery('#product_cat_thumbnail img').attr('src', attachment.url );
								jQuery('.remove_image_button').show();
							});

							// Finally, open the modal.
							file_frame.open();
						});

						jQuery(document).on( 'click', '.remove_image_button', function( event ){
							jQuery('#product_cat_thumbnail img').attr('src', '<?php echo woocommerce_placeholder_img_src(); ?>');
							jQuery('#product_cat_thumbnail_id').val('');
							jQuery('.remove_image_button').hide();
							return false;
						});
					});

				</script>
				<div class="clear"></div>
			</td>
		</tr>
		<?php
	}

	function thumbnail_field_save( $term_id, $tt_id, $taxonomy ) {
		if ( isset( $_POST['product_cat_thumbnail_id'] ) )
			update_woocommerce_term_meta($term_id, 'thumbnail_id', $_POST['product_cat_thumbnail_id']);
	}

	/**
	 * Description for brand page
	 */
	function taxonomy_description() {

		echo wpautop( __( 'Brands be added and managed from this screen. You can optionally upload a brand image to display in brand widgets and on brand archives', 'wc_brands' ) );

	}

	/**
	 * sort_brands function.
	 *
	 * @access public
	 */
	function sort_brands( $sortable ) {
		$sortable[] = 'product_brand';
		return $sortable;
	}

	/**
	 * columns function.
	 *
	 * @access public
	 * @param mixed $columns
	 */
	function columns( $columns ) {
		$new_columns = array();
		$new_columns['cb'] = $columns['cb'];
		$new_columns['thumb'] = __('Image', 'woocommerce');
		unset( $columns['cb'] );
		$columns = array_merge( $new_columns, $columns );
		return $columns;
	}

	/**
	 * column function.
	 *
	 * @access public
	 * @param mixed $columns
	 * @param mixed $column
	 * @param mixed $id
	 */
	function column( $columns, $column, $id ) {
		if ( $column == 'thumb' ) {
			global $woocommerce;

			$image 			= '';
			$thumbnail_id 	= get_woocommerce_term_meta( $id, 'thumbnail_id', true );

			if ( $thumbnail_id )
				$image = wp_get_attachment_url( $thumbnail_id );
			else
				$image = woocommerce_placeholder_img_src();

			$columns .= '<img src="' . $image . '" alt="Thumbnail" class="wp-post-image" height="48" width="48" />';

		}
		return $columns;
	}


}

$GLOBALS['WC_Brands_Admin'] = new WC_Brands_Admin();