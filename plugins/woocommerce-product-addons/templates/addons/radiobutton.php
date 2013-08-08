<?php $first = true; foreach ( $addon['options'] as $option ) :

	$price = $option['price'] > 0 ? '(' . woocommerce_price( $option['price'] ) . ')' : '';

	if ( isset( $_POST[ 'addon-' . sanitize_title( $addon['name'] ) ] ) ) {
		$current_value = (
				isset( $_POST[ 'addon-' . sanitize_title( $addon['name'] ) ] ) &&
				in_array( sanitize_title( $option['label'] ), $_POST[ 'addon-' . sanitize_title( $addon['name'] ) ] )
				) ? 1 : 0;
	} else {
		$current_value = $first ? 1 : 0;
		$first         = false;
	}
	?>

	<p class="form-row form-row-wide">
		<label><input type="radio" class="addon addon-radio" name="addon-<?php echo sanitize_title( $addon['name'] ); ?>[]" data-price="<?php echo $option['price']; ?>" value="<?php echo sanitize_title( $option['label'] ); ?>" <?php checked( $current_value, 1 ); ?> /> <?php echo wptexturize( $option['label'] . ' ' . $price ); ?></label>
	</p>

<?php endforeach; ?>