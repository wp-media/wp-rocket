<?php

return [
	'shouldDoNothingWhenDoNotOptimizeEnabled' => [
		'config' => [
			'original-html' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
			'do-not-optimize' => true,
			'do-not-delay-const' => false,
			'do-not-delay-setting' => false,
		],
		'expected' => [
			'processed-html' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
		],
	],

	'shouldDoNothingWhenDelayConstEnabled' => [
		'config' => [
			'original-html' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
			'do-not-optimize' => false,
			'do-not-delay-const' => true,
			'do-not-delay-setting' => false,
			'allowed-scripts' => [],
		],
		'expected' => [
			'processed-html' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
		],
	],

	'shouldDoNothingWhenDelaySetttingEnabled' => [
		'config' => [
			'original-html' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
			'do-not-optimize' => false,
			'do-not-delay-const' => false,
			'do-not-delay-setting' => true,
			'allowed-scripts' => [],
		],
		'expected' => [
			'processed-html' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
		],
	],

	'shouldProcessDelayWhenAllowed' => [
		'config' => [
			'original-html' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
			'do-not-optimize' => false,
			'do-not-delay-const' => false,
			'do-not-delay-setting' => false,
			'allowed-scripts' => [],
		],
		'expected' => [
			'processed-html' => '<script data-rocketlazyloadscript=\'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js\' src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
		],
	],

];
