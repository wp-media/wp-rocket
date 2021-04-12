<?php

$items = [
	[
		'url'            => 'http://example.org/path1',
		'css'            => 'h1{color:red;}',
		'unprocessedcss' => wp_json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => false,
	],
	[
		'url'            => 'http://example.org/path2',
		'css'            => 'h1{color:red;}',
		'unprocessedcss' => wp_json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => false,
	],
];


return [
	'shouldNotDeleteOnUpdateDueToMissingSettings' => [
		'input' => [
			'remove_unused_css' => false,
			'items'             => $items,
		]
	],
	'shouldDeleteOnUpdate' => [
		'input' => [
			'remove_unused_css' => true,
			'items'             => $items,
		]
	],
];
