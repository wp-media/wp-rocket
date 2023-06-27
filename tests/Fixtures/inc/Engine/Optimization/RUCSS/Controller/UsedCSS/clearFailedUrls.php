<?php

return [
	'testShouldBailOutWithEmptyRows' => [
		'config' => [
			'rows' => [],
		],
        'expected' => [
            'failed_urls' => [],
        ],
	],
    'testShouldBailOutWithAlphabeticStringAsId' => [
		'config' => [
			'rows' => [
                (object) [
                    'id' => 'two',
                    'url' => 'http://example.org/test-2',
                ],
                (object) [
                    'id' => 'three',
                    'url' => 'http://example.org/test-3',
                ],
                (object) [
                    'id' => 'four',
                    'url' => 'http://example.org/test-4',
                ],
            ],
            'is_int' => false,
		],
        'expected' => [
            'failed_urls' => [
                'http://example.org/test-2',
                'http://example.org/test-3',
                'http://example.org/test-4',
            ],
        ],
	],
    'testShouldClearFailedUrlsWithNumericStringAsId' => [
		'config' => [
			'rows' => [
                (object) [
                    'id' => '2',
                    'url' => 'http://example.org/test-2',
                ],
                (object) [
                    'id' => '3',
                    'url' => 'http://example.org/test-3',
                ],
                (object) [
                    'id' => '4',
                    'url' => 'http://example.org/test-4',
                ],
            ],
            'is_int' => true,
		],
        'expected' => [
            'failed_urls' => [
                'http://example.org/test-2',
                'http://example.org/test-3',
                'http://example.org/test-4',
            ],
        ],
	],
    'testShouldClearFailedUrlsWithIntegergAsId' => [
		'config' => [
			'rows' => [
                (object) [
                    'id' => 2,
                    'url' => 'http://example.org/test-2',
                ],
                (object) [
                    'id' => 3,
                    'url' => 'http://example.org/test-3',
                ],
                (object) [
                    'id' => 4,
                    'url' => 'http://example.org/test-4',
                ],
            ],
            'is_int' => true,
		],
        'expected' => [
            'failed_urls' => [
                'http://example.org/test-2',
                'http://example.org/test-3',
                'http://example.org/test-4',
            ],
        ],
	],
];
