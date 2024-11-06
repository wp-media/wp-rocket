<?php

return [
	'test_data' => [
		'testIsNotInCategoryPage' => [
			'config' => [
				'is_category' => false,
			],
			'can_cache' => true,
		],
		'testIsNotInTagPage' => [
			'config' => [
				'is_category' => false,
				'is_tag' => false,
			],
			'can_cache' => true,
		],
		'testIsNotInTaxPage' => [
			'config' => [
				'is_category' => false,
				'is_tag' => false,
				'is_tax' => false,
			],
			'can_cache' => true,
		],
		'testValidTaxonomyPage' => [
			'config' => [
				'is_category' => true,
				'is_tag' => false,
				'is_tax' => false,
				'current_term_id' => 1,
				'current_term_link' => 'http://example.com/category/test1',
				'current_page_url' => 'http://example.com/category/test1',
			],
			'can_cache' => true,
		],
		'testValidTaxonomyPageWithPagination' => [
			'config' => [
				'is_category' => true,
				'is_tag' => false,
				'is_tax' => false,
				'current_term_id' => 1,
				'current_term_link' => 'http://example.com/category/test1',
				'current_page_url' => 'http://example.com/category/test1/page/2',
				'page' => 2,
			],
			'can_cache' => true,
		],
		'testEmptyTermId' => [
			'config' => [
				'is_category' => true,
				'is_tag' => false,
				'is_tax' => false,
				'current_term_id' => 0,
				'current_term_link' => '',
				'current_page_url' => 'http://example.com/category/test1',
			],
			'can_cache' => true,
		],
		'testNotValidTermLink' => [
			'config' => [
				'is_category' => true,
				'is_tag' => false,
				'is_tax' => false,
				'current_term_id' => 0,
				'current_term_link' => new WP_Error(),
				'current_page_url' => 'http://example.com/category/test1',
			],
			'can_cache' => true,
		],
		'testNotValidTaxonomyPage' => [
			'config' => [
				'is_category' => true,
				'is_tag' => false,
				'is_tax' => false,
				'current_term_id' => 1,
				'current_term_link' => 'http://example.com/category/test1',
				'current_page_url' => 'http://example.com/category/additional-query/test1',
			],
			'can_cache' => false,
		],
		'testNotValidTaxonomyPageWithPagination' => [
			'config' => [
				'is_category' => true,
				'is_tag' => false,
				'is_tax' => false,
				'current_term_id' => 1,
				'current_term_link' => 'http://example.com/category/test1',
				'current_page_url' => 'http://example.com/category/additional-query/test1/page/2',
				'page' => 2,
			],
			'can_cache' => false,
		],
	],
];
