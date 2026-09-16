<?php

return [
	'shouldReturnFalseWhenFunctionMissing' => [
		'config'   => [ 'define_tve' => false ],
		'expected' => false,
	],
	'shouldReturnTrueWhenFunctionPresent'  => [
		'config'   => [ 'define_tve' => true ],
		'expected' => true,
	],
];
