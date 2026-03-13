<?php
/**
 * Genesis Custom Blocks template: MPMA Card.
 */

$title            = trim( (string) block_value( 'title' ) );
$title_color      = trim( (string) block_value( 'title-color' ) );
$title_font_size  = trim( (string) block_value( 'title-font-size' ) );
$content          = (string) block_value( 'content' );
$button_text      = trim( (string) block_value( 'button-text' ) );
$button_style     = trim( (string) block_value( 'button-style' ) );
$button_color     = trim( (string) block_value( 'button-color' ) );
$button_hover_color = trim( (string) block_value( 'button-hover-color' ) );
$link             = trim( (string) block_value( 'link' ) );
$bg_image_id      = (int) block_value( 'background-image' );
$show_border      = (bool) block_value( 'border' );
$border_radius    = trim( (string) block_value( 'border-radius' ) );
$border_width     = trim( (string) block_value( 'border-width' ) );
$border_color     = trim( (string) block_value( 'border-color' ) );
$background_color = trim( (string) block_value( 'background-color' ) );
$title_position   = trim( (string) block_value( 'title-position' ) );
$title_alignment  = trim( (string) block_value( 'title-alignment' ) );
$width            = trim( (string) block_value( 'width' ) );
$height           = trim( (string) block_value( 'height' ) );
$padding_top      = block_value( 'padding-top' );
$padding_right    = block_value( 'padding-right' );
$padding_bottom   = block_value( 'padding-bottom' );
$padding_left     = block_value( 'padding-left' );
$flippable        = (bool) block_value( 'flippable' );
$flip_direction   = trim( (string) block_value( 'flip-direction' ) );
$flip_speed       = trim( (string) block_value( 'flip-speed' ) );
$flip_front_text  = trim( (string) block_value( 'flip-card-front-text' ) );
$flip_front_text_position = strtolower( trim( (string) block_value( 'flip-card-front-text-position' ) ) );
$flip_front_text_color = trim( (string) block_value( 'flip-card-front-text-color' ) );
$flip_front_color = trim( (string) block_value( 'flip-card-front-color' ) );
$flip_front_image = (int) block_value( 'flip-card-front-image' );
$drop_shadow      = (bool) block_value( 'drop-shadow' );
$class_name       = trim( (string) block_value( 'className' ) );

if ( ! $drop_shadow ) {
	$drop_shadow = (bool) block_value( 'drop-shadow-toggle' );
}

if ( ! $drop_shadow ) {
	$drop_shadow = (bool) block_value( 'card-drop-shadow' );
}

if ( ! $drop_shadow ) {
	$drop_shadow = (bool) block_value( 'card-shadow' );
}

if ( '' === $title_position ) {
	$title_position = 'inside';
}

if ( '' === $title_alignment ) {
	$title_alignment = 'center';
}

if ( '' === $border_radius ) {
	$border_radius = '2px';
}

if ( '' === $title_color ) {
	$title_color = 'var(--color-white)';
}

if ( '' === $border_color ) {
	$border_color = 'var(--color-primary)';
}

if ( '' === $flip_front_color ) {
	$flip_front_color = 'var(--color-primary)';
}

if ( '' === $flip_speed ) {
	$flip_speed = '0.8';
}

if ( '' === $flip_direction ) {
	$flip_direction = 'left-right';
}

if ( '' === $flip_front_text_position ) {
	$flip_front_text_position = 'middle';
}

if ( ! in_array( $flip_front_text_position, array( 'top', 'middle', 'bottom' ), true ) ) {
	$flip_front_text_position = 'middle';
}

$to_css_value = static function ( string $value ): string {
	$value = sanitize_text_field( $value );
	if ( '' === $value ) {
		return '';
	}

	if ( ! preg_match( '/^[#(),.%\-\+\/:\sa-zA-Z0-9]+$/', $value ) ) {
		return '';
	}

	return $value;
};

$to_css_length = static function ( $value, string $default = '' ) use ( $to_css_value ): string {
	if ( is_numeric( $value ) ) {
		return (string) $value . 'px';
	}

	$cleaned = $to_css_value( (string) $value );
	if ( '' !== $cleaned ) {
		return $cleaned;
	}

	return $default;
};

$to_css_duration = static function ( string $value, string $default = '0.8s' ) use ( $to_css_value ): string {
	$cleaned = $to_css_value( $value );
	if ( '' === $cleaned ) {
		return $default;
	}

	if ( preg_match( '/^[0-9]*\.?[0-9]+$/', $cleaned ) ) {
		return $cleaned . 's';
	}

	if ( preg_match( '/^[0-9]*\.?[0-9]+(ms|s)$/', $cleaned ) ) {
		return $cleaned;
	}

	return $default;
};

$get_image_url = static function ( int $image_id ): string {
	if ( $image_id <= 0 ) {
		return '';
	}

	$image_url = wp_get_attachment_image_url( $image_id, 'full' );
	return is_string( $image_url ) ? $image_url : '';
};

$bg_image_url        = $get_image_url( $bg_image_id );
$flip_front_image_url = $get_image_url( $flip_front_image );

$card_classes = 'mpma-card relative overflow-hidden';
if ( $flippable ) {
	$card_classes .= ' mpma-card--flippable';
}
if ( $drop_shadow ) {
	$card_classes .= ' mpma-card--has-drop-shadow';
}
if ( '' !== $class_name ) {
	$card_classes .= ' ' . $class_name;
}

$alignment_classes = 'text-center items-center';
$title_alignment_classes = 'text-center';
if ( 'left' === $title_alignment ) {
	$alignment_classes = 'text-left items-start';
	$title_alignment_classes = 'text-left';
} elseif ( 'right' === $title_alignment ) {
	$alignment_classes = 'text-right items-end';
	$title_alignment_classes = 'text-right';
}

$flip_transform      = 'rotateY(180deg)';
$flip_back_transform = 'rotateY(180deg)';
$flip_front_justify_class = 'justify-center';

if ( 'right-left' === $flip_direction ) {
	$flip_transform      = 'rotateY(-180deg)';
	$flip_back_transform = 'rotateY(-180deg)';
} elseif ( 'up-down' === $flip_direction ) {
	$flip_transform      = 'rotateX(-180deg)';
	$flip_back_transform = 'rotateX(-180deg)';
} elseif ( 'down-up' === $flip_direction ) {
	$flip_transform      = 'rotateX(180deg)';
	$flip_back_transform = 'rotateX(180deg)';
}

if ( 'top' === $flip_front_text_position ) {
	$flip_front_justify_class = 'justify-start';
} elseif ( 'bottom' === $flip_front_text_position ) {
	$flip_front_justify_class = 'justify-end';
}

$title_styles = [];
$flip_front_text_styles = [];
$safe_title_color = $to_css_value( $title_color );
if ( '' !== $safe_title_color ) {
	$title_styles[] = 'color: ' . $safe_title_color;
}

$safe_title_font_size = $to_css_length( $title_font_size );
if ( '' !== $safe_title_font_size ) {
	$title_styles[] = 'font-size: ' . $safe_title_font_size;
}

$wrapper_styles   = [];
$surface_styles   = [];
$front_face_styles = [];
$back_face_styles  = [];
$has_button        = '' !== $button_text && '' !== $link;
$body_justify_class = $has_button ? 'justify-between' : 'justify-center';
$resolved_card_width  = $to_css_length( $width, '250px' );
$resolved_card_height = $to_css_length( $height, '200px' );

$wrapper_styles[] = 'width: ' . $resolved_card_width;
$wrapper_styles[] = 'height: ' . $resolved_card_height;
$wrapper_styles[] = 'margin: 0';
$wrapper_styles[] = '--mpma-flip-duration: ' . $to_css_duration( $flip_speed );
$wrapper_styles[] = '--mpma-flip-transform: ' . $flip_transform;
$wrapper_styles[] = '--mpma-flip-back-transform: ' . $flip_back_transform;

$safe_border_radius = $to_css_length( $border_radius, '2px' );
if ( '' !== $safe_border_radius ) {
	$wrapper_styles[] = 'border-radius: ' . $safe_border_radius;
	$surface_styles[] = 'border-radius: ' . $safe_border_radius;
	$front_face_styles[] = 'border-radius: ' . $safe_border_radius;
	$back_face_styles[] = 'border-radius: ' . $safe_border_radius;
}

if ( $show_border ) {
	$safe_border_width = $to_css_length( $border_width, '1px' );
	$safe_border_color = $to_css_value( $border_color );
	$surface_styles[] = 'border-style: solid';
	$surface_styles[] = 'border-width: ' . $safe_border_width;
	$surface_styles[] = 'border-color: ' . $safe_border_color;
	$front_face_styles[] = 'border-style: solid';
	$front_face_styles[] = 'border-width: ' . $safe_border_width;
	$front_face_styles[] = 'border-color: ' . $safe_border_color;
	$back_face_styles[] = 'border-style: solid';
	$back_face_styles[] = 'border-width: ' . $safe_border_width;
	$back_face_styles[] = 'border-color: ' . $safe_border_color;
}

$pt = $to_css_length( $padding_top );
$pr = $to_css_length( $padding_right );
$pb = $to_css_length( $padding_bottom );
$pl = $to_css_length( $padding_left );

if ( '' !== $pt ) {
	$surface_styles[] = 'padding-top: ' . $pt;
	$front_face_styles[] = 'padding-top: ' . $pt;
	$back_face_styles[]  = 'padding-top: ' . $pt;
}
if ( '' !== $pr ) {
	$surface_styles[] = 'padding-right: ' . $pr;
	$front_face_styles[] = 'padding-right: ' . $pr;
	$back_face_styles[]  = 'padding-right: ' . $pr;
}
if ( '' !== $pb ) {
	$surface_styles[] = 'padding-bottom: ' . $pb;
	$front_face_styles[] = 'padding-bottom: ' . $pb;
	$back_face_styles[]  = 'padding-bottom: ' . $pb;
}
if ( '' !== $pl ) {
	$surface_styles[] = 'padding-left: ' . $pl;
	$front_face_styles[] = 'padding-left: ' . $pl;
	$back_face_styles[]  = 'padding-left: ' . $pl;
}

$safe_background_color = $to_css_value( $background_color );
if ( '' !== $safe_background_color ) {
	$surface_styles[]   = 'background-color: ' . $safe_background_color;
	$back_face_styles[] = 'background-color: ' . $safe_background_color;
}

if ( '' !== $bg_image_url ) {
	$surface_styles[]   = 'background-image: url(' . esc_url_raw( $bg_image_url ) . ')';
	$surface_styles[]   = 'background-size: cover';
	$surface_styles[]   = 'background-position: center';
	$surface_styles[]   = 'background-repeat: no-repeat';
	$back_face_styles[] = 'background-image: url(' . esc_url_raw( $bg_image_url ) . ')';
	$back_face_styles[] = 'background-size: cover';
	$back_face_styles[] = 'background-position: center';
	$back_face_styles[] = 'background-repeat: no-repeat';
}

$safe_flip_front_color = $to_css_value( $flip_front_color );
if ( '' !== $safe_flip_front_color ) {
	$front_face_styles[] = 'background-color: ' . $safe_flip_front_color;
}

$safe_flip_front_text_color = $to_css_value( $flip_front_text_color );
if ( '' !== $safe_flip_front_text_color ) {
	$flip_front_text_styles[] = 'color: ' . $safe_flip_front_text_color;
}

if ( '' !== $flip_front_image_url ) {
	$front_face_styles[] = 'background-image: url(' . esc_url_raw( $flip_front_image_url ) . ')';
	$front_face_styles[] = 'background-size: cover';
	$front_face_styles[] = 'background-position: center';
	$front_face_styles[] = 'background-repeat: no-repeat';
}

$title_style_attr       = implode( '; ', $title_styles );
$flip_front_text_style_attr = implode( '; ', $flip_front_text_styles );
$wrapper_style_attr     = implode( '; ', $wrapper_styles );
$surface_style_attr     = implode( '; ', $surface_styles );
$front_face_style_attr  = implode( '; ', $front_face_styles );
$back_face_style_attr   = implode( '; ', $back_face_styles );
$outside_title_styles   = $title_styles;
$outside_title_styles[] = 'width: ' . $resolved_card_width;
$outside_title_style_attr = implode( '; ', $outside_title_styles );

$safe_button_color = $to_css_value( $button_color );
$safe_button_hover_color = $to_css_value( $button_hover_color );
$button_style_value = strtolower( sanitize_text_field( $button_style ) );
$use_default_button = in_array( $button_style_value, [ 'secondary', 'pill', 'default' ], true );

// Backward-compatible fallback: if only custom button colors are provided,
// treat it as the default button variant.
if ( ! $use_default_button && ( '' !== $safe_button_color || '' !== $safe_button_hover_color ) ) {
	$use_default_button = true;
}

$button_classes = $use_default_button
	? 'mpma-card__button mpma-card__button--block mpma-card__button--default inline-flex items-center justify-center'
	: 'mpma-card__button mpma-card__button--block mpma-card__button--primary inline-flex items-center justify-center';

$button_styles = [];
if ( $use_default_button ) {
	if ( '' !== $safe_button_color ) {
		$button_styles[] = '--mpma-card-button-bg: ' . $safe_button_color;
	}

	if ( '' !== $safe_button_hover_color ) {
		$button_styles[] = '--mpma-card-button-hover-bg: ' . $safe_button_hover_color;
	}
}

$button_style_attr = implode( '; ', $button_styles );
?>

<?php if ( 'outside' === $title_position && '' !== $title ) : ?>
	<h3 class="mpma-card__title mpma-card__title--outside mb-4 <?php echo esc_attr( $title_alignment_classes ); ?>"<?php echo '' !== $outside_title_style_attr ? ' style="' . esc_attr( $outside_title_style_attr ) . '"' : ''; ?>>
		<?php echo esc_html( $title ); ?>
	</h3>
<?php endif; ?>

<div class="<?php echo esc_attr( $card_classes ); ?>" style="<?php echo esc_attr( $wrapper_style_attr ); ?>">
	<?php if ( $flippable ) : ?>
		<div class="mpma-card__flip relative h-full w-full">
			<div class="mpma-card__flip-inner h-full w-full">
				<div class="mpma-card__face mpma-card__face--front flex h-full w-full flex-col gap-4 p-4 <?php echo esc_attr( $flip_front_justify_class . ' ' . $alignment_classes ); ?>"<?php echo '' !== $front_face_style_attr ? ' style="' . esc_attr( $front_face_style_attr ) . '"' : ''; ?>>
					<?php if ( '' !== $flip_front_text ) : ?>
						<p class="mpma-card__front-text m-0"<?php echo '' !== $flip_front_text_style_attr ? ' style="' . esc_attr( $flip_front_text_style_attr ) . '"' : ''; ?>><?php echo esc_html( $flip_front_text ); ?></p>
					<?php elseif ( 'inside' === $title_position && '' !== $title ) : ?>
						<h3 class="mpma-card__title"<?php echo '' !== $title_style_attr ? ' style="' . esc_attr( $title_style_attr ) . '"' : ''; ?>>
							<?php echo esc_html( $title ); ?>
						</h3>
					<?php endif; ?>
				</div>

				<div class="mpma-card__face mpma-card__face--back flex h-full w-full flex-col gap-4 p-4 <?php echo esc_attr( $body_justify_class . ' ' . $alignment_classes ); ?>"<?php echo '' !== $back_face_style_attr ? ' style="' . esc_attr( $back_face_style_attr ) . '"' : ''; ?>>
					<?php if ( 'inside' === $title_position && '' !== $title ) : ?>
						<h3 class="mpma-card__title"<?php echo '' !== $title_style_attr ? ' style="' . esc_attr( $title_style_attr ) . '"' : ''; ?>>
							<?php echo esc_html( $title ); ?>
						</h3>
					<?php endif; ?>

					<?php if ( '' !== $content ) : ?>
						<div class="mpma-card__content">
							<?php echo wp_kses_post( $content ); ?>
						</div>
					<?php endif; ?>

					<?php if ( $has_button ) : ?>
						<a class="<?php echo esc_attr( $button_classes ); ?>" href="<?php echo esc_url( $link ); ?>"<?php echo '' !== $button_style_attr ? ' style="' . esc_attr( $button_style_attr ) . '"' : ''; ?>>
							<?php echo esc_html( $button_text ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	<?php else : ?>
		<div class="mpma-card__surface flex h-full w-full flex-col gap-4 p-4 <?php echo esc_attr( $body_justify_class . ' ' . $alignment_classes ); ?>"<?php echo '' !== $surface_style_attr ? ' style="' . esc_attr( $surface_style_attr ) . '"' : ''; ?>>
			<?php if ( 'inside' === $title_position && '' !== $title ) : ?>
				<h3 class="mpma-card__title"<?php echo '' !== $title_style_attr ? ' style="' . esc_attr( $title_style_attr ) . '"' : ''; ?>>
					<?php echo esc_html( $title ); ?>
				</h3>
			<?php endif; ?>

			<?php if ( '' !== $content ) : ?>
				<div class="mpma-card__content">
					<?php echo wp_kses_post( $content ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $has_button ) : ?>
				<a class="<?php echo esc_attr( $button_classes ); ?>" href="<?php echo esc_url( $link ); ?>"<?php echo '' !== $button_style_attr ? ' style="' . esc_attr( $button_style_attr ) . '"' : ''; ?>>
					<?php echo esc_html( $button_text ); ?>
				</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
