<?php

return [
	'shouldReturnOriginalHtmlWhenRocketcdnIsPaused'             => [
		'config'   => [
			'cdn_type'       => true,
			'cdn_enabled'    => 0,
			'driver_returns' => true,
			'html'           => '<img srcset="https://example.org/wp-content/uploads/image.jpg 1x">',
		],
		'expected' => [
			'html'           => '<img srcset="https://example.org/wp-content/uploads/image.jpg 1x">',
			'rewrite_called' => false,
		],
	],
	'shouldReturnOriginalHtmlWhenDriverReturnsFalse'             => [
		'config'   => [
			'cdn_type'       => true,
			'cdn_enabled'    => 1,
			'driver_returns' => false,
			'html'           => '<img srcset="https://example.org/wp-content/uploads/image.jpg 1x, https://example.org/wp-content/uploads/image-2x.jpg 2x">',
		],
		'expected' => [
			'html'           => '<img srcset="https://example.org/wp-content/uploads/image.jpg 1x, https://example.org/wp-content/uploads/image-2x.jpg 2x">',
			'rewrite_called' => false,
		],
	],
	'shouldRewriteSrcsetWhenDriverReturnsTrue'                   => [
		'config'   => [
			'cdn_type'       => true,
			'cdn_enabled'    => 1,
			'driver_returns' => true,
			'html'           => '<img srcset="https://example.org/wp-content/uploads/image.jpg 1x">',
			'rewritten_html' => '<img srcset="https://cdn.example.org/wp-content/uploads/image.jpg 1x">',
		],
		'expected' => [
			'html'           => '<img srcset="https://cdn.example.org/wp-content/uploads/image.jpg 1x">',
			'rewrite_called' => true,
		],
	],
	'shouldRewriteSrcsetWhenByocdnEvenIfCdnOptionIsDisabled'     => [
		'config'   => [
			'cdn_type'       => false,
			'cdn_enabled'    => 0,
			'driver_returns' => true,
			'html'           => '<img srcset="https://example.org/wp-content/uploads/image.jpg 1x">',
			'rewritten_html' => '<img srcset="https://cdn.example.org/wp-content/uploads/image.jpg 1x">',
		],
		'expected' => [
			'html'           => '<img srcset="https://cdn.example.org/wp-content/uploads/image.jpg 1x">',
			'rewrite_called' => true,
		],
	],
	'shouldReturnOriginalHtmlWhenByocdnDriverReturnsFalse'       => [
		'config'   => [
			'cdn_type'       => false,
			'cdn_enabled'    => 0,
			'driver_returns' => false,
			'html'           => '<img srcset="https://example.org/wp-content/uploads/image.jpg 1x">',
		],
		'expected' => [
			'html'           => '<img srcset="https://example.org/wp-content/uploads/image.jpg 1x">',
			'rewrite_called' => false,
		],
	],
];
