<?php

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only.\n");
    exit(1);
}

require_once __DIR__ . '/../wp-load.php';

$args = $argv;
array_shift($args);

$options = array(
    'xml' => '',
    'apply' => false,
    'limit' => 0,
    'batch' => '',
    'rollback_batch' => '',
    'skip_backup' => false,
);

foreach ($args as $arg) {
    if ('--apply' === $arg) {
        $options['apply'] = true;
        continue;
    }

    if ('--skip-backup' === $arg) {
        $options['skip_backup'] = true;
        continue;
    }

    if (str_starts_with($arg, '--xml=')) {
        $options['xml'] = substr($arg, 6);
        continue;
    }

    if (str_starts_with($arg, '--limit=')) {
        $options['limit'] = (int) substr($arg, 8);
        continue;
    }

    if (str_starts_with($arg, '--batch=')) {
        $options['batch'] = substr($arg, 8);
        continue;
    }

    if (str_starts_with($arg, '--rollback-batch=')) {
        $options['rollback_batch'] = substr($arg, 17);
        continue;
    }
}

if ('' !== $options['rollback_batch']) {
    $posts = get_posts(array(
        'post_type' => 'post',
        'post_status' => 'any',
        'numberposts' => -1,
        'fields' => 'ids',
        'meta_key' => '_agma_posts_import_batch',
        'meta_value' => $options['rollback_batch'],
    ));

    foreach ($posts as $post_id) {
        wp_delete_post((int) $post_id, true);
    }

    echo 'rolled_back=' . count($posts) . "\n";
    exit(0);
}

if ('' === $options['xml']) {
    fwrite(STDERR, "Usage: php scripts/agma_posts_import.php --xml=/path/file.xml [--apply] [--limit=10] [--batch=name] [--rollback-batch=name]\n");
    exit(1);
}

if (!file_exists($options['xml'])) {
    fwrite(STDERR, "XML not found: {$options['xml']}\n");
    exit(1);
}

if ('' === $options['batch']) {
    $options['batch'] = 'agma-posts-import-' . gmdate('Ymd-His');
}

$category_map = array(
    'press-release' => array(
        'name' => 'Press Releases',
        'slug' => 'press-releases',
    ),
    'member-news' => array(
        'name' => 'Member News',
        'slug' => 'member-news',
    ),
    'job-opening' => array(
        'name' => 'Job Openings',
        'slug' => 'job-openings',
    ),
    'trade-tariffs' => array(
        'name' => 'Trade & Tariffs',
        'slug' => 'trade-tariffs',
    ),
);

function mpma_posts_import_strip_light_text_attrs(array $block): array
{
    if (!empty($block['attrs']['textColor']) && 'white' === $block['attrs']['textColor']) {
        unset($block['attrs']['textColor']);
    }

    if (!empty($block['attrs']['style']['color']['text']) && 'var:preset|color|white' === $block['attrs']['style']['color']['text']) {
        unset($block['attrs']['style']['color']['text']);
    }

    if (!empty($block['attrs']['style']['elements']['link']['color']['text']) && 'var:preset|color|white' === $block['attrs']['style']['elements']['link']['color']['text']) {
        unset($block['attrs']['style']['elements']['link']['color']['text']);
    }

    if (isset($block['attrs']['className'])) {
        $classes = preg_split('/\s+/', trim((string) $block['attrs']['className']));
        $classes = array_values(array_filter($classes, static fn($class) => !in_array($class, array('has-white-color', 'has-text-color'), true)));
        if ($classes) {
            $block['attrs']['className'] = implode(' ', $classes);
        } else {
            unset($block['attrs']['className']);
        }
    }

    foreach (array('innerHTML', 'innerContent') as $field) {
        if ('innerHTML' === $field && is_string($block[$field] ?? null)) {
            $block[$field] = str_replace(
                array(' has-white-color has-text-color', 'has-white-color has-text-color ', 'has-white-color', 'has-text-color'),
                '',
                $block[$field]
            );
        }

        if ('innerContent' === $field && is_array($block[$field] ?? null)) {
            foreach ($block[$field] as $index => $content) {
                if (is_string($content)) {
                    $block[$field][$index] = str_replace(
                        array(' has-white-color has-text-color', 'has-white-color has-text-color ', 'has-white-color', 'has-text-color'),
                        '',
                        $content
                    );
                }
            }
        }
    }

    return $block;
}

function mpma_posts_import_extract_image_data_from_html(string $html): array
{
    $image_src = '';
    $image_alt = '';

    if (preg_match('/<img[^>]+src="([^"]+)"/i', $html, $matches)) {
        $image_src = html_entity_decode($matches[1]);
    }

    if (preg_match('/<img[^>]+alt="([^"]*)"/i', $html, $matches)) {
        $image_alt = html_entity_decode($matches[1]);
    }

    return array($image_src, $image_alt);
}

function mpma_posts_import_render_content_blocks(array $blocks): string
{
    $html = '';

    foreach ($blocks as $block) {
        $html .= render_block(mpma_posts_import_strip_light_text_attrs($block));
    }

    return str_replace(
        array(' has-white-color has-text-color', 'has-white-color has-text-color ', 'has-white-color', 'has-text-color'),
        '',
        $html
    );
}

function mpma_posts_import_build_legacy_html_block(string $image_src, string $image_alt, string $content_html): array
{
    $image_markup = '';
    if ('' !== $image_src) {
        $image_markup = '<figure class="mpma-legacy-media-text__media"><img src="' . esc_url($image_src) . '" alt="' . esc_attr($image_alt) . '" class="mpma-legacy-media-text__image" /></figure>';
    }

    $html = '<section class="mpma-legacy-media-text">' . $image_markup . '<div class="mpma-legacy-media-text__content">' . $content_html . '</div></section>';

    return array(
        'blockName' => 'core/html',
        'attrs' => array(),
        'innerBlocks' => array(),
        'innerHTML' => $html,
        'innerContent' => array($html),
    );
}

function mpma_posts_import_normalize_blocks(array $blocks): array
{
    $normalized = array();

    foreach ($blocks as $block) {
        if (($block['blockName'] ?? '') === 'agma/media-text') {
            [$image_src, $image_alt] = mpma_posts_import_extract_image_data_from_html((string) ($block['innerHTML'] ?? ''));
            $normalized[] = mpma_posts_import_build_legacy_html_block(
                $image_src,
                $image_alt,
                mpma_posts_import_render_content_blocks($block['innerBlocks'] ?? array())
            );
            continue;
        }

        if (!empty($block['innerBlocks'])) {
            $block['innerBlocks'] = mpma_posts_import_normalize_blocks($block['innerBlocks']);
        }

        $normalized[] = mpma_posts_import_strip_light_text_attrs($block);
    }

    return $normalized;
}

function mpma_posts_import_normalize_content(string $content): string
{
    if ('' === trim($content)) {
        return $content;
    }

    $blocks = parse_blocks($content);
    if (!$blocks) {
        return $content;
    }

    return serialize_blocks(mpma_posts_import_normalize_blocks($blocks));
}

function mpma_posts_import_ensure_categories(array $category_map): array
{
    $term_ids = array();

    foreach ($category_map as $source_slug => $target) {
        $term = get_term_by('slug', $target['slug'], 'category');
        if (!$term instanceof WP_Term) {
            $created = wp_insert_term($target['name'], 'category', array('slug' => $target['slug']));
            if (is_wp_error($created)) {
                throw new RuntimeException($created->get_error_message());
            }

            $term_ids[$source_slug] = (int) $created['term_id'];
            continue;
        }

        $term_ids[$source_slug] = (int) $term->term_id;
    }

    return $term_ids;
}

$term_ids = mpma_posts_import_ensure_categories($category_map);

if ($options['apply'] && !$options['skip_backup']) {
    $backup_path = ABSPATH . 'tmp/pre-agma-posts-import-' . $options['batch'] . '.sql';
    $command = escapeshellcmd('/Applications/XAMPP/xamppfiles/bin/mysqldump')
        . ' --socket=' . escapeshellarg('/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock')
        . ' -u root '
        . escapeshellarg(DB_NAME)
        . ' > '
        . escapeshellarg($backup_path);

    exec($command, $output, $exit_code);
    if (0 !== $exit_code) {
        fwrite(STDERR, "Database backup failed.\n");
        exit(1);
    }

    echo 'backup=' . $backup_path . "\n";
}

$xml = simplexml_load_file($options['xml'], 'SimpleXMLElement', LIBXML_NOCDATA);
if (!$xml instanceof SimpleXMLElement) {
    fwrite(STDERR, "Failed to read XML.\n");
    exit(1);
}

$namespaces = $xml->getNamespaces(true);
$channel = $xml->channel;
$items = $channel->item;

$processed = 0;
$created = 0;
$updated = 0;
$summary = array();

foreach ($items as $item) {
    $wp = $item->children($namespaces['wp']);
    $content_ns = $item->children($namespaces['content']);
    $excerpt_ns = $item->children($namespaces['excerpt']);

    if ('post' !== (string) $wp->post_type || 'publish' !== (string) $wp->status) {
        continue;
    }

    $source_categories = array();
    foreach ($item->category as $category_node) {
        if ('category' !== (string) $category_node['domain']) {
            continue;
        }

        $slug = (string) $category_node['nicename'];
        if (isset($category_map[$slug])) {
            $source_categories[] = $slug;
        }
    }

    $source_categories = array_values(array_unique($source_categories));
    if (!$source_categories) {
        continue;
    }

    $processed++;
    if ($options['limit'] > 0 && $processed > $options['limit']) {
        break;
    }

    $source_id = (int) $wp->post_id;
    $slug = sanitize_title((string) $wp->post_name ?: (string) $item->title);
    $existing = get_posts(array(
        'post_type' => 'post',
        'post_status' => 'any',
        'numberposts' => 1,
        'fields' => 'ids',
        'meta_key' => '_agma_posts_import_source_id',
        'meta_value' => (string) $source_id,
    ));

    if (!$existing) {
        $existing = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'any',
            'name' => $slug,
            'numberposts' => 1,
            'fields' => 'ids',
        ));
    }

    $target_category_ids = array_map(static fn(string $source_slug): int => $term_ids[$source_slug], $source_categories);

    $postarr = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'post_title' => wp_slash((string) $item->title),
        'post_name' => $slug,
        'post_excerpt' => wp_slash((string) $excerpt_ns->encoded),
        'post_content' => wp_slash(mpma_posts_import_normalize_content((string) $content_ns->encoded)),
        'post_date' => (string) $wp->post_date,
        'post_date_gmt' => (string) $wp->post_date_gmt,
        'post_category' => $target_category_ids,
    );

    $action = 'create';
    if ($existing) {
        $postarr['ID'] = (int) $existing[0];
        $action = 'update';
    }

    $summary[] = array(
        'source_id' => $source_id,
        'slug' => $slug,
        'action' => $action,
        'categories' => implode(',', $source_categories),
        'title' => (string) $item->title,
    );

    if (!$options['apply']) {
        continue;
    }

    $post_id = 'update' === $action ? wp_update_post($postarr, true) : wp_insert_post($postarr, true);
    if (is_wp_error($post_id)) {
        fwrite(STDERR, 'Failed: ' . $postarr['post_title'] . ' :: ' . $post_id->get_error_message() . "\n");
        continue;
    }

    update_post_meta((int) $post_id, '_agma_posts_import_source_id', $source_id);
    update_post_meta((int) $post_id, '_agma_posts_import_batch', $options['batch']);
    update_post_meta((int) $post_id, '_agma_posts_import_source_slug', $slug);
    update_post_meta((int) $post_id, '_agma_posts_import_source_url', (string) $item->link);

    wp_set_post_categories((int) $post_id, $target_category_ids, false);

    if ('create' === $action) {
        $created++;
    } else {
        $updated++;
    }
}

foreach ($summary as $row) {
    echo implode("\t", array(
        $row['action'],
        $row['source_id'],
        $row['slug'],
        $row['categories'],
        $row['title'],
    )) . "\n";
}

echo 'processed=' . count($summary) . "\n";
if ($options['apply']) {
    echo 'created=' . $created . "\n";
    echo 'updated=' . $updated . "\n";
    echo 'batch=' . $options['batch'] . "\n";
}
