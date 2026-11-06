<?php
use WP_Rocket\Tests\Fixtures\Generators\UserDataGenerator;

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
				'is_live_site' => 'example.org',
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate(),
				'transient' => (object) [ 'rocket_insights_display_post_column' => true ],
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
				'is_live_site' => 'example.org',
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate(),
				'transient' => (object) [ 'rocket_insights_display_post_column' => true ],
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
				'is_live_site' => 'example.org',
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate(),
				'transient' => (object) [ 'rocket_insights_display_post_column' => true ],
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
				'is_live_site' => 'example.org',
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate(),
				'transient' => (object) [ 'rocket_insights_display_post_column' => true ],
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
				'is_live_site' => 'example.org',
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate(),
				'transient' => (object) [ 'rocket_insights_display_post_column' => true ],
			],
			'expected' => [
				'column_label' => 'Rocket Insights',
			],
		],
		'shouldNotAddRocketInsightsColumnForLocalEnv' => [
			'config' => [
				'post_type' => 'post',
				'columns'   => [
					'cb'     => '<input type="checkbox" />',
					'title'  => 'Title',
					'author' => 'Author',
					'date'   => 'Date',
				],
				'is_live_site' => 'localhost',
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate(),
				'transient' => (object) [ 'rocket_insights_display_post_column' => true ],
			],
			'expected' => [
				'column_label' => '',
			],
		],
		'shouldNotAddRocketInsightsColumnWhenRemoteSettingIsDisabled' => [
			'config' => [
				'post_type' => 'post',
				'columns'   => [
					'cb'     => '<input type="checkbox" />',
					'title'  => 'Title',
					'author' => 'Author',
					'date'   => 'Date',
				],
				'is_live_site' => 'example.org',
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate(),
				'transient' => (object) [ 'rocket_insights_display_post_column' => false ],
			],
			'expected' => [
				'column_label' => '',
			],
		],
	],
];
