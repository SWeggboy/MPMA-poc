/**
 * Register starter variations for MPMA Internal Layout.
 */
(function () {
    if (!window.wp || !wp.blocks || !wp.domReady) {
        return;
    }

    var registerBlockVariation = wp.blocks.registerBlockVariation;
    var getBlockVariations = wp.blocks.getBlockVariations;

    var registerVariation = function (config) {
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

    wp.domReady(function () {
        registerVariation({
            blockName: 'tailpress/mpma-internal-layout',
            variation: {
                name: 'tailpress-image-overlap-panel',
                title: 'MPMA Internal Image Overlap Panel',
                description: '12-column layout with a 4-column image overlapping a 10-column content panel.',
                attributes: {
                    sidebarEnabled: false,
                    contentColumns: 12,
                    contentPosition: 'center'
                },
                innerBlocks: [
                    ['tailpress/mpma-internal-layout-row', { columnCount: 2, className: 'mpma-overlap-layout-row' }, [
                        ['tailpress/mpma-internal-layout-column', { widthColumns: 4, verticalAlignment: 'center' }, [
                            ['core/group', { className: 'mpma-overlap-media' }, [
                                ['core/image', {}]
                            ]]
                        ]],
                        ['tailpress/mpma-internal-layout-column', { widthColumns: 10, verticalAlignment: 'center' }, [
                            ['core/group', { className: 'mpma-overlap-panel' }, [
                                ['core/heading', { content: 'Heading', level: 2 }],
                                ['core/paragraph', { content: 'Body copy here.' }],
                                ['core/buttons', {}, [
                                    ['core/button', { text: 'Learn More' }]
                                ]]
                            ]]
                        ]]
                    ]]
                ],
                scope: ['inserter'],
            },
        });
    });
})();
