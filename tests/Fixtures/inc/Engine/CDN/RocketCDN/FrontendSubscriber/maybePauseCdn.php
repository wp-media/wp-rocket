<?php

return [
	'shouldReturnOriginalValueWhenRocketcdnIsSelectedWithFalseValue' => [
		'config'   => [
			'cdn_type' => 'rocketcdn',
			'value'    => false,
		],
		'expected' => [
			'cdn_state' => false,
		],
	],

	'shouldReturnOriginalValueWhenRocketcdnIsSelectedWithTrueValue' => [
		'config'   => [
			'cdn_type' => 'rocketcdn',
			'value'    => true,
		],
		'expected' => [
			'cdn_state' => true,
		],
	],

	'shouldForceTrueWhenByocdnIsSelectedAndValueIsFalse' => [
		'config'   => [
			'cdn_type' => 'byocdn',
			'value'    => false,
		],
		'expected' => [
			'cdn_state' => true,
		],
	],

	'shouldForceTrueWhenByocdnIsSelectedAndValueIsNull' => [
		'config'   => [
			'cdn_type' => 'byocdn',
			'value'    => null,
		],
		'expected' => [
			'cdn_state' => true,
		],
	],
];
