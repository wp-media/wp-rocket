<?php

return [
	'test_data' => [
		'shouldDoNothingWhenDoNotOptimizeEnabled' => [
			'config'   => [
				'bypass'               => false,
				'donotoptimize'        => true,
				'do-not-delay-setting' => 1,
			],
			'html'     => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
			'expected' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
		],

		'shouldDoNothingWhenDelaySettingEnabled' => [
			'config'   => [
				'bypass'               => false,
				'donotoptimize'        => false,
				'do-not-delay-setting' => 0,
				'allowed-scripts'      => [],
			],
			'html'     => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
			'expected' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">',
		],

		'shouldNotProcessDelayJsScriptWhenBypass' => [
			'config'   => [
				'donotoptimize'      => false,
				'do-not-delay-setting' => 1,
				'allowed-scripts'      => [ 'alert("Be alert! We need more lerts!")' ],
				'bypass'               => true,
			],
			'html'     => '<script type="text/javascript" data-any="value">alert("Be alert! We need more lerts!");</script>',
			'expected' => '<script type="text/javascript" data-any="value">alert("Be alert! We need more lerts!");</script>',
		],

		'shouldProcessDelayURLScriptWithSpaces' => [
			'config'   => [
				'bypass'               => false,
				'donotoptimize'        => false,
				'do-not-delay-setting' => 1,
				'allowed-scripts'      => [ 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js' ],
			],
			'html'     => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js"  >  </script>',
			'expected' => '<script data-rocketlazyloadscript=\'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js\' ></script>',
		],

		'shouldProcessDelayURLScript' => [
			'config'   => [
				'bypass'               => false,
				'donotoptimize'        => false,
				'do-not-delay-setting' => 1,
				'allowed-scripts'      => [ 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js' ],
			],
			'html'     => '<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js"></script>',
			'expected' => '<script data-rocketlazyloadscript=\'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js\' ></script>',
		],

		'shouldProcessDelayInlineScript' => [
			'config'   => [
				'bypass'               => false,
				'donotoptimize'        => false,
				'do-not-delay-setting' => 1,
				'allowed-scripts'      => [ 'alert("Be alert! We need more lerts!")' ],
			],
			'html'     => '<script type="text/javascript" data-any="value">alert("Be alert! We need more lerts!");</script>',
			'expected' => '<script data-rocketlazyloadscript=\'data:text/javascript;base64,YWxlcnQoIkJlIGFsZXJ0ISBXZSBuZWVkIG1vcmUgbGVydHMhIik7\' type="text/javascript" data-any="value"></script>',
		],

		'shouldIgnoreScriptsNotAllowed' => [
			'config'   => [
				'bypass'               => false,
				'donotoptimize'        => false,
				'do-not-delay-setting' => 1,
				'allowed-scripts'      => [
					'alert("Be alert! We need more lerts!")',
					'//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js',
				],
			],
			'html'     => '<script type="text/javascript" data-any="value">alert("Be alert! We need more lerts!");</script>
<script data-ignore-me="this script should be ignored!" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>',
			'expected' => '<script data-rocketlazyloadscript=\'data:text/javascript;base64,YWxlcnQoIkJlIGFsZXJ0ISBXZSBuZWVkIG1vcmUgbGVydHMhIik7\' type="text/javascript" data-any="value"></script>
<script data-ignore-me="this script should be ignored!" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js"></script>
<script data-rocketlazyloadscript=\'//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js\' type="text/javascript" ></script>',
		],
		'shouldProcessScriptsWithSpaces' => [
			'config'   => [
				'bypass'               => false,
				'donotoptimize'        => false,
				'do-not-delay-setting' => 1,
				'post-excluded'        => false,
				'allowed-scripts'      => [ 'ck239.infusionsoft.com/app/webTracking/getTrackingCode' ],
			],
			'html'     => '<script src="https://ck239.infusionsoft.com/app/webTracking/getTrackingCode"></script >
<style>.tcm-color-ac span { color: #03a9f4;}</style>',
			'expected' => '<script data-rocketlazyloadscript=\'https://ck239.infusionsoft.com/app/webTracking/getTrackingCode\' ></script>
<style>.tcm-color-ac span { color: #03a9f4;}</style>',
		],
	]
];
