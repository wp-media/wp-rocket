<?php

return [
	'testWhenRocketCdnActiveInitialUrlIsRocketCdnUrl' => [
		'config'   => [
			'is_rocketcdn' => true,
		],
		'expected' => [
			'initial_url'   => 'https://docs.wp-rocket.me/article/1307-rocketcd/?utm_source=wp_plugin&utm_medium=wp_rocket#exclude-files-from-rocketcdn',
			'rocketcdn_url' => 'https://docs.wp-rocket.me/article/1307-rocketcd/?utm_source=wp_plugin&utm_medium=wp_rocket#exclude-files-from-rocketcdn',
			'other_cdn_url' => 'https://docs.wp-rocket.me/article/42-using-wp-rocket-with-a-cdn#exclude-files-from-your-cdn',
		],
	],
	'testWhenOtherCdnActiveInitialUrlIsOtherCdnUrl'   => [
		'config'   => [
			'is_rocketcdn' => false,
		],
		'expected' => [
			'initial_url'   => 'https://docs.wp-rocket.me/article/42-using-wp-rocket-with-a-cdn#exclude-files-from-your-cdn',
			'rocketcdn_url' => 'https://docs.wp-rocket.me/article/1307-rocketcd/?utm_source=wp_plugin&utm_medium=wp_rocket#exclude-files-from-rocketcdn',
			'other_cdn_url' => 'https://docs.wp-rocket.me/article/42-using-wp-rocket-with-a-cdn#exclude-files-from-your-cdn',
		],
	],
];
