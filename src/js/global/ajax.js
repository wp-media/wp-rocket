var $ = jQuery;
$(document).ready(function(){
    /**
     * Refresh License data
     */
    $('#wpr-action-refresh_account').on('click', function(e) {
        var button = $(this);
        e.preventDefault();

        $.post(
            ajaxurl,
            {
                action: 'rocket_refresh_customer_data',
                _ajax_nonce: ajax_data.nonce,
            },
            function(response) {
                if ( true === response.success ) {
                    $('#wpr-account-data').html( response.data.licence_account);
                    $('#wpr-expiration-data').html( response.data.licence_expiration);
                }
            }
        );
    });

    /**
     * Save Varnish option value on change
     */
    $('#varnish_auto_purge').on('change', function(e) {
        e.preventDefault();
        var value = $(this).prop('checked') ? 1 : 0;

        $.post(
            ajaxurl,
            {
                action: 'rocket_toggle_varnish',
                _ajax_nonce: ajax_data.nonce,
                varnish_auto_purge: value
            },
            function(response) {}
        );
    });

    $('#do_cloudflare').on('change', function(e) {
        e.preventDefault();
        var value = $(this).prop('checked') ? 1 : 0;

        $.post(
            ajaxurl,
            {
                action: 'rocket_toggle_cloudflare',
                _ajax_nonce: ajax_data.nonce,
                do_cloudflare: value
            },
            function(response) {}
        );
    });
});