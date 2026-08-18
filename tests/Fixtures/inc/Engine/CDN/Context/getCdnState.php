<?php

return [
	'testShouldReturnNothing'                       => [
		'config'   => [
			'cdn_state' => 'nothing',
		],
		'expected' => 'nothing',
	],
	'testShouldReturnRocketcdnFree'                 => [
		'config'   => [
			'cdn_state' => 'rocketcdn_free',
		],
		'expected' => 'rocketcdn_free',
	],
	'testShouldReturnRocketcdnPro'                   => [
		'config'   => [
			'cdn_state' => 'rocketcdn_pro',
		],
		'expected' => 'rocketcdn_pro',
	],
	'testShouldReturnByocdn'                         => [
		'config'   => [
			'cdn_state' => 'byocdn',
		],
		'expected' => 'byocdn',
	],
	'testShouldFallBackToNothingWhenValueIsInvalid'  => [
		'config'   => [
			'cdn_state' => 'some_garbage_value',
		],
		'expected' => 'nothing',
	],
];
