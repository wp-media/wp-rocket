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
			'cdn_state',
		],
	],
];
