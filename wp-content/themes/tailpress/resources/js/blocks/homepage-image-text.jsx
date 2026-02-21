import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck, RichText } from '@wordpress/block-editor';
import { PanelBody, Button, ToggleControl, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

registerBlockType('tailpress/homepage-image-text', {
    title: __('Homepage Image and Text', 'tailpress'),
    description: __('A two-column section with image and text content', 'tailpress'),
    category: 'mpma-custom',
    icon: 'columns',
    attributes: {
        image: {
            type: 'string',
            default: ''
        },
        imageId: {
            type: 'number',
            default: 0
        },
        imagePosition: {
            type: 'string',
            default: 'left'
        },
        heading: {
            type: 'string',
            source: 'html',
            selector: 'h2',
            default: 'The Power of a Combined Alliance'
        },
        content: {
            type: 'string',
            source: 'html',
            selector: '.content',
            default: 'Enter your content here...'
        },
        backgroundColor: {
            type: 'string',
            default: 'white'
        }
    },
    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps();
        
        const { image, imageId, imagePosition, heading, content, backgroundColor } = attributes;

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Settings', 'tailpress')}>
                        <SelectControl
                            label={__('Image Position', 'tailpress')}
                            value={imagePosition}
                            options={[
                                { label: __('Left', 'tailpress'), value: 'left' },
                                { label: __('Right', 'tailpress'), value: 'right' }
                            ]}
                            onChange={(value) => setAttributes({ imagePosition: value })}
                        />
                        <SelectControl
                            label={__('Background Color', 'tailpress')}
                            value={backgroundColor}
                            options={[
                                { label: __('White', 'tailpress'), value: 'white' },
                                { label: __('Gray', 'tailpress'), value: 'gray' },
                                { label: __('Blue', 'tailpress'), value: 'blue' }
                            ]}
                            onChange={(value) => setAttributes({ backgroundColor: value })}
                        />
                        <MediaUploadCheck>
                            <MediaUpload
                                onSelect={(media) => {
                                    setAttributes({
                                        image: media.url,
                                        imageId: media.id
                                    });
                                }}
                                allowedTypes={['image']}
                                value={imageId}
                                render={({ open }) => (
                                    <Button onClick={open} variant="secondary">
                                        {image ? __('Change Image', 'tailpress') : __('Select Image', 'tailpress')}
                                    </Button>
                                )}
                            />
                        </MediaUploadCheck>
                        {image && (
                            <Button
                                onClick={() => setAttributes({ image: '', imageId: 0 })}
                                variant="link"
                                isDestructive
                            >
                                {__('Remove Image', 'tailpress')}
                            </Button>
                        )}
                    </PanelBody>
                </InspectorControls>
                
                <div {...blockProps}>
                    <div style={{
                        display: 'grid',
                        gridTemplateColumns: '1fr 1fr',
                        gap: '48px',
                        alignItems: 'center',
                        padding: '60px 20px',
                        backgroundColor: backgroundColor === 'gray' ? '#f9fafb' : backgroundColor === 'blue' ? '#eff6ff' : 'white',
                        borderRadius: '8px'
                    }}>
                        {imagePosition === 'left' ? (
                            <>
                                <div>
                                    {image ? (
                                        <img src={image} alt="" style={{ width: '100%', borderRadius: '8px' }} />
                                    ) : (
                                        <div style={{
                                            width: '100%',
                                            height: '400px',
                                            backgroundColor: '#e5e7eb',
                                            borderRadius: '8px',
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'center',
                                            fontSize: '14px',
                                            color: '#6b7280'
                                        }}>
                                            {__('Select an image', 'tailpress')}
                                        </div>
                                    )}
                                </div>
                                <div>
                                    <RichText
                                        tagName="h2"
                                        value={heading}
                                        onChange={(value) => setAttributes({ heading: value })}
                                        placeholder={__('Enter heading...', 'tailpress')}
                                        style={{
                                            fontSize: '36px',
                                            fontWeight: 'bold',
                                            marginBottom: '24px',
                                            lineHeight: '1.2'
                                        }}
                                    />
                                    <RichText
                                        tagName="div"
                                        value={content}
                                        onChange={(value) => setAttributes({ content: value })}
                                        placeholder={__('Enter content...', 'tailpress')}
                                        style={{
                                            fontSize: '16px',
                                            lineHeight: '1.6',
                                            color: '#4b5563'
                                        }}
                                    />
                                </div>
                            </>
                        ) : (
                            <>
                                <div>
                                    <RichText
                                        tagName="h2"
                                        value={heading}
                                        onChange={(value) => setAttributes({ heading: value })}
                                        placeholder={__('Enter heading...', 'tailpress')}
                                        style={{
                                            fontSize: '36px',
                                            fontWeight: 'bold',
                                            marginBottom: '24px',
                                            lineHeight: '1.2'
                                        }}
                                    />
                                    <RichText
                                        tagName="div"
                                        value={content}
                                        onChange={(value) => setAttributes({ content: value })}
                                        placeholder={__('Enter content...', 'tailpress')}
                                        style={{
                                            fontSize: '16px',
                                            lineHeight: '1.6',
                                            color: '#4b5563'
                                        }}
                                    />
                                </div>
                                <div>
                                    {image ? (
                                        <img src={image} alt="" style={{ width: '100%', borderRadius: '8px' }} />
                                    ) : (
                                        <div style={{
                                            width: '100%',
                                            height: '400px',
                                            backgroundColor: '#e5e7eb',
                                            borderRadius: '8px',
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'center',
                                            fontSize: '14px',
                                            color: '#6b7280'
                                        }}>
                                            {__('Select an image', 'tailpress')}
                                        </div>
                                    )}
                                </div>
                            </>
                        )}
                    </div>
                </div>
            </>
        );
    },
    save: () => null // Server-side rendered
});
