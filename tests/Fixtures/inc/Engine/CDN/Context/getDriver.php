<?php

return [
	'testShouldReturnByocdnDriver' => [
		'config'   => [
			'cdn_type'      => 'byocdn',
		],
		'expected' => 'byocdn',
	],
	'testShouldReturnrocketcdnPaidDriver' => [
		'config'   => [
			'cdn_type' => 'rocketcdn',
		],
		'expected' => 'rocketcdn',
	],
	'testShouldReturnRocketcdnFreeDriver' => [
		'config'   => [
			'cdn_type' => 'rocketcdn_free',
		],
		'expected' => 'rocketcdn_free',
	],
];
