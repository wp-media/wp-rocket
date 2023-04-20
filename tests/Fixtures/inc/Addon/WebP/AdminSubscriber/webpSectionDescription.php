<?php

return [
	'shouldReturnTextWhenNoPluginsAndCacheOptionDisabled' => [
		'config' => [
			'webp' => 0,
			'cdn' => 0,
			'convert_webp' => 0,
			'serving_webp' => 0,
			'serving_webp_cdn' => 0,
			'filter' => false,
		],
		'expected' => '<strong>We have not detected any compatible WebP plugin!</strong><br>',
	],
	'shouldReturnTextWhenNoPluginsAndCacheOptionEnabled' => [
		'config' => [
			'webp' => 1,
			'cdn' => 0,
			'convert_webp' => 0,
			'serving_webp' => 0,
			'serving_webp_cdn' => 0,
			'filter' => false,
		],
		'expected' => 'WP Rocket will create separate cache files to serve your WebP images.',
	],
	'shouldReturnTextWhenPluginCreatingWebpAvailableAndCacheOptionDisabled' => [
		'config' => [
			'webp' => 0,
			'cdn' => 0,
			'convert_webp' => 1,
			'serving_webp' => 0,
			'serving_webp_cdn' => 0,
			'filter' => false,
		],
		'expected' => 'You are using Mock to convert images to WebP. If you want WP Rocket to serve them for you,',
	],
	'shouldReturnTextWhenPluginCreatingWebpAvailableAndCacheOptionEnabled' => [
		'config' => [
			'webp' => 1,
			'cdn' => 0,
			'convert_webp' => 1,
			'serving_webp' => 0,
			'serving_webp_cdn' => 0,
			'filter' => false,
		],
		'expected' => 'You are using Mock to convert images to WebP. WP Rocket will create separate cache files',
	],
	'shouldReturnTextWhenPluginServingWebpNotCompatibleAndCacheOptionDisabled' => [
		'config' => [
			'webp' => 0,
			'cdn' => 0,
			'convert_webp' => 1,
			'serving_webp' => 0,
			'serving_webp_cdn' => 0,
			'filter' => false,
		],
		'expected' => 'You are using Mock to convert images to WebP. If you want WP Rocket to serve them for you,',
	],
	'shouldReturnTextWhenPluginServingWebpNotCompatibleAndCacheOptionEnabled' => [
		'config' => [
			'webp' => 1,
			'cdn' => 0,
			'convert_webp' => 1,
			'serving_webp' => 0,
			'serving_webp_cdn' => 0,
			'filter' => false,
		],
		'expected' => 'You are using Mock to convert images to WebP. WP Rocket will create separate cache files',
	],
	'shouldReturnTextWhenPluginServingWebpAvailable' => [
		'config' => [
			'webp' => 1,
			'cdn' => 0,
			'convert_webp' => 1,
			'serving_webp' => 1,
			'serving_webp_cdn' => 0,
			'filter' => false,
		],
		'expected' => 'You are using Mock to serve WebP images so you do not need to enable this option.',
	],
	'shouldReturnTextWhenCacheOptionDisabledByFilter' => [
		'config' => [
			'webp' => 1,
			'cdn' => 0,
			'convert_webp' => 1,
			'serving_webp' => 0,
			'serving_webp_cdn' => 0,
			'filter' => true,
		],
		'expected' => 'WebP cache is disabled by filter.',
	],
];
