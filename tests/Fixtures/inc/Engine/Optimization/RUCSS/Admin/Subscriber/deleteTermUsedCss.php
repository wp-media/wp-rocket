<?php

return [
	'shouldDoNothingWhenRucssDisabled' => [
		'config' => [
			'remove_unused_css' => false,
			'url' => 'http://example.org/category/test/',
			'wp_error' => false,
			'term_id' => 1,
			'removed' => false,
			'deletion_activated' => true,
		],
	],
	'shouldDoNothingWhenWPError' => [
		'config' => [
			'remove_unused_css' => true,
			'url' => 'http://example.org/category/test/',
			'wp_error' => true,
			'term_id' => 1,
			'removed' => false,
			'deletion_activated' => true,
			'is_disabled' => true,
		],
	],
	'shouldDelete' => [
		'config' => [
			'remove_unused_css' => true,
			'url' => 'http://example.org/category/test/',
			'wp_error' => false,
			'term_id' => 1,
			'removed' => true,
			'deletion_activated' => true,
			'is_disabled' => true,
		],
	],
	'shouldNotDeleteOnHookDisabled' => [
		'config' => [
			'remove_unused_css' => true,
			'is_disabled' => false,
			'url' => 'http://example.org/category/test/',
			'term_id' => 1,
			'wp_error' => false,
			'removed' => false,
		]
	]
];
