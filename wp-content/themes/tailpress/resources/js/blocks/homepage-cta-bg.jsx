import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck, RichText } from '@wordpress/block-editor';
import { PanelBody, Button, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

registerBlockType('tailpress/homepage-cta-bg', {
    title: __('Homepage CTA with Background', 'tailpress'),
    description: __('A call-to-action section with background image', 'tailpress'),
    category: 'mpma-custom',
    icon: 'cover-image',
    attributes: {
        backgroundImage: {
            type: 'string',
            default: ''
        },
        backgroundImageId: {
            type: 'number',
            default: 0
        },
        heading: {
            type: 'string',
            source: 'html',
            selector: 'h2',
            default: 'Uniting The Gear And Bearing Industries Under One Dynamic Alliance'
        },
        buttonText: {
            type: 'string',
            default: 'VIEW MERGER ANNOUNCEMENT'
        },
        buttonUrl: {
            type: 'string',
            default: ''
        },
        description: {
            type: 'string',
            source: 'html',
            selector: '.cta-description',
            default: 'The Motion Power Manufacturers Alliance (MPMA) brings together the gear and bearing communities...'
        }
    },
    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps({
            className: 'relative'
        });
        
        const { backgroundImage, backgroundImageId, heading, buttonText, buttonUrl, description } = attributes;

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Settings', 'tailpress')}>
                        <MediaUploadCheck>
                            <MediaUpload
                                onSelect={(media) => {
                                    setAttributes({
                                        backgroundImage: media.url,
                                        backgroundImageId: media.id
                                    });
                                }}
                                allowedTypes={['image']}
                                value={backgroundImageId}
                                render={({ open }) => (
                                    <Button onClick={open} variant="secondary">
                                        {backgroundImage ? __('Change Background Image', 'tailpress') : __('Select Background Image', 'tailpress')}
                                    </Button>
                                )}
                            />
                        </MediaUploadCheck>
                        {backgroundImage && (
                            <Button
                                onClick={() => setAttributes({ backgroundImage: '', backgroundImageId: 0 })}
                                variant="link"
                                isDestructive
                            >
                                {__('Remove Image', 'tailpress')}
                            </Button>
                        )}
                        <TextControl
                            label={__('Button URL', 'tailpress')}
                            value={buttonUrl}
                            onChange={(value) => setAttributes({ buttonUrl: value })}
                        />
                        <TextControl
                            label={__('Button Text', 'tailpress')}
                            value={buttonText}
                            onChange={(value) => setAttributes({ buttonText: value })}
                        />
                    </PanelBody>
                </InspectorControls>
                
                <div {...blockProps}>
                    <div style={{
                        position: 'relative',
                        minHeight: '400px',
                        padding: '60px 20px',
                        backgroundImage: backgroundImage ? `url(${backgroundImage})` : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                        backgroundSize: 'cover',
                        backgroundPosition: 'center',
                        borderRadius: '8px',
                        overflow: 'hidden'
                    }}>
                        <div style={{
                            position: 'absolute',
                            top: 0,
                            left: 0,
                            right: 0,
                            bottom: 0,
                            backgroundColor: 'rgba(0, 0, 0, 0.5)'
                        }}></div>
                        <div style={{
                            position: 'relative',
                            zIndex: 1,
                            maxWidth: '800px',
                            margin: '0 auto',
                            color: 'white',
                            textAlign: 'center'
                        }}>
                            <RichText
                                tagName="h2"
                                value={heading}
                                onChange={(value) => setAttributes({ heading: value })}
                                placeholder={__('Enter heading...', 'tailpress')}
                                style={{
                                    fontSize: '42px',
                                    fontWeight: 'bold',
                                    marginBottom: '24px',
                                    lineHeight: '1.2'
                                }}
                            />
                            <RichText
                                tagName="div"
                                value={description}
                                onChange={(value) => setAttributes({ description: value })}
                                placeholder={__('Enter description...', 'tailpress')}
                                style={{
                                    fontSize: '18px',
                                    lineHeight: '1.6',
                                    marginBottom: '32px',
                                    opacity: 0.95
                                }}
                            />
                            <div style={{
                                display: 'inline-block',
                                padding: '14px 32px',
                                backgroundColor: '#0066cc',
                                color: 'white',
                                borderRadius: '4px',
                                fontWeight: '600',
                                cursor: 'pointer'
                            }}>
                                {buttonText}
                            </div>
                        </div>
                    </div>
                </div>
            </>
        );
    },
    save: () => null // Server-side rendered
});
