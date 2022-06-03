<?php
return [
	'oneShouldDeleteOnce' => [
		'config' => [
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
