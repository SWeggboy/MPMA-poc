<?php
/**
 * MPMA Internal Card block render template.
 *
 * @param array $attributes Block attributes.
 */

if ( ! function_exists( 'tailpress_mpma_internal_card_safe_dimension' ) ) {
	function tailpress_mpma_internal_card_safe_dimension( $value, $fallback ) {
		$value = trim( (string) $value );

		if ( '' === $value ) {
			return $fallback;
		}

		if ( preg_match( '/^(auto|\d+(\.\d+)?(px|%|vh|vw|rem|em))$/', $value ) ) {
			return $value;
		}

		return $fallback;
	}
}

if ( ! function_exists( 'tailpress_mpma_internal_card_safe_length' ) ) {
	function tailpress_mpma_internal_card_safe_length( $value, $fallback ) {
		$value = trim( (string) $value );

		if ( '' === $value ) {
			return $fallback;
		}

		if ( preg_match( '/^\d+(\.\d+)?(px|%|vh|vw|rem|em)$/', $value ) ) {
			return $value;
		}

		return $fallback;
	}
}

if ( ! function_exists( 'tailpress_mpma_internal_card_format_body' ) ) {
	function tailpress_mpma_internal_card_format_body( $body ) {
		$body = trim( (string) $body );

		if ( '' === $body ) {
			return '';
		}

		if ( preg_match( '/<(p|ul|ol|li|br|div|strong|em|a)\b/i', $body ) ) {
			return wp_kses_post( $body );
		}

		$lines = preg_split( '/\r\n|\r|\n/', $body );
		$output = '';
		$list_items = array();
		$paragraph_lines = array();

		$flush_paragraph = static function() use ( &$paragraph_lines, &$output ) {
			if ( empty( $paragraph_lines ) ) {
				return;
			}

			$output .= '<p>' . esc_html( implode( ' ', $paragraph_lines ) ) . '</p>';
			$paragraph_lines = array();
		};

		$flush_list = static function() use ( &$list_items, &$output ) {
			if ( empty( $list_items ) ) {
				return;
			}

			$output .= '<ul>';
			foreach ( $list_items as $item ) {
				$output .= '<li>' . esc_html( $item ) . '</li>';
			}
			$output .= '</ul>';
			$list_items = array();
		};

		foreach ( $lines as $line ) {
			$trimmed = trim( $line );

			if ( '' === $trimmed ) {
				$flush_paragraph();
				$flush_list();
				continue;
			}

			if ( preg_match( '/^[-*•]\s+(.+)$/u', $trimmed, $matches ) ) {
				$flush_paragraph();
				$list_items[] = $matches[1];
				continue;
			}

			$flush_list();
			$paragraph_lines[] = $trimmed;
		}

		$flush_paragraph();
		$flush_list();

		return $output;
	}
}

$width_columns = isset( $attributes['widthColumns'] ) ? (int) $attributes['widthColumns'] : 4;
$width_columns = max( 1, min( 12, $width_columns ) );
$photo_only = ! empty( $attributes['photoOnly'] );
$card_alignment = sanitize_key( $attributes['cardAlignment'] ?? 'center' );
$card_height = tailpress_mpma_internal_card_safe_dimension( $attributes['cardHeight'] ?? '455px', '455px' );
$border_radius = tailpress_mpma_internal_card_safe_length( $attributes['borderRadius'] ?? '1.5rem', '1.5rem' );
$animation_speed = isset( $attributes['animationSpeed'] ) ? (int) $attributes['animationSpeed'] : 400;
$animation_speed = max( 80, min( 2000, $animation_speed ) );
$content_padding_top = tailpress_mpma_internal_card_safe_length( $attributes['contentPaddingTop'] ?? '1.75rem', '1.75rem' );
$content_padding_right = tailpress_mpma_internal_card_safe_length( $attributes['contentPaddingRight'] ?? '1.75rem', '1.75rem' );
$content_padding_bottom = tailpress_mpma_internal_card_safe_length( $attributes['contentPaddingBottom'] ?? '1.75rem', '1.75rem' );
$content_padding_left = tailpress_mpma_internal_card_safe_length( $attributes['contentPaddingLeft'] ?? '1.75rem', '1.75rem' );
$title_font_size = tailpress_mpma_internal_card_safe_length( $attributes['titleFontSize'] ?? '1.5rem', '1.5rem' );
$body_font_size = tailpress_mpma_internal_card_safe_length( $attributes['bodyFontSize'] ?? '1rem', '1rem' );
$drop_shadow = array_key_exists( 'dropShadow', $attributes ) ? (bool) $attributes['dropShadow'] : true;
$flippable = ! empty( $attributes['flippable'] );
$stretch_content = array_key_exists( 'stretchContent', $attributes ) ? (bool) $attributes['stretchContent'] : true;
$vertical_alignment = sanitize_key( $attributes['verticalAlignment'] ?? 'top' );
$horizontal_alignment = sanitize_key( $attributes['horizontalAlignment'] ?? 'center' );
$button_treatment = sanitize_key( $attributes['buttonTreatment'] ?? 'primary' );

if ( ! in_array( $card_alignment, array( 'left', 'center', 'right' ), true ) ) {
	$card_alignment = 'center';
}

if ( ! in_array( $vertical_alignment, array( 'top', 'center', 'bottom' ), true ) ) {
	$vertical_alignment = 'top';
}

if ( ! in_array( $horizontal_alignment, array( 'left', 'center', 'right' ), true ) ) {
	$horizontal_alignment = 'center';
}

if ( ! in_array( $button_treatment, array( 'primary', 'secondary' ), true ) ) {
	$button_treatment = 'primary';
}

$build_side = static function( string $prefix ) use ( $attributes, $photo_only ): array {
	$background_color = sanitize_hex_color( (string) ( $attributes[ $prefix . 'BackgroundColor' ] ?? '#ffffff' ) );

	return array(
		'title' => (string) ( $attributes[ $prefix . 'Title' ] ?? '' ),
		'body' => (string) ( $attributes[ $prefix . 'Body' ] ?? '' ),
		'button_text' => (string) ( $attributes[ $prefix . 'ButtonText' ] ?? '' ),
		'button_url' => trim( esc_url_raw( (string) ( $attributes[ $prefix . 'ButtonUrl' ] ?? '' ) ) ),
		'background_image' => trim( esc_url_raw( (string) ( $attributes[ $prefix . 'BackgroundImage' ] ?? '' ) ) ),
		'background_color' => $background_color ?: '#ffffff',
		'title_color' => sanitize_hex_color( (string) ( $attributes[ $prefix . 'TitleColor' ] ?? '#000000' ) ) ?: '#000000',
		'body_color' => sanitize_hex_color( (string) ( $attributes[ $prefix . 'BodyColor' ] ?? '#000000' ) ) ?: '#000000',
		'photo_only' => array_key_exists( $prefix . 'PhotoOnly', $attributes ) ? (bool) $attributes[ $prefix . 'PhotoOnly' ] : $photo_only,
		'title_font_size' => tailpress_mpma_internal_card_safe_length( $attributes[ $prefix . 'TitleFontSize' ] ?? '', '' ),
		'body_font_size' => tailpress_mpma_internal_card_safe_length( $attributes[ $prefix . 'BodyFontSize' ] ?? '', '' ),
		'vertical_alignment' => sanitize_key( $attributes[ $prefix . 'VerticalAlignment' ] ?? '' ),
		'horizontal_alignment' => sanitize_key( $attributes[ $prefix . 'HorizontalAlignment' ] ?? '' ),
		'button_treatment' => sanitize_key( $attributes[ $prefix . 'ButtonTreatment' ] ?? '' ),
	);
};

$front = $build_side( 'front' );
$back = $build_side( 'back' );
$has_back_content = $flippable && (
	'' !== trim( wp_strip_all_tags( $back['title'] . ' ' . $back['body'] . ' ' . $back['button_text'] ) )
	|| '' !== $back['background_image']
	|| $back['photo_only']
);

$wrapper_classes = array(
	'mpma-internal-card',
	'mpma-internal-card--position-' . $card_alignment,
);

if ( $drop_shadow ) {
	$wrapper_classes[] = 'mpma-internal-card--has-drop-shadow';
}

if ( $stretch_content ) {
	$wrapper_classes[] = 'mpma-internal-card--stretch-content';
}

if ( $front['photo_only'] || $back['photo_only'] ) {
	$wrapper_classes[] = 'mpma-internal-card--photo-only';
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
		'style' => sprintf(
			'--mpma-internal-card-columns:%1$d;--mpma-internal-card-height:%2$s;--mpma-internal-card-radius:%3$s;--mpma-internal-card-fade-speed:%4$dms;--mpma-internal-card-padding-top:%5$s;--mpma-internal-card-padding-right:%6$s;--mpma-internal-card-padding-bottom:%7$s;--mpma-internal-card-padding-left:%8$s;--mpma-internal-card-title-size:%9$s;--mpma-internal-card-body-size:%10$s;',
			$width_columns,
			esc_attr( $card_height ),
			esc_attr( $border_radius ),
			$animation_speed,
			esc_attr( $content_padding_top ),
			esc_attr( $content_padding_right ),
			esc_attr( $content_padding_bottom ),
			esc_attr( $content_padding_left ),
			esc_attr( $title_font_size ),
			esc_attr( $body_font_size )
		),
	)
);

$render_button = static function( array $side, string $button_treatment ) {
	$button_text = trim( (string) $side['button_text'] );
	if ( '' === $button_text ) {
		return;
	}

	$button_wrapper_classes = array( 'wp-block-button', 'mpma-internal-card__button-wrap', 'mpma-internal-card__button-wrap--full' );
	$button_classes = 'wp-block-button__link wp-element-button mpma-internal-card__button mpma-internal-card__button--full mpma-internal-card__button--' . $button_treatment;

	if ( 'secondary' === $button_treatment ) {
		$button_wrapper_classes[] = 'is-style-mpma-secondary';
	}
	$label = esc_html( $button_text );

	if ( '' !== $side['button_url'] ) {
		printf(
			'<div class="%1$s"><a class="%2$s" href="%3$s">%4$s</a></div>',
			esc_attr( implode( ' ', $button_wrapper_classes ) ),
			esc_attr( $button_classes ),
			esc_url( $side['button_url'] ),
			$label
		);
		return;
	}

	printf(
		'<div class="%1$s"><span class="%2$s">%3$s</span></div>',
		esc_attr( implode( ' ', $button_wrapper_classes ) ),
		esc_attr( $button_classes ),
		$label
	);
};

$render_side = static function( array $side, string $slug ) use ( $render_button, $button_treatment, $stretch_content, $vertical_alignment, $horizontal_alignment ) {
	$styles = array(
		'background-color:' . $side['background_color'],
	);
	$side_vertical_alignment = in_array( $side['vertical_alignment'], array( 'top', 'center', 'bottom' ), true ) ? $side['vertical_alignment'] : $vertical_alignment;
	$side_horizontal_alignment = in_array( $side['horizontal_alignment'], array( 'left', 'center', 'right' ), true ) ? $side['horizontal_alignment'] : $horizontal_alignment;
	$side_button_treatment = in_array( $side['button_treatment'], array( 'primary', 'secondary' ), true ) ? $side['button_treatment'] : $button_treatment;
	$has_body = '' !== trim( wp_strip_all_tags( $side['body'] ) );
	$has_button = '' !== trim( wp_strip_all_tags( $side['button_text'] ) );
	$stretch_has_flexible_content = $stretch_content && ( $has_body || $has_button );
	$body_html = tailpress_mpma_internal_card_format_body( $side['body'] );
	$content_styles = array(
		'align-items:' . (
			'left' === $side_horizontal_alignment ? 'flex-start' :
			( 'right' === $side_horizontal_alignment ? 'flex-end' : 'center' )
		),
		'text-align:' . $side_horizontal_alignment,
		'justify-content:' . (
			$stretch_has_flexible_content ? 'flex-start' :
			( 'bottom' === $side_vertical_alignment ? 'flex-end' : ( 'center' === $side_vertical_alignment ? 'center' : 'flex-start' ) )
		),
	);
	$action_styles = array(
		'justify-content:' . (
			'left' === $side_horizontal_alignment ? 'flex-start' :
			( 'right' === $side_horizontal_alignment ? 'flex-end' : 'center' )
		),
	);

	if ( $stretch_has_flexible_content ) {
		$content_styles[] = 'height:100%';
		$action_styles[] = 'margin-top:auto';
	}

	if ( '' !== $side['background_image'] ) {
		$styles[] = 'background-image:url(' . esc_url( $side['background_image'] ) . ')';
	}
	?>
	<div
		class="mpma-internal-card__side mpma-internal-card__side--<?php echo esc_attr( $slug ); ?>"
		data-internal-card-side="<?php echo esc_attr( $slug ); ?>"
		aria-hidden="<?php echo 'front' === $slug ? 'false' : 'true'; ?>"
		style="<?php echo esc_attr( implode( ';', $styles ) . ';' ); ?>"
	>
		<?php if ( ! $side['photo_only'] ) : ?>
			<div class="mpma-internal-card__overlay"></div>
			<div class="mpma-internal-card__content" style="<?php echo esc_attr( implode( ';', $content_styles ) . ';' ); ?>">
				<?php if ( '' !== trim( $side['title'] ) ) : ?>
					<h3 class="mpma-internal-card__title" style="color:<?php echo esc_attr( $side['title_color'] ); ?>;<?php echo '' !== $side['title_font_size'] ? 'font-size:' . esc_attr( $side['title_font_size'] ) . ';' : ''; ?>"><?php echo wp_kses_post( $side['title'] ); ?></h3>
				<?php endif; ?>

				<?php if ( '' !== $body_html ) : ?>
					<div class="mpma-internal-card__body" style="color:<?php echo esc_attr( $side['body_color'] ); ?>;<?php echo '' !== $side['body_font_size'] ? 'font-size:' . esc_attr( $side['body_font_size'] ) . ';' : ''; ?><?php echo $stretch_has_flexible_content ? 'flex:1 1 auto;' : ''; ?>"><?php echo $body_html; ?></div>
				<?php endif; ?>

				<?php if ( '' !== trim( $side['button_text'] ) ) : ?>
					<div class="mpma-internal-card__actions" style="<?php echo esc_attr( implode( ';', $action_styles ) . ';' ); ?>">
						<?php $render_button( $side, $side_button_treatment ); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
};
?>

<div <?php echo $wrapper_attributes; ?> data-mpma-internal-card data-has-back="<?php echo $has_back_content ? '1' : '0'; ?>">
	<div class="mpma-internal-card__surface" data-internal-card-surface <?php if ( $has_back_content ) : ?>tabindex="0"<?php endif; ?>>
		<?php $render_side( $front, 'front' ); ?>
		<?php if ( $has_back_content ) : ?>
			<?php $render_side( $back, 'back' ); ?>
		<?php endif; ?>
	</div>
</div>
