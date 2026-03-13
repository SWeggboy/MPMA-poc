<?php
/**
 * Homepage CTA with Background
 */

$heading = $attributes['heading'] ?? 'The Power of a Combined Alliance';
$description = $attributes['description'] ?? 'As the industry evolves through consolidation and new technologies, MPMA provides a stronger, more connected community. By joining forces, AGMA and ABMA members gain:';
$buttonText = $attributes['buttonText'] ?? 'View Our FAQ\'s';
$buttonLink = $attributes['buttonLink'] ?? '#';
$bottomText = $attributes['bottomText'] ?? '';
$backgroundImage = $attributes['backgroundImage'] ?? '';
$backgroundColor = $attributes['backgroundColor'] ?? '';
$gradient = $attributes['gradient'] ?? '';
$backgroundPosition = $attributes['backgroundPosition'] ?? 'center center';
$backgroundSize = $attributes['backgroundSize'] ?? 'cover';
$backgroundRepeat = $attributes['backgroundRepeat'] ?? 'no-repeat';
$backgroundAttachment = $attributes['backgroundAttachment'] ?? 'scroll';
$overlayOpacity = $attributes['overlayOpacity'] ?? 80;

$iconBox1Title = $attributes['iconBox1Title'] ?? 'Expanded Access to Standards';
$iconBox1Desc = $attributes['iconBox1Desc'] ?? 'Members benefit from a unified library of AGMA and ABMA standards, creating consistent benchmarks for quality, performance, and interoperability across gears and bearings.';
$iconBox2Title = $attributes['iconBox2Title'] ?? 'Comprehensive Education & Training';
$iconBox2Desc = $attributes['iconBox2Desc'] ?? 'MPMA delivers a broad curriculum of courses, technical sessions, and workforce programs that strengthen skills and support the next generation of industry professionals.';
$iconBox3Title = $attributes['iconBox3Title'] ?? 'Broader Networking Connections';
$iconBox3Desc = $attributes['iconBox3Desc'] ?? 'Through joint events, conferences, and exhibitions, members connect across the full motion and power supply chain to share knowledge and build meaningful business relationships.';
$iconBox4Title = $attributes['iconBox4Title'] ?? 'Unified Industry Insights';
$iconBox4Desc = $attributes['iconBox4Desc'] ?? 'Combined research, technical reports, and market intelligence provide a comprehensive view of trends and opportunities.';

// Build background styles
$bgStyles = [];
$overlayStyles = [];

if ($backgroundImage) {
  $bgStyles[] = "background-image: url('" . esc_url($backgroundImage) . "')";
  $bgStyles[] = "background-position: " . esc_attr($backgroundPosition);
  $bgStyles[] = "background-size: " . esc_attr($backgroundSize);
  $bgStyles[] = "background-repeat: " . esc_attr($backgroundRepeat);
  $bgStyles[] = "background-attachment: " . esc_attr($backgroundAttachment);
}
if ($backgroundColor) {
    $bgStyles[] = "background-color: " . esc_attr($backgroundColor);
}
if ($gradient) {
  $overlayStyles[] = "backround-color: transparent";
  $overlayStyles[] = "--background-overlay: ''";
  $overlayStyles[] = "background-image: " . esc_attr($gradient);
  $overlayStyles[] = "opacity: " . ($overlayOpacity / 100);
}

$bgStyle = !empty($bgStyles) ? implode('; ', $bgStyles) : '';
$overlayStyle = !empty($overlayStyles) ? implode('; ', $overlayStyles) : '';

// Determine container class based on alignment
$containerClass = 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8';
if (isset($block->context['align']) && $block->context['align'] === 'full') {
    $containerClass = 'w-full px-4 sm:px-6 lg:px-8';
}
?>

<section class="bg-white relative py-16 md:py-20 lg:py-24 w-screen max-w-none -ml-[50vw] left-[50%]" <?php if ($bgStyle): ?>style="<?php echo $bgStyle; ?>"<?php endif; ?>>
    <?php if ($gradient): ?>
        <div class="absolute inset-0" style="<?php echo $overlayStyle; ?>"></div>
    <?php elseif ($backgroundImage): ?>
        <div class="absolute inset-0 bg-gray-900" style="opacity: <?php echo ($overlayOpacity / 100); ?>;"></div>
    <?php endif; ?>
    <div class="<?php echo $backgroundImage || $gradient ? 'relative' : ''; ?> <?php echo esc_attr($containerClass); ?>">
        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12 mb-16">
            <div class="flex-1">
                <h2 class="!text-[32px] font-bold font-montserrat mb-6 <?php echo ($backgroundImage || $gradient) ? 'text-primary' : 'text-white'; ?> leading-tight"><?php echo wp_kses_post($heading); ?></h2>
                <?php if ($description): ?>
                    <p class="text-lg md:text-xl leading-relaxed"><?php echo wp_kses_post($description); ?></p>
                <?php endif; ?>
            </div>
            <?php if ($buttonText): ?>
                <div class="flex items-start lg:items-center">
                    <a href="<?php echo esc_url($buttonLink); ?>" class="inline-block !text-white font-semibold py-3 px-6 rounded-sm bg-primary border border-white uppercase text-sm tracking-wider hover:bg-[#020048] transition-all duration-200 !no-underline">
                        <?php echo esc_html($buttonText); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-[15px] rounded-[10px]" style="box-shadow: 0px 12px 10px -10px rgba(0, 0, 0, 0.15);">
                <div class="mb-4 flex items-center justify-center">
                    <svg class="w-16 h-16 text-accent" fill="currentColor" aria-hidden="true" viewBox="0 0 640 512"><path d="M610.5 341.3c2.6-14.1 2.6-28.5 0-42.6l25.8-14.9c3-1.7 4.3-5.2 3.3-8.5-6.7-21.6-18.2-41.2-33.2-57.4-2.3-2.5-6-3.1-9-1.4l-25.8 14.9c-10.9-9.3-23.4-16.5-36.9-21.3v-29.8c0-3.4-2.4-6.4-5.7-7.1-22.3-5-45-4.8-66.2 0-3.3.7-5.7 3.7-5.7 7.1v29.8c-13.5 4.8-26 12-36.9 21.3l-25.8-14.9c-2.9-1.7-6.7-1.1-9 1.4-15 16.2-26.5 35.8-33.2 57.4-1 3.3.4 6.8 3.3 8.5l25.8 14.9c-2.6 14.1-2.6 28.5 0 42.6l-25.8 14.9c-3 1.7-4.3 5.2-3.3 8.5 6.7 21.6 18.2 41.1 33.2 57.4 2.3 2.5 6 3.1 9 1.4l25.8-14.9c10.9 9.3 23.4 16.5 36.9 21.3v29.8c0 3.4 2.4 6.4 5.7 7.1 22.3 5 45 4.8 66.2 0 3.3-.7 5.7-3.7 5.7-7.1v-29.8c13.5-4.8 26-12 36.9-21.3l25.8 14.9c2.9 1.7 6.7 1.1 9-1.4 15-16.2 26.5-35.8 33.2-57.4 1-3.3-.4-6.8-3.3-8.5l-25.8-14.9zM496 368.5c-26.8 0-48.5-21.8-48.5-48.5s21.8-48.5 48.5-48.5 48.5 21.8 48.5 48.5-21.7 48.5-48.5 48.5zM96 224c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm224 32c1.9 0 3.7-.5 5.6-.6 8.3-21.7 20.5-42.1 36.3-59.2 7.4-8 17.9-12.6 28.9-12.6 6.9 0 13.7 1.8 19.6 5.3l7.9 4.6c.8-.5 1.6-.9 2.4-1.4 7-14.6 11.2-30.8 11.2-48 0-61.9-50.1-112-112-112S208 82.1 208 144c0 61.9 50.1 112 112 112zm105.2 194.5c-2.3-1.2-4.6-2.6-6.8-3.9-8.2 4.8-15.3 9.8-27.5 9.8-10.9 0-21.4-4.6-28.9-12.6-18.3-19.8-32.3-43.9-40.2-69.6-10.7-34.5 24.9-49.7 25.8-50.3-.1-2.6-.1-5.2 0-7.8l-7.9-4.6c-3.8-2.2-7-5-9.8-8.1-3.3.2-6.5.6-9.8.6-24.6 0-47.6-6-68.5-16h-8.3C179.6 288 128 339.6 128 403.2V432c0 26.5 21.5 48 48 48h255.4c-3.7-6-6.2-12.8-6.2-20.3v-9.2zM173.1 274.6C161.5 263.1 145.6 256 128 256H64c-35.3 0-64 28.7-64 64v32c0 17.7 14.3 32 32 32h65.9c6.3-47.4 34.9-87.3 75.2-109.4z"/></svg>
                </div>
                <h4 class="text-xl font-bold mb-3 text-center uppercase font-roboto-slab text-gray"><?php echo esc_html($iconBox1Title); ?></h4>
                <p class="text-base leading-relaxed text-center"><?php echo esc_html($iconBox1Desc); ?></p>
            </div>

            <div class="bg-white p-[15px] rounded-[10px]" style="box-shadow: 0px 12px 10px -10px rgba(0, 0, 0, 0.15);">
                <div class="mb-4 flex items-center justify-center">
                    <svg class="w-16 h-16 text-accent" fill="currentColor" aria-hidden="true" viewBox="0 0 640 512"><path d="M208 352c-2.39 0-4.78.35-7.06 1.09C187.98 357.3 174.35 360 160 360c-14.35 0-27.98-2.7-40.95-6.91-2.28-.74-4.66-1.09-7.05-1.09C49.94 352-.33 402.48 0 464.62.14 490.88 21.73 512 48 512h224c26.27 0 47.86-21.12 48-47.38.33-62.14-49.94-112.62-112-112.62zm-48-32c53.02 0 96-42.98 96-96s-42.98-96-96-96-96 42.98-96 96 42.98 96 96 96zM592 0H208c-26.47 0-48 22.25-48 49.59V96c23.42 0 45.1 6.78 64 17.8V64h352v288h-64v-64H384v64h-76.24c19.1 16.69 33.12 38.73 39.69 64H592c26.47 0 48-22.25 48-49.59V49.59C640 22.25 618.47 0 592 0z"/></svg>
                </div>
                <h4 class="text-xl font-bold mb-3 text-center uppercase font-roboto-slab text-gray"><?php echo esc_html($iconBox2Title); ?></h4>
                <p class="text-base leading-relaxed text-center"><?php echo esc_html($iconBox2Desc); ?></p>
            </div>

            <div class="bg-white p-[15px] rounded-[10px]" style="box-shadow: 0px 12px 10px -10px rgba(0, 0, 0, 0.15);">
                <div class="mb-4 flex items-center justify-center">
                    <svg class="w-16 h-16 text-accent" fill="currentColor" aria-hidden="true" viewBox="0 0 512 512"><path d="M367.9 329.76c-4.62 5.3-9.78 10.1-15.9 13.65v22.94c66.52 9.34 112 28.05 112 49.65 0 30.93-93.12 56-208 56S48 446.93 48 416c0-21.6 45.48-40.3 112-49.65v-22.94c-6.12-3.55-11.28-8.35-15.9-13.65C58.87 345.34 0 378.05 0 416c0 53.02 114.62 96 256 96s256-42.98 256-96c0-37.95-58.87-70.66-144.1-86.24zM256 128c35.35 0 64-28.65 64-64S291.35 0 256 0s-64 28.65-64 64 28.65 64 64 64zm-64 192v96c0 17.67 14.33 32 32 32h64c17.67 0 32-14.33 32-32v-96c17.67 0 32-14.33 32-32v-96c0-26.51-21.49-48-48-48h-11.8c-11.07 5.03-23.26 8-36.2 8s-25.13-2.97-36.2-8H208c-26.51 0-48 21.49-48 48v96c0 17.67 14.33 32 32 32z"/></svg>
                </div>
                <h4 class="text-xl font-bold mb-3 text-center uppercase font-roboto-slab text-gray"><?php echo esc_html($iconBox3Title); ?></h4>
                <p class="text-base leading-relaxed text-center"><?php echo esc_html($iconBox3Desc); ?></p>
            </div>

            <div class="bg-white p-[15px] rounded-[10px]" style="box-shadow: 0px 12px 10px -10px rgba(0, 0, 0, 0.15);">
                <div class="mb-4 flex items-center justify-center">
                    <svg class="w-16 h-16 text-accent" fill="currentColor" aria-hidden="true" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg"><path d="M171.73,232.813A5.381,5.381,0,0,0,176.7,229.5,48.081,48.081,0,0,1,191.6,204.244c1.243-.828,1.657-2.484,1.657-4.141a4.22,4.22,0,0,0-2.071-3.312L74.429,128.473,148.958,85a9.941,9.941,0,0,0,4.968-8.281,9.108,9.108,0,0,0-4.968-8.281L126.6,55.6a9.748,9.748,0,0,0-9.523,0l-100.2,57.966a9.943,9.943,0,0,0-4.969,8.281V236.954a9.109,9.109,0,0,0,4.969,8.281L39.235,258.07a8.829,8.829,0,0,0,4.968,1.242,9.4,9.4,0,0,0,6.625-2.484,10.8,10.8,0,0,0,2.9-7.039V164.5L169.66,232.4A4.5,4.5,0,0,0,171.73,232.813ZM323.272,377.73a12.478,12.478,0,0,0-4.969,1.242l-74.528,43.062V287.882c0-2.9-2.9-5.8-6.211-4.555a53.036,53.036,0,0,1-28.984.414,4.86,4.86,0,0,0-6.21,4.555V421.619l-74.529-43.061a8.83,8.83,0,0,0-4.969-1.242,9.631,9.631,0,0,0-9.523,9.523v26.085a9.107,9.107,0,0,0,4.969,8.281l100.2,57.553A8.829,8.829,0,0,0,223.486,480a11.027,11.027,0,0,0,4.969-1.242l100.2-57.553a9.941,9.941,0,0,0,4.968-8.281V386.839C332.8,382.285,328.24,377.73,323.272,377.73ZM286.007,78a23,23,0,1,0-23-23A23,23,0,0,0,286.007,78Zm63.627-10.086a23,23,0,1,0,23,23A23,23,0,0,0,349.634,67.914ZM412.816,151.6a23,23,0,1,0-23-23A23,23,0,0,0,412.816,151.6Zm-63.182-9.2a23,23,0,1,0,23,23A23,23,0,0,0,349.634,142.4Zm-63.627,83.244a23,23,0,1,0-23-23A23,23,0,0,0,286.007,225.648Zm-62.074,36.358a23,23,0,1,0-23-23A23,23,0,0,0,223.933,262.006Zm188.883-82.358a23,23,0,1,0,23,23A23,23,0,0,0,412.816,179.648Zm0,72.272a23,23,0,1,0,23,23A23,23,0,0,0,412.816,251.92Z"></path></svg>
                </div>
                <h4 class="text-xl font-bold mb-3 text-center uppercase font-roboto-slab text-gray"><?php echo esc_html($iconBox4Title); ?></h4>
                <p class="text-base leading-relaxed text-center"><?php echo esc_html($iconBox4Desc); ?></p>
            </div>
        </div>

        <?php if ($bottomText): ?>
            <div class="mt-16 text-left">
                <p class="text-[16px] leading-relaxed [&_a]:!no-underline"><?php echo wp_kses_post($bottomText); ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>
