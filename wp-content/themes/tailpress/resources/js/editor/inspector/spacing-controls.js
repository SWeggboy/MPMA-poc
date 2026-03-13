/**
 * Enable per-side spacing controls for heading and paragraph blocks.
 */
(function () {
    if (!window.wp || !wp.hooks || !wp.compose || !wp.element || !wp.blockEditor || !wp.components || !wp.i18n) {
        return;
    }

    var addFilter = wp.hooks.addFilter;
    var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
    var Fragment = wp.element.Fragment;
    var createElement = wp.element.createElement;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody = wp.components.PanelBody;
    var TextControl = wp.components.TextControl;
    var __ = wp.i18n.__;

    function injectPanelLayoutStyles() {
        if (!document || document.getElementById('tailpress-spacing-full-width-style')) {
            return;
        }

        var style = document.createElement('style');
        style.id = 'tailpress-spacing-full-width-style';
        style.textContent =
            '.tailpress-spacing-per-side-wrap{' +
            'display:grid !important;grid-template-columns:minmax(0,1fr) !important;gap:8px !important;width:100% !important;' +
            '}' +
            '.dimensions-block-support-panel .tailpress-spacing-per-side-panel{' +
            'grid-column:1 / -1 !important;' +
            '}';

        document.head.appendChild(style);
    }
    injectPanelLayoutStyles();

    function isTargetBlock(name) {
        return name === 'core/heading' || name === 'core/paragraph';
    }

    function enablePerSideSpacing(settings, name) {
        if (!isTargetBlock(name)) {
            return settings;
        }

        var supports = settings.supports || {};
        var spacing = supports.spacing || {};
        var typography = supports.typography || {};
        var typographyDefaultControls = typography.__experimentalDefaultControls || {};

        return Object.assign({}, settings, {
            supports: Object.assign({}, supports, {
                spacing: Object.assign({}, spacing, {
                    margin: ['top', 'right', 'bottom', 'left'],
                    padding: ['top', 'right', 'bottom', 'left']
                }),
                typography: Object.assign({}, typography, {
                    lineHeight: true,
                    __experimentalFontFamily: true,
                    __experimentalDefaultControls: Object.assign({}, typographyDefaultControls, {
                        fontFamily: true
                    })
                })
            })
        });
    }

    addFilter(
        'blocks.registerBlockType',
        'tailpress/per-side-spacing-controls',
        enablePerSideSpacing
    );

    function updateSpacingValues(attributes, setAttributes, type, side, value) {
        var currentStyle = attributes.style || {};
        var currentSpacing = currentStyle.spacing || {};
        var currentTypeValues = currentSpacing[type] || {};
        var nextTypeValues = Object.assign({}, currentTypeValues);

        if (value === '') {
            delete nextTypeValues[side];
        } else {
            nextTypeValues[side] = value;
        }

        setAttributes({
            style: Object.assign({}, currentStyle, {
                spacing: Object.assign({}, currentSpacing, (function () {
                    var update = {};
                    update[type] = nextTypeValues;
                    return update;
                })())
            })
        });
    }

    function getSpacingValue(attributes, type, side) {
        if (!attributes || !attributes.style || !attributes.style.spacing || !attributes.style.spacing[type]) {
            return '';
        }

        return attributes.style.spacing[type][side] || '';
    }

    function createSpacingControl(props, type, side, label, helpText) {
        return createElement(TextControl, {
            label: label,
            value: getSpacingValue(props.attributes, type, side),
            onChange: function (value) {
                updateSpacingValues(props.attributes, props.setAttributes, type, side, value);
            },
            help: helpText || undefined
        });
    }

    var withPerSideSpacingControls = createHigherOrderComponent(function (BlockEdit) {
        return function (props) {
            if (!isTargetBlock(props.name)) {
                return createElement(BlockEdit, props);
            }

            return createElement(
                Fragment,
                null,
                createElement(BlockEdit, props),
                createElement(
                    InspectorControls,
                    { group: 'dimensions' },
                    createElement(
                        PanelBody,
                        {
                            title: __('Spacing (Per Side)', 'tailpress'),
                            initialOpen: true,
                            className: 'tailpress-spacing-per-side-panel'
                        },
                        createElement(
                            'div',
                            { className: 'tailpress-spacing-per-side-wrap' },
                            createSpacingControl(props, 'margin', 'top', __('Margin Top', 'tailpress'), __('Use CSS units, e.g. 1rem, 16px, 2%', 'tailpress')),
                            createSpacingControl(props, 'margin', 'right', __('Margin Right', 'tailpress')),
                            createSpacingControl(props, 'margin', 'bottom', __('Margin Bottom', 'tailpress')),
                            createSpacingControl(props, 'margin', 'left', __('Margin Left', 'tailpress')),
                            createSpacingControl(props, 'padding', 'top', __('Padding Top', 'tailpress')),
                            createSpacingControl(props, 'padding', 'right', __('Padding Right', 'tailpress')),
                            createSpacingControl(props, 'padding', 'bottom', __('Padding Bottom', 'tailpress')),
                            createSpacingControl(props, 'padding', 'left', __('Padding Left', 'tailpress'))
                        )
                    )
                )
            );
        };
    }, 'withPerSideSpacingControls');

    addFilter(
        'editor.BlockEdit',
        'tailpress/per-side-spacing-controls-panel',
        withPerSideSpacingControls
    );
})();
