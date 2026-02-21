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

// WordPress stores true as '1' and false as '' (empty string)
// Check if meta exists - if not, default to true (show sidebar)
$sidebar_meta_exists = metadata_exists('post', $page_id, 'show_sidebar');
$show_sidebar_by_meta = !$sidebar_meta_exists ? true : ($show_sidebar_meta === '1' || $show_sidebar_meta === 1 || $show_sidebar_meta === true);

$show_sidebar = !is_front_page() && is_active_sidebar('sidebar-1') && $show_sidebar_by_meta;
$content_class = $show_sidebar ? 'lg:col-span-9' : 'lg:col-span-12';
?>

<div class="container my-8 mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-24">
        <div class="<?php echo esc_attr($content_class); ?>">
            <?php if (have_posts()): ?>
                <?php while (have_posts()): the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <?php if (has_post_thumbnail()): ?>
                            <div class="mb-8">
                                <?php the_post_thumbnail('large', ['class' => 'w-full h-auto']); ?>
                            </div>
                        <?php endif; ?>

                        <header class="mb-8">
                            <h1 class="text-4xl font-bold"><?php the_title(); ?></h1>
                        </header>

                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                    </article>

                    <?php if (comments_open() || get_comments_number()): ?>
                        <?php comments_template(); ?>
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
        
        <?php if ($show_sidebar): ?>
            <div class="lg:col-span-3">
                <?php get_sidebar(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
get_footer();
