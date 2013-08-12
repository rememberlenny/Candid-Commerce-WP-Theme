<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <main id="main">
 *
 * @package bullettshop
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width">
<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<div id="page" class="hfeed site">
	<?php do_action( 'before' ); ?>
	<header id="masthead" class=" row site-header" role="banner">
		<div class="site-branding">
			<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><img src="<?php echo ot_get_option( 'header_logo' ); ?>" alt="<?php bloginfo( 'name' ); ?>"></a></h1>
			<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
		</div>

		<nav id="site-navigation" class="main-navigation" role="navigation">
			<div class="social-icon-case">
			<ul class="inline-list">
				<li>
				<a href="#" class="social-icon"><img src="<?php bloginfo('template_directory'); ?>/images/common-assets/icon-facebook.png"></a>
				</li>
				<li>
				<a href="#" class="social-icon"><img src="<?php bloginfo('template_directory'); ?>/images/common-assets/icon-twitter.png"></a>
				</li>
				<li>
				<a href="#" class="social-icon"><img src="<?php bloginfo('template_directory'); ?>/images/common-assets/icon-insta.png"></a>
				</li>
				<li>
				<a href="#" class="social-icon"><img src="<?php bloginfo('template_directory'); ?>/images/common-assets/icon-tumblr.png"></a>
				</li>
			</ul>
			</div>
			<h1 class="menu-toggle"><?php _e( 'Menu', 'bullettshop' ); ?></h1>
			
			<div class="screen-reader-text skip-link"><a href="#content" title="<?php esc_attr_e( 'Skip to content', 'bullettshop' ); ?>"><?php _e( 'Skip to content', 'bullettshop' ); ?></a></div>

			<?php wp_nav_menu( array( 'theme_location' => 'primary' ) ); ?>
			<?php include (TEMPLATEPATH . '/searchform-header.php'); ?>
		</nav><!-- #site-navigation -->
	
	</header><!-- #masthead -->
	
	<?php
  if (is_page('47'))
		get_template_part( 'modules/slider'); 
	?>

	<div id="content" class="site-content row">

	

	<?php atr_header_post(); ?>