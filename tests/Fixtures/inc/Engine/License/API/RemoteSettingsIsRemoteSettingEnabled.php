<?php

return [
	'testShouldReturnTrueWhenRemoteSettingsReturnsFalse' => [
		'config'   => [
			'remote_settings' => false,
		],
		'expected' => true,
	],
	'testShouldReturnTrueWhenPropertyNotSet' => [
		'config'   => [
			'remote_settings' => (object) [
				'other_property' => true,
			],
		],
		'expected' => true,
	],
	'testShouldReturnTrueWhenPropertyIsTrue' => [
		'config'   => [
			'remote_settings' => (object) [
				'rocket_insights_display_post_column' => true,
			],
		],
		'expected' => true,
	],
	'testShouldReturnFalseWhenPropertyIsFalse' => [
		'config'   => [
			'remote_settings' => (object) [
				'rocket_insights_display_post_column' => false,
			],
		],
		'expected' => false,
	],
	'testShouldReturnFalseWhenPropertyIsZero' => [
		'config'   => [
			'remote_settings' => (object) [
				'rocket_insights_display_post_column' => 0,
			],
		],
		'expected' => false,
	],
	'testShouldReturnTrueWhenPropertyIsOne' => [
		'config'   => [
			'remote_settings' => (object) [
				'rocket_insights_display_post_column' => 1,
			],
		],
		'expected' => true,
	],
];
