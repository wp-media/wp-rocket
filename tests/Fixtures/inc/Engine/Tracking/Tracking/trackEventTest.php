<?php

return [
	'shouldNotTrackWhenCannotTrack'               => [
		'config'   => [
			'can_track'        => false,
			'event_name'       => 'Test Event',
			'event_data'       => [],
			'detected_channel' => 'UI',
		],
		'expected' => [
			'track_called' => false,
		],
	],
	'shouldInjectInteractionChannelFromDetector'  => [
		'config'   => [
			'can_track'        => true,
			'event_name'       => 'MCP Ability Executed',
			'event_data'       => [ 'ability' => 'wp-rocket/clear-website-cache' ],
			'detected_channel' => 'MCP',
			'request_uri'      => '/wp-admin/options-general.php?page=wprocket',
		],
		'expected' => [
			'track_called' => true,
			'event_name'   => 'MCP Ability Executed',
			'event_data'   => [
				'context'             => 'wp_plugin',
				'interaction_channel' => 'MCP',
				'path'                => '/wp-admin/options-general.php?page=wprocket',
				'ability'             => 'wp-rocket/clear-website-cache',
			],
		],
	],
	'shouldAllowCallerToOverrideInteractionChannel' => [
		'config'   => [
			'can_track'        => true,
			'event_name'       => 'Test Event',
			'event_data'       => [ 'interaction_channel' => 'CLI' ],
			'detected_channel' => 'UI',
			'request_uri'      => '/wp-admin/options-general.php?page=wprocket',
		],
		'expected' => [
			'track_called' => true,
			'event_name'   => 'Test Event',
			'event_data'   => [
				'context'             => 'wp_plugin',
				'interaction_channel' => 'CLI',
				'path'                => '/wp-admin/options-general.php?page=wprocket',
			],
		],
	],
	'shouldDefaultPathToEmptyStringWhenRequestUriMissing' => [
		'config'   => [
			'can_track'        => true,
			'event_name'       => 'Test Event',
			'event_data'       => [],
			'detected_channel' => 'UI',
		],
		'expected' => [
			'track_called' => true,
			'event_name'   => 'Test Event',
			'event_data'   => [
				'context'             => 'wp_plugin',
				'interaction_channel' => 'UI',
				'path'                => '',
			],
		],
	],
	'shouldAllowCallerToOverridePath' => [
		'config'   => [
			'can_track'        => true,
			'event_name'       => 'Test Event',
			'event_data'       => [ 'path' => '/custom-path' ],
			'detected_channel' => 'UI',
			'request_uri'      => '/wp-admin/options-general.php?page=wprocket',
		],
		'expected' => [
			'track_called' => true,
			'event_name'   => 'Test Event',
			'event_data'   => [
				'context'             => 'wp_plugin',
				'interaction_channel' => 'UI',
				'path'                => '/custom-path',
			],
		],
	],
];
