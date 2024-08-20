<?php

return [
	'testShouldDoNothingWhenNoCapability' => [
		'config' => [
			'capability' => false,
			'screen'     => (object) [
				'id' => 'settings_page_wprocket',
			],
			'boxes'            => [],
			'previous_version' => '3.15',
			'beacon'           => [
				'id'  => '123',
				'url' => 'http://example.org',
			],
		],
		'expected' => null,
	],
	'testShouldDoNothingWhenNotSettingsPage' => [
		'config' => [
			'capability' => true,
			'screen'     => (object) [
				'id' => 'dashboard',
			],
			'boxes'            => [],
			'previous_version' => '3.15',
			'beacon'           => [
				'id'  => '123',
				'url' => 'http://example.org',
			],
		],
		'expected' => null,
	],
	'testShouldDoNothingWhenDismissed' => [
		'config' => [
			'capability' => true,
			'screen'     => (object) [
				'id' => 'settings_page_wprocket',
			],
			'boxes'            => [
				'rocket_update_notice',
			],
			'previous_version' => '3.15',
			'beacon'           => [
				'id'  => '123',
				'url' => 'http://example.org',
			],
		],
		'expected' => null,
	],
	'testShouldDoNothingWhenFirstInstall' => [
		'config' => [
			'capability' => true,
			'screen'     => (object) [
				'id' => 'settings_page_wprocket',
			],
			'boxes'            => [],
			'previous_version' => '',
			'beacon'           => [
				'id'  => '123',
				'url' => 'http://example.org',
			],
		],
		'expected' => null,
	],
	'testShouldDoNothingWhenVersionGT317' => [
		'config' => [
			'capability' => true,
			'screen'     => (object) [
				'id' => 'settings_page_wprocket',
			],
			'boxes'            => [],
			'previous_version' => '3.17.1',
			'beacon'           => null,
		],
		'expected' => null,
	],
	'testShouldDisplayNotice' => [
		'config' => [
			'capability' => true,
			'screen'     => (object) [
				'id' => 'settings_page_wprocket',
			],
			'boxes'            => [],
			'previous_version' => '3.15',
			'beacon'           => [
				'id'  => '123',
				'url' => 'http://example.org',
			],
		],
		'expected' => [
			'status'         => 'info',
			'dismissible'    => '',
			'message'        => '<strong>WP Rocket:</strong> the plugin has been updated to the 3.17 version. New feature: <a href="http://example.org" data-beacon-article="123" target="_blank" rel="noopener noreferrer">Lazy Render Content</a>. Check out our documentation to learn more about it.',
			'dismiss_button' => 'rocket_update_notice',
		],
	],
];
