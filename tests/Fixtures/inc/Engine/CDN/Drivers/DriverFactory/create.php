<?php

return [
	'testShouldReturnFreeDriverForRocketCDNFreeType' => [
		'config'   => [
			'active_driver' => 'rocketcdn_free',
		],
		'expected' => 'cdn_driver_free',
	],
	'testShouldReturnPaidDriverForRocketCDNType'     => [
		'config'   => [
			'active_driver' => 'rocketcdn_paid',
		],
		'expected' => 'cdn_driver_paid',
	],
	'testShouldReturnByocdnDriverForByocdnType'      => [
		'config'   => [
			'active_driver' => 'byocdn',
		],
		'expected' => 'cdn_driver_byocdn',
	],
	'testShouldReturnNullForUnknownDriverType'        => [
		'config'   => [
			'active_driver' => 'unknown_driver',
		],
		'expected' => null,
	],
];
