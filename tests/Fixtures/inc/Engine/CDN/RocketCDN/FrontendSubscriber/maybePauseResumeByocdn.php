<?php

return [
	'shouldReturnOriginalValueWhenRocketcdnIsActiveDriver' => [
		'config'   => [
			'is_rocketcdn'    => true,
			'is_byocdn_paused' => false,
			'value'           => 1,
		],
		'expected' => 1,
	],
	'shouldReturnTrueWhenByocdnIsActiveAndNotPaused'       => [
		'config'   => [
			'is_rocketcdn'    => false,
			'is_byocdn_paused' => false,
			'value'           => null,
		],
		'expected' => true,
	],
	'shouldReturnFalseWhenByocdnIsPaused'                  => [
		'config'   => [
			'is_rocketcdn'    => false,
			'is_byocdn_paused' => true,
			'value'           => null,
		],
		'expected' => false,
	],
];
