var $ = jQuery;
$(document).ready(function(){
    if ('Beacon' in window) {
        /**
         * Show beacons on button "help" click
         */
        $(document).on('click', '.wpr-infoAction--help[data-beacon-id]', function(e){
            var ids = $(this).attr('data-beacon-id');

            if ( ! ids ) {
                return true;
            }

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
                        window.Beacon("navigate", "/answers/");
                        window.Beacon("open");
                    }, 200);
                } else {
                    window.Beacon("article", aID.toString());
                }

        }
    }

	$( '.wpr-ri-report' ).on( 'click', function() {
		wprTrackHelpButton( 'rocket insights see gtmetrix report', 'performance summary' );
	} );

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
                'path': rocket_mixpanel_data.path,
                'interaction_channel': 'UI'
            });
        }
    }

    // Make function globally available
    window.wprTrackHelpButton = wprTrackHelpButton;

    // WAP prompt: intercept "Need Help?" clicks that carry a pre-filled prompt.
    // Runs in capture phase so it fires before the beacon bubble handler above.
    document.addEventListener( 'click', function( e ) {
        var btn = e.target && e.target.closest( '.wpr-infoAction--help[data-wap-prompt]' );
        if ( ! btn ) {
            return;
        }

        // Only intercept when the WAP column is mounted in the DOM.
        var columnEl = document.querySelector( '[data-wap-chat-column="wp-rocket-support"]' );
        if ( ! columnEl ) {
            return;
        }

        e.preventDefault();
        e.stopImmediatePropagation();

        var prompt = btn.getAttribute( 'data-wap-prompt' );
        if ( ! prompt ) {
            return;
        }

        var launcher    = columnEl.querySelector( '.wap-chat-column__launcher' );
        var isCollapsed = launcher && ! launcher.hidden;

        if ( isCollapsed ) {
            launcher.click();
            // Wait for the column open transition before filling the composer.
            setTimeout( function() {
                wprFillWapPrompt( columnEl, prompt );
            }, 300 );
        } else {
            wprFillWapPrompt( columnEl, prompt );
        }
    }, true );

    function wprFillWapPrompt( columnEl, prompt ) {
        var textarea = columnEl.querySelector( 'textarea' );
        if ( ! textarea || textarea.disabled ) {
            return;
        }

        textarea.value = prompt;
        // Dispatch 'input' so WAP's autoGrow listener resizes the composer.
        textarea.dispatchEvent( new Event( 'input', { bubbles: true } ) );
        textarea.focus();

        var sendBtn = columnEl.querySelector( '.gv-input-ai .gv-button-primary' );
        if ( sendBtn ) {
            setTimeout( function() {
                sendBtn.click();
            }, 50 );
        }
    }
});
