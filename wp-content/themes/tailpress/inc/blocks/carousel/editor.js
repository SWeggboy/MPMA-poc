/**
 * Testimonial Carousel Block - Editor Script
 */
(function(wp) {
    const { registerBlockType } = wp.blocks;
    const { InspectorControls, useBlockProps } = wp.blockEditor;
    const { Button, PanelBody, ToggleControl, RangeControl } = wp.components;
    const { __ } = wp.i18n;
    const el = wp.element.createElement;

    registerBlockType('tailpress/carousel', {
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { testimonials, autoplay, speed } = attributes;
            const blockProps = useBlockProps();

            const addTestimonial = () => {
                const newTestimonials = [...testimonials, { text: '', name: '', title: '' }];
                setAttributes({ testimonials: newTestimonials });
            };

            const updateTestimonial = (index, field, value) => {
                const newTestimonials = [...testimonials];
                newTestimonials[index][field] = value;
                setAttributes({ testimonials: newTestimonials });
            };

            const removeTestimonial = (index) => {
                const newTestimonials = testimonials.filter((_, i) => i !== index);
                setAttributes({ testimonials: newTestimonials });
            };

            return el('div', blockProps,
                // Inspector Controls (Sidebar)
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Carousel Settings', 'tailpress'), initialOpen: true },
                        el(ToggleControl, {
                            label: __('Autoplay', 'tailpress'),
                            checked: autoplay,
                            onChange: (value) => setAttributes({ autoplay: value })
                        }),
                        el(RangeControl, {
                            label: __('Speed (ms)', 'tailpress'),
                            value: speed,
                            onChange: (value) => setAttributes({ speed: value }),
                            min: 1000,
                            max: 10000,
                            step: 500
                        })
                    )
                ),

                // Block Content
                el('div', { className: 'testimonial-carousel-editor p-6 border border-gray-300 rounded' },
                    el('h3', { className: 'text-lg font-semibold mb-4' }, __('Testimonials', 'tailpress')),
                    
                    testimonials.map((testimonial, index) =>
                        el('div', { key: index, className: 'testimonial-item mb-6 p-4 border border-gray-200 rounded' },
                            el('div', { className: 'flex justify-between items-center mb-3' },
                                el('strong', {}, __('Testimonial', 'tailpress') + ' ' + (index + 1)),
                                el(Button, {
                                    isDestructive: true,
                                    isSmall: true,
                                    onClick: () => removeTestimonial(index)
                                }, __('Remove', 'tailpress'))
                            ),
                            
                            el('div', { className: 'mb-3' },
                                el('label', { className: 'block text-sm font-medium mb-1' }, __('Quote Text', 'tailpress')),
                                el('textarea', {
                                    value: testimonial.text,
                                    onChange: (e) => updateTestimonial(index, 'text', e.target.value),
                                    className: 'w-full p-2 border border-gray-300 rounded',
                                    rows: 4,
                                    placeholder: __('Enter testimonial quote...', 'tailpress')
                                })
                            ),
                            
                            el('div', { className: 'mb-3' },
                                el('label', { className: 'block text-sm font-medium mb-1' }, __('Name', 'tailpress')),
                                el('input', {
                                    type: 'text',
                                    value: testimonial.name,
                                    onChange: (e) => updateTestimonial(index, 'name', e.target.value),
                                    className: 'w-full p-2 border border-gray-300 rounded',
                                    placeholder: __('John Doe', 'tailpress')
                                })
                            ),
                            
                            el('div', {},
                                el('label', { className: 'block text-sm font-medium mb-1' }, __('Title/Position', 'tailpress')),
                                el('input', {
                                    type: 'text',
                                    value: testimonial.title,
                                    onChange: (e) => updateTestimonial(index, 'title', e.target.value),
                                    className: 'w-full p-2 border border-gray-300 rounded',
                                    placeholder: __('CEO, Company Name', 'tailpress')
                                })
                            )
                        )
                    ),
                    
                    el(Button, {
                        variant: 'primary',
                        onClick: addTestimonial
                    }, __('Add Testimonial', 'tailpress'))
                )
            );
        },

        save: function() {
            // Server-side rendering
            return null;
        }
    });
})(window.wp);
