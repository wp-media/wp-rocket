<?php

return [
	'shouldHandleSingleResource' => [
		'resourceID'   => 10,
		'returnedItem' => [
			'id'      => 10,
			'url'     => 'https://example.com/path/to/style.css',
			'type'    => 'css',
			'content' => 'h1 {color: red;}'
		],
		'sendSuccess' => true,
		'expected' => true,
	]
];
