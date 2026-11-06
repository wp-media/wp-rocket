<?php
return [
    'NoUrlShouldRedirectToReferer' => [
        'config' => [
              'url' => '',
			  'referer' => 'referer',
        ],
		'expected' => 'referer'
    ],
	'UrlShouldRedirectToUrl' => [
		'config' => [
			'url' => 'http://example.org',
			'referer' => 'referer',
		],
		'expected' => 'http://example.org'
	],
];
