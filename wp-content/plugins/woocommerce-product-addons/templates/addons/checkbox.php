<?php foreach ( $addon['options'] as $option ) :

	$price = $option['price'] > 0 ? '(' . woocommerce_price( $option['price'] ) . ')' : '';

	$current_value = (
			isset( $_POST[ 'addon-' . sanitize_title( $addon['name'] ) ] ) &&
			in_array( sanitize_title( $option['label'] ), $_POST[ 'addon-' . sanitize_title( $addon['name'] ) ] )
			) ? 1 : 0;
	?>

	<p class="form-row form-row-wide">
		<label><input type="checkbox" class="addon addon-checkbox" name="addon-<?php echo sanitize_title( $addon['name'] ); ?>[]" data-price="<?php echo $option['price']; ?>" value="<?php echo sanitize_title( $option['label'] ); ?>" <?php checked( $current_value, 1 ); ?> /> <?php echo wptexturize( $option['label'] . ' ' . $price ); ?></label>
	</p>

<?php endforeach; ?>