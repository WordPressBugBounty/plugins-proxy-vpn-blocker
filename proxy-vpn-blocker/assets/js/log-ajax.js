jQuery(document).ready(function($) {
    // Initial page and per_page count
    let currentPage = 1;
    let isRefreshing = false; // Flag to indicate whether a refresh is in progress
    let refreshInterval; // Variable to hold the interval ID
    let isMouseInGrid = false; // Flag to track if the mouse is inside .angry-grid

    $('.log_content').on('click', '.angry-grid', function () {
        // Prevent toggling if currently refreshing
        if (isRefreshing) return;

        const extraDataRow = $(this).next('.extra-data-row');
        const semiCircle = $(this).find('.semi-circle');

        // Check if the extra-data-row is currently visible before toggling
        if (extraDataRow.is(':visible')) {
            // If visible, hide the semi-circle with a fade-out effect
            semiCircle.fadeOut("fast");
        } else {
            // If hidden, show the semi-circle with a fade-in effect
            semiCircle.fadeIn("fast");
        }

        // Toggle the visibility of the extra-data-row with a slide effect
        extraDataRow.slideToggle("slow");
    });

    // Function to load logs via AJAX
    function loadLogs(page = 1) {
        if (isRefreshing) return; // Prevent loading if currently refreshing
        isRefreshing = true; // Set flag to true during refresh

        // Step 1: Capture the IDs of currently open rows
        const openRows = [];
        $('.extra-data-row:visible').each(function () {
            const rowId = $(this).prev('.angry-grid').attr('data-log-id'); // Get the unique log ID
            openRows.push(rowId); // Store the ID of the open rows
        });

        $.ajax({
            url: pvb_action_logs.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'fetch_pvb_logs',
                nonce: pvb_action_logs.nonce,
                page: page,
            },
            success: function(response) {
                if (response.success) {
                    // Step 2: Clear previous logs
                    $('.log_content').empty();

                    // Step 3: Loop through the logs and display them
                    $.each(response.data, function(index, log) {
                        const imagePath = pvb_action_logs.flags_url;
                        const countryCode = log.country_iso;
                        const flagPath = `${imagePath}${countryCode}.png`;

                        let proxyType = log.detected_type === "VPN" ? 'type_vpn' : 'type_others';

                        let logHtml = 
                            '<div class="angry-grid" data-log-id="' + log.id + '">' +  // Use log.id as a unique identifier
                                '<div class="item-0" title="' + log.blocked_at_wp + '"><span class="space">' + timeAgo(log.blocked_at) + '</span></div>' +
                                '<div class="item-1"><span><a href="https://proxycheck.io/threats/' + log.ip_address + '" target="_blank">' + log.ip_address + '</a></span></div>' +
                                '<div class="item-2"><span><img class="country-flag" alt="' + log.country + ' flag" title="' + log.country + '" src="' + flagPath + '"><span>' + log.country + ' (' + log.country_iso + ')</span></span></div>' +
                                '<div class="item-3"><span>' + log.risk_score + '</span></div>' +
                                '<div class="semi-circle" style="display: none;"><i class="fa-fw fa-solid fa-angle-down"></i></div>' + // Initially hidden
                            '</div>' +
                            '<div class="extra-data-row">' +
                                '<div class="extra-data-row-content">' +
                                    '<span class="space ipinflux_' + proxyType + '">' + log.detected_type + '</span>' +
                                    '<span class="space ipinflux_blocked_on_url" alt="' + log.blocked_url + '">URL: ' + log.blocked_url + '</span>' +
                                '</div>' +
                            '</div>';

                        // Append the log data to the .log_content container
                        $('.log_content').append(logHtml);
                    });

                    // Step 4: Restore the previously open rows after logs are loaded
                    openRows.forEach(function (rowId) {
                        const $angryGrid = $(`.angry-grid[data-log-id="${rowId}"]`);
                        if ($angryGrid.length) {
                            $angryGrid.next('.extra-data-row').show(); // Show the extra data row
                            $angryGrid.find('.semi-circle').fadeIn("slow"); // Show the semi-circle
                        }
                    });

                    // Step 5: Update the current page
                    currentPage = page;
                } else {
                    $('#log-container').html('<span>No logs found</span>');
                }
            },
            error: function() {
                $('#log-container').html('<span>Error loading logs</span>');
            },
            complete: function() {
                isRefreshing = false; // Reset flag when refresh is complete
            }
        });
    }

    // Initial load
    loadLogs(currentPage);

    // Handle pagination - Next and Previous buttons
    $('#prev-page').on('click', function() {
        loadLogs(currentPage + 1);
    });

    $('#next-page').on('click', function() {
        if (currentPage > 1) {
            loadLogs(currentPage - 1);
        }
    });

    // Mouse events to control refresh
    $('.angry-grid').on('mouseenter', function() {
        isMouseInGrid = true; // Set the flag when mouse enters
    }).on('mouseleave', function() {
        isMouseInGrid = false; // Reset the flag when mouse leaves
    });

    // Real-time update every 15 seconds (adjust as needed)
    refreshInterval = setInterval(function() {
        if (!isMouseInGrid) { // Only refresh if mouse is not in .angry-grid
            loadLogs(currentPage);
        }
    }, 15000);  // Every 15 seconds
});

// Function to convert timestamp to time ago format relative to the user's timezone
function timeAgo(timestamp) {
    const targetDate = new Date(timestamp + "Z");
    const now = new Date();
    const secondsAgo = Math.floor((now - targetDate) / 1000);
    
    const minutes = Math.floor(secondsAgo / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    const weeks = Math.floor(days / 7);
    
    // Calculate months more accurately
    let months = (now.getMonth() + 12 * now.getFullYear()) - 
                (targetDate.getMonth() + 12 * targetDate.getFullYear());
    
    // Adjust for month boundaries
    if (now.getDate() < targetDate.getDate()) {
        months--;
    }
    
    const years = Math.floor(months / 12);
    months = months % 12;

    if (secondsAgo < 1) {
        return "Now";
    } else if (secondsAgo < 60) {
        return `${secondsAgo} second${secondsAgo === 1 ? '' : 's'} ago`;
    } else if (minutes < 60) {
        return `${minutes} minute${minutes === 1 ? '' : 's'} ago`;
    } else if (hours < 24) {
        return `${hours} hour${hours === 1 ? '' : 's'} ago`;
    } else if (days < 7) {
        return `${days} day${days === 1 ? '' : 's'} ago`;
    } else if (weeks < 4) {
        return `${weeks} week${weeks === 1 ? '' : 's'} ago`;
    } else if (months === 0 && days >= 28) {
        return "1 month ago";
    } else if (months < 12) {
        return `${months} month${months === 1 ? '' : 's'} ago`;
    } else {
        return `${years} year${years === 1 ? '' : 's'} ago`;
    }
}

function convertToLocalTime(utcTimestamp) {
    // Create a new Date object with the UTC timestamp
    const date = new Date(utcTimestamp);

    // Format the date to the local time as a string
    const localTimeString = date.toLocaleString();

    return localTimeString;
}