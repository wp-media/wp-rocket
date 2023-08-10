<?php
return [
    'ShouldReturnPAth' => [
        'config' => [
              'url' => 'https://example.org/test?test=1',
              'path' => '/root/path',
        ],
        'expected' => [
			'stripped_url' => 'https://example.org/test',
			'output' => '/root/path'
		]
    ],
	'InvalidShouldReturnEmpty' => [
		'config' => [
			'url' => 'https://example.org/test?test=1',
			'path' => false,
		],
		'expected' => [
			'stripped_url' => 'https://example.org/test',
			'output' => ''
		]
	],
];
