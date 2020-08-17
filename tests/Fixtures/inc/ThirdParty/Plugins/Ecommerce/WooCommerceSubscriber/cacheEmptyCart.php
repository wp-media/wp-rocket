<?php

return [
	'test_data' => [
		'testShouldDoNothingWhenBypass' => [
			'config' => [
				'wc-ajax' => 'get_refreshed_fragments',
				'bypass'  => true,
			],
			'expected' => false,
		],
	],
];
