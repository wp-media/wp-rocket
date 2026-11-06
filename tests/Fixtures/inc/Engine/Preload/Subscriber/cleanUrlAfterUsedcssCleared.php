<?php
return [
	'manualPreloadOffShouldNotClean' => [
		'config' => [
			'manual_preload' => false,
			'url' => 'https://example.org/url',
		],
		'expected' => [
			'cleaned' => false,
		],
	],
	'manualPreloadOnShouldCleanAndReleaseLock' => [
		'config' => [
			'manual_preload' => true,
			'url' => 'https://example.org/url',
		],
		'expected' => [
			'cleaned' => true,
		],
	],
];
