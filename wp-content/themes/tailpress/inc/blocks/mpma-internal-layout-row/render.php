<?php
/**
 * MPMA Internal Layout Row block render template.
 *
 * @param array  $attributes Block attributes.
 * @param string $content    Inner block content.
 */

$class_name = isset( $attributes['className'] ) ? (string) $attributes['className'] : '';
$is_overlap_row = false !== strpos( $class_name, 'mpma-overlap-layout-row' );
$styles = array();

if ( $is_overlap_row ) {
	if ( preg_match( '/--mpma-internal-layout-column-span:\s*(\d+)/', $content, $matches ) ) {
		$first_span = max( 1, (int) $matches[1] );
		$styles[] = '--mpma-overlap-columns:2';
		$styles[] = '--mpma-overlap-first-span:' . $first_span;
		$styles[] = '--mpma-overlap-second-start:' . max( 1, $first_span - 1 );
	}
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'mpma-internal-layout__row',
		'style' => implode( ';', $styles ),
	)
);
?>

<div <?php echo $wrapper_attributes; ?>>
	<?php echo $content; ?>
</div>
