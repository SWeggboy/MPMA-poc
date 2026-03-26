/**
 * Add sidebar visibility toggles to page and post editors
 */
(function() {
    const { registerPlugin } = wp.plugins;
    const { PluginDocumentSettingPanel } = wp.editPost;
    const { ToggleControl } = wp.components;
    const { useSelect, useDispatch } = wp.data;
    const { __, sprintf } = wp.i18n;
    const { createElement } = wp.element;

    const SidebarToggle = function() {
        const postType = useSelect(function(select) {
            return select('core/editor').getCurrentPostType();
        }, []);
        
        // Only show on pages and posts
        if (postType !== 'page' && postType !== 'post' && postType !== 'tribe_events') {
            return null;
        }

        const currentMeta = useSelect(function(select) {
            return select('core/editor').getEditedPostAttribute('meta') || {};
        }, []);

        const showSidebar = useSelect(function() {
            const meta = currentMeta;
            if (!meta || typeof meta.show_sidebar === 'undefined') {
                return true; // Default to true
            }
            return meta.show_sidebar;
        }, [currentMeta]);

        const sidebarFloating = useSelect(function() {
            const meta = currentMeta;
            if (!meta || typeof meta.sidebar_floating === 'undefined') {
                return true;
            }
            return meta.sidebar_floating;
        }, [currentMeta]);

        const { editPost } = useDispatch('core/editor');

        const handleToggle = function(value) {
            editPost({
                meta: Object.assign({}, currentMeta, {
                    show_sidebar: value
                })
            });
        };

        const handleFloatingToggle = function(value) {
            editPost({
                meta: Object.assign({}, currentMeta, {
                    sidebar_floating: value
                })
            });
        };

        const objectLabel = postType === 'page'
            ? __('page', 'tailpress')
            : (postType === 'tribe_events' ? __('event', 'tailpress') : __('post', 'tailpress'));

        return createElement(
            PluginDocumentSettingPanel,
            {
                name: 'sidebar-visibility',
                title: __('Sidebar Settings', 'tailpress'),
                className: 'sidebar-visibility-panel'
            },
            createElement(ToggleControl, {
                label: sprintf(__('Show sidebar on this %s', 'tailpress'), objectLabel),
                checked: showSidebar,
                onChange: handleToggle,
                help: __('Toggle to show or hide the sidebar widget area.', 'tailpress')
            }),
            showSidebar && createElement(ToggleControl, {
                label: __('Keep sidebar floating', 'tailpress'),
                checked: sidebarFloating,
                onChange: handleFloatingToggle,
                help: __('Turn this off to keep the sidebar in its 4-column row instead of letting content flow under it.', 'tailpress')
            })
        );
    };

    registerPlugin('tailpress-sidebar-toggle', {
        render: SidebarToggle
    });
})();
