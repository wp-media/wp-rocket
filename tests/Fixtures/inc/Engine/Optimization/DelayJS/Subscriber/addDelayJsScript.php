<?php

return [
	'shouldDoNothingWhenDoNotOptimizeEnabled' => [
		'config'   => [
			'do-not-optimize'      => true,
			'do-not-delay-const'   => false,
			'do-not-delay-setting' => 1,
		],
		'expected' => false,
	],

	'shouldDoNothingWhenDelayConstEnabled' => [
		'config'   => [
			'do-not-optimize'      => false,
			'do-not-delay-const'   => true,
			'do-not-delay-setting' => 1,
		],
		'expected' => false,
	],

	'shouldDoNothingWhenDelaySettingEnabled' => [
		'config'   => [
			'do-not-optimize'      => false,
			'do-not-delay-const'   => false,
			'do-not-delay-setting' => 0,
		],
		'expected' => false,
	],

	'shouldNotProcessDelayURLScriptOnBypass' => [
		'config'   => [
			'do-not-optimize'      => false,
			'do-not-delay-const'   => false,
			'do-not-delay-setting' => 1,
			'bypass'               => true,
		],
		'expected' => false,
	],

	'shouldProcessDelayURLScript' => [
		'config'   => [
			'do-not-optimize'      => false,
			'do-not-delay-const'   => false,
			'do-not-delay-setting' => 1,
		],
		'expected' => true,
	],
];
