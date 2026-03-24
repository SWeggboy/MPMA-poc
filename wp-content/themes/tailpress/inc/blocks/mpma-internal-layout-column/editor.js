(function(wp) {
    if (!wp || !wp.blocks || !wp.blockEditor || !wp.components || !wp.i18n || !wp.element || !wp.data) {
        return;
    }

    const { registerBlockType } = wp.blocks;
    const { InspectorControls, InnerBlocks, useBlockProps } = wp.blockEditor;
    const { PanelBody, SelectControl, Notice } = wp.components;
    const { __ } = wp.i18n;
    const { createElement: el, Fragment, useEffect } = wp.element;

    function getAvailableColumns(clientId) {
        const blockEditorStore = wp.data.select('core/block-editor');
        const rowParentId = blockEditorStore.getBlockRootClientId(clientId);
        const layoutParentId = rowParentId ? blockEditorStore.getBlockRootClientId(rowParentId) : '';
        const layoutBlock = layoutParentId ? blockEditorStore.getBlock(layoutParentId) : null;

        if (!layoutBlock || layoutBlock.name !== 'tailpress/mpma-internal-layout') {
            return 12;
        }

        const attributes = layoutBlock.attributes || {};
        const sidebarEnabled = attributes.sidebarEnabled !== false;
        const resolvedColumns = Number(attributes.contentColumns) || (sidebarEnabled ? 8 : 12);

        return Math.min(12, Math.max(4, resolvedColumns));
    }

    function getOverlapColumns(clientId) {
        const blockEditorStore = wp.data.select('core/block-editor');
        const rowParentId = blockEditorStore.getBlockRootClientId(clientId);
        const rowBlock = rowParentId ? blockEditorStore.getBlock(rowParentId) : null;
        const rowClassName = rowBlock && rowBlock.attributes ? rowBlock.attributes.className || '' : '';

        return rowClassName.includes('mpma-overlap-layout-row') ? 2 : 0;
    }

    registerBlockType('tailpress/mpma-internal-layout-column', {
        edit: function(props) {
            const { attributes, setAttributes, clientId } = props;
            const baseGridColumns = getAvailableColumns(clientId);
            const overlapColumns = getOverlapColumns(clientId);
            const maxGridColumns = baseGridColumns + overlapColumns;
            const directParentId = wp.data.select('core/block-editor').getBlockRootClientId(clientId);
            const parentBlock = directParentId ? wp.data.select('core/block-editor').getBlock(directParentId) : null;
            const siblingColumns = parentBlock && Array.isArray(parentBlock.innerBlocks) ? parentBlock.innerBlocks : [];

            let otherWidthTotal = 0;

            siblingColumns.forEach(function(sibling) {
                if (sibling.clientId === clientId) {
                    return;
                }

                const siblingWidth = Number(sibling.attributes && sibling.attributes.widthColumns) || 1;
                otherWidthTotal += Math.min(maxGridColumns, Math.max(1, siblingWidth));
            });

            const maxAllowedWidth = Math.max(1, Math.min(maxGridColumns, maxGridColumns - otherWidthTotal));
            const safeWidth = Math.min(maxAllowedWidth, Math.max(1, Number(attributes.widthColumns) || 1));
            const safeVerticalAlignment = ['top', 'center', 'bottom'].includes(attributes.verticalAlignment) ? attributes.verticalAlignment : 'top';
            const safeHorizontalAlignment = ['left', 'center', 'right'].includes(attributes.horizontalAlignment) ? attributes.horizontalAlignment : 'left';
            const widthOptions = Array.from({ length: maxAllowedWidth }, function(_, index) {
                const value = index + 1;
                return { label: String(value), value: value };
            });
            const totalWidth = otherWidthTotal + safeWidth;
            const widthHelp = overlapColumns > 0
                ? __('Widths are based on a ' + maxGridColumns + '-column overlap row.', 'tailpress')
                : __('Widths are based on a ' + maxGridColumns + '-column row.', 'tailpress');
            const blockProps = useBlockProps({
                className: 'mpma-internal-layout__column'
                    + ' mpma-internal-layout__column--align-' + safeVerticalAlignment
                    + ' mpma-internal-layout__column--horizontal-' + safeHorizontalAlignment,
                style: {
                    '--mpma-internal-layout-column-span': safeWidth
                }
            });

            useEffect(function() {
                if (safeWidth !== attributes.widthColumns) {
                    setAttributes({ widthColumns: safeWidth });
                }
            }, [attributes.widthColumns, safeWidth]);

            return el(Fragment, null,
                el(InspectorControls, null,
                    el(PanelBody, {
                        title: __('Column Settings', 'tailpress'),
                        initialOpen: true
                    },
                        el(SelectControl, {
                            label: __('Column width', 'tailpress'),
                            value: safeWidth,
                            options: widthOptions,
                            onChange: function(value) {
                                setAttributes({ widthColumns: Number(value) || 1 });
                            },
                            help: widthHelp
                        }),
                        el(SelectControl, {
                            label: __('Vertical alignment', 'tailpress'),
                            value: safeVerticalAlignment,
                            options: [
                                { label: __('Top', 'tailpress'), value: 'top' },
                                { label: __('Middle', 'tailpress'), value: 'center' },
                                { label: __('Bottom', 'tailpress'), value: 'bottom' }
                            ],
                            onChange: function(value) {
                                setAttributes({ verticalAlignment: value || 'top' });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Horizontal alignment', 'tailpress'),
                            value: safeHorizontalAlignment,
                            options: [
                                { label: __('Left', 'tailpress'), value: 'left' },
                                { label: __('Center', 'tailpress'), value: 'center' },
                                { label: __('Right', 'tailpress'), value: 'right' }
                            ],
                            onChange: function(value) {
                                setAttributes({ horizontalAlignment: value || 'left' });
                            }
                        }),
                        el(Notice, {
                            status: 'info',
                            isDismissible: false
                        }, __('This row is currently using ' + totalWidth + ' of ' + maxGridColumns + ' columns.', 'tailpress'))
                    )
                ),
                el('div', blockProps,
                    el('div', { className: 'mpma-internal-layout__column-inner' },
                        el(InnerBlocks, {
                            template: [
                                ['core/paragraph', {}]
                            ],
                            templateLock: false
                        })
                    )
                )
            );
        },

        save: function() {
            return el(InnerBlocks.Content);
        }
    });
})(window.wp);
