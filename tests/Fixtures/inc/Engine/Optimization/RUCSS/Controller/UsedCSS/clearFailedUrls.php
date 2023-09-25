<?php
$add_to_queue_response = [
	'code'     => 200,
	'contents' => [ 'jobId' => '2' ]
];
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
					'is_mobile' => false,
                ],
                (object) [
                    'id' => 'three',
                    'url' => 'http://example.org/test-3',
					'is_mobile' => false,
                ],
                (object) [
                    'id' => 'four',
                    'url' => 'http://example.org/test-4',
					'is_mobile' => false,
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
					'is_mobile' => false,
                ],
                (object) [
                    'id' => '3',
                    'url' => 'http://example.org/test-3',
					'is_mobile' => false,
                ],
                (object) [
                    'id' => '4',
                    'url' => 'http://example.org/test-4',
					'is_mobile' => false,
                ],
            ],
            'is_int' => true,
			'add_to_queue_response' => $add_to_queue_response,
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
					'is_mobile' => false,
                ],
                (object) [
                    'id' => 3,
                    'url' => 'http://example.org/test-3',
					'is_mobile' => false,
                ],
                (object) [
                    'id' => 4,
                    'url' => 'http://example.org/test-4',
					'is_mobile' => false,
                ],
            ],
            'is_int' => true,
			'add_to_queue_response' => $add_to_queue_response,
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
