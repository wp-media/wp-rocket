<?php
return [
	'oneShouldDeleteOnce' => [
		'config' => [
			'params' => [
				'status__in' => [ 'failed', 'pending' ],
				'fields'     => [
					'id',
				],
			],
			'results' => [
				(object) [
					'id' => 1
				]
			]
		],
		'expected' => [[1]]
	],
	'twoShouldDeleteTwice' => [
		'config' => [
			'params' => [
				'status__in' => [ 'failed', 'pending' ],
				'fields'     => [
					'id',
				],
			],
			'results' => [
				(object) [
					'id' => 1
				],
				(object) [
					'id' => 2
				]
			]
		],
		'expected' => [[1], [2]]
	]
];
