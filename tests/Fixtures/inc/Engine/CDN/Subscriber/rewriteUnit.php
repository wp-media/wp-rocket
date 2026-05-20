<?php

return [
	'shouldReturnOriginalHtmlWhenDriverReturnsFalse' => [
		'config'   => [
			'cdn_enabled'    => true,
			'subscription_eligible' => true,
			'driver_returns' => false,
			'html'           => '<img src="https://example.org/wp-content/uploads/image.jpg">',
		],
		'expected' => [
			'html' => '<img src="https://example.org/wp-content/uploads/image.jpg">',
		],
	],
	'shouldReturnOriginalHtmlWhenSubscriptionIsNotEligible' => [
		'config'   => [
			'cdn_enabled'            => true,
			'subscription_eligible'  => false,
			'driver_returns'         => true,
			'html'                   => '<img src="https://example.org/wp-content/uploads/image.jpg">',
		],
		'expected' => [
			'html' => '<img src="https://example.org/wp-content/uploads/image.jpg">',
		],
	],
	'shouldRewriteHtmlWhenDriverReturnsTrue'         => [
		'config'   => [
			'cdn_enabled'    => true,
			'subscription_eligible' => true,
			'driver_returns' => true,
			'html'           => '<img src="https://example.org/wp-content/uploads/image.jpg">',
			'rewritten_html' => '<img src="https://cdn.example.org/wp-content/uploads/image.jpg">',
		],
		'expected' => [
			'html' => '<img src="https://cdn.example.org/wp-content/uploads/image.jpg">',
		],
	],
];
