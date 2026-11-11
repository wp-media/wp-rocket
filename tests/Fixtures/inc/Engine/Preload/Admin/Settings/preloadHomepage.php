<?php

return [
	'testShouldDoNothingWhenPreloadDisabled' => [
		'config'   => [
			'options' => [
				'manual_preload' => 0,
			],
		],
		'expected' => false,
	],
	'testShouldPreloadHomepage' => [
		'config'   => [
			'options' => [
				'manual_preload' => 1,
			],
		],
		'expected' => true,
	],
];
