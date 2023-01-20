jQuery( document ).ready( function( $ ){
var sent = false;
	$( '.rocket-dismiss' ).on( 'click', function( e ) {
		e.preventDefault();
		var url = $( this ).attr( 'href' ).replace( 'admin-post', 'admin-ajax' );
		$.get( url ).done( $( this ).closest( '.notice' ).hide( 'slow' ) );
	});

	$( '#deactivate' ).click( function() {
		$( '#export_settings' ).prop( 'checked', false );
		$( '#export_settings' ).hide();
		$( 'label[for=export_settings]' ).hide();
	});

	$( '#safe_mode' ).click( function() {
		$( '#export_settings' ).show();
		$( 'label[for=export_settings]' ).show();
		$( '#export_settings' ).prop( 'checked', true );
	});

	$( '#wpr-deactivation-intent-form' ).submit(function (e) {
		const checked = $( '#export_settings' ).prop('checked');
		if(! checked || sent) {
			return true;
		}

		e.preventDefault();
		$.ajax( {
			url: rocket_option_export.rest_url_option_export,
			method: 'GET',
			success: function( data, textStatus, xhr ) {
				const disposition = xhr.getResponseHeader('content-disposition');

				const filenames = disposition.match('filename="([^"]+)"');

				if(! filenames.length) {
					return;
				}

				const filename = filenames.pop();

				const url = URL.createObjectURL( new Blob( [ JSON.stringify(data, null, 2) ], {
					type: "octet/stream"
				}));

				var a = document.createElement("a");
				document.body.appendChild(a);
				a.style = "display: none";
				a.href = url;
				a.download = filename;
				a.click();
				window.URL.revokeObjectURL(url);
			},
			complete: function () {
				sent = true;
				$( '#wpr-deactivation-intent-form' ).submit();
			}
		} );

		return true;
	});
} );
