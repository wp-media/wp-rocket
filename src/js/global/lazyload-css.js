function rocket_css_lazyload() {

	const pairs = typeof rocket_pairs !== 'undefined' && rocket_pairs !== null ? rocket_pairs : [];

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

rocket_css_lazyload();