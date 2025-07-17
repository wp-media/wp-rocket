<?php

return [
	'test_data' => [
		'testIsNotInPostPage' => [
			'config' => [
				'is_singular' => false,
			],
			'can_cache' => true,
		],
		'testValidPostPage' => [
			'config' => [
				'is_singular' => true,
				'current_post_id' => 1,
				'current_post_link' => 'http://example.com/test1',
				'current_page_url' => 'http://example.com/test1',
			],
			'can_cache' => true,
		],
		'testValidPostPageWithPagination' => [
			'config' => [
				'is_singular' => true,
				'current_post_id' => 1,
				'current_post_link' => 'http://example.com/test1',
				'current_page_url' => 'http://example.com/test1/page/2',
				'page' => 2,
			],
			'can_cache' => true,
		],
		'testEmptyPostId' => [
			'config' => [
				'is_singular' => true,
				'current_post_id' => 0,
				'current_post_link' => '',
				'current_page_url' => 'http://example.com/test1',
			],
			'can_cache' => true,
		],
		'testNotValidPostPage' => [
			'config' => [
				'is_singular' => true,
				'current_post_id' => 1,
				'current_post_link' => 'http://example.com/test1',
				'current_page_url' => 'http://example.com/additional-query/test1',
			],
			'can_cache' => false,
		],
		'testNotValidPostPageWithPagination' => [
			'config' => [
				'is_singular' => true,
				'current_post_id' => 1,
				'current_post_link' => 'http://example.com/test1',
				'current_page_url' => 'http://example.com/additional-query/test1/page/2',
				'page' => 2,
			],
			'can_cache' => false,
		],
		'testValidPostPageWithNonLatinCharactersInUrl' => [
			'config' => [
				'is_singular' => true,
				'current_post_id' => 1,
				'current_post_link' => 'http://example.com/%D0%BF%D1%80%D0%BE%D0%B4%D1%83%D0%BA%D1%82%D0%BE%D0%B2%D0%B0-%D0%BA%D0%B0%D1%82%D0%B5%D0%B3%D0%BE%D1%80%D0%B8%D1%8F/',
				'current_page_url' => 'http://example.com/продуктова-категория/',
			],
			'can_cache' => true,
		],
		'testThatDynamicPagesCanBeCached' => [
			'config' => [
				'is_singular' => true,
				'current_post_id' => 1,
				'current_post_link' => 'http://example.com/index.php/test1',
				'current_page_url' => 'http://example.com/test1',
				'second_current_page_url' => 'http://example.com/index.php/test1',
				'request_uri' => '/index.php/test1',
			],
			'can_cache' => true,
		],
	],
];
