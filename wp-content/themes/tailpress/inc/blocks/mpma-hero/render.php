<?php
/**
 * MPMA Hero block render template.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Inner blocks content.
 * @param WP_Block $block      Block instance.
 */

if (!function_exists('mpma_safe_dimension')) {
    function mpma_safe_dimension($value, $fallback) {
        $value = trim((string) $value);

        if ($value === '') {
            return $fallback;
        }

        if (preg_match('/^(auto|\d+(\.\d+)?(px|%|vh|vw|rem|em))$/', $value) === 1) {
            return $value;
        }

        return $fallback;
    }
}

$custom_title = trim((string) ($attributes['headerText'] ?? ''));
$resolved_title = $custom_title;

if ($resolved_title === '') {
    $resolved_title = get_the_title();
}

if ($resolved_title === '') {
    $resolved_title = __('Page Title', 'tailpress');
}

$background_image = $attributes['backgroundImage'] ?? '';
$hero_height = mpma_safe_dimension($attributes['heroHeight'] ?? '420px', '420px');
$hero_width_raw = mpma_safe_dimension($attributes['heroWidth'] ?? '100vw', '100vw');
$is_full_bleed = in_array($hero_width_raw, ['100%', '100vw'], true);
$hero_width = $is_full_bleed ? '100vw' : $hero_width_raw;
$content_max_width = mpma_safe_dimension($attributes['contentMaxWidth'] ?? '1200px', '1200px');

$overlay_opacity = isset($attributes['overlayOpacity']) ? (int) $attributes['overlayOpacity'] : 30;
$overlay_opacity = max(0, min(90, $overlay_opacity));

$content_alignment = $attributes['contentAlignment'] ?? 'center';
$vertical_alignment = $attributes['verticalAlignment'] ?? 'center';

$justify_map = [
    'left' => 'flex-start',
    'center' => 'center',
    'right' => 'flex-end',
];

$align_map = [
    'top' => 'flex-start',
    'center' => 'center',
    'bottom' => 'flex-end',
];

$text_map = [
    'left' => 'left',
    'center' => 'center',
    'right' => 'right',
];

$justify_content = $justify_map[$content_alignment] ?? 'center';
$align_items = $align_map[$vertical_alignment] ?? 'center';
$text_align = $text_map[$content_alignment] ?? 'center';

$justify_classes = [
    'flex-start' => 'justify-start',
    'center' => 'justify-center',
    'flex-end' => 'justify-end',
];

$align_classes = [
    'flex-start' => 'items-start',
    'center' => 'items-center',
    'flex-end' => 'items-end',
];

$text_align_classes = [
    'left' => 'text-left',
    'center' => 'text-center',
    'right' => 'text-right',
];

$buttons_justify_classes = [
    'left' => '[&_.wp-block-buttons]:justify-start',
    'center' => '[&_.wp-block-buttons]:justify-center',
    'right' => '[&_.wp-block-buttons]:justify-end',
];

$hero_classes = 'relative overflow-hidden text-white bg-primary bg-cover bg-center bg-no-repeat';

if ($is_full_bleed) {
    $hero_classes .= ' w-screen max-w-none ml-[calc(50%-50vw)] mr-[calc(50%-50vw)]';
} else {
    $hero_classes .= ' mx-auto';
}

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => $hero_classes,
]);

$section_styles = ['min-height: ' . $hero_height . ';'];

if (!$is_full_bleed) {
    $section_styles[] = 'width: ' . $hero_width . ';';
}

if (!empty($background_image)) {
    $section_styles[] = 'background-image: url(' . esc_url($background_image) . ');';
}

$section_style = implode(' ', $section_styles);

$content_wrap_classes = 'relative z-[1] flex w-full px-4 sm:px-6 lg:px-8 ' . ($align_classes[$align_items] ?? 'items-center') . ' ' . ($justify_classes[$justify_content] ?? 'justify-center');
$content_classes = 'w-full mx-auto ' . ($text_align_classes[$text_align] ?? 'text-center');
$buttons_classes = ($buttons_justify_classes[$text_align] ?? '[&_.wp-block-buttons]:justify-center');
?>

<section <?php echo $wrapper_attributes; ?> style="<?php echo esc_attr($section_style); ?>">
    <div class="absolute inset-0 bg-dark" style="opacity: <?php echo esc_attr($overlay_opacity / 100); ?>;"></div>
    <div class="<?php echo esc_attr($content_wrap_classes); ?>" style="min-height: <?php echo esc_attr($hero_height); ?>;">
        <div class="<?php echo esc_attr($content_classes); ?>" style="max-width: <?php echo esc_attr($content_max_width); ?>; padding: 2rem;">
            <h1 class="m-0 text-[3rem] font-bold leading-[1.15] font-roboto"><?php echo esc_html($resolved_title); ?></h1>
            <?php if (!empty(trim($content))) : ?>
                <div class="<?php echo esc_attr($buttons_classes); ?>">
                    <?php echo $content; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
