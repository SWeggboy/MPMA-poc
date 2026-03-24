(function(wp) {
    if (!wp || !wp.blocks || !wp.blockEditor || !wp.components || !wp.i18n || !wp.element || !wp.data) {
        return;
    }

    const { registerBlockType, createBlock, cloneBlock } = wp.blocks;
    const { InspectorControls, InnerBlocks, useBlockProps, useInnerBlocksProps } = wp.blockEditor;
    const { PanelBody, RangeControl } = wp.components;
    const { __ } = wp.i18n;
    const { createElement: el, Fragment, useEffect, useRef } = wp.element;

    function getAvailableColumns(clientId) {
        const blockEditorStore = wp.data.select('core/block-editor');
        const directParentId = blockEditorStore.getBlockRootClientId(clientId);
        const layoutParentId = directParentId ? blockEditorStore.getBlockRootClientId(directParentId) : '';
        const layoutBlock = layoutParentId ? blockEditorStore.getBlock(layoutParentId) : null;

        if (!layoutBlock || layoutBlock.name !== 'tailpress/mpma-internal-layout') {
            return 12;
        }

        const attributes = layoutBlock.attributes || {};
        const sidebarEnabled = attributes.sidebarEnabled !== false;
        const resolvedColumns = Number(attributes.contentColumns) || (sidebarEnabled ? 8 : 12);
        const safeColumns = Math.min(12, Math.max(4, resolvedColumns));

        return safeColumns;
    }

    function getOverlapColumns(className) {
        return typeof className === 'string' && className.includes('mpma-overlap-layout-row') ? 2 : 0;
    }

    function getColumnWidths(columnCount, total, overlapColumns) {
        const safeCount = Math.max(1, Number(columnCount) || 1);
        const safeTotal = Math.max(1, Number(total) || 12);

        if (overlapColumns === 2 && safeCount === 2) {
            return [4, safeTotal - 4];
        }

        return distributeWidths(safeCount, safeTotal);
    }

    function distributeWidths(columnCount, total) {
        const safeTotal = Math.min(12, Math.max(1, Number(total) || 12));
        const safeCount = Math.min(safeTotal, Math.max(1, Number(columnCount) || 1));
        const baseWidth = Math.floor(safeTotal / safeCount);
        let remainder = safeTotal - (baseWidth * safeCount);

        return Array.from({ length: safeCount }, function() {
            const width = baseWidth + (remainder > 0 ? 1 : 0);
            if (remainder > 0) {
                remainder -= 1;
            }
            return width;
        });
    }

    registerBlockType('tailpress/mpma-internal-layout-row', {
        edit: function(props) {
            const { attributes, setAttributes, clientId } = props;
            const baseGridColumns = getAvailableColumns(clientId);
            const overlapColumns = getOverlapColumns(attributes.className);
            const configurableColumns = baseGridColumns + overlapColumns;
            const safeDesiredCount = Math.min(configurableColumns, Math.max(1, Number(attributes.columnCount) || 2));
            const pendingInspectorCount = useRef(null);
            const rowBlock = wp.data.select('core/block-editor').getBlock(clientId);
            const actualColumns = rowBlock && Array.isArray(rowBlock.innerBlocks) ? rowBlock.innerBlocks.filter(function(innerBlock) {
                return innerBlock.name === 'tailpress/mpma-internal-layout-column';
            }) : [];
            const firstSpan = Math.max(1, Math.min(configurableColumns, Number(actualColumns[0] && actualColumns[0].attributes ? actualColumns[0].attributes.widthColumns : 4) || 4));
            const secondStart = Math.max(1, firstSpan - overlapColumns + 1);
            const blockProps = useBlockProps({
                className: 'mpma-internal-layout__row',
                style: overlapColumns > 0 ? {
                    '--mpma-overlap-columns': String(overlapColumns),
                    '--mpma-overlap-first-span': String(firstSpan),
                    '--mpma-overlap-second-start': String(secondStart)
                } : undefined
            });
            const innerBlocksProps = useInnerBlocksProps(blockProps, {
                allowedBlocks: ['tailpress/mpma-internal-layout-column'],
                templateLock: 'all',
                orientation: 'horizontal',
                style: {
                    display: 'grid',
                    gridTemplateColumns: 'repeat(' + baseGridColumns + ', minmax(0, 1fr))',
                    gap: '1.5rem',
                    alignItems: 'start'
                }
            });

            useEffect(function() {
                if (safeDesiredCount !== attributes.columnCount) {
                    setAttributes({ columnCount: safeDesiredCount });
                }
            }, [attributes.columnCount, safeDesiredCount]);

            useEffect(function() {
                const blockEditorStore = wp.data.select('core/block-editor');
                const rowBlock = blockEditorStore.getBlock(clientId);
                const existingColumns = rowBlock && Array.isArray(rowBlock.innerBlocks) ? rowBlock.innerBlocks : [];
                const actualColumns = existingColumns.filter(function(innerBlock) {
                    return innerBlock.name === 'tailpress/mpma-internal-layout-column';
                });
                const actualCount = actualColumns.length;

                if (actualCount === 0) {
                    const widths = getColumnWidths(safeDesiredCount, configurableColumns, overlapColumns);
                    const nextColumns = widths.map(function(width) {
                        return createBlock('tailpress/mpma-internal-layout-column', {
                            widthColumns: width
                        }, [
                            createBlock('core/paragraph', {})
                        ]);
                    });

                    wp.data.dispatch('core/block-editor').replaceInnerBlocks(clientId, nextColumns, false);
                    pendingInspectorCount.current = null;
                    return;
                }

                if (pendingInspectorCount.current === null && actualCount !== safeDesiredCount) {
                    setAttributes({ columnCount: actualCount });
                    return;
                }

                if (actualCount === safeDesiredCount && actualColumns.length === existingColumns.length) {
                    pendingInspectorCount.current = null;
                    return;
                }

                const widths = getColumnWidths(safeDesiredCount, configurableColumns, overlapColumns);
                const nextColumns = widths.map(function(width, index) {
                    const existingColumn = actualColumns[index];

                    if (existingColumn && existingColumn.name === 'tailpress/mpma-internal-layout-column') {
                        return cloneBlock(existingColumn, {
                            widthColumns: width
                        });
                    }

                    return createBlock('tailpress/mpma-internal-layout-column', {
                        widthColumns: width
                    }, [
                        createBlock('core/paragraph', {})
                    ]);
                });

                wp.data.dispatch('core/block-editor').replaceInnerBlocks(clientId, nextColumns, false);
                pendingInspectorCount.current = null;
            }, [clientId, safeDesiredCount, baseGridColumns, configurableColumns, overlapColumns]);

            return el(Fragment, null,
                el(InspectorControls, null,
                    el(PanelBody, {
                        title: __('Row Settings', 'tailpress'),
                        initialOpen: true
                    },
                        el(RangeControl, {
                            label: __('Number of columns', 'tailpress'),
                            value: safeDesiredCount,
                            onChange: function(value) {
                                pendingInspectorCount.current = Number(value) || 1;
                                setAttributes({ columnCount: Number(value) || 1 });
                            },
                            min: 1,
                            max: overlapColumns > 0 ? 2 : baseGridColumns
                        })
                    )
                ),
                el('div', innerBlocksProps)
            );
        },

        save: function() {
            return el(InnerBlocks.Content);
        }
    });
})(window.wp);
