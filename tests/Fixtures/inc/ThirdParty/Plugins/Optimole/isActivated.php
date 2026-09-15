<?php

return [
	'shouldReturnFalseWhenOptimoleNotPresent' => [
		'config'   => [ 'optml_version' => null ],
		'expected' => false,
	],
	'shouldReturnTrueWhenOptimolePresent'     => [
		'config'   => [ 'optml_version' => '3.1' ],
		'expected' => true,
	],
];
