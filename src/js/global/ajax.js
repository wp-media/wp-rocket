var $ = jQuery;
$(document).ready(function(){
    /**
     * Refresh License data
     */
    $('#wpr-action-refresh_account').on('click', function(e) {
        var button = $(this);
        var expire = $('#wpr-expiration-data');

        e.preventDefault();
        expire.removeClass('wpr-isValid wpr-isInvalid');

        $.post(
            ajaxurl,
            {
                action: 'rocket_refresh_customer_data',
                _ajax_nonce: rocket_ajax_data.nonce,
            },
            function(response) {
                if ( true === response.success ) {
                    $('#wpr-account-data').html(response.data.licence_account);
                    expire.addClass(response.data.class).html(response.data.licence_expiration);
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
                _ajax_nonce: rocket_ajax_data.nonce,
                option: {
                    name: name,
                    value: value
                }
            },
            function(response) {}
        );
    });
});