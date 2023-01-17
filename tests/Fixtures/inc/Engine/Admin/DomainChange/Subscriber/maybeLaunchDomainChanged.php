<?php
return [
	'noBaseUrlShouldAdd' =>  [
		'config' => [
			'base_url' => 'http://www.example.org',
			'last_base_url' => 'http://example.org',
			'is_base_url_different' => true,
			'base_url_exist' => false,
		],
		'expected' => 'http://www.example.org'
	],
	'baseUrlMatchingShouldDoNothing' => [
		'config' => [
			'base_url' => 'http://example.org',
			'last_base_url' => 'http://example.org',
			'is_base_url_different' => false,
			'base_url_exist' => true,
		],
		'expected' => 'http://example.org'
	],
	'baseUrlNotMatchingShouldFireHook' => [
		'config' => [
			'base_url' => 'http://www.example.org',
			'last_base_url' => 'http://example.org',
			'is_base_url_different' => true,
			'base_url_exist' => true,
		],
		'expected' => 'http://www.example.org'
	]
];
