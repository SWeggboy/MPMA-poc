<?php
/**
 * Genesis Custom Blocks template: MPMA Homepage Advocacy.
 */

$class_name = trim( (string) block_value( 'className' ) );

$to_bool = static function ( $value ): bool {
	if ( is_bool( $value ) ) {
		return $value;
	}

	$normalized = strtolower( trim( (string) $value ) );
	return in_array( $normalized, array( '1', 'true', 'yes', 'on' ), true );
};

$sanitize_length = static function ( string $value, string $default, string $numeric_unit = 'px' ): string {
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

$resolve_image_url = static function ( string $field_key ): string {
	$value = block_value( $field_key );

	if ( is_array( $value ) ) {
		$image_id  = isset( $value['id'] ) ? (int) $value['id'] : ( isset( $value['ID'] ) ? (int) $value['ID'] : 0 );
		$image_url = isset( $value['url'] ) ? trim( (string) $value['url'] ) : '';

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
		if ( '' !== $trimmed ) {
			return $trimmed;
		}
	}

	return '';
};

$background_image_url = $resolve_image_url( 'background-repeater-image' );
$left_image_url       = $resolve_image_url( 'left-side-image' );
$full_width_raw       = block_value( 'full-width' );
$is_full_width        = true;
if ( is_bool( $full_width_raw ) ) {
	$is_full_width = $full_width_raw;
} elseif ( '' !== trim( (string) $full_width_raw ) ) {
	$is_full_width = $to_bool( $full_width_raw );
}
$block_height         = $sanitize_length( (string) block_value( 'height' ), '282px', 'px' );
$content_raw          = (string) block_value( 'content' );

$rendered_content = '';
if ( '' !== trim( $content_raw ) ) {
	$rendered_blocks = function_exists( 'do_blocks' ) ? do_blocks( $content_raw ) : '';
	if ( '' !== trim( $rendered_blocks ) ) {
		$rendered_content = $rendered_blocks;
	} else {
		$rendered_content = wpautop( wp_kses_post( $content_raw ) );
	}
}

if ( '' === trim( $rendered_content ) ) {
	$fallback = '<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Advocacy That Drives Industry Forward</h2><!-- /wp:heading --><!-- wp:paragraph --><p>MPMA represents the industry voice where policy and business intersect.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Use this area to add supporting details for members and stakeholders.</p><!-- /wp:paragraph -->';
	$rendered_content = function_exists( 'do_blocks' ) ? do_blocks( $fallback ) : $fallback;
}

$section_classes = 'mpma-homepage-a relative m-0 p-0';
if ( $is_full_width ) {
	$section_classes .= ' mpma-homepage-a--full-width';
}
if ( '' !== $class_name ) {
	$section_classes .= ' ' . $class_name;
}

$section_styles = array(
	'height:' . $block_height,
	'margin-top:0',
	'margin-bottom:0',
	'padding-top:0',
	'padding-bottom:0',
);

if ( '' !== $background_image_url ) {
	$section_styles[] = 'background-image:url(' . esc_url_raw( $background_image_url ) . ')';
	$section_styles[] = 'background-repeat:repeat-x';
	$section_styles[] = 'background-position:top left';
	$section_styles[] = 'background-size:auto 100%';
}

if ( $is_full_width ) {
	$section_styles[] = 'width:100vw';
	$section_styles[] = 'max-width:100vw';
	$section_styles[] = 'margin-left:calc(50% - 50vw)';
	$section_styles[] = 'margin-right:calc(50% - 50vw)';
}

$inner_classes = $is_full_width
	? 'mx-auto h-full w-full'
	: 'container mx-auto h-full px-4';
?>

<section class="<?php echo esc_attr( $section_classes ); ?>" style="<?php echo esc_attr( implode( ';', $section_styles ) . ';' ); ?>">
	<div class="<?php echo esc_attr( $inner_classes ); ?>">
			<div class="grid h-full grid-cols-1 items-stretch lg:grid-cols-2">
				<div class="mpma-homepage-a__media relative h-full min-w-0 overflow-hidden">
				<?php if ( '' !== $left_image_url ) : ?>
							<img
								src="<?php echo esc_url( $left_image_url ); ?>"
								alt=""
							class="block h-full w-auto max-w-none ml-auto"
							style="height:calc(100% + 6px);margin-top:-4px;"
							/>
				<?php else : ?>
					<div class="flex h-full w-full items-center justify-center bg-gray-200">
						<span class="text-gray-500"><?php esc_html_e( 'Left Image', 'tailpress' ); ?></span>
					</div>
				<?php endif; ?>
			</div>

			<div class="mpma-homepage-a__content relative z-10 flex h-full min-w-0 flex-col justify-center overflow-hidden break-words px-6 py-8">
				<?php echo wp_kses_post( $rendered_content ); ?>
			</div>
		</div>
	</div>
</section>
