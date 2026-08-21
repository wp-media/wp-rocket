<?php

return [
	'testShouldDisableCdnForNothing'            => [
		'state'    => 'nothing',
		'expected' => [
			'cdn' => 0,
		],
	],
	'testShouldMapByocdn'                       => [
		'state'    => 'byocdn',
		'expected' => [
			'cdn'      => 1,
			'cdn_type' => 'byocdn',
		],
	],
	'testShouldMapRocketcdnFreeToRocketcdnType' => [
		'state'    => 'rocketcdn_free',
		'expected' => [
			'cdn'      => 1,
			'cdn_type' => 'rocketcdn',
		],
	],
	'testShouldMapRocketcdnPaidToRocketcdnType' => [
		'state'    => 'rocketcdn_paid',
		'expected' => [
			'cdn'      => 1,
			'cdn_type' => 'rocketcdn',
		],
	],
	'testShouldFailClosedOnGarbageValue'        => [
		'state'    => 'some_garbage_value',
		'expected' => [
			'cdn' => 0,
		],
	],
];
