<?php 

  // add_filter(‘sod_ajax_layered_nav_containers’, ‘aln_add_custom_container’);

  function aln_add_custom_container($containers){
      $containers[] = ‘#content';
      return $containers;
  }

  // add_filter(‘sod_ajax_layered_nav_product_container’, ‘aln_product_container’);
  function aln_product_container($product_container){
    //Enter either the class or id of the container that holds your products
    return ‘.products’;
  }

?>