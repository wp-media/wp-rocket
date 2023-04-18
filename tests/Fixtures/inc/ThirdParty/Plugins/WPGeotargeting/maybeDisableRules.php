<?php
return [
    'noQueryShouldStayFalse' => [
        'config' => [
              'bool' => false,
              'opts' => [],
              'current_url' => 'http://example.org',
			  'query' => '',
        ],
        'expected' => false
    ],
	'QueryShouldReturnTrue' => [
		'config' => [
			'bool' => false,
			'opts' => [],
			'current_url' => 'http://example.org',
			'query' => '',
		],
		'expected' => true
	],
];
