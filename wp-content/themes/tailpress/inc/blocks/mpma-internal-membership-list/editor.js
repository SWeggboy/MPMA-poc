/**
 * MPMA Internal Membership List block editor.
 */
(function(wp) {
    if (!wp || !wp.blocks || !wp.blockEditor || !wp.components || !wp.i18n || !wp.element) {
        return;
    }

    const { registerBlockType } = wp.blocks;
    const { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } = wp.blockEditor;
    const { Button, PanelBody, TextControl, TextareaControl } = wp.components;
    const { __, sprintf } = wp.i18n;
    const { createElement: el, Fragment, useEffect, useState } = wp.element;

    const DEFAULT_ORGANIZATIONS = [
        {
            slug: 'agma',
            label: 'AGMA',
            imageUrl: '',
            imageId: 0,
            sections: [
                {
                    slug: 'corporate',
                    label: 'Corporate',
                    filters: [
                        { slug: 'num', label: '#', items: [] },
                        { slug: 'a', label: 'A', items: [] },
                        { slug: 'b', label: 'B', items: [] },
                        { slug: 'c', label: 'C', items: [] },
                        { slug: 'd', label: 'D', items: [] },
                        { slug: 'e', label: 'E', items: [] },
                        { slug: 'f', label: 'F', items: [] },
                        { slug: 'g', label: 'G', items: [] },
                        { slug: 'h', label: 'H', items: [] },
                        { slug: 'i', label: 'I', items: [] },
                        { slug: 'j', label: 'J', items: [] },
                        { slug: 'k', label: 'K', items: [] },
                        { slug: 'l', label: 'L', items: [] },
                        { slug: 'm', label: 'M', items: [] },
                        { slug: 'n', label: 'N', items: [] },
                        { slug: 'o', label: 'O', items: [] },
                        { slug: 'p', label: 'P', items: [] },
                        { slug: 'q', label: 'Q', items: [] },
                        { slug: 'r', label: 'R', items: [] },
                        { slug: 's', label: 'S', items: [] },
                        { slug: 't', label: 'T', items: [] },
                        { slug: 'u', label: 'U', items: [] },
                        { slug: 'v', label: 'V', items: [] },
                        { slug: 'w', label: 'W', items: [] },
                        { slug: 'x', label: 'X', items: [] },
                        { slug: 'y', label: 'Y', items: [] },
                        { slug: 'z', label: 'Z', items: [] }
                    ],
                    items: []
                },
                { slug: 'consultant', label: 'Consultant', filters: [], items: [] },
                { slug: 'academic', label: 'Academic', filters: [], items: [] },
                { slug: 'emeritus', label: 'Emeritus', filters: [], items: [] },
                {
                    slug: 'membership-timeframes',
                    label: 'Membership Timeframes',
                    filters: [
                        { slug: '100', label: '100', items: [] },
                        { slug: '75', label: '75', items: [] },
                        { slug: '50', label: '50', items: [] },
                        { slug: '25', label: '25', items: [] },
                        { slug: '20', label: '20', items: [] },
                        { slug: '15', label: '15', items: [] },
                        { slug: '10', label: '10', items: [] },
                        { slug: '5', label: '5', items: [] }
                    ],
                    items: []
                }
            ]
        },
        {
            slug: 'abma',
            label: 'ABMA',
            imageUrl: '',
            imageId: 0,
            sections: [
                { slug: 'primary-manufacturer-companies', label: 'Primary Manufacturer Companies', filters: [], items: [] },
                { slug: 'associate-members', label: 'Associate Members', filters: [], items: [] }
            ]
        }
    ];

    function cloneOrganizations(value) {
        if (Array.isArray(value) && value.length) {
            return JSON.parse(JSON.stringify(value));
        }

        return JSON.parse(JSON.stringify(DEFAULT_ORGANIZATIONS));
    }

    function itemsToTextarea(items) {
        if (!Array.isArray(items)) {
            return '';
        }

        return items.map(function(item) {
            const label = typeof item.label === 'string' ? item.label : '';
            const url = typeof item.url === 'string' ? item.url : '';

            if (!label.trim() && !url.trim()) {
                return '';
            }

            return url ? label + ' | ' + url : label;
        }).filter(Boolean).join('\n');
    }

    function textareaToItems(value) {
        const isLinkCandidate = function(candidate) {
            const trimmed = String(candidate || '').trim();
            return /^(https?:\/\/|\/|mailto:|tel:)/i.test(trimmed);
        };

        return String(value || '')
            .split('\n')
            .map(function(line) {
                if (!line.trim()) {
                    return null;
                }

                const separatorIndex = line.indexOf('|');
                const possibleLabel = separatorIndex === -1 ? line : line.slice(0, separatorIndex);
                const possibleUrl = separatorIndex === -1 ? '' : line.slice(separatorIndex + 1);
                const label = separatorIndex !== -1 && isLinkCandidate(possibleUrl) ? possibleLabel : line;
                const url = separatorIndex !== -1 && isLinkCandidate(possibleUrl) ? possibleUrl : '';

                if (!label.trim()) {
                    return null;
                }

                return { label: label, url: url };
            })
            .filter(Boolean);
    }

    function getFirstSectionSlug(org) {
        return org && Array.isArray(org.sections) && org.sections.length ? org.sections[0].slug : '';
    }

    function getFirstFilterSlug(section) {
        return section && Array.isArray(section.filters) && section.filters.length ? section.filters[0].slug : '';
    }

    registerBlockType('tailpress/mpma-internal-membership-list', {
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const organizations = cloneOrganizations(attributes.organizations);
            const blockProps = useBlockProps();

            const [activeOrgSlug, setActiveOrgSlug] = useState(organizations[0] ? organizations[0].slug : '');
            const [activeSectionByOrg, setActiveSectionByOrg] = useState({});
            const [activeFilterBySection, setActiveFilterBySection] = useState({});
            const [pendingInspectorTarget, setPendingInspectorTarget] = useState('');
            const [draftListValues, setDraftListValues] = useState({});

            useEffect(function() {
                if (!Array.isArray(attributes.organizations) || !attributes.organizations.length) {
                    setAttributes({ organizations: cloneOrganizations(DEFAULT_ORGANIZATIONS) });
                }
            }, []);

            useEffect(function() {
                if (!organizations.length) {
                    return;
                }

                const hasActiveOrg = organizations.some(function(org) {
                    return org.slug === activeOrgSlug;
                });

                if (!hasActiveOrg) {
                    setActiveOrgSlug(organizations[0].slug);
                }

                setActiveSectionByOrg(function(previous) {
                    const next = Object.assign({}, previous);

                    organizations.forEach(function(org) {
                        const sectionExists = org.sections.some(function(section) {
                            return section.slug === next[org.slug];
                        });

                        if (!sectionExists) {
                            next[org.slug] = getFirstSectionSlug(org);
                        }
                    });

                    return next;
                });

                setActiveFilterBySection(function(previous) {
                    const next = Object.assign({}, previous);

                    organizations.forEach(function(org) {
                        org.sections.forEach(function(section) {
                            const key = org.slug + ':' + section.slug;
                            const filterExists = section.filters.some(function(filter) {
                                return filter.slug === next[key];
                            });

                            if (!filterExists) {
                                next[key] = getFirstFilterSlug(section);
                            }
                        });
                    });

                    return next;
                });
            }, [attributes.organizations, activeOrgSlug]);

            useEffect(function() {
                if (!pendingInspectorTarget) {
                    return;
                }

                const timer = window.setTimeout(function() {
                    const scope = document.querySelector('[data-membership-inspector-target="' + pendingInspectorTarget + '"]');
                    if (!scope) {
                        setPendingInspectorTarget('');
                        return;
                    }

                    const field = scope.querySelector('textarea, input');
                    if (!field) {
                        setPendingInspectorTarget('');
                        return;
                    }

                    field.focus();
                    if (typeof field.scrollIntoView === 'function') {
                        field.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                    }
                    setPendingInspectorTarget('');
                }, 40);

                return function() {
                    window.clearTimeout(timer);
                };
            }, [pendingInspectorTarget, activeOrgSlug, activeSectionByOrg, activeFilterBySection]);

            function updateOrganizations(updater) {
                const nextOrganizations = cloneOrganizations(organizations);
                updater(nextOrganizations);
                setAttributes({ organizations: nextOrganizations });
            }

            function getDraftValue(key, items) {
                if (Object.prototype.hasOwnProperty.call(draftListValues, key)) {
                    return draftListValues[key];
                }

                return itemsToTextarea(items);
            }

            function setDraftValue(key, value) {
                setDraftListValues(function(previous) {
                    return Object.assign({}, previous, { [key]: value });
                });
            }

            function clearDraftValue(key) {
                setDraftListValues(function(previous) {
                    const next = Object.assign({}, previous);
                    delete next[key];
                    return next;
                });
            }

            function updateOrganization(orgIndex, patch) {
                updateOrganizations(function(nextOrganizations) {
                    nextOrganizations[orgIndex] = Object.assign({}, nextOrganizations[orgIndex], patch);
                });
            }

            function updateSection(orgIndex, sectionIndex, patch) {
                updateOrganizations(function(nextOrganizations) {
                    nextOrganizations[orgIndex].sections[sectionIndex] = Object.assign(
                        {},
                        nextOrganizations[orgIndex].sections[sectionIndex],
                        patch
                    );
                });
            }

            function updateFilter(orgIndex, sectionIndex, filterIndex, patch) {
                updateOrganizations(function(nextOrganizations) {
                    nextOrganizations[orgIndex].sections[sectionIndex].filters[filterIndex] = Object.assign(
                        {},
                        nextOrganizations[orgIndex].sections[sectionIndex].filters[filterIndex],
                        patch
                    );
                });
            }

            const activeOrg = organizations.find(function(org) {
                return org.slug === activeOrgSlug;
            }) || organizations[0] || null;

            const activeSectionSlug = activeOrg ? (activeSectionByOrg[activeOrg.slug] || getFirstSectionSlug(activeOrg)) : '';
            const activeSection = activeOrg && Array.isArray(activeOrg.sections)
                ? activeOrg.sections.find(function(section) {
                    return section.slug === activeSectionSlug;
                }) || activeOrg.sections[0]
                : null;

            const activeFilterKey = activeOrg && activeSection ? activeOrg.slug + ':' + activeSection.slug : '';
            const activeFilterSlug = activeFilterKey ? (activeFilterBySection[activeFilterKey] || getFirstFilterSlug(activeSection)) : '';
            const activeFilter = activeSection && Array.isArray(activeSection.filters) && activeSection.filters.length
                ? activeSection.filters.find(function(filter) {
                    return filter.slug === activeFilterSlug;
                }) || activeSection.filters[0]
                : null;

            const previewItems = activeFilter ? activeFilter.items : (activeSection ? activeSection.items : []);

            return el(Fragment, null,
                el(InspectorControls, null,
                    el(PanelBody, {
                        title: __('General', 'tailpress'),
                        initialOpen: true
                    },
                        el(TextControl, {
                            label: __('Title', 'tailpress'),
                            value: attributes.title || 'Member List',
                            onChange: function(value) {
                                setAttributes({ title: value });
                            }
                        }),
                        el(TextControl, {
                            label: __('Subtitle', 'tailpress'),
                            value: attributes.subtitle || 'Select an organization to view members',
                            onChange: function(value) {
                                setAttributes({ subtitle: value });
                            }
                        })
                    ),
                    organizations.map(function(org, orgIndex) {
                        return el(PanelBody, {
                            key: org.slug,
                            title: sprintf(__('Organization: %s', 'tailpress'), org.label || org.slug),
                            opened: activeOrgSlug === org.slug,
                            onToggle: function(nextOpen) {
                                if (nextOpen) {
                                    setActiveOrgSlug(org.slug);
                                }
                            }
                        },
                            el('div', { 'data-membership-inspector-target': 'organization:' + org.slug },
                                el(TextControl, {
                                    label: __('Organization Label', 'tailpress'),
                                    value: org.label || '',
                                    onChange: function(value) {
                                        updateOrganization(orgIndex, { label: value });
                                    }
                                })
                            ),
                            el(MediaUploadCheck, null,
                                el(MediaUpload, {
                                    onSelect: function(media) {
                                        updateOrganization(orgIndex, {
                                            imageUrl: media && media.url ? media.url : '',
                                            imageId: media && media.id ? media.id : 0
                                        });
                                    },
                                    allowedTypes: ['image'],
                                    value: org.imageId || 0,
                                    render: function(obj) {
                                        return el(Button, {
                                            onClick: obj.open,
                                            variant: 'secondary',
                                            style: { marginBottom: '12px' }
                                        }, org.imageUrl ? __('Replace Category Image', 'tailpress') : __('Select Category Image', 'tailpress'));
                                    }
                                })
                            ),
                            !!org.imageUrl && el(Button, {
                                onClick: function() {
                                    updateOrganization(orgIndex, { imageUrl: '', imageId: 0 });
                                },
                                variant: 'link',
                                isDestructive: true
                            }, __('Remove Category Image', 'tailpress')),
                            Array.isArray(org.sections) && org.sections.map(function(section, sectionIndex) {
                                return el('div', {
                                        key: section.slug,
                                        'data-membership-inspector-target': 'section:' + org.slug + ':' + section.slug,
                                        style: {
                                            borderTop: '1px solid #dcdcde',
                                            marginTop: '16px',
                                            paddingTop: '16px'
                                        }
                                    },
                                    el(TextControl, {
                                        label: sprintf(__('Section Label: %s', 'tailpress'), section.label || section.slug),
                                        value: section.label || '',
                                        onChange: function(value) {
                                            updateSection(orgIndex, sectionIndex, { label: value });
                                        }
                                    }),
                                    Array.isArray(section.filters) && section.filters.length
                                        ? section.filters.map(function(filter, filterIndex) {
                                            const listKey = 'filter:' + org.slug + ':' + section.slug + ':' + filter.slug + ':items';
                                            return el('div', {
                                                    key: filter.slug,
                                                    'data-membership-inspector-target': 'filter:' + org.slug + ':' + section.slug + ':' + filter.slug,
                                                    style: {
                                                        marginTop: '12px',
                                                        paddingTop: '12px',
                                                        borderTop: '1px dashed #dcdcde'
                                                    }
                                                },
                                                el(TextControl, {
                                                    label: __('Filter Label', 'tailpress'),
                                                    value: filter.label || '',
                                                    onChange: function(value) {
                                                        updateFilter(orgIndex, sectionIndex, filterIndex, { label: value });
                                                    }
                                                }),
                                                el(TextareaControl, {
                                                    label: __('List Items', 'tailpress'),
                                                    value: getDraftValue(listKey, filter.items),
                                                    onChange: function(value) {
                                                        setDraftValue(listKey, value);
                                                    },
                                                    onBlur: function() {
                                                        updateFilter(orgIndex, sectionIndex, filterIndex, {
                                                            items: textareaToItems(getDraftValue(listKey, filter.items))
                                                        });
                                                        clearDraftValue(listKey);
                                                    },
                                                    help: __('One item per line. Links only apply when the value after “|” starts with http://, https://, /, mailto:, or tel:.', 'tailpress'),
                                                    rows: 5
                                                })
                                            );
                                        })
                                        : (function() {
                                            const listKey = 'section:' + org.slug + ':' + section.slug + ':items';

                                            return el(TextareaControl, {
                                                label: __('List Items', 'tailpress'),
                                                value: getDraftValue(listKey, section.items),
                                                onChange: function(value) {
                                                    setDraftValue(listKey, value);
                                                },
                                                onBlur: function() {
                                                    updateSection(orgIndex, sectionIndex, {
                                                        items: textareaToItems(getDraftValue(listKey, section.items))
                                                    });
                                                    clearDraftValue(listKey);
                                                },
                                                help: __('One item per line. Links only apply when the value after “|” starts with http://, https://, /, mailto:, or tel:.', 'tailpress'),
                                                rows: 6
                                            });
                                        })()
                                );
                            })
                        );
                    })
                ),
                el('div', blockProps,
                        el('section', {
                            className: 'mpma-membership-list-editor-preview',
                            style: {
                                padding: '0'
                            }
                        },
                        el('div', {
                                style: {
                                    maxWidth: '64rem',
                                    margin: '0 auto',
                                    textAlign: 'center'
                                }
                            },
                            el('h2', {
                                style: {
                                    margin: 0,
                                    color: '#367557',
                                    fontFamily: 'Montserrat, sans-serif',
                                    fontSize: '2rem',
                                    fontWeight: 700
                                }
                            }, attributes.title || 'Member List'),
                            el('p', {
                                style: {
                                    margin: '1rem 0 0',
                                    color: '#000000',
                                    fontFamily: 'Roboto, sans-serif',
                                    fontSize: '1.18rem',
                                    lineHeight: '1.45'
                                }
                            }, attributes.subtitle || 'Select an organization to view members'),
                            el('div', {
                                    style: {
                                        display: 'flex',
                                        flexWrap: 'nowrap',
                                        justifyContent: 'center',
                                        alignItems: 'center',
                                        gap: '1.5rem',
                                        marginTop: '1.75rem',
                                        width: '100%',
                                        marginInline: 'auto'
                                    }
                                },
                                organizations.map(function(org) {
                                    const isActiveOrg = activeOrg && activeOrg.slug === org.slug;

                                    return el('button', {
                                        key: org.slug,
                                        type: 'button',
                                        onClick: function() {
                                            setActiveOrgSlug(org.slug);
                                            setPendingInspectorTarget('organization:' + org.slug);
                                        },
                                        style: {
                                            width: 'min(19.5rem, calc(50% - 0.75rem))',
                                            flex: '0 0 auto',
                                            minHeight: '0',
                                            borderRadius: '0',
                                            overflow: 'hidden',
                                            border: '0',
                                            opacity: isActiveOrg ? 1 : 0.35,
                                            background: org.imageUrl
                                                ? 'center / cover no-repeat url("' + org.imageUrl + '")'
                                                : 'transparent',
                                            color: '#000000',
                                            fontFamily: 'Montserrat, sans-serif',
                                            fontWeight: 600,
                                            padding: '0',
                                            cursor: 'pointer'
                                        }
                                    }, org.imageUrl
                                        ? el('img', {
                                            src: org.imageUrl,
                                            alt: '',
                                            style: {
                                                display: 'block',
                                                width: '100%',
                                                maxWidth: '100%',
                                                height: 'auto',
                                                objectFit: 'contain',
                                                objectPosition: 'center',
                                                marginInline: 'auto'
                                            }
                                        })
                                        : (org.label || org.slug));
                                })
                            ),
                            activeOrg && el('div', {
                                    style: {
                                        display: 'flex',
                                        flexWrap: 'wrap',
                                        justifyContent: 'center',
                                        gap: '0.75rem',
                                        marginTop: '1.75rem'
                                    }
                                },
                                activeOrg.sections.map(function(section) {
                                    const isActiveSection = activeSection && activeSection.slug === section.slug;

                                    return el('button', {
                                        key: section.slug,
                                        type: 'button',
                                        onClick: function() {
                                            setActiveSectionByOrg(Object.assign({}, activeSectionByOrg, { [activeOrg.slug]: section.slug }));
                                            setPendingInspectorTarget('section:' + activeOrg.slug + ':' + section.slug);
                                        },
                                        style: {
                                            whiteSpace: 'nowrap',
                                            border: '0',
                                            padding: '0.2rem 0.45rem',
                                            backgroundColor: 'transparent',
                                            color: isActiveSection ? '#000000' : '#7e7e7e',
                                            fontFamily: 'Montserrat, sans-serif',
                                            fontSize: '1rem',
                                            fontWeight: 700,
                                            textDecoration: 'none',
                                            cursor: 'pointer'
                                        }
                                    }, section.label || section.slug);
                                })
                            ),
                            activeSection && Array.isArray(activeSection.filters) && activeSection.filters.length > 0 && el('div', {
                                    style: {
                                        display: 'flex',
                                        flexWrap: 'wrap',
                                        justifyContent: 'center',
                                        gap: '0.55rem',
                                        marginTop: '1.25rem'
                                    }
                                },
                                activeSection.filters.map(function(filter) {
                                    const key = activeOrg.slug + ':' + activeSection.slug;
                                    const isActiveFilter = activeFilter && activeFilter.slug === filter.slug;

                                    return el('button', {
                                        key: filter.slug,
                                        type: 'button',
                                        onClick: function() {
                                            setActiveFilterBySection(Object.assign({}, activeFilterBySection, { [key]: filter.slug }));
                                            setPendingInspectorTarget('filter:' + activeOrg.slug + ':' + activeSection.slug + ':' + filter.slug);
                                        },
                                        style: {
                                            minWidth: '2.5rem',
                                            whiteSpace: 'nowrap',
                                            borderRadius: '0',
                                            padding: '0.2rem 0.35rem',
                                            border: '0',
                                            backgroundColor: 'transparent',
                                            color: isActiveFilter ? '#000000' : '#7e7e7e',
                                            fontFamily: 'Montserrat, sans-serif',
                                            fontSize: '1rem',
                                            fontWeight: 700,
                                            textDecoration: 'none',
                                            cursor: 'pointer'
                                        }
                                    }, filter.label || filter.slug);
                                })
                            ),
                            el('ul', {
                                style: {
                                    listStyle: 'none',
                                    margin: '1.75rem 0 0',
                                    padding: 0,
                                    textAlign: 'center'
                                }
                            },
                                Array.isArray(previewItems) && previewItems.length
                                    ? previewItems.map(function(item, itemIndex) {
                                        return el('li', {
                                            key: item.label + '-' + itemIndex,
                                            style: { marginTop: itemIndex ? '0.65rem' : 0 }
                                        }, item.label);
                                    })
                                    : el('li', {
                                        style: {
                                            color: '#68737c',
                                            fontStyle: 'italic'
                                        }
                                    }, __('Add list items in the block settings.', 'tailpress'))
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
