<?php
return [
	'nothingShouldNotUpdate' => [
		'config' => [
			'current_time' => 123415,
			'results' => []
		],
		'expected' => [
		],
	],
	'resultsShouldUpdate' => [
		'config' => [
			'current_time' => 123415,
			'results' => [
				(object) ['id' => 10],
				(object) ['id' => 20],
			]
		],
		'expected' => [
			[10, [
					'status' => 'pending',
				]
			],
			[20, [
				'status' => 'pending',
				]
			],
		],
	]
];
