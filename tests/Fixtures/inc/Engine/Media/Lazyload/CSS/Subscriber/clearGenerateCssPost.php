<?php

$post = new WP_Post((object) ['ID' => 1]);

return [
    'shouldDelete' => [
        'config' => [
			'should_delete' => true,
			'post' => $post,
			'url' => 'http://example.org',
        ],
		'expected' => [
			'post' => 1,
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
			'post' => 1,
			'url' => 'http://example.org',
		]
	],

];
