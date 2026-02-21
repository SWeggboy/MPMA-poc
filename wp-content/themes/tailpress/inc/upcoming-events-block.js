(function(wp) {
    var el = wp.element.createElement;
    var __ = wp.i18n.__;
    var registerBlockType = wp.blocks.registerBlockType;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var useBlockProps = wp.blockEditor.useBlockProps;
    var PanelBody = wp.components.PanelBody;
    var TextControl = wp.components.TextControl;
    
    registerBlockType('tailpress/upcoming-events', {
        title: 'Upcoming Events',
        description: 'Display upcoming events from The Events Calendar with featured events at the top',
        category: 'widgets',
        icon: 'calendar-alt',
        keywords: ['events', 'calendar', 'upcoming', 'tribe'],
        attributes: {
            title: {
                type: 'string',
                default: 'Upcoming Events'
            }
        },
        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var blockProps = useBlockProps();
            
            return el('div', blockProps,
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Settings' },
                        el(TextControl, {
                            label: 'Widget Title',
                            value: attributes.title,
                            onChange: function(value) {
                                setAttributes({ title: value });
                            }
                        })
                    )
                ),
                el('div', { 
                    className: 'upcoming-events-block-preview',
                    style: {
                        padding: '20px',
                        border: '2px dashed #ddd',
                        borderRadius: '4px',
                        backgroundColor: '#f9f9f9',
                        textAlign: 'center'
                    }
                },
                    el('div', { style: { fontSize: '40px', marginBottom: '10px' } }, '📅'),
                    el('h3', { style: { marginTop: 0, marginBottom: '10px' } }, attributes.title),
                    el('p', { style: { color: '#666', fontSize: '14px', margin: 0 } }, 
                        'Upcoming events will be displayed here (3 events, featured first)'
                    ),
                    el('p', { style: { color: '#999', fontSize: '12px', fontStyle: 'italic', marginTop: '10px' } }, 
                        'Preview available on the frontend'
                    )
                )
            );
        },
        save: function() {
            return null;
        }
    });
})(window.wp);
