jQuery( document ).ready( function( $ ){
	$( '.rocket-dismiss' ).on( 'click', function( e ) {
		e.preventDefault();
		var url = $( this ).attr( 'href' ).replace( 'admin-post', 'admin-ajax' );
		$.get( url ).done( $( this ).closest( '.notice' ).hide( 'slow' ) );
	});

	$( '#deactivate' ).click( function() {
		$( '#export_settings' ).prop( 'checked', false );
		$( '#export_settings' ).hide();
		$( 'label[for=export_settings]' ).hide();
	})

	$( '#safe_mode' ).click( function() {
		$( '#export_settings' ).show();
		$( 'label[for=export_settings]' ).show();
		$( '#export_settings' ).prop( 'checked', true );
	})
} );
