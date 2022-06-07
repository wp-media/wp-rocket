<?php
return [
	'testAddUrls' => [
		'config' => [
			'urls' => [
				(object) ['url' => 'url'],
				(object) ['url' => 'url1'],
			]
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
