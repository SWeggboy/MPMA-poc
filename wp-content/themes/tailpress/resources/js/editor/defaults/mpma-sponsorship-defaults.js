/**
 * Prefill MPMA Sponsorship blocks with starter layout.
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

    var sponsorshipAccordionItem = function () {
        return [
            'core/accordion-item',
            {},
            [
                [
                    'core/accordion-heading',
                    {
                        title: 'MPMA ANNUAL MEETING',
                    },
                ],
                [
                    'core/accordion-panel',
                    {},
                    [
                        [
                            'core/paragraph',
                            {
                                content: 'Add image or text content here.',
                            },
                        ],
                    ],
                ],
            ],
        ];
    };

    wp.domReady(function () {
        registerDefaultVariation({
            blockName: 'genesis-custom-blocks/mpma-sponsorship',
            variation: {
                name: 'tailpress-default-sponsorship-layout',
                title: 'MPMA Sponsorship',
                description: 'Layout starting with a 50/50 sponsorship & advertising promotion.',
                isDefault: true,
                innerBlocks: [
                    [
                        'genesis-blocks/gb-columns',
                        {
                            columns: 2,
                            layout: 'gb-2-col-equal',
                            columnsGap: 5,
                            marginBottom: 1.5,
                            marginUnit: 'rem',
                            className: 'mpma-sponsorship__main-columns',
                        },
                        [
                            [
                                'genesis-blocks/gb-column',
                                {
                                    className: 'mpma-sponsorship__media-column',
                                },
                                [
                                    [
                                        'core/heading',
                                        {
                                            level: 2,
                                            content: 'Sponsorship Opportunities',
                                        },
                                    ],
                                    [
                                        'core/paragraph',
                                        {
                                            content: 'Add sponsorship summary copy here.',
                                        },
                                    ],
                                    [
                                        'core/accordion',
                                        {},
                                        [
                                            sponsorshipAccordionItem(),
                                            sponsorshipAccordionItem(),
                                            sponsorshipAccordionItem(),
                                            sponsorshipAccordionItem(),
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'genesis-blocks/gb-column',
                                {},
                                [
                                    [
                                        'core/image',
                                        {
                                            sizeSlug: 'full',
                                            linkDestination: 'none',
                                        },
                                    ],
                                    [
                                        'core/buttons',
                                        {
                                            layout: {
                                                type: 'flex',
                                                justifyContent: 'center',
                                            },
                                        },
                                        [
                                            [
                                                'core/button',
                                                {
                                                    text: 'DOWNLOAD PDF',
                                                },
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'genesis-blocks/gb-columns',
                        {
                            columns: 1,
                            layout: 'one-column',
                            marginBottom: 1.5,
                            marginUnit: 'rem',
                            className: 'mpma-sponsorship__footer-columns',
                        },
                        [
                            [
                                'genesis-blocks/gb-column',
                                {},
                                [
                                    [
                                        'core/paragraph',
                                        {
                                            content: 'Add centered supporting copy here.',
                                            align: 'center',
                                        },
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                scope: ['inserter'],
            },
        });
    });
})();
