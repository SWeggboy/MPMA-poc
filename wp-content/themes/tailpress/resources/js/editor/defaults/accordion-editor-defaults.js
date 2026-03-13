(function (wp) {
  if (!wp || !wp.hooks || !wp.hooks.addFilter) {
    return;
  }

  const setAccordionHeadingDefaultFontSize = (settings, name) => {
    if (name !== 'core/accordion-heading') {
      return settings;
    }

    const attributes = settings.attributes ? { ...settings.attributes } : {};
    const fontSizeAttribute = attributes.fontSize
      ? { ...attributes.fontSize }
      : { type: 'string' };

    if (typeof fontSizeAttribute.default === 'undefined') {
      fontSizeAttribute.default = 'base';
    }

    attributes.fontSize = fontSizeAttribute;

    return {
      ...settings,
      attributes,
    };
  };

  wp.hooks.addFilter(
    'blocks.registerBlockType',
    'tailpress/accordion-heading-default-font-size',
    setAccordionHeadingDefaultFontSize
  );
})(window.wp);
