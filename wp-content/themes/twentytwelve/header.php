<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
?><!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<meta name="title" content="The BULLETT Shop"  property="og:title">
<meta name="viewport" content="width=device-width, user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="image" property="og:image" content="http://bullettmedia.com/wp-content/uploads/2013/08/032880.jpg" />
<meta property="fb:admins" content="2337170" />
<meta property="og:type" content="article" />
<meta name="news_keywords" content="America, Depressing Games, Mad Libs, Obama, Politics">
<link rel="shortcut icon" href="http://bullett.wpengine.netdna-cdn.com/wp-content/themes/BULLETTv4/favicon.ico">
<link rel="apple-touch-icon-precomposed" href="http://bullett.wpengine.netdna-cdn.com/wp-content/themes/BULLETTv4/images/icon.png" />
<link rel="apple-touch-icon-precomposed" sizes="29x29" href="http://bullett.wpengine.netdna-cdn.com/wp-content/themes/BULLETTv4/images/icon-29x29.png" />
<link rel="apple-touch-icon-precomposed" sizes="58x58" href="http://bullett.wpengine.netdna-cdn.com/wp-content/themes/BULLETTv4/images/icon-58x58.png" />
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="http://bullett.wpengine.netdna-cdn.com/wp-content/themes/BULLETTv4/images/icon-114x114.png" />
<link rel="apple-touch-icon-precomposed" sizes="144x144" href="http://bullett.wpengine.netdna-cdn.com/wp-content/themes/BULLETTv4/images/icon-144x144.png" />
		


<?php // Loads HTML5 JavaScript file to add support for HTML5 elements in older IE versions. ?>
<!--[if lt IE 9]>
<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
<![endif]-->
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="hfeed site">
	<header id="masthead" class="site-header" role="banner">
		<hgroup>
			<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
			<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
		</hgroup>

		<nav id="site-navigation" class="main-navigation" role="navigation">
			<h3 class="menu-toggle"><?php _e( 'Menu', 'twentytwelve' ); ?></h3>
			<a class="assistive-text" href="#content" title="<?php esc_attr_e( 'Skip to content', 'twentytwelve' ); ?>"><?php _e( 'Skip to content', 'twentytwelve' ); ?></a>
			<?php wp_nav_menu( array( 'theme_location' => 'primary', 'menu_class' => 'nav-menu' ) ); ?>
		</nav><!-- #site-navigation -->

		<?php $header_image = get_header_image();
		if ( ! empty( $header_image ) ) : ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( $header_image ); ?>" class="header-image" width="<?php echo get_custom_header()->width; ?>" height="<?php echo get_custom_header()->height; ?>" alt="" /></a>
		<?php endif; ?>
	</header><!-- #masthead -->

	<div id="main" class="wrapper">