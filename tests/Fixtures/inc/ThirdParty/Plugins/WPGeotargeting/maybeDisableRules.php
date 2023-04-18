<?php
return [
    'noQueryShouldTrue' => [
        'config' => [
              'bool' => false,
              'opts' => [],
              'current_url' => 'http://example.org',
			  'query' => '',
        ],
        'expected' => true
    ],
	'QueryShouldReturnFalse' => [
		'config' => [
			'bool' => false,
			'opts' => [],
			'current_url' => 'http://example.org?nowprocket',
			'query' => 'nowprocket',
		],
		'expected' => false
	],
];
