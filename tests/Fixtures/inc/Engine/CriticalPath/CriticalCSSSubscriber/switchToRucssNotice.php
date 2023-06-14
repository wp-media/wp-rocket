<?php

$notice = <<<NOTICE
<div class="notice notice-wpr-warning ">
<p>
We highly recommend the<b>
updated Remove Unused CSS</b>
for a better CSS optimization. Load CSS Asynchronously is always available as a back-up.</p>
<p>
NOTICE;


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
			'user'   => json_decode( json_encode( [
				'licence_account'    => -1,
				'licence_expiration' => strtotime( 'next year' ),
				'date_created'      => strtotime( 'last year' ),
			] ) ),
        'rucss_status' => false,
        ],
		'expected' => [
			'contains' => true,
			'content' => $notice,
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
			'user'   => json_decode( json_encode( [
				'licence_account'    => -1,
				'licence_expiration' => strtotime( 'next year' ),
				'date_created'      => strtotime( 'last year' ),
			] ) ),
		],
		'expected' => [
			'contains' => false,
			'content' => $notice,
			'rucss_status' => false,
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
			'user'   => json_decode( json_encode( [
				'licence_account'    => -1,
				'licence_expiration' => strtotime( 'next year' ),
				'date_created'      => strtotime( 'last year' ),
			] ) ),
		],
		'expected' => [
			'contains' => false,
			'content' => $notice,
			'rucss_status' => false,
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
			'user'   => json_decode( json_encode( [
				'licence_account'    => -1,
				'licence_expiration' => strtotime( 'last year' ),
				'date_created'      => strtotime( 'last year' ),
			] ) ),
      'rucss_status' => false,
		],
		'expected' => [
			'contains' => false,
			'content' => $notice,
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
			'user'   => json_decode( json_encode( [
				'licence_account'    => -1,
				'licence_expiration' => strtotime( 'next year' ),
				'date_created'      => strtotime( 'last year' ),
			] ) ),
		],
		'expected' => [
			'contains' => false,
			'content' => $notice,
			'rucss_status' => false,
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
      'user'   => json_decode( json_encode( [
				'licence_account'    => -1,
				'licence_expiration' => strtotime( 'next year' ),
				'date_created'      => strtotime( 'last year' ),
			] ) ),
		],
		'expected' => [
      'user_id' => 42,
      'contains' => false,
			'content' => $notice,
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
