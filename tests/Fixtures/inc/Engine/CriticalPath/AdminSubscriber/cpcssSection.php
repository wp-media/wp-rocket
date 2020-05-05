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
				<div id="rocket-metabox-cpcss-notice"></div>
				<div id="rocket-metabox-cpcss-content">
				</div>
			</div>
			<div class="components-notice is-notice is-warning">
				<div class="components-notice__content">
					<p>Publish the post, Enable Optimize CSS delivery in WP Rocket settings, and Enable Optimize CSS delivery in the options above to use this feature.</p>
				</div>
			</div>',
		],
		'testShouldDisplayOptionDisabledWarning'         => [
			'config'   => [
				'options'            => [
					'async_css' => 0,
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
				<div id="rocket-metabox-cpcss-notice"></div>
				<div id="rocket-metabox-cpcss-content">
				</div>
			</div>
			<div class="components-notice is-notice is-warning">
				<div class="components-notice__content">
					<p>Enable Optimize CSS delivery in WP Rocket settings to use this feature.</p>
				</div>
			</div>',
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
				<div id="rocket-metabox-cpcss-notice"></div>
				<div id="rocket-metabox-cpcss-content">
				</div>
			</div>
			<div class="components-notice is-notice is-warning">
				<div class="components-notice__content">
					<p>Publish the post and Enable Optimize CSS delivery in the options above to use this feature.</p>
				</div>
			</div>',
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
				<div id="rocket-metabox-cpcss-notice"></div>
				<div id="rocket-metabox-cpcss-content">
				</div>
			</div>
			<div class="components-notice is-notice is-warning">
				<div class="components-notice__content">
					<p>Publish the post to use this feature.</p>
				</div>
			</div>',
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
				<div id="rocket-metabox-cpcss-notice"></div>
				<div id="rocket-metabox-cpcss-content">
				</div>
			</div>
			<div class="components-notice is-notice is-warning">
				<div class="components-notice__content">
					<p>Enable Optimize CSS delivery in the options above to use this feature.</p>
				</div>
			</div>',
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
				<div id="rocket-metabox-cpcss-notice"></div>
				<div id="rocket-metabox-cpcss-content">
				</div>
			</div>',
		],
	],
];
