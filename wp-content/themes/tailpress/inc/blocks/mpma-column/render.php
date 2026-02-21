<?php
/**
 * MPMA Column block render template.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Inner blocks content.
 * @param WP_Block $block      Block instance.
 */

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'mpma-column-block [&_.wp-block-columns]:items-stretch [&_.wp-block-column]:flex [&_.wp-block-column]:flex-col [&_.wp-block-column>*]:w-full [&_.wp-block-column>.wp-block-group]:flex [&_.wp-block-column>.wp-block-group]:h-full [&_.wp-block-column>.mpma-column-item]:flex [&_.wp-block-column>.mpma-column-item]:h-full',
]);
?>

<section <?php echo $wrapper_attributes; ?>>
    <?php echo $content; ?>
</section>
