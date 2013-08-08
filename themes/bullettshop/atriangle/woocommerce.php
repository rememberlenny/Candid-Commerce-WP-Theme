<?php

// Remove WooCommerce Hooks
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);


//Add own functions to display the wrappers
add_action('woocommerce_before_main_content', 'atr_header_post', 10);
add_action('woocommerce_after_main_content', 'atr_footer_pre', 10);

add_theme_support( 'woocommerce' );

?>