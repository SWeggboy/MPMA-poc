<?php
/**
 * Homepage Image and Text Block Template
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 */

$image = $attributes['image'] ?? '';
$image_position = $attributes['imagePosition'] ?? 'left';
$heading = $attributes['heading'] ?? 'The Power of a Combined Alliance';
$content = $attributes['content'] ?? '';
$background_color = $attributes['backgroundColor'] ?? 'white';
$enableBorderShadow = $attributes['enableBorderShadow'] ?? true;
$imageWidth = $attributes['imageWidth'] ?? '100%';
$textAlignment = $attributes['textAlignment'] ?? 'left';
$buttonText = $attributes['buttonText'] ?? '';
$buttonLink = $attributes['buttonLink'] ?? '';

$bg_class = match($background_color) {
    'gray' => 'bg-gray-50',
    'blue' => 'bg-blue-50',
    default => 'bg-white'
};

$image_class = $enableBorderShadow ? 'rounded-[10px] max-w-full h-auto' : 'max-w-full h-auto';
$image_border = $enableBorderShadow ? 'border-style: solid; border-width: 1px 0px 3px 3px; border-color: #2E5E47;' : '';

$text_align_class = match($textAlignment) {
    'center' => 'text-center',
    'right' => 'text-right',
    default => 'text-left'
};

$heading_class = '!text-[32px] font-bold mb-6 leading-tight !text-primary !font-montserrat';

$grid_order = $image_position === 'right' ? 'lg:order-2' : '';

// Build image HTML
ob_start();
if ($image): 
    $image_style = 'width: ' . esc_attr($imageWidth) . ';';
    if ($image_border) {
        $image_style .= ' ' . $image_border;
    }
    ?>
    <img src="<?php echo esc_url($image); ?>" alt="" class="<?php echo esc_attr($image_class); ?>" style="<?php echo $image_style; ?>">
<?php else: ?>
    <div class="w-full h-96 bg-gray-200 rounded-lg flex items-center justify-center">
        <span class="text-gray-400"><?php _e('Image', 'tailpress'); ?></span>
    </div>
<?php endif;
$image_html = ob_get_clean();

// Build content HTML
ob_start(); ?>
<div class="<?php echo esc_attr($text_align_class); ?>">
    <?php if ($heading): ?>
        <h2 class="<?php echo esc_attr($heading_class); ?>">
            <?php echo wp_kses_post($heading); ?>
        </h2>
    <?php endif; ?>
    
    <?php if ($content): ?>
        <div class="text-[16px] leading-relaxed mb-6 font-roboto">
            <?php echo wp_kses_post($content); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($buttonText): ?>
        <a href="<?php echo esc_url($buttonLink); ?>" class="inline-block !text-white text-sm font-semibold uppercase py-2 px-6 !rounded-sm bg-primary border border-white hover:bg-accent transition-all duration-200 !no-underline mt-8">
            <?php echo esc_html($buttonText); ?>
        </a>
    <?php endif; ?>
</div>
<?php 
$content_html = ob_get_clean();
?>

<section class="homepage-image-text <?php echo esc_attr($bg_class); ?> py-8 my-12">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
            <?php if ($image_position === 'left'): ?>
                <div class="<?php echo esc_attr($grid_order); ?>">
                    <?php echo $image_html; ?>
                </div>
                <?php echo $content_html; ?>
            <?php else: ?>
                <?php echo $content_html; ?>
                <div>
                    <?php echo $image_html; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
