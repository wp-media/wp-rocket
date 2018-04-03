jQuery( document ).ready( function( $ ){
	$( '.rocket-dismiss' ).on( 'click', function( e ) {
		e.preventDefault();
		var url = $( this ).attr( 'href' ).replace( 'admin-post', 'admin-ajax' );
		$.get( url ).done( $( this ).closest( '.notice' ).hide( 'slow' ) );
	});
} );
