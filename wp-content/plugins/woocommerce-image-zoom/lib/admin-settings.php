<?php
					$this->fields['professor_cloud'] = apply_filters('woocommerce_professor_cloud_settings_fields', array(
					
					array(	'name' 	=> __( 'Professor Cloud Configuration', 'woothemes' ), 'type' => 'title','desc' => '', 'id' => 'professor_cloud' ),
					
					array(  
						'name' 		=> __( 'Enable / Disable Professor Cloud', 'woothemes' ),
						'desc' 		=> __( 'Do you want to use the Professor Cloud Zoom on your single product page?.', 'woothemes' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cloud_enableCloud',
						'css' 		=> '',
						'std' 		=> 'true',
						'default' 	=> 'true',
						'type' 		=> 'select',
						'options'	=> array( 'true' => 'Enable Professor Cloud', 'false' => 'Disable Professor Cloud' )
					),
					
					array(  
						'name' 		=> __( 'Image Class', 'woothemes' ),
						'desc' 		=> __( 'Specifies the class for the div surrounding the images on your single product page. the standard WooCommerce class is images. Only change this if your theme changes it', 'woothemes' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cloud_themeclass',
						'css' 		=> '',
						'std' 		=> 'images',
						'default' 	=> 'images',
						'type' 		=> 'text'
					),
					
					array(  
						'name' 		=> __( 'Click For Larger Text', 'woothemes' ),
						'desc' 		=> __( 'Set the link text for the \'Larger Image\' link', 'woothemes' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cloud_clickforlarger',
						'css' 		=> '',
						'std' 		=> 'Click for a larger view',
						'default' 	=> 'Click for a larger view',
						'type' 		=> 'text',
					),
					
					array(  
						'name' 		=> __( 'Zoom Width', 'woothemes' ),
						'desc' 		=> __( 'The width of the zoom window in pixels. If \'auto\' is specified, the width will be the same as the small image.', 'woothemes' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cloud_zoomWidth',
						'css' 		=> '',
						'std' 		=> 'auto',
						'default' 	=> 'auto',
						'type' 		=> 'text',
					),

					array(  
						'name' 		=> __( 'Zoom Height', 'woothemes' ),
						'desc' 		=> __( 'The height of the zoom window in pixels. If \'auto\' is specified, the width will be the same as the small image.', 'woothemes' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cloud_zoomHeight',
						'css' 		=> '',
						'std' 		=> 'auto',
						'default' 	=> 'auto',
						'type' 		=> 'text',
					),

					array(  
						'name' 		=> __( 'Zoom Position', 'woothemes' ),
						'desc' 		=> __( 'Specifies the position of the zoom window relative to the small image. Allowable values are \'left\', \'right\', \'top\', \'bottom\', \'inside\' or you can specifiy the id of an html element to place the zoom window in e.g. position: \'element1\'.', 'woothemes' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cloud_position',
						'css' 		=> '',
						'std' 		=> 'right',
						'default' 	=> 'right',
						'type' 		=> 'text',
					),
					
					array(  
						'name' 		=> __( 'Zoom AdjustX', 'woothemes' ),
						'desc' 		=> __( 'Allows you to fine tune the x-position of the zoom window in pixels.', 'woothemes' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cloud_adjustX',
						'css' 		=> '',
						'std' 		=> '20',
						'default' 	=> '20',
						'type' 		=> 'text',
					),
					
					array(  
						'name' 		=> __( 'Zoom AdjustY', 'woothemes' ),
						'desc' 		=> __( 'Allows you to fine tune the y-position of the zoom window in pixels.', 'woothemes' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cloud_adjustY',
						'css' 		=> '',
						'std' 		=> '-20',
						'default' 	=> '-20',
						'type' 		=> 'text',
					),
					
					array(  
						'name' 		=> __( 'Tint', 'woothemes' ),
						'desc' 		=> __( 'Specifies a tint colour which will cover the small image. Colours should be specified in hex format, e.g. \'#aa00aa\'. Does not work with softFocus.', 'woothemes' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cloud_tint',
						'css' 		=> '',
						'std' 		=> 'false',
						'default' 	=> 'false',
						'type' 		=> 'text'
					),
					
					array(  
						'name' 		=> __( 'Tint Opacity', 'woothemes' ),
						'desc' 		=> __( 'Opacity of the tint, where 0 is fully transparent, and 1 is fully opaque.', 'woothemes' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cloud_tintOpacity',
						'css' 		=> '',
						'std' 		=> '0.5',
						'default' 	=> '0.5',
						'type' 		=> 'text'
					),
					
					array(  
						'name' 		=> __( 'Lens Opacity', 'woothemes' ),
						'desc' 		=> __( 'Opacity of the lens mouse pointer, where 0 is fully transparent, and 1 is fully opaque. In tint and soft-focus modes, it will always be transparent.', 'woothemes' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cloud_lensOpacity',
						'css' 		=> '',
						'std' 		=> '0.5',
						'default' 	=> '0.5',
						'type' 		=> 'text'
					),

					array(  
						'name' 		=> __( 'Enable / Disable Soft Focus', 'woothemes' ),
						'desc' 		=> __( 'Applies a subtle blur effect to the small image. Set to true or false. Does not work with tint.', 'woothemes' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cloud_softFocus',
						'css' 		=> '',
						'std' 		=> 'false',
						'default' 	=> 'false',
						'type' 		=> 'select',
						'options'	=> array( 'true' => 'Enable soft focus', 'false' => 'Disable soft focus' )
					),
					
					array(  
						'name' 		=> __( 'Smooth Move', 'woothemes' ),
						'desc' 		=> __( 'Amount of smoothness/drift of the zoom image as it moves. The higher the number, the smoother/more drifty the movement will be. 1 = no smoothing.', 'woothemes' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cloud_smoothMove',
						'css' 		=> '',
						'std' 		=> '3',
						'default' 	=> '3',
						'type' 		=> 'text'
					),

					array(  
						'name' 		=> __( 'Enable / Disable Image Title', 'woothemes' ),
						'desc' 		=> __( 'Shows the title tag of the image. True or false.', 'woothemes' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cloud_showTitle',
						'css' 		=> '',
						'std' 		=> 'true',
						'default' 	=> 'true',
						'type' 		=> 'select',
						'options'	=> array( 'true' => 'Show Image Title', 'false' => 'Do Not Show Image Title' )
					),
					
					array(  
						'name' 		=> __( 'Title Opacity', 'woothemes' ),
						'desc' 		=> __( 'Specifies the opacity of the title if displayed, where 0 is fully transparent, and 1 is fully opaque.', 'woothemes' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cloud_titleOpacity',
						'css' 		=> '',
						'std' 		=> '0.5',
						'default' 	=> '0.5',
						'type' 		=> 'text'
					),
					
					array( 'type' => 'sectionend', 'id' => 'professor_cloud' ),
					
					array(	'name' 	=> __( 'Mobile Devices', 'woothemes' ), 'type' => 'title','desc' => '', 'id' => 'professor_cloud' ),
					
					array(  
						'name' 		=> __( 'Enable / Disable Professor Cloud Zoom on mobile devices', 'woothemes' ),
						'desc' 		=> __( 'The Professor Cloud effect is designed to work on mobile devices but certain themes can cause the zoom effect to be displayed outside of the viewport. This setting allows you to disable the plugin for mobile devices.', 'woothemes' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cloud_enablemobile',
						'css' 		=> '',
						'std' 		=> 'true',
						'default' 	=> 'true',
						'type' 		=> 'select',
						'options'	=> array( 'true' => 'Enable for mobile devices', 'false' => 'Disable for mobile devices' )
					),
					
					array(  
						'name' 		=> __( 'Force \'inside\' position for mobile devices', 'woothemes' ),
						'desc' 		=> __( 'This allows you to force the zoom position to inside when viewing your site on a mobile device, useful if you have a responsive theme.', 'woothemes' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cloud_forceinside',
						'css' 		=> '',
						'std' 		=> 'true',
						'default' 	=> 'true',
						'type' 		=> 'select',
						'options'	=> array( 'true' => 'Force \'inside\' position', 'false' => 'Do not force \'inside\' position' )
					),

					array(  
						'name' 		=> __( 'Include tablets as a mobile devices', 'woothemes' ),
						'desc' 		=> __( 'Test your theme on your tablet, you may not need to disable Professor Cloud.', 'woothemes' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cloud_includeipad',
						'css' 		=> '',
						'std' 		=> 'true',
						'default' 	=> 'true',
						'type' 		=> 'select',
						'options'	=> array( 'true' => 'Include tablets as mobile devices', 'false' => 'Do not include tablets as mobile devices' )
					),


					array( 'type' => 'sectionend', 'id' => 'professor_cloud' ),
					
					array(	'name' 	=> __( 'Product Slider Additions', 'woothemes' ), 'type' => 'title','desc' => '', 'id' => 'professor_cloud' ),
					
					array(  
						'name' 		=> __( 'Force \'inside\' for products using the product slider', 'woothemes' ),
						'desc' 		=> __( 'This allows you to force the zoom position to inside when using the product slider add-on (sold seperately)', 'woothemes' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cloud_forceinside_slider',
						'css' 		=> '',
						'std' 		=> 'true',
						'default' 	=> 'true',
						'type' 		=> 'select',
						'options'	=> array( 'true' => 'Force \'inside\' position', 'false' => 'Do not force \'inside\' position' )
					),
					
										
					array( 'type' => 'sectionend', 'id' => 'professor_cloud' ),
									
				)); // End Professor Cloud settings
?>