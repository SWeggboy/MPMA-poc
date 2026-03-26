<?php

if (is_file(__DIR__.'/vendor/autoload_packages.php')) {
    require_once __DIR__.'/vendor/autoload_packages.php';
}

function tailpress(): TailPress\Framework\Theme
{
    return TailPress\Framework\Theme::instance()
        ->assets(fn($manager) => $manager
            ->withCompiler(new TailPress\Framework\Assets\ViteCompiler, fn($compiler) => $compiler
                ->registerAsset('resources/css/app.css')
                ->registerAsset('resources/js/app.js')
                ->editorStyleFile('resources/css/editor-style.css')
            )
            ->enqueueAssets()
        )
        ->features(fn($manager) => $manager->add(TailPress\Framework\Features\MenuOptions::class))
        ->menus(fn($manager) => $manager
            ->add('primary', 'Primary Menu')
            ->add('secondary', 'Secondary Menu')
            ->add('footer', 'Footer Menu')
            ->add('mobile', 'Mobile Menu')
        )
        ->themeSupport(fn($manager) => $manager->add([
            'title-tag',
            'custom-logo',
            'post-thumbnails',
            'align-wide',
            'wp-block-styles',
            'responsive-embeds',
            'html5' => [
                'search-form',
                'comment-form',
                'comment-list',
                'gallery',
                'caption',
            ]
        ]));
}

tailpress();

function tailpress_is_local_self_request(string $url): bool
{
    if ('local' !== wp_get_environment_type()) {
        return false;
    }

    $home_host = wp_parse_url(home_url('/'), PHP_URL_HOST);
    $request_host = wp_parse_url($url, PHP_URL_HOST);

    return is_string($home_host)
        && '' !== $home_host
        && is_string($request_host)
        && $request_host === $home_host;
}

add_action('http_api_curl', function($handle, $args, $url) {
    unset($args);

    if (!tailpress_is_local_self_request((string) $url)) {
        return;
    }

    $host = wp_parse_url(home_url('/'), PHP_URL_HOST);
    if (!is_string($host) || '' === $host) {
        return;
    }

    curl_setopt($handle, CURLOPT_RESOLVE, array(
        $host . ':80:127.0.0.1',
        $host . ':443:127.0.0.1',
    ));
}, 10, 3);

add_filter('https_ssl_verify', function($verify, $url) {
    if (tailpress_is_local_self_request((string) $url)) {
        return false;
    }

    return $verify;
}, 10, 2);

function tailpress_enqueue_theme_fonts(): void
{
    wp_enqueue_style(
        'tailpress-theme-fonts',
        'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;600;700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto+Slab:wght@100..900&display=swap',
        [],
        null
    );
}

add_action('wp_enqueue_scripts', 'tailpress_enqueue_theme_fonts');
add_action('enqueue_block_editor_assets', 'tailpress_enqueue_theme_fonts');

add_filter('register_block_type_args', function($args, $block_type) {
    if (!in_array($block_type, ['core/heading', 'core/paragraph'], true)) {
        return $args;
    }

    $args['supports'] = $args['supports'] ?? [];
    $args['supports']['spacing'] = $args['supports']['spacing'] ?? [];
    $args['supports']['spacing']['margin'] = ['top', 'right', 'bottom', 'left'];
    $args['supports']['spacing']['padding'] = ['top', 'right', 'bottom', 'left'];

    return $args;
}, 10, 2);

/**
 * Normalize malformed paragraph block markup like:
 * <!-- wp:paragraph --><p><p style="...">Text</p></p><!-- /wp:paragraph -->
 */
function tailpress_normalize_paragraph_block_markup(string $content): string
{
    if ('' === $content || false === strpos($content, '<!-- wp:paragraph')) {
        return $content;
    }

    return preg_replace_callback(
        '/<!-- wp:paragraph(?:\\s+\\{[\\s\\S]*?\\})?\\s*-->[\\s\\S]*?<!-- \\/wp:paragraph -->/i',
        static function (array $matches): string {
            $block_markup = $matches[0] ?? '';

            if ('' === $block_markup || false === strpos($block_markup, '<p')) {
                return $block_markup;
            }

            // Unwrap accidental nested paragraph tags while preserving inner attributes.
            // Handles both <p><p ...> and <p class="..."><p ...>.
            $block_markup = preg_replace('/<p\\b[^>]*>\\s*<p(\\b[^>]*)>/i', '<p$1>', $block_markup);
            // Collapse duplicate closing paragraph tags produced by the same issue.
            $block_markup = preg_replace('/<\\/p>\\s*<\\/p>/i', '</p>', $block_markup);

            return is_string($block_markup) ? $block_markup : ($matches[0] ?? '');
        },
        $content
    ) ?: $content;
}

add_filter('content_save_pre', function($content) {
    if (!is_string($content)) {
        return $content;
    }

    return tailpress_normalize_paragraph_block_markup($content);
}, 20);

function tailpress_normalize_editor_rest_content($response, $post, $request)
{
    if (!$response instanceof WP_REST_Response) {
        return $response;
    }

    if (!$request instanceof WP_REST_Request || 'edit' !== $request->get_param('context')) {
        return $response;
    }

    $data = $response->get_data();
    if (!is_array($data) || !isset($data['content'])) {
        return $response;
    }

    $updated = false;

    if (is_array($data['content']) && isset($data['content']['raw']) && is_string($data['content']['raw'])) {
        $normalized_raw = tailpress_normalize_paragraph_block_markup($data['content']['raw']);
        if ($normalized_raw !== $data['content']['raw']) {
            $data['content']['raw'] = $normalized_raw;
            $updated = true;
        }
    } elseif (is_string($data['content'])) {
        $normalized_content = tailpress_normalize_paragraph_block_markup($data['content']);
        if ($normalized_content !== $data['content']) {
            $data['content'] = $normalized_content;
            $updated = true;
        }
    }

    if ($updated) {
        $response->set_data($data);
    }

    return $response;
}

add_filter('rest_prepare_post', 'tailpress_normalize_editor_rest_content', 20, 3);
add_filter('rest_prepare_page', 'tailpress_normalize_editor_rest_content', 20, 3);
add_filter('rest_prepare_revision', 'tailpress_normalize_editor_rest_content', 20, 3);
add_filter('rest_prepare_autosave', 'tailpress_normalize_editor_rest_content', 20, 3);

function tailpress_is_gcb_renderer_request($request): bool
{
    return $request instanceof WP_REST_Request
        && 0 === strpos($request->get_route(), '/wp/v2/block-renderer/genesis-custom-blocks/');
}

add_filter('rest_request_before_callbacks', function($response, $handler, $request) {
    unset($handler);

    if (tailpress_is_gcb_renderer_request($request)) {
        $GLOBALS['tailpress_gcb_renderer_request'] = $request;
    }

    return $response;
}, 10, 3);

add_filter('rest_request_after_callbacks', function($response, $handler, $request) {
    unset($handler);

    if (tailpress_is_gcb_renderer_request($request)) {
        unset($GLOBALS['tailpress_gcb_renderer_request']);
    }

    return $response;
}, 10, 3);

add_filter('genesis_custom_blocks_data_content', function($content) {
    if (is_string($content) && '' !== trim($content)) {
        return $content;
    }

    $request = $GLOBALS['tailpress_gcb_renderer_request'] ?? null;
    if (!$request instanceof WP_REST_Request) {
        return $content;
    }

    $inner_blocks = $request->get_param('inner_blocks');
    if (!is_string($inner_blocks) || '' === trim($inner_blocks)) {
        return $content;
    }

    if (false !== strpos($inner_blocks, '%')) {
        $inner_blocks = rawurldecode($inner_blocks);
    }

    return wp_unslash($inner_blocks);
});

function tailpress_html_has_meaningful_content(string $html): bool
{
    if (preg_match('/<(img|picture|video|iframe|embed|object|svg|form|input|button|select|textarea|a|ul|ol|table|blockquote|hr)\b/i', $html)) {
        return true;
    }

    return '' !== trim(wp_strip_all_tags(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}

add_filter('render_block', function($block_content, $block) {
    if (is_admin()) {
        return $block_content;
    }

    if (!is_array($block) || ('genesis-blocks/gb-columns' !== ($block['blockName'] ?? ''))) {
        return $block_content;
    }

    if (tailpress_html_has_meaningful_content((string) $block_content)) {
        return $block_content;
    }

    return '';
}, 10, 2);

// Load custom widgets
if (file_exists(get_template_directory() . '/inc/class-upcoming-events-widget.php')) {
    require_once get_template_directory() . '/inc/class-upcoming-events-widget.php';
} else {
    error_log('Widget file not found: ' . get_template_directory() . '/inc/class-upcoming-events-widget.php');
}

if (file_exists(get_template_directory() . '/inc/class-latest-articles-widget.php')) {
    require_once get_template_directory() . '/inc/class-latest-articles-widget.php';
} else {
    error_log('Widget file not found: ' . get_template_directory() . '/inc/class-latest-articles-widget.php');
}

if (file_exists(get_template_directory() . '/inc/class-footer-logos-widget.php')) {
    require_once get_template_directory() . '/inc/class-footer-logos-widget.php';
} else {
    error_log('Widget file not found: ' . get_template_directory() . '/inc/class-footer-logos-widget.php');
}

// Register widget areas and widgets
add_action('widgets_init', function() {
    // Register sidebar widget area
    register_sidebar(array(
        'name'          => __('Sidebar', 'tailpress'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here to appear in your sidebar.', 'tailpress'),
        'before_widget' => '<div id="%1$s" class="widget mb-8 %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widget-title text-2xl font-semibold mb-4">',
        'after_title'   => '</h2>',
    ));
    
    // Register footer widget areas
    register_sidebar(array(
        'name'          => __('Footer 1', 'tailpress'),
        'id'            => 'footer-1',
        'description'   => __('Add widgets here to appear in your footer.', 'tailpress'),
        'before_widget' => '<div id="%1$s" class="widget mb-8 %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title text-lg font-semibold mb-4">',
        'after_title'   => '</h3>',
    ));
    
    register_sidebar(array(
        'name'          => __('Footer 2', 'tailpress'),
        'id'            => 'footer-2',
        'description'   => __('Add widgets here to appear in your footer.', 'tailpress'),
        'before_widget' => '<div id="%1$s" class="widget mb-8 %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title text-lg font-semibold mb-4">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Logos', 'tailpress'),
        'id'            => 'footer-logos',
        'description'   => __('Add logo widgets here to appear above the footer columns.', 'tailpress'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title text-lg font-semibold mb-4">',
        'after_title'   => '</h3>',
    ));
    
    // Register custom widgets
    if (class_exists('Upcoming_Events_Widget')) {
        register_widget('Upcoming_Events_Widget');
    }

    if (class_exists('Footer_Logos_Widget')) {
        register_widget('Footer_Logos_Widget');
    }
});

// Add shortcode for upcoming events widget
add_shortcode('upcoming_events', function($atts) {
    $atts = shortcode_atts(array(
        'title' => 'Upcoming Events'
    ), $atts);
    
    ob_start();
    the_widget('Upcoming_Events_Widget', $atts);
    return ob_get_clean();
});

/**
 * Render Events Calendar list markup that can be embedded in any page.
 *
 * Usage:
 * [mpma_events_list]
 * [mpma_courses_list]
 * [mpma_webinars_list]
 * [mpma_events_list per_page="10" hide_nav="1" hide_subscribe="1"]
 */
function tailpress_get_mpma_events_shortcode_map(): array
{
    return array(
        'mpma_events_list' => 'events',
        'mpma_courses_list' => 'courses',
        'mpma_webinars_list' => 'webinars',
    );
}

function tailpress_get_mpma_posts_shortcode_map(): array
{
    return array(
        'mpma_posts_list' => '',
        'mpma_press_releases_list' => 'press-releases',
        'mpma_member_news_list' => 'member-news',
        'mpma_job_openings_list' => 'job-openings',
        'mpma_trade_tariffs_list' => 'trade-tariffs',
    );
}

function tailpress_get_mpma_hide_dates_category_slugs(): array
{
    return array(
        'on-demand-courses',
        'on-demand-webinars',
        'on-demand-emerging-technology-webinars',
        'on-demand-trade-webinars',
    );
}

function tailpress_is_mpma_evergreen_event($event = null): bool
{
    $event = get_post($event ?: get_the_ID());
    if (!$event instanceof WP_Post || 'tribe_events' !== $event->post_type) {
        return false;
    }

    $slugs = wp_get_post_terms($event->ID, 'tribe_events_cat', array('fields' => 'slugs'));
    if (is_wp_error($slugs) || empty($slugs)) {
        return false;
    }

    return [] !== array_intersect($slugs, tailpress_get_mpma_hide_dates_category_slugs());
}

function tailpress_should_hide_mpma_event_dates(array $category_slugs, $hide_dates_atts_value): bool
{
    if ('' !== $hide_dates_atts_value && null !== $hide_dates_atts_value) {
        return filter_var($hide_dates_atts_value, FILTER_VALIDATE_BOOLEAN);
    }

    if (empty($category_slugs)) {
        return false;
    }

    return [] === array_diff($category_slugs, tailpress_get_mpma_hide_dates_category_slugs());
}

function tailpress_should_show_past_mpma_events(array $category_slugs): bool
{
    if (empty($category_slugs)) {
        return false;
    }

    return [] === array_diff($category_slugs, tailpress_get_mpma_hide_dates_category_slugs());
}

function tailpress_rewrite_mpma_embedded_nav_url(string $href, string $base_url): string
{
    $parts = wp_parse_url($href);
    if (!is_array($parts)) {
        return $href;
    }

    $query_args = array();
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $query_args);
    }

    $path = isset($parts['path']) ? (string) $parts['path'] : '';
    if (preg_match('#/page/(\d+)/?$#', $path, $matches)) {
        $query_args['paged'] = absint($matches[1]);
    } else {
        $query_args['paged'] = 1;
    }

    $clean_base_url = strtok($base_url, '?');

    return !empty($query_args) ? $clean_base_url . '?' . build_query($query_args) : $clean_base_url;
}

function tailpress_render_mpma_events_list_shortcode($atts, string $shortcode_tag = 'mpma_events_list'): string
{
    $shortcode_map = tailpress_get_mpma_events_shortcode_map();
    $default_category = $shortcode_map[sanitize_key($shortcode_tag)] ?? '';

    $atts = shortcode_atts(array(
        'view' => 'list',
        'per_page' => 10,
        'hide_nav' => '1',
        'hide_subscribe' => '1',
        'hide_dates' => '',
        'show_breadcrumbs' => '0',
        'category' => $default_category,
    ), $atts, 'mpma_events_list');

    $view = sanitize_key($atts['view']);
    if ('' === $view) {
        $view = 'list';
    }

    $per_page = max(1, absint($atts['per_page']));
    $hide_nav = filter_var($atts['hide_nav'], FILTER_VALIDATE_BOOLEAN);
    $hide_subscribe = filter_var($atts['hide_subscribe'], FILTER_VALIDATE_BOOLEAN);
    $show_breadcrumbs = filter_var($atts['show_breadcrumbs'], FILTER_VALIDATE_BOOLEAN);
    $category_slugs = preg_split('/\s*,\s*/', (string) $atts['category']);
    $category_slugs = array_values(array_filter(array_map('sanitize_title', (array) $category_slugs)));
    $category = implode(',', $category_slugs);
    $hide_dates = tailpress_should_hide_mpma_event_dates($category_slugs, $atts['hide_dates']);
    $show_past_events = tailpress_should_show_past_mpma_events($category_slugs);
    $wrapper_class = 'mpma-events-list-shortcode' . ($hide_dates ? ' mpma-events-list-shortcode--evergreen' : '');
    $embedded_page_url = '';
    $queried_object_id = get_queried_object_id();
    if ($queried_object_id && is_singular()) {
        $embedded_page_url = get_permalink($queried_object_id) ?: '';
    }

    // Prefer TEC shortcode output when available (Events Calendar Pro installs this).
    if (shortcode_exists('tribe_events')) {
        $tribe_shortcode = sprintf(
            '[tribe_events view="%s" per_page="%d"%s%s]',
            esc_attr($view),
            $per_page,
            '' !== $category ? ' category="' . esc_attr($category) . '"' : '',
            $show_past_events ? ' eventDisplay="past"' : ''
        );

        $html = do_shortcode($tribe_shortcode);
        if (!is_string($html) || '' === trim($html)) {
            return '';
        }

        if (!$hide_nav && !$hide_subscribe) {
            return '<div class="' . esc_attr($wrapper_class) . '">' . $html . '</div>';
        }

        if (class_exists('DOMDocument')) {
            $internal_errors = libxml_use_internal_errors(true);
            $dom = new DOMDocument('1.0', 'UTF-8');

            // Wrap as full HTML document to keep parsing stable.
            $dom->loadHTML(
                '<!doctype html><html><head><meta charset="utf-8"></head><body><div id="mpma-events-root">' . $html . '</div></body></html>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );

            $xpath = new DOMXPath($dom);
            $queries = array();

            // Always remove page-level header UI in embedded shortcode contexts.
            $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-header__title ")]';
            if (!$show_breadcrumbs) {
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-header__breadcrumbs ")]';
            }

            if ($hide_nav) {
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-calendar-list__calendar-list-nav ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-calendar-list-nav ")]';
            } elseif ($embedded_page_url) {
                $nav_links = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-c-nav ")]//a[@href]');
                if ($nav_links) {
                    for ($i = 0; $i < $nav_links->length; $i++) {
                        $nav_link = $nav_links->item($i);
                        if (!$nav_link instanceof DOMElement) {
                            continue;
                        }

                        $nav_link->setAttribute(
                            'href',
                            tailpress_rewrite_mpma_embedded_nav_url($nav_link->getAttribute('href'), $embedded_page_url)
                        );
                    }
                }
            }

            if ($hide_subscribe) {
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-c-subscribe-dropdown ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-c-subscribe-dropdown-container ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-c-subscribe-dropdown__container ")]';
            }

            if ($hide_dates) {
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-c-events-bar__search-container ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-c-events-bar ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-c-events-bar__views ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-c-view-selector ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-header__top-bar ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-c-top-bar__datepicker ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-calendar-list__month-separator ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-calendar-list__event-date-tag ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-calendar-list__event-datetime-wrapper ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-calendar-latest-past__event-date-tag ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-calendar-latest-past__event-datetime-wrapper ")]';
                if (!$hide_nav) {
                    $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-c-nav__list-item--today ")]';
                }
            }

            foreach ($queries as $query) {
                $nodes = $xpath->query($query);
                if (!$nodes) {
                    continue;
                }

                // Remove from the end to avoid live NodeList mutation issues.
                for ($i = $nodes->length - 1; $i >= 0; $i--) {
                    $node = $nodes->item($i);
                    if ($node && $node->parentNode) {
                        $node->parentNode->removeChild($node);
                    }
                }
            }

            $root = $dom->getElementById('mpma-events-root');
            $filtered_html = '';

            if ($root) {
                foreach ($root->childNodes as $child) {
                    $filtered_html .= $dom->saveHTML($child);
                }
            } else {
                $filtered_html = $html;
            }

            libxml_clear_errors();
            libxml_use_internal_errors($internal_errors);

            return '<div class="' . esc_attr($wrapper_class) . '">' . $filtered_html . '</div>';
        }

        // Fallback: remove known nodes with regex if DOM extension is unavailable.
        $html = preg_replace(
            '/<[^>]+class="[^"]*tribe-events-header__title[^"]*"[^>]*>.*?<\/[^>]+>/si',
            '',
            $html
        );
        if (!$show_breadcrumbs) {
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-header__breadcrumbs[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
        }

        if ($hide_nav) {
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-calendar-list(?:__calendar-list)?-nav[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
        } elseif ($embedded_page_url) {
            $html = preg_replace_callback(
                '/href="([^"]+)"/i',
                static function(array $matches) use ($embedded_page_url): string {
                    return 'href="' . esc_url(tailpress_rewrite_mpma_embedded_nav_url(html_entity_decode($matches[1]), $embedded_page_url)) . '"';
                },
                $html
            );
        }

        if ($hide_subscribe) {
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-c-subscribe-dropdown(?:-container|__container)?[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
        }

        if ($hide_dates) {
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-c-events-bar__search-container[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-c-events-bar[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-c-events-bar__views[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-c-view-selector[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-header__top-bar[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-c-top-bar__datepicker[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-calendar-list__month-separator[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-calendar-list__event-date-tag[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-calendar-list__event-datetime-wrapper[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-calendar-latest-past__event-date-tag[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-calendar-latest-past__event-datetime-wrapper[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            if (!$hide_nav) {
                $html = preg_replace(
                    '/<li[^>]+class="[^"]*tribe-events-c-nav__list-item--today[^"]*"[^>]*>.*?<\/li>/si',
                    '',
                    $html
                );
            }
        }

        return '<div class="' . esc_attr($wrapper_class) . '">' . $html . '</div>';
    }

    // Fallback for sites without Events Calendar Pro shortcode support:
    // render native TEC V2 view markup so output matches /events/ including .tribe-events-header.
    if (class_exists('\Tribe\Events\Views\V2\View') && function_exists('tribe_context')) {
        $context = tribe_context()->alter(array(
            'event_display' => $view,
            'event_display_mode' => $show_past_events ? 'past' : $view,
            'event_date' => 'now',
            'events_per_page' => $per_page,
            'category' => $category,
            'tribe_events_cat' => $category,
            'event_category' => $category,
            'past' => $show_past_events,
            'show_latest_past' => false,
            'paged' => max(1, absint(get_query_var('paged', 1))),
            'page' => max(1, absint(get_query_var('paged', 1))),
        ));

        $html = \Tribe\Events\Views\V2\View::make($view, $context)->get_html();
        if (!is_string($html) || '' === trim($html)) {
            return '';
        }

        if (!$hide_nav && !$hide_subscribe) {
            return '<div class="' . esc_attr($wrapper_class) . '">' . $html . '</div>';
        }

        if (class_exists('DOMDocument')) {
            $internal_errors = libxml_use_internal_errors(true);
            $dom = new DOMDocument('1.0', 'UTF-8');

            $dom->loadHTML(
                '<!doctype html><html><head><meta charset="utf-8"></head><body><div id="mpma-events-root">' . $html . '</div></body></html>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );

            $xpath = new DOMXPath($dom);
            $queries = array();

            // Always remove page-level header UI in embedded shortcode contexts.
            $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-header__title ")]';
            if (!$show_breadcrumbs) {
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-header__breadcrumbs ")]';
            }

            if ($hide_nav) {
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-calendar-list__calendar-list-nav ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-calendar-list-nav ")]';
            } elseif ($embedded_page_url) {
                $nav_links = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-c-nav ")]//a[@href]');
                if ($nav_links) {
                    for ($i = 0; $i < $nav_links->length; $i++) {
                        $nav_link = $nav_links->item($i);
                        if (!$nav_link instanceof DOMElement) {
                            continue;
                        }

                        $nav_link->setAttribute(
                            'href',
                            tailpress_rewrite_mpma_embedded_nav_url($nav_link->getAttribute('href'), $embedded_page_url)
                        );
                    }
                }
            }

            if ($hide_subscribe) {
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-c-subscribe-dropdown ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-c-subscribe-dropdown-container ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-c-subscribe-dropdown__container ")]';
            }

            if ($hide_dates) {
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-c-events-bar__search-container ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-c-events-bar ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-c-events-bar__views ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-c-view-selector ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-header__top-bar ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-c-top-bar__datepicker ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-calendar-list__month-separator ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-calendar-list__event-date-tag ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-calendar-list__event-datetime-wrapper ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-calendar-latest-past__event-date-tag ")]';
                $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-calendar-latest-past__event-datetime-wrapper ")]';
                if (!$hide_nav) {
                    $queries[] = '//*[contains(concat(" ", normalize-space(@class), " "), " tribe-events-c-nav__list-item--today ")]';
                }
            }

            foreach ($queries as $query) {
                $nodes = $xpath->query($query);
                if (!$nodes) {
                    continue;
                }

                for ($i = $nodes->length - 1; $i >= 0; $i--) {
                    $node = $nodes->item($i);
                    if ($node && $node->parentNode) {
                        $node->parentNode->removeChild($node);
                    }
                }
            }

            $root = $dom->getElementById('mpma-events-root');
            $filtered_html = '';

            if ($root) {
                foreach ($root->childNodes as $child) {
                    $filtered_html .= $dom->saveHTML($child);
                }
            } else {
                $filtered_html = $html;
            }

            libxml_clear_errors();
            libxml_use_internal_errors($internal_errors);

            return '<div class="' . esc_attr($wrapper_class) . '">' . $filtered_html . '</div>';
        }

        $html = preg_replace(
            '/<[^>]+class="[^"]*tribe-events-header__title[^"]*"[^>]*>.*?<\/[^>]+>/si',
            '',
            $html
        );
        if (!$show_breadcrumbs) {
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-header__breadcrumbs[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
        }

        if ($hide_nav) {
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-calendar-list(?:__calendar-list)?-nav[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
        } elseif ($embedded_page_url) {
            $html = preg_replace_callback(
                '/href="([^"]+)"/i',
                static function(array $matches) use ($embedded_page_url): string {
                    return 'href="' . esc_url(tailpress_rewrite_mpma_embedded_nav_url(html_entity_decode($matches[1]), $embedded_page_url)) . '"';
                },
                $html
            );
        }

        if ($hide_subscribe) {
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-c-subscribe-dropdown(?:-container|__container)?[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
        }

        if ($hide_dates) {
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-c-events-bar__search-container[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-c-events-bar[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-c-events-bar__views[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-c-view-selector[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-header__top-bar[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-c-top-bar__datepicker[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-calendar-list__month-separator[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-calendar-list__event-date-tag[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-calendar-list__event-datetime-wrapper[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-calendar-latest-past__event-date-tag[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            $html = preg_replace(
                '/<[^>]+class="[^"]*tribe-events-calendar-latest-past__event-datetime-wrapper[^"]*"[^>]*>.*?<\/[^>]+>/si',
                '',
                $html
            );
            if (!$hide_nav) {
                $html = preg_replace(
                    '/<li[^>]+class="[^"]*tribe-events-c-nav__list-item--today[^"]*"[^>]*>.*?<\/li>/si',
                    '',
                    $html
                );
            }
        }

        return '<div class="' . esc_attr($wrapper_class) . '">' . $html . '</div>';
    }

    return '';
}

foreach (array_keys(tailpress_get_mpma_events_shortcode_map()) as $mpma_events_shortcode) {
    add_shortcode($mpma_events_shortcode, function($atts, $content = null, $shortcode_tag = 'mpma_events_list') {
        return tailpress_render_mpma_events_list_shortcode($atts, (string) $shortcode_tag);
    });
}

function tailpress_render_mpma_posts_list_shortcode($atts, string $shortcode_tag = 'mpma_posts_list'): string
{
    $shortcode_map = tailpress_get_mpma_posts_shortcode_map();
    $default_category = $shortcode_map[sanitize_key($shortcode_tag)] ?? '';

    $atts = shortcode_atts(array(
        'category' => $default_category,
        'per_page' => 10,
        'show_excerpt' => '1',
        'show_date' => '0',
        'show_author' => '0',
        'show_image' => '1',
        'paged' => '',
    ), $atts, 'mpma_posts_list');

    $category_slugs = preg_split('/\s*,\s*/', (string) $atts['category']);
    $category_slugs = array_values(array_filter(array_map('sanitize_title', (array) $category_slugs)));

    if (empty($category_slugs)) {
        return '';
    }

    $per_page = max(1, absint($atts['per_page']));
    $show_excerpt = filter_var($atts['show_excerpt'], FILTER_VALIDATE_BOOLEAN);
    $show_date = filter_var($atts['show_date'], FILTER_VALIDATE_BOOLEAN);
    $show_author = filter_var($atts['show_author'], FILTER_VALIDATE_BOOLEAN);
    $show_image = filter_var($atts['show_image'], FILTER_VALIDATE_BOOLEAN);
    $paged = absint($atts['paged']);

    if ($paged < 1) {
        $paged = max(1, absint(get_query_var('paged')), absint(get_query_var('page')));
    }

    $query = new WP_Query(array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'paged' => $paged,
        'ignore_sticky_posts' => true,
        'category_name' => implode(',', $category_slugs),
    ));

    if (!$query->have_posts()) {
        wp_reset_postdata();
        return '<div class="mpma-posts-list-shortcode"><p>No posts found.</p></div>';
    }

    $base_url = '';
    $queried_object_id = get_queried_object_id();
    if ($queried_object_id && is_singular()) {
        $base_url = get_permalink($queried_object_id) ?: '';
    }

    ob_start();
    ?>
    <div class="mpma-posts-list-shortcode">
        <?php while ($query->have_posts()) : $query->the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('mpma-posts-list-shortcode__item'); ?>>
                <div class="mpma-posts-list-shortcode__body">
                    <?php if ($show_date || $show_author) : ?>
                        <div class="mpma-posts-list-shortcode__meta">
                            <?php if ($show_date) : ?>
                                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>" itemprop="datePublished" class="mpma-posts-list-shortcode__date"><?php echo esc_html(get_the_date()); ?></time>
                            <?php endif; ?>

                            <?php if ($show_author) : ?>
                                <div class="mpma-posts-list-shortcode__author"><?php the_author(); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="mpma-posts-list-shortcode__content">
                        <h2 class="mpma-posts-list-shortcode__title text-2xl font-semibold text-zinc-950">
                            <a href="<?php the_permalink(); ?>" class="!no-underline"><?php the_title(); ?></a>
                        </h2>

                        <?php if ($show_image && has_post_thumbnail()) : ?>
                            <div class="mpma-posts-list-shortcode__image mt-6 overflow-hidden rounded-3xl bg-light">
                                <a href="<?php the_permalink(); ?>" class="block">
                                    <?php the_post_thumbnail('large', array('class' => 'aspect-16/10 w-full object-cover')); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if ($show_excerpt) : ?>
                            <div class="mpma-posts-list-shortcode__excerpt mt-4 max-w-2xl text-base text-zinc-600">
                                <?php the_excerpt(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>

        <?php if ($query->max_num_pages > 1) : ?>
            <nav class="mpma-posts-list-shortcode__pagination mt-12 flex items-center justify-between gap-4 text-sm font-semibold" aria-label="<?php esc_attr_e('Posts pagination', 'tailpress'); ?>">
                <div>
                    <?php if ($paged > 1) : ?>
                        <?php
                        $prev_url = $base_url ? add_query_arg('paged', $paged - 1, $base_url) : get_pagenum_link($paged - 1);
                        ?>
                        <a class="mpma-posts-list-shortcode__pagination-link !no-underline inline-flex items-center text-zinc-900 transition hover:text-primary" href="<?php echo esc_url($prev_url); ?>">
                            <?php esc_html_e('Previous', 'tailpress'); ?>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="text-zinc-600">
                    <?php
                    echo esc_html(
                        sprintf(
                            __('Page %1$d of %2$d', 'tailpress'),
                            $paged,
                            (int) $query->max_num_pages
                        )
                    );
                    ?>
                </div>
                <div class="ml-auto">
                    <?php if ($paged < (int) $query->max_num_pages) : ?>
                        <?php
                        $next_url = $base_url ? add_query_arg('paged', $paged + 1, $base_url) : get_pagenum_link($paged + 1);
                        ?>
                        <a class="mpma-posts-list-shortcode__pagination-link !no-underline inline-flex items-center text-zinc-900 transition hover:text-primary" href="<?php echo esc_url($next_url); ?>">
                            <?php esc_html_e('Next', 'tailpress'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </nav>
        <?php endif; ?>
    </div>
    <?php

    wp_reset_postdata();

    return (string) ob_get_clean();
}

foreach (array_keys(tailpress_get_mpma_posts_shortcode_map()) as $mpma_posts_shortcode) {
    add_shortcode($mpma_posts_shortcode, function($atts, $content = null, $shortcode_tag = 'mpma_posts_list') {
        return tailpress_render_mpma_posts_list_shortcode($atts, (string) $shortcode_tag);
    });
}

function tailpress_page_has_shortcode($shortcode_tags): bool
{
    static $cache = array();
    $shortcode_tags = array_values(array_filter(array_map('sanitize_key', (array) $shortcode_tags)));

    if (empty($shortcode_tags)) {
        return false;
    }

    sort($shortcode_tags);
    $cache_key = implode('|', $shortcode_tags);

    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    if (is_admin()) {
        $cache[$cache_key] = false;
        return false;
    }

    $post = null;
    $queried_object_id = get_queried_object_id();

    if ($queried_object_id) {
        $queried_post = get_post($queried_object_id);
        if ($queried_post instanceof WP_Post) {
            $post = $queried_post;
        }
    }

    if (!$post instanceof WP_Post) {
        $global_post = get_post();
        if ($global_post instanceof WP_Post) {
            $post = $global_post;
        }
    }

    if (!$post instanceof WP_Post) {
        $cache[$cache_key] = false;
        return false;
    }

    $content = (string) $post->post_content;
    $has_shortcode = false;

    foreach ($shortcode_tags as $shortcode_tag) {
        if (has_shortcode($content, $shortcode_tag)) {
            $has_shortcode = true;
            break;
        }
    }

    $cache[$cache_key] = $has_shortcode;

    return $has_shortcode;
}

// Ensure TEC frontend assets load on pages that embed [mpma_events_list].
add_filter('tribe_events_assets_should_enqueue_frontend', function($should_enqueue) {
    if ($should_enqueue) {
        return true;
    }

    return tailpress_page_has_shortcode(array_keys(tailpress_get_mpma_events_shortcode_map()));
});

// Force full TEC V2 styles on shortcode pages so output matches /events/.
add_filter('tribe_events_views_v2_assets_should_enqueue_full_styles', function($should_enqueue) {
    if ($should_enqueue) {
        return true;
    }

    return tailpress_page_has_shortcode(array_keys(tailpress_get_mpma_events_shortcode_map()));
});

// Hard enqueue TEC styles on shortcode pages to avoid missing CSS when conditionals misfire.
add_action('wp_enqueue_scripts', function() {
    if (!tailpress_page_has_shortcode(array_keys(tailpress_get_mpma_events_shortcode_map()))) {
        return;
    }

    if (function_exists('tribe_asset_enqueue_group')) {
        tribe_asset_enqueue_group('events-views-v2', true);
    }

    $style_handles = array(
        'tribe-common-skeleton-style',
        'tribe-common-full-style',
        'tribe-events-views-v2-skeleton',
        'tribe-events-views-v2-full',
    );

    foreach ($style_handles as $handle) {
        if (wp_style_is($handle, 'registered')) {
            wp_enqueue_style($handle);
        } elseif (function_exists('tribe_asset_enqueue')) {
            tribe_asset_enqueue($handle, true);
        }
    }
}, 120);

add_filter('tribe_the_notices', function($html, $notices) {
    if (!is_singular('tribe_events') || !tailpress_is_mpma_evergreen_event()) {
        return $html;
    }

    if (empty($notices)) {
        return $html;
    }

    $passed_notice = sprintf(
        esc_html__('This %s has passed.', 'the-events-calendar'),
        tribe_get_event_label_singular_lowercase()
    );

    $filtered_notices = array_values(array_filter((array) $notices, static function($notice) use ($passed_notice) {
        return trim(wp_strip_all_tags((string) $notice)) !== $passed_notice;
    }));

    if (empty($filtered_notices)) {
        return '';
    }

    return '<div class="tribe-events-notices"><ul><li>' . implode('</li><li>', $filtered_notices) . '</li></ul></div>';
}, 20, 2);

add_filter('comments_open', function($open, $post_id) {
    return get_post_type($post_id) === 'post' ? false : $open;
}, 10, 2);

add_filter('pings_open', function($open, $post_id) {
    return get_post_type($post_id) === 'post' ? false : $open;
}, 10, 2);

// Register custom block category
add_filter('block_categories_all', function($categories) {
    $mpma_category = array(
        'slug'  => 'mpma-custom',
        'title' => 'MPMA Custom Blocks',
        'icon'  => 'admin-customizer',
    );

    $ordered_categories = array();
    $genesis_index = null;

    foreach ($categories as $category) {
        if (($category['slug'] ?? '') === 'mpma-custom') {
            $mpma_category = array_merge($mpma_category, $category);
            continue;
        }

        $ordered_categories[] = $category;

        if (($category['slug'] ?? '') === 'genesis-blocks' && $genesis_index === null) {
            $genesis_index = count($ordered_categories) - 1;
        }
    }

    if ($genesis_index === null) {
        array_unshift($ordered_categories, $mpma_category);
        return $ordered_categories;
    }

    array_splice($ordered_categories, $genesis_index, 0, array($mpma_category));
    return $ordered_categories;
}, 99);

// Register custom blocks
add_action('init', function() {
    // Register the Upcoming Events block
    register_block_type('tailpress/upcoming-events', array(
        'api_version' => 2,
        'category' => 'mpma-custom',
        'attributes' => array(
            'title' => array(
                'type' => 'string',
                'default' => 'Upcoming Events'
            )
        ),
        'render_callback' => function($attributes) {
            ob_start();
            the_widget('Upcoming_Events_Widget', array('title' => $attributes['title']));
            return ob_get_clean();
        }
    ));
    
    // Register blocks using block.json
    register_block_type(__DIR__ . '/inc/blocks/carousel');
    register_block_type(__DIR__ . '/inc/blocks/homepage-cta-bg');
    register_block_type(__DIR__ . '/inc/blocks/homepage-image-text');
    register_block_type(__DIR__ . '/inc/blocks/homepage-magazine-cta');
    register_block_type(__DIR__ . '/inc/blocks/mpma-hero');
    register_block_type(__DIR__ . '/inc/blocks/mpma-internal-card');
    register_block_type(__DIR__ . '/inc/blocks/mpma-internal-card-tile');
    register_block_type(__DIR__ . '/inc/blocks/mpma-internal-full-width-carousel');
    register_block_type(__DIR__ . '/inc/blocks/mpma-internal-full-width-carousel-slide');
    register_block_type(__DIR__ . '/inc/blocks/mpma-internal-membership-list');
    register_block_type(__DIR__ . '/inc/blocks/mpma-internal-layout');
    register_block_type(__DIR__ . '/inc/blocks/mpma-internal-layout-row');
    register_block_type(__DIR__ . '/inc/blocks/mpma-internal-layout-column');
    register_block_type(__DIR__ . '/inc/blocks/mpma-column');

    register_block_style('core/button', array(
        'name'         => 'mpma-primary',
        'label'        => __('MPMA Primary', 'tailpress'),
        'is_default'   => true,
    ));

    register_block_style('core/button', array(
        'name'       => 'mpma-secondary',
        'label'      => __('MPMA Secondary', 'tailpress'),
    ));

    register_block_style('core/button', array(
        'name'       => 'mpma-legacy',
        'label'      => __('MPMA Legacy', 'tailpress'),
    ));
});

// Enqueue block editor assets
add_action('enqueue_block_editor_assets', function() {
    wp_enqueue_script(
        'tailpress-upcoming-events-block',
        get_template_directory_uri() . '/inc/upcoming-events-block.js',
        array('wp-blocks', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-element'),
        filemtime(get_template_directory() . '/inc/upcoming-events-block.js'),
        true
    );

    wp_add_inline_script(
        'tailpress-upcoming-events-block',
        "(function(wp){if(!wp||!wp.domReady||!wp.blocks||!wp.data){return;}wp.domReady(function(){var blocks=wp.blocks;var data=wp.data;var select=data.select;var dispatch=data.dispatch;var seenLegacyState={};var collect=function(items,result){(items||[]).forEach(function(block){result.push(block);if(block&&block.innerBlocks&&block.innerBlocks.length){collect(block.innerBlocks,result);}});return result;};var hasLegacyStyle=function(className){return typeof className==='string'&&/(^|\\s)is-style-mpma-legacy(\\s|$)/.test(className);};var ensureLegacyDefaults=function(block){if(!block||block.name!=='core/button'){return;}var isLegacy=hasLegacyStyle(block.attributes&&block.attributes.className);var wasLegacy=!!seenLegacyState[block.clientId];if(!isLegacy){seenLegacyState[block.clientId]=false;return;}if(wasLegacy){return;}seenLegacyState[block.clientId]=true;var attrs=block.attributes||{};var style=Object.assign({},attrs.style||{});var border=Object.assign({},style.border||{});border.radius='2px';border.width='1px';border.color='#ffffff';border.style='solid';style.border=border;dispatch('core/block-editor').updateBlockAttributes(block.clientId,{style:style});};blocks.unregisterBlockStyle('core/button','fill');blocks.unregisterBlockStyle('core/button','outline');collect(select('core/block-editor').getBlocks(),[]).forEach(function(block){seenLegacyState[block.clientId]=hasLegacyStyle(block.attributes&&block.attributes.className);});data.subscribe(function(){collect(select('core/block-editor').getBlocks(),[]).forEach(ensureLegacyDefaults);});});})(window.wp);",
        'after'
    );

    wp_add_inline_script(
        'tailpress-upcoming-events-block',
        "(function(wp){if(!wp||!wp.domReady||!wp.data){return;}wp.domReady(function(){var data=wp.data;var select=data.select;var dispatch=data.dispatch;var initialized={};var collect=function(items,result){(items||[]).forEach(function(block){result.push(block);if(block&&block.innerBlocks&&block.innerBlocks.length){collect(block.innerBlocks,result);}});return result;};var defaults={ 'core/heading': { fontWeight: '600', fontStyle: 'normal' }, 'core/paragraph': { fontWeight: '400', fontStyle: 'normal' } };var ensureTypographyAppearance=function(block){if(!block||!defaults[block.name]){return;}var attrs=block.attributes||{};var style=Object.assign({},attrs.style||{});var typography=Object.assign({},style.typography||{});var hasWeight=typeof typography.fontWeight!=='undefined'&&typography.fontWeight!=='';var hasStyle=typeof typography.fontStyle!=='undefined'&&typography.fontStyle!=='';if(initialized[block.clientId]&&hasWeight&&hasStyle){return;}if(hasWeight&&hasStyle){initialized[block.clientId]=true;return;}if(!hasWeight){typography.fontWeight=defaults[block.name].fontWeight;}if(!hasStyle){typography.fontStyle=defaults[block.name].fontStyle;}style.typography=typography;initialized[block.clientId]=true;dispatch('core/block-editor').updateBlockAttributes(block.clientId,{style:style});};data.subscribe(function(){collect(select('core/block-editor').getBlocks(),[]).forEach(ensureTypographyAppearance);});});})(window.wp);",
        'after'
    );
});

// Override custom logo width with Tailwind classes
add_filter('get_custom_logo', function($html) {
    if (empty($html)) {
        return $html;
    }
    
    // Add Tailwind width class to the image tag
    $html = str_replace('custom-logo-link', 'custom-logo-link w-[252px]', $html);
    
    return $html;
});

/**
 * Enable SVG upload for administrators and sanitize uploaded SVG files.
 */
add_filter('upload_mimes', function($mimes) {
    if (!current_user_can('manage_options')) {
        return $mimes;
    }

    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
});

add_filter('wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if ('svg' === $ext) {
        $data['ext']  = 'svg';
        $data['type'] = 'image/svg+xml';
    }

    return $data;
}, 10, 4);

add_filter('wp_handle_upload_prefilter', function($file) {
    $filename = isset($file['name']) ? (string) $file['name'] : '';
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if ('svg' !== $ext) {
        return $file;
    }

    if (!current_user_can('manage_options')) {
        $file['error'] = __('Only administrators can upload SVG files.', 'tailpress');
        return $file;
    }

    if (empty($file['tmp_name']) || !is_readable($file['tmp_name'])) {
        $file['error'] = __('SVG upload failed: temporary file is not readable.', 'tailpress');
        return $file;
    }

    $svg = file_get_contents($file['tmp_name']);
    if (false === $svg || '' === trim($svg)) {
        $file['error'] = __('SVG upload failed: empty or unreadable file.', 'tailpress');
        return $file;
    }

    if (!preg_match('/<svg\b/i', $svg)) {
        $file['error'] = __('SVG upload failed: invalid SVG markup.', 'tailpress');
        return $file;
    }

    $dom = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $loaded = $dom->loadXML($svg, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    if (!$loaded || !$dom->documentElement || 'svg' !== strtolower($dom->documentElement->tagName)) {
        $file['error'] = __('SVG upload failed: malformed XML.', 'tailpress');
        return $file;
    }

    // Remove high-risk elements entirely.
    $blocked_tags = ['script', 'foreignObject', 'iframe', 'object', 'embed', 'link'];
    foreach ($blocked_tags as $tag) {
        while (($nodes = $dom->getElementsByTagName($tag))->length > 0) {
            $node = $nodes->item(0);
            $node->parentNode->removeChild($node);
        }
    }

    // Remove event handlers and javascript: URLs.
    $all_nodes = $dom->getElementsByTagName('*');
    foreach ($all_nodes as $node) {
        if (!$node->hasAttributes()) {
            continue;
        }

        $to_remove = [];
        foreach ($node->attributes as $attribute) {
            $name = strtolower($attribute->name);
            $value = trim((string) $attribute->value);

            if (0 === strpos($name, 'on')) {
                $to_remove[] = $attribute->name;
                continue;
            }

            if (in_array($name, ['href', 'xlink:href'], true) && preg_match('/^\s*javascript:/i', $value)) {
                $to_remove[] = $attribute->name;
            }
        }

        foreach ($to_remove as $attribute_name) {
            $node->removeAttribute($attribute_name);
        }
    }

    $sanitized = $dom->saveXML($dom->documentElement);
    if (false === $sanitized || '' === trim($sanitized)) {
        $file['error'] = __('SVG upload failed: sanitization returned empty output.', 'tailpress');
        return $file;
    }

    if (false === file_put_contents($file['tmp_name'], $sanitized)) {
        $file['error'] = __('SVG upload failed: unable to write sanitized SVG.', 'tailpress');
        return $file;
    }

    $file['type'] = 'image/svg+xml';
    return $file;
});

/**
 * Ensure SVG attachments always expose width/height metadata.
 *
 * Missing dimensions can trigger PHP warnings in wp-admin/includes/image.php,
 * which can corrupt REST JSON responses used by block editor SSR previews.
 */
function tailpress_get_svg_dimensions_from_file($file_path) {
    if (!is_string($file_path) || '' === $file_path || !file_exists($file_path) || !is_readable($file_path)) {
        return array('width' => 1, 'height' => 1);
    }

    $svg_content = file_get_contents($file_path);
    if (false === $svg_content || '' === trim($svg_content)) {
        return array('width' => 1, 'height' => 1);
    }

    $dom = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $loaded = $dom->loadXML($svg_content, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    if (!$loaded || !$dom->documentElement || 'svg' !== strtolower($dom->documentElement->tagName)) {
        return array('width' => 1, 'height' => 1);
    }

    $to_dimension = static function($value) {
        if (!is_string($value) || '' === trim($value)) {
            return 0;
        }

        if (preg_match('/([0-9]*\.?[0-9]+)/', $value, $matches)) {
            $number = (float) $matches[1];
            if ($number > 0) {
                return (int) max(1, round($number));
            }
        }

        return 0;
    };

    $svg = $dom->documentElement;
    $width = $to_dimension($svg->getAttribute('width'));
    $height = $to_dimension($svg->getAttribute('height'));

    if (($width <= 0 || $height <= 0) && $svg->hasAttribute('viewBox')) {
        $view_box = preg_split('/[\s,]+/', trim((string) $svg->getAttribute('viewBox')));
        if (is_array($view_box) && count($view_box) === 4) {
            if ($width <= 0) {
                $width = $to_dimension((string) $view_box[2]);
            }
            if ($height <= 0) {
                $height = $to_dimension((string) $view_box[3]);
            }
        }
    }

    return array(
        'width' => max(1, $width),
        'height' => max(1, $height),
    );
}

function tailpress_get_image_dimensions_from_file($file_path, $mime_type = '') {
    if ('image/svg+xml' === $mime_type) {
        return tailpress_get_svg_dimensions_from_file($file_path);
    }

    if (!is_string($file_path) || '' === $file_path || !file_exists($file_path) || !is_readable($file_path)) {
        return array('width' => 1, 'height' => 1);
    }

    $image_size = wp_getimagesize($file_path);
    if (is_array($image_size) && isset($image_size[0], $image_size[1])) {
        $width = max(1, (int) $image_size[0]);
        $height = max(1, (int) $image_size[1]);
        return array('width' => $width, 'height' => $height);
    }

    return array('width' => 1, 'height' => 1);
}

function tailpress_normalize_attachment_metadata_dimensions($metadata, $attachment_id) {
    $mime_type = get_post_mime_type($attachment_id);
    if (!is_string($mime_type) || 0 !== strpos($mime_type, 'image/')) {
        return $metadata;
    }

    if (!is_array($metadata)) {
        $metadata = array();
    }

    $needs_dimensions = (
        !isset($metadata['width']) ||
        !isset($metadata['height']) ||
        (int) $metadata['width'] <= 0 ||
        (int) $metadata['height'] <= 0
    );

    if ($needs_dimensions) {
        $file_path = get_attached_file($attachment_id);
        $dimensions = tailpress_get_image_dimensions_from_file($file_path, $mime_type);
        $metadata['width'] = (int) $dimensions['width'];
        $metadata['height'] = (int) $dimensions['height'];
    }

    if (!isset($metadata['sizes']) || !is_array($metadata['sizes'])) {
        $metadata['sizes'] = array();
    }

    return $metadata;
}

add_filter('wp_get_attachment_metadata', 'tailpress_normalize_attachment_metadata_dimensions', 5, 2);
add_filter('wp_generate_attachment_metadata', 'tailpress_normalize_attachment_metadata_dimensions', 5, 2);

// Development-only modifications
if (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'local') {
    // Disable Heartbeat API completely for local dev
    add_action('init', function() {
        wp_deregister_script('heartbeat');
    }, 1);
}

// Render the dropdown
function render_event_form_meta_box($post) {
    wp_nonce_field('save_event_form', 'event_form_nonce');
    $selected_form = get_post_meta($post->ID, '_event_form_id', true);
    
    // Get all WPForms
    $forms = wpforms()->form->get();
    
    echo '<label for="event_form_id">Select Form:</label>';
    echo '<select name="event_form_id" id="event_form_id" style="width:100%;">';
    echo '<option value="">-- None --</option>';
    
    if ($forms) {
        foreach ($forms as $form) {
            $selected = ($selected_form == $form->ID) ? 'selected' : '';
            echo '<option value="' . esc_attr($form->ID) . '" ' . $selected . '>';
            echo esc_html($form->post_title);
            echo '</option>';
        }
    }
    echo '</select>';
}

// Save the selected form
add_action('save_post_tribe_events', 'save_event_form_meta_box');
function save_event_form_meta_box($post_id) {
    if (!isset($_POST['event_form_nonce']) || !wp_verify_nonce($_POST['event_form_nonce'], 'save_event_form')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (isset($_POST['event_form_id'])) {
        update_post_meta($post_id, '_event_form_id', sanitize_text_field($_POST['event_form_id']));
    }
}

// Display the form on single event pages
add_action('tribe_events_single_event_after_the_content', 'display_event_form');
function display_event_form() {
    $form_id = get_post_meta(get_the_ID(), '_event_form_id', true);
    
    if ($form_id) {
        echo '<div class="event-registration-form">';
        echo do_shortcode('[wpforms id="' . $form_id . '"]');
        echo '</div>';
    }
}

// Enable REST API for The Events Calendar custom post types
add_filter('register_post_type_args', 'enable_tribe_rest_api', 10, 2);
function enable_tribe_rest_api($args, $post_type) {
    if ($post_type === 'tribe_organizer' || $post_type === 'tribe_venue') {
        $args['show_in_rest'] = true;
        $args['rest_base'] = $post_type;
        $args['rest_controller_class'] = 'WP_REST_Posts_Controller';
    }
    return $args;
}

// Register custom REST API routes for tribe_organizer and tribe_venue
add_action('rest_api_init', function() {
    register_rest_route('wp/v2', '/tribe_organizer', array(
        'methods' => 'GET',
        'callback' => 'get_tribe_organizers',
        'permission_callback' => '__return_true',
        'args' => array(
            'search' => array(
                'required' => false,
                'type' => 'string',
            ),
        ),
    ));
    
    register_rest_route('wp/v2', '/tribe_venue', array(
        'methods' => 'GET',
        'callback' => 'get_tribe_venues',
        'permission_callback' => '__return_true',
        'args' => array(
            'search' => array(
                'required' => false,
                'type' => 'string',
            ),
        ),
    ));
});

function get_tribe_organizers($request) {
    $search = $request->get_param('search');
    $per_page = $request->get_param('per_page') ?: 10;
    $page = $request->get_param('page') ?: 1;
    
    $args = array(
        'post_type' => 'tribe_organizer',
        'post_status' => array('publish', 'draft'),
        'posts_per_page' => $per_page,
        'paged' => $page,
        'orderby' => 'title',
        'order' => 'ASC',
    );
    
    if (!empty($search)) {
        $args['s'] = $search;
    }
    
    $query = new WP_Query($args);
    $organizers = array();
    
    foreach ($query->posts as $post) {
        $organizers[] = array(
            'id' => $post->ID,
            'title' => array('rendered' => $post->post_title),
            'link' => get_permalink($post->ID),
        );
    }
    
    return rest_ensure_response($organizers);
}

function get_tribe_venues($request) {
    $search = $request->get_param('search');
    $per_page = $request->get_param('per_page') ?: 10;
    $page = $request->get_param('page') ?: 1;
    
    $args = array(
        'post_type' => 'tribe_venue',
        'post_status' => array('publish', 'draft'),
        'posts_per_page' => $per_page,
        'paged' => $page,
        'orderby' => 'title',
        'order' => 'ASC',
    );
    
    if (!empty($search)) {
        $args['s'] = $search;
    }
    
    $query = new WP_Query($args);
    $venues = array();
    
    foreach ($query->posts as $post) {
        $venues[] = array(
            'id' => $post->ID,
            'title' => array('rendered' => $post->post_title),
            'link' => get_permalink($post->ID),
        );
    }
    
    return rest_ensure_response($venues);
}

// Add width/height controls to Image block
add_action('enqueue_block_editor_assets', function() {
    wp_enqueue_script(
        'tailpress-image-dimensions',
        get_template_directory_uri() . '/resources/js/editor/inspector/image-dimensions.js',
        array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-hooks', 'wp-i18n'),
        filemtime(get_template_directory() . '/resources/js/editor/inspector/image-dimensions.js'),
        true
    );

    wp_enqueue_script(
        'tailpress-spacing-controls',
        get_template_directory_uri() . '/resources/js/editor/inspector/spacing-controls.js',
        array('wp-blocks', 'wp-hooks', 'wp-compose', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-dom-ready'),
        filemtime(get_template_directory() . '/resources/js/editor/inspector/spacing-controls.js'),
        true
    );

    $gcb_color_palette_deps = array('wp-hooks', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n');

    if (wp_script_is('genesis-custom-blocks-blocks', 'registered')) {
        $gcb_color_palette_deps[] = 'genesis-custom-blocks-blocks';
    }

    if (wp_script_is('genesis-custom-blocks-edit-block-script', 'registered')) {
        $gcb_color_palette_deps[] = 'genesis-custom-blocks-edit-block-script';
    }

    wp_enqueue_script(
        'tailpress-gcb-color-palette',
        get_template_directory_uri() . '/resources/js/editor/inspector/gcb-color-palette.js',
        $gcb_color_palette_deps,
        filemtime(get_template_directory() . '/resources/js/editor/inspector/gcb-color-palette.js'),
        true
    );

    wp_enqueue_script(
        'tailpress-gcb-renderer-request-fix',
        get_template_directory_uri() . '/resources/js/editor/inspector/gcb-renderer-request-fix.js',
        array('wp-api-fetch'),
        filemtime(get_template_directory() . '/resources/js/editor/inspector/gcb-renderer-request-fix.js'),
        false
    );

    wp_enqueue_script(
        'tailpress-mpma-card-defaults',
        get_template_directory_uri() . '/resources/js/editor/defaults/mpma-card-defaults.js',
        array('wp-blocks', 'wp-dom-ready'),
        filemtime(get_template_directory() . '/resources/js/editor/defaults/mpma-card-defaults.js'),
        true
    );

    wp_enqueue_script(
        'tailpress-mpma-card-tile-defaults',
        get_template_directory_uri() . '/resources/js/editor/defaults/mpma-card-tile-defaults.js',
        array('wp-blocks', 'wp-dom-ready'),
        filemtime(get_template_directory() . '/resources/js/editor/defaults/mpma-card-tile-defaults.js'),
        true
    );

    wp_enqueue_script(
        'tailpress-mpma-sponsorship-defaults',
        get_template_directory_uri() . '/resources/js/editor/defaults/mpma-sponsorship-defaults.js',
        array('wp-blocks', 'wp-dom-ready'),
        filemtime(get_template_directory() . '/resources/js/editor/defaults/mpma-sponsorship-defaults.js'),
        true
    );

    wp_enqueue_script(
        'tailpress-mpma-hero-with-carousel-defaults',
        get_template_directory_uri() . '/resources/js/editor/defaults/mpma-hero-with-carousel-defaults.js',
        array('wp-blocks', 'wp-dom-ready'),
        filemtime(get_template_directory() . '/resources/js/editor/defaults/mpma-hero-with-carousel-defaults.js'),
        true
    );

    wp_enqueue_script(
        'tailpress-mpma-internal-layout-defaults',
        get_template_directory_uri() . '/resources/js/editor/defaults/mpma-internal-layout-defaults.js',
        array('wp-blocks', 'wp-dom-ready'),
        filemtime(get_template_directory() . '/resources/js/editor/defaults/mpma-internal-layout-defaults.js'),
        true
    );

    wp_enqueue_script(
        'tailpress-accordion-editor-defaults',
        get_template_directory_uri() . '/resources/js/editor/defaults/accordion-editor-defaults.js',
        array('wp-hooks'),
        filemtime(get_template_directory() . '/resources/js/editor/defaults/accordion-editor-defaults.js'),
        true
    );
});

add_action('enqueue_block_editor_assets', function() {
    $editor_control_css = '
    .block-editor-block-inspector .components-base-control,
    .block-editor-block-inspector .components-input-control,
    .block-editor-block-inspector .components-number-control,
    .block-editor-block-inspector .components-text-control,
    .block-editor-block-inspector .components-input-control__container,
    .block-editor-block-inspector .components-number-control__container {
        width: 100% !important;
        max-width: none !important;
        min-width: 0 !important;
        flex: 1 1 auto !important;
    }

    .block-editor-block-inspector .components-input-control__input,
    .block-editor-block-inspector .components-number-control__input,
    .block-editor-block-inspector .components-unit-control__input,
    .block-editor-block-inspector .components-text-control__input {
        width: 100% !important;
        max-width: none !important;
        min-width: 0 !important;
        color: #1e1e1e !important;
        -webkit-text-fill-color: #1e1e1e !important;
        opacity: 1 !important;
        text-indent: 0 !important;
        font-size: 13px !important;
        line-height: 1.3 !important;
    }

    .block-editor-block-inspector .gcb-editor-form .genesis-custom-blocks-color-control .components-base-control.genesis-custom-blocks-color-popover,
    .block-editor-block-inspector .gcb-inspector-form .genesis-custom-blocks-color-control .components-base-control.genesis-custom-blocks-color-popover {
        width: auto !important;
        height: auto !important;
        max-width: fit-content !important;
        max-height: none !important;
        min-width: auto !important;
        min-height: 0 !important;
        flex: 0 0 auto !important;
    }

    .block-editor-block-inspector .components-color-indicator,
    .block-editor-block-inspector .component-color-indicator {
        width: 1.5rem !important;
        height: 1.5rem !important;
        min-width: 1.5rem !important;
        min-height: 1.5rem !important;
        max-width: 1.5rem !important;
        max-height: 1.5rem !important;
        flex: 0 0 1.5rem !important;
        display: inline-block !important;
        border-radius: 9999px !important;
        vertical-align: middle !important;
        align-self: center !important;
        margin: 0 !important;
        line-height: 0 !important;
        position: relative !important;
        top: 0 !important;
        transform: none !important;
        overflow: hidden !important;
    }

    .block-editor-block-inspector .gcb-editor-form .genesis-custom-blocks-color-control .components-base-control.genesis-custom-blocks-color-popover .component-color-indicator,
    .block-editor-block-inspector .gcb-editor-form .genesis-custom-blocks-color-control .components-base-control.genesis-custom-blocks-color-popover .components-color-indicator,
    .block-editor-block-inspector .gcb-inspector-form .genesis-custom-blocks-color-control .components-base-control.genesis-custom-blocks-color-popover .component-color-indicator,
    .block-editor-block-inspector .gcb-inspector-form .genesis-custom-blocks-color-control .components-base-control.genesis-custom-blocks-color-popover .components-color-indicator {
        margin: 0 !important;
        margin-top: 0 !important;
        float: none !important;
        vertical-align: middle !important;
        position: relative !important;
        top: 0 !important;
        left: 0 !important;
        transform: none !important;
    }

    .block-editor-block-inspector__tabs [role="tabpanel"][aria-labelledby$="-settings"] .block-editor-line-height-control {
        display: none !important;
    }

    ';

    wp_register_style(
        'tailpress-editor-control-fixes',
        false,
        array('wp-components', 'wp-edit-blocks'),
        null
    );
    wp_enqueue_style('tailpress-editor-control-fixes');
    wp_add_inline_style('tailpress-editor-control-fixes', $editor_control_css);
}, 30);

// Register sidebar visibility meta field
add_action('init', function() {
    $register_post_types = array('page', 'post', 'tribe_events');

    foreach ($register_post_types as $post_type) {
        register_post_meta($post_type, 'show_sidebar', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'boolean',
            'default' => true,
        ));

        register_post_meta($post_type, 'sidebar_floating', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'boolean',
            'default' => true,
        ));

        register_post_meta($post_type, 'page_title_bg_enabled', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'boolean',
            'default' => false,
        ));

        register_post_meta($post_type, 'page_title_bg_image_id', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'integer',
            'default' => 0,
        ));

        register_post_meta($post_type, 'page_title_bg_subtitle', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
            'default' => '',
        ));

        register_post_meta($post_type, 'page_title_bg_use_h1', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'boolean',
            'default' => true,
        ));

        register_post_meta($post_type, 'page_title_bg_min_height', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
            'default' => '26.625rem',
        ));

        register_post_meta($post_type, 'page_title_bg_min_height_mobile', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
            'default' => '12rem',
        ));

        register_post_meta($post_type, 'page_title_bg_overlay_opacity', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'number',
            'default' => 0.95,
        ));

        register_post_meta($post_type, 'show_events_courses', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'boolean',
            'default' => true,
        ));

        register_post_meta($post_type, 'show_events_webinars', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'boolean',
            'default' => true,
        ));

        register_post_meta($post_type, 'show_events_events', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'boolean',
            'default' => true,
        ));

        register_post_meta($post_type, 'show_events_collapsible', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'boolean',
            'default' => 'page' === $post_type,
        ));
    }
});

// Add sidebar toggle to block editor
add_action('enqueue_block_editor_assets', function() {
    wp_enqueue_script(
        'tailpress-page-title-background',
        get_template_directory_uri() . '/resources/js/editor/inspector/page-title-background.js',
        array('wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n', 'wp-block-editor'),
        filemtime(get_template_directory() . '/resources/js/editor/inspector/page-title-background.js'),
        true
    );

    wp_enqueue_script(
        'tailpress-sidebar-toggle',
        get_template_directory_uri() . '/resources/js/editor/inspector/sidebar-toggle.js',
        array('wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n'),
        filemtime(get_template_directory() . '/resources/js/editor/inspector/sidebar-toggle.js'),
        true
    );
    
    wp_enqueue_script(
        'tailpress-events-sections-toggle',
        get_template_directory_uri() . '/resources/js/editor/inspector/events-sections-toggle.js',
        array('wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n'),
        filemtime(get_template_directory() . '/resources/js/editor/inspector/events-sections-toggle.js'),
        true
    );

    wp_enqueue_script(
        'tailpress-block-category-order',
        get_template_directory_uri() . '/resources/js/editor/inspector/block-category-order.js',
        array('wp-dom-ready', 'wp-data'),
        filemtime(get_template_directory() . '/resources/js/editor/inspector/block-category-order.js'),
        true
    );
});

// Expand WordPress search to include pages and events
add_action('pre_get_posts', function($query) {
    if (!is_admin() && $query->is_search() && $query->is_main_query()) {
        $query->set('post_type', array('post', 'page', 'tribe_events'));
    }
});
