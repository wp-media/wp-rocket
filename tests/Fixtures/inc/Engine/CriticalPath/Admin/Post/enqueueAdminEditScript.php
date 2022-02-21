<?php

return [

	'testShouldNotEnqueueScriptDifferentPage' => [
		'config'   => [
			'page'    => 'options-general.php',
			'pagenow' => 'options-general.php',
			'post'    => (object) [
				'ID'          => 1,
				'post_status' => 'draft',
				'post_type'   => 'post',
			],
			'',
		],
		'expected' => false,
	],

	'testShouldNotEnqueueScriptWhenPostsList' => [
		'config'   => [
			'page'               => 'edit.php',
			'pagenow'            => 'edit.php',
			'options'            => [
				'async_css'        => 0,
				'async_css_mobile' => 0,
			],
			'post'               => (object) [
				'ID'          => 1,
				'post_status' => 'draft',
				'post_type'   => 'post',
			],
			'is_option_excluded' => true,
		],
		'expected' => false,
	],

	'testShouldNotEnqueueScriptDisabledWarning' => [
		'config'   => [
			'page'               => 'edit.php',
			'pagenow'            => 'post.php',
			'options'            => [
				'async_css'        => 0,
				'async_css_mobile' => 0,
			],
			'post'               => (object) [
				'ID'          => 1,
				'post_status' => 'draft',
				'post_type'   => 'post',
			],
			'is_option_excluded' => true,
		],
		'expected' => false,
	],

	'testShouldNotEnqueueScriptPostNotPublishedAndOptionExcludedWarning' => [
		'config'   => [
			'page'               => 'post.php',
			'pagenow'            => 'post.php',
			'options'            => [
				'async_css'        => 1,
				'async_css_mobile' => 0,
			],
			'post'               => (object) [
				'ID'          => 1,
				'post_status' => 'draft',
				'post_type'   => 'post',
			],
			'is_option_excluded' => true,
		],
		'expected' => false,
	],

	'testShouldNotEnqueueScriptPostNotPublishedWarning' => [
		'config'   => [
			'page'               => 'edit.php',
			'pagenow'            => 'post.php',
			'options'            => [
				'async_css'        => 1,
				'async_css_mobile' => 0,
			],
			'post'               => (object) [
				'ID'          => 1,
				'post_status' => 'draft',
				'post_type'   => 'post',
			],
			'is_option_excluded' => false,
		],
		'expected' => false,
	],

	'testShouldNotEnqueueScriptExcludedFromPostWarning' => [
		'config'   => [
			'page'               => 'edit.php',
			'pagenow'            => 'post.php',
			'options'            => [
				'async_css'        => 1,
				'async_css_mobile' => 1,
			],
			'post'               => (object) [
				'ID'          => 1,
				'post_status' => 'publish',
				'post_type'   => 'post',
			],
			'is_option_excluded' => true,
		],
		'expected' => false,
	],

	'testShouldEnqueueScript' => [
		'config'   => [
			'page'               => 'edit.php',
			'pagenow'            => 'post.php',
			'options'            => [
				'async_css'        => 1,
				'async_css_mobile' => 1,
			],
			'post'               => (object) [
				'ID'          => 1,
				'post_status' => 'publish',
				'post_type'   => 'post',
			],
			'is_option_excluded' => false,
			'wp_localize_script' => [
				'rest_url'              => 'http://example.org/wp-rocket/v1/cpcss/post/1',
				'rest_nonce'            => 'wp_rest_nonce',
				'generate_btn'          => 'Generate Specific CPCSS',
				'regenerate_btn'        => 'Regenerate specific CPCSS',
				'wprMobileCpcssEnabled' => 1,
			],
		],
		'expected' => true,
	],
];
