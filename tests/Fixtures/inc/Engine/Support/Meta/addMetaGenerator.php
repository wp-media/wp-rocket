<?php

return [
	'testShouldReturnDefaultWhenBypass' => [
		'config' => [
			'nowprocket' => 1,
		],
		'html' => '<html><head></head><body></body></html>',
		'expected' => '<html><head></head><body></body></html>',
	],
	'testShouldReturnDefaultWhenDNRO' => [
		'config' => [
			'donotrocketoptimize' => 1,
		],
		'html' => '<html><head></head><body></body></html>',
		'expected' => '<html><head></head><body></body></html>',
	],
	'testShouldReturnDefaultWhenWL' => [
		'config' => [
			'white_label_footprint' => 1,
		],
		'html' => '<html><head></head><body></body></html>',
		'expected' => '<html><head></head><body></body></html>',
	],
	'testShouldReturnDefaultWhenFilterDisabled' => [
		'config' => [
			'disable_meta' => true,
		],
		'html' => '<html><head></head><body></body></html>',
		'expected' => '<html><head></head><body></body></html>',
	],
	'testShouldReturnDefaultWhenLoggedIn' => [
		'config' => [
			'is_user_logged_in' => true,
		],
		'html' => '<html><head></head><body></body></html>',
		'expected' => '<html><head></head><body></body></html>',
	],
	'testShouldReturnDefaultWhenNoFeatures' => [
		'config' => [
			'disable_meta' => false,
			'cdn' => 0,
			'do_caching_mobile_files' => 0,
			'preload_links' => 0,
		],
		'html' => '<html><head></head><body></body></html>',
		'expected' => '<html><head></head><body></body></html>',
	],
	'testShouldReturnAddMeta' => [
		'config' => [
			'disable_meta' => false,
			'cdn' => 0,
			'do_caching_mobile_files' => 1,
			'preload_links' => 1,
			'is_mobile' => true,
		],
		'html' => '<html><head></head><body></body></html><!-- wpr_remove_unused_css -->',
		'expected' => '<html><head><meta name="generator" content="WP Rocket 3.17" data-wpr-features="wpr_remove_unused_css wpr_mobile wpr_preload_links" /></head><body></body></html>',
	],
];
