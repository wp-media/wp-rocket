<?php

return [
	'shouldReturnFalseWhenClassMissing' => [
		'config'   => [ 'define_kksr' => false ],
		'expected' => false,
	],
	'shouldReturnTrueWhenClassPresent'  => [
		'config'   => [ 'define_kksr' => true ],
		'expected' => true,
	],
];
