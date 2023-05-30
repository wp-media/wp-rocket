<?php
$post = Mockery::mock(WP_Post::class);
return [
    'noPostShouldReturnSame' => [
        'config' => [
			'post' => false,
	        'purge_urls' => [],
	        'post_id' => 145,
        ],
        'expected' => [
			'post_id' => 145,
			'result' => [],
			'purge_urls' => [],
			'filtered_purge_urls' => [
				'http://example.org/test'
			],
			'post' => false,
        ]
    ],
    'postShouldReturnAddURL' => [
	    'config' => [
		    'post' => $post,
		    'purge_urls' => [],
		    'post_id' => 145,
	    ],
	    'expected' => [
		    'post_id' => 145,
		    'result' => [],
		    'purge_urls' => [],
		    'filtered_purge_urls' => [
			    'http://example.org/test'
		    ],
		    'post' => $post,
	    ]
    ],
];
