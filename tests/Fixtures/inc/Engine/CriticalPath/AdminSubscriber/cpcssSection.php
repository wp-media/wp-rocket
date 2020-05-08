<?php

return [
	'vfs_dir'   => 'public/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'plugins' => [
				'wp-rocket' => [
					'views' => [
						'metabox' => [
							'cpcss' => [
								'container.php' => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'views/metabox/cpcss/container.php' ),
							],
						],
					],
				],
			],
		],
	],
	'test_data' => [
		'testShouldDisplayAllWarnings'         => [
			'config'   => [
				'options'            => [
					'async_css' => 0,
				],
				'post'               => [
					'ID'          => 1,
					'post_status' => 'draft',
					'post_type'   => 'post',
				],
				'is_option_excluded' => true,
			],
			'expected' => '<div class="inside">
				<h3>Critical Path CSS</h3>
				<div id="rocket-metabox-cpcss-content"></div>
			</div>
			<div id="cpcss_response_notice" class="components-notice is-notice is-warning">
				<div class="components-notice__content">
				<p>Publish the post, Enable Optimize CSS delivery in WP Rocket settings, and Enable Optimize CSS delivery in the options above to use this feature.</p>
				</div>
			</div>
			<script>
	var cpcss_rest_url       = \'http://example.org/wp-rocket/v1/cpcss/post/1\';
	var cpcss_rest_nonce     = \'wp_rest_nonce\';
	var cpcss_generate_btn   = \'Generate Specific CPCSS\';
	var cpcss_regenerate_btn = \'Regenerate specific CPCSS\';
</script>',
		],
		'testShouldDisplayPostNotPublishedAndOptionExcludedWarning'       => [
			'config'   => [
				'options'            => [
					'async_css' => 1,
				],
				'post'               => [
					'ID'          => 1,
					'post_status' => 'draft',
					'post_type'   => 'post',
				],
				'is_option_excluded' => true,
			],
			'expected' => '<div class="inside">
				<h3>Critical Path CSS</h3>
				<div id="rocket-metabox-cpcss-content"></div>
			</div>
			<div id="cpcss_response_notice" class="components-notice is-notice is-warning">
				<div class="components-notice__content">
				<p>Publish the post and Enable Optimize CSS delivery in the options above to use this feature.</p>
				</div>
			</div>
			<script>
	var cpcss_rest_url       = \'http://example.org/wp-rocket/v1/cpcss/post/1\';
	var cpcss_rest_nonce     = \'wp_rest_nonce\';
	var cpcss_generate_btn   = \'Generate Specific CPCSS\';
	var cpcss_regenerate_btn = \'Regenerate specific CPCSS\';
</script>',
		],
		'testShouldDisplayPostNotPublishedWarning'       => [
			'config'   => [
				'options'            => [
					'async_css' => 1,
				],
				'post'               => [
					'ID'          => 1,
					'post_status' => 'draft',
					'post_type'   => 'post',
				],
				'is_option_excluded' => false,
			],
			'expected' => '<div class="inside">
				<h3>Critical Path CSS</h3>
				<div id="rocket-metabox-cpcss-content"></div>
			</div>
			<div id="cpcss_response_notice" class="components-notice is-notice is-warning">
				<div class="components-notice__content">
				<p>Publish the post to use this feature.</p>
				</div>
			</div>
			<script>
	var cpcss_rest_url       = \'http://example.org/wp-rocket/v1/cpcss/post/1\';
	var cpcss_rest_nonce     = \'wp_rest_nonce\';
	var cpcss_generate_btn   = \'Generate Specific CPCSS\';
	var cpcss_regenerate_btn = \'Regenerate specific CPCSS\';
</script>',
		],
		'testShouldDisplayOptionExcludedFromPostWarning' => [
			'config'   => [
				'options'            => [
					'async_css' => 1,
				],
				'post'               => [
					'ID'          => 1,
					'post_status' => 'publish',
					'post_type'   => 'post',
				],
				'is_option_excluded' => true,
			],
			'expected' => '<div class="inside">
				<h3>Critical Path CSS</h3>
				<div id="rocket-metabox-cpcss-content"></div>
			</div>
			<div id="cpcss_response_notice" class="components-notice is-notice is-warning">
				<div class="components-notice__content">
				<p>Enable Optimize CSS delivery in the options above to use this feature.</p>
				</div>
			</div>
			<script>
	var cpcss_rest_url       = \'http://example.org/wp-rocket/v1/cpcss/post/1\';
	var cpcss_rest_nonce     = \'wp_rest_nonce\';
	var cpcss_generate_btn   = \'Generate Specific CPCSS\';
	var cpcss_regenerate_btn = \'Regenerate specific CPCSS\';
	</script>',
		],
		'testShouldNoWarning'                            => [
			'config'   => [
				'options'            => [
					'async_css' => 1,
				],
				'post'               => [
					'ID'          => 1,
					'post_status' => 'publish',
					'post_type'   => 'post',
				],
				'is_option_excluded' => false,
			],
			'expected' => '<div class="inside">
				<h3>Critical Path CSS</h3>
				<div id="rocket-metabox-cpcss-content"></div>
			</div>
			<div id="cpcss_response_notice" class="components-notice is-notice is-warning hidden">
				<div class="components-notice__content"></div>
			</div>
			<script>
	var cpcss_rest_url       = \'http://example.org/wp-rocket/v1/cpcss/post/1\';
	var cpcss_rest_nonce     = \'wp_rest_nonce\';
	var cpcss_generate_btn   = \'Generate Specific CPCSS\';
	var cpcss_regenerate_btn = \'Regenerate specific CPCSS\';
	</script>',
		],
	],
];
