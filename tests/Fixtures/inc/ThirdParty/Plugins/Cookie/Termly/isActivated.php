<?php

return [
	'shouldReturnFalseWhenTermlyNotPresent' => [
		'config'   => [ 'termly_version' => null ],
		'expected' => false,
	],
	'shouldReturnTrueWhenTermlyPresent'     => [
		'config'   => [ 'termly_version' => '1.0' ],
		'expected' => true,
	],
];
