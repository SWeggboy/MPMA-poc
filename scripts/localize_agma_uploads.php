#!/usr/bin/env php
<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only.\n");
    exit(1);
}

$root = dirname(__DIR__);
$wpLoad = $root . '/wp-load.php';

if (!file_exists($wpLoad)) {
    fwrite(STDERR, "Could not find wp-load.php at {$wpLoad}\n");
    exit(1);
}

require_once $wpLoad;
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

const MPMA_AGMA_UPLOAD_URL_PATTERN = '#https?://(?:www\.)?agma\.org/wp-content/uploads/[^\s"\'<>()]+#i';

function mpma_localize_agma_uploads_usage(): void
{
    $usage = <<<TXT
Usage:
  php scripts/localize_agma_uploads.php [--apply] [--limit=25] [--post-types=post,page,tribe_events] [--include-meta]

Notes:
  - Dry-run is the default mode.
  - In apply mode, each unique AGMA uploads URL is downloaded once, inserted into the media library,
    and all matching post content/meta references are rewritten to the local attachment URL.
TXT;

    fwrite(STDOUT, $usage . "\n");
}

function mpma_localize_agma_uploads_parse_args(): array
{
    $options = getopt('', [
        'apply',
        'limit:',
        'post-types:',
        'include-meta',
        'help',
    ]);

    if (isset($options['help'])) {
        mpma_localize_agma_uploads_usage();
        exit(0);
    }

    $postTypes = ['post', 'page', 'tribe_events'];
    if (!empty($options['post-types'])) {
        $postTypes = array_values(array_filter(array_map('sanitize_key', explode(',', (string) $options['post-types']))));
    }

    if ([] === $postTypes) {
        fwrite(STDERR, "At least one post type is required.\n");
        exit(1);
    }

    return [
        'apply' => isset($options['apply']),
        'limit' => isset($options['limit']) ? max(1, (int) $options['limit']) : 0,
        'post_types' => $postTypes,
        'include_meta' => isset($options['include-meta']),
    ];
}

function mpma_localize_agma_uploads_find_posts(array $postTypes, int $limit = 0): array
{
    global $wpdb;

    $placeholders = implode(',', array_fill(0, count($postTypes), '%s'));
    $sql = "
        SELECT ID, post_type, post_title, post_content
        FROM {$wpdb->posts}
        WHERE post_type IN ($placeholders)
          AND post_status NOT IN ('auto-draft', 'trash', 'inherit')
          AND post_content REGEXP %s
        ORDER BY ID ASC
    ";

    $params = array_merge($postTypes, ['https?://(www\\.)?agma\\.org/wp-content/uploads/']);
    if ($limit > 0) {
        $sql .= ' LIMIT %d';
        $params[] = $limit;
    }

    $prepared = $wpdb->prepare($sql, $params);
    $rows = $wpdb->get_results($prepared, ARRAY_A);

    return is_array($rows) ? $rows : [];
}

function mpma_localize_agma_uploads_extract_urls(string $content): array
{
    if (!preg_match_all(MPMA_AGMA_UPLOAD_URL_PATTERN, $content, $matches)) {
        return [];
    }

    $urls = array_map(
        static function (string $url): string {
            return html_entity_decode($url, ENT_QUOTES | ENT_HTML5);
        },
        $matches[0]
    );

    return array_values(array_unique($urls));
}

function mpma_localize_agma_uploads_find_existing_attachment(string $sourceUrl): int
{
    $existing = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'fields' => 'ids',
        'posts_per_page' => 1,
        'meta_key' => '_mpma_source_url',
        'meta_value' => $sourceUrl,
    ]);

    if (!empty($existing)) {
        return (int) $existing[0];
    }

    return 0;
}

function mpma_localize_agma_uploads_download(string $sourceUrl, int $parentPostId): array
{
    $attachmentId = mpma_localize_agma_uploads_find_existing_attachment($sourceUrl);
    if ($attachmentId > 0) {
        $localUrl = wp_get_attachment_url($attachmentId);
        if (is_string($localUrl) && '' !== $localUrl) {
            return [
                'attachment_id' => $attachmentId,
                'local_url' => $localUrl,
                'created' => false,
            ];
        }
    }

    $tmpFile = download_url($sourceUrl, 30);
    if (is_wp_error($tmpFile)) {
        throw new RuntimeException($tmpFile->get_error_message());
    }

    $urlPath = (string) wp_parse_url($sourceUrl, PHP_URL_PATH);
    $fileName = wp_basename($urlPath);
    if ('' === $fileName) {
        $fileName = 'agma-upload';
    }

    $fileArray = [
        'name' => sanitize_file_name(rawurldecode($fileName)),
        'tmp_name' => $tmpFile,
    ];

    $attachmentId = media_handle_sideload($fileArray, $parentPostId);
    if (is_wp_error($attachmentId)) {
        @unlink($tmpFile);
        throw new RuntimeException($attachmentId->get_error_message());
    }

    update_post_meta($attachmentId, '_mpma_source_url', esc_url_raw($sourceUrl));

    $localUrl = wp_get_attachment_url($attachmentId);
    if (!is_string($localUrl) || '' === $localUrl) {
        throw new RuntimeException('Could not resolve local attachment URL.');
    }

    return [
        'attachment_id' => (int) $attachmentId,
        'local_url' => $localUrl,
        'created' => true,
    ];
}

function mpma_localize_agma_uploads_replace_urls(string $content, array $replacements): string
{
    if ([] === $replacements) {
        return $content;
    }

    return strtr($content, $replacements);
}

function mpma_localize_agma_uploads_update_meta(array $replacements): int
{
    global $wpdb;

    if ([] === $replacements) {
        return 0;
    }

    $metaRows = $wpdb->get_results(
        "SELECT meta_id, meta_value FROM {$wpdb->postmeta} WHERE meta_value REGEXP 'https?://(www\\.)?agma\\.org/wp-content/uploads/'",
        ARRAY_A
    );

    if (!is_array($metaRows)) {
        return 0;
    }

    $updated = 0;

    foreach ($metaRows as $row) {
        $metaId = (int) ($row['meta_id'] ?? 0);
        $metaValue = (string) ($row['meta_value'] ?? '');
        if ($metaId < 1 || '' === $metaValue) {
            continue;
        }

        $newValue = mpma_localize_agma_uploads_replace_urls($metaValue, $replacements);
        if ($newValue === $metaValue) {
            continue;
        }

        $wpdb->update(
            $wpdb->postmeta,
            ['meta_value' => $newValue],
            ['meta_id' => $metaId],
            ['%s'],
            ['%d']
        );
        $updated++;
    }

    return $updated;
}

$options = mpma_localize_agma_uploads_parse_args();
$posts = mpma_localize_agma_uploads_find_posts($options['post_types'], $options['limit']);

$uniqueUrls = [];
$postSummaries = [];

foreach ($posts as $post) {
    $postId = (int) $post['ID'];
    $urls = mpma_localize_agma_uploads_extract_urls((string) $post['post_content']);
    if ([] === $urls) {
        continue;
    }

    foreach ($urls as $url) {
        $uniqueUrls[$url] = true;
    }

    $postSummaries[] = [
        'post_id' => $postId,
        'post_type' => (string) $post['post_type'],
        'post_title' => (string) $post['post_title'],
        'urls' => $urls,
    ];
}

$uniqueUrls = array_keys($uniqueUrls);

echo 'posts_with_matches=' . count($postSummaries) . "\n";
echo 'unique_remote_urls=' . count($uniqueUrls) . "\n";

foreach (array_slice($postSummaries, 0, 10) as $summary) {
    echo sprintf(
        "post=%d\t%s\t%s\turls=%d\n",
        $summary['post_id'],
        $summary['post_type'],
        $summary['post_title'],
        count($summary['urls'])
    );
}

if (!$options['apply']) {
    foreach (array_slice($uniqueUrls, 0, 15) as $url) {
        echo 'remote_url=' . $url . "\n";
    }
    exit(0);
}

$replacements = [];
$downloaded = 0;
$reused = 0;

foreach ($uniqueUrls as $index => $sourceUrl) {
    $parentPostId = 0;
    foreach ($postSummaries as $summary) {
        if (in_array($sourceUrl, $summary['urls'], true)) {
            $parentPostId = (int) $summary['post_id'];
            break;
        }
    }

    $result = mpma_localize_agma_uploads_download($sourceUrl, $parentPostId);
    $replacements[$sourceUrl] = $result['local_url'];
    if ($result['created']) {
        $downloaded++;
    } else {
        $reused++;
    }

    echo sprintf(
        "[%d/%d] %s => %s (%s)\n",
        $index + 1,
        count($uniqueUrls),
        $sourceUrl,
        $result['local_url'],
        $result['created'] ? 'downloaded' : 'reused'
    );
}

$updatedPosts = 0;

foreach ($postSummaries as $summary) {
    $postId = (int) $summary['post_id'];
    $post = get_post($postId);
    if (!$post instanceof WP_Post) {
        continue;
    }

    $newContent = mpma_localize_agma_uploads_replace_urls((string) $post->post_content, $replacements);
    if ($newContent === $post->post_content) {
        continue;
    }

    wp_update_post([
        'ID' => $postId,
        'post_content' => $newContent,
    ]);
    $updatedPosts++;
}

$updatedMeta = 0;
if ($options['include_meta']) {
    $updatedMeta = mpma_localize_agma_uploads_update_meta($replacements);
}

echo 'attachments_downloaded=' . $downloaded . "\n";
echo 'attachments_reused=' . $reused . "\n";
echo 'posts_updated=' . $updatedPosts . "\n";
echo 'meta_rows_updated=' . $updatedMeta . "\n";
