<?php
return [
    'shouldClear' => [
        'config' => [
			'root' => '/cache',
			'exists' => true
		],
        'expected' => [
			'output' => true,
			'path' => '/cache/background-css/'
		]
    ],
	'notExistsShouldReturnFalse' => [
		'config' => [
			'root' => '/cache',
			'exists' => true
		],
		'expected' => [
			'output' => true,
			'path' => '/cache/background-css/'
		]
	],

];
