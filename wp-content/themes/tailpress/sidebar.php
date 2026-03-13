<?php
/**
 * Sidebar template.
 *
 * @package TailPress
 */

if (!is_active_sidebar('sidebar-1')) {
    return;
}
?>

<aside id="secondary" class="widget-area rounded-3xl border-0 bg-white p-6 shadow-[0_-5px_43px_0_color-mix(in_srgb,var(--color-accent-dark)_20%,transparent)] mb-8 px-16 lg:px-10 lg:py-8">
    <?php dynamic_sidebar('sidebar-1'); ?>
</aside>
