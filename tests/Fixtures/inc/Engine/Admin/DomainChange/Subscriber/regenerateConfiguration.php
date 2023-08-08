<?php
return [
    'ActionShouldBeDone' => [
        'config' => [
			'is_validated' => true,
			'transient' => base64_encode('http://www.example.org'),
			'home_url' => 'http://example.org',
			'last_base_url'=> 'http://www.example.org',
        ],
		'expected' => [
			'done' => true,
			'deleted' => true,
		]
    ],
	'ActionShouldDoNothingWhenNoTransient' => [
		'config' => [
			'is_validated' => true,
			'transient' => false,
			'home_url' => 'http://example.org',
			'last_base_url'=> 'http://www.example.org',
		],
		'expected' => [
			'done' => true,
			'deleted' => true,
		]
	],
	'ActionShouldDoNothingOnValidation' => [
		'config' => [
			'is_validated' => false,
			'transient' => base64_encode('http://www.example.org'),
			'home_url' => 'http://example.org',
			'last_base_url'=> 'http://www.example.org',
		],
		'expected' => [
			'done' => true,
			'deleted' => true,
		]
	],
];
