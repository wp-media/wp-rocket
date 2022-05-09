<?php
return [
	'shouldCallCleanCacheUrl' => [
		'config' => [
			'lang' => 'lang',
			'root' => 'root',
			'base_url' => 'http://example.org',
		],
		'expected' => [
			'url' => 'http://example.org/kinsta-clear-cache/',
			'config' => [
				'blocking' => false,
				'timeout'  => 0.01,
			],
		]
	]
];
