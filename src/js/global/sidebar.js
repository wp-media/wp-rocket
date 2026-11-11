/**
 * Dashboard sidebar add-on cards: expand / collapse the benefits list.
 *
 * The toggle is a <button type="button">: the cards sit inside the settings form, a plain button would submit it.
 * A native button also handles the Enter and Space keys.
 */
document.addEventListener( 'DOMContentLoaded', function() {
	var toggles = document.querySelectorAll( '.wpr-addonCard-toggle' );

	Array.prototype.forEach.call( toggles, function( toggle ) {
		toggle.addEventListener( 'click', function( event ) {
			event.preventDefault();

			var list = document.getElementById( toggle.getAttribute( 'aria-controls' ) );
			var card = toggle.closest( '.wpr-addonCard' );

			if ( ! list ) {
				return;
			}

			var expand = 'true' !== toggle.getAttribute( 'aria-expanded' );

			toggle.setAttribute( 'aria-expanded', expand ? 'true' : 'false' );
			toggle.textContent = expand ? toggle.getAttribute( 'data-label-less' ) : toggle.getAttribute( 'data-label-more' );
			list.hidden = ! expand;

			if ( card ) {
				card.classList.toggle( 'is-expanded', expand );
			}
		} );
	} );
} );
