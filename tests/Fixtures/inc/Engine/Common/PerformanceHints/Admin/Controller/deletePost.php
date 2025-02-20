<?php

return [
	'testShoulDoNothingWhenDisabled' => [
		'config' => [
			'filter' => false,
			'post_id' => 1,
			'post_type' => 'post',
			'url' => 'http://example.org',
			'factories' => [
				'get_admin_controller'
			]
		],
		'expected' => false,
	],
	'testShoulDoNothingURLNull' => [
		'config' => [
			'filter' => true,
			'post_id' => 1,
			'url' => null,
			'post_type' => 'post',
		],
		'expected' => false,
	],
	'testShoulDoNothingURLFalse' => [
		'config' => [
			'filter' => true,
			'post_id' => 1,
			'url' => false,
			'post_type' => 'post',
		],
		'expected' => false,
	],
	'testShoulDeletePost' => [
		'config' => [
			'filter' => true,
			'post_id' => 1,
			'post_type' => 'post',
			'url' => 'http://example.org',
		],
		'expected' => true,
	],
	'testShoulNotDeletePostWithAttachmentPostType' => [
		'config' => [
			'filter' => true,
			'post_id' => 1,
			'post_type' => 'attachment',
			'url' => 'http://example.org',
		],
		'expected' => false,
	],
];
