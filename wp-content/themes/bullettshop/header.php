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
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-43414732-1', 'bullettmedia.com');
  ga('send', 'pageview');

</script>
<div id="page" class="hfeed site">
	<?php do_action( 'before' ); ?>

    

	<header id="masthead" class="row site-header" role="banner">
		<div class="site-branding ">
			<h1 class="site-title">
        <a class="logo-head" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
        </a>
      </h1>
			<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
		</div>
    <nav class="cart-menu-holder">
      <section>
        <?php atc_cart_menu(); ?>
      </section>
    </nav>
		<div id="site-navigation" class="main-navigation" role="navigation">
			<div class="screen-reader-text skip-link"><a href="#content" title="<?php esc_attr_e( 'Skip to content', 'bullettshop' ); ?>"><?php _e( 'Skip to content', 'bullettshop' ); ?></a></div>
	        <div class="top-bar-container contain-to-grid">
            <nav class="top-bar">
                <ul class="title-area">
                	<li class="social-icon-wrap">
										<a href="http://www.facebook.com/pages/Bullett-Magazine/143235499055215" class="social-icon"><img src="<?php bloginfo('template_directory'); ?>/images/common-assets/icon-facebook.png"></a>
										<a href="http://twitter.com/#!/bullettmedia/" class="social-icon"><img src="<?php bloginfo('template_directory'); ?>/images/common-assets/icon-twitter.png"></a>
										<a href="http://instagram.com/bullettmagazine" class="social-icon"><img src="<?php bloginfo('template_directory'); ?>/images/common-assets/icon-insta.png"></a>
										<a href="http://bullettmedia.tumblr.com/" class="social-icon"><img src="<?php bloginfo('template_directory'); ?>/images/common-assets/icon-tumblr.png"></a>     
                  </li>
                  <li class="toggle-topbar menu-icon"><a href="#"><span>Menu</span></a></li>
                </ul>
                <section class="top-bar-section">
                    <?php foundation_top_bar_l(); ?>
											
										<ul class="top-bar-menu right">
											<li>
												<?php include (TEMPLATEPATH . '/searchform-header.php'); ?>
											</li>
										</ul>
                </section>
            </nav>
        </div>
		</div><!-- #site-navigation -->
	
	</header><!-- #masthead -->
	
	<?php
  if (is_page('47'))
		get_template_part( 'at-modules/slider'); 
	?>

	<div id="content" class="site-content row">

	

	<?php atr_header_post(); ?>