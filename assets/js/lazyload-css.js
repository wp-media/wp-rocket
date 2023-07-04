const styleElement = document.querySelector('#wpr-lazyload-bg');
const observer = new IntersectionObserver(entries => {
	entries.forEach(entry => {
		if (entry.isIntersecting) {
			const pair = pairs.find(s => entry.target.matches(s.selector));
			if (pair) {
				styleElement.innerHTML += pair.style;
				pair.elements.forEach(el => {
					el.setAttribute('data-rocket-lazy-bg', 'loaded');
					// Stop observing the target element
					observer.unobserve(el);
				});
			}
		}
	});
}, {
	rootMargin: '300px'
});

pairs.forEach(pair => {
	const elements = document.querySelectorAll(pair.selector);
	elements.forEach(el => {
		observer.observe(el);
		// Save el in the pair object (create a new empty array if it doesn't exist)
		(pair.selector.elements ||= []).push(el);
	});
});