<?php

return [
	'testShouldReturnFreeDriverForRocketCDNFreeType' => [
		'config'   => [
			'effective_cdn_state' => 'rocketcdn_free',
		],
		'expected' => 'cdn_driver_free',
	],
	'testShouldReturnPaidDriverForRocketCDNType'     => [
		'config'   => [
			'effective_cdn_state' => 'rocketcdn_paid',
		],
		'expected' => 'cdn_driver_paid',
	],
	'testShouldReturnByocdnDriverForByocdnType'      => [
		'config'   => [
			'effective_cdn_state' => 'byocdn',
		],
		'expected' => 'cdn_driver_byocdn',
	],
	'testShouldReturnDisabledDriverForNothingState'  => [
		'config'   => [
			'effective_cdn_state' => 'nothing',
		],
		'expected' => 'cdn_driver_disabled',
	],
	'testShouldReturnDisabledDriverForUnknownState'  => [
		'config'   => [
			'effective_cdn_state' => 'unknown_state',
		],
		'expected' => 'cdn_driver_disabled',
	],
];
