<?php

return [
	'vfs_dir' => 'wp-content/themes/',

	'test_data' => [
		'bailoutWhenFilterReturnsTrue' => [
			'config'   => [
				'rucss_option'   => true,
				'capability'     => false,
				'filter_return'  => true,
				'template_post' => [
					'post_type' => 'et_header_layout',
				]
			],
			'expected' => [
				'transient_set' => false,
			],
		],

		'bailoutWhenTransientIsThere' => [
			'config'   => [
				'rucss_option'     => true,
				'capability'       => true,
				'filter_return'    => false,
				'transient_return' => true,
				'template_post' => [
					'post_type' => 'et_header_layout',
				]
			],
			'expected' => [
				'transient_set' => true,
			],
		],

		'bailoutWhenAnotherPostType' => [
			'config'   => [
				'rucss_option'     => true,
				'capability'       => true,
				'filter_return'    => false,
				'transient_return' => false,
				'template_post' => [
					'post_type' => 'test',
				]
			],
			'expected' => [
				'transient_set' => false,
			],
		],

		'bailoutWhenPostStatusNotPublish' => [
			'config'   => [
				'rucss_option'     => true,
				'capability'       => true,
				'filter_return'    => false,
				'transient_return' => false,
				'template_post' => [
					'post_type' => 'et_header_layout',
					'post_status' => 'trash',
				]
			],
			'expected' => [
				'transient_set' => false,
			],
		],

		'bailoutWhenNoLayoutInDB' => [
			'config'   => [
				'rucss_option'     => true,
				'capability'       => true,
				'filter_return'    => false,
				'transient_return' => false,
				'template_post' => [
					'post_type' => 'et_header_layout',
				]
			],
			'expected' => [
				'transient_set' => false,
			],
		],

		'success' => [
			'config'   => [
				'rucss_option'     => true,
				'capability'       => true,
				'filter_return'    => false,
				'transient_return' => false,
				'template_post' => [
					'post_type'   => 'et_header_layout',
					'post_status' => 'publish',
				],
				'layout_post' => [
					'post_type'   => 'et_template',
					'post_status' => 'publish',
				],
			],
			'expected' => [
				'transient_set' => true,
			],
		],
	],

];
