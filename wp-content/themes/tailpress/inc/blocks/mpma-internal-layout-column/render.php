<?php
/**
 * MPMA Internal Layout Column block render template.
 *
 * @param array  $attributes Block attributes.
 * @param string $content    Inner block content.
 */

$width_columns = isset( $attributes['widthColumns'] ) ? (int) $attributes['widthColumns'] : 6;
$width_columns = max( 1, min( 12, $width_columns ) );
$vertical_alignment = sanitize_key( $attributes['verticalAlignment'] ?? 'top' );
$horizontal_alignment = sanitize_key( $attributes['horizontalAlignment'] ?? 'left' );

if ( ! in_array( $vertical_alignment, array( 'top', 'center', 'bottom' ), true ) ) {
	$vertical_alignment = 'top';
}

if ( ! in_array( $horizontal_alignment, array( 'left', 'center', 'right' ), true ) ) {
	$horizontal_alignment = 'left';
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'mpma-internal-layout__column mpma-internal-layout__column--align-' . $vertical_alignment . ' mpma-internal-layout__column--horizontal-' . $horizontal_alignment,
		'style' => '--mpma-internal-layout-column-span:' . $width_columns . ';',
	)
);
?>

<div <?php echo $wrapper_attributes; ?>>
	<div class="mpma-internal-layout__column-inner">
		<?php echo $content; ?>
	</div>
</div>
