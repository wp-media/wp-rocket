<?php
return [
	'settings' => [
		'minify_css'  => false,
		'exclude_css' => [],
		'cdn'         => false,
		'cdn_cnames'  => [],
	],
	'test_data' => [
		'shouldNotCleanMinify'             => [
			'value'     => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
			'shouldRun' => false,
		],
		'shouldCleanMinifyCSS'             => [
			'value'     => [
				'minify_css'  => true,
				'exclude_css' => [],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
			'shouldRun' => true,
		],
		'shouldCleanMinifyExcludeCSS'      => [
			'value'     => [
				'minify_css'  => true,
				'exclude_css' => [ '.css_to_exclude' ],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
			'shouldRun' => true,
		],
		'shouldCleanMinifyCDN'             => [
			'value'     => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => true,
				'cdn_cnames'  => [],
			],
			'shouldRun' => true,
		],
		'shouldCleanMinifyCDNCname'        => [
			'value'     => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => false,
				'cdn_cnames'  => [ 'cname' ],
			],
			'shouldRun' => true,
		],
	],
];
