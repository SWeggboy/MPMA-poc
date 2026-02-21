<?php
/**
 * Testimonial Carousel Block - Frontend Render
 *
 * @param array $attributes Block attributes
 * @param string $content Block content
 * @param WP_Block $block Block instance
 */

$testimonials = $attributes['testimonials'] ?? [];
$autoplay = $attributes['autoplay'] ?? true;
$speed = $attributes['speed'] ?? 5000;

if (empty($testimonials)) {
    return '';
}

$block_id = 'carousel-' . uniqid();
?>

<div class="testimonial-carousel-wrapper relative py-16">
    <!-- Testimonial Carousel -->
    <div 
        id="<?php echo esc_attr($block_id); ?>" 
        class="carousel-container relative overflow-hidden"
        data-autoplay="<?php echo esc_attr($autoplay ? 'true' : 'false'); ?>"
        data-speed="<?php echo esc_attr($speed); ?>"
    >
        <div class="carousel-wrapper flex transition-transform duration-500 ease-in-out">
            <?php foreach ($testimonials as $testimonial) : ?>
                <div class="carousel-slide">
                    <div class="carousel-slide-inner py-12 px-8 md:px-16">
                        <div class="testimonial text-center mx-auto relative">
                        <!-- Quote Left Icon -->
                        <div class="absolute -top-13 md:-top-12 -left-8 xl:left-12 text-primary w-20 xl:w-32 h-20 xl:h-32 z-10">
                            <svg aria-hidden="true" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
                                <path d="M464 256h-80v-64c0-35.3 28.7-64 64-64h8c13.3 0 24-10.7 24-24V56c0-13.3-10.7-24-24-24h-8c-88.4 0-160 71.6-160 160v240c0 26.5 21.5 48 48 48h128c26.5 0 48-21.5 48-48V304c0-26.5-21.5-48-48-48zm-288 0H96v-64c0-35.3 28.7-64 64-64h8c13.3 0 24-10.7 24-24V56c0-13.3-10.7-24-24-24h-8C71.6 32 0 103.6 0 192v240c0 26.5 21.5 48 48 48h128c26.5 0 48-21.5 48-48V304c0-26.5-21.5-48-48-48z"></path>
                            </svg>
                        </div>
                        
                        <div class="testimonial__content mb-6">
                            <div class="testimonial__text text-base md:text-xl leading-relaxed text-black italic">
                                <?php echo wp_kses_post($testimonial['text']); ?>
                            </div>
                        </div>
                        
                        <!-- Quote Right Icon -->
                        <div class="absolute bottom-6 md:bottom-7 -right-8 xl:right-12 text-primary w-20 xl:w-32 h-20 xl:h-32 z-10">
                            <svg aria-hidden="true" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
                                <path d="M464 32H336c-26.5 0-48 21.5-48 48v128c0 26.5 21.5 48 48 48h80v64c0 35.3-28.7 64-64 64h-8c-13.3 0-24 10.7-24 24v48c0 13.3 10.7 24 24 24h8c88.4 0 160-71.6 160-160V80c0-26.5-21.5-48-48-48zm-288 0H48C21.5 32 0 53.5 0 80v128c0 26.5 21.5 48 48 48h80v64c0 35.3-28.7 64-64 64h-8c-13.3 0-24 10.7-24 24v48c0 13.3 10.7 24 24 24h8c88.4 0 160-71.6 160-160V80c0-26.5-21.5-48-48-48z"></path>
                            </svg>
                        </div>
                        
                        <div class="testimonial__footer mt-6">
                            <cite class="not-italic">
                                <span class="testimonial__name block font-montserrat font-semibold text-xl text-gray-900 mb-1">
                                    <?php echo esc_html($testimonial['name']); ?>
                                </span>
                                <span class="testimonial__title block text-sm font-semibold text-secondary">
                                    <?php echo esc_html($testimonial['title']); ?>
                                </span>
                            </cite>
                        </div>
                    </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($testimonials) > 1) : ?>
            <!-- Dots Indicator -->
            <div class="carousel-dots flex justify-center gap-2.5">
                <?php foreach ($testimonials as $index => $testimonial) : ?>
                    <button 
                        class="carousel-dot w-1.5 h-1.5 rounded-full bg-gray-400 hover:bg-dark transition-colors"
                        data-index="<?php echo esc_attr($index); ?>"
                        aria-label="Go to slide <?php echo esc_attr($index + 1); ?>"
                    ></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    const carousel = document.getElementById('<?php echo esc_js($block_id); ?>');
    if (!carousel) return;

    const wrapper = carousel.querySelector('.carousel-wrapper');
    const slides = carousel.querySelectorAll('.carousel-slide');
    const dots = carousel.querySelectorAll('.carousel-dot');
    
    let currentIndex = 0;
    const totalSlides = slides.length;
    let autoplayEnabled = carousel.dataset.autoplay === 'true';
    const speed = parseInt(carousel.dataset.speed) || 5000;
    let autoplayInterval;
    let isTransitioning = false;

    function goToSlide(index) {
        if (isTransitioning) return;
        isTransitioning = true;
        
        currentIndex = (index + totalSlides) % totalSlides;
        wrapper.style.transform = `translateX(-${currentIndex * 100}%)`;
        
        // Update dots
        dots.forEach((dot, i) => {
            if (i === currentIndex) {
                dot.classList.remove('bg-gray-400');
                dot.classList.add('bg-dark');
            } else {
                dot.classList.add('bg-gray-400');
                dot.classList.remove('bg-dark');
            }
        });
        
        setTimeout(() => {
            isTransitioning = false;
        }, 500);
    }

    function nextSlide() {
        goToSlide(currentIndex + 1);
    }

    // Event listeners for dots
    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            // Stop autoplay permanently when user clicks
            autoplayEnabled = false;
            clearInterval(autoplayInterval);
            goToSlide(parseInt(dot.dataset.index));
        });
    });

    // Autoplay
    function startAutoplay() {
        clearInterval(autoplayInterval);
        if (autoplayEnabled && totalSlides > 1) {
            autoplayInterval = setInterval(nextSlide, speed);
        }
    }

    // Pause on hover
    carousel.addEventListener('mouseenter', () => {
        if (autoplayEnabled) clearInterval(autoplayInterval);
    });

    carousel.addEventListener('mouseleave', () => {
        if (autoplayEnabled) startAutoplay();
    });

    // Initialize
    goToSlide(0);
    startAutoplay();
})();
</script>
