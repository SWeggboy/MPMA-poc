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

if ( ! function_exists( 'tailpress_sync_awards_slide_render_attrs' ) ) {
	/**
	 * Sync awards slide attributes onto the nested rendered block tree.
	 *
	 * @param array $block Parsed block array.
	 * @param array $slide_attrs Slide-level attributes.
	 * @return array
	 */
	function tailpress_sync_awards_slide_render_attrs( array $block, array $slide_attrs ): array {
		if ( empty( $block['innerBlocks'] ) || ! is_array( $block['innerBlocks'] ) ) {
			return $block;
		}

		$horizontal_alignment = sanitize_key( (string) ( $slide_attrs['awardsPanelHorizontalAlignment'] ?? '' ) );
		if ( ! in_array( $horizontal_alignment, array( 'left', 'center', 'right' ), true ) ) {
			$horizontal_alignment = 'center';
		}

		$vertical_alignment = sanitize_key( (string) ( $slide_attrs['awardsPanelVerticalAlignment'] ?? '' ) );
		if ( ! in_array( $vertical_alignment, array( 'top', 'center', 'bottom' ), true ) ) {
			$vertical_alignment = 'top';
		}

		$width_columns = isset( $slide_attrs['awardsPanelWidthColumns'] ) ? (int) $slide_attrs['awardsPanelWidthColumns'] : 6;
		$width_columns = max( 1, min( 12, $width_columns ) );

		$padding = array(
			'top'    => (string) ( $slide_attrs['awardsPanelPaddingTop'] ?? '2rem' ),
			'right'  => (string) ( $slide_attrs['awardsPanelPaddingRight'] ?? '1.5rem' ),
			'bottom' => (string) ( $slide_attrs['awardsPanelPaddingBottom'] ?? '2rem' ),
			'left'   => (string) ( $slide_attrs['awardsPanelPaddingLeft'] ?? '1.5rem' ),
		);

		$show_bullets = ! empty( $slide_attrs['awardsShowBullets'] );
		$show_dividers = ! array_key_exists( 'awardsShowDividers', $slide_attrs ) || ! empty( $slide_attrs['awardsShowDividers'] );
		$list_class = 'mpma-internal-carousel-awards-list';
		if ( $show_bullets ) {
			$list_class .= ' has-bullets';
		}
		if ( $show_dividers ) {
			$list_class .= ' has-dividers';
		}

		foreach ( $block['innerBlocks'] as $index => $inner_block ) {
			if ( ! is_array( $inner_block ) ) {
				continue;
			}

			$block_name = $inner_block['blockName'] ?? '';
			$attrs = isset( $inner_block['attrs'] ) && is_array( $inner_block['attrs'] ) ? $inner_block['attrs'] : array();

			if ( 'tailpress/mpma-internal-layout' === $block_name ) {
				$attrs['contentPosition'] = $horizontal_alignment;
				$attrs['contentColumns'] = max( 4, $width_columns );
				$inner_block['attrs'] = $attrs;
			} elseif ( 'tailpress/mpma-internal-layout-column' === $block_name ) {
				$attrs['widthColumns'] = $width_columns;
				$attrs['verticalAlignment'] = $vertical_alignment;
				$inner_block['attrs'] = $attrs;
			} elseif ( 'core/group' === $block_name ) {
				$class_name = isset( $attrs['className'] ) ? (string) $attrs['className'] : '';
				if ( false !== strpos( $class_name, 'mpma-internal-carousel-awards-panel' ) ) {
					if ( false === strpos( $class_name, 'mpma-internal-carousel-awards-panel--carousel' ) ) {
						$class_name = trim( $class_name . ' mpma-internal-carousel-awards-panel--carousel' );
						$attrs['className'] = $class_name;
					}
					$current_style = isset( $attrs['style'] ) && is_array( $attrs['style'] ) ? $attrs['style'] : array();
					$current_spacing = isset( $current_style['spacing'] ) && is_array( $current_style['spacing'] ) ? $current_style['spacing'] : array();
					$attrs['style'] = array_merge(
						$current_style,
						array(
							'spacing' => array_merge(
								$current_spacing,
								array(
									'padding' => $padding,
								)
							),
						)
					);
					$inner_block['attrs'] = $attrs;
				}
			} elseif ( 'core/list' === $block_name ) {
				$attrs['className'] = $list_class;
				$inner_block['attrs'] = $attrs;
			}

			if ( ! empty( $inner_block['innerBlocks'] ) && is_array( $inner_block['innerBlocks'] ) ) {
				$inner_block = tailpress_sync_awards_slide_render_attrs( $inner_block, $slide_attrs );
			}

			$block['innerBlocks'][ $index ] = $inner_block;
		}

		return $block;
	}
}

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

		if ( 'awards' === $variation ) {
			$inner_block = tailpress_sync_awards_slide_render_attrs( $inner_block, $slide_attrs );
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
