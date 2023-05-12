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
			'is_private' => false,
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
			'is_private' => false,
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
			'is_private' => false,
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
	'excludePostStatusWithPrivateStatus' => [
		'config' => [
			'urls' => [
				'url',
				'url1',
			],
			'is_excluded' => true,
			'is_excluded_by_filter' => true,
			'is_private' => true,
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
