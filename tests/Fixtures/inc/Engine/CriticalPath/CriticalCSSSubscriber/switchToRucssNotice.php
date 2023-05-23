<?php
return [
    'shouldDisplayNotice' => [
        'config' => [
			'user_id' => 42,
			'boxes' => [

			],
			'in_boxes' => false,
			'async_css' => true,
			'expired_license' => false,
			'is_right_screen' => true,
			'screen' => (object) [
				'id' => 'settings_page_wprocket'
			],
			'rucss_status' => false,
        ],
		'expected' => [
			'user_id' => 42,
			'notice' => [
				'status'                 => 'wpr-warning',
				'dismissible'            => '',
				'dismiss_button'         => 'switch_to_rucss_notice',
				'message'                => 'We highly recommend the <b>updated Remove Unused CSS</b> for a better CSS optimization. Load CSS Asynchronously is always available as a back-up.',
				'action'                 => 'switch_to_rucss',
				'dismiss_button_message' => 'Stay with the old option',
			]
		]
    ],
	'dismissedShouldNotDisplayNotice' => [
		'config' => [
			'user_id' => 42,
			'boxes' => [
				'switch_to_rucss_notice'
			],
			'in_boxes' => true,
			'async_css' => true,
			'expired_license' => false,
			'is_right_screen' => true,
			'screen' => (object) [
				'id' => 'settings_page_wprocket'
			],
			'rucss_status' => false,
		],
		'expected' => [
			'user_id' => 42,
			'notice' => [
				'status'                 => 'wpr-warning',
				'dismissible'            => '',
				'dismiss_button'         => 'switch_to_rucss_notice',
				'message'                => 'We highly recommend the <b>updated Remove Unused CSS</b> for a better CSS optimization. Load CSS Asynchronously is always available as a back-up.',
				'action'                 => 'switch_to_rucss',
				'dismiss_button_message' => 'Stay with the old option',
			]
		]
	],
	'asyncCssDisabledShouldNotDisplayNotice' => [
		'config' => [
			'user_id' => 42,
			'boxes' => [

			],
			'in_boxes' => false,
			'async_css' => false,
			'expired_license' => false,
			'is_right_screen' => true,
			'screen' => (object) [
				'id' => 'settings_page_wprocket'
			],
			'rucss_status' => false,
		],
		'expected' => [
			'user_id' => 42,
			'notice' => [
				'status'                 => 'wpr-warning',
				'dismissible'            => '',
				'dismiss_button'         => 'switch_to_rucss_notice',
				'message'                => 'We highly recommend the <b>updated Remove Unused CSS</b> for a better CSS optimization. Load CSS Asynchronously is always available as a back-up.',
				'action'                 => 'switch_to_rucss',
				'dismiss_button_message' => 'Stay with the old option',
			]
		]
	],
	'expiredLicenceShouldNotDisplayNotice' => [
		'config' => [
			'user_id' => 42,
			'boxes' => [

			],
			'in_boxes' => false,
			'async_css' => true,
			'expired_license' => true,
			'is_right_screen' => true,
			'screen' => (object) [
				'id' => 'settings_page_wprocket'
			],
			'rucss_status' => false,
		],
		'expected' => [
			'user_id' => 42,
			'notice' => [
				'status'                 => 'wpr-warning',
				'dismissible'            => '',
				'dismiss_button'         => 'switch_to_rucss_notice',
				'message'                => 'We highly recommend the <b>updated Remove Unused CSS</b> for a better CSS optimization. Load CSS Asynchronously is always available as a back-up.',
				'action'                 => 'switch_to_rucss',
				'dismiss_button_message' => 'Stay with the old option',
			]
		]
	],
	'wrongScreenShouldNotDisplayNotice' => [
		'config' => [
			'user_id' => 42,
			'boxes' => [

			],
			'in_boxes' => false,
			'async_css' => true,
			'expired_license' => false,
			'is_right_screen' => false,
			'screen' => (object) [
				'id' => 'random'
			],
			'rucss_status' => false,
		],
		'expected' => [
			'user_id' => 42,
			'notice' => [
				'status'                 => 'wpr-warning',
				'dismissible'            => '',
				'dismiss_button'         => 'switch_to_rucss_notice',
				'message'                => 'We highly recommend the <b>updated Remove Unused CSS</b> for a better CSS optimization. Load CSS Asynchronously is always available as a back-up.',
				'action'                 => 'switch_to_rucss',
				'dismiss_button_message' => 'Stay with the old option',
			]
		]
	],
	'RucssDisabledShouldNotDisplayNotice' => [
		'config' => [
			'user_id' => 42,
			'boxes' => [

			],
			'in_boxes' => false,
			'async_css' => true,
			'expired_license' => false,
			'is_right_screen' => true,
			'screen' => (object) [
				'id' => 'settings_page_wprocket'
			],
			'rucss_status' => true,
		],
		'expected' => [
			'user_id' => 42,
			'notice' => [
				'status'                 => 'wpr-warning',
				'dismissible'            => '',
				'dismiss_button'         => 'switch_to_rucss_notice',
				'message'                => 'We highly recommend the <b>updated Remove Unused CSS</b> for a better CSS optimization. Load CSS Asynchronously is always available as a back-up.',
				'action'                 => 'switch_to_rucss',
				'dismiss_button_message' => 'Stay with the old option',
			]
		]
	]
];
