<?php
/**
 * Footer Logos Widget
 *
 * @package TailPress
 */

if (!defined('ABSPATH')) {
    exit;
}

class Footer_Logos_Widget extends WP_Widget {

    private const MAX_LOGOS = 12;

    public function __construct() {
        parent::__construct(
            'footer_logos_widget',
            __('Footer Logos', 'tailpress'),
            array('description' => __('Display a configurable list of logos with custom alt text and optional links.', 'tailpress'))
        );

        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('customize_controls_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function enqueue_admin_assets(): void {
        wp_enqueue_media();
    }

    public function widget($args, $instance) {
        $num_logos = !empty($instance['num_logos']) ? (int) $instance['num_logos'] : 4;
        $num_logos = max(1, min(self::MAX_LOGOS, $num_logos));

        $logos = array();

        for ($i = 1; $i <= self::MAX_LOGOS; $i++) {
            $image_id = !empty($instance["logo_{$i}_image_id"]) ? (int) $instance["logo_{$i}_image_id"] : 0;
            $alt_text = !empty($instance["logo_{$i}_alt"]) ? $instance["logo_{$i}_alt"] : '';
            $link = !empty($instance["logo_{$i}_link"]) ? $instance["logo_{$i}_link"] : '';

            if ($image_id > 0) {
                $image_src = wp_get_attachment_image_url($image_id, 'full');
                if (!$image_src) {
                    continue;
                }

                if (empty($alt_text)) {
                    $alt_text = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                }

                if (empty($alt_text)) {
                    $alt_text = get_the_title($image_id);
                }

                $logos[] = array(
                    'image_id' => $image_id,
                    'alt' => $alt_text,
                    'link' => $link,
                );
            }
        }

        if (empty($logos)) {
            return;
        }

        $logos = array_slice($logos, 0, $num_logos);

        echo $args['before_widget'];
        echo '<div class="footer-logos-widget flex flex-wrap items-center justify-center gap-6 md:gap-10">';

        foreach ($logos as $logo) {
            $image_html = wp_get_attachment_image(
                $logo['image_id'],
                'full',
                false,
                array(
                    'class' => 'h-12 w-auto',
                    'alt' => $logo['alt'],
                    'loading' => 'lazy',
                    'decoding' => 'async',
                )
            );

            if (empty($image_html)) {
                continue;
            }

            $link = !empty($logo['link']) ? esc_url($logo['link']) : '';

            echo '<div class="footer-logo-item">';

            if (!empty($link)) {
                echo '<a href="' . $link . '" target="_blank" rel="noopener noreferrer" class="inline-flex items-center">' . $image_html . '</a>';
            } else {
                echo $image_html;
            }

            echo '</div>';
        }

        echo '</div>';
        echo $args['after_widget'];
    }

    public function form($instance) {
        $num_logos = !empty($instance['num_logos']) ? (int) $instance['num_logos'] : 4;
        $num_logos = max(1, min(self::MAX_LOGOS, $num_logos));
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('num_logos')); ?>">
                <?php _e('Number of Logos to Display (1-12):', 'tailpress'); ?>
            </label>
            <input class="tiny-text"
                   id="<?php echo esc_attr($this->get_field_id('num_logos')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('num_logos')); ?>"
                   type="number"
                   min="1"
                   max="12"
                   value="<?php echo esc_attr($num_logos); ?>"
                   style="width: 60px;">
        </p>

        <hr style="margin: 15px 0;">
        <p style="margin-bottom: 15px;">
            <strong><?php _e('Logos:', 'tailpress'); ?></strong><br>
            <small><?php _e('Select logos from the Media Library, set custom alt text, and optionally add external links.', 'tailpress'); ?></small>
        </p>

        <?php for ($i = 1; $i <= self::MAX_LOGOS; $i++):
            $image_id = !empty($instance["logo_{$i}_image_id"]) ? (int) $instance["logo_{$i}_image_id"] : 0;
            $alt_text = !empty($instance["logo_{$i}_alt"]) ? $instance["logo_{$i}_alt"] : '';
            $link = !empty($instance["logo_{$i}_link"]) ? $instance["logo_{$i}_link"] : '';

            $preview_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
            $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
        ?>
        <div class="tailpress-logo-row" style="background: #f5f5f5; padding: 10px; margin-bottom: 15px; border-left: 3px solid #0073aa;">
            <p style="margin: 0 0 8px 0; font-weight: bold;">Logo <?php echo (int) $i; ?></p>

            <input type="hidden"
                   class="tailpress-logo-image-id"
                   id="<?php echo esc_attr($this->get_field_id("logo_{$i}_image_id")); ?>"
                   name="<?php echo esc_attr($this->get_field_name("logo_{$i}_image_id")); ?>"
                   value="<?php echo esc_attr($image_id); ?>">

            <p style="margin: 0 0 8px 0;">
                <img class="tailpress-logo-preview"
                     src="<?php echo esc_url($preview_url); ?>"
                     alt=""
                     style="max-width: 120px; height: auto; display: <?php echo !empty($preview_url) ? 'block' : 'none'; ?>; margin-bottom: 8px;">

                <input type="text"
                       class="widefat tailpress-logo-image-url"
                       value="<?php echo esc_attr($image_url); ?>"
                       readonly
                       placeholder="No image selected"
                       style="margin-bottom: 6px;">

                <button type="button" class="button tailpress-select-logo"><?php _e('Select Logo', 'tailpress'); ?></button>
                <button type="button" class="button tailpress-remove-logo"><?php _e('Remove', 'tailpress'); ?></button>
            </p>

            <p style="margin: 0 0 8px 0;">
                <label for="<?php echo esc_attr($this->get_field_id("logo_{$i}_alt")); ?>" style="display: block; margin-bottom: 3px;">
                    <?php _e('Alt Text:', 'tailpress'); ?>
                </label>
                <input class="widefat"
                       id="<?php echo esc_attr($this->get_field_id("logo_{$i}_alt")); ?>"
                       name="<?php echo esc_attr($this->get_field_name("logo_{$i}_alt")); ?>"
                       type="text"
                       value="<?php echo esc_attr($alt_text); ?>"
                       placeholder="Descriptive alt text">
            </p>

            <p style="margin: 0;">
                <label for="<?php echo esc_attr($this->get_field_id("logo_{$i}_link")); ?>" style="display: block; margin-bottom: 3px;">
                    <?php _e('External Link (optional):', 'tailpress'); ?>
                </label>
                <input class="widefat"
                       id="<?php echo esc_attr($this->get_field_id("logo_{$i}_link")); ?>"
                       name="<?php echo esc_attr($this->get_field_name("logo_{$i}_link")); ?>"
                       type="url"
                       value="<?php echo esc_attr($link); ?>"
                       placeholder="https://">
            </p>
        </div>
        <?php endfor; ?>

        <?php
        static $tailpress_footer_logos_media_script_printed = false;

        if (!$tailpress_footer_logos_media_script_printed) :
            $tailpress_footer_logos_media_script_printed = true;
            ?>
            <script>
                (function($){
                    function openMediaFrame($row) {
                        const frame = wp.media({
                            title: 'Select Logo',
                            button: { text: 'Use this logo' },
                            multiple: false,
                            library: { type: 'image' }
                        });

                        frame.on('select', function() {
                            const attachment = frame.state().get('selection').first().toJSON();
                            const previewUrl = (attachment.sizes && attachment.sizes.thumbnail) ? attachment.sizes.thumbnail.url : attachment.url;

                            $row.find('.tailpress-logo-image-id').val(attachment.id);
                            $row.find('.tailpress-logo-image-url').val(attachment.url);
                            $row.find('.tailpress-logo-preview').attr('src', previewUrl).show();
                        });

                        frame.open();
                    }

                    $(document).on('click', '.tailpress-select-logo', function(e){
                        e.preventDefault();
                        const $row = $(this).closest('.tailpress-logo-row');
                        openMediaFrame($row);
                    });

                    $(document).on('click', '.tailpress-remove-logo', function(e){
                        e.preventDefault();
                        const $row = $(this).closest('.tailpress-logo-row');
                        $row.find('.tailpress-logo-image-id').val('');
                        $row.find('.tailpress-logo-image-url').val('');
                        $row.find('.tailpress-logo-preview').attr('src', '').hide();
                    });
                })(jQuery);
            </script>
            <?php
        endif;
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['num_logos'] = !empty($new_instance['num_logos']) ? (int) $new_instance['num_logos'] : 4;
        $instance['num_logos'] = max(1, min(self::MAX_LOGOS, $instance['num_logos']));

        for ($i = 1; $i <= self::MAX_LOGOS; $i++) {
            $instance["logo_{$i}_image_id"] = !empty($new_instance["logo_{$i}_image_id"]) ? (int) $new_instance["logo_{$i}_image_id"] : 0;
            $instance["logo_{$i}_alt"] = !empty($new_instance["logo_{$i}_alt"]) ? sanitize_text_field($new_instance["logo_{$i}_alt"]) : '';
            $instance["logo_{$i}_link"] = !empty($new_instance["logo_{$i}_link"]) ? esc_url_raw($new_instance["logo_{$i}_link"]) : '';
        }

        return $instance;
    }
}

function tailpress_register_footer_logos_widget() {
    if (class_exists('Footer_Logos_Widget')) {
        register_widget('Footer_Logos_Widget');
    }
}
add_action('widgets_init', 'tailpress_register_footer_logos_widget');
