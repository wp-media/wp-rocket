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
				'is_live_site' => true,
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate()
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
				'is_live_site' => true,
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate()
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
				'is_live_site' => true,
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate()
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
				'is_live_site' => false,
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate()
			],
			'expected' => [
				'column_label' => '',
			],
		],
		'shouldNotAddRocketInsightsColumnForResller' => [
			'config' => [
				'post_type' => 'post',
				'columns'   => [
					'cb'     => '<input type="checkbox" />',
					'title'  => 'Title',
					'author' => 'Author',
					'date'   => 'Date',
				],
				'is_live_site' => true,
				'customer_data' => (new UserDataGenerator())->with_reseller_status(1)->generate()
			],
			'expected' => [
				'column_label' => '',
			],
		],
	],
];
