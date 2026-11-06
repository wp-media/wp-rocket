<?php

return [
	'testShouldDoNothingWhenAdthriveDisabled' => [
		'settings' => [
			'plugin_active' => false,
			'value' => [
				'delay_js' => 1,
				'delay_js_exclusions' => [],
			],
			'old_value' => [
				'delay_js' => 1,
				'delay_js_exclusions' => [],
			]
		],
		'expected' => [
			'delay_js' => 1,
			'delay_js_exclusions' => [],
		],
	],
	'testShouldDoNothingWhenDelayJsDisabled' => [
		'settings' => [
			'plugin_active' => true,
			'value' => [
				'delay_js' => 0,
				'delay_js_exclusions' => [],
			],
			'old_value' => [
				'delay_js' => 1,
				'delay_js_exclusions' => [],
			]
		],
		'expected' => [
			'delay_js' => 0,
			'delay_js_exclusions' => [],
		],
	],
	'testShouldDoNothingWhenSameDelayJsValue' => [
		'settings' => [
			'plugin_active' => true,
			'value' => [
				'delay_js' => 1,
				'delay_js_exclusions' => [],
			],
			'old_value' => [
				'delay_js' => 1,
				'delay_js_exclusions' => [],
			]
		],
		'expected' => [
			'delay_js' => 1,
			'delay_js_exclusions' => [],
		],
	],
	'testShouldDoNothingWhenPatternAlreadyExcluded' => [
		'settings' => [
			'plugin_active' => true,
			'value' => [
				'delay_js' => 1,
				'delay_js_exclusions' => [
					'adthrive'
				],
			],
			'old_value' => [
				'delay_js' => 0,
				'delay_js_exclusions' => [],
			]
		],
		'expected' => [
			'delay_js' => 1,
			'delay_js_exclusions' => [
				'adthrive'
			],
		],
	],
	'testShouldAddExclusion' => [
		'settings' => [
			'plugin_active' => true,
			'value' => [
				'delay_js' => 1,
				'delay_js_exclusions' => [],
			],
			'old_value' => [
				'delay_js' => 0,
				'delay_js_exclusions' => [],
			]
		],
		'expected' => [
			'delay_js' => 1,
			'delay_js_exclusions' => [
				'adthrive'
			],
		],
	],
];
