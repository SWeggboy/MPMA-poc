(function(wp) {
    if (!wp || !wp.blocks || !wp.blockEditor || !wp.components || !wp.i18n || !wp.element || !wp.data) {
        return;
    }

    const { registerBlockType } = wp.blocks;
    const { InspectorControls, useBlockProps, MediaUpload, MediaUploadCheck, InnerBlocks, RichText } = wp.blockEditor;
    const { Button, PanelBody, TextControl, SelectControl, RangeControl } = wp.components;
    const { __ } = wp.i18n;
    const { createElement: el } = wp.element;

    registerBlockType('tailpress/mpma-hero', {
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const {
                headerText,
                backgroundImage,
                backgroundImageId,
                heroHeight,
                heroWidth,
                contentMaxWidth,
                overlayOpacity,
                contentAlignment,
                verticalAlignment
            } = attributes;

            const postTitle = wp.data.select('core/editor')?.getEditedPostAttribute('title') || '';
            const resolvedTitle = headerText && headerText.trim().length
                ? headerText
                : (postTitle || __('Page Title', 'tailpress'));

            const horizontalMap = {
                left: 'flex-start',
                center: 'center',
                right: 'flex-end'
            };

            const verticalMap = {
                top: 'flex-start',
                center: 'center',
                bottom: 'flex-end'
            };

            const blockProps = useBlockProps({
                className: 'mpma-hero-editor'
            });

            const previewStyle = {
                position: 'relative',
                minHeight: heroHeight || '420px',
                width: heroWidth || '100vw',
                maxWidth: '100%',
                margin: '0 auto',
                padding: '1.5rem',
                display: 'flex',
                alignItems: verticalMap[verticalAlignment] || 'center',
                justifyContent: horizontalMap[contentAlignment] || 'center',
                backgroundImage: backgroundImage
                    ? `linear-gradient(rgba(0,0,0,${(overlayOpacity || 0) / 100}), rgba(0,0,0,${(overlayOpacity || 0) / 100})), url(${backgroundImage})`
                    : 'linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.35))',
                backgroundSize: 'cover',
                backgroundPosition: 'center',
                color: '#ffffff',
                    overflow: 'hidden',
                    borderRadius: '0'
            };

            const contentStyle = {
                width: '100%',
                maxWidth: contentMaxWidth || '1200px',
                textAlign: contentAlignment || 'center',
                padding: '2rem'
            };

            const titleStyle = {
                margin: 0,
                color: '#ffffff',
                fontSize: '3rem',
                fontWeight: 700,
                lineHeight: 1.15,
                fontFamily: 'var(--font-montserrat, inherit)'
            };

            const contentAreaStyle = {
                marginTop: '1.5rem'
            };

            return el('div', blockProps,
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Hero Settings', 'tailpress'), initialOpen: true },
                        el(TextControl, {
                            label: __('Hero Min Height', 'tailpress'),
                            value: heroHeight,
                            onChange: (value) => setAttributes({ heroHeight: value }),
                            help: __('Examples: 420px, 60vh, 38rem', 'tailpress')
                        }),
                        el(TextControl, {
                            label: __('Hero Width', 'tailpress'),
                            value: heroWidth,
                            onChange: (value) => setAttributes({ heroWidth: value }),
                            help: __('Use 100vw for full-bleed. Examples: 100vw, 90vw, 1200px', 'tailpress')
                        }),
                        el(TextControl, {
                            label: __('Content Max Width', 'tailpress'),
                            value: contentMaxWidth,
                            onChange: (value) => setAttributes({ contentMaxWidth: value }),
                            help: __('Examples: 1200px, 80rem', 'tailpress')
                        }),
                        el(SelectControl, {
                            label: __('Horizontal Content Alignment', 'tailpress'),
                            value: contentAlignment,
                            options: [
                                { label: __('Left', 'tailpress'), value: 'left' },
                                { label: __('Center', 'tailpress'), value: 'center' },
                                { label: __('Right', 'tailpress'), value: 'right' }
                            ],
                            onChange: (value) => setAttributes({ contentAlignment: value })
                        }),
                        el(SelectControl, {
                            label: __('Vertical Content Alignment', 'tailpress'),
                            value: verticalAlignment,
                            options: [
                                { label: __('Top', 'tailpress'), value: 'top' },
                                { label: __('Center', 'tailpress'), value: 'center' },
                                { label: __('Bottom', 'tailpress'), value: 'bottom' }
                            ],
                            onChange: (value) => setAttributes({ verticalAlignment: value })
                        }),
                        el(RangeControl, {
                            label: __('Overlay Opacity', 'tailpress'),
                            value: overlayOpacity,
                            onChange: (value) => setAttributes({ overlayOpacity: Number(value) || 0 }),
                            min: 0,
                            max: 90,
                            step: 5
                        }),
                        el(MediaUploadCheck, {},
                            el(MediaUpload, {
                                onSelect: (media) => setAttributes({ backgroundImage: media.url, backgroundImageId: media.id }),
                                allowedTypes: ['image'],
                                value: backgroundImageId,
                                render: ({ open }) => el(Button, { onClick: open, variant: 'secondary' },
                                    backgroundImage ? __('Change Cover Image', 'tailpress') : __('Select Cover Image', 'tailpress')
                                )
                            })
                        ),
                        backgroundImage && el(Button, {
                            onClick: () => setAttributes({ backgroundImage: '', backgroundImageId: 0 }),
                            variant: 'link',
                            isDestructive: true
                        }, __('Remove Cover Image', 'tailpress'))
                    )
                ),
                el('div', { style: previewStyle },
                    el('div', { style: contentStyle },
                        el(RichText, {
                            tagName: 'h1',
                            value: headerText || resolvedTitle,
                            onChange: (value) => setAttributes({ headerText: value }),
                            placeholder: resolvedTitle,
                            style: titleStyle,
                            allowedFormats: []
                        }),
                        el('div', { style: contentAreaStyle },
                            el(InnerBlocks, {
                                allowedBlocks: ['core/paragraph', 'core/buttons', 'core/button'],
                                templateLock: false,
                                renderAppender: InnerBlocks.DefaultBlockAppender
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
