<?php

return [
//	'shouldDoNothingWhenDoNotOptimizeEnabled' => [
//		'config'   => [
//			'original-html'        => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
//			'do-not-optimize'      => true,
//			'do-not-delay-const'   => false,
//			'do-not-delay-setting' => 1,
//		],
//		'expected' => [
//			'processed-html' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
//		],
//	],
//
//	'shouldDoNothingWhenDelayConstEnabled' => [
//		'config'   => [
//			'original-html'        => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
//			'do-not-optimize'      => false,
//			'do-not-delay-const'   => true,
//			'do-not-delay-setting' => 1,
//			'allowed-scripts'      => [],
//		],
//		'expected' => [
//			'processed-html' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
//		],
//	],
//
//	'shouldDoNothingWhenDelaySettingEnabled' => [
//		'config'   => [
//			'original-html'        => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
//			'do-not-optimize'      => false,
//			'do-not-delay-const'   => false,
//			'do-not-delay-setting' => 0,
//			'allowed-scripts'      => [],
//		],
//		'expected' => [
//			'processed-html' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
//		],
//	],

	'shouldProcessDelayURLScript' => [
		'config'   => [
			'html'        => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js"></script>',
			'do-not-optimize'      => false,
			'do-not-delay-const'   => false,
			'do-not-delay-setting' => 1,
			'allowed-scripts'      => ['https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js'],
		],
		'expected' => [
			'html' => '<script data-rocketlazyloadscript=\'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js\' ></script>',
		],
	],

//	'shouldProcessDelayInlineScript' => [
//
//	],
//
//	'shouldIgnoreScriptsNotAllowed' => [
//
//	],
];
