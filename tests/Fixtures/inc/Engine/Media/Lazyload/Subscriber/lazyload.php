<?php

$original_images = '<img width="300" height="300" src="http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="" srcset="http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg 300w, http://example.org/wp-content/uploads/2019/05/beanie-100x100.jpg 100w, http://example.org/wp-content/uploads/2019/05/beanie-600x600.jpg 600w, http://example.org/wp-content/uploads/2019/05/beanie-150x150.jpg 150w, http://example.org/wp-content/uploads/2019/05/beanie-768x768.jpg 768w, http://example.org/wp-content/uploads/2019/05/beanie.jpg 801w" sizes="(max-width: 300px) 85vw, 300px" />';

$original_picture = '<picture><source srcset="http://example.org/wp-content/uploads/2019/03/4.sm.webp" type="image/webp"><source srcset="http://example.org/wp-content/uploads/2019/03/4.sm_.jpg"><img src="http://example.org/wp-content/uploads/2019/03/4.sm_.jpg" /></picture>';

$native_images = '<img loading="lazy" width="300" height="300" src="http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="" srcset="http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg 300w, http://example.org/wp-content/uploads/2019/05/beanie-100x100.jpg 100w, http://example.org/wp-content/uploads/2019/05/beanie-600x600.jpg 600w, http://example.org/wp-content/uploads/2019/05/beanie-150x150.jpg 150w, http://example.org/wp-content/uploads/2019/05/beanie-768x768.jpg 768w, http://example.org/wp-content/uploads/2019/05/beanie.jpg 801w" sizes="(max-width: 300px) 85vw, 300px" />';

$native_picture = '<picture><source srcset="http://example.org/wp-content/uploads/2019/03/4.sm.webp" type="image/webp"><source srcset="http://example.org/wp-content/uploads/2019/03/4.sm_.jpg"><img loading="lazy" src="http://example.org/wp-content/uploads/2019/03/4.sm_.jpg" /></picture>';

$js_images = '<img width="300" height="300" src="data:image/svg+xml,%3Csvg%20xmlns=\'http://www.w3.org/2000/svg\'%20viewBox=\'0%200%20300%20300\'%3E%3C/svg%3E" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="" data-lazy-srcset="http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg 300w, http://example.org/wp-content/uploads/2019/05/beanie-100x100.jpg 100w, http://example.org/wp-content/uploads/2019/05/beanie-600x600.jpg 600w, http://example.org/wp-content/uploads/2019/05/beanie-150x150.jpg 150w, http://example.org/wp-content/uploads/2019/05/beanie-768x768.jpg 768w, http://example.org/wp-content/uploads/2019/05/beanie.jpg 801w" data-lazy-sizes="(max-width: 300px) 85vw, 300px" data-lazy-src="http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg" /><noscript><img width="300" height="300" src="http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="" srcset="http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg 300w, http://example.org/wp-content/uploads/2019/05/beanie-100x100.jpg 100w, http://example.org/wp-content/uploads/2019/05/beanie-600x600.jpg 600w, http://example.org/wp-content/uploads/2019/05/beanie-150x150.jpg 150w, http://example.org/wp-content/uploads/2019/05/beanie-768x768.jpg 768w, http://example.org/wp-content/uploads/2019/05/beanie.jpg 801w" sizes="(max-width: 300px) 85vw, 300px" /></noscript>';

$js_picture = '<picture><source data-lazy-srcset="http://example.org/wp-content/uploads/2019/03/4.sm.webp" type="image/webp"><source data-lazy-srcset="http://example.org/wp-content/uploads/2019/03/4.sm_.jpg"><img src="data:image/svg+xml,%3Csvg%20xmlns=\'http://www.w3.org/2000/svg\'%20viewBox=\'0%200%200%200\'%3E%3C/svg%3E" data-lazy-src="http://example.org/wp-content/uploads/2019/03/4.sm_.jpg" /><noscript><img src="http://example.org/wp-content/uploads/2019/03/4.sm_.jpg" /></noscript></picture>';

return [
	'shouldReturnSameWhenIsAdmin' => [
		'config' => [
			'is_admin' => true,
			'is_feed' => false,
			'is_preview' => false,
			'is_search' => false,
			'is_rest_request' => false,
			'is_not_lazy_load' => false,
			'is_rocket_optimize' => false,
			'is_native' => true,
			'options'  => [
				'lazyload'         => 1,
				'lazyload_iframes' => 0,
			],
		],
		'html' => [
			'original' => $original_images,
		],
		'expected' => $original_images,
	],
	'shouldReturnSameWhenIsFeed' => [
		'config' => [
			'is_admin' => false,
			'is_feed' => true,
			'is_preview' => false,
			'is_search' => false,
			'is_rest_request' => false,
			'is_not_lazy_load' => false,
			'is_rocket_optimize' => false,
			'is_native' => true,
			'options'  => [
				'lazyload'         => 1,
				'lazyload_iframes' => 0,
			],
		],
		'html' => [
			'original' => $original_images,
		],
		'expected' => $original_images,
	],
	'shouldReturnSameWhenIsPreview' => [
		'config' => [
			'is_admin' => false,
			'is_feed' => false,
			'is_preview' => true,
			'is_search' => false,
			'is_rest_request' => false,
			'is_not_lazy_load' => false,
			'is_rocket_optimize' => false,
			'is_native' => true,
			'options'  => [
				'lazyload'         => 1,
				'lazyload_iframes' => 0,
			],
		],
		'html' => [
			'original' => $original_images,
		],
		'expected' => $original_images,
	],
	'shouldReturnSameWhenIsSearch' => [
		'config' => [
			'is_admin' => false,
			'is_feed' => false,
			'is_preview' => false,
			'is_search' => true,
			'is_rest_request' => false,
			'is_not_lazy_load' => false,
			'is_rocket_optimize' => false,
			'is_native' => true,
			'options'  => [
				'lazyload'         => 1,
				'lazyload_iframes' => 0,
			],
		],
		'html' => [
			'original' => $original_images,
		],
		'expected' => $original_images,
	],
	'shouldReturnSameWhenIsRestRequest' => [
		'config' => [
			'is_admin' => false,
			'is_feed' => false,
			'is_preview' => false,
			'is_search' => false,
			'is_rest_request' => true,
			'is_not_lazy_load' => false,
			'is_rocket_optimize' => false,
			'is_native' => true,
			'options'  => [
				'lazyload'         => 1,
				'lazyload_iframes' => 0,
			],
		],
		'html' => [
			'original' => $original_images,
		],
		'expected' => $original_images,
	],
	'shouldReturnSameWhenIsDoNotLazyload' => [
		'config' => [
			'is_admin' => false,
			'is_feed' => false,
			'is_preview' => false,
			'is_search' => false,
			'is_rest_request' => false,
			'is_not_lazy_load' => true,
			'is_rocket_optimize' => false,
			'is_native' => true,
			'options'  => [
				'lazyload'         => 1,
				'lazyload_iframes' => 0,
			],
		],
		'html' => [
			'original' => $original_images,
		],
		'expected' => $original_images,
	],
	'shouldReturnSameWhenIsDoNotRocketOptimize' => [
		'config' => [
			'is_admin' => false,
			'is_feed' => false,
			'is_preview' => false,
			'is_search' => false,
			'is_rest_request' => false,
			'is_not_lazy_load' => false,
			'is_rocket_optimize' => true,
			'is_native' => true,
			'options'  => [
				'lazyload'         => 1,
				'lazyload_iframes' => 0,
			],
		],
		'html' => [
			'original' => $original_images,
		],
		'expected' => $original_images,
	],
	'shouldReturnSameWhenIsDoNotRocketOptimize' => [
		'config' => [
			'is_admin' => false,
			'is_feed' => false,
			'is_preview' => false,
			'is_search' => false,
			'is_rest_request' => false,
			'is_not_lazy_load' => false,
			'is_rocket_optimize' => true,
			'is_native' => true,
			'options'  => [
				'lazyload'         => 1,
				'lazyload_iframes' => 0,
			],
		],
		'html' => [
			'original' => $original_images,
		],
		'expected' => $original_images,
	],
	'shouldReturnUpdatedWhenNativeLazyloadImagesOnly' => [
		'config' => [
			'is_admin' => false,
			'is_feed' => false,
			'is_preview' => false,
			'is_search' => false,
			'is_rest_request' => false,
			'is_not_lazy_load' => false,
			'is_rocket_optimize' => false,
			'is_native' => true,
			'options'  => [
				'lazyload'         => 1,
				'lazyload_iframes' => 0,
			],
		],
		'html' => [
			'original' => $original_images . $original_picture,
		],
		'expected' => $native_images . $native_picture,
	],
	'shouldReturnUpdatedWhenJSLazyloadImagesOnly' => [
		'config' => [
			'is_admin' => false,
			'is_feed' => false,
			'is_preview' => false,
			'is_search' => false,
			'is_rest_request' => false,
			'is_not_lazy_load' => false,
			'is_rocket_optimize' => false,
			'is_native' => false,
			'options'  => [
				'lazyload'         => 1,
				'lazyload_iframes' => 0,
			],
		],
		'html' => [
			'original' => $original_images . $original_picture,
			'after_picture' => $original_images . $js_picture,
		],
		'expected' => $js_images . $js_picture,
	],
];
