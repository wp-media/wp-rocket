function rocket_css_lazyload_launch() {

	const pairs = typeof rocket_pairs === 'undefined' ? [] : rocket_pairs;

	const styleElement = document.querySelector('#wpr-lazyload-bg');

	const threshold = rocket_lazyload_css_data.threshold || 300;

	const observer = new IntersectionObserver(entries => {
		entries.forEach(entry => {
			if (entry.isIntersecting) {
				const pairs = rocket_pairs.filter(s => entry.target.matches(s.selector));
				pairs.map(pair => {
					if (pair) {
						styleElement.innerHTML += pair.style;
						pair.elements.forEach(el => {
							el.setAttribute('data-rocket-lazy-bg', 'loaded');
							// Stop observing the target element
							observer.unobserve(el);
						});
					}
				})
			}
		});
	}, {
		rootMargin: threshold + 'px'
	});

	function lazyload() {


		pairs.forEach(pair => {
			try {
				const elements = document.querySelectorAll(pair.selector);
				elements.forEach(el => {
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
		const MutationObserver = window.MutationObserver || window.WebKitMutationObserver;

		return function( obj, callback ){
			if( !obj || obj.nodeType !== 1 ) return;

			if( MutationObserver ){
				// define a new observer
				const mutationObserver = new MutationObserver(callback);

				// have the observer observe for changes in children
				mutationObserver.observe( obj, { attributes: true, childList:true, subtree:true })
				return mutationObserver
			}

			// browser support fallback
			else if( window.addEventListener ){
				obj.addEventListener('DOMNodeInserted', callback, false)
				obj.addEventListener('DOMNodeRemoved', callback, false)
				obj.addEventListener('DOMSubtreeModified', callback, false)
			}
		}
	})()

	const body = document.querySelector('body');

	observe_DOM(body, lazyload)
}

rocket_css_lazyload_launch();