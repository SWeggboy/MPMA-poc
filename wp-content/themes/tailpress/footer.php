<?php
/**
 * Theme footer template.
 *
 * @package TailPress
 */
?>
        </main>

        <?php do_action('tailpress_content_end'); ?>
    </div>

    <?php do_action('tailpress_content_after'); ?>

    <footer id="colophon" class="bg-primary mt-12" role="contentinfo">
        <?php do_action('tailpress_footer'); ?>
        <div class="bg-secondary">
          <div class="container flex flex-col md:flex-row items-center justify-center gap-6 mx-auto pt-4 text-center">
              <?php if (is_active_sidebar('footer-logos')) : ?>
                  <?php dynamic_sidebar('footer-logos'); ?>
              <?php endif; ?>
          </div>
        </div>
        <?php if (is_active_sidebar('footer-1') || is_active_sidebar('footer-2')) : ?>
        <div class="container mx-auto py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-36 text-white">
                <?php if (is_active_sidebar('footer-1')) : ?>
                    <div class="footer-column">
                        <?php dynamic_sidebar('footer-1'); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (is_active_sidebar('footer-2')) : ?>
                    <div class="footer-column">
                        <?php dynamic_sidebar('footer-2'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="container mx-auto py-6 border-t border-white/20">
            <div class="text-sm text-white flex flex-wrap items-center gap-2">
                <span>&copy;<?php echo esc_html(date_i18n('Y')); ?> - <?php bloginfo('name'); ?></span>
                <?php if (has_nav_menu('footer')) : ?>
                  <span>-</span>
                    <?php
                    wp_nav_menu([
                        'container'       => false,
                        'menu_class'      => 'inline-flex flex-wrap items-center gap-2 [&_a]:!no-underline [&_li:last-child_span]:hidden',
                        'theme_location'  => 'footer',
                        'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                        'before'          => '',
                        'after'           => '<span class="text-white ml-2">|</span>',
                        'link_before'     => '',
                        'link_after'      => '',
                        'fallback_cb'     => false,
                    ]);
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
