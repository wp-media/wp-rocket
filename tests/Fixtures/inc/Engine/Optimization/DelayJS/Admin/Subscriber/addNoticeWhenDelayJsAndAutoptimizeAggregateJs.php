<?php

declare( strict_types=1 );

return [
	'shouldAddNoticeWhenAutoptimizeAggregateJsOnAndDelayJsActivated' => [
		'config'   => [
			'delayJSActiveOld'             => [ 'delay_js' => 0 ],
			'delayJSActiveNew'             => [ 'delay_js' => 1 ],
			'autoptimizeAggregateJSActive' => 'on',
		],
		'expected' => [
			[
				'setting' => 'general',
				'code'    => 'compatibility_notice',
				'message' => '</strong>We have detected that Autoptimize\'s JavaScript Aggregation feature is enabled. The Delay JavaScript Execution will not be applied to the file it creates. We suggest disabling it to take full advantage of Delay JavaScript Execution.<strong>',
				'type'    => 'notice',
			],
		],
	],

	'shouldSkipWhenAutoptimizeAggregateJsOffAndDelayJsNotActivated' => [
		'config'   => [
			'delayJSActiveOld'             => [ 'delay_js' => 0 ],
			'delayJSActiveNew'             => [ 'delay_js' => 0 ],
			'autoptimizeAggregateJSActive' => 'off',
		],
		'expected' => [],
	],

	'shouldSkipWhenAutoptimizeAggregateJsOffAndDelayJsActivated' => [
		'config'   => [
			'delayJSActiveOld'             => [ 'delay_js' => 0 ],
			'delayJSActiveNew'             => [ 'delay_js' => 1 ],
			'autoptimizeAggregateJSActive' => 'off',
		],
		'expected' => [],
	],

	'shouldSkipWhenAutoptimizeAggregateJsOnAndDelayJsNotActivated' => [
		'config'   => [
			'delayJSActiveOld'             => [ 'delay_js' => 0 ],
			'delayJSActiveNew'             => [ 'delay_js' => 0 ],
			'autoptimizeAggregateJSActive' => 'on',
		],
		'expected' => [],
	],

	'shouldSkipWhenAutoptimizeAggregateJsOffAndDelayJsActivated' => [
		'config' => [
			'delayJSActiveOld'             => [ 'delay_js' => 0 ],
			'delayJSActiveNew'             => [ 'delay_js' => 1 ],
			'autoptimizeAggregateJSActive' => 'off',
		],
		'expected' => [],
	],

	'shouldSkipWhenAutoptimizeAggregateJsOffAndDelayJsPreviouslyActivated' => [
		'config' => [
			'delayJSActiveOld'             => [ 'delay_js' => 1 ],
			'delayJSActiveNew'             => [ 'delay_js' => 1 ],
			'autoptimizeAggregateJSActive' => 'off',
		],
		'expected' => [],
	],

	'shouldSkipWhenAutoptimizeAggregateJsOnAndDelayJsPreviouslyActivated' => [
		'config' => [
			'delayJSActiveOld'             => [ 'delay_js' => 1 ],
			'delayJSActiveNew'             => [ 'delay_js' => 1 ],
			'autoptimizeAggregateJSActive' => 'on',
		],
		'expected' => [],
	],

	'shouldSkipWhenAutoptimizeAggregateJsOffAndDelayJsDeactivated' => [
		'config' => [
			'delayJSActiveOld'             => [ 'delay_js' => 1 ],
			'delayJSActiveNew'             => [ 'delay_js' => 0 ],
			'autoptimizeAggregateJSActive' => 'off',
		],
		'expected' => [],
	],

	'shouldSkipWhenAutoptimizeAggregateJsOnAndDelayJsDeactivated' => [
		'config' => [
			'delayJSActiveOld'             => [ 'delay_js' => 1 ],
			'delayJSActiveNew'             => [ 'delay_js' => 0 ],
			'autoptimizeAggregateJSActive' => 'on',
		],
		'expected' => [],
	],


];
