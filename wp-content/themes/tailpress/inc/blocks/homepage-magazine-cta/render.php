<?php
/**
 * Homepage Magazine CTA Block Template
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 */

$heading = $attributes['heading'] ?? 'Industry Publications That Inform and Inspire';
$description = $attributes['description'] ?? '';

$magazine1_image = $attributes['magazine1Image'] ?? '';
$magazine1_title = $attributes['magazine1Title'] ?? 'GEAR TECHNOLOGY';
$magazine1_description = $attributes['magazine1Description'] ?? '';
$magazine1_button_text = $attributes['magazine1ButtonText'] ?? 'VISIT GEAR TECHNOLOGY';
$magazine1_url = $attributes['magazine1Url'] ?? '#';

$magazine2_image = $attributes['magazine2Image'] ?? '';
$magazine2_title = $attributes['magazine2Title'] ?? 'POWER TRANSMISSION ENGINEERING';
$magazine2_description = $attributes['magazine2Description'] ?? '';
$magazine2_button_text = $attributes['magazine2ButtonText'] ?? 'VISIT PTE';
$magazine2_url = $attributes['magazine2Url'] ?? '#';

// Function to render a magazine card
if (!function_exists('render_magazine_card')) {
    function render_magazine_card($image, $url, $title, $description, $button_text) {
        ob_start();
        ?>
    <div class="magazine-card items-center text-center">
      <div class="overflow-hidden bg-[#F7F7F7] px-[15px] pb-[25px] border-solid border-t-2 border-b border-accent rounded-t-[10px]">
          <?php if ($image): ?>
              <a href="<?php echo esc_url($url); ?>" class="flex items-center justify-center text-primary font-montserrat">
                  <img src="<?php echo esc_url($image); ?>" 
                      alt="<?php echo esc_attr($title); ?>" 
                      class="w-full max-w-[235px]">
              </a>
          <?php else: ?>
              <div class="w-full h-auto bg-gray-200">
                  <span class="text-primary font-montserrat"><?php _e('Magazine Image', 'tailpress'); ?></span>
              </div>
          <?php endif; ?>
        
              <h3 class="text-xl font-medium !mt-2 mb-4 uppercase font-roboto-slab">
                  <a href="<?php echo esc_url($url); ?>" class="!text-gray hover:text-accent !no-underline">
                      <?php echo esc_html($title); ?>
                  </a>
              </h3>
              
              <?php if ($description): ?>
                  <p class="text-[16px] leading-relaxed">
                      <?php echo esc_html($description); ?>
                  </p>
              <?php endif; ?>
      </div>
      <div>
        <a href="<?php echo esc_url($url); ?>" 
            class="inline-block !text-white text-sm font-semibold py-2 px-6 mt-8 !rounded-sm border border-white hover:bg-accent transition-all duration-200 !no-underline uppercase bg-primary">
            <?php echo esc_html($button_text); ?>
        </a>  
      </div>
    </div>
    <?php
        return ob_get_clean();
    }
}
?>

<section class="homepage-magazine-cta py-8 my-12">
    <div class="container mx-auto px-4">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <?php if ($heading): ?>
                <h2 class="!text-[32px] font-bold mb-2 leading-tight text-primary !font-montserrat">
                    <?php echo wp_kses_post($heading); ?>
                </h2>
            <?php endif; ?>
            
            <?php if ($description): ?>
                <div class="text-[16px] leading-relaxed intro-text">
                    <?php echo wp_kses_post($description); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 max-w-5xl mx-auto">
            <?php echo render_magazine_card($magazine1_image, $magazine1_url, $magazine1_title, $magazine1_description, $magazine1_button_text); ?>
            <?php echo render_magazine_card($magazine2_image, $magazine2_url, $magazine2_title, $magazine2_description, $magazine2_button_text); ?>
        </div>
    </div>
</section>
