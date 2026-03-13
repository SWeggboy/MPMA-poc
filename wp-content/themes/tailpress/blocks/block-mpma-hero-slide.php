<?php
/**
 * Genesis Custom Blocks template: MPMA Hero Slide.
 */

$first_non_empty = static function ( array $keys, $default = '' ) {
	foreach ( $keys as $key ) {
		$value = block_value( $key );
		if ( is_string( $value ) && '' !== trim( $value ) ) {
			return $value;
		}

		if ( ! is_string( $value ) && null !== $value && '' !== $value ) {
			return $value;
		}
	}

	return $default;
};

$format_rich_text = static function ( string $value ): string {
	$value = trim( $value );
	if ( '' === $value ) {
		return '';
	}

	$safe = wp_kses_post( $value );
	if ( '' === $safe ) {
		return '';
	}

	if ( $safe === wp_strip_all_tags( $safe ) ) {
		return wpautop( $safe );
	}

	return $safe;
};

$resolve_image_url = static function ( array $keys ) {
	foreach ( $keys as $key ) {
		$value = block_value( $key );
		$image_id = 0;
		$image_url = '';

		if ( is_array( $value ) ) {
			if ( isset( $value['id'] ) && is_numeric( $value['id'] ) ) {
				$image_id = (int) $value['id'];
			} elseif ( isset( $value['ID'] ) && is_numeric( $value['ID'] ) ) {
				$image_id = (int) $value['ID'];
			}

			if ( isset( $value['url'] ) && is_string( $value['url'] ) ) {
				$image_url = trim( $value['url'] );
			} elseif ( isset( $value['src'] ) && is_string( $value['src'] ) ) {
				$image_url = trim( $value['src'] );
			}
		} elseif ( is_numeric( $value ) ) {
			$image_id = (int) $value;
		} elseif ( is_string( $value ) ) {
			$trimmed = trim( $value );
			if ( preg_match( '/^\d+$/', $trimmed ) ) {
				$image_id = (int) $trimmed;
			} elseif ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $trimmed, $matches ) ) {
				$image_url = trim( (string) ( $matches[1] ?? '' ) );
			} elseif ( '' !== $trimmed ) {
				$image_url = $trimmed;
			}
		}

		if ( $image_id > 0 ) {
			$attachment_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( is_string( $attachment_url ) && '' !== $attachment_url ) {
				return $attachment_url;
			}
		}

		if ( '' !== $image_url ) {
			return $image_url;
		}
	}

	return '';
};

$slide_header = trim( (string) $first_non_empty( array( 'slide-heading', 'slide_heading', 'slide-header', 'slide_header', 'header', 'heading', 'title' ), '' ) );
$slide_subtitle_markup = $format_rich_text( (string) $first_non_empty( array( 'slide-subtitle', 'slide_subtitle', 'subtitle', 'subheading' ), '' ) );
$slide_image_url = $resolve_image_url( array( 'slide-image', 'slide_image', 'slide-background-image', 'slide_background_image', 'image', 'background-image', 'background_image' ) );

$slide_image_url = is_string( $slide_image_url ) ? $slide_image_url : '';
$has_slide_image = '' !== trim( $slide_image_url );
$slide_media_style = $has_slide_image ? 'background-image: url("' . esc_url( $slide_image_url ) . '");' : '';
?>

<article class="mpma-hero-carousel__slide<?php echo $has_slide_image ? ' mpma-hero-carousel__slide--has-image' : ''; ?>">
	<div class="mpma-hero-carousel__slide-media" aria-hidden="true"<?php echo '' !== $slide_media_style ? ' style="' . esc_attr( $slide_media_style ) . '"' : ''; ?>></div>
	<div class="mpma-hero-carousel__slide-content">
		<?php if ( '' !== $slide_header ) : ?>
			<h1 class="mpma-hero-carousel__title"><?php echo esc_html( $slide_header ); ?></h1>
		<?php endif; ?>

		<?php if ( '' !== $slide_subtitle_markup ) : ?>
			<div class="mpma-hero-carousel__subtitle"><?php echo wp_kses_post( $slide_subtitle_markup ); ?></div>
		<?php endif; ?>
	</div>
</article>
