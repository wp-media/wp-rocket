/*eslint-env es6, browser*/
/* global MicroModal, mixpanel, rocket_mixpanel_data, rocket_ajax_data, ajaxurl */
( ( document, window ) => {
	'use strict';

	const BANNER_STATE = {
		OPENED: false,    // Big CTA - opened state
		COLLAPSED: true   // Small CTA - collapsed state
	};

	document.addEventListener( 'DOMContentLoaded', () => {
		document.querySelectorAll( '.wpr-rocketcdn-open' ).forEach( ( el ) => {
			el.addEventListener( 'click', ( e ) => {
				e.preventDefault();
				const isCTA = el.classList.contains( 'wpr-rocketcdn-pricing--cta' );
				checkButtonUrlAndOpen( isCTA );
			} );
		} );

		// Always initialize MicroModal to set up close handlers
		MicroModal.init( {
			disableScroll: true
		} );

		maybeOpenModalFromURL();

		// Only auto-open modal if there's no direct button URL
		if ( ! window.rocketcdnButtonUrl || window.rocketcdnButtonUrl === '' ) {
			maybeOpenModal();

			const iframe = document.getElementById('rocketcdn-iframe');
			const loader = document.getElementById('wpr-rocketcdn-modal-loader');
			if ( iframe && loader ) {
				iframe.addEventListener('load', function() {
					loader.style.display = 'none';
				});
			}
		}
	} );

	/**
	 * Checks if the user is currently on the CDN tab.
	 *
	 * @return {boolean} True if on CDN tab, false otherwise.
	 */
	function isOnCDNTab() {
		return window.location.hash === '#page_cdn';
	}

	function maybeTrackBannerView() {
		const smallCTA = document.querySelector( '#wpr-rocketcdn-cta-small' );
		const bigCTA = document.querySelector( '#wpr-rocketcdn-cta' );

		// Only track if one of the banners is visible.
		if ( bigCTA && ! bigCTA.classList.contains( 'wpr-isHidden' ) ) {
			trackRocketCDNUpsellBannerViewed( BANNER_STATE.OPENED );
		} else if ( smallCTA && ! smallCTA.classList.contains( 'wpr-isHidden' ) ) {
			trackRocketCDNUpsellBannerViewed( BANNER_STATE.COLLAPSED );
		}
	}

	window.addEventListener( 'load', () => {
		let openCTA = document.querySelector( '#wpr-rocketcdn-open-cta' ),
			closeCTA = document.querySelector( '#wpr-rocketcdn-close-cta' ),
			smallCTA = document.querySelector( '#wpr-rocketcdn-cta-small' ),
			bigCTA = document.querySelector( '#wpr-rocketcdn-cta' );

		const ctaToggle = document.querySelectorAll( '.wpr-rocketcdn-cta-toggle' );

		/**
		 * Toggles RocketCDN CTA internal collapsed/expanded state.
		 *
		 * @return {void}
		 */
		function toggleBigCTAState() {
			if ( ! bigCTA || ! ctaToggle.length ) {
				return;
			}

			const isCollapsed = bigCTA.classList.toggle( 'wpr-rocketcdn-cta--collapsed' );
			bigCTA.classList.toggle( 'wpr-rocketcdn-cta--expanded', ! isCollapsed );
			ctaToggle.forEach( ( el ) => {
				el.setAttribute( 'aria-expanded', isCollapsed ? 'false' : 'true' );
			} );
		}

		if ( ctaToggle.length && bigCTA ) {
			ctaToggle.forEach( ( el ) => {
				el.addEventListener( 'click', toggleBigCTAState );
				el.addEventListener( 'keydown', ( event ) => {
					if ( 'Enter' === event.key || ' ' === event.key ) {
						event.preventDefault();
						toggleBigCTAState();
					}
				} );
			} );
		}

		// Track banner view on page load if banner is visible and user is on CDN tab.
		maybeTrackBannerView();

		// Track banner view when user navigates to CDN tab.
		window.addEventListener( 'hashchange', () => {
			maybeTrackBannerView();
		} );

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

		// Track RocketCDN activation failed CTA click
		const activationCTA = document.querySelector('#wpr-rocketcdn-activation-cta');
		if (activationCTA) {
			activationCTA.addEventListener('click', () => {
				trackRocketCDNActivationCTA();
			});
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
		processStatus( e.data );
		enableCDN( e.data, iframeURL );
		disableCDN( e.data, iframeURL );
		validateTokenAndCNAME( e.data );
	};

	function openRocketCDNModal() {
		const rocketcdnIframe = document.getElementById('rocketcdn-iframe');
		if ( !rocketcdnIframe || !rocketcdnIframe.dataset || !rocketcdnIframe.dataset.src || rocketcdnIframe.dataset.src === rocketcdnIframe.src ) {
			return;
		}
		rocketcdnIframe.src = rocketcdnIframe.dataset.src;
		MicroModal.show( 'wpr-rocketcdn-modal' );
	}

	function checkButtonUrlAndOpen( isCTA ) {
		let iframeVisit = !window.rocketcdnButtonUrl;
		// Track CTA click if this is the pricing CTA button.
		if ( isCTA ) {
			trackRocketCDNUpsellCTAClicked(iframeVisit);
		}

		// Check if button URL was injected by PHP
		if ( !iframeVisit ) {
			// Small delay to ensure Mixpanel event is sent before navigation
			setTimeout( function() {
				window.location.href = window.rocketcdnButtonUrl;
			}, 100 );
		} else {
			// Show iframe modal as usual
			openRocketCDNModal();
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

		window.rocketcdnButtonUrl = setIsMonthlyParam(window.rocketcdnButtonUrl, isYearly);
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
					openRocketCDNModal();
				}
			}
		};
	}

	function maybeOpenModalFromURL() {
		const urlParams = new URLSearchParams( window.location.search );

		if ( urlParams.has( 'rocketcdn_open_iframe' ) && '1' === urlParams.get( 'rocketcdn_open_iframe' ) ) {
			// Set hash to page_cdn to show CDN tab behind modal
			window.location.hash = '#page_cdn';

			openRocketCDNModal();

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
		// Ensure scroll is restored
		document.body.style.overflow = '';

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

	function setIsMonthlyParam(url, isYearly) {
		// Remove any existing is_monthly param
		let newUrl = url.replace(/([?&])is_monthly=[^&]*/g, '');
		// Add the new param
		const sep = newUrl.includes('?') ? '&' : '?';
		newUrl += sep + 'is_monthly=' + (isYearly ? '0' : '1');
		return newUrl;
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

	/**
	 * Tracks RocketCDN activation failed CTA click with Mixpanel.
	 */
	function trackRocketCDNActivationCTA() {
		if (typeof mixpanel === 'undefined' || !mixpanel.track) {
			return;
		}

		// Check if user has opted in
		if (typeof rocket_mixpanel_data === 'undefined' || !rocket_mixpanel_data.optin_enabled || rocket_mixpanel_data.optin_enabled === '0') {
			return;
		}

		// Identify user if available
		if (rocket_mixpanel_data.user_id && typeof mixpanel.identify === 'function') {
			mixpanel.identify(rocket_mixpanel_data.user_id);
		}

		mixpanel.track('RocketCDN Activation Failed CTA Clicked', {
			context: rocket_mixpanel_data.context,
			plugin: rocket_mixpanel_data.plugin,
			brand: rocket_mixpanel_data.brand,
			application: rocket_mixpanel_data.app
		});
	}

	/**
	 * Tracks a RocketCDN upsell event with Mixpanel.
	 *
	 * @param {string} eventName   The Mixpanel event name.
	 * @param {Object} [extraProps] Optional additional properties to merge.
	 */
	function trackRocketCDNUpsellMixpanelEvent( eventName, extraProps ) {
		if ( typeof mixpanel === 'undefined' || ! mixpanel.track ) {
			return;
		}

		// Check if user has opted in.
		if ( typeof rocket_mixpanel_data === 'undefined' || ! rocket_mixpanel_data.optin_enabled || rocket_mixpanel_data.optin_enabled === '0' ) {
			return;
		}

		// Identify user if available.
		if ( ! rocket_mixpanel_data.user_id || typeof mixpanel.identify !== 'function' ) {
			return;
		}

		mixpanel.identify( rocket_mixpanel_data.user_id );

		var props = {
			context: rocket_mixpanel_data.context,
			plugin: rocket_mixpanel_data.plugin,
			brand: rocket_mixpanel_data.brand,
			application: rocket_mixpanel_data.app,
			path: rocket_mixpanel_data.path
		};

		// Merge extra properties if provided and valid.
		if ( extraProps && typeof extraProps === 'object' ) {
			for ( var key in extraProps ) {
				if ( Object.prototype.hasOwnProperty.call( extraProps, key ) ) {
					props[ key ] = extraProps[ key ];
				}
			}
		}

		mixpanel.track( eventName, props );
	}

	/**
	 * Tracks RocketCDN upsell banner view with Mixpanel.
	 *
	 * @param {boolean} [is_collapsed=false] Whether the small banner variant is displayed, Sends `collapsed` when true, otherwise `opened`.
	 */
	function trackRocketCDNUpsellBannerViewed( is_collapsed = false ) {
		if ( ! isOnCDNTab() ) {
			return;
		}
		trackRocketCDNUpsellMixpanelEvent( 'RocketCDN Upsell Banner Viewed', {
			state: is_collapsed ? 'collapsed' : 'opened'
		} );
	}

	/**
	 * Tracks RocketCDN upsell CTA click with Mixpanel.
	 */
	function trackRocketCDNUpsellCTAClicked( iframeVisit = false ) {
		trackRocketCDNUpsellMixpanelEvent( 'RocketCDN Upsell CTA Clicked', {
			destination: iframeVisit ? 'iframe' : 'express-checkout'
		} );
	}
} )( document, window );
