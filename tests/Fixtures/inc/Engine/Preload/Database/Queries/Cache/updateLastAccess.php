<?php
return [
	'shouldUpdate' => [
		'config' => [
			'id' => 1,
			'now' => 120
		],
		'expected' => [
			'id' => 1,
			'data' => [
				'last_accessed' => 120
			]
		]
	]
];
