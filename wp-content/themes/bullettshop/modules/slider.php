<?php
  if (is_front_page()){
  ?> 
  <div id="slide-case" class="slideshow-wrapper row slide-case">
    <div class="preloader"></div>
    <ul data-orbit data-options="animation:fade; bullets: false; slide_number: false; timer: false;">
      <?php
      if ( function_exists( 'ot_get_option' ) ) :
      ?>
      <li>
        <img src="<?php echo ot_get_option( 'home_page_featured_slider' ); ?>" alt="<?php bloginfo( 'name' ); ?>">
      </li>
      <li>
        <img src="<?php echo ot_get_option( 'slide_2' ); ?>" alt="<?php bloginfo( 'name' ); ?>">
      </li>
      <li>
        <img src="<?php echo ot_get_option( 'home_page_featured_slider' ); ?>" alt="<?php bloginfo( 'name' ); ?>">
      </li>
      <?php
      endif;
      ?>
    </ul>
  </div>
  <?php
  }
?>