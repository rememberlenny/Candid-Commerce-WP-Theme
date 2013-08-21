<?php
/*
Plugin Name: WooCommerce Professor Cloud
Plugin URI: http://chromeorange.co.uk/cloud-zoom-for-woocommerce/
Description: Modifies the standard large image display on 'single-product.php' to use the Professor Cloud hover/magnify :). In the event of a problem please submit a suport ticket at support.woothemes.com with a screen shot of your cloud zoom settings and a link to your site.
Version: 2.0.2
Author: Andrew Benbow
Author URI: http://www.chromeorange.co.uk

	Copyright: Copyright 2009-2011 Andrew Benbow.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) )
	require_once( 'woo-includes/woo-functions.php' );

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '5f17b2e04985aa9cef1f979823971445', '18617' );

/**
 * Plugin Version
 */
define( 'IMAGEZOOM_VERSION', '2.0.2' );

if ( is_woocommerce_active() ) :

	/**
	 * Localisation
	 */
	load_plugin_textdomain( 'co_imagezoom', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	/**
	 * Plugin page links
	 */
	function co_imagezoom_plugin_links( $links ) {

		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=woocommerce&tab=professor_cloud' ) . '">' . __( 'Settings', 'co_imagezoom' ) . '</a>',
			'<a href="http://support.woothemes.com/">' . __( 'Support', 'co_imagezoom' ) . '</a>',
			'<a href="http://docs.woothemes.com/document/image-zoom-2/">' . __( 'Docs', 'co_imagezoom' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}

	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'co_imagezoom_plugin_links' );

	/**
	 * Redirect to settings page on activation
	 **/
	register_activation_hook(__FILE__, 'imagezoom_plugin_activate');
	add_action('admin_init', 'imagezoom_plugin_redirect');

	function imagezoom_plugin_activate() {
	    add_option('imagezoom_plugin_do_activation_redirect', true);
	}

	function imagezoom_plugin_redirect() {
		
	    if ( get_option('imagezoom_plugin_do_activation_redirect', false) ) :
		
	       	delete_option( 'imagezoom_plugin_do_activation_redirect' );
	        wp_redirect( admin_url( 'admin.php?page=woocommerce&tab=professor_cloud' ) );
			
	    endif;
		
	}

	/**
	 * woocommerce_professor_cloud class
	 **/
	if ( ! class_exists( 'woocommerce_professor_cloud' ) ) :

		class woocommerce_professor_cloud {

			var $settings_tabs;
			var $current_tab;
			var $fields = array();

			var $enableCloud;	// true
			var $zoomWidth; 	// auto
			var $zoomHeight; 	// auto
			var $position; 		// right
			var $adjustX; 		// 0
			var $adjustY; 		// 0
			var $tint; 			// false
			var $tintOpacity; 	// 0.5
			var $lensOpacity; 	// 0.5
			var $softFocus; 	// false
			var $smoothMove; 	// 3
			var $showTitle; 	// true
			var $titleOpacity; 	// 0.5
			var $scaleImages;  	// true
			var $enablemobile;
			var $forceinside;
			var $includeipad;
			var $sliderInside;
			var $clickForLarger;
			var $themeclass; 	// images

			public function __construct() {

				$this->current_tab = ( isset($_GET['tab'] ) ) ? $_GET['tab'] : 'general';

				$this->settings_tabs = array(
					'professor_cloud' => __( 'Image Zoom', 'co_imagezoom' )
				);

				// Load in the new settings tabs.
				add_action( 'woocommerce_settings_tabs', array( $this, 'add_tab' ), 10 );

				// Run these actions when generating the settings tabs.
				foreach ( $this->settings_tabs as $name => $label ) {
					add_action( 'woocommerce_settings_tabs_' . $name, array( $this, 'settings_tab_action' ), 10 );
					add_action( 'woocommerce_update_options_' . $name, array( $this, 'save_settings' ), 10 );
				}

				// Add the settings fields to each tab.
				add_action( 'woocommerce_professor_cloud_settings', array( $this, 'add_settings_fields' ), 10 );

				// Frontend

				// Get settings
				$this->enableCloud 	 = get_option('woocommerce_cloud_enableCloud'); 	// true
				$this->zoomWidth 	 = get_option('woocommerce_cloud_zoomWidth'); 		// auto
				$this->zoomHeight 	 = get_option('woocommerce_cloud_zoomHeight'); 		// auto
				$this->position 	 = get_option('woocommerce_cloud_position'); 		// right
				$this->adjustX 		 = get_option('woocommerce_cloud_adjustX'); 		// 0
				$this->adjustY 		 = get_option('woocommerce_cloud_adjustY'); 		// 0
				$this->tint 		 = get_option('woocommerce_cloud_tint'); 			// false
				$this->tintOpacity 	 = get_option('woocommerce_cloud_tintOpacity'); 	// 0.5
				$this->lensOpacity 	 = get_option('woocommerce_cloud_lensOpacity'); 	// 0.5
				$this->softFocus 	 = get_option('woocommerce_cloud_softFocus'); 		// false
				$this->smoothMove 	 = get_option('woocommerce_cloud_smoothMove'); 		// 3
				$this->showTitle 	 = get_option('woocommerce_cloud_showTitle'); 		// true
				$this->titleOpacity  = get_option('woocommerce_cloud_titleOpacity'); 	// 0.5
				$this->scaleImages 	 = get_option('woocommerce_cloud_scaleImages'); 	// true
				$this->enablemobile  = get_option('woocommerce_cloud_enablemobile');
				$this->forceinside	 = get_option('woocommerce_cloud_forceinside');
				$this->includeipad	 = get_option('woocommerce_cloud_includeipad');
				$this->sliderInside	 = get_option('woocommerce_cloud_forceinside_slider');
				$this->clickForLarger= get_option('woocommerce_cloud_clickforlarger');
				
				$this->themeclass	 = get_option('woocommerce_cloud_themeclass');		// images

				// Load the CSS
				add_action('wp_print_styles', array($this, 'professor_cloud_stylesheet'),0);
				// Load the jScript
				add_action('wp_enqueue_scripts', array($this, 'professor_cloud_scripts'),999);
				
				// Remove standard thumbnails
				add_action( 'woocommerce_product_thumbnails' , array( $this,'woocommerce_image_zoom_remove_action'), 0 );
				
				// Add modified thumbnails
				add_action( 'woocommerce_product_thumbnails', array($this, 'woocommerce_show_product_thumbnails_cloud'), 20 );
				
				// Add the javascript to modify the main image HTML
				add_action( 'wp', array($this,'woocommerce_image_zoom_modify_html'), 20 );

		    } // EOF public function __construct


			/*-----------------------------------------------------------------------------------*/
			/* Admin Tabs */
			/*-----------------------------------------------------------------------------------*/

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
			 */
			function settings_tab_action() {
				global $woocommerce_settings;

				// Determine the current tab in effect.
				$current_tab = $this->get_tab_in_view( current_filter(), 'woocommerce_settings_tabs_' );

				// Hook onto this from another function to keep things clean.
				do_action( 'woocommerce_professor_cloud_settings' );

				// Display settings for this tab (make sure to add the settings to the tab).
				woocommerce_admin_fields( $woocommerce_settings[$current_tab] );
			}

			/**
			 * add_settings_fields()
			 *
			 * Add settings fields for each tab.
			 */
			function add_settings_fields() {
				global $woocommerce_settings;

				// Load the prepared form fields.
				$this->init_form_fields();

				if ( is_array( $this->fields ) )
					foreach ( $this->fields as $k => $v )
						$woocommerce_settings[$k] = $v;
			}

			/**
			 * get_tab_in_view()
			 *
			 * Get the tab current in view/processing.
			 */
			function get_tab_in_view ( $current_filter, $filter_base ) {
				return str_replace( $filter_base, '', $current_filter );
			}

			/**
			 * init_form_fields()
			 *
			 * Prepare form fields to be used in the various tabs.
			 */
			function init_form_fields() {
				global $woocommerce_settings,$woocommerce;
				// Define settings
				include('lib/admin-settings.php');
			}

			/**
			 * save_settings()
			 *
			 * Save settings in a single field in the database for each tab's fields (one field per tab).
			 */
			function save_settings() {
				global $woocommerce_settings;

				// Make sure our settings fields are recognised.
				$this->add_settings_fields();

				$current_tab = $this->get_tab_in_view( current_filter(), 'woocommerce_update_options_' );
				woocommerce_update_options( $woocommerce_settings[$current_tab] );
			}

	        /*-----------------------------------------------------------------------------------*/
			/* Frontend */
			/*-----------------------------------------------------------------------------------*/
			function woocommerce_image_zoom_remove_action() {
				global $_product, $post, $woocommerce;
				
				remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
			}

			/**
			 * Set rel tag depending on WC version
			 */
			 function woocommerce_image_zoom_set_rel() {
				if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) :
					$rel = 'thumbnails';
				else :
					$rel = 'prettyPhoto';
				endif;
				
				return $rel;
			 }
			
			/**
			 * Load Mobile_Detect if needed and a theme hasn't already
			 */
			 function woocommerce_image_zoom_mobile_detect( $answer = NULL ) {
				 
			 	/**
				 * Set the mobile enable variable to true regardless of device
				 */
				$mobileEnable = 'yes';
				 
				if ( !class_exists( 'Mobile_Detect' ) && $this->enableCloud == 'true' && has_post_thumbnail() ) :
					define( 'IMAGEZOOM_PATH', plugin_dir_path(__FILE__) );
					require IMAGEZOOM_PATH . 'lib/Mobile_Detect.php';
				endif;
				
				/**
				 * Load the Mobile Detect class
				 */
				$detect = new Mobile_Detect();
				
				/**
				 * Set the device type
				 */
				$deviceType = ( $detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer' );
				
				if ( $answer == 'deviceType' ) :
					return $deviceType;
				endif;
				
				if ( $this->enablemobile == 'false' && $deviceType == 'tablet' && $this->includeipad == 'true' ) :
					// The viewer is using a mobile device and we don't want Image Zoom enabled on mobile devices
					$mobileEnable = 'no';
				elseif ( $this->enablemobile == 'false' && $deviceType == 'phone' ) :
					// The viewer is using a mobile device and we don't want Image Zoom enabled on mobile devices
					$mobileEnable = 'no';
				else :
					// fallback
					$mobileEnable = 'yes';
				
				endif;
			
				return $mobileEnable;
			
			}
			
			/**
			 * Check if Product Gallery Slider is enabled and active on this product
			 */
			function woocommerce_image_zoom_product_slider() {
				global $_product, $post, $woocommerce;
				
				$enabled 			= get_option( 'woocommerce_product_gallery_slider_enabled' );
				$enabled_for_post 	= get_post_meta( $post->ID, '_woocommerce_product_gallery_slider_enabled', true );
				$product_slider 	= 'no';

				if ( $enabled == 'yes' && in_array( 'woocommerce-product-gallery-slider/product-gallery-slider.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) :
					if ( ! $enabled_for_post || $enabled_for_post == 'yes' ) :
						$product_slider = 'yes';
					endif;
				endif;
				
				return $product_slider;
			
			}

			/**
			 * Compile a list of the zoom options
			 */
			function prof_cloud_option_list() {

				/**
				 * Check if Product Gallery Slider is enabled and active on this product
				 */
				$product_slider = $this->woocommerce_image_zoom_product_slider();

				/**
				 * Check mobile status
				 */
				$mobileEnable = $this->woocommerce_image_zoom_mobile_detect();
				
				/**
				 * Check device type
				 */
				$deviceType = $this->woocommerce_image_zoom_mobile_detect('deviceType');
				
				/**
				 * Set Rel
				 */
				$rel = $this->woocommerce_image_zoom_set_rel();

				/**
				 * Compile a list of the zoom options
				 */
				$prof_cloud_option_array 	= array();
					
				if ( $this->zoomWidth != '' && $this->zoomWidth != 'auto' )
					$prof_cloud_option_array[] = "zoomWidth:".$this->zoomWidth;
						
				if ( $this->zoomHeight != '' && $this->zoomHeight != 'auto' )
					$prof_cloud_option_array[] = "zoomHeight:".$this->zoomHeight;
	
				if ( $deviceType == 'tablet' && $mobileEnable = 'yes' && $this->forceinside == 'true' && $this->includeipad == 'true' ) :
					$prof_cloud_option_array[] = "position:'inside'";
				elseif ( $deviceType == 'phone' && $mobileEnable = 'yes' && $this->forceinside == 'true' ) :
					$prof_cloud_option_array[] = "position:'inside'";
				elseif ( $product_slider == 'yes' && $this->sliderInside == 'true' ) :
					$prof_cloud_option_array[] = "position:'inside'";
				else :
					$prof_cloud_option_array[] = "position:'".$this->position."'";
				endif;

				if ( $this->adjustX != '' && $this->adjustX != '0' )
					$prof_cloud_option_array[] = "adjustX:".$this->adjustX;
						
				if ( $this->adjustY != '' && $this->adjustY != '0' )
					$prof_cloud_option_array[] = "adjustY:".$this->adjustY;
					
				if ( $this->lensOpacity != '' && $this->lensOpacity != '0.5' )
					$prof_cloud_option_array[] = "lensOpacity:".$this->lensOpacity;

				if ( $this->smoothMove != '' && $this->smoothMove != '3' )
					$prof_cloud_option_array[] = "smoothMove:".$this->smoothMove;

				if ( $this->showTitle != '' && $this->showTitle != 'true' )
					$prof_cloud_option_array[] = "showTitle:".$this->showTitle;
					
				if ( $this->titleOpacity != '' && $this->tintOpacity != '0.5' )
					$prof_cloud_option_array[] = "titleOpacity:".$this->titleOpacity;
				
				$prof_cloud_option_list = implode( ',',$prof_cloud_option_array );
				
				return $prof_cloud_option_list;
			}

			function woocommerce_image_zoom_modify_html() {
				global $_product, $post, $woocommerce;
				
				/**
				 * STOP! if this is not a single product page
				 * from 2.0.1
				 */
				if ( !is_product() )
					return;
					
				/**
				 * Check if Product Gallery Slider is enabled and active on this product
				 */
				$product_slider = $this->woocommerce_image_zoom_product_slider();

				/**
				 * Check mobile status
				 */
				 $mobileEnable = $this->woocommerce_image_zoom_mobile_detect();
			
				if ( $this->enableCloud == 'true' && has_post_thumbnail() && $mobileEnable == 'yes' ) :
					
					/**
					 * Set Rel
					 */
					$rel = $this->woocommerce_image_zoom_set_rel();
					
					/**
					 * Set $this->themeclass
					 */
					if ( $this->themeclass == '' || !$this->themeclass ) :
						$this->themeclass = 'images';
					endif;


					/**
					 * Add the gallery slider code if necesary, otherwise just add a script to the footer
					 */				 
					 if ( $product_slider == 'yes' ) :
						add_action( 'woocommerce_before_single_product_summary', array($this, 'show_product_gallery'), 31 );
					 else :					
						add_action( 'wp_footer', array( $this,'woocommerce_image_zoom_modify_html_std') , 999 );
					endif;

				endif;
			}


			/**
			 * Product Gallery Slider layout
			 *
			 * Show all single product images in a <ul>
			 */
			function show_product_gallery( $prof_cloud_option_list ) {
				global $post, $product, $woocommerce;
				
				/**
				 * Set options list
				 */
				 $prof_cloud_option_list = $this->prof_cloud_option_list();

				/**
				 * Set Rel
				 */
				$rel = $this->woocommerce_image_zoom_set_rel();
				
				$small_thumbnail_size = apply_filters( 'single_product_small_thumbnail_size', 'shop_single' );

				if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
					$args = array(
						'post_type' 	=> 'attachment',
						'numberposts' 	=> -1,
						'post_status' 	=> null,
						'post_parent' 	=> $post->ID,
						'post_mime_type'=> 'image',
						'orderby'		=> 'menu_order',
						'order'			=> 'ASC',
						'fields'		=> 'ids'
					);
					$attachments = get_posts( $args );
				} else {
					$attachments = $product->get_gallery_attachment_ids();
				}

				if ( $attachments ) {
					$loop = 0;
					$columns = apply_filters('woocommerce_product_thumbnails_columns', 3);
					echo '<div class="product-gallery images"><ul class="slides">';

					foreach ( $attachments as $attachment_id ) {

						if ( get_post_meta( $attachment_id, '_woocommerce_exclude_image', true ) == 1 )
							continue;

						$loop++;

						$url        = wp_get_attachment_image_src( $attachment_id, 'shop_thumbnail' );
						$url_large  = wp_get_attachment_image_src( $attachment_id, 'shop_single' );
						$post_title = esc_attr( get_the_title( $attachment_id ) );
						$image      = wp_get_attachment_image( $attachment_id, $small_thumbnail_size );

						/** 
						 * The full size version of the product image
						 * Returns 
						 * $imagezoom_bigimage[0] -> image URL
						 * $imagezoom_bigimage[1] -> image width
						 * $imagezoom_bigimage[2] -> image height
						 */
						$imagezoom_bigimage   = wp_get_attachment_image_src( $attachment_id ,'full' );

						echo '<li data-thumb="' . $url[0] . '">
						<a href="' . $imagezoom_bigimage[0] . '" title="'.$post_title.'" rel="' . $rel . '" class="zoom cloud-zoom ';
						if ( $loop == 1 || ( $loop-1 ) % $columns == 0 ) echo 'first';
						if ( $loop % $columns == 0 ) echo 'last';
						echo '" id="zoom1" cloud="'.$prof_cloud_option_list.'">' . $image . '</a>
						<p>
						<a id="cloud-link" class="fancybox zoom" 
						href="' . $imagezoom_bigimage[0] . '" 
						title="' . $post_title . '" 
						rel="' . $rel . '">' . $this->clickForLarger . ' </a>
						</p>
						</li>';
					}

					echo '</ul></div>';

				} else {
					woocommerce_show_product_images();
					add_action( 'wp_footer', array( $this,'woocommerce_image_zoom_modify_html_std') , 999 );
				}
			}

			/**
			 * Standard Woo layout
			 *
			 * Footer output for standard layout
			 * non-gallery slider 
			 */
			 function woocommerce_image_zoom_modify_html_std() {
				 global $_product, $post, $woocommerce;
				 
				 /**
				 * Set options list
				 */
				 $prof_cloud_option_list = $this->prof_cloud_option_list();

				/**
				 * Set Rel
				 */
				$rel = $this->woocommerce_image_zoom_set_rel();
				
				/** 
				 * The full size version of the product image
				 * Returns 
				 * $imagezoom_bigimage[0] -> image URL
				 * $imagezoom_bigimage[1] -> image width
				 * $imagezoom_bigimage[2] -> image height
				 */
				$imagezoom_bigimage   = wp_get_attachment_image_src( get_post_thumbnail_id() ,'full' );

				/**
				 * 'Click for larger link'
				 * Allow users to filter if necessary
				 */
				$imagezoom_largerlink = '<p><a id="cloud-link" class="fancybox zoom" href="' . $imagezoom_bigimage[0] . '" title="' . get_the_title() . '" rel="' . $rel . '">' . $this->clickForLarger . ' </a></p>';
				$imagezoom_largerlink = apply_filters( 'woocommerce_image_zoom_larger_link', $imagezoom_largerlink, $post );
?>
				<script>
					jQuery(".<?php echo $this->themeclass;?> a").first().addClass("cloud-zoom");
					jQuery(".<?php echo $this->themeclass;?> a").first().attr("cloud","<?php echo $prof_cloud_option_list; ?>");
					jQuery(".<?php echo $this->themeclass;?> a").first().attr("id","zoom1");
					jQuery(".<?php echo $this->themeclass;?> a").first().after('<?php echo $imagezoom_largerlink; ?>');
				</script>				
<?php 
				 
			 }


			/**
			 * woocommerce_show_product_thumbnails_cloud function.
			 *
			 * @access public
			 * @return void
	 		*/
			function woocommerce_show_product_thumbnails_cloud() {
				global $post, $woocommerce, $product;
				
				/**
				 * Set Rel
				 */
				$rel = $this->woocommerce_image_zoom_set_rel();

				echo '<div class="thumbnails"><ul class="large-block-grid-1 small-block-grid-5';

				$thumb_id 				= get_post_thumbnail_id();
				$small_thumbnail_size  	= apply_filters('single_product_small_thumbnail_size', 'shop_thumbnail');
				$medium_thumbnail_size 	= apply_filters('single_product_large_thumbnail_size', 'shop_single');
				
				// Defaults
				$show_original 			= 'nope';
				$exclusion 				= array( '0' );
				$variation_counter 		= 0;

				// Let's get the attachments
				if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) :
				
					$args = array(
						'post_type' 	=> 'attachment',
						'numberposts' 	=> -1,
						'post_status' 	=> null,
						'post_parent' 	=> $post->ID,
						'post_mime_type'=> 'image',
						'orderby'		=> 'menu_order',
						'order'			=> 'ASC',
						'fields'		=> 'ids'
					);
					$attachments = get_posts( $args );
					$attachments = array_unique ( $attachments );
					
				else :
				
					$attachments 	= $product->get_gallery_attachment_ids();
					if ( get_post_thumbnail_id() ) : 
						$attachments 	= array_reverse ($attachments);
						$attachments[]	= get_post_thumbnail_id();
						$attachments 	= array_reverse ($attachments);
					endif;
					$attachments = array_unique ( $attachments );
					
				endif;

				// Check for variation images
				if ( $product->product_type == 'variable' ) :
					$variation_counter = 0;
					// Let's check if the variations have images!
					foreach($product->get_children() as $child_id) :

 		   				$variation = $product->get_child( $child_id );
 		   				if ($variation instanceof WC_Product_Variation) :

  		  					if (get_post_status( $variation->get_variation_id() ) != 'publish')
  		  						continue; // Disabled

    						if ( has_post_thumbnail( $variation->get_variation_id() ) ) :
    							$show_original = 'yep';
								$variation_counter++;
							endif;
					
						endif;

					endforeach;
					
				endif;

				/**
				 * If we have attribute images OR more than 1 attachment then don't exclude the post thumbnail from the gallery
				 * We need the post thumbnail in there so we can get it back
				 */		
				if ( count($attachments) > 1 ) :
					$exclusion = array( '0' );
					$show_original == 'yep';
				endif;
				
				if ( $product->product_type == 'variable' && $variation_counter == 0 ) :
					$exclusion = array( '0' );
				else :
					$exclusion = array( $thumb_id );
				endif;
				
				// $attachments = array_diff( $attachments, $exclusion );

				if ( ( $attachments && count( $attachments ) > intval(1) ) || ( $attachments && $product->product_type == 'variable' && $variation_counter > 0 ) ) :

					$loop = 0;
					$columns = apply_filters( 'woocommerce_product_thumbnails_columns', 3 );

					foreach ( $attachments as $attachment_id ) :

						if ( get_post_meta( $attachment_id, '_woocommerce_exclude_image', true ) == 1 )
							continue;

						$loop++;

						$url        = wp_get_attachment_image_src( $attachment_id, 'full' );
						$post_title = esc_attr( get_the_title( $attachment_id ) );
						$image      = wp_get_attachment_image( $attachment_id, $small_thumbnail_size );

						$cloudmediumImage	= wp_get_attachment_image_src( $attachment_id, $medium_thumbnail_size );
						$cloudimagepath 	= wp_get_attachment_image_src( $attachment_id, 'large' );

						echo '<a href="' . $url[0] . '" title="' . $post_title . '" cloud="useZoom:\'zoom1\',smallImage:\'' . $cloudmediumImage[0] . '\'" class="cloud-zoom-gallery zoom';
						if ( $loop == 1 || ( $loop - 1 ) % $columns == 0 ) echo ' first';
						if ( $loop % $columns == 0 ) echo ' last';
						echo '" rel="'.$rel.'"><li>' . $image . '</li></a>' ."\r\n" ."\r\n";
						
					endforeach;
				endif;

				echo '</ul></div>';
			}

			/**
			 * Load the CSS
			 **/
            function professor_cloud_stylesheet() {

                $plugin_version     = plugins_url('lib/woocomm-professor-cloud.css', __FILE__);
                $theme_version_file = get_stylesheet_directory() . '/professor_cloud/woocomm-professor-cloud.css';
                $theme_version_url  = get_stylesheet_directory_uri() . '/professor_cloud/woocomm-professor-cloud.css';

                if ( get_option('woocommerce_cloud_enableCloud') == 'true' && is_product() ) :
                    $css = file_exists($theme_version_file) ? $theme_version_url : $plugin_version;
                    wp_register_style('professor_cloud_stylesheets', $css, '', IMAGEZOOM_VERSION);
                    wp_enqueue_style( 'professor_cloud_stylesheets');
                endif;

            } // END professor_cloud_stylesheet


			/**
			 * Load the jscript
			 **/
			function professor_cloud_scripts() {

				if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) :
					$suffix = '-one';
				else:
					$suffix = '-two';
				endif;

				// Respects SSL, Style.css is relative to the current file
		        $professor_cloud_jscript_url  = plugins_url('lib/woocomm-professor-cloud-script'.$suffix.'.js', __FILE__);
		        $professor_cloud_jscript_file = WP_PLUGIN_DIR . '/woocommerce-professor-cloud/lib/woocomm-professor-cloud-script'.$suffix.'.js';

				if ( get_option('woocommerce_cloud_enableCloud') == 'true' && is_product() ) :
					// register your script location, dependencies and version
					wp_register_script('professor_cloud', plugins_url('lib/woocomm-professor-cloud-script'.$suffix.'.js', __FILE__), array('jquery'), IMAGEZOOM_VERSION, 'TRUE' );
			   		// enqueue the script
			   		wp_enqueue_script('professor_cloud');
				endif;

			} // END professor_cloud_scripts


		} // EOF woocommerce_professor_cloud class

		global $woocommerce_professor_cloud;

		$woocommerce_professor_cloud = new woocommerce_professor_cloud();

	endif; // EOF if class exists

endif;