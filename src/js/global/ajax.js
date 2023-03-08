var $ = jQuery;
$(document).ready(function(){
    /**
     * Refresh License data
     */
    var _isRefreshing = false;
    $('#wpr-action-refresh_account').on('click', function(e) {
        if(!_isRefreshing){
            var button = $(this);
            var account = $('#wpr-account-data');
            var expire = $('#wpr-expiration-data');

            e.preventDefault();
            _isRefreshing = true;
            button.trigger( 'blur' );
            button.addClass('wpr-isLoading');
            expire.removeClass('wpr-isValid wpr-isInvalid');

            $.post(
                ajaxurl,
                {
                    action: 'rocket_refresh_customer_data',
                    _ajax_nonce: rocket_ajax_data.nonce,
                },
                function(response) {
                    button.removeClass('wpr-isLoading');
                    button.addClass('wpr-isHidden');

                    if ( true === response.success ) {
                        account.html(response.data.license_type);
                        expire.addClass(response.data.license_class).html(response.data.license_expiration);
                        setTimeout(function() {
                            button.removeClass('wpr-icon-refresh wpr-isHidden');
                            button.addClass('wpr-icon-check');
                        }, 250);
                    }
                    else{
                        setTimeout(function() {
                            button.removeClass('wpr-icon-refresh wpr-isHidden');
                            button.addClass('wpr-icon-close');
                        }, 250);
                    }

                    setTimeout(function() {
                        var vTL = new TimelineLite({onComplete:function(){
                            _isRefreshing = false;
                        }})
                          .set(button, {css:{className:'+=wpr-isHidden'}})
                          .set(button, {css:{className:'-=wpr-icon-check'}}, 0.25)
                          .set(button, {css:{className:'-=wpr-icon-close'}})
                          .set(button, {css:{className:'+=wpr-icon-refresh'}}, 0.25)
                          .set(button, {css:{className:'-=wpr-isHidden'}})
                        ;
                    }, 2000);
                }
            );
        }
        return false;
    });

    /**
     * Save Toggle option values on change
     */
    $('.wpr-radio input[type=checkbox]').on('change', function(e) {
        e.preventDefault();
        var name  = $(this).attr('id');
        var value = $(this).prop('checked') ? 1 : 0;

		var excluded = [ 'cloudflare_auto_settings', 'cloudflare_devmode' ];
		if ( excluded.indexOf( name ) >= 0 ) {
			return;
		}

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

	/**
     * Save enable CPCSS for mobiles option.
     */
    $('#wpr-action-rocket_enable_mobile_cpcss').on('click', function(e) {
        e.preventDefault();

		$('#wpr-action-rocket_enable_mobile_cpcss').addClass('wpr-isLoading');

        $.post(
            ajaxurl,
            {
                action: 'rocket_enable_mobile_cpcss',
                _ajax_nonce: rocket_ajax_data.nonce
            },
			function(response) {
				if ( response.success ) {
					// Hide Mobile CPCSS btn on success.
					$('#wpr-action-rocket_enable_mobile_cpcss').hide();
					$('.wpr-hide-on-click').hide();
					$('.wpr-show-on-click').show();
					$('#wpr-action-rocket_enable_mobile_cpcss').removeClass('wpr-isLoading');
				}
			}
        );
    });

    /**
     * Save enable Google Fonts Optimization option.
     */
    $('#wpr-action-rocket_enable_google_fonts').on('click', function(e) {
        e.preventDefault();

		$('#wpr-action-rocket_enable_google_fonts').addClass('wpr-isLoading');

        $.post(
            ajaxurl,
            {
                action: 'rocket_enable_google_fonts',
                _ajax_nonce: rocket_ajax_data.nonce
            },
			function(response) {
				if ( response.success ) {
					// Hide Mobile CPCSS btn on success.
					$('#wpr-action-rocket_enable_google_fonts').hide();
					$('.wpr-hide-on-click').hide();
					$('.wpr-show-on-click').show();
                    $('#wpr-action-rocket_enable_google_fonts').removeClass('wpr-isLoading');
                    $('#minify_google_fonts').val(1);
				}
			}
        );
    });

    $( '#rocket-dismiss-promotion' ).on( 'click', function( e ) {
        e.preventDefault();

        $.post(
            ajaxurl,
            {
                action: 'rocket_dismiss_promo',
                nonce: rocket_ajax_data.nonce
            },
			function(response) {
				if ( response.success ) {
					$('#rocket-promo-banner').hide( 'slow' );
				}
			}
        );
    } );

    $( '#rocket-dismiss-renewal' ).on( 'click', function( e ) {
        e.preventDefault();

        $.post(
            ajaxurl,
            {
                action: 'rocket_dismiss_renewal',
                nonce: rocket_ajax_data.nonce
            },
			function(response) {
				if ( response.success ) {
					$('#rocket-renewal-banner').hide( 'slow' );
				}
			}
        );
    } );
	$( '#wpr-update-exclusion-list' ).on( 'click', function( e ) {
		e.preventDefault();
		$('#wpr-update-exclusion-msg').html('');
		$.ajax({
			url: rocket_ajax_data.rest_url,
			beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', rocket_ajax_data.rest_nonce );
			},
			method: "PUT",
			success: function(responses) {
				let exclusion_msg_container = $('#wpr-update-exclusion-msg');
				exclusion_msg_container.html('');
				if ( Array.isArray( responses ) && undefined !== responses['success'] ) {
					exclusion_msg_container.append( responses['message'] );
					return;
				}
				Object.keys( responses ).forEach(( response_key ) => {
					if ( responses[response_key]['success'] ) {
						exclusion_msg_container.append( '<strong>' + response_key + ': </strong>' );
						exclusion_msg_container.append( responses[response_key]['message'] );
						exclusion_msg_container.append( '<br>' );
					}
				});
			}
		});
	} );
});
