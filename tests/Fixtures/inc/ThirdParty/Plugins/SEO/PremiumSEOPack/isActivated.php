<?php

return [
	'shouldReturnFalseWhenPSPNotPresent' => [
		'config'   => [ 'define_psp' => false ],
		'expected' => false,
	],
	'shouldReturnTrueWhenPSPPresent'     => [
		'config'   => [ 'define_psp' => true ],
		'expected' => true,
	],
];
