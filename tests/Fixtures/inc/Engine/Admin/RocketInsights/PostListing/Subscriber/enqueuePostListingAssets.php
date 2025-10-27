<?php

return [
	'shouldEnqueueOnPostListingPage' => [
		'config'   => [
			'screen_id' => 'edit-post',
			'post_type' => 'post',
		],
		'expected' => [
			'should_enqueue' => true,
		],
	],
	'shouldEnqueueOnPageListingPage' => [
		'config'   => [
			'screen_id' => 'edit-page',
			'post_type' => 'page',
		],
		'expected' => [
			'should_enqueue' => true,
		],
	],
	'shouldNotEnqueueOnDashboard'    => [
		'config'   => [
			'screen_id' => 'dashboard',
			'post_type' => null,
		],
		'expected' => [
			'should_enqueue' => false,
		],
	],
	'shouldNotEnqueueOnSettingsPage' => [
		'config'   => [
			'screen_id' => 'settings_page_wprocket',
			'post_type' => null,
		],
		'expected' => [
			'should_enqueue' => false,
		],
	],
	'shouldNotEnqueueForExcludedPostType' => [
		'config'   => [
			'screen_id' => 'edit-elementor_library',
			'post_type' => 'elementor_library',
		],
		'expected' => [
			'should_enqueue' => false,
		],
	],
];
