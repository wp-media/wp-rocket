<?php
$post = new WP_Post((object)[]);
return [
    'noPostShouldReturnSame' => [
        'config' => [
			'post' => false,
	        'purge_urls' => [],
	        'post_id' => 145,
			'filtered_purge_urls' => [
				'http://example.org/author/'
			],
        ],
        'expected' => [
			'post_id' => 145,
			'result' => [],
			'purge_urls' => [],
			'post' => false,
        ]
    ],
    'postShouldReturnAddURL' => [
	    'config' => [
		    'post' => $post,
		    'purge_urls' => [],
		    'post_id' => 145,
			'filtered_purge_urls' => [
				'http://example.org/author/'
			],
		],
	    'expected' => [
		    'post_id' => 145,
		    'result' => [
				'http://example.org/author/'
			],
		    'purge_urls' => [],
		    'post' => $post,
	    ]
    ],
];
