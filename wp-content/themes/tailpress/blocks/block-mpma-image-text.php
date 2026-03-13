<?php
/**
 * Genesis Custom Blocks template: MPMA Image Text.
 */

$class_name = trim( (string) block_value( 'className' ) );

$resolve_image_url = static function ( string $field_key ): string {
	$value = block_value( $field_key );

	if ( is_array( $value ) ) {
		$image_id = 0;
		$image_url = '';

		if ( isset( $value['id'] ) && is_numeric( $value['id'] ) ) {
			$image_id = (int) $value['id'];
		} elseif ( isset( $value['ID'] ) && is_numeric( $value['ID'] ) ) {
			$image_id = (int) $value['ID'];
		}

		if ( isset( $value['url'] ) && is_string( $value['url'] ) ) {
			$image_url = trim( $value['url'] );
		}

		if ( $image_id > 0 ) {
			$resolved = wp_get_attachment_image_url( $image_id, 'full' );
			if ( is_string( $resolved ) && '' !== $resolved ) {
				return $resolved;
			}
		}

		if ( '' !== $image_url ) {
			return $image_url;
		}
	}

	if ( is_numeric( $value ) ) {
		$resolved = wp_get_attachment_image_url( (int) $value, 'full' );
		if ( is_string( $resolved ) && '' !== $resolved ) {
			return $resolved;
		}
	}

	if ( is_string( $value ) ) {
		$trimmed = trim( $value );
		if ( '' === $trimmed ) {
			return '';
		}

		if ( preg_match( '/^\d+$/', $trimmed ) ) {
			$resolved = wp_get_attachment_image_url( (int) $trimmed, 'full' );
			if ( is_string( $resolved ) && '' !== $resolved ) {
				return $resolved;
			}
		}

		if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $trimmed, $matches ) ) {
			return trim( (string) ( $matches[1] ?? '' ) );
		}

		return $trimmed;
	}

	return '';
};

$to_bool = static function ( $value ): bool {
	if ( is_bool( $value ) ) {
		return $value;
	}

	$normalized = strtolower( trim( (string) $value ) );
	return in_array( $normalized, array( '1', 'true', 'yes', 'on' ), true );
};

$normalize_position = static function ( string $value ): string {
	$value = strtolower( trim( $value ) );
	if ( 'right' === $value || false !== strpos( $value, 'right' ) ) {
		return 'right';
	}

	return 'left';
};

$sanitize_length = static function ( string $value, string $default, string $numeric_unit ): string {
	$value = sanitize_text_field( trim( $value ) );
	if ( '' === $value ) {
		return $default;
	}

	if ( preg_match( '/^-?\d+(\.\d+)?$/', $value ) ) {
		return $value . $numeric_unit;
	}

	if ( preg_match( '/^-?\d+(\.\d+)?(px|rem|em|vh|vw|%)$/', $value ) ) {
		return $value;
	}

	return $default;
};

$sanitize_color = static function ( string $value ): string {
	$value = sanitize_text_field( trim( $value ) );
	if ( '' === $value ) {
		return '';
	}

	$hex = sanitize_hex_color( $value );
	if ( is_string( $hex ) && '' !== $hex ) {
		return $hex;
	}

	if ( preg_match( '/^(rgba?|hsla?)\([^)]+\)$/i', $value ) ) {
		return $value;
	}

	if ( preg_match( '/^var\(--[a-z0-9-]+\)$/i', $value ) ) {
		return $value;
	}

	return '';
};

$main_image_position = $normalize_position( (string) block_value( 'main-image-position' ) );
$main_image_url = $resolve_image_url( 'main-image' );
$is_full_width = $to_bool( block_value( 'full-width' ) );
$gap_value = $sanitize_length( (string) block_value( 'gap' ), '2rem', 'rem' );
$content_offset_value = $sanitize_length( (string) block_value( 'content-offset' ), '0px', 'px' );
$content_top_padding_value = $sanitize_length( (string) block_value( 'content-top-padding' ), '0rem', 'rem' );
$content_bottom_padding_value = $sanitize_length( (string) block_value( 'content-bottom-padding' ), '0rem', 'rem' );
$image_width_value = $sanitize_length( (string) block_value( 'image-width' ), '36rem', 'px' );
$image_height_value = $sanitize_length( (string) block_value( 'image-height' ), '24rem', 'px' );
$content_width_value = $sanitize_length( (string) block_value( 'content-width' ), $image_width_value, 'px' );
$content_height_value = $sanitize_length( (string) block_value( 'content-height' ), $image_height_value, 'px' );
$background_color_value = $sanitize_color( (string) block_value( 'background-color' ) );

$content = (string) block_value( 'content' );
$rendered_content = '';
if ( '' !== trim( $content ) ) {
	$rendered = function_exists( 'do_blocks' ) ? do_blocks( $content ) : '';
	$rendered_content = '' !== trim( $rendered ) ? $rendered : $content;
}

if ( '' === trim( $rendered_content ) ) {
	$fallback = '<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Image Text Heading</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Add your content here.</p><!-- /wp:paragraph -->';
	$rendered_content = function_exists( 'do_blocks' ) ? do_blocks( $fallback ) : $fallback;
}

$section_classes = 'mpma-image-text bg-white py-8 my-12';
$section_style_parts = array();
if ( $is_full_width ) {
	$section_classes .= ' mpma-image-text--full-width';
	$section_style_parts[] = 'margin-left:calc(50% - 50vw)';
	$section_style_parts[] = 'margin-right:calc(50% - 50vw)';
	$section_style_parts[] = 'max-width:100vw';
	$section_style_parts[] = 'width:100vw';
}
if ( 'left' === $main_image_position ) {
	$section_classes .= ' mpma-image-text--image-left';
} else {
	$section_classes .= ' mpma-image-text--image-right';
}
$section_style_parts[] = '--mpma-image-text-gap:' . $gap_value;
$section_style_parts[] = '--mpma-image-text-content-offset:' . $content_offset_value;
$section_style_parts[] = '--mpma-image-text-content-top-padding:' . $content_top_padding_value;
$section_style_parts[] = '--mpma-image-text-content-bottom-padding:' . $content_bottom_padding_value;
$section_style_parts[] = '--mpma-image-text-image-width:' . $image_width_value;
$section_style_parts[] = '--mpma-image-text-image-height:' . $image_height_value;
$section_style_parts[] = '--mpma-image-text-content-width:' . $content_width_value;
$section_style_parts[] = '--mpma-image-text-content-height:' . $content_height_value;

if ( '' !== $class_name ) {
	$section_classes .= ' ' . $class_name;
}

$section_style = implode( ';', $section_style_parts );
if ( '' !== $section_style ) {
	$section_style .= ';';
}

$inner_classes = $is_full_width ? 'mpma-image-text__container mx-auto w-full px-4 md:px-8 lg:px-12' : 'mpma-image-text__container container mx-auto px-4';
$inner_style = '' !== $background_color_value ? 'background-color:' . $background_color_value . ';' : '';
?>

<section class="<?php echo esc_attr( $section_classes ); ?>"<?php echo '' !== $section_style ? ' style="' . esc_attr( $section_style ) . '"' : ''; ?>>
	<div class="<?php echo esc_attr( $inner_classes ); ?>"<?php echo '' !== $inner_style ? ' style="' . esc_attr( $inner_style ) . '"' : ''; ?>>
			<div class="mpma-image-text__layout">
				<div class="mpma-image-text__image">
				<?php if ( '' !== $main_image_url ) : ?>
					<img
						src="<?php echo esc_url( $main_image_url ); ?>"
						alt=""
						class="w-full h-full"
					/>
				<?php else : ?>
					<div class="w-full h-full bg-gray-200 rounded-[10px] flex items-center justify-center">
						<span class="text-gray-500"><?php esc_html_e( 'Main Image', 'tailpress' ); ?></span>
					</div>
				<?php endif; ?>
			</div>

			<div class="mpma-image-text__content">
				<?php echo wp_kses_post( $rendered_content ); ?>
			</div>
		</div>
	</div>
</section>
