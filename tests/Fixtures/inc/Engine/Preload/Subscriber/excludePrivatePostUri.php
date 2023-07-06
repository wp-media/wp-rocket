<?php

return [
    'testShouldReturnOriginRegexIfNoPrivatePost' => [
        'config' => [
            'regex' => [
                '/page/\d+',
            ],
            'have_posts' => false,
            'posts' => [],
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
        ],
        'expected' => [
            '/page/\d+',
            'http://example.org/test-4/',
        ],
    ],
];