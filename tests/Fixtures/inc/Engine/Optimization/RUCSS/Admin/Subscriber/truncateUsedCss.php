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
		],
		'config' => [
			'remove_unused_css' => true,
			'is_disabled' => true,
			'delete_used_css_row' => true,
			'used_css_count' => 10,
		],
	],
	'shouldTruncateUnusedCSS' => [
		'input' => [
			'remove_unused_css' => true,
			'items'             => $items,
		],
		'config' => [
			'remove_unused_css' => true,
			'is_disabled' => true,
			'delete_used_css_row' => true,
			'used_css_count' => 10,
		],
	],
	'shouldNoTruncateOnHookDisabled' =>  [
		'input' => [
			'remove_unused_css' => true,
			'items'             => $items,
		],
		'config' => [
			'remove_unused_css' => true,
			'is_disabled' => false,
			'used_css_count' => 10,
		],
	]
];
