/*eslint-env es6*/
( ( document, window ) => {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', () => {
		document.querySelectorAll( '.wpr-rocketcdn-open' ).forEach( ( el ) => {
			el.addEventListener( 'click', ( e ) => {
				e.preventDefault();
			} );
		} );

		rocketMaybeOpenModal();

		MicroModal.init( {
			disableScroll: true
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

		displayTokenField( e.data );
		setCDNFrameHeight( e.data );
		closeModal( e.data );
		tokenHandler( e.data, iframeURL );
		processStatus( e.data );
		enableCDN( e.data, iframeURL );
		disableCDN( e.data, iframeURL );
	};

	function rocketMaybeOpenModal() {
		let postData = '';

		postData += 'action=rocketcdn_process_status';
		postData += '&nonce=' + rocket_ajax_data.nonce;

		const request = rocketSendHTTPRequest( postData );

		request.onreadystatechange = () => {
			if ( request.readyState === XMLHttpRequest.DONE && 200 === request.status ) {
				let responseTxt = JSON.parse(request.responseText);

				if ( true === responseTxt.success ) {
					MicroModal.show( 'wpr-rocketcdn-modal' );
				}
			}
		};
	}

	function displayTokenField( data ) {
		if ( ! data.hasOwnProperty( 'cdn_manual_token' ) ) {
			return;
		}

		let field = document.querySelector( '.wpr-rocketcdn-token' );
		field.classList.remove( 'wpr-isHidden' );
	}

	function closeModal( data ) {
		if ( ! data.hasOwnProperty( 'cdnFrameClose' ) ) {
			return;
		}

		MicroModal.close( 'wpr-rocketcdn-modal' );

		let pages = [ 'iframe-payment-success', 'iframe-unsubscribe-success' ];

		if ( ! data.hasOwnProperty( 'cdn_page_message' ) ) {
			return;
		}

		if ( pages.indexOf( data.cdn_page_message ) === -1 ) {
			return;
		}

		document.location.reload();
	}

	function processStatus( data ) {
		if ( ! data.hasOwnProperty( 'rocketcdn_process' ) ) {
			return;
		}

		let postData = '';

		postData += 'action=rocketcdn_process_set';
		postData += '&status=' + data.rocketcdn_process;
		postData += '&nonce=' + rocket_ajax_data.nonce;

		rocketSendHTTPRequest( postData );
	}

	function enableCDN( data, iframeURL ) {
		let iframe = document.querySelector( '#rocketcdn-iframe' ).contentWindow;

		if ( ! data.hasOwnProperty( 'rocketcdn_url' ) ) {
			return;
		}

		let postData = '';

		postData += 'action=rocketcdn_enable';
		postData += '&cdn_url=' + data.rocketcdn_url;
		postData += '&nonce=' + rocket_ajax_data.nonce;

		const request = rocketSendHTTPRequest( postData );

		request.onreadystatechange = () => {
			if ( request.readyState === XMLHttpRequest.DONE && 200 === request.status ) {
				let responseTxt = JSON.parse(request.responseText);
				iframe.postMessage(
					{
						'success': responseTxt.success,
						'data': responseTxt.data,
						'rocketcdn': true
					},
					iframeURL
				);
			}
		};
	}

	function disableCDN( data, iframeURL ) {
		let iframe = document.querySelector( '#rocketcdn-iframe' ).contentWindow;

		if ( ! data.hasOwnProperty( 'rocketcdn_disable' ) ) {
			return;
		}

		let postData = '';

		postData += 'action=rocketcdn_disable';
		postData += '&nonce=' + rocket_ajax_data.nonce;

		const request = rocketSendHTTPRequest( postData );

		request.onreadystatechange = () => {
			if ( request.readyState === XMLHttpRequest.DONE && 200 === request.status ) {
				let responseTxt = JSON.parse(request.responseText);
				iframe.postMessage(
					{
						'success': responseTxt.success,
						'data': responseTxt.data,
						'rocketcdn': true
					},
					iframeURL
				);
			}
		};
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

		if ( ! data.hasOwnProperty( 'rocketcdn_token' ) ) {
			let data = {process:"subscribe", message:"token_not_received"};
			iframe.postMessage(
				{
					'success': false,
					'data': data,
					'rocketcdn': true
				},
				iframeURL
			);
			return;
		}

		let postData = '';

		postData += 'action=save_rocketcdn_token';
		postData += '&value=' + data.rocketcdn_token;
		postData += '&nonce=' + rocket_ajax_data.nonce;

		const request = rocketSendHTTPRequest( postData );

		request.onreadystatechange = () => {
			if ( request.readyState === XMLHttpRequest.DONE && 200 === request.status ) {
				let responseTxt = JSON.parse(request.responseText);
				iframe.postMessage(
					{
						'success': responseTxt.success,
						'data': responseTxt.data,
						'rocketcdn': true
					},
					iframeURL
				);
			}
		};
	}
} )( document, window );
