<?php

return [
	'test_data' => [
		'testShouldBailOutWhenPublicTypeIsFalse' => [
			'config' => [
				'post_data' => [
					'ID' => 1,
					'post_name' => 'test',
					'url' => '/test-page/',
					'post_type' => 'post',
					'next_post_id' => 2,
					'post_author' => 1,
					'post_status' => 'draft'
				],
				'post_type_public' => [
					'public' => false
				],
			],
			'expected' => []
		]
	]
];
