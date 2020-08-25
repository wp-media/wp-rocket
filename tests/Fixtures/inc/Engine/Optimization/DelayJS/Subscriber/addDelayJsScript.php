<?php

return [
	'testShouldNotAddScriptsWhenBypass' => [
		'config'   => [
			'delay_js'      => 1,
			'donotoptimize' => false,
			'bypass'        => true,
		],
		'expected' => false,
	],

	'testShouldNotAddScriptsWhenDONOTOPTIMIZE' => [
		'config'   => [
			'delay_js'      => 0,
			'donotoptimize' => true,
			'bypass'        => false,
		],
		'expected' => false,
	],

	'testShouldNotAddScriptsWhenDelaySettingDisabled' => [
		'config'   => [
			'delay_js'      => 0,
			'donotoptimize' => false,
			'bypass'        => false,
		],
		'expected' => false,
	],

	'testShouldAddScripts' => [
		'config'   => [
			'delay_js' => 1,
			'donotoptimize' => false,
			'bypass'        => false,
		],
		'expected' => true,
	],
];
