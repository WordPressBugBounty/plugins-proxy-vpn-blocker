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

        if (extraDataRow.is(':visible')) {
            semiCircle.fadeOut("fast");
        } else {
            semiCircle.fadeIn("fast");
        }

        extraDataRow.slideToggle("slow");
    });

    // Function to update the risk score colors.
    function updateRiskScoreColors() {
        document.querySelectorAll('.item-3 span').forEach(span => {
            const score = parseInt(span.textContent);
            const hue = (1 - score / 100) * 120;
            span.style.backgroundColor = `hsl(${hue}, 80%, 45%)`;
            span.style.color = score > 50 ? 'white' : 'black';
        });
    }

    /**
     * Build a single log row using DOM construction only.
     *
     * No log value is ever passed through innerHTML or string concatenation —
     * every field is assigned via .textContent, .setAttribute(), or a typed
     * DOM property. This means a stored payload such as:
     *   xx" onerror="alert(1)"
     * is treated as a plain string by the browser and never parsed as HTML.
     */
    function buildLogRow(log) {
        const imagePath = pvb_action_logs.flags_url;

        // ── Outer angry-grid wrapper ──────────────────────────────────────────
        const angryGrid = document.createElement('div');
        angryGrid.className = 'angry-grid';
        angryGrid.dataset.logId = log.id; // safe: dataset assignment, not innerHTML

        // item-0 — timestamp
        const item0 = document.createElement('div');
        item0.className = 'item-0';
        item0.title = log.blocked_at_wp;           // .title is a text property, not HTML
        const timeSpan = document.createElement('span');
        timeSpan.className = 'space';
        timeSpan.textContent = timeAgo(log.blocked_at); // textContent never parsed as HTML
        item0.appendChild(timeSpan);
        angryGrid.appendChild(item0);

        // item-1 — IP address link
        const item1 = document.createElement('div');
        item1.className = 'item-1';
        const ipSpan = document.createElement('span');
        const ipLink = document.createElement('a');
        // Build the href by concatenating onto a fixed prefix, then assign
        // as a property — the browser normalises it and won't execute javascript:
        ipLink.href = 'https://proxycheck.io/threats/' + encodeURIComponent(log.ip_address);
        ipLink.target = '_blank';
        ipLink.textContent = log.ip_address;
        ipSpan.appendChild(ipLink);
        item1.appendChild(ipSpan);
        angryGrid.appendChild(item1);

        // item-2 — country flag + name
        const item2 = document.createElement('div');
        item2.className = 'item-2';
        const countrySpan = document.createElement('span');

        const flag = document.createElement('img');
        flag.className = 'country-flag';
        // src is built from a fixed base path + a server-escaped ISO code.
        // Assigning to .src (not innerHTML) means the browser parses it as a
        // URL — a payload like  xx" onerror="...  becomes a broken image URL,
        // not an event handler.
        flag.src = imagePath + log.country_iso + '.png';
        flag.alt = log.country + ' flag'; // .alt is a text property
        flag.title = log.country;         // .title is a text property

        const countryText = document.createElement('span');
        countryText.textContent = log.country + ' (' + log.country_iso + ')';

        countrySpan.appendChild(flag);
        countrySpan.appendChild(countryText);
        item2.appendChild(countrySpan);
        angryGrid.appendChild(item2);

        // item-3 — risk score
        const item3 = document.createElement('div');
        item3.className = 'item-3';
        const riskSpan = document.createElement('span');
        riskSpan.textContent = log.risk_score;
        item3.appendChild(riskSpan);
        angryGrid.appendChild(item3);

        // semi-circle toggle indicator
        const semiCircle = document.createElement('div');
        semiCircle.className = 'semi-circle';
        semiCircle.style.display = 'none';
        const chevron = document.createElement('i');
        chevron.className = 'fa-fw fa-solid fa-angle-down';
        semiCircle.appendChild(chevron);
        angryGrid.appendChild(semiCircle);

        // ── Extra data row ────────────────────────────────────────────────────
        const extraRow = document.createElement('div');
        extraRow.className = 'extra-data-row';
        const extraContent = document.createElement('div');
        extraContent.className = 'extra-data-row-content';

        // Detected types section
        const typesSection = document.createElement('div');
        typesSection.className = 'extra-data-row-section';
        const typeTitle = document.createElement('div');

        if (log.detected_type) {
            const detectedTypes = log.detected_type.split(',').map(t => t.trim());
            typeTitle.className = 'data-row-title';
            typeTitle.textContent = 'Detected Types';
            typesSection.appendChild(typeTitle);

            detectedTypes.forEach(function(type) {
                const typeSpan = document.createElement('span');
                typeSpan.className = 'space ipinflux_type_' + type.toLowerCase();
                typeSpan.textContent = type; // textContent — not innerHTML
                typesSection.appendChild(typeSpan);
            });
        } else {
            typeTitle.className = 'data-row-title';
            typeTitle.textContent = 'Detected Type';
            typesSection.appendChild(typeTitle);

            const fallbackSpan = document.createElement('span');
            const proxyType = log.detected_type === 'VPN' ? 'type_vpn' : 'type_others';
            fallbackSpan.className = 'space ipinflux_type_' + proxyType;
            fallbackSpan.textContent = log.detected_type;
            typesSection.appendChild(fallbackSpan);
        }
        extraContent.appendChild(typesSection);

        // Information section
        const infoSection = document.createElement('div');
        infoSection.className = 'extra-data-row-section';
        const infoTitle = document.createElement('div');
        infoTitle.className = 'data-row-title';
        infoTitle.textContent = 'Information';
        infoSection.appendChild(infoTitle);

        // Blocked URL — textContent only, never injected into an attribute
        const urlSpan = document.createElement('span');
        urlSpan.className = 'space ipinflux_blocked_on_url';
        urlSpan.textContent = 'URL: ' + log.blocked_url;
        infoSection.appendChild(urlSpan);

        // API type
        if (log.api_type) {
            const apiSpan = document.createElement('span');
            apiSpan.className = 'space ipinflux_api_type_used';
            apiSpan.textContent = 'Processed By: ' + log.api_type;
            infoSection.appendChild(apiSpan);
        }

        // Whitelist button
        if (pvb_action_logs.proxycheck_apikey_set === 'yes') {
            const wlDiv = document.createElement('div');
            wlDiv.className = 'log-whitelist-btn';
            const wlBtn = document.createElement('button');
            wlBtn.className = 'add-to-whitelist-btn';
            // Store IP in dataset — never in an HTML attribute via concatenation
            wlBtn.dataset.ip = log.ip_address;
            const wlIcon = document.createElement('i');
            wlIcon.className = 'fa-solid fa-plus';
            wlBtn.appendChild(wlIcon);
            wlBtn.appendChild(document.createTextNode(' Whitelist IP'));
            wlDiv.appendChild(wlBtn);
            infoSection.appendChild(wlDiv);
        }

        extraContent.appendChild(infoSection);
        extraRow.appendChild(extraContent);

        // Return both nodes as a fragment so the caller appends them together
        const fragment = document.createDocumentFragment();
        fragment.appendChild(angryGrid);
        fragment.appendChild(extraRow);
        return fragment;
    }

    // Function to load logs via AJAX
    function loadLogs(page = 1) {
        if (isRefreshing) return;
        isRefreshing = true;

        // Capture the IDs of currently open rows
        const openRows = [];
        $('.extra-data-row:visible').each(function () {
            const rowId = $(this).prev('.angry-grid').attr('data-log-id');
            openRows.push(rowId);
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
                    const container = document.querySelector('.log_content');
                    container.innerHTML = ''; // Safe — clearing our own container, not inserting user data

                    $.each(response.data, function(index, log) {
                        container.appendChild(buildLogRow(log));
                    });

                    // Restore previously open rows
                    openRows.forEach(function (rowId) {
                        const $angryGrid = $(`.angry-grid[data-log-id="${rowId}"]`);
                        if ($angryGrid.length) {
                            $angryGrid.next('.extra-data-row').show();
                            $angryGrid.find('.semi-circle').fadeIn("slow");
                        }
                    });

                    updateRiskScoreColors();
                    currentPage = page;
                } else {
                    $('#log-container').html('<span>No logs found</span>');
                }
            },
            error: function() {
                $('#log-container').html('<span>Error loading logs</span>');
            },
            complete: function() {
                isRefreshing = false;
            }
        });
    }

    // Initial load
    loadLogs(currentPage);

    // Handle pagination
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
        isMouseInGrid = true;
    }).on('mouseleave', function() {
        isMouseInGrid = false;
    });

    // Real-time update every 15 seconds
    refreshInterval = setInterval(function() {
        if (!isMouseInGrid) {
            loadLogs(currentPage);
        }
    }, 15000);
});

function timeAgo(timestamp) {
    const targetDate = new Date(timestamp + "Z");
    const now = new Date();
    const secondsAgo = Math.floor((now - targetDate) / 1000);
    
    const minutes = Math.floor(secondsAgo / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    const weeks = Math.floor(days / 7);
    
    let months = (now.getMonth() + 12 * now.getFullYear()) - 
                (targetDate.getMonth() + 12 * targetDate.getFullYear());
    
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
    const date = new Date(utcTimestamp);
    return date.toLocaleString();
}

jQuery(function($) {
    $(document).on('click', '.add-to-whitelist-btn', function(e) {
        e.preventDefault();

        const ip = $(this).data('ip');
        const $btn = $(this);

        $.ajax({
            url: pvb_action_logs.ajax_url,
            type: 'POST',
            data: {
                action: 'whitelist_add',
                nonce_add_ip_whitelist: pvb_action_logs.whitelist_nonce,
                add: ip
            },
            success: function(response) {
                if (response.success === 'false') {
                    $btn.text('✖ Access Denied by proxycheck.io').prop('disabled', true);
                } else {
                    $btn.text('✔ Successfully Whitelisted').prop('disabled', true);
                }
            },
            error: function(xhr, status, error) {
                $btn.text('✖ Failed').addClass('error');
            }
        });
    });
});