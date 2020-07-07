<?php

return [
	'test_data' => [
		'testShouldReturnErrorWhenWpCacheNotSet' => [
			'wp_cache' => null,
			'expected' => [
				'badge'       => [
					'label' => 'Cache',
					'color' => 'red',
				],
				'description' => '<p>The WP_CACHE constant needs to be set to true for WP Rocket cache to work properly</p>',
				'actions'     => '',
				'test'        => 'wp_cache_status',
				'label'       => 'WP_CACHE is not set',
				'status'      => 'critical',
			],
		],
		'testShouldReturnErrorWhenWpCacheFalse' => [
			'wp_cache' => false,
			'expected' => [
				'badge'       => [
					'label' => 'Cache',
					'color' => 'red',
				],
				'description' => '<p>The WP_CACHE constant needs to be set to true for WP Rocket cache to work properly</p>',
				'actions'     => '',
				'test'        => 'wp_cache_status',
				'label'       => 'WP_CACHE is set to false',
				'status'      => 'critical',
			],
		],
		'testShouldReturnSuccessWhenWpCacheTrue' => [
			'wp_cache' => true,
			'expected' => [
				'badge'       => [
					'label' => 'Cache',
					'color' => 'green',
				],
				'description' => '<p>The WP_CACHE constant needs to be set to true for WP Rocket cache to work properly</p>',
				'actions'     => '',
				'test'        => 'wp_cache_status',
				'label'       => 'WP_CACHE is set to true',
				'status'      => 'good',
			],
		],
	],
];