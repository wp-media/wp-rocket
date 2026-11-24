// WAP prompt: intercept "Need Help?" clicks that carry a pre-filled prompt.
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

    // Stopping the click below keeps main.js from tracking it, so track it here, as main.js does.
    if ( typeof window.wprTrackHelpButton === 'function' ) {
        window.wprTrackHelpButton( 'Need Help', btn.getAttribute( 'data-wpr_track_context' ) || 'General' );
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
    if ( ! textarea ) {
        return;
    }

    textarea.value = prompt;

    if ( ! textarea.disabled ) {
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
}

// Support menu item: open the chat column instead of following the link to the contact page.
document.addEventListener( 'click', function( e ) {
    var item = e.target && e.target.closest( '[data-wap-open-chat]' );
    if ( ! item ) {
        return;
    }

    // Modified and non-primary clicks keep the link behavior, for example opening it in a new tab.
    if ( e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey ) {
        return;
    }

    // Without the column or its launcher the link works as a normal link.
    var columnEl = document.querySelector( '[data-wap-chat-column="wp-rocket-support"]' );
    var launcher = columnEl && columnEl.querySelector( '.wap-chat-column__launcher' );
    if ( ! launcher ) {
        return;
    }

    e.preventDefault();
    e.stopImmediatePropagation();

    if ( ! launcher.hidden ) {
        launcher.click();
    }
}, true );
