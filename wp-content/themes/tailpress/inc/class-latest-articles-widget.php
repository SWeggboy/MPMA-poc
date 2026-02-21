<?php
/**
 * Latest Articles Widget
 *
 * @package TailPress
 */

if (!defined('ABSPATH')) {
    exit;
}

class Latest_Articles_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'latest_articles_widget',
            __('Latest Articles (External)', 'tailpress'),
            array('description' => __('Display latest articles from Gear Technology and Power Transmission', 'tailpress'))
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];

        $title = !empty($instance['title']) ? $instance['title'] : __('Read Our Latest Articles', 'tailpress');
        $num_articles = !empty($instance['num_articles']) ? intval($instance['num_articles']) : 3;
        $num_articles = max(1, min(6, $num_articles)); // Limit between 1 and 6
        
        echo $args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'];

        // Build articles array from widget settings
        $all_articles = array();
        
        for ($i = 1; $i <= 6; $i++) {
            $article_title = !empty($instance["article_{$i}_title"]) ? $instance["article_{$i}_title"] : '';
            $article_link = !empty($instance["article_{$i}_link"]) ? $instance["article_{$i}_link"] : '';
            $article_date = !empty($instance["article_{$i}_date"]) ? $instance["article_{$i}_date"] : '';
            $article_source = !empty($instance["article_{$i}_source"]) ? $instance["article_{$i}_source"] : '';
            
            // Only add if at least title and link are provided
            if (!empty($article_title) && !empty($article_link)) {
                $all_articles[] = array(
                    'title' => $article_title,
                    'link' => $article_link,
                    'date' => !empty($article_date) ? strtotime($article_date) : time(),
                    'date_formatted' => !empty($article_date) ? date('F j, Y', strtotime($article_date)) : date('F j, Y'),
                    'source' => !empty($article_source) ? $article_source : 'External Source'
                );
            }
        }
        
        // Sort by date (newest first)
        if (!empty($all_articles)) {
            usort($all_articles, function($a, $b) {
                return $b['date'] - $a['date'];
            });
            
            // Limit to requested number
            $all_articles = array_slice($all_articles, 0, $num_articles);
        }
        
        echo '<div class="latest-articles-widget">';
        
        if (!empty($all_articles)) {
            echo '<div class="latest-articles-list space-y-4">';
            
            foreach ($all_articles as $article) {
                $article_title = esc_html($article['title']);
                $article_link = esc_url($article['link']);
                $article_date = $article['date_formatted'];
                $article_source = esc_html($article['source']);
                
                echo '<div class="latest-article-item border-b border-gray-200 pb-3">';
                echo '<h4 class="text-base font-semibold mb-1">';
                echo '<a href="' . $article_link . '" target="_blank" rel="noopener noreferrer" class="text-mpma-blue hover:text-mpma-orange transition-colors">';
                echo $article_title;
                echo '</a>';
                echo '</h4>';
                echo '<div class="text-sm text-gray-600">';
                echo '<span class="font-medium">' . $article_source . '</span>';
                echo ' &bull; ';
                echo '<time>' . $article_date . '</time>';
                echo '</div>';
                echo '</div>';
            }
            
            echo '</div>';
        } else {
            echo '<p class="text-sm text-gray-600">' . __('No articles available at this time. Please check back later.', 'tailpress') . '</p>';
        }
        
        echo '</div>'; // .latest-articles-widget

        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Read Our Latest Articles', 'tailpress');
        $num_articles = !empty($instance['num_articles']) ? intval($instance['num_articles']) : 3;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php _e('Title:', 'tailpress'); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('num_articles')); ?>">
                <?php _e('Number of Articles to Display (1-6):', 'tailpress'); ?>
            </label>
            <input class="tiny-text" 
                   id="<?php echo esc_attr($this->get_field_id('num_articles')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('num_articles')); ?>" 
                   type="number" 
                   min="1"
                   max="6"
                   value="<?php echo esc_attr($num_articles); ?>"
                   style="width: 60px;">
        </p>
        
        <hr style="margin: 15px 0;">
        
        <p style="margin-bottom: 15px;">
            <strong><?php _e('Articles (enter up to 6):', 'tailpress'); ?></strong><br>
            <small><?php _e('Articles will be automatically sorted by date. Only filled entries will be displayed.', 'tailpress'); ?></small>
        </p>
        
        <?php for ($i = 1; $i <= 6; $i++): 
            $article_title = !empty($instance["article_{$i}_title"]) ? $instance["article_{$i}_title"] : '';
            $article_link = !empty($instance["article_{$i}_link"]) ? $instance["article_{$i}_link"] : '';
            $article_date = !empty($instance["article_{$i}_date"]) ? $instance["article_{$i}_date"] : '';
            $article_source = !empty($instance["article_{$i}_source"]) ? $instance["article_{$i}_source"] : '';
        ?>
        
        <div style="background: #f5f5f5; padding: 10px; margin-bottom: 15px; border-left: 3px solid #0073aa;">
            <p style="margin: 0 0 8px 0; font-weight: bold;">Article <?php echo $i; ?></p>
            
            <p style="margin: 0 0 8px 0;">
                <label for="<?php echo esc_attr($this->get_field_id("article_{$i}_title")); ?>" style="display: block; margin-bottom: 3px;">
                    <?php _e('Title:', 'tailpress'); ?>
                </label>
                <input class="widefat" 
                       id="<?php echo esc_attr($this->get_field_id("article_{$i}_title")); ?>" 
                       name="<?php echo esc_attr($this->get_field_name("article_{$i}_title")); ?>" 
                       type="text" 
                       value="<?php echo esc_attr($article_title); ?>"
                       placeholder="Article title">
            </p>
            
            <p style="margin: 0 0 8px 0;">
                <label for="<?php echo esc_attr($this->get_field_id("article_{$i}_link")); ?>" style="display: block; margin-bottom: 3px;">
                    <?php _e('Link:', 'tailpress'); ?>
                </label>
                <input class="widefat" 
                       id="<?php echo esc_attr($this->get_field_id("article_{$i}_link")); ?>" 
                       name="<?php echo esc_attr($this->get_field_name("article_{$i}_link")); ?>" 
                       type="url" 
                       value="<?php echo esc_attr($article_link); ?>"
                       placeholder="https://">
            </p>
            
            <p style="margin: 0 0 8px 0;">
                <label for="<?php echo esc_attr($this->get_field_id("article_{$i}_date")); ?>" style="display: block; margin-bottom: 3px;">
                    <?php _e('Date:', 'tailpress'); ?>
                </label>
                <input class="widefat" 
                       id="<?php echo esc_attr($this->get_field_id("article_{$i}_date")); ?>" 
                       name="<?php echo esc_attr($this->get_field_name("article_{$i}_date")); ?>" 
                       type="date" 
                       value="<?php echo esc_attr($article_date); ?>">
            </p>
            
            <p style="margin: 0;">
                <label for="<?php echo esc_attr($this->get_field_id("article_{$i}_source")); ?>" style="display: block; margin-bottom: 3px;">
                    <?php _e('Source:', 'tailpress'); ?>
                </label>
                <input class="widefat" 
                       id="<?php echo esc_attr($this->get_field_id("article_{$i}_source")); ?>" 
                       name="<?php echo esc_attr($this->get_field_name("article_{$i}_source")); ?>" 
                       type="text" 
                       value="<?php echo esc_attr($article_source); ?>"
                       placeholder="e.g., Gear Technology">
            </p>
        </div>
        
        <?php endfor; ?>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['num_articles'] = (!empty($new_instance['num_articles'])) ? intval($new_instance['num_articles']) : 3;
        $instance['num_articles'] = max(1, min(6, $instance['num_articles'])); // Ensure between 1-6
        
        // Save all article fields
        for ($i = 1; $i <= 6; $i++) {
            $instance["article_{$i}_title"] = (!empty($new_instance["article_{$i}_title"])) ? sanitize_text_field($new_instance["article_{$i}_title"]) : '';
            $instance["article_{$i}_link"] = (!empty($new_instance["article_{$i}_link"])) ? esc_url_raw($new_instance["article_{$i}_link"]) : '';
            $instance["article_{$i}_date"] = (!empty($new_instance["article_{$i}_date"])) ? sanitize_text_field($new_instance["article_{$i}_date"]) : '';
            $instance["article_{$i}_source"] = (!empty($new_instance["article_{$i}_source"])) ? sanitize_text_field($new_instance["article_{$i}_source"]) : '';
        }
        
        return $instance;
    }
}

// Register the widget
function tailpress_register_latest_articles_widget() {
    if (class_exists('Latest_Articles_Widget')) {
        register_widget('Latest_Articles_Widget');
    }
}
add_action('widgets_init', 'tailpress_register_latest_articles_widget');
