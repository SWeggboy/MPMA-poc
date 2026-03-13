(function(wp) {
    if (!wp || !wp.blocks || !wp.blockEditor || !wp.components || !wp.i18n || !wp.element || !wp.data) {
        return;
    }

    const { registerBlockType, createBlock } = wp.blocks;
    const { InspectorControls, InnerBlocks, useBlockProps, useInnerBlocksProps } = wp.blockEditor;
    const { PanelBody, TextControl, RangeControl, SelectControl, ToggleControl } = wp.components;
    const { __ } = wp.i18n;
    const { createElement: el, Fragment, useEffect } = wp.element;
    const { useSelect } = wp.data;

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

    const createBlocksFromTemplate = function(template) {
        return (template || []).map(function(item) {
            const name = item && item[0] ? item[0] : '';
            const attributes = item && item[1] ? item[1] : {};
            const innerTemplate = item && Array.isArray(item[2]) ? item[2] : [];

            return createBlock(name, attributes, createBlocksFromTemplate(innerTemplate));
        });
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
        const headingColumn = headingRow && Array.isArray(headingRow.innerBlocks) ? headingRow.innerBlocks[0] : null;
        const contentColumns = contentRow && Array.isArray(contentRow.innerBlocks) ? contentRow.innerBlocks : [];
        const buttonColumn = buttonRow && Array.isArray(buttonRow.innerBlocks) ? buttonRow.innerBlocks[0] : null;
        const headingBlock = headingColumn && Array.isArray(headingColumn.innerBlocks) ? headingColumn.innerBlocks[0] : null;
        const leftParagraph = contentColumns[0] && Array.isArray(contentColumns[0].innerBlocks) ? contentColumns[0].innerBlocks[0] : null;
        const rightParagraph = contentColumns[1] && Array.isArray(contentColumns[1].innerBlocks) ? contentColumns[1].innerBlocks[0] : null;
        const buttonsBlock = buttonColumn && Array.isArray(buttonColumn.innerBlocks) ? buttonColumn.innerBlocks[0] : null;
        const firstButton = buttonsBlock && Array.isArray(buttonsBlock.innerBlocks) ? buttonsBlock.innerBlocks[0] : null;

        return headingBlock
            && headingBlock.name === 'core/heading'
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
        const headingBlock = groupChildren[0];
        const listBlock = groupChildren[1];
        const groupClassName = groupBlock && groupBlock.attributes ? String(groupBlock.attributes.className || '') : '';

        return groupBlock
            && groupBlock.name === 'core/group'
            && groupClassName.indexOf('mpma-internal-carousel-awards-panel') !== -1
            && headingBlock
            && headingBlock.name === 'core/heading'
            && listBlock
            && listBlock.name === 'core/list'
            && listBlock.attributes
            && String(listBlock.attributes.values || '') === '<li>' + __('Recognition item one', 'tailpress') + '</li><li>' + __('Recognition item two', 'tailpress') + '</li><li>' + __('Recognition item three', 'tailpress') + '</li>';
    };

    const buildTemplate = function(navLabel, variation, awardsSettings) {
        const awardsWidthColumns = Math.max(1, Math.min(12, Number(awardsSettings && awardsSettings.widthColumns) || 6));
        const awardsVerticalAlignment = awardsSettings && ['top', 'center', 'bottom'].includes(awardsSettings.verticalAlignment)
            ? awardsSettings.verticalAlignment
            : 'top';
        const awardsHorizontalAlignment = awardsSettings && ['left', 'center', 'right'].includes(awardsSettings.horizontalAlignment)
            ? awardsSettings.horizontalAlignment
            : 'center';
        const awardsShowBullets = !!(awardsSettings && awardsSettings.showBullets);
        const awardsShowDividers = awardsSettings ? awardsSettings.showDividers !== false : true;
        const awardsPaddingTop = getAwardsPaddingValue(awardsSettings && awardsSettings.paddingTop, '2rem');
        const awardsPaddingRight = getAwardsPaddingValue(awardsSettings && awardsSettings.paddingRight, '1.5rem');
        const awardsPaddingBottom = getAwardsPaddingValue(awardsSettings && awardsSettings.paddingBottom, '2rem');
        const awardsPaddingLeft = getAwardsPaddingValue(awardsSettings && awardsSettings.paddingLeft, '1.5rem');

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
                                    content: navLabel || __('Year', 'tailpress'),
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
                            content: navLabel || __('Slide Heading', 'tailpress'),
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

    const findFirstHeadingBlock = function(blocks) {
        for (let index = 0; index < blocks.length; index += 1) {
            const block = blocks[index];

            if (!block) {
                continue;
            }

            if (block.name === 'core/heading') {
                return block;
            }

            const childBlocks = Array.isArray(block.innerBlocks) ? block.innerBlocks : [];
            const nestedHeading = findFirstHeadingBlock(childBlocks);

            if (nestedHeading) {
                return nestedHeading;
            }
        }

        return null;
    };

    const findFirstBlock = function(blocks, predicate) {
        for (let index = 0; index < blocks.length; index += 1) {
            const block = blocks[index];

            if (!block) {
                continue;
            }

            if (predicate(block)) {
                return block;
            }

            const childBlocks = Array.isArray(block.innerBlocks) ? block.innerBlocks : [];
            const nestedBlock = findFirstBlock(childBlocks, predicate);

            if (nestedBlock) {
                return nestedBlock;
            }
        }

        return null;
    };

    const syncHeadingWithLabel = function(clientId, nextLabel) {
        const blockEditorStore = wp.data.select('core/block-editor');
        const currentBlock = blockEditorStore.getBlock(clientId);
        const innerBlocks = currentBlock && Array.isArray(currentBlock.innerBlocks) ? currentBlock.innerBlocks : [];
        const headingBlock = findFirstHeadingBlock(innerBlocks);

        if (!headingBlock || !headingBlock.clientId) {
            return;
        }

        wp.data.dispatch('core/block-editor').updateBlockAttributes(headingBlock.clientId, {
            content: nextLabel || __('Slide Heading', 'tailpress')
        });
    };

    const syncAwardsPanelSettings = function(clientId, settings) {
        const blockEditorStore = wp.data.select('core/block-editor');
        const currentBlock = blockEditorStore.getBlock(clientId);
        const innerBlocks = currentBlock && Array.isArray(currentBlock.innerBlocks) ? currentBlock.innerBlocks : [];
        const firstLayoutBlock = findFirstBlock(innerBlocks, function(block) {
            return block.name === 'tailpress/mpma-internal-layout';
        });
        const firstColumnBlock = findFirstBlock(innerBlocks, function(block) {
            return block.name === 'tailpress/mpma-internal-layout-column';
        });
        const firstPanelBlock = findFirstBlock(innerBlocks, function(block) {
            return block.name === 'core/group'
                && block.attributes
                && typeof block.attributes.className === 'string'
                && block.attributes.className.indexOf('mpma-internal-carousel-awards-panel') !== -1;
        });
        const firstListBlock = findFirstBlock(innerBlocks, function(block) {
            return block.name === 'core/list';
        });

        if (firstLayoutBlock && firstLayoutBlock.clientId) {
            const currentLayoutAttributes = firstLayoutBlock.attributes || {};
            const layoutSidebarEnabled = currentLayoutAttributes.sidebarEnabled !== false;
            const nextContentColumns = layoutSidebarEnabled
                ? Math.min(8, Math.max(4, settings.widthColumns))
                : Math.max(Number(currentLayoutAttributes.contentColumns) || 12, settings.widthColumns);

            if ((Number(currentLayoutAttributes.contentColumns) || 0) !== nextContentColumns) {
                wp.data.dispatch('core/block-editor').updateBlockAttributes(firstLayoutBlock.clientId, {
                    contentColumns: nextContentColumns
                });
            }
        }

        if (firstColumnBlock && firstColumnBlock.clientId) {
            const nextColumnAttributes = {};
            const currentColumnAttributes = firstColumnBlock.attributes || {};

            if ((Number(currentColumnAttributes.widthColumns) || 0) !== settings.widthColumns) {
                nextColumnAttributes.widthColumns = settings.widthColumns;
            }

            if ((currentColumnAttributes.verticalAlignment || 'top') !== settings.verticalAlignment) {
                nextColumnAttributes.verticalAlignment = settings.verticalAlignment;
            }

            if ((currentColumnAttributes.horizontalAlignment || 'left') !== settings.horizontalAlignment) {
                nextColumnAttributes.horizontalAlignment = settings.horizontalAlignment;
            }

            if (Object.keys(nextColumnAttributes).length) {
                wp.data.dispatch('core/block-editor').updateBlockAttributes(firstColumnBlock.clientId, nextColumnAttributes);
            }
        }

        if (firstPanelBlock && firstPanelBlock.clientId) {
            const currentPanelAttributes = firstPanelBlock.attributes || {};
            const currentStyle = currentPanelAttributes.style || {};
            const currentSpacing = currentStyle.spacing || {};
            const currentPadding = currentSpacing.padding || {};
            const nextPadding = {
                top: settings.paddingTop,
                right: settings.paddingRight,
                bottom: settings.paddingBottom,
                left: settings.paddingLeft
            };

            if (
                currentPadding.top !== nextPadding.top
                || currentPadding.right !== nextPadding.right
                || currentPadding.bottom !== nextPadding.bottom
                || currentPadding.left !== nextPadding.left
            ) {
                wp.data.dispatch('core/block-editor').updateBlockAttributes(firstPanelBlock.clientId, {
                    style: Object.assign({}, currentStyle, {
                        spacing: Object.assign({}, currentSpacing, {
                            padding: nextPadding
                        })
                    })
                });
            }
        }

        if (firstListBlock && firstListBlock.clientId) {
            const nextListClassName = buildAwardsListClassName(settings.showBullets, settings.showDividers);
            const currentListClassName = firstListBlock.attributes ? firstListBlock.attributes.className || '' : '';

            if (currentListClassName !== nextListClassName) {
                wp.data.dispatch('core/block-editor').updateBlockAttributes(firstListBlock.clientId, {
                    className: nextListClassName
                });
            }
        }
    };

    registerBlockType('tailpress/mpma-internal-full-width-carousel-slide', {
        edit: function(props) {
            const { attributes, setAttributes, clientId, isSelected } = props;
            const navLabel = String(attributes.navLabel || '');
            const seedVariation = attributes.seedVariation === 'awards' ? 'awards' : attributes.seedVariation === 'default' ? 'default' : '';
            const isCustomized = !!attributes.isCustomized;
            const awardsPanelWidthColumns = Math.max(1, Number(attributes.awardsPanelWidthColumns) || 6);
            const awardsPanelVerticalAlignment = ['top', 'center', 'bottom'].includes(attributes.awardsPanelVerticalAlignment)
                ? attributes.awardsPanelVerticalAlignment
                : 'top';
            const awardsPanelHorizontalAlignment = ['left', 'center', 'right'].includes(attributes.awardsPanelHorizontalAlignment)
                ? attributes.awardsPanelHorizontalAlignment
                : 'center';
            const awardsShowBullets = !!attributes.awardsShowBullets;
            const awardsShowDividers = attributes.awardsShowDividers !== false;
            const awardsPanelPaddingTop = getAwardsPaddingValue(attributes.awardsPanelPaddingTop, '2rem');
            const awardsPanelPaddingRight = getAwardsPaddingValue(attributes.awardsPanelPaddingRight, '1.5rem');
            const awardsPanelPaddingBottom = getAwardsPaddingValue(attributes.awardsPanelPaddingBottom, '2rem');
            const awardsPanelPaddingLeft = getAwardsPaddingValue(attributes.awardsPanelPaddingLeft, '1.5rem');
            const editorState = useSelect(function(select) {
                const blockEditor = select('core/block-editor');
                const rootClientId = select('core/block-editor').getBlockRootClientId(clientId);
                const parentBlock = rootClientId ? blockEditor.getBlock(rootClientId) : null;
                const innerBlocks = blockEditor.getBlocks(clientId) || [];
                const layoutBlock = findFirstBlock(innerBlocks, function(block) {
                    return block.name === 'tailpress/mpma-internal-layout';
                });
                const layoutAttributes = layoutBlock && layoutBlock.attributes ? layoutBlock.attributes : {};
                const layoutSidebarEnabled = layoutAttributes.sidebarEnabled !== false;
                const layoutMaxColumns = layoutSidebarEnabled ? 8 : 12;

                return {
                    parentVariation: parentBlock && parentBlock.attributes && parentBlock.attributes.variation === 'awards' ? 'awards' : 'default',
                    hasInnerBlocks: innerBlocks.length > 0,
                    awardsPanelMaxColumns: Math.max(4, Math.min(12, layoutMaxColumns)),
                    innerBlocks: innerBlocks
                };
            }, [clientId]);
            const parentVariation = editorState.parentVariation;
            const hasInnerBlocks = editorState.hasInnerBlocks;
            const currentInnerBlocks = Array.isArray(editorState.innerBlocks) ? editorState.innerBlocks : [];
            const awardsPanelMaxColumns = Math.max(1, editorState.awardsPanelMaxColumns || 6);
            const safeAwardsPanelWidthColumns = Math.max(1, Math.min(awardsPanelMaxColumns, awardsPanelWidthColumns));
            const awardsSettings = {
                widthColumns: safeAwardsPanelWidthColumns,
                verticalAlignment: awardsPanelVerticalAlignment,
                horizontalAlignment: awardsPanelHorizontalAlignment,
                showBullets: awardsShowBullets,
                showDividers: awardsShowDividers,
                paddingTop: awardsPanelPaddingTop,
                paddingRight: awardsPanelPaddingRight,
                paddingBottom: awardsPanelPaddingBottom,
                paddingLeft: awardsPanelPaddingLeft
            };
            const blockProps = useBlockProps({
                className: 'mpma-internal-full-width-carousel-slide-editor' + (parentVariation === 'awards' ? ' is-awards' : '')
            });
            const innerBlocksProps = useInnerBlocksProps(
                {
                    className: 'mpma-internal-full-width-carousel-slide-editor__inner'
                },
                {
                    template: hasInnerBlocks ? undefined : buildTemplate(navLabel, parentVariation, awardsSettings),
                    templateLock: false,
                    renderAppender: isSelected ? InnerBlocks.ButtonBlockAppender : false
                }
            );

            useEffect(function() {
                if (!hasInnerBlocks) {
                    return;
                }

                const hasStarterLayout = isDefaultStarterSlide(currentInnerBlocks) || isAwardsStarterSlide(currentInnerBlocks);

                if (!hasStarterLayout) {
                    if (!isCustomized || seedVariation !== parentVariation) {
                        setAttributes({
                            isCustomized: true,
                            seedVariation: parentVariation
                        });
                    }
                    return;
                }

                if (seedVariation !== parentVariation) {
                    setAttributes({
                        seedVariation: parentVariation,
                        isCustomized: false
                    });
                }

                if (isCustomized) {
                    return;
                }
            }, [
                hasInnerBlocks,
                seedVariation,
                parentVariation,
                isCustomized,
                currentInnerBlocks,
                setAttributes
            ]);

            useEffect(function() {
                if (parentVariation !== 'awards' || !hasInnerBlocks) {
                    return;
                }

                syncAwardsPanelSettings(clientId, awardsSettings);
            }, [
                clientId,
                parentVariation,
                hasInnerBlocks,
                awardsSettings.widthColumns,
                awardsSettings.verticalAlignment,
                awardsSettings.horizontalAlignment,
                awardsSettings.showBullets,
                awardsSettings.showDividers,
                awardsSettings.paddingTop,
                awardsSettings.paddingRight,
                awardsSettings.paddingBottom,
                awardsSettings.paddingLeft
            ]);

            return el(Fragment, null,
                el(InspectorControls, null,
                    el(PanelBody, {
                        title: __('Slide Settings', 'tailpress'),
                        initialOpen: true
                    },
                        el(TextControl, {
                            label: __('Navigation label', 'tailpress'),
                            value: navLabel,
                            onChange: function(value) {
                                const nextValue = value || '';
                                setAttributes({ navLabel: nextValue });
                                syncHeadingWithLabel(clientId, nextValue);
                            },
                            help: __('Upper navigation label shown above the carousel.', 'tailpress')
                        })
                    ),
                    parentVariation === 'awards' && el(PanelBody, {
                        title: __('Awards Panel', 'tailpress'),
                        initialOpen: true
                    },
                        el(RangeControl, {
                            label: __('Panel width columns', 'tailpress'),
                            value: safeAwardsPanelWidthColumns,
                            onChange: function(value) {
                                setAttributes({ awardsPanelWidthColumns: Math.max(1, Number(value) || 1) });
                            },
                            min: 1,
                            max: awardsPanelMaxColumns
                        }),
                        el(SelectControl, {
                            label: __('Vertical alignment', 'tailpress'),
                            value: awardsPanelVerticalAlignment,
                            options: [
                                { label: __('Top', 'tailpress'), value: 'top' },
                                { label: __('Middle', 'tailpress'), value: 'center' },
                                { label: __('Bottom', 'tailpress'), value: 'bottom' }
                            ],
                            onChange: function(value) {
                                setAttributes({ awardsPanelVerticalAlignment: value || 'top' });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Horizontal alignment', 'tailpress'),
                            value: awardsPanelHorizontalAlignment,
                            options: [
                                { label: __('Left', 'tailpress'), value: 'left' },
                                { label: __('Center', 'tailpress'), value: 'center' },
                                { label: __('Right', 'tailpress'), value: 'right' }
                            ],
                            onChange: function(value) {
                                setAttributes({ awardsPanelHorizontalAlignment: value || 'center' });
                            }
                        }),
                        el(ToggleControl, {
                            label: __('Show list bullets', 'tailpress'),
                            checked: awardsShowBullets,
                            onChange: function(value) {
                                setAttributes({ awardsShowBullets: !!value });
                            }
                        }),
                        el(ToggleControl, {
                            label: __('Show list dividers', 'tailpress'),
                            checked: awardsShowDividers,
                            onChange: function(value) {
                                setAttributes({ awardsShowDividers: !!value });
                            },
                            help: __('If you replace the list with other blocks, this toggle only affects the first list block found in the awards panel.', 'tailpress')
                        }),
                        el(TextControl, {
                            label: __('Padding top', 'tailpress'),
                            value: awardsPanelPaddingTop,
                            onChange: function(value) {
                                setAttributes({ awardsPanelPaddingTop: value || '2rem' });
                            },
                            help: __('Default: 2rem', 'tailpress')
                        }),
                        el(TextControl, {
                            label: __('Padding right', 'tailpress'),
                            value: awardsPanelPaddingRight,
                            onChange: function(value) {
                                setAttributes({ awardsPanelPaddingRight: value || '1.5rem' });
                            },
                            help: __('Default: 1.5rem', 'tailpress')
                        }),
                        el(TextControl, {
                            label: __('Padding bottom', 'tailpress'),
                            value: awardsPanelPaddingBottom,
                            onChange: function(value) {
                                setAttributes({ awardsPanelPaddingBottom: value || '2rem' });
                            },
                            help: __('Default: 2rem', 'tailpress')
                        }),
                        el(TextControl, {
                            label: __('Padding left', 'tailpress'),
                            value: awardsPanelPaddingLeft,
                            onChange: function(value) {
                                setAttributes({ awardsPanelPaddingLeft: value || '1.5rem' });
                            },
                            help: __('Default: 1.5rem', 'tailpress')
                        })
                    )
                ),
                el('section', blockProps,
                    el('div', {
                        className: 'mpma-internal-full-width-carousel-slide-editor__label',
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
                        }, __('Carousel Slide', 'tailpress')),
                        el('span', {
                            style: {
                                display: 'inline-flex',
                                alignItems: 'center',
                                padding: '0.125rem 0.5rem',
                                backgroundColor: 'rgba(15, 23, 42, 0.06)',
                                fontSize: '0.75rem',
                                lineHeight: 1.4
                            }
                        }, parentVariation === 'awards' ? __('Awards', 'tailpress') : __('Default', 'tailpress'))
                    ),
                    el('div', innerBlocksProps)
                )
            );
        },

        save: function() {
            return el(InnerBlocks.Content);
        }
    });
})(window.wp);
