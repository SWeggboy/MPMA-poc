<?php
/**
 * MPMA Internal Full Width Carousel block render template.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 */

$animation_speed = isset( $attributes['animationSpeed'] ) ? (int) $attributes['animationSpeed'] : 400;
$animation_speed = max( 150, min( 3000, $animation_speed ) );
$variation = isset( $attributes['variation'] ) && 'awards' === $attributes['variation'] ? 'awards' : 'default';
$enable_nav_overflow = ! isset( $attributes['enableNavOverflow'] ) || ! empty( $attributes['enableNavOverflow'] );
$equal_panel_heights = ! isset( $attributes['equalPanelHeights'] ) || ! empty( $attributes['equalPanelHeights'] );
$nav_active_color = sanitize_hex_color( $attributes['navActiveColor'] ?? '#000000' );
$nav_inactive_color = sanitize_hex_color( $attributes['navInactiveColor'] ?? '#747474' );

if ( ! $nav_active_color ) {
	$nav_active_color = '#000000';
}

if ( ! $nav_inactive_color ) {
	$nav_inactive_color = '#747474';
}

$slides = array();

if ( isset( $block ) && is_object( $block ) && isset( $block->parsed_block['innerBlocks'] ) && is_array( $block->parsed_block['innerBlocks'] ) ) {
	foreach ( $block->parsed_block['innerBlocks'] as $index => $inner_block ) {
		if ( ! is_array( $inner_block ) || ( $inner_block['blockName'] ?? '' ) !== 'tailpress/mpma-internal-full-width-carousel-slide' ) {
			continue;
		}

		$slide_attrs = isset( $inner_block['attrs'] ) && is_array( $inner_block['attrs'] ) ? $inner_block['attrs'] : array();
		$nav_label  = trim( sanitize_text_field( (string) ( $slide_attrs['navLabel'] ?? '' ) ) );

		if ( '' === $nav_label ) {
			/* translators: %d: slide number */
			$nav_label = sprintf( __( 'Slide %d', 'tailpress' ), $index + 1 );
		}

		$slides[] = array(
			'nav_label'  => $nav_label,
			'content'    => render_block( $inner_block ),
		);
	}
}

if ( empty( $slides ) ) {
	return '';
}

$block_id = wp_unique_id( 'mpma-internal-full-width-carousel-' );
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'mpma-internal-full-width-carousel mpma-internal-carousel alignfull'
			. ( 'awards' === $variation ? ' is-awards' : '' )
			. ( $enable_nav_overflow ? ' has-nav-overflow' : '' ),
		'style' => '--mpma-internal-full-width-carousel-speed:' . $animation_speed . 'ms;'
			. '--mpma-internal-full-width-carousel-nav-active-color:' . $nav_active_color . ';'
			. '--mpma-internal-full-width-carousel-nav-inactive-color:' . $nav_inactive_color . ';',
	)
);

$nav_column_classes = 'col-span-12 lg:col-start-2 lg:col-span-10';

if ( 'awards' === $variation && ! $enable_nav_overflow ) {
	$nav_column_classes = 'col-span-12 lg:col-start-4 lg:col-span-6';
}

$initial_index = 'awards' === $variation ? count( $slides ) - 1 : 0;
$nav_viewport_labels = isset( $attributes['navViewportLabels'] ) ? (int) $attributes['navViewportLabels'] : 4;
$nav_viewport_labels = max( 1, min( count( $slides ), $nav_viewport_labels ) );
?>

<section <?php echo $wrapper_attributes; ?> data-mpma-internal-full-width-carousel data-nav-overflow="<?php echo $enable_nav_overflow ? '1' : '0'; ?>" data-nav-visible-labels="<?php echo esc_attr( (string) $nav_viewport_labels ); ?>" data-equal-panel-heights="<?php echo $equal_panel_heights ? '1' : '0'; ?>" id="<?php echo esc_attr( $block_id ); ?>">
	<div class="layout-shell mpma-internal-full-width-carousel__nav-shell">
		<div class="layout-grid">
			<div class="<?php echo esc_attr( $nav_column_classes ); ?>">
				<div class="mpma-internal-full-width-carousel__nav" role="tablist" aria-label="<?php esc_attr_e( 'Carousel slides', 'tailpress' ); ?>">
					<button type="button" class="mpma-internal-full-width-carousel__arrow" data-full-width-carousel-prev aria-label="<?php esc_attr_e( 'Previous slide', 'tailpress' ); ?>">&lt;</button>
					<div class="mpma-internal-full-width-carousel__nav-viewport" data-full-width-carousel-nav-viewport>
						<div class="mpma-internal-full-width-carousel__nav-items" data-full-width-carousel-nav-track>
							<?php foreach ( $slides as $index => $slide ) : ?>
								<button
									type="button"
									class="mpma-internal-full-width-carousel__nav-item<?php echo $initial_index === $index ? ' is-active' : ''; ?>"
									data-full-width-carousel-nav
									data-slide-index="<?php echo esc_attr( (string) $index ); ?>"
									aria-selected="<?php echo $initial_index === $index ? 'true' : 'false'; ?>"
									role="tab"
								>
									<?php echo esc_html( $slide['nav_label'] ); ?>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
					<button type="button" class="mpma-internal-full-width-carousel__arrow" data-full-width-carousel-next aria-label="<?php esc_attr_e( 'Next slide', 'tailpress' ); ?>">&gt;</button>
				</div>
			</div>
		</div>
	</div>

	<div class="mpma-internal-full-width-carousel__viewport" data-full-width-carousel-viewport>
		<div class="mpma-internal-full-width-carousel__track" data-full-width-carousel-track>
				<?php foreach ( $slides as $index => $slide ) : ?>
					<article
						class="mpma-internal-full-width-carousel__slide<?php echo $initial_index === $index ? ' is-active' : ''; ?>"
					data-full-width-carousel-slide
						role="tabpanel"
						aria-hidden="<?php echo $initial_index === $index ? 'false' : 'true'; ?>"
					>
						<div class="mpma-internal-full-width-carousel__slide-inner">
							<?php echo $slide['content']; ?>
						</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
