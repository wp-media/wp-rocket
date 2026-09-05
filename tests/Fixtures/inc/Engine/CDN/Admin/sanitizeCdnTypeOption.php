<?php

/**
 * Fixtures for CDN\Admin\Subscriber::sanitize_cdn_type_option().
 *
 * The method ignores submitted form values for cdn_type and cdn_state and always
 * returns the values currently stored in the database (via Options_Data).
 * This prevents a stale hidden-field / checkbox from overwriting a mode-toggle
 * REST API change that happened on the same page load.
 *
 * Each case provides:
 *   config.options – the DB state that Options_Data / filters should reflect.
 *   config.input   – stale POST values that would arrive if the form was rendered
 *                    before a REST API mode change.
 *   expected       – what the method must return regardless of the input.
 */
return [
	'testShouldReturnByocdnTypeFromDbWhenInputHasStaleRocketcdnType' => [
		'config'   => [
			'options' => [
				'cdn'       => 1,
				'cdn_type'  => 'byocdn',
				'cdn_state' => 'byocdn',
			],
			'input'   => [
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'nothing',
			],
		],
		'expected' => [
			'cdn_type'  => 'byocdn',
			'cdn_state' => 'byocdn',
		],
	],

	'testShouldReturnNothingFromDbWhenInputHasStaleByocdnValues' => [
		'config'   => [
			'options' => [
				'cdn'       => 0,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'nothing',
			],
			'input'   => [
				'cdn_type'  => 'byocdn',
				'cdn_state' => 'byocdn',
			],
		],
		'expected' => [
			'cdn_type'  => 'rocketcdn',
			'cdn_state' => 'nothing',
		],
	],
];
