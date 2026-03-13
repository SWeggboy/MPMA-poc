(function(wp) {
    if (!wp || !wp.blocks || !wp.blockEditor || !wp.components || !wp.i18n || !wp.element) {
        return;
    }

    const { registerBlockType } = wp.blocks;
    const { InspectorControls, InnerBlocks, MediaUpload, MediaUploadCheck, useBlockProps } = wp.blockEditor;
    const { PanelBody, ToggleControl, TextControl, SelectControl, Button } = wp.components;
    const { __ } = wp.i18n;
    const { createElement: el, Fragment } = wp.element;
    const HORIZONTAL_CONTENT_POSITION_OPTIONS = [
        { label: __('Left', 'tailpress'), value: 'left' },
        { label: __('Center', 'tailpress'), value: 'center' },
        { label: __('Right', 'tailpress'), value: 'right' }
    ];
    const VERTICAL_CONTENT_POSITION_OPTIONS = [
        { label: __('Top', 'tailpress'), value: 'top' },
        { label: __('Middle', 'tailpress'), value: 'center' },
        { label: __('Bottom', 'tailpress'), value: 'bottom' }
    ];
    const DEFAULT_TEMPLATE = [
        ['tailpress/mpma-internal-layout-row', { columnCount: 2 }, [
            ['tailpress/mpma-internal-layout-column', { widthColumns: 6 }, [
                ['core/paragraph', {}]
            ]],
            ['tailpress/mpma-internal-layout-column', { widthColumns: 6 }, [
                ['core/paragraph', {}]
            ]]
        ]]
    ];

    function buildColumnOptions(maxColumns) {
        return Array.from({ length: Math.max(1, maxColumns - 3) }, function(_, index) {
            const value = index + 4;
            return { label: String(value), value: value };
        });
    }

    registerBlockType('tailpress/mpma-internal-layout', {
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const {
                fullWidth,
                backgroundImage,
                backgroundImageId,
                usePageTitleOverlay,
                minHeight,
                sidebarEnabled,
                contentColumns,
                contentPosition,
                verticalContentPosition
            } = attributes;

            const maxContentColumns = sidebarEnabled ? 8 : 12;
            const resolvedColumns = Number(contentColumns) || (sidebarEnabled ? 8 : 12);
            const safeColumns = Math.min(maxContentColumns, Math.max(4, resolvedColumns));
            const columnOptions = buildColumnOptions(maxContentColumns);
            const remainingColumns = Math.max(0, 12 - safeColumns);
            const resolvedContentPosition = contentPosition || 'center';
            const resolvedVerticalContentPosition = ['top', 'center', 'bottom'].includes(verticalContentPosition) ? verticalContentPosition : 'top';
            let leftSpacerColumns = 0;
            let rightSpacerColumns = 0;

            if (sidebarEnabled) {
                rightSpacerColumns = remainingColumns;
            } else if (resolvedContentPosition === 'left') {
                rightSpacerColumns = remainingColumns;
            } else if (resolvedContentPosition === 'right') {
                leftSpacerColumns = remainingColumns;
            } else {
                leftSpacerColumns = Math.floor(remainingColumns / 2);
                rightSpacerColumns = Math.max(0, remainingColumns - leftSpacerColumns);
            }

            const blockProps = useBlockProps({
                className: 'mpma-internal-layout-editor'
                    + (fullWidth ? ' mpma-internal-layout-editor--full-width alignfull' : ''),
                style: {
                    minHeight: minHeight || undefined,
                    backgroundImage: backgroundImage ? 'url("' + backgroundImage + '")' : undefined,
                    backgroundSize: backgroundImage ? 'cover' : undefined,
                    backgroundPosition: backgroundImage ? 'center' : undefined,
                    backgroundRepeat: backgroundImage ? 'no-repeat' : undefined,
                    paddingTop: '1.5rem',
                    paddingBottom: '1.5rem'
                }
            });

            const previewGrid = {
                display: 'grid',
                gridTemplateColumns: 'repeat(12, minmax(0, 1fr))',
                gap: '1.5rem',
                alignItems: 'start'
            };

            return el(Fragment, null,
                el(InspectorControls, null,
                    el(PanelBody, {
                        title: __('Layout Settings', 'tailpress'),
                        initialOpen: true
                    },
                        el(ToggleControl, {
                            label: __('Full width background breakout', 'tailpress'),
                            checked: !!fullWidth,
                            onChange: function(value) {
                                setAttributes({ fullWidth: !!value });
                            }
                        }),
                        el(ToggleControl, {
                            label: __('Reserve 4-column sidebar space', 'tailpress'),
                            checked: !!sidebarEnabled,
                            onChange: function(value) {
                                setAttributes({
                                    sidebarEnabled: !!value,
                                    contentColumns: value ? 8 : 12
                                });
                            },
                            help: __('Keeps the content in the left content column and leaves the right 4 columns empty on desktop.', 'tailpress')
                        }),
                        el(SelectControl, {
                            label: __('Content columns', 'tailpress'),
                            value: safeColumns,
                            options: columnOptions,
                            onChange: function(value) {
                                setAttributes({ contentColumns: Number(value) });
                            }
                        }),
                        !sidebarEnabled && el(SelectControl, {
                            label: __('Horizontal Content Position', 'tailpress'),
                            value: resolvedContentPosition,
                            options: HORIZONTAL_CONTENT_POSITION_OPTIONS,
                            onChange: function(value) {
                                setAttributes({ contentPosition: value || 'center' });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Vertical Content Position', 'tailpress'),
                            value: resolvedVerticalContentPosition,
                            options: VERTICAL_CONTENT_POSITION_OPTIONS,
                            onChange: function(value) {
                                setAttributes({ verticalContentPosition: value || 'top' });
                            }
                        }),
                        el(TextControl, {
                            label: __('Minimum height', 'tailpress'),
                            value: minHeight || '',
                            onChange: function(value) {
                                setAttributes({ minHeight: value || '' });
                            },
                            help: __('Use CSS units, e.g. 455px, 32rem, 60vh.', 'tailpress')
                        }),
                        el(MediaUploadCheck, null,
                            el(MediaUpload, {
                                onSelect: function(media) {
                                    setAttributes({
                                        backgroundImage: media && media.url ? media.url : '',
                                        backgroundImageId: media && media.id ? media.id : 0
                                    });
                                },
                                allowedTypes: ['image'],
                                value: backgroundImageId || 0,
                                render: function(obj) {
                                    return el(Button, {
                                        onClick: obj.open,
                                        variant: 'secondary'
                                    }, backgroundImage ? __('Replace background image', 'tailpress') : __('Select background image', 'tailpress'));
                                }
                            })
                        ),
                        backgroundImage && el(ToggleControl, {
                            label: __('Use Page Title Background overlay', 'tailpress'),
                            checked: !!usePageTitleOverlay,
                            onChange: function(value) {
                                setAttributes({ usePageTitleOverlay: !!value });
                            }
                        }),
                        backgroundImage && el(Button, {
                            onClick: function() {
                                setAttributes({ backgroundImage: '', backgroundImageId: 0, usePageTitleOverlay: false });
                            },
                            variant: 'link',
                            isDestructive: true
                        }, __('Remove background image', 'tailpress'))
                    )
                ),
                el('section', blockProps,
                    backgroundImage && usePageTitleOverlay && el(Fragment, null,
                        el('div', {
                            className: 'absolute inset-0 bg-[#004d84]/25 pointer-events-none',
                            style: {
                                opacity: 0.95
                            }
                        }),
                        el('div', {
                            className: 'absolute inset-0 pointer-events-none bg-[linear-gradient(to_bottom,rgba(20,58,94,0)_45%,rgba(20,58,94,0.50)_100%),linear-gradient(to_right,rgba(0,77,132,0.66)_0%,rgba(0,77,132,0.53)_25%,rgba(0,77,132,0.27)_50%,rgba(0,77,132,0.06)_75%,rgba(0,77,132,0)_100%),linear-gradient(to_right,rgba(91,152,113,0)_0%,rgba(91,152,113,0.18)_35%,rgba(91,152,113,0.45)_70%,rgba(91,152,113,0.72)_100%)]',
                            style: {
                                opacity: 0.95
                            }
                        })
                    ),
                        el('div', {
                            className: 'mpma-internal-layout-editor__label',
                            style: {
                                position: 'relative',
                                zIndex: 20,
                                display: 'inline-flex',
                                alignItems: 'center',
                                gap: '0.5rem',
                                margin: '0 0 1rem',
                                padding: '0.5rem 0.75rem',
                                border: '1px solid var(--color-accent-light)',
                                borderRadius: '0',
                                backgroundColor: 'rgba(255,255,255,0.92)',
                                color: '#1f2937',
                                fontSize: '0.75rem',
                                fontWeight: 600,
                                letterSpacing: '0.04em',
                                textTransform: 'uppercase',
                                boxShadow: '0 1px 2px rgba(15, 23, 42, 0.08)'
                            }
                        },
                        __('MPMA Internal Layout', 'tailpress')
                    ),
                    el('div', {
                            className: 'layout-shell',
                            style: {
                                paddingTop: '1.5rem',
                                paddingBottom: '1.5rem',
                                position: 'relative',
                                zIndex: 10
                            }
                        },
                        el('div', {
                                className: 'layout-grid items-start gap-y-8',
                                style: previewGrid
                            },
                            leftSpacerColumns > 0 && el('div', {
                                'aria-hidden': 'true',
                                style: {
                                    gridColumn: 'span ' + leftSpacerColumns + ' / span ' + leftSpacerColumns
                                }
                            }),
                            el('div', {
                                    style: {
                                        gridColumn: (leftSpacerColumns + 1) + ' / span ' + safeColumns,
                                        alignSelf: ({
                                            top: 'start',
                                            center: 'center',
                                            bottom: 'end'
                                        }[resolvedVerticalContentPosition] || 'start')
                                    }
                                },
                                el('div', {
                                    style: {
                                        minHeight: '12rem',
                                        padding: '1.5rem',
                                        color: '#000000',
                                        '--mpma-internal-layout-row-columns': String(safeColumns)
                                    }
                                },
                                    el(InnerBlocks, {
                                        allowedBlocks: ['tailpress/mpma-internal-layout-row'],
                                        template: DEFAULT_TEMPLATE,
                                        templateLock: false
                                    })
                                )
                            ),
                            rightSpacerColumns > 0 && el('div', {
                                'aria-hidden': 'true',
                                style: {
                                    gridColumn: 'span ' + rightSpacerColumns + ' / span ' + rightSpacerColumns,
                                    minHeight: '12rem'
                                }
                            })
                        )
                    )
                )
            );
        },

        save: function() {
            return el(InnerBlocks.Content);
        }
    });
})(window.wp);
