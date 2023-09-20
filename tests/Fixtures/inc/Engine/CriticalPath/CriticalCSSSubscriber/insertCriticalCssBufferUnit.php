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
		'testShouldBailOutDONOTROCKETOPTIMIZE'      => [
			'config'   => [
				'DONOTROCKETOPTIMIZE' => true,
			],
			'expected' => false,
		],
		'testShouldBailOutAsyncCSSOpt'              => [
			'config'   => [
				'DONOTROCKETOPTIMIZE' => false,
				'options'             => [
					'async_css' => [
						'value'   => false,
						'default' => 0,
					],
				],
			],
			'expected' => false,
		],
		'testShouldBailOutRocketExcludedOption'     => [
			'config'   => [
				'DONOTROCKETOPTIMIZE'            => false,
				'options'                        => [
					'async_css' => [
						'value'   => true,
						'default' => 0,
					],
				],
				'is_rocket_post_excluded_option' => true,
			],
			'expected' => false,
		],
		'testShouldBailOutNoCurrentPageCriticalCSS' => [
			'config'   => [
				'DONOTROCKETOPTIMIZE'            => false,
				'options'                        => [
					'async_css' => [
						'value'   => true,
						'default' => 0,
					],
				],
				'is_rocket_post_excluded_option' => false,
				'get_critical_css_content'       => '',
			],
			'expected' => false,
		],
		'testShouldBailOutEmptyFallBackCriticalCSS' => [
			'config'   => [
				'DONOTROCKETOPTIMIZE'            => false,
				'options'                        => [
					'async_css'    => [
						'value'   => true,
						'default' => 0,
					],
					'critical_css' => [
						'value'   => '',
						'default' => '',
					],
				],
				'is_rocket_post_excluded_option' => false,
				'get_critical_css_content'       => '',
			],
			'expected' => false,
		],
		'testShouldDisplatFallBackCriticalCSS'      => [
			'config'   => [
				'DONOTROCKETOPTIMIZE'            => false,
				'options'                        => [
					'async_css'    => [
						'value'   => true,
						'default' => 0,
					],
					'critical_css' => [
						'value'   => '.fallback { color: red; }',
						'default' => '',
					],
				],
				'is_rocket_post_excluded_option' => false,
				'get_critical_css_content'       => '.fallback { color: red; }',
				'SCRIPT_DEBUG'                   => false,
			],
			'expected' => true,
			'html'     => '<html><head><title></title><style id="rocket-critical-css">.fallback { color: red; }</style></head><body>' . $script_min . '</body></html>',
		],

		'testShouldDisplayFileCriticalCSS' => [
			'config'   => [
				'DONOTROCKETOPTIMIZE'            => false,
				'options'                        => [
					'async_css'    => [
						'value'   => true,
						'default' => 0,
					],
					'critical_css' => [
						'value'   => '.fallback { color: red; }',
						'default' => '',
					],
				],
				'is_rocket_post_excluded_option' => false,
				'get_critical_css_content'       => '.post_tag { color: red; }',
				'SCRIPT_DEBUG'                   => false,
			],
			'expected' => true,
			'html'     => '<html><head><title></title><style id="rocket-critical-css">.post_tag { color: red; }</style></head><body>' . $script_min . '</body></html>',
		],

		'testShouldDisplayCustomFileCriticalCSS' => [
			'config'   => [
				'DONOTROCKETOPTIMIZE'            => false,
				'options'                        => [
					'async_css'    => [
						'value'   => true,
						'default' => 0,
					],
					'critical_css' => [
						'value'   => '.fallback { color: red; }',
						'default' => '',
					],
				],
				'is_rocket_post_excluded_option' => false,
				'get_critical_css_content'       => '.page { color: red; }',
				'SCRIPT_DEBUG'                   => true,
			],
			'expected' => true,
			'html'     => <<<HTML
<html><head><title></title><style id="rocket-critical-css">.page { color: red; }</style></head><body><script>function wprRemoveCPCSS() {
	let preload_stylesheets = document.querySelectorAll( 'link[data-rocket-async="style"][rel="preload"]' );
	if ( preload_stylesheets && preload_stylesheets.length > 0 ) {
		for ( let stylesheet_index = 0;stylesheet_index < preload_stylesheets.length;stylesheet_index++ ){
			let media = preload_stylesheets[stylesheet_index].getAttribute('media') || 'all';
			if( window.matchMedia(media).matches ){
				setTimeout( wprRemoveCPCSS, 200 );
				return;
			}
		}
	}

	const elem = document.getElementById( 'rocket-critical-css' );
	if ( elem && 'remove' in elem ) {
		elem.remove();
	}
}

if ( window.addEventListener ) {
	window.addEventListener( 'load', wprRemoveCPCSS );
} else if ( window.attachEvent ) {
	window.attachEvent( 'onload', wprRemoveCPCSS );
}
</script></body></html>
HTML
			,
		],
	],
];
