<?php

return [
	// Default settings.
	'settings' => [
		'minify_js'  => false,
		'exclude_js' => [],
		'cdn'         => false,
		'cdn_cnames'  => [],
	],

	'test_data' => [
		'shouldNotRegenerateKey'         => [
			'settings'   => [
				'minify_js'  => false,
				'exclude_js' => [],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
			'expected'   => [
				'minify_js'  => false,
				'exclude_js' => [],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
			'should_run' => false,
		],
		'shouldNotRegenerateKeyNewCname' => [
			'settings'   => [
				'minify_js'  => false,
				'exclude_js' => [],
				'cdn'         => false,
				'cdn_cnames'  => [ 'cname' ],
			],
			'expected'   => [
				'minify_js'  => false,
				'exclude_js' => [],
				'cdn'         => false,
				'cdn_cnames'  => [ 'cname' ],
			],
			'should_run' => false,
		],
		'shouldRegenerateKey'            => [
			'settings'   => [
				'minify_js'  => true,
				'exclude_js' => [],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
			'expected'   => [
				'minify_js'     => true,
				'exclude_js'    => [],
				'cdn'            => false,
				'cdn_cnames'     => [],
				'minify_js_key' => 'minify_js_key',
			],
			'should_run' => true,
		],
		'shouldRegenerateKeyExcludeJS'  => [
			'settings'   => [
				'minify_js'  => true,
				'exclude_js' => [ '/wp-content/plugins/some-plugin/file.js' ],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
			'expected'   => [
				'minify_js'     => true,
				'exclude_js'    => [ '/wp-content/plugins/some-plugin/file.js' ],
				'cdn'            => false,
				'cdn_cnames'     => [],
				'minify_js_key' => 'minify_js_key',
			],
			'should_run' => true,
		],
		'shouldRegenerateKeyCDN'         => [
			'settings'   => [
				'minify_js'  => false,
				'exclude_js' => [],
				'cdn'         => true,
				'cdn_cnames'  => [],
			],
			'expected'   => [
				'minify_js'     => false,
				'exclude_js'    => [],
				'cdn'            => true,
				'cdn_cnames'     => [],
				'minify_js_key' => 'minify_js_key',
			],
			'should_run' => true,
		],
		'shouldRegenerateKeyCDNCname'    => [
			'settings'   => [
				'minify_js'  => false,
				'exclude_js' => [],
				'cdn'         => true,
				'cdn_cnames'  => [ 'cname' ],
			],
			'expected'   => [
				'minify_js'     => false,
				'exclude_js'    => [],
				'cdn'            => true,
				'cdn_cnames'     => [ 'cname' ],
				'minify_js_key' => 'minify_js_key',
			],
			'should_run' => true,
		],
	],
];
