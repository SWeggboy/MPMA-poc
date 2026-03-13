(function(wp) {
    if (!wp || !wp.blocks || !wp.blockEditor || !wp.components || !wp.i18n || !wp.element || !wp.data) {
        return;
    }

    const { registerBlockType } = wp.blocks;
    const { InspectorControls, InnerBlocks, useBlockProps, useInnerBlocksProps, ColorPalette: BlockEditorColorPalette } = wp.blockEditor;
    const { PanelBody, ToggleControl, TextControl, RangeControl, SelectControl } = wp.components;
    const { __ } = wp.i18n;
    const { createElement: el, Fragment } = wp.element;
    const { useSelect } = wp.data;
    const ColorPalette = BlockEditorColorPalette || wp.components.ColorPalette;

    const ALLOWED_BLOCKS = ['tailpress/mpma-internal-card'];
    const CAROUSEL_WIDTH_OPTIONS = [{ label: __('Auto', 'tailpress'), value: '0' }].concat(
        Array.from({ length: 12 }, function(_, index) {
            const value = String(index + 1);
            return { label: value, value: value };
        })
    );

    const buildTemplate = function(count) {
        return Array.from({ length: count }, function() {
            return ['tailpress/mpma-internal-card', {}];
        });
    };

    registerBlockType('tailpress/mpma-internal-card-tile', {
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const gap = attributes.gap || '1.5rem';
            const carouselEnabled = !!attributes.carouselEnabled;
            const navColor = attributes.navColor || '#ffffff';
            const rows = carouselEnabled ? 1 : Math.max(1, Number(attributes.rows) || 1);
            const columns = Math.max(carouselEnabled ? 2 : 1, Number(attributes.columns) || 2);
            const viewportCards = Math.max(1, Number(attributes.viewportCards) || 1);
            const safeViewportCards = carouselEnabled ? Math.min(columns, viewportCards) : 1;
            const innerBlocks = useSelect(function(select) {
                return select('core/block-editor').getBlocks(props.clientId);
            }, [props.clientId]);
            const firstCardBlock = (innerBlocks || []).find(function(block) {
                return block && block.name === 'tailpress/mpma-internal-card';
            });
            const firstCardColumns = Math.min(12, Math.max(1, Number(firstCardBlock && firstCardBlock.attributes ? firstCardBlock.attributes.widthColumns : 4) || 4));
            const resolvedCarouselWidthColumns = Math.min(
                12,
                Math.max(1, Number(attributes.carouselWidthColumns) || (firstCardColumns * safeViewportCards))
            );
            const templateCount = Math.max(2, rows * columns);

            const blockProps = useBlockProps({
                className: 'mpma-internal-card-tile-editor' + (carouselEnabled ? ' is-carousel' : ' is-grid'),
                style: {
                    '--mpma-internal-card-tile-gap': gap,
                    '--mpma-internal-card-tile-columns': String(columns),
                    '--mpma-internal-card-tile-rows': String(rows),
                    '--mpma-internal-card-tile-visible': String(safeViewportCards),
                    '--mpma-internal-card-tile-carousel-columns': String(resolvedCarouselWidthColumns),
                    '--mpma-internal-card-tile-nav-color': navColor
                }
            });

            const trackStyle = carouselEnabled
                ? {
                    display: 'flex',
                    gap: gap,
                    width: '100%',
                    minWidth: 0
                }
                : {
                    display: 'grid',
                    gridTemplateColumns: 'repeat(' + columns + ', minmax(0, 1fr))',
                    gridTemplateRows: 'repeat(' + rows + ', minmax(0, 1fr))',
                    gap: gap,
                    alignItems: 'stretch',
                    width: '100%',
                    minWidth: 0
                };

            const innerBlocksProps = useInnerBlocksProps(
                {
                    className: 'mpma-internal-card-tile__track' + (carouselEnabled ? ' is-carousel' : ' is-grid'),
                    style: trackStyle
                },
                {
                    allowedBlocks: ALLOWED_BLOCKS,
                    template: buildTemplate(templateCount),
                    templateLock: false,
                    orientation: carouselEnabled ? 'horizontal' : 'vertical',
                    renderAppender: InnerBlocks.ButtonBlockAppender
                }
            );

            const summaryParts = carouselEnabled
                ? [
                    __('Carousel', 'tailpress'),
                    String(columns) + ' ' + __('cards', 'tailpress'),
                    String(safeViewportCards) + ' ' + __('visible', 'tailpress'),
                    String(resolvedCarouselWidthColumns) + ' ' + __('cols wide', 'tailpress')
                ]
                : [
                    __('Grid', 'tailpress'),
                    String(rows) + ' ' + __('rows', 'tailpress'),
                    String(columns) + ' ' + __('columns', 'tailpress'),
                    gap + ' ' + __('gap', 'tailpress')
                ];

            return el(Fragment, null,
                el(InspectorControls, null,
                    el(PanelBody, {
                        title: __('Tile Settings', 'tailpress'),
                        initialOpen: true
                    },
                        el(TextControl, {
                            label: __('Gap', 'tailpress'),
                            value: gap,
                            onChange: function(value) {
                                setAttributes({ gap: value || '1.5rem' });
                            },
                            help: __('Defaults to the current 12-column gutter. Use px or rem values.', 'tailpress')
                        }),
                        el(RangeControl, {
                            label: __('Rows', 'tailpress'),
                            value: rows,
                            onChange: function(value) {
                                setAttributes({ rows: carouselEnabled ? 1 : Math.max(1, Number(value) || 1) });
                            },
                            min: 1,
                            max: 6,
                            step: 1,
                            disabled: carouselEnabled
                        }),
                        el(RangeControl, {
                            label: __('Columns', 'tailpress'),
                            value: columns,
                            onChange: function(value) {
                                const nextColumns = Math.max(carouselEnabled ? 2 : 1, Number(value) || 1);
                                setAttributes({
                                    columns: nextColumns,
                                    viewportCards: carouselEnabled ? Math.min(nextColumns, safeViewportCards) : attributes.viewportCards
                                });
                            },
                            min: carouselEnabled ? 2 : 1,
                            max: 6,
                            step: 1
                        }),
                        el(ToggleControl, {
                            label: __('Display as carousel', 'tailpress'),
                            checked: carouselEnabled,
                            onChange: function(value) {
                                const enabled = !!value;
                                const nextColumns = enabled ? Math.max(2, columns) : columns;
                                setAttributes({
                                    carouselEnabled: enabled,
                                    rows: enabled ? 1 : rows,
                                    columns: nextColumns,
                                    viewportCards: enabled ? Math.min(nextColumns, Math.max(1, safeViewportCards)) : attributes.viewportCards
                                });
                            }
                        }),
                        carouselEnabled && el(RangeControl, {
                            label: __('Visible cards in viewport', 'tailpress'),
                            value: safeViewportCards,
                            onChange: function(value) {
                                setAttributes({ viewportCards: Math.min(columns, Math.max(1, Number(value) || 1)) });
                            },
                            min: 1,
                            max: columns,
                            step: 1
                        }),
                        carouselEnabled && el(SelectControl, {
                            label: __('Carousel Width Columns', 'tailpress'),
                            value: String(Number(attributes.carouselWidthColumns) || 0),
                            options: CAROUSEL_WIDTH_OPTIONS,
                            help: __('Auto matches the contained card width multiplied by the visible card count.', 'tailpress'),
                            onChange: function(value) {
                                setAttributes({ carouselWidthColumns: Number(value) || 0 });
                            }
                        }),
                        carouselEnabled && el('div', { style: { marginBottom: '1rem' } },
                            el('p', { style: { margin: '0 0 0.5rem', fontSize: '11px', fontWeight: 500, textTransform: 'uppercase' } }, __('Navigation Color', 'tailpress')),
                            el(ColorPalette, {
                                value: navColor,
                                onChange: function(value) {
                                    setAttributes({ navColor: value || '#ffffff' });
                                },
                                clearable: false
                            })
                        ),
                        carouselEnabled && el(ToggleControl, {
                            label: __('Loop forever', 'tailpress'),
                            checked: attributes.loopEnabled !== false,
                            onChange: function(value) {
                                setAttributes({ loopEnabled: !!value });
                            }
                        })
                    )
                ),
                el('section', blockProps,
                        el('div', {
                            className: 'mpma-internal-card-tile-editor__label',
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
                        }, __('MPMA Internal Card Tile', 'tailpress')),
                        summaryParts.map(function(part, index) {
                            return el('span', {
                                key: index,
                                style: {
                                    display: 'inline-flex',
                                    alignItems: 'center',
                                    padding: '0.125rem 0.5rem',
                                    borderRadius: '0',
                                    backgroundColor: 'rgba(15, 23, 42, 0.06)',
                                    fontSize: '0.75rem',
                                    lineHeight: 1.4
                                }
                            }, part);
                        })
                    ),
                    carouselEnabled && el('div', { className: 'mpma-internal-card-tile__editor-nav' },
                        el('button', {
                            type: 'button',
                            className: 'mpma-internal-card-tile__nav-button',
                            disabled: true,
                            'aria-hidden': 'true'
                        }, '<'),
                        el('button', {
                            type: 'button',
                            className: 'mpma-internal-card-tile__nav-button',
                            disabled: true,
                            'aria-hidden': 'true'
                        }, '>')
                    ),
                    el('div', {
                            className: 'mpma-internal-card-tile__viewport'
                        },
                        el('div', innerBlocksProps)
                    )
                )
            );
        },

        save: function() {
            return el(InnerBlocks.Content);
        }
    });
})(window.wp);
