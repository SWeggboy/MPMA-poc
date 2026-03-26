<?php

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only.\n");
    exit(1);
}

require_once __DIR__ . '/../wp-load.php';

$args = array_slice($argv, 1);
$apply = in_array('--apply', $args, true);
$all = in_array('--all', $args, true);
$post_id = null;

foreach ($args as $arg) {
    if (str_starts_with($arg, '--post=')) {
        $post_id = (int) substr($arg, strlen('--post='));
    }
}

if (!$all && !$post_id) {
    fwrite(STDERR, "Usage: php scripts/convert_agma_media_text_blocks.php [--post=ID|--all] [--apply]\n");
    exit(1);
}

function mpma_legacy_strip_light_text_attrs(array $block): array
{
    if (!empty($block['attrs']['textColor']) && 'white' === $block['attrs']['textColor']) {
        unset($block['attrs']['textColor']);
    }

    if (!empty($block['attrs']['style']['elements']['link']['color']['text']) && 'var:preset|color|white' === $block['attrs']['style']['elements']['link']['color']['text']) {
        unset($block['attrs']['style']['elements']['link']['color']['text']);
    }

    if (!empty($block['attrs']['style']['color']['text']) && 'var:preset|color|white' === $block['attrs']['style']['color']['text']) {
        unset($block['attrs']['style']['color']['text']);
    }

    if (isset($block['attrs']['className'])) {
        $classes = preg_split('/\s+/', trim((string) $block['attrs']['className']));
        $classes = array_values(array_filter($classes, static fn($class) => !in_array($class, array(
            'has-white-color',
            'has-text-color',
        ), true)));
        if ($classes) {
            $block['attrs']['className'] = implode(' ', $classes);
        } else {
            unset($block['attrs']['className']);
        }
    }

    if (!empty($block['innerHTML'])) {
        $block['innerHTML'] = str_replace(
            array(
                ' has-white-color has-text-color',
                'has-white-color has-text-color ',
                'has-white-color',
                'has-text-color',
            ),
            '',
            $block['innerHTML']
        );
    }

    if (!empty($block['innerContent']) && is_array($block['innerContent'])) {
        foreach ($block['innerContent'] as $index => $content) {
            if (!is_string($content)) {
                continue;
            }

            $block['innerContent'][$index] = str_replace(
                array(
                    ' has-white-color has-text-color',
                    'has-white-color has-text-color ',
                    'has-white-color',
                    'has-text-color',
                ),
                '',
                $content
            );
        }
    }

    return $block;
}

function mpma_legacy_extract_image_data_from_html(string $html): array
{
    $image_src = '';
    $image_alt = '';
    $image_id = 0;

    if (preg_match('/<img[^>]+src="([^"]+)"/i', $html, $src_matches)) {
        $image_src = html_entity_decode($src_matches[1]);
    }

    if (preg_match('/<img[^>]+alt="([^"]*)"/i', $html, $alt_matches)) {
        $image_alt = html_entity_decode($alt_matches[1]);
    }

    if (preg_match('/wp-image-(\d+)/i', $html, $id_matches)) {
        $image_id = (int) $id_matches[1];
    }

    return array($image_src, $image_alt, $image_id);
}

function mpma_legacy_render_content_blocks(array $blocks): string
{
    $html = '';

    foreach ($blocks as $content_block) {
        $content_block = mpma_legacy_strip_light_text_attrs($content_block);
        $html .= render_block($content_block);
    }

    return str_replace(
        array(
            ' has-white-color has-text-color',
            'has-white-color has-text-color ',
            'has-white-color',
            'has-text-color',
        ),
        '',
        $html
    );
}

function mpma_legacy_build_html_block(string $image_src, string $image_alt, string $content_html): array
{
    $image_markup = '';
    if ('' !== $image_src) {
        $image_markup = '<figure class="mpma-legacy-media-text__media"><img src="' . esc_url($image_src) . '" alt="' . esc_attr($image_alt) . '" class="mpma-legacy-media-text__image" /></figure>';
    }

    $html = '<section class="mpma-legacy-media-text">' . $image_markup . '<div class="mpma-legacy-media-text__content">' . $content_html . '</div></section>';

    return array(
        'blockName' => 'core/html',
        'attrs' => array(
        ),
        'innerBlocks' => array(),
        'innerHTML' => $html,
        'innerContent' => array($html),
    );
}

function mpma_legacy_build_image_block(string $image_src, string $image_alt, int $image_id = 0): array
{
    $attrs = array(
        'sizeSlug' => 'full',
        'linkDestination' => 'none',
    );

    if ($image_id > 0) {
        $attrs['id'] = $image_id;
        $attrs['className'] = 'wp-image-' . $image_id;
    }

    $figure_classes = array('wp-block-image', 'size-full');
    if ($image_id > 0) {
        $figure_classes[] = 'wp-image-' . $image_id;
    }

    $img_classes = array('mpma-legacy-media-text__image');
    if ($image_id > 0) {
        $img_classes[] = 'wp-image-' . $image_id;
    }

    $html = '<figure class="' . esc_attr(implode(' ', $figure_classes)) . '"><img src="' . esc_url($image_src) . '" alt="' . esc_attr($image_alt) . '" class="' . esc_attr(implode(' ', $img_classes)) . '" /></figure>';

    return array(
        'blockName' => 'core/image',
        'attrs' => $attrs,
        'innerBlocks' => array(),
        'innerHTML' => $html,
        'innerContent' => array($html),
    );
}

function mpma_legacy_transform_media_text_block(array $block): array
{
    [$image_src, $image_alt] = mpma_legacy_extract_image_data_from_html((string) ($block['innerHTML'] ?? ''));
    $content_html = mpma_legacy_render_content_blocks($block['innerBlocks'] ?? array());

    return mpma_legacy_build_html_block($image_src, $image_alt, $content_html);
}

function mpma_legacy_transform_columns_block(array $block): array
{
    $columns = $block['innerBlocks'] ?? array();
    if (count($columns) < 2) {
        return $block;
    }

    [$image_src, $image_alt] = mpma_legacy_extract_image_data_from_html(render_block($columns[0]));

    $content_blocks = $columns[1]['innerBlocks'] ?? array();
    if (isset($content_blocks[0]['blockName']) && 'core/group' === $content_blocks[0]['blockName']) {
        $content_blocks = $content_blocks[0]['innerBlocks'] ?? array();
    }

    $content_html = mpma_legacy_render_content_blocks($content_blocks);

    return mpma_legacy_build_html_block($image_src, $image_alt, $content_html);
}

function mpma_legacy_is_columns_image_block(array $block): bool
{
    if (($block['blockName'] ?? '') !== 'core/html') {
        return false;
    }

    $html = (string) ($block['innerHTML'] ?? '');

    return str_contains($html, '<img') && (
        str_contains($html, 'mpma-legacy-media-text__image')
        || str_contains($html, 'wp-block-image')
    );
}

function mpma_legacy_is_convertible_columns_layout(array $block): bool
{
    if (($block['blockName'] ?? '') !== 'core/columns') {
        return false;
    }

    $columns = $block['innerBlocks'] ?? array();
    if (count($columns) !== 2) {
        return false;
    }

    $left_blocks = $columns[0]['innerBlocks'] ?? array();
    $right_blocks = $columns[1]['innerBlocks'] ?? array();

    if (count($left_blocks) !== 1 || !mpma_legacy_is_columns_image_block($left_blocks[0])) {
        return false;
    }

    if (empty($right_blocks)) {
        return false;
    }

    return true;
}

function mpma_legacy_transform_image_columns_block(array $block): array
{
    $columns = $block['innerBlocks'] ?? array();
    $left_column = $columns[0];
    $right_column = $columns[1];

    [$image_src, $image_alt, $image_id] = mpma_legacy_extract_image_data_from_html((string) ($left_column['innerBlocks'][0]['innerHTML'] ?? ''));

    if ('' !== $image_src) {
        $left_column['innerBlocks'] = array(
            mpma_legacy_build_image_block($image_src, $image_alt, $image_id)
        );
    }

    $right_column['innerBlocks'] = array_map(
        static fn(array $inner_block): array => mpma_legacy_strip_light_text_attrs($inner_block),
        $right_column['innerBlocks'] ?? array()
    );

    $block['innerBlocks'][0] = $left_column;
    $block['innerBlocks'][1] = $right_column;

    $class_name = trim((string) ($block['attrs']['className'] ?? ''));
    $class_tokens = $class_name === '' ? array() : preg_split('/\s+/', $class_name);
    if (!in_array('mpma-legacy-media-text-columns', $class_tokens, true)) {
        $class_tokens[] = 'mpma-legacy-media-text-columns';
    }
    $class_tokens = array_values(array_filter($class_tokens));
    if ($class_tokens) {
        $block['attrs']['className'] = implode(' ', $class_tokens);
    }

    return $block;
}

function mpma_legacy_convert_blocks(array $blocks, int &$converted, bool $convert_existing_legacy = false): array
{
    $result = array();

    foreach ($blocks as $block) {
        if (($block['blockName'] ?? '') === 'agma/media-text') {
            $result[] = mpma_legacy_transform_media_text_block($block);
            $converted++;
            continue;
        }

        if (
            $convert_existing_legacy
            && ($block['blockName'] ?? '') === 'core/columns'
            && !empty($block['attrs']['className'])
            && str_contains((string) $block['attrs']['className'], 'mpma-legacy-media-text')
        ) {
            $result[] = mpma_legacy_transform_columns_block($block);
            $converted++;
            continue;
        }

        if (mpma_legacy_is_convertible_columns_layout($block)) {
            $result[] = mpma_legacy_transform_image_columns_block($block);
            $converted++;
            continue;
        }

        if (!empty($block['innerBlocks'])) {
            $block['innerBlocks'] = mpma_legacy_convert_blocks($block['innerBlocks'], $converted, $convert_existing_legacy);
        }

        $result[] = $block;
    }

    return $result;
}

$post_ids = array();
if ($all) {
    global $wpdb;
    $post_ids = $wpdb->get_col(
        "SELECT ID FROM {$wpdb->posts} WHERE (post_content LIKE '%wp:agma/media-text%' OR post_content LIKE '%mpma-legacy-media-text%') AND post_status NOT IN ('trash','auto-draft')"
    );
} elseif ($post_id) {
    $post_ids = array($post_id);
}

$summary = array();

foreach ($post_ids as $target_post_id) {
    $post = get_post((int) $target_post_id);
    if (!$post instanceof WP_Post) {
        continue;
    }

    $blocks = parse_blocks($post->post_content);
    $converted = 0;
    $new_blocks = mpma_legacy_convert_blocks($blocks, $converted, true);

    if ($converted < 1) {
        continue;
    }

    $new_content = serialize_blocks($new_blocks);

    if ($apply) {
        wp_update_post(array(
            'ID' => $post->ID,
            'post_content' => $new_content,
        ));
        clean_post_cache($post->ID);
    }

    $summary[] = array(
        'ID' => $post->ID,
        'type' => $post->post_type,
        'status' => $post->post_status,
        'title' => $post->post_title,
        'converted' => $converted,
    );
}

foreach ($summary as $row) {
    echo implode("\t", array(
        $row['ID'],
        $row['type'],
        $row['status'],
        $row['converted'],
        $row['title'],
    )) . "\n";
}

echo "posts=" . count($summary) . "\n";
