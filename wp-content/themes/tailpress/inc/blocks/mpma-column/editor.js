(function(wp) {
    const { registerBlockType } = wp.blocks;
    const { useBlockProps, InnerBlocks } = wp.blockEditor;
    const { createElement: el } = wp.element;
    const { __ } = wp.i18n;

    registerBlockType('tailpress/mpma-column', {
        edit: function() {
            const blockProps = useBlockProps({
                className: 'mpma-column-block-editor [&_.wp-block-columns]:items-stretch [&_.wp-block-column]:flex [&_.wp-block-column]:flex-col [&_.wp-block-column>*]:w-full [&_.wp-block-column>.wp-block-group]:flex [&_.wp-block-column>.wp-block-group]:h-full [&_.wp-block-column>.mpma-column-item]:flex [&_.wp-block-column>.mpma-column-item]:h-full'
            });

            return el('div', blockProps,
                el('p', { style: { margin: '0 0 0.75rem', fontSize: '0.875rem', color: '#4b5563' } },
                    __('Use this as an equal-height columns wrapper. Add or edit columns inside this block.', 'tailpress')
                ),
                el(InnerBlocks, {
                    allowedBlocks: ['core/columns'],
                    template: [
                        ['core/columns', {}, [
                            ['core/column', {}, [
                                ['core/group', { className: 'mpma-column-item', style: { spacing: { padding: { top: '1.5rem', right: '1.5rem', bottom: '1.5rem', left: '1.5rem' } } } }, [
                                    ['core/paragraph', { content: __('Column content', 'tailpress') }]
                                ]]
                            ]],
                            ['core/column', {}, [
                                ['core/group', { className: 'mpma-column-item', style: { spacing: { padding: { top: '1.5rem', right: '1.5rem', bottom: '1.5rem', left: '1.5rem' } } } }, [
                                    ['core/paragraph', { content: __('Column content', 'tailpress') }]
                                ]]
                            ]]
                        ]]
                    ],
                    templateLock: false
                })
            );
        },

        save: function() {
            return el(InnerBlocks.Content);
        }
    });
})(window.wp);
