// Dark Mode Toggle Functionality
(function () {
    'use strict';

    // Initialize dark mode based on localStorage
    // Initialize dark mode based on localStorage
    function initDarkMode() {
        // Force dark mode as default and only option
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
    }

    // Make functions globally accessible

    window.initDarkMode = initDarkMode;

    // Initialize on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDarkMode);
    } else {
        initDarkMode();
    }
})();


