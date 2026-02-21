import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

registerBlockType('tailpress/upcoming-events', {
    title: __('Upcoming Events', 'tailpress'),
    description: __('Display upcoming events from The Events Calendar', 'tailpress'),
    category: 'mpma-custom',
    icon: 'calendar-alt',
    attributes: {
        title: {
            type: 'string',
            default: 'Upcoming Events'
        }
    },
    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps();
        const { title } = attributes;

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Settings', 'tailpress')}>
                        <TextControl
                            label={__('Title', 'tailpress')}
                            value={title}
                            onChange={(value) => setAttributes({ title: value })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps}>
                    <div style={{
                        padding: '20px',
                        border: '1px dashed #ccc',
                        borderRadius: '4px',
                        backgroundColor: '#f9f9f9'
                    }}>
                        <h3 style={{ marginTop: 0 }}>{title}</h3>
                        <p style={{ color: '#666', fontSize: '14px' }}>
                            📅 Upcoming events will be displayed here
                        </p>
                        <p style={{ color: '#999', fontSize: '12px', fontStyle: 'italic' }}>
                            Preview available on the frontend
                        </p>
                    </div>
                </div>
            </>
        );
    },
    save: () => null // Server-side rendered
});
