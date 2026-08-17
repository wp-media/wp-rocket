<?php
return [
	'testShouldReturnUpdatedArrayOfHiddenFields' => [
		'config' => [
			'remove_unused_css',
			'async_css',
		],
		'expected' => [
			'remove_unused_css',
			'async_css',
			'cdn_type',
			'rocketcdn_free_enabled',
			'rocketcdn_pro_enabled',
			'cdn_byocdn_enabled',
		],
	],
];
