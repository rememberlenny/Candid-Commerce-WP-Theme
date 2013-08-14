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
    add_action( 'woocommerce_before_shop_loop', 'woocommerce_div_before', 30, 0 );
    add_action( 'woocommerce_before_shop_loop', 'woocommerce_breadcrumb', 31, 0 );
    add_action( 'woocommerce_before_shop_loop', 'woocommerce_div_after', 32, 0 );
}

function woocommerce_div_before() {
  echo '<div class="column large-6 hide-medium-down">';
}
function woocommerce_div_after() {
  echo '</div>';
}
// Changing order of single product pages
add_action( 'init', 'atc_reorder_single_product' );

function atc_reorder_single_product() {
    remove_action(  'woocommerce_single_product_summary',       'woocommerce_template_single_add_to_cart', 30 );
    add_action(     'woocommerce_single_product_summary',       'woocommerce_template_single_add_to_cart', 15 );
    remove_action(  'woocommerce_single_product_summary',       'woocommerce_template_single_excerpt'         );
    remove_action(  'woocommerce_single_product_summary',       'woocommerce_template_single_meta', 40 );
    remove_action(  'woocommerce_single_product_summary',       'woocommerce_template_single_meta', 40 );
    remove_action(  'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs',    10 );
    add_action(     'woocommerce_single_product_summary',       'woocommerce_output_product_data_tabs',    25 );
    add_action(     'woocommerce_single_product_summary',       'atc_display_tag',    1 );
}

add_filter( 'woocommerce_breadcrumb_defaults', 'my_change_breadcrumb_delimiter' );
function my_change_breadcrumb_delimiter( $defaults ) {
    // Change the breadcrumb delimiter from '/' to '>'
    $defaults['delimiter'] = ' &gt; ';
    return $defaults;
}

add_action( 'init', 'atc_pagination' );
// Move Pagnation to top
function atc_pagination(){
  add_action( 'woocommerce_before_shop_loop', 'woocommerce_pagination', 35, 0 );
}


?>
