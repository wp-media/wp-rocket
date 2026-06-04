function rocket_css_lazyload_launch() {

	const usable_pairs = typeof rocket_pairs === 'undefined' ? [] : rocket_pairs;
	const excluded_pairs = typeof rocket_excluded_pairs === 'undefined' ? [] : rocket_excluded_pairs;

	excluded_pairs.forEach(pair => {
		const nodes = document.querySelectorAll(pair.selector);
		nodes.forEach(el => {
			el.setAttribute(`data-rocket-lazy-bg-${pair.hash}`, 'excluded');
		});
	});



	const styleElement = document.querySelector('#wpr-lazyload-bg-container');

	const threshold = rocket_lazyload_css_data.threshold || 300;

	const observer = new IntersectionObserver(entries => {
		entries.forEach(entry => {
			if (entry.isIntersecting) {
				const pairs = usable_pairs.filter(s => entry.target.matches(s.selector));
				pairs.forEach(pair => {
					if (pair) {
						var new_style_element = document.createElement('style');
						new_style_element.textContent = pair.style;
						styleElement.insertAdjacentElement('afterend', new_style_element);

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

	function lazyload() {
		usable_pairs.forEach(pair => {
			try {

				const elements = document.querySelectorAll(pair.selector);
				elements.forEach(el => {

					const status = el.getAttribute(`data-rocket-lazy-bg-${pair.hash}`);

					if(	status === 'loaded' || status === 'excluded' ) {
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

	function observe_DOM( obj, callback ) {
		if( !obj || obj.nodeType !== 1 ) return;

		// Create a new observer or recycle existing one
		if (window.rocket_lzl_mo) {
			window.rocket_lzl_mo.disconnect();
		} else {
			window.rocket_lzl_mo = new MutationObserver(callback);
		}

		rocket_lzl_mo.observe( obj, { attributes: true, childList:true, subtree:true });
	}

	observe_DOM(document.body, lazyload);
}

rocket_css_lazyload_launch();