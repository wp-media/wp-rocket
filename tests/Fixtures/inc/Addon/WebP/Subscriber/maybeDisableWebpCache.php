<?php

return [
	'shouldReturnFalseWhenValueFalseAndNoPluginServingWebP' => [
		'config' => [
			'plugin_active' => false,
			'cdn' => false,
			'convert_to_webp' => false,
			'serve_webp' => false,
			'serve_webp_compatible_with_cdn' => false,
		],
		'value' => false,
		'expected' => false,
	],
	'shouldReturnFalseWhenValueFalseAndPluginServingWebP' => [
		'config' => [
			'plugin_active' => true,
			'cdn' => false,
			'convert_to_webp' => true,
			'serve_webp' => false,
			'serve_webp_compatible_with_cdn' => false,
		],
		'value' => false,
		'expected' => false,
	],
	'shouldReturnTrueWhenValueTrueAndNoPluginServingWebP' => [
		'config' => [
			'plugin_active' => false,
			'cdn' => false,
			'convert_to_webp' => false,
			'serve_webp' => false,
			'serve_webp_compatible_with_cdn' => false,
		],
		'value' => true,
		'expected' => true,
	],
	'shouldReturnTrueWhenValueTrueAndPluginServingWebP' => [
		'config' => [
			'plugin_active' => true,
			'cdn' => false,
			'convert_to_webp' => true,
			'serve_webp' => false,
			'serve_webp_compatible_with_cdn' => false,
		],
		'value' => true,
		'expected' => true,
	],
];
