<?php
return [
    'existingShouldJustCheck' => [
        'config' => [
			'exists' => true,
			'is_writable' => false,
			'root' => '/var/html/wp-content/cache'
        ],
        'expected' => [
			'path' => '/var/html/wp-content/cache/background-css/',
			'output' => false
        ]
    ],

	'notExistingShouldCreate' => [
		'config' => [
			'exists' => false,
			'is_writable' => true,
			'root' => '/var/html/wp-content/cache'
		],
		'expected' => [
			'path' => '/var/html/wp-content/cache/background-css/',
			'output' => true
		]
	],

];
