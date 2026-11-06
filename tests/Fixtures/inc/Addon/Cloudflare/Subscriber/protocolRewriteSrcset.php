<?php

$value = [
	[
		'url' => 'http://example.org/image-128x128.jpg',
	],
	[
		'url' => 'http://example.org/image-256x256.jpg',
	],
	[
		'url' => 'http://example.org/image-1024x768.jpg',
	],
];

$updated = [
	[
		'url' => '//example.org/image-128x128.jpg',
	],
	[
		'url' => '//example.org/image-256x256.jpg',
	],
	[
		'url' => '//example.org/image-1024x768.jpg',
	],
];

return [
	'testShouldDoNothingWhenCfDisabled' => [
		'config' => [
			'cloudflare' => 0,
			'rewrite' => 1,
			'filter' => true,
		],
		'value' => $value,
		'expected' => $value,
	],
	'testShouldDoNothingWhenRewriteAndFilterDisabled' => [
		'config' => [
			'cloudflare' => 1,
			'rewrite' => 0,
			'filter' => false,
		],
		'value' => $value,
		'expected' => $value,
	],
	'testShouldDoNothingWhenEmptyValue' => [
		'config' => [
			'cloudflare' => 1,
			'rewrite' => 0,
			'filter' => false,
		],
		'value' => [],
		'expected' => [],
	],
	'testShouldRewriteWhenRewriteEnabled' => [
		'config' => [
			'cloudflare' => 1,
			'rewrite' => 1,
			'filter' => false,
		],
		'value' => $value,
		'expected' => $updated,
	],
	'testShouldRewriteWhenFilterEnabled' => [
		'config' => [
			'cloudflare' => 1,
			'rewrite' => 0,
			'filter' => true,
		],
		'value' => $value,
		'expected' => $updated,
	],
];
