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
		],
		'expected' => [
			'track_called' => true,
			'event_name'   => 'MCP Ability Executed',
			'event_data'   => [
				'context'             => 'wp_plugin',
				'interaction_channel' => 'MCP',
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
		],
		'expected' => [
			'track_called' => true,
			'event_name'   => 'Test Event',
			'event_data'   => [
				'context'             => 'wp_plugin',
				'interaction_channel' => 'CLI',
			],
		],
	],
];
