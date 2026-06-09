<?php

return [
	'shouldReturnOriginalHtmlWhenRocketcdnIsPaused'             => [
		'config'   => [
			'cdn_type'       => true,
			'cdn_enabled'    => 0,
			'driver_returns' => true,
			'html'           => '<img src="https://example.org/wp-content/uploads/image.jpg">',
		],
		'expected' => [
			'html'           => '<img src="https://example.org/wp-content/uploads/image.jpg">',
			'rewrite_called' => false,
		],
	],
	'shouldReturnOriginalHtmlWhenDriverReturnsFalse'             => [
		'config'   => [
			'cdn_type'       => true,
			'cdn_enabled'    => 1,
			'driver_returns' => false,
			'html'           => '<img src="https://example.org/wp-content/uploads/image.jpg">',
		],
		'expected' => [
			'html'           => '<img src="https://example.org/wp-content/uploads/image.jpg">',
			'rewrite_called' => false,
		],
	],
	'shouldRewriteHtmlWhenDriverReturnsTrue'                     => [
		'config'   => [
			'cdn_type'       => true,
			'cdn_enabled'    => 1,
			'driver_returns' => true,
			'html'           => '<img src="https://example.org/wp-content/uploads/image.jpg">',
			'rewritten_html' => '<img src="https://cdn.example.org/wp-content/uploads/image.jpg">',
		],
		'expected' => [
			'html'           => '<img src="https://cdn.example.org/wp-content/uploads/image.jpg">',
			'rewrite_called' => true,
		],
	],
	'shouldRewriteHtmlWhenByocdnEvenIfCdnOptionIsDisabled'       => [
		'config'   => [
			'cdn_type'       => false,
			'cdn_enabled'    => 0,
			'driver_returns' => true,
			'html'           => '<img src="https://example.org/wp-content/uploads/image.jpg">',
			'rewritten_html' => '<img src="https://cdn.example.org/wp-content/uploads/image.jpg">',
		],
		'expected' => [
			'html'           => '<img src="https://cdn.example.org/wp-content/uploads/image.jpg">',
			'rewrite_called' => true,
		],
	],
	'shouldReturnOriginalHtmlWhenByocdnDriverReturnsFalse'       => [
		'config'   => [
			'cdn_type'       => false,
			'cdn_enabled'    => 0,
			'driver_returns' => false,
			'html'           => '<img src="https://example.org/wp-content/uploads/image.jpg">',
		],
		'expected' => [
			'html'           => '<img src="https://example.org/wp-content/uploads/image.jpg">',
			'rewrite_called' => false,
		],
	],
];
