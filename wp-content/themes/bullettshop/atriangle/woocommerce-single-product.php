<?php

function wpa89819_wc_single_product(){


}
add_action( 'woocommerce_single_product_summary', 'wpa89819_wc_single_product', 2 );

// Change add to cart button text
 
add_filter('single_add_to_cart_text', 'woo_custom_cart_button_text');
 
function woo_custom_cart_button_text() {
  return __('Get it!', 'woocommerce');
}

function atc_display_tag(){
  echo '<h2 class="subheader brand-name">' .'</h2>';
}

// Remove all reviews

add_filter( 'woocommerce_product_tabs', 'sb_woo_remove_reviews_tab', 98);

function sb_woo_remove_reviews_tab($tabs) {
 unset($tabs['reviews']);
 return $tabs;
}

?>