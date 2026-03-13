<?php
/**
 * Page template file.
 *
 * @package TailPress
 */

get_header();

// Check if sidebar should be displayed
$page_id = get_the_ID();
$show_sidebar_meta = get_post_meta($page_id, 'show_sidebar', true);
$sidebar_floating_meta = get_post_meta($page_id, 'sidebar_floating', true);
$page_title_bg_enabled_meta = get_post_meta($page_id, 'page_title_bg_enabled', true);
$page_title_bg_image_id = absint(get_post_meta($page_id, 'page_title_bg_image_id', true));
$page_title_bg_subtitle = trim((string) get_post_meta($page_id, 'page_title_bg_subtitle', true));
$page_title_bg_use_h1_meta = get_post_meta($page_id, 'page_title_bg_use_h1', true);
$page_title_bg_min_height = trim((string) get_post_meta($page_id, 'page_title_bg_min_height', true));
$page_title_bg_min_height_mobile = trim((string) get_post_meta($page_id, 'page_title_bg_min_height_mobile', true));
$page_title_bg_overlay_opacity_raw = get_post_meta($page_id, 'page_title_bg_overlay_opacity', true);

// WordPress stores true as '1' and false as '' (empty string)
// Check if meta exists - if not, default to true (show sidebar)
$sidebar_meta_exists = metadata_exists('post', $page_id, 'show_sidebar');
$show_sidebar_by_meta = !$sidebar_meta_exists ? true : ($show_sidebar_meta === '1' || $show_sidebar_meta === 1 || $show_sidebar_meta === true);
$sidebar_floating_exists = metadata_exists('post', $page_id, 'sidebar_floating');
$sidebar_floating = !$sidebar_floating_exists ? true : ($sidebar_floating_meta === '1' || $sidebar_floating_meta === 1 || $sidebar_floating_meta === true);

$page_title_bg_enabled_exists = metadata_exists('post', $page_id, 'page_title_bg_enabled');
$page_title_bg_enabled = $page_title_bg_enabled_exists && (
    $page_title_bg_enabled_meta === '1' ||
    $page_title_bg_enabled_meta === 1 ||
    $page_title_bg_enabled_meta === true ||
    $page_title_bg_enabled_meta === 'true'
);

$page_title_bg_use_h1_exists = metadata_exists('post', $page_id, 'page_title_bg_use_h1');
$page_title_bg_use_h1 = !$page_title_bg_use_h1_exists || (
    $page_title_bg_use_h1_meta === '1' ||
    $page_title_bg_use_h1_meta === 1 ||
    $page_title_bg_use_h1_meta === true ||
    $page_title_bg_use_h1_meta === 'true'
);

$page_title_bg_image_url = $page_title_bg_image_id ? wp_get_attachment_image_url($page_title_bg_image_id, 'full') : '';

if (!$page_title_bg_min_height || !preg_match('/^\d+(\.\d+)?(px|rem|em|vh|vw|%)$/', $page_title_bg_min_height)) {
    $page_title_bg_min_height = '26.625rem';
}

if (!$page_title_bg_min_height_mobile || !preg_match('/^\d+(\.\d+)?(px|rem|em|vh|vw|%)$/', $page_title_bg_min_height_mobile)) {
    $page_title_bg_min_height_mobile = '12rem';
}

$page_title_bg_overlay_opacity = is_numeric($page_title_bg_overlay_opacity_raw)
    ? (float) $page_title_bg_overlay_opacity_raw
    : 0.95;
$page_title_bg_overlay_opacity = max(0, min(1, $page_title_bg_overlay_opacity));

$show_page_title_background = $page_title_bg_enabled && !empty($page_title_bg_image_url);
$page_title_background_style = $show_page_title_background
    ? sprintf(
        '--page-title-bg-image:url("%s");--page-title-min-height-mobile:%s;--page-title-min-height:%s;--page-title-overlay-opacity:%s;',
        esc_url($page_title_bg_image_url),
        $page_title_bg_min_height_mobile,
        $page_title_bg_min_height,
        $page_title_bg_overlay_opacity
    )
    : '';

$post_content_raw = (string) get_post_field('post_content', $page_id);
$has_hero_carousel_block = (bool) preg_match(
    '/genesis-custom-blocks\/[a-z0-9_-]*hero[a-z0-9_-]*(carousel|slider)|genesis-custom-blocks\/[a-z0-9_-]*(carousel|slider)[a-z0-9_-]*hero/i',
    $post_content_raw
);

$outer_container_spacing_class = ($show_page_title_background || $has_hero_carousel_block)
    ? 'mt-0 mb-0'
    : 'mt-14 mb-0';

$show_sidebar = !is_front_page() && is_active_sidebar('sidebar-1') && $show_sidebar_by_meta;
?>

<?php if (have_posts()): ?>
    <?php while (have_posts()): the_post(); ?>
        <?php if ($show_page_title_background): ?>
            <section class="page-title-hero page-title-hero--full-bleed mb-14 relative overflow-hidden" style="<?php echo esc_attr($page_title_background_style); ?>">
                <!-- overall blue wash -->
                <div class="absolute inset-0 bg-[#004d84]/25 pointer-events-none" style="opacity: var(--page-title-overlay-opacity, 0.95);"></div>

                <!-- composite gradient overlay -->
                <div
                  class="absolute inset-0 pointer-events-none
                        bg-[linear-gradient(to_bottom,rgba(20,58,94,0)_45%,rgba(20,58,94,0.50)_100%),linear-gradient(to_right,rgba(0,77,132,0.66)_0%,rgba(0,77,132,0.53)_25%,rgba(0,77,132,0.27)_50%,rgba(0,77,132,0.06)_75%,rgba(0,77,132,0)_100%),linear-gradient(to_right,rgba(91,152,113,0)_0%,rgba(91,152,113,0.18)_35%,rgba(91,152,113,0.45)_70%,rgba(91,152,113,0.72)_100%)]"
                  style="opacity: var(--page-title-overlay-opacity, 0.95);"
                ></div>
                <div class="page-title-hero__inner container  relative z-10">
                    <?php if ($page_title_bg_use_h1): ?>
                        <h1 class="page-title-hero__title"><?php the_title(); ?></h1>
                    <?php else: ?>
                        <p class="page-title-hero__title page-title-hero__title--display"><?php the_title(); ?></p>
                    <?php endif; ?>
                    <?php if ('' !== $page_title_bg_subtitle): ?>
                        <p class="page-title-hero__subtitle"><?php echo esc_html($page_title_bg_subtitle); ?></p>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <div class="layout-shell <?php echo esc_attr($outer_container_spacing_class); ?>">
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <?php if (has_post_thumbnail()): ?>
                    <div class="mb-8">
                        <?php the_post_thumbnail('large', ['class' => 'w-full h-auto']); ?>
                    </div>
                <?php endif; ?>

                <?php $page_title = trim((string) get_the_title()); ?>
                <?php $show_default_page_title = (!$show_page_title_background && '' !== $page_title); ?>

                <?php if ($show_sidebar): ?>
                    <div class="layout-grid items-start gap-y-8 <?php echo !$sidebar_floating ? 'page-layout-grid--sidebar-contained' : ''; ?>">
                        <div class="entry-content order-1 col-span-12 min-w-0 lg:order-none lg:col-span-8">
                            <?php if ($show_default_page_title): ?>
                                <h1 class="page-title-hero__title page-title-hero__title--default wp-block-heading"><?php echo esc_html($page_title); ?></h1>
                            <?php endif; ?>
                            <?php the_content(); ?>
                        </div>
                        
                        <aside class="order-2 col-span-12 lg:order-none lg:col-span-4 z-40 <?php echo !$sidebar_floating ? 'page-layout-grid__sidebar' : ''; ?>">
                            <?php get_sidebar(); ?>
                        </aside>
                    </div>
                <?php else: ?>
                    <div class="entry-content">
                        <?php if ($show_default_page_title): ?>
                            <h1 class="page-title-hero__title page-title-hero__title--default wp-block-heading"><?php echo esc_html($page_title); ?></h1>
                        <?php endif; ?>
                        <?php the_content(); ?>
                    </div>
                <?php endif; ?>
            </article>

            <?php if (comments_open() || get_comments_number()): ?>
                <?php comments_template(); ?>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
<?php endif; ?>

<?php
get_footer();
