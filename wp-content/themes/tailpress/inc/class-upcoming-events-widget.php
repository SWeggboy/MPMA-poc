<?php
/**
 * Upcoming Events Widget
 *
 * @package TailPress
 */

if (!defined('ABSPATH')) {
    exit;
}

class Upcoming_Events_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'upcoming_events_widget',
            __('Upcoming Events', 'tailpress'),
            array('description' => __('Display upcoming events from The Events Calendar', 'tailpress'))
        );
    }

    public function widget($args, $instance) {
        if (!function_exists('tribe_get_events')) {
            return;
        }

        echo $args['before_widget'];

        // Get page-level settings (if on a page)
        $page_id = get_queried_object_id();
        
        $page_show_courses = get_post_meta($page_id, 'show_events_courses', true);
        $page_show_webinars = get_post_meta($page_id, 'show_events_webinars', true);
        $page_show_events = get_post_meta($page_id, 'show_events_events', true);
        $page_events_collapsible = get_post_meta($page_id, 'show_events_collapsible', true);
        
        // Convert to boolean
        // WordPress stores true as '1' (string) and false as '' (empty string) when using boolean type in REST API
        // Check if meta exists first - if not, default to true
        $courses_exists = metadata_exists('post', $page_id, 'show_events_courses');
        $webinars_exists = metadata_exists('post', $page_id, 'show_events_webinars');
        $events_exists = metadata_exists('post', $page_id, 'show_events_events');
        $collapsible_exists = metadata_exists('post', $page_id, 'show_events_collapsible');
        
        $page_show_courses = !$courses_exists ? true : ($page_show_courses === '1' || $page_show_courses === 1 || $page_show_courses === true);
        $page_show_webinars = !$webinars_exists ? true : ($page_show_webinars === '1' || $page_show_webinars === 1 || $page_show_webinars === true);
        $page_show_events = !$events_exists ? true : ($page_show_events === '1' || $page_show_events === 1 || $page_show_events === true);
        $default_events_collapsible = get_post_type($page_id) === 'page';
        $events_collapsible = !$collapsible_exists ? $default_events_collapsible : ($page_events_collapsible === '1' || $page_events_collapsible === 1 || $page_events_collapsible === true);

        // Get widget-level toggle settings
        $widget_show_courses = !isset($instance['show_courses']) || $instance['show_courses'];
        $widget_show_webinars = !isset($instance['show_webinars']) || $instance['show_webinars'];
        $widget_show_events = !isset($instance['show_events']) || $instance['show_events'];
        
        // Both page and widget must allow the section
        $show_courses = $page_show_courses && $widget_show_courses;
        $show_webinars = $page_show_webinars && $widget_show_webinars;
        $show_events = $page_show_events && $widget_show_events;

        // Helper function to get events by category
        $get_events_by_category = function($category_slug, $limit = 3) {
            $category = get_term_by('slug', $category_slug, 'tribe_events_cat');
            if (!$category) {
                return [];
            }

            // Get category and all child categories
            $category_ids = [$category->term_id];
            $children = get_term_children($category->term_id, 'tribe_events_cat');
            if (!is_wp_error($children)) {
                $category_ids = array_merge($category_ids, $children);
            }

            $args = [
                'posts_per_page' => $limit,
                'start_date' => 'now',
                'orderby' => 'event_date',
                'order' => 'ASC',
                'tax_query' => [
                    [
                        'taxonomy' => 'tribe_events_cat',
                        'field' => 'term_id',
                        'terms' => $category_ids,
                        'include_children' => false,
                    ]
                ]
            ];

            return tribe_get_events($args);
        };

        $visible_sections = array_values(array_filter([
            $show_courses ? 'courses' : null,
            $show_webinars ? 'webinars' : null,
            $show_events ? 'events' : null,
        ]));

        $default_open_section = $visible_sections[0] ?? '';

        // Render section function
        $render_section = function($events, $title, $section_slug, $is_expanded_by_default = false) use ($args, $events_collapsible) {
            if (empty($events)) {
                return;
            }

            if (!$events_collapsible) {
                echo '<div class="tribe-events-section mb-8 last:mb-0" data-sidebar-section="' . esc_attr($section_slug) . '">';
                echo $args['before_title'] . esc_html($title) . $args['after_title'];
                echo '<div class="tribe-events-widget-events-list">';
            } else {
            $section_id = 'tribe-events-section-' . sanitize_html_class($section_slug) . '-' . wp_unique_id();
            $toggle_id = $section_id . '-toggle';
            $content_id = $section_id . '-content';

            echo '<section class="tribe-events-section tribe-events-section--accordion mb-6 last:mb-0" data-sidebar-section="' . esc_attr($section_slug) . '">';
            echo '<h2 class="tribe-events-section__heading widget-title text-2xl font-semibold mb-0">';
            echo '<button'
                . ' type="button"'
                . ' id="' . esc_attr($toggle_id) . '"'
                . ' class="tribe-events-section__toggle flex w-full items-center justify-between gap-4 py-1 text-left"'
                . ' aria-expanded="' . ($is_expanded_by_default ? 'true' : 'false') . '"'
                . ' aria-controls="' . esc_attr($content_id) . '"'
                . '>';
            echo '<span class="tribe-events-section__toggle-label">' . esc_html($title) . '</span>';
            echo '<span class="tribe-events-section__toggle-icon inline-flex h-7 w-7 items-center justify-center text-primary transition-transform duration-300" aria-hidden="true">';
            echo '<svg viewBox="0 0 20 20" fill="currentColor" class="h-7 w-7">';
            echo '<path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />';
            echo '</svg>';
            echo '</span>';
            echo '</button>';
            echo '</h2>';
            echo '<div'
                . ' id="' . esc_attr($content_id) . '"'
                . ' class="tribe-events-section__panel overflow-hidden"'
                . ' role="region"'
                . ' aria-labelledby="' . esc_attr($toggle_id) . '"'
                . ' style="' . esc_attr($is_expanded_by_default ? 'max-height:none;opacity:1;' : 'max-height:0;opacity:0;') . '"'
                . ($is_expanded_by_default ? '' : ' hidden')
                . '>';
            echo '<div class="tribe-events-section__panel-inner pt-4">';
            echo '<div class="tribe-events-widget-events-list">';
            }
            
            foreach ($events as $event) {
                setup_postdata($event);
                $event_id = $event->ID;
                $is_featured = get_post_meta($event_id, '_tribe_featured', true) === '1';
                
                $row_classes = 'tribe-events-widget-events-list__event-row flex gap-4 pb-4 mb-4 last:mb-0 border-b border-gray-200 last:border-0 p-4';
                
                echo '<div class="' . esc_attr($row_classes) . '">';
                
                // Date tag (left side)
                $date_tag_classes = 'tribe-events-widget-events-list__event-date-tag flex-shrink-0 text-center rounded px-3 py-2 min-w-[60px]';
                $date_tag_classes .= $is_featured ? ' bg-primary' : ' bg-gray-200';
                echo '<div class="' . esc_attr($date_tag_classes) . '">';
                echo '<time datetime="' . esc_attr(tribe_get_start_date($event_id, false, 'Y-m-d')) . '">';
                $month_classes = 'tribe-events-widget-events-list__event-date-tag-month block text-xs font-semibold uppercase';
                $month_classes .= $is_featured ? ' text-white' : ' text-gray-600';
                echo '<span class="' . esc_attr($month_classes) . '">';
                echo tribe_get_start_date($event_id, false, 'M');
                echo '</span>';
                $day_classes = 'tribe-events-widget-events-list__event-date-tag-daynum block text-2xl font-bold leading-tight';
                $day_classes .= $is_featured ? ' text-white' : ' text-gray-900';
                echo '<span class="' . esc_attr($day_classes) . '">';
                echo tribe_get_start_date($event_id, false, 'j');
                echo '</span>';
                echo '</time>';
                echo '</div>';
                
                // Event details (right side)
                echo '<div class="tribe-events-widget-events-list__event-wrapper flex-1">';
                echo '<article class="tribe-events-widget-events-list__event">';
                echo '<div class="tribe-events-widget-events-list__event-details">';
                
                echo '<header class="tribe-events-widget-events-list__event-header">';
                
                // Featured label
                if ($is_featured) {
                    echo '<div class="tribe-events-widget-events-list__event-featured-label text-xs font-semibold text-primary mb-1">';
                    echo '<em class="tribe-events-calendar-list__event-datetime-featured-icon inline-block mr-1">';
                    echo '<svg class="tribe-common-c-svgicon tribe-common-c-svgicon--featured tribe-events-calendar-list__event-datetime-featured-icon-svg inline-block w-2 h-2.5 fill-current" aria-hidden="true" viewBox="0 0 8 10" xmlns="http://www.w3.org/2000/svg">';
                    echo '<path fill-rule="evenodd" clip-rule="evenodd" d="M0 0h8v10L4.049 7.439 0 10V0z"/>';
                    echo '</svg>';
                    echo '</em>';
                    echo 'Featured';
                    echo '</div>';
                }
                
                // Title
                $title_link_classes = 'tribe-events-widget-events-list__event-title-link !no-underline';
                echo '<h3 class="tribe-events-widget-events-list__event-title text-base font-semibold mb-2 leading-snug">';
                echo '<a href="' . esc_url(get_permalink($event_id)) . '" class="' . esc_attr($title_link_classes) . '">';
                echo esc_html(get_the_title($event_id));
                echo '</a>';
                echo '</h3>';
                
                // Categories as pills (exclude section parent categories)
                $categories = get_the_terms($event_id, 'tribe_events_cat');
                if ($categories && !is_wp_error($categories)) {
                    // Filter out the main section categories
                    $excluded_slugs = ['courses', 'webinars', 'events'];
                    $filtered_categories = array_filter($categories, function($cat) use ($excluded_slugs) {
                        return !in_array($cat->slug, $excluded_slugs);
                    });
                    
                    if (!empty($filtered_categories)) {
                        echo '<div class="tribe-events-widget-events-list__event-categories flex flex-wrap gap-2 mb-2">';
                        foreach ($filtered_categories as $category) {
                            $category_link = get_term_link($category);
                            $is_parent = $category->parent === 0;
                            
                            if ($is_parent) {
                                $pill_classes = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-accent text-white hover:bg-accent/80 transition-colors duration-200';
                            } else {
                                $pill_classes = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800 hover:bg-gray-300 hover:text-gray-800/80 transition-colors duration-200';
                            }
                            
                            echo '<a href="' . esc_url($category_link) . '" class="' . esc_attr($pill_classes) . ' !no-underline">';
                            echo esc_html($category->name);
                            echo '</a>';
                        }
                        echo '</div>';
                    }
                }
                
                // Date and time details
                $datetime_classes = 'tribe-events-widget-events-list__event-datetime-wrapper text-sm text-gray-600';
                echo '<div class="' . esc_attr($datetime_classes) . '">';
                echo tribe_get_start_date($event_id, false, 'F j, Y');
                if (tribe_get_start_date($event_id, false, 'g:i a') !== '12:00 am') {
                    echo ' @ ' . tribe_get_start_date($event_id, false, 'g:i a');
                }
                echo '</div>';
                
                echo '</header>';
                echo '</div>';
                echo '</article>';
                echo '</div>';
                
                echo '</div>';
            }
            
            if (!$events_collapsible) {
                echo '</div>';
                echo '<div class="border-b border-gray-200 mt-4"></div>';
                echo '</div>';
            } else {
                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '</section>';
            }
            
            wp_reset_postdata();
        };

        // Render each section based on toggle settings
        if ($show_courses) {
            $courses = $get_events_by_category('courses', 3);
            $render_section($courses, __('Upcoming Courses', 'tailpress'), 'courses', $default_open_section === 'courses');
        }

        if ($show_webinars) {
            $webinars = $get_events_by_category('webinars', 3);
            $render_section($webinars, __('Upcoming Webinars', 'tailpress'), 'webinars', $default_open_section === 'webinars');
        }

        if ($show_events) {
            $events = $get_events_by_category('events', 3);
            $render_section($events, __('Upcoming Events', 'tailpress'), 'events', $default_open_section === 'events');
        }

        // View all link - only show if at least one section is enabled
        if ($show_courses || $show_webinars || $show_events) {
            echo '<div class="tribe-events-widget-events-list__view-more mt-4">';
            echo '<a href="' . esc_url(tribe_get_events_link()) . '" class="tribe-events-widget-events-list__view-more-link inline-flex items-center text-sm font-medium text-primary hover:text-primary-dark no-underline">';
            echo __('View All Events', 'tailpress');
            echo ' <span class="ml-1">→</span>';
            echo '</a>';
            echo '</div>';
        }

        echo $args['after_widget'];
    }

    public function form($instance) {
        $show_courses = !isset($instance['show_courses']) || $instance['show_courses'];
        $show_webinars = !isset($instance['show_webinars']) || $instance['show_webinars'];
        $show_events = !isset($instance['show_events']) || $instance['show_events'];
        ?>
        <p><strong><?php _e('Widget-Level Section Controls:', 'tailpress'); ?></strong></p>
        <p class="description"><?php _e('Note: Page-level controls will also affect visibility.', 'tailpress'); ?></p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_courses); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_courses')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_courses')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_courses')); ?>">
                <?php _e('Show Upcoming Courses', 'tailpress'); ?>
            </label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_webinars); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_webinars')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_webinars')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_webinars')); ?>">
                <?php _e('Show Upcoming Webinars', 'tailpress'); ?>
            </label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_events); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_events')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_events')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_events')); ?>">
                <?php _e('Show Upcoming Events', 'tailpress'); ?>
            </label>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['show_courses'] = isset($new_instance['show_courses']) ? (bool) $new_instance['show_courses'] : false;
        $instance['show_webinars'] = isset($new_instance['show_webinars']) ? (bool) $new_instance['show_webinars'] : false;
        $instance['show_events'] = isset($new_instance['show_events']) ? (bool) $new_instance['show_events'] : false;
        return $instance;
    }
}
