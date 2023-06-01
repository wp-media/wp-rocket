<?php
$post = Mockery::mock(WP_Post::class);
return [
    'noPostShouldReturnSame' => [
        'config' => [
			'post' => false,
	        'purge_urls' => [],
	        'post_id' => 145,
			'filtered_purge_urls' => [
				'http://example.org/test'
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
				'http://example.org/test'
			],
		],
	    'expected' => [
		    'post_id' => 145,
		    'result' => [],
		    'purge_urls' => [],
		    'post' => $post,
	    ]
    ],
];
