<?php

/**
 * WC_Brands class.
 */
class WC_Brands {

	var $template_url;
	var $plugin_path;

	/**
	 * __construct function.
	 */
	public function __construct() {
		$this->template_url = apply_filters( 'woocommerce_template_url', 'woocommerce/' );

		add_action( 'woocommerce_register_taxonomy', array( $this, 'init_taxonomy' ) );
		add_action( 'widgets_init', array( $this, 'init_widgets' ) );
		add_filter( 'template_include', array( $this, 'template_loader' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'styles' ) );
		add_action( 'wp', array( $this, 'body_class' ) );

		add_action( 'woocommerce_product_meta_end', array( $this, 'show_brand' ) );

		if ( function_exists( 'add_image_size' ) ) {
			add_image_size( 'brand-thumb', 300, 9999 );
		}

		if ( get_option( 'wc_brands_show_description' ) == 'yes' )
			add_action( 'woocommerce_archive_description', array( $this, 'brand_description' ) );

		$this->register_shortcodes();
    }

    function body_class() {
	    global $woocommerce;

	    if ( is_tax( 'product_brand' ) ) {
			$woocommerce->add_body_class( 'woocommerce' );
			$woocommerce->add_body_class( 'woocommerce-page' );
		}
    }

    function styles() {
	    wp_enqueue_style( 'brands-styles', plugins_url( '/assets/css/style.css', dirname( __FILE__ ) ) );
    }

	/**
	 * init_taxonomy function.
	 *
	 * @access public
	 */
	function init_taxonomy() {
		global $woocommerce;

		$shop_page_id = woocommerce_get_page_id( 'shop' );

		$base_slug = $shop_page_id > 0 && get_page( $shop_page_id ) ? get_page_uri( $shop_page_id ) : 'shop';

		$category_base = get_option('woocommerce_prepend_shop_page_to_urls') == "yes" ? trailingslashit( $base_slug ) : '';

		$cap = version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ? 'manage_woocommerce_products' : 'edit_products';

		register_taxonomy( 'product_brand',
	        array('product'),
	        apply_filters( 'register_taxonomy_product_brand', array(
	            'hierarchical' 			=> true,
	            'update_count_callback' => '_update_post_term_count',
	            'label' 				=> __( 'Brands', 'wc_brands'),
	            'labels' => array(
	                    'name' 				=> __( 'Brands', 'wc_brands' ),
	                    'singular_name' 	=> __( 'Brand', 'wc_brands' ),
	                    'search_items' 		=> __( 'Search Brands', 'wc_brands' ),
	                    'all_items' 		=> __( 'All Brands', 'wc_brands' ),
	                    'parent_item' 		=> __( 'Parent Brand', 'wc_brands' ),
	                    'parent_item_colon' => __( 'Parent Brand:', 'wc_brands' ),
	                    'edit_item' 		=> __( 'Edit Brand', 'wc_brands' ),
	                    'update_item' 		=> __( 'Update Brand', 'wc_brands' ),
	                    'add_new_item' 		=> __( 'Add New Brand', 'wc_brands' ),
	                    'new_item_name' 	=> __( 'New Brand Name', 'wc_brands' )
	            	),
	            'show_ui' 				=> true,
	            'show_in_nav_menus' 	=> true,
				'capabilities'			=> array(
					'manage_terms' 		=> $cap,
					'edit_terms' 		=> $cap,
					'delete_terms' 		=> $cap,
					'assign_terms' 		=> $cap
				),
	            'rewrite' 				=> array( 'slug' => $category_base . __( 'brand', 'wc_brands' ), 'with_front' => false, 'hierarchical' => true ),
	        ) )
	    );
	}

	/**
	 * init_widgets function.
	 *
	 * @access public
	 */
	function init_widgets() {

		// Inc
		require_once( 'widgets/class-wc-widget-brand-description.php' );
		require_once( 'widgets/class-wc-widget-brand-nav.php' );
		require_once( 'widgets/class-wc-widget-brand-thumbnails.php' );

		// Register
		register_widget( 'WC_Widget_Brand_Description' );
		register_widget( 'WC_Widget_Brand_Nav' );
		register_widget( 'WC_Widget_Brand_Thumbnails' );
	}

	/**
	 * Get the plugin path
	 */
	function plugin_path() {
		if ( $this->plugin_path ) return $this->plugin_path;

		return $this->plugin_path = untrailingslashit( plugin_dir_path( dirname( __FILE__ ) ) );
	}

	/**
	 * template_loader
	 *
	 * Handles template usage so that we can use our own templates instead of the themes.
	 *
	 * Templates are in the 'templates' folder. woocommerce looks for theme
	 * overides in /theme/woocommerce/ by default
	 *
	 * For beginners, it also looks for a woocommerce.php template first. If the user adds
	 * this to the theme (containing a woocommerce() inside) this will be used for all
	 * woocommerce templates.
	 */
	function template_loader( $template ) {

		$find = array( 'woocommerce.php' );
		$file = '';

		if ( is_tax( 'product_brand' ) ) {

			$term = get_queried_object();

			$file 		= 'taxonomy-' . $term->taxonomy . '.php';
			$find[] 	= 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
			$find[] 	= $this->template_url . 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
			$find[] 	= $file;
			$find[] 	= $this->template_url . $file;

		}

		if ( $file ) {
			$template = locate_template( $find );
			if ( ! $template ) $template = $this->plugin_path() . '/templates/' . $file;
		}

		return $template;
	}

	/**
	 * brand_image function.
	 *
	 * @access public
	 */
	function brand_description() {

		if ( ! is_tax( 'product_brand' ) )
			return;

		if ( ! get_query_var( 'term' ) )
			return;

		$thumbnail = '';

		$term = get_term_by( 'slug', get_query_var( 'term' ), 'product_brand' );
		$thumbnail = get_brand_thumbnail_url( $term->term_id, 'full' );

		woocommerce_get_template( 'brand-description.php', array(
			'thumbnail'	=> $thumbnail
		), 'woocommerce-brands', $this->plugin_path() . '/templates/' );
	}

	/**
	 * show_brand function.
	 *
	 * @access public
	 * @return void
	 */
	function show_brand() {
		global $post;

		if ( is_singular( 'product' ) ) {
			echo get_brands( $post->ID, ', ', ' <span class="posted_in">' . __('Brand:', 'wc_brands').' ', '.</span>' );
		}
	}

	/**
	 * register_shortcodes function.
	 *
	 * @access public
	 */
	function register_shortcodes() {

		add_shortcode( 'product_brand', array( $this, 'output_product_brand' ) );
		add_shortcode( 'product_brand_thumbnails', array( $this, 'output_product_brand_thumbnails' ) );
		add_shortcode( 'product_brand_list', array( $this, 'output_product_brand_list' ) );

	}

	/**
	 * output_product_brand function.
	 *
	 * @access public
	 */
	function output_product_brand( $atts ) {
		global $post;

		extract( shortcode_atts( array(
		      'width'   => '',
		      'height'  => '',
		      'class'   => 'aligncenter',
		      'post_id' => ''
	    ), $atts ) );

	    if ( ! $post_id && ! $post )
	    	return;

		if ( ! $post_id )
			$post_id = $post->ID;

		$brands = wp_get_post_terms( $post_id, 'product_brand', array( "fields" => "ids" ) );

		if ( $brands )
			$brand = $brands[0];

		if ( ! empty( $brand ) ) {

			$thumbnail = get_brand_thumbnail_url( $brand );

			if ( $thumbnail ) {

				$term = get_term_by( 'id', $brand, 'product_brand' );

				if ( $width || $height ) {
					$width = $width ? $width : 'auto';
					$height = $height ? $height : 'auto';
				}

				ob_start();

				woocommerce_get_template( 'shortcodes/single-brand.php', array(
					'term'		=> $term,
					'width'		=> $width,
					'height'	=> $height,
					'thumbnail'	=> $thumbnail,
					'class'		=> $class
				), 'woocommerce-brands', untrailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) . '/templates/' );

				return ob_get_clean();

			}
		}

	}

	/**
	 * output_product_brand_list function.
	 *
	 * @access public
	 * @return void
	 */
	function output_product_brand_list( $atts ) {

		 extract( shortcode_atts( array(
		      'show_top_links' 	=> true,
		      'show_empty' 		=> true
	     ), $atts ) );

		$product_brands = array();
		$terms = get_terms( 'product_brand', array( 'hide_empty' => 0 ) );

		foreach ( $terms as $term ) {

			$term_letter = substr( $term->slug, 0, 1 );

			if ( ctype_alpha( $term_letter ) ) {

				foreach ( range( 'a', 'z' ) as $i )
					if ( $i == $term_letter ) {
						$product_brands[ $i ][] = $term;
						break;
					}

			} else {
				$product_brands[ '0-9' ][] = $term;
			}

		}

		ob_start();

		woocommerce_get_template( 'shortcodes/brands-a-z.php', array(
			'terms'				=> $terms,
			'index'				=> array_merge( range( 'a', 'z' ), array( '0-9' ) ),
			'product_brands'	=> $product_brands,
			'show_empty'		=> $show_empty,
			'show_top_links'	=> $show_top_links
		), 'woocommerce-brands', untrailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) . '/templates/' );

		return ob_get_clean();
	}

	/**
	 * output_product_brand_thumbnails function.
	 *
	 * @access public
	 * @param mixed $atts
	 * @return void
	 */
	function output_product_brand_thumbnails( $atts ) {

		extract( shortcode_atts( array(
		      'show_empty' 		=> true,
		      'columns'			=> 4,
		      'hide_empty'		=> 0,
		      'orderby'			=> 'name',
		      'exclude'			=> '',
		      'number'			=> ''
	     ), $atts ) );

	    $exclude = array_map( 'intval', explode(',', $exclude) );
	    $order = $orderby == 'name' ? 'asc' : 'desc';

		$brands = get_terms( 'product_brand', array( 'hide_empty' => $hide_empty, 'orderby' => $orderby, 'exclude' => $exclude, 'number' => $number, 'order' => $order ) );

		if ( ! $brands )
			return;

		ob_start();

		woocommerce_get_template( 'widgets/brand-thumbnails.php', array(
			'brands'	=> $brands,
			'columns'	=> $columns
		), 'woocommerce-brands', untrailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) . '/templates/' );

		return ob_get_clean();
	}
}

$GLOBALS['WC_Brands'] = new WC_Brands();