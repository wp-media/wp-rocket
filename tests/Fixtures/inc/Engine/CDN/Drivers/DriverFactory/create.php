<?php

return [
	'testShouldReturnFreeDriverForRocketCDNFreeType' => [
		'config'   => [
			'active_driver' => 'rocketcdn_free',
		],
		'expected' => 'cdn_driver.free',
	],
	'testShouldReturnPaidDriverForRocketCDNType'     => [
		'config'   => [
			'active_driver' => 'rocketcdn',
		],
		'expected' => 'cdn_driver.paid',
	],
	'testShouldReturnByocdnDriverForByocdnType'      => [
		'config'   => [
			'active_driver' => 'byocdn',
		],
		'expected' => 'cdn_driver.byocdn',
	],
	'testShouldReturnNullForUnknownDriverType'        => [
		'config'   => [
			'active_driver' => 'unknown_driver',
		],
		'expected' => null,
	],
];