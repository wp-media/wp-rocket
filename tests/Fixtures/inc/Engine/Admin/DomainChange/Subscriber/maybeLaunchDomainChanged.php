<?php
return [
	'bailoutWithAjax' =>  [
		'config' => [
			'ajax_request' => true,
			'base_url' => 'http://www.example.org',
			'last_base_url' => base64_encode('http://example.org'),
			'is_base_url_different' => true,
			'base_url_exist' => false,
		],
		'expected' => [
			'url' => 'http://www.example.org',
			'old_url' =>'http://example.org',
			'encrypted_old_url' => base64_encode('http://www.example.org')
		]
	],
	'noBaseUrlShouldAdd' =>  [
		'config' => [
			'ajax_request' => false,
			'base_url' => 'http://www.example.org',
			'last_base_url' => base64_encode('http://example.org'),
			'is_base_url_different' => true,
			'base_url_exist' => false,
		],
		'expected' => [
			'url' => 'http://www.example.org',
			'old_url' =>'http://example.org',
			'encrypted_old_url' => base64_encode('http://www.example.org')
		]
	],
	'baseUrlMatchingShouldDoNothing' => [
		'config' => [
			'ajax_request' => false,
			'base_url' => 'http://example.org',
			'last_base_url' => base64_encode('http://example.org'),
			'is_base_url_different' => false,
			'base_url_exist' => true,
		],
		'expected' => [
			'url' => 'http://example.org',
			'old_url' =>'http://example.org',
			'encrypted_old_url' => base64_encode('http://example.org')
		]
	],
	'baseUrlNotMatchingShouldFireHook' => [
		'config' => [
			'ajax_request' => false,
			'base_url' => 'http://www.example.org',
			'last_base_url' => base64_encode('http://example.org'),
			'is_base_url_different' => true,
			'base_url_exist' => true,
		],
		'expected' => [
			'url' => 'http://www.example.org',
			'old_url' =>'http://example.org',
			'encrypted_old_url' => base64_encode('http://www.example.org')
		]
	]
];
