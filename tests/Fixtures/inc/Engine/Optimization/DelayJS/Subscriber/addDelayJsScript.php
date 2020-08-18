<?php

return [
	'shouldNotProcessDelayURLScriptOnBypass' => [
		'config'   => [
			'do-not-optimize'      => false,
			'delay_js'             => 1,
			'bypass'               => true,
		],
		'expected' => false,
	],

	'shouldAddScriptsWhenDoNotOptimizeEnabled' => [
		'config'   => [
			'do-not-optimize'      => true,
			'delay_js' => 1,
		],
		'expected' => true,
	],

	'shouldAddScriptsWhenDelaySettingEnabled' => [
		'config'   => [
			'do-not-optimize'      => false,
			'delay_js' => 1,
		],
		'expected' => true,
	],

	'shouldProcessDelayURLScript' => [
		'config'   => [
			'do-not-optimize'      => false,
			'delay_js' => 1,
		],
		'expected' => true,
	],
];
