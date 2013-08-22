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
		<div class="foot">
      <a href="#content">
        <div class="logo"></div>
      </a>
    </div>
    <div class="row text-center">
      <ul class="inline-list text-center privacy-links" style="">
        <li>
          <a href="<?php echo get_page_link('privacy'); ?>">Privacy</a>
        </li>
        <li>|</li>
        <li>
          <a href="<?php echo get_page_link('store-policy'); ?>">Store Policy</a>
        </li>
      </ul>
    </div>
    

	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>




</body>
</html>