<?php

return [
	'test_data' => [
		'testShouldReplaceEmptyValueWithO2SwitchTitle' => [
			'settings' => [],
			'expected' => [
				'varnish_auto_purge' => [
					'title' => 'Your site is hosted on O2Switch, we have enabled Varnish auto-purge for compatibility.'
				],
			],
		],
		'testShouldReplaceDefaultTitleWithWPEngineTitle' => [
			'settings' => [
				'varnish_auto_purge' => [
					'title' => 'If Varnish runs on your server, you must activate this add-on.',
				],
			],
			'expected' => [
				'varnish_auto_purge' => [
					'title' => 'Your site is hosted on O2Switch, we have enabled Varnish auto-purge for compatibility.'
				],
			],
		],
	]
];
