<?php
return [
	'shouldCallCleanCacheUrl' => [
		'config' => [
			'lang' => 'lang',
			'root' => 'root',
			'base_url' => 'https://example.com',
		],
		'expected' => [
			'url' => 'https://example.com/kinsta-clear-cache/',
			'config' => [
				'blocking' => false,
				'timeout'  => 0.01,
			],
		]
	]
];
