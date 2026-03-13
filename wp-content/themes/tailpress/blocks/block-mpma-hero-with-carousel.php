<?php
/**
 * Genesis Custom Blocks template: MPMA Hero With Carousel.
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

$hero_slides = (string) $first_non_empty( array( 'hero-slide', 'hero_slide', 'hero-slides', 'hero_slides', 'slides', 'slide-area', 'slide_area', 'slides-area', 'slides_area' ), '' );
$autoplay_raw = $first_non_empty( array( 'autoplay' ), '' );
$animation_speed_raw = $first_non_empty( array( 'animation-speed', 'animation_speed', 'animationspeed', 'speed' ), '' );
$animation_delay_raw = $first_non_empty( array( 'animation-delay', 'animation_delay', 'animationdelay', 'delay' ), '' );
$hero_max_height_raw = trim( (string) $first_non_empty( array( 'hero-max-height', 'hero_max_height', 'heromaxheight', 'max-height' ), '' ) );
$content_max_width_raw = trim( (string) $first_non_empty( array( 'content-max-width', 'content_max_width', 'contentmaxwidth', 'max-width' ), '' ) );
$slide_body = (string) $first_non_empty( array( 'slide-body', 'slide_body', 'slidebody', 'body' ), '' );
$slide_button_text = trim( (string) $first_non_empty( array( 'slide-button-text', 'slide_button_text', 'slidebuttontext', 'button-text', 'button_text' ), '' ) );
$slide_button_url = trim( (string) $first_non_empty( array( 'slide-button-url', 'slide_button_url', 'slidebuttonurl', 'button-url', 'button_url' ), '' ) );
$class_name = trim( (string) $first_non_empty( array( 'className', 'class_name' ), '' ) );

$to_bool = static function ( $value, bool $default ): bool {
	if ( is_bool( $value ) ) {
		return $value;
	}

	$normalized = strtolower( trim( (string) $value ) );
	if ( '' === $normalized ) {
		return $default;
	}

	return in_array( $normalized, array( '1', 'true', 'yes', 'on' ), true );
};

$to_length = static function ( string $value, string $default ): string {
	$value = sanitize_text_field( $value );

	if ( '' === $value ) {
		return $default;
	}

	if ( preg_match( '/^\d+(\.\d+)?$/', $value ) ) {
		return $value . 'px';
	}

	if ( preg_match( '/^\d+(\.\d+)?(px|rem|em|vh|vw|%)$/', $value ) ) {
		return $value;
	}

	return $default;
};

$to_ms = static function ( $value, int $default, int $min, int $max ): int {
	if ( ! is_numeric( $value ) ) {
		return $default;
	}

	$normalized = (int) round( (float) $value );
	$normalized = max( $min, $normalized );
	$normalized = min( $max, $normalized );

	return $normalized;
};

$autoplay = $to_bool( $autoplay_raw, true );
$animation_speed = $to_ms( $animation_speed_raw, 1200, 100, 20000 );
$animation_delay = $to_ms( $animation_delay_raw, 5000, 500, 60000 );
$hero_max_height = $to_length( $hero_max_height_raw, '530px' );
$content_max_width = $to_length( $content_max_width_raw, '1076px' );

$slides_output = '';
if ( '' !== trim( $hero_slides ) ) {
	$rendered_slides = function_exists( 'do_blocks' ) ? do_blocks( $hero_slides ) : $hero_slides;
	$slides_output = '' !== trim( $rendered_slides ) ? $rendered_slides : $hero_slides;
}

if ( '' === trim( $slides_output ) ) {
	$slides_output = '
	<article class="mpma-hero-carousel__slide">
		<div class="mpma-hero-carousel__slide-media" aria-hidden="true"></div>
		<div class="mpma-hero-carousel__slide-content">
			<h1 class="mpma-hero-carousel__title">Hero Title</h1>
			<p class="mpma-hero-carousel__subtitle">Hero subtitle goes here.</p>
		</div>
	</article>';
}

$button_href = esc_url( $slide_button_url );
$show_button = '' !== $slide_button_text && '' !== $button_href;

$wrapper_classes = 'mpma-hero-carousel';
if ( '' !== $class_name ) {
	$wrapper_classes .= ' ' . $class_name;
}
?>

<section
	class="<?php echo esc_attr( $wrapper_classes ); ?>"
	data-autoplay="<?php echo $autoplay ? '1' : '0'; ?>"
	data-animation-speed="<?php echo esc_attr( (string) $animation_speed ); ?>"
	data-animation-delay="<?php echo esc_attr( (string) $animation_delay ); ?>"
	style="--mpma-hero-max-height: <?php echo esc_attr( $hero_max_height ); ?>; --mpma-hero-content-max-width: <?php echo esc_attr( $content_max_width ); ?>; --mpma-hero-animation-speed: <?php echo esc_attr( (string) $animation_speed ); ?>ms;"
>
	<div class="mpma-hero-carousel__slides">
		<?php
		// Render trusted inner-block markup without KSES re-sanitizing inline slide styles.
		echo $slides_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
	</div>

	<div class="mpma-hero-carousel__persistent">
		<hr class="mpma-hero-carousel__rule" />

		<?php if ( '' !== trim( $slide_body ) ) : ?>
			<div class="mpma-hero-carousel__body">
				<?php echo wp_kses_post( wpautop( $slide_body ) ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $show_button ) : ?>
			<a class="mpma-hero-carousel__button wp-element-button !no-underline" href="<?php echo $button_href; ?>">
				<?php echo esc_html( $slide_button_text ); ?>
			</a>
		<?php endif; ?>
	</div>
</section>
