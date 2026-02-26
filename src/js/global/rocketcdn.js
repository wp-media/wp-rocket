/*eslint-env es6*/
( ( document, window ) => {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', () => {
		document.querySelectorAll( '.wpr-rocketcdn-open' ).forEach( ( el ) => {
			el.addEventListener( 'click', ( e ) => {
				e.preventDefault();
				checkButtonUrlAndOpen();
			} );
		} );

		// Only initialize modal if there's no direct button URL
		if ( ! window.rocketcdnButtonUrl || window.rocketcdnButtonUrl === '' ) {
			maybeOpenModal();
			maybeOpenModalFromURL();

			MicroModal.init( {
				disableScroll: true
			} );

			const iframe = document.getElementById('rocketcdn-iframe');
			const loader = document.getElementById('wpr-rocketcdn-modal-loader');
			if ( iframe && loader ) {
				iframe.addEventListener('load', function() {
					loader.style.display = 'none';
				});
			}
		}
	} );

	window.addEventListener( 'load', () => {
		let openCTA = document.querySelector( '#wpr-rocketcdn-open-cta' ),
			closeCTA = document.querySelector( '#wpr-rocketcdn-close-cta' ),
			smallCTA = document.querySelector( '#wpr-rocketcdn-cta-small' ),
			bigCTA = document.querySelector( '#wpr-rocketcdn-cta' ),
			inputToggle = document.querySelector('.wpr-rocketcdn-toggle--input');

		// Prices selectors for toggling visibility based on the billing cycle toggle state.
		const prices = {
			monthly: {
				regular: document.querySelectorAll('.wpr-rocketcdn-pricing-regular-price--monthly'),
				current: document.querySelectorAll('.wpr-rocketcdn-pricing--monthly'),
				period: document.querySelectorAll('.wpr-rocketcdn-pricing--billing-period--monthly')
			},
			yearly: {
				regular: document.querySelectorAll('.wpr-rocketcdn-pricing-regular-price--yearly'),
				current: document.querySelectorAll('.wpr-rocketcdn-pricing--annual'),
				period: document.querySelectorAll('.wpr-rocketcdn-pricing--billing-period--yearly')
			}
		}

		if ( null !== openCTA && null !== smallCTA && null !== bigCTA ) {
			openCTA.addEventListener( 'click', ( e ) => {
				e.preventDefault();

				smallCTA.classList.add( 'wpr-isHidden' );
				bigCTA.classList.remove( 'wpr-isHidden' );

				sendHTTPRequest( getPostData( 'big' ) );
			} );
		}

		if ( null !== closeCTA && null !== smallCTA && null !== bigCTA ) {
			closeCTA.addEventListener( 'click', ( e ) => {
				e.preventDefault();

				smallCTA.classList.remove( 'wpr-isHidden' );
				bigCTA.classList.add( 'wpr-isHidden' );

				sendHTTPRequest( getPostData( 'small' ) );
			} );
		}

		function getPostData( status ) {
			let postData = '';

			postData += 'action=toggle_rocketcdn_cta';
			postData += '&status=' + status;
			postData += '&nonce=' + rocket_ajax_data.nonce;

			return postData;
		}

		// Display the correct prices on page based on billing cycle toggle state.
		inputToggle.addEventListener('change', function() {
			const isYearly = this.checked;

			if (isYearly) {
				Object.values(prices.monthly).forEach(list => list.forEach(el => el.classList.add('wpr-isHidden')));
				Object.values(prices.yearly).forEach(list => list.forEach(el => el.classList.remove('wpr-isHidden')));
			} else {
				Object.values(prices.monthly).forEach(list => list.forEach(el => el.classList.remove('wpr-isHidden')));
				Object.values(prices.yearly).forEach(list => list.forEach(el => el.classList.add('wpr-isHidden')));
			}

			// Update the button URL with the correct is_monthly parameter.
			updateButtonUrlBillingCycle(isYearly);
		});

	} );

	window.onmessage = ( e ) => {
		const iframeURL = rocket_ajax_data.origin_url;

		if ( e.origin !== iframeURL ) {
			return;
		}

		setCDNFrameHeight( e.data );
		closeModal( e.data );
		tokenHandler( e.data, iframeURL );
		processStatus( e.data );
		enableCDN( e.data, iframeURL );
		disableCDN( e.data, iframeURL );
		validateTokenAndCNAME( e.data );
	};

	function checkButtonUrlAndOpen() {
		// Check if button URL was injected by PHP
		if ( window.rocketcdnButtonUrl && window.rocketcdnButtonUrl !== '' ) {
			// Navigate to button URL in same tab
			window.location.href = window.rocketcdnButtonUrl;
		} else {
			// Show iframe modal as usual
			MicroModal.show( 'wpr-rocketcdn-modal' );
		}
	}

	/**
	 * Updates the button URL with the correct is_monthly parameter based on billing cycle toggle.
	 *
	 * @param {boolean} isYearly - True if yearly billing is selected, false for monthly.
	 */
	function updateButtonUrlBillingCycle( isYearly ) {
		if ( ! window.rocketcdnButtonUrl || window.rocketcdnButtonUrl === '' ) {
			return;
		}

		const url = new URL( window.rocketcdnButtonUrl );
		url.searchParams.set( 'is_monthly', isYearly ? '0' : '1' );
		window.rocketcdnButtonUrl = url.toString();
	}

	function maybeOpenModal() {
		let postData = '';

		postData += 'action=rocketcdn_process_status';
		postData += '&nonce=' + rocket_ajax_data.nonce;

		const request = sendHTTPRequest( postData );

		request.onreadystatechange = () => {
			if ( request.readyState === XMLHttpRequest.DONE && 200 === request.status ) {
				let responseTxt = JSON.parse(request.responseText);

				if ( true === responseTxt.success ) {
					MicroModal.show( 'wpr-rocketcdn-modal' );
				}
			}
		};
	}

	function maybeOpenModalFromURL() {
		const urlParams = new URLSearchParams( window.location.search );

		if ( urlParams.has( 'rocketcdn_open_iframe' ) && '1' === urlParams.get( 'rocketcdn_open_iframe' ) ) {
			// Set hash to page_cdn to show CDN tab behind modal
			window.location.hash = '#page_cdn';
			
			MicroModal.show( 'wpr-rocketcdn-modal' );

			// Clean up the URL to prevent re-triggering on refresh
			urlParams.delete( 'rocketcdn_open_iframe' );
			const search = urlParams.toString();
			const newURL = window.location.pathname + ( search ? '?' + search : '' ) + window.location.hash;
			window.history.replaceState( {}, '', newURL );
		}
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

		sendHTTPRequest( postData );
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

		const request = sendHTTPRequest( postData );

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

		const request = sendHTTPRequest( postData );

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

	function sendHTTPRequest( postData ) {
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

		const request = sendHTTPRequest( postData );

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

	function validateTokenAndCNAME( data ) {
		if ( ! data.hasOwnProperty( 'rocketcdn_validate_token' ) || ! data.hasOwnProperty( 'rocketcdn_validate_cname' ) ) {
			return;
		}

		let postData = '';

		postData += 'action=rocketcdn_validate_token_cname';
		postData += '&cdn_url=' + data.rocketcdn_validate_cname;
		postData += '&cdn_token=' + data.rocketcdn_validate_token;
		postData += '&nonce=' + rocket_ajax_data.nonce;

		const request = sendHTTPRequest( postData );
	}
} )( document, window );
