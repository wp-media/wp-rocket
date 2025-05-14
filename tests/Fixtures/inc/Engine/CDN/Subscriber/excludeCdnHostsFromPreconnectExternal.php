<?php
return [
	'cdn_disabled' => [
		'config' => [
			'cdn' => false,
			'exclusions' => [],
			'cdn_hosts' => [],
		],
		'expected' => [],
	],
	'no_cdn_hosts' => [
		'config' => [
			'cdn' => true,
			'exclusions' => [],
			'cdn_hosts' => [],
		],
		'expected' => [],
	],
	'with_cdn_hosts' => [
		'config' => [
			'cdn' => true,
			'exclusions' => [],
			'cdn_hosts' => ['cdn1.example.com', 'cdn2.example.com'],
		],
		'expected' => [
			['type' => 'domain', 'value' => 'cdn1.example.com'],
			['type' => 'domain', 'value' => 'cdn2.example.com'],
		],
	],
	'existing_exclusions' => [
		'config' => [
			'cdn' => true,
			'exclusions' => [['type' => 'domain', 'value' => 'existing.com']],
			'cdn_hosts' => ['cdn1.example.com'],
		],
		'expected' => [
			['type' => 'domain', 'value' => 'existing.com'],
			['type' => 'domain', 'value' => 'cdn1.example.com'],
		],
	],
];
