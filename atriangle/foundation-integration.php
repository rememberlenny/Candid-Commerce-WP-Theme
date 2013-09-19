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
    // wp_deregister_script('jquery');

    // Load JavaScripts
    wp_enqueue_script( 'foundation', get_template_directory_uri() . '/js/foundation.min.js', null, '4.0', true );
    wp_enqueue_script( 'modernizr', get_template_directory_uri().'/js/vendor/custom.modernizr.js', null, '2.1.0');
    wp_enqueue_script( 'throttle', get_template_directory_uri().'/js/throttle.js', null, false, true);
    if ( is_singular() ) wp_enqueue_script( "comment-reply" );

    // Load Stylesheets
    wp_enqueue_style( 'app', get_stylesheet_uri(), array('foundation') );

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
    echo '<script>jQuery(document).foundation();</script>';
}

add_action('wp_footer', 'foundation_js_init', 50);

endif;

/**
 * ZeptoJS and jQuery Fallback
 * @see: http://foundation.zurb.com/docs/javascript.html
 */

// if ( ! function_exists( 'foundation_comptability' ) ) :

// function foundation_comptability () {

// echo "<script>";
// echo "document.write('<script src=' +";
// echo "('__proto__' in {} ? '" . get_template_directory_uri() . "/js/vendor/jquery" . "' : '" . get_template_directory_uri() . "/js/vendor/jquery" . "') +";
// echo "'.js><\/script>')";
// echo "</script>";

// }

// add_action('wp_footer', 'foundation_comptability', 10);

// endif;


/**
 * Top Scroller Solution
 */

// if ( ! function_exists( 'top_scroll' ) ) :

// function top_scroll () {
//     if (is_page('47')): 
//     echo "<script>";
//     echo "var elWrap = $('#page');";
//     echo "var elMenu = $('#slide-case');";
//     echo "var osMenu = elMenu.offset().top;";
//     echo "var osFoot = $('#content').offset().top - elMenu.height();";
//     echo "";
//     echo "$(window).scroll($.throttle(10, function() {";
//     echo "";
//     echo "elMenu.css('top', 0);";
//     echo "elMenu.css('left', 0);";
//     echo "elMenu.css('right', 0);";
//     echo "var edge = $(window).scrollTop();";
//     echo "";
//     echo "if (osMenu <= edge && osFoot > edge) {";
//     echo "elWrap.addClass('dock').removeClass('stop');";
//     echo "}";
//     echo "else {";
//     echo "elWrap.removeClass('dock stop');";
//     echo "}";
//     echo "if (osFoot <= edge){";
//     echo "elMenu.css('top', 0);";
//     echo "elWrap.removeClass('dock').addClass('stop');";
//     echo "}";
//     echo "";
//     echo "}));";
//     echo "</script>";
//     endif;
// }

// add_action('wp_footer', 'top_scroll', 55);

// endif;

?>
