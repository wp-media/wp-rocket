<?php
return [
	'testAddUrls' => [
		'config' => [
			'urls' => [
				'url',
				'url1',
			],
			'is_excluded' => false,
			'is_excluded_by_filter' => false,
		],
		'expected' => [
			'urls' => [
				[
					[
						'url' => 'url',
						'status' => 'pending',
					]
				],
				[
					[
						'url' => 'url1',
						'status' => 'pending',
					]
				]
			]
		]
	],
	'excludedShouldNotAdd' => [
		'config' => [
			'urls' => [
				'url',
				'url1',
			],
			'is_excluded' => true,
			'is_excluded_by_filter' => false,
		],
		'expected' => [
			'urls' => [
				[
					[
						'url' => 'url',
						'status' => 'pending',
					]
				],
				[
					[
						'url' => 'url1',
						'status' => 'pending',
					]
				]
			]
		]
	],
	'excludedByFilterShouldDelete' => [
		'config' => [
			'urls' => [
				'url',
				'url1',
			],
			'is_excluded' => true,
			'is_excluded_by_filter' => true,
		],
		'expected' => [
			'urls' => [
				[
					'url',
				],
				[
					'url1',
				]
			]
		]
	],
];
