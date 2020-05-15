<?php

$lazyload_script = require __DIR__ . '/content/insertLazyloadScript.php';
$inline_script   = require __DIR__ . '/content/getInlineLazyloadScript.php';

return [
//	'testShouldReturnNothingWhenLazyloadDisabled' => [
//		'options'  => [
//			'lazyload'         => 0,
//			'lazyload_iframes' => 0,
//		],
//		'expected' => [
//			'unit' => [
//				'inline_script' => '',
//				'script'        => '',
//				'result'        => '',
//			],
//			'integration' => '',
//		],
//	],
	'testShouldReturnLazyloadForImagesOnly' => [
		'options'  => [
			'lazyload'         => 1,
			'lazyload_iframes' => 0,
		],
		'expected' => [
			'unit' => [
				'inline_script' => $inline_script['script_image'],
				'script'        => $lazyload_script['min_script'],
				'result'        => "<script>{$inline_script['script_image']}</script>{$lazyload_script['min_script']}",
			],
			'integration' => '<script>
		window.lazyLoadOptions={elements_selector:"img[data-lazy-src],.rocket-lazyload",data_src:"lazy-src",data_srcset:"lazy-srcset",data_sizes:"lazy-sizes",class_loading:"lazyloading",class_loaded:"lazyloaded",threshold:300,callback_loaded:function(element){if(element.tagName==="IFRAME"&&element.dataset.rocketLazyload=="fitvidscompatible"){if(element.classList.contains("lazyloaded")){if(typeof window.jQuery!="undefined"){if(jQuery.fn.fitVids){jQuery(element).parent().fitVids()}}}}}};window.addEventListener(\'LazyLoad::Initialized\',function(e){var lazyLoadInstance=e.detail.instance;if(window.MutationObserver){var observer=new MutationObserver(function(mutations){var image_count=0;var iframe_count=0;var rocketlazy_count=0;mutations.forEach(function(mutation){for(i=0;i<mutation.addedNodes.length;i++){if(typeof mutation.addedNodes[i].getElementsByTagName!==\'function\'){return}
if(typeof mutation.addedNodes[i].getElementsByClassName!==\'function\'){return}
images=mutation.addedNodes[i].getElementsByTagName(\'img\');is_image=mutation.addedNodes[i].tagName=="IMG";iframes=mutation.addedNodes[i].getElementsByTagName(\'iframe\');is_iframe=mutation.addedNodes[i].tagName=="IFRAME";rocket_lazy=mutation.addedNodes[i].getElementsByClassName(\'rocket-lazyload\');image_count+=images.length;iframe_count+=iframes.length;rocketlazy_count+=rocket_lazy.length;if(is_image){image_count+=1}
if(is_iframe){iframe_count+=1}}});if(image_count>0||iframe_count>0||rocketlazy_count>0){lazyLoadInstance.update()}});var b=document.getElementsByTagName("body")[0];var config={childList:!0,subtree:!0};observer.observe(b,config)}},!1)</script><script data-no-minify="1" async src="http://example.org/wp-content/plugins/wp-rocket/assets/js/lazyload/15.1.1/lazyload.min.js"></script>',
		],
	],

//	'testShouldReturnLazyloadForIframesOnly' => [
//		'options'  => [
//			'lazyload'         => 0,
//			'lazyload_iframes' => 1,
//		],
//		'expected' => [
//			'unit' => [
//				'inline_script' => $inline_script['script_iframe'],
//				'script'        => $lazyload_script['min_script'],
//				'result'        => "<script>{$inline_script['script_iframe']}</script>{$lazyload_script['min_script']}",
//			],
//			'integration' => '<script>
//		window.lazyLoadOptions={elements_selector:"iframe[data-lazy-src]",data_src:"lazy-src",data_srcset:"lazy-srcset",data_sizes:"lazy-sizes",class_loading:"lazyloading",class_loaded:"lazyloaded",threshold:300,callback_loaded:function(element){if(element.tagName==="IFRAME"&&element.dataset.rocketLazyload=="fitvidscompatible"){if(element.classList.contains("lazyloaded")){if(typeof window.jQuery!="undefined"){if(jQuery.fn.fitVids){jQuery(element).parent().fitVids()}}}}}};window.addEventListener(\'LazyLoad::Initialized\',function(e){var lazyLoadInstance=e.detail.instance;if(window.MutationObserver){var observer=new MutationObserver(function(mutations){var image_count=0;var iframe_count=0;var rocketlazy_count=0;mutations.forEach(function(mutation){for(i=0;i<mutation.addedNodes.length;i++){if(typeof mutation.addedNodes[i].getElementsByTagName!==\'function\'){return}
//if(typeof mutation.addedNodes[i].getElementsByClassName!==\'function\'){return}
//images=mutation.addedNodes[i].getElementsByTagName(\'img\');is_image=mutation.addedNodes[i].tagName=="IMG";iframes=mutation.addedNodes[i].getElementsByTagName(\'iframe\');is_iframe=mutation.addedNodes[i].tagName=="IFRAME";rocket_lazy=mutation.addedNodes[i].getElementsByClassName(\'rocket-lazyload\');image_count+=images.length;iframe_count+=iframes.length;rocketlazy_count+=rocket_lazy.length;if(is_image){image_count+=1}
//if(is_iframe){iframe_count+=1}}});if(image_count>0||iframe_count>0||rocketlazy_count>0){lazyLoadInstance.update()}});var b=document.getElementsByTagName("body")[0];var config={childList:!0,subtree:!0};observer.observe(b,config)}},!1)</script><script data-no-minify="1" async src="http://example.org/wp-content/plugins/wp-rocket/assets/js/lazyload/15.1.1/lazyload.min.js"></script>',
//		],
//	],
//	'testShouldReturnLazyloadForImagesAndIframes' => [
//		'options'  => [
//			'lazyload'         => 1,
//			'lazyload_iframes' => 1,
//		],
//		'expected' => [
//			'unit' => [
//				'inline_script' => $inline_script['script_both'],
//				'script'        => $lazyload_script['min_script'],
//				'result'        => "<script>{$inline_script['script_both']}</script>{$lazyload_script['min_script']}",
//			],
//			'integration' => '<script>
//		window.lazyLoadOptions={elements_selector:"img[data-lazy-src],.rocket-lazyload,iframe[data-lazy-src]",data_src:"lazy-src",data_srcset:"lazy-srcset",data_sizes:"lazy-sizes",class_loading:"lazyloading",class_loaded:"lazyloaded",threshold:300,callback_loaded:function(element){if(element.tagName==="IFRAME"&&element.dataset.rocketLazyload=="fitvidscompatible"){if(element.classList.contains("lazyloaded")){if(typeof window.jQuery!="undefined"){if(jQuery.fn.fitVids){jQuery(element).parent().fitVids()}}}}}};window.addEventListener(\'LazyLoad::Initialized\',function(e){var lazyLoadInstance=e.detail.instance;if(window.MutationObserver){var observer=new MutationObserver(function(mutations){var image_count=0;var iframe_count=0;var rocketlazy_count=0;mutations.forEach(function(mutation){for(i=0;i<mutation.addedNodes.length;i++){if(typeof mutation.addedNodes[i].getElementsByTagName!==\'function\'){return}
//if(typeof mutation.addedNodes[i].getElementsByClassName!==\'function\'){return}
//images=mutation.addedNodes[i].getElementsByTagName(\'img\');is_image=mutation.addedNodes[i].tagName=="IMG";iframes=mutation.addedNodes[i].getElementsByTagName(\'iframe\');is_iframe=mutation.addedNodes[i].tagName=="IFRAME";rocket_lazy=mutation.addedNodes[i].getElementsByClassName(\'rocket-lazyload\');image_count+=images.length;iframe_count+=iframes.length;rocketlazy_count+=rocket_lazy.length;if(is_image){image_count+=1}
//if(is_iframe){iframe_count+=1}}});if(image_count>0||iframe_count>0||rocketlazy_count>0){lazyLoadInstance.update()}});var b=document.getElementsByTagName("body")[0];var config={childList:!0,subtree:!0};observer.observe(b,config)}},!1)</script><script data-no-minify="1" async src="http://example.org/wp-content/plugins/wp-rocket/assets/js/lazyload/15.1.1/lazyload.min.js"></script>',
//		],
//	],
//	'testShouldReturnLazyloadForImagesAndIframesWithCustomThreshold' => [
//		'options'  => [
//			'lazyload'         => 1,
//			'lazyload_iframes' => 1,
//			'threshold'        => 500,
//		],
//		'expected' => [
//			'unit' => [
//				'inline_script' => $inline_script['script_custom_threshold'],
//				'script'        => $lazyload_script['min_script'],
//				'result'        => "<script>{$inline_script['script_custom_threshold']}</script>{$lazyload_script['min_script']}",
//			],
//			'integration' => '<script>
//		window.lazyLoadOptions={elements_selector:"img[data-lazy-src],.rocket-lazyload,iframe[data-lazy-src]",data_src:"lazy-src",data_srcset:"lazy-srcset",data_sizes:"lazy-sizes",class_loading:"lazyloading",class_loaded:"lazyloaded",threshold:500,callback_loaded:function(element){if(element.tagName==="IFRAME"&&element.dataset.rocketLazyload=="fitvidscompatible"){if(element.classList.contains("lazyloaded")){if(typeof window.jQuery!="undefined"){if(jQuery.fn.fitVids){jQuery(element).parent().fitVids()}}}}}};window.addEventListener(\'LazyLoad::Initialized\',function(e){var lazyLoadInstance=e.detail.instance;if(window.MutationObserver){var observer=new MutationObserver(function(mutations){var image_count=0;var iframe_count=0;var rocketlazy_count=0;mutations.forEach(function(mutation){for(i=0;i<mutation.addedNodes.length;i++){if(typeof mutation.addedNodes[i].getElementsByTagName!==\'function\'){return}
//if(typeof mutation.addedNodes[i].getElementsByClassName!==\'function\'){return}
//images=mutation.addedNodes[i].getElementsByTagName(\'img\');is_image=mutation.addedNodes[i].tagName=="IMG";iframes=mutation.addedNodes[i].getElementsByTagName(\'iframe\');is_iframe=mutation.addedNodes[i].tagName=="IFRAME";rocket_lazy=mutation.addedNodes[i].getElementsByClassName(\'rocket-lazyload\');image_count+=images.length;iframe_count+=iframes.length;rocketlazy_count+=rocket_lazy.length;if(is_image){image_count+=1}
//if(is_iframe){iframe_count+=1}}});if(image_count>0||iframe_count>0||rocketlazy_count>0){lazyLoadInstance.update()}});var b=document.getElementsByTagName("body")[0];var config={childList:!0,subtree:!0};observer.observe(b,config)}},!1)</script><script data-no-minify="1" async src="http://example.org/wp-content/plugins/wp-rocket/assets/js/lazyload/15.1.1/lazyload.min.js"></script>',
//		],
//	],
//	'testShouldReturnLazyloadForImagesAndIframesWithPolyfill' => [
//		'options'  => [
//			'lazyload'         => 1,
//			'lazyload_iframes' => 1,
//			'polyfill'         => true,
//		],
//		'expected' => [
//			'unit' => [
//				'inline_script' => $inline_script['script_both'],
//				'script'        => $lazyload_script['min_script_polyfill'],
//				'result'        => "<script>{$inline_script['script_both']}</script>{$lazyload_script['min_script_polyfill']}",
//			],
//			'integration' => '<script>
//		window.lazyLoadOptions={elements_selector:"img[data-lazy-src],.rocket-lazyload,iframe[data-lazy-src]",data_src:"lazy-src",data_srcset:"lazy-srcset",data_sizes:"lazy-sizes",class_loading:"lazyloading",class_loaded:"lazyloaded",threshold:300,callback_loaded:function(element){if(element.tagName==="IFRAME"&&element.dataset.rocketLazyload=="fitvidscompatible"){if(element.classList.contains("lazyloaded")){if(typeof window.jQuery!="undefined"){if(jQuery.fn.fitVids){jQuery(element).parent().fitVids()}}}}}};window.addEventListener(\'LazyLoad::Initialized\',function(e){var lazyLoadInstance=e.detail.instance;if(window.MutationObserver){var observer=new MutationObserver(function(mutations){var image_count=0;var iframe_count=0;var rocketlazy_count=0;mutations.forEach(function(mutation){for(i=0;i<mutation.addedNodes.length;i++){if(typeof mutation.addedNodes[i].getElementsByTagName!==\'function\'){return}
//if(typeof mutation.addedNodes[i].getElementsByClassName!==\'function\'){return}
//images=mutation.addedNodes[i].getElementsByTagName(\'img\');is_image=mutation.addedNodes[i].tagName=="IMG";iframes=mutation.addedNodes[i].getElementsByTagName(\'iframe\');is_iframe=mutation.addedNodes[i].tagName=="IFRAME";rocket_lazy=mutation.addedNodes[i].getElementsByClassName(\'rocket-lazyload\');image_count+=images.length;iframe_count+=iframes.length;rocketlazy_count+=rocket_lazy.length;if(is_image){image_count+=1}
//if(is_iframe){iframe_count+=1}}});if(image_count>0||iframe_count>0||rocketlazy_count>0){lazyLoadInstance.update()}});var b=document.getElementsByTagName("body")[0];var config={childList:!0,subtree:!0};observer.observe(b,config)}},!1)</script><script crossorigin="anonymous" src="https://polyfill.io/v3/polyfill.min.js?flags=gated&features=default%2CIntersectionObserver%2CIntersectionObserverEntry"></script><script data-no-minify="1" async src="http://example.org/wp-content/plugins/wp-rocket/assets/js/lazyload/15.1.1/lazyload.min.js"></script>',
//		],
//	],
//	'testShouldReturnLazyloadForImagesAndIframesWithNativeLazyload' => [
//		'options'  => [
//			'lazyload'         => 1,
//			'lazyload_iframes' => 1,
//			'use_native'       => true,
//		],
//		'expected' => [
//			'unit' => [
//				'inline_script' => $inline_script['script_native_lazyload'],
//				'script'        => $lazyload_script['min_script'],
//				'result'        => "<script>{$inline_script['script_native_lazyload']}</script>{$lazyload_script['min_script']}",
//			],
//			'integration' => '<script>
//		window.lazyLoadOptions={elements_selector:"[loading=lazy],img[data-lazy-src],.rocket-lazyload,iframe[data-lazy-src]",data_src:"lazy-src",data_srcset:"lazy-srcset",data_sizes:"lazy-sizes",class_loading:"lazyloading",class_loaded:"lazyloaded",threshold:300,callback_loaded:function(element){if(element.tagName==="IFRAME"&&element.dataset.rocketLazyload=="fitvidscompatible"){if(element.classList.contains("lazyloaded")){if(typeof window.jQuery!="undefined"){if(jQuery.fn.fitVids){jQuery(element).parent().fitVids()}}}}},use_native:!0};window.addEventListener(\'LazyLoad::Initialized\',function(e){var lazyLoadInstance=e.detail.instance;if(window.MutationObserver){var observer=new MutationObserver(function(mutations){var image_count=0;var iframe_count=0;var rocketlazy_count=0;mutations.forEach(function(mutation){for(i=0;i<mutation.addedNodes.length;i++){if(typeof mutation.addedNodes[i].getElementsByTagName!==\'function\'){return}
//if(typeof mutation.addedNodes[i].getElementsByClassName!==\'function\'){return}
//images=mutation.addedNodes[i].getElementsByTagName(\'img\');is_image=mutation.addedNodes[i].tagName=="IMG";iframes=mutation.addedNodes[i].getElementsByTagName(\'iframe\');is_iframe=mutation.addedNodes[i].tagName=="IFRAME";rocket_lazy=mutation.addedNodes[i].getElementsByClassName(\'rocket-lazyload\');image_count+=images.length;iframe_count+=iframes.length;rocketlazy_count+=rocket_lazy.length;if(is_image){image_count+=1}
//if(is_iframe){iframe_count+=1}}});if(image_count>0||iframe_count>0||rocketlazy_count>0){lazyLoadInstance.update()}});var b=document.getElementsByTagName("body")[0];var config={childList:!0,subtree:!0};observer.observe(b,config)}},!1)</script><script data-no-minify="1" async src="http://example.org/wp-content/plugins/wp-rocket/assets/js/lazyload/15.1.1/lazyload.min.js"></script>',
//		],
//	],
];
