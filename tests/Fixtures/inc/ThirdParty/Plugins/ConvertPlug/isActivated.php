<?php

return [
	'shouldReturnFalseWhenConvertPlugNotPresent' => [
		'config'   => [ 'cp_version' => null ],
		'expected' => false,
	],
	'shouldReturnTrueWhenConvertPlugPresent'     => [
		'config'   => [ 'cp_version' => '3.0' ],
		'expected' => true,
	],
];
