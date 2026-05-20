<?php

return [
	'shouldReturnOriginalHtmlWhenDriverReturnsFalse' => [
		'config'   => [
			'cdn_enabled'    => true,
			'driver_returns' => false,
			'html'           => '<img srcset="https://example.org/wp-content/uploads/image.jpg 1x, https://example.org/wp-content/uploads/image-2x.jpg 2x">',
		],
		'expected' => [
			'html' => '<img srcset="https://example.org/wp-content/uploads/image.jpg 1x, https://example.org/wp-content/uploads/image-2x.jpg 2x">',
		],
	],
	'shouldRewriteSrcsetWhenDriverReturnsTrue'       => [
		'config'   => [
			'cdn_enabled'    => true,
			'driver_returns' => true,
			'html'           => '<img srcset="https://example.org/wp-content/uploads/image.jpg 1x">',
			'rewritten_html' => '<img srcset="https://cdn.example.org/wp-content/uploads/image.jpg 1x">',
		],
		'expected' => [
			'html' => '<img srcset="https://cdn.example.org/wp-content/uploads/image.jpg 1x">',
		],
	],
];
