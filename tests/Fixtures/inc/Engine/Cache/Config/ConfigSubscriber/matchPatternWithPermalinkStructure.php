<?php
return [
    'testShouldReturnEmptyArrayWhenPatternIsEmpty' => [
        'config' => [
            'patterns' => [],
        ],
        'expected' => [],
    ],
    'testShouldMatchPatternWithPermalinkStructureWithTrailingSlash' => [
        'config' => [
            'patterns' => [
                '/hello-world',
                '/testing/(.*)'
            ],
            'permalink' => [
                'trailing_slash' => true,
                'structure' => '/%postname%/',
            ],
        ],
        'expected' => [
            '/hello-world/',
            '/testing/(.*)/',
        ],
    ],
    'testShouldMatchPatternWithPermalinkStructureWithoutTrailingSlash' => [
        'config' => [
            'patterns' => [
                '/hello-world',
                '/testing/(.*)'
            ],
            'permalink' => [
                'trailing_slash' => false,
                'structure' => '/%postname%',
            ],
        ],
        'expected' => [
            '/hello-world',
            '/testing/(.*)',
        ],
    ],
];