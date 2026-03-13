/**
 * Prefill MPMA Hero With Carousel blocks with 3 starter slides.
 */
(function () {
    if (!window.wp || !wp.blocks || !wp.domReady) {
        return;
    }

    var registerBlockVariation = wp.blocks.registerBlockVariation;
    var getBlockVariations = wp.blocks.getBlockVariations;
    var getBlockTypes = wp.blocks.getBlockTypes;

    var registerDefaultVariation = function (config) {
        if (!config || !config.blockName || !config.variation || !config.variation.name) {
            return;
        }

        if (typeof getBlockVariations === 'function') {
            var existingVariations = getBlockVariations(config.blockName) || [];
            var exists = existingVariations.some(function (variation) {
                return variation && variation.name === config.variation.name;
            });

            if (exists) {
                return;
            }
        }

        registerBlockVariation(config.blockName, config.variation);
    };

    var findBlockName = function (keywords, fallback) {
        if (typeof getBlockTypes !== 'function') {
            return fallback;
        }

        var allBlocks = getBlockTypes() || [];
        var match = allBlocks.find(function (blockType) {
            if (!blockType || !blockType.name || blockType.name.indexOf('genesis-custom-blocks/') !== 0) {
                return false;
            }

            return keywords.every(function (keyword) {
                return blockType.name.indexOf(keyword) !== -1;
            });
        });

        return match && match.name ? match.name : fallback;
    };

    var findHeroCarouselBlockName = function () {
        if (typeof getBlockTypes !== 'function') {
            return 'genesis-custom-blocks/mpma-hero-with-carousel';
        }

        var allBlocks = getBlockTypes() || [];
        var match = allBlocks.find(function (blockType) {
            if (!blockType || !blockType.name || blockType.name.indexOf('genesis-custom-blocks/') !== 0) {
                return false;
            }

            var name = blockType.name;
            var hasHero = name.indexOf('hero') !== -1;
            var hasCarousel = name.indexOf('carousel') !== -1 || name.indexOf('slider') !== -1;
            return hasHero && hasCarousel;
        });

        return match && match.name ? match.name : 'genesis-custom-blocks/mpma-hero-with-carousel';
    };

    wp.domReady(function () {
        var heroSlideBlockName = findBlockName(['hero', 'slide'], 'genesis-custom-blocks/mpma-hero-slide');
        var heroCarouselBlockName = findHeroCarouselBlockName();

        var starterHeroSlide = function (index) {
            return [
                heroSlideBlockName,
                {
                    'slide-header': 'Slide ' + index + ' Header',
                    'slide-subtitle': 'Slide ' + index + ' subtitle',
                },
            ];
        };

        registerDefaultVariation({
            blockName: heroCarouselBlockName,
            variation: {
                name: 'tailpress-default-hero-carousel',
                title: 'MPMA Hero With Carousel',
                description: 'Starts with 3 slides and autoplay enabled.',
                isDefault: true,
                attributes: {
                    autoplay: true,
                    'animation-speed': 1200,
                    'animation-delay': 5000,
                    'hero-max-height': '530px',
                    'content-max-width': '1076px',
                    'slide-body': 'Add supporting text here.',
                    'slide-button-text': 'Learn More',
                    'slide-button-url': '#',
                },
                innerBlocks: [
                    starterHeroSlide(1),
                    starterHeroSlide(2),
                    starterHeroSlide(3),
                ],
                scope: ['inserter'],
            },
        });
    });
})();
