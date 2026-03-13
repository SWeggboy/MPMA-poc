/**
 * Prefill MPMA Card Tile blocks with starter cards.
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

    var starterParagraph = function () {
        return [
            'core/paragraph',
            {
                content: 'Text here.',
                style: {
                    color: {
                        text: '#ffffff',
                    },
                },
            },
        ];
    };

    var starterCard = function (index) {
        return [
            'genesis-custom-blocks/mpma-card',
            {
                title: 'Card ' + index,
                'title-color': '#ffffff',
            },
            [starterParagraph()],
        ];
    };

    wp.domReady(function () {
        registerDefaultVariation({
            blockName: 'genesis-custom-blocks/mpma-card-tile',
            variation: {
                name: 'tailpress-default-cards',
                title: 'MPMA Card Tile',
                description: 'Layout starting with 4 MPMA Card blocks',
                isDefault: true,
                attributes: {
                    gap: 4,
                    columns: 2,
                    rows: 2,
                },
                innerBlocks: [
                    starterCard(1),
                    starterCard(2),
                    starterCard(3),
                    starterCard(4),
                ],
                scope: ['inserter'],
            },
        });
    });
})();
