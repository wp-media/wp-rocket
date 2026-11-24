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

    var launcher = columnEl.querySelector( '.wap-chat-column__launcher' );
    if ( launcher && ! launcher.hidden ) {
        launcher.click();
    }

    wprSendWapPrompt( columnEl, prompt );
}, true );

// The prompt waiting to be sent, and the last one sent.
var wprWapPending  = null;
var wprWapLastSent = { prompt: '', time: 0 };

/**
 * Sends a prompt to the chat as soon as it can take a message.
 *
 * The message box stays disabled while the chat connects, until the consent is accepted and
 * when the quota is used up, and the send button is a Stop button while an answer streams.
 * The prompt is put in the message box right away and sent once the chat is ready. Waiting
 * stops when the chat is closed or another prompt is requested; the prompt stays in the box.
 *
 * @param {Element} columnEl The chat column.
 * @param {string}  prompt   The prompt to send.
 */
function wprSendWapPrompt( columnEl, prompt ) {
    // A second click on the same "Need Help?" doesn't send its prompt twice.
    if ( wprWapPending && wprWapPending.prompt === prompt ) {
        return;
    }
    if ( wprWapLastSent.prompt === prompt && Date.now() - wprWapLastSent.time < 2000 ) {
        return;
    }

    wprCancelWapPrompt();

    var pending = {
        prompt: prompt,
        opened: false,
        observer: null
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

    wprTrySendWapPrompt( columnEl, pending );
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
