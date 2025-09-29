var $ = jQuery;
$(document).ready(function(){
    if ('Beacon' in window) {
        /**
         * Show beacons on button "help" click
         */
        var $help = $('.wpr-infoAction--help');
        $help.on('click', function(e){
            var ids = $(this).data('beacon-id');
            var button = $(this).data('wpr_track_button') || 'Beacon Help';
            var context = $(this).data('wpr_track_context') || 'Settings';

            // Track with MixPanel JS SDK
            wprTrackHelpButton(button, context);

            // Continue with existing beacon functionality
            wprCallBeacon(ids);
            return false;
        });

        function wprCallBeacon(aID){
            aID = aID.split(',');
            if ( aID.length === 0 ) {
                return;
            }

                if ( aID.length > 1 ) {
                    window.Beacon("suggest", aID);

                    setTimeout(function() {
                        window.Beacon("open");
                    }, 200);
                } else {
                    window.Beacon("article", aID.toString());
                }

        }
    }

    // MixPanel tracking function
    function wprTrackHelpButton(button, context) {
        if (typeof mixpanel !== 'undefined' && mixpanel.track) {
            // Check if user has opted in using localized data
            if (typeof rocket_mixpanel_data === 'undefined' || !rocket_mixpanel_data.optin_enabled || rocket_mixpanel_data.optin_enabled === '0') {
                return;
            }

            // Identify user with hashed license email if available
            if (rocket_mixpanel_data.user_id && typeof mixpanel.identify === 'function') {
                mixpanel.identify(rocket_mixpanel_data.user_id);
            }

            mixpanel.track('Button Clicked', {
                'button': button,
				'button_context': context,
				'plugin': rocket_mixpanel_data.plugin,
                'brand': rocket_mixpanel_data.brand,
                'application': rocket_mixpanel_data.app,
                'context': rocket_mixpanel_data.context,
                'path': rocket_mixpanel_data.path
            });
        }
    }

    // Make function globally available
    window.wprTrackHelpButton = wprTrackHelpButton;
});
