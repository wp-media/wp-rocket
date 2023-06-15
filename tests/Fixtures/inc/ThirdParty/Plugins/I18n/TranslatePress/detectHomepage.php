<?php
return [
    'SameURLShouldReturnItAsHomepage' => [
		'config' => [
			'home_url' => 'http://example.org/',
			'url' => 'http://example.org/fr',
			'language' => 'fr',
			'url_language' => 'http://example.org/fr',
		],
		'expected' => [
			'url' => 'http://example.org/fr',
			'language' => 'fr',
			'result' => 'http://example.org/fr',
		]
    ],
    'DifferentURLShouldKeepOriginal' => [
        'config' => [
              'home_url' => 'http://example.org/',
              'url' => 'http://example.org/fr',
			  'language' => 'fr',
			  'url_language' => 'http://example.org/fr2',
        ],
        'expected' => [
			'url' => 'http://example.org/fr',
			'language' => 'fr',
			'result' => 'http://example.org/',
        ]
    ],

];
