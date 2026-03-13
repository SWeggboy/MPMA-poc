<?php
/**
 * MPMA Internal Layout block render template.
 *
 * @param array  $attributes Block attributes.
 * @param string $content    Inner block content.
 */

$full_width = ! empty( $attributes['fullWidth'] );
$background_image = trim( esc_url_raw( (string) ( $attributes['backgroundImage'] ?? '' ) ) );
$background_image_id = isset( $attributes['backgroundImageId'] ) && is_numeric( $attributes['backgroundImageId'] ) ? (int) $attributes['backgroundImageId'] : 0;
$use_page_title_overlay = ! empty( $attributes['usePageTitleOverlay'] );
$min_height = trim( sanitize_text_field( (string) ( $attributes['minHeight'] ?? '' ) ) );
$sidebar_enabled = array_key_exists( 'sidebarEnabled', $attributes ) ? (bool) $attributes['sidebarEnabled'] : true;
$content_columns = isset( $attributes['contentColumns'] ) ? (int) $attributes['contentColumns'] : ( $sidebar_enabled ? 8 : 12 );
$content_position = sanitize_key( $attributes['contentPosition'] ?? 'center' );
$vertical_content_position = sanitize_key( $attributes['verticalContentPosition'] ?? 'top' );

if ( $background_image_id > 0 ) {
	$resolved_image = wp_get_attachment_image_url( $background_image_id, 'full' );
	if ( is_string( $resolved_image ) && '' !== $resolved_image ) {
		$background_image = $resolved_image;
	}
}

if ( '' !== $min_height && ! preg_match( '/^\d+(\.\d+)?(px|rem|em|vh|vw|%)$/', $min_height ) ) {
	$min_height = '';
}

$content_columns = max( 4, min( 12, $content_columns ) );

$left_spacer = 0;
$right_spacer = 0;

if ( $sidebar_enabled ) {
	$right_spacer = max( 0, 12 - $content_columns );
} else {
	$remaining = max( 0, 12 - $content_columns );
	if ( 'left' === $content_position ) {
		$right_spacer = $remaining;
	} elseif ( 'right' === $content_position ) {
		$left_spacer = $remaining;
	} else {
		$left_spacer = (int) floor( $remaining / 2 );
		$right_spacer = max( 0, $remaining - $left_spacer );
	}
}

$style_parts = array(
	'--mpma-internal-layout-content-start:' . ( $left_spacer + 1 ),
	'--mpma-internal-layout-content-span:' . $content_columns,
	'--mpma-internal-layout-row-columns:' . $content_columns,
);

if ( '' !== $min_height ) {
	$style_parts[] = '--mpma-internal-layout-min-height:' . $min_height;
}

if ( $right_spacer > 0 ) {
	$style_parts[] = '--mpma-internal-layout-spacer-start:' . ( $left_spacer + $content_columns + 1 );
	$style_parts[] = '--mpma-internal-layout-spacer-span:' . $right_spacer;
}

if ( '' !== $background_image ) {
	$style_parts[] = '--mpma-internal-layout-bg-image:url("' . esc_url( $background_image ) . '")';
}

$wrapper_classes = 'mpma-internal-layout';
if ( $full_width ) {
	$wrapper_classes .= ' mpma-internal-layout--full-width alignfull';
}
if ( '' !== $background_image ) {
	$wrapper_classes .= ' mpma-internal-layout--has-bg';
}
if ( $sidebar_enabled && $right_spacer > 0 ) {
	$wrapper_classes .= ' mpma-internal-layout--has-sidebar';
}
if ( in_array( $vertical_content_position, array( 'top', 'center', 'bottom' ), true ) ) {
	$wrapper_classes .= ' mpma-internal-layout--vertical-' . $vertical_content_position;
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => $wrapper_classes,
		'style' => implode( ';', $style_parts ) . ';',
	)
);
?>

<section <?php echo $wrapper_attributes; ?>>
	<?php if ( '' !== $background_image && $use_page_title_overlay ) : ?>
		<div class="absolute inset-0 bg-[#004d84]/25 pointer-events-none" style="opacity: 0.95;"></div>
		<div
			class="absolute inset-0 pointer-events-none bg-[linear-gradient(to_bottom,rgba(20,58,94,0)_45%,rgba(20,58,94,0.50)_100%),linear-gradient(to_right,rgba(0,77,132,0.66)_0%,rgba(0,77,132,0.53)_25%,rgba(0,77,132,0.27)_50%,rgba(0,77,132,0.06)_75%,rgba(0,77,132,0)_100%),linear-gradient(to_right,rgba(91,152,113,0)_0%,rgba(91,152,113,0.18)_35%,rgba(91,152,113,0.45)_70%,rgba(91,152,113,0.72)_100%)]"
			style="opacity: 0.95;"
		></div>
	<?php endif; ?>
	<div class="layout-shell relative z-10">
		<div class="layout-grid items-start gap-y-8 mpma-internal-layout__grid">
			<div class="mpma-internal-layout__content">
				<?php echo $content; ?>
			</div>
			<?php if ( $right_spacer > 0 ) : ?>
				<div class="mpma-internal-layout__spacer" aria-hidden="true"></div>
			<?php endif; ?>
		</div>
	</div>
</section>
