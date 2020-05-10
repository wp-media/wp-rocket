<?php

return [
	'testShouldReplaceEmptyValueWithCloudwaysTitle' => [
		'settings' => [],
		'expected' => [
			'varnish_auto_purge' => [
				'title' => 'Your site is hosted on Cloudways, we have enabled Varnish auto-purge for compatibility.'
			],
		],
	],
	'testShouldReplaceDefaultTitleWithCloudwaysTitle' => [
		'settings' => [
			'varnish_auto_purge' => [
				'title' => 'If Varnish runs on your server, you must activate this add-on.',
			],
		],
		'expected' => [
			'varnish_auto_purge' => [
				'title' => 'Your site is hosted on Cloudways, we have enabled Varnish auto-purge for compatibility.'
			],
		],
	],
];
