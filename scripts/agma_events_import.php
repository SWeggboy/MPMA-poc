#!/usr/bin/env php
<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$wpLoad = $root . '/wp-load.php';

if (!file_exists($wpLoad)) {
    fwrite(STDERR, "Could not find wp-load.php at {$wpLoad}\n");
    exit(1);
}

require_once $wpLoad;

const AGMA_IMPORT_TERM_MAP = [
    'courses' => 'courses',
    'in-person-courses' => 'in-person-courses',
    'live-online-courses' => 'live-online-courses',
    'on-demand-courses' => 'on-demand-courses',
    'online-workforce-training' => 'online-workforce-training',
    'upcoming-courses' => 'upcoming-courses',
    'webinars' => 'webinars',
    'on-demand-webinars' => 'on-demand-webinars',
    'emerging-technology-on-demand-webinars' => 'on-demand-emerging-technology-webinars',
    'emerging-technology-upcoming-webinars' => 'upcoming-emerging-technology-webinars',
    'trade-on-demand-webinars' => 'on-demand-trade-webinars',
    'trade-webinars' => 'on-demand-trade-webinars',
    'upcoming-webinars' => 'upcoming-webinars',
    'trade-upcoming-webinars' => 'upcoming-trade-webinars',
    'committee-meeting' => 'committee-meetings',
];

const AGMA_IMPORT_EXCLUDED_TERMS = [
    'events',
    'working-group',
];

function agma_import_usage(): void
{
    $usage = <<<TXT
Usage:
  php scripts/agma_events_import.php --xml=/path/to/export.xml [--verify-live] [--limit=25]
  php scripts/agma_events_import.php --xml=/path/to/export.xml --apply [--batch=agma-20260324-120000] [--verify-live] [--limit=25] [--skip-backup]
  php scripts/agma_events_import.php --rollback-batch=agma-20260324-120000

Notes:
  - Dry-run is the default mode when --apply is omitted.
  - Apply mode imports only publish-status event posts that map to allowed categories.
  - Rollback deletes tribe_events posts tagged with the given batch id.
TXT;

    fwrite(STDOUT, $usage . "\n");
}

function agma_import_fail(string $message): void
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}

function agma_import_parse_args(): array
{
    $options = getopt('', [
        'xml:',
        'apply',
        'rollback-batch:',
        'verify-live',
        'batch:',
        'limit:',
        'skip-backup',
        'help',
    ]);

    if (isset($options['help'])) {
        agma_import_usage();
        exit(0);
    }

    $isApply = isset($options['apply']);
    $rollbackBatch = $options['rollback-batch'] ?? '';
    $xmlPath = $options['xml'] ?? '';

    if ('' !== $rollbackBatch && '' !== $xmlPath) {
        agma_import_fail('Use either --rollback-batch or --xml, not both.');
    }

    if ('' === $rollbackBatch && '' === $xmlPath) {
        agma_import_usage();
        agma_import_fail('Missing required --xml or --rollback-batch option.');
    }

    return [
        'xml_path' => $xmlPath,
        'apply' => $isApply,
        'rollback_batch' => $rollbackBatch,
        'verify_live' => isset($options['verify-live']),
        'batch' => $options['batch'] ?? '',
        'limit' => isset($options['limit']) ? max(1, (int) $options['limit']) : 0,
        'skip_backup' => isset($options['skip-backup']),
    ];
}

function agma_import_load_events(string $xmlPath, bool $verifyLive = false): array
{
    if (!file_exists($xmlPath)) {
        agma_import_fail("XML file not found: {$xmlPath}");
    }

    libxml_use_internal_errors(true);
    $xml = simplexml_load_file($xmlPath, 'SimpleXMLElement', LIBXML_NOCDATA);
    if (!$xml || !isset($xml->channel)) {
        agma_import_fail("Unable to parse XML file: {$xmlPath}");
    }

    $channel = $xml->channel;
    $items = $channel->item;
    $events = [];

    foreach ($items as $item) {
        $event = agma_import_parse_item($item, $verifyLive);
        if (null === $event) {
            continue;
        }

        $events[] = $event;
    }

    return $events;
}

function agma_import_parse_item(SimpleXMLElement $item, bool $verifyLive = false): ?array
{
    $namespaces = $item->getNamespaces(true);
    $wp = $item->children($namespaces['wp']);
    $contentNs = $item->children($namespaces['content']);
    $excerptNs = $item->children($namespaces['excerpt']);

    if ('event' !== trim((string) $wp->post_type)) {
        return null;
    }

    if ('publish' !== trim((string) $wp->status)) {
        return null;
    }

    $sourceTerms = [];
    $mappedTerms = [];
    foreach ($item->category as $category) {
        $attrs = $category->attributes();
        if ('event-category' !== (string) $attrs['domain']) {
            continue;
        }

        $slug = trim((string) $attrs['nicename']);
        if ('' === $slug) {
            continue;
        }

        $sourceTerms[] = $slug;

        if (in_array($slug, AGMA_IMPORT_EXCLUDED_TERMS, true)) {
            continue;
        }

        if (isset(AGMA_IMPORT_TERM_MAP[$slug])) {
            $mappedTerms[] = AGMA_IMPORT_TERM_MAP[$slug];
        }
    }

    $mappedTerms = array_values(array_unique($mappedTerms));
    if ([] === $mappedTerms) {
        return null;
    }

    $postMeta = [];
    foreach ($wp->postmeta as $meta) {
        $metaChildren = $meta->children($namespaces['wp']);
        $metaKey = trim((string) $metaChildren->meta_key);
        if ('' === $metaKey) {
            continue;
        }

        $postMeta[$metaKey] = (string) $metaChildren->meta_value;
    }

    $postName = trim((string) $wp->post_name);
    $liveUrl = '' !== $postName
        ? 'https://www.agma.org/event/' . $postName . '/'
        : str_replace('https://agmastag.wpenginepowered.com', 'https://www.agma.org', trim((string) $item->link));

    $liveStatus = '';
    if ($verifyLive) {
        $liveStatus = (string) agma_import_check_live_url($liveUrl);
    }

    $dates = agma_import_extract_dates($postMeta);
    $location = trim((string) ($postMeta['location'] ?? ''));

    return [
        'source_id' => (int) trim((string) $wp->post_id),
        'title' => trim((string) $item->title),
        'slug' => $postName,
        'post_date' => trim((string) $wp->post_date),
        'post_date_gmt' => trim((string) $wp->post_date_gmt),
        'content' => (string) $contentNs->encoded,
        'excerpt' => (string) $excerptNs->encoded,
        'source_terms' => $sourceTerms,
        'mapped_terms' => $mappedTerms,
        'source_link' => trim((string) $item->link),
        'live_url' => $liveUrl,
        'live_status' => $liveStatus,
        'location' => $location,
        'dates' => $dates,
    ];
}

function agma_import_extract_dates(array $postMeta): array
{
    $start = trim((string) ($postMeta['dates_0_start_date'] ?? ''));
    $end = trim((string) ($postMeta['dates_0_end_date'] ?? ''));

    if ('' === $start) {
        $start = trim((string) ($postMeta['_piecal_start_date'] ?? ''));
    }

    if ('' === $end) {
        $end = trim((string) ($postMeta['_piecal_end_date'] ?? ''));
    }

    if ('' === $start) {
        return [
            'start_local' => '',
            'end_local' => '',
            'start_utc' => '',
            'end_utc' => '',
            'all_day' => false,
        ];
    }

    if ('' === $end) {
        $end = $start;
    }

    $timezone = wp_timezone();
    $startDate = agma_import_parse_datetime($start, $timezone);
    $endDate = agma_import_parse_datetime($end, $timezone);

    if (!$startDate || !$endDate) {
        return [
            'start_local' => '',
            'end_local' => '',
            'start_utc' => '',
            'end_utc' => '',
            'all_day' => false,
        ];
    }

    $allDay = (
        '00:00:00' === $startDate->format('H:i:s') &&
        '00:00:00' === $endDate->format('H:i:s')
    );

    return [
        'start_local' => $startDate->format('Y-m-d H:i:s'),
        'end_local' => $endDate->format('Y-m-d H:i:s'),
        'start_utc' => $startDate->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
        'end_utc' => $endDate->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
        'all_day' => $allDay,
    ];
}

function agma_import_parse_datetime(string $value, DateTimeZone $timezone): ?DateTimeImmutable
{
    $formats = [
        'Y-m-d H:i:s',
        'Y-m-d\TH:i:s',
        DateTimeInterface::ATOM,
    ];

    foreach ($formats as $format) {
        $date = DateTimeImmutable::createFromFormat($format, $value, $timezone);
        if ($date instanceof DateTimeImmutable) {
            return $date;
        }
    }

    try {
        return new DateTimeImmutable($value, $timezone);
    } catch (Throwable $e) {
        return null;
    }
}

function agma_import_check_live_url(string $url): int
{
    $response = wp_remote_head($url, [
        'timeout' => 20,
        'redirection' => 5,
        'user-agent' => 'Mozilla/5.0',
    ]);

    if (is_wp_error($response)) {
        $response = wp_remote_get($url, [
            'timeout' => 20,
            'redirection' => 5,
            'user-agent' => 'Mozilla/5.0',
        ]);
    }

    if (is_wp_error($response)) {
        return 0;
    }

    return (int) wp_remote_retrieve_response_code($response);
}

function agma_import_find_existing_post_id(int $sourceId): int
{
    $posts = get_posts([
        'post_type' => 'tribe_events',
        'post_status' => 'any',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_key' => '_agma_import_source_id',
        'meta_value' => (string) $sourceId,
        'suppress_filters' => true,
    ]);

    return isset($posts[0]) ? (int) $posts[0] : 0;
}

function agma_import_resolve_term_ids(array $mappedTerms): array
{
    $termIds = [];
    foreach ($mappedTerms as $slug) {
        $term = get_term_by('slug', $slug, 'tribe_events_cat');
        if (!$term || is_wp_error($term)) {
            agma_import_fail("Missing local tribe_events_cat term for slug: {$slug}");
        }

        $termIds[] = (int) $term->term_id;
    }

    return array_values(array_unique($termIds));
}

function agma_import_backup_database(string $batch, string $root): string
{
    $tmpDir = $root . '/tmp';
    if (!is_dir($tmpDir) && !mkdir($tmpDir, 0775, true) && !is_dir($tmpDir)) {
        agma_import_fail("Could not create tmp directory: {$tmpDir}");
    }

    $backupPath = $tmpDir . '/pre-agma-import-' . $batch . '.sql';
    $mysqlDumpBin = '/Applications/XAMPP/xamppfiles/bin/mysqldump';

    if (!is_executable($mysqlDumpBin)) {
        agma_import_fail("mysqldump not found at {$mysqlDumpBin}");
    }

    $parts = [$mysqlDumpBin];
    $dbHost = DB_HOST;
    if (false !== strpos($dbHost, ':/')) {
        [, $socketPath] = explode(':', $dbHost, 2);
        $parts[] = '--socket=' . escapeshellarg($socketPath);
    } else {
        $parts[] = '-h ' . escapeshellarg($dbHost);
    }

    $parts[] = '-u ' . escapeshellarg(DB_USER);
    if ('' !== DB_PASSWORD) {
        $parts[] = '-p' . escapeshellarg(DB_PASSWORD);
    }

    $parts[] = escapeshellarg(DB_NAME);
    $command = implode(' ', $parts) . ' > ' . escapeshellarg($backupPath);
    exec($command, $output, $exitCode);

    if (0 !== $exitCode || !file_exists($backupPath)) {
        agma_import_fail('Database backup failed before import.');
    }

    return $backupPath;
}

function agma_import_apply(array $events, string $batch): void
{
    $created = 0;
    $updated = 0;

    foreach ($events as $event) {
        $existingId = agma_import_find_existing_post_id($event['source_id']);
        $postarr = [
            'post_type' => 'tribe_events',
            'post_status' => 'publish',
            'post_title' => $event['title'],
            'post_name' => $event['slug'],
            'post_content' => $event['content'],
            'post_excerpt' => $event['excerpt'],
            'post_date' => $event['post_date'] ?: current_time('mysql'),
            'post_date_gmt' => $event['post_date_gmt'] ?: get_gmt_from_date($event['post_date'] ?: current_time('mysql')),
        ];

        if ($existingId > 0) {
            $postarr['ID'] = $existingId;
            $postId = wp_update_post(wp_slash($postarr), true);
            $updated++;
        } else {
            $postId = wp_insert_post(wp_slash($postarr), true);
            $created++;
        }

        if (is_wp_error($postId)) {
            agma_import_fail('Failed importing "' . $event['title'] . '": ' . $postId->get_error_message());
        }

        $postId = (int) $postId;
        $termIds = agma_import_resolve_term_ids($event['mapped_terms']);
        wp_set_object_terms($postId, $termIds, 'tribe_events_cat', false);

        $dates = $event['dates'];
        update_post_meta($postId, '_EventOrigin', 'events-calendar');
        update_post_meta($postId, '_EventTimezone', wp_timezone_string() ?: 'America/New_York');
        update_post_meta($postId, '_EventDateTimeSeparator', ' @ ');
        update_post_meta($postId, '_EventTimeRangeSeparator', ' - ');
        update_post_meta($postId, '_EventCurrencySymbol', '$');
        update_post_meta($postId, '_EventCurrencyCode', 'USD');
        update_post_meta($postId, '_EventCurrencyPosition', 'prefix');
        update_post_meta($postId, '_EventShowMap', '');
        update_post_meta($postId, '_EventShowMapLink', '');
        update_post_meta($postId, '_EventURL', $event['live_url']);
        update_post_meta($postId, '_EventAllDay', $dates['all_day'] ? 'yes' : '');

        if ('' !== $dates['start_local']) {
            update_post_meta($postId, '_EventStartDate', $dates['start_local']);
            update_post_meta($postId, '_EventEndDate', $dates['end_local']);
            update_post_meta($postId, '_EventStartDateUTC', $dates['start_utc']);
            update_post_meta($postId, '_EventEndDateUTC', $dates['end_utc']);
        }

        update_post_meta($postId, '_agma_import_source_id', (string) $event['source_id']);
        update_post_meta($postId, '_agma_import_source_slug', $event['slug']);
        update_post_meta($postId, '_agma_import_source_link', $event['source_link']);
        update_post_meta($postId, '_agma_import_live_url', $event['live_url']);
        update_post_meta($postId, '_agma_import_source_terms', wp_json_encode($event['source_terms']));
        update_post_meta($postId, '_agma_import_mapped_terms', wp_json_encode($event['mapped_terms']));
        update_post_meta($postId, '_agma_import_location', $event['location']);
        update_post_meta($postId, '_agma_import_batch', $batch);

        fwrite(STDOUT, sprintf(
            "[%s] %s -> %d\n",
            $existingId > 0 ? 'update' : 'create',
            $event['title'],
            $postId
        ));
    }

    fwrite(STDOUT, "\nImport complete.\n");
    fwrite(STDOUT, "Created: {$created}\n");
    fwrite(STDOUT, "Updated: {$updated}\n");
    fwrite(STDOUT, "Batch: {$batch}\n");
}

function agma_import_rollback(string $batch): void
{
    $posts = get_posts([
        'post_type' => 'tribe_events',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_key' => '_agma_import_batch',
        'meta_value' => $batch,
        'suppress_filters' => true,
    ]);

    if ([] === $posts) {
        fwrite(STDOUT, "No imported tribe_events found for batch {$batch}\n");
        return;
    }

    foreach ($posts as $postId) {
        wp_delete_post((int) $postId, true);
        fwrite(STDOUT, "Deleted imported post {$postId}\n");
    }

    fwrite(STDOUT, "Rollback complete for batch {$batch}\n");
}

function agma_import_print_summary(array $events, bool $verifyLive): void
{
    $mappedCounts = [];
    $liveCounts = [];
    foreach ($events as $event) {
        foreach ($event['mapped_terms'] as $term) {
            if (!isset($mappedCounts[$term])) {
                $mappedCounts[$term] = 0;
            }
            $mappedCounts[$term]++;
        }

        if ($verifyLive) {
            $status = $event['live_status'] ?: '0';
            if (!isset($liveCounts[$status])) {
                $liveCounts[$status] = 0;
            }
            $liveCounts[$status]++;
        }
    }

    ksort($mappedCounts);
    fwrite(STDOUT, "Dry Run Summary\n");
    fwrite(STDOUT, "Candidates: " . count($events) . "\n");
    fwrite(STDOUT, "Unique titles: " . count(array_unique(array_column($events, 'title'))) . "\n\n");
    fwrite(STDOUT, "Mapped Target Term Counts\n");
    foreach ($mappedCounts as $term => $count) {
        fwrite(STDOUT, "- {$term}: {$count}\n");
    }

    if ($verifyLive) {
        ksort($liveCounts);
        fwrite(STDOUT, "\nLive URL Status Counts\n");
        foreach ($liveCounts as $status => $count) {
            fwrite(STDOUT, "- {$status}: {$count}\n");
        }
    }

    fwrite(STDOUT, "\nSample Rows\n");
    foreach (array_slice($events, 0, 15) as $event) {
        fwrite(STDOUT, sprintf(
            "- %s | mapped=%s | live=%s%s\n",
            $event['title'],
            implode(',', $event['mapped_terms']),
            $event['live_url'],
            $verifyLive ? ' | live_status=' . $event['live_status'] : ''
        ));
    }
}

$args = agma_import_parse_args();

if ('' !== $args['rollback_batch']) {
    agma_import_rollback($args['rollback_batch']);
    exit(0);
}

$events = agma_import_load_events($args['xml_path'], $args['verify_live']);

if ($args['limit'] > 0) {
    $events = array_slice($events, 0, $args['limit']);
}

if (!$args['apply']) {
    agma_import_print_summary($events, $args['verify_live']);
    exit(0);
}

$batch = '' !== $args['batch'] ? sanitize_title($args['batch']) : 'agma-' . gmdate('Ymd-His');

if (!$args['skip_backup']) {
    $backupPath = agma_import_backup_database($batch, $root);
    fwrite(STDOUT, "Database backup: {$backupPath}\n");
}

agma_import_apply($events, $batch);
