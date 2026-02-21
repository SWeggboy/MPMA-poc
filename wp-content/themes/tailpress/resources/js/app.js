window.addEventListener('load', function () {
    let mainNavigation = document.getElementById('primary-navigation')
    let mainNavigationToggle = document.getElementById('primary-menu-toggle')

    if(mainNavigation && mainNavigationToggle) {
        const rootStyles = getComputedStyle(document.documentElement)
        const mdBreakpoint = rootStyles.getPropertyValue('--breakpoint-md').trim()

        if (!mdBreakpoint) {
            return
        }

        const desktopMediaQuery = window.matchMedia(`(min-width: ${mdBreakpoint})`)

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

    searchToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (!isExpanded) {
            // Expand
            searchBox.style.width = '180px';
            searchInput.style.opacity = '1';
            searchInput.style.pointerEvents = 'auto';
            searchInput.focus();
            isExpanded = true;
        }
    });

    // Collapse when clicking outside
    document.addEventListener('click', function(e) {
        if (isExpanded && !searchBox.contains(e.target) && !searchInput.value) {
            searchInput.style.opacity = '0';
            searchInput.style.pointerEvents = 'none';
            searchBox.style.width = '38px';
            isExpanded = false;
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

    if (!mobileSecondaryMenu) {
        return;
    }

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

        toggleButton.addEventListener('click', function (event) {
            event.preventDefault();
            const isOpen = item.classList.toggle('is-open');
            toggleButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        link.insertAdjacentElement('afterend', toggleButton);
    });
});
