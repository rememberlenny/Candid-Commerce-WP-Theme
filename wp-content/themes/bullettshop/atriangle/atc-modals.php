<?php

function ipadPopUpScript(){
?>
  jQuery("a[href$='http://bullettstoreat.wpengine.com/p/ipad-magazine-subscription/']").attr('data-reveal-id', 'ipadPopUp');
<?php
}

function ipadPopUpModal(){
?>
  jQuery('#colophon').after('<div id="ipadPopUp" class="reveal-modal"><div class="row"><div class="large-4 small-12 column"style="float: right"><?php echo get_the_post_thumbnail('post-419', 'medium');?><\/div><div class="large-8 column small-12"><p class="lead">The following link will open a'+
  ' new window to the iTunes AppStore. </p><p>You can make individual purchases or subscribe to the Bullett maga'+
  'zine from the iTunes AppStore. If you have any items in your shopping cart, they '+
  'won\'t go anywhere (as long as you don\'t close the window).</p><a href="'+
  'https://itunes.apple.com/us/app/bullett/id557294227?mt=8" class="button l'+
  'arge" target="_blank" style="width:100%!important;">Get it!<\/a><\/div><a class="clo'+
  'se-reveal-modal">&#215;<\/a><\/div><\/div>');
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

