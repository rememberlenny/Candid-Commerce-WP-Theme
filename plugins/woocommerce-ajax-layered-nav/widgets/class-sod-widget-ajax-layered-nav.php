<?php
/**
 * Layered Nav Init
 */
add_action( 'init', 'sod_ajax_layered_nav_init', 99 );

function sod_ajax_layered_nav_init( ) {

	if ( is_active_widget( false, false, 'sod_ajax_layered_nav', true ) && ! is_admin() ) {

		global $_chosen_attributes, $woocommerce, $_attributes_array;
		$_chosen_attributes = $_attributes_array = array();

		$attribute_taxonomies = $woocommerce->get_attribute_taxonomies();
		if ( $attribute_taxonomies ) {
			foreach ( $attribute_taxonomies as $tax ) {

		    	$attribute = sanitize_title( $tax->attribute_name );
		    	$taxonomy = $woocommerce->attribute_taxonomy_name( $attribute );

				// create an array of product attribute taxonomies
				$_attributes_array[] = $taxonomy;

		    	$name = 'filter_' . $attribute;
		    	$query_type_name = 'query_type_' . $attribute;

		    	if ( ! empty( $_GET[ $name ] ) && taxonomy_exists( $taxonomy ) ) {

		    		$_chosen_attributes[ $taxonomy ]['terms'] = explode( ',', $_GET[ $name ] );

		    		if ( ! empty( $_GET[ $query_type_name ] ) && $_GET[ $query_type_name ] == 'or' )
		    			$_chosen_attributes[ $taxonomy ]['query_type'] = 'or';
		    		else
		    			$_chosen_attributes[ $taxonomy ]['query_type'] = 'and';

				}
			}
	    }

	    add_filter('loop_shop_post_in', 'woocommerce_ajax_layered_nav_query');
   }
}


/**
 * Layered Nav post filter
 *
 * @package 	WooCommerce/Widgets
 * @access public
 * @param array $filtered_posts
 * @return array
 */
function woocommerce_ajax_layered_nav_query( $filtered_posts ) {
	global $_chosen_attributes, $woocommerce, $wp_query;
	
	if ( sizeof( $_chosen_attributes ) > 0 ) {

		$matched_products = array();
		$filtered_attribute = false;

		foreach ( $_chosen_attributes as $attribute => $data ) {

			$matched_products_from_attribute = array();
			$filtered = false;

			if ( sizeof( $data['terms'] ) > 0 ) {
				foreach ( $data['terms'] as $value ) {

					$posts = get_posts(
						array(
							'post_type' 	=> 'product',
							'numberposts' 	=> -1,
							'post_status' 	=> 'publish',
							'fields' 		=> 'ids',
							'no_found_rows' => true,
							'tax_query' => array(
								array(
									'taxonomy' 	=> $attribute,
									'terms' 	=> $value,
									'field' 	=> 'id'
								)
							)
						)
					);

					// AND or OR
					if ( $data['query_type'] == 'or' ) {

						if ( ! is_wp_error( $posts ) && ( sizeof( $matched_products_from_attribute ) > 0 || $filtered ) )
							$matched_products_from_attribute = array_merge($posts, $matched_products_from_attribute);
						elseif ( ! is_wp_error( $posts ) )
							$matched_products_from_attribute = $posts;

					} else {

						if ( ! is_wp_error( $posts ) && ( sizeof( $matched_products_from_attribute ) > 0 || $filtered ) )
							$matched_products_from_attribute = array_intersect($posts, $matched_products_from_attribute);
						elseif ( ! is_wp_error( $posts ) )
							$matched_products_from_attribute = $posts;
					}

					$filtered = true;

				}
			}

			if ( sizeof( $matched_products ) > 0 || $filtered_attribute )
				$matched_products = array_intersect( $matched_products_from_attribute, $matched_products );
			else
				$matched_products = $matched_products_from_attribute;

			$filtered_attribute = true;

		}

		if ( $filtered ) {

			$woocommerce->query->layered_nav_post__in = $matched_products;
			$woocommerce->query->layered_nav_post__in[] = 0;

			if ( sizeof( $filtered_posts ) == 0 ) {
				$filtered_posts = $matched_products;
				$filtered_posts[] = 0;
			} else {
				$filtered_posts = array_intersect( $filtered_posts, $matched_products );
				$filtered_posts[] = 0;
			}

		}
	}

	return (array) $filtered_posts;
}

/**
 * Ajax Layered Nav Widget
 * Ajax widget to control layaered navigation
 */
class SOD_Widget_Ajax_Layered_Nav extends WP_Widget {

	/** Variables to setup the widget. */
	var $woo_widget_cssclass;
	var $woo_widget_description;
	var $woo_widget_idbase;
	var $woo_widget_name;

	/** constructor */
	function SOD_Widget_Ajax_Layered_Nav() {

		/* Widget variable settings. */
		$this->woo_widget_cssclass = 'widget_layered_nav';
		$this->woo_widget_description = __( 'Shows a custom attribute in a widget which lets you narrow down the list of products when viewing product categories.', 'sod_ajax_layered_nav' );
		$this->woo_widget_idbase = 'sod_ajax_layered_nav';
		$this->woo_widget_name = __('WooCommerce Ajax Layered Nav', 'sod_ajax_layered_nav' );

		/* Widget settings. */
		$widget_ops = array( 'classname' => $this->woo_widget_cssclass, 'description' => $this->woo_widget_description );

		/* Create the widget. */
		$this->WP_Widget('sod_ajax_layered_nav', $this->woo_widget_name, $widget_ops);
	}

	/** @see WP_Widget */
	function widget( $args, $instance ) {
		extract($args);

		global $_chosen_attributes, $woocommerce, $_attributes_array;

		if ( !is_post_type_archive('product') && !is_tax( array_merge( $_attributes_array, array('product_cat', 'product_tag') ) ) ) return;

		$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
		$taxonomy 	= $woocommerce->attribute_taxonomy_name($instance['attribute']);
		$query_type = (isset($instance['query_type'])) ? $instance['query_type'] : 'and';
		$display_type = (isset($instance['display_type'])) ? $instance['display_type'] : 'list';
		$labels = (isset($instance['labels'])) ? unserialize($instance['labels']) : null;
		$colors = (isset($instance['colors'])) ? unserialize($instance['colors']) : null;

		if (!taxonomy_exists($taxonomy)) return;

		$args = array(
			'hide_empty' => '1'
		);
		$terms = get_terms( $taxonomy, $args );
		$count = count($terms);

		if($count > 0){

			$found = false;
			ob_start();

			echo $before_widget . $before_title . $title . $after_title;

			// Force found when option is selected
			if (is_array($_chosen_attributes) && array_key_exists($taxonomy, $_chosen_attributes)) $found = true;
				switch($display_type){
					/* List of Checkboxes */
					case "checkbox":
						echo '<nav>
						<div class="ajax-layered"><ul class="checkboxes">';
						foreach ($terms as $term) {
							// Get count based on current view - uses transients
							$transient_name = 'woocommerce_layered_nav_count_' . sanitize_key($taxonomy) . sanitize_key( $term->term_id );
							delete_transient($transient_name);
							if ( false === ( $_products_in_term = get_transient( $transient_name ) ) ) {
								$_products_in_term = get_objects_in_term( $term->term_id, $taxonomy );
								set_transient( $transient_name, $_products_in_term );
							}
							$option_is_set = (isset($_chosen_attributes[$taxonomy]) && in_array($term->term_id, $_chosen_attributes[$taxonomy]['terms']));
							// If this is an AND query, only show options with count > 0
							if ($query_type=='and') {
								$count = sizeof(array_intersect($_products_in_term, $woocommerce->query->filtered_product_ids));
								if ($count>0) $found = true;
								if ($count==0 && !$option_is_set) continue;
							// If this is an OR query, show all options so search can be expanded
							} else {
								$count = sizeof(array_intersect($_products_in_term, $woocommerce->query->unfiltered_product_ids));
								if ($count>0) $found = true;
							}
							$class = '';
							$arg = 'filter_'.strtolower(sanitize_title($instance['attribute']));
							if (isset($_GET[ $arg ])) $current_filter = explode(',', $_GET[ $arg ]); else $current_filter = array();
							if (!is_array($current_filter)) $current_filter = array();
							if (!in_array($term->term_id, $current_filter)) $current_filter[] = $term->term_id;
							// Base Link decided by current page
							if (defined('SHOP_IS_ON_FRONT')) :
								$link = home_url();
							elseif (is_post_type_archive('product') || is_page( woocommerce_get_page_id('shop') )) :
								$link = get_post_type_archive_link('product');
							else :
								$link = get_term_link( get_query_var('term'), get_query_var('taxonomy') );
							endif;

							// All current filters
							if ($_chosen_attributes) foreach ($_chosen_attributes as $name => $data) :
								if ($name!==$taxonomy) :
									$link = add_query_arg( strtolower(sanitize_title(str_replace('pa_', 'filter_', $name))), implode(',', $data['terms']), $link );
									if ($data['query_type']=='or') $link = add_query_arg( strtolower(sanitize_title(str_replace('pa_', 'query_type_', $name))), 'or', $link );
								endif;
							endforeach;

							// Min/Max
							if (isset($_GET['min_price'])) :
								$link = add_query_arg( 'min_price', $_GET['min_price'], $link );
							endif;
							if (isset($_GET['max_price'])) :
								$link = add_query_arg( 'max_price', $_GET['max_price'], $link );
							endif;

							// Current Filter = this widget
							if (isset( $_chosen_attributes[$taxonomy] ) && is_array($_chosen_attributes[$taxonomy]['terms']) && in_array($term->term_id, $_chosen_attributes[$taxonomy]['terms'])) :
								$class = 'chosen filter-selected';
								// Remove this term is $current_filter has more than 1 term filtered
								if (sizeof($current_filter)>1) :
									$current_filter_without_this = array_diff($current_filter, array($term->term_id));
									$link = add_query_arg( $arg, implode(',', $current_filter_without_this), $link );
								endif;
							else :
								$link = add_query_arg( $arg, implode(',', $current_filter), $link );
							endif;
							// Search Arg
							if (get_search_query()) :
								$link = add_query_arg( 's', get_search_query(), $link );
							endif;
							// Post Type Arg
							if (isset($_GET['post_type'])) :
								$link = add_query_arg( 'post_type', $_GET['post_type'], $link );
							endif;

							// Query type Arg
							if ($query_type=='or' && !( sizeof($current_filter) == 1 && isset( $_chosen_attributes[$taxonomy]['terms'] ) && is_array($_chosen_attributes[$taxonomy]['terms']) && in_array($term->term_id, $_chosen_attributes[$taxonomy]['terms']) )) :
								$link = add_query_arg( 'query_type_'.strtolower(sanitize_title($instance['attribute'])), 'or', $link );
							endif;
							$checked = $class=="chosen filter-selected" ? 'checked="checked"':"";
							echo '<li class="'.$class.'">';
							echo '<input type="checkbox" data-filter"'.$link.'" '.$checked.'  data-link="'.$link.'" name="'.$term->name.'" value="'.$term->name.'" />';
							echo '<label for="'.$term->name.'">'.$term->name.'</label>';
							echo '</li>';
						}
						echo "</ul></div></nav>";
						break;
					/*Regular List of Terms*/
					case "list":
						echo '<nav>
						<div class="ajax-layered"><ul>';
						foreach ($terms as $term) {
							// Get count based on current view - uses transients
							$transient_name = 'woocommerce_layered_nav_count_' . sanitize_key($taxonomy) . sanitize_key( $term->term_id );
							delete_transient($transient_name);
							if ( false === ( $_products_in_term = get_transient( $transient_name ) ) ) {
								$_products_in_term = get_objects_in_term( $term->term_id, $taxonomy );
								set_transient( $transient_name, $_products_in_term );
							}
							$option_is_set = (isset($_chosen_attributes[$taxonomy]) && in_array($term->term_id, $_chosen_attributes[$taxonomy]['terms']));
							// If this is an AND query, only show options with count > 0
							if ($query_type=='and') {
								$count = sizeof(array_intersect($_products_in_term, $woocommerce->query->filtered_product_ids));
								if ($count>0) $found = true;
								if ($count==0 && !$option_is_set) continue;
							// If this is an OR query, show all options so search can be expanded
							} else {
								$count = sizeof(array_intersect($_products_in_term, $woocommerce->query->unfiltered_product_ids));
								if ($count>0) $found = true;
							}
							$class = '';
							$arg = 'filter_'.strtolower(sanitize_title($instance['attribute']));
							if (isset($_GET[ $arg ])) $current_filter = explode(',', $_GET[ $arg ]); else $current_filter = array();
							if (!is_array($current_filter)) $current_filter = array();
							if (!in_array($term->term_id, $current_filter)) $current_filter[] = $term->term_id;
							// Base Link decided by current page
							if (defined('SHOP_IS_ON_FRONT')) :
								$link = home_url();
							elseif (is_post_type_archive('product') || is_page( woocommerce_get_page_id('shop') )) :
								$link = get_post_type_archive_link('product');
							else :
								$link = get_term_link( get_query_var('term'), get_query_var('taxonomy') );
							endif;

							// All current filters
							if ($_chosen_attributes) foreach ($_chosen_attributes as $name => $data) :
								if ($name!==$taxonomy) :
									$link = add_query_arg( strtolower(sanitize_title(str_replace('pa_', 'filter_', $name))), implode(',', $data['terms']), $link );
									if ($data['query_type']=='or') $link = add_query_arg( strtolower(sanitize_title(str_replace('pa_', 'query_type_', $name))), 'or', $link );
								endif;
							endforeach;

							// Min/Max
							if (isset($_GET['min_price'])) :
								$link = add_query_arg( 'min_price', $_GET['min_price'], $link );
							endif;
							if (isset($_GET['max_price'])) :
								$link = add_query_arg( 'max_price', $_GET['max_price'], $link );
							endif;

							// Current Filter = this widget
							if (isset( $_chosen_attributes[$taxonomy] ) && is_array($_chosen_attributes[$taxonomy]['terms']) && in_array($term->term_id, $_chosen_attributes[$taxonomy]['terms'])) :
								$class = 'chosen filter-selected';
								// Remove this term is $current_filter has more than 1 term filtered
								if (sizeof($current_filter)>1) :
									$current_filter_without_this = array_diff($current_filter, array($term->term_id));
									$link = add_query_arg( $arg, implode(',', $current_filter_without_this), $link );
								endif;
							else :
								$link = add_query_arg( $arg, implode(',', $current_filter), $link );
							endif;
							// Search Arg
							if (get_search_query()) :
								$link = add_query_arg( 's', get_search_query(), $link );
							endif;
							// Post Type Arg
							if (isset($_GET['post_type'])) :
								$link = add_query_arg( 'post_type', $_GET['post_type'], $link );
							endif;

							// Query type Arg
							if ($query_type=='or' && !( sizeof($current_filter) == 1 && isset( $_chosen_attributes[$taxonomy]['terms'] ) && is_array($_chosen_attributes[$taxonomy]['terms']) && in_array($term->term_id, $_chosen_attributes[$taxonomy]['terms']) )) :
								$link = add_query_arg( 'query_type_'.strtolower(sanitize_title($instance['attribute'])), 'or', $link );
							endif;

							echo '<li class="'.$class.'">';
							//$link = "";<a href="'.$link.'" data-filter"'.$link.'">'; else echo '<span>
							if ($count>0 || $option_is_set) echo '<a href="#" data-filter"'.$link.'" data-link="'.$link.'" >'; else echo '<span>';

							echo $term->name;

							if ($count>0 || $option_is_set) echo '</a>'; else echo '</span>';

							echo ' <small class="count">'.$count.'</small></li>';
						}
						echo "</ul></div></nav>";
				break;
					/* Size Labels */
					case "sizeselector":
						echo '<nav>
						<div class="ajax-layered"><ul class="sizes">';
						foreach ($terms as $term) {
							// Get count based on current view - uses transients
							$transient_name = 'woocommerce_layered_nav_count_' . sanitize_key($taxonomy) . sanitize_key( $term->term_id );
							delete_transient($transient_name);
							if ( false === ( $_products_in_term = get_transient( $transient_name ) ) ) {
								$_products_in_term = get_objects_in_term( $term->term_id, $taxonomy );
								set_transient( $transient_name, $_products_in_term );
							}
							$option_is_set = (isset($_chosen_attributes[$taxonomy]) && in_array($term->term_id, $_chosen_attributes[$taxonomy]['terms']));
							// If this is an AND query, only show options with count > 0
							if ($query_type=='and') {
								$count = sizeof(array_intersect($_products_in_term, $woocommerce->query->filtered_product_ids));
								if ($count>0) $found = true;
								if ($count==0 && !$option_is_set) continue;
							// If this is an OR query, show all options so search can be expanded
							} else {
								$count = sizeof(array_intersect($_products_in_term, $woocommerce->query->unfiltered_product_ids));
								if ($count>0) $found = true;
							}
							$class = '';
							$arg = 'filter_'.strtolower(sanitize_title($instance['attribute']));
							if (isset($_GET[ $arg ])) $current_filter = explode(',', $_GET[ $arg ]); else $current_filter = array();
							if (!is_array($current_filter)) $current_filter = array();
							if (!in_array($term->term_id, $current_filter)) $current_filter[] = $term->term_id;
							// Base Link decided by current page
							if (defined('SHOP_IS_ON_FRONT')) :
								$link = home_url();
							elseif (is_post_type_archive('product') || is_page( woocommerce_get_page_id('shop') )) :
								$link = get_post_type_archive_link('product');
							else :
								$link = get_term_link( get_query_var('term'), get_query_var('taxonomy') );
							endif;

							// All current filters
							if ($_chosen_attributes) foreach ($_chosen_attributes as $name => $data) :
								if ($name!==$taxonomy) :
									$link = add_query_arg( strtolower(sanitize_title(str_replace('pa_', 'filter_', $name))), implode(',', $data['terms']), $link );
									if ($data['query_type']=='or') $link = add_query_arg( strtolower(sanitize_title(str_replace('pa_', 'query_type_', $name))), 'or', $link );
								endif;
							endforeach;

							// Min/Max
							if (isset($_GET['min_price'])) :
								$link = add_query_arg( 'min_price', $_GET['min_price'], $link );
							endif;
							if (isset($_GET['max_price'])) :
								$link = add_query_arg( 'max_price', $_GET['max_price'], $link );
							endif;

							// Current Filter = this widget
							if (isset( $_chosen_attributes[$taxonomy] ) && is_array($_chosen_attributes[$taxonomy]['terms']) && in_array($term->term_id, $_chosen_attributes[$taxonomy]['terms'])) :
								$class = 'chosen filter-selected';
								// Remove this term is $current_filter has more than 1 term filtered
								if (sizeof($current_filter)>1) :
									$current_filter_without_this = array_diff($current_filter, array($term->term_id));
									$link = add_query_arg( $arg, implode(',', $current_filter_without_this), $link );
								endif;
							else :
								$link = add_query_arg( $arg, implode(',', $current_filter), $link );
							endif;
							// Search Arg
							if (get_search_query()) :
								$link = add_query_arg( 's', get_search_query(), $link );
							endif;
							// Post Type Arg
							if (isset($_GET['post_type'])) :
								$link = add_query_arg( 'post_type', $_GET['post_type'], $link );
							endif;

							// Query type Arg
							if ($query_type=='or' && !( sizeof($current_filter) == 1 && isset( $_chosen_attributes[$taxonomy]['terms'] ) && is_array($_chosen_attributes[$taxonomy]['terms']) && in_array($term->term_id, $_chosen_attributes[$taxonomy]['terms']) )) :
								$link = add_query_arg( 'query_type_'.strtolower(sanitize_title($instance['attribute'])), 'or', $link );
							endif;

							echo '<li class="'.$class.'">';
							//$link = '<a href="'.$link.'" data-filter"'.$link.'">';
							if ($count>0 || $option_is_set) echo '<a href="#" data-filter"'.$link.'" data-link="'.$link.'" >'; else echo '<span>';

							echo '<div class="size-filter">'.$labels[$term->term_id].'</div>';

							if ($count>0 || $option_is_set) echo '</a>'; else echo '</span>';

							echo '</li>';
						}
						echo "</ul></div></nav>";
				break;
					/* Color Boxes*/
					case "colorpicker":
						echo '<nav>
						<div class="ajax-layered"><ul class="colors">';
						foreach ($terms as $term) {
							// Get count based on current view - uses transients
							$transient_name = 'woocommerce_layered_nav_count_' . sanitize_key($taxonomy) . sanitize_key( $term->term_id );
							delete_transient($transient_name);
							if ( false === ( $_products_in_term = get_transient( $transient_name ) ) ) {
								$_products_in_term = get_objects_in_term( $term->term_id, $taxonomy );
								set_transient( $transient_name, $_products_in_term );
							}
							$option_is_set = (isset($_chosen_attributes[$taxonomy]) && in_array($term->term_id, $_chosen_attributes[$taxonomy]['terms']));
							// If this is an AND query, only show options with count > 0
							if ($query_type=='and') {
								$count = sizeof(array_intersect($_products_in_term, $woocommerce->query->filtered_product_ids));
								if ($count>0) $found = true;
								if ($count==0 && !$option_is_set) continue;
							// If this is an OR query, show all options so search can be expanded
							} else {
								$count = sizeof(array_intersect($_products_in_term, $woocommerce->query->unfiltered_product_ids));
								if ($count>0) $found = true;
							}
							$class = '';
							$arg = 'filter_'.strtolower(sanitize_title($instance['attribute']));
							if (isset($_GET[ $arg ])) $current_filter = explode(',', $_GET[ $arg ]); else $current_filter = array();
							if (!is_array($current_filter)) $current_filter = array();
							if (!in_array($term->term_id, $current_filter)) $current_filter[] = $term->term_id;
							// Base Link decided by current page
							if (defined('SHOP_IS_ON_FRONT')) :
								$link = home_url();
							elseif (is_post_type_archive('product') || is_page( woocommerce_get_page_id('shop') )) :
								$link = get_post_type_archive_link('product');
							else :
								$link = get_term_link( get_query_var('term'), get_query_var('taxonomy') );
							endif;

							// All current filters
							if ($_chosen_attributes) foreach ($_chosen_attributes as $name => $data) :
								if ($name!==$taxonomy) :
									$link = add_query_arg( strtolower(sanitize_title(str_replace('pa_', 'filter_', $name))), implode(',', $data['terms']), $link );
									if ($data['query_type']=='or') $link = add_query_arg( strtolower(sanitize_title(str_replace('pa_', 'query_type_', $name))), 'or', $link );
								endif;
							endforeach;

							// Min/Max
							if (isset($_GET['min_price'])) :
								$link = add_query_arg( 'min_price', $_GET['min_price'], $link );
							endif;
							if (isset($_GET['max_price'])) :
								$link = add_query_arg( 'max_price', $_GET['max_price'], $link );
							endif;

							// Current Filter = this widget
							if (isset( $_chosen_attributes[$taxonomy] ) && is_array($_chosen_attributes[$taxonomy]['terms']) && in_array($term->term_id, $_chosen_attributes[$taxonomy]['terms'])) :
								$class = 'chosen filter-selected';
								// Remove this term is $current_filter has more than 1 term filtered
								if (sizeof($current_filter)>1) :
									$current_filter_without_this = array_diff($current_filter, array($term->term_id));
									$link = add_query_arg( $arg, implode(',', $current_filter_without_this), $link );
								endif;
							else :
								$link = add_query_arg( $arg, implode(',', $current_filter), $link );
							endif;
							// Search Arg
							if (get_search_query()) :
								$link = add_query_arg( 's', get_search_query(), $link );
							endif;
							// Post Type Arg
							if (isset($_GET['post_type'])) :
								$link = add_query_arg( 'post_type', $_GET['post_type'], $link );
							endif;

							// Query type Arg
							if ($query_type=='or' && !( sizeof($current_filter) == 1 && isset( $_chosen_attributes[$taxonomy]['terms'] ) && is_array($_chosen_attributes[$taxonomy]['terms']) && in_array($term->term_id, $_chosen_attributes[$taxonomy]['terms']) )) :
								$link = add_query_arg( 'query_type_'.strtolower(sanitize_title($instance['attribute'])), 'or', $link );
							endif;

							echo '<li class="'.$class.'">';
							//$link = "";<a href="'.$link.'" data-filter"'.$link.'">'; else echo '<span>
							if ($count>0 || $option_is_set) echo '<a href="#" data-filter"'.$link.'" data-link="'.$link.'" >'; else echo '<span>';

							echo '<div class="box" style="background:'.$colors[$term->term_id].';"></div>';

							if ($count>0 || $option_is_set) echo '</a>'; else echo '</span>';

							echo '</li>';
						}
						echo "</ul></div></nav>";
				break;
			}

			echo '<div class="clear"></div>';

			//} // End display type conditional
			/* After Widget closing container output*/
			echo $after_widget;

			if (!$found) :
				ob_clean();
				return;
			else :
				$widget = ob_get_clean();
				echo $widget;
			endif;

		}
	}

	/** @see WP_Widget->update */
	function update( $new_instance, $old_instance ) {
		global $woocommerce;
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['attribute'] = stripslashes($new_instance['attribute']);
		$instance['query_type'] = stripslashes($new_instance['query_type']);
		$instance['display_type'] = stripslashes($new_instance['display_type']);
		$instance['labels'] =  stripslashes(serialize($new_instance['labels']));
		$instance['colors'] =  stripslashes(serialize($new_instance['colors']));
		return $instance;
	}

	/** @see WP_Widget->form */
	function form( $instance ) {
		global $woocommerce;
		if (!isset($instance['query_type'])) $instance['query_type'] = 'and';
		if (!isset($instance['display_type'])) $instance['display_type'] = 'list';
		?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'sod_ajax_layered_nav') ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php if (isset ( $instance['title'])) {echo esc_attr( $instance['title'] );} ?>" /></p>
			<?php /* Build drop-down of available attributes */ ?>
			<p><label for="<?php echo $this->get_field_id('attribute'); ?>"><?php _e('Attribute:', 'sod_ajax_layered_nav') ?></label>
			<select class="layered_nav_attributes" id="<?php echo esc_attr( $this->get_field_id('attribute') ); ?>" name="<?php echo esc_attr( $this->get_field_name('attribute') ); ?>">
				<?php
				$attribute_taxonomies = $woocommerce->get_attribute_taxonomies();
				if ( $attribute_taxonomies ) :
					foreach ($attribute_taxonomies as $tax) :
						if (taxonomy_exists( $woocommerce->attribute_taxonomy_name($tax->attribute_name))) :

							echo '<option value="'.$tax->attribute_name.'" ';
							if (isset($instance['attribute']) && $instance['attribute']==$tax->attribute_name) :
								echo 'selected="selected"';
							endif;
							echo '>'.$tax->attribute_name.'</option>';

						endif;
					endforeach;
				endif;
				?>
			</select></p>
			<?php /* Build drop-down of available display types */ ?>
			<p><label for="<?php echo $this->get_field_id('display_type'); ?>"><?php _e('Display Type:', 'sod_ajax_layered_nav') ?></label>
			<select class="layered_nav_type" id="<?php echo esc_attr( $this->get_field_id('display_type') ); ?>" name="<?php echo esc_attr( $this->get_field_name('display_type') ); ?>">
				<option value="list" <?php selected($instance['display_type'], 'list'); ?>><?php _e('List', 'sod_ajax_layered_nav'); ?></option>
				<option value="colorpicker" <?php selected($instance['display_type'], 'colorpicker'); ?>><?php _e('Color Selector', 'sod_ajax_layered_nav'); ?></option>
				<option value="sizeselector" <?php selected($instance['display_type'], 'sizeselector'); ?>><?php _e('Size / Amount Selector', 'sod_ajax_layered_nav'); ?></option>
				<option value="checkbox" <?php selected($instance['display_type'], 'checkbox'); ?>><?php _e('Checkbox', 'sod_ajax_layered_nav'); ?></option>
			</select></p>

			<?php if(isset($instance['display_type'])):
				$args=array(
    				'hide_empty'=>'0'
				);
				$labels = isset($instance['labels'])?unserialize($instance['labels']):false;
				$colors = isset($instance['colors'])?unserialize($instance['colors']):false;

				$attributes = isset($instance['attribute'])? get_terms('pa_'.$instance['attribute'],$args):NULL;
				$html = null;
				?>
			<p>
				<div id="<?php echo esc_attr( $this->get_field_id('labels') ); ?>">
				<?php
					/*Build special options configurations - colorselctor, sizeselector are the only ones that have additional configuration*/
					switch ($instance['display_type']) {
					case 'list':
					case 'slider':
					case 'checkbox':
						break;
					/* Color Picker Table if options already set*/
					case 'colorpicker':
						$html .= '<table class="color">
							<thead>
								<tr>
									<td>'.__('Name', 'sod_ajax_layered_nav').'</td>
									<td>'.__('Color Code', 'sod_ajax_layered_nav').'</td>
								</tr>
							</thead>
							<tbody>';
							if($attributes):
								foreach($attributes as $attribute){
									$value = isset($colors) ? esc_attr($colors[$attribute->term_id]):"";
									$html.='<tr>
												<td class="labels"><label for="'.$this->get_field_name('colors').'['.$instance['attribute'].']">'.$attribute->name.'</label></td>
												<td class="inputs"><input class="color_input" type="text" name="'.$this->get_field_name('colors').'['.$attribute->term_id.']" id="'.$this->get_field_id('colors').'['.$attribute->term_id.']" value="'.$value.'" size="3" maxlength="3"/>
												<div class="colorSelector"><div style="background-color:'.$value.'"></div></div></td>
											</tr>';
								}
							endif;
							$html .= '</tbody>
							</table>';
						break;
					/* Sizes Table if options already set*/
					case 'sizeselector':
						$html .= '<table class="sizes">
							<thead>
								<tr>
									<td>'.__('Name', 'sod_ajax_layered_nav').'</td>
									<td>'.__('Label', 'sod_ajax_layered_nav').'</td>
								</tr>
							</thead>
							<tbody>';
							if($attributes):
							foreach($attributes as $attribute){
								$value = isset($labels) ? esc_attr($labels[$attribute->term_id]):"";
								$html.='<tr>
											<td class="labels"><label for="'.$this->get_field_name('labels').'['.$instance['attribute'].']">'.$attribute->name.'</label></td>
											<td class="inputs"><input type="text" name="'.$this->get_field_name('labels').'['.$attribute->term_id.']" id="'.$this->get_field_id('labels').'['.$attribute->term_id.']" value="'.$value.'" size="3"/></td>
										</tr>';
							}
							endif;
							$html .= '</tbody>
							</table>';
						break;

					default:
						break;

				}
				echo $html;
				?>
				</div>
			</p>
			<?php endif;?>
			<p><label for="<?php echo $this->get_field_id('query_type'); ?>"><?php _e('Query Type:', 'sod_ajax_layered_nav') ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id('query_type') ); ?>" name="<?php echo esc_attr( $this->get_field_name('query_type') ); ?>">
				<option value="and" <?php selected($instance['query_type'], 'and'); ?>><?php _e('AND', 'sod_ajax_layered_nav'); ?></option>
				<option value="or" <?php selected($instance['query_type'], 'or'); ?>><?php _e('OR', 'sod_ajax_layered_nav'); ?></option>
			</select></p>
		<?php
	}
} // class SOD_Widget_Ajax_Layered_Nav