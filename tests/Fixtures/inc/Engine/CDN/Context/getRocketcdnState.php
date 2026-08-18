<?php

return [
	'testShouldReturnOngoingActivationFreeWhenLoading' => [
		'config'   => [
			'is_subscription_creation_loading' => true,
		],
		'expected' => 'ongoing_activation_free',
	],
	'testShouldReturnProWhenCdnStateIsRocketcdnPro'     => [
		'config'   => [
			'cdn_state' => 'rocketcdn_pro',
		],
		'expected' => 'pro',
	],
	'testShouldReturnFreeWhenCdnStateIsRocketcdnFree'   => [
		'config'   => [
			'cdn_state' => 'rocketcdn_free',
		],
		'expected' => 'free',
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
