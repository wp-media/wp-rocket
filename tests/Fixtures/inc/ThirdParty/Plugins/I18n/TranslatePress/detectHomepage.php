<?php
return [
    'SameURLShouldReturnItAsHomepage' => [
		'config' => [
			'home_url' => 'http://example.org/fr',
			'url' => 'http://example.org/fr',
			'language' => 'fr',
		],
		'expected' => 'http://example.org/fr',
    ],
    'DifferentURLShouldKeepOriginal' => [
        'config' => [
			'home_url' => 'http://example.org/fr',
            'url' => 'http://example.org/us',
			'language' => 'us',
        ],
        'expected' => 'http://example.org/us',
    ],
];
