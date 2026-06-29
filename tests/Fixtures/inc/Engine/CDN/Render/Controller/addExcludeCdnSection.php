<?php

return [
	'testWhenRocketCdnActiveInitialUrlIsRocketCdnUrl' => [
		'config'   => [
			'is_rocketcdn' => true,
		],
		'expected' => [
			'initial_url'   => 'https://docs.wp-rocket.me/article/1307-rocketcd/?utm_source=wp_plugin&utm_medium=wp_rocket#exclude-files-from-rocketcdn',
			'rocketcdn_url' => 'https://docs.wp-rocket.me/article/1307-rocketcd/?utm_source=wp_plugin&utm_medium=wp_rocket#exclude-files-from-rocketcdn',
			'rocketcdn_id'  => '5e4c84bd04286364bc958833',
			'other_cdn_url' => 'https://docs.wp-rocket.me/article/42-using-wp-rocket-with-a-cdn#exclude-files-from-your-cdn',
			'other_cdn_id'  => '54c7fa3de4b0512429885b5c',
		],
	],
	'testWhenOtherCdnActiveInitialUrlIsOtherCdnUrl'   => [
		'config'   => [
			'is_rocketcdn' => false,
		],
		'expected' => [
			'initial_url'   => 'https://docs.wp-rocket.me/article/42-using-wp-rocket-with-a-cdn#exclude-files-from-your-cdn',
			'rocketcdn_url' => 'https://docs.wp-rocket.me/article/1307-rocketcd/?utm_source=wp_plugin&utm_medium=wp_rocket#exclude-files-from-rocketcdn',
			'rocketcdn_id'  => '5e4c84bd04286364bc958833',
			'other_cdn_url' => 'https://docs.wp-rocket.me/article/42-using-wp-rocket-with-a-cdn#exclude-files-from-your-cdn',
			'other_cdn_id'  => '54c7fa3de4b0512429885b5c',
		],
	],
];
