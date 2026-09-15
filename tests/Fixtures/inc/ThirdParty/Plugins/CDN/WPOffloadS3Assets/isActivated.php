<?php

return [
	'shouldReturnFalseWhenFunctionMissing' => [
		'config'   => [ 'define_as3cf_assets_init' => false ],
		'expected' => false,
	],
	'shouldReturnTrueWhenFunctionPresent'  => [
		'config'   => [ 'define_as3cf_assets_init' => true ],
		'expected' => true,
	],
];
