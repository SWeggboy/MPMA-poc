<?php
/**
 * Theme header template.
 *
 * @package TailPress
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <?php wp_head(); ?>
</head>
<body <?php body_class('bg-white text-zinc-900 antialiased'); ?>>
<?php do_action('tailpress_site_before'); ?>

<div id="page" class="min-h-screen flex flex-col">
    <?php do_action('tailpress_header'); ?>

    <header class="container mx-auto py-10">
        <div class="lg:flex lg:justify-between lg:items-center">

            <div class="flex justify-between items-center">
                <div>
                    <?php if (has_custom_logo()): ?>
                        <?php the_custom_logo(); ?>
                    <?php else: ?>
                        <div class="flex items-center gap-2">
                            <a href="<?php echo esc_url(home_url('/')); ?>" class="!no-underline lowercase font-medium text-lg">
                                <?php bloginfo('name'); ?>
                            </a>
                            <?php if ($description = get_bloginfo('description')): ?>
                                <span class="text-lg font-light text-dark/80">|</span>
                                <span class="text-lg font-light text-dark/80"><?php echo esc_html($description); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (has_nav_menu('primary')): ?>
                    <div class="lg:hidden">
                        <button type="button" aria-label="Toggle navigation" id="primary-menu-toggle" class="cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <div id="primary-navigation" class="max-h-0 opacity-0 overflow-hidden lg:max-h-none lg:opacity-100 lg:overflow-visible lg:flex lg:bg-transparent gap-6 items-center lg:border lg:border-light lg:border-none lg:rounded-xl lg:p-0 transition-[max-height,opacity] duration-300 ease-in-out will-change-[max-height]">
                <div class="py-4 lg:p-0 w-full lg:w-auto lg:flex lg:gap-6 lg:items-center">
                <nav>
                    <?php if (current_user_can('administrator') && !has_nav_menu('primary')): ?>
                        <a href="<?php echo esc_url(admin_url('nav-menus.php')); ?>" class="text-sm text-zinc-600"><?php esc_html_e('Edit Menus', 'tailpress'); ?></a>
                    <?php else: ?>
                        <?php
                        wp_nav_menu([
                            'container_id'    => 'primary-menu',
                            'container_class' => '',
                            'menu_class'      => 'lg:flex lg:-mx-4 [&_a]:!no-underline',
                            'theme_location'  => 'primary',
                            'li_class'        => 'lg:mx-4 !text-lg !font-roboto !uppercase',
                            'fallback_cb'     => false,
                        ]);
                        ?>
                    <?php endif; ?>
                </nav>

                <!-- Secondary menu in mobile -->
                <nav class="lg:hidden">
                    <?php if (current_user_can('administrator') && !has_nav_menu('secondary')): ?>
                        <a href="<?php echo esc_url(admin_url('nav-menus.php')); ?>" class="text-sm text-zinc-600"><?php esc_html_e('Edit Secondary Menu', 'tailpress'); ?></a>
                    <?php else: ?>
                        <?php
                        wp_nav_menu([
                            'container_id'    => 'mobile-secondary-menu',
                            'container_class' => 'mt-4',
                            'menu_class'      => '[&_a]:!no-underline',
                            'theme_location'  => 'secondary',
                            'li_class'        => '!font-roboto !font-semibold !uppercase',
                            'fallback_cb'     => false,
                        ]);
                        ?>
                    <?php endif; ?>
                </nav>

                <!-- Search form - collapsed on desktop, expanded on mobile -->
                <div class="inline-block mt-4 lg:mt-0 w-full lg:w-auto">
                    <div class="hidden lg:block"><?php get_search_form(); ?></div>
                    <div class="block w-6/12 lg:hidden"><?php get_search_form(['mobile' => true]); ?></div>
                </div>
                </div>
            </div> 
        </div>
    </header>
    <div id="secondary-navigation" class="hidden lg:flex lg:justify-center lg:items-center lg:min-h-[40px] bg-primary text-white font-roboto">
      <nav>
          <?php if (current_user_can('administrator') && !has_nav_menu('secondary')): ?>
              <a href="<?php echo esc_url(admin_url('nav-menus.php')); ?>" class="text-sm text-zinc-600"><?php esc_html_e('Edit Menus', 'tailpress'); ?></a>
          <?php else: ?>
              <?php
              wp_nav_menu([
                  'container_id'    => 'secondary-menu',
                  'container_class' => '',
                  'menu_class'      => 'lg:flex [&_a]:!no-underline [&>li]:lg:mx-4 [&_.sub-menu>li]:!mx-0',
                  'theme_location'  => 'secondary',
                  'li_class'        => '!text-[16px] !font-semibold',
                  'fallback_cb'     => false,
              ]);
              ?>
          <?php endif; ?>
      </nav>
  </div> 

  <?php do_action('tailpress_content_start'); ?>
  <main class="flex-1">
