<?php

// Remove WooCommerce Hooks
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);


// Add own functions to display the wrappers
add_action('woocommerce_before_main_content', 'atr_header_post', 10);
add_action('woocommerce_after_main_content', 'atr_footer_pre', 10);

add_theme_support( 'woocommerce' );

// Remove breadcrumbs from all pages
add_action( 'init', 'jk_remove_wc_breadcrumbs' );

function jk_remove_wc_breadcrumbs() {
    remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
}

// Changing order of single product pages
add_action( 'init', 'atc_reorder_single_product' );

function atc_reorder_single_product() {
    remove_action(  'woocommerce_single_product_summary',       'woocommerce_template_single_price'           );
    remove_action(  'woocommerce_single_product_summary',       'woocommerce_template_single_add_to_cart', 30 );
    add_action(     'woocommerce_single_product_summary',       'woocommerce_template_single_add_to_cart', 15 );
    remove_action(  'woocommerce_single_product_summary',       'woocommerce_template_single_excerpt'         );
    // remove_action(  'woocommerce_single_product_summary',       'woocommerce_template_single_meta', 40 );
    remove_action(  'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs',    10 );
    add_action(     'woocommerce_single_product_summary',       'woocommerce_output_product_data_tabs',    25 );
    add_action(     'woocommerce_single_product_summary',       'atc_display_tag',    1 );
}


// Change add to cart button text
 
add_filter('single_add_to_cart_text', 'woo_custom_cart_button_text');
 
function woo_custom_cart_button_text() {
  return __('Get it!', 'woocommerce');
}

function atc_display_tag(){
    $size = get_the_terms( $post->ID, 'product_tag' );
    print '<h1>'.$size[24]->name.'</h1>';
}

?>