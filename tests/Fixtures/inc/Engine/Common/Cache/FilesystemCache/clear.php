<?php
return [
    'shouldClear' => [
        'config' => [
			'root' => '/cache',
			'exists' => true
		],
        'expected' => [
			'output' => true,
			'path' => '/cache/background-css/1/'
		]
    ],
	'notExistsShouldReturnFalse' => [
		'config' => [
			'root' => '/cache',
			'exists' => true
		],
		'expected' => [
			'output' => true,
			'path' => '/cache/background-css/1/'
		]
	],

];
