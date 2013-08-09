<?php
/**
 * Template Name: Home Page
 * The template for displaying home page.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package bullettshop
 */

get_header(); ?>


	<div id="primary" class="content-area">
		
		<?php get_template_part( 'modules/slider'); ?>

		<main id="main" class="site-main row" role="main">
		
			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'content', 'product' ); ?>

			<?php endwhile; // end of the loop. ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
