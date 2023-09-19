function rocket_css_lazyload_launch() {

	const usable_pairs = typeof rocket_pairs === 'undefined' ? [] : rocket_pairs;


	const styleElement = document.querySelector('#wpr-lazyload-bg');

	const threshold = rocket_lazyload_css_data.threshold || 300;

	const observer = new IntersectionObserver(entries => {
		entries.forEach(entry => {
			if (entry.isIntersecting) {
				const pairs = usable_pairs.filter(s => entry.target.matches(s.selector));
				pairs.map(pair => {
					if (pair) {
						styleElement.innerHTML += pair.style;
						pair.elements.forEach(el => {
							// Stop observing the target element
							observer.unobserve(el);
							el.setAttribute(`data-rocket-lazy-bg-${pair.hash}`, 'loaded');
						});
					}
				})
			}
		});
	}, {
		rootMargin: threshold + 'px'
	});

	function lazyload(e = []) {

		const pass = e.length > 0;

		if(! pass ) {
			return;
		}

		usable_pairs.forEach(pair => {
			try {

				const elements = document.querySelectorAll(pair.selector);
				elements.forEach(el => {
					if(el.getAttribute(`data-rocket-lazy-bg-${pair.hash}`) === 'loaded') {
						return;
					}
					observer.observe(el);
					// Save el in the pair object (create a new empty array if it doesn't exist)
					(pair.elements ||= []).push(el);
				});
			} catch (error) {
				console.error(error);
			}
		});
	}

	lazyload();

	const observe_DOM = (function(){
		const MutationObserver = window.MutationObserver;

		return function( obj, callback ){
			if( !obj || obj.nodeType !== 1 ) return;

			// define a new observer
			const mutationObserver = new MutationObserver(callback);

			// have the observer observe for changes in children
			mutationObserver.observe( obj, { attributes: true, childList:true, subtree:true })
			return mutationObserver

		}

	})()

	const body = document.querySelector('body');

	observe_DOM(body, lazyload)
}

rocket_css_lazyload_launch();