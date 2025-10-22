<?php

return [
	'test_data' => [
		'shouldRenderPlaceholderContent' => [
			'config' => [
				'post_type'   => 'post',
				'column_name' => 'rocket_insights',
				'post_data'   => [
					'post_title'  => 'Test Post',
					'post_status' => 'publish',
					'post_type'   => 'post',
				],
			],
			'expected' => [
				'should_render' => true,
				'content'       => 'Coming Soon',
			],
		],
		'shouldNotRenderForOtherColumns' => [
			'config' => [
				'post_type'   => 'post',
				'column_name' => 'author',
				'post_data'   => [
					'post_title'  => 'Test Post',
					'post_status' => 'publish',
					'post_type'   => 'post',
				],
			],
			'expected' => [
				'should_render' => false,
				'content'       => '',
			],
		],
		'shouldRenderPlaceholderForPages' => [
			'config' => [
				'post_type'   => 'page',
				'column_name' => 'rocket_insights',
				'post_data'   => [
					'post_title'  => 'Test Page',
					'post_status' => 'publish',
					'post_type'   => 'page',
				],
			],
			'expected' => [
				'should_render' => true,
				'content'       => 'Coming Soon',
			],
		],
	],
];
