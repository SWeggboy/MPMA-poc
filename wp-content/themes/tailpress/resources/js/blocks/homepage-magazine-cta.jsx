import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck, RichText } from '@wordpress/block-editor';
import { PanelBody, Button, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

registerBlockType('tailpress/homepage-magazine-cta', {
    title: __('Homepage Magazine CTA', 'tailpress'),
    description: __('Promote industry publications with images and links', 'tailpress'),
    category: 'mpma-custom',
    icon: 'book',
    attributes: {
        heading: {
            type: 'string',
            source: 'html',
            selector: 'h2',
            default: 'Industry Publications That Inform and Inspire'
        },
        description: {
            type: 'string',
            source: 'html',
            selector: '.intro-text',
            default: 'MPMA supports two of the most respected publications in mechanical power transmission:'
        },
        magazine1Image: {
            type: 'string',
            default: ''
        },
        magazine1ImageId: {
            type: 'number',
            default: 0
        },
        magazine1Title: {
            type: 'string',
            default: 'GEAR TECHNOLOGY'
        },
        magazine1Description: {
            type: 'string',
            default: 'Delivering technical expertise, best practices, and thought leadership in gear manufacturing.'
        },
        magazine1ButtonText: {
            type: 'string',
            default: 'VISIT GEAR TECHNOLOGY'
        },
        magazine1Url: {
            type: 'string',
            default: 'https://www.geartechnology.com/'
        },
        magazine2Image: {
            type: 'string',
            default: ''
        },
        magazine2ImageId: {
            type: 'number',
            default: 0
        },
        magazine2Title: {
            type: 'string',
            default: 'POWER TRANSMISSION ENGINEERING'
        },
        magazine2Description: {
            type: 'string',
            default: 'Covering the entire motion and power ecosystem, from design to performance.'
        },
        magazine2ButtonText: {
            type: 'string',
            default: 'VISIT PTE'
        },
        magazine2Url: {
            type: 'string',
            default: 'https://www.powertransmission.com/'
        }
    },
    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps();
        
        const { 
            heading, description,
            magazine1Image, magazine1ImageId, magazine1Title, magazine1Description, magazine1ButtonText, magazine1Url,
            magazine2Image, magazine2ImageId, magazine2Title, magazine2Description, magazine2ButtonText, magazine2Url
        } = attributes;

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Magazine 1 Settings', 'tailpress')}>
                        <MediaUploadCheck>
                            <MediaUpload
                                onSelect={(media) => {
                                    setAttributes({
                                        magazine1Image: media.url,
                                        magazine1ImageId: media.id
                                    });
                                }}
                                allowedTypes={['image']}
                                value={magazine1ImageId}
                                render={({ open }) => (
                                    <Button onClick={open} variant="secondary">
                                        {magazine1Image ? __('Change Image', 'tailpress') : __('Select Image', 'tailpress')}
                                    </Button>
                                )}
                            />
                        </MediaUploadCheck>
                        <TextControl
                            label={__('Title', 'tailpress')}
                            value={magazine1Title}
                            onChange={(value) => setAttributes({ magazine1Title: value })}
                        />
                        <TextControl
                            label={__('Description', 'tailpress')}
                            value={magazine1Description}
                            onChange={(value) => setAttributes({ magazine1Description: value })}
                        />
                        <TextControl
                            label={__('Button Text', 'tailpress')}
                            value={magazine1ButtonText}
                            onChange={(value) => setAttributes({ magazine1ButtonText: value })}
                        />
                        <TextControl
                            label={__('URL', 'tailpress')}
                            value={magazine1Url}
                            onChange={(value) => setAttributes({ magazine1Url: value })}
                        />
                    </PanelBody>
                    
                    <PanelBody title={__('Magazine 2 Settings', 'tailpress')}>
                        <MediaUploadCheck>
                            <MediaUpload
                                onSelect={(media) => {
                                    setAttributes({
                                        magazine2Image: media.url,
                                        magazine2ImageId: media.id
                                    });
                                }}
                                allowedTypes={['image']}
                                value={magazine2ImageId}
                                render={({ open }) => (
                                    <Button onClick={open} variant="secondary">
                                        {magazine2Image ? __('Change Image', 'tailpress') : __('Select Image', 'tailpress')}
                                    </Button>
                                )}
                            />
                        </MediaUploadCheck>
                        <TextControl
                            label={__('Title', 'tailpress')}
                            value={magazine2Title}
                            onChange={(value) => setAttributes({ magazine2Title: value })}
                        />
                        <TextControl
                            label={__('Description', 'tailpress')}
                            value={magazine2Description}
                            onChange={(value) => setAttributes({ magazine2Description: value })}
                        />
                        <TextControl
                            label={__('Button Text', 'tailpress')}
                            value={magazine2ButtonText}
                            onChange={(value) => setAttributes({ magazine2ButtonText: value })}
                        />
                        <TextControl
                            label={__('URL', 'tailpress')}
                            value={magazine2Url}
                            onChange={(value) => setAttributes({ magazine2Url: value })}
                        />
                    </PanelBody>
                </InspectorControls>
                
                <div {...blockProps}>
                    <div style={{ padding: '60px 20px', textAlign: 'center' }}>
                        <RichText
                            tagName="h2"
                            value={heading}
                            onChange={(value) => setAttributes({ heading: value })}
                            placeholder={__('Enter heading...', 'tailpress')}
                            style={{
                                fontSize: '36px',
                                fontWeight: 'bold',
                                marginBottom: '16px',
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
                                marginBottom: '48px',
                                color: '#4b5563'
                            }}
                        />
                        
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '48px', maxWidth: '1000px', margin: '0 auto' }}>
                            {/* Magazine 1 */}
                            <div style={{ textAlign: 'left' }}>
                                {magazine1Image ? (
                                    <img src={magazine1Image} alt={magazine1Title} style={{ width: '100%', height: '300px', objectFit: 'cover', borderRadius: '8px', marginBottom: '24px' }} />
                                ) : (
                                    <div style={{
                                        width: '100%',
                                        height: '300px',
                                        backgroundColor: '#e5e7eb',
                                        borderRadius: '8px',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        marginBottom: '24px',
                                        fontSize: '14px',
                                        color: '#6b7280'
                                    }}>
                                        {__('Magazine 1 Image', 'tailpress')}
                                    </div>
                                )}
                                <h3 style={{ fontSize: '24px', fontWeight: 'bold', marginBottom: '12px' }}>{magazine1Title}</h3>
                                <p style={{ fontSize: '16px', lineHeight: '1.6', color: '#4b5563', marginBottom: '20px' }}>{magazine1Description}</p>
                                <a 
                                    href={magazine1Url} 
                                    style={{
                                        display: 'inline-block',
                                        padding: '12px 24px',
                                        backgroundColor: '#0066cc',
                                        color: 'white',
                                        borderRadius: '4px',
                                        fontWeight: '600',
                                        textDecoration: 'none'
                                    }}
                                >
                                    {magazine1ButtonText}
                                </a>
                            </div>
                            
                            {/* Magazine 2 */}
                            <div style={{ textAlign: 'left' }}>
                                {magazine2Image ? (
                                    <img src={magazine2Image} alt={magazine2Title} style={{ width: '100%', height: '300px', objectFit: 'cover', borderRadius: '8px', marginBottom: '24px' }} />
                                ) : (
                                    <div style={{
                                        width: '100%',
                                        height: '300px',
                                        backgroundColor: '#e5e7eb',
                                        borderRadius: '8px',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        marginBottom: '24px',
                                        fontSize: '14px',
                                        color: '#6b7280'
                                    }}>
                                        {__('Magazine 2 Image', 'tailpress')}
                                    </div>
                                )}
                                <h3 style={{ fontSize: '24px', fontWeight: 'bold', marginBottom: '12px' }}>{magazine2Title}</h3>
                                <p style={{ fontSize: '16px', lineHeight: '1.6', color: '#4b5563', marginBottom: '20px' }}>{magazine2Description}</p>
                                <a 
                                    href={magazine2Url} 
                                    style={{
                                        display: 'inline-block',
                                        padding: '12px 24px',
                                        backgroundColor: '#0066cc',
                                        color: 'white',
                                        borderRadius: '4px',
                                        fontWeight: '600',
                                        textDecoration: 'none'
                                    }}
                                >
                                    {magazine2ButtonText}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </>
        );
    },
    save: () => null // Server-side rendered
});
