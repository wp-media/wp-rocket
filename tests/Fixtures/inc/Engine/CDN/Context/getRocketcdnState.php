<?php

return [
	'testShouldReturnProWhenCdnStateIsRocketcdnPro'     => [
		'config'   => [
			'cdn_state' => 'rocketcdn_paid',
		],
		'expected' => 'rocketcdn_paid',
	],
	'testShouldReturnFreeWhenCdnStateIsRocketcdnFree'   => [
		'config'   => [
			'cdn_state' => 'rocketcdn_free',
		],
		'expected' => 'rocketcdn_free',
	],
	'testShouldReturnNothingWhenCdnStateIsNothing'      => [
		'config'   => [
			'cdn_state' => 'nothing',
		],
		'expected' => 'nothing',
	],
	'testShouldReturnNothingWhenCdnStateIsByocdn'       => [
		'config'   => [
			'cdn_state' => 'byocdn',
		],
		'expected' => 'nothing',
	],
];
