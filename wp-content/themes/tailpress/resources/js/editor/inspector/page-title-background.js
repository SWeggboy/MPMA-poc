/**
 * Add page title background controls to page editor.
 */
(function() {
    if (!window.wp || !wp.plugins || !wp.editPost || !wp.components || !wp.data || !wp.i18n || !wp.element || !wp.blockEditor) {
        return;
    }

    const { registerPlugin } = wp.plugins;
    const { PluginDocumentSettingPanel } = wp.editPost;
    const {
        ToggleControl,
        TextControl,
        TextareaControl,
        RangeControl,
        Button,
        BaseControl
    } = wp.components;
    const { MediaUpload, MediaUploadCheck } = wp.blockEditor;
    const { useSelect, useDispatch } = wp.data;
    const { __ } = wp.i18n;
    const { createElement, Fragment, useEffect, useRef, useState } = wp.element;

    const PANEL_NAME = 'tailpress-page-title-background/page-title-background';

    function ensurePreviewStyles() {
        if (document.getElementById('tailpress-page-title-bg-preview-style')) {
            return;
        }

        var style = document.createElement('style');
        style.id = 'tailpress-page-title-bg-preview-style';
        style.textContent =
            '.tailpress-page-title-bg-preview{' +
            'position:relative;display:flex;align-items:center;justify-content:center;' +
            'min-height:var(--tailpress-page-title-min-height-mobile,12rem);' +
            'padding:2rem 1rem;margin-bottom:1.5rem;' +
            'background-image:var(--tailpress-page-title-image);' +
            'background-size:cover;background-position:center;background-repeat:no-repeat;' +
            'overflow:hidden;' +
            '}' +
            '.tailpress-page-title-bg-preview::before,.tailpress-page-title-bg-preview::after{' +
            'content:\"\";position:absolute;inset:0;pointer-events:none;' +
            '}' +
            '.tailpress-page-title-bg-preview::before{' +
            'background-color:rgba(0,77,132,0.25);opacity:var(--tailpress-page-title-overlay-opacity,0.95);' +
            '}' +
            '.tailpress-page-title-bg-preview::after{' +
            'background-image:linear-gradient(to bottom,rgba(20,58,94,0) 45%,rgba(20,58,94,0.50) 100%),linear-gradient(to right,rgba(0,77,132,0.66) 0%,rgba(0,77,132,0.53) 25%,rgba(0,77,132,0.27) 50%,rgba(0,77,132,0.06) 75%,rgba(0,77,132,0) 100%),linear-gradient(to right,rgba(91,152,113,0) 0%,rgba(91,152,113,0.18) 35%,rgba(91,152,113,0.45) 70%,rgba(91,152,113,0.72) 100%);opacity:var(--tailpress-page-title-overlay-opacity,0.95);' +
            '}' +
            '@media (min-width:782px){.tailpress-page-title-bg-preview{min-height:var(--tailpress-page-title-min-height,26.625rem);}}' +
            '.tailpress-page-title-bg-preview .editor-post-title__input{' +
            'position:relative;z-index:1;color:#ffffff !important;text-align:center !important;' +
            'font-family:Montserrat,sans-serif !important;font-size:3.75rem !important;font-weight:700 !important;line-height:1.1 !important;' +
            '}' +
            '.tailpress-page-title-bg-preview .editor-post-title__input::placeholder{' +
            'color:rgba(255,255,255,0.85) !important;' +
            '}';

        document.head.appendChild(style);
    }

    function getTitlePreviewElement() {
        return (
            document.querySelector('.edit-post-visual-editor__post-title-wrapper') ||
            document.querySelector('.editor-post-title') ||
            document.querySelector('.editor-post-title__block')
        );
    }

    function normalizeOpacity(value) {
        const number = parseFloat(value);
        if (Number.isNaN(number)) {
            return 0.95;
        }
        if (number < 0) {
            return 0;
        }
        if (number > 1) {
            return 1;
        }
        return number;
    }

    function normalizeBoolean(value) {
        return value === true || value === 1 || value === '1' || value === 'true';
    }

    function resolveMediaId(media) {
        if (!media) {
            return 0;
        }

        var raw = media.id || media.ID || 0;
        var parsed = Number(raw);
        return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
    }

    function resolveMediaUrl(media) {
        if (!media) {
            return '';
        }

        if (typeof media.source_url === 'string' && media.source_url) {
            return media.source_url;
        }

        if (typeof media.url === 'string' && media.url) {
            return media.url;
        }

        return '';
    }

    const PageTitleBackgroundPanel = function() {
        const postType = useSelect(function(select) {
            return select('core/editor').getCurrentPostType();
        }, []);

        if (postType !== 'page') {
            return null;
        }

        const currentMeta = useSelect(function(select) {
            return select('core/editor').getEditedPostAttribute('meta') || {};
        }, []);

        const meta = useSelect(function() {
            return {
                enabled: normalizeBoolean(currentMeta.page_title_bg_enabled),
                imageId: Number(currentMeta.page_title_bg_image_id || 0),
                subtitle: currentMeta.page_title_bg_subtitle || '',
                useH1: currentMeta.page_title_bg_use_h1 !== false && currentMeta.page_title_bg_use_h1 !== 0 && currentMeta.page_title_bg_use_h1 !== '0' && currentMeta.page_title_bg_use_h1 !== 'false',
                minHeight: currentMeta.page_title_bg_min_height || '26.625rem',
                minHeightMobile: currentMeta.page_title_bg_min_height_mobile || '12rem',
                overlayOpacity: normalizeOpacity(currentMeta.page_title_bg_overlay_opacity)
            };
        }, [currentMeta]);

        const panelOpened = useSelect(function(select) {
            return select('core/editor').isEditorPanelOpened(PANEL_NAME);
        }, []);

        const [optimisticImage, setOptimisticImage] = useState(null);
        const effectiveImageId = optimisticImage && optimisticImage.id ? optimisticImage.id : meta.imageId;

        const selectedImage = useSelect(function(select) {
            if (!effectiveImageId) {
                return null;
            }
            return select('core').getMedia(effectiveImageId);
        }, [effectiveImageId]);

        const { editPost, toggleEditorPanelOpened } = useDispatch('core/editor');
        const initialOpenHandled = useRef(false);

        function updateMeta(field, value) {
            editPost({
                meta: Object.assign({}, currentMeta, { [field]: value }),
            });
        }

        function onSelectImage(media) {
            var mediaId = resolveMediaId(media);
            if (!mediaId) {
                return;
            }

            var mediaUrl = resolveMediaUrl(media);
            setOptimisticImage({
                id: mediaId,
                source_url: mediaUrl,
                alt_text: media && media.alt_text ? media.alt_text : '',
                media_details: media && media.media_details ? media.media_details : null,
            });

            editPost({
                meta: Object.assign({}, currentMeta, {
                    page_title_bg_image_id: mediaId,
                    page_title_bg_enabled: true
                }),
            });
        }

        function onRemoveImage() {
            setOptimisticImage(null);
            updateMeta('page_title_bg_image_id', 0);
        }

        useEffect(function() {
            if (initialOpenHandled.current) {
                return;
            }

            initialOpenHandled.current = true;
            if (!panelOpened) {
                toggleEditorPanelOpened(PANEL_NAME);
            }
        }, [panelOpened, toggleEditorPanelOpened]);

        useEffect(function() {
            if (optimisticImage && meta.imageId && optimisticImage.id === meta.imageId) {
                setOptimisticImage(null);
            }
        }, [optimisticImage, meta.imageId]);

        useEffect(function() {
            ensurePreviewStyles();

            var previewElement = getTitlePreviewElement();
            if (!previewElement) {
                return undefined;
            }

            var displayImage = selectedImage || optimisticImage;
            var imageUrl = displayImage && displayImage.source_url ? displayImage.source_url : '';

            if (meta.enabled && imageUrl) {
                previewElement.classList.add('tailpress-page-title-bg-preview');
                previewElement.style.setProperty('--tailpress-page-title-image', 'url("' + imageUrl.replace(/"/g, '\\"') + '")');
                previewElement.style.setProperty('--tailpress-page-title-min-height', meta.minHeight || '26.625rem');
                previewElement.style.setProperty('--tailpress-page-title-min-height-mobile', meta.minHeightMobile || '12rem');
                previewElement.style.setProperty('--tailpress-page-title-overlay-opacity', String(normalizeOpacity(meta.overlayOpacity)));
            } else {
                previewElement.classList.remove('tailpress-page-title-bg-preview');
                previewElement.style.removeProperty('--tailpress-page-title-image');
                previewElement.style.removeProperty('--tailpress-page-title-min-height');
                previewElement.style.removeProperty('--tailpress-page-title-min-height-mobile');
                previewElement.style.removeProperty('--tailpress-page-title-overlay-opacity');
            }

            return function() {
                previewElement.classList.remove('tailpress-page-title-bg-preview');
                previewElement.style.removeProperty('--tailpress-page-title-image');
                previewElement.style.removeProperty('--tailpress-page-title-min-height');
                previewElement.style.removeProperty('--tailpress-page-title-min-height-mobile');
                previewElement.style.removeProperty('--tailpress-page-title-overlay-opacity');
            };
        }, [meta.enabled, meta.minHeight, meta.minHeightMobile, meta.overlayOpacity, selectedImage, optimisticImage]);

        return createElement(
            PluginDocumentSettingPanel,
            {
                name: 'page-title-background',
                title: __('Page Title Background', 'tailpress'),
                className: 'page-title-background-panel'
            },
            createElement(ToggleControl, {
                label: __('Enable title background image', 'tailpress'),
                checked: meta.enabled,
                onChange: function(value) {
                    updateMeta('page_title_bg_enabled', !!value);
                },
                help: __('Display a background image behind the page title.', 'tailpress')
            }),
            meta.enabled && createElement(
                Fragment,
                null,
                createElement(
                    BaseControl,
                    {
                        label: __('Background image', 'tailpress')
                    },
                    (selectedImage || optimisticImage) && (selectedImage || optimisticImage).source_url && createElement('img', {
                        src: (selectedImage || optimisticImage).media_details && (selectedImage || optimisticImage).media_details.sizes && (selectedImage || optimisticImage).media_details.sizes.medium
                            ? (selectedImage || optimisticImage).media_details.sizes.medium.source_url
                            : (selectedImage || optimisticImage).source_url,
                        alt: (selectedImage || optimisticImage).alt_text || '',
                        style: {
                            display: 'block',
                            width: '100%',
                            height: 'auto',
                            borderRadius: '4px',
                            marginBottom: '8px'
                        }
                    }),
                    createElement(
                        MediaUploadCheck,
                        null,
                        createElement(MediaUpload, {
                            onSelect: onSelectImage,
                            allowedTypes: ['image'],
                            value: effectiveImageId || 0,
                            render: function(obj) {
                                return createElement(Button, {
                                    variant: 'secondary',
                                    onClick: obj.open,
                                    style: { marginRight: '8px' }
                                }, effectiveImageId ? __('Replace image', 'tailpress') : __('Select image', 'tailpress'));
                            }
                        })
                    ),
                    effectiveImageId > 0 && createElement(Button, {
                        variant: 'tertiary',
                        isDestructive: true,
                        onClick: onRemoveImage
                    }, __('Remove image', 'tailpress'))
                ),
                createElement(TextareaControl, {
                    label: __('Subtitle', 'tailpress'),
                    value: meta.subtitle,
                    onChange: function(value) {
                        updateMeta('page_title_bg_subtitle', value);
                    },
                    help: __('Optional supporting text displayed below the page title.', 'tailpress')
                }),
                createElement(ToggleControl, {
                    label: __('Use H1 tag in hero title', 'tailpress'),
                    checked: meta.useH1,
                    onChange: function(value) {
                        updateMeta('page_title_bg_use_h1', !!value);
                    },
                    help: __('Turn this off if the page body will provide the primary H1 for SEO and accessibility.', 'tailpress')
                }),
                createElement(TextControl, {
                    label: __('Minimum height (Desktop)', 'tailpress'),
                    value: meta.minHeight,
                    onChange: function(value) {
                        updateMeta('page_title_bg_min_height', value || '26.625rem');
                    },
                    help: __('Use CSS units, e.g. 300px, 20rem, 50vh.', 'tailpress')
                }),
                createElement(TextControl, {
                    label: __('Minimum height (Mobile)', 'tailpress'),
                    value: meta.minHeightMobile,
                    onChange: function(value) {
                        updateMeta('page_title_bg_min_height_mobile', value || '12rem');
                    },
                    help: __('Use CSS units, e.g. 160px, 12rem, 35vh.', 'tailpress')
                }),
                createElement(RangeControl, {
                    label: __('Overlay opacity', 'tailpress'),
                    value: meta.overlayOpacity,
                    onChange: function(value) {
                        updateMeta('page_title_bg_overlay_opacity', normalizeOpacity(value));
                    },
                    min: 0,
                    max: 1,
                    step: 0.05,
                    withInputField: true
                })
            )
        );
    };

    registerPlugin('tailpress-page-title-background', {
        render: PageTitleBackgroundPanel,
        icon: 'format-image'
    });
})();
