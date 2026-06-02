/**
 * Alpha Engine - Main App Script
 * Handles header interactions, sticky states, and mobile touch submenus.
 */
document.addEventListener('DOMContentLoaded', () => {
    // 1. Sticky Header Functionality
    const telhado = document.querySelector('.telhado');
    if (telhado) {
        const handleScroll = () => {
            if (window.scrollY > 50) {
                telhado.classList.add('sticky');
            } else {
                telhado.classList.remove('sticky');
            }
        };
        
        // Run once on load to handle initial position
        handleScroll();
        
        // Listen to window scroll
        window.addEventListener('scroll', handleScroll);
    }

    // 2. Touch/Click Navigation support for recursive dropdown menu on mobile devices
    const menuButton = document.querySelector('.menu-button-left');
    const mainDropdown = document.querySelector('.dropdown-menu-recursive');

    if (menuButton && mainDropdown) {
        menuButton.addEventListener('click', (e) => {
            if (window.innerWidth < 992) {
                e.preventDefault();
                e.stopPropagation();
                mainDropdown.classList.toggle('show');
            }
        });
    }

    const dropdownItems = document.querySelectorAll('.dropdown-menu-recursive li');
    dropdownItems.forEach(item => {
        const submenu = item.querySelector('.submenu');
        if (submenu) {
            // Find parent link
            const link = item.querySelector('a');
            if (link) {
                link.addEventListener('click', (e) => {
                    // Only apply mobile touch adjustments on smaller viewports (lg breakpoint is 992px)
                    if (window.innerWidth < 992) {
                        const isOpen = submenu.classList.contains('show');
                        
                        // Close other submenus at the same level
                        const parentUl = item.closest('ul');
                        if (parentUl) {
                            const openSubmenus = parentUl.querySelectorAll('.submenu');
                            openSubmenus.forEach(sub => {
                                if (sub !== submenu) {
                                    sub.classList.remove('show');
                                }
                            });
                        }
                        
                        if (!isOpen) {
                            e.preventDefault();
                            e.stopPropagation();
                            submenu.classList.add('show');
                        } else {
                            e.preventDefault();
                            e.stopPropagation();
                            submenu.classList.remove('show');
                        }
                    }
                });
            }
        }
    });

    // 3. Close menus when clicking outside the menu container
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.ag-menu-container')) {
            if (mainDropdown) {
                mainDropdown.classList.remove('show');
            }
            const submenus = document.querySelectorAll('.submenu');
            submenus.forEach(sub => {
                sub.classList.remove('show');
            });
        }
    });
});
