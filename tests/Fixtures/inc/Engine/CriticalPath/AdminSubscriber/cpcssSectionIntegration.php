<?php

return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache'   => [
				'critical-css' => [
					'1' => [
						'posts' => [
							'post-2.css' => '.p { color: red; }',
						],
					],
				],
			],
			'plugins' => [
				'wp-rocket' => [
					'views' => [
						'metabox' => [
							'cpcss' => [
								'container.php'  => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'views/metabox/cpcss/container.php' ),
								'generate.php'   => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'views/metabox/cpcss/generate.php' ),
								'regenerate.php' => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'views/metabox/cpcss/regenerate.php' ),
							],
						],
					],
				],
			],
		],
	],

	// Test Data.
	'test_data' => [
		'testShouldDisplayGenerateTemplateDisabledWarningWhenOptionDIsabled' => [
			'config'   => [
				'options'            => [
					'async_css' => 0,
				],
				'post'               => [
					'post_status' => 'draft',
					'post_type'   => 'post',
					'ID'          => 1,
				],
				'is_option_excluded' => true,
			],
			'expected' => '<div class="inside">
			<h3>Critical Path CSS</h3>
			<div id="rocket-metabox-cpcss-notice"></div>
			<div id="rocket-metabox-cpcss-content">
			<p>
			Generate specific Critical Path CSS for this post. <a href="" target="_blank" rel="noopener noreferrer">More info</a></p>
			<div class="components-panel__row">
			<button id="rocket-generate-post-cpss" class="button components-button is-secondary"  disabled=\'disabled\'>
			Generate Specific CPCSS	</button>
			</div>
			</div>
			</div>
			<div class="components-notice is-notice is-warning">
			<div class="components-notice__content">
			<p>Enable Optimize CSS delivery in WP Rocket settings to use this feature</p>
			</div>
			</div>',
		],
		'testShouldDisplayRegenerateTemplateOptionDisabledWarning' => [
			'config'   => [
				'options'            => [
					'async_css' => 0,
				],
				'post'               => [
					'post_status' => 'draft',
					'post_type'   => 'post',
					'ID'          => 2,
				],
				'is_option_excluded' => true,
			],
			'expected' => '<div class="inside">
			<h3>
			Critical Path CSS</h3>
			<div id="rocket-metabox-cpcss-notice">
			</div>
			<div id="rocket-metabox-cpcss-content">
			<p>
			This post uses specific Critical Path CSS.<a href="" target="_blank" rel="noopener noreferrer">
			More info</a>
			</p>
			<div class="components-panel__row">
			<button id="rocket-generate-post-cpss" class="components-button is-link"  disabled=\'disabled\'>
			Regenerate specific CPCSS</button>
			</div>
			<div class="components-panel__row">
			<button id="rocket-delete-post-cpss" class="components-button is-link is-destructive"  disabled=\'disabled\'>
			Revert back to the default CPCSS</button>
			</div>
			</div>
			</div>
			<div class="components-notice is-notice is-warning">
			<div class="components-notice__content">
			<p>
			Enable Optimize CSS delivery in WP Rocket settings to use this feature</p>
			</div>
			</div>',
		],
		'testShouldDisplayGenerateTemplatePostNotPublishedWarning' => [
			'config'   => [
				'options'            => [
					'async_css' => 1,
				],
				'post'               => [
					'post_status' => 'draft',
					'post_type'   => 'post',
					'ID'          => 1,
				],
				'is_option_excluded' => false,
			],
			'expected' => '<div class="inside">
			<h3>Critical Path CSS</h3>
			<div id="rocket-metabox-cpcss-notice"></div>
			<div id="rocket-metabox-cpcss-content">
			<p>
			Generate specific Critical Path CSS for this post. <a href="" target="_blank" rel="noopener noreferrer">More info</a></p>
			<div class="components-panel__row">
			<button id="rocket-generate-post-cpss" class="button components-button is-secondary"  disabled=\'disabled\'>
			Generate Specific CPCSS	</button>
			</div>
			</div>
			</div>
			<div class="components-notice is-notice is-warning">
			<div class="components-notice__content">
			<p>Publish the post to use this feature</p>
			</div>
			</div>',
		],
		'testShouldDisplayRegenerateTemplatePostNotPublishedWarning' => [
			'config'   => [
				'options'            => [
					'async_css' => 1,
				],
				'post'               => [
					'post_status' => 'draft',
					'post_type'   => 'post',
					'ID'          => 2,
				],
				'is_option_excluded' => false,
			],
			'expected' => '<div class="inside">
			<h3>Critical Path CSS</h3>
			<div id="rocket-metabox-cpcss-notice"></div>
			<div id="rocket-metabox-cpcss-content">
			<p>
			This post uses specific Critical Path CSS. <a href="" target="_blank" rel="noopener noreferrer">More info</a></p>
			<div class="components-panel__row">
			<button id="rocket-generate-post-cpss" class="components-button is-link"  disabled=\'disabled\'>
			Regenerate specific CPCSS	</button>
			</div>
			<div class="components-panel__row">
			<button id="rocket-delete-post-cpss" class="components-button is-link is-destructive"  disabled=\'disabled\'>
			Revert back to the default CPCSS	</button>
			</div>
			</div>
			</div>
			<div class="components-notice is-notice is-warning">
			<div class="components-notice__content">
			<p>Publish the post to use this feature</p>
			</div>
			</div>',
		],
		'testShouldDisplayGenerateTemplatePostNotPublishedAndOptionExcludedWarning' => [
			'config'   => [
				'options'            => [
					'async_css' => 1,
				],
				'post'               => [
					'post_status' => 'draft',
					'post_type'   => 'post',
					'ID'          => 1,
				],
				'is_option_excluded' => true,
			],
			'expected' => '<div class="inside">
			<h3>Critical Path CSS</h3>
			<div id="rocket-metabox-cpcss-notice"></div>
			<div id="rocket-metabox-cpcss-content">
			<p>
			Generate specific Critical Path CSS for this post. <a href="" target="_blank" rel="noopener noreferrer">More info</a></p>
			<div class="components-panel__row">
			<button id="rocket-generate-post-cpss" class="button components-button is-secondary"  disabled=\'disabled\'>
			Generate Specific CPCSS	</button>
			</div>
			</div>
			</div>
			<div class="components-notice is-notice is-warning">
			<div class="components-notice__content">
			<p>Publish the post and enable Optimize CSS delivery in the options above to use this feature</p>
			</div>
			</div>',
		],
		'testShouldDisplayRegenerateTemplatePostNotPublishedAndOptionExcludedWarning' => [
			'config'   => [
				'options'            => [
					'async_css' => 1,
				],
				'post'               => [
					'post_status' => 'draft',
					'post_type'   => 'post',
					'ID'          => 2,
				],
				'is_option_excluded' => true,
			],
			'expected' => '<div class="inside">
			<h3>Critical Path CSS</h3>
			<div id="rocket-metabox-cpcss-notice"></div>
			<div id="rocket-metabox-cpcss-content">
			<p>
			This post uses specific Critical Path CSS. <a href="" target="_blank" rel="noopener noreferrer">More info</a></p>
			<div class="components-panel__row">
			<button id="rocket-generate-post-cpss" class="components-button is-link"  disabled=\'disabled\'>
			Regenerate specific CPCSS	</button>
			</div>
			<div class="components-panel__row">
			<button id="rocket-delete-post-cpss" class="components-button is-link is-destructive"  disabled=\'disabled\'>
			Revert back to the default CPCSS	</button>
			</div>
			</div>
			</div>
			<div class="components-notice is-notice is-warning">
			<div class="components-notice__content">
			<p>Publish the post and enable Optimize CSS delivery in the options above to use this feature</p>
			</div>
			</div>',
		],
		'testShouldDisplayGenerateTemplateOptionExcludedFromPostWarning' => [
			'config'   => [
				'options'            => [
					'async_css' => 1,
				],
				'post'               => [
					'post_status' => 'publish',
					'post_type'   => 'post',
					'ID'          => 1,
				],
				'is_option_excluded' => true,
			],
			'expected' => '<div class="inside">
			<h3>Critical Path CSS</h3>
			<div id="rocket-metabox-cpcss-notice"></div>
			<div id="rocket-metabox-cpcss-content">
			<p>
			Generate specific Critical Path CSS for this post. <a href="" target="_blank" rel="noopener noreferrer">More info</a></p>
			<div class="components-panel__row">
			<button id="rocket-generate-post-cpss" class="button components-button is-secondary"  disabled=\'disabled\'>
			Generate Specific CPCSS	</button>
			</div>
			</div>
			</div>
			<div class="components-notice is-notice is-warning">
			<div class="components-notice__content">
			<p>Enable Optimize CSS delivery in the options above to use this feature</p>
			</div>
			</div>',
		],
		'testShouldDisplayRegenerateTemplateOptionExcludedFromPostWarning' => [
			'config'   => [
				'options'            => [
					'async_css' => 1,
				],
				'post'               => [
					'post_status' => 'publish',
					'post_type'   => 'post',
					'ID'          => 2,
				],
				'is_option_excluded' => true,
			],
			'expected' => '<div class="inside">
			<h3>Critical Path CSS</h3>
			<div id="rocket-metabox-cpcss-notice"></div>
			<div id="rocket-metabox-cpcss-content">
			<p>
			This post uses specific Critical Path CSS. <a href="" target="_blank" rel="noopener noreferrer">More info</a></p>
			<div class="components-panel__row">
			<button id="rocket-generate-post-cpss" class="components-button is-link"  disabled=\'disabled\'>
			Regenerate specific CPCSS	</button>
			</div>
			<div class="components-panel__row">
			<button id="rocket-delete-post-cpss" class="components-button is-link is-destructive"  disabled=\'disabled\'>
			Revert back to the default CPCSS	</button>
			</div>
			</div>
			</div>
			<div class="components-notice is-notice is-warning">
			<div class="components-notice__content">
			<p>Enable Optimize CSS delivery in the options above to use this feature</p>
			</div>
			</div>',
		],
		'testShouldDisplayGenerateTemplateNoWarning'   => [
			'config'   => [
				'options'            => [
					'async_css' => 1,
				],
				'post'               => [
					'post_status' => 'publish',
					'post_type'   => 'post',
					'ID'          => 1,
				],
				'is_option_excluded' => false,
			],
			'expected' => '<div class="inside">
			<h3>Critical Path CSS</h3>
			<div id="rocket-metabox-cpcss-notice"></div>
			<div id="rocket-metabox-cpcss-content">
			<p>
			Generate specific Critical Path CSS for this post. <a href="" target="_blank" rel="noopener noreferrer">More info</a></p>
			<div class="components-panel__row">
			<button id="rocket-generate-post-cpss" class="button components-button is-secondary" >
			Generate Specific CPCSS	</button>
			</div>
			</div>
			</div>',
		],
		'testShouldDisplayRegenerateTemplateNoWarning' => [
			'config'   => [
				'options'            => [
					'async_css' => 1,
				],
				'post'               => [
					'post_status' => 'publish',
					'post_type'   => 'post',
					'ID'          => 2,
				],
				'is_option_excluded' => false,
			],
			'expected' => '<div class="inside">
			<h3>Critical Path CSS</h3>
			<div id="rocket-metabox-cpcss-notice"></div>
			<div id="rocket-metabox-cpcss-content">
			<p>
			This post uses specific Critical Path CSS. <a href="" target="_blank" rel="noopener noreferrer">More info</a></p>
			<div class="components-panel__row">
			<button id="rocket-generate-post-cpss" class="components-button is-link" >
			Regenerate specific CPCSS	</button>
			</div>
			<div class="components-panel__row">
			<button id="rocket-delete-post-cpss" class="components-button is-link is-destructive" >
			Revert back to the default CPCSS	</button>
			</div>
			</div>
			</div>',
		],
	],
];
