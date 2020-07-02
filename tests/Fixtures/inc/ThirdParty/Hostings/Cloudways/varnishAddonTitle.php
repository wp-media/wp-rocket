<?php

return [
	'testShouldReplaceEmptyValueWithCloudwaysTitleNoVarnish' => [
		'settings'      => [],
		'config_server' => [],
		'expected'      => [
			'varnish_auto_purge' => [
				'title' => 'Varnish auto-purge will be automatically enabled once Varnish is enabled on your Cloudways server.'
			],
		],
	],
	'testShouldReplaceEmptyValueWithCloudwaysTitleWithoutVarnishApp' => [
		'settings'      => [],
		'config_server' => [
			'HTTP_X_VARNISH'     => 'HTTP_X_VARNISH',
		],
		'expected'      => [
			'varnish_auto_purge' => [
				'title' => 'Varnish auto-purge will be automatically enabled once Varnish is enabled on your Cloudways server.'
			],
		],
	],
	'testShouldReplaceEmptyValueWithCloudwaysTitleWithVarnishPass' => [
		'settings'      => [],
		'config_server' => [
			'HTTP_X_VARNISH'     => 'HTTP_X_VARNISH',
			'HTTP_X_APPLICATION' => 'varnishpass',
		],
		'expected'      => [
			'varnish_auto_purge' => [
				'title' => 'Varnish auto-purge will be automatically enabled once Varnish is enabled on your Cloudways server.'
			],
		],
	],
	'testShouldReplaceEmptyValueWithCloudwaysTitle' => [
		'settings'      => [],
		'config_server' => [
			'HTTP_X_VARNISH'     => 'HTTP_X_VARNISH',
			'HTTP_X_APPLICATION' => 'HTTP_X_APPLICATION',
		],
		'expected'      => [
			'varnish_auto_purge' => [
				'title' => 'Your site is hosted on Cloudways, we have enabled Varnish auto-purge for compatibility.'
			],
		],
	],
	'testShouldReplaceDefaultTitleWithCloudwaysTitle' => [
		'settings'      => [
			'varnish_auto_purge' => [
				'title' => 'If Varnish runs on your server, you must activate this add-on.',
			],
		],
		'config_server' => [
			'HTTP_X_VARNISH'     => 'HTTP_X_VARNISH',
			'HTTP_X_APPLICATION' => 'HTTP_X_APPLICATION',
		],
		'expected'      => [
			'varnish_auto_purge' => [
				'title' => 'Your site is hosted on Cloudways, we have enabled Varnish auto-purge for compatibility.'
			],
		],
	],
];
