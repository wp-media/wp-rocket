<?php

return [
	// Default settings.
	'settings'  => [
		'minify_css'  => false,
		'exclude_css' => [],
		'cdn'         => false,
		'cdn_cnames'  => [],
	],
	'test_data' => [
		'shouldNotRegenerateKey'             => [
			'value'     => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
            'should_run' => false,
            'expected' => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
        ],
        'shouldNotRegenerateKeyNewCname'      => [
			'value'     => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => false,
				'cdn_cnames'  => [ 'cname' ],
			],
            'should_run' => false,
            'expected' => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => false,
				'cdn_cnames'  => [ 'cname' ],
			],
		],
		'shouldRegenerateKey'             => [
			'value'     => [
				'minify_css'  => true,
				'exclude_css' => [],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
            'should_run' => true,
            'expected' => [
				'minify_css'  => true,
				'exclude_css' => [],
				'cdn'         => false,
                'cdn_cnames'  => [],
                'minify_css_key' => 'minify_css_key',
			],
		],
		'shouldRegenerateKeyExcludeCSS'      => [
			'value'     => [
				'minify_css'  => true,
				'exclude_css' => [ '/wp-content/plugins/some-plugin/file.css' ],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
            'should_run' => true,
            'expected' => [
				'minify_css'  => true,
				'exclude_css' => [ '/wp-content/plugins/some-plugin/file.css' ],
				'cdn'         => false,
                'cdn_cnames'  => [],
                'minify_css_key' => 'minify_css_key',
			],
		],
		'shouldRegenerateKeyCDN'             => [
			'value'     => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => true,
				'cdn_cnames'  => [],
			],
            'should_run' => true,
            'expected' => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => true,
                'cdn_cnames'  => [],
                'minify_css_key' => 'minify_css_key',
			],
		],
		'shouldRegenerateKeyCDNCname'        => [
			'value'     => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => true,
				'cdn_cnames'  => [ 'cname' ],
			],
            'should_run' => true,
            'expected' => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => true,
                'cdn_cnames'  => [ 'cname' ],
                'minify_css_key' => 'minify_css_key',
			],
		],
	],
];
