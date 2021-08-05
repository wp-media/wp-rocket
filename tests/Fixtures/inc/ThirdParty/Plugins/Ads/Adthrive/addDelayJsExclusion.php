<?php

return [
	'shouldDoNothingWhenDelayJsDisabled' => [
		'settings' => [
			'delay_js' => 0,
			'delay_js_exclusions' => [],
		],
		'expected' => [
			'delay_js' => 0,
			'delay_js_exclusions' => [],
		],
	],
	'shouldDoNothingWhenPatternAlreadyExists' => [
		'settings' => [
			'delay_js' => 1,
			'delay_js_exclusions' => [
				'adthrive',
			],
		],
		'expected' => [
			'delay_js' => 1,
			'delay_js_exclusions' => [
				'adthrive',
			],
		],
	],
	'shouldUpdateSettingsWhenPatternNotAlreadyExists' => [
		'settings' => [
			'delay_js' => 1,
			'delay_js_exclusions' => [
				'js-(before|after)',
			],
		],
		'expected' => [
			'delay_js' => 1,
			'delay_js_exclusions' => [
				'js-(before|after)',
				'adthrive',
			],
		],
	],
];
