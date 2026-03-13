<?php
/**
 * Genesis Custom Blocks template: MPMA Text.
 *
 * Mirrors the Homepage Image and Text block using existing core + Genesis blocks.
 */

$image_id           = (int) block_value( 'image' );
$header_raw         = trim( (string) block_value( 'header' ) );
$body_raw           = trim( (string) block_value( 'body' ) );
$button_text        = trim( (string) block_value( 'button-text' ) );
$button_url_raw     = trim( (string) block_value( 'button-url' ) );
$image_position_raw = trim( (string) block_value( 'image-position' ) );
$class_name         = trim( (string) block_value( 'className' ) );

$default_header = 'Advocacy That<br>Drives Industry Forward';
$default_body   = 'MPMA represents the collective interests of the mechanical power transmission industry at the federal level, ensuring that members\' priorities are heard and advanced.';

$normalize_position = static function ( string $value ): string {
	$value = strtolower( trim( $value ) );
	if ( '' === $value ) {
		return 'left';
	}

	if ( 0 === strpos( $value, 'right' ) || false !== strpos( $value, 'right' ) ) {
		return 'right';
	}

	return 'left';
};

$header = '' !== $header_raw ? $header_raw : $default_header;
$body   = '' !== $body_raw ? $body_raw : $default_body;

$image_position = $normalize_position( $image_position_raw );
$button_url     = esc_url_raw( $button_url_raw );

$image_url = '';
if ( $image_id > 0 ) {
	$resolved_image_url = wp_get_attachment_image_url( $image_id, 'full' );
	if ( is_string( $resolved_image_url ) ) {
		$image_url = $resolved_image_url;
	}
}

$heading_content = wp_kses(
	$header,
	array(
		'br' => array(),
	),
);

$body_content = esc_html( $body );

$heading_block = '<!-- wp:heading {"level":2,"className":"!text-[32px] font-bold mb-6 leading-tight !text-primary !font-montserrat"} --><h2 class="wp-block-heading !text-[32px] font-bold mb-6 leading-tight !text-primary !font-montserrat">' . $heading_content . '</h2><!-- /wp:heading -->';
$body_block    = '<!-- wp:paragraph {"className":"text-[16px] leading-relaxed mb-6 font-roboto"} --><p class="text-[16px] leading-relaxed mb-6 font-roboto">' . $body_content . '</p><!-- /wp:paragraph -->';

$button_block = '';
if ( '' !== $button_text && '' !== $button_url ) {
	$button_block = '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $button_url ) . '">' . esc_html( $button_text ) . '</a></div><!-- /wp:button --></div><!-- /wp:buttons -->';
}

$text_column_blocks = $heading_block . $body_block . $button_block;

$image_block = '';
if ( '' !== $image_url ) {
	$image_style = 'width:100%;border-style:solid;border-width:1px 0 3px 3px;border-color:#2E5E47;border-radius:10px;';
	$image_block = '<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"rounded-[10px]"} --><figure class="wp-block-image size-full rounded-[10px]"><img src="' . esc_url( $image_url ) . '" alt="" class="rounded-[10px] max-w-full h-auto" style="' . esc_attr( $image_style ) . '"/></figure><!-- /wp:image -->';
} else {
	$image_block = '<!-- wp:group {"className":"w-full h-96 bg-gray-200 rounded-lg flex items-center justify-center"} --><div class="wp-block-group w-full h-96 bg-gray-200 rounded-lg flex items-center justify-center"><!-- wp:paragraph {"textColor":"gray"} --><p class="has-gray-color has-text-color">Image</p><!-- /wp:paragraph --></div><!-- /wp:group -->';
}

$text_column  = '<!-- wp:genesis-blocks/gb-column --><div class="wp-block-genesis-blocks-gb-column gb-block-layout-column"><div class="gb-block-layout-column-inner">' . $text_column_blocks . '</div></div><!-- /wp:genesis-blocks/gb-column -->';
$image_column = '<!-- wp:genesis-blocks/gb-column --><div class="wp-block-genesis-blocks-gb-column gb-block-layout-column"><div class="gb-block-layout-column-inner">' . $image_block . '</div></div><!-- /wp:genesis-blocks/gb-column -->';

$columns_inner = 'left' === $image_position ? $image_column . $text_column : $text_column . $image_column;

$columns_markup = '<!-- wp:genesis-blocks/gb-columns {"columns":2,"layout":"gb-2-col-equal","columnsGap":5} --><div class="wp-block-genesis-blocks-gb-columns gb-layout-columns-2 gb-2-col-equal"><div class="gb-layout-column-wrap gb-block-layout-column-gap-5 gb-is-responsive-column">' . $columns_inner . '</div></div><!-- /wp:genesis-blocks/gb-columns -->';

$rendered_markup = function_exists( 'do_blocks' ) ? do_blocks( $columns_markup ) : $columns_markup;

$wrapper_classes = 'homepage-image-text bg-white py-8 my-12';
if ( '' !== $class_name ) {
	$wrapper_classes .= ' ' . $class_name;
}
?>

<section class="<?php echo esc_attr( $wrapper_classes ); ?>">
	<div class="container mx-auto px-4">
		<?php echo wp_kses_post( $rendered_markup ); ?>
	</div>
</section>
