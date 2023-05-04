<?php

return [
	'testEmptyFilePathShouldReturnTrue' => [
		'config' => [
			'url' => 'url',
			'file' => [
				'path' => ''
			],
			'content_url' => 'content_url',
			'url_host' => 'url_host',
			'url_parsed' => [
				'host' => 'host',
				'path' => '',
			],
			'zones' => [
				'js'
			],
			'cdn_hosts' => [
				'exmaple.com'
			],
			'lang_url' => 'en.exmaple.com',
			'lang_hosts' => [
				'en.exmaple.com'
			]
		],
		'expected' => true,
	],
	'testEmptyContentURLPathShouldReturnTrue' => [
		'config' => [
			'url' => 'url',
			'file' => [
				'path' => 'path'
			],
			'parse_content' => true,
			'content_url' => 'content_url',
			'url_host' => 'url_host',
			'url_parsed' => [
				'host' => 'host',
				'path' => '',
			],
			'zones' => [
				'js'
			],
			'cdn_hosts' => [
				'exmaple.com'
			],
			'lang_url' => 'en.exmaple.com',
			'lang_hosts' => [
				'en.exmaple.com'
			]
		],
		'expected' => true,
	],
	'testEmptyContentURLHostShouldReturnTrue' => [
		'config' => [
			'url' => 'url',
			'file' => [
				'path' => 'path',
				'host' => 'host',
			],
			'parse_content' => true,
			'collect_hosts' => true,
			'content_url' => 'content_url',
			'url_host' => 'url_host',
			'url_parsed' => [
				'host' => '',
				'path' => 'path',
			],
			'zones' => [
				'js'
			],
			'cdn_hosts' => [

			],
			'lang_url' => 'en.exmaple.com',
			'lang_hosts' => [

			]
		],
		'expected' => true,
	],
	'testNoHostFileShouldReturnTrue' => [
		'config' => [
			'url' => 'url',
			'file' => [
				'path' => 'random'
			],
			'parse_content' => true,
			'collect_hosts' => true,
			'content_url' => 'content_url',
			'url_host' => 'url_host',
			'url_parsed' => [
				'host' => 'host',
				'path' => 'path',
			],
			'zones' => [
				'js'
			],
			'cdn_hosts' => [

			],
			'lang_url' => 'en.exmaple.com',
			'lang_hosts' => [

			]
		],
		'expected' => true,
	],
	'testNoHostFileShouldReturnFalse' => [
		'config' => [
			'url' => 'url',
			'file' => [
				'path' => 'path'
			],
			'parse_content' => true,
			'collect_hosts' => true,
			'content_url' => 'content_url',
			'url_host' => 'url_host',
			'url_parsed' => [
				'host' => 'host',
				'path' => 'path',
			],
			'zones' => [
				'js'
			],
			'cdn_hosts' => [

			],
			'lang_url' => 'en.exmaple.com',
			'lang_hosts' => [

			]
		],
		'expected' => false,
	],
	'testHostFileShouldReturnFalse' => [
		'config' => [
			'url' => 'host',
			'file' => [
				'path' => 'path',
				'host' => 'host',
			],
			'parse_content' => true,
			'collect_hosts' => true,
			'content_url' => 'content_url',
			'url_host' => 'url_host',
			'url_parsed' => [
				'host' => 'host',
				'path' => 'path',
			],
			'zones' => [
				'js'
			],
			'cdn_hosts' => [

			],
			'lang_url' => 'en.exmaple.com',
			'lang_hosts' => [

			]
		],
		'expected' => false,
	],
	'testHostFileShouldReturnTrue' => [
		'config' => [
			'url' => 'url',
			'file' => [
				'path' => 'path',
				'host' => 'host',
			],
			'parse_content' => true,
			'collect_hosts' => true,
			'content_url' => 'content_url',
			'url_host' => 'url_host',
			'url_parsed' => [
				'host' => 'host',
				'path' => 'path',
			],
			'zones' => [
				'js'
			],
			'cdn_hosts' => [

			],
			'lang_url' => 'en.exmaple.com',
			'lang_hosts' => [

			]
		],
		'expected' => true,
	],
	'testHostFileEvenWithLocalAsParamShouldReturnTrue' => [
		'config' => [
			'url' => 'url?domain=host',
			'file' => [
				'path' => 'path',
				'host' => 'host',
			],
			'parse_content' => true,
			'collect_hosts' => true,
			'content_url' => 'content_url',
			'url_host' => 'url_host',
			'url_parsed' => [
				'host' => 'host',
				'path' => 'path',
			],
			'zones' => [
				'js'
			],
			'cdn_hosts' => [

			],
			'lang_url' => 'en.exmaple.com',
			'lang_hosts' => [

			]
		],
		'expected' => true,
	],
];
