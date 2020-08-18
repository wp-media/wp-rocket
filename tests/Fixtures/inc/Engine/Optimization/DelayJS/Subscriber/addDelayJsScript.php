<?php

return [
	'shouldNotAddScriptsWhenBypass' => [
		'config'   => [
			'delay_js' => 1,
			'bypass'   => true,
		],
		'expected' => false,
	],

	'shouldNotAddScriptsWhenDelaySettingDisabled' => [
		'config'   => [
			'delay_js' => 0,
		],
		'expected' => false,
	],

	'shouldProcessDelayURLScript' => [
		'config'   => [
			'delay_js' => 1,
		],
		'expected' => true,
	],
];
