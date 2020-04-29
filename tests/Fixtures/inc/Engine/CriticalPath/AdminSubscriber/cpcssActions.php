<?php

return [
	'vfs_dir'   => 'public/',

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
								'generate.php'   => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'views/metabox/cpcss/generate.php' ),
								'regenerate.php' => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'views/metabox/cpcss/regenerate.php' ),
							],
						],
					],
				],
			],
		],
	],
	// Test Data
	'test_data' => [
		'testShouldDisplayGenerateTemplateOptionDisabled' => [
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
			'expected' => '<p>Generate specific Critical Path CSS for this post. <a href="" target="_blank" rel="noopener noreferrer">More info</a></p>
			<div class="components-panel__row">
				<button id="rocket-generate-post-cpss" class="button components-button is-secondary" disabled="disabled">
					Generate Specific CPCSS
				</button>
			</div>',
		],
		'testShouldDisplayRegenerateTemplateOptionDisabled' => [
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
			'expected' => '<p>This post uses specific Critical Path CSS. <a href="" target="_blank" rel="noopener noreferrer">More info</a></p>
			<div class="components-panel__row">
				<button id="rocket-generate-post-cpss" class="components-button is-link" disabled="disabled">
					Regenerate specific CPCSS
				</button>
			</div>
			<div class="components-panel__row">
				<button id="rocket-delete-post-cpss" class="components-button is-link is-destructive" disabled="disabled">
					Revert back to the default CPCSS
				</button>
			</div>',
		],
		'testShouldDisplayGenerateTemplatePostNotPublished' => [
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
			'expected' => '<p>Generate specific Critical Path CSS for this post. <a href="" target="_blank" rel="noopener noreferrer">More info</a></p>
			<div class="components-panel__row">
				<button id="rocket-generate-post-cpss" class="button components-button is-secondary" disabled="disabled">
					Generate Specific CPCSS
				</button>
			</div>',
		],
		'testShouldDisplayReenerateTemplatePostNotPublished' => [
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
			'expected' => '<p>This post uses specific Critical Path CSS. <a href="" target="_blank" rel="noopener noreferrer">More info</a></p>
			<div class="components-panel__row">
				<button id="rocket-generate-post-cpss" class="components-button is-link" disabled="disabled">
					Regenerate specific CPCSS
				</button>
			</div>
			<div class="components-panel__row">
				<button id="rocket-delete-post-cpss" class="components-button is-link is-destructive" disabled="disabled">
					Revert back to the default CPCSS
				</button>
			</div>',
		],
		'testShouldDisplayGenerateTemplateOptionExcludedFromPost' => [
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
			'expected' => '<p>Generate specific Critical Path CSS for this post. <a href="" target="_blank" rel="noopener noreferrer">More info</a></p>
			<div class="components-panel__row">
				<button id="rocket-generate-post-cpss" class="button components-button is-secondary" disabled="disabled">
					Generate Specific CPCSS
				</button>
			</div>',
		],
		'testShouldDisplayRegenerateTemplateOptionExcludedFromPost' => [
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
			'expected' => '<p>This post uses specific Critical Path CSS. <a href="" target="_blank" rel="noopener noreferrer">More info</a></p>
			<div class="components-panel__row">
				<button id="rocket-generate-post-cpss" class="components-button is-link" disabled="disabled">
					Regenerate specific CPCSS
				</button>
			</div>
			<div class="components-panel__row">
				<button id="rocket-delete-post-cpss" class="components-button is-link is-destructive" disabled="disabled">
					Revert back to the default CPCSS
				</button>
			</div>',
		],
		'testShouldDisplayGenerateTemplate'               => [
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
			'expected' => '<p>Generate specific Critical Path CSS for this post. <a href="" target="_blank" rel="noopener noreferrer">More info</a></p>
			<div class="components-panel__row">
				<button id="rocket-generate-post-cpss" class="button components-button is-secondary" >
					Generate Specific CPCSS
				</button>
			</div>',
		],
		'testShouldDisplayRegenerateTemplate'             => [
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
			'expected' => '<p>This post uses specific Critical Path CSS. <a href="" target="_blank" rel="noopener noreferrer">More info</a></p>
			<div class="components-panel__row">
				<button id="rocket-generate-post-cpss" class="components-button is-link" >
					Regenerate specific CPCSS
				</button>
			</div>
			<div class="components-panel__row">
				<button id="rocket-delete-post-cpss" class="components-button is-link is-destructive" >
					Revert back to the default CPCSS
				</button>
			</div>',
		],
	],
];
