<?php

function ipadPopUpScript(){
?>
  jQuery("a[href$='http://bullettstoreat.wpengine.com/p/ipad-magazine-subscr'+
  'iption/']").attr('data-reveal-id', 'ipadPopUp');
<?php
}

function ipadPopUpModal(){
?>
  jQuery('#colophon').after('<div id="ipadPopUp" class="reveal-modal"><h2>Th'+ 
  'is will open a new tab.</h2><p class="lead">The following link will open a'+
  ' new window to the iTunes AppStore. You can subscribe to the Bullett maga'+
  'zine from there.</p><p>If you have any items in your shopping cart, they '+
  'won\'t go anywhere (as long as you don\'t close the window).</p><a href="'+
  'https://itunes.apple.com/us/app/bullett/id557294227?mt=8" class="button l'+
  'arge single_add_to_cart_button" target="_blank">Get it!<\/a><a class="clo'+
  'se-reveal-modal">&#215;<\/a><\/div>');
<?php
}

function PopUpModal(){
  ?>
  <script>
    <?php ipadPopUpScript(); ?>
    <?php ipadPopUpModal(); ?>
  </script>
  <?php
}

add_action('wp_footer', 'PopUpModal', 33);

?>