import LcpBeacon from 'rocket-scripts';

( rocket_lcp_data => {
	if ( !rocket_lcp_data ) {
		return;
	}

	const instance = new LcpBeacon( rocket_lcp_data );

	if (document.readyState !== 'loading') {
		setTimeout(() => {
			instance.init();
		}, rocket_lcp_data.delay);
		return;
	}

	document.addEventListener("DOMContentLoaded", () => {
		setTimeout(() => {
			instance.init();
		}, rocket_lcp_data.delay);
	});
} )( window.rocket_lcp_data );