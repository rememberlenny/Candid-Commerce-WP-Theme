
<?php if(get_field('image_slider_repeater')): ?>
  <div id="slide-case" class="slideshow-wrapper row slide-case">
    <div class="preloader"></div>
    <ul data-orbit data-options="animation:fade; bullets: false; slide_number: false; timer: false;">
      <?php while(has_sub_field('image_slider_repeater')): ?>
        <li>
          <a href="<?php the_sub_field('image_slide_link_destination'); ?>">
            <img src="<?php the_sub_field('image_slide_photo_content'); ?>" alt="<?php bloginfo( 'name' ); ?>">
          </a>
        </li>
      <?php endwhile; ?>
    </ul>
  </div>
<?php endif; ?>