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
		<div class="row text-center">
      <a href="<?php echo home_url(); ?>">Back to Top</a>
    </div>
    <div class="foot">
      <a href="http://bullettmedia.com">
        <div class="logo"></div>
      </a>
    </div>
    <div class="row text-center">
      <ul class="inline-list text-center privacy-links" style="">
        <li>
          <a href="http://shop.bullettmedia.com/privacy-policy/">Privacy</a>
        </li>
        <li>|</li>
        <li>
          <a href="http://shop.bullettmedia.com/store-policy/">Store Policy</a>
        </li>
      </ul>
    </div>
    

	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>




</body>
</html>