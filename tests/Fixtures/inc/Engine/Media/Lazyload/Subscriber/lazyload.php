<?php

$original_images = '<img width="300" height="300" src="http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="" srcset="http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg 300w, http://example.org/wp-content/uploads/2019/05/beanie-100x100.jpg 100w, http://example.org/wp-content/uploads/2019/05/beanie-600x600.jpg 600w, http://example.org/wp-content/uploads/2019/05/beanie-150x150.jpg 150w, http://example.org/wp-content/uploads/2019/05/beanie-768x768.jpg 768w, http://example.org/wp-content/uploads/2019/05/beanie.jpg 801w" sizes="(max-width: 300px) 85vw, 300px" />';

$original_picture = '<picture><source srcset="http://example.org/wp-content/uploads/2019/03/4.sm.webp" type="image/webp"><source srcset="http://example.org/wp-content/uploads/2019/03/4.sm_.jpg"><img src="http://example.org/wp-content/uploads/2019/03/4.sm_.jpg" /></picture>';

$native_images = '<img loading="lazy" width="300" height="300" src="http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="" srcset="http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg 300w, http://example.org/wp-content/uploads/2019/05/beanie-100x100.jpg 100w, http://example.org/wp-content/uploads/2019/05/beanie-600x600.jpg 600w, http://example.org/wp-content/uploads/2019/05/beanie-150x150.jpg 150w, http://example.org/wp-content/uploads/2019/05/beanie-768x768.jpg 768w, http://example.org/wp-content/uploads/2019/05/beanie.jpg 801w" sizes="(max-width: 300px) 85vw, 300px" />';

$native_picture = '<picture><source srcset="http://example.org/wp-content/uploads/2019/03/4.sm.webp" type="image/webp"><source srcset="http://example.org/wp-content/uploads/2019/03/4.sm_.jpg"><img loading="lazy" src="http://example.org/wp-content/uploads/2019/03/4.sm_.jpg" /></picture>';

$js_images = '<img width="300" height="300" src="data:image/svg+xml,%3Csvg%20xmlns=\'http://www.w3.org/2000/svg\'%20viewBox=\'0%200%20300%20300\'%3E%3C/svg%3E" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="" data-lazy-srcset="http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg 300w, http://example.org/wp-content/uploads/2019/05/beanie-100x100.jpg 100w, http://example.org/wp-content/uploads/2019/05/beanie-600x600.jpg 600w, http://example.org/wp-content/uploads/2019/05/beanie-150x150.jpg 150w, http://example.org/wp-content/uploads/2019/05/beanie-768x768.jpg 768w, http://example.org/wp-content/uploads/2019/05/beanie.jpg 801w" data-lazy-sizes="(max-width: 300px) 85vw, 300px" data-lazy-src="http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg" /><noscript><img width="300" height="300" src="http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="" srcset="http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg 300w, http://example.org/wp-content/uploads/2019/05/beanie-100x100.jpg 100w, http://example.org/wp-content/uploads/2019/05/beanie-600x600.jpg 600w, http://example.org/wp-content/uploads/2019/05/beanie-150x150.jpg 150w, http://example.org/wp-content/uploads/2019/05/beanie-768x768.jpg 768w, http://example.org/wp-content/uploads/2019/05/beanie.jpg 801w" sizes="(max-width: 300px) 85vw, 300px" /></noscript>';

$js_picture = '<picture><source data-lazy-srcset="http://example.org/wp-content/uploads/2019/03/4.sm.webp" type="image/webp"><source data-lazy-srcset="http://example.org/wp-content/uploads/2019/03/4.sm_.jpg"><img src="data:image/svg+xml,%3Csvg%20xmlns=\'http://www.w3.org/2000/svg\'%20viewBox=\'0%200%200%200\'%3E%3C/svg%3E" data-lazy-src="http://example.org/wp-content/uploads/2019/03/4.sm_.jpg" /><noscript><img src="http://example.org/wp-content/uploads/2019/03/4.sm_.jpg" /></noscript></picture>';

$background_image_url = '<div class="awb-background-mask" style="background-image: url(http://example.org/wp-content/uploads/2019/03/4.sm.webp);">';
$background_image_url_lazy_loaded = '<div data-bg="http://example.org/wp-content/uploads/2019/03/4.sm.webp" class="awb-background-mask rocket-lazyload" style="">';

$background_image_base64 = '<div class="awb-background-mask" style="background-image: url(data:image/svg+xml;utf8,%3Csvg%20width%3D%221920%22%20height%3D%22954%22%20fill%3D%22none%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cg%20clip-path%3D%22url%28%23prefix__clip0_58_745%29%22%20fill%3D%22rgba%28254%2C245%2C238%2C1%29%22%3E%3Cpath%20d%3D%22M250.018-408.977c69.019-18.494%20142.66%201.238%20193.185%2051.763L718.31-82.107c50.525%2050.525%2070.258%20124.167%2051.764%20193.185L669.378%20486.881c-18.493%2069.019-72.403%20122.928-141.421%20141.422L152.154%20728.999c-69.019%2018.493-142.66-1.239-193.186-51.764l-275.106-275.107c-50.525-50.525-70.258-124.167-51.764-193.185l100.696-375.803c18.493-69.018%2072.403-122.928%20141.421-141.421l375.803-100.696zM1646.73%201264.15c33.13%208.88%2068.48-.59%2092.73-24.84l147.89-147.89a96.031%2096.031%200%200024.85-92.732l-54.13-202.022a96.012%2096.012%200%2000-67.89-67.882l-202.02-54.132c-33.13-8.877-68.47.595-92.73%2024.847l-147.89%20147.89a95.994%2095.994%200%2000-24.84%2092.729l54.13%20202.022a95.967%2095.967%200%200067.88%2067.88l202.02%2054.13zM1572.48%20252.659a23.996%2023.996%200%200023.18%206.211l50.5-13.533a23.97%2023.97%200%200016.97-16.97l13.54-50.506a24.004%2024.004%200%2000-6.21-23.182l-36.98-36.973a23.993%2023.993%200%2000-23.18-6.211l-50.5%2013.533a24%2024%200%2000-16.98%2016.97l-13.53%2050.506a24.004%2024.004%200%20006.21%2023.182l36.98%2036.973z%22%2F%3E%3C%2Fg%3E%3Cdefs%3E%3CclipPath%20id%3D%22prefix__clip0_58_745%22%3E%3Cpath%20fill%3D%22%23fff%22%20d%3D%22M0%200h1920v954H0z%22%2F%3E%3C%2FclipPath%3E%3C%2Fdefs%3E%3C%2Fsvg%3E);">';


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
			'exclude_src' => [],
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
			'exclude_src' => [],
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
			'exclude_src' => [],
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
			'exclude_src' => [],
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
			'exclude_src' => [],
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
			'exclude_src' => [],
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
			'exclude_src' => [],
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
	'shouldReturnSameWhenBackgroundImageBase64' => [
		'config' => [
			'is_admin' => false,
			'is_feed' => false,
			'is_preview' => false,
			'is_search' => false,
			'is_rest_request' => false,
			'is_not_lazy_load' => false,
			'is_rocket_optimize' => true,
			'is_native' => true,
			'exclude_src' => [],
			'options'  => [
				'lazyload'         => 1,
				'lazyload_iframes' => 0,
			],
		],
		'html' => [
			'original' => $background_image_base64,
		],
		'expected' => $background_image_base64,
	],
	'shouldReturnWithoutLazyLoadWhenExcluded' => [
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
			'exclude_src' => [
				'http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg 300w, http://example.org/wp-content/uploads/2019/05/beanie-100x100.jpg 100w, http://example.org/wp-content/uploads/2019/05/beanie-600x600.jpg 600w, http://example.org/wp-content/uploads/2019/05/beanie-150x150.jpg 150w, http://example.org/wp-content/uploads/2019/05/beanie-768x768.jpg 768w, http://example.org/wp-content/uploads/2019/05/beanie.jpg 801'
			],
			'exclude' => true
		],
		'html' => [
			'original' => $original_images ,
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
			'exclude' => false,
			'exclude_src' => [],
			'options'  => [
				'lazyload'         => 1,
				'lazyload_iframes' => 0,
			],
		],
		'html' => [
			'original' => $original_images . $original_picture . $background_image_url,
		],
		'expected' => $native_images . $native_picture . $background_image_url_lazy_loaded,
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
			'exclude_src' => [],
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
