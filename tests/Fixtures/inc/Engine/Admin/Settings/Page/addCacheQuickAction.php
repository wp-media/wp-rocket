<?php

$referer = 'http%3A%2F%2Fexample.org%2Fwp-admin%2Foptions-general.php%3Fpage%3Dwprocket%23dashboard';

return [
	'testShouldReturnActionsUnchangedWithoutCapability' => [
		'config'   => [ 'can' => false, 'valid_key' => true, 'actions' => [ 'existing' ] ],
		'expected' => [ 'existing' ],
	],
	'testShouldReturnActionsUnchangedWithInvalidKey' => [
		'config'   => [ 'can' => true, 'valid_key' => false, 'actions' => [] ],
		'expected' => [],
	],
	'testShouldAppendTheCacheAction' => [
		'config'   => [ 'can' => true, 'valid_key' => true, 'actions' => [ 'existing' ] ],
		'expected' => [
			'existing',
			[
				'id'           => 'purge_cache',
				'title'        => 'Cache files',
				'description'  => 'Clear and preload all the cache files.',
				'button_label' => 'Clear and preload',
				'button_url'   => "http://example.org/wp-admin/admin-post.php?action=purge_cache&type=all&_wp_http_referer={$referer}|purge_cache_all",
			],
		],
	],
];
