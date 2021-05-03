<?php

$items = [
	[
		'url'            => 'http://example.org/home',
		'css'            => 'h1{color:red;}',
		'unprocessedcss' => wp_json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => false,
	],
	[
		'url'            => 'http://example.org/home',
		'css'            => 'h1{color:red;}',
		'unprocessedcss' => wp_json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => true,
	],
];


return [
	'shouldNotTruncateUnusedCSSDueToMissingSettings' => [
		'input' => [
			'remove_unused_css' => false,
			'items'             => $items,
		]
	],
	'shouldTruncateUnusedCSS' => [
		'input' => [
			'remove_unused_css' => true,
			'items'             => $items,
		]
	],
];
