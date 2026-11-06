<?php

return [
	'testShouldReplaceDefaultTitleWithWPXCloudTitle' => [
		'settings'      => [
			'varnish_auto_purge' => [
				'title' => 'If Varnish runs on your server, you must activate this add-on.',
			],
		],
		'expected'      => [
			'varnish_auto_purge' => [
				'title' => 'Your site is hosted on WPX, we have enabled Varnish auto-purge for compatibility.'
			],
		],
	],
];
