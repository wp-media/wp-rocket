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
     * Save Toggle option values on change
     */
    $('.wpr-radio input[type=checkbox]').on('change', function(e) {
        e.preventDefault();
        var name  = $(this).attr('id');
        var value = $(this).prop('checked') ? 1 : 0;

        $.post(
            ajaxurl,
            {
                action: 'rocket_toggle_option',
                _ajax_nonce: ajax_data.nonce,
                option: {
                    name: name,
                    value: value
                }
            },
            function(response) {}
        );
    });
});