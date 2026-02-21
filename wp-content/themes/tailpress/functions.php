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

// Register custom block category
add_filter('block_categories_all', function($categories) {
    return array_merge(
        array(
            array(
                'slug'  => 'mpma-custom',
                'title' => 'MPMA Custom Blocks',
                'icon'  => 'admin-customizer'
            )
        ),
        $categories
    );
});

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
    register_block_type(__DIR__ . '/inc/blocks/mpma-column');
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

// Development-only modifications
if (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'local') {
    // Enable SVG uploads
    add_filter('upload_mimes', function($mimes) {
        $mimes['svg'] = 'image/svg+xml';
        $mimes['svgz'] = 'image/svg+xml';
        return $mimes;
    });

    // Fix MIME type detection for SVG
    add_filter('wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {
        $filetype = wp_check_filetype($filename, $mimes);
        return [
            'ext'             => $filetype['ext'],
            'type'            => $filetype['type'],
            'proper_filename' => $data['proper_filename']
        ];
    }, 10, 4);

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
        get_template_directory_uri() . '/resources/js/image-dimensions.js',
        array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-hooks', 'wp-i18n'),
        filemtime(get_template_directory() . '/resources/js/image-dimensions.js'),
        true
    );
});

// Register sidebar visibility meta field
add_action('init', function() {
    register_post_meta('page', 'show_sidebar', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'boolean',
        'default' => true,
    ));
    
    // Register event section visibility meta fields
    register_post_meta('page', 'show_events_courses', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'boolean',
        'default' => true,
    ));
    
    register_post_meta('page', 'show_events_webinars', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'boolean',
        'default' => true,
    ));
    
    register_post_meta('page', 'show_events_events', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'boolean',
        'default' => true,
    ));
});

// Add sidebar toggle to block editor
add_action('enqueue_block_editor_assets', function() {
    wp_enqueue_script(
        'tailpress-sidebar-toggle',
        get_template_directory_uri() . '/resources/js/sidebar-toggle.js',
        array('wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n'),
        filemtime(get_template_directory() . '/resources/js/sidebar-toggle.js'),
        true
    );
    
    wp_enqueue_script(
        'tailpress-events-sections-toggle',
        get_template_directory_uri() . '/resources/js/events-sections-toggle.js',
        array('wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n'),
        filemtime(get_template_directory() . '/resources/js/events-sections-toggle.js'),
        true
    );
});

// Expand WordPress search to include pages and events
add_action('pre_get_posts', function($query) {
    if (!is_admin() && $query->is_search() && $query->is_main_query()) {
        $query->set('post_type', array('post', 'page', 'tribe_events'));
    }
});
