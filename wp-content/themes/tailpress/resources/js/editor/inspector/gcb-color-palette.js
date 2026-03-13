/**
 * Add theme color palette swatches to Genesis Custom Blocks color controls.
 */
(function () {
    if (!window.wp || !wp.hooks || !wp.element || !wp.components) {
        return;
    }

    if (window.tailpressGcbColorPaletteLoaded) {
        return;
    }
    window.tailpressGcbColorPaletteLoaded = true;

    var addFilter = wp.hooks.addFilter;
    var createElement = wp.element.createElement;
    var useState = wp.element.useState;

    var BaseControl = wp.components.BaseControl;
    var ColorIndicator = wp.components.ColorIndicator;
    var ColorPalette = wp.components.ColorPalette;
    var ColorPicker = wp.components.ColorPicker;
    var Popover = wp.components.Popover;
    var TextControl = wp.components.TextControl;
    var __ = wp.i18n && wp.i18n.__ ? wp.i18n.__ : function (text) { return text; };

    if (!BaseControl || !ColorIndicator || !ColorPicker || !Popover || !TextControl) {
        return;
    }

    function getNextColorFromPicker(color) {
        if (!color || !color.rgb) {
            return color && color.hex ? color.hex : '';
        }

        if (typeof color.rgb.a === 'number' && color.rgb.a < 1) {
            return 'rgba(' + color.rgb.r + ', ' + color.rgb.g + ', ' + color.rgb.b + ', ' + color.rgb.a + ')';
        }

        return color.hex || '';
    }

    var cachedThemeColors = null;
    var enhancedColorControlRef = null;

    function getThemeColors() {
        if (cachedThemeColors !== null) {
            return cachedThemeColors;
        }

        if (!wp.data || !wp.data.select) {
            cachedThemeColors = [];
            return cachedThemeColors;
        }

        var blockEditorSelect = wp.data.select('core/block-editor');
        if (!blockEditorSelect || !blockEditorSelect.getSettings) {
            cachedThemeColors = [];
            return cachedThemeColors;
        }

        var settings = blockEditorSelect.getSettings() || {};
        cachedThemeColors = Array.isArray(settings.colors) ? settings.colors : [];
        return cachedThemeColors;
    }

    function ensureColorPopoverStyles() {
        if (!document || document.getElementById('tailpress-gcb-color-palette-style')) {
            return;
        }

        var style = document.createElement('style');
        style.id = 'tailpress-gcb-color-palette-style';
        style.textContent =
            '.tailpress-gcb-color-popover .components-popover__content{' +
            'padding:10px !important;min-width:240px !important;' +
            '}' +
            '.tailpress-gcb-theme-color-palette{' +
            'margin-top:10px !important;' +
            '}';

        document.head.appendChild(style);
    }

    ensureColorPopoverStyles();

    function createEnhancedColorControl() {
        return function EnhancedColorControl(props) {
            var field = props.field || {};
            var getValue = props.getValue;
            var onChange = props.onChange;
            var value = typeof getValue === 'function' ? getValue(props) : '';
            var colorValue = typeof value !== 'undefined' ? value : field.default || '';
            var inputId = 'gcb-color-' + (field && field.name ? field.name : '');
            var pickerId = __('Color control picker', 'genesis-custom-blocks');
            var themeColors = getThemeColors();
            var hasThemePalette = ColorPalette && Array.isArray(themeColors) && themeColors.length > 0;
            var _useState = useState(false);
            var isOpen = _useState[0];
            var setIsOpen = _useState[1];

            return createElement(
                BaseControl,
                {
                    label: field.label,
                    id: inputId,
                    className: 'genesis-custom-blocks-color-control',
                    help: field.help
                },
                createElement(TextControl, {
                    id: inputId,
                    value: colorValue,
                    onChange: onChange
                }),
                createElement(
                    BaseControl,
                    {
                        className: 'genesis-custom-blocks-color-popover',
                        id: pickerId
                    },
                    createElement(ColorIndicator, {
                        colorValue: colorValue,
                        onMouseDown: function (event) {
                            event.preventDefault();
                        },
                        onClick: function () {
                            setIsOpen(true);
                        }
                    })
                ),
                isOpen
                    ? createElement(
                          Popover,
                          {
                              className: 'tailpress-gcb-color-popover',
                              onClick: function (event) {
                                  event.stopPropagation();
                              },
                              onClose: function () {
                                  setIsOpen(false);
                              }
                          },
                          createElement(ColorPicker, {
                              color: colorValue,
                              onChangeComplete: function (nextColor) {
                                  onChange(getNextColorFromPicker(nextColor));
                              }
                          }),
                          hasThemePalette
                              ? createElement(
                                    'div',
                                    {
                                        className: 'tailpress-gcb-theme-color-palette'
                                    },
                                    createElement(ColorPalette, {
                                        colors: themeColors,
                                        value: colorValue,
                                        onChange: function (nextColor) {
                                            onChange(nextColor || '');
                                        },
                                        disableCustomColors: false,
                                        clearable: true
                                    })
                                )
                              : null
                      )
                    : null
            );
        };
    }

    enhancedColorControlRef = createEnhancedColorControl();

    addFilter(
        'genesisCustomBlocks.controls',
        'tailpress/gcb-color-palette',
        function (controls) {
            if (!controls || !controls.color) {
                return controls;
            }

            return Object.assign({}, controls, {
                color: enhancedColorControlRef
            });
        },
        20
    );
})();
