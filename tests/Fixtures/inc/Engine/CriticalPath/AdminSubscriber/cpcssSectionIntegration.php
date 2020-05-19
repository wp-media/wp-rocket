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
						'cpcss' => [
							'metabox' => [
								'container.php'  => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'views/cpcss/metabox/container.php' ),
								'generate.php'   => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'views/cpcss/metabox/generate.php' ),
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
					'post_status' => 'publish',
					'post_type'   => 'post',
					'ID'          => 1,
				],
				'is_option_excluded' => false,
			],
			'expected' => '<div class="inside">
				<h3>Critical Path CSS</h3>
				<div id="rocket-metabox-cpcss-content">
					<p class="cpcss_generate ">
						Generate specific Critical Path CSS for this post.<a href="" target="_blank" rel="noopener noreferrer">More info</a>
					</p>
					<p class="cpcss_regenerate hidden">
						This post uses specific Critical Path CSS.<a href="" target="_blank" rel="noopener noreferrer">More info</a>
					</p>
					<div class="components-panel__row cpcss_generate cpcss_regenerate">
						<button id="rocket-generate-post-cpss" class="button components-button is-secondary"  disabled=\'disabled\'>
							<span style="display: none;" class="spinner"></span>
							<span class="rocket-generate-post-cpss-btn-txt">Generate Specific CPCSS</span>
						</button>
					</div>
					<div class="components-panel__row cpcss_regenerate hidden">
						<button id="rocket-delete-post-cpss" class="button components-button is-secondary"  disabled=\'disabled\'><span>Revert back to the default CPCSS</span></button>
					</div>
				</div>
			</div>
			<div id="cpcss_response_notice" class="components-notice is-notice is-warning">
				<div class="components-notice__content">
					<p>Enable Optimize CSS delivery in WP Rocket settings to use this feature.</p>
				</div>
			</div>
			<script>
	var cpcss_rest_url       = \'http://example.org/index.php?rest_route=/wp-rocket/v1/cpcss/post/1\';
	var cpcss_rest_nonce     = \'wp_rest_nonce\';
	var cpcss_generate_btn   = \'Generate Specific CPCSS\';
	var cpcss_regenerate_btn = \'Regenerate specific CPCSS\';
</script>',
		],
		'testShouldDisplayRegenerateTemplateOptionDisabledWarning' => [
			'config'   => [
				'options'            => [
					'async_css' => 0,
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
				<div id="rocket-metabox-cpcss-content">
					<p class="cpcss_generate hidden">
						Generate specific Critical Path CSS for this post.<a href="" target="_blank" rel="noopener noreferrer">More info</a>
					</p>
					<p class="cpcss_regenerate ">
						This post uses specific Critical Path CSS.<a href="" target="_blank" rel="noopener noreferrer">More info</a>
					</p>
					<div class="components-panel__row cpcss_generate cpcss_regenerate">
						<button id="rocket-generate-post-cpss" class="button components-button is-secondary"  disabled=\'disabled\'>
							<span style="display: none;" class="spinner"></span>
							<span class="rocket-generate-post-cpss-btn-txt">Regenerate specific CPCSS</span>
						</button>
					</div>
					<div class="components-panel__row cpcss_regenerate ">
						<button id="rocket-delete-post-cpss" class="button components-button is-secondary"  disabled=\'disabled\'><span>Revert back to the default CPCSS</span></button>
					</div>
				</div>
			</div>
			<div id="cpcss_response_notice" class="components-notice is-notice is-warning">
				<div class="components-notice__content">
					<p>Enable Optimize CSS delivery in WP Rocket settings to use this feature.</p>
				</div>
			</div>
			<script>
	var cpcss_rest_url       = \'http://example.org/index.php?rest_route=/wp-rocket/v1/cpcss/post/2\';
	var cpcss_rest_nonce     = \'wp_rest_nonce\';
	var cpcss_generate_btn   = \'Generate Specific CPCSS\';
	var cpcss_regenerate_btn = \'Regenerate specific CPCSS\';
</script>',
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
				<div id="rocket-metabox-cpcss-content">
					<p class="cpcss_generate ">
						Generate specific Critical Path CSS for this post.<a href="" target="_blank" rel="noopener noreferrer">More info</a>
					</p>
					<p class="cpcss_regenerate hidden">
						This post uses specific Critical Path CSS.<a href="" target="_blank" rel="noopener noreferrer">More info</a>
					</p>
					<div class="components-panel__row cpcss_generate cpcss_regenerate">
						<button id="rocket-generate-post-cpss" class="button components-button is-secondary"  disabled=\'disabled\'>
							<span style="display: none;" class="spinner"></span>
							<span class="rocket-generate-post-cpss-btn-txt">Generate Specific CPCSS</span>
						</button>
					</div>
					<div class="components-panel__row cpcss_regenerate hidden">
						<button id="rocket-delete-post-cpss" class="button components-button is-secondary"  disabled=\'disabled\'><span>Revert back to the default CPCSS</span></button>
					</div>
				</div>
			</div>
			<div id="cpcss_response_notice" class="components-notice is-notice is-warning">
				<div class="components-notice__content">
					<p>Publish the post to use this feature.</p>
				</div>
			</div>
			<script>
	var cpcss_rest_url       = \'http://example.org/index.php?rest_route=/wp-rocket/v1/cpcss/post/1\';
	var cpcss_rest_nonce     = \'wp_rest_nonce\';
	var cpcss_generate_btn   = \'Generate Specific CPCSS\';
	var cpcss_regenerate_btn = \'Regenerate specific CPCSS\';
</script>',
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
				<div id="rocket-metabox-cpcss-content">
					<p class="cpcss_generate hidden">
						Generate specific Critical Path CSS for this post.<a href="" target="_blank" rel="noopener noreferrer">More info</a>
					</p>
					<p class="cpcss_regenerate ">
						This post uses specific Critical Path CSS.<a href="" target="_blank" rel="noopener noreferrer">More info</a>
					</p>
					<div class="components-panel__row cpcss_generate cpcss_regenerate">
						<button id="rocket-generate-post-cpss" class="button components-button is-secondary"  disabled=\'disabled\'>
							<span style="display: none;" class="spinner"></span>
							<span class="rocket-generate-post-cpss-btn-txt">Regenerate specific CPCSS</span>
						</button>
					</div>
					<div class="components-panel__row cpcss_regenerate ">
						<button id="rocket-delete-post-cpss" class="button components-button is-secondary"  disabled=\'disabled\'><span>Revert back to the default CPCSS</span></button>
					</div>
				</div>
			</div>
			<div id="cpcss_response_notice" class="components-notice is-notice is-warning">
				<div class="components-notice__content">
					<p>Publish the post to use this feature.</p>
				</div>
			</div>
			<script>
	var cpcss_rest_url       = \'http://example.org/index.php?rest_route=/wp-rocket/v1/cpcss/post/2\';
	var cpcss_rest_nonce     = \'wp_rest_nonce\';
	var cpcss_generate_btn   = \'Generate Specific CPCSS\';
	var cpcss_regenerate_btn = \'Regenerate specific CPCSS\';
</script>',
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
				<div id="rocket-metabox-cpcss-content">
					<p class="cpcss_generate ">
						Generate specific Critical Path CSS for this post.<a href="" target="_blank" rel="noopener noreferrer">More info</a>
					</p>
					<p class="cpcss_regenerate hidden">
						This post uses specific Critical Path CSS.<a href="" target="_blank" rel="noopener noreferrer">More info</a>
					</p>
					<div class="components-panel__row cpcss_generate cpcss_regenerate">
						<button id="rocket-generate-post-cpss" class="button components-button is-secondary"  disabled=\'disabled\'>
							<span style="display: none;" class="spinner"></span>
							<span class="rocket-generate-post-cpss-btn-txt">Generate Specific CPCSS</span>
						</button>
					</div>
					<div class="components-panel__row cpcss_regenerate hidden">
						<button id="rocket-delete-post-cpss" class="button components-button is-secondary"  disabled=\'disabled\'><span>Revert back to the default CPCSS</span></button>
					</div>
				</div>
			</div>
			<div id="cpcss_response_notice" class="components-notice is-notice is-warning">
				<div class="components-notice__content">
					<p>Publish the post and Enable Optimize CSS delivery in the options above to use this feature.</p>
				</div>
			</div>
			<script>
	var cpcss_rest_url       = \'http://example.org/index.php?rest_route=/wp-rocket/v1/cpcss/post/1\';
	var cpcss_rest_nonce     = \'wp_rest_nonce\';
	var cpcss_generate_btn   = \'Generate Specific CPCSS\';
	var cpcss_regenerate_btn = \'Regenerate specific CPCSS\';
</script>',
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
				<div id="rocket-metabox-cpcss-content">
					<p class="cpcss_generate hidden">
						Generate specific Critical Path CSS for this post.<a href="" target="_blank" rel="noopener noreferrer">More info</a>
					</p>
					<p class="cpcss_regenerate ">
						This post uses specific Critical Path CSS.<a href="" target="_blank" rel="noopener noreferrer">More info</a>
					</p>
					<div class="components-panel__row cpcss_generate cpcss_regenerate">
						<button id="rocket-generate-post-cpss" class="button components-button is-secondary"  disabled=\'disabled\'>
							<span style="display: none;" class="spinner"></span>
							<span class="rocket-generate-post-cpss-btn-txt">Regenerate specific CPCSS</span>
						</button>
					</div>
					<div class="components-panel__row cpcss_regenerate ">
						<button id="rocket-delete-post-cpss" class="button components-button is-secondary"  disabled=\'disabled\'><span>Revert back to the default CPCSS</span></button>
					</div>
				</div>
			</div>
			<div id="cpcss_response_notice" class="components-notice is-notice is-warning">
				<div class="components-notice__content">
					<p>Publish the post and Enable Optimize CSS delivery in the options above to use this feature.</p>
				</div>
			</div>
			<script>
	var cpcss_rest_url       = \'http://example.org/index.php?rest_route=/wp-rocket/v1/cpcss/post/2\';
	var cpcss_rest_nonce     = \'wp_rest_nonce\';
	var cpcss_generate_btn   = \'Generate Specific CPCSS\';
	var cpcss_regenerate_btn = \'Regenerate specific CPCSS\';
</script>',
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
				<div id="rocket-metabox-cpcss-content">
					<p class="cpcss_generate ">
						Generate specific Critical Path CSS for this post.<a href="" target="_blank" rel="noopener noreferrer">More info</a>
					</p>
					<p class="cpcss_regenerate hidden">
						This post uses specific Critical Path CSS.<a href="" target="_blank" rel="noopener noreferrer">More info</a>
					</p>
					<div class="components-panel__row cpcss_generate cpcss_regenerate">
						<button id="rocket-generate-post-cpss" class="button components-button is-secondary"  disabled=\'disabled\'>
							<span style="display: none;" class="spinner"></span>
							<span class="rocket-generate-post-cpss-btn-txt">Generate Specific CPCSS</span>
						</button>
					</div>
					<div class="components-panel__row cpcss_regenerate hidden">
						<button id="rocket-delete-post-cpss" class="button components-button is-secondary"  disabled=\'disabled\'><span>Revert back to the default CPCSS</span></button>
					</div>
				</div>
			</div>
			<div id="cpcss_response_notice" class="components-notice is-notice is-warning">
				<div class="components-notice__content">
					<p>Enable Optimize CSS delivery in the options above to use this feature.</p>
				</div>
			</div>
			<script>
	var cpcss_rest_url       = \'http://example.org/index.php?rest_route=/wp-rocket/v1/cpcss/post/1\';
	var cpcss_rest_nonce     = \'wp_rest_nonce\';
	var cpcss_generate_btn   = \'Generate Specific CPCSS\';
	var cpcss_regenerate_btn = \'Regenerate specific CPCSS\';
	</script>
			',
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
				<div id="rocket-metabox-cpcss-content">
					<p class="cpcss_generate hidden">
						Generate specific Critical Path CSS for this post.<a href="" target="_blank" rel="noopener noreferrer">More info</a>
					</p>
					<p class="cpcss_regenerate ">
						This post uses specific Critical Path CSS.<a href="" target="_blank" rel="noopener noreferrer">More info</a>
					</p>
					<div class="components-panel__row cpcss_generate cpcss_regenerate">
						<button id="rocket-generate-post-cpss" class="button components-button is-secondary"  disabled=\'disabled\'>
							<span style="display: none;" class="spinner"></span>
							<span class="rocket-generate-post-cpss-btn-txt">Regenerate specific CPCSS</span>
						</button>
					</div>
					<div class="components-panel__row cpcss_regenerate ">
						<button id="rocket-delete-post-cpss" class="button components-button is-secondary"  disabled=\'disabled\'><span>Revert back to the default CPCSS</span></button>
					</div>
				</div>
			</div>
			<div id="cpcss_response_notice" class="components-notice is-notice is-warning">
				<div class="components-notice__content">
					<p>Enable Optimize CSS delivery in the options above to use this feature.</p>
				</div>
			</div>
			<script>
	var cpcss_rest_url       = \'http://example.org/index.php?rest_route=/wp-rocket/v1/cpcss/post/2\';
	var cpcss_rest_nonce     = \'wp_rest_nonce\';
	var cpcss_generate_btn   = \'Generate Specific CPCSS\';
	var cpcss_regenerate_btn = \'Regenerate specific CPCSS\';
</script>',
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
				<div id="rocket-metabox-cpcss-content">
					<p class="cpcss_generate ">
						Generate specific Critical Path CSS for this post.<a href="" target="_blank" rel="noopener noreferrer">More info</a>
					</p>
					<p class="cpcss_regenerate hidden">
						This post uses specific Critical Path CSS.<a href="" target="_blank" rel="noopener noreferrer">More info</a>
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
				</div>
			</div>
			<div id="cpcss_response_notice" class="components-notice is-notice is-warning hidden">
				<div class="components-notice__content"></div>
			</div>
			<script>
	var cpcss_rest_url       = \'http://example.org/index.php?rest_route=/wp-rocket/v1/cpcss/post/1\';
	var cpcss_rest_nonce     = \'wp_rest_nonce\';
	var cpcss_generate_btn   = \'Generate Specific CPCSS\';
	var cpcss_regenerate_btn = \'Regenerate specific CPCSS\';
</script>',
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
				<div id="rocket-metabox-cpcss-content">
					<p class="cpcss_generate hidden">
						Generate specific Critical Path CSS for this post.<a href="" target="_blank" rel="noopener noreferrer">More info</a>
					</p>
					<p class="cpcss_regenerate ">
						This post uses specific Critical Path CSS.<a href="" target="_blank" rel="noopener noreferrer">More info</a>
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
				</div>
			</div>
			<div id="cpcss_response_notice" class="components-notice is-notice is-warning hidden">
				<div class="components-notice__content"></div>
			</div>
			<script>
	var cpcss_rest_url       = \'http://example.org/index.php?rest_route=/wp-rocket/v1/cpcss/post/2\';
	var cpcss_rest_nonce     = \'wp_rest_nonce\';
	var cpcss_generate_btn   = \'Generate Specific CPCSS\';
	var cpcss_regenerate_btn = \'Regenerate specific CPCSS\';
</script>',
		],
	],
];
