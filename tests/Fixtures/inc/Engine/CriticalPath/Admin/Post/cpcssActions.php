<?php

return [
	'vfs_dir'   => 'wp-content/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'critical-css' => [
					'1' => [
						'posts' => [
							'post-2.css' => '.p { color: red; }',
						],
					],
				],
			],
		],
	],

	'test_data' => [

		'testShouldDisplayGenerateTemplateOptionDisabled' => [
			'config'   => [
				'options'            => [
					'async_css' => 0,
				],
				'post'               => (object) [
					'ID'          => 1,
					'post_status' => 'draft',
					'post_type'   => 'post',
				],
				'is_option_excluded' => true,
			],
			'expected' => [
				// For Unit Test: the data the "generate" method should receive.
				'data' => [
					'disabled'     => true,
					'beacon'       => 'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket',
					'cpcss_exists' => false,
				],

				'html' => <<<HTML
<p class="cpcss_generate ">
	Generate specific Critical Path CSS for this post.<a href="'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket'" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">More info</a>
</p>
<p class="cpcss_regenerate hidden">
	This post uses specific Critical Path CSS.<a href="'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket'" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">More info</a>
</p>
<div class="components-panel__row cpcss_generate cpcss_regenerate">
	<button id="rocket-generate-post-cpss" class="button components-button is-secondary" disabled="disabled">
		<span style="display: none;" class="spinner"></span>
		<span class="rocket-generate-post-cpss-btn-txt">Generate Specific CPCSS</span>
	</button>
</div>
<div class="components-panel__row cpcss_regenerate hidden">
	<button id="rocket-delete-post-cpss" class="button components-button is-secondary" disabled="disabled"><span>Revert back to the default CPCSS</span></button>
</div>
HTML
				,
			],
		],

		'testShouldDisplayRegenerateTemplateOptionDisabled' => [
			'config'   => [
				'options'            => [
					'async_css' => 0,
				],
				'post'               => (object) [
					'ID'          => 2,
					'post_status' => 'draft',
					'post_type'   => 'post',
				],
				'is_option_excluded' => true,
			],
			'expected' => [
				// For Unit Test: the data the "generate" method should receive.
				'data' => [
					'disabled'     => true,
					'beacon'       => 'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket',
					'cpcss_exists' => true,
				],

				'html' => <<<HTML
<p class="cpcss_generate hidden">
	Generate specific Critical Path CSS for this post.<a href="'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket'" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">More info</a>
</p>
<p class="cpcss_regenerate ">
	This post uses specific Critical Path CSS.<a href="'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket'" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">More info</a>
</p>
<div class="components-panel__row cpcss_generate cpcss_regenerate">
	<button id="rocket-generate-post-cpss" class="button components-button is-secondary" disabled="disabled">
		<span style="display: none;" class="spinner"></span>
		<span class="rocket-generate-post-cpss-btn-txt">Regenerate specific CPCSS</span>
	</button>
</div>
<div class="components-panel__row cpcss_regenerate ">
	<button id="rocket-delete-post-cpss" class="button components-button is-secondary" disabled="disabled"><span>Revert back to the default CPCSS</span></button>
</div>
HTML
				,
			],
		],

		'testShouldDisplayGenerateTemplatePostNotPublished' => [
			'config' => [
				'options'            => [
					'async_css' => 1,
				],
				'post'               => (object) [
					'ID'          => 1,
					'post_status' => 'draft',
					'post_type'   => 'post',
				],
				'is_option_excluded' => true,
			],

			'expected' => [
				// For Unit Test: the data the "generate" method should receive.
				'data' => [
					'disabled'     => true,
					'beacon'       => 'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket',
					'cpcss_exists' => false,
				],

				// For the integration test.
				'html' => <<<HTML
<p class="cpcss_generate ">
	Generate specific Critical Path CSS for this post.<a href="'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket'" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">More info</a>
</p>
<p class="cpcss_regenerate hidden">
	This post uses specific Critical Path CSS.<a href="'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket'" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">More info</a>
</p>
<div class="components-panel__row cpcss_generate cpcss_regenerate">
	<button id="rocket-generate-post-cpss" class="button components-button is-secondary" disabled="disabled">
		<span style="display: none;" class="spinner"></span>
		<span class="rocket-generate-post-cpss-btn-txt">Generate Specific CPCSS</span>
	</button>
</div>
<div class="components-panel__row cpcss_regenerate hidden">
	<button id="rocket-delete-post-cpss" class="button components-button is-secondary" disabled="disabled"><span>Revert back to the default CPCSS</span></button>
</div>
HTML
				,
			],
		],

		'testShouldDisplayReenerateTemplatePostNotPublished' => [
			'config' => [
				'options'            => [
					'async_css' => 1,
				],
				'post'               => (object) [
					'ID'          => 2,
					'post_status' => 'draft',
					'post_type'   => 'post',
				],
				'is_option_excluded' => true,
			],

			'expected' => [
				// For Unit Test: the data the "generate" method should receive.
				'data' => [
					'disabled'     => true,
					'beacon'       => 'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket',
					'cpcss_exists' => true,
				],

				// For the integration test.
				'html' => <<<HTML
<p class="cpcss_generate hidden">
	Generate specific Critical Path CSS for this post.<a href="'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket'" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">More info</a>
</p>
<p class="cpcss_regenerate ">
	This post uses specific Critical Path CSS.<a href="'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket'" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">More info</a>
</p>
<div class="components-panel__row cpcss_generate cpcss_regenerate">
	<button id="rocket-generate-post-cpss" class="button components-button is-secondary" disabled="disabled">
		<span style="display: none;" class="spinner"></span>
		<span class="rocket-generate-post-cpss-btn-txt">Regenerate specific CPCSS</span>
	</button>
</div>
<div class="components-panel__row cpcss_regenerate ">
	<button id="rocket-delete-post-cpss" class="button components-button is-secondary" disabled="disabled"><span>Revert back to the default CPCSS</span></button>
</div>
HTML
				,
			],
		],

		'testShouldDisplayGenerateTemplateOptionExcludedFromPost' => [
			'config' => [
				'options'            => [
					'async_css' => 1,
				],
				'post'               => (object) [
					'ID'          => 1,
					'post_status' => 'publish',
					'post_type'   => 'post',
				],
				'is_option_excluded' => true,
			],

			'expected' => [
				// For Unit Test: the data the "generate" method should receive.
				'data' => [
					'disabled'     => true,
					'beacon'       => 'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket',
					'cpcss_exists' => false,
				],

				// For the integration test.
				'html' => <<<HTML
<p class="cpcss_generate ">
	Generate specific Critical Path CSS for this post.<a href="'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket'" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">More info</a>
</p>
<p class="cpcss_regenerate hidden">
	This post uses specific Critical Path CSS.<a href="'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket'" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">More info</a>
</p>
<div class="components-panel__row cpcss_generate cpcss_regenerate">
	<button id="rocket-generate-post-cpss" class="button components-button is-secondary" disabled="disabled">
		<span style="display: none;" class="spinner"></span>
		<span class="rocket-generate-post-cpss-btn-txt">Generate Specific CPCSS</span>
	</button>
</div>
<div class="components-panel__row cpcss_regenerate hidden">
	<button id="rocket-delete-post-cpss" class="button components-button is-secondary" disabled="disabled"><span>Revert back to the default CPCSS</span></button>
</div>
HTML
				,
			],
		],

		'testShouldDisplayRegenerateTemplateOptionExcludedFromPost' => [
			'config' => [
				'options'            => [
					'async_css' => 1,
				],
				'post'               => (object) [
					'ID'          => 2,
					'post_status' => 'publish',
					'post_type'   => 'post',
				],
				'is_option_excluded' => true,
			],

			'expected' => [
				// For Unit Test: the data the "generate" method should receive.
				'data' => [
					'disabled'     => true,
					'beacon'       => 'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket',
					'cpcss_exists' => true,
				],

				// For the integration test.
				'html' => <<<HTML
<p class="cpcss_generate hidden">
	Generate specific Critical Path CSS for this post.<a href="'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket'" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">More info</a>
</p>
<p class="cpcss_regenerate ">
	This post uses specific Critical Path CSS.<a href="'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket'" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">More info</a>
</p>
<div class="components-panel__row cpcss_generate cpcss_regenerate">
	<button id="rocket-generate-post-cpss" class="button components-button is-secondary" disabled="disabled">
		<span style="display: none;" class="spinner"></span>
		<span class="rocket-generate-post-cpss-btn-txt">Regenerate specific CPCSS</span>
	</button>
</div>
<div class="components-panel__row cpcss_regenerate ">
	<button id="rocket-delete-post-cpss" class="button components-button is-secondary" disabled="disabled"><span>Revert back to the default CPCSS</span></button>
</div>
HTML
				,
			],
		],

		'testShouldDisplayGenerateTemplate' => [
			'config' => [
				'options'            => [
					'async_css' => 1,
				],
				'post'               => (object) [
					'ID'          => 1,
					'post_status' => 'publish',
					'post_type'   => 'post',
				],
				'is_option_excluded' => false,
			],

			'expected' => [
				// For Unit Test: the data the "generate" method should receive.
				'data' => [
					'disabled'     => false,
					'beacon'       => 'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket',
					'cpcss_exists' => false,
				],

				// For the integration test.
				'html' => <<<HTML
<p class="cpcss_generate ">
	Generate specific Critical Path CSS for this post.<a href="'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket'" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">More info</a>
</p>
<p class="cpcss_regenerate hidden">
	This post uses specific Critical Path CSS.<a href="'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket'" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">More info</a>
</p>
<div class="components-panel__row cpcss_generate cpcss_regenerate">
	<button id="rocket-generate-post-cpss" class="button components-button is-secondary" >
		<span style="display: none;" class="spinner"></span>
		<span class="rocket-generate-post-cpss-btn-txt">Generate Specific CPCSS</span>
	</button>
</div>
<div class="components-panel__row cpcss_regenerate hidden">
	<button id="rocket-delete-post-cpss" class="button components-button is-secondary" ><span>Revert back to the default CPCSS</span></button>
</div>
HTML
				,
			],
		],

		'testShouldDisplayRegenerateTemplate' => [
			'config' => [
				'options'            => [
					'async_css' => 1,
				],
				'post'               => (object) [
					'ID'          => 2,
					'post_status' => 'publish',
					'post_type'   => 'post',
				],
				'is_option_excluded' => false,
			],

			'expected' => [
				'dump' => true,
				// For Unit Test: the data the "generate" method should receive.
				'data' => [
					'disabled'     => false,
					'beacon'       => 'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket',
					'cpcss_exists' => true,
				],

				// For the integration test.
				'html' => <<<HTML
<p class="cpcss_generate hidden">
	Generate specific Critical Path CSS for this post.<a href="'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket'" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">More info</a>
</p>
<p class="cpcss_regenerate ">
	This post uses specific Critical Path CSS.<a href="'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket'" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">More info</a>
</p>
<div class="components-panel__row cpcss_generate cpcss_regenerate">
	<button id="rocket-generate-post-cpss" class="button components-button is-secondary" >
		<span style="display: none;" class="spinner"></span>
		<span class="rocket-generate-post-cpss-btn-txt">Regenerate specific CPCSS</span>
	</button>
</div>
<div class="components-panel__row cpcss_regenerate ">
	<button id="rocket-delete-post-cpss" class="button components-button is-secondary" ><span>Revert back to the default CPCSS</span></button>
</div>
HTML
				,
			],
		],
	],
];
