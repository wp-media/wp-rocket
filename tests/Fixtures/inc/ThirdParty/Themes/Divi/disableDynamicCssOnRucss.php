<?php

return [
	'test_data' => [
		'shouldDisableOnRUCSSActivated' => [
			'config' => [
				'rucss_enabled' => true,
				'stylesheet'  => 'divi',
				'theme-name'  => 'Divi',
			],
			'expected' => 10
		],
		'shouldBeEnabledOnRUCSSDisabled' => [
			'config' => [
				'rucss_enabled' => false,
				'stylesheet'  => 'divi',
				'theme-name'  => 'Divi',
			],
			'expected' => false
		]
	]
];
