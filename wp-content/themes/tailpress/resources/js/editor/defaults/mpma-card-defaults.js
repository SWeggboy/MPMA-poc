/**
 * Prefill MPMA Card blocks with starter content.
 */
(function () {
    if (!window.wp || !wp.blocks || !wp.domReady) {
        return;
    }

    var registerBlockVariation = wp.blocks.registerBlockVariation;
    var getBlockVariations = wp.blocks.getBlockVariations;

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

    var starterCardParagraph = function () {
        return [
            'core/paragraph',
            {
                content: 'Enter text here',
                textColor: 'white',
                backgroundColor: 'secondary',
                fontSize: 'sm',
                style: {
                    typography: {
                        lineHeight: '1.2',
                    },
                },
            },
        ];
    };

    wp.domReady(function () {
        registerDefaultVariation({
            blockName: 'genesis-custom-blocks/mpma-card',
            variation: {
                name: 'tailpress-default-content',
                title: 'MPMA Card',
                description: 'Starts with default paragraph content.',
                isDefault: true,
                innerBlocks: [starterCardParagraph()],
                scope: ['inserter'],
            },
        });
    });
})();

