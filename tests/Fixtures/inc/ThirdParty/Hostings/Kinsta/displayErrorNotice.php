<?php
return [
		'testNoRightShouldBailOut' => [
			'config' => [
				'has_right' => false,
			]
		],
		'testNotRightScreenShouldBailOut' => [
			'config' => [
				'has_right' => true,
				'screen' => (object) [
					'id' => 'id'
				]
			]
		],
		'testShouldDisplayNotice' => [
			'config' => [
				'has_right' => true,
				'screen' => (object)[
					'id' => 'settings_page_wprocket'
				],
				'notice' => [
					'status'      => 'error',
					'dismissible' => '',
					'message'     => sprintf( 'Your installation seems to be missing core Kinsta files managing Cache clearing, which will prevent your Kinsta installation and WP Rocket from working correctly. Please get in touch with Kinsta support through your %1$sMyKinsta%2$s account to resolve this issue.', '<a href="https://my.kinsta.com/login/" target="_blank">', '</a>' ),
				]
			]
		]
];
