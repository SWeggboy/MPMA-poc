/**
 * Homepage CTA with Background Block - Editor Script (data-id 566b387)
 */
(function(wp) {
    const { registerBlockType } = wp.blocks;
    const { InspectorControls, useBlockProps, MediaUpload, MediaUploadCheck, RichText } = wp.blockEditor;
    const { Button, PanelBody, TextControl, TextareaControl } = wp.components;
    const { __ } = wp.i18n;
    const el = wp.element.createElement;

    registerBlockType('tailpress/homepage-cta-bg', {
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { 
                backgroundImage, backgroundImageId, backgroundColor, backgroundPosition, backgroundSize, 
                backgroundRepeat, backgroundAttachment, overlayOpacity, gradient,
                heading, description, buttonText, buttonLink, bottomText,
                iconBox1Title, iconBox1Desc, iconBox2Title, iconBox2Desc, 
                iconBox3Title, iconBox3Desc, iconBox4Title, iconBox4Desc 
            } = attributes;
            const blockProps = useBlockProps();

            return el('div', blockProps,
                // Inspector Controls (Sidebar)
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Background Settings', 'tailpress'), initialOpen: true },
                        el(MediaUploadCheck, {},
                            el(MediaUpload, {
                                onSelect: (media) => setAttributes({ backgroundImage: media.url, backgroundImageId: media.id }),
                                allowedTypes: ['image'],
                                value: backgroundImageId,
                                render: ({ open }) => el(Button, { onClick: open, variant: 'secondary' },
                                    backgroundImage ? __('Change Background Image', 'tailpress') : __('Select Background Image', 'tailpress')
                                )
                            })
                        ),
                        backgroundImage && el(Button, {
                            onClick: () => setAttributes({ backgroundImage: '', backgroundImageId: 0 }),
                            variant: 'link',
                            isDestructive: true
                        }, __('Remove Image', 'tailpress')),
                        el(TextControl, {
                            label: __('Background Color', 'tailpress'),
                            value: backgroundColor,
                            onChange: (value) => setAttributes({ backgroundColor: value }),
                            help: __('CSS color (e.g., #003366, rgba(0,0,0,0.5))', 'tailpress')
                        }),
                        el(TextControl, {
                            label: __('Background Position', 'tailpress'),
                            value: backgroundPosition,
                            onChange: (value) => setAttributes({ backgroundPosition: value }),
                            help: __('e.g., center center, top left, 50% 50%', 'tailpress')
                        }),
                        el(TextControl, {
                            label: __('Background Size', 'tailpress'),
                            value: backgroundSize,
                            onChange: (value) => setAttributes({ backgroundSize: value }),
                            help: __('e.g., cover, contain, 100% auto', 'tailpress')
                        }),
                        el(TextControl, {
                            label: __('Background Repeat', 'tailpress'),
                            value: backgroundRepeat,
                            onChange: (value) => setAttributes({ backgroundRepeat: value }),
                            help: __('e.g., no-repeat, repeat, repeat-x', 'tailpress')
                        }),
                        el(TextControl, {
                            label: __('Background Attachment', 'tailpress'),
                            value: backgroundAttachment,
                            onChange: (value) => setAttributes({ backgroundAttachment: value }),
                            help: __('e.g., scroll, fixed, local', 'tailpress')
                        }),
                        backgroundImage && el(TextControl, {
                            label: __('Overlay Opacity (%)', 'tailpress'),
                            type: 'number',
                            value: overlayOpacity,
                            onChange: (value) => setAttributes({ overlayOpacity: parseInt(value) }),
                            min: 0,
                            max: 100
                        }),
                        el(TextareaControl, {
                            label: __('Gradient CSS', 'tailpress'),
                            value: gradient,
                            onChange: (value) => setAttributes({ gradient: value }),
                            help: __('e.g., linear-gradient(90deg, #2E5E47 68%, #020048 100%)', 'tailpress'),
                            rows: 3
                        })
                    ),
                    el(PanelBody, { title: __('Content Settings', 'tailpress'), initialOpen: true },
                        el(TextControl, {
                            label: __('Button Text', 'tailpress'),
                            value: buttonText,
                            onChange: (value) => setAttributes({ buttonText: value })
                        }),
                        el(TextControl, {
                            label: __('Button URL', 'tailpress'),
                            value: buttonLink,
                            onChange: (value) => setAttributes({ buttonLink: value })
                        }),
                        el(TextareaControl, {
                            label: __('Bottom Text', 'tailpress'),
                            value: bottomText,
                            onChange: (value) => setAttributes({ bottomText: value }),
                            help: __('Text to display below the CTA boxes', 'tailpress'),
                            rows: 3
                        })
                    ),
                    el(PanelBody, { title: __('Icon Box 1', 'tailpress'), initialOpen: false },
                        el(TextControl, {
                            label: __('Title', 'tailpress'),
                            value: iconBox1Title,
                            onChange: (value) => setAttributes({ iconBox1Title: value })
                        }),
                        el(TextareaControl, {
                            label: __('Description', 'tailpress'),
                            value: iconBox1Desc,
                            onChange: (value) => setAttributes({ iconBox1Desc: value })
                        })
                    ),
                    el(PanelBody, { title: __('Icon Box 2', 'tailpress'), initialOpen: false },
                        el(TextControl, {
                            label: __('Title', 'tailpress'),
                            value: iconBox2Title,
                            onChange: (value) => setAttributes({ iconBox2Title: value })
                        }),
                        el(TextareaControl, {
                            label: __('Description', 'tailpress'),
                            value: iconBox2Desc,
                            onChange: (value) => setAttributes({ iconBox2Desc: value })
                        })
                    ),
                    el(PanelBody, { title: __('Icon Box 3', 'tailpress'), initialOpen: false },
                        el(TextControl, {
                            label: __('Title', 'tailpress'),
                            value: iconBox3Title,
                            onChange: (value) => setAttributes({ iconBox3Title: value })
                        }),
                        el(TextareaControl, {
                            label: __('Description', 'tailpress'),
                            value: iconBox3Desc,
                            onChange: (value) => setAttributes({ iconBox3Desc: value })
                        })
                    ),
                    el(PanelBody, { title: __('Icon Box 4', 'tailpress'), initialOpen: false },
                        el(TextControl, {
                            label: __('Title', 'tailpress'),
                            value: iconBox4Title,
                            onChange: (value) => setAttributes({ iconBox4Title: value })
                        }),
                        el(TextareaControl, {
                            label: __('Description', 'tailpress'),
                            value: iconBox4Desc,
                            onChange: (value) => setAttributes({ iconBox4Desc: value })
                        })
                    )
                ),
                
                // Block Content (Main Canvas)
                el('div', {
                    className: 'homepage-cta-editor-preview',
                    style: {
                        padding: '30px 20px',
                        backgroundColor: '#ffffff',
                        minHeight: '400px'
                    }
                },
                    el('div', { style: { marginBottom: '30px' }}, 
                        el('h2', {
                            style: { fontSize: '32px', fontWeight: 'bold', marginBottom: '12px', color: '#1a202c', lineHeight: '1.2' }
                        }, heading || 'The Power of a Combined Alliance'),
                        el('p', {
                            style: { fontSize: '16px', marginBottom: '20px', color: '#4a5568', lineHeight: '1.6' }
                        }, description || 'Description...'),
                        el('div', {
                            style: { 
                                display: 'inline-block', 
                                padding: '0.75rem 1.5rem', 
                                backgroundColor: 'var(--wp--preset--color--primary)', 
                                color: '#ffffff', 
                                border: '1px solid white',
                                borderRadius: '0.5rem',
                                fontFamily: 'Roboto, sans-serif',
                                fontWeight: '600', 
                                fontSize: '14px',
                                textTransform: 'uppercase',
                                letterSpacing: '0.5px',
                                textDecoration: 'none'
                            }
                        }, buttonText || 'VIEW OUR FAQ\'S')
                    ),
                    el('div', { style: { borderTop: '1px solid #e2e8f0', paddingTop: '20px' }},
                        el('div', { style: { fontSize: '12px', fontWeight: '600', color: '#718096', marginBottom: '12px', textTransform: 'uppercase', letterSpacing: '0.5px' }}, 'Icon Boxes'),
                        el('div', { style: { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: '20px' }},
                            el('div', { style: { padding: '15px', backgroundColor: 'white', borderRadius: '10px', boxShadow: '0px 12px 10px -10px rgba(0, 0, 0, 0.15)' }},
                                el('div', { style: { fontWeight: '500', marginBottom: '8px', color: '#68737c', fontSize: '14px', fontFamily: 'Roboto Slab, serif' }}, iconBox1Title || 'Icon Box 1'),
                                el('div', { style: { fontSize: '12px', color: '#4a5568', lineHeight: '1.5' }}, iconBox1Desc ? (iconBox1Desc.substring(0, 60) + '...') : 'Description...')
                            ),
                            el('div', { style: { padding: '15px', backgroundColor: 'white', borderRadius: '10px', boxShadow: '0px 12px 10px -10px rgba(0, 0, 0, 0.15)' }},
                                el('div', { style: { fontWeight: '500', marginBottom: '8px', color: '#68737c', fontSize: '14px', fontFamily: 'Roboto Slab, serif' }}, iconBox2Title || 'Icon Box 2'),
                                el('div', { style: { fontSize: '12px', color: '#4a5568', lineHeight: '1.5' }}, iconBox2Desc ? (iconBox2Desc.substring(0, 60) + '...') : 'Description...')
                            ),
                            el('div', { style: { padding: '15px', backgroundColor: 'white', borderRadius: '10px', boxShadow: '0px 12px 10px -10px rgba(0, 0, 0, 0.15)' }},
                                el('div', { style: { fontWeight: '500', marginBottom: '8px', color: '#68737c', fontSize: '14px', fontFamily: 'Roboto Slab, serif' }}, iconBox3Title || 'Icon Box 3'),
                                el('div', { style: { fontSize: '12px', color: '#4a5568', lineHeight: '1.5' }}, iconBox3Desc ? (iconBox3Desc.substring(0, 60) + '...') : 'Description...')
                            ),
                            el('div', { style: { padding: '15px', backgroundColor: 'white', borderRadius: '10px', boxShadow: '0px 12px 10px -10px rgba(0, 0, 0, 0.15)' }},
                                el('div', { style: { fontWeight: '500', marginBottom: '8px', color: '#68737c', fontSize: '14px', fontFamily: 'Roboto Slab, serif' }}, iconBox4Title || 'Icon Box 4'),
                                el('div', { style: { fontSize: '12px', color: '#4a5568', lineHeight: '1.5' }}, iconBox4Desc ? (iconBox4Desc.substring(0, 60) + '...') : 'Description...')
                            )
                        )
                    )
                )
            );
        },

        save: function() {
            // Server-side rendering
            return null;
        }
    });
})(window.wp);
