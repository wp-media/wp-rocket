<?php

return [
	'test_data' => [
		'testShouldDoNothingWhenNotFragments' => [
			'config' => [
				'wc-ajax' => null,
				'bypass'  => false,
			],
			'expected' => false,
		],
		'testShouldDoNothingWhenBypass' => [
			'config' => [
				'wc-ajax' => 'get_refreshed_fragments',
				'bypass'  => true,
			],
			'expected' => false,
		],
	],
];
