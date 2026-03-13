/**
 * Homepage Image and Text Block - Editor Script
 */
(function(wp) {
    if (!wp || !wp.blocks || !wp.blockEditor || !wp.components || !wp.i18n || !wp.element) {
        return;
    }

    const { registerBlockType } = wp.blocks;
    const { InspectorControls, useBlockProps, MediaUpload, MediaUploadCheck, RichText } = wp.blockEditor;
    const { Button, PanelBody, SelectControl, ToggleControl, TextControl } = wp.components;
    const { __ } = wp.i18n;
    const el = wp.element.createElement;

    registerBlockType('tailpress/homepage-image-text', {
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { image, imageId, imagePosition, heading, content, backgroundColor, enableBorderShadow, imageWidth, textAlignment, buttonText, buttonLink } = attributes;
            const blockProps = useBlockProps();

            const bgColor = backgroundColor === 'gray' ? '#f9fafb' : backgroundColor === 'blue' ? '#eff6ff' : 'white';

            const imageEl = image 
                ? el('img', { src: image, alt: '', style: { width: '100%', borderRadius: '8px' }})
                : el('div', {
                    style: { width: '100%', height: '400px', backgroundColor: '#e5e7eb', borderRadius: '8px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '14px', color: '#6b7280' }
                }, __('Select an image', 'tailpress'));

            const textContent = el('div', { style: { textAlign: textAlignment || 'left' }},
                el(RichText, {
                    tagName: 'h2',
                    value: heading,
                    onChange: (value) => setAttributes({ heading: value }),
                    placeholder: __('Enter heading...', 'tailpress'),
                    style: { fontSize: '36px', fontWeight: 'bold', marginBottom: '24px', lineHeight: '1.2' }
                }),
                el(RichText, {
                    tagName: 'div',
                    value: content,
                    onChange: (value) => setAttributes({ content: value }),
                    placeholder: __('Enter content...', 'tailpress'),
                    style: { fontSize: '16px', lineHeight: '1.6', color: '#4b5563', marginBottom: '24px' }
                }),
                buttonText && el('div', {
                    style: { display: 'inline-block', padding: '0.75rem 1.5rem', backgroundColor: 'var(--wp--preset--color--primary)', color: '#ffffff', border: '1px solid white', borderRadius: '0.5rem', fontWeight: '600', textDecoration: 'none' }
                }, buttonText)
            );

            return el('div', blockProps,
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Settings', 'tailpress'), initialOpen: true },
                        el(SelectControl, {
                            label: __('Image Position', 'tailpress'),
                            value: imagePosition,
                            options: [
                                { label: __('Left', 'tailpress'), value: 'left' },
                                { label: __('Right', 'tailpress'), value: 'right' }
                            ],
                            onChange: (value) => setAttributes({ imagePosition: value })
                        }),
                        el(SelectControl, {
                            label: __('Background Color', 'tailpress'),
                            value: backgroundColor,
                            options: [
                                { label: __('White', 'tailpress'), value: 'white' },
                                { label: __('Gray', 'tailpress'), value: 'gray' },
                                { label: __('Blue', 'tailpress'), value: 'blue' }
                            ],
                            onChange: (value) => setAttributes({ backgroundColor: value })
                        }),
                        el(SelectControl, {
                            label: __('Text Alignment', 'tailpress'),
                            value: textAlignment,
                            options: [
                                { label: __('Left', 'tailpress'), value: 'left' },
                                { label: __('Center', 'tailpress'), value: 'center' },
                                { label: __('Right', 'tailpress'), value: 'right' }
                            ],
                            onChange: (value) => setAttributes({ textAlignment: value })
                        }),
                        el(ToggleControl, {
                            label: __('Enable Border & Shadow', 'tailpress'),
                            checked: enableBorderShadow,
                            onChange: (value) => setAttributes({ enableBorderShadow: value })
                        }),
                        el(TextControl, {
                            label: __('Image Width', 'tailpress'),
                            value: imageWidth,
                            onChange: (value) => setAttributes({ imageWidth: value }),
                            help: __('e.g., 100%, 500px, 75%', 'tailpress')
                        }),
                        el(TextControl, {
                            label: __('Button Text', 'tailpress'),
                            value: buttonText,
                            onChange: (value) => setAttributes({ buttonText: value }),
                            placeholder: __('Leave empty to hide button', 'tailpress')
                        }),
                        el(TextControl, {
                            label: __('Button Link', 'tailpress'),
                            value: buttonLink,
                            onChange: (value) => setAttributes({ buttonLink: value }),
                            placeholder: __('https://example.com', 'tailpress')
                        }),
                        el(MediaUploadCheck, {},
                            el(MediaUpload, {
                                onSelect: (media) => setAttributes({ image: media.url, imageId: media.id }),
                                allowedTypes: ['image'],
                                value: imageId,
                                render: ({ open }) => el(Button, { onClick: open, variant: 'secondary' },
                                    image ? __('Change Image', 'tailpress') : __('Select Image', 'tailpress')
                                )
                            })
                        ),
                        image && el(Button, {
                            onClick: () => setAttributes({ image: '', imageId: 0 }),
                            variant: 'link',
                            isDestructive: true
                        }, __('Remove Image', 'tailpress'))
                    )
                ),
                el('div', {
                    style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '48px', alignItems: 'center', padding: '60px 20px', backgroundColor: bgColor, borderRadius: '8px' }
                },
                    imagePosition === 'left' 
                        ? [el('div', { key: 'img' }, imageEl), el('div', { key: 'text' }, textContent)]
                        : [el('div', { key: 'text' }, textContent), el('div', { key: 'img' }, imageEl)]
                )
            );
        }
    });
})(window.wp);
