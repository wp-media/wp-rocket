jQuery( document ).ready( function( $ ){
	$( '.rkt-cross' ).on( 'click', function( e ) {
		e.preventDefault();
		var url = $( this ).attr( 'href' ).replace( 'admin-post', 'admin-ajax' );
		$.get( url ).done( $( this ).parent().hide( 'slow' ) );
	});
} );