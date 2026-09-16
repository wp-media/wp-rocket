<?php

return [
	'shouldReturnFalseWhenConstantMissing' => [
		'config'   => [ 'soliloquy_version' => null ],
		'expected' => false,
	],
	'shouldReturnTrueWhenConstantDefined'  => [
		'config'   => [ 'soliloquy_version' => '2.7.7' ],
		'expected' => true,
	],
];
