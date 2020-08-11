<?php

return [

	'vfs_dir' => 'wp-content/plugins/wp-rocket/',

	'structure' => [
		'wp-content' => [
			'plugins' => [
				'wp-rocket' => [
					'assets' => [
						'js' => [
							'lazyload-scripts.js' => '',
							'lazyload-scripts.min.js' => ''
						]
					]
				]
			]
		]
	],

	'test_data' => [
		'shouldDoNothingWhenDoNotOptimizeEnabled' => [
			'config'   => [
				'html'        => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
				'do-not-optimize'      => true,
				'do-not-delay-const'   => false,
				'do-not-delay-setting' => 1,
			],
			'expected' => [
				'html' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
			],
		],

		'shouldDoNothingWhenDelayConstEnabled' => [
			'config'   => [
				'html'        => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
				'do-not-optimize'      => false,
				'do-not-delay-const'   => true,
				'do-not-delay-setting' => 1,
				'allowed-scripts'      => [],
			],
			'expected' => [
				'html' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
			],
		],

		'shouldDoNothingWhenDelaySettingEnabled' => [
			'config'   => [
				'html'        => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
				'do-not-optimize'      => false,
				'do-not-delay-const'   => false,
				'do-not-delay-setting' => 0,
				'allowed-scripts'      => [],
			],
			'expected' => [
				'html' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
			],
		],

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
	]

];
