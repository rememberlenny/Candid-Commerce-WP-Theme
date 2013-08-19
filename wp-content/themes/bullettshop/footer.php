<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package bullettshop
 */
?>

  <?php atr_footer_pre(); ?>

	</div><!-- #content -->

	<footer id="colophon" class="site-footer row" role="contentinfo">
		<div class="site-info column">
			<!-- Site was developed and implemented by Leonard Kiyoshi Bogdonoff (http://rememberlenny.com) from A Triangle Corporation (http://atriangle.com) -->
			<a href="http://wordpress.org/" title="<?php esc_attr_e( 'A Semantic Personal Publishing Platform', 'bullettshop' ); ?>" rel="generator"><?php printf( __( 'Proudly powered by %s', 'bullettshop' ), 'WordPress' ); ?></a>
			<span class="sep"> | </span>
			<?php printf( __( 'Theme: %1$s by %2$s.', 'bullettshop' ), 'bullettshop', '<a href="http://underscores.me/" rel="designer">Underscores.me</a>' ); ?>
		</div><!-- .site-info -->
		<div class="foot">
			<div class="logo">
				
			</div>
		</div>
	
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>


</body>
</html>