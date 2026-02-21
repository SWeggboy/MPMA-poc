<?php
/**
 * Search form template
 * 
 * @param bool $mobile Whether to display the mobile version (always expanded)
 */
$mobile = isset($args['mobile']) ? $args['mobile'] : false;

if ($mobile): ?>
    <form method="GET" action="<?php echo get_bloginfo('url'); ?>" class="search-box-wrapper-mobile w-full">
        <div class="search-box-mobile relative border border-2 border-dark/40 rounded-sm overflow-hidden bg-white w-full h-[38px]">
            <input type="text" name="s" class="search-input-mobile border-0 px-3 text-sm bg-transparent w-full h-[38px] outline-none pr-12" value="<?php echo get_search_query(); ?>" placeholder="<?php _e('Search'); ?>">
            <button type="submit" class="search-submit-mobile absolute right-2 top-2 flex items-center justify-center cursor-pointer">
                <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2.125 14.875L5.20625 11.7938M3.54167 7.79167C3.54167 10.9213 6.07872 13.4583 9.20833 13.4583C12.3379 13.4583 14.875 10.9213 14.875 7.79167C14.875 4.66205 12.3379 2.125 9.20833 2.125C6.07872 2.125 3.54167 4.66205 3.54167 7.79167Z" stroke="#1E1E1E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    </form>
<?php else: ?>
    <form method="GET" action="<?php echo get_bloginfo('url'); ?>" class="search-box-wrapper">
        <div class="search-box relative border border-2 border-dark/40 rounded-sm overflow-hidden bg-white w-[38px] h-[38px] transition-[width] duration-300 ease-in-out">
            <input type="text" name="s" class="search-input absolute left-0 top-0 border-0 px-3 text-sm bg-transparent w-full h-[34px] opacity-0 outline-none pointer-events-none transition-opacity duration-300 ease-in-out" value="<?php echo get_search_query(); ?>" placeholder="<?php _e('Search'); ?>">
            <button type="button" class="search-toggle absolute right-2 top-2 flex items-center justify-center cursor-pointer">
                <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2.125 14.875L5.20625 11.7938M3.54167 7.79167C3.54167 10.9213 6.07872 13.4583 9.20833 13.4583C12.3379 13.4583 14.875 10.9213 14.875 7.79167C14.875 4.66205 12.3379 2.125 9.20833 2.125C6.07872 2.125 3.54167 4.66205 3.54167 7.79167Z" stroke="#1E1E1E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    </form>
<?php endif; ?>
