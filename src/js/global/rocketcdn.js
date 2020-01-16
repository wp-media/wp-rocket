/*eslint-env es6*/
( ( document, window ) => {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', () => {
		document.querySelectorAll( '.wpr-rocketcdn-open' ).forEach( ( el ) => {
			el.addEventListener( 'click', ( e ) => {
				e.preventDefault();
			} );
		} );

		MicroModal.init( {
			disableScroll: true,
			onClose: () => {
				document.location.reload();
			}
		} );
	} );

	window.addEventListener( 'load', () => {
		let openCTA = document.querySelector( '#wpr-rocketcdn-open-cta' ),
			closeCTA = document.querySelector( '#wpr-rocketcdn-close-cta' ),
			smallCTA = document.querySelector( '#wpr-rocketcdn-cta-small' ),
			bigCTA = document.querySelector( '#wpr-rocketcdn-cta' );

		if ( null !== openCTA && null !== smallCTA && null !== bigCTA ) {
			openCTA.addEventListener( 'click', ( e ) => {
				e.preventDefault();

				smallCTA.classList.add( 'wpr-isHidden' );
				bigCTA.classList.remove( 'wpr-isHidden' );

				rocketSendHTTPRequest( rocketGetPostData( 'big' ) );
			} );
		}

		if ( null !== closeCTA && null !== smallCTA && null !== bigCTA ) {
			closeCTA.addEventListener( 'click', ( e ) => {
				e.preventDefault();

				smallCTA.classList.remove( 'wpr-isHidden' );
				bigCTA.classList.add( 'wpr-isHidden' );

				rocketSendHTTPRequest( rocketGetPostData( 'small' ) );
			} );
		}

		function rocketGetPostData( status ) {
			let postData = '';

			postData += 'action=toggle_rocketcdn_cta';
			postData += '&status=' + status;
			postData += '&nonce=' + rocket_ajax_data.nonce;

			return postData;
		}
	} );

	window.onmessage = ( e ) => {
		const iframeURL = rocket_ajax_data.origin_url;

		if ( e.origin !== iframeURL ) {
			return;
		}

		setCDNFrameHeight( e.data );
		closeModal( e.data );
		tokenHandler( e.data, iframeURL );
	};

	function closeModal( data ) {
		if ( ! data.hasOwnProperty( 'cdnFrameClose' ) ) {
			return;
		}

		MicroModal.close( 'wpr-rocketcdn-modal' );
	}

	function rocketSendHTTPRequest( postData ) {
		const httpRequest = new XMLHttpRequest();

		httpRequest.open( 'POST', ajaxurl );
		httpRequest.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
		httpRequest.send( postData );

		return httpRequest;
	}

	function setCDNFrameHeight( data ) {
		if ( ! data.hasOwnProperty( 'cdnFrameHeight' ) ) {
			return;
		}

		document.getElementById( 'rocketcdn-iframe' ).style.height = `${ data.cdnFrameHeight }px`;
	}

	function tokenHandler( data, iframeURL ) {
		let iframe = document.querySelector( '#rocketcdn-iframe' ).contentWindow;

		if ( ! data.hasOwnProperty( 'cdn_token' ) ) {
			iframe.postMessage(
				{
					'success': false,
					'data': 'token_not_received'
				},
				iframeURL
			);
			return;
		}

		let postData = '';

		postData += 'action=save_rocketcdn_token';
		postData += '&value=' + data.cdn_token;
		postData += '&nonce=' + rocket_ajax_data.nonce;

		const request = rocketSendHTTPRequest( postData );

		request.onreadystatechange = () => {
			if ( request.readyState === XMLHttpRequest.DONE && 200 === request.status ) {
				iframe.postMessage( request.responseText, iframeURL );
			}
		};
	}
} )( document, window );
