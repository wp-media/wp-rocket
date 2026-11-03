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
