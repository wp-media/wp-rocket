<?php

return [
	'testShouldReturnNothingWhenCdnStateIsNothing'         => [
		'config'   => [
			'cdn_state' => 'nothing',
		],
		'expected' => 'nothing',
	],
	'testShouldReturnRocketcdnWhenCdnStateIsRocketcdnFree' => [
		'config'   => [
			'cdn_state' => 'rocketcdn_free',
		],
		'expected' => 'rocketcdn',
	],
	'testShouldReturnRocketcdnWhenCdnStateIsRocketcdnPro'  => [
		'config'   => [
			'cdn_state' => 'rocketcdn_paid',
		],
		'expected' => 'rocketcdn',
	],
	'testShouldReturnByocdnWhenCdnStateIsByocdn'           => [
		'config'   => [
			'cdn_state' => 'byocdn',
		],
		'expected' => 'byocdn',
	],
];
