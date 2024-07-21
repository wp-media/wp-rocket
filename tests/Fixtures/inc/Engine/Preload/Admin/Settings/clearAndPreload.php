<?php

return [
	'testShouldOnlyTruncateWhenPreloadDisabled' => [
		'config'   => [
			'options' => [
				'manual_preload' => 0,
			],
		],
		'expected' => false,
	],
	'testShouldPreloadHomepageAndSitemap' => [
		'config'   => [
			'options' => [
				'manual_preload' => 1,
			],
		],
		'expected' => true,
	],
];
