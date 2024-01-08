<?php
return [
	'testShouldClearFailedUrlsRUCSS' => [
		'config' => [
			'value' => 3,
            'unit' => 'days',
            'is_allowed' => true,
            'optimization_type' => 'rucss',
		],
        'expected' => [
            'failed_urls' => [
                'http://example.org/test-2',
                'http://example.org/test-3',
                'http://example.org/test-4',
            ],
        ],
	],
    'testShouldClearFailedUrlsATF' => [
		'config' => [
			'value' => 3,
            'unit' => 'days',
            'is_allowed' => true,
            'optimization_type' => 'atf',
		],
        'expected' => [
            'failed_urls' => [
                'http://example.org/test-2',
                'http://example.org/test-3',
                'http://example.org/test-4',
            ],
        ],
	],
    'testShouldNotClearFailedUrlsIfNotAllowed' => [
		'config' => [
			'value' => 3,
            'unit' => 'days',
            'is_allowed' => false,
		],
        'expected' => [
            'failed_urls' => [],
        ],
	],
];

