jQuery(document).ready(function($) {
    'use strict';

    // Handle clicks on IP filter icons
    $(document).on('click', '.pvb-ip-filter-icon', function(e) {
        e.preventDefault();

        var ipAddress = $(this).data('ip');
        if (!ipAddress) {
            return;
        }

        // Find the search input field in the users table
        var searchInput = $('input[name="s"]');
        if (searchInput.length === 0) {
            return;
        }

        // Set the IP address in the search field
        searchInput.val(ipAddress);

        // Find and submit the search form
        var searchForm = searchInput.closest('form');
        if (searchForm.length > 0) {
            searchForm.submit();
        } else {
            // Fallback: try to submit the form containing the search input
            searchInput.closest('form, .search-form').find('input[type="submit"], button[type="submit"]').first().click();
        }
    });
});
