<?php
return [
	[
		'config' => [
			'options'                        => [
				'cache_reject_uri' => [],
			],
			'home_dirname'                   => '',
			'filter_rocket_cache_reject_uri' => [],
		],
		'expected' => '',
	],
	[
		'config' => [
			'options'                        => [
				'cache_reject_uri' => [
					'/members/(.*)',
				],
			],
			'home_dirname'                   => '',
			'filter_rocket_cache_reject_uri' => [],
		],
		'expected' => '',
	],
	[
		'config' => [
			'options'                        => [
				'cache_reject_uri' => [],
			],
			'home_dirname'                   => '',
			'filter_rocket_cache_reject_uri' => [
				'/(.+/)?feed/?.+/?',
				'/(?:.+/)?embed/',
			],
		],
		'expected' => '/(.+/)?feed/?.+/?|/(?:.+/)?embed/',
	],
	[
		'config' => [
			'options'                        => [
				'cache_reject_uri' => [
					'/members/(.*)',
				],
			],
			'home_dirname'                   => '',
			'filter_rocket_cache_reject_uri' => [
				'/(.+/)?feed/?.+/?',
				'/(?:.+/)?embed/',
				'/members/(.*)',
			],
		],
		'expected' => '/(.+/)?feed/?.+/?|/(?:.+/)?embed/|/members/(.*)',
	],
	[
		'config' => [
			'options'                        => [
				'cache_reject_uri' => [],
			],
			'home_dirname'                   => '/',
			'filter_rocket_cache_reject_uri' => [
				'/(.+/)?feed/?.+/?',
				'/(?:.+/)?embed/',
			],
		],
		'expected' => '/(/(.+/)?feed/?.+/?|/(?:.+/)?embed/)',
	],
	[
		'config' => [
			'options'                        => [
				'cache_reject_uri' => [
					'/members/(.*)',
				],
			],
			'home_dirname'                   => '/',
			'filter_rocket_cache_reject_uri' => [
				'/(.+/)?feed/?.+/?',
				'/(?:.+/)?embed/',
				'/members/(.*)',
			],
		],
		'expected' => '/(/(.+/)?feed/?.+/?|/(?:.+/)?embed/|/members/(.*))',
	],
	[
		'config' => [
			'options'                        => [
				'cache_reject_uri' => [],
			],
			'home_dirname'                   => '/subfolder/',
			'filter_rocket_cache_reject_uri' => [
				'/(.+/)?feed/?.+/?',
				'/(?:.+/)?embed/',
			],
		],
		'expected' => '/subfolder/(/(.+/)?feed/?.+/?|/(?:.+/)?embed/)',
	],
	[
		'config' => [
			'options'                        => [
				'cache_reject_uri' => [
					'/subfolder/members/(.*)',
				],
			],
			'home_dirname'                   => '/subfolder/',
			'filter_rocket_cache_reject_uri' => [
				'/(.+/)?feed/?.+/?',
				'/(?:.+/)?embed/',
				'/members/(.*)',
			],
		],
		'expected' => '/subfolder/(/(.+/)?feed/?.+/?|/(?:.+/)?embed/|/members/(.*))',
	],
	[
		'config' => [
			'options'                        => [
				'cache_reject_uri' => [
					'/members/(.*)',
				],
			],
			'home_dirname'                   => '/subfolder/',
			'filter_rocket_cache_reject_uri' => [
				'/(.+/)?feed/?.+/?',
				'/(?:.+/)?embed/',
				'/members/(.*)',
			],
		],
		'expected' => '/subfolder/(/(.+/)?feed/?.+/?|/(?:.+/)?embed/|/members/(.*))',
	],
];
