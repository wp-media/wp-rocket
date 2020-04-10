<?php
return [
	'shouldNotCleanMinifyWhenNotIsset' => [
		'old_value' => null,
		'value'     => null,
		'shouldRun' => false,
	],
	'shouldNotCleanMinify'             => [
		'old_value' => [
			'minify_css'  => false,
			'exclude_css' => [],
			'cdn'         => false,
			'cdn_cnames'  => [],
		],
		'value'     => [
			'minify_css'  => false,
			'exclude_css' => [],
			'cdn'         => false,
			'cdn_cnames'  => [],
		],
		'shouldRun' => false,
	],
	'shouldCleanMinifyCSS'             => [
		'old_value' => [
			'minify_css'  => false,
			'exclude_css' => [],
			'cdn'         => false,
			'cdn_cnames'  => [],
		],
		'value'     => [
			'minify_css'  => true,
			'exclude_css' => [],
			'cdn'         => false,
			'cdn_cnames'  => [],
		],
		'shouldRun' => true,
	],
	'shouldCleanMinifyExcludeCSS'      => [
		'old_value' => [
			'minify_css'  => false,
			'exclude_css' => [],
			'cdn'         => false,
			'cdn_cnames'  => [],
		],
		'value'     => [
			'minify_css'  => true,
			'exclude_css' => [ '.css_to_exclude' ],
			'cdn'         => false,
			'cdn_cnames'  => [],
		],
		'shouldRun' => true,
	],
	'shouldCleanMinifyCDN'             => [
		'old_value' => [
			'minify_css'  => false,
			'exclude_css' => [],
			'cdn'         => true,
			'cdn_cnames'  => [],
		],
		'value'     => [
			'minify_css'  => true,
			'exclude_css' => [],
			'cdn'         => false,
			'cdn_cnames'  => [],
		],
		'shouldRun' => true,
	],
	'shouldCleanMinifyCDNCname'        => [
		'old_value' => [
			'minify_css'  => false,
			'exclude_css' => [],
			'cdn'         => true,
			'cdn_cnames'  => [],
		],
		'value'     => [
			'minify_css'  => true,
			'exclude_css' => [],
			'cdn'         => false,
			'cdn_cnames'  => [ 'cname' ],
		],
		'shouldRun' => true,
	],
];
