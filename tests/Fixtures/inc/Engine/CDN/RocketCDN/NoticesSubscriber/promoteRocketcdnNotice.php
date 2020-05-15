<?php

return [
	'vfs_dir'   => 'public/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'plugins' => [
				'wp-rocket' => [
					'views' => [
						'settings' => [
							'rocketcdn' => [
								'promote-notice.php' => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'views/settings/rocketcdn/promote-notice.php' ),
							],
						],
					],
				],
			],
		],
	],
	'test_data' => [],
];