<?php

$post = new WP_Post((object) []);

return [
    'shouldDelete' => [
        'config' => [
			'should_delete' => true,
			'post' => $post,
			'url' => 'http://example.org',
        ],
		'expected' => [
			'post' => $post,
			'url' => 'http://example.org',
		]
    ],

	'shouldNoUrlShouldNotDelete' => [
		'config' => [
			'should_delete' => false,
			'post' => $post,
			'url' => false,
		],
		'expected' => [
			'post' => $post,
			'url' => 'http://example.org',
		]
	],

];
