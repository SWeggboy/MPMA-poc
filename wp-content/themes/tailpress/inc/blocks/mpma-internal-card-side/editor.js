(function(wp) {
    if (!wp || !wp.blocks || !wp.blockEditor || !wp.components || !wp.i18n || !wp.element || !wp.data) {
        return;
    }

    const { registerBlockType, serialize } = wp.blocks;
    const { InspectorControls, InnerBlocks, MediaUpload, MediaUploadCheck, useBlockProps } = wp.blockEditor;
    const { PanelBody, SelectControl, Button, ColorPalette } = wp.components;
    const { __ } = wp.i18n;
    const { createElement: el, Fragment, useEffect, useState } = wp.element;
    const { useSelect } = wp.data;

    const SIDE_OPTIONS = [
        { label: __('Front', 'tailpress'), value: 'front' },
        { label: __('Back', 'tailpress'), value: 'back' }
    ];

    registerBlockType('tailpress/mpma-internal-card-side', {
        edit: function(props) {
            const { attributes, setAttributes, clientId, isSelected } = props;
            const side = attributes.side === 'back' ? 'back' : 'front';
            const [isEditMode, setIsEditMode] = useState(false);
            const innerBlocks = useSelect(function(select) {
                return select('core/block-editor').getBlocks(clientId);
            }, [clientId]);
            const hasSelectedInnerBlock = useSelect(function(select) {
                return select('core/block-editor').hasSelectedInnerBlock(clientId, true);
            }, [clientId]);
            const isEditing = !!hasSelectedInnerBlock || isEditMode;
            const previewHtml = serialize(innerBlocks || []);

            useEffect(function() {
                if (!isSelected && !hasSelectedInnerBlock) {
                    setIsEditMode(false);
                }
            }, [hasSelectedInnerBlock, isSelected]);

            const blockProps = useBlockProps({
                className: 'mpma-internal-card__side mpma-internal-card__side--' + side + (isEditing ? ' is-editing' : ' is-preview'),
                style: {
                    backgroundColor: attributes.backgroundColor || '#ffffff',
                    backgroundImage: attributes.backgroundImage ? 'url("' + attributes.backgroundImage + '")' : undefined,
                    backgroundPosition: attributes.backgroundImage ? 'center' : undefined,
                    backgroundSize: attributes.backgroundImage ? 'cover' : undefined,
                    backgroundRepeat: attributes.backgroundImage ? 'no-repeat' : undefined
                },
                'data-internal-card-side': side
            });

            return el(Fragment, null,
                el(InspectorControls, null,
                    el(PanelBody, {
                        title: __('Card Side Settings', 'tailpress'),
                        initialOpen: true
                    },
                        el(SelectControl, {
                            label: __('Side', 'tailpress'),
                            value: side,
                            options: SIDE_OPTIONS,
                            onChange: function(value) {
                                setAttributes({ side: value });
                            }
                        }),
                        el('div', { style: { marginBottom: '1rem' } },
                            el('p', { style: { margin: '0 0 0.5rem', fontSize: '11px', fontWeight: 500, textTransform: 'uppercase' } }, __('Background Color', 'tailpress')),
                            el(ColorPalette, {
                                value: attributes.backgroundColor || '#ffffff',
                                onChange: function(value) {
                                    setAttributes({ backgroundColor: value || '#ffffff' });
                                },
                                clearable: false
                            })
                        ),
                        el(MediaUploadCheck, null,
                            el(MediaUpload, {
                                onSelect: function(media) {
                                    setAttributes({
                                        backgroundImage: media && media.url ? media.url : '',
                                        backgroundImageId: media && media.id ? media.id : 0
                                    });
                                },
                                allowedTypes: ['image'],
                                value: attributes.backgroundImageId || 0,
                                render: function(obj) {
                                    return el(Button, {
                                        onClick: obj.open,
                                        variant: 'secondary'
                                    }, attributes.backgroundImage
                                        ? __('Replace background image', 'tailpress')
                                        : __('Select background image', 'tailpress'));
                                }
                            })
                        ),
                        attributes.backgroundImage && el(Button, {
                            onClick: function() {
                                setAttributes({ backgroundImage: '', backgroundImageId: 0 });
                            },
                            variant: 'link',
                            isDestructive: true
                        }, __('Remove background image', 'tailpress'))
                    )
                ),
                el('div', blockProps,
                    el('div', { className: 'mpma-internal-card__overlay' }),
                    el('div', { className: 'mpma-internal-card__content' },
                        isEditing
                            ? el(InnerBlocks, {
                                allowedBlocks: ['core/heading', 'core/paragraph', 'core/list', 'core/buttons', 'core/button', 'core/image', 'core/spacer'],
                                templateLock: false
                            })
                            : el(Fragment, null,
                                el('div', {
                                    className: 'mpma-internal-card__preview',
                                    dangerouslySetInnerHTML: { __html: previewHtml }
                                }),
                                isSelected && el('div', {
                                        style: {
                                            marginTop: '1rem',
                                            display: 'flex',
                                            justifyContent: 'center'
                                        }
                                    },
                                    el(Button, {
                                        variant: 'secondary',
                                        onClick: function() {
                                            setIsEditMode(true);
                                        }
                                    }, __('Edit side content', 'tailpress'))
                                )
                            )
                    )
                )
            );
        },
        save: function() {
            const { attributes } = arguments[0];
            const { useBlockProps, InnerBlocks } = wp.blockEditor;
            const side = attributes.side === 'back' ? 'back' : 'front';
            const blockProps = useBlockProps.save({
                className: 'mpma-internal-card__side mpma-internal-card__side--' + side,
                style: {
                    backgroundColor: attributes.backgroundColor || '#ffffff',
                    backgroundImage: attributes.backgroundImage ? 'url("' + attributes.backgroundImage + '")' : undefined,
                    backgroundPosition: attributes.backgroundImage ? 'center' : undefined,
                    backgroundSize: attributes.backgroundImage ? 'cover' : undefined,
                    backgroundRepeat: attributes.backgroundImage ? 'no-repeat' : undefined
                },
                'data-internal-card-side': side,
                'aria-hidden': side === 'front' ? 'false' : 'true'
            });

            return el('div', blockProps,
                el('div', { className: 'mpma-internal-card__overlay' }),
                el('div', { className: 'mpma-internal-card__content' }, el(InnerBlocks.Content))
            );
        }
    });
})(window.wp);
