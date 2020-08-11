<?php

return [

	'vfs_dir' => 'wp-content/plugins/wp-rocket/',

	'structure' => [
		'wp-content' => [
			'plugins' => [
				'wp-rocket' => [
					'assets' => [
						'js' => [
							'lazyload-scripts.js'     => '',
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
				'html'                 => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
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
				'html'                 => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
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
				'html'                 => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
				'do-not-optimize'      => false,
				'do-not-delay-const'   => false,
				'do-not-delay-setting' => 0,
				'allowed-scripts'      => [],
			],
			'expected' => [
				'html' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
			],
		],

		'shouldNotProcessDelayJsScriptWhenBypass' => [
			'config'   => [
				'html'                 => '<script type="text/javascript" data-any="value">alert("Be alert! We need more lerts!");</script>',
				'do-not-optimize'      => false,
				'do-not-delay-const'   => false,
				'do-not-delay-setting' => 1,
				'allowed-scripts'      => [ 'alert("Be alert! We need more lerts!")' ],
				'bypass'               => true
			],
			'expected' => [
				'html' => '<script type="text/javascript" data-any="value">alert("Be alert! We need more lerts!");</script>',
			],
		],

		'shouldProcessDelayURLScript' => [
			'config'   => [
				'html'                 => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js"></script>',
				'do-not-optimize'      => false,
				'do-not-delay-const'   => false,
				'do-not-delay-setting' => 1,
				'allowed-scripts'      => [ 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js' ],
			],
			'expected' => [
				'html' => '<script data-rocketlazyloadscript=\'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js\' ></script>',
			],
		],

		'shouldProcessDelayInlineScript' => [
			'config'   => [
				'html'                 => '<script type="text/javascript" data-any="value">alert("Be alert! We need more lerts!");</script>',
				'do-not-optimize'      => false,
				'do-not-delay-const'   => false,
				'do-not-delay-setting' => 1,
				'allowed-scripts'      => [ 'alert("Be alert! We need more lerts!")' ],
			],
			'expected' => [
				'html' => '<script data-rocketlazyloadscript=\'data:text/javascript;base64,YWxlcnQoIkJlIGFsZXJ0ISBXZSBuZWVkIG1vcmUgbGVydHMhIik7\' type="text/javascript" data-any="value"></script>',
			],
		],

		'shouldIgnoreScriptsNotAllowed' => [
			'config'   => [
				'html'                 => '<script type="text/javascript" data-any="value">alert("Be alert! We need more lerts!");</script>
<script data-ignore-me="this script should be ignored!" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>',
				'do-not-optimize'      => false,
				'do-not-delay-const'   => false,
				'do-not-delay-setting' => 1,
				'allowed-scripts'      => [
					'alert("Be alert! We need more lerts!")',
					'//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js',
				],
			],
			'expected' => [
				'html' => '<script data-rocketlazyloadscript=\'data:text/javascript;base64,YWxlcnQoIkJlIGFsZXJ0ISBXZSBuZWVkIG1vcmUgbGVydHMhIik7\' type="text/javascript" data-any="value"></script>
<script data-ignore-me="this script should be ignored!" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js"></script>
<script data-rocketlazyloadscript=\'//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js\' type="text/javascript" ></script>',
			],
		],
	]

];
