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
		'testEmptyTaxonomies' => [
			'config' => [
				'is_category' => true,
				'is_tag' => false,
				'is_tax' => false,
				'taxonomies' => [],
			],
			'can_cache' => true,
		],
		'testValidTaxonomyPage' => [
			'config' => [
				'is_category' => true,
				'is_tag' => false,
				'is_tax' => false,
				'taxonomies' => [
					(object) [
						'query_var' => 'category_name',
					],
				],
				'current_query' => [
					'category_name' => 'category',
				],
				'current_query_var' => [
					'category_name' => 'category',
				],
			],
			'can_cache' => true,
		],
		'testNotValidTaxonomyPage' => [
			'config' => [
				'is_category' => true,
				'is_tag' => false,
				'is_tax' => false,
				'taxonomies' => [
					(object) [
						'query_var' => 'category_name',
					],
				],
				'current_query' => [
					'category_name' => 'category1',
				],
				'current_query_var' => [
					'category_name' => 'nothing',
				],
			],
			'can_cache' => false,
		],
	],
];
