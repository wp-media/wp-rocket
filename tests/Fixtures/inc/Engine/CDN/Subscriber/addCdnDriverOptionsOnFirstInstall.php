<?php

return [
	'shouldAddByocdnAndRocketcdnWhenOptionsAreEmpty'          => [
		'config'   => [],
		'expected' => [
			'byocdn'   => true,
			'rocketcdn' => true,
		],
	],
	'shouldAddByocdnAndRocketcdnWhenOtherOptionsAlreadyExist' => [
		'config'   => [ 'cdn_type' => 'rocketcdn' ],
		'expected' => [
			'cdn_type'  => 'rocketcdn',
			'byocdn'    => true,
			'rocketcdn' => true,
		],
	],
];
