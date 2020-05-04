var $ = jQuery;
var checkCPCSSGenerationCall;
var cpcsssGenerationPending = 0;

$( document ).ready( function() {
	$( '#rocket-delete-post-cpss' ).on( 'click', function( e ) {
		e.preventDefault();
		deleteCPCSS();
	});

	$( '#rocket-generate-post-cpss' ).on( 'click', function( e ) {
		e.preventDefault();
		checkCPCSSGeneration();
	});
});

function checkCPCSSGeneration( timeout = null ) {
	var spinner = $( '#rocket-generate-post-cpss' ).find( '.spinner' );
	spinner.show();
	spinner.css( 'visibility', 'visible' );

	$.ajax( {
		url: cpcss_rest_url,
		method: 'POST',
		data: { 'timeout': timeout },
		dataType: 'JSON',
		beforeSend: function ( xhr ) {
			xhr.setRequestHeader( 'X-WP-Nonce', cpcss_rest_nonce );
		},
	} ).done( function ( cpcss_response ) {
		if ( cpcss_response.data.status !== 200 ) {
			stopCPCSSGeneration( spinner );
			cpcssNotice( cpcss_response.message, 'error' );
			return;
		}

		if ( cpcss_response.data.status === 200 && cpcss_response.code !== 'cpcss_generation_pending') {
			stopCPCSSGeneration( spinner );
			cpcssNotice( cpcss_response.message, 'success' );
			// Revert view to Regenerate.
			$( '.rocket-generate-post-cpss-btn-txt' ).html( cpcss_regenerate_btn );
			$( '.cpcss_generate' ).hide();
			$( '.cpcss_regenerate' ).show();
			$( '#rocket-delete-post-cpss' ).show();
			return;
		}

		cpcsssGenerationPending++;

		if ( cpcsssGenerationPending > 10 ) {
			stopCPCSSGeneration( spinner );
			cpcsssGenerationPending = 0;
			checkCPCSSGeneration( true );
			return;
		}

		checkCPCSSGenerationCall = setTimeout(function () {
			checkCPCSSGeneration();
		}, 3000);
	} );
}

function stopCPCSSGeneration( spinner ) {
	spinner.hide();
	clearTimeout( checkCPCSSGenerationCall );
}

function deleteCPCSS() {
	$.ajax( {
		url: cpcss_rest_url,
		method: 'DELETE',
		dataType: 'JSON',
		beforeSend: function ( xhr ) {
			xhr.setRequestHeader( 'X-WP-Nonce', cpcss_rest_nonce );
		},
	} ).done( function ( cpcss_response ) {
		if ( cpcss_response.data.status !== 200 ) {
			cpcssNotice( cpcss_response.message, 'error' );
			return;
		}
		cpcssNotice( cpcss_response.message, 'success' );

		// Revert view to Generate.
		$( '.rocket-generate-post-cpss-btn-txt' ).html( cpcss_generate_btn );
		$( '.cpcss_regenerate' ).hide();
		$( '.cpcss_generate' ).show();
		$( '#rocket-delete-post-cpss' ).hide();
	} );
}

function cpcssNotice( msg, type ) {
    /* Add notice class */
	var cpcssNotice = document.getElementById( 'cpcss_response_notice' );
	cpcssNotice.innerHTML = '';
	cpcssNotice.classList.remove( 'notice', 'notice-error', 'notice-success');
    cpcssNotice.classList.add( 'notice', 'notice-' + type );

    /* create paragraph element to hold message */
    var p = document.createElement( 'p' );
    p.appendChild( document.createTextNode( msg ) );

    /* Add the whole message to notice div */
    cpcssNotice.appendChild( p );
}
