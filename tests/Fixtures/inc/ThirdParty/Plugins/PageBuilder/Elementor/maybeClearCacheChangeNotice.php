<?php
return [
	'shouldDisplayNotice' => [
		'config' => [
			'boxes' => [],
			'user_id' => 10,
			'can' => true,
			'transient' => true,
			'notice' => true,
			'rucss' => true,
		],
		'expected' => [
			'notice' => [
				'status'         => 'warning',
				'dismissible'    => '',
				'dismiss_button' => 'maybe_clear_cache_change_notice',
				'message'        => '<strong>WP Rocket:</strong> Your Elementor template was updated. Clear the Used CSS if the layout, design or CSS styles were changed.',
				'action'         => 'elementor_clear_usedcss',
			]
		]
	],
	'shouldDisplayNoticeWithoutRUCSS' => [
		'config' => [
			'boxes' => [],
			'user_id' => 10,
			'can' => true,
			'transient' => true,
			'notice' => true,
			'rucss' => false,
		],
		'expected' => [
			'notice' => [
				'status'         => 'warning',
				'dismissible'    => '',
				'dismiss_button' => 'maybe_clear_cache_change_notice',
				'message'        => '<strong>WP Rocket:</strong> Your Elementor template was updated. Clear the cache if the display conditions were changed.',
				'action'         => 'elementor_clear_usedcss',
			]
		]
	],

];
