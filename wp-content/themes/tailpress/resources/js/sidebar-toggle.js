/**
 * Add sidebar visibility toggle to page editor
 */
(function() {
    const { registerPlugin } = wp.plugins;
    const { PluginDocumentSettingPanel } = wp.editPost;
    const { ToggleControl } = wp.components;
    const { useSelect, useDispatch } = wp.data;
    const { __ } = wp.i18n;
    const { createElement } = wp.element;

    const SidebarToggle = function() {
        const postType = useSelect(function(select) {
            return select('core/editor').getCurrentPostType();
        }, []);
        
        // Only show on pages
        if (postType !== 'page') {
            return null;
        }

        const showSidebar = useSelect(function(select) {
            const meta = select('core/editor').getEditedPostAttribute('meta');
            if (!meta || typeof meta.show_sidebar === 'undefined') {
                return true; // Default to true
            }
            return meta.show_sidebar;
        }, []);

        const { editPost } = useDispatch('core/editor');

        const handleToggle = function(value) {
            editPost({
                meta: {
                    show_sidebar: value
                }
            });
        };

        return createElement(
            PluginDocumentSettingPanel,
            {
                name: 'sidebar-visibility',
                title: __('Sidebar Settings', 'tailpress'),
                className: 'sidebar-visibility-panel'
            },
            createElement(ToggleControl, {
                label: __('Show sidebar on this page', 'tailpress'),
                checked: showSidebar,
                onChange: handleToggle,
                help: __('Toggle to show or hide the sidebar widget area.', 'tailpress')
            })
        );
    };

    registerPlugin('tailpress-sidebar-toggle', {
        render: SidebarToggle
    });
})();
