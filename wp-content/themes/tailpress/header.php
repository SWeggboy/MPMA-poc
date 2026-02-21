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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto+Slab:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <?php wp_head(); ?>
</head>
<body <?php body_class('bg-white text-zinc-900 antialiased'); ?>>
<?php do_action('tailpress_site_before'); ?>

<div id="page" class="min-h-screen flex flex-col">
    <?php do_action('tailpress_header'); ?>

    <header class="container mx-auto py-6">
        <div class="md:flex md:justify-between md:items-center">

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
                                <span class="text-sm font-light text-dark/80">|</span>
                                <span class="text-sm font-light text-dark/80"><?php echo esc_html($description); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (has_nav_menu('primary')): ?>
                    <div class="md:hidden">
                        <button type="button" aria-label="Toggle navigation" id="primary-menu-toggle">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <div id="primary-navigation" class="max-h-0 opacity-0 overflow-hidden md:max-h-none md:opacity-100 md:overflow-visible md:flex md:bg-transparent gap-6 items-center md:border md:border-light md:border-none md:rounded-xl md:p-0 transition-[max-height,opacity] duration-300 ease-in-out will-change-[max-height]">
                <div class="p-4 md:p-0 w-full md:w-auto md:flex md:gap-6 md:items-center">
                <nav>
                    <?php if (current_user_can('administrator') && !has_nav_menu('primary')): ?>
                        <a href="<?php echo esc_url(admin_url('nav-menus.php')); ?>" class="text-sm text-zinc-600"><?php esc_html_e('Edit Menus', 'tailpress'); ?></a>
                    <?php else: ?>
                        <?php
                        wp_nav_menu([
                            'container_id'    => 'primary-menu',
                            'container_class' => '',
                            'menu_class'      => 'md:flex md:-mx-4 [&_a]:!no-underline',
                            'theme_location'  => 'primary',
                            'li_class'        => 'md:mx-4 !text-[12px] !font-roboto !font-semibold !uppercase',
                            'fallback_cb'     => false,
                        ]);
                        ?>
                    <?php endif; ?>
                </nav>

                <!-- Secondary menu in mobile -->
                <nav class="md:hidden">
                    <?php if (current_user_can('administrator') && !has_nav_menu('secondary')): ?>
                        <a href="<?php echo esc_url(admin_url('nav-menus.php')); ?>" class="text-sm text-zinc-600"><?php esc_html_e('Edit Secondary Menu', 'tailpress'); ?></a>
                    <?php else: ?>
                        <?php
                        wp_nav_menu([
                            'container_id'    => 'mobile-secondary-menu',
                            'container_class' => 'mt-4',
                            'menu_class'      => '[&_a]:!no-underline',
                            'theme_location'  => 'secondary',
                            'li_class'        => '!text-[12px] !font-roboto !font-semibold !uppercase',
                            'fallback_cb'     => false,
                        ]);
                        ?>
                    <?php endif; ?>
                </nav>

                <!-- Search form - collapsed on desktop, expanded on mobile -->
                <div class="inline-block mt-4 md:mt-0 w-full md:w-auto">
                    <div class="hidden md:block"><?php get_search_form(); ?></div>
                    <div class="block md:hidden"><?php get_search_form(['mobile' => true]); ?></div>
                </div>
                </div>
            </div> 
        </div>
    </header>
    <div id="secondary-navigation" class="hidden md:flex md:justify-center md:items-center md:min-h-[140px] bg-primary text-white font-roboto">
      <nav>
          <?php if (current_user_can('administrator') && !has_nav_menu('secondary')): ?>
              <a href="<?php echo esc_url(admin_url('nav-menus.php')); ?>" class="text-sm text-zinc-600"><?php esc_html_e('Edit Menus', 'tailpress'); ?></a>
          <?php else: ?>
              <?php
              wp_nav_menu([
                  'container_id'    => 'secondary-menu',
                  'container_class' => '',
                  'menu_class'      => 'md:flex [&_a]:!no-underline [&>li]:md:mx-4 [&_.sub-menu>li]:!mx-0',
                  'theme_location'  => 'secondary',
                  'li_class'        => '!text-[16px] !font-semibold',
                  'fallback_cb'     => false,
              ]);
              ?>
          <?php endif; ?>
      </nav>
  </div> 

  <?php do_action('tailpress_content_start'); ?>
  <main>
