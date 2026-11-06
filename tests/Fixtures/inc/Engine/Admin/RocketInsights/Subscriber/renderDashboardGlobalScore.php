<?php

return [
	'test_data' => [
		'shouldRenderDisabledStateWhenNotAllowed' => [
			'config'   => [
				'is_allowed'     => false,
				'is_local'       => false,
				'score'          => [],
				'remaining_urls' => 0,
			],
			'expected' => [
				'data'           => null,
				'disabled_local' => false,
			],
		],
		'shouldRenderLocalDisabledStateWhenNotAllowedOnLocalSite' => [
			'config'   => [
				'is_allowed'     => false,
				'is_local'       => true,
				'score'          => [],
				'remaining_urls' => 0,
			],
			'expected' => [
				'data'           => null,
				'disabled_local' => true,
			],
		],
		'shouldRenderWithDashboardContext'        => [
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
