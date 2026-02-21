/**
 * Add custom width/height controls to Image block
 */
const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl } = wp.components;
const { addFilter } = wp.hooks;
const { __ } = wp.i18n;

// Add custom attributes
function addAttributes(settings, name) {
    if (name !== 'core/image') {
        return settings;
    }

    return {
        ...settings,
        attributes: {
            ...settings.attributes,
            customWidth: {
                type: 'string',
            },
            customHeight: {
                type: 'string',
            },
        },
    };
}

addFilter(
    'blocks.registerBlockType',
    'tailpress/image-dimensions-attributes',
    addAttributes
);

// Add controls to sidebar
const withInspectorControls = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        if (props.name !== 'core/image') {
            return wp.element.createElement(BlockEdit, props);
        }

        const { attributes, setAttributes } = props;
        const { customWidth, customHeight } = attributes;

        return wp.element.createElement(
            Fragment,
            null,
            wp.element.createElement(BlockEdit, props),
            wp.element.createElement(
                InspectorControls,
                null,
                wp.element.createElement(
                    PanelBody,
                    {
                        title: __('Custom Dimensions', 'tailpress'),
                        initialOpen: false
                    },
                    wp.element.createElement(TextControl, {
                        label: __('Width (px)', 'tailpress'),
                        value: customWidth || '',
                        onChange: (value) => setAttributes({ customWidth: value }),
                        type: 'number',
                        min: 0
                    }),
                    wp.element.createElement(TextControl, {
                        label: __('Height (px)', 'tailpress'),
                        value: customHeight || '',
                        onChange: (value) => setAttributes({ customHeight: value }),
                        type: 'number',
                        min: 0
                    })
                )
            )
        );
    };
}, 'withInspectorControls');

addFilter(
    'editor.BlockEdit',
    'tailpress/image-dimensions-controls',
    withInspectorControls
);

// Apply custom dimensions to saved content
function applyCustomDimensions(props, blockType, attributes) {
    if (blockType.name !== 'core/image') {
        return props;
    }

    const { customWidth, customHeight } = attributes;

    if (customWidth || customHeight) {
        const style = props.style || {};
        return {
            ...props,
            style: {
                ...style,
                width: customWidth ? `${customWidth}px` : style.width,
                height: customHeight ? `${customHeight}px` : style.height,
            },
        };
    }

    return props;
}

addFilter(
    'blocks.getSaveContent.extraProps',
    'tailpress/image-dimensions-save',
    applyCustomDimensions
);
