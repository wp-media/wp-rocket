<?php

$observer = 'window.addEventListener(\'LazyLoad::Initialized\', function (e) {
	var lazyLoadInstance = e.detail.instance;

	if (window.MutationObserver) {
		var observer = new MutationObserver(function(mutations) {
			var image_count = 0;
			var iframe_count = 0;
			var rocketlazy_count = 0;

			mutations.forEach(function(mutation) {
				for (i = 0; i < mutation.addedNodes.length; i++) {
					if (typeof mutation.addedNodes[i].getElementsByTagName !== \'function\') {
						continue;
					}

				   if (typeof mutation.addedNodes[i].getElementsByClassName !== \'function\') {
						continue;
					}

					images = mutation.addedNodes[i].getElementsByTagName(\'img\');
					is_image = mutation.addedNodes[i].tagName == "IMG";
					iframes = mutation.addedNodes[i].getElementsByTagName(\'iframe\');
					is_iframe = mutation.addedNodes[i].tagName == "IFRAME";
					rocket_lazy = mutation.addedNodes[i].getElementsByClassName(\'rocket-lazyload\');

					image_count += images.length;
					iframe_count += iframes.length;
					rocketlazy_count += rocket_lazy.length;

					if(is_image){
						image_count += 1;
					}

					if(is_iframe){
						iframe_count += 1;
					}
				}
			} );

			if(image_count > 0 || iframe_count > 0 || rocketlazy_count > 0){
				lazyLoadInstance.update();
			}
		} );

		var b      = document.getElementsByTagName("body")[0];
		var config = { childList: true, subtree: true };

		observer.observe(b, config);
	}
}, false);';

$script_image = 'window.lazyLoadOptions = {
	elements_selector: ".rocket-lazyload",
	data_src: "lazy-src",
	data_srcset: "lazy-srcset",
	data_sizes: "lazy-sizes",
	class_loading: "lazyloading",
	class_loaded: "lazyloaded",
	threshold: 300,
	callback_loaded: function(element) {
		if ( element.tagName === "IFRAME" && element.dataset.rocketLazyload == "fitvidscompatible" ) {
			if (element.classList.contains("lazyloaded") ) {
				if (typeof window.jQuery != "undefined") {
					if (jQuery.fn.fitVids) {
						jQuery(element).parent().fitVids();
					}
				}
			}
		}
	}
};';

$script_iframe = 'window.lazyLoadOptions = {
	elements_selector: "iframe[data-lazy-src]",
	data_src: "lazy-src",
	data_srcset: "lazy-srcset",
	data_sizes: "lazy-sizes",
	class_loading: "lazyloading",
	class_loaded: "lazyloaded",
	threshold: 300,
	callback_loaded: function(element) {
		if ( element.tagName === "IFRAME" && element.dataset.rocketLazyload == "fitvidscompatible" ) {
			if (element.classList.contains("lazyloaded") ) {
				if (typeof window.jQuery != "undefined") {
					if (jQuery.fn.fitVids) {
						jQuery(element).parent().fitVids();
					}
				}
			}
		}
	}
};';

$script_both = 'window.lazyLoadOptions = {
	elements_selector: ".rocket-lazyload,iframe[data-lazy-src]",
	data_src: "lazy-src",
	data_srcset: "lazy-srcset",
	data_sizes: "lazy-sizes",
	class_loading: "lazyloading",
	class_loaded: "lazyloaded",
	threshold: 300,
	callback_loaded: function(element) {
		if ( element.tagName === "IFRAME" && element.dataset.rocketLazyload == "fitvidscompatible" ) {
			if (element.classList.contains("lazyloaded") ) {
				if (typeof window.jQuery != "undefined") {
					if (jQuery.fn.fitVids) {
						jQuery(element).parent().fitVids();
					}
				}
			}
		}
	}
};';

$script_custom_threshold = 'window.lazyLoadOptions = {
	elements_selector: ".rocket-lazyload,iframe[data-lazy-src]",
	data_src: "lazy-src",
	data_srcset: "lazy-srcset",
	data_sizes: "lazy-sizes",
	class_loading: "lazyloading",
	class_loaded: "lazyloaded",
	threshold: 500,
	callback_loaded: function(element) {
		if ( element.tagName === "IFRAME" && element.dataset.rocketLazyload == "fitvidscompatible" ) {
			if (element.classList.contains("lazyloaded") ) {
				if (typeof window.jQuery != "undefined") {
					if (jQuery.fn.fitVids) {
						jQuery(element).parent().fitVids();
					}
				}
			}
		}
	}
};';

$script_no_native_lazyload = 'window.lazyLoadOptions = {
	elements_selector: "img[data-lazy-src],.rocket-lazyload,iframe[data-lazy-src]",
	data_src: "lazy-src",
	data_srcset: "lazy-srcset",
	data_sizes: "lazy-sizes",
	class_loading: "lazyloading",
	class_loaded: "lazyloaded",
	threshold: 300,
	callback_loaded: function(element) {
		if ( element.tagName === "IFRAME" && element.dataset.rocketLazyload == "fitvidscompatible" ) {
			if (element.classList.contains("lazyloaded") ) {
				if (typeof window.jQuery != "undefined") {
					if (jQuery.fn.fitVids) {
						jQuery(element).parent().fitVids();
					}
				}
			}
		}
	};';

return [
	'script_image'              => "{$script_image}{$observer}",
	'script_iframe'             => "{$script_iframe}{$observer}",
	'script_both'               => "{$script_both}{$observer}",
	'script_custom_threshold'   => "{$script_custom_threshold}{$observer}",
	'script_no_native_lazyload' => "{$script_no_native_lazyload}{$observer}",
];
