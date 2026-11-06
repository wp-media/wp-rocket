<?php
return [
	'noWebpAndNotExistingShouldReturnFalse' => [
		'config' => [
			'ssl' => true,
			'url' => 'url',
			'parsed_url' => [
				'host' => 'host',
				'path' => 'path',
				'query' => 'query',
			],
			'cache_ssl' => true,
			'cache_webp' => false,
			'cache_path' => 'WP_ROCKET_CACHE_PATHhostpath/#query/index-https.html',
			'cache_path_exists' => false,
			'webp_path' => 'WP_ROCKET_CACHE_PATHhostpath/#query/index-https-webp.html',
			'webp_path_exists' => true,
			'nowebp_path' => 'WP_ROCKET_CACHE_PATHhost/path/#query/.no-webp',
			'nowebp_path_exists' => true
		],
		'expected' => false,
	],
	'noWebpAndExistingShouldReturnTrue' => [
		'config' => [
			'ssl' => true,
			'url' => 'url',
			'parsed_url' => [
				'host' => 'host',
				'path' => 'path',
				'query' => 'query',
			],
			'cache_ssl' => true,
			'cache_webp' => false,
			'cache_path' => 'WP_ROCKET_CACHE_PATHhostpath/#query/index-https.html',
			'cache_path_exists' => true,
			'webp_path' => 'WP_ROCKET_CACHE_PATHhostpath/#query/index-https-webp.html',
			'webp_path_exists' => false,
			'nowebp_path' => 'WP_ROCKET_CACHE_PATHhost/path/#query/.no-webp',
			'nowebp_path_exists' => false
		],
		'expected' => true
	],
	'webpAndNotExistingShouldReturnFalse' => [
		'config' => [
			'ssl' => true,
			'url' => 'url',
			'parsed_url' => [
				'host' => 'host',
				'path' => 'path',
				'query' => 'query',
			],
			'cache_ssl' => true,
			'cache_webp' => true,
			'cache_path' => 'WP_ROCKET_CACHE_PATHhostpath/#query/index-https.html',
			'cache_path_exists' => true,
			'webp_path' => 'WP_ROCKET_CACHE_PATHhostpath/#query/index-https-webp.html',
			'webp_path_exists' => false,
			'nowebp_path' => 'WP_ROCKET_CACHE_PATHhostpath/#query/.no-webp',
			'nowebp_path_exists' => false
		],
		'expected' => false
	],
	'webpAndExistingWebpShouldReturnTrue' => [
		'config' => [
			'ssl' => true,
			'url' => 'url',
			'parsed_url' => [
				'host' => 'host',
				'path' => 'path',
				'query' => 'query',
			],
			'cache_ssl' => true,
			'cache_webp' => true,
			'cache_path' => 'WP_ROCKET_CACHE_PATHhostpath/#query/index-https.html',
			'cache_path_exists' => true,
			'webp_path' => 'WP_ROCKET_CACHE_PATHhostpath/#query/index-https-webp.html',
			'webp_path_exists' => true,
			'nowebp_path' => 'WP_ROCKET_CACHE_PATHhostpath/#query/.no-webp',
			'nowebp_path_exists' => false
		],
		'expected' => true
	],
	'webpAndExistingNoWebpShouldReturnTrue' => [
		'config' => [
			'ssl' => true,
			'url' => 'url',
			'parsed_url' => [
				'host' => 'host',
				'path' => 'path',
				'query' => 'query',
			],
			'cache_ssl' => true,
			'cache_webp' => true,
			'cache_path' => 'WP_ROCKET_CACHE_PATHhostpath/#query/index-https.html',
			'cache_path_exists' => false,
			'webp_path' => 'WP_ROCKET_CACHE_PATHhostpath/#query/index-https-webp.html',
			'webp_path_exists' => true,
			'nowebp_path' => 'WP_ROCKET_CACHE_PATHhostpath/#query/.no-webp',
			'nowebp_path_exists' => true
		],
		'expected' => true
	],
];
