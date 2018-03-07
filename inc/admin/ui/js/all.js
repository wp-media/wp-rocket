jQuery( document ).ready( function( $ ){
	$( '.rocket-dismiss' ).on( 'click', function( e ) {
		e.preventDefault();
		var url = $( this ).attr( 'href' ).replace( 'admin-post', 'admin-ajax' );
		$.get( url ).done( $( this ).closest( '.notice' ).hide( 'slow' ) );
	});

	$('#wpr-action-safe_mode').on('click', function(e) {
		var button = $(this);
		e.preventDefault();

		$.post(
			ajaxurl,
			{
				action: 'rocket_safe_mode',
				_ajax_nonce: ajax_data.nonce,
			},
			function(response) {
				if ( true === response.success ) {
					button.hide();
					$('.show-if-safe-mode').show();
				}
			}
		);
	});
} );
