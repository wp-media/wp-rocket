<?php
return [
	'testAddUrls' => [
		'config' => [
			'urls' => [
				'url',
				'url1',
			],
			'is_excluded' => false,
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
];
