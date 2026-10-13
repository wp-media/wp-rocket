<?php

return [
	'test_data' => [
		'shouldNotRenderWhenNotAllowed' => [
			'config'   => [
				'is_allowed'     => false,
				'score'          => [],
				'remaining_urls' => 0,
			],
			'expected' => [
				'data' => null,
			],
		],
		'shouldRenderWithDashboardContext' => [
			'config'   => [
				'is_allowed'     => true,
				'score'          => [
					'score'     => 90,
					'pages_num' => 2,
				],
				'remaining_urls' => 3,
			],
			'expected' => [
				'data' => [
					'score'          => 90,
					'pages_num'      => 2,
					'remaining_urls' => 3,
					'context'        => 'dashboard',
				],
			],
		],
	],
];
