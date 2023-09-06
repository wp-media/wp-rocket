<?php

$lazyload_script = require __DIR__ . '/content/insertLazyloadScript.php';
$inline_script   = require __DIR__ . '/content/getInlineLazyloadScript.php';

return [
	'test_data' => [
		'testShouldReturnNothingWhenIsAdmin' => [
			'config' => [
				'is_admin' => true,
				'options'  => [
					'lazyload'         => 1,
					'lazyload_iframes' => 0,
				],
			],
			'expected' => [
				'unit' => [
					'inline_script' => '',
					'script'        => '',
					'result'        => '',
				],
				'integration' => '',
			],
		],
		'testShouldReturnNothingWhenIsFeed' => [
			'config' => [
				'is_feed' => true,
				'options'  => [
					'lazyload'         => 1,
					'lazyload_iframes' => 0,
				],
			],
			'expected' => [
				'unit' => [
					'inline_script' => '',
					'script'        => '',
					'result'        => '',
				],
				'integration' => '',
			],
		],
		'testShouldReturnNothingWhenIsPreview' => [
			'config' => [
				'is_preview' => true,
				'options'  => [
					'lazyload'         => 1,
					'lazyload_iframes' => 0,
				],
			],
			'expected' => [
				'unit' => [
					'inline_script' => '',
					'script'        => '',
					'result'        => '',
				],
				'integration' => '',
			],
		],
		'testShouldReturnNothingWhenIsSearch' => [
			'config' => [
				'is_search' => true,
				'options'  => [
					'lazyload'         => 1,
					'lazyload_iframes' => 0,
				],
			],
			'expected' => [
				'unit' => [
					'inline_script' => '',
					'script'        => '',
					'result'        => '',
				],
				'integration' => '',
			],
		],
		'testShouldReturnNothingWhenIsRest' => [
			'config' => [
				'is_rest_request' => true,
				'options'  => [
					'lazyload'         => 1,
					'lazyload_iframes' => 0,
				],
			],
			'expected' => [
				'unit' => [
					'inline_script' => '',
					'script'        => '',
					'result'        => '',
				],
				'integration' => '',
			],
		],
		'testShouldReturnNothingWhenIsNotRocketOptimize' => [
			'config' => [
				'is_not_rocket_optimize' => true,
				'options'  => [
					'lazyload'         => 1,
					'lazyload_iframes' => 0,
				],
			],
			'expected' => [
				'unit' => [
					'inline_script' => '',
					'script'        => '',
					'result'        => '',
				],
				'integration' => '',
			],
		],
		'testShouldReturnNothingWhenLazyloadDisabled' => [
			'config' => [
				'options'  => [
					'lazyload'         => 0,
					'lazyload_iframes' => 0,
				],
			],
			'expected' => [
				'unit' => [
					'inline_script' => '',
					'script'        => '',
					'result'        => '',
				],
				'integration' => '',
			],
		],
		'testShouldReturnLazyloadForImagesOnly' => [
			'config' => [
				'options'  => [
					'lazyload'          => 1,
					'lazyload_iframes'  => 0,
					'use_native'        => false,
					'use_native_images' => true,
				],
			],
			'expected' => [
				'unit' => [
					'inline_script' => $inline_script['script_image'],
					'script'        => $lazyload_script['min_script'],
					'result'        => "<script>{$inline_script['script_image']}</script>{$lazyload_script['min_script']}",
				],
				'integration' => '<script>
			window.lazyLoadOptions=[{elements_selector:".rocket-lazyload",data_src:"lazy-src",data_srcset:"lazy-srcset",data_sizes:"lazy-sizes",class_loading:"lazyloading",class_loaded:"lazyloaded",threshold:300,callback_loaded:function(element){if(element.tagName==="IFRAME"&&element.dataset.rocketLazyload=="fitvidscompatible"){if(element.classList.contains("lazyloaded")){if(typeof window.jQuery!="undefined"){if(jQuery.fn.fitVids){jQuery(element).parent().fitVids()}}}}}},{elements_selector:".rocket-lazyload",data_src:"lazy-src",data_srcset:"lazy-srcset",data_sizes:"lazy-sizes",class_loading:"lazyloading",class_loaded:"lazyloaded",threshold:300,}];window.addEventListener(\'LazyLoad::Initialized\',function(e){var lazyLoadInstance=e.detail.instance;if(window.MutationObserver){var observer=new MutationObserver(function(mutations){var image_count=0;var iframe_count=0;var rocketlazy_count=0;mutations.forEach(function(mutation){for(var i=0;i<mutation.addedNodes.length;i++){if(typeof mutation.addedNodes[i].getElementsByTagName!==\'function\'){continue}
if(typeof mutation.addedNodes[i].getElementsByClassName!==\'function\'){continue}
images=mutation.addedNodes[i].getElementsByTagName(\'img\');is_image=mutation.addedNodes[i].tagName=="IMG";iframes=mutation.addedNodes[i].getElementsByTagName(\'iframe\');is_iframe=mutation.addedNodes[i].tagName=="IFRAME";rocket_lazy=mutation.addedNodes[i].getElementsByClassName(\'rocket-lazyload\');image_count+=images.length;iframe_count+=iframes.length;rocketlazy_count+=rocket_lazy.length;if(is_image){image_count+=1}
if(is_iframe){iframe_count+=1}}});if(image_count>0||iframe_count>0||rocketlazy_count>0){lazyLoadInstance.update()}});var b=document.getElementsByTagName("body")[0];var config={childList:!0,subtree:!0};observer.observe(b,config)}},!1)</script><script data-no-minify="1" async src="http://example.org/wp-content/plugins/wp-rocket/assets/js/lazyload/17.8.3/lazyload.min.js"></script>',
			],
		],

		'testShouldReturnLazyloadForIframesOnly' => [
			'config' => [
				'options'  => [
					'lazyload'          => 0,
					'lazyload_iframes'  => 1,
					'use_native'        => false,
					'use_native_images' => true,
				],
			],
			'expected' => [
				'unit' => [
					'inline_script' => $inline_script['script_iframe'],
					'script'        => $lazyload_script['min_script'],
					'result'        => "<script>{$inline_script['script_iframe']}</script>{$lazyload_script['min_script']}",
				],
				'integration' => '<script>
			window.lazyLoadOptions={elements_selector:"iframe[data-lazy-src]",data_src:"lazy-src",data_srcset:"lazy-srcset",data_sizes:"lazy-sizes",class_loading:"lazyloading",class_loaded:"lazyloaded",threshold:300,callback_loaded:function(element){if(element.tagName==="IFRAME"&&element.dataset.rocketLazyload=="fitvidscompatible"){if(element.classList.contains("lazyloaded")){if(typeof window.jQuery!="undefined"){if(jQuery.fn.fitVids){jQuery(element).parent().fitVids()}}}}}};window.addEventListener(\'LazyLoad::Initialized\',function(e){var lazyLoadInstance=e.detail.instance;if(window.MutationObserver){var observer=new MutationObserver(function(mutations){var image_count=0;var iframe_count=0;var rocketlazy_count=0;mutations.forEach(function(mutation){for(var i=0;i<mutation.addedNodes.length;i++){if(typeof mutation.addedNodes[i].getElementsByTagName!==\'function\'){continue}
if(typeof mutation.addedNodes[i].getElementsByClassName!==\'function\'){continue}
images=mutation.addedNodes[i].getElementsByTagName(\'img\');is_image=mutation.addedNodes[i].tagName=="IMG";iframes=mutation.addedNodes[i].getElementsByTagName(\'iframe\');is_iframe=mutation.addedNodes[i].tagName=="IFRAME";rocket_lazy=mutation.addedNodes[i].getElementsByClassName(\'rocket-lazyload\');image_count+=images.length;iframe_count+=iframes.length;rocketlazy_count+=rocket_lazy.length;if(is_image){image_count+=1}
if(is_iframe){iframe_count+=1}}});if(image_count>0||iframe_count>0||rocketlazy_count>0){lazyLoadInstance.update()}});var b=document.getElementsByTagName("body")[0];var config={childList:!0,subtree:!0};observer.observe(b,config)}},!1)</script><script data-no-minify="1" async src="http://example.org/wp-content/plugins/wp-rocket/assets/js/lazyload/17.8.3/lazyload.min.js"></script>',
			],
		],
		'testShouldReturnLazyloadForImagesAndIframes' => [
			'config' => [
				'options'  => [
					'lazyload'          => 1,
					'lazyload_iframes'  => 1,
					'use_native'        => false,
					'use_native_images' => true,
				],
			],
			'expected' => [
				'unit' => [
					'inline_script' => $inline_script['script_both'],
					'script'        => $lazyload_script['min_script'],
					'result'        => "<script>{$inline_script['script_both']}</script>{$lazyload_script['min_script']}",
				],
				'integration' => '<script>
			window.lazyLoadOptions=[{elements_selector:".rocket-lazyload,iframe[data-lazy-src]",data_src:"lazy-src",data_srcset:"lazy-srcset",data_sizes:"lazy-sizes",class_loading:"lazyloading",class_loaded:"lazyloaded",threshold:300,callback_loaded:function(element){if(element.tagName==="IFRAME"&&element.dataset.rocketLazyload=="fitvidscompatible"){if(element.classList.contains("lazyloaded")){if(typeof window.jQuery!="undefined"){if(jQuery.fn.fitVids){jQuery(element).parent().fitVids()}}}}}},{elements_selector:".rocket-lazyload",data_src:"lazy-src",data_srcset:"lazy-srcset",data_sizes:"lazy-sizes",class_loading:"lazyloading",class_loaded:"lazyloaded",threshold:300,}];window.addEventListener(\'LazyLoad::Initialized\',function(e){var lazyLoadInstance=e.detail.instance;if(window.MutationObserver){var observer=new MutationObserver(function(mutations){var image_count=0;var iframe_count=0;var rocketlazy_count=0;mutations.forEach(function(mutation){for(var i=0;i<mutation.addedNodes.length;i++){if(typeof mutation.addedNodes[i].getElementsByTagName!==\'function\'){continue}
if(typeof mutation.addedNodes[i].getElementsByClassName!==\'function\'){continue}
images=mutation.addedNodes[i].getElementsByTagName(\'img\');is_image=mutation.addedNodes[i].tagName=="IMG";iframes=mutation.addedNodes[i].getElementsByTagName(\'iframe\');is_iframe=mutation.addedNodes[i].tagName=="IFRAME";rocket_lazy=mutation.addedNodes[i].getElementsByClassName(\'rocket-lazyload\');image_count+=images.length;iframe_count+=iframes.length;rocketlazy_count+=rocket_lazy.length;if(is_image){image_count+=1}
if(is_iframe){iframe_count+=1}}});if(image_count>0||iframe_count>0||rocketlazy_count>0){lazyLoadInstance.update()}});var b=document.getElementsByTagName("body")[0];var config={childList:!0,subtree:!0};observer.observe(b,config)}},!1)</script><script data-no-minify="1" async src="http://example.org/wp-content/plugins/wp-rocket/assets/js/lazyload/17.8.3/lazyload.min.js"></script>',
			],
		],
		'testShouldReturnLazyloadForImagesAndIframesWithCustomThreshold' => [
			'config' => [
				'options'  => [
					'lazyload'          => 1,
					'lazyload_iframes'  => 1,
					'threshold'         => 500,
					'use_native'        => false,
					'use_native_images' => true,
				],
			],
			'expected' => [
				'unit' => [
					'inline_script' => $inline_script['script_custom_threshold'],
					'script'        => $lazyload_script['min_script'],
					'result'        => "<script>{$inline_script['script_custom_threshold']}</script>{$lazyload_script['min_script']}",
				],
				'integration' => '<script>
			window.lazyLoadOptions=[{elements_selector:".rocket-lazyload,iframe[data-lazy-src]",data_src:"lazy-src",data_srcset:"lazy-srcset",data_sizes:"lazy-sizes",class_loading:"lazyloading",class_loaded:"lazyloaded",threshold:500,callback_loaded:function(element){if(element.tagName==="IFRAME"&&element.dataset.rocketLazyload=="fitvidscompatible"){if(element.classList.contains("lazyloaded")){if(typeof window.jQuery!="undefined"){if(jQuery.fn.fitVids){jQuery(element).parent().fitVids()}}}}}},{elements_selector:".rocket-lazyload",data_src:"lazy-src",data_srcset:"lazy-srcset",data_sizes:"lazy-sizes",class_loading:"lazyloading",class_loaded:"lazyloaded",threshold:500,}];window.addEventListener(\'LazyLoad::Initialized\',function(e){var lazyLoadInstance=e.detail.instance;if(window.MutationObserver){var observer=new MutationObserver(function(mutations){var image_count=0;var iframe_count=0;var rocketlazy_count=0;mutations.forEach(function(mutation){for(var i=0;i<mutation.addedNodes.length;i++){if(typeof mutation.addedNodes[i].getElementsByTagName!==\'function\'){continue}
if(typeof mutation.addedNodes[i].getElementsByClassName!==\'function\'){continue}
images=mutation.addedNodes[i].getElementsByTagName(\'img\');is_image=mutation.addedNodes[i].tagName=="IMG";iframes=mutation.addedNodes[i].getElementsByTagName(\'iframe\');is_iframe=mutation.addedNodes[i].tagName=="IFRAME";rocket_lazy=mutation.addedNodes[i].getElementsByClassName(\'rocket-lazyload\');image_count+=images.length;iframe_count+=iframes.length;rocketlazy_count+=rocket_lazy.length;if(is_image){image_count+=1}
if(is_iframe){iframe_count+=1}}});if(image_count>0||iframe_count>0||rocketlazy_count>0){lazyLoadInstance.update()}});var b=document.getElementsByTagName("body")[0];var config={childList:!0,subtree:!0};observer.observe(b,config)}},!1)</script><script data-no-minify="1" async src="http://example.org/wp-content/plugins/wp-rocket/assets/js/lazyload/17.8.3/lazyload.min.js"></script>',
			],
		],
		'testShouldReturnLazyloadForImagesAndIframesWithoutNativeLazyloadForImages' => [
			'config' => [
				'options'  => [
					'lazyload'         => 1,
					'lazyload_iframes' => 1,
					'use_native'        => false,
					'use_native_images' => false,
				],
			],
			'expected' => [
				'unit' => [
					'inline_script' => $inline_script['script_no_native_lazyload'],
					'script'        => $lazyload_script['min_script'],
					'result'        => "<script>{$inline_script['script_no_native_lazyload']}</script>{$lazyload_script['min_script']}",
				],
				'integration' => '<script>
			window.lazyLoadOptions=[{elements_selector:"img[data-lazy-src],.rocket-lazyload,iframe[data-lazy-src]",data_src:"lazy-src",data_srcset:"lazy-srcset",data_sizes:"lazy-sizes",class_loading:"lazyloading",class_loaded:"lazyloaded",threshold:300,callback_loaded:function(element){if(element.tagName==="IFRAME"&&element.dataset.rocketLazyload=="fitvidscompatible"){if(element.classList.contains("lazyloaded")){if(typeof window.jQuery!="undefined"){if(jQuery.fn.fitVids){jQuery(element).parent().fitVids()}}}}}},{elements_selector:".rocket-lazyload",data_src:"lazy-src",data_srcset:"lazy-srcset",data_sizes:"lazy-sizes",class_loading:"lazyloading",class_loaded:"lazyloaded",threshold:300,}];window.addEventListener(\'LazyLoad::Initialized\',function(e){var lazyLoadInstance=e.detail.instance;if(window.MutationObserver){var observer=new MutationObserver(function(mutations){var image_count=0;var iframe_count=0;var rocketlazy_count=0;mutations.forEach(function(mutation){for(var i=0;i<mutation.addedNodes.length;i++){if(typeof mutation.addedNodes[i].getElementsByTagName!==\'function\'){continue}
if(typeof mutation.addedNodes[i].getElementsByClassName!==\'function\'){continue}
images=mutation.addedNodes[i].getElementsByTagName(\'img\');is_image=mutation.addedNodes[i].tagName=="IMG";iframes=mutation.addedNodes[i].getElementsByTagName(\'iframe\');is_iframe=mutation.addedNodes[i].tagName=="IFRAME";rocket_lazy=mutation.addedNodes[i].getElementsByClassName(\'rocket-lazyload\');image_count+=images.length;iframe_count+=iframes.length;rocketlazy_count+=rocket_lazy.length;if(is_image){image_count+=1}
if(is_iframe){iframe_count+=1}}});if(image_count>0||iframe_count>0||rocketlazy_count>0){lazyLoadInstance.update()}});var b=document.getElementsByTagName("body")[0];var config={childList:!0,subtree:!0};observer.observe(b,config)}},!1)</script><script data-no-minify="1" async src="http://example.org/wp-content/plugins/wp-rocket/assets/js/lazyload/17.8.3/lazyload.min.js"></script>',
			],
		],
	],
];
