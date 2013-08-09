<?php

/**
 * Enqueue Scripts and Styles for Front-End
 */

if ( ! function_exists( 'foundation_assets' ) ) :

function foundation_assets() {

  if (!is_admin()) {

    /** 
     * Deregister jQuery in favour of ZeptoJS
     * jQuery will be used as a fallback if ZeptoJS is not compatible
     * @see foundation_compatibility & http://foundation.zurb.com/docs/javascript.html
     */
    wp_deregister_script('jquery');

    // Load JavaScripts
    wp_enqueue_script( 'foundation', get_template_directory_uri() . '/js/foundation.min.js', null, '4.0', true );
    wp_enqueue_script( 'modernizr', get_template_directory_uri().'/js/vendor/custom.modernizr.js', null, '2.1.0');
    if ( is_singular() ) wp_enqueue_script( "comment-reply" );

    // Load Stylesheets
    wp_enqueue_style( 'normalize', get_template_directory_uri().'/css/normalize.css' );
    wp_enqueue_style( 'foundation', get_template_directory_uri().'/css/foundation.min.css' );
    wp_enqueue_style( 'app', get_stylesheet_uri(), array('foundation') );

    // Load Google Fonts API
    wp_enqueue_style( 'google-fonts', 'http://fonts.googleapis.com/css?family=Open+Sans:400,300' );

  }

}

add_action( 'wp_enqueue_scripts', 'foundation_assets' );

endif;

/**
 * Initialise Foundation JS
 * @see: http://foundation.zurb.com/docs/javascript.html
 */

if ( ! function_exists( 'foundation_js_init' ) ) :

function foundation_js_init () {
    echo '<script>$(document).foundation();</script>';
}

add_action('wp_footer', 'foundation_js_init', 50);

endif;

/**
 * ZeptoJS and jQuery Fallback
 * @see: http://foundation.zurb.com/docs/javascript.html
 */

if ( ! function_exists( 'foundation_comptability' ) ) :

function foundation_comptability () {

echo "<script>";
echo "document.write('<script src=' +";
echo "('__proto__' in {} ? '" . get_template_directory_uri() . "/js/vendor/zepto" . "' : '" . get_template_directory_uri() . "/js/vendor/jquery" . "') +";
echo "'.js><\/script>')";
echo "</script>";

}

add_action('wp_footer', 'foundation_comptability', 10);

endif;

?>