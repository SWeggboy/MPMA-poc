/**
 * Add event sections visibility toggles to page editor
 */
(function() {
    const { registerPlugin } = wp.plugins;
    const { PluginDocumentSettingPanel } = wp.editPost;
    const { ToggleControl } = wp.components;
    const { useSelect, useDispatch } = wp.data;
    const { __ } = wp.i18n;
    const { createElement } = wp.element;

    const EventsSectionsToggle = function() {
        const postType = useSelect(function(select) {
            return select('core/editor').getCurrentPostType();
        }, []);
        
        // Only show on pages and posts
        if (postType !== 'page' && postType !== 'post' && postType !== 'tribe_events') {
            return null;
        }

        const meta = useSelect(function(select) {
            const currentMeta = select('core/editor').getEditedPostAttribute('meta');
            const defaultSectionsCollapsible = postType === 'page';
            return {
                sectionsCollapsible: typeof currentMeta?.show_events_collapsible !== 'undefined' ? currentMeta.show_events_collapsible : defaultSectionsCollapsible,
                showCourses: typeof currentMeta?.show_events_courses !== 'undefined' ? currentMeta.show_events_courses : true,
                showWebinars: typeof currentMeta?.show_events_webinars !== 'undefined' ? currentMeta.show_events_webinars : true,
                showEvents: typeof currentMeta?.show_events_events !== 'undefined' ? currentMeta.show_events_events : true,
            };
        }, [postType]);

        const { editPost } = useDispatch('core/editor');

        const handleToggle = function(field) {
            return function(value) {
                editPost({
                    meta: {
                        [field]: value
                    }
                });
            };
        };

        return createElement(
            PluginDocumentSettingPanel,
            {
                name: 'events-sections-visibility',
                title: __('Upcoming Events Sections', 'tailpress'),
                className: 'events-sections-visibility-panel'
            },
            createElement(ToggleControl, {
                label: __('Use Collapsible Sections', 'tailpress'),
                checked: meta.sectionsCollapsible,
                onChange: handleToggle('show_events_collapsible'),
                help: __('Turn off to keep the sidebar section headings static on this page.', 'tailpress')
            }),
            createElement(ToggleControl, {
                label: __('Show Upcoming Courses', 'tailpress'),
                checked: meta.showCourses,
                onChange: handleToggle('show_events_courses'),
                help: __('Display the Upcoming Courses section in the sidebar', 'tailpress')
            }),
            createElement(ToggleControl, {
                label: __('Show Upcoming Webinars', 'tailpress'),
                checked: meta.showWebinars,
                onChange: handleToggle('show_events_webinars'),
                help: __('Display the Upcoming Webinars section in the sidebar', 'tailpress')
            }),
            createElement(ToggleControl, {
                label: __('Show Upcoming Events', 'tailpress'),
                checked: meta.showEvents,
                onChange: handleToggle('show_events_events'),
                help: __('Display the Upcoming Events section in the sidebar', 'tailpress')
            })
        );
    };

    registerPlugin('events-sections-toggle', {
        render: EventsSectionsToggle,
        icon: 'calendar-alt'
    });
})();
