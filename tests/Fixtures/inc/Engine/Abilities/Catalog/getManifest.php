<?php

return [
	'testShouldReturnEmptyArrayWhenNoAbilitiesRegistered' => [
		'config'   => [
			'abilities' => [],
		],
		'expected' => [],
	],
	'testShouldFilterOutAbilitiesFromOtherVendors'        => [
		'config'   => [
			'abilities' => [
				[
					'name'          => 'wp-rocket/get-options',
					'label'         => 'Get WP Rocket options',
					'description'   => 'Retrieves current WP Rocket settings.',
					'category'      => 'wp-rocket-options',
					'input_schema'  => [],
					'output_schema' => [ 'type' => 'object' ],
					'meta'          => [ 'show_in_rest' => true ],
				],
				[
					'name'          => 'other-plugin/do-something',
					'label'         => 'Do something',
					'description'   => 'Belongs to another plugin.',
					'category'      => 'other-plugin',
					'input_schema'  => [],
					'output_schema' => [],
					'meta'          => [],
				],
			],
		],
		'expected' => [
			[
				'name'          => 'wp-rocket/get-options',
				'label'         => 'Get WP Rocket options',
				'description'   => 'Retrieves current WP Rocket settings.',
				'category'      => 'wp-rocket-options',
				'input_schema'  => [],
				'output_schema' => [ 'type' => 'object' ],
				'meta'          => [ 'show_in_rest' => true ],
			],
		],
	],
	'testShouldReturnMultipleWpRocketAbilities'           => [
		'config'   => [
			'abilities' => [
				[
					'name'          => 'wp-rocket/get-options',
					'label'         => 'Get WP Rocket options',
					'description'   => 'Retrieves current WP Rocket settings.',
					'category'      => 'wp-rocket-options',
					'input_schema'  => [],
					'output_schema' => [ 'type' => 'object' ],
					'meta'          => [ 'show_in_rest' => true ],
				],
				[
					'name'          => 'wp-rocket/set-option',
					'label'         => 'Set a WP Rocket option',
					'description'   => 'Updates a single WP Rocket setting.',
					'category'      => 'wp-rocket-options',
					'input_schema'  => [ 'type' => 'object' ],
					'output_schema' => [ 'type' => 'object' ],
					'meta'          => [ 'show_in_rest' => true ],
				],
			],
		],
		'expected' => [
			[
				'name'          => 'wp-rocket/get-options',
				'label'         => 'Get WP Rocket options',
				'description'   => 'Retrieves current WP Rocket settings.',
				'category'      => 'wp-rocket-options',
				'input_schema'  => [],
				'output_schema' => [ 'type' => 'object' ],
				'meta'          => [ 'show_in_rest' => true ],
			],
			[
				'name'          => 'wp-rocket/set-option',
				'label'         => 'Set a WP Rocket option',
				'description'   => 'Updates a single WP Rocket setting.',
				'category'      => 'wp-rocket-options',
				'input_schema'  => [ 'type' => 'object' ],
				'output_schema' => [ 'type' => 'object' ],
				'meta'          => [ 'show_in_rest' => true ],
			],
		],
	],
];
