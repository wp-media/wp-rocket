<?php

return [
	'testShouldReturnFalseIfNoPost' => [
		'config' => [
			'have_posts' => false,
			'posts' => [],
			'url' => 'http://example.org/test-4/',
		],
		'expected' => [
			'result' => false,
			'rocket_url_to_postid' => 0,
			'get_post' => null,

		],
	],
	'testShouldReturnFalseIfNotPrivate' => [
		'config' => [
			'have_posts' => true,
			'posts' => [
				(object) [
					'ID' => 2,
					'post_status' => 'public',
				],
			],
			'get_permalink' => [
				'http://example.org/test-4/',
			],
			'url' => 'http://example.org/test-4/',
		],
		'expected' => [
			'result' => false,
			'rocket_url_to_postid' => 2,
			'get_post' => (object) [
				'ID' => 2,
				'post_status' => 'public',
			],

		],
	],
	'testShouldReturnTrueIfPrivate' => [
		'config' => [
			'have_posts' => true,
			'posts' => [
				(object) [
					'ID' => 2,
					'post_status' => 'private',
				],
			],
			'get_permalink' => [
				'http://example.org/test-4/',
			],
			'url' => 'http://example.org/test-4/',
		],
		'expected' => [
			'result' => true,
			'rocket_url_to_postid' => 2,
			'get_post' => (object) [
				'ID' => 2,
				'post_status' => 'private',
			],

		],
	]
];
