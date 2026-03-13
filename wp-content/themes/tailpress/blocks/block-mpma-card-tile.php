<?php
/**
 * Genesis Custom Blocks template: MPMA Card Tile.
 */

$tile_area  = (string) block_value( 'tile-area' );
$gap_field  = block_value( 'gap' );
$columns_field = block_value( 'columns' );
$rows_field = block_value( 'rows' );
$width_field = trim( (string) block_value( 'width' ) );
$height_field = trim( (string) block_value( 'height' ) );
$mobile_width_field = trim( (string) block_value( 'mobile-width' ) );
$mobile_height_field = trim( (string) block_value( 'mobile-height' ) );
$class_name = trim( (string) block_value( 'className' ) );

$gap_scale = is_numeric( $gap_field ) ? (float) $gap_field : 4.0;
$gap_scale = max( 0.0, $gap_scale );
$gap_rem   = $gap_scale * 0.25;

$gap_string = rtrim( rtrim( number_format( $gap_rem, 4, '.', '' ), '0' ), '.' );
if ( '' === $gap_string ) {
	$gap_string = '0';
}

$sanitize_length = static function ( string $value, string $default ): string {
	$value = sanitize_text_field( $value );

	if ( '' === $value ) {
		return $default;
	}

	if ( 'auto' === strtolower( $value ) ) {
		return 'auto';
	}

	if ( preg_match( '/^\d+(\.\d+)?$/', $value ) ) {
		return $value . 'px';
	}

	if ( preg_match( '/^\d+(\.\d+)?(px|rem|em|vh|vw|%)$/', $value ) ) {
		return $value;
	}

	return $default;
};

$tile_container_width  = $sanitize_length( $width_field, '520px' );
$tile_container_height = $sanitize_length( $height_field, '415px' );
$tile_mobile_width     = $sanitize_length( $mobile_width_field, '100%' );
$tile_mobile_height    = $sanitize_length( $mobile_height_field, 'auto' );

$sanitize_positive_int = static function ( $value, int $default ): int {
	if ( '' === (string) $value || ! is_numeric( $value ) ) {
		return $default;
	}

	$normalized = (int) $value;
	return $normalized > 0 ? $normalized : $default;
};

$tile_columns = $sanitize_positive_int( $columns_field, 2 );
$tile_rows    = $sanitize_positive_int( $rows_field, 2 );

$tile_area_output = '';
if ( '' !== trim( $tile_area ) ) {
	$rendered = function_exists( 'do_blocks' ) ? do_blocks( $tile_area ) : '';
	$tile_area_output = '' !== trim( $rendered ) ? $rendered : $tile_area;
}

$wrapper_classes = 'mpma-card-tile';
if ( '' !== $class_name ) {
	$wrapper_classes .= ' ' . $class_name;
}
?>

<div
	class="<?php echo esc_attr( $wrapper_classes ); ?>"
	style="--mpma-card-tile-gap: <?php echo esc_attr( $gap_string . 'rem' ); ?>; --mpma-card-tile-columns: <?php echo esc_attr( (string) $tile_columns ); ?>; --mpma-card-tile-rows: <?php echo esc_attr( (string) $tile_rows ); ?>; --mpma-card-tile-width: <?php echo esc_attr( $tile_container_width ); ?>; --mpma-card-tile-height: <?php echo esc_attr( $tile_container_height ); ?>; --mpma-card-tile-width-mobile: <?php echo esc_attr( $tile_mobile_width ); ?>; --mpma-card-tile-height-mobile: <?php echo esc_attr( $tile_mobile_height ); ?>;"
>
	<div class="mpma-card-tile__grid">
		<?php echo wp_kses_post( $tile_area_output ); ?>
	</div>
</div>
