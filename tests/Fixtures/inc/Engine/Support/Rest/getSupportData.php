<?php

return [
	'testShouldReturnSupportArrayWhenCorrectReferer' => [
		'support_data' => [
			'Website'                  => 'http://example.org',
			'WordPress Version'        => '5.5',
			'WP Rocket Version'        => '3.7.5',
			'Theme'                    => 'WordPress Default',
			'Plugins Enabled'          => 'Hello Dolly',
			'WP Rocket Active Options' => 'Mobile Cache - Disable Emojis - Defer JS Safe - Combine Google Fonts - Preload',
		],
		'expected' => [
			'code'    => 'rest_support_data_success',
			'message' => 'Support data request successful',
			'data'    => [
				'status'  => 200,
				'content' => [
					'Website'                  => 'http://example.org',
					'WordPress Version'        => '5.5',
					'WP Rocket Version'        => '3.7.5',
					'Theme'                    => 'WordPress Default',
					'Plugins Enabled'          => 'Hello Dolly',
					'WP Rocket Active Options' => 'Mobile Cache - Disable Emojis - Defer JS Safe - Combine Google Fonts - Preload',
				],
			],
		],
	],
];
