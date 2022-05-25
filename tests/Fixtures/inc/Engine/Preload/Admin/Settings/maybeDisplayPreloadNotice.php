<?php
return [
	'notRightScreenShouldBailOut' => [
		'config' => [
			'screen' => 'random',
			'has_right' => false,
		],
		'expected' => [
			'notice' => [
				'status'  => 'info',
				'message' => '<strong>WP Rocket</strong>: Please wait. The preload service is processing your pages.',
				'id'      => 'rocket-notice-preload-processing',
			]
		]
	],
	'noRightShouldBailOut' => [
		'config' => [
			'screen' => 'settings_page_wprocket',
			'has_right' => false,
		],
		'expected' => [
			'notice' => [
				'status'  => 'info',
				'message' => '<strong>WP Rocket</strong>: Please wait. The preload service is processing your pages.',
				'id'      => 'rocket-notice-preload-processing',
			]
		]
	],
	'notProcessingShouldBailOut' => [
		'config' => [
			'screen' => 'settings_page_wprocket',
			'has_right' => true,
			'load_transient' => true,
			'enabled' => true,
			'transient' => false,
		],
		'expected' => [
			'notice' => [
				'status'  => 'info',
				'message' => '<strong>WP Rocket</strong>: Please wait. The preload service is processing your pages.',
				'id'      => 'rocket-notice-preload-processing',
			]
		]
	],
	'shouldDisplayNotice' => [
		'config' => [
			'screen' => 'settings_page_wprocket',
			'has_right' => true,
			'load_transient' => true,
			'enabled' => true,
			'transient' => true,
			'show_display_notice' => true,
		],
		'expected' => [
			'notice' => [
				'status'  => 'info',
				'message' => '<strong>WP Rocket</strong>: Please wait. The preload service is processing your pages.',
				'id'      => 'rocket-notice-preload-processing',
			]
		]
	]
];
