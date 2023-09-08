<?php

return [
    'testShouldReturnOriginRegexIfNoPrivatePost' => [
        'config' => [
            'regex' => [
                '/page/\d+',
            ],
            'have_posts' => false,
            'posts' => [],
	        'url' => 'http://example.org/test-4/',
            'post_types' => [
	            'post',
	            'page',
	            'attachment',
            ],
        ],
        'expected' => [
            '/page/\d+',
        ],
    ],
    'testShouldNotReturnUrlIfNotPrivate' => [
	    'config' => [
		    'regex' => [
			    '/page/\d+',
		    ],
		    'have_posts' => true,
		    'posts' => [
			    (object) [
				    'ID' => 2,
				    'post_status' => 'private',
			    ],
		    ],
		    'get_permalink' => [
			    'http://example.org/test-4/',
		    ],
		    'url' => 'http://example.org/test-400/',
		    'post_types' => [
				'post',
			    'page',
			    'attachment',
		    ],
	    ],
	    'expected' => [
		    '/page/\d+',
	    ],
    ],
    'testShouldReturnExpectedRegex' => [
	    'config' => [
		    'regex' => [
			    '/page/\d+',
		    ],
		    'have_posts' => true,
		    'posts' => [
			    (object) [
				    'ID' => 2,
				    'post_status' => 'private',
			    ],
		    ],
		    'get_permalink' => [
			    'http://example.org/test-4/',
		    ],
		    'url' => 'http://example.org/test-4/',
		    'post_types' => [
			    'post',
			    'page',
			    'attachment',
		    ],
	    ],
	    'expected' => [
		    '/page/\d+',
		    'http://example.org/test-4/',
	    ],
    ],
];
