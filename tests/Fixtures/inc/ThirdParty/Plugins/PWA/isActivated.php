<?php

return [
	'shouldReturnFalseWhenServiceWorkerFunctionMissing' => [
		'config'   => [ 'function_exists' => false ],
		'expected' => false,
	],
	'shouldReturnTrueWhenServiceWorkerFunctionPresent'  => [
		'config'   => [ 'function_exists' => true ],
		'expected' => true,
	],
];
