<?php

return [
	'test_data' => [
		'shouldAddRocketInsightsColumn' => [
			'config' => [
				'post_type' => 'post',
				'columns'   => [
					'cb'     => '<input type="checkbox" />',
					'title'  => 'Title',
					'author' => 'Author',
					'date'   => 'Date',
				],
			],
			'expected' => [
				'column_label' => 'Rocket Insights',
			],
		],
		'shouldAddRocketInsightsColumnToPage' => [
			'config' => [
				'post_type' => 'page',
				'columns'   => [
					'cb'     => '<input type="checkbox" />',
					'title'  => 'Title',
					'author' => 'Author',
					'date'   => 'Date',
				],
			],
			'expected' => [
				'column_label' => 'Rocket Insights',
			],
		],
		'shouldPreserveExistingColumns' => [
			'config' => [
				'post_type' => 'post',
				'columns'   => [
					'cb'       => '<input type="checkbox" />',
					'title'    => 'Title',
					'author'   => 'Author',
					'categories' => 'Categories',
					'tags'     => 'Tags',
					'date'     => 'Date',
				],
			],
			'expected' => [
				'column_label' => 'Rocket Insights',
			],
		],
		'shouldAddRocketInsightsColumnToProduct' => [
			'config' => [
				'post_type' => 'product',
				'columns'   => [
					'cb'     => '<input type="checkbox" />',
					'title'  => 'Product',
					'sku'    => 'SKU',
					'price'  => 'Price',
					'date'   => 'Date',
				],
			],
			'expected' => [
				'column_label' => 'Rocket Insights',
			],
		],
		'shouldAddRocketInsightsColumnToCustomPostType' => [
			'config' => [
				'post_type' => 'custom_cpt',
				'columns'   => [
					'cb'     => '<input type="checkbox" />',
					'title'  => 'Title',
					'date'   => 'Date',
				],
			],
			'expected' => [
				'column_label' => 'Rocket Insights',
			],
		],
	],
];
