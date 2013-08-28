<?php

// View all link
add_action('init', 'woo_showall');

function woo_showall(){
    if( isset( $_GET['showall'] ) ){ 
        add_filter( 'loop_shop_per_page', create_function( '$cols', 'return -1;' ) ); 
    } else {
        add_filter( 'loop_shop_per_page', create_function( '$cols', 'return 12;' ) );
    }
}

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
    add_action( 'woocommerce_before_shop_loop', 'woocommerce_div_before', 12, 0 );
    add_action( 'woocommerce_before_shop_loop', 'woocommerce_breadcrumb', 13, 0 );
    add_action('woocommerce_before_main_content', 'woocommerce_result_count', 14);
    add_action( 'woocommerce_before_shop_loop', 'woocommerce_div_after', 15, 0 );
}

function woocommerce_div_before() {
  // echo '<div class="column large-6 hide-medium-down">';
}
function woocommerce_div_after() {
  // echo '</div>';
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

add_action( 'init', 'atc_alter_product_top' );

function atc_alter_product_top(){
  remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
}


add_action( 'init', 'atc_pagination' );
// Move Pagnation to top
function atc_pagination(){
  add_action( 'woocommerce_before_shop_loop', 'woocommerce_pagination', 35, 0 );
  remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10, 0 );
  // add_action( 'woocommerce_before_shop_loop', 'atc_showall_button', 34, 0 );
  add_action( 'woocommerce_sidebar', 'woocommerce_pagination', 15, 0 );
}





function atc_showall_button(){
  echo '<a href="' . get_permalink( $post->ID ) . '?showall=1">Show all</a>';
}

add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_rollover_image', 12 );

function woocommerce_rollover_image(){
    if(get_field('product_rollover_image')){
        $image = wp_get_attachment_image_src(get_field('product_rollover_image'), 'medium'); ?>
        <img src="<?php echo $image[0]; ?>" alt="<?php echo get_the_title(get_field('product_rollover_image')); ?>" class="rollover" style="display:none;" width="275" height="330" />
    <?php
    }
}

function atc_begin_shoploop_item_perma(){
  echo '<a href="';
  the_permalink(); 
  echo '">';
}

function atc_end_shoploop_item_perma(){
  echo '</a>';
}

function atc_begin_shoploop_text_box(){
  echo '<div class="text-box">';
}

function atc_end_shoploop_text_box(){
  echo '</div>';
}

function after_thething(){
    echo '</a>';
}
function before_thething(){
    echo '<a href="';
  the_permalink(); 
  echo '">';
}

  add_action(     'woocommerce_after_shop_loop_item_title',       'atc_title_display_cat',   7 );
  add_action(     'woocommerce_after_shop_loop_item_title',       'atc_display_tag',    5 );
  add_action( 'woocommerce_before_shop_loop_item_title', 'atc_begin_shoploop_item_perma', 5);
  add_action( 'woocommerce_before_shop_loop_item_title', 'atc_end_shoploop_item_perma', 13);
  add_action( 'woocommerce_after_shop_loop_item_title', 'atc_begin_shoploop_item_perma', 2);
  add_action( 'woocommerce_after_shop_loop_item_title', 'atc_begin_shoploop_text_box', 3);
  add_action( 'woocommerce_after_shop_loop_item_title', 'atc_end_shoploop_text_box', 15);
  add_action( 'woocommerce_after_shop_loop_item_title', 'atc_end_shoploop_item_perma', 20);
  add_action( 'woocommerce_after_shop_loop_item_title', 'before_thething', 9);
  add_action( 'woocommerce_after_shop_loop_item_title', 'after_thething', 11);



function atc_title_display_cat(){
  echo '<a href="';
  the_permalink(); 
  echo '">';
  echo '<h3>'; 
  echo the_title(); 
  echo '</h3>';
  echo '</a>';
}

?>

