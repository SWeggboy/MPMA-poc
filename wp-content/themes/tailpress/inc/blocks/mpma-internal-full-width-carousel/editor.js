(function(wp) {
    if (!wp || !wp.blocks || !wp.blockEditor || !wp.components || !wp.i18n || !wp.element || !wp.data) {
        return;
    }

    const { registerBlockType, createBlock } = wp.blocks;
    const { InspectorControls, InnerBlocks, useBlockProps, ColorPalette } = wp.blockEditor;
    const { PanelBody, RangeControl, SelectControl, ToggleControl } = wp.components;
    const { __ } = wp.i18n;
    const { createElement: el, Fragment } = wp.element;
    const { useSelect } = wp.data;

    const ALLOWED_BLOCKS = ['tailpress/mpma-internal-full-width-carousel-slide'];
    const DEFAULT_TEMPLATE = [
        ['tailpress/mpma-internal-full-width-carousel-slide', {}]
    ];
    const NAV_COLOR_PRESETS = {
        default: {
            active: '#000000',
            inactive: '#747474'
        },
        light: {
            active: '#ffffff',
            inactive: '#cfcfcf'
        }
    };

    const createBlocksFromTemplate = function(template) {
        return (template || []).map(function(item) {
            const name = item && item[0] ? item[0] : '';
            const attributes = item && item[1] ? item[1] : {};
            const innerTemplate = item && Array.isArray(item[2]) ? item[2] : [];

            return createBlock(name, attributes, createBlocksFromTemplate(innerTemplate));
        });
    };

    const buildAwardsListClassName = function(showBullets, showDividers) {
        const classes = ['mpma-internal-carousel-awards-list'];

        if (showBullets) {
            classes.push('has-bullets');
        }

        if (showDividers) {
            classes.push('has-dividers');
        }

        return classes.join(' ');
    };

    const getAwardsPaddingValue = function(value, fallback) {
        const normalizedValue = String(value || '').trim();
        return normalizedValue || fallback;
    };

    const buildSlideTemplate = function(navLabel, variation, slideAttributes) {
        const safeNavLabel = String(navLabel || '').trim();
        const awardsWidthColumns = Math.max(1, Math.min(12, Number(slideAttributes && slideAttributes.awardsPanelWidthColumns) || 6));
        const awardsVerticalAlignment = ['top', 'center', 'bottom'].includes(slideAttributes && slideAttributes.awardsPanelVerticalAlignment)
            ? slideAttributes.awardsPanelVerticalAlignment
            : 'top';
        const awardsHorizontalAlignment = ['left', 'center', 'right'].includes(slideAttributes && slideAttributes.awardsPanelHorizontalAlignment)
            ? slideAttributes.awardsPanelHorizontalAlignment
            : 'center';
        const awardsShowBullets = !!(slideAttributes && slideAttributes.awardsShowBullets);
        const awardsShowDividers = slideAttributes ? slideAttributes.awardsShowDividers !== false : true;
        const awardsPaddingTop = getAwardsPaddingValue(slideAttributes && slideAttributes.awardsPanelPaddingTop, '2rem');
        const awardsPaddingRight = getAwardsPaddingValue(slideAttributes && slideAttributes.awardsPanelPaddingRight, '1.5rem');
        const awardsPaddingBottom = getAwardsPaddingValue(slideAttributes && slideAttributes.awardsPanelPaddingBottom, '2rem');
        const awardsPaddingLeft = getAwardsPaddingValue(slideAttributes && slideAttributes.awardsPanelPaddingLeft, '1.5rem');

        if (variation === 'awards') {
            return [
                ['tailpress/mpma-internal-layout', {
                    fullWidth: false,
                    sidebarEnabled: false,
                    contentColumns: 12,
                    contentPosition: 'center'
                }, [
                    ['tailpress/mpma-internal-layout-row', { columnCount: 1 }, [
                        ['tailpress/mpma-internal-layout-column', {
                            widthColumns: awardsWidthColumns,
                            verticalAlignment: awardsVerticalAlignment,
                            horizontalAlignment: awardsHorizontalAlignment
                        }, [
                            ['core/group', {
                                className: 'mpma-internal-carousel-awards-panel',
                                style: {
                                    spacing: {
                                        padding: {
                                            top: awardsPaddingTop,
                                            right: awardsPaddingRight,
                                            bottom: awardsPaddingBottom,
                                            left: awardsPaddingLeft
                                        }
                                    }
                                }
                            }, [
                                ['core/heading', {
                                    level: 2,
                                    content: safeNavLabel || __('Year', 'tailpress'),
                                    textAlign: 'center'
                                }],
                                ['core/list', {
                                    className: buildAwardsListClassName(awardsShowBullets, awardsShowDividers),
                                    values: '<li>' + __('Recognition item one', 'tailpress') + '</li><li>' + __('Recognition item two', 'tailpress') + '</li><li>' + __('Recognition item three', 'tailpress') + '</li>'
                                }]
                            ]]
                        ]]
                    ]]
                ]]
            ];
        }

        return [
            ['tailpress/mpma-internal-layout', {
                fullWidth: false,
                sidebarEnabled: false,
                contentColumns: 10,
                contentPosition: 'center'
            }, [
                ['tailpress/mpma-internal-layout-row', { columnCount: 1 }, [
                    ['tailpress/mpma-internal-layout-column', { widthColumns: 10, verticalAlignment: 'top' }, [
                        ['core/heading', {
                            level: 2,
                            content: safeNavLabel || __('Slide Heading', 'tailpress'),
                            textAlign: 'center',
                            style: {
                                spacing: {
                                    margin: {
                                        top: '3rem'
                                    }
                                },
                                typography: {
                                    fontSize: '2.245rem',
                                    fontWeight: '700'
                                }
                            }
                        }]
                    ]]
                ]],
                ['tailpress/mpma-internal-layout-row', { columnCount: 2 }, [
                    ['tailpress/mpma-internal-layout-column', { widthColumns: 6, verticalAlignment: 'top' }, [
                        ['core/paragraph', {}]
                    ]],
                    ['tailpress/mpma-internal-layout-column', { widthColumns: 4, verticalAlignment: 'top' }, [
                        ['core/paragraph', {}]
                    ]]
                ]],
                ['tailpress/mpma-internal-layout-row', { columnCount: 1 }, [
                    ['tailpress/mpma-internal-layout-column', { widthColumns: 10, verticalAlignment: 'top' }, [
                        ['core/buttons', {
                            layout: {
                                type: 'flex',
                                justifyContent: 'center'
                            }
                        }, [
                            ['core/button', { text: __('Learn More', 'tailpress') }]
                        ]]
                    ]]
                ]]
            ]]
        ];
    };

    const isDefaultStarterSlide = function(blocks) {
        if (!Array.isArray(blocks) || blocks.length !== 1) {
            return false;
        }

        const layoutBlock = blocks[0];

        if (!layoutBlock || layoutBlock.name !== 'tailpress/mpma-internal-layout') {
            return false;
        }

        const rows = Array.isArray(layoutBlock.innerBlocks) ? layoutBlock.innerBlocks : [];

        if (rows.length !== 3) {
            return false;
        }

        const [headingRow, contentRow, buttonRow] = rows;
        const contentColumns = contentRow && Array.isArray(contentRow.innerBlocks) ? contentRow.innerBlocks : [];
        const leftParagraph = contentColumns[0] && Array.isArray(contentColumns[0].innerBlocks) ? contentColumns[0].innerBlocks[0] : null;
        const rightParagraph = contentColumns[1] && Array.isArray(contentColumns[1].innerBlocks) ? contentColumns[1].innerBlocks[0] : null;
        const buttonColumn = buttonRow && Array.isArray(buttonRow.innerBlocks) ? buttonRow.innerBlocks[0] : null;
        const buttonsBlock = buttonColumn && Array.isArray(buttonColumn.innerBlocks) ? buttonColumn.innerBlocks[0] : null;
        const firstButton = buttonsBlock && Array.isArray(buttonsBlock.innerBlocks) ? buttonsBlock.innerBlocks[0] : null;

        return headingRow
            && contentColumns.length === 2
            && leftParagraph
            && leftParagraph.name === 'core/paragraph'
            && rightParagraph
            && rightParagraph.name === 'core/paragraph'
            && buttonsBlock
            && buttonsBlock.name === 'core/buttons'
            && firstButton
            && firstButton.name === 'core/button'
            && firstButton.attributes
            && firstButton.attributes.text === __('Learn More', 'tailpress');
    };

    const isAwardsStarterSlide = function(blocks) {
        if (!Array.isArray(blocks) || blocks.length !== 1) {
            return false;
        }

        const layoutBlock = blocks[0];

        if (!layoutBlock || layoutBlock.name !== 'tailpress/mpma-internal-layout') {
            return false;
        }

        const rows = Array.isArray(layoutBlock.innerBlocks) ? layoutBlock.innerBlocks : [];

        if (rows.length !== 1) {
            return false;
        }

        const firstColumn = rows[0] && Array.isArray(rows[0].innerBlocks) ? rows[0].innerBlocks[0] : null;
        const groupBlock = firstColumn && Array.isArray(firstColumn.innerBlocks) ? firstColumn.innerBlocks[0] : null;
        const groupChildren = groupBlock && Array.isArray(groupBlock.innerBlocks) ? groupBlock.innerBlocks : [];
        const listBlock = groupChildren[1];
        const groupClassName = groupBlock && groupBlock.attributes ? String(groupBlock.attributes.className || '') : '';

        return groupBlock
            && groupBlock.name === 'core/group'
            && groupClassName.indexOf('mpma-internal-carousel-awards-panel') !== -1
            && listBlock
            && listBlock.name === 'core/list';
    };

    registerBlockType('tailpress/mpma-internal-full-width-carousel', {
        edit: function(props) {
            const { attributes, setAttributes, clientId } = props;
            const variation = attributes.variation === 'awards' ? 'awards' : 'default';
            const navColorPreset = attributes.navColorPreset === 'light' ? 'light' : attributes.navColorPreset === 'custom' ? 'custom' : 'default';
            const navActiveColor = String(attributes.navActiveColor || NAV_COLOR_PRESETS.default.active);
            const navInactiveColor = String(attributes.navInactiveColor || NAV_COLOR_PRESETS.default.inactive);
            const animationSpeed = Math.max(150, Number(attributes.animationSpeed) || 400);
            const rawViewportLabels = Math.max(1, Number(attributes.navViewportLabels) || 4);
            const slideBlocks = useSelect(function(select) {
                return select('core/block-editor').getBlocks(clientId);
            }, [clientId]) || [];
            const slideCount = slideBlocks.filter(function(block) {
                return block && block.name === 'tailpress/mpma-internal-full-width-carousel-slide';
            }).length;
            const enableNavOverflow = attributes.enableNavOverflow !== false;
            const navViewportLabels = Math.max(1, Math.min(Math.max(1, slideCount || 1), rawViewportLabels));
            const navLabels = slideBlocks.map(function(block, index) {
                const navLabel = block && block.attributes ? String(block.attributes.navLabel || '').trim() : '';
                return navLabel || __('Slide', 'tailpress') + ' ' + (index + 1);
            });
            const blockProps = useBlockProps({
                className: 'mpma-internal-full-width-carousel-editor'
                    + (variation === 'awards' ? ' is-awards' : '')
                    + (enableNavOverflow ? ' has-nav-overflow' : ''),
                style: Object.assign(
                    {},
                    enableNavOverflow ? {
                        '--mpma-internal-full-width-carousel-nav-visible': String(navViewportLabels)
                    } : {},
                    {
                        '--mpma-internal-full-width-carousel-nav-active-color': navActiveColor,
                        '--mpma-internal-full-width-carousel-nav-inactive-color': navInactiveColor
                    }
                )
            });

            return el(Fragment, null,
                el(InspectorControls, null,
                    el(PanelBody, {
                        title: __('Carousel Settings', 'tailpress'),
                        initialOpen: true
                    },
                        el(SelectControl, {
                            label: __('Variation', 'tailpress'),
                            value: variation,
                            options: [
                                { label: __('Default', 'tailpress'), value: 'default' },
                                { label: __('Awards', 'tailpress'), value: 'awards' }
                            ],
                            onChange: function(value) {
                                const nextVariation = value === 'awards' ? 'awards' : 'default';
                                slideBlocks.forEach(function(block) {
                                    if (!block || block.name !== 'tailpress/mpma-internal-full-width-carousel-slide' || !block.clientId) {
                                        return;
                                    }

                                    const slideAttributes = block.attributes || {};
                                    const nextNavLabel = String(slideAttributes.navLabel || '');

                                    wp.data.dispatch('core/block-editor').replaceInnerBlocks(
                                        block.clientId,
                                        createBlocksFromTemplate(buildSlideTemplate(nextNavLabel, nextVariation, slideAttributes)),
                                        false
                                    );
                                    wp.data.dispatch('core/block-editor').updateBlockAttributes(block.clientId, {
                                        seedVariation: nextVariation,
                                        isCustomized: false
                                    });
                                });

                                setAttributes({ variation: nextVariation });
                            }
                        }),
                        el(RangeControl, {
                            label: __('Animation speed (ms)', 'tailpress'),
                            value: animationSpeed,
                            onChange: function(value) {
                                setAttributes({ animationSpeed: Math.max(150, Number(value) || 400) });
                            },
                            min: 150,
                            max: 1500,
                            step: 50
                        })
                    ),
                    el(
                        PanelBody,
                        {
                            title: __('Navigation', 'tailpress'),
                            initialOpen: true
                        },
                        el(ToggleControl, {
                            label: __('Enable Overflow', 'tailpress'),
                            checked: enableNavOverflow,
                            onChange: function(value) {
                                setAttributes({ enableNavOverflow: !!value });
                            },
                            help: enableNavOverflow
                                ? __('Navigation is clipped to a measured viewport when labels exceed the available width.', 'tailpress')
                                : __('Navigation uses the non-overflow layout behavior.', 'tailpress')
                        }),
                        el(RangeControl, {
                            label: __('Viewport label count', 'tailpress'),
                            value: navViewportLabels,
                            onChange: function(value) {
                                setAttributes({ navViewportLabels: Math.max(1, Number(value) || 1) });
                            },
                            min: 1,
                            max: Math.max(1, slideCount || 1),
                            disabled: !enableNavOverflow,
                            help: __('Viewport width is calculated from this many labels using the widest rendered label.', 'tailpress')
                        }),
                        el(SelectControl, {
                            label: __('Color preset', 'tailpress'),
                            value: navColorPreset,
                            options: [
                                { label: __('Default', 'tailpress'), value: 'default' },
                                { label: __('Light mode', 'tailpress'), value: 'light' },
                                { label: __('Custom', 'tailpress'), value: 'custom' }
                            ],
                            onChange: function(value) {
                                const nextPreset = value === 'light' ? 'light' : value === 'custom' ? 'custom' : 'default';
                                const presetColors = NAV_COLOR_PRESETS[nextPreset];

                                if (presetColors) {
                                    setAttributes({
                                        navColorPreset: nextPreset,
                                        navActiveColor: presetColors.active,
                                        navInactiveColor: presetColors.inactive
                                    });
                                    return;
                                }

                                setAttributes({ navColorPreset: nextPreset });
                            }
                        }),
                        el(
                            'div',
                            {
                                style: {
                                    marginTop: '1rem'
                                }
                            },
                            el(
                                'p',
                                {
                                    style: {
                                        margin: '0 0 0.5rem',
                                        fontSize: '11px',
                                        fontWeight: 600,
                                        textTransform: 'uppercase'
                                    }
                                },
                                __('Active color', 'tailpress')
                            ),
                            el(ColorPalette, {
                                value: navActiveColor,
                                onChange: function(value) {
                                    setAttributes({
                                        navColorPreset: 'custom',
                                        navActiveColor: value || NAV_COLOR_PRESETS.default.active
                                    });
                                }
                            })
                        ),
                        el(
                            'div',
                            {
                                style: {
                                    marginTop: '1rem'
                                }
                            },
                            el(
                                'p',
                                {
                                    style: {
                                        margin: '0 0 0.5rem',
                                        fontSize: '11px',
                                        fontWeight: 600,
                                        textTransform: 'uppercase'
                                    }
                                },
                                __('Inactive color', 'tailpress')
                            ),
                            el(ColorPalette, {
                                value: navInactiveColor,
                                onChange: function(value) {
                                    setAttributes({
                                        navColorPreset: 'custom',
                                        navInactiveColor: value || NAV_COLOR_PRESETS.default.inactive
                                    });
                                }
                            })
                        )
                    )
                ),
                el('section', blockProps,
                    el('div', {
                        className: 'mpma-internal-full-width-carousel-editor__label',
                        style: {
                            position: 'relative',
                            zIndex: 20,
                            display: 'flex',
                            flexWrap: 'wrap',
                            alignItems: 'center',
                            gap: '0.5rem',
                            margin: '0 0 1rem',
                            padding: '0.5rem 0.75rem',
                            border: '1px solid var(--color-accent-light)',
                            borderRadius: '0',
                            backgroundColor: 'rgba(255,255,255,0.92)',
                            color: '#1f2937',
                            boxShadow: '0 1px 2px rgba(15, 23, 42, 0.08)'
                        }
                    },
                        el('span', {
                            style: {
                                fontSize: '0.75rem',
                                fontWeight: 600,
                                letterSpacing: '0.04em',
                                textTransform: 'uppercase'
                            }
                        }, __('MPMA Internal Carousel', 'tailpress')),
                        el('span', {
                            style: {
                                display: 'inline-flex',
                                alignItems: 'center',
                                padding: '0.125rem 0.5rem',
                                backgroundColor: 'rgba(15, 23, 42, 0.06)',
                                fontSize: '0.75rem',
                                lineHeight: 1.4
                            }
                        }, slideCount + ' ' + __('slides', 'tailpress')),
                        el('span', {
                            style: {
                                display: 'inline-flex',
                                alignItems: 'center',
                                padding: '0.125rem 0.5rem',
                                backgroundColor: 'rgba(15, 23, 42, 0.06)',
                                fontSize: '0.75rem',
                                lineHeight: 1.4
                            }
                        }, variation === 'awards' ? __('Awards', 'tailpress') : __('Default', 'tailpress')),
                        el('span', {
                            style: {
                                display: 'inline-flex',
                                alignItems: 'center',
                                padding: '0.125rem 0.5rem',
                                backgroundColor: 'rgba(15, 23, 42, 0.06)',
                                fontSize: '0.75rem',
                                lineHeight: 1.4
                            }
                        }, animationSpeed + 'ms'),
                        el('span', {
                            style: {
                                display: 'inline-flex',
                                alignItems: 'center',
                                padding: '0.125rem 0.5rem',
                                backgroundColor: 'rgba(15, 23, 42, 0.06)',
                                fontSize: '0.75rem',
                                lineHeight: 1.4
                            }
                        }, enableNavOverflow
                            ? __('Overflow on', 'tailpress') + ' · ' + navViewportLabels
                            : __('Overflow off', 'tailpress'))
                    ),
                    slideCount > 0 && el('div', {
                        className: 'mpma-internal-full-width-carousel-editor__nav'
                    },
                        el('button', {
                            type: 'button',
                            className: 'mpma-internal-full-width-carousel__arrow',
                            disabled: true,
                            'aria-hidden': 'true'
                        }, '<'),
                        el('div', {
                            className: 'mpma-internal-full-width-carousel__nav-viewport'
                        },
                            el('div', {
                                className: 'mpma-internal-full-width-carousel__nav-items'
                            }, navLabels.map(function(label, index) {
                                return el('span', {
                                    key: index,
                                    className: 'mpma-internal-full-width-carousel__nav-item' + (index === 0 ? ' is-active' : '')
                                }, label);
                            }))
                        ),
                        el('button', {
                            type: 'button',
                            className: 'mpma-internal-full-width-carousel__arrow',
                            disabled: true,
                            'aria-hidden': 'true'
                        }, '>')
                    ),
                    el(InnerBlocks, {
                        allowedBlocks: ALLOWED_BLOCKS,
                        template: DEFAULT_TEMPLATE,
                        templateLock: false,
                        renderAppender: InnerBlocks.ButtonBlockAppender
                    })
                )
            );
        },

        save: function() {
            return el(InnerBlocks.Content);
        }
    });
})(window.wp);
