window.addEventListener('load', function () {
    let mainNavigation = document.getElementById('primary-navigation')
    let mainNavigationToggle = document.getElementById('primary-menu-toggle')

    if(mainNavigation && mainNavigationToggle) {
        const rootStyles = getComputedStyle(document.documentElement)
        const lgBreakpoint = rootStyles.getPropertyValue('--breakpoint-lg').trim()

        if (!lgBreakpoint) {
            return
        }

        const desktopMediaQuery = window.matchMedia(`(min-width: ${lgBreakpoint})`)

        const closeMobileMenu = function () {
            mainNavigation.style.maxHeight = '0px'
            mainNavigation.style.opacity = '0'
            mainNavigation.dataset.open = 'false'
        }

        const openMobileMenu = function () {
            mainNavigation.style.opacity = '1'
            mainNavigation.style.maxHeight = `${mainNavigation.scrollHeight}px`
            mainNavigation.dataset.open = 'true'
        }

        if (!desktopMediaQuery.matches) {
            closeMobileMenu()
        } else {
            mainNavigation.style.maxHeight = 'none'
            mainNavigation.style.opacity = '1'
            mainNavigation.dataset.open = 'true'
        }

        mainNavigationToggle.addEventListener('click', function (e) {
            e.preventDefault()

            const isOpen = mainNavigation.dataset.open === 'true'

            if (isOpen) {
                mainNavigation.style.maxHeight = `${mainNavigation.scrollHeight}px`
                requestAnimationFrame(function () {
                    closeMobileMenu()
                })
            } else {
                mainNavigation.style.maxHeight = '0px'
                requestAnimationFrame(function () {
                    openMobileMenu()
                })
            }
        })

        window.addEventListener('resize', function () {
            if (desktopMediaQuery.matches) {
                mainNavigation.style.maxHeight = 'none'
                mainNavigation.style.opacity = '1'
                mainNavigation.dataset.open = 'true'
                return
            }

            if (mainNavigation.dataset.open === 'true') {
                openMobileMenu()
            } else {
                closeMobileMenu()
            }
        })
    }
})

// Expandable Search Box
document.addEventListener('DOMContentLoaded', function() {
    const searchBox = document.querySelector('.search-box');
    const searchToggle = document.querySelector('.search-toggle');
    const searchInput = document.querySelector('.search-input');
    
    if (!searchBox || !searchToggle || !searchInput) return;
    
    let isExpanded = false;

    const expandSearch = function (focusInput = false) {
        searchBox.style.width = '180px';
        searchInput.style.opacity = '1';
        searchInput.style.pointerEvents = 'auto';
        isExpanded = true;

        if (focusInput) {
            searchInput.focus();
        }
    };

    const collapseSearch = function () {
        searchInput.style.opacity = '0';
        searchInput.style.pointerEvents = 'none';
        searchBox.style.width = '38px';
        isExpanded = false;
    };

    searchToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (!isExpanded) {
            expandSearch(true);
        }
    });

    // Expand when tab/focus reaches the search control.
    searchBox.addEventListener('focusin', function() {
        if (!isExpanded) {
            expandSearch(false);
        }
    });

    // Collapse after keyboard navigation leaves the control.
    searchBox.addEventListener('focusout', function() {
        window.setTimeout(function() {
            if (isExpanded && !searchBox.contains(document.activeElement) && !searchInput.value) {
                collapseSearch();
            }
        }, 0);
    });

    // Collapse when clicking outside
    document.addEventListener('click', function(e) {
        if (isExpanded && !searchBox.contains(e.target) && !searchInput.value) {
            collapseSearch();
        }
    });

    // Submit on Enter
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && searchInput.value) {
            searchInput.closest('form').submit();
        }
    });
})

// Mobile secondary menu nested toggles
document.addEventListener('DOMContentLoaded', function () {
    const mobileSecondaryMenu = document.querySelector('#mobile-secondary-menu');
    const mainNavigation = document.getElementById('primary-navigation');

    if (!mobileSecondaryMenu) {
        return;
    }

    const syncMobileNavHeight = function () {
        if (!mainNavigation || mainNavigation.dataset.open !== 'true') {
            return;
        }

        mainNavigation.style.maxHeight = `${mainNavigation.scrollHeight}px`;
    };

    const parentItems = mobileSecondaryMenu.querySelectorAll('.menu-item-has-children');

    parentItems.forEach(function (item) {
        const existingToggle = item.querySelector(':scope > .submenu-toggle');
        const link = item.querySelector(':scope > a');

        if (existingToggle || !link) {
            return;
        }

        const toggleButton = document.createElement('button');
        toggleButton.type = 'button';
        toggleButton.className = 'submenu-toggle';
        toggleButton.setAttribute('aria-expanded', 'false');
        toggleButton.setAttribute('aria-label', 'Toggle submenu');
        toggleButton.textContent = '\u200B';

        toggleButton.addEventListener('click', function (event) {
            event.preventDefault();
            const isOpen = item.classList.toggle('is-open');
            toggleButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            window.requestAnimationFrame(syncMobileNavHeight);
        });

        link.insertAdjacentElement('afterend', toggleButton);
    });
});

// Sidebar accordion sections for courses, webinars, and events
document.addEventListener('DOMContentLoaded', function () {
    const accordionToggles = document.querySelectorAll('.tribe-events-section__toggle');

    if (!accordionToggles.length) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const setExpandedState = function (toggle, panel, shouldExpand) {
        toggle.setAttribute('aria-expanded', shouldExpand ? 'true' : 'false');

        if (prefersReducedMotion) {
            panel.hidden = !shouldExpand;
            panel.style.maxHeight = shouldExpand ? 'none' : '0px';
            panel.style.opacity = shouldExpand ? '1' : '0';
            return;
        }

        if (shouldExpand) {
            panel.hidden = false;
            panel.style.maxHeight = '0px';
            panel.style.opacity = '0';

            window.requestAnimationFrame(function () {
                panel.style.maxHeight = `${panel.scrollHeight}px`;
                panel.style.opacity = '1';
            });

            const handleExpandEnd = function (event) {
                if (event.propertyName !== 'max-height') {
                    return;
                }

                panel.style.maxHeight = 'none';
                panel.removeEventListener('transitionend', handleExpandEnd);
            };

            panel.addEventListener('transitionend', handleExpandEnd);
            return;
        }

        const currentHeight = panel.scrollHeight;
        panel.style.maxHeight = `${currentHeight}px`;
        panel.style.opacity = '1';

        window.requestAnimationFrame(function () {
            panel.style.maxHeight = '0px';
            panel.style.opacity = '0';
        });

        const handleCollapseEnd = function (event) {
            if (event.propertyName !== 'max-height') {
                return;
            }

            panel.hidden = true;
            panel.removeEventListener('transitionend', handleCollapseEnd);
        };

        panel.addEventListener('transitionend', handleCollapseEnd);
    };

    accordionToggles.forEach(function (toggle) {
        const panelId = toggle.getAttribute('aria-controls');
        const panel = panelId ? document.getElementById(panelId) : null;

        if (!panel) {
            return;
        }

        const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
        panel.style.maxHeight = isExpanded ? 'none' : '0px';
        panel.style.opacity = isExpanded ? '1' : '0';
        panel.hidden = !isExpanded;
        panel.classList.add('is-ready');

        toggle.addEventListener('click', function () {
            const shouldExpand = toggle.getAttribute('aria-expanded') !== 'true';
            setExpandedState(toggle, panel, shouldExpand);
        });
    });
});

// MPMA hero cross-fade carousel
document.addEventListener('DOMContentLoaded', function () {
    const heroCarousels = document.querySelectorAll('.mpma-hero-carousel');

    if (!heroCarousels.length) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    heroCarousels.forEach(function (carousel) {
        const slides = Array.from(
            carousel.querySelectorAll('.mpma-hero-carousel__slides .mpma-hero-carousel__slide')
        );

        if (!slides.length) {
            return;
        }

        const autoplayEnabled = carousel.dataset.autoplay === '1' && !prefersReducedMotion;
        const animationSpeed = Number.parseInt(carousel.dataset.animationSpeed || '1200', 10);
        const animationDelay = Number.parseInt(carousel.dataset.animationDelay || '5000', 10);
        const safeSpeed = Number.isFinite(animationSpeed) ? Math.max(150, animationSpeed) : 1200;
        const safeDelay = Number.isFinite(animationDelay) ? Math.max(safeSpeed + 350, animationDelay) : 5000;

        let activeIndex = 0;
        let rafId = null;
        let nextSwitchAt = null;

        const setActiveSlide = function (index) {
            slides.forEach(function (slide, slideIndex) {
                const isActive = slideIndex === index;
                slide.classList.toggle('is-active', isActive);
                slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
            });
        };

        const goToNextSlide = function () {
            activeIndex = (activeIndex + 1) % slides.length;
            setActiveSlide(activeIndex);
        };

        const stopAutoplay = function () {
            if (!rafId) {
                return;
            }

            window.cancelAnimationFrame(rafId);
            rafId = null;
        };

        const stepAutoplay = function (now) {
            if (!autoplayEnabled || slides.length < 2) {
                rafId = null;
                return;
            }

            if (nextSwitchAt === null) {
                nextSwitchAt = now + safeDelay;
            }

            if (now >= nextSwitchAt) {
                goToNextSlide();

                // Keep cadence stable even if a frame is delayed.
                while (now >= nextSwitchAt) {
                    nextSwitchAt += safeDelay;
                }
            }

            rafId = window.requestAnimationFrame(stepAutoplay);
        };

        const startAutoplay = function (resetClock) {
            if (!autoplayEnabled || slides.length < 2 || rafId) {
                return;
            }

            if (resetClock || nextSwitchAt === null) {
                nextSwitchAt = null;
            }

            rafId = window.requestAnimationFrame(stepAutoplay);
        };

        carousel.style.setProperty('--mpma-hero-animation-speed', `${safeSpeed}ms`);
        setActiveSlide(activeIndex);
        carousel.classList.add('is-initialized');
        startAutoplay(true);

        carousel.addEventListener('focusin', stopAutoplay);
        carousel.addEventListener('focusout', function () {
            window.requestAnimationFrame(function () {
                if (!carousel.contains(document.activeElement)) {
                    startAutoplay(false);
                }
            });
        });
    });
});

// MPMA homepage publishing badge reveal on scroll
document.addEventListener('DOMContentLoaded', function () {
    const publishingSections = document.querySelectorAll('.mpma-homepage-publishing');

    if (!publishingSections.length || typeof window.IntersectionObserver !== 'function') {
        return;
    }

    const observer = new IntersectionObserver(
        function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-in-view');
                observer.unobserve(entry.target);
            });
        },
        {
            threshold: 0.25,
            rootMargin: '0px 0px -10% 0px',
        }
    );

    publishingSections.forEach(function (section) {
        observer.observe(section);
    });
});

// MPMA internal membership list interactions
document.addEventListener('DOMContentLoaded', function () {
    const membershipLists = document.querySelectorAll('[data-membership-list]');
    const fadeDuration = 220;

    if (!membershipLists.length) {
        return;
    }

    const resetPanelState = function (panel, isActive) {
        panel.classList.toggle('is-visible', isActive);
        panel.classList.toggle('is-active', isActive);
        panel.classList.remove('is-leaving', 'is-measuring');
        panel.setAttribute('aria-hidden', isActive ? 'false' : 'true');
    };

    const clearContainerTransition = function (container) {
        if (!container) {
            return;
        }

        if (container._membershipFadeTimeout) {
            window.clearTimeout(container._membershipFadeTimeout);
            delete container._membershipFadeTimeout;
        }

        if (container._membershipFadeFrame) {
            window.cancelAnimationFrame(container._membershipFadeFrame);
            delete container._membershipFadeFrame;
        }

        container.style.removeProperty('height');
        container.style.removeProperty('overflow');
    };

    const showPanel = function (container, panels, nextPanel, immediate) {
        if (!nextPanel) {
            return;
        }

        const currentPanel = Array.from(panels).find(function (panel) {
            return panel.classList.contains('is-active');
        });

        clearContainerTransition(container);

        if (!currentPanel || currentPanel === nextPanel || !container || immediate) {
            panels.forEach(function (panel) {
                resetPanelState(panel, panel === nextPanel);
            });

            return;
        }

        nextPanel.classList.add('is-measuring');
        const nextHeight = nextPanel.offsetHeight;
        nextPanel.classList.remove('is-measuring');

        const currentHeight = currentPanel.offsetHeight;
        container.style.height = currentHeight + 'px';
        container.style.overflow = 'hidden';

        currentPanel.classList.add('is-visible', 'is-active', 'is-leaving');
        currentPanel.setAttribute('aria-hidden', 'true');
        nextPanel.classList.add('is-visible');
        nextPanel.classList.remove('is-leaving');
        nextPanel.setAttribute('aria-hidden', 'false');

        void container.offsetHeight;

        container._membershipFadeFrame = window.requestAnimationFrame(function () {
            currentPanel.classList.remove('is-active');
            nextPanel.classList.add('is-active');
            container.style.height = nextHeight + 'px';
            delete container._membershipFadeFrame;
        });

        container._membershipFadeTimeout = window.setTimeout(function () {
            panels.forEach(function (panel) {
                resetPanelState(panel, panel === nextPanel);
            });

            clearContainerTransition(container);
        }, fadeDuration);
    };

    const updateButtonState = function (buttons, nextButton) {
        buttons.forEach(function (button) {
            const isActive = button === nextButton;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
    };

    const activateFirstFilter = function (scope, immediate) {
        const filterButtons = scope.querySelectorAll('[data-membership-filter-toggle]');
        const filterPanels = scope.querySelectorAll('[data-membership-filter-panel]');
        const filterPanelContainer = scope.querySelector('.mpma-membership-list__filter-panels');

        if (!filterButtons.length || !filterPanels.length) {
            if (filterPanelContainer) {
                filterPanelContainer.style.removeProperty('height');
            }
            return;
        }

        updateButtonState(filterButtons, filterButtons[0]);
        showPanel(filterPanelContainer, filterPanels, filterPanels[0], immediate);
    };

    const activateSection = function (orgPanel, sectionSlug, immediate) {
        const sectionButtons = orgPanel.querySelectorAll('[data-membership-section-toggle]');
        const sectionPanels = orgPanel.querySelectorAll('[data-membership-section-panel]');
        const sectionPanelContainer = orgPanel.querySelector('.mpma-membership-list__section-panels');
        const targetButton = orgPanel.querySelector('[data-membership-section-toggle="' + CSS.escape(sectionSlug) + '"]');
        const targetPanel = orgPanel.querySelector('[data-membership-section-panel="' + CSS.escape(sectionSlug) + '"]');

        if (!targetButton || !targetPanel) {
            return;
        }

        updateButtonState(sectionButtons, targetButton);
        activateFirstFilter(targetPanel, true);
        showPanel(sectionPanelContainer, sectionPanels, targetPanel, immediate);
    };

    const activateOrganization = function (block, orgSlug, immediate) {
        const orgButtons = block.querySelectorAll('[data-membership-org-toggle]');
        const orgPanels = block.querySelectorAll('[data-membership-org-panel]');
        const orgPanelContainer = block.querySelector('.mpma-membership-list__org-panels');
        const targetButton = block.querySelector('[data-membership-org-toggle="' + CSS.escape(orgSlug) + '"]');
        const targetPanel = block.querySelector('[data-membership-org-panel="' + CSS.escape(orgSlug) + '"]');

        if (!targetButton || !targetPanel) {
            return;
        }

        updateButtonState(orgButtons, targetButton);
        const firstSectionButton = targetPanel.querySelector('[data-membership-section-toggle]');
        if (firstSectionButton) {
            activateSection(targetPanel, firstSectionButton.getAttribute('data-membership-section-toggle'), true);
        }

        showPanel(orgPanelContainer, orgPanels, targetPanel, immediate);
    };

    membershipLists.forEach(function (block) {
        const orgButtons = block.querySelectorAll('[data-membership-org-toggle]');
        const orgPanels = block.querySelectorAll('[data-membership-org-panel]');
        const orgPanelContainer = block.querySelector('.mpma-membership-list__org-panels');

        orgButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                activateOrganization(block, button.getAttribute('data-membership-org-toggle'), false);
            });
        });

        orgPanels.forEach(function (orgPanel) {
            const sectionButtons = orgPanel.querySelectorAll('[data-membership-section-toggle]');
            const sectionPanels = orgPanel.querySelectorAll('[data-membership-section-panel]');
            const sectionPanelContainer = orgPanel.querySelector('.mpma-membership-list__section-panels');

            sectionButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    activateSection(orgPanel, button.getAttribute('data-membership-section-toggle'), false);
                });
            });

            const filterButtons = orgPanel.querySelectorAll('[data-membership-filter-toggle]');
            filterButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const sectionPanel = button.closest('[data-membership-section-panel]');
                    if (!sectionPanel) {
                        return;
                    }

                    const peerButtons = sectionPanel.querySelectorAll('[data-membership-filter-toggle]');
                    const peerPanels = sectionPanel.querySelectorAll('[data-membership-filter-panel]');
                    const targetPanel = sectionPanel.querySelector('[data-membership-filter-panel="' + CSS.escape(button.getAttribute('data-membership-filter-toggle')) + '"]');

                    if (!targetPanel) {
                        return;
                    }

                    updateButtonState(peerButtons, button);
                    const filterPanelContainer = sectionPanel.querySelector('.mpma-membership-list__filter-panels');
                    showPanel(filterPanelContainer, peerPanels, targetPanel, false);
                });
            });

            sectionPanels.forEach(function (sectionPanel) {
                const filterPanelContainer = sectionPanel.querySelector('.mpma-membership-list__filter-panels');
                if (filterPanelContainer) {
                    clearContainerTransition(filterPanelContainer);
                }
            });

            if (sectionPanelContainer) {
                clearContainerTransition(sectionPanelContainer);
            }
        });

        if (orgPanelContainer) {
            clearContainerTransition(orgPanelContainer);
        }

        const initiallyActiveOrg = block.querySelector('[data-membership-org-toggle].is-active');
        if (initiallyActiveOrg) {
            activateOrganization(block, initiallyActiveOrg.getAttribute('data-membership-org-toggle'), true);
        } else if (orgButtons[0]) {
            activateOrganization(block, orgButtons[0].getAttribute('data-membership-org-toggle'), true);
        }

        window.addEventListener('resize', function () {
            if (orgPanelContainer) {
                clearContainerTransition(orgPanelContainer);
            }

            orgPanels.forEach(function (orgPanel) {
                const sectionPanels = orgPanel.querySelectorAll('[data-membership-section-panel]');
                const sectionPanelContainer = orgPanel.querySelector('.mpma-membership-list__section-panels');
                if (sectionPanelContainer) {
                    clearContainerTransition(sectionPanelContainer);
                }

                sectionPanels.forEach(function (sectionPanel) {
                    const filterPanelContainer = sectionPanel.querySelector('.mpma-membership-list__filter-panels');
                    if (filterPanelContainer) {
                        clearContainerTransition(filterPanelContainer);
                    }
                });
            });
        });
    });
});

// Remove empty legacy Genesis columns blocks that only contribute stray vertical space
document.addEventListener('DOMContentLoaded', function () {
    const legacyColumns = document.querySelectorAll('.wp-block-genesis-blocks-gb-columns');

    legacyColumns.forEach(function (block) {
        const columnInners = block.querySelectorAll('.gb-block-layout-column-inner');
        if (!columnInners.length) {
            return;
        }

        const hasVisibleContent = Array.from(columnInners).some(function (inner) {
            return inner.textContent.trim() !== '' || inner.querySelector('img, picture, video, iframe, embed, object, svg, form, input, button, a, ul, ol, table, blockquote, hr');
        });

        if (!hasVisibleContent) {
            block.remove();
        }
    });
});

// MPMA internal card hover/touch crossfade
document.addEventListener('DOMContentLoaded', function () {
    const cards = document.querySelectorAll('[data-mpma-internal-card]');

    if (!cards.length) {
        return;
    }

    const getInteractiveTarget = function (target) {
        if (!target) {
            return null;
        }

        const element = target.nodeType === 1 ? target : target.parentElement;
        if (!element || typeof element.closest !== 'function') {
            return null;
        }

        return element.closest('a, button, input, textarea, select, summary, .wp-element-button, .wp-block-button__link');
    };

    const isInteractiveTarget = function (target) {
        return !!getInteractiveTarget(target);
    };

    cards.forEach(function (card) {
        const surface = card.querySelector('[data-internal-card-surface]');
        const backSide = card.querySelector('[data-internal-card-side="back"]');

        if (!surface || !backSide) {
            return;
        }

        card.setAttribute('data-has-back', '1');

        const openTouchState = function () {
            cards.forEach(function (otherCard) {
                if (otherCard !== card) {
                    otherCard.classList.remove('is-flipped-touch');
                }
            });
            card.classList.add('is-flipped-touch');
        };

        const closeTouchState = function () {
            card.classList.remove('is-flipped-touch');
        };

        surface.addEventListener('click', function (event) {
            const isCoarsePointer = window.matchMedia('(hover: none), (pointer: coarse)').matches;

            if (!isCoarsePointer || isInteractiveTarget(event.target)) {
                return;
            }

            event.preventDefault();

            if (card.classList.contains('is-flipped-touch')) {
                closeTouchState();
                return;
            }

            openTouchState();
        });

        surface.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            if (isInteractiveTarget(event.target)) {
                return;
            }

            event.preventDefault();
            if (card.classList.contains('is-flipped-touch')) {
                closeTouchState();
                return;
            }

            openTouchState();
        });

        surface.addEventListener('mouseleave', function () {
            card.classList.remove('is-flipped-touch');
        });
    });

    document.addEventListener('click', function (event) {
        const isCoarsePointer = window.matchMedia('(hover: none), (pointer: coarse)').matches;

        if (!isCoarsePointer) {
            return;
        }

        if (event.target && event.target.closest && event.target.closest('[data-mpma-internal-card]')) {
            return;
        }

        cards.forEach(function (card) {
            card.classList.remove('is-flipped-touch');
        });
    });
});

// MPMA internal card tile carousel
document.addEventListener('DOMContentLoaded', function () {
    const cardTiles = document.querySelectorAll('[data-mpma-internal-card-tile][data-carousel="1"]');

    if (!cardTiles.length) {
        return;
    }

    cardTiles.forEach(function (tile) {
        const viewport = tile.querySelector('[data-card-tile-viewport]');
        const track = tile.querySelector('[data-card-tile-track]');
        const prevButton = tile.querySelector('[data-card-tile-prev]');
        const nextButton = tile.querySelector('[data-card-tile-next]');

        if (!viewport || !track) {
            return;
        }

        const slides = Array.from(track.children).filter(function (child) {
            return child.nodeType === 1;
        });

        if (!slides.length) {
            return;
        }

        const loopEnabled = tile.getAttribute('data-loop') !== '0';
        let currentIndex = 0;

        const getVisibleCount = function () {
            if (window.matchMedia('(max-width: 959px)').matches) {
                return 1;
            }

            const parsed = Number.parseInt(tile.getAttribute('data-viewport-cards') || '1', 10);
            return Math.max(1, Math.min(slides.length, parsed || 1));
        };

        const getMaxIndex = function () {
            return Math.max(0, slides.length - getVisibleCount());
        };

        const syncButtons = function () {
            if (!prevButton || !nextButton) {
                return;
            }

            const hideButtons = slides.length <= getVisibleCount() || window.matchMedia('(max-width: 959px)').matches;
            prevButton.classList.toggle('is-hidden', hideButtons);
            nextButton.classList.toggle('is-hidden', hideButtons);

            if (hideButtons) {
                prevButton.disabled = true;
                nextButton.disabled = true;
                return;
            }

            if (loopEnabled) {
                prevButton.disabled = false;
                nextButton.disabled = false;
                return;
            }

            prevButton.disabled = currentIndex <= 0;
            nextButton.disabled = currentIndex >= getMaxIndex();
        };

        const goToIndex = function (nextIndex) {
            const maxIndex = getMaxIndex();

            if (loopEnabled) {
                if (nextIndex < 0) {
                    nextIndex = maxIndex;
                } else if (nextIndex > maxIndex) {
                    nextIndex = 0;
                }
            } else {
                nextIndex = Math.max(0, Math.min(maxIndex, nextIndex));
            }

            currentIndex = nextIndex;

            const targetSlide = slides[currentIndex];
            const translateX = targetSlide ? targetSlide.offsetLeft : 0;
            track.style.transform = 'translateX(' + (-translateX) + 'px)';
            syncButtons();
        };

        let touchStartX = null;

        viewport.addEventListener('touchstart', function (event) {
            if (!event.touches || !event.touches.length) {
                return;
            }

            touchStartX = event.touches[0].clientX;
        }, { passive: true });

        viewport.addEventListener('touchend', function (event) {
            if (touchStartX === null || !event.changedTouches || !event.changedTouches.length) {
                touchStartX = null;
                return;
            }

            const deltaX = event.changedTouches[0].clientX - touchStartX;
            touchStartX = null;

            if (Math.abs(deltaX) < 40) {
                return;
            }

            goToIndex(currentIndex + (deltaX < 0 ? 1 : -1));
        }, { passive: true });

        if (prevButton) {
            prevButton.addEventListener('click', function () {
                goToIndex(currentIndex - 1);
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', function () {
                goToIndex(currentIndex + 1);
            });
        }

        window.addEventListener('resize', function () {
            goToIndex(Math.min(currentIndex, getMaxIndex()));
        });

        goToIndex(0);
    });
});

// MPMA internal full width carousel
document.addEventListener('DOMContentLoaded', function () {
    const carousels = document.querySelectorAll('[data-mpma-internal-full-width-carousel]');

    if (!carousels.length) {
        return;
    }

    carousels.forEach(function (carousel) {
        const viewport = carousel.querySelector('[data-full-width-carousel-viewport]');
        const track = carousel.querySelector('[data-full-width-carousel-track]');
        const slides = Array.from(carousel.querySelectorAll('[data-full-width-carousel-slide]'));
        const navButtons = Array.from(carousel.querySelectorAll('[data-full-width-carousel-nav]'));
        const prevButton = carousel.querySelector('[data-full-width-carousel-prev]');
        const nextButton = carousel.querySelector('[data-full-width-carousel-next]');
        const navViewport = carousel.querySelector('[data-full-width-carousel-nav-viewport]');
        const navTrack = carousel.querySelector('[data-full-width-carousel-nav-track]');
        const computedSpeed = Number.parseFloat(getComputedStyle(carousel).getPropertyValue('--mpma-internal-full-width-carousel-speed')) || 400;
        const equalPanelHeights = carousel.dataset.equalPanelHeights !== '0';
        const navOverflowEnabled = carousel.dataset.navOverflow !== '0';
        const visibleLabelCount = Math.max(1, Math.min(navButtons.length || 1, Number.parseInt(carousel.dataset.navVisibleLabels || '4', 10) || 4));
        const navContainer = carousel.querySelector('.mpma-internal-full-width-carousel__nav');
        const isAwards = carousel.classList.contains('is-awards');
        let currentIndex = isAwards ? Math.max(0, slides.length - 1) : 0;
        let cleanupTimer = null;

        if (!viewport || !track || slides.length < 1) {
            return;
        }

        const normalizeIndex = function (index) {
            const lastIndex = slides.length - 1;

            if (index < 0) {
                return lastIndex;
            }

            if (index > lastIndex) {
                return 0;
            }

            return index;
        };

        const syncNav = function () {
            navButtons.forEach(function (button, index) {
                const isActive = index === currentIndex;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
        };

        const syncNavViewport = function () {
            if (!navOverflowEnabled || !navViewport || !navTrack || !navButtons.length || !navContainer) {
                if (navContainer) {
                    navContainer.style.width = '';
                    navContainer.style.maxWidth = '';
                }

                if (navViewport) {
                    navViewport.style.width = '';
                    navViewport.style.maxWidth = '';
                    navViewport.style.setProperty('--mpma-internal-full-width-carousel-nav-viewport-width', '');
                }

                if (navTrack) {
                    navTrack.style.paddingLeft = '';
                    navTrack.style.paddingRight = '';
                    navTrack.style.transform = '';
                }
                return;
            }

            const trackStyles = window.getComputedStyle(navTrack);
            const navStyles = window.getComputedStyle(navContainer);
            const navWrapper = navContainer.parentElement;
            const trackGap = Number.parseFloat(trackStyles.columnGap || trackStyles.gap || '0') || 0;
            const navGap = Number.parseFloat(navStyles.columnGap || navStyles.gap || '0') || 0;
            const prevWidth = prevButton ? prevButton.offsetWidth : 0;
            const nextWidth = nextButton ? nextButton.offsetWidth : 0;
            const widestLabel = navButtons.reduce(function (maxWidth, button) {
                return Math.max(maxWidth, button.offsetWidth);
            }, 0);
            const coreWidth = (widestLabel * visibleLabelCount) + (trackGap * Math.max(0, visibleLabelCount - 1));
            const peekBuffer = coreWidth * 0.2;
            const desiredWidth = coreWidth + (peekBuffer * 2);
            const arrowAllowance = prevWidth + nextWidth + (navGap * 2);
            const availableNavWidth = Math.max(0, (navWrapper ? navWrapper.clientWidth : navContainer.clientWidth) || 0);
            const availableWidth = Math.max(
                0,
                availableNavWidth - arrowAllowance
            );
            const nextViewportWidth = Math.min(desiredWidth, navTrack.scrollWidth, availableWidth || desiredWidth);
            const totalNavWidth = Math.min(availableNavWidth || (nextViewportWidth + arrowAllowance), nextViewportWidth + arrowAllowance);
            const edgePadding = Math.max(0, (nextViewportWidth - widestLabel) / 2);

            navContainer.style.width = totalNavWidth > 0 ? totalNavWidth + 'px' : '';
            navContainer.style.maxWidth = '100%';

            navViewport.style.width = nextViewportWidth > 0 ? nextViewportWidth + 'px' : '';
            navViewport.style.maxWidth = '100%';
            navViewport.style.setProperty('--mpma-internal-full-width-carousel-nav-viewport-width', nextViewportWidth > 0 ? nextViewportWidth + 'px' : '');
            navTrack.style.paddingLeft = edgePadding > 0 ? edgePadding + 'px' : '';
            navTrack.style.paddingRight = edgePadding > 0 ? edgePadding + 'px' : '';
        };

        const syncNavPosition = function (index, immediate) {
            if (!navOverflowEnabled || !navViewport || !navTrack || !navButtons.length) {
                return;
            }

            const targetButton = navButtons[index];

            if (!targetButton) {
                return;
            }

            const viewportWidth = navViewport.offsetWidth;
            const trackWidth = navTrack.scrollWidth;

            if (!viewportWidth || trackWidth <= viewportWidth) {
                navTrack.style.transform = 'translateX(0px)';
                return;
            }

            const targetCenter = targetButton.offsetLeft + (targetButton.offsetWidth / 2);
            const maxOffset = Math.max(0, trackWidth - viewportWidth);
            const nextOffset = Math.min(maxOffset, Math.max(0, targetCenter - (viewportWidth / 2)));

            if (immediate) {
                const previousTransition = navTrack.style.transition;
                navTrack.style.transition = 'none';
                navTrack.style.transform = 'translateX(' + (-nextOffset) + 'px)';
                navTrack.offsetHeight;
                navTrack.style.transition = previousTransition;
                return;
            }

            navTrack.style.transform = 'translateX(' + (-nextOffset) + 'px)';
        };

        const getSlideHeight = function (slide) {
            if (!slide) {
                return 0;
            }

            slide.classList.add('is-measuring');
            const slideInner = slide.querySelector('.mpma-internal-full-width-carousel__slide-inner');
            const measuredNode = slideInner || slide;
            const rectHeight = measuredNode.getBoundingClientRect().height || 0;
            const offsetHeight = measuredNode.offsetHeight || 0;
            const scrollHeight = measuredNode.scrollHeight || 0;
            slide.classList.remove('is-measuring');

            return Math.max(rectHeight, offsetHeight, scrollHeight);
        };

        const getTrackHeight = function (index) {
            if (!equalPanelHeights) {
                return getSlideHeight(slides[normalizeIndex(index)]);
            }

            return slides.reduce(function (maxHeight, slide) {
                return Math.max(maxHeight, getSlideHeight(slide));
            }, 0);
        };

        const syncHeight = function (index, immediate) {
            if (!equalPanelHeights) {
                track.style.height = '';
                viewport.style.height = '';
                viewport.style.minHeight = '';
            }

            const nextHeight = getTrackHeight(index);
            const applyHeight = function () {
                track.style.height = nextHeight + 'px';
                viewport.style.height = nextHeight + 'px';

                if (equalPanelHeights) {
                    viewport.style.minHeight = nextHeight + 'px';
                } else {
                    viewport.style.minHeight = '';
                }
            };

            if (immediate) {
                applyHeight();
                return;
            }

            requestAnimationFrame(function () {
                applyHeight();
            });
        };

        let scheduledSyncFrame = null;
        let scheduledSyncTimeout = null;

        const scheduleLayoutSync = function () {
            if (scheduledSyncFrame) {
                window.cancelAnimationFrame(scheduledSyncFrame);
            }

            if (scheduledSyncTimeout) {
                window.clearTimeout(scheduledSyncTimeout);
            }

            syncNavViewport();

            scheduledSyncFrame = window.requestAnimationFrame(function () {
                syncNavPosition(currentIndex, true);
                syncHeight(currentIndex, true);

                scheduledSyncFrame = window.requestAnimationFrame(function () {
                    syncNavPosition(currentIndex, true);
                    syncHeight(currentIndex, true);
                    scheduledSyncFrame = null;
                });
            });

            scheduledSyncTimeout = window.setTimeout(function () {
                syncNavViewport();
                syncNavPosition(currentIndex, true);
                syncHeight(currentIndex, true);
                scheduledSyncTimeout = null;
            }, computedSpeed);
        };

        const activateSlide = function (index, immediate) {
            const nextIndex = normalizeIndex(index);
            const currentSlide = slides[currentIndex];
            const nextSlide = slides[nextIndex];

            if (!nextSlide) {
                return;
            }

            if (cleanupTimer) {
                window.clearTimeout(cleanupTimer);
                cleanupTimer = null;
            }

            if (immediate || currentSlide === nextSlide) {
                slides.forEach(function (slide, slideIndex) {
                    const isActive = slideIndex === nextIndex;
                    slide.classList.toggle('is-active', isActive);
                    slide.classList.remove('is-leaving');
                    slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
                });
                currentIndex = nextIndex;
                syncNav();
                syncNavPosition(nextIndex, true);
                syncHeight(nextIndex, true);
                return;
            }

            currentSlide.classList.add('is-leaving');
            currentSlide.classList.remove('is-active');
            currentSlide.setAttribute('aria-hidden', 'true');
            nextSlide.classList.add('is-active');
            nextSlide.classList.remove('is-leaving');
            nextSlide.setAttribute('aria-hidden', 'false');

            currentIndex = nextIndex;
            syncNav();
            syncNavPosition(nextIndex, false);
            syncHeight(nextIndex, false);

            cleanupTimer = window.setTimeout(function () {
                slides.forEach(function (slide, slideIndex) {
                    if (slideIndex !== currentIndex) {
                        slide.classList.remove('is-active');
                        slide.classList.remove('is-leaving');
                        slide.setAttribute('aria-hidden', 'true');
                    }
                });

                syncHeight(currentIndex, true);
            }, computedSpeed);
        };

        navButtons.forEach(function (button, index) {
            button.addEventListener('click', function () {
                activateSlide(index, false);
            });
        });

        if (prevButton) {
            prevButton.addEventListener('click', function () {
                activateSlide(currentIndex - 1, false);
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', function () {
                activateSlide(currentIndex + 1, false);
            });
        }

        if (typeof window.ResizeObserver === 'function') {
            const resizeObserver = new window.ResizeObserver(function () {
                scheduleLayoutSync();
            });

            resizeObserver.observe(carousel);
            slides.forEach(function (slide) {
                resizeObserver.observe(slide);

                const slideInner = slide.querySelector('.mpma-internal-full-width-carousel__slide-inner');

                if (slideInner) {
                    resizeObserver.observe(slideInner);
                }
            });
        }

        window.addEventListener('resize', function () {
            scheduleLayoutSync();
        });

        activateSlide(currentIndex, true);
        scheduleLayoutSync();

        if (navOverflowEnabled) {
            window.requestAnimationFrame(function () {
                carousel.classList.add('is-nav-ready');
            });
        }

        window.addEventListener('load', function () {
            scheduleLayoutSync();
        }, { once: true });
    });
});
