(function(wp) {
    if (!wp || !wp.blocks || !wp.blockEditor || !wp.components || !wp.i18n || !wp.element) {
        return;
    }

    const { registerBlockType } = wp.blocks;
    const { InspectorControls, MediaUpload, MediaUploadCheck, RichText, useBlockProps } = wp.blockEditor;
    const { PanelBody, TextControl, ToggleControl, SelectControl, RangeControl, Button, ColorPalette } = wp.components;
    const { __ } = wp.i18n;
    const { select } = wp.data;
    const { createElement: el, Fragment, useState } = wp.element;
    const BoxControl = wp.components.BoxControl || wp.components.__experimentalBoxControl;

    const COLUMN_OPTIONS = Array.from({ length: 12 }, function(_, index) {
        const value = index + 1;
        return { label: String(value), value: value };
    });

    const SIDE_OPTIONS = [
        { label: __('Front', 'tailpress'), value: 'front' },
        { label: __('Back', 'tailpress'), value: 'back' }
    ];

    const VERTICAL_OPTIONS = [
        { label: __('Top', 'tailpress'), value: 'top' },
        { label: __('Center', 'tailpress'), value: 'center' },
        { label: __('Bottom', 'tailpress'), value: 'bottom' }
    ];

    const HORIZONTAL_OPTIONS = [
        { label: __('Left', 'tailpress'), value: 'left' },
        { label: __('Center', 'tailpress'), value: 'center' },
        { label: __('Right', 'tailpress'), value: 'right' }
    ];

    const CARD_ALIGNMENT_OPTIONS = [
        { label: __('Left', 'tailpress'), value: 'left' },
        { label: __('Center', 'tailpress'), value: 'center' },
        { label: __('Right', 'tailpress'), value: 'right' }
    ];

    const BUTTON_TREATMENT_OPTIONS = [
        { label: __('Primary', 'tailpress'), value: 'primary' },
        { label: __('Secondary', 'tailpress'), value: 'secondary' }
    ];

    const buildSideData = function(attributes, side) {
        const key = side === 'front' ? 'front' : 'back';

        return {
            title: attributes[key + 'Title'],
            body: attributes[key + 'Body'],
            buttonText: attributes[key + 'ButtonText'],
            buttonUrl: attributes[key + 'ButtonUrl'],
            backgroundImage: attributes[key + 'BackgroundImage'],
            backgroundImageId: attributes[key + 'BackgroundImageId'],
            backgroundColor: attributes[key + 'BackgroundColor'],
            titleColor: attributes[key + 'TitleColor'],
            bodyColor: attributes[key + 'BodyColor'],
            photoOnly: attributes[key + 'PhotoOnly'],
            titleFontSize: attributes[key + 'TitleFontSize'],
            bodyFontSize: attributes[key + 'BodyFontSize'],
            verticalAlignment: attributes[key + 'VerticalAlignment'],
            horizontalAlignment: attributes[key + 'HorizontalAlignment'],
            buttonTreatment: attributes[key + 'ButtonTreatment']
        };
    };

    const updateSideData = function(setAttributes, side, nextData) {
        const key = side === 'front' ? 'front' : 'back';
        const updates = {};

        Object.keys(nextData).forEach(function(prop) {
            const normalized = prop.charAt(0).toUpperCase() + prop.slice(1);
            updates[key + normalized] = nextData[prop];
        });

        setAttributes(updates);
    };

    const getPreviewWidth = function(columns) {
        const safeColumns = Math.min(12, Math.max(1, Number(columns) || 4));
        return Math.min((safeColumns * 88) + ((safeColumns - 1) * 24), 900) + 'px';
    };

    registerBlockType('tailpress/mpma-internal-card', {
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const [activeSide, setActiveSide] = useState('front');
            const sideData = buildSideData(attributes, activeSide);
            const widthColumns = Math.min(12, Math.max(1, Number(attributes.widthColumns) || 4));
            const animationSpeed = Math.max(80, Math.min(2000, Number(attributes.animationSpeed) || 400));
            const isFlippable = !!attributes.flippable;
            const cardAlignment = attributes.cardAlignment || 'center';
            const verticalAlignment = sideData.verticalAlignment || attributes.verticalAlignment || 'top';
            const horizontalAlignment = sideData.horizontalAlignment || attributes.horizontalAlignment || 'center';
            const stretchContent = !!attributes.stretchContent;
            const titleFontSize = sideData.titleFontSize || attributes.titleFontSize || '1.5rem';
            const bodyFontSize = sideData.bodyFontSize || attributes.bodyFontSize || '1rem';
            const contentPaddingTop = attributes.contentPaddingTop || '1.75rem';
            const contentPaddingRight = attributes.contentPaddingRight || '1.75rem';
            const contentPaddingBottom = attributes.contentPaddingBottom || '1.75rem';
            const contentPaddingLeft = attributes.contentPaddingLeft || '1.75rem';
            const buttonTreatment = (sideData.buttonTreatment || attributes.buttonTreatment) === 'secondary' ? 'secondary' : 'primary';
            const globalPhotoOnly = !!attributes.photoOnly;
            const sidePhotoOnly = sideData.photoOnly === undefined ? globalPhotoOnly : !!sideData.photoOnly;
            const themeColors = (((select('core/block-editor') || {}).getSettings || function() { return {}; })().colors) || [];
            const hasBodyContent = !!(sideData.body || '').replace(/<[^>]+>/g, '').trim();
            const hasButtonText = !!(sideData.buttonText || '').replace(/<[^>]+>/g, '').trim();
            const stretchHasFlexibleContent = stretchContent && (hasBodyContent || hasButtonText);
            const alignmentJustify = ({
                top: 'flex-start',
                center: 'center',
                bottom: 'flex-end'
            }[verticalAlignment] || 'flex-start');

            const blockProps = useBlockProps({
                className: 'mpma-internal-card-editor'
            });

            const previewShellStyle = {
                width: '100%',
                maxWidth: getPreviewWidth(widthColumns),
                marginLeft: cardAlignment === 'right' ? 'auto' : '0',
                marginRight: cardAlignment === 'left' ? 'auto' : '0'
            };

            const surfaceStyle = {
                position: 'relative',
                minHeight: attributes.cardHeight || '455px',
                borderRadius: attributes.borderRadius || '1.5rem',
                overflow: 'hidden',
                boxShadow: attributes.dropShadow ? '0 -5px 43px 0 color-mix(in srgb, var(--color-accent-dark) 20%, transparent)' : 'none',
                background: '#ffffff'
            };

            const sideStyle = {
                minHeight: attributes.cardHeight || '455px',
                paddingTop: contentPaddingTop,
                paddingRight: contentPaddingRight,
                paddingBottom: contentPaddingBottom,
                paddingLeft: contentPaddingLeft,
                display: 'flex',
                flexDirection: 'column',
                justifyContent: stretchHasFlexibleContent ? 'flex-start' : alignmentJustify,
                alignItems: ({
                    left: 'flex-start',
                    center: 'center',
                    right: 'flex-end'
                }[horizontalAlignment] || 'center'),
                textAlign: horizontalAlignment,
                backgroundColor: sideData.backgroundColor || '#ffffff',
                backgroundImage: sideData.backgroundImage ? 'url("' + sideData.backgroundImage + '")' : undefined,
                backgroundPosition: sideData.backgroundImage ? 'center' : undefined,
                backgroundSize: sideData.backgroundImage ? 'cover' : undefined,
                backgroundRepeat: sideData.backgroundImage ? 'no-repeat' : undefined
            };

            const contentStyle = {
                display: 'flex',
                flexDirection: 'column',
                minHeight: stretchHasFlexibleContent ? '100%' : 'auto',
                height: stretchHasFlexibleContent ? '100%' : 'auto',
                gap: '1rem',
                width: '100%'
            };

            const titleStyle = {
                margin: 0,
                color: sideData.titleColor || '#000000',
                fontFamily: 'var(--font-montserrat, inherit)',
                fontSize: titleFontSize,
                fontWeight: 600,
                lineHeight: 1.15
            };

            const bodyStyle = {
                margin: 0,
                color: sideData.bodyColor || '#000000',
                fontFamily: 'var(--font-roboto, inherit)',
                fontSize: bodyFontSize,
                lineHeight: 1.6,
                flex: stretchHasFlexibleContent && hasBodyContent ? '1 1 auto' : '0 1 auto',
                width: '100%'
            };

            const buttonWrapperStyle = {
                marginTop: stretchHasFlexibleContent && hasButtonText ? 'auto' : '0',
                width: '100%'
            };

            const buttonStyle = {
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                width: '100%',
                padding: '0.65rem 2.325rem',
                borderRadius: '0.5rem',
                background: buttonTreatment === 'secondary'
                    ? 'linear-gradient(to right, #1f97dd 0%, #0e8ed4 25%, #0681ca 55%, #0c6cb6 100%)'
                    : 'linear-gradient(90deg, #639c7e 0%, #408b78 50%, #138078 100%)',
                color: '#ffffff',
                fontFamily: 'var(--font-montserrat, inherit)',
                fontSize: '1rem',
                fontWeight: 600,
                textTransform: 'uppercase',
                textAlign: 'center',
                border: '0'
            };

            const updateCurrentSide = function(nextData) {
                updateSideData(setAttributes, activeSide, nextData);
            };

            const sidePanel = function(side) {
                const panelData = buildSideData(attributes, side);
                const sideLabel = side === 'front' ? __('Front Side', 'tailpress') : __('Back Side', 'tailpress');

                return el(PanelBody, {
                    title: sideLabel,
                    initialOpen: side === 'front'
                },
                    el(ToggleControl, {
                        label: __('Photo only', 'tailpress'),
                        checked: panelData.photoOnly === undefined ? globalPhotoOnly : !!panelData.photoOnly,
                        onChange: function(value) {
                            updateSideData(setAttributes, side, { photoOnly: !!value });
                        }
                    }),
                    el('div', { style: { marginBottom: '1rem' } },
                        el('p', { style: { margin: '0 0 0.5rem', fontSize: '11px', fontWeight: 500, textTransform: 'uppercase' } }, __('Background Color', 'tailpress')),
                        el(ColorPalette, {
                            value: panelData.backgroundColor || '#ffffff',
                            colors: themeColors,
                            onChange: function(value) {
                                updateSideData(setAttributes, side, { backgroundColor: value || '#ffffff' });
                            },
                            clearable: false
                        })
                    ),
                    !(panelData.photoOnly === undefined ? globalPhotoOnly : !!panelData.photoOnly) && el('div', { style: { marginBottom: '1rem' } },
                        el('p', { style: { margin: '0 0 0.5rem', fontSize: '11px', fontWeight: 500, textTransform: 'uppercase' } }, __('Heading Color', 'tailpress')),
                        el(ColorPalette, {
                            value: panelData.titleColor || '#000000',
                            colors: themeColors,
                            onChange: function(value) {
                                updateSideData(setAttributes, side, { titleColor: value || '#000000' });
                            },
                            clearable: false
                        })
                    ),
                    !(panelData.photoOnly === undefined ? globalPhotoOnly : !!panelData.photoOnly) && el('div', { style: { marginBottom: '1rem' } },
                        el('p', { style: { margin: '0 0 0.5rem', fontSize: '11px', fontWeight: 500, textTransform: 'uppercase' } }, __('Body Copy Color', 'tailpress')),
                        el(ColorPalette, {
                            value: panelData.bodyColor || '#000000',
                            colors: themeColors,
                            onChange: function(value) {
                                updateSideData(setAttributes, side, { bodyColor: value || '#000000' });
                            },
                            clearable: false
                        })
                    ),
                    el(MediaUploadCheck, null,
                        el(MediaUpload, {
                            onSelect: function(media) {
                                updateSideData(setAttributes, side, {
                                    backgroundImage: media && media.url ? media.url : '',
                                    backgroundImageId: media && media.id ? media.id : 0
                                });
                            },
                            allowedTypes: ['image'],
                            value: panelData.backgroundImageId || 0,
                            render: function(obj) {
                                return el(Button, {
                                    onClick: obj.open,
                                    variant: 'secondary'
                                }, panelData.backgroundImage
                                    ? __('Replace background image', 'tailpress')
                                    : __('Select background image', 'tailpress'));
                            }
                        })
                    ),
                    panelData.backgroundImage && el(Button, {
                        onClick: function() {
                            updateSideData(setAttributes, side, {
                                backgroundImage: '',
                                backgroundImageId: 0
                            });
                        },
                        variant: 'link',
                        isDestructive: true
                    }, __('Remove background image', 'tailpress')),
                    !(panelData.photoOnly === undefined ? globalPhotoOnly : !!panelData.photoOnly) && el(TextControl, {
                        label: __('Heading Size', 'tailpress'),
                        value: panelData.titleFontSize || attributes.titleFontSize || '1.5rem',
                        onChange: function(value) {
                            updateSideData(setAttributes, side, { titleFontSize: value || '' });
                        }
                    }),
                    !(panelData.photoOnly === undefined ? globalPhotoOnly : !!panelData.photoOnly) && el(TextControl, {
                        label: __('Body Copy Size', 'tailpress'),
                        value: panelData.bodyFontSize || attributes.bodyFontSize || '1rem',
                        onChange: function(value) {
                            updateSideData(setAttributes, side, { bodyFontSize: value || '' });
                        }
                    }),
                    !(panelData.photoOnly === undefined ? globalPhotoOnly : !!panelData.photoOnly) && el(SelectControl, {
                        label: __('Vertical Alignment', 'tailpress'),
                        value: panelData.verticalAlignment || attributes.verticalAlignment || 'top',
                        options: VERTICAL_OPTIONS,
                        onChange: function(value) {
                            updateSideData(setAttributes, side, { verticalAlignment: value || '' });
                        }
                    }),
                    !(panelData.photoOnly === undefined ? globalPhotoOnly : !!panelData.photoOnly) && el(SelectControl, {
                        label: __('Horizontal Alignment', 'tailpress'),
                        value: panelData.horizontalAlignment || attributes.horizontalAlignment || 'center',
                        options: HORIZONTAL_OPTIONS,
                        onChange: function(value) {
                            updateSideData(setAttributes, side, { horizontalAlignment: value || '' });
                        }
                    }),
                    !(panelData.photoOnly === undefined ? globalPhotoOnly : !!panelData.photoOnly) && el(SelectControl, {
                        label: __('Button Treatment', 'tailpress'),
                        value: panelData.buttonTreatment || attributes.buttonTreatment || 'primary',
                        options: BUTTON_TREATMENT_OPTIONS,
                        onChange: function(value) {
                            updateSideData(setAttributes, side, { buttonTreatment: value || '' });
                        }
                    }),
                    !(panelData.photoOnly === undefined ? globalPhotoOnly : !!panelData.photoOnly) && el(TextControl, {
                        label: __('Button Link', 'tailpress'),
                        value: panelData.buttonUrl || '',
                        onChange: function(value) {
                            updateSideData(setAttributes, side, { buttonUrl: value || '' });
                        }
                    })
                );
            };

            return el(Fragment, null,
                el(InspectorControls, null,
                    el(PanelBody, {
                        title: __('Card Settings', 'tailpress'),
                        initialOpen: true
                    },
                        el(SelectControl, {
                            label: __('Width Columns', 'tailpress'),
                            value: widthColumns,
                            options: COLUMN_OPTIONS,
                            onChange: function(value) {
                                setAttributes({ widthColumns: Number(value) });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Card Alignment', 'tailpress'),
                            value: cardAlignment,
                            options: CARD_ALIGNMENT_OPTIONS,
                            onChange: function(value) {
                                setAttributes({ cardAlignment: value });
                            }
                        }),
                        el(TextControl, {
                            label: __('Card Height', 'tailpress'),
                            value: attributes.cardHeight || '455px',
                            onChange: function(value) {
                                setAttributes({ cardHeight: value || '455px' });
                            }
                        }),
                        el(TextControl, {
                            label: __('Border Radius', 'tailpress'),
                            value: attributes.borderRadius || '1.5rem',
                            onChange: function(value) {
                                setAttributes({ borderRadius: value || '1.5rem' });
                            }
                        }),
                        el(ToggleControl, {
                            label: __('Show drop shadow', 'tailpress'),
                            checked: !!attributes.dropShadow,
                            onChange: function(value) {
                                setAttributes({ dropShadow: !!value });
                            }
                        }),
                        el(ToggleControl, {
                            label: __('Enable flip / back side', 'tailpress'),
                            checked: isFlippable,
                            onChange: function(value) {
                                setActiveSide('front');
                                setAttributes({ flippable: !!value });
                            }
                        }),
                        isFlippable && el(RangeControl, {
                            label: __('Fade Speed (ms)', 'tailpress'),
                            value: animationSpeed,
                            onChange: function(value) {
                                setAttributes({ animationSpeed: Number(value) || 400 });
                            },
                            min: 80,
                            max: 2000,
                            step: 20
                        }),
                        el(ToggleControl, {
                            label: __('Stretch content to card height', 'tailpress'),
                            checked: stretchContent,
                            onChange: function(value) {
                                setAttributes({ stretchContent: !!value });
                            }
                        })
                    ),
                    sidePanel('front'),
                    isFlippable && sidePanel('back')
                ),
                BoxControl && el(InspectorControls, { group: 'styles' },
                    el(PanelBody, {
                        title: __('Padding', 'tailpress'),
                        initialOpen: true
                    },
                        el(BoxControl, {
                            label: __('Content Padding', 'tailpress'),
                            values: {
                                top: contentPaddingTop,
                                right: contentPaddingRight,
                                bottom: contentPaddingBottom,
                                left: contentPaddingLeft
                            },
                            onChange: function(nextValues) {
                                const values = nextValues || {};
                                setAttributes({
                                    contentPaddingTop: values.top || '1.75rem',
                                    contentPaddingRight: values.right || '1.75rem',
                                    contentPaddingBottom: values.bottom || '1.75rem',
                                    contentPaddingLeft: values.left || '1.75rem'
                                });
                            },
                            resetValues: {
                                top: '1.75rem',
                                right: '1.75rem',
                                bottom: '1.75rem',
                                left: '1.75rem'
                            }
                        })
                    )
                ),
                el('div', blockProps,
                    isFlippable && el('div', {
                            style: {
                                display: 'flex',
                                justifyContent: 'center',
                                gap: '0.5rem',
                                marginBottom: '1rem'
                            }
                        },
                        SIDE_OPTIONS.map(function(option) {
                            const isActiveSide = activeSide === option.value;
                            return el(Button, {
                                key: option.value,
                                variant: isActiveSide ? 'primary' : 'secondary',
                                onClick: function() {
                                    setActiveSide(option.value);
                                },
                                style: isActiveSide ? undefined : {
                                    background: '#ffffff',
                                    borderColor: 'var(--color-accent-light)',
                                    color: 'var(--color-accent-light)'
                                }
                            }, option.label);
                        })
                    ),
                    el('div', { style: previewShellStyle },
                        el('div', {
                                className: 'mpma-internal-card-editor__surface',
                                style: surfaceStyle
                            },
                            el('div', { style: sideStyle },
                                !sidePhotoOnly && el('div', { style: contentStyle },
                                    el(RichText, {
                                        tagName: 'h3',
                                        value: sideData.title,
                                        onChange: function(value) {
                                            updateCurrentSide({ title: value });
                                        },
                                        placeholder: __('Card heading', 'tailpress'),
                                        allowedFormats: [],
                                        style: titleStyle
                                    }),
                                    el(RichText, {
                                        tagName: 'div',
                                        value: sideData.body,
                                        onChange: function(value) {
                                            updateCurrentSide({ body: value });
                                        },
                                        placeholder: __('Add card copy', 'tailpress'),
                                        style: bodyStyle
                                    }),
                                    el('div', { style: buttonWrapperStyle },
                                        el(RichText, {
                                            tagName: 'div',
                                            value: sideData.buttonText,
                                            onChange: function(value) {
                                                updateCurrentSide({ buttonText: value });
                                            },
                                            placeholder: __('Button text', 'tailpress'),
                                            allowedFormats: [],
                                            style: buttonStyle
                                        })
                                    )
                                )
                            )
                        )
                    )
                )
            );
        },

        save: function() {
            return null;
        }
    });
})(window.wp);
