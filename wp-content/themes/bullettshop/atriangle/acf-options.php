<?php

/*
*  Change the Options Page menu to 'Theme Options'
*/
 
add_action('init','acf_options_modification', 22);

function acf_options_modification(){

   if( function_exists('acf_set_options_page_title') )
   {
       acf_set_options_page_title( 'Image Slider' );
   }

   if( function_exists('acf_set_options_page_menu') )
   {
       acf_set_options_page_menu( 'Image Slider' );
   }

}

?>