<?php

$script_min = <<<JS
<script>"use strict";function wprRemoveCPCSS(){var preload_stylesheets=document.querySelectorAll('link[data-rocket-async="style"][rel="preload"]');if(preload_stylesheets&&0<preload_stylesheets.length)for(var stylesheet_index=0;stylesheet_index<preload_stylesheets.length;stylesheet_index++){var media=preload_stylesheets[stylesheet_index].getAttribute("media")||"all";if(window.matchMedia(media).matches)return void setTimeout(wprRemoveCPCSS,200)}var elem=document.getElementById("rocket-critical-css");elem&&"remove"in elem&&elem.remove()}window.addEventListener?window.addEventListener("load",wprRemoveCPCSS):window.attachEvent&&window.attachEvent("onload",wprRemoveCPCSS);</script>
JS;

return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache'   => [
				'critical-css' => [
					'1' => [
						'.'                => '',
						'..'               => '',
						'posts'            => [
							'.'           => '',
							'..'          => '',
							'post-1.css'  => '.post-1 { color: red; }',
							'post-10.css' => '.post-10 { color: red; }',
							'page-20.css' => '.page-20 { color: red; }',
						],
						'home.css'         => '.home { color: red; }',
						'front_page.css'   => '.front_page { color: red; }',
						'category.css'     => '.category { color: red; }',
						'post_tag.css'     => '.post_tag { color: red; }',
						'page.css'         => '.page { color: red; }',
						'wptests_tax1.css' => '.wptests_tax1 { color: red; }',
					],
					'2' => [
						'.'              => '',
						'..'             => '',
						'posts'          => [
							'.'           => '',
							'..'          => '',
							'post-1.css'  => '.post-1 { color: red; }',
							'post-3.css'  => '.post-3 { color: red; }',
							'page-20.css' => '.page-20 { color: red; }',
						],
						'home.css'       => '.home { color: red; }',
						'front_page.css' => '.front_page { color: red; }',
						'category.css'   => '.category { color: red; }',
						'post_tag.css'   => '.post_tag { color: red; }',
						'page.css'       => '.page { color: red; }',
					],
				],
			],
			'plugins' => [
				'wp-rocket' => [
					'assets' => [
						'js' => [
							'cpcss-removal.js'     => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'assets/js/cpcss-removal.js' ),
							'cpcss-removal.min.js' => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'assets/js/cpcss-removal.min.js' ),
						],
					],
				],
			],
		],
	],

	'test_data' => [

		'testShouldReturnDefaultCSS' => [
			'config'        => [
				'blog_id'       => 1,
				'type'          => 'front_page', // default
				'expected_type' => [
					[
						'type'   => 'is_home',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_front_page',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_category',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_tag',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_tax',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_singular',
						'return' => false,
						'param'  => '',
					],
				],
				'excluded_type' => [],
			],
			'expected_file' => 'wp-content/cache/critical-css/1/front_page.css',
			null,
			'js_script'     => $script_min,
		],

		'testShouldReturnHomeCSS' => [
			'config'        => [
				'blog_id'       => 1,
				'type'          => 'home', // is_home
				'show_on_front' => 'page', // front default
				'expected_type' => [
					[
						'type'   => 'is_home',
						'return' => true,
						'param'  => '',
					],
					[
						'type'   => 'get_option',
						'return' => 'page',
						'param'  => 'show_on_front',
					],
				],
				'excluded_type' => [ 'is_front_page', 'is_category', 'is_tag', 'is_tax', 'is_singular' ],
			],
			'expected_file' => 'wp-content/cache/critical-css/1/home.css',
			null,
			'js_script'     => $script_min,
		],

		'testShouldReturnFrontPageCSS' => [
			'config'        => [
				'blog_id'       => 1,
				'type'          => 'home', // front default
				'show_on_front' => 'front', // front default
				'expected_type' => [
					[
						'type'   => 'is_home',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_front_page',
						'return' => true,
						'param'  => '',
					],
				],
				'excluded_type' => [ 'is_category', 'is_tag', 'is_tax', 'is_singular' ],
			],
			'expected_file' => 'wp-content/cache/critical-css/1/front_page.css',
			null,
			'js_script'     => $script_min,
		],

		'testShouldReturnCategoryPageCSS' => [
			'config'        => [
				'blog_id'       => 1,
				'type'          => 'is_category', // category
				'expected_type' => [
					[
						'type'   => 'is_home',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_front_page',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_category',
						'return' => true,
						'param'  => '',
					],
				],
				'excluded_type' => [ 'is_tag', 'is_tax', 'is_singular' ],
			],
			'expected_file' => 'wp-content/cache/critical-css/1/category.css',
			null,
			'js_script'     => $script_min,
		],

		'testShouldReturnTagCSS' => [
			'config'        => [
				'blog_id'       => 1,
				'type'          => 'is_tag',
				'expected_type' => [
					[
						'type'   => 'is_home',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_front_page',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_category',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_tag',
						'return' => true,
						'param'  => '',
					],
				],
				'excluded_type' => [ 'is_tax', 'is_singular' ],
			],
			'expected_file' => 'wp-content/cache/critical-css/1/post_tag.css',
			null,
			'js_script'     => $script_min,
		],

		'testShouldReturnTaxCSS' => [
			'config'        => [
				'blog_id'       => 1,
				'type'          => 'is_tax',
				'taxonomy'      => 'wptests_tax1',
				'expected_type' => [
					[
						'type'   => 'is_home',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_front_page',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_category',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_tag',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_tax',
						'return' => true,
						'param'  => '',
					],
					[
						'type'   => 'get_queried_object',
						'return' => (object) [ 'taxonomy' => 'wptests_tax1' ],
						'param'  => '',
					],
				],
				'excluded_type' => [ 'is_singular' ],
			],
			'expected_file' => 'wp-content/cache/critical-css/1/wptests_tax1.css',
			null,
			'js_script'     => $script_min,
		],

		'testShouldReturnTaxDoesNotExistReturnFalseFallbackCSS' => [
			'config'            => [
				'blog_id'       => 1,
				'type'          => 'is_tax',
				'taxonomy'      => 'wptests_tax2',
				'expected_type' => [
					[
						'type'   => 'is_home',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_front_page',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_category',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_tag',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_tax',
						'return' => true,
						'param'  => '',
					],
					[
						'type'   => 'get_queried_object',
						'return' => (object) [ 'taxonomy' => 'wptests_tax2' ],
						'param'  => '',
					],
					[
						'type'   => 'get_rocket_option',
						'return' => '',
						'param'  => '',
					],
				],
				'excluded_type' => [ 'is_singular' ],
			],
			'expected_file'     => '',
			'expected_fallback' => false,
			'js_script'         => '',
		],

		'testShouldReturnTaxDoesNotExistReturnFallbackCSS' => [
			'config'            => [
				'blog_id'       => 1,
				'type'          => 'is_tax',
				'taxonomy'      => 'wptests_tax3',
				'fallback_css'  => '.fallback_css{ color: red; }',
				'expected_type' => [
					[
						'type'   => 'is_home',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_front_page',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_category',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_tag',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_tax',
						'return' => true,
						'param'  => '',
					],
					[
						'type'   => 'get_queried_object',
						'return' => (object) [ 'taxonomy' => 'wptests_tax3' ],
						'param'  => '',
					],
					[
						'type'   => 'get_rocket_option',
						'return' => '.fallback_css{ color: red; }',
						'param'  => '',
					],
				],
				'excluded_type' => [ 'is_singular' ],
			],
			'expected_file'     => '',
			'expected_fallback' => 'fallback',
			'js_script'         => $script_min,
		],

		'testShouldReturnSingularCSS' => [
			'config'        => [
				'blog_id'       => 1,
				'type'          => 'is_page',
				'post_id'       => 2,
				'post_data'     => [
					'import_id' => 2,
					'post_type' => 'page',
				],
				'expected_type' => [
					[
						'type'   => 'is_home',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_front_page',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_category',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_tag',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_tax',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_singular',
						'return' => true,
						'param'  => '',
					],
					[
						'type'   => 'get_post_type',
						'return' => 'page',
						'param'  => '',
					],
					[
						'type'   => 'get_the_ID',
						'return' => '2',
						'param'  => '',
					],
				],
				'excluded_type' => [],
			],
			'expected_file' => 'wp-content/cache/critical-css/1/page.css',
		],

		'testShouldReturnSingularCustomPostsCSS' => [
			'config'        => [
				'blog_id'       => 1,
				'type'          => 'is_post',
				'post_id'       => 1,
				'post_data'     => [
					'import_id' => 1,
					'post_type' => 'post',
				],
				'expected_type' => [
					[
						'type'   => 'is_home',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_front_page',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_category',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_tag',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_tax',
						'return' => false,
						'param'  => '',
					],
					[
						'type'   => 'is_singular',
						'return' => true,
						'param'  => '',
					],
					[
						'type'   => 'get_post_type',
						'return' => 'post',
						'param'  => '',
					],
					[
						'type'   => 'get_the_ID',
						'return' => '1',
						'param'  => '',
					],
				],
				'excluded_type' => [],
			],
			'expected_file' => 'wp-content/cache/critical-css/1/posts/post-1.css',
		],
	],
];
