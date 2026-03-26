<?php
/**
 * Single post template file.
 *
 * @package TailPress
 */

get_header();
?>

<div class="layout-shell mt-14 mb-0">
    <?php if (have_posts()): ?>
        <?php while (have_posts()): the_post(); ?>
            <?php
            $post_id = get_the_ID();
            $show_sidebar_meta = get_post_meta($post_id, 'show_sidebar', true);
            $sidebar_floating_meta = get_post_meta($post_id, 'sidebar_floating', true);

            $sidebar_meta_exists = metadata_exists('post', $post_id, 'show_sidebar');
            $show_sidebar_by_meta = !$sidebar_meta_exists ? true : ($show_sidebar_meta === '1' || $show_sidebar_meta === 1 || $show_sidebar_meta === true);
            $sidebar_floating_exists = metadata_exists('post', $post_id, 'sidebar_floating');
            $sidebar_floating = !$sidebar_floating_exists ? true : ($sidebar_floating_meta === '1' || $sidebar_floating_meta === 1 || $sidebar_floating_meta === true);
            $show_sidebar = is_active_sidebar('sidebar-1') && $show_sidebar_by_meta;
            ?>

            <?php if ($show_sidebar): ?>
                <div class="layout-grid items-start gap-y-8 <?php echo !$sidebar_floating ? 'page-layout-grid--sidebar-contained' : ''; ?>">
                    <div class="entry-content order-1 col-span-12 min-w-0 lg:order-none lg:col-span-8">
                        <?php get_template_part('template-parts/content', 'single'); ?>
                    </div>

                    <aside class="order-2 col-span-12 lg:order-none lg:col-span-4 z-40 <?php echo !$sidebar_floating ? 'page-layout-grid__sidebar' : ''; ?>">
                        <?php get_sidebar(); ?>
                    </aside>
                </div>
            <?php else: ?>
                <div class="entry-content">
                    <?php get_template_part('template-parts/content', 'single'); ?>
                </div>
            <?php endif; ?>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php
get_footer();
