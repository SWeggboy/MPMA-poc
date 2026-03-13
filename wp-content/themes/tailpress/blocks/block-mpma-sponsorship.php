<?php
/**
 * Genesis Custom Blocks template: MPMA Sponsorship.
 */

$class_name = trim( (string) block_value( 'className' ) );

$resolve_inner_blocks = static function (): string {
	$field_keys = [
		'content',
		'sponsorship-content',
		'layout',
		'inner-content',
		'block-content',
	];

	foreach ( $field_keys as $field_key ) {
		$value = (string) block_value( $field_key );
		if ( '' !== trim( $value ) ) {
			return $value;
		}
	}

	return '';
};

$default_blocks = <<<'BLOCKS'
<!-- wp:genesis-blocks/gb-columns {"columns":2,"layout":"gb-2-col-equal","columnsGap":5,"marginBottom":1.5,"marginUnit":"rem","className":"mpma-sponsorship__main-columns"} -->
<div class="wp-block-genesis-blocks-gb-columns mpma-sponsorship__main-columns gb-layout-columns-2 gb-2-col-equal" style="margin-bottom:1.5rem"><div class="gb-layout-column-wrap gb-block-layout-column-gap-5 gb-is-responsive-column"><!-- wp:genesis-blocks/gb-column -->
<div class="wp-block-genesis-blocks-gb-column gb-block-layout-column"><div class="gb-block-layout-column-inner"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Sponsorship Opportunities</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Add sponsorship summary copy here.</p>
<!-- /wp:paragraph -->

<!-- wp:accordion -->
<div role="group" class="wp-block-accordion"><!-- wp:accordion-item -->
<div class="wp-block-accordion-item"><!-- wp:accordion-heading -->
<h3 class="wp-block-accordion-heading"><button class="wp-block-accordion-heading__toggle"><span class="wp-block-accordion-heading__toggle-title">MPMA ANNUAL MEETING</span><span class="wp-block-accordion-heading__toggle-icon" aria-hidden="true">+</span></button></h3>
<!-- /wp:accordion-heading -->

<!-- wp:accordion-panel {"style":{"spacing":{"padding":{"top":"1.5rem"}}}} -->
<div role="region" class="wp-block-accordion-panel" style="padding-top:1.5rem"><!-- wp:paragraph -->
<p>Add accordion content here.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:accordion-panel --></div>
<!-- /wp:accordion-item -->

<!-- wp:accordion-item -->
<div class="wp-block-accordion-item"><!-- wp:accordion-heading -->
<h3 class="wp-block-accordion-heading"><button class="wp-block-accordion-heading__toggle"><span class="wp-block-accordion-heading__toggle-title">MPMA ANNUAL MEETING</span><span class="wp-block-accordion-heading__toggle-icon" aria-hidden="true">+</span></button></h3>
<!-- /wp:accordion-heading -->

<!-- wp:accordion-panel {"style":{"spacing":{"padding":{"top":"1.5rem"}}}} -->
<div role="region" class="wp-block-accordion-panel" style="padding-top:1.5rem"><!-- wp:paragraph -->
<p>Add accordion content here.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:accordion-panel --></div>
<!-- /wp:accordion-item -->

<!-- wp:accordion-item -->
<div class="wp-block-accordion-item"><!-- wp:accordion-heading -->
<h3 class="wp-block-accordion-heading"><button class="wp-block-accordion-heading__toggle"><span class="wp-block-accordion-heading__toggle-title">MPMA ANNUAL MEETING</span><span class="wp-block-accordion-heading__toggle-icon" aria-hidden="true">+</span></button></h3>
<!-- /wp:accordion-heading -->

<!-- wp:accordion-panel {"style":{"spacing":{"padding":{"top":"1.5rem"}}}} -->
<div role="region" class="wp-block-accordion-panel" style="padding-top:1.5rem"><!-- wp:paragraph -->
<p>Add accordion content here.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:accordion-panel --></div>
<!-- /wp:accordion-item -->

<!-- wp:accordion-item -->
<div class="wp-block-accordion-item"><!-- wp:accordion-heading -->
<h3 class="wp-block-accordion-heading"><button class="wp-block-accordion-heading__toggle"><span class="wp-block-accordion-heading__toggle-title">MPMA ANNUAL MEETING</span><span class="wp-block-accordion-heading__toggle-icon" aria-hidden="true">+</span></button></h3>
<!-- /wp:accordion-heading -->

<!-- wp:accordion-panel {"style":{"spacing":{"padding":{"top":"1.5rem"}}}} -->
<div role="region" class="wp-block-accordion-panel" style="padding-top:1.5rem"><!-- wp:paragraph -->
<p>Add accordion content here.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:accordion-panel --></div>
<!-- /wp:accordion-item --></div>
<!-- /wp:accordion --></div></div>
<!-- /wp:genesis-blocks/gb-column -->

<!-- wp:genesis-blocks/gb-column {"className":"mpma-sponsorship__media-column"} -->
<div class="wp-block-genesis-blocks-gb-column gb-block-layout-column mpma-sponsorship__media-column"><div class="gb-block-layout-column-inner"><!-- wp:image {"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img alt=""/></figure>
<!-- /wp:image -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button">DOWNLOAD PDF</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:genesis-blocks/gb-column --></div></div>
<!-- /wp:genesis-blocks/gb-columns -->

<!-- wp:genesis-blocks/gb-columns {"columns":1,"layout":"one-column","marginBottom":1.5,"marginUnit":"rem","className":"mpma-sponsorship__footer-columns"} -->
<div class="wp-block-genesis-blocks-gb-columns mpma-sponsorship__footer-columns gb-layout-columns-1 one-column" style="margin-bottom:1.5rem"><div class="gb-layout-column-wrap gb-block-layout-column-gap-2 gb-is-responsive-column"><!-- wp:genesis-blocks/gb-column -->
<div class="wp-block-genesis-blocks-gb-column gb-block-layout-column"><div class="gb-block-layout-column-inner"><!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Add centered supporting copy here.</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:genesis-blocks/gb-column --></div></div>
<!-- /wp:genesis-blocks/gb-columns -->
BLOCKS;

$inner_blocks = $resolve_inner_blocks();
$blocks_markup = '' !== trim( $inner_blocks ) ? $inner_blocks : $default_blocks;
$rendered = function_exists( 'do_blocks' ) ? do_blocks( $blocks_markup ) : '';

if ( '' === trim( $rendered ) ) {
	$rendered = $blocks_markup;
}

$wrapper_classes = 'mpma-sponsorship-block';
if ( '' !== $class_name ) {
	$wrapper_classes .= ' ' . $class_name;
}
?>

<div class="<?php echo esc_attr( $wrapper_classes ); ?>">
	<?php echo wp_kses_post( $rendered ); ?>
</div>
