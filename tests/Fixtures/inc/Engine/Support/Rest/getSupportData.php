<?php

return [
	'testShouldReturnEmptyArrayWhenWrongReferer' => [
		'support_data' => [
			'Website'                  => 'http://example.org',
			'WordPress Version'        => '5.5',
			'WP Rocket Version'        => '3.7.5',
			'Theme'                    => 'WordPress Default',
			'Plugins Enabled'          => 'Hello Dolly',
			'WP Rocket Active Options' => 'Mobile Cache - Disable Emojis - Defer JS Safe - Combine Google Fonts - Preload',
		],
		'referer' => 'http://google.com',
		'expected' => [],
	],

	'testShouldReturnSupportArrayWhenCorrectReferer' => [
		'support_data' => [
			'Website'                  => 'http://example.org',
			'WordPress Version'        => '5.5',
			'WP Rocket Version'        => '3.7.5',
			'Theme'                    => 'WordPress Default',
			'Plugins Enabled'          => 'Hello Dolly',
			'WP Rocket Active Options' => 'Mobile Cache - Disable Emojis - Defer JS Safe - Combine Google Fonts - Preload',
		],
		'referer' => 'https://wp-rocket.me',
		'expected' => [
			'Website'                  => 'http://example.org',
			'WordPress Version'        => '5.5',
			'WP Rocket Version'        => '3.7.5',
			'Theme'                    => 'WordPress Default',
			'Plugins Enabled'          => 'Hello Dolly',
			'WP Rocket Active Options' => 'Mobile Cache - Disable Emojis - Defer JS Safe - Combine Google Fonts - Preload',
		],
	],
];
