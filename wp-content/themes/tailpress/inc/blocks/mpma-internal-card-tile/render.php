<?php
/**
 * MPMA Internal Card Tile block render template.
 *
 * @param array  $attributes Block attributes.
 * @param string $content    Inner block content.
 */

if ( ! function_exists( 'tailpress_mpma_internal_card_tile_safe_gap' ) ) {
	function tailpress_mpma_internal_card_tile_safe_gap( $value, $fallback ) {
		$value = trim( (string) $value );

		if ( '' === $value ) {
			return $fallback;
		}

		if ( preg_match( '/^\d+(\.\d+)?(px|rem)$/', $value ) ) {
			return $value;
		}

		return $fallback;
	}
}

$gap = tailpress_mpma_internal_card_tile_safe_gap( $attributes['gap'] ?? '1.5rem', '1.5rem' );
$carousel_enabled = ! empty( $attributes['carouselEnabled'] );
$nav_color = sanitize_hex_color( (string) ( $attributes['navColor'] ?? '#ffffff' ) ) ?: '#ffffff';
$rows = $carousel_enabled ? 1 : max( 1, (int) ( $attributes['rows'] ?? 1 ) );
$columns = max( $carousel_enabled ? 2 : 1, (int) ( $attributes['columns'] ?? 2 ) );
$viewport_cards = max( 1, min( $columns, (int) ( $attributes['viewportCards'] ?? 1 ) ) );
$carousel_width_columns = isset( $attributes['carouselWidthColumns'] ) ? (int) $attributes['carouselWidthColumns'] : 0;
$first_card_columns = 4;

if ( isset( $block ) && is_object( $block ) && isset( $block->parsed_block['innerBlocks'] ) && is_array( $block->parsed_block['innerBlocks'] ) ) {
	foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {
		if ( ! is_array( $inner_block ) || ( $inner_block['blockName'] ?? '' ) !== 'tailpress/mpma-internal-card' ) {
			continue;
		}

		$first_card_columns = isset( $inner_block['attrs']['widthColumns'] ) ? (int) $inner_block['attrs']['widthColumns'] : 4;
		break;
	}
}

$first_card_columns = max( 1, min( 12, $first_card_columns ) );
$carousel_width_columns = max(
	1,
	min(
		12,
		$carousel_width_columns > 0 ? $carousel_width_columns : ( $first_card_columns * $viewport_cards )
	)
);
$loop_enabled = array_key_exists( 'loopEnabled', $attributes ) ? (bool) $attributes['loopEnabled'] : true;

$wrapper_classes = array(
	'mpma-internal-card-tile',
	$carousel_enabled ? 'mpma-internal-card-tile--carousel' : 'mpma-internal-card-tile--grid',
);

$style = sprintf(
	'--mpma-internal-card-tile-gap:%1$s;--mpma-internal-card-tile-columns:%2$d;--mpma-internal-card-tile-rows:%3$d;--mpma-internal-card-tile-visible:%4$d;--mpma-internal-card-tile-carousel-columns:%5$d;--mpma-internal-card-tile-nav-color:%6$s;',
	esc_attr( $gap ),
	$columns,
	$rows,
	$viewport_cards,
	$carousel_width_columns,
	esc_attr( $nav_color )
);

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
		'style' => $style,
	)
);
?>

<section
	<?php echo $wrapper_attributes; ?>
	data-mpma-internal-card-tile
	data-carousel="<?php echo $carousel_enabled ? '1' : '0'; ?>"
	data-loop="<?php echo $loop_enabled ? '1' : '0'; ?>"
	data-viewport-cards="<?php echo esc_attr( (string) $viewport_cards ); ?>"
>
	<?php if ( $carousel_enabled ) : ?>
		<div class="mpma-internal-card-tile__carousel-shell">
			<button type="button" class="mpma-internal-card-tile__nav-button mpma-internal-card-tile__nav-button--prev" data-card-tile-prev aria-label="<?php esc_attr_e( 'Previous cards', 'tailpress' ); ?>">&lt;</button>
			<div class="mpma-internal-card-tile__viewport" data-card-tile-viewport>
				<div class="mpma-internal-card-tile__track is-carousel" data-card-tile-track>
					<?php echo $content; ?>
				</div>
			</div>
			<button type="button" class="mpma-internal-card-tile__nav-button mpma-internal-card-tile__nav-button--next" data-card-tile-next aria-label="<?php esc_attr_e( 'Next cards', 'tailpress' ); ?>">&gt;</button>
		</div>
	<?php else : ?>
		<div class="mpma-internal-card-tile__track is-grid">
			<?php echo $content; ?>
		</div>
	<?php endif; ?>
</section>
