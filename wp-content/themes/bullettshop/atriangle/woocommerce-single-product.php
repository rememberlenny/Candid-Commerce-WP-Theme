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
  if (get_brands()){
    
    echo '<h2 class="subheader brand-name brand-link">'. get_brands() .'</h2>';
  }
}

function atc_title_display(){
  echo '<h3>'; 
  echo the_title(); 
  echo '</h3>';
}

// Remove all reviews

add_filter( 'woocommerce_product_tabs', 'sb_woo_remove_reviews_tab', 98);

function sb_woo_remove_reviews_tab($tabs) {
 unset($tabs['reviews']);
 return $tabs;
}


function atc_get_content(){
  ?>
  <section>
    <h2 class="title" data-section-title><a href="#">Description</a></h2>
    <div class="content" data-section-content>
      <p>
        <?php the_content(); ?>
      </p>
    </div>
  </section>
  <?php 
}

function atc_get_product_details(){
  ?>
  <section>
    <h2 class="title" data-section-title><a href="#">Details</a></h2>
    <div class="content" data-section-content>
      <p>
        <?php the_field('product_details'); ?>
      </p>
    </div>
  </section>
  <?php 
}


function atc_get_size_and_fit(){
  ?>
  <section>
    <h2 class="title" data-section-title><a href="#">Sizing & Fit</a></h2>
    <div class="content" data-section-content>
      <p>
        <?php the_field('size_and_fit'); ?>
      </p>
    </div>
  </section>
  <?php 
}

function atc_product_accordion(){
?>
  <div class="section-container accordion" data-section="accordion">
    <?php
    if( atc_get_content() ){
      atc_get_content();
    }
    ?>

    <?php
    if( get_field("size_and_fit")){
      atc_get_size_and_fit();
    }
    ?>

    <?php 
    if( get_field("product_details")){
      atc_get_product_details();
    }
    ?>
  </div>
<?php
}

add_filter( 'woocommerce_product_tabs', 'atc_product_accordion', 15);

?>

