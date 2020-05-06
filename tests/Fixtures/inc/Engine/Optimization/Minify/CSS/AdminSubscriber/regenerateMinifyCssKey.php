<?php

return [
	// Default settings.
	'settings' => [
		'minify_css'  => false,
		'exclude_css' => [],
		'cdn'         => false,
		'cdn_cnames'  => [],
	],

	'test_data' => [
		'shouldNotRegenerateKey'         => [
			'settings'   => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
			'expected'   => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
			'should_run' => false,
		],
		'shouldNotRegenerateKeyNewCname' => [
			'settings'   => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => false,
				'cdn_cnames'  => [ 'cname' ],
			],
			'expected'   => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => false,
				'cdn_cnames'  => [ 'cname' ],
			],
			'should_run' => false,
		],
		'shouldRegenerateKey'            => [
			'settings'   => [
				'minify_css'  => true,
				'exclude_css' => [],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
			'expected'   => [
				'minify_css'     => true,
				'exclude_css'    => [],
				'cdn'            => false,
				'cdn_cnames'     => [],
				'minify_css_key' => 'minify_css_key',
			],
			'should_run' => true,
		],
		'shouldRegenerateKeyExcludeCSS'  => [
			'settings'   => [
				'minify_css'  => true,
				'exclude_css' => [ '/wp-content/plugins/some-plugin/file.css' ],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
			'expected'   => [
				'minify_css'     => true,
				'exclude_css'    => [ '/wp-content/plugins/some-plugin/file.css' ],
				'cdn'            => false,
				'cdn_cnames'     => [],
				'minify_css_key' => 'minify_css_key',
			],
			'should_run' => true,
		],
		'shouldRegenerateKeyCDN'         => [
			'settings'   => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => true,
				'cdn_cnames'  => [],
			],
			'expected'   => [
				'minify_css'     => false,
				'exclude_css'    => [],
				'cdn'            => true,
				'cdn_cnames'     => [],
				'minify_css_key' => 'minify_css_key',
			],
			'should_run' => true,
		],
		'shouldRegenerateKeyCDNCname'    => [
			'settings'   => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => true,
				'cdn_cnames'  => [ 'cname' ],
			],
			'expected'   => [
				'minify_css'     => false,
				'exclude_css'    => [],
				'cdn'            => true,
				'cdn_cnames'     => [ 'cname' ],
				'minify_css_key' => 'minify_css_key',
			],
			'should_run' => true,
		],
	],
];
