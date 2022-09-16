<?php
return [
	'nothingShouldNotUpdate' => [
		'config' => [
			'results' => []
		],
		'expected' => [
		],
	],
	'resultsShouldUpdate' => [
		'config' => [
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
