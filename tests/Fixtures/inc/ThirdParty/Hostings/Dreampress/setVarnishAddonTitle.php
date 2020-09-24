<?php

return [
	'test_data' => [
		'testShouldReplaceEmptyValueWithDreamPressTitle' => [
			'settings' => [],
			'expected' => [
				'varnish_auto_purge' => [
					'title' => 'Your site is hosted on DreamPress, we have enabled Varnish auto-purge for compatibility.'
				],
			],
		],
		'testShouldReplaceDefaultTitleWithDreamPressTitle' => [
			'settings' => [
				'varnish_auto_purge' => [
					'title' => 'If Varnish runs on your server, you must activate this add-on.',
				],
			],
			'expected' => [
				'varnish_auto_purge' => [
					'title' => 'Your site is hosted on DreamPress, we have enabled Varnish auto-purge for compatibility.'
				],
			],
		],
	]
];
