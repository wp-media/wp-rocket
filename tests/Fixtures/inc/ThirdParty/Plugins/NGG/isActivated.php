<?php

return [
	'shouldReturnFalseWhenNGGNotPresent' => [
		'config'   => [ 'define_ngg' => false ],
		'expected' => false,
	],
	'shouldReturnTrueWhenNGGPresent'     => [
		'config'   => [ 'define_ngg' => true ],
		'expected' => true,
	],
];
