/**
 * Homepage Magazine CTA Block - Editor Script
 */
(function(wp) {
    if (!wp || !wp.blocks || !wp.blockEditor || !wp.components || !wp.i18n || !wp.element) {
        return;
    }

    const { registerBlockType } = wp.blocks;
    const { InspectorControls, useBlockProps, MediaUpload, MediaUploadCheck, RichText } = wp.blockEditor;
    const { Button, PanelBody, TextControl } = wp.components;
    const { __ } = wp.i18n;
    const el = wp.element.createElement;

    registerBlockType('tailpress/homepage-magazine-cta', {
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { heading, description, magazine1Image, magazine1ImageId, magazine1Title, magazine1Description, magazine1ButtonText, magazine1Url, magazine2Image, magazine2ImageId, magazine2Title, magazine2Description, magazine2ButtonText, magazine2Url } = attributes;
            const blockProps = useBlockProps();

            const magazineCard = (imageUrl, imageId, title, desc, btnText, url, prefix) => {
                return el('div', { style: { backgroundColor: '#F7F7F7', padding: '0px 15px 25px 15px', borderStyle: 'solid', borderWidth: '2px 0px 1px 0px', borderColor: '#020048', borderRadius: '10px 10px 0px 0px', overflow: 'hidden' }},
                    imageUrl 
                        ? el('img', { src: imageUrl, alt: title, style: { width: 'calc(100% + 30px)', height: '300px', objectFit: 'cover', marginLeft: '-15px', marginTop: '0', marginBottom: '24px' }})
                        : el('div', {
                            style: { width: 'calc(100% + 30px)', height: '300px', backgroundColor: '#e5e7eb', display: 'flex', alignItems: 'center', justifyContent: 'center', marginLeft: '-15px', marginTop: '0', marginBottom: '24px', fontSize: '14px', color: '#6b7280' }
                        }, prefix + ' Image'),
                    el('h3', { style: { fontSize: '18px', fontWeight: '500', marginBottom: '12px', color: '#68737c', fontFamily: 'Roboto Slab, serif', textTransform: 'uppercase' }}, title),
                    el('p', { style: { fontSize: '16px', lineHeight: '1.6', color: '#4b5563', marginBottom: '20px' }}, desc),
                    el('a', {
                        href: url,
                        style: { display: 'inline-block', padding: '0.75rem 1.5rem', backgroundColor: 'var(--wp--preset--color--primary)', color: '#ffffff', border: '1px solid white', borderRadius: '0.5rem', fontFamily: 'Roboto, sans-serif', fontWeight: '600', textDecoration: 'none' }
                    }, btnText)
                );
            };

            return el('div', blockProps,
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Magazine 1 Settings', 'tailpress'), initialOpen: false },
                        el(MediaUploadCheck, {},
                            el(MediaUpload, {
                                onSelect: (media) => setAttributes({ magazine1Image: media.url, magazine1ImageId: media.id }),
                                allowedTypes: ['image'],
                                value: magazine1ImageId,
                                render: ({ open }) => el(Button, { onClick: open, variant: 'secondary' },
                                    magazine1Image ? __('Change Image', 'tailpress') : __('Select Image', 'tailpress')
                                )
                            })
                        ),
                        el(TextControl, { label: __('Title', 'tailpress'), value: magazine1Title, onChange: (value) => setAttributes({ magazine1Title: value }) }),
                        el(TextControl, { label: __('Description', 'tailpress'), value: magazine1Description, onChange: (value) => setAttributes({ magazine1Description: value }) }),
                        el(TextControl, { label: __('Button Text', 'tailpress'), value: magazine1ButtonText, onChange: (value) => setAttributes({ magazine1ButtonText: value }) }),
                        el(TextControl, { label: __('URL', 'tailpress'), value: magazine1Url, onChange: (value) => setAttributes({ magazine1Url: value }) })
                    ),
                    el(PanelBody, { title: __('Magazine 2 Settings', 'tailpress'), initialOpen: false },
                        el(MediaUploadCheck, {},
                            el(MediaUpload, {
                                onSelect: (media) => setAttributes({ magazine2Image: media.url, magazine2ImageId: media.id }),
                                allowedTypes: ['image'],
                                value: magazine2ImageId,
                                render: ({ open }) => el(Button, { onClick: open, variant: 'secondary' },
                                    magazine2Image ? __('Change Image', 'tailpress') : __('Select Image', 'tailpress')
                                )
                            })
                        ),
                        el(TextControl, { label: __('Title', 'tailpress'), value: magazine2Title, onChange: (value) => setAttributes({ magazine2Title: value }) }),
                        el(TextControl, { label: __('Description', 'tailpress'), value: magazine2Description, onChange: (value) => setAttributes({ magazine2Description: value }) }),
                        el(TextControl, { label: __('Button Text', 'tailpress'), value: magazine2ButtonText, onChange: (value) => setAttributes({ magazine2ButtonText: value }) }),
                        el(TextControl, { label: __('URL', 'tailpress'), value: magazine2Url, onChange: (value) => setAttributes({ magazine2Url: value }) })
                    )
                ),
                el('div', { style: { padding: '60px 20px', textAlign: 'center' }},
                    el(RichText, {
                        tagName: 'h2',
                        value: heading,
                        onChange: (value) => setAttributes({ heading: value }),
                        placeholder: __('Enter heading...', 'tailpress'),
                        style: { fontSize: '36px', fontWeight: 'bold', marginBottom: '16px', lineHeight: '1.2' }
                    }),
                    el(RichText, {
                        tagName: 'div',
                        value: description,
                        onChange: (value) => setAttributes({ description: value }),
                        placeholder: __('Enter description...', 'tailpress'),
                        style: { fontSize: '18px', lineHeight: '1.6', marginBottom: '48px', color: '#4b5563' }
                    }),
                    el('div', { style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '48px', maxWidth: '1000px', margin: '0 auto' }},
                        magazineCard(magazine1Image, magazine1ImageId, magazine1Title, magazine1Description, magazine1ButtonText, magazine1Url, 'Magazine 1'),
                        magazineCard(magazine2Image, magazine2ImageId, magazine2Title, magazine2Description, magazine2ButtonText, magazine2Url, 'Magazine 2')
                    )
                )
            );
        }
    });
})(window.wp);
