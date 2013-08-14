<?php
/**
 * Layered Navigation Widget for brands
 *
 * @author 		WooThemes
 * @extends 	WP_Widget
 */
class WC_Widget_Brand_Nav extends WP_Widget {

	var $woo_widget_cssclass;
	var $woo_widget_description;
	var $woo_widget_idbase;
	var $woo_widget_name;

	/**
	 * constructor
	 *
	 * @access public
	 * @return void
	 */
	function WC_Widget_Brand_Nav() {

		/* Widget variable settings. */
		$this->woo_widget_cssclass 		= 'widget_brand_nav';
		$this->woo_widget_description 	= __( 'Shows brands in a widget which lets you narrow down the list of products when viewing products.', 'wc_brands' );
		$this->woo_widget_idbase 		= 'woocommerce_brand_nav';
		$this->woo_widget_name 			= __('WooCommerce Brand Layered Nav', 'wc_brands' );

		/* Widget settings. */
		$widget_ops = array( 'classname' => $this->woo_widget_cssclass, 'description' => $this->woo_widget_description );

		/* Create the widget. */
		$this->WP_Widget( 'woocommerce_brand_nav', $this->woo_widget_name, $widget_ops);
	}

	/**
	 * widget function.
	 *
	 * @see WP_Widget
	 * @access public
	 * @param array $args
	 * @param array $instance
	 * @return void
	 */
	function widget( $args, $instance ) {
		global $_chosen_attributes, $woocommerce, $_attributes_array;

		extract( $args );

		if ( ! is_post_type_archive( 'product' ) && ! is_tax( array_merge( is_array( $_attributes_array ) ? $_attributes_array : array(), array( 'product_cat', 'product_tag' ) ) ) )
			return;

		$current_term 	= $_attributes_array && is_tax( $_attributes_array ) ? get_queried_object()->term_id : '';
		$current_tax 	= $_attributes_array && is_tax( $_attributes_array ) ? get_queried_object()->taxonomy : '';

		$title 			= apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
		$taxonomy 		= 'product_brand';
		$display_type 	= isset( $instance['display_type'] ) ? $instance['display_type'] : 'list';

		if ( ! taxonomy_exists( $taxonomy ) )
			return;

		$terms = get_terms( $taxonomy, array( 'hide_empty' => '1' ) );

		if ( count( $terms ) > 0 ) {

			ob_start();

			$found = false;

			echo $before_widget . $before_title . $title . $after_title;

			// Force found when option is selected - do not force found on taxonomy attributes
			if ( ! $_attributes_array || ! is_tax( $_attributes_array ) )
				if ( is_array( $_chosen_attributes ) && array_key_exists( $taxonomy, $_chosen_attributes ) )
					$found = true;

			if ( $display_type == 'dropdown' ) {

				// skip when viewing the taxonomy
				if ( $current_tax && $taxonomy == $current_tax ) {

					$found = false;

				} else {

					$taxonomy_filter = $taxonomy;

					$found = true;

					echo '<select id="dropdown_layered_nav_' . $taxonomy_filter . '">';

					echo '<option value="">' . __( 'Any brand', 'wc_brands' ) .'</option>';

					foreach ( $terms as $term ) {

						// If on a term page, skip that term in widget list
						if ( $term->term_id == $current_term )
							continue;

						// Get count based on current view - uses transients
						$transient_name = 'wc_ln_count_' . md5( sanitize_key( $taxonomy ) . sanitize_key( $term->term_id ) );

						if ( false === ( $_products_in_term = get_transient( $transient_name ) ) ) {

							$_products_in_term = get_objects_in_term( $term->term_id, $taxonomy );

							set_transient( $transient_name, $_products_in_term );
						}

						$option_is_set = ( isset( $_chosen_attributes[ $taxonomy ] ) && in_array( $term->term_id, $_chosen_attributes[ $taxonomy ]['terms'] ) );

						// only show options with count > 0
						$count = sizeof( array_intersect( $_products_in_term, $woocommerce->query->filtered_product_ids ) );

						if ( $count > 0 )
							$found = true;

						//if ( $count == 0 && ! $option_is_set )
						//	continue;

						echo '<option value="' . $term->term_id . '" '.selected( isset( $_GET[ 'filter_product_brand' ] ) ? $_GET[ 'filter_product_brand' ] : '' , $term->term_id, false ) . '>' . $term->name . '</option>';
					}

					echo '</select>';

					$woocommerce->add_inline_js("

						jQuery('#dropdown_layered_nav_$taxonomy_filter').change(function(){

							location.href = '" . add_query_arg('filtering', '1', remove_query_arg( 'filter_product_brand' ) ) . "&filter_product_brand=' + jQuery('#dropdown_layered_nav_$taxonomy_filter').val();

						});

					");

				}

			} else {

				// List display
				echo "<ul>";

				foreach ( $terms as $term ) {

					// Get count based on current view - uses transients
					$transient_name = 'wc_ln_count_' . md5( sanitize_key( $taxonomy ) . sanitize_key( $term->term_id ) );

					if ( false === ( $_products_in_term = get_transient( $transient_name ) ) ) {

						$_products_in_term = get_objects_in_term( $term->term_id, $taxonomy );

						set_transient( $transient_name, $_products_in_term );
					}

					$option_is_set = ( isset( $_chosen_attributes[ $taxonomy ] ) && in_array( $term->term_id, $_chosen_attributes[ $taxonomy ]['terms'] ) );

					// If this is an AND query, only show options with count > 0
					$count = sizeof( array_intersect( $_products_in_term, $woocommerce->query->filtered_product_ids ) );

					// skip the term for the current archive
					if ( $current_term == $term->term_id )
						continue;

					if ( $count > 0 && $current_term !== $term->term_id )
						$found = true;

					if ( $count == 0 && ! $option_is_set )
						continue;

					$current_filter = ( isset( $_GET[ 'filter_product_brand' ] ) ) ? explode( ',', $_GET[ 'filter_product_brand' ] ) : array();

					if ( ! is_array( $current_filter ) )
						$current_filter = array();

					if ( ! in_array( $term->term_id, $current_filter ) )
						$current_filter[] = $term->term_id;

					// Base Link decided by current page
					if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
						$link = home_url();
					} elseif ( is_post_type_archive( 'product' ) || is_page( woocommerce_get_page_id('shop') ) ) {
						$link = get_post_type_archive_link( 'product' );
					} else {
						$link = get_term_link( get_query_var('term'), get_query_var('taxonomy') );
					}

					// All current filters
					if ( $_chosen_attributes ) {
						foreach ( $_chosen_attributes as $name => $data ) {
							if ( $name !== 'product_brand' ) {

								//exclude query arg for current term archive term
								while ( in_array( $current_term, $data['terms'] ) ) {
									$key = array_search( $current_term, $data );
									unset( $data['terms'][$key] );
								}

								if ( ! empty( $data['terms'] ) )
									$link = add_query_arg( sanitize_title( str_replace( 'pa_', 'filter_', $name ) ), implode(',', $data['terms']), $link );
							}
						}
					}

					// Min/Max
					if ( isset( $_GET['min_price'] ) )
						$link = add_query_arg( 'min_price', $_GET['min_price'], $link );

					if ( isset( $_GET['max_price'] ) )
						$link = add_query_arg( 'max_price', $_GET['max_price'], $link );

					// Current Filter = this widget
					if ( isset( $_chosen_attributes['product_brand'] ) && is_array( $_chosen_attributes['product_brand']['terms'] ) && in_array( $term->term_id, $_chosen_attributes['product_brand']['terms'] ) ) {

						$class = 'class="chosen"';

						// Remove this term is $current_filter has more than 1 term filtered
						if ( sizeof( $current_filter ) > 1 ) {
							$current_filter_without_this = array_diff( $current_filter, array( $term->term_id ) );
							$link = add_query_arg( 'filter_product_brand', implode( ',', $current_filter_without_this ), $link );
						}

					} else {
						$class = '';
						$link = add_query_arg( 'filter_product_brand', implode( ',', $current_filter ), $link );
					}

					// Search Arg
					if ( get_search_query() )
						$link = add_query_arg( 's', get_search_query(), $link );

					// Post Type Arg
					if ( isset( $_GET['post_type'] ) )
						$link = add_query_arg( 'post_type', $_GET['post_type'], $link );

					echo '<li ' . $class . '>';

					echo ( $count > 0 || $option_is_set ) ? '<a href="' . $link . '">' : '<span>';

					echo $term->name;

					echo ( $count > 0 || $option_is_set ) ? '</a>' : '</span>';

					echo ' <small class="count">' . $count . '</small></li>';
				}

				echo "</ul>";

			} // End display type conditional

			echo $after_widget;

			if ( ! $found )
				ob_clean();
			else
				echo ob_get_clean();
		}
	}

	/**
	 * update function.
	 *
	 * @see WP_Widget->update
	 * @access public
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		global $woocommerce;

		if ( empty( $new_instance['title'] ) )
			$new_instance['title'] = __( 'Brands', 'wc_brands' );

		$instance['title'] 			= strip_tags( stripslashes( $new_instance['title'] ) );
		$instance['display_type'] 	= stripslashes( $new_instance['display_type'] );

		return $instance;
	}

	/**
	 * form function.
	 *
	 * @see WP_Widget->form
	 * @access public
	 * @param array $instance
	 * @return void
	 */
	function form( $instance ) {
		global $woocommerce;

		if ( ! isset( $instance['display_type'] ) )
			$instance['display_type'] = 'list';
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wc_brands' ) ?></label>
		<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php if ( isset( $instance['title'] ) ) echo esc_attr( $instance['title'] ); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'display_type' ); ?>"><?php _e( 'Display Type:', 'wc_brands' ) ?></label>
		<select id="<?php echo esc_attr( $this->get_field_id( 'display_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_type' ) ); ?>">
			<option value="list" <?php selected( $instance['display_type'], 'list' ); ?>><?php _e( 'List', 'wc_brands' ); ?></option>
			<option value="dropdown" <?php selected( $instance['display_type'], 'dropdown' ); ?>><?php _e( 'Dropdown', 'wc_brands' ); ?></option>
		</select></p>
		<?php
	}
}

/**
 * Layered Nav Init
 *
 * @package 	WooCommerce/Widgets
 * @access public
 * @return void
 */
function woocommerce_brands_layered_nav_init( $filtered_posts ) {
	global $woocommerce, $_chosen_attributes;

	if ( is_active_widget( false, false, 'woocommerce_brand_nav', true ) && ! is_admin() ) {

		if ( ! empty( $_GET[ 'filter_product_brand' ] ) ) {

			$terms 	= array_map( 'intval', explode( ',', $_GET[ 'filter_product_brand' ] ) );

			if ( sizeof( $terms ) > 0 ) {

				$_chosen_attributes['product_brand']['terms'] = $terms;
				$_chosen_attributes['product_brand']['query_type'] = 'and';

				$matched_products = get_posts(
					array(
						'post_type' 	=> 'product',
						'numberposts' 	=> -1,
						'post_status' 	=> 'publish',
						'fields' 		=> 'ids',
						'no_found_rows' => true,
						'tax_query' => array(
							'relation' => 'AND',
							array(
								'taxonomy' 	=> 'product_brand',
								'terms' 	=> $terms,
								'field' 	=> 'id'
							)
						)
					)
				);

				$woocommerce->query->layered_nav_post__in = array_merge( $woocommerce->query->layered_nav_post__in, $matched_products );
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

    }

    return (array) $filtered_posts;
}

add_action( 'loop_shop_post_in', 'woocommerce_brands_layered_nav_init', 11 );