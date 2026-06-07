<?php

return [
	'shouldAddByocdnAndRocketcdnWhenOptionsAreEmpty'          => [
		'config'   => [],
		'expected' => [
			'byocdn'   => 1,
			'rocketcdn' => 1,
		],
	],
	'shouldAddByocdnAndRocketcdnWhenOtherOptionsAlreadyExist' => [
		'config'   => [ 'cdn_type' => 'rocketcdn' ],
		'expected' => [
			'cdn_type'  => 'rocketcdn',
			'byocdn'    => 1,
			'rocketcdn' => 1,
		],
	],
];
