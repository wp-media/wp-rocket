<?php

return [
	'shouldReturnOriginalValueWhenOtherCdnIsActiveDriver' => [
		'config'   => [
			'is_rocketcdn'        => false,
			'is_rocketcdn_paused' => false,
			'value'               => 1,
		],
		'expected' => 1,
	],
	'shouldReturnTrueWhenRocketcdnIsActiveAndNotPaused'   => [
		'config'   => [
			'is_rocketcdn'        => true,
			'is_rocketcdn_paused' => false,
			'value'               => null,
		],
		'expected' => true,
	],
	'shouldReturnFalseWhenRocketcdnIsPaused'              => [
		'config'   => [
			'is_rocketcdn'        => true,
			'is_rocketcdn_paused' => true,
			'value'               => null,
		],
		'expected' => false,
	],
];
