<?php

return [
	'shouldReturnFalseWhenSeopressToggleFunctionMissing' => [
		'config'   => [ 'function_exists' => false ],
		'expected' => false,
	],
	'shouldReturnTrueWhenSeopressToggleFunctionPresent'  => [
		'config'   => [ 'function_exists' => true ],
		'expected' => true,
	],
];
