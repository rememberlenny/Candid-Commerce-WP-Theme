<?php

$loop = 0;
$current_value = isset( $_POST['addon-' . sanitize_title( $addon['name'] ) ] ) ? $_POST[ 'addon-' . sanitize_title( $addon['name'] ) ] : '';
?>
<p class="form-row form-row-wide">
	<select class="addon addon-select" name="addon-<?php echo sanitize_title( $addon['name'] ); ?>">

		<?php if ( ! isset( $addon['required'] ) ) : ?>
			<option value=""><?php _e('None', 'wc_product_addons'); ?></option>
		<?php else : ?>
			<option value=""><?php _e('Select an option...', 'wc_product_addons'); ?></option>
		<?php endif; ?>

		<?php foreach ( $addon['options'] as $option ) :
			$loop ++;
			$price = $option['price'] > 0 ? ' (' . woocommerce_price( $option['price'] ) . ')' : '';
			?>
			<option data-price="<?php echo $option['price']; ?>" value="<?php echo sanitize_title( $option['label'] ) . '-' . $loop; ?>" <?php selected( $current_value, sanitize_title( $option['label'] ) . '-' . $loop ); ?>><?php echo wptexturize( $option['label'] ) . $price ?></option>
		<?php endforeach; ?>

	</select>
</p>