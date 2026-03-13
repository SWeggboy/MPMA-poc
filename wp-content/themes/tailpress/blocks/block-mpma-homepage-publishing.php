<?php
/**
 * Genesis Custom Blocks template: MPMA Homepage Publishing Container.
 */

$class_name = trim( (string) block_value( 'className' ) );

$sanitize_text_content = static function ( string $value ): string {
	$value = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/', ' ', $value );
	$value = preg_replace( '/\s+/', ' ', $value );

	return is_string( $value ) ? trim( $value ) : '';
};

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

$sanitize_color = static function ( string $value, string $default ): string {
	$value = sanitize_text_field( trim( $value ) );
	if ( '' === $value ) {
		return $default;
	}

	$hex = sanitize_hex_color( $value );
	if ( is_string( $hex ) && '' !== $hex ) {
		return $hex;
	}

	if ( preg_match( '/^var\(--[a-z0-9-]+\)$/i', $value ) ) {
		return $value;
	}

	if ( preg_match( '/^(rgba?|hsla?)\([^)]+\)$/i', $value ) ) {
		return $value;
	}

	return $default;
};

$header = $sanitize_text_content( (string) block_value( 'header' ) );
if ( '' === $header ) {
	$header = 'Publishing + Media';
}

$description_raw = $sanitize_text_content( (string) block_value( 'description' ) );
$description_parts = preg_split( '/\R\R+/', $description_raw );
$description_parts = array_values(
	array_filter(
		array_map( 'trim', is_array( $description_parts ) ? $description_parts : array() ),
		static function ( string $part ): bool {
			return '' !== $part;
		}
	)
);

if ( 0 === count( $description_parts ) ) {
	$description_parts = array(
		'Add the first paragraph for your publishing narrative.',
		'Add the second paragraph for supporting publishing context.',
	);
} elseif ( 1 === count( $description_parts ) ) {
	$description_parts[] = '';
}

$full_width_raw = block_value( 'full-width' );
$is_full_width = true;
if ( is_bool( $full_width_raw ) ) {
	$is_full_width = $full_width_raw;
} elseif ( '' !== trim( (string) $full_width_raw ) ) {
	$is_full_width = $to_bool( $full_width_raw );
}

$height_value = $sanitize_length( (string) block_value( 'height' ), '525px', 'px' );
$background_color = $sanitize_color( (string) block_value( 'background-color' ), '#010047' );

$content_raw = (string) block_value( 'content' );
$content_rendered = '';
if ( '' !== trim( $content_raw ) ) {
	$rendered = function_exists( 'do_blocks' ) ? do_blocks( $content_raw ) : '';
	$content_rendered = '' !== trim( $rendered ) ? $rendered : $content_raw;
}

$section_classes = 'mpma-homepage-publishing m-0 p-0';
if ( $is_full_width ) {
	$section_classes .= ' mpma-homepage-publishing--full-width';
}
if ( '' !== $class_name ) {
	$section_classes .= ' ' . $class_name;
}

$section_styles = array(
	'--mpma-homepage-publishing-bg:' . $background_color,
	'--mpma-homepage-publishing-height:' . $height_value,
);
if ( $is_full_width ) {
	$section_styles[] = 'width:100vw';
	$section_styles[] = 'max-width:100vw';
	$section_styles[] = 'margin-left:calc(50% - 50vw)';
	$section_styles[] = 'margin-right:calc(50% - 50vw)';
}

$fallback_cards = '
	<div class="mpma-homepage-publishing__card">
		<div class="mpma-homepage-publishing__card-logo-wrap">
			<div class="mpma-homepage-publishing__logo-placeholder">LOGO</div>
		</div>
		<a class="mpma-homepage-publishing__card-button wp-block-button__link wp-element-button" href="#">Read More</a>
		<div class="mpma-homepage-publishing__card-badge" aria-hidden="true">
			<div class="mpma-homepage-publishing__badge-placeholder">IMG</div>
		</div>
	</div>
	<div class="mpma-homepage-publishing__card">
		<div class="mpma-homepage-publishing__card-logo-wrap">
			<div class="mpma-homepage-publishing__logo-placeholder">LOGO</div>
		</div>
		<a class="mpma-homepage-publishing__card-button wp-block-button__link wp-element-button" href="#">Read More</a>
		<div class="mpma-homepage-publishing__card-badge" aria-hidden="true">
			<div class="mpma-homepage-publishing__badge-placeholder">IMG</div>
		</div>
	</div>
';
?>

<section class="<?php echo esc_attr( $section_classes ); ?>" style="<?php echo esc_attr( implode( ';', $section_styles ) . ';' ); ?>">
	<div class="mpma-homepage-publishing__inner <?php echo esc_attr( $is_full_width ? 'w-full' : 'container mx-auto' ); ?>">
		<div class="mpma-homepage-publishing__layout">
			<div class="mpma-homepage-publishing__copy">
				<h2 class="mpma-homepage-publishing__title"><?php echo esc_html( $header ); ?></h2>
				<p class="mpma-homepage-publishing__description"><?php echo esc_html( $description_parts[0] ); ?></p>
				<?php if ( '' !== $description_parts[1] ) : ?>
					<p class="mpma-homepage-publishing__description"><?php echo esc_html( $description_parts[1] ); ?></p>
				<?php endif; ?>
			</div>

			<div class="mpma-homepage-publishing__cards">
				<?php if ( '' !== trim( $content_rendered ) ) : ?>
					<?php echo wp_kses_post( $content_rendered ); ?>
				<?php else : ?>
					<?php echo wp_kses_post( $fallback_cards ); ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
