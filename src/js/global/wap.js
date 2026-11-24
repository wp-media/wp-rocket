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

    var launcher = columnEl.querySelector( '.wap-chat-column__launcher' );
    if ( launcher && ! launcher.hidden ) {
        launcher.click();
    }

    wprSendWapPrompt( columnEl, prompt );
}, true );

// The prompt waiting to be sent, and the last one sent.
var wprWapPending  = null;
var wprWapLastSent = { prompt: '', time: 0 };

// Once sent, the send button is a Stop button for the answer, so a second click would queue the
// same prompt again. Clicks on the same prompt within this window are ignored.
var WPR_WAP_RESEND_MS = 2000;
// How long to wait for the chat to take the prompt, not counting the consent popup or a streaming answer.
var WPR_WAP_WAIT_MS = 30000;

/**
 * Puts the prompt in the message box and sends it once the chat can take a message.
 *
 * The box is disabled while the chat connects, until the consent is accepted and when the quota is
 * used up, and the send button is a Stop button while an answer streams. The prompt stays in the box
 * unsent when the chat is closed, the consent is declined, another prompt is requested, or the wait times out.
 *
 * @param {Element} columnEl The chat column.
 * @param {string}  prompt   The prompt to send.
 */
function wprSendWapPrompt( columnEl, prompt ) {
    // A second click on the same "Need Help?" doesn't send its prompt twice.
    if ( wprWapPending && wprWapPending.prompt === prompt ) {
        return;
    }
    if ( wprWapLastSent.prompt === prompt && Date.now() - wprWapLastSent.time < WPR_WAP_RESEND_MS ) {
        return;
    }

    wprCancelWapPrompt();

    var pending = {
        prompt: prompt,
        opened: false,
        // A "consent required" notice already in the chat doesn't cancel the prompt; a new one does.
        consentGate: columnEl.querySelector( '.wap-consent-gate' ),
        observer: null,
        timer: null
    };
    wprWapPending = pending;

    var textarea = columnEl.querySelector( 'textarea' );
    if ( textarea ) {
        textarea.value = prompt;
    }

    pending.observer = new MutationObserver( function() {
        wprTrySendWapPrompt( columnEl, pending );
    } );
    pending.observer.observe( columnEl, {
        subtree: true,
        childList: true,
        attributes: true,
        attributeFilter: [ 'disabled', 'class', 'hidden' ]
    } );
    wprWaitWapPrompt( columnEl, pending );

    wprTrySendWapPrompt( columnEl, pending );
}

function wprWaitWapPrompt( columnEl, pending ) {
    pending.timer = setTimeout( function() {
        if ( wprWapPending !== pending ) {
            return;
        }

        var sendBtn = columnEl.querySelector( '.gv-input-ai .gv-button-primary' );
        if ( columnEl.querySelector( '.wap-consent-modal' ) || ( sendBtn && sendBtn.classList.contains( 'wap-stop' ) ) ) {
            wprWaitWapPrompt( columnEl, pending );
            return;
        }

        wprCancelWapPrompt();
    }, WPR_WAP_WAIT_MS );
}

function wprTrySendWapPrompt( columnEl, pending ) {
    if ( wprWapPending !== pending ) {
        return;
    }

    // The launcher shows only while the chat is closed. Closing it after it opened cancels the prompt.
    var launcher = columnEl.querySelector( '.wap-chat-column__launcher' );
    if ( launcher && ! launcher.hidden ) {
        if ( pending.opened ) {
            wprCancelWapPrompt();
        }
        return;
    }
    pending.opened = true;

    // The chat adds this notice when the consent popup is dismissed without accepting.
    var consentGate = columnEl.querySelector( '.wap-consent-gate' );
    if ( consentGate && consentGate !== pending.consentGate ) {
        wprCancelWapPrompt();
        return;
    }

    var textarea = columnEl.querySelector( 'textarea' );
    var sendBtn  = columnEl.querySelector( '.gv-input-ai .gv-button-primary' );
    if ( ! textarea || ! sendBtn || textarea.disabled || sendBtn.disabled || sendBtn.classList.contains( 'wap-stop' ) ) {
        return;
    }

    wprCancelWapPrompt();

    textarea.value = pending.prompt;
    // Dispatch 'input' so WAP's autoGrow listener resizes the composer.
    textarea.dispatchEvent( new Event( 'input', { bubbles: true } ) );
    textarea.focus();

    wprWapLastSent = { prompt: pending.prompt, time: Date.now() };
    sendBtn.click();
}

function wprCancelWapPrompt() {
    if ( ! wprWapPending ) {
        return;
    }

    wprWapPending.observer.disconnect();
    clearTimeout( wprWapPending.timer );
    wprWapPending = null;
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
