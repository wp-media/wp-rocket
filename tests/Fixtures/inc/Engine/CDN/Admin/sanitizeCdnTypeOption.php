<?php

/**
 * Fixtures for CDN\Admin\Subscriber::sanitize_cdn_type_option().
 *
 * The method validates and sanitizes the submitted cdn_type and cdn_state values:
 *   - cdn_type: must be 'rocketcdn' or 'byocdn'; empty or invalid values fall back to 'rocketcdn'.
 *   - cdn_state: when present, must be one of the four known states; invalid values fall back to 'nothing'.
 */
return [
	'testShouldDefaultCdnTypeToRocketcdnWhenEmpty'       => [
		'input'    => [
			'cdn_type' => '',
		],
		'expected' => [
			'cdn_type' => 'rocketcdn',
		],
	],

	'testShouldPreserveValidByocdnTypeAndState'          => [
		'input'    => [
			'cdn_type'  => 'byocdn',
			'cdn_state' => 'byocdn',
		],
		'expected' => [
			'cdn_type'  => 'byocdn',
			'cdn_state' => 'byocdn',
		],
	],

	'testShouldPreserveValidRocketcdnFreeState'          => [
		'input'    => [
			'cdn_type'  => 'rocketcdn',
			'cdn_state' => 'rocketcdn_free',
		],
		'expected' => [
			'cdn_type'  => 'rocketcdn',
			'cdn_state' => 'rocketcdn_free',
		],
	],

	'testShouldDefaultInvalidCdnTypeToRocketcdn'         => [
		'input'    => [
			'cdn_type' => 'unknown_driver',
		],
		'expected' => [
			'cdn_type' => 'rocketcdn',
		],
	],

	'testShouldDefaultInvalidCdnStateToNothing'          => [
		'input'    => [
			'cdn_type'  => 'rocketcdn',
			'cdn_state' => 'invalid_state',
		],
		'expected' => [
			'cdn_type'  => 'rocketcdn',
			'cdn_state' => 'nothing',
		],
	],

	'testShouldLeaveCdnStateAbsentWhenNotInInput'        => [
		'input'    => [
			'cdn_type' => 'rocketcdn',
		],
		'expected' => [
			'cdn_type' => 'rocketcdn',
		],
	],
];
