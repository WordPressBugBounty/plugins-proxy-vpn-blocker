jQuery(document).ready(function($) {
    function updateToolbarState(data) {
        var toolbarItem = $('#wp-admin-bar-proxy_vpn_blocker .ab-item img');
        var blockMethod = $('#wp-admin-bar-block_method .ab-item');
        var cacheStatus = $('#wp-admin-bar-cache_status');
        var toggleLink = $('#wp-admin-bar-pvb-toggle-block-post .ab-item');
        var promptLink = $('#wp-admin-bar-prompt .ab-item');

        if (data.checkbox_value === '1') {
            toolbarItem.attr('src', pvb_admin_toolbar.plugin_url + 'assets/img/pvb-green-dot.svg');
            blockMethod.text('This Page/Post is restricted for Proxies/VPNs');
            toggleLink.text('Immediately Unblock Proxies and VPNs Here');
            cacheStatus.show();
            cacheStatus.find('.ab-item').text(pvb_admin_toolbar.cache_status);
        } else {
            toolbarItem.attr('src', pvb_admin_toolbar.plugin_url + 'assets/img/pvb-red-dot.svg');
            blockMethod.text('This Page/Post is not restricted for Proxies/VPNs');
            cacheStatus.hide();
            toggleLink.text('Immediately Block Proxy and VPNs Here');
        }

        if (data.post_edit_link) {
            promptLink.attr('href', data.post_edit_link);
            promptLink.text('Change blocking method');
        }
    }

    // Toggle block post
    $('#wp-admin-bar-pvb-toggle-block-post a').on('click', function(e) {
        e.preventDefault();

        // Get post ID and nonce from localized data
        var post_id = pvb_admin_toolbar.post_id;
        var nonce = pvb_admin_toolbar.nonce;

        // AJAX request to update post meta and fetch toolbar state
        $.ajax({
            type: 'POST',
            url: pvb_admin_toolbar.ajax_url,
            data: {
                action: 'pvb_admin_toolbar', // Action name
                nonce: nonce,
                post_id: post_id,
                toggle: true
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    updateToolbarState(data);
                }
            },
        });
    });

    // Initial AJAX request to update the toolbar on page load
    $.ajax({
        type: 'POST',
        url: pvb_admin_toolbar.ajax_url,
        data: {
            action: 'pvb_admin_toolbar', // Action name
            nonce: pvb_admin_toolbar.nonce,
            post_id: pvb_admin_toolbar.post_id
        },
        success: function(response) {
            if (response.success) {
                var data = response.data;
                updateToolbarState(data);
            }
        },
    });
});
